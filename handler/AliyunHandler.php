<?php

namespace handler;

use lib\Msg;
use logger\Handler;

/**
 * 阿里云日志服务处理类
 * User: dbn
 * Date: 2018/5/22
 * Time: 11:18
 */
class AliyunHandler extends Handler
{
    private $_client = null;

    /**
     * AliyunHandler constructor.
     */
    public function __construct()
    {
        vendor('aliyun-log-php-sdk/Log_Autoload.php');
        $this->_client = new \Aliyun_Log_Client(config('ALIYUN_ENDPOINT'), config('ALIYUN_ACCESS_KEY_ID'), config('ALIYUN_ACCESS_KEY'));
    }

    /**
     * 列出 project 下的所有日志库名称
     * @author dbn
     * @rMethod get
     * @access public
     * @param  string $project 日志项目名称
     * @return void
     */
    public function getProjectLogstoresList($project)
    {
        if (!empty($project)) {
            $req = new \Aliyun_Log_Models_ListLogstoresRequest($project);
            $result = $this->_client->listLogstores($req);
            if ($this->getAliyunResponse($result)) {
                $this->setReturnVal(API_SUCCESS_CODE, Msg::GET_SUCCESS, $result->getLogstores());
            } else {
                $this->setReturnVal(API_ERROR_CODE, Msg::GET_ERROR);
            }
        } else {
            $this->setReturnVal(API_ERROR_CODE, Msg::MISSING_PARAMETERS);
        }
        $this->restReturn();
    }

    /**
     * 创建日志库
     * @author dbn
     * @rMethod get
     * @access public
     * @param  string $project 日志项目名称
     * @param  string $logstore 日志库名称
     * @return void
     */
    public function createLogstore($project, $logstore)
    {
        if (!empty($project) && !empty($logstore)) {
            $req = new \Aliyun_Log_Models_CreateLogstoreRequest($project, $logstore, 3, 2);
            $result = $this->_client->createLogstore($req);
            if ($this->getAliyunResponse($result)) {
                $this->setReturnVal(API_SUCCESS_CODE, Msg::ADD_SUCCESS);
            } else {
                $this->setReturnVal(API_ERROR_CODE, Msg::ADD_ERROR);
            }
        } else {
            $this->setReturnVal(API_ERROR_CODE, Msg::MISSING_PARAMETERS);
        }
        $this->restReturn();
    }

    /**
     * 删除日志库
     * @author dbn
     * @rMethod get
     * @access public
     * @param  string $project 日志项目名称
     * @param  string $logstore 日志库名称
     * @return void
     */
    public function deleteLogstore($project, $logstore)
    {
        if (!empty($project) && !empty($logstore)) {
            $req = new \Aliyun_Log_Models_DeleteLogstoreRequest('vs-test', 'vs-test-3');
            $result = $this->_client->deleteLogstore($req);
            if ($this->getAliyunResponse($result)) {
                $this->setReturnVal(API_SUCCESS_CODE, Msg::DEL_SUCCESS);
            } else {
                $this->setReturnVal(API_ERROR_CODE, Msg::DEL_ERROR);
            }
        } else {
            $this->setReturnVal(API_ERROR_CODE, Msg::MISSING_PARAMETERS);
        }
        $this->restReturn();
    }

    /**
     * 写入日志
     * @author dbn
     * @rMethod post
     * @access public
     * @param string $project 日志项目名称
     * @param string $logstore 日志库名称
     * @param array $logitems 日志记录，纯多维数组（每个值为数组）识别为多条日志记录
     * @param string $topic 日志主题，默认为''，''为有效主题
     * @param string $source 日志来源，默认为''，为''时为当前服务器IP地址
     * @return void
     */
    public function writeLog()
    {
        $project  = input('post.project');
        $logstore = input('post.logstore');
        $logitems = input('post.logitems');
        $topic    = input('post.topic', '');
        $source   = input('post.source', '');

        if (!is_null($project) && is_string($project)
            && !is_null($logstore) && is_string($logstore)
            && !is_null($logitems) && is_array($logitems)
        ) {
            $contents = array();

            $multidimensional = true;
            foreach ($logitems as $value) {
                if (!is_array($value)) {
                    $multidimensional = false;break;
                }
            }

            // 判断是否为多维数组
            if (!$multidimensional) {

                // 一维数组
                foreach ($logitems as &$log) {
                    if (is_array($log)) $log = json_encode($log);
                }

                $logItem = new \Aliyun_Log_Models_LogItem();
                $logItem->setTime(time());
                $logItem->setContents($logitems);
                array_push($contents, $logItem);
            } else {

                // 多维数组
                foreach ($logitems as $value) {

                    foreach ($value as &$log) {
                        if (is_array($log)) $log = json_encode($log);
                    }

                    $logItem = new \Aliyun_Log_Models_LogItem();
                    $logItem->setTime(time());
                    $logItem->setContents($value);
                    array_push($contents, $logItem);
                }
            }

            $req = new \Aliyun_Log_Models_PutLogsRequest($project, $logstore, $topic, $source, $contents);
            $result = $this->_client->putLogs($req);
            if ($this->getAliyunResponse($result)) {
                $this->setReturnVal(API_SUCCESS_CODE, Msg::WRITE_SUCCESS);
            } else {
                $this->setReturnVal(API_ERROR_CODE, Msg::WRITE_ERROR);
            }
        } else {
            $this->setReturnVal(API_ERROR_CODE, Msg::MISSING_PARAMETERS);
        }
        $this->restReturn();
    }

    /**
     * 查询日志分布情况
     * @author dbn
     * @rMethod get
     * @access public
     * @param string $project 日志项目名称
     * @param string $logstore 日志库名称
     * @param int $startTime 查询起始时间，时间戳
     * @param int $endTime 查询结束时间，时间戳
     * @param string $topic 日志主题，默认'null'（防止URL参数丢失）'null'代表主题为''
     * @param string $query 查询语句，默认'null'（防止URL参数丢失），查询语句需经过urlencode(base64_encode($query))处理，查询语句需要配置相应的全文索引（只能登录阿里配置），参考阿里日志服务文档
     * @return void
     * @rExample 查询成功示例：{"code":1,"msg":"\u67e5\u8be2\u6210\u529f","data":{"count":"0","items":[{"from":1527040401,"to":1527040402,"count":0},{"from":1527040402,"to":1527040403,"count":0},{"from":1527040403,"to":1527040404,"count":0}]}}
     */
    public function getHistograms($project, $logstore, $startTime, $endTime, $topic='null', $query='null')
    {
        if (!empty($project) && !empty($logstore) && is_numeric($startTime) && is_numeric($endTime) && is_string($topic) && is_string($query)) {
            $topic = $topic == 'null' ? '' : $topic;
            $query = $query == 'null' ? '' : base64_decode($query);
            $req = new \Aliyun_Log_Models_GetHistogramsRequest($project, $logstore, $startTime, $endTime, $topic, $query);
            $result = $this->_client->getHistograms($req);
            if ($this->getAliyunResponse($result)) {
                $data = array(
                    'count' => $result->getTotalCount(),
                    'items' => $this->histograms2array($result->getHistograms())
                );
                $this->setReturnVal(API_SUCCESS_CODE, Msg::GET_SUCCESS, $data);
            } else {
                $this->setReturnVal(API_ERROR_CODE, Msg::GET_ERROR);
            }
        } else {
            $this->setReturnVal(API_ERROR_CODE, Msg::MISSING_PARAMETERS);
        }
        $this->restReturn();
    }
    private function histograms2array($histograms)
    {
        $result = array();
        if (is_array($histograms) && count($histograms) > 0) {
            foreach ($histograms as $value) {
                if ($value instanceof \Aliyun_Log_Models_Histogram) {
                    $result[] = array(
                        'from'  => $value->getFrom(),
                        'to'    => $value->getTo(),
                        'count' => $value->getCount(),
                    );
                }
            }
        }
        return $result;
    }

    /**
     * 查询日志数据
     * @author dbn
     * @rMethod get
     * @access public
     * @param string $project 日志项目名称
     * @param string $logstore 日志库名称
     * @param int $startTime 查询起始时间，时间戳
     * @param int $endTime 查询结束时间，时间戳
     * @param string $topic 日志主题，默认'null'（防止URL参数丢失）'null'代表主题为''
     * @param string $query 查询语句，默认'null'（防止URL参数丢失），查询语句需经过urlencode(base64_encode($query))处理，查询语句需要配置相应的全文索引（只能登录阿里配置），参考阿里日志服务文档
     * @param int $line 查询日志返回条数，默认返回50条
     * @param int $offset 查询日志返回偏移量，默认0
     * @param int $reverse 0||1 是否反向返回，如果将反向设置为1，则查询将首先返回最新的日志。默认0
     * @return void
     * @rExample 查询成功示例：{"code":1,"msg":"\u67e5\u8be2\u6210\u529f","data":{"count":"3","items":[{"time":"1526984008","source":"192.168.109.1","contents":{"__topic__":"","test":"p166"}},{"time":"1526984228","source":"192.168.109.1","contents":{"__topic__":"","test":"p166"}},{"time":"1526984914","source":"192.168.109.1","contents":{"__topic__":"","test":"p166"}}]}}
     */
    public function getLogs($project, $logstore, $startTime, $endTime, $topic='null', $query='null', $line=50, $offset=0, $reverse=0)
    {
        if (!empty($project) && !empty($logstore) && is_numeric($startTime) && is_numeric($endTime)
            && is_string($topic) && is_string($query) && is_numeric($line) && is_numeric($offset) && is_numeric($reverse)
        ) {
            $topic   = $topic == 'null' ? '' : $topic;
            $query   = $query == 'null' ? '' : base64_decode($query);
            $reverse = intval($reverse) === 1 ? true : false;
            $req = new \Aliyun_Log_Models_GetLogsRequest($project, $logstore, $startTime, $endTime, $topic, $query, $line, $offset, $reverse);
            $result = $this->_client->getLogs($req);
            if ($this->getAliyunResponse($result)) {
                $data = array(
                    'count' => $result->getCount(),
                    'items' => $this->logs2array($result->getLogs())
                );
                $this->setReturnVal(API_SUCCESS_CODE, Msg::GET_SUCCESS, $data);
            } else {
                $this->setReturnVal(API_ERROR_CODE, Msg::GET_ERROR);
            }
        } else {
            $this->setReturnVal(API_ERROR_CODE, Msg::MISSING_PARAMETERS);
        }
        $this->restReturn();
    }
    private function logs2array($logs)
    {
        $result = array();
        if (is_array($logs) && count($logs) > 0) {
            foreach ($logs as $value) {
                if ($value instanceof \Aliyun_Log_Models_QueriedLog) {
                    $result[] = array(
                        'time'     => $value->getTime(),
                        'source'   => $value->getSource(),
                        'contents' => $value->getContents(),
                    );
                }
            }
        }
        return $result;
    }

    /**
     * 获取日志库下分区信息
     * @author dbn
     * @rMethod get
     * @access public
     * @param string $project 日志项目名称
     * @param string $logstore 日志库名称
     * @return void
     * @rExample 查询成功示例：{"code":1,"msg":"\u67e5\u8be2\u6210\u529f","data":{"shardIds":[0,1],"shards":[{"shardId":0,"status":"readwrite","inclusiveBeginKey":"00000000000000000000000000000000","exclusiveEndKey":"80000000000000000000000000000000","createTime":1526629723},{"shardId":1,"status":"readwrite","inclusiveBeginKey":"80000000000000000000000000000000","exclusiveEndKey":"ffffffffffffffffffffffffffffffff","createTime":1526629723}]}}
     */
    public function getLogstoreShards($project, $logstore)
    {
        if (!empty($project) && !empty($logstore)) {
            $listShardRequest  = new \Aliyun_Log_Models_ListShardsRequest($project, $logstore);
            $listShardResponse = $this->_client->listShards($listShardRequest);
            if ($this->getAliyunResponse($listShardResponse)) {
                $data = array(
                    'shardIds' => $listShardResponse->getShardIds(),
                    'shards' => $this->shards2array($listShardResponse->getShards())
                );
                $this->setReturnVal(API_SUCCESS_CODE, Msg::GET_SUCCESS, $data);
            } else {
                $this->setReturnVal(API_ERROR_CODE, Msg::GET_ERROR);
            }
        } else {
            $this->setReturnVal(API_ERROR_CODE, Msg::MISSING_PARAMETERS);
        }
        $this->restReturn();
    }
    private function shards2array($shards)
    {
        $result = array();
        if (is_array($shards) && count($shards) > 0) {
            foreach ($shards as $value) {
                if ($value instanceof \Aliyun_Log_Models_Shard) {
                    $result[] = array(
                        'shardId'           => $value->getShardId(),
                        'status'            => $value->getStatus(),
                        'inclusiveBeginKey' => $value->getInclusiveBeginKey(),
                        'exclusiveEndKey'   => $value->getExclusiveBeginKey(),
                        'createTime'        => $value->getCreateTime(),
                    );
                }
            }
        }
        return $result;
    }

    /**
     * 获取单个分区的日志数据，最大返回100条，新查询数据需要几分钟后才能查询到
     * @author dbn
     * @rMethod get
     * @access public
     * @param string $project 日志项目名称
     * @param string $logstore 日志库名称
     * @param int $shardId 分区ID
     * @param int $startTime 查询起始时间，时间戳
     * @return void
     * @rExample 查询成功示例：{"code":0,"msg":"\u67e5\u8be2\u6210\u529f","data":[{"test":"p1"},{"test":"p2"},{"test":"p166"},{"test":"p166"},{"test":"p166"},{"test":"p166"},{"test":"p2","key2":"p2"}]}
     */
    public function getShardLogs($project, $logstore, $shardId, $startTime)
    {
        if (!empty($project) && !empty($logstore) && is_numeric($shardId) && is_numeric($startTime)) {
            $getCursorRequest = new \Aliyun_Log_Models_GetCursorRequest($project, $logstore, $shardId, null, intval($startTime));
            $response = $this->_client->getCursor($getCursorRequest);
            if ($this->getAliyunResponse($response)) {
                $cursor   = $response->getCursor();
                $count    = 100;
                $logs     = array();
                while(true)
                {
                    $batchGetDataRequest = new \Aliyun_Log_Models_BatchGetLogsRequest($project, $logstore, $shardId, $count, $cursor);
                    $response = $this->_client->batchGetLogs($batchGetDataRequest);
                    if ($cursor == $response->getNextCursor()) {
                        break;
                    }
                    $logGroupList = $response->getLogGroupList();
                    foreach ($logGroupList as $logGroup) {
                        if ($logGroup instanceof \LogGroup) {
                            foreach ($logGroup->getLogsArray() as $log) {
                                if ($log instanceof \Log) {
                                    $tem = array();
                                    foreach($log->getContentsArray() as $content)
                                    {
                                        $tem[$content->getKey()] = $content->getValue();
                                    }
                                    if (!empty($tem)) $logs[] = $tem;
                                }
                            }
                        }

                    }
                    $cursor = $response->getNextCursor();
                }
                $this->setReturnVal(API_ERROR_CODE, Msg::GET_SUCCESS, $logs);
            } else {
                $this->setReturnVal(API_ERROR_CODE, Msg::GET_ERROR);
            }
        } else {
            $this->setReturnVal(API_ERROR_CODE, Msg::MISSING_PARAMETERS);
        }
        $this->restReturn();
    }

    /**
     * 获取投递任务列表
     * @author dbn
     * @rMethod get
     * @access public
     * @param  string $project 日志项目名称
     * @param  string $logstore 日志库名称
     * @return void
     * @rExample 查询成功示例：{"code":1,"msg":"\u67e5\u8be2\u6210\u529f","data":{"count":0,"total":0,"shippers":[]}}
     */
    public function getListShipper($project, $logstore)
    {
        if (!empty($project) && !empty($logstore)) {
            $req = new \Aliyun_Log_Models_ListShipperRequest($project);
            $req->setLogStore($logstore);
            $result = $this->_client->listShipper($req);
            if ($this->getAliyunResponse($result)) {
                $data = array(
                    'count'    => $result->getCount(),
                    'total'    => $result->getTotal(),
                    'shippers' => $result->getShippers()
                );
                $this->setReturnVal(API_SUCCESS_CODE, Msg::GET_SUCCESS, $data);
            } else {
                $this->setReturnVal(API_ERROR_CODE, Msg::GET_ERROR);
            }
        } else {
            $this->setReturnVal(API_ERROR_CODE, Msg::MISSING_PARAMETERS);
        }
        $this->restReturn();
    }

    /**
     * 验证阿里云返回结果
     * @author dbn
     * @access private
     * @param resource $result 阿里云返回结果集
     * @return boolean
     */
    private function getAliyunResponse($result)
    {
        if (method_exists($result, 'getAllHeaders')) {
            $headers = $result->getAllHeaders();
            if ($headers['_info']['http_code'] && intval($headers['_info']['http_code']) >= 200 && intval($headers['_info']['http_code']) < 300) {
                return true;
            }
        }
        return false;
    }
}
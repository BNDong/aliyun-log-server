<?php

namespace Logger\Module\Aliyun;

use Logger\Module\AModule;
use Logger\Library\Content;

/**
 * 阿里云日志服务
 * User: dbn
 * Date: 2018/5/28
 * Time: 14:54
 */
class AliyunModule extends AModule
{

    /**
     * 写入日志
     * @param string $level 日志级别
     * @param int $code 级别编号
     * @param string $message 错误信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题
     * @return void
     */
    public function writeLog($level, $code, $message, $data=array(), $topic='')
    {
        $content = Content::getContent($level, $code, $message, $data, $this->config->getClientId());
        $data = array(
            'project'  => $this->config->getProject(),
            'logstore' => $this->config->getLogstore(),
            'topic'    => $topic,
            'source'   => $this->config->getSource(),
            'logitems' => array(
                $content
            ),
        );
        $result = $this->logger->post($this->config->getHost().'/Aliyun/writeLog', $data);
        $this->checkResult($result);
    }

    /**
     * 获取日志
     * @param int $startTime 查询起始时间，时间戳
     * @param int $endTime 查询结束时间，时间戳
     * @param string $topic 日志主题，默认''
     * @param string $query 查询语句, 默认''，查询语句需要配置相应的全文索引（只能登录阿里配置），参考阿里日志服务文档
     * @param int $line 查询日志返回条数，默认返回50条
     * @param int $offset 查询日志返回偏移量，默认0
     * @param int $reverse 0||1 是否反向返回，如果将反向设置为1，则查询将首先返回最新的日志。默认0
     * @return mixed
     */
    public function getLogs($startTime, $endTime, $topic = '', $query = '', $line = 50, $offset = 0, $reverse = 0)
    {
        $topic = empty($topic) ? 'null' : $topic;
        $query = empty($query) ? 'null' : urlencode(base64_encode($query));
        $result = $this->logger->get($this->config->getHost()."/Aliyun/getLogs/project/".$this->config->getProject()."/logstore/".$this->config->getLogstore()."/startTime/$startTime/endTime/$endTime/topic/$topic/query/$query/line/$line/offset/$offset/reverse/$reverse");
        return $this->checkResult($result);
    }

    /**
     * 验证结果
     */
    private function checkResult($result)
    {
        try {
            $result = json_decode($result, true);
            if ($result && array_key_exists('code', $result) && $result['code'] > 0) {
                return $result;
            } else {
                throw new \LogException($result['msg'].':'.$result['data']);
            }
        } catch (\LogException $e) {
            $e->error();
        }
    }
}
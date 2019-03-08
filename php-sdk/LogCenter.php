<?php

require_once dirname(__FILE__) . '/LogAutoload.php';

use Logger\Module\Aliyun\AliyunModule;
use Logger\Config\AliyunConfig;
use Logger\Library\CommonConst;

/**
 * 日志中心PHP-SDK V1.0
 * User: dbn
 * Date: 2018/5/28
 * Time: 11:12
 */
class LogCenter
{
    /**
     * 日志处理模块
     * @var $_module
     */
    private $_module;

    /**
     * 日志处理模块配置
     * @var $config
     */
    public $config;

    /**
     * 当前类实例
     * @var $_instance
     */
    private static $_instance = null;

    /**
     * LogCenter constructor.
     * @param string $module 处理模块
     * @throws LogException 未定义的模块类型抛出异常
     */
    private function __construct($module)
    {
        try {
            switch (strtolower($module)) {
                case 'aliyun':
                    $this->config  = new AliyunConfig();
                    $this->_module = AliyunModule::getInstance($this);
                    break;
                default:
                    throw new \LogException("module does not exist");
                    break;
            }
        } catch (\LogException $e) {
            $e->error();
        }
    }

    /**
     * 获取当前类实例
     * @param string $module 日志处理模块
     * @return LogCenter
     */
    public static function getInstance($module='Aliyun')
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self($module);
        }
        return self::$_instance;
    }

    /**
     * 写入DEBUG级别日志
     * @param string $message 日志信息
     * @param array/string $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function debug($message, $data=array(), $topic='')
    {
        $this->writeLog(CommonConst::LEVEL_DEBUG_STR, CommonConst::LEVEL_DEBUG_CODE, $message, $data, $topic);
    }

    /**
     * 写入INFO级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function info($message, $data=array(), $topic='')
    {
        $this->writeLog(CommonConst::LEVEL_INFO_STR, CommonConst::LEVEL_INFO_CODE, $message, $data, $topic);
    }

    /**
     * 写入WARN级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function warn($message, $data=array(), $topic='')
    {
        $this->writeLog(CommonConst::LEVEL_WARN_STR, CommonConst::LEVEL_WARN_CODE, $message, $data, $topic);
    }

    /**
     * 写入ERROR级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function error($message, $data=array(), $topic='')
    {
        $this->writeLog(CommonConst::LEVEL_ERROR_STR, CommonConst::LEVEL_ERROR_CODE, $message, $data, $topic);
    }

    /**
     * 写入FATAL级别日志
     * @param string $message 日志信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题，默认为''
     */
    public function fatal($message, $data=array(), $topic='')
    {
        $this->writeLog(CommonConst::LEVEL_FATAL_STR, CommonConst::LEVEL_FATAL_CODE, $message, $data, $topic);
    }

    /**
     * 写入日志处理
     * @param string $level 日志级别
     * @param int $code 级别编号
     * @param string $message 错误信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题
     * @throws \LogException 未知最低日志级别，抛出异常
     */
    private function writeLog($level, $code, $message, $data, $topic)
    {
        try {
            $lowestLevel     = strtolower($this->config->getLowestLevel());
            $lowestLevelCode = null;
            switch ($lowestLevel) {
                case CommonConst::LEVEL_DEBUG_STR:
                    $lowestLevelCode = CommonConst::LEVEL_DEBUG_CODE;
                    break;
                case CommonConst::LEVEL_INFO_STR:
                    $lowestLevelCode = CommonConst::LEVEL_INFO_CODE;
                    break;
                case CommonConst::LEVEL_WARN_STR:
                    $lowestLevelCode = CommonConst::LEVEL_WARN_CODE;
                    break;
                case CommonConst::LEVEL_ERROR_STR:
                    $lowestLevelCode = CommonConst::LEVEL_ERROR_CODE;
                    break;
                case CommonConst::LEVEL_FATAL_STR:
                    $lowestLevelCode = CommonConst::LEVEL_FATAL_CODE;
                    break;
                default:
                    throw new \LogException('unknown lowest level');
                    break;
            }
            if (!is_null($lowestLevelCode) && $code >= $lowestLevelCode) {
                $this->_module->writeLog($level, $code, $message, $data, $topic);
            }
        } catch (\LogException $e) {
            $e->error();
        }
    }

    /**
     * 查询日志
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
        return $this->_module->getLogs($startTime, $endTime, $topic, $query, $line, $offset, $reverse);
    }
}
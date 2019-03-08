<?php

namespace Logger\Module;
use Logger\Config\IConfig;
use Logger\Library\Logger;

/**
 * 模块类抽象类
 * User: dbn
 * Date: 2018/5/28
 * Time: 14:58
 */
abstract class AModule
{
    /**
     * 日志中心实例
     * @var $logCenter
     */
    public $logCenter;

    /**
     * 日志配置实例
     * @var $config
     */
    public $config;

    /**
     * 日志处理类
     * @var $logger
     */
    protected $logger;

    /**
     * 当前类实例
     * @var $_instance
     */
    protected static $_instance = null;
    private   static $_config   = null;

    /**
     * AModule constructor.
     * @param \LogCenter $logCenter
     */
    public function __construct(\LogCenter $logCenter) {
        $this->logCenter = $logCenter;
        $this->config    = $logCenter->config;
        $this->logger    = Logger::getInstance($this->config->getClientId(), $this->config->getSecret());
    }

    /**
     * 获取类实例
     * @param \LogCenter $logCenter
     * @return null|object
     */
    public static function getInstance(\LogCenter $logCenter)
    {
        if (is_null(self::$_instance) || self::$_config !== $logCenter->config) {
            $prodClass = new \ReflectionClass(get_called_class());
            self::$_instance = $prodClass->newInstance($logCenter);
            self::$_config   = $logCenter->config;
        }
        return self::$_instance;
    }

    /**
     * 写入日志
     * @param string $level 日志级别
     * @param int $code 级别编号
     * @param string $message 错误信息
     * @param array/json $data 日志携带数据，数组或json字符串
     * @param string $topic 日志主题
     * @return mixed
     */
    abstract public function writeLog($level, $code, $message, $data=array(), $topic='');

    /**
     * 获取日志
     * @param int $startTime 查询起始时间，时间戳
     * @param int $endTime 查询结束时间，时间戳
     * @param string $topic 日志主题，默认''
     * @param string $query 查询语句, 默认''，，查询语句需要配置相应的全文索引（只能登录阿里配置），参考阿里日志服务文档
     * @param int $line 查询日志返回条数，默认返回50条
     * @param int $offset 查询日志返回偏移量，默认0
     * @param int $reverse 0||1 是否反向返回，如果将反向设置为1，则查询将首先返回最新的日志。默认0
     * @return mixed
     */
    abstract public function getLogs($startTime, $endTime, $topic='', $query='', $line=50, $offset=0, $reverse=0);
}
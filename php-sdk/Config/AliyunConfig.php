<?php

namespace Logger\Config;

/**
 * 日志配置类
 * User: dbn
 * Date: 2018/5/28
 * Time: 14:02
 */
class AliyunConfig implements IConfig
{
    private $_host;
    private $_clientId;
    private $_secret;
    private $_project;
    private $_logstore;
    private $_source;
    private $_lowestLevel;

    public function __construct()
    {
        // ---- 初始化统一设置：默认配置请在此处配置 ----

        $this->_host        = '';       // 日志服务器入口文件
        $this->_clientId    = '';       // 客户端ID
        $this->_secret      = '';       // 客户端secret
        $this->_project     = '';       // 日志项目
        $this->_logstore    = '';       // 日志库
        $this->_source      = '';       // 日志来源，''默认使用服务器公网IP
        $this->_lowestLevel = 'debug';  // 最低日志级别，例：设置为warn级别，不会记录debug和info级别的日志

        // --------------------------------------
    }

    /**
     * @return string
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * @param string $clientId
     */
    public function setClientId($clientId)
    {
        $this->_clientId = $clientId;
    }

    /**
     * @return string
     */
    public function getSecret()
    {
        return $this->_secret;
    }

    /**
     * @param string $secret
     */
    public function setSecret($secret)
    {
        $this->_secret = $secret;
    }

    /**
     * @return string
     */
    public function getProject()
    {
        return $this->_project;
    }

    /**
     * @param string $project
     */
    public function setProject($project)
    {
        $this->_project = $project;
    }

    /**
     * @return string
     */
    public function getLogstore()
    {
        return $this->_logstore;
    }

    /**
     * @param string $logstore
     */
    public function setLogstore($logstore)
    {
        $this->_logstore = $logstore;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->_source = $source;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->_host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * @param mixed $lowestLevel
     */
    public function setLowestLevel($lowestLevel)
    {
        $this->_lowestLevel = $lowestLevel;
    }

    /**
     * @return mixed
     */
    public function getLowestLevel()
    {
        return $this->_lowestLevel;
    }
}
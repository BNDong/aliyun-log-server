<?php

namespace Logger\Config;

/**
 * 配置类接口
 * User: dbn
 * Date: 2018/5/29
 * Time: 9:42
 */
interface IConfig
{
    /**
     * @return string
     */
    public function getClientId();

    /**
     * @param string $clientId
     */
    public function setClientId($clientId);

    /**
     * @return string
     */
    public function getSecret();

    /**
     * @param string $secret
     */
    public function setSecret($secret);

    /**
     * @return string
     */
    public function getHost();

    /**
     * @param string $host
     */
    public function setHost($host);

    /**
     * @param mixed $lowestLevel
     */
    public function setLowestLevel($lowestLevel);

    /**
     * @return mixed
     */
    public function getLowestLevel();
}
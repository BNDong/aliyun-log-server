<?php

namespace logger;

/**
 * Redis处理类
 * User: dbn
 * Date: 2018/5/24
 * Time: 14:17
 */
Class Redis
{
    private static $_redis = null;

    private function __construct(){}

    public static function getInstance()
    {
        if (is_null(self::$_redis) || !(self::$_redis instanceof \Redis)) {
            self::$_redis = new \Redis();
            self::$_redis->connect(config('REDIS_HOST'), config('REDIS_PORT'));
        }
        return self::$_redis;
    }
}
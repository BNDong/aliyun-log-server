<?php
/**
 * 系统配置，优先级高于系统默认配置，获取配置可以使用辅助函数config('配置项')
 * User: dbn
 * Date: 2018/5/21
 * Time: 18:39
 */
return array(
    'ERROR_PATH_HIDING'     => true,     // 错误输出文件路径隐藏，只隐藏捕获到的路径 /www/index.php -> ****/index.php

    //---------------阿里云日志服务---------------------
    'ALIYUN_ENDPOINT'       =>  'cn-beijing.log.aliyuncs.com',
    'ALIYUN_ACCESS_KEY_ID'  =>  '111111111111111111111',
    'ALIYUN_ACCESS_KEY'     =>  '111111111111111111111',

    //---------------安全认证--------------------------
    'AUTH_KEY' => array(
        // 客户端id => 签名密钥

        // Sample
        'test-client-U2FsdGVkX1'    => '367bcd3f257800c90d96cef23f4dd8f',
    ),

    'REQUEST_TIME_RANGE'    => 100, // 接口合法请求时间范围，单位为秒，当前时间-请求时间>=范围，判断为请求非法

    //---------------Redis配置------------------------
    'REDIS_HOST'            => 'localhost',
    'REDIS_PORT'            => '6379',
);
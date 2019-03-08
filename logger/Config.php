<?php
/**
 * 系统默认配置，获取配置可以使用辅助函数config('配置项')
 * User: dbn
 * Date: 2018/5/21
 * Time: 18:39
 */
return array(
    'ERROR_PATH_HIDING'         => true,     // 错误输出文件路径隐藏，只隐藏捕获到的路径 /www/index.php -> ****/index.php
    'URL_PATHINFO_FETCH'        => 'ORIG_PATH_INFO,REDIRECT_PATH_INFO,REDIRECT_URL', // 用于兼容判断PATH_INFO 参数的SERVER替代变量列表
    'REQUEST_TIME_RANGE'        => 100, // 接口合法请求时间范围，单位为秒，当前时间-请求时间>=范围，判断为请求非法
    'DEFAULT_FILTER'            => '', // 默认参数过滤方法 用于input函数...
    'AUTH_KEY'                  => array(), // 客户端签名密钥配置
    'REDIS_HOST'                => 'localhost', // Redis主机
    'REDIS_PORT'                => '6379', // Redis端口
);
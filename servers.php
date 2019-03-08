<?php

require_once realpath(dirname(__FILE__) . '/logger/Logger.php');

/**
 * 入口文件
 * User: dbn
 * Date: 2018/5/21
 * Time: 10:02
 */

//==========================
// URL模式：PATHINFO 例：http://域名/handler/方法/servers.php/key1/val1/key2/val2?key3=val3&key4=val4...
//==========================

\logger\Logger::start();
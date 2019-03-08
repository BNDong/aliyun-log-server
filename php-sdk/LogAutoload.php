<?php
/**
 * 自动加载类
 * User: dbn
 * Date: 2018/5/28
 * Time: 11:12
 */

function Log_PHP_Client_Autoload($className) {
    $path = '';
    $classPath = explode('\\', $className);
    if ($classPath[0] == 'Logger') array_shift($classPath);
    $className && $path = dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $classPath) . '.php';
    if (file_exists($path)) require_once realpath($path);
}

spl_autoload_register('Log_PHP_Client_Autoload');
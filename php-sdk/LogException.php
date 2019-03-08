<?php
/**
 * 异常类
 * User: dbn
 * Date: 2018/5/28
 * Time: 16:07
 */
/**
 * The LogException class.
 */
class LogException extends Exception  {

    public function error()
    {
        // TODO:异常处理统一处理，日志报错不应影响业务流程
    }
}
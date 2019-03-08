<?php

namespace logger\crypt;

/**
 * 加密方式接口
 * User: dbn
 * Date: 2018/5/25
 * Time: 9:58
 */
interface ICrypt
{
    public function strEncrypt($str, $key='');
}
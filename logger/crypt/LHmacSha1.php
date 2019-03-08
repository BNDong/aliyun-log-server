<?php

namespace logger\crypt;

/**
 * cus-asymmetric 加解密类
 * User: dbn
 * Date: 2018/5/25
 * Time: 9:54
 */
class LHmacSha1 implements ICrypt
{
    /**
     * 对字符串进行加密
     * @param  string $str 需要加密的字符串
     * @param  string $key 加密密钥
     * @return string 加密后的串
     * @author dbn
     */
    public function strEncrypt($str, $key='')
    {
        return md5(hash_hmac('sha1', $str, $key));
    }
}
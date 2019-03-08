<?php

namespace logger\hook;

use logger\crypt\LHmacSha1;
use logger\crypt\ICrypt;
use logger\Redis;

/**
 * 请求认证
 * User: dbn
 * Date: 2018/5/21
 * Time: 17:03
 */
class Auth implements IHook
{
    private $_clientId;    // 客户端ID
    private $_requestId;   // 请求ID
    private $_requestTime; // 请求时间
    private $_sign;        // 数据签名
    private $_secret;      // 客户端secret
    private $_encrypt;     // 签名加密方式
    private $_encryptInstance = null; // 签名处理实例
    private $_encryptStr      = '';   // 签名加密原始串

    /**
     * 运行钩子
     * @author dbn
     * @access public
     * @param array $param 参数数组
     * @throws \Exception 请求未通过抛出异常
     * @return mixed
     */
    public function run($param)
    {
        // 验证请求头
        if (!$this->checkHeaders()) throw new AuthException('Missing header');

        // 验证secret
        $this->getSecret();
        if (empty($this->_secret)) throw new AuthException('Missing secret');

        // 验证加密方式
        if (!$this->checkEncrypt()) throw new AuthException('Sign encryption missing');

        // 验证签名
        if (!$this->checkSign()) throw new AuthException('Signature illegal');

        // 获取缓存对象
        $redis = Redis::getInstance();
        if (!($redis instanceof \Redis)) throw new AuthException('Failed to get cached object');

        // 验证请求时间是否超时
        if (time() - $this->_requestTime >= config('REQUEST_TIME_RANGE')) throw new AuthException('Request timed out');

        // 判断请求是否重复
        if ($redis->exists($this->_sign)) throw new AuthException('Repeat request');

        // 验证通过
        $redis->setex($this->_sign, config('REQUEST_TIME_RANGE'), 'logger');
        return true;
    }

    /**
     * 验证请求头
     * @author dbn
     * @access private
     * @return boolean
     */
    private function checkHeaders()
    {
        if (isset($_SERVER['HTTP_L_CLIENT_ID']) && is_string($_SERVER['HTTP_L_CLIENT_ID'])
            && isset($_SERVER['HTTP_L_REQUEST_ID']) && is_string($_SERVER['HTTP_L_REQUEST_ID'])
            && isset($_SERVER['HTTP_L_REQUEST_TIME']) && is_numeric($_SERVER['HTTP_L_REQUEST_TIME'])
            && isset($_SERVER['HTTP_L_REQUEST_SIGN']) && is_string($_SERVER['HTTP_L_REQUEST_SIGN'])
            && isset($_SERVER['HTTP_L_ENCRYPT']) && is_string($_SERVER['HTTP_L_ENCRYPT'])
        ) {
            $this->_clientId    = $_SERVER['HTTP_L_CLIENT_ID'];
            $this->_requestId   = $_SERVER['HTTP_L_REQUEST_ID'];
            $this->_requestTime = $_SERVER['HTTP_L_REQUEST_TIME'];
            $this->_sign        = $_SERVER['HTTP_L_REQUEST_SIGN'];
            $this->_encrypt     = $_SERVER['HTTP_L_ENCRYPT'];
            return true;
        }
        return false;
    }

    /**
     * 获取clientId对应的secret
     * @author dbn
     * @access private
     * @return void
     */
    private function getSecret()
    {
        $this->_secret = array_key_exists($this->_clientId, config('AUTH_KEY')) ? config('AUTH_KEY')[$this->_clientId] : '';
    }

    /**
     * 获取解密Key
     * @author dbn
     * @access private
     * @return string
     */
    private function getSignKey()
    {
        return md5(md5($this->_clientId.$this->_secret));
    }

    /**
     * 验证加密方式
     * @author dbn
     * @access private
     * @return boolean
     */
    private function checkEncrypt()
    {
        switch ($this->_encrypt) {
            case 'l-hmac-sha1':
                $this->_encryptInstance = new LHmacSha1();
                $this->_encryptStr = json_encode(array(
                    'url'       => $_SERVER['HTTP_REFERER'],
                    'data'      => $_SERVER['REQUEST_METHOD'] == 'POST' ? http_build_query($_POST) : '',
                    'method'    => $_SERVER['REQUEST_METHOD'],
                    'requestId' => $this->_requestId,
                    'clientId'  => $this->_clientId,
                    'time'      => $this->_requestTime
                ));
                return true;
            default:
                return false;
        }
    }

    /**
     * 验证签名
     * @author dbn
     * @access private
     * @return boolean
     */
    private function checkSign()
    {
        $signKey   = $this->getSignKey();
        if ($this->_encryptInstance instanceof ICrypt) {
            $signAgain = $this->_encryptInstance->strEncrypt($this->_encryptStr, $signKey);
            if ($signAgain == $this->_sign) return true;
        }
        return false;
    }
}

/**
 * Default Auth Exception.
 */
class AuthException extends \Exception {}
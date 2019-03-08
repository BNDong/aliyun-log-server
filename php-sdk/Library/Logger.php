<?php

namespace Logger\Library;

/**
 * 日志请求处理类
 * User: dbn
 * Date: 2018/5/29
 * Time: 9:37
 */
class Logger
{
    private $_client = null;
    private $_secret = null;
    private static $_instance = null;

    private function __construct($client, $secret)
    {
        $this->_client = $client;
        $this->_secret = $secret;
    }

    public static function getInstance($client, $secret)
    {
        if (!is_null(self::$_instance)
            && (self::$_instance instanceof self)
            && self::$_instance->getClient() === $client
            && self::$_instance->getSecret() === $secret
        ) {
            return self::$_instance;
        }
        self::$_instance = new self($client, $secret);
        return self::$_instance;
    }

    public function getClient()
    {
        return $this->_client;
    }

    public function getSecret()
    {
        return $this->_secret;
    }

    public function get($url)
    {
        return $this->request($url, 'GET');
    }

    public function post($url, $data=array())
    {
        return $this->request($url, 'POST', $data);
    }

    private function request($url, $type, $data=array())
    {
        $type = strtoupper($type);
        $data = http_build_query($data);
        if (!in_array($type, array('GET', 'POST'))) return false;
        $headers['L-CLIENT-ID']    = $this->_client;
        $headers['L-REQUEST-ID']   = $this->createRequestId();
        $headers['L-REQUEST-TIME'] = time();
        $headers['L-ENCRYPT']      = 'l-hmac-sha1';
        $headers['L-REQUEST-SIGN'] = $this->getSign($url, $data, $type, $headers['L-REQUEST-ID'], $headers['L-REQUEST-TIME']);
        $r = $this->getHttpResponse($type, $url, $data, $headers);
        return $r[2];
    }

    private function createRequestId()
    {
        return md5(uniqid('', true));
    }

    private function getSign($url, $data, $method, $requestId, $time)
    {
        $str = json_encode(array(
            'url' => strval($url),
            'data' => strval($data),
            'method' => strval($method),
            'requestId' => strval($requestId),
            'clientId' => strval($this->_client),
            'time' => strval($time)
        ));
        return $this->strEncrypt($str, $this->getKey());
    }

    private function getKey()
    {
        return md5(md5($this->_client.$this->_secret));
    }

    private function strEncrypt($str, $key)
    {
        return md5(hash_hmac('sha1', $str, $key));
    }

    private function getHttpResponse($method, $url, $body, $headers) {
        $request = new RequestCore ( $url );
        foreach ( $headers as $key => $value )
            $request->add_header ( $key, $value );
        $request->set_method ( $method );
        if ($method == "POST" || $method == "PUT")
            $request->set_body ( $body );
        $request->send_request ();
        $response = array ();
        $response [] = ( int ) $request->get_response_code ();
        $response [] = $request->get_response_header ();
        $response [] = $request->get_response_body ();
        return $response;
    }
}
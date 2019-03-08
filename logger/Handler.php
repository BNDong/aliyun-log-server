<?php

namespace logger;

/**
 * 基础 Handler
 * User: dbn
 * Date: 2018/5/22
 * Time: 14:37
 */
class Handler
{
    private $_returnVal;

    /**
     * 设置返回值
     * @author dbn
     * @access protected
     * @param int $code
     * @param string $msg
     * @param mixed $data
     * @return void
     */
    protected function setReturnVal($code, $msg, $data=null)
    {
        $this->_returnVal = array('code' => $code, 'msg' => $msg, 'data' => $data);
    }

    /**
     * 返回结果
     * @author dbn
     * @access protected
     * @return void
     */
    protected function restReturn()
    {
        echo json_encode($this->_returnVal);exit;
    }
}
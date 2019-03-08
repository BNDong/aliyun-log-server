<?php

namespace logger\hook;

/**
 * 钩子接口定义
 * User: dbn
 * Date: 2018/5/21
 * Time: 17:07
 */
interface IHook
{
    /**
     * 运行钩子
     * @author dbn
     * @access public
     * @param array $param 参数数组
     * @return mixed
     */
    public function run($param);
}
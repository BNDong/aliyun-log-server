<?php
/**
 * 系统辅助函数
 * User: dbn
 * Date: 2018/5/21
 * Time: 15:10
 */

/**
 * 获取配置项
 * @author dbn
 * @param string $setting 配置项
 * @return mixed 如有配置返回配置值，无配置返回 NULL。配置文件 ../config/Config.php 优先级高于 ../logger/Config.php
 */
function config($setting=null)
{
    $path = realpath(dirname(dirname(__FILE__)));
    $configMain = require($path . '/config/Config.php');
    $configSub  = require($path . '/logger/Config.php');
    $config = array_merge($configSub, $configMain);
    if (is_string($setting)) {
        return $config[$setting];
    }
    return $setting;
}

/**
 * 系统异常停止，报错输出
 * @author dbn
 * @param string $error 错误信息
 * @return void
 */
function exceptionHalt($error, $type='system')
{
    switch ($type) {
        case 'aliyun':
            echo json_encode(array(
                'code'   => API_ERROR_CODE,
                'msg'    => 'aliyun error',
                'data'   => $error
            ));
            break;
        case 'auth':
            echo json_encode(array(
                'code'   => API_ERROR_CODE,
                'msg'    => 'auth error',
                'data'   => $error
            ));
            break;
        case 'system':
        default:
            echo json_encode(array(
                'code'   => SYS_ERROR_CODE,
                'msg'    => 'system error',
                'data'   => $error
            ));
            break;
    }
    exit;
}

/**
 * 引用第三方类库
 * @author dbn
 * @param string $path vendor文件下类库路径
 * @return void
 */
function vendor($path)
{
    $path = VENDOR_PATH . DIRECTORY_SEPARATOR . trim($path,'/');
    if (file_exists($path)) require_once realpath($path);
}

/**
 * 获取请求参数
 * @param string $name 变量的名称 支持指定类型
 * @param mixed $default 不存在的时候默认值
 * @param mixed $filter 参数过滤方法
 * @param mixed $datas 要获取的额外数据源
 * @return mixed
 */
function input($name, $default=null, $filter=null, $datas=null)
{
    static $_PUT = null;
    if(strpos($name,'.')) { // 指定参数来源
        list($method, $name) =   explode('.',$name,2);
    }else{ // 默认为自动判断
        $method =   'param';
    }
    switch(strtolower($method)) {
        case 'get'     :
            $input =& $_GET;
            break;
        case 'post'    :
            $input =& $_POST;
            break;
        case 'put'     :
            if(is_null($_PUT)){
                parse_str(file_get_contents('php://input'), $_PUT);
            }
            $input 	=	$_PUT;
            break;
        case 'param'   :
            switch($_SERVER['REQUEST_METHOD']) {
                case 'POST':
                    $input  =  $_POST;
                    break;
                case 'PUT':
                    if(is_null($_PUT)){
                        parse_str(file_get_contents('php://input'), $_PUT);
                    }
                    $input 	=	$_PUT;
                    break;
                default:
                    $input  =  $_GET;
            }
            break;
        case 'path'    :
            $input  =   array();
            if(!empty($_SERVER['PATH_INFO'])){
                $input  =   explode('/' ,trim($_SERVER['PATH_INFO'], '/'));
            }
            break;
        case 'request' :
            $input =& $_REQUEST;
            break;
        case 'session' :
            $input =& $_SESSION;
            break;
        case 'cookie'  :
            $input =& $_COOKIE;
            break;
        case 'server'  :
            $input =& $_SERVER;
            break;
        case 'globals' :
            $input =& $GLOBALS;
            break;
        case 'data'    :
            $input =& $datas;
            break;
        default:
            return null;
    }
    if(''==$name) { // 获取全部变量
        $data       =   $input;
        $filters    =   isset($filter)?$filter:config('DEFAULT_FILTER');
        if($filters) {
            if(is_string($filters)){
                $filters    =   explode(',',$filters);
            }
            foreach($filters as $filter){
                $data   =   array_map_recursive($filter,$data); // 参数过滤
            }
        }
    }elseif(isset($input[$name])) { // 取值操作
        $data       =   $input[$name];
        $filters    =   isset($filter)?$filter:config('DEFAULT_FILTER');
        if($filters) {
            if(is_string($filters)){
                if(0 === strpos($filters,'/')){
                    if(1 !== preg_match($filters,(string)$data)){
                        // 支持正则验证
                        return   isset($default) ? $default : null;
                    }
                }else{
                    $filters    =   explode(',',$filters);
                }
            }elseif(is_int($filters)){
                $filters    =   array($filters);
            }

            if(is_array($filters)){
                foreach($filters as $filter){
                    if(function_exists($filter)) {
                        $data   =   is_array($data) ? array_map_recursive($filter,$data) : $filter($data); // 参数过滤
                    }else{
                        $data   =   filter_var($data,is_int($filter) ? $filter : filter_id($filter));
                        if(false === $data) {
                            return   isset($default) ? $default : null;
                        }
                    }
                }
            }
        }
        if(!empty($type)){
            switch(strtolower($type)){
                case 'a':	// 数组
                    $data 	=	(array)$data;
                    break;
                case 'd':	// 数字
                    $data 	=	(int)$data;
                    break;
                case 'f':	// 浮点
                    $data 	=	(float)$data;
                    break;
                case 'b':	// 布尔
                    $data 	=	(boolean)$data;
                    break;
                case 's':   // 字符串
                default:
                    $data   =   (string)$data;
            }
        }
    }else{ // 变量默认值
        $data       =    isset($default)?$default:null;
    }
    return $data;
}
function array_map_recursive($filter, $data) {
    $result = array();
    foreach ($data as $key => $val) {
        $result[$key] = is_array($val)
            ? array_map_recursive($filter, $val)
            : call_user_func($filter, $val);
    }
    return $result;
}
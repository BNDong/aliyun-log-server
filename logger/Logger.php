<?php
namespace logger;

use logger\hook\Auth;

/**
 * 基础文件
 * User: dbn
 * Date: 2018/5/21
 * Time: 10:32
 */
class Logger
{
    /**
     * 应用程序初始化
     * @author dbn
     * @access public
     * @return void
     */
    public static function start()
    {
        header("Content-Type: text/html; charset=utf-8");

        // 系统常量定义
        self::sysDefine();

        // 注册AUTOLOAD方法
        spl_autoload_register('logger\Logger::autoload');

        // 设定错误和异常处理
        register_shutdown_function('logger\Logger::fatalError');
        set_error_handler('logger\Logger::appError');
        set_exception_handler('logger\Logger::appException');

        // 加载系统工具函数库和公共函数库，函数不能重复定义
        require_once LOGGER_PATH . DIRECTORY_SEPARATOR .'Function.php';
        require_once COMMON_PATH . DIRECTORY_SEPARATOR .'Function.php';

        // 请求认证
        Hook::addHook('base', new Auth());

        // 执行Hook
        Hook::run('base');

        // 路径解析调用
        self::pathInfoAnalysis();
    }

    /**
     * 类库自动加载
     * @author dbn
     * @access public
     * @param  string $className 对象类名
     * @return void
     */
    public static function autoload($className)
    {
        $path = '';
        $className && $path = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $className) . '.php';
        if (file_exists($path)) require_once realpath($path);
    }

    /**
     * 系统常量定义
     * @author dbn
     * @access public
     */
    public static function sysDefine()
    {
        define('SYS_ERROR_CODE', -1);
        define('API_ERROR_CODE', 0);
        define('API_SUCCESS_CODE', 1);
        define('ROOT_PATH', dirname(dirname(__FILE__)));
        define('COMMON_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'common');
        define('HANDLER_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'handler');
        define('LOGGER_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'logger');
        define('VENDOR_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'logger' . DIRECTORY_SEPARATOR .'vendor');
        define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
        define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']);
        define('IS_GET',        REQUEST_METHOD =='GET' ? true : false);
        define('IS_POST',       REQUEST_METHOD =='POST' ? true : false);
        define('IS_PUT',        REQUEST_METHOD =='PUT' ? true : false);
        define('IS_DELETE',     REQUEST_METHOD =='DELETE' ? true : false);
        define('IS_AJAX', ((isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || !empty($_POST['ajax']) || !empty($_GET['ajax'])) ? true : false);
    }

    /**
     * 自定义异常处理
     * @author dbn
     * @access public
     * @param mixed $e 异常对象
     */
    public static function appException($e) {
        $error = array();
        $error['message']   =   $e->getMessage();
        $error['file']      =   $e->getFile();
        $error['line']      =   $e->getLine();
        $error['trace']     =   $e->getTraceAsString();
        ob_end_clean();

        if ($e instanceof \Aliyun_Log_Exception) {
            // 阿里云抛出异常处理
            exceptionHalt($error['message'] . ' ' . (config('ERROR_PATH_HIDING') ? '****/' . basename($error['file']) : $error['file']) . ' line:' . $error['line'], 'aliyun');
        } elseif ($e instanceof \logger\hook\AuthException) {
            // 认证抛出异常
            exceptionHalt($error['message'] . ' ' . (config('ERROR_PATH_HIDING') ? '****/' . basename($error['file']) : $error['file']) . ' line:' . $error['line'], 'auth');
        }
        exceptionHalt($error['message'] . ' ' . (config('ERROR_PATH_HIDING') ? '****/' . basename($error['file']) : $error['file']) . ' line:' . $error['line']);
    }

    /**
     * 自定义错误处理
     * @author dbn
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    public static function appError($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            default:
                ob_end_clean();
                $errorStr = "$errstr " . (config('ERROR_PATH_HIDING') ? '****/' . basename($errfile) : $errfile) . " line:$errline";
                exceptionHalt($errorStr);
                break;
        }
    }

    /**
     * 致命错误捕获
     * @author dbn
     * @access public
     * @return void
     */
    public static function fatalError() {
        if ($e = error_get_last()) {
            switch($e['type']){
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    ob_end_clean();
                    exceptionHalt($e['message'] . ' ' . (config('ERROR_PATH_HIDING') ? '****/' . basename($e['file']) : $e['file']) . ' line:' . $e['line']);
                    break;
            }
        }
    }

    /**
     * 路径解析调用
     * @author dbn
     * @access public
     * @throws object 路径解析或调用失败抛出异常
     * @return void
     */
    public static function pathInfoAnalysis()
    {
        try {

            // 验证路径
            if(!isset($_SERVER['PATH_INFO']) || empty($_SERVER['PATH_INFO'])) throw new \ReflectionException("Failed to get url");

            // 路径解析
            $_SERVER['PATH_INFO'] = trim($_SERVER['PATH_INFO'],'/');
            $pathinfo = explode('/', $_SERVER['PATH_INFO']);
            if (!is_array($pathinfo) || count($pathinfo) == 0) throw new \ReflectionException("Url parsing failed");

            // 无方法名默认调用index方法
            if (count($pathinfo) === 1) $pathinfo[] = 'index';
            // 获取handler
            $handlerName = array_shift($pathinfo) . 'Handler';
            // 获取方法名
            $actionName  = array_shift($pathinfo);

            // 判断文件是否存在
            $handlerPath = HANDLER_PATH."/$handlerName.php";
            if (!file_exists($handlerPath)) throw new \ReflectionException("handler $handlerName does not exist");

            $prodClass = new \ReflectionClass('handler\\'.$handlerName);

            // 验证类是否可实例化
            if (!$prodClass || !$prodClass->isInstantiable()) throw new \ReflectionException("handler $handlerName Cannot be instantiated");

            $model = $prodClass->newInstance();

            // 验证方法是否存在
            if (!$prodClass->hasMethod($actionName)) throw new \ReflectionException("method $actionName does not exist");

            $method = new \ReflectionMethod('handler\\'.$handlerName, $actionName);

            // 验证方法是否为public
            if (!$method || !$method->isPublic()) throw new \ReflectionException("method $actionName not public");

            // 参数处理
            self::pathParameter($pathinfo);
            $params     = $method->getParameters();
            $callParams = array();
            if (is_array($params) && count($params) > 0) {
                foreach ($params as $value) {
                    $tem = $value->name;
                    // 验证是否传递参数
                    if (array_key_exists($tem, $_REQUEST)) {
                        $callParams[] = input("request.$tem");
                    } else { // 未传递参数

                        // 验证参数是否存在默认值
                        $temParams = new \ReflectionParameter(array('handler\\'.$handlerName, $actionName), $tem);
                        if ($temParams->isDefaultValueAvailable()) {
                            // 有默认值使用默认值
                            $callParams[] = $temParams->getDefaultValue();
                        } else { // 没有默认值，参数缺失
                            throw new \ReflectionException("Missing parameters $tem");
                        }
                    }
                }
            }

            // 调用handler
            $method->invokeArgs($model, $callParams);

        } catch (\ReflectionException $e) {
            throw new \Exception($e->getMessage());
        }
        return ;
    }

    /**
     * 处理路由参数
     * @author dbn
     * @access public
     * @param array $path 路径中参数部分数组
     * @return void
     */
    private static function pathParameter($path)
    {
        if (!empty($path)) {
            foreach ($path as $key=>$value) {
                if (intval($key) % 2 === 0) {
                    $_GET[$value] = array_key_exists((intval($key)+1), $path) ? $path[(intval($key)+1)] : '';
                }
            }
        }
        //保证$_REQUEST正常取值
        $_REQUEST = array_merge($_POST,$_GET,$_COOKIE);
    }
}
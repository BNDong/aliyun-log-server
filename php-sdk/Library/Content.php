<?php

namespace Logger\Library;

/**
 * 日志内容处理
 * User: dbn
 * Date: 2018/5/29
 * Time: 10:49
 */

class Content
{
    private static $format = array(
        'client_id'  => '##clientId##',
        'log_level'  => '##level##',
        'level_code' => '##code##',
        'time'       => '##time##',
        'message'    => '##message##',
        'data'       => '##data##',
        'backtrace'  => array(
            'file'     => '##backtrace_file##',
            'line'     => '##backtrace_line##',
            'class'    => '##backtrace_class##',
            'function' => '##backtrace_function##',
        ),
    );

    /**
     * 获取日志内容
     * @param string $level 日志级别
     * @param int $code 级别编号
     * @param string $message 错误信息
     * @param array/json $data 日志携带数据
     * @param string $clientId 客户端ID
     * @throws \LogException 未找到回溯路径，抛出异常
     * @return array
     */
    public static function getContent($level, $code, $message, $data, $clientId)
    {
        try {
            $trace = self::getLocationInformation();
            if ($trace instanceof LocationInfo) {
                $data        = is_array($data)
                    ? json_encode($data)
                    : (
                    empty($data)
                        ? '{}'
                        : (json_decode($data) ? $data : '{}')
                    );

                $replace = array(
                    '##clientId##' => $clientId,
                    '##level##'    => $level,
                    '##code##'     => $code,
                    '##time##'     => time(),
                    '##message##'  => $message,
                    '##data##'     => $data,
                    '##backtrace_file##'     => $trace->getFileName(),
                    '##backtrace_line##'     => $trace->getLineNumber(),
                    '##backtrace_class##'    => $trace->getClassName(),
                    '##backtrace_function##' => $trace->getMethodName(),
                );
                $replace     = array_map('addslashes', $replace);
                $placeholder = array_keys($replace);
                $replace     = array_values($replace);
                return json_decode(str_replace($placeholder, $replace, json_encode(self::$format)), true);
            } else {
                throw new \LogException("The backtrace was not found");
            }
        } catch (\LogException $e) {
            $e->error();
        }
    }

    /**
     * 获取调用文件信息
     * @return LocationInfo
     */
    public static function getLocationInformation() {
        $locationInfo = array();
        $trace = debug_backtrace();
        $prevHop = null;
        // make a downsearch to identify the caller
        $hop = array_pop($trace);
        while($hop !== null) {
            if(isset($hop['class'])) {
                // we are sometimes in functions = no class available: avoid php warning here
                $className = strtolower($hop['class']);
                if(!empty($className) and ($className == 'lib\logger\logger' or ($className == 'restclient' && $hop['function'] == 'logger') or $className == 'logcenter' or
                        strtolower(get_parent_class($className)) == 'logcenter')) {
                    $locationInfo['line'] = $hop['line'];
                    $locationInfo['file'] = $hop['file'];
                    break;
                }
            }
            $prevHop = $hop;
            $hop = array_pop($trace);
        }
        $locationInfo['class'] = isset($prevHop['class']) ? $prevHop['class'] : 'main';
        if(isset($prevHop['function']) and
            $prevHop['function'] !== 'include' and
            $prevHop['function'] !== 'include_once' and
            $prevHop['function'] !== 'require' and
            $prevHop['function'] !== 'require_once') {

            $locationInfo['function'] = $prevHop['function'];
        } else {
            $locationInfo['function'] = 'main';
        }
        return new LocationInfo($locationInfo);
    }
}

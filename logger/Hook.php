<?php
namespace logger;

use logger\hook\IHook;

/**
 * 系统钩子
 * User: dbn
 * Date: 2018/5/21
 * Time: 11:49
 */
class Hook
{
    /**
     * 系统钩子组
     * @author dbn
     * @access private
     * @var array $_hooks
     */
    private static $_hooks = array();

    /**
     * 添加hook
     * @author dbn
     * @access public
     * @param string $group hook所属组
     * @param IHook $hook 钩子对象
     * @param array $params 参数数组
     * @return void
     */
    public static function addHook($group, IHook $hook, $params=array())
    {
        if (is_string($group)) {
            if (array_key_exists($group, self::$_hooks) && in_array(['hook'=>$hook, 'params'=>$params], self::$_hooks[$group], true)) return;
            self::$_hooks[$group][] = ['hook'=>$hook, 'params'=>$params];
        }
    }

    /**
     * 运行指定钩子组
     * @author dbn
     * @access public
     * @param string/array $group hook所属组，可传递单个组名或者以数组的方式传递多个组名
     * @return void
     */
    public static function run($group)
    {
        if (!empty(self::$_hooks) && !empty($group)) {
            if (is_array($group)) {
                foreach ($group as $groupField) {
                    if (array_key_exists($groupField, self::$_hooks)) {
                        foreach (self::$_hooks[$groupField] as $hook) {
                            if (isset($hook['hook']) && isset($hook['params']) && ($hook['hook'] instanceof IHook)) $hook['hook']->run($hook['params']);
                        }
                    }
                }
            } elseif (is_string($group)) {
                if (array_key_exists($group, self::$_hooks)) {
                    foreach (self::$_hooks[$group] as $hook) {
                        if (isset($hook['hook']) && isset($hook['params']) && ($hook['hook'] instanceof IHook)) $hook['hook']->run($hook['params']);
                    }
                }
            }
        }
    }
}
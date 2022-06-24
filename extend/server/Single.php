<?php
// +----------------------------------------------------------------------
// | Created by [ PhpStorm ]
// +----------------------------------------------------------------------
// | Copyright (c) 2021-2022.
// +----------------------------------------------------------------------
// | Create Time (2021/11/12 - 11:08 上午)
// +----------------------------------------------------------------------
// | tangyijun
// +----------------------------------------------------------------------
namespace server;

trait Single
{
    /**
     * @param string $name
     * @param string $key
     * @return static
     */
    public static function getInstance($name = 'default', $key = 'default',$options = [])
    {
        $class = get_called_class();
        $instance = &self::statics($class);
        if (isset($instance[$name][$key])) {
            return $instance[$name][$key];
        }
        return $instance[$name][$key] = new $class($name,$key,$options);
    }

    private static function &statics($name, $defaultValue = null, $reset = false)
    {
        static $data = [], $default = [];
        if (isset($data[$name]) || array_key_exists($name, $data)) {
            if ($reset) {
                $data[$name] = $default[$name];
            }
            return $data[$name];
        }
        if (isset($name)) {
            if ($reset) {
                return $data;
            }
            $default[$name] = $data[$name] = $defaultValue;
            return $data[$name];
        }
        foreach ($default as $name => $value) {
            $data[$name] = $value;
        }
        return $data;
    }
}
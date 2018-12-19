<?php
/**
 * User: Jaackie
 * Date: 2018/3/18
 */

trait Instance
{
    private static $instances;

    public function __construct()
    {
    }

    public function __clone()
    {
    }

    /**
     * @param string $id
     * @return static
     */
    public static function instance($id = '')
    {
        $className = get_called_class();
        $key = md5($className . ':' . $id);
        if (!isset(self::$instances[$key])) {
            self::$instances[$key] = new $className($id);
        }
        return self::$instances[$key];
    }
}
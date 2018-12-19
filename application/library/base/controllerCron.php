<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/11/22
 * Time: 15:29
 */
class base_controllerCron extends base_controller
{
    const LINE = "\n";

    private $_start_time;

    public function init()
    {
        $this->_deny();
        parent::init();
        self::__message('Start:');
        $this->_start_time = microtime(1);
    }

    /**
     * 限制非服务器访问
     */
    private function _deny()
    {
        if ('cli' !== PHP_SAPI) {
            header("HTTP/1.1 403 Forbidden");
            $this->__halt('403');
        }
    }

    public function __destruct()
    {
        $end_time = sprintf('%0.3f', (microtime(1) - $this->_start_time) * 1000);
        self::__message("End, {$end_time}ms.");
    }

    protected static function __message($msg, $split = self::LINE)
    {
        echo date('[Y-m-d H:i:s] ') . $msg . $split;
    }

    protected static function __halt($msg, $split = self::LINE)
    {
        self::__message($msg, $split);
        return false || exit;
    }

}
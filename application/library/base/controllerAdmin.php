<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/11/22
 * Time: 15:29
 */
class base_controllerAdmin extends base_controller
{

    public function init()
    {
        parent::init();
        self::_ipLimit();
        $this->_view->assign('menu', menuModel::admin());
        $this->_view->assign('_c', strtolower($this->_request->getControllerName()));
        $this->_view->assign('_a', $this->_request->getActionName());
    }

    /**
     * ip限制
     * @return bool
     */
    private static function _ipLimit()
    {
        $ip = tool_ip::get();
        if ($ip && $ip != 'unknown') {
            $ip_list = file_get_contents(APPLICATION_PATH . '/conf/ip.txt');
            $ip_arr = explode("\n", $ip_list);
            foreach ($ip_arr as $ip_white) {
                if ($ip == $ip_white) {
                    return true;
                }
            }
        }
        header('HTTP/1.1 403 Forbidden');
        return false || exit;
    }

    /**
     * 包含头尾的完整网页显示
     * @param $name
     * @param bool $header
     * @param bool $footer
     * @return false
     */
    protected function __show($name = null, $header = true, $footer = true)
    {
        if ($header) {
            parent::display('../header');
        }
        $name = $name ?: $this->_request->getActionName();
        $this->display($name);
        if ($footer) {
            parent::display('../footer');
        }
        return false || exit;
    }

    /**
     * 显示单个页面
     * @param null $name
     * @param bool $exit
     * @return bool
     */
    protected function __display($name = null, $exit = true)
    {
        $name = $name ?: $this->_request->getActionName();
        parent::display($name);
        if ($exit) {
            return false || exit;
        }
    }

    protected function __errorAjax($message = '', $code = 1, $data = [])
    {
        $this->__response($code, $data, $message);
    }

    protected function __successAjax($data = [], $code = 0, $message = '')
    {
        $this->__response($code, $data, $message);
    }

    protected function __errorPage($message = '', $code = 1, $data = [])
    {
        echo $message;
        return false || exit;
    }

    protected function __e($message = '', $code = 1, $data = [])
    {
        is_ajax() ? $this->__errorAjax($message, $code, $data) : $this->__errorPage($code, $message, $data);
    }


}
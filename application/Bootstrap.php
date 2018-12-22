<?php

/**
 * Created by PhpStorm.
 * User: jaackie
 * Date: 2017/3/11
 * Time: 下午3:51
 */
class Bootstrap extends Yaf_Bootstrap_Abstract
{
    protected $config;

    public function _initConfig(Yaf_Dispatcher $dispatcher)
    {
        $this->config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set('config', $this->config);
        //判断请求方式，命令行请求应跳过一些HTTP请求使用的初始化操作，如模板引擎初始化
        define('REQUEST_METHOD', strtoupper($dispatcher->getRequest()->getMethod()));
        $dispatcher->autoRender(false);
    }

    public function _initImport()
    {
//        Yaf_Loader::import(APPLICATION_PATH . '/application/functions/app.php');
    }

    public function _initView(Yaf_Dispatcher $dispatcher)
    {
        //命令行下基本不需要使用smarty
        /*if (REQUEST_METHOD != 'CLI') {
            $smarty = new Smarty_Adapter(null, $this->config->smarty);
            $smarty->registerFunction('function', 'truncate', array('Tools', 'truncate'));
            $dispatcher->setView($smarty);
        }*/
    }
}
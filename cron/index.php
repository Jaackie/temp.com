<?php
/**
 * crontab入口文件
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/12/22 0022
 * Time: 9:52
 */
unset($argv[0]);

if (isset($argv[1])) {
    $url = trim($argv[1]);
    unset($argv[1]);
    if (isset($argv[2])) {
        parse_str($argv[2], $params);
    } else {
        $params = [];
    }
} else {
    $url = '/index/index';
    $params = [];
}

$_GET = &$params;

define('APPLICATION_PATH', dirname(__DIR__));

$app = new Yaf_Application(APPLICATION_PATH . '/conf/application.ini');
$dispatcher = $app->getDispatcher();
$dispatcher->autoRender(false);

$request = new Yaf_Request_Simple();
$request->setRequestUri('/crontab' . $url);
$response = $dispatcher->dispatch($request);
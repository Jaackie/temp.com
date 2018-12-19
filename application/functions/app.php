<?php
/**
 * @author Jaackie <ljq4@meitu.com>
 * @Date 2018/3/14
 */

/**
 * 获取资源路径
 * @return mixed
 */
function r()
{
    $config = Yaf_Registry::get("config");
    return $config['application']['resource'];
}

/**
 * 资源路径
 */
define('R', r());

function include_view($viewName, $module = 'Admin')
{
    include(APPLICATION_PATH . '/application/modules/' . $module . '/views/' . $viewName . '.phtml');
}

function is_ajax()
{
    if (isset($_REQUEST['is_ajax'])) {
        return $_REQUEST['is_ajax'] != 0;
    }
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
}

function rr(&$data, $field = '', $depth = 2)
{
    if (is_array($data)) {
        if (!$data) return;
        $fieldArr = explode(',', $field);
        if ($depth == 2) {
            foreach ($data as &$val) {
                foreach ($fieldArr as $k) {
                    if (isset($val[$k])) {
                        $val[$k] = R . $val[$k];
                    }
                }
            }
        } elseif ($depth == 1) {
            foreach ($fieldArr as $k) {
                if (isset($data[$k])) {
                    $data[$k] = R . $data[$k];
                }
            }
        }
    } elseif (is_string($data)) {
        $data = R . $data;
    } else {
        return;
    }
}

/**
 * 打印数据
 */
function __d()
{
    $args = func_get_args();
    echo "<pre>";
    foreach ($args as $arg) {
        var_export($arg);
    }
    echo "</pre>";
}

/**
 * 打印数据并退出
 */
function __dd()
{
    $args = func_get_args();
    echo "<pre>";
    foreach ($args as $arg) {
        var_export($arg);
    }
    echo "</pre>";
    exit;
}


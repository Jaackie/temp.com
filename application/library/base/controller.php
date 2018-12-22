<?php

/**
 * Created by PhpStorm.
 * User: jaackie
 * Date: 2017/3/11
 * Time: 下午4:19
 */
class base_controller extends Yaf_Controller_Abstract
{
    /**
     * @var string callback function
     */
    private $_callback = null;

    /**
     * @var float time when script start
     */
    private $_timer_start = 0.0;

    /**
     * @var null|string
     */
    private $_write_post = null;

    public function init()
    {
//        $this->_jsonCallback();

    }

    /**
     * 前端js的回调
     */
    private function _jsonCallback()
    {
        if (!empty($_REQUEST['callback']) && preg_match('/^[.\w$]+$/i', $_REQUEST['callback'])) {
            $this->_callback = $_REQUEST['callback'];
        }
    }

    /**
     * 获取post中的page，没有设置默认1;
     * @param bool $positive 是否要求正数（偶尔有负数的用作它用）
     * @return int
     */
    public function getPage($positive = true)
    {
        $page = isset($_POST['page']) ? intval($_POST['page']) : 1;

        return $positive && $page <= 0 ? 1 : $page;
    }

    /**
     * 获取一个必须的参数
     * @param $keyName
     * @return string $token
     * @throws
     */
    public function requirePost($keyName)
    {
        $post = $this->post($keyName);
        if ($post !== null) {
            return $post;
        } else {
            $this->__error(res_msg::E_MISS_ARGS);
        }
    }

    /**
     * 获取post参数
     * @param $keyName
     * @param $defaultValue
     * @return string $token
     * @throws Exception
     */
    public function post($keyName, $defaultValue = null)
    {
        if (isset($_POST[$keyName])) {
            return $_POST[$keyName];
        } else {
            return $defaultValue;
        }
    }

    /**
     * 获取get参数
     * @param $keyName
     * @param $defaultValue
     * @return string $token
     * @throws Exception
     */
    public function get($keyName, $defaultValue = null)
    {
        if (isset($_GET[$keyName])) {
            return $_GET[$keyName];
        } else {
            return $defaultValue;
        }
    }

    /**
     * @param string $keyName
     * @param mixed $defaultValue
     * @return mixed
     */
    public function input($keyName, $defaultValue = null)
    {
        if (isset($_REQUEST[$keyName])) {
            return $_REQUEST[$keyName];
        } else {
            return $defaultValue;
        }
    }

    /**
     * Return result
     * @param mixed $result
     * @param string $message
     * @return bool
     */
    protected function __result($result = [], $message = '')
    {
        return $this->__response(200, $result, $message);
    }

    /**
     * Return error
     * @param string $errNo
     * @param string $message
     * @param mixed $data
     * @return bool
     */
    protected function __error($errNo, $message = '', $data = [])
    {
        if (preg_match('/^(\d+):(.+)$/is', $errNo, $res)) {
            $data = $message;
            $errNo = $res[1];
            $message = $res[2];
        }
        return $this->__response($errNo, $data, $message);
    }

    /**
     * Response return data
     * @param int $code - return code, 200 for success, others for error
     * @param mixed $result - return result
     * @param string $message
     * @return bool
     */
    protected function __response($code, $result = null, $message = null)
    {
        $data = ['code' => intval($code)];
        if ($code == 200 || $data !== null) {
            $data['result'] = $result;
        }
        if ($message !== null) {
            $data['msg'] = $message;
        }
        $res = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($this->_callback) {
            echo $this->_callback, '(', $res, ');';
        } else {
            echo $res;
        }
        /**
         * Write log
         */
        /*$timer = microtime(1) - $this->_timer_start;
        $file = '/tmp/api.' . date('Ymd') . '.log';
        $writePost = self::_writePost($this->_write_post);
        $logData = sprintf("[%s]\t%03d\t%10.2f\t%s\n", date('m-d H:i:s'), $code, $timer * 1000, $_SERVER['REQUEST_URI'] . $writePost);
        file_put_contents($file, $logData, FILE_APPEND);*/

        return false || exit;
    }

    /**
     * 开启post参数记录
     * @param string $needle 如果null则不写, 如果'*'写全部, 如果'key1,key2,key3'写若干个需要的
     * @return $this
     */
    protected function __setWritePost($needle)
    {
        $this->_write_post = $needle;
        return $this;
    }

    /**
     * 是否需要开启post参数记录
     * @param null|string $needle 如果null则不写, 如果'*'写全部, 如果'key1,key2,key3'写若干个需要的
     * @return string
     */
    private static function _writePost($needle = null)
    {
        if (!$needle) return '';

        $return = "\t";
        if ($needle == '*') {
            foreach ($_POST as $key => $val) {
                $return .= "{$key}:{$val},";
            }
            return $return;
        }

        $key_arr = explode(',', $needle);
        foreach ($key_arr as $key) {
            $val = isset($_POST[$key]) ? $_POST[$key] : '';
            $return .= "{$key}:{$val},";
        }

        return $return;
    }

    /**
     * @param $name
     * @param mixed $value
     * @return $this
     */
    protected function __assign($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as &$val) {
                self::_htmlSpecialChars($val);
            }
        } else {
            self::_htmlSpecialChars($value);
        }
        $this->_view->assign($name, $value);
        return $this;
    }

    /**
     * 输出过滤
     * @param $value
     */
    private static function _htmlSpecialChars(&$value)
    {
        if (is_array($value)) {
            foreach ($value as $val) {
                self::_htmlSpecialChars($val);
            }
        } else {
            $value = is_numeric($value) || is_bool($value) ? $value : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        }
    }

}
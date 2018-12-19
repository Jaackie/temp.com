<?php
/**
 * User: Jaackie
 * Date: 2018/3/18
 */

class Uploader
{
    use Instance;

    private $_file;
    private $_allow_suffix = [];

    private $_error = '0:未知';

    private $_size = 50;    //大小限制 单位M

    const ERROR_SUFFIX = '1:格式不正确';
    const ERROR_SIZE = '2:文件大小超出限制';
    const ERROR_SYSTEM = '3:系统错误';

    /**
     * 设置上传文件 如$_FILES['file']
     * @param $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->_file = $file;
        return $this;
    }

    /**
     * 设置允许的后缀列表
     * @param $suffixArr
     * @return $this
     */
    public function setAllowSuffix($suffixArr)
    {
        $this->_allow_suffix = $suffixArr;
        return $this;
    }

    /**
     * 设置大小限制
     * @param $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->_size = (float)$size;
        return $this;
    }

    /**
     * 上传文件的入口
     * @param $uri
     * @return bool
     */
    public function upload($uri)
    {
        if (!$this->_allowSize()) return false;

        $path = self::dir() . $uri;
        self::_createDir($path);
        $res = move_uploaded_file($this->_file['tmp_name'], $path);
        if ($res) {
            $res = $uri;
        } else {
            $this->_error = self::ERROR_SYSTEM;
        }
        return $res;
    }

    /**
     * 获取上传文件存放的目录
     * @return string
     */
    public static function dir()
    {
        return APPLICATION_PATH . '/public/r/';
    }

    /**
     * 视频存放目录
     * @return string
     */
    /*public static function dirVideo()
    {
        return self::dir() . 'video/';
    }*/

    /**
     * 图片存放目录
     * @return string
     */
    /*public static function dirImage()
    {
        return self::dir() . 'image/';
    }*/

    /**
     * 上传视频
     * @return bool
     */
    public function uploadVideo()
    {
        if (!$this->_allow_suffix) {
            $this->setAllowSuffix(['mp4']);
        }
        $suffix = '';
        if (!$this->_allowSuffix($suffix)) return false;

        $uri = 'video/' . self::_getRandName() . '.' . $suffix;
        return self::upload($uri);
    }

    /**
     * 上传图片
     * @return bool
     */
    public function uploadImage()
    {
        if (!$this->_allow_suffix) {
            $this->setAllowSuffix(['jpg']);
        }
        $suffix = '';
        if (!$this->_allowSuffix($suffix)) return false;

        $uri = 'image/' . self::_getRandName() . '.' . $suffix;
        return self::upload($uri);
    }

    /**
     * 获取随机名字
     * @return string
     */
    private static function _getRandName()
    {
        $time = time();
        return date('y/m/d/', $time) . $time . '_' . rand(100, 999);
    }

    /**
     * 后缀限制检查
     * @param string $suffix
     * @return bool
     */
    private function _allowSuffix(&$suffix = '')
    {
        $suffix = $this->getSuffix();
        $res = in_array($suffix, $this->_allow_suffix);
        if (!$res) {
            $this->_error = self::ERROR_SUFFIX;
        }
        return $res;
    }

    /**
     * @return bool
     */
    private function _allowSize()
    {
        $size = $this->getSize();
        if (!$size || $size / (1024 * 1024) > $this->_size) {
            $this->_error = self::ERROR_SIZE;
            return false;
        }
        return true;
    }

    /**
     * 获取后缀
     * @return null|string
     */
    public function getSuffix()
    {
        $file_name = $this->getName();
        $suffix = null;
        if ($file_name) {
            $suffix = substr($file_name, strrpos($file_name, '.') + 1);
        }

        return $suffix;
    }

    /**
     * 获取文件名
     * @return null|string
     */
    public function getName()
    {
        return $this->_get('name');
    }

    /**
     * 获取文件大小
     * @return int
     */
    public function getSize()
    {
        return (int)$this->_get('size');
    }

    /**
     * 获取文件中的信息
     * @param $field
     * @return null
     */
    private function _get($field)
    {
        return isset($this->_file[$field]) ? $this->_file[$field] : null;
    }

    /**
     * 获取错误信息
     * @return array
     */
    public function getError()
    {
        list($code, $msg) = explode(':', $this->_error);
        return ['code' => $code, 'msg' => $msg];
    }

    /**
     * 生成目录
     * @param $path
     */
    private static function _createDir($path)
    {
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    }


}
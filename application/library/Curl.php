<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/3/20
 * Time: 18:15
 */
class Curl
{
    private $_ch;
    private $_url = '';
    private $_opts = [CURLOPT_RETURNTRANSFER => 1];
    private $_get_params = [];
    private $_post_params = [];

    private $_debug = false;
    private $_clear = false;

    use Instance;

    /**
     * 设置url
     * @param $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->_url = $url;
        return $this;
    }

    /**
     * 设置curl选项
     * @param string|array $option curl选项或者选项数组
     * @param null $value 选项值
     * @return $this
     */
    public function setOpt($option, $value = null)
    {
        if (is_array($option)) {
            foreach ($option as $opt => $val) {
                $this->_opts[$opt] = $val;
            }
        } else {
            $this->_opts[$option] = $value;
        }
        return $this;
    }

    /**
     * 设置get参数
     * @param string|array $key
     * @param null $value
     * @return $this
     */
    public function setGetParam($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->_get_params[$k] = $v;
            }
        } else {
            $this->_get_params[$key] = $value;
        }
        return $this;
    }

    /**
     * 设置post参数
     * @param string|array $key
     * @param null $value
     * @return $this
     */
    public function setPostParam($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->_post_params[$k] = $v;
            }
        } else {
            $this->_post_params[$key] = $value;
        }
        return $this;
    }

    /**
     * 开启debug输出curl信息
     * @return $this
     */
    public function debug()
    {
        $this->_debug = true;
        return $this;
    }

    /**
     * @return $this
     */
    public function setClear()
    {
        $this->_clear = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getGetParams()
    {
        return $this->_get_params;
    }

    /**
     * @return array
     */
    public function getPostParams()
    {
        return $this->_post_params;
    }

    /**
     * 获取返回结果
     * @param $decode
     * @return mixed
     */
    public function getResult($decode = false)
    {
        $this->_makeCurl();
        $result = curl_exec($this->_ch);
        curl_close($this->_ch);
        if ($decode) {
            $result = @json_decode($result, 1);
        }
        $this->_clear();
        return $result;
    }

    /**
     * 获取返回http状态码
     * @return mixed
     */
    public function getHttpCode()
    {
        $this->_makeCurl();
        curl_exec($this->_ch);
        $code = curl_getinfo($this->_ch, CURLINFO_HTTP_CODE);
        curl_close($this->_ch);
        $this->_clear();
        return $code;
    }

    /**
     * 获取url
     * @param bool $needEcho 是否直接输出url
     * @return string
     */
    public function getUrl($needEcho = false)
    {
        $this->_makeCurl();
        if ($needEcho) {
            echo $this->_url;
        }
        return $this->_url;
    }

    /**
     * 获取结果并输出
     * @param bool $to_array
     * @throws Exception
     */
    public function dumpResult($to_array = true)
    {
        $result = $this->getResult(false);
        if ($to_array) {
            if ($result_arr = json_decode($result, 1)) {
                //__d($result_arr, 'result');
            } else {
                //__d($result, 'result (error)');
            }
            return;
        }
        //__d($result, 'result');
    }

    /**
     * 生成curl
     * @throws Exception
     */
    private function _makeCurl()
    {
        $this->_makeUrl();
        $this->_makePost();

//        if (!$this->_ch) {
        $this->_ch = curl_init();
        curl_setopt_array($this->_ch, $this->_opts);


        $this->_debugger();
    }

    /**
     * debug输出
     */
    private function _debugger()
    {
        if ($this->_debug) {
            //__d($this->_opts, 'curl opts');
            //__d($this->_post_params, 'curl post');
            //__d($this->_get_params, 'curl get');
        }
    }

    /**
     * 检查是否有get参数并生成url设置到curl中
     * @throws Exception
     */
    private function _makeUrl()
    {
        if (!$this->_url) {
            throw new Exception('Url is empty!');
        }
        if ($this->_get_params) {
            $this->_url = $this->_url . '?' . http_build_query($this->_get_params);
        }
        $this->setOpt(CURLOPT_URL, $this->_url);
    }

    /**
     * 检查是否有post参数并设置curl post数据
     */
    private function _makePost()
    {
        if ($this->_post_params) {
            $data = http_build_query($this->_post_params);
            $this->setOpt([
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => $data
            ]);
        }
    }

    private function _clear()
    {
        if ($this->_clear) {
            $this->_url = '';
            $this->_opts = [CURLOPT_RETURNTRANSFER => 1];
            $this->_get_params = [];
            $this->_post_params = [];
        }
    }


}

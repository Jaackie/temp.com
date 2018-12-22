<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/11/22
 * Time: 15:29
 */
class base_controllerApi extends base_controller
{
    protected $checkSign = true;

    public function init()
    {
        parent::init();
        $this->__checkSign($_POST);
    }

    /**
     * 检查签名
     * @param array $data
     * @param null $token
     */
    protected function __checkSign($data, $token = null)
    {
        if ($this->checkSign && !Sign::checkSign($data, $token)) {
            $this->__error(res_msg::E_SIGN_ERROR);
        }
    }


}
<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/11/22
 * Time: 15:31
 */
class IndexController extends base_controllerApi
{

    public function indexAction()
    {
        $data = config_indexModel::instance()->initByKey()->getValue();
        $this->__result($data);
    }

}
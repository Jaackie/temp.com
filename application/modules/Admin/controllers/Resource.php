<?php
/**
 * User: Jaackie <ljq4@meitu.com>
 * Date: 2018/3/21
 */

class ResourceController extends base_controllerAdmin
{
    public function indexAction()
    {
        $page = $this->getPage();
        $list = imgModel::instance()->getAll($page, 100);
        $this->__assign('data', $list)->__show();
    }

    /**
     * 全局上传图片
     */
    public function uploadImageAction()
    {
        if (!is_ajax()) {
            $this->__display();
        }
        $uploader = Uploader::instance()->setFile($_FILES["file"])->setAllowSuffix(['jpg', 'png']);
        $uri = $uploader->uploadImage();
        $res = imgModel::instance()->setUrl($uri)->add();
        if (!$uri) {
            $this->__errorAjax($uploader->getError()['msg']);
        }
        $this->__successAjax(['uri' => $uri, 'res' => $res]);
    }

}
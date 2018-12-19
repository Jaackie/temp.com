<?php
/**
 * User: Jaackie <ljq4@meitu.com>
 * Date: 2018/3/23
 */

class TagController extends base_controllerAdmin
{
    /**
     * 列表页面
     */
    public function indexAction()
    {
        $page = $this->getPage();
        $list = tagModel::instance()->getAll($page, 200);

        $this->__assign('data', $list)->__show();
    }

    /**
     * 添加
     * @throws Exception
     */
    public function addAction()
    {
        if (!is_ajax()) {
            $this->__display();
        }
        $tag = tagModel::instance();

        $tag_name = $this->requirePost('tag_name');
        $cover = $this->post('cover', '');
        $intro = $this->post('intro', '');

        $res = $tag->setTagName($tag_name)->setCover($cover)->setIntro($intro)->add();
        if (!$res) {
            $this->__errorAjax('添加失败');
        }

        $this->__successAjax();
    }

    /**
     * 编辑
     * @throws Exception
     */
    public function editAction()
    {
        $tag_id = $this->input('tag_id');
        $tag = tagModel::instance($tag_id)->init();
        if (!$tag->isInit()) {
            $this->__e('分类不存在');
        }
        if (!is_ajax()) {
            $this->__assign('info', $tag->info())->__display();
        }

        $tag_name = $this->requirePost('tag_name');
        $cover = $this->post('cover', '');
        $intro = $this->post('intro', '');


        $res = $tag->setTagName($tag_name)->setCover($cover)->setIntro($intro)->updateInfo();
        if (!$res) {
            $this->__errorAjax('保存失败');
        }

        $this->__successAjax();
    }

}
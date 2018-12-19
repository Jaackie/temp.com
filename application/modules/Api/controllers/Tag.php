<?php
/**
 * User: Jaackie <ljq4@meitu.com>
 * Date: 2018/3/23
 */

class TagController extends base_controllerApi
{
    /**
     * 分类信息
     */
    public function infoAction()
    {
        $tag_id = $this->requirePost('id');
        $tag = tagModel::instance($tag_id)->init();
        if (!$tag->isInit()) {
            $this->__error(res_msg::TAG_NONE);
        }
        $info = $tag->info();
        rr($info, 'cover', 1);
        $this->__result(['tag' => $info]);
    }

    /**
     * 分类下的视频列表
     */
    public function videoListAction()
    {
        $tag_id = $this->requirePost('id');
        $page = $this->getPage();
        if ($page == 1) {
            $tag = tagModel::instance($tag_id)->init();
            if (!$tag->isInit()) {
                $this->__error(res_msg::TAG_NONE);
            }
            $info = $tag->info();
            rr($info, 'cover', 1);
        } else {
            $info = [];
        }
        $num = 10;
        $list = tagVideoModel::instance()->setTagId($tag_id)->getListByTagId($page, $num, true);
        $this->__result(['list' => $list, 'info' => $info]);
    }

}
<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/11/22
 * Time: 15:31
 */
class VideoController extends base_controllerAdmin
{
    /**
     * 列表页面
     */
    public function indexAction()
    {
        $page = $this->getPage();

        $video_list = videoModel::instance()->getAll($page, 10);
        tagVideoModel::instance()->videoListWithTagList($video_list, true);

        $this->__assign('data', $video_list)
            ->__show();
    }

    /**
     * 上传/编辑页面
     * @throws Exception
     */
    public function uploadAction()
    {
        $video_id = $this->get('video_id');
        if ($video_id) {
            $video = videoModel::instance($video_id)->init();
            if (!$video->isInit()) {
                $this->__errorAjax('该视频不存');
            }
            $video_info = $video->info();
            tagVideoModel::instance()->videoWithTagList($video_info, true);
            $tag_name_arr = [];
            foreach ($video_info['tags'] as $tag) {
                $tag_name_arr[] = $tag['tag_name'];
            }
            $video_info['tag_str'] = implode(',', $tag_name_arr);

            $this->__assign('video', $video_info)
                ->__assign('video_id', $video_id);
        }
        $this->__show();
    }

    /**
     * 上传视频并生成Video记录
     */
    public function addAction()
    {
        $uploader = Uploader::instance()->setFile($_FILES["file"]);
        $uri = $uploader->uploadVideo();
        if (!$uri) $this->__errorAjax($uploader->getError()['msg']);

        $video_id = videoModel::instance()->setUrl($uri)->add();
        if (!$video_id) {
            $this->__errorAjax('视频生成失败');
        }
        $this->__successAjax(['video_id' => $video_id]);
    }

    /**
     * 保存视频信息
     * @throws Exception
     */
    public function saveAction()
    {
        $video_id = $this->requirePost('video_id');
        $video = videoModel::instance($video_id)->init();
        if (!$video->isInit()) {
            $this->__errorAjax('该视频不存在');
        }

        if ($this->post('cover')) { //保存封面图
            $uploader = Uploader::instance()->setFile($_FILES["file"])->setAllowSuffix(['jpg', 'png']);
            $uri = $uploader->uploadImage();
            if (!$uri) $this->__errorAjax($uploader->getError()['msg']);

            imgModel::instance()->setUrl($uri)->add();
            $res_info = $video->setCover($uri)->saveInfo();
        } else { //保存其它东西
            $intro = $this->post('intro', '');
            $time_length = $this->post('time_length', '');
            $tag_str = $this->post('tag_str');

            $res_info = $video->setIntro($intro)->setTimeLength($time_length)->saveInfo();
            if ($tag_str) {
                tagVideoModel::instance()->setVideoId($video_id)->saveByTagStr($tag_str);
            }
        }
        if (!$res_info) {
            $this->__errorAjax('保存信息失败');
        }

        $this->__successAjax();
    }

    /**
     * 删除视频
     */
    public function delAction()
    {
        $video_id = $this->requirePost('video_id');
        $video = videoModel::instance($video_id)->init();
        if (!$video->isInit()) {
            $this->__errorAjax('该视频不存在');
        }
        $res = $video->deleteVideo();
        if (!$res) {
            $this->__errorAjax('删除失败');
        }
        $this->__successAjax();
    }

}
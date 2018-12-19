<?php
/**
 * User: Jaackie <ljq4@meitu.com>
 * Date: 2018/3/23
 */

class VideoController extends base_controllerApi
{
    public function infoAction()
    {
        $video_id = $this->requirePost('id');

        $video = videoModel::instance($video_id)->init();
        if (!$video->isInit()) {
            $this->__error(res_msg::VIDEO_NONE);
        }
        $video_info = $video->info();
        $video->setViewNum(1)->saveViewNum();
        tagVideoModel::instance()->videoWithTagList($video_info, true);
        rr($video_info, 'url,cover', 1);
        $this->__result(['video' => $video_info]);
    }

}
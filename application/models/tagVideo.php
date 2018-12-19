<?php
/**
 * User: Jaackie
 * Date: 2018/3/13
 */

class tagVideoModel extends base_model
{
    public $id;
    public $tag_id;
    public $video_id;
    public $create_time;

    protected $__table_name = 'tag_video';
    protected $__primary_key = 'id';

    public function __construct($id = 0)
    {
        $this->setId($id);
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = (int)$id;
        return $this;
    }

    /**
     * @param mixed $tag_id
     * @return $this
     */
    public function setTagId($tag_id)
    {
        $this->tag_id = (int)$tag_id;
        return $this;
    }

    /**
     * @param mixed $video_id
     * @return $this
     */
    public function setVideoId($video_id)
    {
        $this->video_id = (int)$video_id;
        return $this;
    }

    /**
     * @return $this
     */
    private function _setCreateTime()
    {
        $this->create_time = time();
        return $this;
    }

    /**
     * @return array
     */
    public function findByTagIdVideoId()
    {
        return $this->find('tag_id,video_id');
    }


    /**
     * @return bool|int
     */
    private function _insert()
    {
        if (!$this->tag_id || !$this->video_id) return false;

        $this->_setCreateTime();
        $res = $this->table()->insert([
            'tag_id' => $this->tag_id,
            'video_id' => $this->video_id,
            'create_time' => $this->create_time
        ]);
        if ($res) {
            tagModel::instance()->setTagId($this->tag_id)->updateVideoNum(1, '+');
        }
        return $res;
    }

    /**
     * @return bool|int
     */
    private function _del()
    {
        $res = $this->delete();
        if ($res) {
            tagModel::instance()->setTagId($this->tag_id)->updateVideoNum(1, '-');
        }
        return $res;
    }


    /**
     * 通过tag字符串保存的
     * @param $str
     * @return bool|int
     */
    public function saveByTagStr($str)
    {
        if (!$this->video_id) return 0;

        $res = 0;

        $tag_video_list = $this->getListByVideoId(true);    //原有的关联


        $tag_id_arr = [];   //新的关联
        if ($str) {
            $tag_name_arr = explode(',', str_replace('，', ',', $str));
            $tag = tagModel::instance();
            foreach ($tag_name_arr as $tag_name) {
                $tag_id_arr[] = $tag->setTagName($tag_name)->add();
            }
        }

        if ($tag_id_arr) {
            foreach ($tag_id_arr as $tag_id) {
                if (!isset($tag_video_list[$tag_id])) {
                    $res_add = $this->setTagId($tag_id)->_insert(); //增加的
                    !$res_add ?: $res++;
                }
            }
        }

        if ($tag_video_list) {
            foreach ($tag_video_list as $tag_id => $tag_video) {
                if (!in_array($tag_id, $tag_id_arr)) {
                    $res_del = $this->setId($tag_video['id'])->setTagId($tag_id)->_del();   //删除的
                    !$res_del ?: $res++;
                }
            }
        }

        return $res;
    }

    /**
     * @param bool $assoc
     * @return array
     */
    public function getListByVideoId($assoc = false)
    {
        if (!$this->video_id) return [];

        $list = $this->table()->whereField('video_id', $this->video_id)->get();
        if ($assoc) {
            tool_arr::assocByKey($list, 'tag_id');
        }
        return $list;
    }

    /**
     * 给视频列表添加上tag信息
     * @param $videoList
     * @param bool $withTagInfo
     */
    public function videoListWithTagList(&$videoList, $withTagInfo = false)
    {
        if (!$videoList) return;

        $video_id_arr = tool_arr::getKeyArrFromArr($videoList, 'video_id');
        $list = $this->table()->whereIn('video_id', $video_id_arr)->get();
        if ($withTagInfo) {
            tagModel::instance()->withTagInfo($list);
        }
        tool_arr::mergeArrMulti($videoList, $list, 'video_id', 'tags');
    }

    /**
     * 给单个视频添加上tag信息
     * @param $videoInfo
     * @param bool $withTagInfo
     */
    public function videoWithTagList(&$videoInfo, $withTagInfo = true)
    {
        if (!$videoInfo) return;

        $video_list = [$videoInfo];
        $this->videoListWithTagList($video_list, $withTagInfo);
        $videoInfo = $video_list[0];
    }

    /**
     * 分类列表合并上视频列表信息
     * @param array $tagList 分类列表
     * @param int $num 视频数量
     * @param bool $withVideoInfo 是否携带视频信息
     */
    public function tagListWithVideoList(&$tagList, $num = 6, $withVideoInfo = true)
    {
        if (!$tagList) return;
        $tag_id_arr = tool_arr::getId($tagList, 'tag_id');
        if (!$tag_id_arr) return;

        $video_list = $this->table()->whereIn('tag_id', $tag_id_arr)
            ->order($this->__primary_key, false)
            ->limit($num * 30)/*30倍的数量查找*/
            ->get();
        if ($withVideoInfo) {
            videoModel::instance()->withVideoInfo($video_list);
        }

        tool_arr::mergeArrMulti($tagList, $video_list, 'tag_id', 'video_list', $num);
    }

    /**
     * 通过tag_id获取视频列表
     * @param int $page
     * @param int $num
     * @param bool $withVideoInfo
     * @return array
     */
    public function getListByTagId($page = 1, $num = 10, $withVideoInfo = true)
    {
        if (!$this->tag_id) return [];

        $list = $this->table()->whereField('tag_id', $this->tag_id)
            ->page($num, $page)
            ->order($this->__primary_key, false)
            ->get();
        if ($withVideoInfo) {
            videoModel::instance()->withVideoInfo($list);
        }
        return $list;
    }


}
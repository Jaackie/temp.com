<?php
/**
 * User: Jaackie
 * Date: 2018/3/13
 */

class tagModel extends base_model
{
    public $tag_id;
    public $tag_name = '';
    public $cover = '';
    public $video_num = 0;
    public $intro = '';
    public $create_time = 0;
    public $update_time = 0;

    protected $__table_name = 'tag';
    protected $__primary_key = 'tag_id';

    public function __construct($tagId = 0)
    {
        $this->setTagId($tagId);
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
     * @param string $tag_name
     * @return $this
     */
    public function setTagName($tag_name)
    {
        $this->tag_name = trim($tag_name);
        return $this;
    }

    /**
     * @param string $cover
     * @return $this
     */
    public function setCover($cover)
    {
        $this->cover = $cover;
        return $this;
    }


    /**
     * @param string $intro
     * @return $this
     */
    public function setIntro($intro)
    {
        $this->intro = $intro;
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
     * @return $this
     */
    private function _setUpdateTime()
    {
        $this->update_time = time();
        return $this;
    }

    /**
     * @return array
     */
    public function findByName()
    {
        return $this->find('tag_name');
    }

    /**
     * @return bool|int
     */
    private function _insert()
    {
        if (!$this->tag_name) return false;

        $this->_setCreateTime()->_setUpdateTime();
        return $this->table()->insert([
            'tag_name' => $this->tag_name,
            'cover' => $this->cover,
            'intro' => $this->intro,
            'create_time' => $this->create_time,
            'update_time' => $this->update_time
        ], true);
    }

    /**
     * @return bool|int
     */
    public function add()
    {
        if (!$this->tag_name) return false;

        if ($find = $this->findByName()) {
            return $find['tag_id'];
        }

        return $this->_insert();
    }

    /**
     * 更新视频数量
     * @param $num
     * @param string $opt
     * @return bool|int
     */
    public function updateVideoNum($num, $opt = '+')
    {
        if (!$this->tag_id) return false;

        return $this->table()->set('video_num', $num, $opt)->whereField('tag_id', $this->tag_id)->update();
    }

    /**
     * 更新信息
     * @return bool|int
     */
    public function updateInfo()
    {
        $this->_setUpdateTime();
        return $this->save('tag_name,cover,intro,update_time');
    }

    /**
     * 一组含tag_id的列表增加tag信息
     * @param $tagList
     */
    public function withTagInfo(&$tagList)
    {
        if (!$tagList) return;

        $tag_id_arr = tool_arr::getKeyArrFromArr($tagList, 'tag_id');
        $list = $this->table()->whereIn('tag_id', $tag_id_arr)->get();

        tool_arr::mergeArr($tagList, $list, 'tag_id');
    }


}
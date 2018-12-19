<?php
/**
 * 记录上传的图片资源
 * User: Jaackie <ljq4@meitu.com>
 * Date: 2018/3/21
 */

class imgModel extends base_model
{
    public $id;
    public $url;

    protected $__table_name = 'img';
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
     * @param mixed $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     *
     * @return bool|int
     */
    public function add()
    {
        if (!$this->url) return false;

        return $this->table()->insert(['url' => $this->url]);
    }

}
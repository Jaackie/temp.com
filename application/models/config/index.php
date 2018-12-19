<?php
/**
 * User: Jaackie <ljq4@meitu.com>
 * Date: 2018/3/20
 */

class config_indexModel extends configModel
{
    public $key = 'index';
    protected $__is_arr = true;

    const TAG_VIDEO_NUM = 6; //每个分类下6个视频
    const TAG_EXPIRES = 30; //分类下的视频每30分钟刷新一次

    public function getValue()
    {
        $value = parent::getValue();
        $valueObj = tool_arr::getInstance($value);
        $nav = $valueObj->get('nav', []);
        $hot = $valueObj->get('hot', []);
        $tag = $valueObj->get('tag', []);
        $last = $valueObj->get('last', 0);
        $return = [
            'nav' => $nav,
            'hot' => $hot,
            'tag' => $tag,
            'last' => $last,
        ];
        if ($last && time() - $last > self::TAG_EXPIRES * 60) {
            tagVideoModel::instance()->tagListWithVideoList($tag, self::TAG_VIDEO_NUM, true);
            $return['tag'] = $tag;
            $return['last'] = time();
            $this->setValue($return)->update();
        }
        return $return;
    }

}
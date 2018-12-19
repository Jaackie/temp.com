<?php
/**
 * User: Jaackie <ljq4@meitu.com>
 * Date: 2018/3/20
 */

class ConfigController extends base_controllerAdmin
{
    public function indexAction()
    {
        $page = $this->getPage();
        $config_list = configModel::instance()->getAll($page, 50);
        $this->__assign('data', $config_list);
        $this->__show();
    }

    public function addAction()
    {
        if (!is_ajax()) $this->__display();

        $key = $this->requirePost('key');
        $intro = $this->post('intro', '');
        $value = $this->requirePost('value');

        $config = configModel::instance()->setKey($key)->initByKey();
        if ($config->isInit()) {
            $this->__errorAjax('该key已存在');
        }

        $res = $config->setIntro($intro)->setValue($value)->add();
        if (!$res) {
            $this->__errorAjax('添加失败');
        }

        $this->__successAjax();
    }

    public function editAction()
    {
        $config = configModel::instance();
        if (!is_ajax()) {
            $id = $this->get('id');
            $row = $this->get('row', 0);
            $config->setId($id)->init();
            if (!$config->isInit()) {
                $this->__errorAjax('该配置不存在');
            }
            $method_name = '_edit_' . $config->key;

            $this->__assign('info', $config->info());
            if (!$row && method_exists($this, $method_name)) {
                $this->$method_name($config);
            }
            $this->__assign('row', $row)->__display();
        }

        $id = $this->requirePost('id');
        $intro = $this->post('intro', '');
        $value = $this->post('value', '');
        $row = $this->post('row', 0);

        $config->setId($id)->init();
        if (!$config->isInit()) {
            $this->__errorAjax('该配置不存在');
        }

        $method_name = '_edit_' . $config->key;
        if (!$row && method_exists($this, $method_name)) {
            $value = $this->$method_name($config);
        }

        if ($config->value == $value && $config->intro == $intro) {
            $this->__successAjax();
        }
        $res = $config->setIntro($intro)->setValue($value)->update();
        if (!$res) {
            $this->__errorAjax('保存失败');
        }
        $this->__successAjax();
    }

    private function _edit_index(configModel $config)
    {
        if (!is_ajax()) {
            $value = $config->isArr()->getValue();
            $value_obj = tool_arr::getInstance($value);
            $nav_arr = $value_obj->get('nav', []);
            $nav = '';
            if ($nav_arr) {
                foreach ($nav_arr as $item) {
                    $item_val_arr = [];
                    foreach ($item as $key => $val) {
                        $item_val_arr[] = $val;
                    }
                    $nav .= implode(',', $item_val_arr) . "\n";
                }
            }
            $hot_arr = $value_obj->get('hot', []);
            $hot = '';
            if ($hot_arr) {
                $hot = $value_obj::getKeyArrFromArr($hot_arr, 'video_id', true);
            }
            $tag_arr = $value_obj->get('tag', []);
            $tag = '';
            if ($tag_arr) {
                $tag = $value_obj::getKeyArrFromArr($tag_arr, 'tag_id', true);
            }

            $this->__assign('nav', $nav)->__assign('hot', $hot)->__assign('tag', $tag)
                ->__display('editIndex');
        }

        $nav = $this->requirePost('nav');
        $hot = $this->requirePost('hot');
        $tag = $this->requirePost('tag');
        $nav_arr = [];
        foreach (explode("\n", $nav) as $nav_item) {
            if ($nav_item) {
                list($img, $url) = explode(',', $nav_item);
                if ($img && $url) {
                    $nav_arr[] = ['img' => $img, 'url' => $url];
                }
            }
        }
        if (!$nav_arr) $this->__errorAjax('轮播图配置错误');
        $hot_arr = videoModel::instance()->getMulti(explode(',', $hot));
        if (!$hot_arr) $this->__errorAjax('热门配置错误');
        $tag_arr = tagModel::instance()->getMulti(explode(',', $tag));
        if (!$tag_arr) $this->__errorAjax('分类配置错误');
        tagVideoModel::instance()->tagListWithVideoList($tag_arr, config_indexModel::TAG_VIDEO_NUM, true);

        rr($hot_arr, 'url,cover');
        rr($nav_arr, 'img');

        return ['nav' => $nav_arr, 'hot' => $hot_arr, 'tag' => $tag_arr, 'last' => time()];
    }

}
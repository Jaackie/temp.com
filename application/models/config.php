<?php
/**
 * User: Jaackie
 * Date: 2018/3/19
 */

class configModel extends base_model
{
    public $id;
    public $key;
    public $intro = '';
    public $value = '';

    protected $__table_name = 'config';
    protected $__primary_key = 'id';

    protected $__is_arr = true;
    protected $__formatter = [];

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
     * @param mixed $key
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;
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
     * @param string|array $value
     * @return $this
     */
    public function setValue($value)
    {
        if (is_array($value)) {
            if ($this->__formatter) {
                $this->__format($value);
            }
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        $this->value = $value;
        return $this;
    }

    /**
     * 设置是否数组
     * @param bool $isArr
     * @return $this
     */
    public function isArr($isArr = true)
    {
        $this->__is_arr = $isArr;
        return $this;
    }

    /**
     * @return $this
     */
    public function initByKey()
    {
        return $this->init('key');
    }

    /**
     * @return bool|int
     */
    public function add()
    {
        if (!$this->key) return false;

        return $this->table()->insert([
            'key' => $this->key,
            'intro' => $this->intro,
            'value' => $this->value,
        ]);
    }

    /**
     * @return bool|int
     */
    public function update()
    {
        return $this->save('intro,value');
    }

    /**
     * @return array|string
     */
    public function getValue()
    {
        if ($this->__is_arr) {
            return $this->value ? json_decode($this->value, true) : [];
        }
        return $this->value;
    }

    /**
     * 格式化数据
     * @param $value
     * @param array $formatter
     */
    protected function __format(&$value, $formatter = null)
    {
        /*if (is_null($formatter)) $formatter = $this->__formatter;
        if (!$formatter || !is_array($formatter)) return;

        foreach ($formatter as $key => $val) {
            if (!isset($value[$key])) {
                $value[$key] = $val;
                continue;
            }
            if (is_array($val)) {
                $this->__format($value[$key], $val);
            }
        }

        foreach ($value as $k => $v) {
            if (!isset($formatter[$k])) {
                unset($value[$k]);
            }
        }*/
    }


}
<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/3/23
 * Time: 10:08
 */
class tool_arr
{
    private $_arr = [];

    public function __construct($arr = [])
    {
        $this->_arr = $arr;
    }

    public static function getInstance($arr = [])
    {
        return new self($arr);
    }

    /**
     * 获取键值
     * @param $index
     * @param null $default
     * @return null
     */
    public function get($index, $default = null)
    {
        return isset($this->_arr[$index]) ? $this->_arr[$index] : $default;
    }

    /**
     * 获取bool值
     * @param $index
     * @param bool $default
     * @return bool
     */
    public function getBool($index, $default = true)
    {
        $res = $this->get($index, $default);
        if ($res == 'false') {
            return false;
        }
        return $res || $res == 'true' ? true : false;
    }

    /**
     * 获取必要键值, 否则抛出异常
     * @param $index
     * @param null $exp_msg
     * @return null
     * @throws Exception
     */
    public function getRequired($index, $exp_msg = null)
    {
        $default = null;
        $res = $this->get($index, $default);
        if ($res === $default) {
            $exp_msg = $exp_msg ? $exp_msg : $index . '不能为空';
            throw new Exception($exp_msg);
        }
        return $res;
    }

    /**
     * 将一组导航栏配置中的opt选项中类似'[o1]'提取到配置的索引排序（解决配置排序问题）
     * @param $itemsArr
     * @param string $field 存在排序规则的字段名
     */
    public static function orderOption(&$itemsArr, $field = 'opt')
    {
        $items_tmp = [];
        $start_index = 1000;
        foreach ($itemsArr as $i => $item) {
            if (isset($item[$field]) && preg_match('/\[o(\d+)\]/', $item[$field], $res)) {
                $item[$field] = preg_replace('/\[o(\d+)\]/', '', $item[$field]);
                $order_key = intval($res[1]);
                if (!array_key_exists($order_key, $items_tmp)) {
                    $items_tmp[$order_key] = $item;
                } else {
                    $items_tmp[] = $item;
                }
            } else {
                $items_tmp[$start_index++] = $item;
            }
        }

        ksort($items_tmp);
        $items_tmp = array_values($items_tmp);
        $itemsArr = $items_tmp;
    }

    /**
     * 将二维数组中，第二维键名对应值取出成数组或以‘,’分隔的字符串
     * @param $arr
     * @param $keyName
     * @param bool $isToString
     * @param string $separator 分隔符
     * @return array|string
     */
    public static function getKeyArrFromArr($arr, $keyName, $isToString = false, $separator = ',')
    {
        if ($arr) {
            $key_arr = [];
            foreach ($arr as $k => $v) {
                if (isset($v[$keyName])) {
                    $key_arr[$k] = $v[$keyName];
                }
            }

            if ($isToString) {
                return implode($separator, $key_arr);
            } else {
                return $key_arr;
            }

        } else {

            if ($isToString) {
                return '';
            } else {
                return [];
            }

        }
    }

    /**
     * 获取二维数组中的id信息，并校检（删除0id）后返回id数组或者用户查询的id字符串
     * @param $arr
     * @param $idName
     * @param bool $toString
     * @return array|string
     */
    public static function getId($arr, $idName, $toString = false)
    {
        $id_arr = self::getKeyArrFromArr($arr, $idName, false);

        $toString = $toString ? ',' : false;
        $id_str = self::checkIdArr($id_arr, $toString, true);
        if ($toString) {
            return $id_str;
        } else {
            return $id_arr;
        }
    }

    /**
     * 检查用于数据库查询的id数组
     * @param $idArr
     * @param bool $unset0
     * @param bool $toStringGlue
     * @return string
     */
    public static function checkIdArr(&$idArr, $toStringGlue = false, $unset0 = true)
    {
        if (!$idArr) return '';

        $idArr = array_unique($idArr);
        if ($unset0) {
            foreach ($idArr as $i => $id) {
                if (!$id) {
                    unset($idArr[$i]);
                }
            }
        }
        if ($toStringGlue) {
            return implode($toStringGlue, $idArr);
        }
    }

    /**
     * 通过一个键名的值来合并两个二维以上的数组
     * @param $arr1
     * @param $arr2
     * @param $keyName
     * @param array $defaultArr 默认合并数组,如果为null，则删除掉该元素
     */
    public static function mergeArr(&$arr1, $arr2, $keyName, $defaultArr = [])
    {
        if (!$arr1) return;

        self::assocByKey($arr2, $keyName);

        foreach ($arr1 as $i => $val) {
            if (array_key_exists($val[$keyName], $arr2)) {
                $arr1[$i] = array_merge($val, $arr2[$val[$keyName]]);
            } elseif ($defaultArr !== null) {
                $arr1[$i] = array_merge($val, $defaultArr);
            } else {
                unset($arr1[$i]);
            }
        }
        if ($defaultArr === null) {
            self::getValues($arr1);
        }
    }

    /**
     * 通过一个键名的值来合并两个二维以上的数组（与上个方法区别在与合并在原数组中的新键名中）
     * @param $arr1
     * @param $arr2
     * @param $keyName
     * @param $newKey
     * @param array $defaultArr 默认合并数组
     */
    public static function mergeArrNewKey(&$arr1, $arr2, $keyName, $newKey, $defaultArr = null)
    {
        if (!$arr1) return;

        self::assocByKey($arr2, $keyName);

        foreach ($arr1 as $i => $val) {
            if (array_key_exists($val[$keyName], $arr2)) {
                $arr1[$i][$newKey] = $arr2[$val[$keyName]];
            } elseif ($defaultArr !== null) {
                $arr1[$i][$newKey] = $defaultArr;
            }
        }
    }

    /**
     * 将两个数组进行多维合并
     * @param array $arr1 原数组
     * @param array $arr2 带合并数组
     * @param string $keyName 对比键
     * @param string $newKeyName 合并后的键
     * @param int $number 合并数量
     * @param array $defaultArr
     */
    public static function mergeArrMulti(&$arr1, $arr2, $keyName, $newKeyName, $number = 0, $defaultArr = [])
    {
        if (!$arr1) return;

        foreach ($arr1 as $i => $val1) {
            $arr1[$i][$newKeyName] = $defaultArr;
            foreach ($arr2 as $val2) {
                if ($val1[$keyName] == $val2[$keyName]) {
                    $arr1[$i][$newKeyName][] = $val2;
                }
                if ($number && count($arr1[$i][$newKeyName]) == $number) {
                    break;
                }
            }
        }
    }


    /**
     * 将数组中的一个键值取出来当改数组的索引(如果$keyName中包含多个键名，则组成多个键名的联合索引)
     * @param $arr
     * @param $keyName
     */
    public static function assocByKey(&$arr, $keyName)
    {
        if (!$arr) return;

        $key_arr = explode(',', $keyName);

        $arr_tmp = [];
        foreach ($arr as $i => $v) {
            $key = '';
            foreach ($key_arr as $j => $k) {
                if (isset($v[$k])) {
                    if ($key) {
                        $key .= '-' . $v[$k];
                    } else {
                        $key = $v[$k];
                    }
                }
            }
            if ($key && !array_key_exists($key, $arr_tmp)) {
                $arr_tmp[$key] = $v;
            } else {
                $arr_tmp[] = $v;
            }
        }

        $arr = $arr_tmp;
    }

    /**
     * 抽取二维数组的随机若干数组
     * @param $array
     * @param $num
     * @return array
     */
    public static function getRandValue($array, $num)
    {
        if (count($array) <= $num) {
            self::getValues($array);
            return $array;
        }

        $keyArr = array_rand($array, $num);
        $arrTmp = [];
        if (!is_array($keyArr)) {
            $keyArr = [$keyArr];
        }
        foreach ($keyArr as $v) {
            $arrTmp[] = $array[$v];
        }
        return $arrTmp;
    }

    /**
     * 取出键值
     * @param $arr
     */
    public static function getValues(&$arr)
    {
        $arr = array_values($arr);
    }

    /**
     * 过滤掉键名为值的重复二维数组
     * @param $array
     * @param $keyName
     */
    public static function filterRepeat(&$array, $keyName)
    {
        if (!$array) return;
        $tmp = [];
        foreach ($array as $i => $v) {
            if (!in_array($v[$keyName], $tmp)) {
                $tmp[] = $v[$keyName];
            } else {
                unset($array[$i]);
            }
        }
        self::getValues($array);
    }


    /**
     * 查询出的二维数组，根据其中某个键值排序
     * @param array $orderArr 待排序的数组
     * @param array $keyArr 排序好的键值数组
     * @param string $keyName 排序的键名
     * @param bool $filterRepeat 删除重复的
     */
    public static function order(&$orderArr, $keyArr, $keyName, $filterRepeat = false)
    {
        if (!$orderArr || !$keyArr || !$keyName) return;

        $arr_tmp = [];
        self::assocByKey($orderArr, $keyName);
        foreach ($keyArr as $key) {
            if (isset($orderArr[$key])) {
                $arr_tmp[] = $orderArr[$key];
            }
        }

        if ($filterRepeat) {
            self::filterRepeat($arr_tmp, $keyName);
        }

        $orderArr = array_values($arr_tmp);
    }

    /**
     * 两个多维数组的键名比较
     * @param $arr1
     * @param $arr2
     * @return bool
     */
    public static function arrKeysCompare($arr1, $arr2)
    {
        if (!$arr1 || !$arr2) return false;
        $res = true;
        foreach ($arr1 as $k => $v) {
            if (isset($arr2[$k])) {
                if (is_array($v)) {
                    $res = self::arrKeysCompare($arr1[$k], $arr2[$k]);
                }
                if ($res) {
                    continue;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        return $res;
    }

    /**
     * 获取多维数组的键名
     * @param $array
     * @return array
     */
    public static function arrKeys($array)
    {
        $keys = [];
        if (!$array) return $keys;

        foreach ($array as $k => $v) {
            $keys[] = $k;
            if (is_array($v)) {
                $keys = array_merge($keys, self::arrKeys($v));
            }
        }
        return $keys;
    }

    /**
     * 二维数组按照某个值来排序
     * @param $array
     * @param $keyName
     */
    /**
     * @param $array
     * @param $keyName
     * @param bool $isIncrease
     */
    public static function arrOrder(&$array, $keyName, $isIncrease = true)
    {
        if (!$array) return;

        foreach ($array as $i => $v) {
            $name[$i] = $v[$keyName];
        }

        if ($isIncrease) {
            $sort = SORT_ASC;
        } else {
            $sort = SORT_DESC;
        }
        array_multisort($name, $sort, $array);
    }

    /**
     * 获取多个分类下固定数量的前几条数据
     * @param array $arr 多分类数组
     * @param string $key 分类的键名
     * @param int $number 数量
     */
    public static function arrGroupLimit(&$arr, $key, $number)
    {
        $id_count = [];
        foreach ($arr as $i => $tp) {
            $id = $tp[$key];
            if (!array_key_exists($id, $id_count)) {
                $id_count[$id] = 1;
            } elseif ($id_count[$id] < $number) {
                $id_count[$id]++;
            } else {
                unset($arr[$i]);
            }
        }
        array_values($arr);
    }

    /**
     * 根据二维数组下的某个键值重新组合一个以该键值为索引的数组
     * @param $arr
     * @param $key
     * @param bool $onlyValue
     * @return array
     */
    public static function groupArrByKey($arr, $key, $onlyValue = false)
    {
        if (!$arr) return [];

        $return = [];
        foreach ($arr as $v) {
            $return[$v[$key]][] = $v;
        }

        if ($onlyValue) {
            self::getValues($return);
        }
        return $return;
    }

    /**
     * 给一个不存在键名的数组的键值附上默认值
     * @param $arr
     * @param $name
     * @param $default
     * @return mixed
     */
    public static function arrValueDefault(&$arr, $name, $default)
    {
        $arr[$name] = isset($arr[$name]) && $arr[$name] ? $arr[$name] : $default;
    }

}
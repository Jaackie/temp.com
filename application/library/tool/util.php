<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/10/25
 * Time: 14:33
 */
class tool_util
{
    /**
     * 数组核查是否还有下一页,并截取所期望条数
     * @param array $arr 核查的数组
     * @param int $count 核查的数量
     * @return int 核查的结果，1有下页，0没有下页
     */
    public static function isMore(&$arr, $count = 10)
    {
        $more = 0;
        if (count($arr) == $count + 1) {
            $more = 1;
            $arr = array_slice($arr, 0, $count);
        }
        return $more;
    }

    /**
     * 上个方法的升级版，将原数组基础上再加一维数组，并且包含more的信息
     * @param array $arr
     * @param int $count
     */
    public static function isMoreRes(array &$arr, $count = 10)
    {
        $more = 0;
        if (count($arr) >= $count + 1) {
            $more = 1;
            $arr = array_slice($arr, 0, $count);
        }
        $arr = [
            'list' => $arr,
            'more' => $more
        ];
    }


    /**
     * 授权加密
     * @param $uid
     * @param $secret
     * @return string
     */
    public static function authEncode($uid, $secret)
    {
        $syn = self::_authMakeSyn([$uid], $secret, time());
        $syn = base64_encode(pack('H*', $syn));
        return $uid . '|' . $syn;
    }

    /**
     * 授权解密
     * @param $str
     * @param $secret
     * @return array|bool
     */
    public static function authDecode($str, $secret)
    {
        if (!preg_match('/^(\d+)\|(.+)$/', $str, $tmp)) {
            return false;
        }
        $uid = intval($tmp[1]);
        if ($uid <= 0) {
            return false;
        }
        $syn = unpack('H*', base64_decode($tmp[2]))[1];
        if (!preg_match('/^([0-9a-f]{22})(\d{10})$/i', $syn, $tmp)) {
            return false;
        }
        $time = $tmp[2];

        if ($syn === self::_authMakeSyn([$uid], $secret, $time)) {
            return ['uid' => $uid, 'time' => $time];
        }
        return false;
    }

    /**
     * 授权加密算法
     * @param $data
     * @param $secret
     * @param $time
     * @return string
     */
    private static function _authMakeSyn($data, $secret, $time)
    {
        $data[] = $secret;
        $data[] = $time;
        return substr(md5(implode('|', $data)), 10) . $time;
    }

    /**
     * 对已有数组进行分页
     * @param $list
     * @param $page
     * @param $count
     * @return array
     */
    public static function listPage($list, $page, $count)
    {
        if (!$list) return ['list' => [], 'more' => 0];

        $page = intval($page);
        $count = intval($count);

        $res = array_slice($list, ($page - 1) * $count, $count + 1);

        self::isMoreRes($res, $count);
        return $res;
    }

}
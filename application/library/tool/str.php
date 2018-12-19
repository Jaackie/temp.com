<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/3/23
 * Time: 10:06
 */
class tool_str
{
    /**
     * utf8截取字符串长度
     * @param $string
     * @param $length
     * @param string $suffix
     * @return string
     */
    public static function utf8_substr($string, $length, $suffix = '...')
    {
        $stringTmp = mb_substr($string, 0, $length, 'utf-8');
        if ($string == $stringTmp) return $string;

        return $stringTmp . $suffix;
    }

    /**
     * utf8字符串长度
     * @param $string
     * @return int
     */
    public static function utf8_strlen($string)
    {
        return mb_strlen($string, 'utf-8');
    }

    /**
     * utf8字符串长度检查
     * @param $string
     * @param int $checkNum 长度
     * @return bool
     */
    public static function utf8_strlen_check($string, $checkNum = 233)
    {
        if (self::utf8_strlen($string) > $checkNum) {
            return false;
        }

        return true;
    }

    /**
     * 截取命名类的字符串，去除空格类
     * @param $string
     * @param $length
     * @return string
     */
    public static function utf8_substr_name($string, $length)
    {
        $string = str_replace(' ', '', $string);
        return self::utf8_substr($string, $length, '');
    }

    /**
     * 网页端输出过滤
     * @param $arr
     * @param $key
     */
    public static function htmlFilter(&$arr, $key)
    {
        if (!$arr) return;

        foreach ($arr as $i => $a) {
            if (isset($a[$key])) {
                $arr[$i][$key] = htmlspecialchars($a[$key]);
            }
        }
    }

    /**
     * 过滤非法字符串
     * @param $string
     */
    public static function filterString(&$string)
    {
        $string = htmlspecialchars($string);
    }
}
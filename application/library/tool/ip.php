<?php
/**
 * User: Jaackie
 * Date: 2018/3/18
 */

class tool_ip
{
    public static function get()
    {
        $ip = '';
        $unknown = 'unknown';
        if (!$ip && !empty($_SERVER['HTTP_X_FORWARDED_FOR']) && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'], $unknown)) {
            $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $clientIp = trim(current($ipList));
            if (ip2long($clientIp) !== false) {
                $ip = $clientIp;
            }
        }
        if (!$ip && !empty($_SERVER['REMOTE_ADDR']) && strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)) {
            $ip = trim($_SERVER['REMOTE_ADDR']);
        }
        return $ip;
    }

}
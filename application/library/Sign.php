<?php
/**
 * User: Jaackie
 * Date: 12/22/18
 */

class Sign
{
    /**
     * 获取签名
     * @param array $params 请求参数
     * @param string $token
     * @return string
     */
    public static function getSign($params, $token = null)
    {
        if (is_null($token) && isset($params['token'])) {
            $token = $params['token'];
        }

        $buildQuery = self::buildQuery($params);

        $sign = md5($buildQuery . $token);
        return $sign;
    }

    /**
     * 将参数组合成字符串
     * @param $query
     * @return string
     */
    private static function buildQuery($query)
    {
        $arr = [];
        if ($query) {
            ksort($query);
            foreach ($query as $k => $v) {
                if (is_array($v)) {
                    $v = self::buildQuery($query);
                }
                $arr[] = "$k=$v";
            }
        }
        return implode('&', $arr);
    }

    /**
     * 检查签名
     * @param array $data
     * @param string $token
     * @return bool
     */
    public static function checkSign($data, $token = null)
    {
        $originSign = $data['sign'];

        unset($data['sign']);
        $sign = self::getSign($data, $token);

        return $originSign === $sign;
    }

}
<?php

/**
 * Created by PhpStorm.
 * User: Jaackie
 * Date: 2017/3/24
 * Time: 10:39
 */
class tool_time
{
    public $time;           //当前时间戳
    public $days_in_month;  //每月天数
    public $weeks_in_month; //每月周数

    const FORMAT_S = 'Y-n-d H:i:s';

    public function __construct($time = null)
    {
        $this->time = $time === null ? time() : intval($time);
        $this->days_in_month = cal_days_in_month(CAL_GREGORIAN, date('n', $this->time), date('Y', $this->time));
        $this->weeks_in_month = round(($this->days_in_month / 7), 5);
    }

    public static function instance($time = null)
    {
        return new self($time);
    }

    /**
     * 人性化时间 humanize time
     * @param $time
     * @return string
     */
    public function hTime($time)
    {
        $t = $this->time - $time;

        $f = [
            86400 => ['max' => 7, 'unit' => '天'],
            604800 => ['max' => $this->weeks_in_month, 'unit' => '星期'],
            3600 => ['max' => 24, 'unit' => '小时'],
            2592000 => ['max' => 12, 'unit' => '个月'],
            60 => ['max' => 60, 'unit' => '分钟'],
            1 => ['max' => 60, 'unit' => '秒'],
            31536000 => ['max' => 100, 'unit' => '年'],
        ];
        foreach ($f as $k => $v) {
            $c = round(($t / $k), 5);
            if (1 <= $c && $c < $v['max']) {
                return intval($c) . $v['unit'] . '前';
            }
        }
    }

}
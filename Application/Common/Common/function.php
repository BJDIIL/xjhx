<?php
// +----------------------------------------------------------------------
// | Joinusad  function.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.10 15:33
// +----------------------------------------------------------------------
define('NAME_IS_NULL', 10000);
define('EMAIL_IS_NULL', 10001);
define('EMAIL_INVALID', 10002);
define('MOBILE_IS_NULL', 10003);
define('MOBILE_INVALID', 10004);
define('SUCCESS', 10005);
define('FAIL', 10006);
define('REGISTERED', 10007);
define('CODE_INVALID', 10008);
define('PRESENT_NOT_EXIST', 10009);
define('SEC_END', 10010);
define('STOCK_NOT_ENOUGH', 10011);
define('PROFILE_INVALID', 10012);
define('INTEGRAL_NOT_ENOUGH', 10013);
define('SHIPPING_METHOD_INVALID', 10014);
define('SEC_NOT_START', 10015);
define('EXCHANGE_TIME_OVER', 10016);


define('SERVER_HOST', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);

function time_to_date_base($time, $format)
{
    return date($format, $time);
}

function time_to_date($time)
{
    return time_to_date_base($time, 'Y-m-d');
}

function time_to_datetime($time)
{
    return time_to_date_base($time, 'Y-m-d H:i:s');
}

function remove_slash($source)
{
    return str_replace('/', '', $source);
}

function getNameByCode($code)
{
    return M('city')->where('code = ' . $code)->getField('name');
}

function category_to_plus_or_minus($category)
{
    //增加积分类别小于20
    if ($category < 20) {
        return '+';
    }
    // 消费积分 类别大于等于20
    if ($category >= 20) {
        return '-';
    }
}

function debug_log($msg)
{
    if (C('SHOW_DEBUG_LOG')) {
        file_put_contents('log.txt', date('Y-m-d h:i:s') . ' ' . json_encode($msg) . PHP_EOL, FILE_APPEND);
    }
}
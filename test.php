<?php
// +----------------------------------------------------------------------
// | Joinusad  test.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.9 14:52
// +----------------------------------------------------------------------

//var_dump($_SERVER);
//echo $_SERVER['REQUEST_SCHEME'] .'://'. $_SERVER['HTTP_HOST'];

//echo time() - 3600 * 24 * 2 - 15;
//echo "__PUBLIC__". __PUBLIC__;

//echo date('Y-m-d H:i:s');

$week = date('w');
echo $week;
$weeks['mon'] = date('m��d��', strtotime('+' . 1 - $week . ' days'));
$weeks['tue'] = date('m��d��', strtotime('+' . 2 - $week . ' days'));
$weeks['wed'] = date('m��d��', strtotime('+' . 3 - $week . ' days'));
$weeks['thu'] = date('m��d��', strtotime('+' . 4 - $week . ' days'));
$weeks['fri'] = date('m��d��', strtotime('+' . 5 - $week . ' days'));
$weeks['sat'] = date('m��d��', strtotime('+' . 6 - $week . ' days'));
$weeks['sun'] = date('m��d��', strtotime('+' . 7 - $week . ' days'));
var_dump($weeks);

echo date('Y-m-d 00:00:00', strtotime('+' . 1 - $week . ' days'));
<?php
// +----------------------------------------------------------------------
// | Joinusad  BaseController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.13 10:20
// +----------------------------------------------------------------------

namespace Home\Controller;


use Think\Controller;

class BaseController extends Controller
{
    public function __construct()
    {
        parent::__construct();


        if (!isset($_SESSION['openid'])) {
            $url = 'index.php?s=/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
            $_SESSION['returnURL'] = $url;
            $this->redirect('Account/get_openid');
            die;
        }
        $map['uid'] = array('eq', $_SESSION['uid']);
        $profile = M('profile')->where($map)->select();
        if ($profile) {
            $_SESSION['profile'] = $profile[0];

            return;
        }
        if (!isset($_SESSION['profile'])) {
            $this->redirect('Account/register', array('msg' => '尚未注册'));
            die;
        }
    }
}
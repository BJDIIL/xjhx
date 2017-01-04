<?php
// +----------------------------------------------------------------------
// | Joinusad  TipsController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.10 16:08
// +----------------------------------------------------------------------

namespace Home\Controller;


use Think\Controller;

class TipsController extends BaseController
{

    public function index()
    {
        $this->display();
    }

    private function tip($type)
    {
        $class_name = '';
        switch ($type) {
            case 'success':
                $class_name = 'weui-icon-success';
                break;
            case 'fail':
                $class_name = 'weui-icon-warn';
                break;
        }
        $this->assign('class_name', $class_name);
        $msg__title = I('get.msg__title');
        $msg__desc = I('get.msg__desc');
        $btn_primary = I('get.btn_primary');
        $btn_primary_url = urldecode(I('get.btn_primary_url'));
        $btn_default = I('get.btn_default');
        $btn_default_url = urldecode(I('get.btn_default_url'));
        $this->assign('msg__title', $msg__title);
        $this->assign('msg__desc', $msg__desc);
        $this->assign('btn_primary', $btn_primary);
        $this->assign('btn_primary_url', $btn_primary_url);
        $this->assign('btn_default', $btn_default);
        $this->assign('btn_default_url', $btn_default_url);
        $this->display('tip');
    }

    public function success()
    {
        $this->tip('success');
    }


    public function fail()
    {
        $this->tip('fail');
    }
}
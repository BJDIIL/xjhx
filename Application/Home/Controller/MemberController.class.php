<?php
// +----------------------------------------------------------------------
// | Joinusad  MemberController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.9 9:32
// +----------------------------------------------------------------------

namespace Home\Controller;


use Think\Controller;

class MemberController extends BaseController
{
    public function index()
    {
        $map['uid'] = array('eq', $_SESSION['uid']);
        $profile = M('profile')->where($map)->select();
        $_SESSION['profile'] = $profile[0];
        $this->assign('profile', $_SESSION['profile']);
        $this->display();
    }

    public function integral()
    {
        $map['uid'] = array('eq', $_SESSION['uid']);
        $integral_detail = M('integral_detail')
            ->where($map)
            ->order('id desc')
            ->limit(0, C('INTEGRAL_HISTORY_COUNT'))
            ->select();

        $this->assign('integral_detail', $integral_detail);
        $this->assign('profile', $_SESSION['profile']);
        $this->display();
    }

    public function my_info()
    {
        if (IS_POST) {
            $map['uid'] = array('eq', $_SESSION['uid']);
            $data['name'] = I('post.name');
            $profile = M('profile')->where($map)->save($data);
            exit(json_encode(SUCCESS));
        }
        $map['uid'] = array('eq', $_SESSION['uid']);
        $profile = M('profile')->where($map)->select();
        $_SESSION['profile'] = $profile[0];
        $this->assign('profile', $_SESSION['profile']);
        $this->display();
    }

    public function clear_session()
    {
        session(null);
    }

    public function order()
    {
        $this->display();
    }

}
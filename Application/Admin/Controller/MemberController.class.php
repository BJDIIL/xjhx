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

namespace Admin\Controller;


use Think\Page;

class MemberController extends AdminController
{
    public function index($p = 1)
    {
        if ($p == 1) {
            $total = M('profile')->count();
            $_SESSION['profile_total'] = $total;
        }

        $page = new Page($_SESSION['profile_total'], C('PAGE_SIZE'));
        $show = $page->show();
        $this->assign('page', $show);
        $profile = M('profile')
            ->order('create_time desc')
            ->limit($page->firstRow, $page->listRows)
            ->select();
//        $profile = null;
        $this->assign('profile', $profile);
        $this->display();
    }

    public function logout()
    {
        $_SESSION['admin'] = null;
        $this->redirect('Account/login');
    }
}
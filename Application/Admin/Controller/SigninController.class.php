<?php
// +----------------------------------------------------------------------
// | Joinusad  SigninController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.8 9:25
// +----------------------------------------------------------------------

namespace Admin\Controller;


use Think\Page;

class SigninController extends AdminController
{
    public function index($p = 1)
    {
        if ($p == 1) {
            $total = M('signin_history')->count();
            $_SESSION['signin_history_total'] = $total;
        }

        $page = new Page($_SESSION['signin_history_total'], C('PAGE_SIZE'));
        $show = $page->show();
        $this->assign('page', $show);
        $signin_history = M('signin_history sh')
            ->join('profile p on sh.uid = p.uid ')
            ->field('sh.id,p.name,p.mobile,p.email,sh.signin_time,sh.integral,sh.days')
            ->order('sh.signin_time desc')
            ->limit($page->firstRow, $page->listRows)
            ->select();
//        $profile = null;
        $this->assign('signin_history', $signin_history);
        $this->display();
    }

    public function success()
    {
        $this->display();
    }
}
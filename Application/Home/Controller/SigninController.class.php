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

namespace Home\Controller;


use Think\Controller;

class SigninController extends BaseController
{
    public function index()
    {
        //今日已经签到
        $map = array();
        $map['uid'] = array('eq', $_SESSION['uid']);
        $start = strtotime(date('Y-m-d'));
        $end = strtotime(date('Y-m-d')) + 60 * 60 * 24;
        $map['signin_time'] = array(array('gt', $start), array('lt', $end));
        $today_signin = M('signin_history')->where($map)->count();
        if ($today_signin) { // 已签到
            $url = urlencode(U('Member/index'));
            $url_default = urlencode(U('Signin/history'));
            $this->redirect('Tips/success',
                array('msg__title'      => '今日已签到',
                      'btn_primary'     => '个人中心',
                      'btn_primary_url' => $url,
                      'btn_default'     => '签到记录',
                      'btn_default_url' => $url_default));
            die;
        }
        // 尚未签到
        $trans = M('user');
        $trans->startTrans();

        $signin_history = M('signin_history');
        $signin_history->uid = $_SESSION['uid'];
        $signin_history->signin_time = time();
        $signin_history->integral = C('SIGNIN_INTEGRAL');
        // 查询连续签到天数
        $map = array();
        $map['uid'] = array('eq', $_SESSION['uid']);
        $start = strtotime(date('Y-m-d')) - 60 * 60 * 24;
        $end = strtotime(date('Y-m-d'));
        $map['signin_time'] = array(array('gt', $start), array('lt', $end));
        $yesterday = M('signin_history')->where($map)->select();
        $days = 1;
        if ($yesterday) {
            $days = $yesterday[0]['days'] + 1;
        }
        $signin_history->days = $days;
        $signin_history_result = $signin_history->add();

        $map['uid'] = array('eq', $_SESSION['uid']);
        $add_total_integral = M('profile')->where($map)->setInc('total_integral', C('SIGNIN_INTEGRAL'));
        $add_remain_integral = M('profile')->where($map)->setInc('remain_integral', C('SIGNIN_INTEGRAL'));
        // 添加积分详情
        $detail = M('integral_detail');
        $detail->integral = C('SIGNIN_INTEGRAL');
        $detail->create_time = time();
        $detail->uid = $_SESSION['uid'];
        $detail->category = C('INTEGRAL_CATEGORY_SIGNIN');
        $detail->description = '签到赠送【' . C('SIGNIN_INTEGRAL') . '】积分';
        $detail_result = $detail->add();

        if ($signin_history_result
            && $add_total_integral
            && $add_remain_integral
            && $detail_result
        ) {
            $trans->commit();
            // 签到完成
            $url = urlencode(U('Member/index'));
            $url_default = urlencode(U('Signin/history'));
            $this->redirect('Tips/success',
                array('msg__title'      => '签到成功',
                      'btn_primary'     => '个人中心',
                      'btn_primary_url' => $url,
                      'btn_default'     => '签到记录',
                      'btn_default_url' => $url_default));
            die;

        } else {
            $url = urlencode(U('Member/index'));
            $this->redirect('Tips/fail',
                array('msg__title'      => '签到失败',
                      'btn_primary'     => '个人中心',
                      'btn_primary_url' => $url,
                      'msg__desc'       => '添加签到记录失败'));
            die;
        }

    }

    public function history()
    {
        $map['uid'] = $_SESSION['uid'];
        $start = strtotime(date('Y-m-01 00:00:00', strtotime('-3 month')));
        $end = time();
        $map['signin_time'] = array(array('gt', $start), array('lt', $end));
        $history = M('signin_history')->where($map)->order('signin_time desc')->select();

        $this->assign('history', $history);
        $this->display();
    }
}
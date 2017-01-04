<?php
// +----------------------------------------------------------------------
// | Joinusad  IntegralMallController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.13 10:09
// +----------------------------------------------------------------------

namespace Home\Controller;


use Think\Controller;

/**
 * 积分商城
 * Class IntegralMallController
 * @package Home\Controller
 */
class IntegralMallController extends BaseController
{

    /**
     * 礼品列表
     */
    public function index()
    {
        $map['xiajia'] = array('eq', 0);
        $map['status'] = array('eq', 0);
        if ($_POST['key']) {
            $map['name'] = array('like', '%' . $_POST['key'] . '%');
            $this->assign('key', $_POST['key']);
        }
        $data = M('present')->where($map)->order('sort')->select();
        $now = time();
        foreach ($data as &$value) {
            if ($value['is_sec'] == 1) {
                if ($now > $value['sec_start'] && $now < $value['sec_end']) {
                    $value['is_sec'] = 1;
                } else {
                    $value['is_sec'] = 0;
                }
            }
        }
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 礼品详情
     */
    public function details()
    {
        $id = intval(I('get.id'));
        $_SESSION['present_id'] = $id;

        $count = intval($_SESSION['count']);
        if (!$count) {
            $count = 1;
        }
        $this->assign('count', $count);
        $map['id'] = array('eq', $id);
        $map['xiajia'] = array('eq', 0);
        $map['status'] = array('eq', 0);
        $data = M('present')->where($map)->select();

        $data = $data[0];
        $now = time();
        if ($data['is_sec'] == 1) {
            if ($now > $data['sec_start'] && $now < $data['sec_end']) {
                $data['is_sec'] = 1;
            } else {
                $data['is_sec'] = 0;
            }
        }
        if ($data['is_sec'] == 1) {
            $data['miaoshaRemainTime'] = intval($data['sec_end']) - time();
        }

        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 确认订单信息
     */
    public function order()
    {
        $count = intval(I('get.count'));
        $_SESSION['count'] = $count;

        // 收货地址
        $map['uid'] = array('eq', $_SESSION['uid']);
        $map['status'] = array('eq', 0);
        $address = M('address')->where($map)->select();
        $default_address_id = 0;
        foreach ($address as &$value) {
            if ($value['is_default'] == 1) {
                $default_address_id = $value['id'];
            }
            $value['address'] = getNameByCode($value['province_id'])
                . getNameByCode($value['city_id'])
                . getNameByCode($value['district_id'])
                . $value['details'];
        }
        $this->assign('address', $address);
        $this->assign('default_address_id', $default_address_id);


        if (!$count) {
            $count = 1;
        }
        $this->assign('count', $count);
        // 礼品信息
        $id = $_SESSION['present_id'];
        $map['id'] = array('eq', $id);
        $map['xiajia'] = array('eq', 0);
        $map['status'] = array('eq', 0);
        $data = M('present')->where($map)->select();

        $data = $data[0];
        $now = time();
        if ($data['is_sec'] == 1) {
            if ($now > $data['sec_start'] && $now < $data['sec_end']) {
                $data['is_sec'] = 1;
            } else {
                $data['is_sec'] = 0;
            }
        }
        if ($data['is_sec'] == 1) {
            $data['integral']=$data['sec_integral'];
            $data['miaoshaRemainTime'] = intval($data['sec_end']) - time();
        }

        $this->assign('data', $data);
        $this->display();
    }

}
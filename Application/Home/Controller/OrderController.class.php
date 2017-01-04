<?php
// +----------------------------------------------------------------------
// | Joinusad  OrderController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.20 12:22
// +----------------------------------------------------------------------

namespace Home\Controller;


class OrderController extends BaseController
{
    /**
     * 订单列表
     */
    public function index()
    {
        $map['uid'] = array('eq', $_SESSION['uid']);
        $data = M('orders o')
            ->join('present p on o.pid = p.id')
            ->where($map)
            ->field('o.id,p.name,p.image,o.create_time,o.integral,o.total,o.count')
            ->order('o.id desc')
            ->select();
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 下单
     */
    public function add()
    {
        if (IS_POST) {
            $uid = intval($_SESSION['uid']);
            $count = intval($_SESSION['count']);
            $shipping_method = intval(I('post.shipping_method'));
            $address_id = intval(I('post.address_id'));
            $integral = 0;
            $total = 0;
            $pid = intval(I('post.id'));
            $present = M('present')->find($pid);
            // 已经下架或者删除
            if (!$present || $present['status'] != 0 || $present['xiajia'] != 0) {
                exit(json_encode(PRESENT_NOT_EXIST));
            }
            $map['uid'] = array('eq', $uid);
            $profile = M('profile')->where($map)->select();
            if (count($profile) > 0) {
                $profile = $profile[0];
            } else {
                exit(json_encode(PROFILE_INVALID));
            }
            //秒杀结束后按普通方式进行

            if ($present['is_sec'] == 1) {
                $now = time();
                if ($now > $present['sec_start'] && $now < $present['sec_end']) {
                    $present['is_sec'] = 1;
                } else {
                    $present['is_sec'] = 0;
                }
            }
            //秒杀
            if ($present['is_sec'] == 1) {

                // 当天兑换次数是否超标
                $start = strtotime(date('Y-m-d'));
                $end = strtotime(date('Y-m-d', strtotime('+1 days')));
                $map['create_time'] = array('between', array($start, $end));
                $map['uid'] = array('eq', $uid);
                $map['status'] = array('neq', 4);
                $map['is_sec'] = array('eq', 1);
                $exchanged = M('orders')->where($map)->count();
                if ($exchanged > C('EXCHANGE_TIME_PER_DAY')) {
                    exit(json_encode(EXCHANGE_TIME_OVER));
                }
                $integral = $present['sec_integral'];
                $total = $integral * $count;
                // 积分不足
                if ($profile['remain_integral'] < $present['sec_integral']) {
                    exit(json_encode(INTEGRAL_NOT_ENOUGH));
                }
                $now = time();
                // 不在秒杀的时间段
                if ($now > $present['sec_end']) {
                    exit(json_encode(SEC_END));
                }
                if ($now < $present['sec_start']) {
                    exit(json_encode(SEC_NOT_START));
                }
                // 库存不足
                if ($count > $present['sec_stock']) {
                    exit(json_encode(STOCK_NOT_ENOUGH));
                }

            } else {
                $integral = $present['integral'];
                $total = $integral * $count;
                // 积分不足
                if ($profile['remain_integral'] < $total) {
                    exit(json_encode(INTEGRAL_NOT_ENOUGH));
                }
                // 库存不足
                if ($count > $present['stock']) {
                    exit(json_encode(STOCK_NOT_ENOUGH));
                }

            }

            // 配送方式
            // 只允许自提
            $status = 0;
            if ($present['self_delivery'] == 1) {
                $status = 1;
                if ($shipping_method == 2) {
                    // 配送方式错误
                    exit(json_encode(SHIPPING_METHOD_INVALID));
                }
            }
            $address = "";
            $name = "";
            $mobile = "";
            $email = '';

            if ($shipping_method == 2) {
                $address_info = M('address')->find($address_id);
                $address .= getNameByCode($address_info['province_id'])
                    . getNameByCode($address_info['city_id'])
                    . getNameByCode($address_info['district_id'])
                    . $address_info['details'];
                $name = $address_info['name'];
                $mobile = $address_info['mobile'];
                $email = $address_info['email'];

            }

            $trans = M();
            $trans->startTrans();

            // 添加订单
            $data = M('orders');
            $data->uid = $uid;
            $data->pid = $pid;
            $data->integral = $integral;
            $data->is_sec = $present['is_sec'];
            $data->create_time = time();
            $data->shipping_method = $shipping_method;
            $data->address = $address;
            $data->mobile = $mobile;
            $data->email = $email;
            $data->status = $status;
            $data->name = $name;
            $data->count = $count;
            $data->total = $total;
            $order_add_result = $data->add();
            // 更新用户积分
            $map = array();
            $map['uid'] = array('eq', $uid);

            $r2 = M('profile')->where($map)->setDec('remain_integral', $total);
            $r3 = M('profile')->where($map)->setInc('exchange_integral', $total);
            // 添加积分明细

            $detail = M('integral_detail');
            $detail->integral = $total;
            $detail->create_time = time();
            $detail->uid = $uid;
            $detail->category = C('INTEGRAL_CATEGORY_EXCHANGE_PRESENT');
            $detail->description = '积分兑换消费【' . $total . '】积分';
            $detail_result = $detail->add();

            //更新库存
            $r4 = false;
            if ($present['is_sec'] == 1) {
                $r4 = M('present')->where('id=' . $pid)->setDec('sec_stock', $count);
            } else {
                $r4 = M('present')->where('id=' . $pid)->setDec('stock', $count);
            }
            if ($order_add_result
               && $r2 && $r3 && $r4
                && $detail_result
            ) {
                $trans->commit();
                exit(json_encode(SUCCESS));
            } else {
                $trans->rollback();
                exit(json_encode(FAIL));
            }
        }
    }

    /**
     * 订单详情
     */
    public function details()
    {
        $oid = intval($_GET['id']);
        $map['o.uid'] = array('eq', intval($_SESSION['uid']));
        $map['o.id'] = array('eq', $oid);
        $data = M('orders o')
            ->join('present p on o.pid = p.id')
            ->where($map)
            ->field("p.image,p.name,p.desc,"
                . "case when o.shipping_method = 1 then '自提' else '物流配送' end as shipping_method ,"
                . "o.address,o.count,o.integral,o.total,o.status,o.id")
            ->select();

        $this->assign('data', $data[0]);
        $this->display();
    }

    public function confirm()
    {
        if (IS_POST) {
            $id = intval(I('post.id'));
            $uid = intval($_SESSION['uid']);
            $map['id'] = array('eq', $id);
            $map['uid'] = array('eq', $uid);
            M('orders')->where($map)->save(array('status' => 3));
            exit(json_encode(SUCCESS));
        }
    }

}
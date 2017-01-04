<?php
// +----------------------------------------------------------------------
// | Joinusad  OrderController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.21 13:56
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Page;

/**
 * 订单管理
 * Class OrderController
 * @package Admin\Controller
 */
class OrderController extends AdminController
{
    // 改控制器对应的表名
    private $table_name = 'orders';
    private $total_count = 'orders_total';

    /**
     * 订单列表
     *
     * @param int $p
     */
    public function index($p = 1)
    {
        if ($p == 1) {
            $total = M($this->table_name)->count();
            $_SESSION[$this->total_count] = $total;
        }
        $page = new Page($_SESSION[$this->total_count], C('PAGE_SIZE'));
        $show = $page->show();
        $this->assign('page', $show);


        $data = M('orders o')
            ->join('present p on o.pid = p.id')
            ->field("o.create_time,o.id,p.image,p.name,p.image,"
                . "case when o.shipping_method = 1 then '自提' else '物流配送' end as shipping_method ,"
                . "o.address,o.count,o.integral,o.total,o.status,o.is_sec,o.name as username,o.mobile")
            ->limit($page->firstRow, $page->listRows)
            ->order('o.id desc')
            ->select();

        $this->assign('data', $data);
        $this->display();
    }

    public function update_status()
    {
        if (IS_POST) {
            $id = intval(I('post.id'));
            $status = intval(I('post.status'));
            $map['id'] = array('eq', $id);

            if ($status == 4) {
                $trans = M();
                $trans->startTrans();
                // 修改订单状态
                $result = M('orders')->where($map)->save(array('status' => $status));
                $order = M('orders')->find($id);
                // 返还积分
                $map = array();
                $map['uid'] = array('eq', $order['uid']);
                $r1 = M('profile')->where($map)->setInc('remain_integral', $order['total']);
                $r2 = M('profile')->where($map)->setDec('exchange_integral', $order['total']);
                // 更新库存
                if ($order['is_sec'] == 1) {
                    $r3 = M('present')->where('id=' . $order['pid'])->setInc('sec_stock', $order['count']);
                } else {
                    $r3 = M('present')->where('id=' . $order['pid'])->setInc('stock', $order['count']);
                }

                if ($result && $r1 == 1 && $r2 == 1 && $r3 == 1) {
                    $trans->commit();
                    exit(json_encode(SUCCESS));
                }
                $trans->rollback();
                exit(json_encode(FAIL));
            } else {
                $result = M('orders')->where($map)->save(array('status' => $status));
                if ($result) {
                    exit(json_encode(SUCCESS));
                } else {
                    exit(json_encode(FAIL));
                }
            }

        }
    }
}
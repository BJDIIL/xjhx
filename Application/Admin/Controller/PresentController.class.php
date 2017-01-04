<?php
// +----------------------------------------------------------------------
// | Joinusad  PresentController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.14 22:37
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Page;

/**
 * 礼品管理
 * Class PresentController
 * @package Admin\Controller
 */
class PresentController extends AdminController
{
    /**
     * 礼品列表
     *
     * @param $p int
     */
    public function index($p = 1)
    {
        if ($p == 1) {
            $total = M('present')->count();
            $_SESSION['present_total'] = $total;
        }

        $page = new Page($_SESSION['present_total'], C('PAGE_SIZE'));
        $show = $page->show();
        $this->assign('page', $show);

        $data = M('present')
            ->order('sort')
            ->limit($page->firstRow, $page->listRows)
            ->select();
//        $profile = null;
        $this->assign('data', $data);

        $this->display();
    }

    /**
     * 添加礼品
     */
    public function add()
    {
        if ($_GET['id']) {
            $data = M('present')->find(intval($_GET['id']));

            $this->assign('data', $data);
        }
        if (IS_POST) {
//            $count = intval($_POST['count']);
//
//            $stock = intval($_POST['stock']);
//            if ($stock > $count) {
//                exit(json_encode());
//            }

            $_POST['sec_start'] = strtotime($_POST['sec_start']);
            $_POST['sec_end'] = strtotime($_POST['sec_end']);
            if ($_POST['id'] == '') {
                $_POST['create_time'] = time();
                $_POST['status'] = 0;
                $result = M('present')->add($_POST);
            } else {
                $_POST['update_time'] = time();
                $result = M('present')->save($_POST);
            }

            if (!$result) {
                exit(json_encode(FAIL));
            }
            exit(json_encode(SUCCESS));
        }
        $this->display();
    }
}
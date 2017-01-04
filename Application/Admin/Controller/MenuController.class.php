<?php
// +----------------------------------------------------------------------
// | Joinusad  MenuController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.21 14:05
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Page;

/**
 * �˵�����
 * Class MenuController
 * @package Admin\Controller
 */
class MenuController extends AdminController
{
// �Ŀ�������Ӧ�ı���
    private $table_name = 'menu';
    private $total_count = 'menu_total';

    /**
     * �����б�
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

        $data = M($this->table_name)
            ->order('id desc')
            ->limit($page->firstRow, $page->listRows)
            ->select();
        $this->assign('data', $data);
        $this->display();
    }
}
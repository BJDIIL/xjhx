<?php
// +----------------------------------------------------------------------
// | Joinusad  ForwardController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.14 22:42
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Page;

/**
 * 转发管理
 * Class ForwardController
 * @package Admin\Controller
 */
class ForwardController extends AdminController
{
    // 改控制器对应的表名
    private $table_name = 'forward';
    private $total_count = 'forward_total';

    /**
     * 转发文章列表
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


        $data = M('forward f')
            ->join('article a on f.aid = a.id')
            ->join('profile p on f.uid = p.uid')
            ->field("f.id,a.title,f.create_time,p.name")
            ->limit($page->firstRow, $page->listRows)
            ->order('f.id desc')
            ->select();

        $this->assign('data', $data);
        $this->display();
    }
}
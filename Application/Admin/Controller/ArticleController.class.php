<?php
// +----------------------------------------------------------------------
// | Joinusad  ArticleController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.23 9:50
// +----------------------------------------------------------------------

namespace Admin\Controller;

use Think\Page;

/**
 * 转发文章管理
 * Class ArticleController
 * @package Admin\Controller
 */
class ArticleController extends AdminController
{
    // 改控制器对应的表名
    private $table_name = 'article';
    private $total_count = 'article_total';

    /**
     * 转发文章列表
     *
     * @param int $p
     */
    public function index($p = 1)
    {
        $map['status'] = array('eq', 0);
        if ($p == 1) {
            $total = M($this->table_name)->where($map)->count();
            $_SESSION[$this->total_count] = $total;
        }
        $page = new Page($_SESSION[$this->total_count], C('PAGE_SIZE'));
        $show = $page->show();
        $this->assign('page', $show);

        $data = M('article a')
            ->where($map)
            ->limit($page->firstRow, $page->listRows)
            ->field('id,title,reading,(select count(1) from forward where aid = a.id) forward,'
                . 'can_forward,create_time,status')
            ->order('id desc')
            ->select();

//        dump($data);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 添加文章
     */
    public function add()
    {
        if ($_GET['id']) {
            $data = M($this->table_name)->find(intval($_GET['id']));

            $data['content'] = json_encode($data['content']);
            $this->assign('data', $data);
        }
        if (IS_POST) {
            $trans = M();
            $trans->startTrans();

            $update_can_forward = true;
            if ($_POST['can_forward'] == 1) {
                $map['status'] = array('eq', 0);
                $map['can_forward'] = array('eq', 1);
                $update_can_forward = M('article')->where($map)->save(array('can_forward' => 0));
            }
            if ($_POST['id'] == '') {
                $_POST['create_time'] = date('Y-m-d H:i:s');
                $_POST['status'] = 0;
                $result = M($this->table_name)->add($_POST);
            } else {
                $_POST['update_time'] = date('Y-m-d H:i:s');
                $result = M($this->table_name)->save($_POST);
            }

            if ($update_can_forward >= 0
                && $result > 0
            ) {
                $trans->commit();
                exit(json_encode(SUCCESS));
            }

            $trans->rollback();
            exit(json_encode(FAIL));

        }
        $this->display();
    }

    public function del()
    {
        if (IS_POST) {
            $id = intval(I('post.id'));
            $map['id'] = array('eq', $id);
            M('article')->where($map)->save(array('status' => 1));
            exit(json_encode(SUCCESS));
        }
    }
}
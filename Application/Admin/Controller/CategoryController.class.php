<?php
// +----------------------------------------------------------------------
// | Joinusad  CategoryController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.14 22:34
// +----------------------------------------------------------------------

namespace Admin\Controller;


class CategoryController extends AdminController
{
    /**
     * 礼品分类列表
     */
    public function index()
    {
        $category = M('category')->where('status=0')->order('create_time desc')->select();
        $this->assign('data', $category);
        $this->display();
    }

    /**
     * 添加礼品分类
     */
    public function add()
    {
        if (IS_POST) {
            $_POST['create_time'] = time();
            $result = M('category')->add($_POST);
            if (!$result) {
                $this->error("添加失败");
                die;
            }
            $this->success('添加成功', U('Category/index'));
            die;
        }
        $this->display();
    }

    /**
     * 删除礼品分类
     */
    public function delete()
    {
        $id = intval($_GET['id']);
        $result = M('category')->where('id= ' . $id)->save(array('status' => 1));
        exit(json_encode($result));
    }
}
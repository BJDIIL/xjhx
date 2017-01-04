<?php
// +----------------------------------------------------------------------
// | Joinusad  AdminController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.13 10:22
// +----------------------------------------------------------------------

namespace Admin\Controller;


use Think\Controller;

/**
 * 后台控制器基类
 * Class AdminController
 * @package Admin\Controller
 */
class AdminController extends Controller
{
    /**
     * 构造函数，验证用户是否已经登陆
     */
    public function __construct()
    {
        parent::__construct();
        if (!isset($_SESSION['admin']) || empty($_SESSION['admin'])) {
            $this->redirect("Account/login");
            die();
        }
        $this->assign('__MENU__', $this->getMenus());
    }

    /**
     * 获取可用的导航菜单信息
     * @return mixed
     */
    final public function getMenus()
    {
        $main_menus = M('menu')->where('pid = 0 and status = 0')->order('orderby')->select();
        foreach ($main_menus as $key => &$value) {
            $value['sub_menus'] = M('menu')
                ->where('pid = ' . $value['id'] . ' and status = 0 ')->order('orderby')->select();
        }

        return $main_menus;
    }
}
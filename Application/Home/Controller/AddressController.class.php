<?php
// +----------------------------------------------------------------------
// | Joinusad  AddressController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.19 18:49
// +----------------------------------------------------------------------

namespace Home\Controller;


/**
 * 收货地址管理
 * Class AddressController
 * @package Home\Controller
 */
class AddressController extends BaseController
{
    /**
     * 收货地址列表
     */
    public function index()
    {
        $map['uid'] = array('eq', $_SESSION['uid']);
        $map['status'] = array('eq', 0);
        $data = M('address')->where($map)->select();
        foreach ($data as &$value) {
            $value['address'] = getNameByCode($value['province_id'])
                . getNameByCode($value['city_id'])
                . getNameByCode($value['district_id'])
                . $value['details'];
        }
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($_GET['id']) {
            $data = M('address')->find(intval($_GET['id']));
            $data['address'] = getNameByCode($data['province_id'])
                . ' ' . getNameByCode($data['city_id'])
                . ' ' . getNameByCode($data['district_id']);
            $data['data_codes'] = $data['province_id'] . ','
                . $data['city_id'] . ','
                . $data['district_id'];

            $this->assign('data', $data);
        }
        if (IS_POST) {
            if ($_POST['id'] == '') {
                $_POST['create_time'] = time();
                $_POST['uid'] = $_SESSION['uid'];
                $data_codes = $_POST['data_codes'];
                unset($_POST['data_codes']);
                $data_codes = explode(',', $data_codes);
                $_POST['province_id'] = $data_codes[0];
                $_POST['city_id'] = $data_codes[1];
                $_POST['district_id'] = $data_codes[2];
                $_POST['status'] = 0;
                if ($_POST['is_default'] == 1) {
                    $map = array();
                    $map['uid'] = array('eq', $_SESSION['uid']);
                    $map['is_default'] = array('eq', 1);
                    $map['status'] = array('eq', 0);
                    M('address')->where($map)->save(array('is_default' => 0));
                }
                $result = M('address')->add($_POST);

            } else {
                $_POST['update_time'] = time();
                $data_codes = $_POST['data_codes'];
                unset($_POST['data_codes']);
                $data_codes = explode(',', $data_codes);
                $_POST['province_id'] = $data_codes[0];
                $_POST['city_id'] = $data_codes[1];
                $_POST['district_id'] = $data_codes[2];
                if ($_POST['is_default'] == 1) {
                    $map = array();
                    $map['uid'] = array('eq', $_SESSION['uid']);
                    $map['is_default'] = array('eq', 1);
                    $map['status'] = array('eq', 0);
                    M('address')->where($map)->save(array('is_default' => 0));
                }
                $result = M('address')->save($_POST);
            }

            if (!$result) {
                exit(json_encode(FAIL));
            }
            exit(json_encode(SUCCESS));
        }
        $this->display();
    }
}
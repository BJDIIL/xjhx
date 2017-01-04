<?php
// +----------------------------------------------------------------------
// | Joinusad  AccountController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.13 18:57
// +----------------------------------------------------------------------

namespace Home\Controller;


use Think\Controller;
use Think\Verify;

class AccountController extends Controller
{
    public function index()
    {
        $code = $_GET['code'];
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . C("APPID")
            . "&secret=" . C("APPSECRET") . "&code=$code&grant_type=authorization_code";
        $content = file_get_contents($url);
        $content = json_decode($content, true);

        // 添加获取记录
        $access_token_history = M('wx_access_token_history');
        $access_token_history->access_token = $content['access_token'];
        $access_token_history->code = $code;
        $access_token_history->expires_in = time() + 7200;
        $access_token_history->refresh_token = $content['refresh_token'];
        $access_token_history->scope = C('SCOPE');
        $access_token_history->unionid = $content['unionid'];
        $access_token_history->openid = $content['openid'];
        $access_token_history->add();

        $openid = $content['openid'];
        $_SESSION['openid'] = $openid;
        $map['openid'] = array('eq', $openid);
        $user = M('user')->where($map)->select();
        $uid = 0;
        if (!$user) {
            // 存在微信用户
            $user = M('user');
            $user->nickname = '';
            $user->openid = $openid;
            $user->password = "";
            $uid = $user->add();
        } else {
            $uid = $user[0]['id'];
        }
        $_SESSION['uid'] = $uid;

        $returnUrl = $_SESSION['returnURL'];
        if ($returnUrl) {
            header("Location:$returnUrl");
            die;
        }

        $this->redirect('Member/index');

    }

    public function rules()
    {
        $this->display();
    }

    public function get_openid()
    {
        $redirect_uri = SERVER_HOST . '/index.php?s=/Home/Account/index';
        $redirect_uri = urlencode($redirect_uri);
        $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=" . C('APPID')
            . "&redirect_uri=$redirect_uri"
            . "&response_type=code&scope=" . C('SCOPE')
            . "&state=STATE#wechat_redirect";
        header("Location:$url");


//        $openid = 84965;
//        $_SESSION['openid'] = $openid;
//        $map['openid'] = array('eq', $openid);
//        $user = M('user')->where($map)->select();
//        $uid = 0;
//        if (!$user) {
//            // 存在微信用户
//            $user = M('user');
//            $user->nickname = '';
//            $user->openid = $openid;
//            $user->password = "";
//            $uid = $user->add();
//        } else {
//            $uid = $user[0]['id'];
//        }
//        $_SESSION['uid'] = $uid;
//
//        $returnUrl = $_SESSION['returnURL'];
//        if ($returnUrl) {
//            header("Location:$returnUrl");
//            die;
//        }
//        $this->redirect('Member/index');
    }

    public function register()
    {
        $msg = I('get.msg');
        $this->assign('msg', $msg);
        if (IS_POST) {
            if (!$this->check_verify(I('post.code'))) {
                exit(json_encode(CODE_INVALID));
            }
            $uid = $_SESSION['uid'];
            $map = array();
            $map['uid'] = array('eq', $uid);
            $profile_count = M('profile')->where($map)->select();
            if ($profile_count) {
                $_SESSION['profile'] = $profile_count[0];
                exit(json_encode(REGISTERED));
            }
            $name = I('post.name');
            //$email = I('post.email');
            $mobile = I('post.mobile');
            if (!trim($name)) {
                exit(json_encode(NAME_IS_NULL));
            }
//            if (!trim($email)) {
//                exit(json_encode(EMAIL_IS_NULL));
//            }
//            $pattern = C('EMAIL_PATTERN');
//            if (!preg_match($pattern, $email)) {
//                exit(json_encode(EMAIL_INVALID));
//            }

            if (!trim($mobile)) {
                exit(json_encode(MOBILE_IS_NULL));
            }
            $pattern = C('MOBILE_PATTERN');
            if (!preg_match($pattern, $mobile)) {
                exit(json_encode(MOBILE_INVALID));
            }

            $trans = M('profile');
            $trans->startTrans();

            $profile = M('profile');
            $profile->uid = $uid;
            $profile->total_integral = C('REGISTER_INTEGRAL');
            $profile->exchange_integral = 0;
            $profile->remain_integral = C('REGISTER_INTEGRAL');
            $profile->mobile = $mobile;
//            $profile->email = $email;
            $profile->create_time = time();
            $profile->name = $name;
            $profile_result = $profile->add();

            // 增加积分明细
            $detail = M('integral_detail');
            $detail->integral = C('REGISTER_INTEGRAL');
            $detail->create_time = time();
            $detail->uid = $uid;
            $detail->category = C('INTEGRAL_CATEGORY_REGISTER');
            $detail->description = '注册会员赠送【' . C('REGISTER_INTEGRAL') . '】积分';
            $detail_result = $detail->add();

            if ($profile_result
                && $detail_result
            ) {
                $trans->commit();
                $_SESSION['profile'] = M('profile')->find($profile_result);
                exit(json_encode(SUCCESS));
            } else {
                $trans->callback();
                exit(json_encode(FAIL));
            }

        }
        $this->display();
    }

    public function verify()
    {
        $config = array(
            'fontSize'  => C('VERIFY_FONT_SIZE'),
            'length'    => C('VERIFY_LENGTH'),
            'userNoise' => C('VERIFY_USE_NOISE'),
        );
        $Verify = new Verify($config);
        $Verify->entry();
    }

    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    function check_verify($code, $id = '')
    {
        $verify = new Verify();

        return $verify->check($code, $id);
    }

}
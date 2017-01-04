<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015.12.2
 * Time: 10:11
 */

namespace Admin\Controller;

use Think\Controller;
use Think\Verify;

class AccountController extends Controller
{
    public function register()
    {
        if ($_POST) {
            $user = M('user');
            $user->startTrans();
            $user = $user->where($_POST)->select();
            if ($user) {
                dump("email has registered.");
                die();
            }


            $user = M('auth_user');
            $user->username = $_POST['email'];
            $user->email = $_POST['email'];
            $password = md5($_POST['email']);
            $user->password = $password;
            $user->is_superuser = 0;
            $user->first_name = '';
            $user->last_name = '';
            $user->is_staff = 1;
            $user->is_active = 0;
            $user->date_joined = date('Y-m-d H:i:s');
            $user_id = $user->add();
            $profile = M('profile');
            $profile->pic = '/Public/img/uploads/default.png';
            $profile->reputation = 0;
            $profile->user_id = $user_id;
            $profile->add();
            //$result = sendMail($_POST[ 'email' ], 'Welcom to qa!', '您的初始密码就是您的邮箱，请登录后及时修改密码！');

            $user->commit();
            //dump($result);
            $this->redirect("/");
            die();
        }
        $this->display();
    }

    public function login()
    {
        if ($_POST) {
            if (!$this->check_verify(I('post.code'))) {
                $this->error('验证码错误');
                die;
            }
            // 根据用户名获取用户信息
            $map['name'] = array('eq', I('post.name'));
            $user = M('admin')->where($map)->select();
            if ($user) {
                // 进行密码校验
                $password = md5(I('post.password'));
                if ($user[0]['password'] == $password) {
                    $_SESSION['admin'] = $user;
                    $this->redirect('Index/index');
                } else {
                    $this->error('密码错误！');
                    die;
                }
            } else {
                $this->error('用户名不存在');
                die;
            }

        }
        $this->display();
    }

    public function logout()
    {
        session(null);
        session_destroy();
        $this->redirect('login');
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
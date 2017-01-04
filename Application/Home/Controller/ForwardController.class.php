<?php
// +----------------------------------------------------------------------
// | Joinusad  ForwardController.class.php
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.23 14:03
// +----------------------------------------------------------------------

namespace Home\Controller;


use Think\Controller;

class ForwardController extends Controller
{
    public function index()
    {
        if (!isset($_SESSION['openid'])) {
            $url = 'index.php?s=/' . MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME;
            $_SESSION['returnURL'] = $url;
            $this->redirect('Account/get_openid');
            die;
        }
        $map['uid'] = array('eq', $_SESSION['uid']);
        $profile = M('profile')->where($map)->select();
        if ($profile) {
            $_SESSION['profile'] = $profile[0];
        }
        if (!isset($_SESSION['profile'])) {
            $this->redirect('Account/register', array('msg' => '尚未注册'));
            die;
        }

        $jssdk = new JSSDK(C("APPID"), C("APPSECRET"));
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', json_encode($signPackage));
        $map['can_forward'] = array('eq', 1);
        $map['status'] = array('eq', 0);
        $data = M('article')->where($map)->select();
        M('article')->where($map)->setInc('reading', 1);
        if (count($data) > 0) {
            $data = $data[0];
            $data['create_time'] = time_to_date(strtotime($data['create_time']));
        }
        $this->assign('tongji_share', 1);
        $this->assign('data', $data);
        $this->display();
    }

    /**
     * 统计分享
     */
    public function share()
    {
        $aid = intval(I('get.id'));
        $uid = intval($_SESSION['uid']);
        $forward = M('forward');
        $forward->aid = $aid;
        $forward->uid = $uid;
        $forward->create_time = date('Y-m-d H:i:s');
        $forward->add();

        $week = date('w');
        $weeks['mon'] = date('Y-m-d 00:00:00', strtotime('+' . 1 - $week . ' days'));
        $start = date('Y-m-d 00:00:00');
        $end = date('Y-m-d 23:59:59');
        $map = array();
        $map['uid'] = array('eq', $uid);
        $map['create_time'] = array('between', array($start, $end));

        $sql = "";
        $sql .= " SELECT count(aid) cou ";
        $sql .= " from forward ";
        $sql .= " where uid = " . $uid;
        $sql .= " and aid = " . $aid;
        $sql .= " and create_time > '" . $start . "'";
        $sql .= " and create_time < '" . $end . "' ";
        debug_log($sql);
        $model = M();
        $result = $model->query($sql);
        debug_log($result);
        $count = 0;
        if (count($result) == 1) {
            $count = $result[0]['cou'];
        }
        // 分享次数达标 送积分
        if ($count == C('FORWARD_COUNT')) {
//            $map = array();
//            $map['uid'] = array('eq', $uid);
//            $map['create_time'] = array('between', array(strtotime($start), strtotime($end)));
//            $map['category'] = array('eq', 4);
//
//            $count = M('integral_detail')->where($map)->count();
//            // 已经赠送过积分
//            if ($count > 0) {
//                return;
//            }
            $map = array();
            $map['uid'] = array('eq', $_SESSION['uid']);
            M('profile')->where($map)->setInc('total_integral', C('FORWARD_INTEGRAL'));
            M('profile')->where($map)->setInc('remain_integral', C('FORWARD_INTEGRAL'));
            // 增加积分明细
            $detail = M('integral_detail');
            $detail->integral = C('FORWARD_INTEGRAL');
            $detail->create_time = time();
            $detail->uid = $uid;
            $detail->category = C('INTEGRAL_CATEGORY_FORWARD');
            $detail->description = '转发赠送【' . C('FORWARD_INTEGRAL') . '】积分';
            $detail->add();
        }
    }

    public function details()
    {
        $jssdk = new JSSDK(C("APPID"), C("APPSECRET"));
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('signPackage', json_encode($signPackage));
        $id = intval(I('get.id'));
        $map['id'] = array('eq', $id);
        $data = M('article')->where($map)->select();
        M('article')->where($map)->setInc('reading', 1);
        if (count($data) > 0) {
            $data = $data[0];
        }
        $this->assign('tongji_share', 0);
        $this->assign('data', $data);
        $this->display('index');
    }
}

class JSSDK
{
    private $appId;
    private $appSecret;

    public function __construct($appId, $appSecret)
    {
        $this->appId = $appId;
        $this->appSecret = $appSecret;
    }

    public function getSignPackage()
    {
        $jsapiTicket = $this->getJsApiTicket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId"     => $this->appId,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string,
        );

        return $signPackage;
    }

    private function createNonceStr($length = 16)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }

        return $str;
    }

    private function getJsApiTicket()
    {
        // jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("jsapi_ticket.php"));
        if ($data->expire_time < time()) {
            $accessToken = $this->getAccessToken();
            // 如果是企业号用以下 URL 获取 ticket
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
            $res = json_decode($this->httpGet($url));
            $ticket = $res->ticket;
            if ($ticket) {
                $data->expire_time = time() + 7000;
                $data->jsapi_ticket = $ticket;
                $this->set_php_file("jsapi_ticket.php", json_encode($data));
            }
        } else {
            $ticket = $data->jsapi_ticket;
        }

        return $ticket;
    }

    private function getAccessToken()
    {
        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
        $data = json_decode($this->get_php_file("access_token.php"));
        if ($data->expire_time < time()) {
            // 如果是企业号用以下URL获取access_token
            // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
            $res = json_decode($this->httpGet($url));
            $access_token = $res->access_token;
            if ($access_token) {
                $data->expire_time = time() + 7000;
                $data->access_token = $access_token;
                $this->set_php_file("access_token.php", json_encode($data));
            }
        } else {
            $access_token = $data->access_token;
        }

        return $access_token;
    }

    private function httpGet($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);
        curl_close($curl);

        return $res;
    }

    private function get_php_file($filename)
    {
        return trim(substr(file_get_contents($filename), 15));
    }

    private function set_php_file($filename, $content)
    {
        $fp = fopen($filename, "w");
        fwrite($fp, "<?php exit();?>" . $content);
        fclose($fp);
    }
}
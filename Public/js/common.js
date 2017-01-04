// +----------------------------------------------------------------------
// | Joinusad  
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.10 14:56
// +----------------------------------------------------------------------

var common = {
    //验证必填项是否为空
    isNullIpt: function (ipt, message) {
        var marke = false;
        if ($(ipt)) {
            if (undefined == $(ipt).val() || $(ipt).val() == "" || $(ipt).val() == message) {
                alert(message);
                marke = true;
            }
            return marke;
        }
    },

    // 显示提示窗口，并进行跳转
    showTips: function (msg, url) {
        var $toast = $('#toast');
        if (msg != undefined && msg != "") {
            $('.weui-toast__content').html(msg);
        }
        if ($toast.css('display') != 'none') return;
        $toast.fadeIn(100);
        setTimeout(function () {
            $toast.fadeOut(100);
            if (undefined != url) {
                window.location.href = url;
            }
        }, 2000);
    },
    validMobile: function (mobile) {
        return mobile.match(/^(\+86)|(86)?1[3,5,8,7]{1}[0-9]{1}[0-9]{8}$/);
    }
};



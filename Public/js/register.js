// +----------------------------------------------------------------------
// | Joinusad  
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.10 14:56
// +----------------------------------------------------------------------

$(function () {
    $('#showTooltips').click(function (event) {
        if (common.isNullIpt('input[name="name"]', '请填写姓名')) {
            return;
        }
        if (common.isNullIpt('input[name="mobile"]', '请输入手机号')) {
            return;
        } else {
            if (!common.validMobile($('input[name="mobile"]').val())) {
                alert('手机号格式不正确');
                return;
            }
        }
        if (common.isNullIpt('input[name="code"]', '请填验证码')) {
            return;
        }
        var name = $('input[name="name"]').val();
        var mobile = $('input[name="mobile"]').val();
        var code = $('input[name="code"]').val();
        $.ajax({
            url: 'index.php?s=/Home/Account/register',
            type: 'POST',
            dataType: 'json',
            data: {
                name: name,
                mobile: mobile,
                code: code
            },
            success: function (data) {
                if (data == 10008) {
                    alert('验证码错误');
                }
                if (data == 10005) {
                    common.showTips('注册成功', 'index.php?s=/Home/Member/index');
                }
                if (data == 10007) {
                    common.showTips('您已注册', 'index.php?s=/Home/Member/index');
                }
            },
            error: function () {
            },
        })
    });
});



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


    $('#save').click(function (event) {
        if (common.isNullIpt('input[name="name"]', '请填写姓名')) {
            return;
        }

        var name = $('input[name="name"]').val();
        $.ajax({
            url: 'index.php?s=/Home/Member/my_info',
            type: 'POST',
            dataType: 'json',
            data: {
                name: name,
            },
            success: function (data) {
                if (data == 10005) {
                    common.showTips('保存成功', 'index.php?s=/Home/Member/index');
                } else {
                    alert('保存失败');
                }

            },
            error: function () {
            },
        });
    });
})



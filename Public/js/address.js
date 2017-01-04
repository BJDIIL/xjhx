// +----------------------------------------------------------------------
// | Joinusad  
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.joinusad.com/ All rights reserved.
// +----------------------------------------------------------------------
// | Author: xiaobudian <849351660@qq.com>
// +----------------------------------------------------------------------
// | CreateTime: 2016.12.19 22:23
// +----------------------------------------------------------------------
function is_default() {
    if ($('#is_default').is(':checked')) {
        $('input[name=is_default]').val(1);
    } else {
        $('input[name=is_default]').val(0);
    }

}
$(function () {


    $('#save').click(function (event) {
        if (common.isNullIpt('input[name="name"]', '请填写收货人姓名')) {
            return;
        }

        if (!common.validMobile($('input[name="mobile"]').val())) {
            alert('请填写有效的手机号');
            return;
        }

        var code = $('#city-picker').attr('data-codes');
        if (undefined == code || code == '') {
            alert('请选择地区');
            return;
        }

        var details = $('textarea[name="details"]').val();
        if (undefined == details || details == '') {
            alert('请填写详细地址');
            return;
        }

        var name = $('input[name="name"]').val();
        var mobile = $('input[name="mobile"]').val();
        var data_codes = $('#city-picker').attr('data-codes');

        var is_default = $('input[name="is_default"]').val();
        var id = $('input[name="id"]').val();
        $.ajax({
            url: 'index.php?s=/Home/Address/add',
            type: 'POST',
            dataType: 'json',
            data: {
                id: id,
                name: name,
                mobile: mobile,
                data_codes: data_codes,
                details: details,
                is_default: is_default
            },
            success: function (data) {
                if (data == 10005) {
                    common.showTips('保存成功', 'index.php?s=/Home/Address/index');
                } else {
                    alert("保存失败");
                }

            },
            error: function () {
            },
        })
    });
})
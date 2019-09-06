
<?php

var_dump($data);

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Title</title>
    <link rel="stylesheet" href="/vendor/layui/css/layui.css">
    <link rel="stylesheet" href="/vendor/assets/sass/base.css">
    <link rel="stylesheet" href="/vendor/assets/sass/index.css">
    <script src="/vendor/layui/layui.js"></script>
    <script type='text/javascript' src='http://dev-uc.ipktv.com/youCS/static/jquery-3.0.0.min.js'></script>
</head>
<body>
<div class="container">
    <table id="storeHouseCreate" lay-filter="storeHouseCreate"></table>

</div>
<script>

    $(document).ready(function () {
        // tableInit();
    })
    //执行渲染
    // function tableInit() {
    layui.use('table', function () {
        var table = layui.table;
        // var tableWidth = layui.$('#storeHouseCreate').width();
        //第一个实例
        table.render({
            elem: '#storeHouseCreate'
            , id: 'storeHouseCreate'
            , height: 500
            , limit: 3
            , method: 'post'
            , url: 'http://dev-uc.ipktv.com/ybPfAdmin/rm/list' //数据接口
            , headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                // "contentType": "application/json;charset=utf-8"
            }
            ,where: {json:JSON.stringify({
                    "header": {
                        "data_type": "proxy",
                        "data_direction": "request",
                        "server": "vod_http_server",
                        "id": "vod_http_server"
                    },
                    "request": {
                        "function": "1001",
                        "limit": '10',
                        "version": "1.0",
                        "clientFunc": "immediateIndex",
                        "token": "0961e78320cb0634d6838b484040f953",
                        "order_id": "D613305345919076",
                        "ahead_user_id": "139569152"
                    },
                    "comment": ""
                })}
            , parseData: function (res) { //res 即为原始返回的数据
                return {
                    "code": res.status, //解析接口状态
                    // "msg": res.message, //解析提示文本
                    "count": res.WarehouseList.total, //解析数据长度
                    "data": '<?php json_encode($data);?>' //解析数据列表
                };
            }
            , page: true //开启分页
            // , toolbar: true
            // , defaultToolbar: ['filter', 'exports']
            , cols: [[ //表头
                {checkbox: true}
                , {field: 'id', title: '编号', align: 'center', width: 60}
                , {
                    field: 'name', title: '仓名称', width: 190, templet: function (d) {
                        return '<div class="yc-table-cell" title="' + d.name + '">' + d.name + '</div>'
                    }
                }
                , {field: 'barn_id', title: '仓ID', align: 'center', width: 80}
                , {
                    field: 'province', title: '省', align: 'center', width: 100, templet: function (d) {
                        if (d.binding_barn_id) {
                            return d.province
                        } else {
                            return '<div><select class="table-select province-select"  name="province" lay-verify="required" lay-search data-value="' + d.province + '" >' +
                                '<option value=""></option>' +
                                '<option value="18000">北京</option>' +
                                '<option value="20000">上海</option>' +
                                '<option value="20001">广州</option>' +
                                '<option value="20002">深圳</option>' +
                                '<option value="20003">杭州</option>' +
                                '</select></div>'
                        }
                    }
                }
                , {
                    field: 'city', title: '市', align: 'center', width: 100, templet: function (d) {
                        if (d.binding_barn_id) {
                            return d.city
                        } else {
                            return '<select class="table-select" name="city" lay-filter="testSelect" lay-verify="required" data-value="' + d.city + '" >' +
                                '<option value=""></option>' +
                                '<option value="18000">北京</option>' +
                                '<option value="20000">上海</option>' +
                                '<option value="20001">广州</option>' +
                                '<option value="20002">深圳</option>' +
                                '<option value="20003">杭州</option>' +
                                '</select>'
                        }
                    }
                }
                , {
                    field: 'zone', title: '区', align: 'center', width: 100, templet: function (d) {
                        if (d.binding_barn_id) {
                            return d.zone
                        } else {
                            return '<select class="table-select" name="city" lay-filter="testSelect" lay-verify="required" data-value="' + d.city + '" >' +
                                '<option value=""></option>' +
                                '<option value="18000">北京</option>' +
                                '<option value="20000">上海</option>' +
                                '<option value="20001">广州</option>' +
                                '<option value="20002">深圳</option>' +
                                '<option value="20003">杭州</option>' +
                                '</select>'
                        }
                    }
                }
                , {
                    field: 'address', title: '详细地址', width: 190, templet: function (d) {
                        if (d.binding_barn_id) {
                            return d.address
                        } else {
                            return '<input class="table-input address-input" type="text" value="' + d.address + '"/>'
                        }
                    }
                }
                , {
                    field: 'contact_name', title: '联系人名称', align: 'center', width: 100, templet: function (d) {
                        if (d.binding_barn_id) {
                            return d.contact_name
                        } else {
                            return '<input class="table-input name-input" type="text" value="' + d.contact_name + '"/>'
                        }
                    }
                }
                , {
                    field: 'contact_phone', title: '联系电话', width: 120, templet: function (d) {
                        if (d.binding_barn_id) {
                            return d.contact_phone
                        } else {
                            return '<input class="table-input phone-input" type="text" value="' + d.contact_phone + '"/>'
                        }
                    }
                }
                , {
                    field: 'binding_barn_id', title: '天猫仓ID', align: 'center', width: 100, templet: function (d) {
                        if (d.binding_barn_id) {
                            return d.binding_barn_id
                        } else {
                            return '<span class="table-span">暂无</span>'
                        }
                    }
                }
                , {fixed: 'right', title: '操作', width: 100, align: 'center', toolbar: '#toolTpl'} //这里的toolbar值是模板元素的选择器
            ]]
            , done: function (res, curr, count) {
                // $('tr').css({'background-color': '#009688', 'color': '#fff'});
            }
        });

        //监听工具条
        table.on('tool(storeHouseCreate)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var data = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var tr = obj.tr; //获得当前行 tr 的DOM对象

            if (layEvent === 'created') { //查看
                //do somehing
                console.log(data)
                console.log($(tr));
                console.log($(tr).find(".address-input").val());
            }
        });

    });

    $(document).on("click", ".table-select+.layui-form-select", function () {
        var num = Number($(this).parents('tr').attr('data-index')) + 2;
        var height = Number($(this).parents('tr').height()) * num + 30;
        $('.layui-form-select dl').css('top', height);
        var tds = Number($(this).parents('td').index());
        var width = 0;
        for (var i = 0; i < tds; i++) {
            width += Number($(this).parents('tr').find("td").eq(i).width());
        }
        width = width + 45;
        $('.layui-form-select dl').css('left', width);
    })
</script>
<script type="text/html" id="toolTpl">
{{--    @if(d.binding_barn_id)
    <a class="table-btn disable">已创建</a>
    @else
    <a class="table-btn" lay-event="created">创建</a>
    @endif--}}
</script>
</body>
</html>
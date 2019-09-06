

<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<link rel="stylesheet" href="/vendor/assets/sass/optimizeSelectOption.css">
<link rel="stylesheet" href="/vendor/assets/sass/base.css">
<link rel="stylesheet" href="/vendor/assets/sass/index.css">
<script src="/vendor/layui/layui.js"></script>
<script src="/vendor/assets/js/optimizeSelectOption.js"></script>


<div class="container">
    <div class="table-operation-row">
        <div class="total-title">商场信息<span class="total-wrapper">共<span class="total"></span>条</span></div>
        <div class="table-input-wrapper">
            <input type="text" name="title" required lay-verify="required" placeholder="请输入商场名或id"
                   autocomplete="off" class="layui-input table-input table-search-input">
        </div>
        <div class="table-top-btn search-btn" title="查询">查询</div>
        <div class="table-top-btn export-btn" title="导出">导出</div>
    </div>
    <table id="storeHouseCreate" lay-filter="storeHouseCreate"></table>

</div>
<script>
    var provinceCommon;
    // var ;
    $(document).ready(function () {
        tableInit();
        getCommonProvince();
        $(".export-btn").on('click',function () {

        })
    })
    //执行渲染
    function tableInit() {
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
                // , headers: {
                //     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                //     // "contentType": "application/json;charset=utf-8"
                // }
                , where: {
                    json: JSON.stringify({
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1004",
                            "limit": '10',
                            "version": "1.0",
                            "type":"",
                            "market_id":"",
                        },
                        "comment": ""
                    })
                }
                , parseData: function (res) { //res 即为原始返回的数据
                    return {
                        "code": res.status, //解析接口状态
                        // "msg": res.message, //解析提示文本
                        "count": res.data.total, //解析数据长度
                        "data": res.data.data //解析数据列表
                    };
                }
                , page: true //开启分页
                // , toolbar: true
                // , defaultToolbar: ['exports']
                , cols: [[ //表头
                    {checkbox: true}
                    , {field: 'id', title: '编号', align: 'center', width: 60}
                    , {
                        field: 'name', title: '商场名称', width: 190, templet: function (d) {
                            return '<div class="yc-table-cell" title="' + d.name + '">' + d.name + '<\/div>'
                        }
                    }
                    , {field: 'barn_id', title: '仓ID', align: 'center', width: 80}
                    , {
                        field: 'province', title: '省', align: 'center', width: 120, templet: function (d) {
                            if (d.binding_barn_id) {
                                return d.province
                            } else {
                                if(d.province) {
                                    var ele = '.province-select'+d.id;
                                    setTimeout(function () {
                                        $(ele).val(d.province);
                                        layui.form.render();
                                    },50)
                                }
                                return '<select class="table-select province-select province-select'+d.id+'"  name="province" lay-filter="province" lay-verify="required" lay-search value="' + d.province + '" >' +
                                    provinceCommon+
                                    '<\/select>'
                            }
                        }
                    }
                    , {
                        field: 'city', title: '市', align: 'center', width: 120, templet: function (d) {
                            if (d.binding_barn_id) {
                                return d.city
                            } else {
                                if(d.province) {
                                    setTimeout(function () {
                                        var ele = '.city-select'+d.id;
                                        getProvince(d.province, $(ele),d.city);
                                    },50)
                                }
                                return '<select class="table-select city-select'+d.id+'" name="city" lay-filter="city" lay-verify="required" lay-search value="' + d.city + '" >' +
                                    '<\/select>'
                            }
                        }
                    }
                    , {
                        field: 'zone', title: '区', align: 'center', width: 120, templet: function (d) {
                            if (d.binding_barn_id) {
                                return d.zone
                            } else {
                                if(d.city) {
                                    setTimeout(function () {
                                        var ele = '.zone-select'+d.id;
                                        getProvince(d.city, $(ele),d.zone);
                                    },50)
                                }
                                return '<select class="table-select zone-select'+d.id+'" name="zone" lay-filter="zone" lay-verify="required" lay-search value="' + d.zone + '" >' +
                                    '<\/select>'
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
                    console.log(res);
                    $(".total").html(res.count);
                    layui.form.on('select(province)', function (data) {
                        console.log(data);
                        $(data.elem).closest("tr").find("select[name='city']").html('');
                        $(data.elem).closest("tr").find("select[name='zone']").html('');
                        getProvince(data.value, $(data.elem).closest("tr").find("select[name='city']"));
                    });
                    layui.form.on('select(city)', function (data) {
                        console.log(data);
                        $(data.elem).closest("tr").find("select[name='zone']").html('');
                        getProvince(data.value, $(data.elem).closest("tr").find("select[name='zone']"));
                    });
                    $(".export-btn").on('click',function () {
                        table.exportFile('storeHouseCreate','','xls'); //data 为该实例中的任意数量的数据
                    })
                }
            });

            //监听工具条
            table.on('tool(storeHouseCreate)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
                var data = obj.data; //获得当前行数据
                var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                var tr = obj.tr; //获得当前行 tr 的DOM对象

                if (layEvent === 'created') { //查看
                    console.log(data)
                    console.log($(tr));
                    var province = $(tr).find("select[name='province']").val(),
                        city = $(tr).find("select[name='city']").val(),
                        zone = $(tr).find("select[name='zone']").val(),
                        address = $(tr).find(".address-input").val(),
                        contact_name = $(tr).find(".name-input").val(),
                        contact_phone = $(tr).find(".phone-input").val();
                    createStoreHouse(data.id,province,city,zone,address,contact_name,contact_phone)
                }
            });

        });
    }


    function getProvince(parent_id, ele,defaultVal) {
        var data = {
            "header": {
                "data_type": "proxy",
                "data_direction": "request",
                "server": "vod_http_server",
                "id": "vod_http_server"
            },
            "request": {
                "function": "1003",
                "parent_id": parent_id
            },
            "comment": ""
        };
        $.ajax({
            url: "http://dev-uc.ipktv.com/ybPfAdmin/rm/list",
            type: 'POST',
            dataType: 'json',
            data: {json: JSON.stringify(data)},
            success: function (res) {
                if (res.status == 0) {
                    var html = '<option value="">请选择</option>';
                    $.each(res.data, function (index, item) {
                        html += '<option value="' + item.id + '">' + item.name + '</option>'
                    });
                    $(ele).html("");
                    $(ele).append(html);
                    if(defaultVal) {
                        $(ele).val(defaultVal);
                    }
                    layui.form.render();
                } else {

                }
            },
            error: function (response) {

            }
        });
    }

    function getCommonProvince() {
        var data = {
            "header": {
                "data_type": "proxy",
                "data_direction": "request",
                "server": "vod_http_server",
                "id": "vod_http_server"
            },
            "request": {
                "function": "1003",
                "parent_id": 1
            },
            "comment": ""
        };
        $.ajax({
            url: "http://dev-uc.ipktv.com/ybPfAdmin/rm/list",
            type: 'POST',
            dataType: 'json',
            data: {json: JSON.stringify(data)},
            success: function (res) {
                if (res.status == 0) {
                    var html = '<option value="">请选择</option>';
                    $.each(res.data, function (index, item) {
                        html += '<option value="' + item.id + '">' + item.name + '</option>'
                    });
                    provinceCommon = html;
                    console.log(provinceCommon);
                } else {

                }
            },
            error: function (response) {

            }
        });
    }

    function createStoreHouse(id,province, city, zone, address, contact_name, contact_phone) {
        var data = {
            "header": {
                "data_type": "proxy",
                "data_direction": "request",
                "server": "vod_http_server",
                "id": "vod_http_server"
            },
            "request": {
                "function": "1002",
                "id": id,
                "contact_name": contact_name,
                "contact_phone": contact_phone,
                "province": province,
                "city": city,
                "zone": zone,
                "address": address
            },
            "comment": ""
        };
        $.ajax({
            url: "http://dev-uc.ipktv.com/ybPfAdmin/rm/list",
            type: 'POST',
            dataType: 'json',
            data: {json: JSON.stringify(data)},
            success: function (res) {
                if (res.status == 0) {
                    layui.table.reload('storeHouseCreate', {
                        where: {
                            json: JSON.stringify({
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
                            })
                        }
                        // ,page: {
                        //     curr: 1 //重新从第 1 页开始
                        // }
                    }); //只重载数据
                } else {

                }
            },
            error: function (response) {

            }
        });
    }
</script>
<script type="text/html" id="toolTpl">
    {{#  if(d.binding_barn_id){ }}
    <a class="table-btn disable">已创建</a>
    {{#  } else { }}
    <a class="table-btn" lay-event="created">创建</a>
    {{#  } }}
</script>

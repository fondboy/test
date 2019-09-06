<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<link rel="stylesheet" href="/vendor/assets/sass/optimizeSelectOption.css">
<link rel="stylesheet" href="/vendor/assets/sass/base.css">
<link rel="stylesheet" href="/vendor/assets/sass/index.css">
<script src="/vendor/layui/layui.js"></script>
<script src="/vendor/assets/js/optimizeSelectOption.js"></script>

<div class="container" style="width:96%">

    <div class="table-operation-row">
        <div class="total-title">仓库信息<span class="total-wrapper">共<span class="total"></span>条</span></div>
        <div class="table-input-wrapper">
            <form onsubmit="getStoreHouseList(event)">
                <input type="text" name="title" lay-verify="required" placeholder="请输入仓库名称或者仓库ID"
                       autocomplete="off" class="layui-input table-input table-search-input">
            </form>
        </div>
        <div class="table-top-select">
            <form class="layui-form">
                <select class="table-select" name="typeSelect" lay-filter="typeSelect" lay-verify="required">
                    <option value="0">全部</option>
                    <option value="3">已创建</option>
                    <option value="4">未创建</option>
                </select>
            </form>
        </div>

        <div class="table-top-btn export-btn" title="导出">导出</div>
    </div>
    <table id="storeHouseCreate" lay-filter="storeHouseCreate"></table>

</div>
<script>
    var provinceCommon;
    var typeSelect;
    layui.use(['form', 'layer'], function () {
        var form = layui.form;
        form.render('select');
        form.on('select(typeSelect)', function (data) {
            console.log(data);
            typeSelect = data.value;
            var val = $(".table-search-input").val();
            tableInit(val, typeSelect);
        });
    })

    $(document).ready(function () {
        getCommonProvince();
        tableInit();
    })


    function getStoreHouseList(event) {
        event.preventDefault();
        var val = $(".table-search-input").val();
        tableInit(val, typeSelect);
    }


    //执行渲染
    function tableInit(barn_id, type) {
        layui.use(['table', 'layer'], function () {
            var table = layui.table;
            var layer = layui.layer;
            var storeHouse = table.render({
                elem: '#storeHouseCreate'
                , id: 'storeHouseCreate'
                , limit: 10
                , height: 480
                , method: 'post'
                , url: '<?php echo $request_url;?>' //数据接口
                , where: {
                    json: JSON.stringify({
                            "header": {
                                "data_type": "proxy",
                                "data_direction": "request",
                                "server": "vod_http_server",
                                "id": "vod_http_server"
                            },
                            "request": {
                                "function": "1001",
                                "barn_id": barn_id || '',
                                "type": type || ''
                            },
                            "comment": ""
                        }
                    )
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
                , cols: [[ //表头
                    // {checkbox: true}
                    // {field: 'id', title: '编号', align: 'center', width: 80}
                    {
                        field: 'org5_id', title: '仓ID', align: 'center', width: 80
                        //     ,templet:function(d){
                        //     return '<span class="tipsWrapper">'+d.org5_id+'<span class="tip'+d.org5_id+' tips" style="display:none"><\/span><\/span>';
                        // }
                    }
                    ,
                    {
                        field: 'name', title: '仓名称', width: 120, templet: function (d) {
                            var ele = '.barn-name-' + d.id;
                            if (d.delete_mark == 1) {
                                // setTimeout(function () {
                                //     layer.tips('该信息VMS已变更！', ele, {
                                //         tips: [3, '#404040'],
                                //         tipsMore: true,
                                //         time: 2000
                                //     });
                                // },0);
                                // setTimeout(function () {
                                //     var ycTip = '<div class="yc-tips-wrapper"><div class="yc-tip-content" style="width: 115px">该信息VMS已变更<i class="yc-tip-triangle"><\/i><\/div><\/div>';
                                //     $(ele).append(ycTip);
                                //     setTimeout(function () {
                                //         $(ele).find('.yc-tips-wrapper').hide();
                                //     },3000)
                                // },0)
                            }
                            return '<div class="yc-table-cell barn-name barn-name-' + d.id + '" title="' + d.name + '">' + d.name + '<\/div>'
                        }
                    }
                    , {
                        field: 'province', title: '省', align: 'center', width: 100, templet: function (d) {
                            if (d.province) {
                                return d.province.name
                            } else {
                                if (d.province) {
                                    var ele = '.province-select' + d.id;
                                    setTimeout(function () {
                                        $(ele).val(d.province.id);
                                        layui.form.render();
                                    }, 50)
                                }
                                return '<select class="table-select province-select province-select' + d.id + '"  name="province" lay-filter="province" lay-verify="required" lay-search value="' + d.province + '" >' +
                                    provinceCommon +
                                    '<\/select>'
                            }
                        }
                    }
                    , {
                        field: 'city', title: '市', align: 'center', width: 100, templet: function (d) {
                            if (d.city) {
                                return d.city.name
                            } else {
                                if (d.city) {
                                    setTimeout(function () {
                                        var ele = '.city-select' + d.id;
                                        getProvince(d.province.id, $(ele), d.city.id);
                                    }, 50)
                                }
                                return '<select class="table-select city-select' + d.id + '" name="city" lay-filter="city" lay-verify="required" lay-search value="' + d.city + '" >' +
                                    '<\/select>'
                            }
                        }
                    }
                    , {
                        field: 'zone', title: '区', align: 'center', width: 120, templet: function (d) {
                            if(d.binding_barn_id){
                                return d.zone.name
                            }else{
                                if (d.city) {
                                    setTimeout(function () {
                                        var ele = '.zone-select' + d.id;
                                        getProvince(d.city.id, $(ele), d.zone.id,d.city.name);
                                    }, 50)
                                }
                                return '<select class="table-select zone-select' + d.id + '" name="zone" lay-filter="zone" lay-verify="required" lay-search value="' + d.zone + '" >' +
                                    '<\/select>';
                            }
                        }
                    }
                    , {
                        field: 'address', title: '详细地址', width: 200, templet: function (d) {
                            if (d.binding_barn_id) {
                                return '<div class="yc-table-cell" title="' + d.address + '">' + d.address + '<\/div>'
                            } else {
                                return '<input class="table-input address-input" type="text" value="' + d.address + '"/>'
                            }
                        }
                    }
                    , {
                        field: 'contact_name', title: '联系人', align: 'center', templet: function (d) {
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
                        field: 'binding_barn_id', title: '天猫仓ID', align: 'center', templet: function (d) {
                            if (d.binding_barn_id) {
                                return d.binding_barn_id
                            } else {
                                return '<span class="table-span">暂无<\/span>'
                            }
                        }
                    },
                    {
                        field: 'ub_barn_id', title: '操作', width: 100, align: 'center',
                        templet: function (d) {
                            if (d.binding_barn_id) {
                                return '<a class="table-btn disable" lay-event="bind" >已创建<\/a>'
                            } else {
                                return ' <a class="table-btn" lay-event="created" >创建<\/a>';
                            }
                        }
                    }
                    // , {fixed: 'right', title: '操作',  align: 'center', toolbar: '#toolTpl'} //这里的toolbar值是模板元素的选择器
                ]]
                , done: function (res, curr, count) {
                    $(".total").html(res.count);
                    res.data.forEach(function (item, index) {
                        if (Number(item.delete_mark)) {
                            if (index == storeHouse.config.limit - 1) {
                                var ycTip = '<div class="yc-tips-wrapper yc-tips-top-wrapper"><div class="yc-tip-content" style="width: 115px">该信息VMS已变更<i class="yc-tip-triangle triangle-top"><\/i><\/div><\/div>';
                            } else if (res.data.length < storeHouse.config.limit && index == res.data.length - 1) {
                                var ycTip = '<div class="yc-tips-wrapper yc-tips-top-wrapper"><div class="yc-tip-content" style="width: 115px">该信息VMS已变更<i class="yc-tip-triangle triangle-top"><\/i><\/div><\/div>';
                            } else {
                                var ycTip = '<div class="yc-tips-wrapper"><div class="yc-tip-content" style="width: 115px">该信息VMS已变更<i class="yc-tip-triangle"><\/i><\/div><\/div>';
                            }
                            $("tr").eq(index + 1).find(".barn-name").append(ycTip);
                            setTimeout(function () {
                                $("tr").eq(index + 1).find('.yc-tips-wrapper').fadeOut();
                            }, 3000);
                            $("tr").eq(index + 1).addClass("delete");
                            //隐藏操作
                            $('tr').eq(index+1).find('td').find('.table-btn').hide();

                            // $("tr").eq(index+1).find(".tips").fadeIn();
                            // $("tr").eq(index+1).addClass("delete");
                            // $('tr').eq(index+1).find('td').eq(9).hide();
                            // setTimeout(function(){
                            //     $("tr").eq(index+1).find(".tips").fadeOut();
                            // },1000)
                        }
                    });
                    layui.form.on('select(province)', function (data) {
                        $(data.elem).closest("tr").find("select[name='city']").html('');
                        $(data.elem).closest("tr").find("select[name='zone']").html('');
                        getProvince(data.value, $(data.elem).closest("tr").find("select[name='city']"),'');
                    });
                    layui.form.on('select(city)', function (data) {
                        $(data.elem).closest("tr").find("select[name='zone']").html('');
                        getProvince(data.value, $(data.elem).closest("tr").find("select[name='zone']"),'');
                    });
                    $(".export-btn").unbind('click').bind('click', function () {
                        var val = encodeURI($(".table-search-input").val())|| 0;
                        var choosetype = type || "0";
                        window.location.href = '<?php echo $request_url;?>?json={"header": {"data_type": "proxy","data_direction": "request","server": "vod_http_server","id": "vod_http_server"},"request": {"function":"1001","excel":1,"barn_id":"'+val+'","type":'+choosetype+'},"comment": ""}&page='+curr+'&limit='+storeHouse.config.limit;
                        // exportExcel(curr, storeHouse.config.limit);
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
                    var province = obj.data.province?obj.data.province.id:$(tr).find("select[name='province']").val(),
                        city = obj.data.city?obj.data.city.id:$(tr).find("select[name='city']").val(),
                        zone = obj.data.zone?obj.data.zone.id:$(tr).find("select[name='zone']").val(),
                        address = $(tr).find(".address-input").val(),
                        contact_name = $(tr).find(".name-input").val(),
                        contact_phone = $(tr).find(".phone-input").val(),
                        barn_id = data.org5_id;
                    createStoreHouse(data.id, province, city, zone, address, contact_name, contact_phone, barn_id)
                }
            });

        });
    }


    function getProvince(parent_id, ele, defaultVal,nullReturn) {
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
            url: '<?php echo $request_url;?>',
            type: 'POST',
            dataType: 'json',
            data: {json: JSON.stringify(data)},
            success: function (res) {
                if (res.status == 0) {
                    var html = '<option value="">请选择<\/option>';
                    console.log(res.data.length);
                    if(res.data.length>0){
                        $.each(res.data, function (key, item) {
                            html += '<option value="' + res.data[key].id + '">' + res.data[key].name + '</option>'
                        });
                    }else{
                       if(nullReturn!=''){
                           $(ele).parent().parent().html(''); // 暂时处理成空
                       }
                        return ;
                    }


                    $(ele).html("");
                    $(ele).append(html);
                    if (defaultVal) {
                        $(ele).val(defaultVal);
                    }
                    layui.form.render();
                } else {
                    layer.msg(res.msg);
                }
            },
            error: function (response) {
                layer.msg('我们出错了');
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
            url: '<?php echo $request_url;?>',
            type: 'POST',
            dataType: 'json',
            data: {json: JSON.stringify(data)},
            success: function (res) {
                if (res.status == 0) {
                    var html = '<option value="">请选择<\/option>';
                    $.each(res.data, function (key, item) {
                        // console.log(key, res.data[key]);
                        html += '<option value="' + res.data[key].id + '">' + res.data[key].name + '</option>'
                    });
                    provinceCommon = html;
                    // console.log(provinceCommon);
                } else {
                    layer.msg(res.msg);
                }
            },
            error: function (response) {
                layer.msg('我们出错了');
            }
        });
    }

    function createStoreHouse(id, province, city, zone, address, contact_name, contact_phone, barn_id) {
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
                "address": address,
                "barn_id": barn_id
            },
            "comment": ""
        };
        $.ajax({
            url: '<?php echo $request_url;?>',
            type: 'POST',
            dataType: 'json',
            data: {json: JSON.stringify(data)},
            success: function (res) {
                if (res.status == 0) {
                    layer.msg('创建成功');
                    var val = $(".table-search-input").val();
                    tableInit(val, typeSelect);
                } else {
                    layer.msg(res.msg);
                }
            },
            error: function (response) {
                layer.msg('我们出错了');
            }
        });
    }

    function exportExcel(page, limit) {
        var val = $(".table-search-input").val();
        var data = {
            "header": {
                "data_type": "proxy",
                "data_direction": "request",
                "server": "vod_http_server",
                "id": "vod_http_server"
            },
            "request": {
                "function": "1001",
                "barn_id": val || '',
                "type": typeSelect || '',
                "excel": 1
            },
            "comment": ""
        };
        $.ajax({
            url: "<?php echo $request_url;?>",
            type: 'post',
            dataType: 'json',
            data: {json: JSON.stringify(data), page: page, limit: limit},
            success: function (res) {
                if (res.status == 0) {

                } else {
                    layer.msg(res.msg);
                }
            },
            error: function (response) {
                layer.msg('我们出错了');
            }
        });

    }
</script>


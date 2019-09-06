<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<link rel="stylesheet" href="/vendor/assets/sass/base.css">
<link rel="stylesheet" href="/vendor/assets/sass/index.css">
<link rel="stylesheet" href="/vendor/assets/sass/optimizeSelectOption.css">
<link rel="stylesheet" href="/vendor/assets/sass/createPoint.css">
<script src="/vendor/layui/layui.js"></script>
<script src="/vendor/assets/js/optimizeSelectOption.js"></script>


<div class="container">
    <div class="table-operation-row">
        <div class="total-title">点位信息<span class="total-wrapper">共<span class="total"></span>条</span></div>
        <div class="table-top-btn export-btn new-export" title="导出">导出</div>
        <form class="layui-form select-box">
            <select name="type" class="company-box" lay-filter="type">
                <option value="">全部</option>
                <option value="3">已创建</option>
                <option value="4">未创建</option>
            </select>
        </form>
        <div class="table-input-wrapper">
            <form onSubmit="reload()">
                <input type="text" name="title" placeholder="请输入点位名称" class="layui-input table-input table-search-input market_id" type="submit" autocomplete="off">
            </form>
        </div>
    </div>
    <table id="storeHouseCreate" lay-filter="storeHouseCreate"></table>
</div>
<script>
    var barnNameList = {},
        locationArr = [],
        clickFlag = false,
        nodeTypeArr = [];
    $(document).ready(function () {
        tableInit();
        layui.use('form', function(){
            var form = layui.form.render();
            form.on('select(type)', function(data){
                var market_id = $(".market_id").val();
                var type = data.value;
                tableInit(market_id,type);
            });
        });
    })
    //执行渲染
    function tableInit(market_id,type) {
        layui.use('table', function () {
            var table = layui.table;
            var pointTable = table.render({
                elem: '#storeHouseCreate'
                , id: 'storeHouseCreate'
                , limit: 20
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
                            "function": "1006",
                            "node_id": market_id,
                            "type": type
                        },
                        "comment": ""
                    })
                }
                , parseData: function (res) { //res 即为原始返回的数据
                    locationArr = res.data.location;
                    nodeTypeArr = res.data.node_type;
                    return {
                        "code": res.status, //解析接口状态
                        // "msg": res.message, //解析提示文本
                        "count": res.data.SiteList.total, //解析数据长度
                        "data": res.data.SiteList.data //解析数据列表
                    };
                }
                , page: true //开启分页
                , cols: [[ //表头
                    // {checkbox: true},
                    {field: 'id', title: '编号', align: 'center', width: "8%",totalRow:true,templet:function(d){
                            return '<span class="tipsWrapper">'+d.id+'<span class="tip'+d.id+' tips" style="display:none"></span></span>';
                        }},
                    {field: 'node_name', title: '点位名称', width:"15%",templet:function(d){
                            return'<p >'+d.node_name+'<\/p>'
                        }},
                    {field: 'node_type', title: '场地类型', align: 'center', width: "16%",
                        templet: function (d) {
                            var resultHtml = "";
                            if(d.tmall_node_id){
                                resultHtml = '<div class="barn-name yc-table-cell">'+d.node_type+'<\/div>';
                            }else{
                                var listHtml = renderSelect(nodeTypeArr);
                                resultHtml='<select class="table-select province-select province-select'+d.node_type+'"  name="nodeType" lay-filter="barnName" lay-verify="required" lay-search value="' + d.node_type + '" >'
                                    + listHtml+ '<\/select>'
                            }
                            return  resultHtml;
                        }
                    },
                    {field: 'location', title: '位置', align: 'center', width: "15%",
                        templet: function (d) {
                            var resultHtml = "";
                            if(d.tmall_node_id){
                                resultHtml = '<div class="barn-name yc-table-cell" data-barnId="'+d.location+'">'+d.location+'<\/div>';
                            }else{
                                var listHtml = renderSelect(locationArr);
                                resultHtml='<select class="table-select province-select province-select'+d.location+'"  name="location" lay-filter="location" lay-verify="required" lay-search value="' + d.location + '" >'
                                    + listHtml+ '<\/select>'
                            }
                            return  resultHtml;
                        }
                    },
                    {field: 'address', title: '业务归属', width:"12%", align: 'center',
                        templet: function (d) {
                            return '<div class="yc-table-cell">'+d.belong_to+'<\/div>'
                        }
                    },
                    {field: 'org5_id', title: '仓ID', width:"12%",align:"center"},
                    {field: 'address', title: '天猫点位ID', width:"12%",align:"center",
                        templet: function (d) {
                            if(d.tmall_node_id){
                                return d.tmall_node_id
                            }else{
                                return "暂无"
                            }

                        }
                    },
                    {field: 'ub_barn_id', title: '操作', width:"10%",align: 'center',fixed: 'right',
                        templet:function(d){
                            if(d.tmall_node_id){
                                return '<a class="table-btn bind_fail disable" lay-event="bind" >已创建<\/a>'
                            }else{
                                return ' <a class="table-btn success_btn" lay-event="create" >创建<\/a>';
                            }
                        }
                    },
                ]]
                , done: function (res, curr, count) {
                    $(".total").html(count);
                    $(".export-btn").on('click', function () {
                        var node_id = encodeURI($(".market_id").val());
                        var type = $(".select-box").find("select[name='type']").val();
                        var data ={
                            "header": {
                                "data_type": "proxy",
                                "data_direction": "request",
                                "server": "vod_http_server",
                                "id": "vod_http_server"
                            },
                            "request": {
                                "function": "1006",
                                "excel":1,
                                "node_id": node_id,
                                "type": type
                            },
                            "comment": ""
                        }
                        data = JSON.stringify(data)+'&page='+curr+'&limit='+pointTable.config.limit;
                        window.location.href = '<?php echo $request_url;?>?json='+data;
                    })
                    res.data.forEach(function(item ,index){
                        if(item.delete_mark == '1'){
                            $("tr").eq(index+1).find(".tips").fadeIn()
                            $("tr").eq(index+1).addClass("delete");
                            $('tr').eq(index+1).find('td').eq(7).html("")
                            setTimeout(function(){
                                $("tr").eq(index+1).find(".tips").fadeOut()
                            },1000)
                        }
                    })
                }
            });

            //监听工具条
            table.on('tool(storeHouseCreate)', function (obj) {
                var data = obj.data, //获得当前行数据
                    layEvent = obj.event, //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                    tr = obj.tr,//获得当前行 tr 的DOM对象
                    node_type= "",
                    location ="位置";
                if (!clickFlag && layEvent === 'create') {
                    clickFlag =true;
                    node_type=$(tr).find("select[name='nodeType']").val();
                    location =$(tr).find("select[name='location']").val();
                    if(!node_type){
                        layer.msg("请选择场地类型");
                        return false;
                    }
                    if(!location){
                        layer.msg("请选择位置");
                        return false;
                    }
                    var requestData ={
                        "function": "1007",
                        "id": data.id,
                        "node_id": data.node_id,
                        "node_type":  node_type,
                        "node_name": data.node_name,
                        "location": location,
                        "node_address":data.node_address
                    }
                }
                bindBarn(requestData,obj)
            });

        });
    }
    function renderSelect(data ,isMarket){
        var html = '<option value="">请选择</option>';
        $.each(data, function (index, item) {
            if(isMarket){
                html += '<option value="' + item.market_id+ '" data-name="'+item.market_name+'">' + item.market_name + '</option>'
            }else{
                html += '<option value="' + item+ '" >' + item + '</option>'
            }
        });
        layui.form.render();
        return html;
    }
    function bindBarn(requstData,obj,flag){
        var datas = {
            "header": {
                "data_type": "proxy",
                "data_direction": "request",
                "server": "vod_http_server",
                "id": "vod_http_server"
            },
            "request":requstData,
            "comment": ""
        };
        var datas2 = JSON.stringify(datas);
        return $.ajax({
            type: "post",
            dataType: "json",
            url:'<?php echo $request_url;?>',
            data: {
                json: datas2
            },
            success:function(res){
                clickFlag = false;
                if(res.status == 0){
                    tableInit();
                    layer.msg("创建成功")
                } else {
                    layer.msg(res.msg);
                }
            },
            error: function(e) {
                layer.msg("我们出错了");
            }
        });
    }
    function reload(){
        event.preventDefault();//取消默认事件
        var market_id = $(".market_id").val();
        var type = $(".select-box").find("select[name='type']").val();
        tableInit(market_id,type);
    }
</script>
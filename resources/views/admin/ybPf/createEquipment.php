<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<link rel="stylesheet" href="/vendor/assets/sass/base.css">
<link rel="stylesheet" href="/vendor/assets/sass/index.css">
<link rel="stylesheet" href="/vendor/assets/sass/createEquipment.css">
<script src="/vendor/layui/layui.js"></script>

<div class="container">
    <div class="table-operation-row">
        <div class="total-title">设备信息<span class="total-wrapper">共<span class="total"></span>条</span></div>
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
                <input type="text" name="title" placeholder="请输入机器ID" class="layui-input table-input table-search-input market_id" type="submit" autocomplete="off">
            </form>
        </div>
    </div>
    <table id="storeHouseCreate" lay-filter="storeHouseCreate"></table>
</div>
<script>
    var barnNameList = {},
        locationArr = [],
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
            var equimentTable = table.render({
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
                            "function": "1008",
                            "vm_code": market_id,
                            "type": type
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
                , cols: [[ //表头
                    // {checkbox: true},
                    {field: 'id', title: '编号', align: 'center'
                    ,templet:function(d){
                        return '<span class="tipsWrapper">'+d.id+'<span class="tips" style="display:none"><\/span><\/span>';
                    }},
                    {field: 'node_name', title: '点位名称',
                        templet: function (d) {
                            return '<div class="yc-table-cell" >' + d.node_name+ '<\/div>'
                        }
                    },
                    {field: 'node_id', title: '友宝点位ID', align: 'center'},
                    {field: 'vm_code', title: '友宝机器ID', align: 'center'},
                /*    {field: 'device_name', title: '设备类型', width:150, align: 'center'},
                    {field: 'tmall_node_id', title: '天猫点位ID', align: 'center', width: 100},*/
                    {field: 'site_store_id', title: 'storeID',align:"center"},
                    {field: 'device_code', title: '天猫devicecode', align:"center",
                        templet: function (d) {
                            if(d.device_code!='0'&&d.device_code!=null){
                                return d.device_code
                            }else{
                                return "暂无"
                            }

                        }
                    },
                    {field: 'activation_code', title: '激活码',align:"center",
                        templet: function (d) {
                            if(Number(d.activation_code)){
                                return d.activation_code
                            }else{
                                return "暂无"
                            }

                        }
                    },
                    {field: 'ub_barn_id', title: '操作',align: 'center',
                        templet:function(d){
                            if(!Number(d.device_id)){
                                return '<a class="table-btn " lay-event="create">创建</a>'
                            }else if(Number(d.device_id)){
                                return '<a class="table-btn bind_fail" >已创建</a>'
                            }
                        }
                    },
                ]]
                , done: function (res, curr, count) {
                    $(".total").html(count);
                    $(".export-btn").on('click', function () {
                        var market_id = encodeURI($(".market_id").val());
                        var type = $(".select-box").find("select[name='type']").val();
                        var data ={
                            "header": {
                                "data_type": "proxy",
                                "data_direction": "request",
                                "server": "vod_http_server",
                                "id": "vod_http_server"
                            },
                            "request": {
                                "function": "1008",
                                "excel":1,
                                "vm_code": market_id,
                                "type": type
                            },
                            "comment": ""
                        }
                        data = JSON.stringify(data)+'&page='+curr+'&limit='+equimentTable.config.limit;
                        window.location.href = '<?php echo $request_url;?>?json='+data;
                    })
                    res.data.forEach(function(item ,index){
                        if(item.delete_mark == "1"){
                            $('tr').eq(index+1).find('td').find('.table-btn').hide();
                            $("tr").eq(index+1).find(".tips").fadeIn()
                            $("tr").eq(index+1).addClass("delete");
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
                    tr = obj.tr;//获得当前行 tr 的DOM对象
                var requestData ={
                    "function": "1009",
                    "id": data.id,
                    "node_id": data.node_id,
                    "vm_code": data.vm_code,
                    "node_name": data.node_name,
                    "tmall_node_id": data.site_tmall_node_id,
                    "vm_type": data.vm_type,
                    "store_id": data.site_store_id,
                }
                if (layEvent === 'create') {
                    requestData.type = 1
                }else if(layEvent == "delete"){
                    requestData.type = 2
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
        return html;
    }
    function bindBarn(requstData,obj){
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
                if(res.status == 0){
                    tableInit()
                }else{
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

<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<link rel="stylesheet" href="/vendor/assets/sass/base.css">
<link rel="stylesheet" href="/vendor/assets/sass/index.css">
<link rel="stylesheet" href="/vendor/assets/sass/optimizeSelectOption.css">
<script src="/vendor/layui/layui.js"></script>
<script src="/vendor/assets/js/optimizeSelectOption.js"></script>

<div class="container">
    <div class="table-operation-row">
        <div class="total-title">商场信息<span class="total-wrapper">共<span class="total"></span>条</span></div>
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
                <input type="text" name="title" placeholder="请输入商场名称或者ID" class="layui-input table-input table-search-input market_id" type="submit" autocomplete="off">
            </form>
        </div>
    </div>
    <table id="storeHouseCreate" lay-filter="storeHouseCreate"></table>
</div>
<script>
    var barnNameList ={};
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
            table.render({
                elem: '#storeHouseCreate'
                , id: 'storeHouseCreate'
                , limit: 20
                , height: 490
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
                            "function": "1004",
                            "market_id": market_id,
                            "type": type
                        },
                        "comment": ""
                    })
                }
                , parseData: function (res) { //res 即为原始返回的数据
                    barnNameList = res.data.list;
                    return {
                        "code": res.status, //解析接口状态
                        // "msg": res.message, //解析提示文本
                        "count": res.data.total, //解析数据长度
                        "data": res.data.MallList //解析数据列表
                    };
                }
                , page: true //开启分页
                , cols: [[ //表头
                    // {checkbox: true},
                    {field: 'id', title: '编号', align: 'center', width: "8%",
                        templet: function(d){
                            return '<span class="tipsWrapper">'+d.id+'<span class="tip'+d.id+' tips" style="display:none"></span></span>'
                        }
                    },
                    {field: 'market_name', title: '商场名称', width:"15%",
                        templet: function (d) {
                            return '<div class="yc-table-cell">'+d.market_name+'<\/div>'
                        }
                    },
                    {field: 'market_id', title: '商场ID', align: 'center', width: "15%"},
                    {field: 'city', title: '市', align: 'center', width: "15%",
                        templet: function (d) {
                            return d.city.name
                        }
                    },
                    {field: 'address', title: '详细地址', width:"20%",
                        templet: function (d) {
                            return '<div class="yc-table-cell">'+d.address+'<\/div>'
                        }
                    },
                    {field: 'ub_barn_name', title: '仓名称', align: 'center', width: "15%",
                        templet: function (d) {
                            var resultHtml = "";
                            if(d.ub_barn_id){
                                resultHtml = '<div class="barn-name yc-table-cell" data-barnId="'+d.ub_barn_id+'">'+d.ub_barn_name+'<\/div>';
                            }else{
                                var listHtml = renderSelect(barnNameList);
                                resultHtml='<select class="table-select province-select province-select'+d.ub_barn_id+'"  name="barnName" lay-filter="barnName" lay-verify="required" lay-search value="' + d.ub_barn_name + '" >'
                                    + listHtml+ '<\/select>'
                            }
                            return resultHtml;
                        }
                    },
                    {field: 'ub_barn_id', title: '操作', width:"12%",align: 'center',
                        templet:function(d){
                            if(Number(d.ub_barn_id)){
                                return ' <a class="table-btn bind_success" lay-event="notbind">解绑<\/a>';
                            }else if(!(Number(d.ub_barn_id))){
                                return '<a class="table-btn bind_fail" lay-event="bind">绑定<\/a>'
                            }
                        }
                    },
                    // {fixed: 'right', title: '操作', width: "15%", align: 'center', toolbar: '#toolTpl'}
                ]]
                , done: function (res, curr, count) {
                    $(".total").html(count);
                    res.data.forEach(function(item ,index){
                        if(Number(item.delete_mark)){
                            $("tr").eq(index).find(".tips").fadeIn();
                            $("tr").eq(index).addClass("delete");
                            $('tr').eq(index).find('td').eq(6).hide();
                            setTimeout(function(){
                                $("tr").eq(index).find(".tips").fadeOut()
                            },1000)
                        }
                    })
                    $(".export-btn").on('click', function () {
                        table.exportFile('storeHouseCreate', '', 'xls'); //data 为该实例中的任意数量的数据
                    })
                }
            });

            //监听工具条
            table.on('tool(storeHouseCreate)', function (obj) {
                var data = obj.data, //获得当前行数据
                    layEvent = obj.event, //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
                    tr = obj.tr,//获得当前行 tr 的DOM对象
                    type ="",
                    ub_barn_id="",
                    ub_barn_name = "";
                if (layEvent === 'bind') {
                    ub_barn_id=$(tr).find("select[name='barnName']").val();
                    ub_barn_name=barnNameList[ub_barn_id];
                    if(!ub_barn_id){
                        layer.msg("请选择仓名称！");
                        return false;
                    }
                    type=1 //绑定
                }else if(layEvent === 'notbind'){
                    ub_barn_id=$(tr).find(".barn-name").attr("data-barnId");
                    ub_barn_name=$(tr).find(".barn-name").html();
                    type=2 //解绑
                }
                bindBarn(data.id,type,ub_barn_id,ub_barn_name,obj)
            });

        });
    }
    function renderSelect(data){
        var html = '<option value="">请选择</option>';
        $.each(data, function (index, item) {
            html += '<option value="' + index + '">' + item + '</option>'
        });
        return html;
    }
    function bindBarn(id,type,ub_barn_id,ub_barn_name,obj){
        var datas = {
            "header": {
                "data_type": "proxy",
                "data_direction": "request",
                "server": "vod_http_server",
                "id": "vod_http_server"
            },
            "request":{
                "function": "1005",
                "id": id,
                "type": type,
                "ub_barn_id": ub_barn_id,
                "ub_barn_name": ub_barn_name
            },
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
                    if(type==1){
                        obj.update({
                            "ub_barn_id":ub_barn_id,
                            "ub_barn_name": ub_barn_name
                        })
                        layer.msg("绑定成功");
                    }else{
                        obj.update({
                            "ub_barn_id":"",
                            "ub_barn_name": ""
                        })
                        layui.form.render();
                        layer.msg("解绑成功");
                    }
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
<style>
    .container{
        width:96%;
    }
    .bind_success{
        color:#FF5784
    }
    .delete{
        color:#999;
    }
    .table-input-wrapper{
        width:220px;
        height:32px;
        display:inline-block;
        overflow:hidden;
        float:right;
    }
    .table-search-input{
        width:216px;
        margin-right:8px;
    }
    .select-box{
        float:right;
        width:112px;
        height:32px;
    }
    .select-box .layui-unselect{
        height:32px;
    }
    .layui-form-selected dl{
        width:100%;
        text-align:center;
    }
    .new-export{
        float:right;
    }
</style>
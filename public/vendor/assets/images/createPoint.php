<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>商场绑定</title>
    <link rel="stylesheet" href="/vendor/layui/css/layui.css">
    <link rel="stylesheet" href="/vendor/assets/sass/base.css">
    <link rel="stylesheet" href="/vendor/assets/sass/index.css">
    <link rel="stylesheet" href="/vendor/assets/sass/optimizeSelectOption.css">
    <script src="/vendor/layui/layui.js"></script>
    <script src="/vendor/assets/js/optimizeSelectOption.js"></script>
    <script type='text/javascript' src='http://dev-uc.ipktv.com/youCS/static/jquery-3.0.0.min.js'></script>
</head>
<body>
<div class="container">
    <div class="table-operation-row">
        <div class="total-title">商场信息<span class="total-wrapper">共<span class="total"></span>条</span></div>
        <form class="layui-form select-box">
            <select name="type" class="company-box" lay-filter="type">
                <option value="">全部</option>
                <option value="1">未删除</option>
                <option value="2">删除</option>
                <option value="3">已创建</option>
                <option value="4">未创建</option>
            </select>
        </form>
        <div class="table-input-wrapper">
            <form onSubmit="reload()">
                <input type="text" name="title" placeholder="请输入点位名称" class="layui-input table-input table-search-input market_id" type="submit">
            </form>
<!--            <div class="table-top-btn export-btn" title="导出">导出</div>-->
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
        $(".export-btn").on('click',function () {
        });
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
                , height: 550
                , method: 'post'
                , url: 'http://dev-uc.ipktv.com/ybPfAdmin/rm/list' //数据接口
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
                    barnNameList = res.data.SiteList;
                    locationArr = res.data.location;
                    nodeTypeArr = res.data.node_type;
                    return {
                        "code": res.status, //解析接口状态
                        // "msg": res.message, //解析提示文本
                        "count": res.data.total, //解析数据长度
                        "data": res.data.SiteList //解析数据列表
                    };
                }
                , page: true //开启分页
                , cols: [[ //表头
                    // {checkbox: true},
                    {field: 'id', title: '编号', align: 'center', width: "5%",totalRow:true,templet:function(d){
                        return '<span class="tipsWrapper">'+d.siteInfo.id+'<span class="tip'+d.siteInfo.id+' tips" style="display:none"></span></span>';
                        }},
                    {field: 'node_name', title: '点位名称', width:"15%",templet:function(d){
                            return d.siteInfo.node_name;
                        }},
                    {field: 'node_type', title: '场地类型', align: 'center', width: "12%",
                        templet: function (d) {
                            var resultHtml = "";
                            if(d.siteInfo.tmall_node_id){
                                resultHtml = '<div class="barn-name" data-barnId="'+d.siteInfo.node_type+'">'+d.siteInfo.node_type+'</div>';
                            }else{
                                var listHtml = renderSelect(nodeTypeArr);
                                resultHtml='<select class="table-select province-select province-select'+d.siteInfo.node_type+'"  name="nodeType" lay-filter="barnName" lay-verify="required" lay-search value="' + d.siteInfo.node_type + '" >'
                                    + listHtml+ '</select>'
                            }
                            return  resultHtml;
                        }
                    },
                    {field: 'location', title: '位置', align: 'center', width: "12%",
                        templet: function (d) {
                            var resultHtml = "";
                            if(d.siteInfo.tmall_node_id){
                                resultHtml = '<div class="barn-name" data-barnId="'+d.siteInfo.location+'">'+d.siteInfo.location+'</div>';
                            }else{
                                var listHtml = renderSelect(locationArr);
                                resultHtml='<select class="table-select province-select province-select'+d.siteInfo.location+'"  name="location" lay-filter="location" lay-verify="required" lay-search value="' + d.siteInfo.location + '" >'
                                    + listHtml+ '</select>'
                            }
                            return  resultHtml;
                        }
                    },
                    {field: 'address', title: '业务归属', width:"10%", align: 'center',
                        templet: function (d) {
                            return d.siteInfo.belong_to
                        }
                    },
                    {field: '', title: '<span class="market_templet">商场ID</span> 商场名称', align: 'left', width: "21%",
                        templet: function (d) {
                            var resultHtml = "";
                            if(d.siteInfo.tmall_node_id){
                                resultHtml ='<span class="market_templet">'+d.siteInfo.market_id+'</span>'+d.siteInfo.market_name;
                            }else{
                                var listHtml = renderSelect(d.mall,1);
                                resultHtml='<select class="table-select province-select province-select'+d.ub_barn_id+'"  name="market_name" lay-filter="barnName" lay-verify="required" lay-search value="' + d.ub_barn_name + '" >'
                                    + listHtml+ '</select>'
                            }
                            return  resultHtml
                        }
                    },
                    {field: 'address', title: '仓ID', width:"8%",align:"center",
                        templet: function (d) {
                            return d.siteInfo.barn_id
                        }
                    },
                    {field: 'address', title: '天猫点位ID', width:"8%",align:"center",
                        templet: function (d) {
                            if(d.siteInfo.tmall_node_id){
                                return d.siteInfo.tmall_node_id
                            }else{
                                return "暂无"
                            }

                        }
                    },
                    {field: 'ub_barn_id', title: '操作', width:"10%",align: 'center',fixed: 'right',
                        templet:function(d){
                            if(d.siteInfo.tmall_node_id){
                                return '<a class="table-btn bind_fail" lay-event="bind">已创建</a>'
                            }else{
                                return ' <a class="table-btn" lay-event="create" id="tip">创建</a>';
                            }
                        }
                    },
                    // {fixed: 'right', title: '操作', width: "15%", align: 'center', toolbar: '#toolTpl'}
                ]]
                , done: function (res, curr, count) {
                    $(".total").html(count);
                    res.data.forEach(function(item ,index){
                        if(item.siteInfo.delete_mark){
                            $("tr").eq(index).find(".tips").fadeIn()
                            $("tr").eq(index).addClass("delete");
                            setTimeout(function(){
                                $("tr").eq(index).find(".tips").fadeOut()
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
                    location ="位置",
                    market_id= "商场id",
                    market_name= "商场名字";
                if (layEvent === 'create') {
                    node_type=$(tr).find("select[name='nodeType']").val();
                    location =$(tr).find("select[name='location']").val();
                    market_id =$(tr).find("select[name='market_name']").val();
                    market_name = $(tr).find("select[name='market_name']").attr("data-name")
                    data.mall.forEach(function(item){
                        if(item.market_id == market_id){
                            market_name = item.market_name;
                            return false
                        }
                    })
                    if(!node_type){
                        layer.msg("请选择场地类型");
                        return false;
                    }
                    if(!location){
                        layer.msg("请选择位置");
                        return false;
                    }
                    if(!market_id){
                        layer.msg("请选择商场名称或商场ID");
                        return false;
                    }
                    console.log(tr)
                    var requestData ={
                        "function": "1007",
                            "id": data.siteInfo.id,
                            "node_id": data.siteInfo.node_id,
                            "node_type":  node_type,
                            "node_name": data.siteInfo.node_name,
                            "location": location,
                            "market_id": market_id,
                            "market_name": market_name,
                            "node_address":data.siteInfo.node_address
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
            url:"http://dev-uc.ipktv.com/ybPfAdmin/rm/list",
            data: {
                json: datas2
            },
            success:function(res){
                    var tr= obj.tr;
                    $(tr).find('td').eq(2).html('<div class="barn-name" >'+requstData.node_type+'</div>');
                    $(tr).find('td').eq(3).html('<div class="barn-name" >'+requstData.location+'</div>');
                    $(tr).find('td').eq(5).html('<span class="market_templet">'+requstData.market_id+'</span>'+requstData.market_name);
                    $(tr).find('td').eq(7).html('<div class="barn-name" >'+res.data.tmall_node_id+'</div>');
                    $(tr).find('td').eq(8).html('<div class="bind_fail" >已创建</div>');
                    layer.msg("创建成功")
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
<script type="text/html" id="toolTpl">
    {{#  if(d.ub_barn_id){ }}
    <a class="table-btn bind_success" lay-event="notbind">解绑</a>
    {{#  } else { }}
    <a class="table-btn bind_fail" lay-event="bind">绑定</a>
    {{#  } }}
</script>
<style>
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
    .market_right{

        /*margin-left:8px;*/
    }
    .market_templet{
        display:inline-block;
        width:32%;
    }
    .bind_fail{
        color:#999;
    }
    .tipsWrapper{
        position:relative;
    }
    .tips:after{
        content:"提示：该信息已变更";
        display:block;
        width:125px;
        height:32px;
        line-height:32px;
        background:rgb(0,0,0);
        position:absolute;
        top:21px;
        left:7px;
        color:#fff;
        border-radius:2px;
        font-size:12px;
    }
    .tips:before{
        content:"";
        width: 0;
        height: 0;
        border: 5px solid;
        border-color: transparent transparent #000;
        position:absolute;
        top:11px;
        left:32px;
    }
</style>
</body>
</html>
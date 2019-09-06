<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<link rel="stylesheet" href="/vendor/assets/sass/base.css">
<link rel="stylesheet" href="/vendor/assets/sass/index.css">
<link rel="stylesheet" href="/vendor/assets/sass/optimizeSelectOption.css">
<!--<link rel="stylesheet" href="/vendor/assets/sass/datareConciliation.css">-->
<script src="/vendor/layui/layui.js"></script>
<script src="/vendor/assets/js/optimizeSelectOption.js"></script>
<div id="dataWatch">
    <div class="dataWatch-wrapper">
        <div class="search-box">
            <div class="date-select">
                <input type="text" class="layui-input selectDate"  placeholder="请选择日期" autocomplete="off">
            </div>
            <form class="layui-form select-box company-select">
                <div class="layui-form-item">
                    <label class="layui-form-label">分公司</label>
                    <div class="layui-input-block" >
                        <select name="city" class="company-box" lay-filter="company">
                        </select>
                    </div>
                </div>
            </form>
            <div class="inputs-box">
                <div class="search-input-box">
                    <label class="layui-form-label">客户名称</label>
                    <div class="search-wrapper" >
                        <img src="/vendor/assets/images/search_icon.png">
                        <form v-on:submit.prevent="initTable()">
                            <input placeholder="请输入客户名"  v-model="bandName" autocomplete="off">
                        </form>
                    </div>
                </div>
                <div class="search-input-box">
                    <label class="layui-form-label">商品名称</label>
                    <div class="search-wrapper" >
                        <img src="/vendor/assets/images/search_icon.png">
                        <form v-on:submit.prevent="initTable()">
                            <input placeholder="请输入商品名"  v-model="productName" autocomplete="off">
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="keyWord-box">
            <p>关键指标</p>
            <div class="keyword-wrapper">
                <ul class="keyword-title">
                    <li v-show="bandNameShow">客户名称</li>
                    <li v-show="productNameShow">商品名称</li>
                    <li v-show="companyName">公司名称</li>
                    <li>商品数量</li>
                    <li>发货总量</li>
                    <li>实收量</li>
                    <li>累计派发总量</li>
                    <li>货损量</li>
                    <li>货损率</li>
                    <li>结存</li>
                </ul>
                <ul class="keyword-content" v-cloak>
                    <li v-show="bandNameShow">{{bandNameShow}}</li>
                    <li v-show="productNameShow">{{productNameShow}}</li>
                    <li v-show="companyName">{{companyName}}</li>
                    <li class="color-back">{{keywordList.total}}</li>
                    <li class="color-back">{{keywordList.total_shipments}}</li>
                    <li class="color-back">{{keywordList.inbound_num}}</li>
                    <li class="color-back">{{keywordList.total_quantity}}</li>
                    <li class="color-back">{{keywordList.damage_amount}}</li>
                    <li class="color-back">{{keywordList.damage_rate}}</li>
                    <li class="color-back">{{keywordList.deposit_num}}</li>
                </ul>
            </div>
        </div>
        <div class="table-top-btn export-btn new-export" title="导出">导出</div>
        <table id="search-list-table" lay-filter="search-list-table"></table>
    </div>
</div>
<script>
    var vm = new Vue({
        el: '#dataWatch',
        data: {
            keywordList:{},
            companyName:"",
            bandName:"",//参数
            bandNameShow:"",
            productName:"",//参数
            productNameShow:"",
            start_time: "",
            end_time: "",
            testOne:true,
            dataList:[]
        },
        mounted:function(){
            var that = this;
            var dateRange = this.getDateRange()
            this.$nextTick(function(){
                layui.use('form', function(){
                    var form = layui.form.render();
                    form.on('select(company)', function(data){
                        that.companyName = data.value;
                        that.initTable();
                    });
                });
                layui.use('laydate', function(){
                    var laydate = layui.laydate;
                    var searchDate= laydate.render({
                        elem: '.selectDate' //指定元素
                        ,range: true
                        , theme: 'purple'
                        , trigger: 'click'
                        ,value:dateRange,
                        done: function(value) {
                            if(!value){
                                setTimeout(function(){
                                    $(".selectDate").val("");
                                    searchDate.config.value = "";
                                },0)
                                that.start_time = "";
                                that.end_time = "";
                            }else{
                                var date = value.split(" - ");
                                that.start_time = date[0];
                                that.end_time = date[1];
                            }
                            that.initTable()
                        }
                    });
                    $(".selectDate").attr("lay-key",new Date().getTime());
                })
            })
            this.initTable()
        },
        methods:{
            initTable:function(){
                var that = this;
                that.bandNameShow = that.bandName
                that.productNameShow =that.productName
                layui.use('table', function () {
                    var table = layui.table;
                   var dataTable= table.render({
                        elem: '#search-list-table'
                        , id: 'search-list-table'
                        ,limit: 10
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
                                    "function": "1017",
                                    "product_name": that.productName,
                                    "brand": that.bandName,
                                    "company_name": that.companyName,
                                    "start_time":that.start_time,
                                    "end_time": that.end_time,
                                    "limit": "10"
                                },
                                "comment": ""
                            })
                        }
                        , parseData: function (res) { //res 即为原始返回的数据
                            if(res.status == 0){
                                var response = res.data;
                                if(that.testOne){
                                    $(".company-box").html(that.renderSelect(response.company_name));
                                    $(".band-box").html(that.renderSelect(response.brand));
                                    $(".product-box").html(that.renderSelect(response.productName));
                                    layui.form.render();
                                    that.testOne = false;
                                }
                                that.keywordList = response.totalList;
                                that.dataList = response.inventoryList.data;
                            }
                            return {
                                "code": res.status, //解析接口状态
                                // "msg": res.message, //解析提示文本
                                "count": res.data.inventoryList.total, //解析数据长度
                                "data": res.data.inventoryList.data //解析数据列表
                            };
                        }
                        , page: true //开启分页
                        , cols: [[ //表头
                            {
                                field: "id",
                                title: "编号",
                                width: "5%",
                            },{
                                field: "create_at",
                                title: "创建日期",
                                width: "10%",
                            },{
                                field: "company_name",
                                title: "公司名",
                                width: "10%"
                            }, {
                                field: "brand",
                                title: "客户名",
                                width: "12%"
                            }, {
                                field: "product_name",
                                title: "商品名",
                                width: "12%"
                            }, {
                                field: "product_id",
                                title: "商品ID",
                                width: "12%"
                            }, {
                                field: "unit",
                                title: "计量单位",
                                width: "7%",
                                align:'center'
                            },{
                                field:"inbound_num",
                                title: "入库数量",
                                width: "11%"
                            },{
                                field:"outbound_num",
                                title: "派发数量",
                                width: "10.5%",
                            },{
                                field:"deposit_num",
                                title: "结存数量",
                                width: "10%"
                            }
                        ]],
                        done:function(res,curr,count){
                            $(".export-btn").on('click', function () {
                                var data ={
                                    "header": {
                                        "data_type": "proxy",
                                        "data_direction": "request",
                                        "server": "vod_http_server",
                                        "id": "vod_http_server"
                                    },
                                    "request": {
                                        "function": "1017",
                                        "excel":1,
                                        "product_name": that.productName,
                                        "brand": that.bandName,
                                        "company_name": that.companyName,
                                        "start_time":that.start_time,
                                        "end_time": that.end_time,
                                        "limit": "10"
                                    },
                                    "comment": ""
                                }
                                data = JSON.stringify(data)+'&page='+curr+'&limit='+dataTable.config.limit;
                                window.location.href = '<?php echo $request_url;?>?json='+data;
                            })
                        }
                    });

                });
            },
            getDateRange:function(){
                var dt = new Date();
                var year = dt.getFullYear();
                var month = dt.getMonth() + 1;
                var date = dt.getDate();
                var today = year + '-' + (month < 10 ? '0' + month : month) + '-' + (date < 10 ? '0' + date : date);

                var pastDate = new Date(dt - 1000 * 60 * 60 * 24 * 30);
                var pastY = pastDate.getFullYear();
                var pastM = pastDate.getMonth() + 1;
                var pastD = pastDate.getDate();
                var beginning = pastY + '-' + (pastM < 10 ? '0' + pastM : pastM) + '-' + (pastD < 10 ? '0' + pastD : pastD);
                var defaultdate = beginning + ' - ' + today;
                this.start_time = beginning;
                this.end_time = today;
                return defaultdate
            },
            renderSelect:function(data){
                var html = '<option value="">请选择</option>';
                $.each(data, function (index, item) {
                    html += '<option value="' + item + '">' + item + '</option>'
                });
                return html;
            }
        }
    })
</script>
<style>

    .dataWatch-wrapper{
        font-family: "微软雅黑";
        background:#fff;
        padding:18px 20px;
        margin:0 20px;
        border-radius:5px;
        overflow:hidden;
    }
    .search-box{
        display:flex;
        background:#fff;
        color:rgba(0,0,0,0.65);
        width:100%;
    }
    .date-select{
        position:relative;
        width:234px;
        height:30px;
        display:flex;
        border:1px solid #d9d9d9;
        border-radius:5px;
        align-items: center;
    }

    .date-select input{
        border:none;
        width:230px;
        height:30px;
        line-height:30px;
        background: url("/vendor/assets/images/date_icon.png") no-repeat;
        background-position: 202px 5px;
        background-size: 18px 18px;
    }
    .select-box{
        display:flex;
    }
    .layui-input, .layui-select, .layui-textarea{
        border-radius:5px;
        height: 30px;
    }

    .layui-form-select dl{
        width:100%;
    }
    .layui-laydate .layui-this{
        background:#5F6BFF!important;
    }
    .inputs-box{
        display:flex;
        width:100%;
        margin-left:auto;
    }
    .layui-form-label{
        /*width:83px;*/
        line-height:30px;
        padding:0;
        min-width:70px;
    }
    .search-input-box{
        display:flex;
    }
    .layui-form-label, .layui-form-mid, .layui-form-select, .layui-input-block, .layui-input-inline, .layui-textarea{
        /*min-width:100px;*/
    }
    .layui-laydate-content td.laydate-selected{
        background:#fff;
    }
    .layui-laydate-footer span:hover{
        color:#5F6BFF
    }
    #keyWord-table{
        padding:10px 0;
    }
    #keyWord-table tr {
        background-color: #f5f5f5;
    }
    .layui-table td, .layui-table th{
        height:15px;
        line-height:15px;
        text-align:center;
        border:none;
        color:#333;
    }
    .keyWord-box p{
        color:#666;
        margin-bottom:16px;
    }
    .keyword-wrapper{
        background:#f5f5f5;
    }
    .keyword-title, .keyword-content{
        display:flex;
        width:100%;
        height:56px;
        line-height:56px;
        color:#333;
        min-width:300px;
    }
    .keyword-title li, .keyword-content li{
        flex:1;
        text-align:center;
    }
    .export-btn{
        background:#5F6BFF;
        width:64px;
        height:30px;
        line-height:30px;
        text-align:center;
        color:#fff;
        border-radius:5px;
        float:right;
        font-size:14px;
        margin:15px 0;
    }
    #search-list-table{
        margin-top:50px;
    }
    .laytable-cell-1-0-0{
        width:80px;
    }
    .layui-table-box{
        width:100%;
    }
    .layui-table tr{
        border-bottom:1px solid #e8e8e8;
    }
    .layui-table-page{
        margin-top:20px;
    }
    .search-wrapper{
        width:180px;
        height:30px;
        border:1px solid #D9D9D9;
        border-radius: 4px;
        display:flex;
        align-items:center;
        box-sizing:border-box;
    }
    .search-wrapper img{
        width:13px;
        height:13px;
        margin:0 10px;
    }
    .search-wrapper input{
        border:none;
        width:136px;
    }
    .search-input-box label{
        margin-right:10px;
    }
    .company-select .layui-form-label{
        width:83px;
    }
    .company-select .layui-input-block{
        width:100px;
        margin-left:90px;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<link rel="stylesheet" href="/vendor/assets/sass/base.css">
<link rel="stylesheet" href="/vendor/assets/sass/index.css">
<script src="/vendor/layui/layui.js"></script>

<div id="purchase">
    <div class="purchase-header">
        <!--        <div class="purchase-title">任务管理 / <span class="em-bold">商品采购</span></div>-->
        <div class="search-wrapper" >
            <img src="/vendor/assets/images/search_icon.png">
            <form v-on:submit.prevent="reload()">
                <input placeholder="搜索商品名称" id="product_name" v-model="params.product_name" autocomplete="off">
            </form>
        </div>
        <div class="date-select">
            <input type="text" class="layui-input test-item" id="date" placeholder="请选择日期" autocomplete="off" style="width:230px;background: url('/vendor/assets/images/date_icon.png') no-repeat;background-position:204px 5px; background-size:18px 18px;">
        </div>
    </div>
    <ul class="purchase-content" v-cloak>
        <li :class="['purchase-list col-lg-4 col-md-4 col-sm-2 col-xs-2',{'space-margin':(index+1-2)%3==0}]"  v-for="(item,index) in purchaseData">
            <div class="title em-bold">{{item.product_name}}</div>
            <div class="title2 padd-top11"><span class="em-bold">品牌主：</span>{{item.brand}}</div>
            <div class="padd-top11"><span class="em-bold">天猫商品ID：</span>{{item.tmall_product_id}}</div>
            <div class="padd-top11"><span class="em-bold">友宝商品ID：</span>{{item.product_id}}</div>
            <div class="padd-top11"><span class="em-bold">总投放量：</span>{{item.total_quantity}}</div>
            <div class="padd-top11"><span class="em-bold">分配仓库数：</span>{{item.barn_count}}</div>
            <div class="number-wrapper padd-top11">
                <div class="all-number">总到货量：{{item.totalArrivalVolume}}</div>
                <div class="all-number">采购量：{{item.totalQuantity}}</div>
            </div>
            <div class="layui-progress">
                <div class="layui-progress-bar" :style="{width:transPercent(item.totalArrivalVolume,item.totalQuantity)}"></div>
            </div>
            <div class="detail-btn" v-on:click="openDetail(item.id)">详情</div>
        </li>
        <li v-show="purchaseData.length==0" class="empty-data">暂无数据</li>
    </ul>
    <div class="detail-mask" style="display:none">
        <div class="detail-wrapper" v-if = "detailShow">
            <div class="detai-header">
                <div class="detail-title">{{detailData.TaskInfo.product_name}}</div>
                <div class="title2 padd-top11"><span class="em-bold">品牌主：</span>{{detailData.TaskInfo.brand}}</div>
                <div class="padd-top11"><span class="em-bold">天猫商品ID：</span>{{detailData.TaskInfo.tmall_product_id}}</div>
                <div class="padd-top11"><span class="em-bold">友宝商品ID：</span>{{detailData.TaskInfo.product_id}}</div>
                <div class="padd-top11"><span class="em-bold">总投放量：</span>{{detailData.TaskInfo.total_quantity}}</div>
                <div class="search-wrapper" >
                    <img src="/vendor/assets/images/search_icon.png">
                    <form v-on:submit.prevent="searchReload(detailData.TaskInfo.id)"><input placeholder="请输入仓库名称或者仓库ID" v-model="barn_id"></form>
                </div>
            </div>
            <div class="detail-stores" v-for="item in detailData.list">
                <div class="store-content">
                    <div class="store-name-wrapper">
                        <div class="store-name">
                            <div class="name-id"><span>{{item.name}}</span>{{"仓ID"+item.barn_id}}</div>
                        </div>
                        <div class="number-progress">
                            <div class="number-wrapper">
                                <div class="all-number">总到货量：{{item.getTotal}}</div>
                                <div class="all-number">采购量：{{item.buyTotal}}</div>
                            </div>
                            <div class="layui-progress">
                                <div class="layui-progress-bar" lay-percent="50%" :style="{width:transPercent(item.getTotal,item.buyTotal)}"></div>
                            </div>
                        </div>
                    </div>
                    <div class="table-content" v-for="itemList in item.list" v-show="itemList.id">
                        <div class="time-state">
                            <span class="em-bold">{{itemList.purchase_time}}</span>
                            <span :class="[itemList.inbound_order ? 'state-success' : 'state-fail', 'state-normal']">{{itemList.inbound_order?'已到货':'未到货'}}</span>
                        </div>
                        <table class="logistics-info layui-table">
                            <colgroup>
                                <col width="150">
                                <col width="200">
                                <col>
                            </colgroup>
                            <thead>
                            <tr>
                                <th>发货量</th>
                                <th>物流公司</th>
                                <th>物流单号</th>
                                <th>入库单</th>
                                <th>实收量</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="color-back">{{itemList.shipment_amount}}</td>
                                <td class="color-back">{{itemList.logistics_company}}</td>
                                <td class="color-back">{{itemList.logistics_order}}</td>
                                <td class="color-back">{{itemList.inbound_order}}</td>
                                <td class="color-back">{{itemList.actual_amount}}</td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    layui.use(['laydate', 'element', 'layer'], function () {
        var layer = layui.layer;
        var element = layui.element;
        var laydate = layui.laydate;
        var vm = new Vue({
            el: '#purchase',
            data: {
                detailData:{},
                detailShow: false,
                purchaseData:[],
                params:{},
                header:{
                    "data_type": "proxy",
                    "data_direction": "request",
                    "server": "vod_http_server",
                    "id": "vod_http_server"
                },
                activity_start_time:"",
                activity_end_time:"",
                totalPage:"",
                current_page:"",
                barn_id:""
            },
            mounted:function(){
                var that=this;
                var dateRange = that.getDateRange()
                this.getPurchaseData();
                that.scroll();
                that.$nextTick(function () {
                    var searchDateOne = laydate.render({
                        elem: '#date' //指定元素
                        , range: true
                        , theme: 'purple'
                        , value: dateRange
                        , trigger: 'click'
                        , done: function(value) {
                            if(!value){
                                setTimeout(function(){
                                    $("#date").val("");
                                    searchDateOne.config.value = "";
                                },0)
                                that.activity_start_time = "";
                                that.activity_end_time = "";
                            }else{
                                var date = value.split(" - ");
                                that.activity_start_time = date[0];
                                that.activity_end_time = date[1];
                            }

                            that.getPurchaseData();
                        }
                    });
                    $("#date").attr("lay-key",new Date().getTime());
                });
            },
            methods:{
                openDetail:function(id,isSearch){
                    var that=this;
                    var data = {
                        "header":this.header,
                        "request": {
                            "function": "1016",
                            "id":id,
                            "barn_id":that.barn_id
                        },
                        "comment": ""
                    };
                    $.ajax({
                        url: '<?php echo admin_url('ResourceManagement/index');?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {json: JSON.stringify(data)},
                        success: function (res) {
                            if (res.status == 0) {
                                that.detailShow = true;
                                that.detailData = res.data;
                                // layer.closeAll();
                                if(!isSearch &&that.detailShow){
                                    layui.use('layer', function () {
                                        layer.open({
                                            type: 1,
                                            content:$('.detail-mask'),
                                            area: ['798px', '650px'],
                                            title: false,
                                            scrollbar: false,
                                            success: function (layero, index) {
                                            }
                                        });

                                    });
                                    // layer.open({
                                    //     type:1,
                                    //     scrollbar:false,
                                    //     area: ['798px', '600px'],
                                    //     content:$('.detail-mask'),
                                    // });
                                }

                            }else{
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {

                        }
                    });
                },
                getPurchaseData:function(isLoading,page){
                    var that=this;
                    var data = {
                        "header":this.header,
                        "request": {
                            "function": "1015",
                            "product_name": that.params.product_name,
                            "activity_start_time": that.activity_start_time,
                            "activity_end_time":  that.activity_end_time

                        },
                        "comment": ""
                    };
                    $.ajax({
                        url:'<?php echo $request_url;?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {json: JSON.stringify(data),page:page},
                        success: function (res) {
                            if (res.status == 0) {
                                that.totalPage = res.data.last_page;
                                that.current_page = res.data.current_page;
                                if(that.current_page ==1){
                                    that.purchaseData = [];
                                }
                                that.purchaseData=that.purchaseData.concat(res.data.data);

                                isLoading = false
                            }else{
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {

                        }
                    });

                },
                transPercent:function(quantity,total){
                    return Number(quantity/total)*100+"%";
                },
                reload:function(){
                    this.getPurchaseData();
                    this.scroll();
                },
                searchReload:function(id){
                    this.openDetail(id,1);

                },
                scroll:function() {
                    var isLoading = false;
                    var that = this;
                    window.onscroll =function () {
                        var bottomOfWindow = document.documentElement.offsetHeight - document.documentElement.scrollTop - window.innerHeight <= 200
                        if (bottomOfWindow && isLoading == false) {
                            isLoading = true
                            if(that.current_page< that.totalPage){
                                that.getPurchaseData(isLoading,that.current_page+1);
                            }
                        }
                    }
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
                    this.activity_start_time = beginning;
                    this.activity_end_time = today;
                    return defaultdate
                },
            }
        })
    })

    layui.use('laydate', function () {
        var laydate = layui.laydate;
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

        var searchDate = laydate.render({
            elem: '#date'
            , range: true
            , type: 'date'
            , value: defaultdate
            , theme: 'purple'
            , trigger: 'click'
        });
        $("#date").attr("lay-key", new Date().getTime());

    });




</script>
<style>
    .table-content{
        padding-top:12px;
    }
    .layui-table th{
        color:rgba(0,0,0,0.65)
    }
    .padd-top11{
        padding-top:10px;
    }
    .purchase-header{
        width:100%;
        height:64px;
        display:flex;
        align-items: center;
        background:#fff;
        padding-left:2%;
    }
    .purchase-title{
        line-height:64px;
    }
    .em-bold{
        color:#333;
        font-weight:bold;
    }
    .search-wrapper{
        width:212px;
        height:32px;
        border:1px solid #D9D9D9;
        border-radius: 4px;
        display:flex;
        align-items:center;
        margin:0 8px 0 0;
        box-sizing:border-box;
    }
    .search-wrapper img{
        width:16px;
        height:16px;
        margin:0 10px;
    }
    .search-wrapper input{
        border:none;
        width:168px;
    }
    .purchase-content{
        overflow:hidden;
        margin:0 2%;
    }
    .purchase-list{
        /*min-width:388px;*/
        background:#fff;
        margin-top:24px;
        border-radius:5px;
        padding:20px;
        width:32%;
        height:350px;
    }
    .number-wrapper{
        display:flex;
        margin:25px 0 15px 0;
        justify-content:space-between;
    }
    .detail-btn{
        float:right;
        margin-top:14px;
        color:#5F6BFF;
        cursor:pointer;
    }
    .layui-progress{
        background:rgb(239,240,255);
    }
    .layui-progress-bar{
        background:#5F6BFF
    }
    .detail-mask{
        /*position:fixed;*/
        /*width:100%;*/
        /*height:100%;*/
        /*background:rgba(0,0,0,0.5);*/
        /*top:0;*/
        /*left:0;*/
    }
    .detail-wrapper{
        background:#fff;
        /*margin: 100px auto;*/
        /*width:70%;*/
        max-width:798px;
        border-radius:2px;
        padding:15px 20px;
    }
    .detai-header{
        overflow:hidden;
    }
    .detail-wrapper .search-wrapper{
        float:right;
    }
    .detail-stores{
        border: 1px solid rgba(0,0,0,0.15);
        border-radius: 4px;
        box-sizing:border-box;
        margin-top:13px;
    }
    .store-name-wrapper{
        display:flex;
        justify-content: space-between;
        padding:0 20px;
    }
    .time-state{
        padding:0 20px;
    }
    .name-id{
        height:62px;
        line-height:62px;
        color:#999;
    }
    .name-id>span{
        font-size: 20px;
        color: #333333;
        margin-right:5px;
    }
    .detail-stores .number-wrapper{
        margin: 15px 0 10px 0;
    }
    .detail-stores  .number-progress{
        width:60%;
    }
    .state-normal{
        margin-left:24px;
        color:#fff;
        border-radius:5px;
        height:22px;
        line-height:22px;
        text-align:center;
        width:60px;
        display:inline-block;
    }
    .state-success{
        background:#5F6BFF ;
    }
    .state-fail{
        background:#FF5784;
    }
    .layui-table td, .layui-table th{
        height:45px;
        line-height:45px;
        padding:0;
        text-align:center;
        border:none;
    }
    .color-back{
        color:#000;
    }
    .logistics-info tbody tr:hover {
        background-color: #fff;
    }
    .logistics-info tbody tr{
        border-bottom:1px solid #f5f5f5;
    }
    .logistics-info, .layui-table-view {
        margin: 10px 0 0 0;
    }
    .detail-title{
        font-size: 20px;
        color: #333333;
        font-weight:bold;
    }
    .space-margin{
        margin-left:2%;
        margin-right:2%;
    }
    .date-select{
        position:relative;
        width:234px;
        height:34px;
        display:flex;
        border:1px solid #d9d9d9;
        border-radius:5px;
        align-items: center;
        box-sizing:border-box;
    }
    .date-select img{
        width:16px;
        height:16px;
        margin-right:5px;
    }
    .date-select input{
        border:none;
        width:190px;
        height:32px;
        line-height:32px;
    }
    .empty-data{
        text-align: center;
        margin-top: 100px;
    }
    /*日期样式修改*/
    .layui-laydate .layui-this{
        background:#5F6BFF!important;
    }
    .layui-laydate-content td.laydate-selected{
        background:rgb(239,240,255);
    }
    .layui-laydate-footer span:hover{
        color:#5F6BFF
    }
</style>


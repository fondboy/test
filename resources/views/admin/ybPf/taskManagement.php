<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<link rel="stylesheet" href="/vendor/assets/sass/base.css">
<link rel="stylesheet" href="/vendor/assets/sass/index.css">
<script src="/vendor/layui/layui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>


<div class="task-container" id="taskManagement">
    <div class="task-top">
        <div class="table-input-wrapper">
            <form v-on:submit.prevent="searchTaskList">
                <input type="text" name="title" lay-verify="required" placeholder="请输入商品名或者机器ID"
                       autocomplete="off" class="layui-input table-input table-search-input" ref="searchInput">
            </form>
        </div>
        <div class="date-choose top-date-choose ">
            <input type="text" class="search-date test-item" readonly placeholder="请选择日期"/>
        </div>
        <div class="taskStatus-btn" :class="{'un-finish':taskStatus==0,'finished':taskStatus==4}"
             v-on:click="changeTaskStatus"><span class="status-text">已完成</span><span
                    class="layui-badge point-icon" v-if="finishNum" v-cloak>{{finishNum}}</span></div>
    </div>
    <div class="task-wrapper">
        <div class="task-list" v-for="task in taskList" v-cloak>
            <div class="task-number">任务编号 <span class="number">{{task.id}}</span></div>
            <div class="task-title">{{task.product_name}}</div>
            <ul class="task-ul">
                <li class="task-li">
                    <span class="task-li-left">品牌主：</span>
                    <span class="task-li-right">{{task.brand}}</span>
                </li>
                <li class="task-li">
                    <span class="task-li-left">天猫商品ID：</span>
                    <span class="task-li-right">{{task.tmall_product_id}}</span>
                </li>
                <li class="task-li">
                    <span class="task-li-left">投放量：</span>
                    <span class="task-li-right">{{task.total_quantity}}</span>
                </li>
                <li class="task-li">
                    <span class="task-li-left">市场价：</span>
                    <span class="task-li-right">￥{{filterPrice(task.market_price)}}</span>
                </li>
                <li class="task-li">
                    <span class="task-li-left">活动时间：</span>
                    <span class="task-li-right">{{filterDate(task.activity_start_time)}}-{{filterDate(task.activity_end_time)}}</span>
                </li>
            </ul>
            <div>
                <div class="task-content" v-on:click="popStoreHouseAssign(task.id,task.finish_flag)">
                    <div :class="[task.finish_flag>0? 'finish':'notStart', 'task-status']"></div>
                    <div class="task-name">仓库分配</div>
                </div>
                <div v-bind:class="['task-content',{'gray-bg':task.finish_flag==0}]"
                     v-on:click="popGoodsSet(task.id,task.finish_flag)">
                    <div :class="[task.finish_flag>1? 'finish':'notStart', 'task-status']"></div>
                    <div class="task-name">商品配置</div>
                </div>
                <div v-bind:class="['task-content',{'gray-bg':task.finish_flag==0||task.finish_flag==1}]"
                     v-on:click="popPriceSet(task.id,task.finish_flag)">
                    <div :class="[task.finish_flag>2? 'finish':'notStart', 'task-status']"></div>
                    <div class="task-name">价格配置</div>
                </div>
            </div>
        </div>
    </div>
    <div class="storeHouseAssignPop" style="display: none">
        <div class="storeHouse-content">
            <div class="goods-name">{{goodsInfo.product_name}}</div>
            <ul>
                <li class="goods-item">
                    <span class="left-item">品牌主：</span>
                    <span class="right-item">{{goodsInfo.brand}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">天猫商品ID：</span>
                    <span class="right-item">{{goodsInfo.tmall_product_id}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">投放量：</span>
                    <span class="right-item">{{goodsInfo.total_quantity}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">活动时间：</span>
                    <span class="right-item">{{filterDate(goodsInfo.activity_start_time)}}-{{filterDate(goodsInfo.activity_end_time)}}</span>
                </li>
            </ul>
            <div class="pop-title">
                <span>仓库分配</span>
                <div class="table-top-btn export-btn" style="display: none">导出</div>
            </div>
            <div class="pop-task-wrapper">
                <div class="pop-task-list" v-for="item in storeHouseSetList">
                    <div class="pop-task-list-top">
                        <div class="top-item-3 flex-box">
                            <div class="top-item-left customize1">分公司名称</div>
                            <div class="top-item-right customize1-right">{{item.company_name}}</div>
                        </div>
                        <div class="top-item-3 flex-box">
                            <div class="top-item-left customize2">仓库名称</div>
                            <div class="top-item-right customize2-right">{{item.barn_name}}</div>
                        </div>
                        <div class="top-item-2 flex-box">
                            <div class="top-item-left customize3">仓库ID</div>
                            <div class="top-item-right customize3-right">{{item.barn_id}}</div>
                        </div>
                        <div class="top-item-3 flex-box">
                            <div class="top-item-left customize4">派发量</div>
                            <div class="top-item-right customize4-right">
                                <span v-if="modify == 1">{{item.amount}}</span>
                                <input v-else type="text" class="table-input" placeholder="今日17点前可修改"
                                       v-model="item.amount" v-on:blur="judgeAmount($event,goodsInfo.total_quantity)"/>
                            </div>
                        </div>
                    </div>
                    <div class="pop-task-list-content">
                        <div class="list-content-title">点位 ({{item.node_num}})</div>
                        <div class="list-content">{{item.node_names}}</div>
                    </div>
                </div>

            </div>
            <div class="pop-bottom-wrapper">
                <div class="cancel-btn" v-on:click="cancelPop">取消</div>
                <div class="edit-btn" v-on:click="commitAmount">确定</div>
            </div>
        </div>
    </div>
    <div class="goodsSetPop" style="display: none">
        <div class="goodspop-scroll-wrapper" v-cloak>
            <div class="goods-name">{{goodsInfo.product_name}}</div>
            <ul>
                <li class="goods-item">
                    <span class="left-item">品牌主：</span>
                    <span class="right-item">{{goodsInfo.brand}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">天猫商品ID：</span>
                    <span class="right-item">{{goodsInfo.tmall_product_id}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">投放量：</span>
                    <span class="right-item">{{goodsInfo.total_quantity}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">活动时间：</span>
                    <span class="right-item">{{filterDate(goodsInfo.activity_start_time)}}-{{filterDate(goodsInfo.activity_end_time)}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">市场价：</span>
                    <span class="right-item">￥{{filterPrice(goodsInfo.market_price)}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">友宝商品ID：</span>
<!--                    <span class="right-item" v-if="goodsInfo.product_id">{{goodsInfo.product_id}}</span>-->
<!--                    <div class="ubox-goodsid-wrapper" v-else>-->
<!--                        <input class="table-input" type="text" v-model="uboxGoodsID"/>-->
<!--                    </div>-->
                    <div class="ubox-goodsid-wrapper">
                        <input class="table-input" type="text" v-model="goodsInfo.product_id"/>
                    </div>
                </li>
            </ul>
            <div class="pop-title">
                <span>图片素材配置</span>
            </div>
            <div class="image-upload">
                <div class="set-list-left">* 商品主图</div>
                <div class="set-list-right">
                    <img v-bind:src="goodsInfo.img_url" class="img-url" v-if="goodsInfo.img_url"
                         v-on:click="viewPicture(goodsInfo.img_url)"/>
                    <template v-else>
                        <input id="goodsImg" type="file" name="goodsImg" v-on:change="uploadImg(goodsInfo.id)"
                               accept="image/*" v-show="false"/>
                        <div class="pop-upload-btn" v-on:click="clickUpload">
                            <span class="upload-icon"></span><span>上传</span>
                        </div>
                        <div class="image-name" v-if="goodsInfo.imgName"
                             v-on:click="viewPicture(goodsInfo.localImgUrl)">
                            {{goodsInfo.imgName}}
                        </div>
                        <span class="description">png、jpg格式大小为778*999</span>
                    </template>
                </div>
            </div>
            <div class="pop-title">
                <span>派发时间配置</span>
            </div>
            <div class="distribute-time-set">
                <div class="distribute-time-set-list" v-for="(item,index) in goodsSetList">
                    <div class="list-wrapper list-top">
                        <div class="set-list-left">上下架时间</div>
                        <div class="set-list-right">
                            <div class="date-choose">
                                <input type="text" placeholder="请选择时间" :class="['date-input'+item.id,'date-time-input']" readonly
                                       :key="item.id"/>
                            </div>
                        </div>
                    </div>
                    <div class="list-wrapper list-content">
                        <div class="set-list-left">派发机器</div>
                        <div class="set-list-right">
                            <textarea class="machine-number" placeholder="请填入机器ID，并用英文逗号隔开" v-model="item.vm_codes"
                                      v-on:blur="judgeMachineNumber($event,goodsInfo.id)"></textarea>
                            <div class="pop-upload-btn" style="display: none"><span
                                        class="upload-icon"></span><span>上传</span></div>
                            <div class="operate-icon-wrapper">
                                <div class="operate-icon add" v-on:click="goodsSetAdd"></div>
                                <div class="operate-icon decrease" v-on:click="goodsSetDecrease(index)"
                                     v-show="goodsSetList.length>1"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="pop-bottom-btn">
            <div class="table-top-btn export-btn" style="display: none">导出</div>
            <div class="cancel-btn" v-on:click="cancelPop">取消</div>
            <div class="table-top-btn submit-btn" v-on:click="judgeSetList">确定</div>
        </div>
    </div>
    <div class="priceSetPop" style="display: none">
        <div class="priceSetPop-wrapper">
            <div class="goods-name">{{goodsInfo.product_name}}</div>
            <ul>
                <li class="goods-item">
                    <span class="left-item">品牌主：</span>
                    <span class="right-item">{{goodsInfo.brand}}</span>
                </li>
                <!--<li class="goods-item">-->
                <!--<span class="left-item">国际码：</span>-->
                <!--<span class="right-item">8763920013884</span>-->
                <!--</li>-->
                <li class="goods-item">
                    <span class="left-item">天猫商品ID：</span>
                    <span class="right-item">{{goodsInfo.tmall_product_id}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">投放量：</span>
                    <span class="right-item">{{goodsInfo.total_quantity}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">活动时间：</span>
                    <span class="right-item">{{filterDate(goodsInfo.activity_start_time)}}-{{filterDate(goodsInfo.activity_end_time)}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">市场价：</span>
                    <span class="right-item">￥{{filterPrice(goodsInfo.market_price)}}</span>
                </li>
                <li class="goods-item">
                    <span class="left-item">友宝商品ID：</span>
                    <span class="right-item">{{goodsInfo.product_id}}</span>
                </li>
            </ul>
            <div class="pop-title">
                <span>派发价格配置</span>
            </div>
            <div class="price-set-wrapper">
                <div class="price-set-list" v-for="(item,index) in priceSetList">
                    <div class="price-title price-title1">价格{{index+1}}</div>
                    <div class="price-input-wrapper"><input class="layui-input table-input price-set-margin" type="text"
                                                            placeholder="￥"
                                                            autocomplete="off" v-model="item.price"
                                                            v-on:blur="judgePrice($event)"/></div>
                    <div class="price-title price-title2">机器ID</div>
                    <div class="price-machine-number-wrapper">
                        <input class="price-machine-number layui-input table-input price-set-margin" type="text"
                               placeholder="请填入机器ID，并用英文逗号隔开" autocomplete="off" v-model="item.vm_codes"
                               v-on:blur="judgeMachineNumber($event,goodsInfo.id)"/>
                    </div>
                    <div class="operate-icon-wrapper">
                        <div class="operate-icon add" v-on:click="priceSetAdd"></div>
                        <div class="operate-icon decrease" v-on:click="priceSetDecrease(index)"
                             v-show="priceSetList.length>1"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="pop-bottom-btn">
            <div class="table-top-btn export-btn" style="display: none">导出</div>
            <div class="cancel-btn" v-on:click="cancelPop">取消</div>
            <div class="table-top-btn submit-btn" v-on:click="judgeSetList">确定</div>
        </div>
    </div>

</div>
<script>
    layui.use(['laydate', 'layer', 'flow'], function () {
        var laydate = layui.laydate;
        var flow = layui.flow;

        var app = new Vue({
            el: '#taskManagement',
            data: {
                taskList: [],
                goodsInfo: {},
                storeHouseSetList: {},
                goodsSetList: [],
                goodsSetItem: {
                    id: 0,
                    added_time: "",
                    dismounted_time: "",
                    vm_codes: ""
                },
                priceSetList: [],
                priceSetItem: {
                    id: 0,
                    price: "",
                    vm_codes: ""
                },
                setType: '',
                activity_time: '',
                end_time: '',
                // page: 1,
                taskStatus: 0,//0:未完成 4：已完成
                finishNum: 0,
                uboxGoodsID: '',
                modify: 0
            },
            created() {
                // this.page = 1;
            },
            mounted() {
                var that = this;
                that.setSearchDate();
                that.flow('', that.activity_time, that.end_time, that.taskStatus);
                that.getTaskListFinishNumber();
                //     $(that.$refs.searchInput).blur(function () {
                //         var val = $(that.$refs.searchInput).val();
                //         that.taskList = [];
                //         that.flow(val, that.activity_time, that.end_time);
                //     })
            },
            methods: {
                judgeAmount(event, quantity) {
                    var total = 0;
                    $.each(this.storeHouseSetList, function (index, value) {
                        total += Number(value.amount)
                    });
                    if (total > quantity) {
                        // if(event.target.value > quantity) {
                        var ycTip = '<div class="yc-tips-wrapper"><div class="yc-tip-content" style="width: 100px">超出最大投放量<i class="yc-tip-triangle"><\/i><\/div><\/div>';
                        $(event.target).after(ycTip);
                        setTimeout(function () {
                            $(event.target).siblings(".yc-tips-wrapper").fadeOut().remove();
                        }, 2000)
                        // layer.tips('超出最大投放量！', event.target, {
                        //     tips: [3, '#404040'],
                        //     time: 2000
                        // });
                    }
                },
                judgeMachineNumber(event, id) {
                    console.log(event.target.value, id);
                    if (event.target.value == '') {
                        return;
                    }
                    var data = {
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1021",
                            "devices": event.target.value,
                            "id": id
                        },
                        "comment": ""
                    };
                    $.ajax({
                        url: "<?php echo $request_url;?>",
                        type: 'POST',
                        dataType: 'json',
                        data: {json: JSON.stringify(data)},
                        success: function (res) {
                            if (res.status == 0) {
                                if (res.data.devices) {
                                    var ycTip = '<div class="yc-tips-wrapper" style="bottom:unset;"><div class="yc-tip-content" style="max-width: 266px;text-align:left;">机器ID ' + res.data.devices + ' 不存在<i class="yc-tip-triangle"><\/i><\/div><\/div>';
                                    $(event.target).after(ycTip);
                                    setTimeout(function () {
                                        $(event.target).siblings(".yc-tips-wrapper").fadeOut().remove();
                                    }, 3000)
                                }
                            } else {
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {
                            layer.msg('我们出错了');
                        }
                    });
                },
                judgePrice(event) {
                    var reg = /(^\d*[1-9]\d*$)|(^\d*[1-9]\d*[.]\d\d?$)|(^\d+[.][1-9]\d$)|(^\d+[.]\d[1-9]$)/;
                    if (!reg.test(event.target.value) && event.target.value.trim() !== '') {
                        var ycTip = '<div class="yc-tips-wrapper"><div class="yc-tip-content" style="width: 90px">价格格式错误<i class="yc-tip-triangle"><\/i><\/div><\/div>';
                        $(event.target).after(ycTip);
                        setTimeout(function () {
                            $(event.target).siblings(".yc-tips-wrapper").fadeOut().remove();
                        }, 2000)
                    }
                },
                setSearchDate() {
                    var that = this;
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

                    that.activity_time = beginning;
                    that.end_time = today;
                    console.log(defaultdate);
                    that.$nextTick(function () {
                        var searchDate = laydate.render({
                            elem: '.search-date'
                            , range: true
                            , type: 'date'
                            , value: defaultdate
                            , theme: 'purple'
                            , trigger: 'click'
                            , done: function (value, date, endDate) {
                                console.log(value);
                                if (value == '') {
                                    setTimeout(function () {
                                        $('.search-date').val('');
                                        searchDate.config.value = '';
                                    }, 0);
                                    that.activity_time = '';
                                    that.end_time = '';
                                } else {
                                    that.activity_time = value.split(' - ')[0];
                                    that.end_time = value.split(' - ')[1];
                                }
                                that.taskList = [];
                                var val = $(that.$refs.searchInput).val();
                                that.flow(val, that.activity_time, that.end_time, that.taskStatus);
                            }
                        });
                        $(".search-date").attr("lay-key", new Date().getTime());
                    })
                },
                filterPrice(price) {
                    if (price) {
                        return (Number(price) / 100)
                        // return (Number(price)/100).toFixed(2)
                    }
                },
                changeTaskStatus() {
                    this.taskStatus = this.taskStatus === 0 ? 4 : 0;
                    var val = $(this.$refs.searchInput).val();
                    this.taskList = [];
                    // if (this.taskStatus === 0) {
                    //     this.flow(val, this.activity_time, this.end_time, this.taskStatus);
                    // } else {
                    //     this.resetSearchDate();
                    //     // $(".search-date").val('');
                    //     this.flow('', '', '', this.taskStatus);
                    // }
                    this.flow(val, this.activity_time, this.end_time,this.taskStatus);
                    // if(this.taskStatus === 0) {
                    //     this.getTaskListFinishNumber();
                    // }
                },
                resetSearchDate() {
                    var that = this;
                    var searchDate = laydate.render({
                        elem: '.search-date'
                        , range: true
                        , type: 'date'
                        , value: ' '
                        , theme: 'purple'
                        , trigger: 'click'
                        , done: function (value, date, endDate) {
                            console.log(value);
                            if (value == '') {
                                setTimeout(function () {
                                    $('.search-date').val('');
                                    searchDate.config.value = '';
                                }, 0);
                                that.activity_time = '';
                                that.end_time = '';
                            } else {
                                that.activity_time = value.split(' - ')[0];
                                that.end_time = value.split(' - ')[1];
                            }
                            that.taskList = [];
                            var val = $(that.$refs.searchInput).val();
                            that.flow(val, that.activity_time, that.end_time, that.taskStatus);
                        }
                    });
                    $(".search-date").attr("lay-key", new Date().getTime());
                },
                flow(item_name, activity_time, end_time, taskStatus) {
                    var that = this;
                    // if(taskStatus==0) {
                    //     that.getTaskListFinishNumber(item_name,that.activity_time, that.end_time);
                    // }
                    flow.load({
                        elem: '.task-container' //指定列表容器
                        , done: function (page, next) { //到达临界点（默认滚动触发），触发下一页
                            //以jQuery的Ajax请求为例，请求下一页数据（注意：page是从2开始返回）
                            var data = {
                                "header": {
                                    "data_type": "proxy",
                                    "data_direction": "request",
                                    "server": "vod_http_server",
                                    "id": "vod_http_server"
                                },
                                "request": {
                                    "function": "1010",
                                    "item_name": item_name || '',
                                    "activity_time": activity_time || '',
                                    "end_time": end_time || '',
                                    "finish_flag": taskStatus || ''
                                },
                                "comment": ""
                            };
                            $.ajax({
                                url: '<?php echo $request_url;?>',
                                type: 'POST',
                                dataType: 'json',
                                data: {json: JSON.stringify(data), page: page},
                                success: function (res) {
                                    if (res.status == 0) {
                                        if (res.data.current_page == 1) {
                                            that.taskList = [];
                                        }
                                        that.taskList = that.taskList.concat(res.data.data);
                                        // if(taskStatus==4) {
                                        //     that.finishNum = res.data.total;
                                        // }
                                        console.log(that.taskList)
                                        var total = res.data.last_page;
                                        next('', page < total);
                                    } else {
                                        layer.msg(res.msg);
                                    }
                                },
                                error: function (response) {
                                    layer.msg('我们出错了');
                                }
                            });
                        }
                    });
                },
                searchTaskList(event) {
                    event.preventDefault();
                    var val = $(this.$refs.searchInput).val();
                    this.taskList = [];
                    this.flow(val, this.activity_time, this.end_time, this.taskStatus);
                },
                getTaskListFinishNumber(item_name, activity_time, end_time) {
                    var that = this;
                    var data = {
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1010",
                            "item_name": item_name || '',
                            "activity_time": activity_time || '',
                            "end_time": end_time || '',
                            "finish_flag": 4
                        },
                        "comment": ""
                    };
                    $.ajax({
                        url: '<?php echo $request_url;?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {json: JSON.stringify(data), page: that.page},
                        success: function (res) {
                            if (res.status == 0) {
                                that.finishNum = res.data.total;
                            } else {
                                that.finishNum = 0;
                                // layer.msg(res.msg);
                            }
                        },
                        error: function (response) {
                            that.finishNum = 0;
                            layer.msg('我们出错了');
                        }
                    });
                },
                getGoodsInfo(id) {
                    var that = this;
                    var data = {
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1018",
                            "id": id
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
                                that.goodsInfo = res.data;
                            } else {
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {
                            layer.msg('我们出错了');
                        }
                    });
                },

                popStoreHouseAssign(id, finish_flag) {
                    var that = this;
                    layui.use('layer', function () {
                        layer.open({
                            type: 1,
                            content: $('.storeHouseAssignPop'),
                            area: ['830px', '650px'],
                            title: false,
                            // scrollbar: false,
                            success: function (layero, index) {
                                that.setType = 1;
                                that.getGoodsInfo(id);
                                // if(finish_flag>0) {
                                that.getStoreHouseSetInfo(id);
                                // }else{
                                // that.initStoreHouseSetList();
                                // }
                            }
                        });

                    });
                },
                popGoodsSet(id, finish_flag) {
                    var that = this;
                    if (finish_flag == 0) {
                        return;
                    }
                    layui.use('layer', function () {
                        layer.open({
                            type: 1,
                            content: $('.goodsSetPop'),
                            area: ['805px', '700px'],
                            title: false,
                            // scrollbar: false,
                            success: function (layero, index) {
                                that.setType = 2;
                                that.uboxGoodsID = '';
                                that.getGoodsInfo(id);
                                if (finish_flag > 1) {
                                    that.getGoodsSetInfo(id);
                                } else {
                                    that.initGoodsSetList();
                                }
                            }
                        });

                    });
                },
                popPriceSet(id, finish_flag) {
                    var that = this;
                    if (finish_flag == 0 || finish_flag == 1) {
                        return;
                    }
                    layui.use('layer', function () {
                        layer.open({
                            type: 1,
                            content: $('.priceSetPop'),
                            area: ['800px', '560px'],
                            title: false,
                            // scrollbar: false,
                            success: function (layero, index) {
                                that.setType = 3;
                                that.getGoodsInfo(id);
                                if (finish_flag > 2) {
                                    that.getPriceSetInfo(id);
                                } else {
                                    that.initPriceSetList();
                                }
                            }
                        });
                    });
                },
                filterDate(date) {
                    if (date) {
                        return date.replace(/-/g, '/');
                    }
                },
                goodsSetAdd() {
                    this.goodsSetItem.id = this.goodsSetItem.id + 1;
                    this.goodsSetList.push(JSON.parse(JSON.stringify(this.goodsSetItem)));
                    this.initAddDateChoose();
                },
                initGoodsSetList() {
                    this.goodsSetList = [];
                    this.goodsSetItem.id = 0;
                    this.goodsSetList.push(JSON.parse(JSON.stringify(this.goodsSetItem)));
                    this.initDateChoose();
                },
                goodsSetDecrease(index) {
                    this.goodsSetList.splice(index, 1);
                    this.setDateChoose(this.goodsSetList);
                },
                initPriceSetList() {
                    this.priceSetList = [];
                    this.priceSetItem.id = 0;
                    this.priceSetList.push(JSON.parse(JSON.stringify(this.priceSetItem)));
                },
                priceSetAdd() {
                    this.priceSetItem.id = this.priceSetItem.id + 1;
                    this.priceSetList.push(JSON.parse(JSON.stringify(this.priceSetItem)));
                },
                priceSetDecrease(index) {
                    this.priceSetList.splice(index, 1);
                },
                judgeSetList() {
                    var list = [];
                    if (this.setType === 2) {
                        list = this.goodsSetList;
                    } else if (this.setType === 3) {
                        list = this.priceSetList;
                    }
                    // console.log(list);
                    var flag = 1;
                    // 校验
                    list.forEach(function (v, k) {
                        if (typeof(v.price) != 'undefined' && ((Math.floor(v.price * 100) / 100) !== parseFloat(v.price) || parseFloat(v.price) == 0)) {
                            //console.log((Math.floor(v.price * 100) / 100),parseFloat(v.price));
                            layer.msg('价格请用两位小数');
                            throw new TypeError('非法参数格式');
                        }

                        $.each(v, function (k2, v2) {
                            if (v2 === '') {
                                flag = 0;
                                layer.msg('配置项均为必填项');
                                return false;
                            }

                        });
                    });
                    if (flag === 1 && this.setType === 2) {
                        this.commitGoodsSet();
                    } else if (flag === 1 && this.setType === 3) {
                        this.commitPriceSet();
                    }

                },
                commitGoodsSet() {
                    console.log(this.goodsInfo);
                    console.log(this.goodsSetList);
                    var that = this;
                    var imgurl = that.goodsInfo.img_url ? that.goodsInfo.img_url : that.goodsInfo.localImgUrl;
                    if (!that.goodsInfo.product_id) {
                        // if (that.uboxGoodsID == '') {
                            layer.msg('请输入友宝商品ID');
                            return;
                        // }
                    }
                    if (!imgurl) {
                        layer.msg('请上传商品主图');
                        return;
                    }
                    var data = {
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1013",
                            "list": that.goodsSetList,
                            "activity_id": that.goodsInfo.activity_id,
                            "product_id": that.goodsInfo.product_id,
                            // "product_id": that.goodsInfo.product_id ? that.goodsInfo.product_id : that.uboxGoodsID,
                            "item_id": that.goodsInfo.tmall_product_id,
                            "img_url": imgurl,
                            "id": that.goodsInfo.id,
                            "finish_flag": that.goodsInfo.finish_flag
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
                                layer.msg('配置成功');
                                layer.closeAll('page');
                                // that.getTaskList();
                                that.taskList = [];
                                var val = $(that.$refs.searchInput).val();
                                that.flow(val, that.activity_time, that.end_time, that.taskStatus);
                            } else {
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {
                            layer.msg('我们出错了');
                        }
                    });
                },
                commitPriceSet() {
                    console.log(this.goodsInfo);
                    console.log(this.priceSetList);
                    var that = this;
                    var list = JSON.parse(JSON.stringify(that.priceSetList));
                    $.each(list, function (index, value) {
                        value.price = value.price * 100;
                    });
                    console.log(list);
                    var data = {
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1014",
                            "list": list,
                            // "list": that.priceSetList,
                            "activity_id": that.goodsInfo.activity_id,
                            "product_id": that.goodsInfo.product_id,
                            "item_id": that.goodsInfo.tmall_product_id,
                            "id": that.goodsInfo.id,
                            "finish_flag": that.goodsInfo.finish_flag
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
                                layer.msg('配置成功');
                                layer.closeAll('page');
                                // that.getTaskList();
                                that.taskList = [];
                                var val = $(that.$refs.searchInput).val();
                                that.flow(val, that.activity_time, that.end_time, that.taskStatus);
                                that.getTaskListFinishNumber();
                            } else {
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {
                            layer.msg('我们出错了');
                        }
                    });
                },
                cancelPop() {
                    layer.closeAll('tips');
                    layer.closeAll('page');
                },
                initDateChoose() {
                    var that = this;
                    that.$nextTick(function () {
                        lay('.date-time-input').each(function (index, elem) {
                            // console.log(elem);
                            laydate.render({
                                elem: elem
                                , range: true
                                , type: 'datetime'
                                , theme: 'purple'
                                , value: ' '
                                , position: 'fixed'
                                // , isInitValue: false
                                , done: function (value, date, endDate) {
                                    console.log(value);
                                    console.log(date);
                                    console.log(endDate);
                                    that.goodsSetList[index].added_time = value.split(' - ')[0];
                                    that.goodsSetList[index].dismounted_time = value.split(' - ')[1];
                                    console.log(that.goodsSetList);
                                }
                            });
                        });
                    })
                },
                initAddDateChoose() {
                    var that = this;
                    that.$nextTick(function () {
                        lay('.date-time-input').each(function (index, elem) {
                            // console.log(elem);
                            laydate.render({
                                elem: elem
                                , range: true
                                , type: 'datetime'
                                , theme: 'purple'
                                , position: 'fixed'
                                // , value: ' '
                                // , isInitValue: false
                                , done: function (value, date, endDate) {
                                    console.log(value);
                                    console.log(date);
                                    console.log(endDate);
                                    that.goodsSetList[index].added_time = value.split(' - ')[0];
                                    that.goodsSetList[index].dismounted_time = value.split(' - ')[1];
                                    console.log(that.goodsSetList);
                                }
                            });
                        });
                    })
                },
                setDateChoose(list) {
                    var that = this;
                    var name = '';
                    that.$nextTick(function () {
                        lay('.date-time-input').each(function (index, elem) {
                            name = 'date' + index;
                            var name = laydate.render({
                                elem: elem
                                , range: true
                                , type: 'datetime'
                                , theme: 'purple'
                                , position: 'fixed'
                                , value: list[index].added_time + ' - ' + list[index].dismounted_time
                                , done: function (value, date, endDate) {
                                    if (value == '') {
                                        setTimeout(function () {
                                            $(elem).val('');
                                            name.config.value = '';
                                        }, 0)
                                        that.goodsSetList[index].added_time = '';
                                        that.goodsSetList[index].dismounted_time = '';
                                    } else {
                                        that.goodsSetList[index].added_time = value.split(' - ')[0];
                                        that.goodsSetList[index].dismounted_time = value.split(' - ')[1];
                                    }
                                    console.log(that.goodsSetList);
                                }
                            });
                        });
                    })
                },
                getStoreHouseSetInfo(id) {
                    var that = this;
                    var data = {
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1011",
                            "id": id
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
                                that.storeHouseSetList = res.data.getTaskToWarehouse;
                                that.modify = res.data.modify;
                            } else {
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {
                            layer.msg('我们出错了');
                        }
                    })
                },
                commitAmount() {
                    var that = this;
                    var list = [],
                        item,
                        total = 0;
                    $.each(that.storeHouseSetList, function (index, value) {
                        item = {
                            id: value.id,
                            amount: value.amount
                        };
                        total += Number(value.amount);
                        list.push(item);
                    });
                    if (total > that.goodsInfo.total_quantity) {
                        layer.msg('超出最大投放量，请重新配置');
                        return;
                    }
                    console.log(list);
                    var data = {
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1012",
                            "id": that.goodsInfo.id,
                            "lists": list,
                            "finish_flag": that.goodsInfo.finish_flag
                        },
                        "comment": ""
                    };
                    $.ajax({
                        url: "<?php echo $request_url;?>",
                        type: 'POST',
                        dataType: 'json',
                        data: {json: JSON.stringify(data)},
                        success: function (res) {
                            if (res.status == 0) {
                                layer.msg('配置成功');
                                layer.closeAll('page');
                                that.taskList = [];
                                var val = $(that.$refs.searchInput).val();
                                that.flow(val, that.activity_time, that.end_time, that.taskStatus);
                            } else {
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {
                            layer.msg('我们出错了');
                        }
                    });
                },
                getGoodsSetInfo(id) {
                    var that = this;
                    var data = {
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1019",
                            "id": id
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
                                that.goodsSetList = res.data;
                                that.setDateChoose(res.data);
                                that.goodsSetItem.id = res.data[res.data.length - 1].id;
                            } else {
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {
                            layer.msg('我们出错了');
                        }
                    });
                },
                getPriceSetInfo(id) {
                    var that = this;
                    var data = {
                        "header": {
                            "data_type": "proxy",
                            "data_direction": "request",
                            "server": "vod_http_server",
                            "id": "vod_http_server"
                        },
                        "request": {
                            "function": "1020",
                            "id": id
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
                                var list = res.data;
                                $.each(list, function (index, value) {
                                    value.price = (Number(value.price) / 100);
                                    // value.price = (Number(value.price)/100).toFixed(2);
                                });
                                that.priceSetList = list;
                                that.priceSetItem.id = res.data[res.data.length - 1].id;
                            } else {
                                layer.msg(res.msg);
                            }
                        },
                        error: function (response) {
                            layer.msg('我们出错了');
                        }
                    });
                },
                uploadImg(id) {
                    var that = this;
                    console.log($("#goodsImg").get(0).files[0]);
                    that.$set(that.goodsInfo, 'imgName', $("#goodsImg").get(0).files[0].name);
                    var fd = new FormData();
                    fd.append("file", $("#goodsImg").get(0).files[0]);
                    fd.append("id", id);
                    $.ajax({
                        url: '<?php echo $request_upload;?>',
                        type: "POST",
                        processData: false,
                        contentType: false,
                        data: fd,
                        success: function (res) {
                            var result = JSON.parse(res);
                            if (result.status == 0) {
                                that.$set(that.goodsInfo, 'localImgUrl', result.data);
                                // that.goodsInfo.img_url = result.data;
                            } else {
                                layer.msg('');
                            }
                        },
                        error: function (response) {
                            layer.msg('我们出错了');
                        }
                    });
                },
                clickUpload() {
                    $("#goodsImg").click();
                },
                viewPicture(url) {
                    console.log(url);
                    layer.open({
                        type: 1,
                        title: false,
                        closeBtn: 0,
                        skin: 'layui-layer-nobg',
                        shadeClose: true,
                        area: ['520px', '520px'],
                        content: '<div class="picWrap"><img class="showAdPic" src=' + url + '><\/div>'
                    });
                },
                judgeStatus(type, status) {
                    switch (Number(type)) {
                        case 1:
                            if (status > 0) {
                                return 'finish';
                            } else {
                                return 'notStart'
                            }
                        case 2:
                            if (status > 1) {
                                return 'finish';
                            } else {
                                return 'notStart'
                            }
                        case 3:
                            if (status > 2) {
                                return 'finish';
                            } else {
                                return 'notStart'
                            }
                        default:
                            return 'notStart'
                    }
                },
            },
        })
    });

    layui.use('laydate', function () {
        console.log(1222);
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
            elem: '.search-date'
            , range: true
            , type: 'date'
            , value: defaultdate
            , theme: 'purple'
            , trigger: 'click'
        });
        $(".search-date").attr("lay-key", new Date().getTime());

    });

</script>



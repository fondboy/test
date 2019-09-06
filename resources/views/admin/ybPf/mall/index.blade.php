<link rel="stylesheet" href="/vendor/layui/css/layui.css">
<script src="/vendor/layui/layui.js"></script>


<style>



  td[data-field=city]>div.layui-table-cell{
    overflow: visible;
  }
  .layui-layer-tips{
    display: inline;
  }


  td .layui-form-select {
    margin-top: -10px;
    margin-left: -15px;
    margin-right: -15px;
  }



</style>

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="layui-tab">
  <ul class="layui-tab-title">
    <li class="layui-this">仓库创建</li>
    <li>商场绑定</li>
  </ul>
  <div class="layui-tab-content">
    <div class="layui-tab-item layui-show">
      <table class="layui-hide" id="warehouse" lay-filter="warehouse"></table>

      <script type="text/html" id="warehouseTool">
        <div class="form-search">
          <form id="testform">
            <input type="text" name="province" placeholder="省份" class="searchName">
            <input type="text" name="city" value="city" placeholder="城市" class="searchName">
            <i class="searchBtn iconfont icon-fangdajing" alt="" onclick="getSearchData('testform')">搜索</i>
          </form>
        </div>

       {{-- <div class="layui-btn-container">
          <button class="layui-btn layui-btn-sm" lay-event="getCheckData">获取选中行数据</button>
          <button class="layui-btn layui-btn-sm" lay-event="getCheckLength">获取选中数目</button>
          <button class="layui-btn layui-btn-sm" lay-event="isAll">验证是否全选</button>
        </div>--}}
      </script>

      <script type="text/html" id="warehouseBar">
        @{{# if(d.city==''){  }}
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        @{{#  } }}
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
      </script>


    </div>
    <div class="layui-tab-item">
      <table class="layui-hide" id="tmall_market" lay-filter="tmall_market"></table>

      <script type="text/html" id="tmall_marketTool">
        <div class="layui-btn-container">

        </div>

        {{--<div class="layui-btn-container">
          <button class="layui-btn layui-btn-sm" lay-event="getCheckData">获取选中行数据</button>
          <button class="layui-btn layui-btn-sm" lay-event="getCheckLength">获取选中数目</button>
          <button class="layui-btn layui-btn-sm" lay-event="isAll">验证是否全选</button>
        </div>--}}
      </script>

      <script type="text/html" id="tmall_marketBar">
        <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
        <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
      </script>

      <script type="text/html" id="tableSelectTemplate">
            @{{#  if(d.city!='' ){ }}
              @{{d.city}}
            @{{#  }else{ }}
          <select name="city" lay-filter="tdcity" attrid="@{{d.id}}" attrindex="@{{d.LAY_INDEX}}">
            <option value="">请选择</option>
            <option value="宁波">宁波</option>
            <option value="温州">温州</option>
            <option value="台州">台州</option>
            <option value="绍兴">绍兴</option>
          </select>
        @{{#  } }}
      </script>

    </div>
  </div>
</div>



<script>

  var tdselect='';
  var tdrowindex=0;
  var tdselectid=0;

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  layui.use('element', function(){
    var $ = layui.jquery
            ,element = layui.element; //Tab的切换功能，切换事件监听等，需要依赖element模块
  });

  layui.use('table', function(){
    var table = layui.table;
    var form = layui.form;

    table.render({
      elem: '#warehouse'
      ,url:'{{admin_url('mallLin/warehouseTable')}}'
      ,toolbar: '#warehouseTool'
      ,where: getSearchData('testform')
      ,title: '仓库列表'
      ,limit: 15
      ,cols: [[
         {type: 'checkbox', fixed: 'left'}
        ,{field:'id', title:'ID',  fixed: 'left', unresize: true, sort: true}
        ,{field:'name', title:'name', edit: 'text'}
        ,{field:'barn_id', title:'barn_id', edit: 'text', sort: true}
        ,{field:'org5_id', title:'org5_id'}
        ,{field:'province', title:'province'}
        ,{field:'city', title:'city',templet:"#tableSelectTemplate"}
        ,{field:'zone', title:'zone'}
        ,{field:'address', title:'address',sort: true}
        ,{field:'contact_name', title:'contact_name'}
        ,{field:'contact_phone', title:'contact_phone'}
        ,{field:'binding_barn_id', title:'binding_barn_id'}
        ,{field:'create_at', title:'create_at'}
        ,{field:'update_at', title:'update_at'}
        ,{fixed: 'right', title:'操作', toolbar: '#warehouseBar', width:150}
      ]]
      ,page: true
    });

 /*     table.exportFile(['名字','性别','年龄'], [
          ['张三','男','20'],
          ['李四','女','18'],
          ['王五','女','19']
      ], 'csv');*/

  /*  form.on('select(tdcity)', function(obj){
        tdselect=obj.value;
        tdrowindex=$(obj.elem).attr('attrindex');
        tdselectid=$(obj.elem).attr('attrid');
        //console.log($(obj.elem).attr('attrid'),obj.value);
    });*/

    //头工具栏事件
    table.on('toolbar(warehouse)', function(obj){
      var checkStatus = table.checkStatus(obj.config.id);
      switch(obj.event){
        case 'getCheckData':
          var data = checkStatus.data;
          layer.alert(JSON.stringify(data));
          break;
        case 'getCheckLength':
          var data = checkStatus.data;
          layer.msg('选中了：'+ data.length + ' 个');
          break;
        case 'isAll':
          layer.msg(checkStatus.isAll ? '全选': '未全选');
          break;
      };
    });

    //监听行工具事件
    table.on('tool(warehouse)', function(obj){
      var data = obj.data;
      //console.log(obj)
      if(obj.event === 'del'){
         console.log(tdselect);
        layer.confirm('真的删除行么', function(index){
          $.post("{{admin_url('mallLin/warehouseDel')}}",{id:data.id,update_at:data.update_at},function(res){
              if(res.code===0){
                obj.del();
                layer.close(index);
              }else{
                alert(res.msg);
            }
          },'json');

        });
      } else if(obj.event === 'edit'){
          if(tdrowindex==0||tdselect==''||tdselectid==0||tdselectid!=data.id){
              return alert(tdselectid+'请选择城市'+data.id);
          }
          $($("td[data-field=city]>")[tdrowindex-1]).html(tdselect);
         return ;
        layer.prompt({
          formType: 2
          ,value: data.email
        }, function(value, index){
          obj.update({
            email: value
          });
          layer.close(index);
        });
      }
    });

    // 搜索框
    var $ = layui.$, active = {
      reload: function () {
        var demoReload = $('#demoReload');

        table.reload('testReload', {
          where: {
            keyword: demoReload.val()
          }
        });
      }
    };

    $('.demoTable .layui-btn').on('click', function () {
      var type = $(this).data('type');
      active[type] ? active[type].call(this) : '';
    });

  });


  layui.use('table', function(){
    var table = layui.table;

    table.render({
      elem: '#tmall_market'
    //  ,url:'{{admin_url('mallLin/warehouseTable')}}'
      ,toolbar: '#tmall_marketTool'
      ,title: '天猫商场'
      ,limit: 15
      ,cols: [[
        {type: 'checkbox', fixed: 'left'}
        ,{field:'id', title:'ID', width:80, fixed: 'left', unresize: true, sort: true}
        ,{field:'name', title:'name', width:120, edit: 'text'}
        ,{field:'barn_id', title:'barn_id', width:80, edit: 'text', sort: true}
        ,{field:'org5_id', title:'org5_id', width:100}
        ,{field:'province', title:'province'}
        ,{field:'city', title:'city', width:80, sort: true}
        ,{field:'zone', title:'zone', width:120}
        ,{field:'address', title:'address', width:100, sort: true}
        ,{field:'contact_name', title:'contact_name', width:120}
        ,{field:'contact_phone', title:'contact_phone', width:120}
        ,{field:'binding_barn_id', title:'binding_barn_id', width:120}
        ,{field:'create_at', title:'create_at', width:120}
        ,{fixed: 'right', title:'操作', toolbar: '#tmall_marketBar', width:150}
      ]]
      ,page: true
    });

    //头工具栏事件
    table.on('toolbar(tmall_market)', function(obj){
      var checkStatus = table.checkStatus(obj.config.id);
      switch(obj.event){
        case 'getCheckData':
          var data = checkStatus.data;
          layer.alert(JSON.stringify(data));
          break;
        case 'getCheckLength':
          var data = checkStatus.data;
          layer.msg('选中了：'+ data.length + ' 个');
          break;
        case 'isAll':
          layer.msg(checkStatus.isAll ? '全选': '未全选');
          break;
      };
    });


  });


  function getSearchData(formid){
      var res     = [];
      $('#'+formid).serializeArray().forEach(function(v,k){
          if(v.value!=''){
              res[v.name] = v.value;
          }
      });

      console.log(res);

      return res;
  }




</script>
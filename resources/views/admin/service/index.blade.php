<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>服务列表</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/static/admin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/static/admin/font-awesome/css/font-awesome.min.css" media="all">
    <style>font{vertical-align:baseline!important;}</style>
</head>
<body class="layui-layout-body" style="overflow-y:visible;background: #fff;">
<div class="layui-form">
    <blockquote class="layui-elem-quote quoteBox">
        <div class="layui-inline" style="margin-left: 2rem;">
            <a class="layui-btn add">添加服务</a>
        </div>
        <div class="layui-inline" style="margin-left: 1rem;">
            <select id="search_type">
                <option value="0">请选择服务</option>
                <option value="1">标准服务</option>
                <option value="2">附加服务</option>
            </select>
        </div>
        <div class="layui-inline" style="margin-left: 1rem;">
            <a class="layui-btn  layui-btn-normal" id="search">搜索</a>
        </div>
    </blockquote>
</div>
<table class="layui-hide" id="table" lay-filter="table"></table>
<div id="edit" class="layui-form layui-form-pane" style="display: none;margin:1rem 3rem;">
  <div class="layui-form-item img" style="display: none;">
    <label class="layui-form-label"></label>
    <div class="layui-input-block">
        <img src="" alt="" id="img_show" style="width: 355.55px;height: 200px;">
    </div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">排序</label>
    <div class="layui-input-block">
      <input type="text" placeholder="数字越大越靠前" class="layui-input" value="0" id="sort">
      <span style="color:red;">*排序数字越大，越靠前</span>
    </div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">服务名称</label>
    <div class="layui-input-block">
      <input type="text" placeholder="请输入服务名称" class="layui-input" value="" id="title">
    </div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">服务类型</label>
    <div class="layui-input-block">
      <select id="type">
        <option value="0">请选择服务类型</option>
        <option value="1">标准服务</option>
        <option value="2">附加服务</option>
      </select>
    </div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">服务价格</label>
    <div class="layui-input-block">
      <input type="text" placeholder="请输入服务价格" class="layui-input" value="" id="price">
    </div>
  </div>
  <div class="layui-form-item">
    <div class="layui-input-block">
      <button class="layui-btn" lay-filter="formDemo" id="submit">立即提交</button>
    </div>
  </div>
</div>
<script type="text/html" id="type_name">
@{{# if(d.type == 1){ }}
    标准服务
@{{# }else if(d.type == 2){ }}
    附加服务
@{{# }else{ }}
    未知服务
@{{# } }}
</script>
<script type="text/html" id="buttons">
  <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
  <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>
<script src="/static/admin/jquery/jquery.min.js"></script>
<script src="/static/admin/layui/layui.js"></script>
<script type="text/javascript">
var id;
$.ajaxSetup({
    headers:{
        'X-CSRF-TOKEN': "{{csrf_token()}}"
    },
    dataType:'json',
    timeout:5000,
    beforeSend:function(){
        layer.load();
    },
    error:function(xhr){
        if(xhr.status == 419){
          layer.msg('CSRF验证过期,请刷新本页面后重试');
        }else if(xhr.status == 403){
          layer.msg('请检查您是否有权限');
        }else{
          layer.msg('访问出错');
        }
    },
    complete:function(){
        layer.closeAll('loading');
    }
});
layui.use(['table','form','layer'], function(){
  var table = layui.table;
  var form = layui.form;
  var layer = layui.layer;
  table.render({
    elem: '#table' //表格id
    ,url:"{{route('admin.service.list')}}"//list接口地址
    ,cellMinWidth: 80 //全局定义常规单元格的最小宽度
    ,height: 'full-120',
    page: true,
    limits: [14, 25, 45, 60],
    limit: 14
    ,cols: [[
    //align属性是文字在列表中的位置 可选参数left center right
    //sort属性是排序功能
    //title是这列的标题
    //field是取接口的字段值
    //width是宽度，不填则自动根据值的长度
      {field:'id', title: 'ID',align: 'center'},
      {field:'sort',title: '排序',align: 'center'},
      {field:'title',title: '名称',align: 'center'},
      {field:'type',title: '类型',align: 'center',templet:'#type_name'},
      {field:'price',title: '价格',align: 'center'},
      {field:'create_time', title: '创建时间',align: 'center'},
      {field:'update_time', title: '最后修改时间',align: 'center'},
      {fixed:'right',title: '操作', align:'center', toolbar: '#buttons'}
    ]],
  });
  //监听
  table.on('tool(table)', function(obj){
      console.log(obj);
      //data就是一行的数据
      var data = obj.data;
      if(obj.event === 'del'){
          layer.confirm('真的删除吗', function(index){
              $.post("{{route('admin.service.delete')}}",{id:data.id},function(res){
                if (res.status == 1) {
                    obj.del();//删除表格这行数据
                }
                layer.msg(res.msg);
              })
          });
      }else if(obj.event === 'edit'){
          id = data.id;
          $('#title').val(data.title);
          $('#price').val(data.price);
          $('#sort').val(data.sort);
          $('#type').val(data.type);
          layui.use('form', function(){
          var form = layui.form;
              form.render('select');
          });
          layer.open({
            type: 1,
            title:'编辑服务',
            skin: 'layui-layer-rim', //加上边框
            area: ['50rem;', '22rem;'], //宽高
            content: $('#edit'),
          });
      }
    });
});
$('.add').click(function(){
    id = 0;
    $('#title').val('');
    $('#price').val('');
    $('#sort').val('0');
    $('#type').val('0');
    layui.use('form', function(){
        var form = layui.form;
        form.render('select');
    });
    layer.open({
            type: 1,
            title:'添加服务',
            skin: 'layui-layer-rim', //加上边框
            area: ['50rem;', '22rem;'], //宽高
            content: $('#edit'),
          });
})
$('#submit').click(function(){
    var data = {
        id:id,
        sort:$('#sort').val(),
        title:$('#title').val(),
        price:$('#price').val(),
        type:$('#type').val(),
    };
    if(!id || id == '0'){
        var url = "{{route('admin.service.create')}}";
    }else{
        var url = "{{route('admin.service.edit')}}";
    }
    $.post(url,data,function(res){
        if (res.status == 1) {
            layer.closeAll();
            layui.use('table', function(){
                var table = layui.table;
                table.reload('table', { //表格的id
                    url:"{{route('admin.service.list')}}",
                });
          })
        }
        layer.msg(res.msg);
    },'json');
})
$('#search').click(function(){
      //传递where条件实现搜索，并且重载表格数据
      layui.use('table', function(){
            var table = layui.table;
            table.reload('table', { //表格的id
                url:"{{route('admin.service.list')}}",
                where:{
                    'type':$('#search_type').val(),
                }
            });
      })
})
$(document).on('keydown', function(e){
    if(e.keyCode == 13){
        $('#search').click();
    }
})
</script>
</html>
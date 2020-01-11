<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>充值优惠管理</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/static/admin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/static/admin/font-awesome/css/font-awesome.min.css" media="all">
    <style>font{vertical-align:baseline!important;}</style>
</head>
<body class="layui-layout-body" style="overflow-y:visible;background: #fff;">
<form class="layui-form">
    <blockquote class="layui-elem-quote quoteBox">
        <div class="layui-inline" style="margin-left: 2rem;">
            <a class="layui-btn add">添加充值优惠</a>
        </div>
    </blockquote>
</form>
<table class="layui-hide" id="table" lay-filter="table"></table>
<div id="edit" class="layui-form layui-form-pane" style="display: none;margin:1rem 3rem;">
  <div class="layui-form-item">
    <label class="layui-form-label">满多少</label>
    <div class="layui-input-block">
      <input type="text" placeholder="请输入满多少金额赠送" class="layui-input" value="" id="full">
    </div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">送多少</label>
    <div class="layui-input-block">
      <input type="text" placeholder="请输入赠送多少余额" class="layui-input" value="" id="give">
    </div>
  </div>
  <div class="layui-form-item">
    <div class="layui-input-block">
      <button class="layui-btn" lay-filter="formDemo" id="submit">立即提交</button>
    </div>
  </div>
</div>
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
    error:function(){
        layer.msg('        if(xhr.status == 419){
          layer.msg('CSRF验证过期,请刷新本页面后重试');
        }else if(xhr.status == 403){
          layer.msg('请检查您是否有权限');
        }else{
          layer.msg('访问出错');
        }');
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
    ,url:"{{route('admin.marketing.recharge.list')}}"//list接口地址
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
      {field:'full',title: '满多少',align: 'center'},
      {field:'give',title: '送多少',align: 'center',templet:'#img_path'},
      {field:'create_time', title: '创建时间',align: 'center'},
      {field:'update_time', title: '最后修改时间',align: 'center'},
      {fixed:'right',title: '操作', align:'center', toolbar: '#buttons'}
    ]]
  });
  //监听
  table.on('tool(table)', function(obj){
      console.log(obj);
      //data就是一行的数据
      var data = obj.data;
      if(obj.event === 'del'){
          layer.confirm('真的删除吗', function(index){
              $.post("{{route('admin.marketing.recharge.delete')}}",{id:data.id},function(res){
                if (res.status == 1) {
                    obj.del();//删除表格这行数据
                }
                layer.msg(res.msg);
              })
          });
      }else if(obj.event === 'edit'){
          id = data.id;
          $('#full').val(data.full);
          $('#give').val(data.give);
          layer.open({
            type: 1,
            title:'编辑充值优惠',
            skin: 'layui-layer-rim', //加上边框
            area: ['50rem;', '14rem;'], //宽高
            content: $('#edit'),
          });
      }
    });
});
$('.add').click(function(){
    id = 0;
    $('#full').val('');
    $('#give').val('');
    layer.open({
            type: 1,
            title:'添加充值优惠',
            skin: 'layui-layer-rim', //加上边框
            area: ['50rem;', '14rem;'], //宽高
            content: $('#edit'),
    });
})
$('#submit').click(function(){
    var data = {
        id:id,
        full:$('#full').val(),
        give:$('#give').val()
    };
    if(!id || id == '0'){
        var url = "{{route('admin.marketing.recharge.create')}}";
    }else{
        var url = "{{route('admin.marketing.recharge.edit')}}";
    }
    $.post(url,data,function(res){
        if (res.status == 1) {
            layer.closeAll();
            layui.use('table', function(){
                var table = layui.table;
                table.reload('table', { //表格的id
                    url:"{{route('admin.marketing.recharge.list')}}",
                });
          })
        }
        layer.msg(res.msg);
    });
})
</script>
</html>
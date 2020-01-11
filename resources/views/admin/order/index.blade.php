<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>轮播图列表</title>
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
        <div class="layui-inline" style="margin-left: 1rem;width: 22rem;">
            <input type="text" placeholder="请输入订单号,姓名,手机号,昵称,OPENID" class="layui-input" id="keyword">
        </div>
        <div class="layui-inline" style="margin-left: 1rem;">
            <select id="search_status">
                <option value=" ">请选择订单状态</option>
                <option value="0">待付款</option>
                <option value="1">进行中</option>
                <option value="2">已完成</option>
                <option value="-1">已取消</option>
            </select>
        </div>
        <div class="layui-inline" style="margin-left: 1rem;">
            <select id="search_type">
                <option value="0">请选择服务类型</option>
                <option value="1">店内</option>
                <option value="2">邮寄</option>
            </select>
        </div>
        <div class="layui-inline" style="margin-left: 1rem;">
            <a class="layui-btn  layui-btn-normal" id="search">搜索</a>
        </div>
    </blockquote>
</form>
<table class="layui-hide" id="table" lay-filter="table"></table>
<script type="text/html" id="avatar_url">
  <div>
    <a href="#">
      <img src="@{{d.avatar_url}}" style="width: 25px">
    </a>
  </div>
</script>
<script type="text/html" id="status">
@{{# if(d.status == 0){ }}
    <span style="color:red;">待付款</span>
@{{# }else if(d.status == 1){ }}
    <span style="color:#ffbf00;">进行中</span>
@{{# }else if(d.status == 2){ }}
    <span style="color:green;">已完成</span>
@{{# }else if(d.status == -1){ }}
    <span style="color:#808080;">已取消</span>
@{{# } }}
</script>
<script type="text/html" id="buttons">
  <a class="layui-btn layui-btn-xs" lay-event="edit">查看</a>
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
        layer.msg('请检查是否有权限,如有权限请刷新后重试');
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
    ,url:"{{route('admin.order.list')}}"//list接口地址
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
      {field:'ordersn', title: '订单号',align: 'center',width:150},
      {field:'avatar_url',title: '微信头像',align: 'center',templet:'#avatar_url',width:100},
      {field:'nickname',title: '微信昵称',align: 'center',width:150},
      {field:'openid',title: 'OPENID',align: 'center',width:260},
      {field:'name',title: '姓名',align: 'center',width:100},
      {field:'phone',title: '手机号',align: 'center'},
      {field:'type',title: '服务类型',align: 'center',width:100,templet:function(d){
          if(d.type == 1){
              return '店内';
          }else if(d.type == 2){
              return '邮寄';
          }
      }},
      {field:'status',title: '订单状态',align: 'center',templet:"#status",width:120},
      {field:'total_price',title: '总价',align: 'center',templet:function(d){
          return d.total_price + "元";
      }},
      {field:'payable_price',title: '实付价',align: 'center',templet:function(d){
          return d.payable_price + "元";
      }},
      {field:'create_time', title: '创建时间',align: 'center'},
      {field:'update_time', title: '最后修改时间',align: 'center'},
      {fixed:'right',title: '操作', align:'center', toolbar: '#buttons'}
    ]],
    done: function () {
        hoverOpenImg();
    }
  });
  //监听
  table.on('tool(table)', function(obj){
      //data就是一行的数据
      var data = obj.data;
        if(obj.event === 'edit'){
          var index = layer.open({
            type: 2,
            title:'查看订单',
            maxmin: true,
            content: "{{route('admin.order.see')}}?id="+data.id,
            end:function(){
                $('#search').click();
            }
          });
         layer.full(index);
      }
    });
});
function hoverOpenImg(){
        var img_show = null;
        $('td img').hover(function () {
            var kd = $(this).width();
            var kd1 = kd * 8;
            var kd2 = kd * 8 + 30;
            var img = "<img class='img_msg' src='" + $(this).attr('src') + "' style='width:" + kd1 + "px;' />";
            img_show = layer.tips(img, this, {
                tips: [2, 'rgba(41,41,41,.1)']
                , area: [kd2 + 'px']
            });
        }, function () {
            layer.close(img_show);
        });
}
$('#search').click(function(){
      //传递where条件实现搜索，并且重载表格数据
      layui.use('table', function(){
            var table = layui.table;
            table.reload('table', { //表格的id
                url:"{{route('admin.order.list')}}",
                where:{
                    'status':$('#search_status').val(),
                    'keyword':$('#keyword').val(),
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
$(function(){
    var openid = getQueryVariable('openid');
    if(openid){
        $('#keyword').val(openid);
        setTimeout(function(){
            layui.use('table', function(){
                var table = layui.table;
                table.reload('table', { //表格的id
                    url:"{{route('admin.order.list')}}",
                    where:{
                        'status':$('#search_status').val(),
                        'keyword':$('#keyword').val(),
                        'type':$('#search_type').val(),
                    }
                });
            })
        },200)
    }
})
function getQueryVariable(variable){
   var query = window.location.search.substring(1);
   var vars = query.split("&");
   for (var i=0;i<vars.length;i++) {
       var pair = vars[i].split("=");
       if(pair[0] == variable){return pair[1];}
   }
   return(false);
}
</script>
</html>
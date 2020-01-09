<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>会员列表</title>
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
        <div class="layui-inline" style="margin-left: 1rem;width: 15rem;">
            <input type="text" placeholder="请输入昵称,OPENID,会员ID" class="layui-input" id="keyword">
        </div>
        <div class="layui-inline" style="margin-left: 1rem;">
            <a class="layui-btn  layui-btn-normal" id="search">搜索</a>
        </div>
    </blockquote>
</div>
<table class="layui-hide" id="table" lay-filter="table"></table>
<div id="recharge" class="layui-form layui-form-pane" style="display: none;margin:1rem 3rem;">
  <div class="layui-form-item">
    <label class="layui-form-label">操作</label>
    <div class="layui-input-block">
      <input type="radio" name="type" value="1" title="增加" checked>
      <input type="radio" name="type" value="2" title="减少">
      <input type="radio" name="type" value="3" title="最终">
    </div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">金额</label>
    <div class="layui-input-block">
      <input type="text" placeholder="请输入金额" class="layui-input" value="0" id="price">
    </div>
  </div>
  <div class="layui-form-item">
    <label class="layui-form-label">备注</label>
    <div class="layui-input-block">
      <input type="text" placeholder="请输入备注" class="layui-input" value="" id="remark">
    </div>
  </div>
  <div class="layui-form-item">
    <div class="layui-input-block">
      <button class="layui-btn" lay-filter="formDemo" id="submit">立即提交</button>
    </div>
  </div>
</div>
<script type="text/html" id="avatar">
  <div>
    <a href="#">
      <img src="@{{d.avatar_url}}" style="width: 25px">
    </a>
  </div>
</script>
<script type="text/html" id="buttons">
  <a class="layui-btn layui-btn-xs" lay-event="recharge">充值</a>
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
        layer.msg('访问出错,请检查是否有权限');
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
    ,url:"{{route('admin.member.list')}}"//list接口地址
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
      {field:'id', title: '会员ID',align: 'center'},
      {field:'avatar_url',title: '会员头像',align: 'center',templet:'#avatar'},
      {field:'openid',title: 'OPENID',align: 'center'},
      {field:'nickname',title: '会员昵称',align: 'center'},
      //{field:'phone',title: '会员手机号',align: 'center'},
      {field:'balance',title: '会员余额',align: 'center'},
      {field:'create_time', title: '注册时间',align: 'center'},
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
      $("#price").val('');
      $('#remark').val('');
      if(obj.event === 'recharge'){
        id = data.id;
        layer.open({
            type: 1,
            title:'充值',
            skin: 'layui-layer-rim', //加上边框
            area: ['50rem;', '18rem;'], //宽高
            content: $('#recharge'),
          });
      }
    });
});
$('#submit').click(function(){
    var data = {
        id:id,
        type:$("input[type='radio']:checked").val(),
        price:$("#price").val(),
        remark:$('#remark').val(),
    };
    var url = "{{route('admin.member.recharge')}}";
    $.post(url,data,function(res){
        if (res.status == 1) {
            layer.closeAll();
            layui.use('table', function(){
                var table = layui.table;
                table.reload('table', { //表格的id
                    url:"{{route('admin.member.list')}}",
                });
          })
        }
        layer.msg(res.msg);
    },'json');
})
function hoverOpenImg() {
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
                url:"{{route('admin.member.list')}}",
                where:{
                    'keyword':$('#keyword').val(),
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
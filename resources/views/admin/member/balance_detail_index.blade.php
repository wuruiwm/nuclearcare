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
</head>
<body class="layui-layout-body" style="overflow-y:visible;background: #fff;">
<form class="layui-form">
    <blockquote class="layui-elem-quote quoteBox">
        <div class="layui-inline" style="margin-left: 1rem;width: 15rem;">
            <input type="text" placeholder="请输入昵称,OPENID,用户ID" class="layui-input" id="keyword">
        </div>
        <div class="layui-inline" style="margin-left: 1rem;">
            <a class="layui-btn  layui-btn-normal" id="search">搜索</a>
        </div>
    </blockquote>
</form>
<script type="text/html" id="avatar">
  <div>
    <a href="#">
      <img src="@{{d.avatar_url}}" style="width: 25px">
    </a>
  </div>
</script>
<script type="text/html" id="type">
@{{# if(d.type == 1){ }}
    增加
@{{# }else if(d.type == 2){ }}
    减少
@{{# }else if(d.type == 3){ }}
    最终
@{{# } }}
</script>
<table class="layui-hide" id="table" lay-filter="table"></table>
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
    ,url:"{{route('admin.member.balance_detail_list')}}"//list接口地址
    ,cellMinWidth: 80 //全局定义常规单元格的最小宽度
    ,height: 'full-120',
    page: true,
    limits: [15, 25, 45, 60],
    limit: 15
    ,cols: [[
    //align属性是文字在列表中的位置 可选参数left center right
    //sort属性是排序功能
    //title是这列的标题
    //field是取接口的字段值
    //width是宽度，不填则自动根据值的长度
      {field:'id', title: 'ID',align: 'center'},
      {field:'avatar_url',title: '会员头像',align: 'center',templet:'#avatar'},
      {field:'openid',title: 'OPENID',align: 'center'},
      {field:'nickname',title: '会员昵称',align: 'center'},
      {field:'type', title: '操作',align: 'center',templet:'#type'},
      {field:'price',title: '金额',align: 'center'},
      {field:'remark', title: '备注',align: 'center'},
      {field:'create_time', title: '创建时间',align: 'center'},
      {field:'update_time', title: '最后修改时间',align: 'center'},
    ]],
    done: function () {
        hoverOpenImg();
    }
  });
});
$('#search').click(function(){
      //传递where条件实现搜索，并且重载表格数据
      layui.use('table', function(){
            var table = layui.table;
            table.reload('table', { //表格的id
                url:"{{route('admin.member.balance_detail_list')}}",
                where:{
                    'keyword':$('#keyword').val(),
                }
            });
      })
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
</script>
</html>
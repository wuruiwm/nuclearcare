<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>优惠券列表</title>
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
                    <a class="layui-btn add">添加优惠券</a>
                </div>
        </blockquote>
    </div>
<table class="layui-hide" id="table" lay-filter="table"></table>
</body>
<div id="qrcode" style="display:none;">
    <img src="" style="width:100%;height:100%;">
</div>
<script type="text/html" id="buttons">
    <a class="layui-btn layui-btn-xs" lay-event="show">查看优惠券码</a>
    <a class="layui-btn layui-btn-xs" lay-event="edit">编辑</a>
    <a class="layui-btn layui-btn-danger layui-btn-xs" lay-event="del">删除</a>
</script>
<script src="/static/admin/jquery/jquery.min.js"></script>
<script src="/static/admin/layui/layui.js"></script>
<script type="text/javascript">
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
    ,url:"{{route('admin.marketing.coupon.list')}}"//list接口地址
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
      {field:'id', title: 'ID',align: 'center',width:100},
      {field:'face_value',title: '面值',align: 'center'},
      {field:'validity_time',title: '领取后有效期',align: 'center',width:150,templet:function(d){
          return d.validity_time + '天';
      }},
      {field:'full',title: '券满多少可用',align: 'center',width:150},
      {field:'total',title: '优惠券总数',align: 'center',width:100},
      {field:'receive_num',title: '已领取数',align: 'center',width:100},
      {field:'start_time',title: '开始领取日期',align: 'center',width:180},
      {field:'end_time',title: '结束领取日期',align: 'center',width:180},
      {field:'create_time', title: '创建时间',align: 'center',width:180},
      {field:'update_time', title: '最后修改时间',align: 'center',width:180},
      {fixed:'right',title: '操作', align:'center', toolbar: '#buttons',width:210}
    ]]
  });
  form.on('switch(is_status)', function (obj) {
        //找被点击的div同级的input上的data-id
        var id = obj.othis.siblings("input").data('id');
        var bool = obj.elem.checked;
        $.post("{:url('admin/coupon/status')}",{id:id,status:bool},function(res){
            console.log(res);
        },'json')
  });
    //监听
  table.on('tool(table)', function(obj){
      console.log(obj);
      //data就是一行的数据
      var data = obj.data;
      if(obj.event === 'del'){
          layer.confirm('真的删除吗', function(index){
              $.post("{{route('admin.marketing.coupon.delete')}}",{id:data.id},function(res){
                if (res.status == 1) {
                    obj.del();//删除表格这行数据
                }
                layer.msg(res.msg);
              },'json')
          });
      }else if(obj.event === 'edit'){
        var index = layer.open({
            type: 2,
            content: "{{route('admin.marketing.coupon.edit')}}"+'?id='+data.id,
            maxmin: true,
            title:'编辑优惠券'
        });
        layer.full(index);
      }else if(obj.event == 'show'){
          $.post("{{route('admin.marketing.coupon.qrcode')}}",{id:data.id},function(res){
                if(res.status == 1){
                    $('#qrcode img').attr('src',res.url);
                    layer.open({
                        type: 1,
                        title: false,
                        closeBtn: 0,
                        area: ['20rem', '20rem'],
                        skin: 'layui-layer-nobg', //没有背景色
                        shadeClose: true,
                        content: $('#qrcode')
                    });
                }else{
                    layer.msg(res.msg);
                }
          })
      }
    });
});
$('.add').click(function(){
    var index = layer.open({
        type: 2,
        content: "{{route('admin.marketing.coupon.create')}}",
        maxmin: true,
        title:'添加优惠券'
    });
    layer.full(index);
})
</script>
</html>
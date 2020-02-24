<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>首页长图</title>
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
            <a class="layui-btn upload">上传长图</a>
            <span style="color: red;">*推荐图片宽度为750</span>
        </div>
    </blockquote>
</div>
<img src="{{$img_url}}" id="img_show">
</body>
<script src="/static/admin/jquery/jquery.min.js"></script>
<script src="/static/admin/layui/layui.js"></script>
<script>
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
layui.use('upload', function(){
  var upload = layui.upload;
  //执行实例
  var uploadInst = upload.render({
    elem: '.upload',
    url: "{{route('admin.banner.long_edit')}}",
    done: function(res){
        console.log(res);
        if(res.status == 1){
            $('#img_show').attr('src',res.url);
        }
    }
  });
});
</script>
</html>
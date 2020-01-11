<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>查看订单</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="/static/admin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="/static/admin/font-awesome/css/font-awesome.min.css" media="all">
</head>
<body class="layui-layout-body" style="overflow-y:visible;">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 30px;">
        <legend>
        查看订单
        </legend>
    </fieldset>
    <table class="layui-table">
        <colgroup>
          <col width="120">
          <col>
        </colgroup>
        <tbody>
          <tr>
            <td>订单号</td>
            <td>{{$order['ordersn']}}</td>
          </tr>
          <tr>
            <td>订单状态</td>
            <td>@if ($order['status'] == 0)
                <span style="color:red;">待付款</span>
            @elseif ($order['status'] == 1)
                <span style="color:#ffbf00;">进行中</span>
            @elseif ($order['status'] == 2)
                <span style="color:green;">已完成</span>
            @elseif ($order['status'] == -1)
                <span style="color:#808080;">已取消</span>
            @endif</td>
          </tr>
          <tr>
            <td>服务类型</td>
            <td>@if ($order['type'] == 1)
                店内
            @elseif ($order['type'] == 2)
                邮寄
            @endif</td>
          </tr>
          <tr>
            <td>微信头像</td>
            <td>
                <img src="{{$order['avatar_url']}}" style="max-width:70px;" id="avatar">
            </td>
          </tr>
          <tr>
            <td>微信昵称</td>
            <td>{{$order['nickname']}}</td>
          </tr>
          <tr>
            <td>OPENID</td>
            <td>{{$order['openid']}}</td>
          </tr>
          <tr>
            <td>姓名</td>
            <td>{{$order['name']}}</td>
          </tr>
          <tr>
            <td>手机号</td>
            <td>{{$order['phone']}}</td>
          </tr>
          <tr>
            <td>订单备注</td>
            <td>@if (!empty($order['remark'])){{$order['remark']}}@else 无备注 @endif</td>
          </tr>
          <tr>
            <td>是否寄出</td>
            <td>@if (!empty($order['is_send']))
                已寄出
            @else
                未寄出
            @endif
            </td>
          </tr>
          @if (!empty($order['express_name']))
          <tr>
            <td>快递公司名称</td>
            <td>{{$order['express_name']}}</td>
          </tr>
          @endif
          @if (!empty($order['express_number']))
          <tr>
            <td>快递公司单号</td>
            <td>{{$order['express_number']}}</td>
          </tr>
          @endif
          @if (!empty($order['photos']))
          <tr>
            <td>鞋子照片</td>
            <td>@foreach ($order['photos'] as $v)
                <img src="{{$v}}" class="photos">
            @endforeach</td>
          </tr>
          @endif
          <tr>
            <td>鞋子服务</td>
            <td>
              <div class="layui-text">
                <ul>@foreach ($order_service as $k => $v)
                  <li>
                  <span>{{$k + 1}}、</span>
                  标准服务 <b>{{$v->standard_service_title}}</b><em>{{$v->standard_service_price}}元</em>
                  @if (!empty($v->additional))
                  附加服务 
                    @foreach ($v->additional as $k2 => $v2)
                    <b>{{$v2['title']}}</b><em>{{$v2['price']}}元</em>
                    @endforeach
                  @endif
                  <b>总计</b><em>{{$v->price}}元</em>
                  </li>
                @endforeach</ul>
              </div>
            </td>
          </tr>
          <tr>
            <td>鞋子操作</td>
            <td>
            @foreach ($order_service as $k => $v)
            <li>
                <span>{{$k + 1}}、</span>
                <div class="layui-form" style="display: inline;">
                    <input lay-filter="order_service_status" type="radio" name="order_service_{{$v->id}}" data-id="{{$v->id}}" value="0" title="待处理" @if ($v->status == 0) checked @endif>
                    <input lay-filter="order_service_status" type="radio" name="order_service_{{$v->id}}" data-id="{{$v->id}}" value="1" title="清洗中" @if ($v->status == 1) checked @endif>
                    <input lay-filter="order_service_status" type="radio" name="order_service_{{$v->id}}" data-id="{{$v->id}}" value="2" title="待提货" @if ($v->status == 2) checked @endif>
                    <input lay-filter="order_service_status" type="radio" name="order_service_{{$v->id}}" data-id="{{$v->id}}" value="3" title="已提货" @if ($v->status == 3) checked @endif>
                </div>
            </li>
            @endforeach
            </td>
          </tr>
          @if (!empty($order['coupon_log_id']))
          <tr>
            <td>优惠券面值</td>
            <td>{{$order['coupon_price']}}</td>
          </tr>
          @endif
          @if ($order['balance'] != 0.00)
          <tr>
            <td>余额抵扣金额</td>
            <td>{{$order['balance']}}</td>
          </tr>
          @endif
          @if ($order['status'] == 1 || $order['status'] == 2)
          <tr>
            <td>支付方式</td>
            <td>@if ($order['pay_type'] == 1)
                余额支付
            @elseif ($order['pay_type'] == 2)
                微信支付
            @elseif ($order['pay_type'] == 3)
                后台确认支付
            @endif
            </td>
          </tr>
          <tr>
            <td>支付时间</td>
            <td>{{date("Y-m-d H:i:s",$order['pay_time'])}}</td>
          </tr>
          @endif
          @if (!empty($order['wx_transaction_id']))
          <tr>
            <td>微信支付单号</td>
            <td>{{$order['wx_transaction_id']}}</td>
          </tr>
          @endif
          <tr>
            <td>总价</td>
            <td>{{$order['total_price']}}</td>
          </tr>
          <tr>
            <td>应付价</td>
            <td>{{$order['payable_price']}}</td>
          </tr>
          <tr>
            <td>创建时间</td>
            <td>{{$order['create_time']}}</td>
          </tr>
          <tr>
            <td>最后修改时间</td>
            <td>{{$order['update_time']}}</td>
          </tr>
          <tr>
            <td>订单操作</td>
            <td>
                @if ($order['status'] == 0)
                    <a class="layui-btn layui-btn-danger layui-btn-sm btn-click" lay-event="cancel">取消订单</a>
                    <a class="layui-btn layui-btn-sm btn-click" lay-event="confirmpay">确认支付</a>
                @elseif ($order['status'] == 1)
                    <a class="layui-btn layui-btn-normal layui-btn-sm btn-click" lay-event="complete">确认完成</a>
                @elseif ($order['status'] == 2)
                    <span style="color:green;">订单已完成</span>
                @elseif ($order['status'] == -1)
                    <span style="color:#808080;">订单已取消</span>
                @endif
            </td>
          </tr>
        </tbody>
      </table>
</body>
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
layui.use(['layer','form','table'],function(){
    var layer = layui.layer;
    var form = layui.form;
    var table = layui.table;
    form.on('radio(order_service_status)', function(data){
        var data = {
            id:$(data.elem).data('id'),
            status:data.value,
            order_id:getQueryVariable('id')
        };
        $.post("{{route('admin.order.service.status')}}",data,function(res){
            layer.msg(res.msg);
        })
    });
})
$('.btn-click').click(function(){
    if($(this).attr('lay-event') == 'cancel'){
        layer.confirm('确认要取消订单吗', function(index){
            $.post("{{route('admin.order.cancel')}}",{id:getQueryVariable('id')},function(res){
                if(res.status == 1){
                    layer.alert(res.msg, function(index){
                        location.reload();
                    });
                }else{
                    layer.msg(res.msg);
                }
            })
        });
    }else if($(this).attr('lay-event') == 'confirmpay'){
        layer.confirm('确认这笔订单已支付吗', function(index){
            $.post("{{route('admin.order.confirmpay')}}",{id:getQueryVariable('id')},function(res){
                if(res.status == 1){
                    layer.alert(res.msg, function(index){
                        location.reload();
                    });
                }else{
                    layer.msg(res.msg);
                }
            })
        });
    }else if($(this).attr('lay-event') == 'complete'){
        layer.confirm('确认这笔订单已完成吗', function(index){
            $.post("{{route('admin.order.complete')}}",{id:getQueryVariable('id')},function(res){
                if(res.status == 1){
                    layer.alert(res.msg, function(index){
                        location.reload();
                    });
                }else{
                    layer.msg(res.msg);
                }
            })
        });
    }
});
hover_open_img('#avatar',2.5);
hover_open_img('.photos',4);
function hover_open_img($text = '',$num = 3){
    $($text).hover(function () {
    var kd = $(this).width();
    var kd1 = kd * $num;
    var kd2 = kd * $num + 30;
    var img = "<img class='img_msg' src='" + $(this).attr('src') + "' style='width:" + kd1 + "px;' />";
    img_show = layer.tips(img, this, {
        tips: [2, 'rgba(41,41,41,.1)']
        , area: [kd2 + 'px']
    });
    }, function () {
        layer.close(img_show);
    });
}
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
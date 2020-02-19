<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-07 09:46:33
 * @LastEditors: 傍晚升起的太阳
 * @LastEditTime : 2020-01-18 09:38:38
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Models\Wx;

class OrderController extends BaseController
{
    public function index(){
        return view('admin.order.index');
    }
    public function list(Request $request){
        extract(page($request->input()));
        extract(Order::list($number,$limit,$request->input('keyword'),$request->input('type'),$request->input('status')));
        return ['data'=>$data,'count'=>$count,'code'=>0];
    }
    public function see(Request $request){
        !empty($id = get_id($request->input('id'))) || msg(0,'请传入正确的id');
        $order = Order::from("order as o")
        ->join('member as m','o.member_id','=','m.id')
        ->where('o.id',$id)
        ->select(['o.*','m.nickname','m.openid','m.avatar_url'])
        ->first();
        $order['photos'] = img_path_url_arr(json_decode($order['photos'],true));
        $order_service = DB::table('order_service')
        ->where('order_id',$id)
        ->select(['id','status','standard_service_title','standard_service_price','additional','number'])
        ->get();
        foreach ($order_service as $k => $v) {
            $v->price = $v->standard_service_price;
            $v->additional = json_decode($v->additional,true);
            foreach ($v->additional as $k2 => $v2) {
                $v->price += $v2['price'];
            }
        }
        return view('admin.order.see',['order'=>$order,'order_service'=>$order_service]);
    }
    public function service_status(Request $request){
        $rule = [
            'id'=>'required|integer',
            'order_id'=>'required|integer',
            'status'=>'required|in:0,1,2,3',
        ];
        $msg = [
            'id.required'=>'请传入id',
            'id.integer'=>'请传入正确的id',
            'order_id.required'=>'请传入订单id',
            'order_id.integer'=>'请传入正确的订单id',
            'status.required'=>'请传入状态码',
            'status.in'=>'请传入正确的状态码',
        ];
        extract(data_check($request->all(),$rule,$msg));
        DB::beginTransaction();
        try {
            DB::table('order_service')
            ->where('id',$id)
            ->where('order_id',$order_id)
            ->update(['update_time'=>time(),'status'=>$status]);
            Order::where('id',$order_id)->update(['update_time'=>time()]);
            DB::commit();
            msg(1,"修改成功");
        } catch (\Throwable $th) {
            DB::rollBack();
            msg(0,"修改失败");
        }
    }
    public function confirmpay(Request $request){
        $id = delete_id($request->input('id'));
        !empty($order = Order::where('id',$id)
        ->select(['status'])
        ->first()) || msg(0,'订单不存在');
        $order->status == 0 || msg(0,"订单不是待付款状态，无法完成订单");
        try {
            Order::where('id',$id)
            ->update(['update_time'=>time(),'status'=>1,'pay_type'=>3,'pay_time'=>time()]) ? msg(1,"操作成功") : msg(0,"操作失败");
        } catch (\Throwable $th) {
            msg(0,"操作失败");
        }
    }
    public function cancel(Request $request){
        Order::cancel(delete_id($request->input('id')));
    }
    public function complete(Request $request){
        $id = delete_id($request->input('id'));
        !empty($order = Order::where('id',$id)
        ->select(['status'])
        ->first()) || msg(0,'订单不存在');
        $order->status == 1 || msg(0,"订单不是进行中状态，无法完成订单");
        try {
            Order::where('id',$id)
            ->update(['update_time'=>time(),'status'=>2]) ? msg(1,"操作成功") : msg(0,"操作失败");
        } catch (\Throwable $th) {
            msg(0,"操作失败");
        }
    }
    public function notice(Request $request){
        $id = delete_id($request->input('id'));
        !empty($order = Order::from("order as o")
        ->where('o.id',$id)
        ->join('member as m','o.member_id','=','m.id')
        ->join('order_service as os','o.id','=','os.order_id')
        ->select(['m.openid','o.status','o.create_time','o.ordersn','os.standard_service_title'])
        ->first()) || msg(0,'订单不存在');
        $order->status == 1 || msg(0,"订单不是进行中状态，无法发送通知");
        $msg_data = @json_encode(config('wx.template.pick_up_goods.data'),JSON_UNESCAPED_UNICODE);
        $msg_data = @json_decode(@strtr($msg_data,['{订单号}'=>$order['ordersn'],'{下单时间}'=>$order['create_time'],'{商品名称}'=>$order['standard_service_title']
        ]),true);
        if(($res = Wx::notice(Wx::access_token(),$order['openid'],config('wx.template.pick_up_goods.id'),$msg_data,'pages/me/order/index?num=4')) === true){
            try {
                Order::where('id',$id)->update(['update_time'=>time(),'is_notice'=>1]);
                msg(1,'推送通知成功');
            } catch (\Throwable $th) {
                msg(0,"通知发送成功,修改订单状态失败");
            }
        }else{
            msg(0,$res);
        }
    }
    public function number(Request $request){
        $id = delete_id($request->input('id'));
        $service_id = delete_id($request->input('service_id'));
        !empty($val = $request->input('val')) || msg(0,'输入的编号不能为空');
        DB::beginTransaction();
        try {
            DB::table('order_service')
            ->where('id',$service_id)
            ->where('order_id',$id)
            ->update(['update_time'=>time(),'number'=>$val]);
            $order_service = DB::table('order_service')
            ->where('order_id',$id)
            ->select(['number'])->get();
            $number_text = [];
            foreach($order_service as $k => $v){
                $number_text[] = $v->number;
            }
            Order::where('id',$id)->update(['update_time'=>time(),'number_text'=>json_encode($number_text,JSON_UNESCAPED_UNICODE)]);
            DB::commit();
            msg(1,"修改成功");
        } catch (\Throwable $th) {
            DB::rollBack();
            msg(0,"修改失败");
        }
    }
}
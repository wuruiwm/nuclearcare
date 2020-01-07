<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-07 09:46:33
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-07 17:20:26
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

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
        ->select(['id','status','standard_service_title','standard_service_price','additional'])
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
}
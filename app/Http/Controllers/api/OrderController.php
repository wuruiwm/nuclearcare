<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-04 09:50:19
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-04 15:48:07
 */

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Member;

class OrderController extends BaseController
{
    public function create(Request $request){
        $member_id = get_token();
        $rule = [
            'type'=>'required|in:1,2',
            'name'=>'required',
            'phone'=>'required',
            'service'=>'required',
            'is_send'=>'required|in:0,1',
        ];
        $msg = [
            'type.required'=>'请传入正确的类型',
            'type.in'=>'请传入正确的类型',
            'name.required'=>'姓名不能为空',
            'phone.required'=>'手机号不能为空',
            'service.required'=>'服务不能为空,请选择服务',
            'is_send.required'=>'请传入是否寄出',
            'is_send.in'=>'请传入正确的是否寄出',
        ];
        $data = data_check($request->all(),$rule,$msg,0);
        !empty($data['service'] = @json_decode($data['service'],true)) || api_json(500,"服务不能为空,请选择服务");
        preg_match("/^1[3456789]\d{9}$/", $data['phone']) || api_json(500,"请输入正确的手机号");
        $service = $data['service'];
        unset($data['service']);
        //获取所有的服务 用in查询查出，再赋值到对应的服务上 减少查询，提高性能
        $service_arr = DB::table('service')->whereIn('id',service_in_arr($service))->select(['id','title','type','price'])->get();
        $service_arr = ob_to_array($service_arr);
        $data['total_price'] = order_total_price($service,$service_arr);
        $data['member_id'] = $member_id;
        $data['ordersn'] = Order::create_ordersn();
        $data['remark'] = $request->input('remark');
        $data['payable_price'] = $data['total_price'];
        $data['update_time'] = time();
        $data['create_time'] = time();
        if($data['type'] == 2){
            !empty($data['address'] = $request->input('address')) || api_json(500,"地址不能为空");
            if($data['is_send'] == 1){
                empty($request->input('express_name')) || $data['express_name'] = $request->input('express_name');
                empty($request->input('express_number')) || $data['express_number'] = $request->input('express_number');
            }
            empty($request->input('photos')) || $data['photos'] = $request->input('photos');
        }
        DB::beginTransaction();
        try {
            //优惠券
            if($data['payable_price'] > 0 && !empty($request->input('coupon_log_id')) && is_numeric($request->input('coupon_log_id'))){
                !empty($copon_log = (array)DB::table('coupon_log')
                ->where('status',0)
                ->where('expire_time','>',time())
                ->where('id',$request->input('coupon_log_id'))
                ->first()) || api_json(500,"选择的优惠券不存在,请重新选择");
                $data['total_price'] >= $copon_log['full'] || api_json(500,"优惠券不满足使用条件,请重新选择");
                $data['payable_price'] = $data['total_price'] - $copon_log['face_value'];
                $data['payable_price'] >= 0 || $data['payable_price'] = 0;
                $data['coupon_log_id'] = $request->input('coupon_log_id');
                $data['coupon_price'] = $copon_log['face_value'];
                $res = DB::table('coupon_log')->where('id',$request->input('coupon_log_id'))->update(['status'=>1,'update_time'=>time()]);
            }
            //余额抵扣
            if($data['payable_price'] > 0 && !empty($request->input('is_balance'))){
                $member = Member::get_member($member_id);
                $payable_price = $data['payable_price'];
                $data['payable_price'] = $data['payable_price'] - $member['balance'];
                if($data['payable_price'] >= 0){
                    $data['balance'] = $member['balance'];
                    $member['balance'] == 0 || Member::balance($member_id,$member['balance'],2,"订单消费".$member['balance']."元 ".$data['ordersn']);
                }else{
                    $data['payable_price'] = 0;
                    $data['balance'] = $payable_price;
                    $payable_price == 0 || Member::balance($member_id,$payable_price,2,"订单消费".$payable_price."元 ".$data['ordersn']);
                }
            }
            if($data['payable_price'] == 0){
                $data['status'] = 1;
                $data['pay_type'] = 1;
            }else{
                $data['pay_type'] = 2;
            }
            $order_id = Order::insertGetId($data);
            DB::table('order_service')->insert(order_service_arr($service,$service_arr,$order_id));
            DB::commit();
            api_json(200,'提交订单成功',['order_id'=>$order_id]);
        } catch (\Throwable $th) {
            DB::rollBack();
            api_json(500,'提交订单失败,请重试');
        }
    }
}
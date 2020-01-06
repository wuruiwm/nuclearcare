<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-04 13:31:23
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-06 16:40:42
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Order extends Base
{
    protected $table = 'order';//定义表名
    public static function create_ordersn(){
        $ordersn = 'SE'.date('Ymd').mt_rand(10000,99999);
        while(true){
            $res = self::where('ordersn',$ordersn)->select(['id'])->first();
            if(!$res){
                return $ordersn;
            }else{
                $ordersn = 'SE'.date('Ymd').mt_rand(10000,99999);
            }
        }
    }
    public static function cancel($id,$member_id = 0){
        !empty($order = self::where('id',$id)
        ->where(function($query)use($member_id){
            empty($member_id) || $query->where('member_id',$member_id);    
        })
        ->select(['status','coupon_log_id','balance','member_id','ordersn'])
        ->first()) || (!empty($member_id) ? api_json(500,"订单不存在,请重试") : msg(0,'订单不存在,请重试'));
        $order->status == 0 || (!empty($member_id) ? api_json(500,"订单不是待付款状态,无法取消订单") : msg(0,'订单不是待付款状态,无法取消订单'));
        DB::beginTransaction();
        try {
            $order->coupon_log_id == 0 || DB::table('coupon_log')->where('id',$order->coupon_log_id)->update(['status'=>0,'update_time'=>time()]);
            if($order->balance != 0.00){
                !empty($member_id) ? $remark = "用户取消订单" : $remark = "后台取消订单";
                $remark .= " 退还余额 ".$order->ordersn;
                Member::balance($order->member_id,$order->balance,1,$remark);
            }
            self::where('id',$id)
            ->where(function($query)use($member_id){
                empty($member_id) || $query->where('member_id',$member_id);    
            })
            ->update(['status'=>-1,'update_time'=>time()]);
            DB::commit();
            !empty($member_id) ? api_json(200,"取消订单成功") : msg(1,'取消订单成功');
        } catch (\Throwable $th) {
            DB::rollBack();
            !empty($member_id) ? api_json(500,"取消订单失败") : msg(1,'取消订单失败');
        }
    }
}
<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-04 13:31:23
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-10 10:30:51
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
    public static function list($number,$limit,$keyword = '',$type = 0,$status = ''){
        $model = self::from("order as o")
        ->orderBy('o.id','desc')
        ->join('member as m','o.member_id','=','m.id')
        ->where(function($query)use($status){
            field_check($status,'required|in:-1,0,1,2') === false || $query->orwhere('o.status',$status);
        })
        ->where(function($query)use($keyword){
            if(!empty($keyword)){
                $query->orwhere('o.ordersn','like','%'.$keyword.'%');
                $query->orwhere('o.name','like','%'.$keyword.'%');
                $query->orwhere('o.phone','like','%'.$keyword.'%');
                $query->orwhere('m.nickname','like','%'.$keyword.'%');
                $query->orwhere('m.openid','like','%'.$keyword.'%');
            }  
        })
        ->where(function($query)use($type){
            !empty($type) && ($type == 1 || $type == 2) && $query->where('o.type',$type);
        });
        $count = $model->count();
        $data = $model->offset($number)
        ->limit($limit)
        ->select(['o.id','o.ordersn','o.status','o.name','o.remark','o.phone','o.address','o.type','o.total_price','o.payable_price','o.create_time','o.update_time','m.nickname','m.openid','m.avatar_url'])
        ->get();
        return ['data'=>$data,'count'=>$count];
    }
}

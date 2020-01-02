<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-02 10:50:06
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-02 15:43:55
 */

namespace App\Models;

use Illuminate\Support\Facades\Cache;
use App\Models\MarketingRecharge;

class RechargeOrder extends Base
{
    protected $table = 'recharge_order';//定义表名
    public static function create_order($member_id,$price){
        try {
            $data = [
                'member_id'=>$member_id,
                'status'=>0,
                'price'=>$price,
                'ordersn'=>self::create_ordersn(),
                'create_time'=>time(),
                'update_time'=>time(),
            ];
            $marketing_recharge = MarketingRecharge::where('full','<=',$price)->orderBy('give','desc')->limit(1)->first();
            empty($marketing_recharge) || $data['give'] = $marketing_recharge->give;
            $res = self::insertGetId($data);
            if($res){
                $data['id'] = $res;
                return $data;
            }else{
                return false;
            }
        } catch (\Throwable $th) {
            return false;
        }
    }
    protected static function create_ordersn(){
        $ordersn = 'RE'.date('Ymd').mt_rand(10000,99999);
        while(true){
            $res = self::where('ordersn',$ordersn)->select(['id'])->first();
            if(!$res){
                return $ordersn;
            }else{
                $ordersn = 'RE'.date('Ymd').mt_rand(10000,99999);
            }
        }
    }
}

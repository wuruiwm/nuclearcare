<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-04 13:31:23
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-04 13:36:40
 */

namespace App\Models;

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
}

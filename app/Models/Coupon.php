<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-03 09:45:27
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-03 09:45:40
 */


namespace App\Models;

use Illuminate\Support\Facades\Cache;

class Coupon extends Base
{
    protected $table = 'coupon';//定义表名
    public static function list($number,$limit){
        $model = self::orderBy('id','desc');
        $count = $model->count();
        $data = $model->offset($number)
        ->limit($limit)
        ->get();
        return ['data'=>$data,'count'=>$count];
    }
}

<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-28 14:04:50
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-31 15:00:03
 */
namespace App\Models;

use Illuminate\Support\Facades\Cache;

class Service extends Base
{
    protected $table = 'service';//定义表名
    public static function list($number,$limit,$type = 0){
        $model = self::orderBy('id','asc')->where(function($query)use($type){
            !($type == 1 || $type == 2) || $query->where('type',$type);
        });
        $count = $model->count();
        $data = $model->offset($number)
        ->limit($limit)
        ->get();
        return ['data'=>$data,'count'=>$count];
    }
    public static function api_list(){
        $model = self::orderBy('sort','desc');
        $data = Cache::store('redis')->rememberForever('api_service_list_data',function () use($model){
            return $model->select(['id','title','price','type'])
            ->get();
        });
        return $data;
    }
}

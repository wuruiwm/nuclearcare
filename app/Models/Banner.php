<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:12:26
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-06 11:20:57
 */

namespace App\Models;

use Illuminate\Support\Facades\Cache;

class Banner extends Base
{
    protected $table = 'banner';//定义表名
    public static function list($number,$limit){
        $model = self::orderBy('id','desc');
        $count = $model->count();
        $data = $model->offset($number)
        ->limit($limit)
        ->get();
        return ['data'=>$data,'count'=>$count];
    }
    public static function api_list(){
        $model = self::orderBy('sort','desc');
        $data = Cache::store('redis')->rememberForever('api_banner_list_data',function () use($model){
            return $model->select(['id','img_path'])
            ->get();
        });
        $count = Cache::store('redis')->rememberForever('api_banner_list_count',function () use($model){
            return $model->count();
        });
        return ['data'=>$data,'count'=>$count];
    }
}

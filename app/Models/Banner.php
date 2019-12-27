<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:12:26
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-27 14:10:50
 */

namespace App\Models;

class Banner extends Base
{
    protected $table = 'banner';//定义表名
    public static function list($number,$limit,$type = '',$keyword = ''){
        $model = self::orderBy('id','asc');
        $count = $model->count();
        $data = $model->offset($number)
        ->limit($limit)
        ->get();
        return ['data'=>$data,'count'=>$count];
    }
}

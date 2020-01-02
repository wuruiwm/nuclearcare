<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:12:26
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-02 10:31:24
 */

namespace App\Models;

class BalanceLog extends Base
{
    protected $table = 'balance_log';//定义表名
    public static function insert_log($member_id,$type,$price,$remark){
        $data = [
            'member_id'=>$member_id,
            'type'=>$type,
            'price'=>$price,
            'remark'=>$remark,
            'create_time'=>time(),
            'update_time'=>time(),
        ];
        return self::insert($data);
    }
    public static function list($number,$limit,$keyword = ''){
        $model = self::from("balance_log as l")->orderBy('id','asc')
        ->join('member as m','l.member_id','=','m.id')
        ->where(function($query)use($keyword){
            empty($keyword) || $query->orwhere('m.nickname','like','%'.$keyword.'%')
            ->orwhere('m.id',$keyword)
            ->orwhere('m.openid','like','%'.$keyword.'%');
        })
        ->select(['l.*','m.openid','m.nickname','m.avatar_url']);
        $count = $model->count();
        $data = $model->offset($number)
        ->limit($limit)
        ->get();
        return ['data'=>$data,'count'=>$count];
    }
}

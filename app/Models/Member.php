<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:12:26
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-06 11:21:32
 */

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Member extends Base
{
    protected $table = 'member';//定义表名
    public static function get_member($keyword = ''){
        return is_numeric($keyword) ? self::where('id',$keyword)->first() : self::where('openid',$keyword)->first();
    }
    public static function member_create($data){
        $member = [
            'openid'=>$data['openId'],
            'nickname'=>$data['nickName'],
            'avatar_url'=>$data['avatarUrl'],
            'balance'=>0,
            'create_time'=>time(),
            'update_time'=>time(),
        ];
        try {
            $member['id'] = self::insertGetId($member);
        } catch (\Throwable $th) {
            api_json(500,'用户注册失败');
        }
        return $member;
    }
    public static function list($number,$limit,$keyword = ''){
        $model = self::orderBy('id','desc')
        ->where(function($query)use($keyword){
            empty($keyword) || $query->orwhere('nickname','like','%'.$keyword.'%')
            ->orwhere('id',$keyword)
            ->orwhere('openid','like','%'.$keyword.'%');
        });
        $count = $model->count();
        $data = $model->offset($number)
        ->limit($limit)
        ->get();
        return ['data'=>$data,'count'=>$count];
    }
    public static function balance($member_id,$price,$type,$remark){
        //type 1加 2减 3最终
        DB::beginTransaction();
        try {
            $type != 1 || $blance_res = self::where('id',$member_id)->increment('balance',$price,['update_time'=>time()]);
            $type != 2 || $blance_res = self::where('id',$member_id)->decrement('balance',$price,['update_time'=>time()]);
            $type != 3 || $blance_res = self::where('id',$member_id)->update(['balance'=>$price,'update_time'=>time()]);
            $blance_log_res = BalanceLog::insert_log($member_id,$type,$price,$remark);
            if(!empty($blance_log_res) && !empty($blance_res)){
                DB::commit();
                return true;
            }else{
                DB::rollBack();
                return false;
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return false;
        }
    }
}

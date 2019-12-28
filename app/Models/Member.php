<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:12:26
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-28 10:52:09
 */

namespace App\Models;

class Member extends Base
{
    protected $table = 'member';//定义表名
    public static function get_member($keyword = ''){
        return is_numeric($keyword) ? self::where('id',$keyword)->get() : self::where('openid',$keyword)->first();
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
}

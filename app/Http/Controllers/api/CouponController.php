<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-03 11:50:11
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-03 12:05:07
 */
namespace App\Http\Controllers\api;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends BaseController
{
    public function detail(Request $request){
        $id = get_id($request->input('id'));
        !empty($id) || api_json(500,'请传入正确的id');
        !empty($data = Coupon::where('id',$id)->first()) || api_json(500,'优惠券不存在');
        (time() >= $data['start_time'] && time() <= $data['end_time']) || api_json(500,'优惠券不在领取时间');
        find_date($data,['start_time','end_time']);
        api_json(200,'获取优惠券详情成功',$data);
    }
    public function receive(Request $request){
        
    }
}
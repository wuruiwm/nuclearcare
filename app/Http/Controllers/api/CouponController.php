<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-03 11:50:11
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-03 14:09:13
 */
namespace App\Http\Controllers\api;

use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CouponController extends BaseController
{
    public function detail(Request $request){
        $id = get_id($request->input('id'));
        !empty($id) || api_json(500,'请传入正确的id');
        !empty($data = Coupon::where('id',$id)
        ->select(['id','face_value','validity_time','full','total','receive_num','start_time','end_time'])
        ->first()) || api_json(500,'优惠券不存在');
        (time() >= $data['start_time'] && time() <= $data['end_time']) || api_json(500,'优惠券不在领取时间');
        unset($data['start_time']);
        unset($data['end_time']);
        api_json(200,'获取优惠券详情成功',$data);
    }
    public function receive(Request $request){
        $member_id = get_token();
        $id = get_id($request->input('id'));
        !empty($id) || api_json(500,'请传入正确的id');
        $coupon = Coupon::where('start_time','<=',time())->where('end_time','>=',time())->find($id);
        !empty($coupon) || api_json(500,'优惠券不存在或者不在领取时间');
        $coupon['total'] > $coupon['receive_num'] || api_json(500,'优惠券已领完,下次请早点来喔');
        !DB::table('coupon_log')
        ->where('coupon_id',$id)
        ->where('member_id',$member_id)
        ->select(['id'])->first() || api_json(500,'已领取过喔，不可贪心');
        $data = [
            'member_id'=>$member_id,
            'coupon_id'=>$id,
            'face_value'=>$coupon['face_value'],
            'validity_time'=>$coupon['validity_time'],
            'full'=>$coupon['full'],
            'create_time'=>time(),
            'expire_time'=>strtotime('+'.$coupon['validity_time'].' days'),
            'update_time'=>time(),
            'status'=>0
        ];
        DB::beginTransaction();
        try {
            DB::table('coupon_log')->insert($data);
            Coupon::where('id',$id)->increment('receive_num');
            DB::commit();
            api_json(200,'领取成功');
        } catch (\Throwable $th) {
            DB::rollBack();
            api_json(500,'领取失败');
        }
    }
}
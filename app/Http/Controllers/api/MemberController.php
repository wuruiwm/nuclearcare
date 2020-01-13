<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-02 10:45:44
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-13 17:12:23
 */

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\RechargeOrder;
use App\Models\Wx;
use App\Models\Member;
use App\Models\MarketingRecharge;
use Illuminate\Support\Facades\DB;

class MemberController extends BaseController
{
    public function recharge(Request $request){
        $member_id = $request->get('member_id');
        $price = $request->input('price');
        !(empty($price) || !is_numeric($price)) || api_json(500,'请输入正确的金额');
        $price = round($price,2);
        !empty($member = Member::get_member($member_id)) || api_json(500,'用户数据错误');
        !empty($order = RechargeOrder::create_order($member_id,$price)) || api_json(500,'订单创建失败');
        api_json(200,'获取支付参数成功',Wx::pay($order['ordersn'],$order['price'],$member['openid']));
    }
    public function recharge_list(){
        extract(MarketingRecharge::api_list());
        foreach ($data as $k => $v) {
            $data[$k]->full = floatval($v['full']);
            $data[$k]->give = floatval($v['give']);
        }
        api_json(200,'获取充值优惠列表成功',$data,$count);
    }
    public function order_base(Request $request){
        $member_id = $request->get('member_id');
        !empty($member = Member::get_member($member_id)) || api_json(500,'用户数据错误');
        $coupon_log_list = DB::table('coupon_log')
        ->where('member_id',$member['id'])
        ->where('status',0)
        ->where('expire_time','>=',time())
        ->select(['id','face_value','full','expire_time'])
        ->get();
        $data = [
            'balance'=>$member['balance'],
            'coupon_log_list'=>$coupon_log_list,
        ];
        array_date($coupon_log_list,['expire_time']);
        !$coupon_log_list->isEmpty() ? $data['is_coupon'] = 1 : $data['is_coupon'] = 0;
        if(!$coupon_log_list->isEmpty()){
            foreach ($coupon_log_list as $k => $v) {
                $coupon_log_list[$k]->face_value = floatval($v['face_value']);
                $coupon_log_list[$k]->full = floatval($v['full']);
            }
        }
        api_json(200,"获取余额和可用优惠券列表成功",$data);
    }
    public function detail(Request $request){
        $member_id = $request->get('member_id');
        !empty($member = Member::get_member($member_id)) || api_json(500,'用户数据错误');
        $member['coupon_total'] = DB::table('coupon_log')
        ->where('member_id',$member['id'])
        ->where('status',0)
        ->where('expire_time','>=',time())
        ->count();
        unset($member['create_time'],$member['update_time'],$member['phone']);
        api_json(200,'获取个人中心数据成功',$member);
    }
}
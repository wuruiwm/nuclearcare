<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-02 10:45:44
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-03 16:45:59
 */

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\RechargeOrder;
use App\Models\Wx;
use App\Models\Member;
use App\Models\MarketingRecharge;

class MemberController extends BaseController
{
    public function recharge(Request $request){
        $member_id = get_token();
        $price = $request->input('price');
        !(empty($price) || !is_numeric($price)) || api_json(500,'请输入正确的金额');
        $price = round($price,2);
        !empty($member = Member::get_member($member_id)) || api_json(500,'用户数据错误');
        !empty($order = RechargeOrder::create_order($member_id,$price)) || api_json(500,'订单创建失败');
        api_json(200,'获取支付参数成功',Wx::pay($order['ordersn'],$order['price'],$member['openid']));
    }
    public function recharge_list(){
        extract(MarketingRecharge::api_list());
        api_json(200,'获取充值优惠列表成功',$data,$count);
    }
}
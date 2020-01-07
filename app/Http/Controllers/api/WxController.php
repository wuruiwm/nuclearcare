<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 17:06:05
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-07 14:16:14
 */

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\RechargeOrder;
use Illuminate\Support\Facades\DB;
use App\Models\Order;

class WxController extends BaseController
{
    public function login(Request $request){
        $code = $request->input('code');
        !empty($code) || api_json(500,'code不能为空');
 		$param['appid'] = config('wx.appid');
 		$param['secret'] = config('wx.appsecret');
 		$param['js_code'] = define_str_replace($code);
        $param['grant_type'] = 'authorization_code';
 		$http_key = httpCurl('https://api.weixin.qq.com/sns/jscode2session', $param, 'GET');
        $session_key = @json_decode($http_key,true);
        !empty($session_key['session_key']) ? api_json(200,'获取session_key成功',$session_key) : api_json(500,'登陆出错,请重试');
    }
    public function memberdecrypt(Request $request){
        $data = $this->decrypt($request);
        !empty($data['openId']) || api_json(500,'登陆出错,请重试');
        $member = Member::get_member($data['openId']);
        !empty($member) || $member = Member::member_create($data);
        !empty($member) || api_json(500,'登陆出错,请重试');
        $token = set_token($member['id']);
        $member['token'] = $token['token'];
        $member['token_time'] = $token['token_time'];
        $member['coupon_total'] = DB::table('coupon_log')
        ->where('member_id',$member['id'])
        ->where('status',0)
        ->where('expire_time','>=',time())
        ->count();
        api_json(200,'登陆成功',$member);
    }
    public function mobiledecrypt(Request $request){
        $request->input('openid') ? $openid = $request->input('openid') : api_json(500,'请传入openid');
        $data = $this->decrypt($request);
        !empty($data['purePhoneNumber']) || api_json(500,'获取手机号失败,请重试');
        try {
            Member::where('openid',$openid)->update(['phone'=>$data['purePhoneNumber'],'update_time'=>time()]);
        } catch (\Throwable $th) {
            api_json(500,'修改用户信息失败,请重试');
        }
        !empty($member = Member::get_member($openid)) || api_json(500,'登陆出错,请重试');
        api_json(200,'登陆成功',set_token($member['id']));
    }
    protected function decrypt($request){
        $rule = [
            'session_key' => 'required',
            'encrypteData' => 'required',
            'iv' => 'required',
        ];
        $msg = [
            'session_key.required'=>'session_key不能为空',
            'encrypteData.required'=>'encrypteData不能为空',
            'iv.required'=>'iv不能为空',
        ];
        $data = data_check($request->all(),$rule,$msg,0);
        return decryptData(config('wx.appid'), $data['session_key'], define_str_replace(urldecode($data['encrypteData'])), define_str_replace($data['iv']));
    }
    public function set_token(Request $request){
        return set_token($request->input('id'));
    }
    public function notify(){
        $result = (array)simplexml_load_string(file_get_contents('php://input'), 'SimpleXMLElement', LIBXML_NOCDATA);
        $log = fopen(__DIR__."/paylog/".date("Ymd").".txt", "a");
        $txt = json_encode($result)."\n\n";
        fwrite($log, $txt);
        fclose($log);
        if(!empty($result['result_code']) && $result['result_code'] == "SUCCESS") {
            substr($result['out_trade_no'],0,2) != 'RE' || $this->re($result);
            substr($result['out_trade_no'],0,2) != 'SE' || $this->se($result);
        }
        echo '<xml>
        <return_code><![CDATA[SUCCESS]]></return_code>
        <return_msg><![CDATA[OK]]></return_msg>
      </xml>';
    }
    protected function re($result){
        $order = RechargeOrder::where('ordersn',$result['out_trade_no'])->select(['status','member_id','price','give'])->first();
        if(!empty($order) && $order->status == 0){
            DB::beginTransaction();
            try{
                $member_balance_res = Member::balance($order->member_id,$order->price+$order->give,1,'用户微信充值'.$order->price.'赠送'.$order->give.' 订单号:'.$result['out_trade_no']);
                $order_res = RechargeOrder::where('ordersn',$result['out_trade_no'])->update(['status'=>1,'update_time'=>time()]);
                if(!empty($member_balance_res) && !empty($order_res)){
                    DB::commit();
                }else{
                    DB::rollBack();
                }
            }catch(\Throwable $th) {
                DB::rollBack();
            }
        }
    }
    protected function se($result){
        Order::where('ordersn',$result['out_trade_no'])
        ->update([
            'status'=>1,
            'pay_time'=>time(),
            'update_time'=>time(),
            'wx_transaction_id'=>$result['transaction_id']
        ]);
    }
}
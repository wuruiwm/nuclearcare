<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:12:26
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-07 13:39:52
 */

namespace App\Models;

class Wx extends Base
{
    public static function pay($ordersn,$price,$openid){
        $order = [
            'appid'=>config('wx.appid'),
            'body'=>config('wx.body'),
            'mch_id'=>config('wx.MCHID'),
            'nonce_str'=>md5(mt_rand(1,9999).time()),
            'notify_url'=>domain_name().'/api/wx/notify',
            'openid'=>$openid,
            'out_trade_no'=>$ordersn,
            'spbill_create_ip'=>'8.8.8.8',
            'total_fee'=>$price*100,
            'trade_type'=>'JSAPI'
        ];
        $order['sign'] = strtoupper(md5(to_url_params($order)."&key=".config('wx.KEY')));
        $result = @json_decode(xml_to_json(post_xml_curl(array_to_xml($order),"https://api.mch.weixin.qq.com/pay/unifiedorder",true)),true);
        (!empty($result) || $result['return_code'] == 'SUCCESS') || api_json(500,'调起支付异常');
        $data = [
            'appId'=>config('wx.appid'),
            'nonceStr'=>md5(time().mt_rand(1,9999).$openid),
            'package'=>'prepay_id='.$result['prepay_id'],
            'signType'=>'MD5',
            'timeStamp'=>time()
        ];
        $data['paySign'] = strtoupper(md5(to_url_params($data)."&key=".config('wx.KEY')));
        return $data;
    }
}

<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 10:12:26
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-18 09:21:48
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
    public static function access_token(){
        $data = @json_decode(@file_get_contents("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".config('wx.appid')."&secret=".config('wx.appsecret')),true);
        if(!empty($data['access_token'])){
            return $data['access_token'];
        }else{
            msg(0,'获取access_token失败');
        }
    }
    public static function qrcode($access_token,$page,$query){
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $access_token;
        $data = curl_post($url,json_encode(['page'=>$page,'scene'=>$query],JSON_UNESCAPED_UNICODE));
        empty(@json_decode($data,true)['errcode']) || msg(0,"请检查小程序路径是否存在");
        $path = '/qrcode/'.md5(time().mt_rand(1000,9999)).'.png';
        file_put_contents(public_path().$path,$data);
        $url = img_path_url($path);
        return ['status'=>1,'msg'=>'获取优惠券码成功','url'=>$url];
    }
    public static function notice($access_token,$openid,$template_id,$data,$page = ''){
        $url = 'https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token=' . $access_token;
        $data = ['touser'=>$openid,'template_id'=>$template_id,'data'=>$data];
        !empty($page) && $data['page'] = $page;
        $data = @json_decode(curl_post($url,@json_encode($data,JSON_UNESCAPED_UNICODE)),true);
        if($data['errcode'] == 0){
            return true;
        }else if($data['errcode'] == 43101){
            return '发送通知失败,用户未订阅,或拒收通知';
        }else{
            return '发送通知失败,错误码'.$data['errcode'];
        }
    }
}

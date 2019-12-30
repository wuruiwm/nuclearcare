<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 17:06:05
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-28 11:31:57
 */

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Models\Member;

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
        api_json(200,'用户信息获取成功',$member);
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
        $member = Member::get_member($openid);
        !empty($member) || api_json(500,'登陆出错,请重试');
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
}
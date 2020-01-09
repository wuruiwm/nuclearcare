<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-30 14:54:41
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-09 14:55:48
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\MarketingRecharge;
use App\Models\Coupon;
use App\Models\Wx;

class MarketingController extends BaseController
{
    public function recharge_index(){
        return view('admin.marketing.recharge_index');
    }
    public function recharge_list(Request $request){
        extract(page($request->input()));
        extract(MarketingRecharge::list($number,$limit));
        return ['data'=>$data,'count'=>$count,'code'=>0];
    }
    public function recharge_delete(Request $request){
        $id = delete_id($request->input('id'));
        try {
            MarketingRecharge::where('id',$id)->delete() ? msg(1,'删除成功') : msg(0,'删除失败');
        } catch (\Throwable $th) {
            msg(0,'删除失败');
        }
    }
    public function recharge_create(Request $request){
        $this->recharge_post($request);
    }
    public function recharge_edit(Request $request){
        $id = get_id($request->input('id'));
        $this->recharge_post($request,$id);
    }
    protected function recharge_post($request,$id = 0){
        $rule = [
            'full' => 'required|numeric',
            'give'=>'required|numeric',
        ];
        $msg = [
            'full.required'=>'请填写满多少',
            'full.numeric'=>'请填写正确的满多少',
            'give.required'=>'请填写送多少',
            'give.numeric'=>'请填写正确的送多少',
        ];
        $data = data_check($request->all(),$rule,$msg);
        $data['full'] = round($data['full'],2);
        $data['give'] = round($data['give'],2);
        $data['update_time'] = time();
        if (!empty($id)) {
            try {
                MarketingRecharge::where('id',$id)->update($data) ? msg(1,'修改成功') : msg(0,'修改失败');
            } catch (\Throwable $th) {
                msg(0,'修改失败');
            }
        }else{
            $data['create_time'] = time();
            try {
                MarketingRecharge::insert($data) ? msg(1,'添加成功') : msg(0,'添加失败');
            } catch (\Throwable $th) {
                msg(0,'添加失败');
            }
        }
    }
    public function coupon_index(){
        return view('admin.marketing.coupon_index');
    }
    public function coupon_list(Request $request){
        extract(page($request->input()));
        extract(Coupon::list($number,$limit));
        array_date($data,['start_time','end_time']);
        return ['data'=>$data,'count'=>$count,'code'=>0];
    }
    public function coupon_delete(Request $request){
        $id = delete_id($request->input('id'));
        try {
            Coupon::where('id',$id)->delete() ? msg(1,'删除成功') : msg(0,'删除失败');
        } catch (\Throwable $th) {
            msg(0,'删除失败');
        }
    }
    public function coupon_create(Request $request){
        if ($request->isMethod('get')) {
            return view('admin.marketing.coupon_post');
        }else if($request->isMethod('post')){
            $this->coupon_post($request);
        }
    }
    public function coupon_edit(Request $request){
        $id = get_id($request->input('id'));
        if ($request->isMethod('get')) {
            $data = Coupon::where('id',$id)->first()->toArray();
            return view('admin.marketing.coupon_post',['data'=>$data]);
        }else if($request->isMethod('post')){
            $this->coupon_post($request,$id);
        }
    }
    protected function coupon_post($request,$id = 0){
        $rule = [
            'face_value' => 'required|numeric',
            'validity_time'=>'required|integer',
            'full' => 'required|numeric',
            'total'=>'required|integer',
            'time'=>'required',
        ];
        $msg = [
            'face_value.required'=>'请填写面值',
            'face_value.numeric'=>'请填写正确的面值',
            'validity_time.required'=>'请填写有效天数',
            'validity_time.integer'=>'请填写正确的有效天数',
            'full.required'=>'请填写满减',
            'full.numeric'=>'请填写正确的满减',
            'total.required'=>'请填写总数量',
            'total.integer'=>'请填写正确的总数量',
            'time.required'=>'请填写领取时间',
        ];
        $data = data_check($request->all(),$rule,$msg);
        $time = explode(' - ',$data['time']);
        (count($time) == 2 && strtotime($time[0]) && strtotime($time[1]) && (strtotime($time[1]) > strtotime($time[0]))) || msg(0,'请输入正确的领取时间范围');
        $data['start_time'] = strtotime($time[0]);
        $data['end_time'] = strtotime($time[1]);
        unset($data['time']);
        $data['face_value'] = round($data['face_value'],2);
        $data['full'] = round($data['full'],2);
        $data['update_time'] = time();
        !empty($data['face_value']) || msg(0,'面值不能为0');
        !empty($data['validity_time']) || msg(0,'有效天数不能为0');
        !empty($data['total']) || msg(0,'数量不能为0');
        if (!empty($id)) {
            try {
                $receive_num = Coupon::where('id',$id)->pluck('receive_num');
                $data['total'] >= $receive_num[0] || msg(0,'优惠券总数量不能小于已经领取的数量');
                Coupon::where('id',$id)->update($data) ? msg(1,'修改成功') : msg(0,'修改失败');
            } catch (\Throwable $th) {
                msg(0,'修改失败');
            }
        }else{
            $data['create_time'] = time();
            try {
                Coupon::insert($data) ? msg(1,'添加成功') : msg(0,'添加失败');
            } catch (\Throwable $th) {
                msg(0,'添加失败');
            }
        }
    }
    public function qrcode(Request $request){
        $id = delete_id($request->input('id'));
        return Wx::qrcode(Wx::access_token(),config('wx.coupon'),'?id='.$id);
    }
}
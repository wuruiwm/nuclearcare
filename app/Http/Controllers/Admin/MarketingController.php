<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-30 14:54:41
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-02 15:28:46
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\MarketingRecharge;

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
}
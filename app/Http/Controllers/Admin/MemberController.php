<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-30 14:54:41
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-02 10:19:23
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\BalanceLog;

class MemberController extends BaseController
{
    public function index(){
        return view('admin.member.index');
    }
    public function list(Request $request){
        extract(page($request->input()));
        extract(Member::list($number,$limit,$request->input('keyword')));
        return ['data'=>$data,'count'=>$count,'code'=>0];
    }
    public function recharge(Request $request){
        $rule = [
            'id'=>'required|integer',
            'type' => 'required|in:1,2,3',
            'price'=>'required|numeric',
        ];
        $msg = [
            'id.required'=>'请传入用户id',
            'id.integer'=>'请传入正确的用户id',
            'type.required'=>'请选择类型',
            'type.in'=>'请选择正确的类型',
            'price.required'=>'请输入金额',
            'price.numeric'=>'请输入正确的金额',
        ];
        $data = data_check($request->all(),$rule,$msg);
        !empty($request->input('remark')) ? $remark = $request->input('remark') : $remark = '无备注';
        Member::balance($data['id'],$data['price'],$data['type'],'后台操作:'.$remark) ? msg(1,'修改成功') : msg(0,'修改失败');
    }
    public function balance_detail_index(){
        return view('admin.member.balance_detail_index');
    }
    public function balance_detail_list(Request $request){
        extract(page($request->input()));
        extract(BalanceLog::list($number,$limit,$request->input('keyword')));
        return ['data'=>$data,'count'=>$count,'code'=>0];
    }
}
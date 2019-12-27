<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 09:38:45
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-27 16:23:44
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Banner;

class BannerController extends BaseController
{
    public function index(){
        return view('admin.banner.index');
    }
    public function list(Request $request){
        extract(page($request->input()));
        extract(Banner::list($number,$limit));
        return ['data'=>$data,'count'=>$count,'code'=>0];
    }
    public function delete(Request $request){
        $id = delete_id($request->input('id'));
        Banner::where('id',$id)->delete() ? msg(1,'删除成功') : msg(0,'删除失败');
    }
    public function create(Request $request){
        $this->post($request);
    }
    public function edit(Request $request){
        $this->post($request);
    }
    public function post($request){
        $rule = [
            'img_path' => 'required',
            'sort'=>'required|integer',
        ];
        $msg = [
            'img_path.required'=>'请上传图片',
            'sort.required'=>'请填写排序',
            'sort.integer'=>'排序必须为整数值',
        ];
        $data = data_check($request->all(),$rule,$msg);
        $id = get_id($request->input('id'));
        $data['update_time'] = time();
        if (!empty($id)) {
            Banner::where('id',$id)->update($data) ? msg(1,'修改成功') : msg(0,'修改失败');
        }else{
            $total = Banner::count();
            $total < 10 || msg(0,'轮播图数量已达上限10个,请删除后再添加');
            $data['create_time'] = time();
            Banner::insert($data) ? msg(1,'添加成功') : msg(0,'添加失败');
        }
    }
}
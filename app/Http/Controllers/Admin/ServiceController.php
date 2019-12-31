<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-28 11:41:23
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-31 15:07:54
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends BaseController
{
    protected $redis_key_arr = [
        'api_service_list_data',
    ];
    public function index(){
        return view('admin.service.index');
    }
    public function list(Request $request){
        extract(page($request->input()));
        extract(Service::list($number,$limit,$request->input('type')));
        return ['data'=>$data,'count'=>$count,'code'=>0];
    }
    public function edit(Request $request){
        $id = get_id($request->input('id'));
        $this->post($request,$id);
    }
    public function create(Request $request){
        $this->post($request);
    }
    public function delete(Request $request){
        $id = delete_id($request->input('id'));
        try {
            $this->del_redis();
            Service::where('id',$id)->delete() ? msg(1,'删除成功') : msg(0,'删除失败');
        } catch (\Throwable $th) {
            msg(0,'删除失败');
        }
    }
    protected function post($request,$id = 0){
        $rule = [
            'sort'=>'required|integer',
            'title' => 'required',
            'type'=>'required|in:1,2',
            'price'=>'required|numeric',
        ];
        $msg = [
            'title.required'=>'请填写服务名称',
            'type.required'=>'请选择服务类型',
            'type.in'=>'请选择服务类型',
            'price.required'=>'请输入服务价格',
            'price.numeric'=>'请输入正确的价格',
            'sort.required'=>'请填写排序',
            'sort.integer'=>'排序必须为整数值',
        ];
        $data = data_check($request->all(),$rule,$msg);
        $data['update_time'] = time();
        if (!empty($id)){
            try {
                $this->del_redis();
                Service::where('id',$id)->update($data) ? msg(1,'修改成功') : msg(0,'修改失败');
            } catch (\Throwable $th) {
                msg(0,'修改失败');
            }
        }else{
            $data['create_time'] = time();
            try {
                $this->del_redis();
                Service::insert($data) ? msg(1,'添加成功') : msg(0,'添加失败');
            } catch (\Throwable $th) {
                msg(0,'添加失败');
            }
        }
    }
}
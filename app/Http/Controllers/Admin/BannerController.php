<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 09:38:45
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-02-24 16:59:38
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Banner;
use Image;
use Illuminate\Support\Facades\Cache;

class BannerController extends BaseController
{
    protected $redis_key_arr = [
        'api_banner_list_data',
        'api_banner_list_count'
    ];
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
        try {
            $this->del_redis();
            Banner::where('id',$id)->delete() ? msg(1,'删除成功') : msg(0,'删除失败');
        } catch (\Throwable $th) {
            msg(0,'删除失败');
        }
    }
    public function create(Request $request){
        $this->post($request);
    }
    public function edit(Request $request){
        $id = get_id($request->input('id'));
        $this->post($request,$id);
    }
    protected function post($request,$id = 0){
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
        $data['update_time'] = time();
        if (!empty($id)) {
            try {
                $this->del_redis();
                Banner::where('id',$id)->update($data) ? msg(1,'修改成功') : msg(0,'修改失败');
            } catch (\Throwable $th) {
                msg(0,'修改失败');
            }
        }else{
            $total = Banner::count();
            $total < 10 || msg(0,'轮播图数量已达上限10个,请删除后再添加');
            $data['create_time'] = time();
            try {
                $this->del_redis();
                Banner::insert($data) ? msg(1,'添加成功') : msg(0,'添加失败');
            } catch (\Throwable $th) {
                msg(0,'添加失败');
            }
        }
    }
    public function long_index(){
        $long_img_path = Cache::store('redis')->get('long_img_path');
        $img_url = img_path_url($long_img_path);
        return view('admin.banner.long_index',['img_url'=>$img_url]);
    }
    public function long_edit(Request $request){
        !empty($request->file('file')) || msg(0,"请上传图片");
        $data = [
            'suffix'=>$request->file('file')->extension()
        ];
        $rule = [
            'suffix'=>'required|in:jpeg,jpg,gif,bmp,png'
        ];
        $msg = [
            'suffix.required'=>'请选择图片',
            'suffix.in'=>'请选择正确的图片类型'
        ];
        $name = md5(time() . mt_rand(0,99)).'.'.data_check($data,$rule,$msg)['suffix'];
        $path = public_path().'/upload/'.date('Y/m/d/');
        is_dir($path) || mkdir($path,0777,true) || msg(0,'创建文件夹失败');
        $path .= $name;
        $img = Image::make($request->file('file'));
        $img->resize(750,null)->save($path);
        $url = domain_name() . str_replace(public_path(),'',$path);
        Cache::store('redis')->put('long_img_path',str_replace(public_path(),'',$path));
        showjson(['status'=>1,'msg'=>'上传成功','url'=>$url]);
    }
}
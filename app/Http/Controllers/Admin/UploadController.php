<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 11:31:22
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-03 09:21:59
 */
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Image;

class UploadController extends BaseController
{
    public function image(Request $request){
        $data = [
            'suffix'=>$request->file('file')->extension()
        ];
        $rule = [
            'suffix'=>'required|in:jpeg,jpg,gif,bmp,png'
        ];
        $msg = [
            'suffix.required'=>'请选择图片','suffix.in'=>'请选择正确的图片类型'
        ];
        data_check($data,$rule,$msg);
        $name = md5(time() . mt_rand(0,99)).'.'.data_check($data,$rule,$msg)['suffix'];
        $path = public_path().'/upload/'.date('Y/m/d/');
        is_dir($path) || mkdir($path,0777,true) || msg(0,'创建文件夹失败');
        $path .= $name;
        $img = Image::make($request->file('file'));
        $width = $img->width() * 0.3;
        $height = $img->height() * 0.3;
        $img->resize(750,380)->save($path);
        showjson(['status'=>1,'msg'=>'上传成功','path'=>str_replace(public_path(),'',$path)]);
    }
}
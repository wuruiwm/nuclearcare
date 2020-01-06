<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-06 10:31:43
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-06 10:42:51
 */

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use Image;

class UploadController extends BaseController
{
    public function image(Request $request){
        !empty($request->file('file')) || api_json(500,"请上传图片");
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
        $name = md5(time() . mt_rand(0,99)).'.'.data_check($data,$rule,$msg,0)['suffix'];
        $path = public_path().'/upload/'.date('Y/m/d/');
        is_dir($path) || mkdir($path,0777,true) || msg(0,'创建文件夹失败');
        $path .= $name;
        $img = Image::make($request->file('file'));
        $width = $img->width() * 0.5;
        $height = $img->height() * 0.5;
        $img->resize($width,$height)->save($path);
        $path = str_replace(public_path(),'',$path);
        api_json(200,'上传成功',['path'=>$path,'url'=>img_path_url($path)]);
    }
}
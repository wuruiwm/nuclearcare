<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 15:57:02
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-02-24 17:07:08
 */
namespace App\Http\Controllers\api;

use App\Models\Banner;
use Illuminate\Support\Facades\Cache;

class BannerController extends BaseController
{
    public function list(){
        extract(Banner::api_list());
        foreach ($data as $k => $v) {
            $data[$k]->url = img_path_url($v->img_path);
            unset($data[$k]->img_path);
        }
        $long_img_url = img_path_url(Cache::store('redis')->get('long_img_path'));
        api_json(200,'获取轮播图列表成功',$data,$count,['long_img_url'=>$long_img_url]);
    }
}
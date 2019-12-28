<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 15:57:02
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-27 17:22:45
 */
namespace App\Http\Controllers\api;

use App\Models\Banner;

class BannerController extends BaseController
{
    public function list(){
        extract(Banner::api_list());
        foreach ($data as $k => $v) {
            $data[$k]->url = img_path_url($v->img_path);
            unset($data[$k]->img_path);
        }
        api_json(200,'获取轮播图列表成功',$data,$count);
    }
}
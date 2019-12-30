<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-28 15:01:24
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-28 15:11:02
 */
namespace App\Http\Controllers\api;

use App\Models\Service;

class ServiceController extends BaseController
{
    public function list(){
        extract(Service::api_list());
        api_json(200,'获取服务列表成功',$data);
    }
}
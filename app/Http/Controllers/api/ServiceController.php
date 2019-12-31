<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-28 15:01:24
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-31 14:51:02
 */
namespace App\Http\Controllers\api;

use App\Models\Service;

class ServiceController extends BaseController
{
    public function list(){
        $data = Service::api_list();
        $standard = [
            'data' =>[],
            'count'=>0
        ];
        $additional = [
            'data' =>[],
            'count'=>0
        ];
        foreach ($data as $k => $v) {
            if($v->type == 1){
                $standard['data'][] = $v;
                $standard['count']++; 
            }
            if($v->type == 2){
                $additional['data'][] = $v;
                $additional['count']++; 
            }
        }
        api_json(200,'获取服务列表成功',['standard'=>$standard,'additional'=>$additional]);
    }
}
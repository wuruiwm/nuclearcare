<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 15:20:43
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-31 15:06:18
 */

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function __construct(){
        Log::info($_SERVER["REQUEST_URI"]);
        Log::info(request()->all());
        Log::info(request()->header());
    }
    protected function del_redis(){
        foreach ($this->redis_key_arr as $v) {
            Cache::store('redis')->forget($v);
        }
    }
}
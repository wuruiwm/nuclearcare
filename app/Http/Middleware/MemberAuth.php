<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-06 10:53:03
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-06 10:57:30
 */

namespace App\Http\Middleware;

use Closure;

class MemberAuth
{
    public function handle($request, Closure $next){
        $request->attributes->add(['member_id'=>get_token()]);
        return $next($request);
    }
}

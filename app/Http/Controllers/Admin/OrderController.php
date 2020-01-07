<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2020-01-07 09:46:33
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-07 11:35:54
 */

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{
    public function index(){
        return view('admin.order.index');
    }
    public function list(Request $request){
        extract(page($request->input()));
        extract(Order::list($number,$limit,$request->input('keyword'),$request->input('type'),$request->input('status')));
        return ['data'=>$data,'count'=>$count,'code'=>0];
    }
}
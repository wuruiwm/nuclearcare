<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 15:20:43
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2019-12-27 16:36:32
 */

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//文件上传接口
Route::post('upload', 'ApiController@upload')->name('api.upload');

//api路由
Route::group(['namespace' => 'api'],function(){
    //轮播图
    Route::get('banner','BannerController@list');  
});
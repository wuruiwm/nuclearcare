<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 15:20:43
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-02 13:48:24
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
    //wx相关
    Route::group(['prefix'=>'wx'],function(){
        Route::post('login','WxController@login');
        Route::post('memberdecrypt','WxController@memberdecrypt');
        Route::post('mobiledecrypt','WxController@mobiledecrypt');
        Route::post('set_token','WxController@set_token');
        Route::any('notify','WxController@notify');
    });
    //服务
    Route::get('service','ServiceController@list');
    //充值
    Route::post('recharge','MemberController@recharge');
});
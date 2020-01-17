<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 15:20:43
 * @LastEditors  : 傍晚升起的太阳
 * @LastEditTime : 2020-01-15 11:52:34
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        //左侧菜单
        view()->composer('admin.layout',function($view){
            $menus = \App\Models\Permission::with(['childs'])->where('parent_id',0)->orderBy('sort','desc')->get();
            $view->with('menus',$menus);
        });
        DB::listen(function($query) {
            Log::info('SQL：'.$query->sql.' 参数：'.json_encode($query->bindings).' 耗时：'.$query->time.'ms');
        });
    }
}

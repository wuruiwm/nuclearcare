<?php
/*
 * @Author: 傍晚升起的太阳
 * @QQ: 1250201168
 * @Email: wuruiwm@qq.com
 * @Date: 2019-12-27 14:10:20
 * @LastEditors: 傍晚升起的太阳
 * @LastEditTime: 2019-12-27 14:10:29
 */
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    protected $dates = [
        'create_time',
        'update_time'
    ];
    public $timestamps = false;
}
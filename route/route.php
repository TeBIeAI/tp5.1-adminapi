<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// Route::get('think', function () {
//     return 'hello,ThinkPHP5!';
// });

// Route::get('hello/:name', 'index/hello');

// 用户登录 登出

// use think\Route;

// Route::domain('adminapi', function () {

// });

Route::post('login', 'adminapi/login/login');
Route::get('logout', 'adminapi/login/logout');

// 权限接口
Route::resource('auths', 'adminapi/auth', [], ['id' => '\d+']);
// 查询菜单权限接口
Route::get('nav', 'adminapi/auth/nav');
//  角色接口
Route::resource('roles', 'adminapi/role', [], ['id' => '\d+']);
// 管理员
Route::resource('admins', 'adminapi/admin', [], ['id' => '\d+']);

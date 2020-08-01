<?php
namespace app\adminapi\logic;

use app\common\model\Admin;
use app\common\model\Auth;
use app\common\model\Role;
use tools\jwt\Token;

class AuthLogic
{
    /**
     * 检测用户是否有权访问
     *
     * @return Bool
     */
    public static function check()
    {
        // 判断是否特殊页面(比如首页，不要检测)
        $controller = request()->controller(); // 控制器名称 返回的首字母是大写的
        $action     = request()->action(); // 方法名称
        echo $controller . '__' . $action;

        if ($controller == 'Index' && $action == 'index') {
            //访问首页不要检测(有权限访问)
            return true;
        }

        // 获取到管理员角色id
        $user_id = Token::getUserId();
        $info    = Admin::find($user_id);
        $role_id = $info['role_id'];

        // 判断是否超级管理员
        if ($role_id == 1) {
            return true;
        }

        // 查询当前管理员所拥有的权限数组ids (从角色表查询role_auth_ids)
        $role_ids      = Role::find($role_id);
        $role_auth_ids = explode(',', $role_ids['role_auth_ids']); //字符串转数组

        // 根据当前访问的控制器， 方法查询到具体的权限id
        $auth    = Auth::where('auth_c', $controller)->where('auth_a', $action)->find();
        $auth_id = $auth['auth_id'];

        // 判断当前权限id 是否在role_auth_ids范围中
        if (in_array($auth_id, $role_auth_ids)) {
            return true;
        }
        // 没有权限访问
        return false;
    }
}

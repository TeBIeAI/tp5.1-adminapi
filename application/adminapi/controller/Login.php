<?php
namespace app\adminapi\controller;

use app\common\model\Admin;
use tools\jwt\Token;

class Login extends BaseApi
{
    public function index()
    {

    }

    /**
     * 用户登录
     *
     * @return void
     */
    public function login()
    {
        // echo encrypt_password(123456);
        // 接受参数
        $params = input();

        // 参数检测
        $validate = $this->validate($params, [
            'username|用户名' => 'require',
            'password|密码'  => 'require',
        ]);
        if ($validate !== true) {
            // 验证失败
            $this->fail($validate, 401);die;
        }

        // 查询用户表进行认证
        $password = encrypt_password($params['password']);
        $info     = Admin::where('username', $params['username'])
            ->where('password', $password)
            ->find();
        if (empty($info)) {
            $this->fail('用户名密码错误', 401);die;
        }
        // 生成token
        $token = \tools\jwt\Token::getToken($info['id']);

        // 返回数据
        $data = [
            'token'    => $token,
            'user_id'  => $info['id'],
            'username' => $info['username'],
            'email'    => $info['email'],
        ];

        $this->ResSuccess($data);
    }

    public function logout()
    {
        // var_dump($_SERVER);
        // 记录token 为退出
        // 获取当前请求中token
        $token          = Token::getRequestToken();
        $delete_token   = cache('delete_token') ?: [];
        $delete_token[] = $token;
        // 将推出的token添加到数字中重新存到缓存中
        cache('delete_token', $delete_token, 86400);

        // 返回数据
        // 返回数据
        $data = [
            'code' => 200,
            'msg'  => '退出成功',
        ];

        $this->ResSuccess($data);

    }
}

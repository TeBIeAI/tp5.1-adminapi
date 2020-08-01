<?php

namespace app\adminapi\controller;

use app\adminapi\logic\AuthLogic;
use think\Controller;
use tools\jwt\Token;

class BaseApi extends Controller
{
    // 无需验证token的接口
    // protected static $white = ['login/login', 'login/logout'];
    protected static $white = ['login/login'];

    protected function initialize()
    {
        // 父类初始化
        parent::initialize();

        // 处理跨域
        header('Access-Control-Allow-Origin:*'); //允许跨域
        header('Access-Control-Allow-Methods:OPTIONS, GET, POST'); // 允许option，get，post请求
        header('Access-Control-Allow-Headers:origin, x-requested-with, Content-Type, Accept, Authorization'); // 允许x-requested-with

        // 登录检测
        try {
            // 获取当前请求的控制器
            $path = strtolower($this->request->controller()) . '/' . $this->request->action();
            if (!in_array($path, self::$white)) {
                // 需要做登录判断
                $this->user_id = Token::getUserId();
                if (empty($this->user_id)) {
                    $this->fail('token验证失败', 403);die;
                }
                // 权限检测
                $auth_check = AuthLogic::check();
                if (!$auth_check) {
                    $this->fail('没有权限访问');
                }
            }
        } catch (\Exception $e) {
            //throw $th;
            $this->fail('token解析失败', 403);
        }
    }

    /**
     * 通用请求
     *
     * @param 响应码 $code
     * @param 响应信息 $msg
     * @param 响应数据 $data
     * @return void
     */
    protected function response($code = 200, $msg = "success", $data = [])
    {
        $res = [
            'code' => $code,
            'msg'  => $msg,
            'data' => $data,
        ];

        // JSON_UNESCAPED_UNICODE防止返回数据中汉字  unicode
        // echo json_encode($res, JSON_UNESCAPED_UNICODE);die;
        return json($res)->send(); // send 组织返回数据后  阻止代码运行
    }

    /**
     * 请求成功响应
     *
     * @param 响应码 $code
     * @param 响应信息 $msg
     * @param 响应数据 $data
     * @return void
     */
    protected function ResSuccess($data = [], $code = 200, $msg = "success")
    {
        $this->response($code, $msg, $data);
    }

    /**
     * 请求成功响应
     *
     * @param 响应码 $code
     * @param 响应信息 $msg
     * @return void
     */
    protected function fail($msg = "error", $code = 401)
    {
        $this->response($code, $msg);
    }

}

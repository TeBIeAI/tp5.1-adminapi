<?php

namespace app\adminapi\controller;

use app\common\model\Admin as ModelAdmin;
use think\Controller;
use think\Request;

class Admin extends BaseApi
{
    /**
     * 显示管理员列表
     *
     * @return \think\Response
     */
    public function index()
    {
        // 接受参数 keyword
        $params  = input();
        $where   = [];
        $keyword = $params['keyword'];
        $where[] = ['username', 'like', "%$keyword%"];
        // 连表查询 SELECT `t1`.*,`t2`.`role_name` FROM `hc_admin` `t1` LEFT JOIN `hc_role` `t2` ON `t1`.`role_id`=`t2`.`id` WHERE  `username` LIKE '%a%' LIMIT 0, 1
        $list = ModelAdmin::alias('t1')
            ->join('hc_role t2', 't1.role_id=t2.id', 'left')
            ->field('t1.*, t2.role_name')
            ->where($where)
            ->paginate(5);

        $this->ResSuccess($list);
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 新建管理员
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        // 接收数据
        $params = input();

        // 检测数据
        $validate = $this->validate($params, [
            'username|用户名' => 'require|unique:admin,username',
            'email|邮箱'     => 'require|email',
            'role_id|所属角色' => 'require|integer|gt:0',
            'password'     => 'length:6,20',
        ]);
        if ($validate !== true) {
            $this->fail($validate, 401);
        }

        // 添加数据
        if (empty($params['password'])) {
            $params['password'] = '123456';
        }
        $params['password'] = encrypt_password($params['password']);
        $params['nickname'] = $params['username'];
        $info               = ModelAdmin::create($params, true);
        // 查询刚才添加的完整数据
        $admin = ModelAdmin::find($info['id']);
        // 返回数据
        $this->ResSuccess($admin);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     * 重置密码 请求put
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        // 避免修改超级管理员
        if ($id == 1) {
            $this->fail('超级管理员无权修改', 401);die;
        }

        // 接收参数
        $params = input();
        if (!empty($params['type']) && $params['type'] == 'reset_pwd') {
            $password = encrypt_password('123456');
            ModelAdmin::update(['password' => $password], ['id' => $id], true);
        } else {
            // 检测数据
            $validate = $this->validate($params, [
                'email|邮箱'     => 'email',
                'role_id|所属角色' => 'integer|gt:0',
                'nickname|昵称'  => 'max:50',
            ]);
            if ($validate !== true) {
                $this->fail($validate, 401);die;
            }
            // 修改数据 （用户名和密码不能修改）
            unset($params['username']);
            unset($params['password']);
            ModelAdmin::update($params, ['id' => $id], true);
        }

        $info = ModelAdmin::find($id);
        $this->ResSuccess($info);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        // 不能删除超级管理员 （也不能删除他自己）
        if ($id == 1) {
            $this->fail('超级管理员无法删除', 401);die;
        }
        if ($this->user_id == $id) {
            $this->fail('删除自己，你在开玩笑吗？？？？？？？', 401);die;
        }
        // 删除数据
        ModelAdmin::destroy($id);

        // 返回结果
        $this->ResSuccess();
    }
}

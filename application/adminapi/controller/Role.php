<?php

namespace app\adminapi\controller;

use app\common\model\Auth;
use app\common\model\Role as ModelRole;
use think\Controller;
use think\Request;

class Role extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        // 查询数据
        // field('id, role_name, desc, role_auths')
        $list = ModelRole::select();
        // 对每条角色数据  查询对应的权限 增加role_auths下标的父子级树状结构数据
        foreach ($list as &$v) {
            // 查询权限表
            $auths           = Auth::where('id', 'in', $v['role_auth_ids'])->select();
            $auths           = (new \think\Collection($auths))->toArray();
            $auths           = get_tree_list($auths);
            $v['role_auths'] = $auths;
        }
        unset($v);
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
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        // 接受数据
        $params = input();

        // 检测参数
        $validate = $this->validate($params, [
            'role_name' => 'require',
            'role_ids'  => 'require',
        ]);
        if ($validate !== true) {
            $this->fail($validate, 401);die;
        }

        // 添加数据
        $params['role_auth_ids'] = $params['role_ids'];
        $role                    = ModelRole::create($params, true);
        $info                    = ModelRole::find($role['id']);

        $this->ResSuccess($info);
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        // 查询数据
        $data = ModelRole::find($id);
        if (empty($data)) {
            $this->fail('查询错误', 401);die;
        }
        $this->ResSuccess($data);
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
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}

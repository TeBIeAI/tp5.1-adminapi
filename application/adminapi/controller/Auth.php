<?php

namespace app\adminapi\controller;

use app\common\model\Admin;
use app\common\model\Auth as ModelAuth;
use app\common\model\Role;
use think\Controller;
use think\Request;

class Auth extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     *
     * @return 列表 普通列表 无限级分类列表  父子级树类列表
     */
    public function index()
    {
        // 接收参数  keyword type
        $params = input();

        $where = [];
        // 查询数据
        if (!empty($params['keyword'])) {
            $where[] = ["auth_name", 'like', "%" . $params['keyword'] . "%"];
        }
        $list = ModelAuth::field('pid, pid_path, id, auth_name, auth_c, auth_a, is_nav')
            ->where($where)
            ->select();
        // 转化为标准的二维数组
        $list = (new \think\Collection($list))->toArray();
        if (!empty($params['type']) && $params['type'] == 'tree') {
            // 父子级树类列表
            $list = get_tree_list($list);
        } else {
            // 无限级分类列表
            $list = get_cate_list($list);
        }

        // 返回数据
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
        echo 'aaa';

        // 接收数据
        $params = input();
        // 参数检测
        $validate = $this->validate($params, [
            'auth_name|权限名称' => 'require',
            'pid|上级权限'       => 'require',
            'is_nav|菜单权限'    => 'require',
        ]);
        if ($validate !== true) {
            $this->fail($validate, 401);die;
        }
        // 添加数据
        if ($params['pid'] == 0) {
            $params['level']    = 0;
            $params['pid_path'] = 0;
            $params['auth_c']   = '';
            $params['auth_a']   = '';
        } else {
            // 不是顶级权限 1 查询上级
            $p_info = ModelAuth::find($params['pid']);
            if (empty($p_info)) {
                $this->fail('数据异常', 402);die;
            }
            // 设置级别 +1  家族图谱拼接
            $params['level']    = $p_info['level'] + 1;
            $params['pid_path'] = $p_info['pid_path'] . '_' . $p_info['id'];
        }
        $auth    = ModelAuth::create($params, true);
        $newInfo = ModelAuth::find($auth['id']);

        // 返回数据
        $this->ResSuccess($newInfo);
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
        $auth = ModelAuth::field('pid, pid_path, id, auth_name, auth_c, auth_a, is_nav')
            ->find($id);
        // 返回数据
        $this->ResSuccess($auth);
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
        // 修改使用put
        // 接受数据
        $params = input();
        // 参数检测
        $validate = $this->validate($params, [
            'auth_name|权限名称' => 'require',
            'pid|上级权限'       => 'require',
            'is_nav|菜单权限'    => 'require',
        ]);
        if ($validate !== true) {
            $this->fail($validate, 401);
        }

        // 修改数据
        $auth = ModelAuth::find($id);
        if ($params['pid'] == 0) {
            // 如果修改为顶级权限
            $params['level']    = 0;
            $params['pid_path'] = 0;
        } else if ($params['pid'] != $auth['pid']) {
            // 如果修改为其上级权限 重新设计level级别 和pid_path家族图谱
            $p_auth = ModelAuth::find($params['pid']);
            if (empty($p_auth)) {
                $this->fail('数据异常', 403);die;
            }
            $params['level']    = $p_auth['level'] + 1;
            $params['pid_path'] = $p_auth['pid_path'] . '_' . $p_auth['id'];
        }
        ModelAuth::update($params, ['id' => $id], true);
        // 返回数据
        $data = ModelAuth::find($id);
        $this->ResSuccess($data);
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        // 判断权限下是否有子权限
        echo $id;
        $total = ModelAuth::where('pid', $id)->count();
        echo $total;
        if ($total > 0) {
            $this->fail('含有子权限，不能删除');die;
        }

        // 删除数据
        ModelAuth::destroy($id);

        // 返回数据
        $this->ResSuccess();
    }

    /**
     * 菜单权限
     *
     * @return void
     */
    public function nav()
    {
        // 获取用户登录管理员 用户id
        $user_id = $this->user_id;

        // 查询管理员角色id
        $info    = Admin::find($user_id);
        $role_id = $info['role_id'];

        // 判断是否超级管理员
        if ($role_id == 1) {
            // 超级管理员
            $data = ModelAuth::where('is_nav', 1)->select();
        } else {
            // 先查询角色表
            $role = Role::find($role_id);
            // 在查询权限表
            $role_auth_ids = $role['role_auth_ids'];
            $data          = ModelAuth::where('is_nav', 1)->where('id', 'in', $role_auth_ids)->select();
        }
        // 先转化为标准二维数组
        $data = (new \think\Collection($data))->toArray();

        // 在转化为父子级树状结构
        $data = get_tree_list($data);
        $this->ResSuccess($data);
    }
}

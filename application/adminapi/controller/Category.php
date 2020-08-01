<?php

namespace app\adminapi\controller;

use app\common\model\Category as ModelCategory;
use think\Controller;
use think\Request;

class Category extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        // 查询数据
        $list = ModelCategory::select();
        // 转化无线级分类列表
        $list = (new \think\Collection($list))->toArray();
        $list = get_cate_list($list);
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
        // 接收参数
        $params = input();

        // 参数检测
        $validate = $this->validate($params, [
            'cate_name' => 'require|length:2,20',
            'pid'       => 'require',
            'is_show'   => 'require|in:0,1',
            'is_hot'    => 'require|in:0,1',
            'sort'      => 'require|between:0,9999',
        ]);
        if ($validate !== true) {
            $this->fail($validate);die;
        }

        // 添加数据 （处理pid pid_path_name level）
        if ($params['pid'] == 0) {
            $params['pid_path']      = 0;
            $params['pid_path_name'] = '';
            $params['level']         = 0;
        } else {
            // 不是顶级分类 查询其上级分类
            $p_info = ModelCategory::where('id', $params['pid'])->find();
            if (empty($p_info)) {
                // 没有查到父级
                $this->fail('数据异常，请稍后再试');die;
            }
            $params['pid_path']      = $p_info['pid_path'] . '_' . $p_info['id'];
            $params['pid_path_name'] = $p_info['pid_path_name'] . '_' . $p_info['cate_name'];
            $params['level']         = $p_info['level'] + 1;
        }

        // logo图片
        // $params['image_url'] = $params['logo'];
        $cate = ModelCategory::create($params, true);
        $info = ModelCategory::find($cate['id']);

        // 返回数据
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
        $list = ModelCategory::find($id);
        $this->ResSuccess($list);
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
        // 接收参数
        $params = input();

        // 参数检测
        $validate = $this->validate($params, [
            'cate_name' => 'require|length:2,20',
            'pid'       => 'require',
            'is_show'   => 'require|in:0,1',
            'is_hot'    => 'require|in:0,1',
            'sort'      => 'require|between:0,9999',
        ]);
        if ($validate !== true) {
            $this->fail($validate);die;
        }

        // 修改数据 （处理pid pid_path_name level）
        if ($params['pid'] == 0) {
            $params['pid_path']      = 0;
            $params['pid_path_name'] = '';
            $params['level']         = 0;
        } else {
            // 不是顶级分类 查询其上级分类
            $p_info = ModelCategory::where('id', $params['pid'])->find();
            if (empty($p_info)) {
                // 没有查到父级
                $this->fail('数据异常，请稍后再试');die;
            }
            $params['pid_path']      = $p_info['pid_path'] . '_' . $p_info['id'];
            $params['pid_path_name'] = $p_info['pid_path_name'] . '_' . $p_info['cate_name'];
            $params['level']         = $p_info['level'] + 1;
        }

        // logo图片
        if (isset($params['logo']) && !empty($params['logo'])) {
            $params['image_url'] = $params['logo'];
        }
        $cate = ModelCategory::update($params, ['id' => $id, true]);
        $info = ModelCategory::find($cate['id']);

        // 返回数据
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
        // 查询数据
        $total = ModelCategory::where('pid', $id)->count();
        // 数据检测
        if ($total > 0) {
            $this->fail('分类下有子分类， 不允许删除');die;
        }
        ModelCategory::destroy($id);
        $this->ResSuccess('删除成功');
    }
}

<?php

namespace app\adminapi\controller;

use app\common\model\Brand as ModelBrand;
use app\common\model\Goods as ModelGoods;
use think\Controller;
use think\Request;

class Brand extends BaseApi
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
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
        // 接受参数
        $params = input();

        // 参数检测
        $validate = $this->validate($params, [
            'name'    => 'require',
            'cate_id' => 'require|integer|gt:0',
            'is_hot'  => 'require|in:0,1',
            'is_sort' => 'require|between:0,9999',
        ]);
        if ($validate !== true) {
            $this->fail($validate);die;
        }
        // 生成缩略图
        if (isset($params['logo']) && !empty($params['logo']) && is_file('.' . $params['logo'])) {
            $image = \think\Image::open('.' . $params['logo'])->thumb(200, 100)->save('.' . $params['logo']);
        }
        // 添加数据
        $brand = ModelBrand::create($params);
        $info  = ModelBrand::find($brand['id']);
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
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $params = input();
        // 参数检测
        $validate = $this->validate($params, [
            'name'    => 'require',
            'cate_id' => 'require|integer|gt:0',
            'is_hot'  => 'require|in:0,1',
            'is_sort' => 'require|between:0,9999',
        ]);
        if ($validate !== true) {
            $this->fail($validate);die;
        }
        // 修改数据(logo 缩略图)
        if (isset($params['logo']) && !empty($params['logo']) && is_file('.' . $params['logo'])) {
            // 生成缩略图
            $image = \think\Image::open('.' . $params['logo'])->thumb(200, 100)->save('.' . $params['logo']);
        }
        // 修改数据
        ModelBrand::update($params, ['id' => $id], true);
        $info = ModelBrand::find($id);
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
        // 判断品牌下 是否有商品
        $total = ModelGoods::where('brand_id', $id)->count();
        if ($total > 0) {
            $this->fail('品牌下有商品 不能删除');
        }
        ModelBrand::destroy($id);
        $this->ResSuccess();
    }
}

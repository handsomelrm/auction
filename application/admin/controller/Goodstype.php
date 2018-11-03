<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/22/022
 * Time: 17:27
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;
use think\Request;

class Goodstype extends Controller
{
    //添加商品分类
    public function add()
    {
        if (Request::instance()->isPost()) {
            $add_goodsType_feedbook = ['code'=>200,'mess'=>'添加分类成功'];
            $data = Request::instance()->post();
            $result = Db::name('goods_type')->insert($data);
            if (!$result) {
                $add_goodsType_feedbook = ['code'=>500,'mess'=>'添加分类失败'];
            }
            echo json_encode($add_goodsType_feedbook, JSON_UNESCAPED_UNICODE);
        }else{
            $add_goodsType_feedbook = ['code'=>500,'mess'=>'未请求参数'];
            echo json_encode($add_goodsType_feedbook, JSON_UNESCAPED_UNICODE);
        }
    }

    //商品分类列表
    public function lists()
    {
        //总数
        $totalCount = Db::name('goods_type')->count('gt_id');

        $goodsType_list = Db::name('goods_type')->order('gt_id desc')->select();
        if (!empty($goodsType_list)) {
            $paginate_feedbook = ['code' => 200, 'mess' => '处理数据成功', "count" => $totalCount, 'data' => $goodsType_list];
        } else {
            $paginate_feedbook = ['code' => 500, 'mess' => '处理数据失败', "count" => '数据接口异常', 'data' => '数据接口异常'];
        }
        echo json_encode($paginate_feedbook, JSON_UNESCAPED_UNICODE);
    }

    //修改商品分类
    public function edit()
    {
        if (Request::instance()->isPost()) {
            $edit_goodsType_feedbook = ['code'=>200,'mess'=>'修改分类成功'];
            $data = Request::instance()->post();
            $result = Db::name('goods_type')->where('gt_id', $data['gt_id'])->update($data);
            if (!$result) {
                $edit_goodsType_feedbook = ['code'=>500,'mess'=>'修改分类失败'];
            }
            echo json_encode($edit_goodsType_feedbook, JSON_UNESCAPED_UNICODE);
        }
    }

    //删除商品分类
    public function del()
    {
        $del_goodsType_feedbook = ['code' => 200, 'mess' => '删除商品分类成功'];
        $gt_id = input('gt_id');
        //判断 get 传递的值是否存在
        if (!empty($gt_id)){
            $result = Db::name('goods_type')->where('gt_id', $gt_id)->delete();
            if (!$result) {
                $del_goodsType_feedbook = ['code' => 500, 'mess' => '删除商品分类失败'];
            }
        }else{
            $del_goodsType_feedbook = ['code' => 500, 'mess' => '未请求参数，数据接口异常'];
        }
        echo json_encode($del_goodsType_feedbook, JSON_UNESCAPED_UNICODE);
    }
}
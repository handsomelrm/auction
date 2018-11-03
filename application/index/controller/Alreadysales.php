<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/30/030
 * Time: 12:45
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Session;

class Alreadysales extends Controller
{
    //卖家的已售的商品列表
    function alreadySalesLists()
    {
        //页数
        $page = input('page');
        $pageIndex = isset($page) ? $page : 1;
        //每页显示条数
        $limit = input('limit');
        $pageSize = isset($limit) ? $limit : 5;

        //接收前台传递的 token 值，根据这个值进行查询
        $u_token = input('u_token');
        $u_id = Db::name('user')->where('u_token',$u_token)->value('u_id');

        //总数
        $totalCount =  Db::name('already_sales')->where('u_id', $u_id)->count('g_id');

        $goods_list = Db::name('already_sales')->alias('as')
            ->join('goods g', 'as.g_id=g.g_id')
            ->join('user u', 'as.u_id=u.u_id')
            ->where('as.u_id', $u_id)
            ->order('as.g_id desc')
            ->limit('' . ($pageIndex - 1) * $pageSize . ',' . $pageSize . '')
            ->select();

        for ($i = 0; $i < count($goods_list); $i++) {
            $goods_list[$i]['g_img'] = 'uploads/' . $goods_list[$i]['g_img'];
        }

        //判断 结果集 是否有数据
        if (isset($goods_list)) {
            $paginate_feedbook = ['code' => 200, 'mess' => '处理数据成功', "count" => $totalCount, 'data' => $goods_list];
        } else {
            $paginate_feedbook = ['code' => 500, 'mess' => '处理数据失败', "count" => '数据接口异常', 'data' => '数据接口异常'];
        }
        echo json_encode($paginate_feedbook, JSON_UNESCAPED_UNICODE);
    }
}
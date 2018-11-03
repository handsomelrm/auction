<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/25/025
 * Time: 10:38
 */


namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Session;

//用户拍卖商品管理控制器
class Bidders extends Controller
{
    //用户已上架的竞拍商品列表
    public function lists()
    {
        //接收前台传递的 token 值，根据这个值进行查询
        $u_token = input('u_token');
        if (!empty($u_token)) {
            $u_id = Db::name('user')->where('u_token', $u_token)->value('u_id');

            //总数
            $totalCount = Db::name('bidders')->alias('b')
                ->join('goods g', 'b.g_id=g.g_id')
                ->where('g.u_id', $u_id)->count('b.b_id');


            //用户正在拍卖的商品（包含：当前出价、出价次数）
            $bidders_list = Db::name('bidders')->alias('b')
                ->join('goods g', 'b.g_id=g.g_id')
                ->where('g.u_id', $u_id)
                ->where('b.b_status', 1)
                ->order('b.b_id desc')
                ->select();

            for ($i = 0; $i < count($bidders_list); $i++) {
                $bidders_list[$i]['g_img'] = 'uploads/' . $bidders_list[$i]['g_img'];
                //无人拍卖时，以拍卖表中的最高价，进行前台渲染当前价，后续有人出价，则动态显示当前价
                //这里判断：无人出价时，拍卖表中的最高价为0.00，则把当前商品拍卖价格，赋值给卖表中的最高价
                if (empty($bidders_list[$i]['b_user_highpay']) || $bidders_list[$i]['b_user_highpay'] == 0.00) {
                    $bidders_list[$i]['b_user_highpay'] = $bidders_list[$i]['g_price'];
                }

//            if (time() > strtotime($bidders_list[$i]['g_auction_time'])) {
//                $bidders_list[$i]['g_auction_time'] = '该商品已结束拍卖';
//            }
            }


            //判断 结果集 是否有数据
            if (!empty($bidders_list)) {
                $paginate_feedbook = ['code' => 200, 'mess' => '处理数据成功', "count" => $totalCount, 'data' => $bidders_list];
            } else {
                $paginate_feedbook = ['code' => 500, 'mess' => '处理数据失败', "count" => '无数据', 'data' => '无数据'];
            }
        }else{
            $paginate_feedbook = ['code' => 500, 'mess' => '未请求参数，数据接口异常'];
        }

        echo json_encode($paginate_feedbook, JSON_UNESCAPED_UNICODE);
    }

    //点击下架按钮
    public function down()
    {
        $del_biddersGoods_feedbook = ['code' => 200, 'mess' => '下架商品成功'];
        $g_id = input('g_id');
        //1.判断 get 传递的值是否存在
        if (!empty($g_id)) {
            //执行下架
            $goods_data = [
                'g_status' => 0
            ];
            $bidders_data = [
                'b_status' => 0
            ];
            $goods_result = Db::name('goods')->where('g_id', $g_id)->update($goods_data);
            $bidders_result = Db::name('bidders')->where('g_id', $g_id)->update($bidders_data);
            if (!$goods_result && !$bidders_result) {
                $del_biddersGoods_feedbook = ['code' => 500, 'mess' => '下架商品失败'];
            }
        } else {
            $del_biddersGoods_feedbook = ['code' => 500, 'mess' => '未请求参数，数据接口异常'];
        }
        echo json_encode($del_biddersGoods_feedbook, JSON_UNESCAPED_UNICODE);
    }
}
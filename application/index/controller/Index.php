<?php
namespace app\index\controller;

use think\Controller;
use think\Db;


class Index extends Controller
{
    //显示所有正在拍卖的商品
    public function index()
    {
        //页数
        $page = input('page');
        $pageIndex = isset($page) ? $page : 1;
        //每页显示条数
        $limit = input('limit');
        $pageSize = isset($limit) ? $limit : 5;

        //总数
        $totalCount = Db::name('bidders')->alias('b')
            ->join('goods g', 'b.g_id=g.g_id')
            ->where('b_status',1)
            ->count('b_id');

        //所有正在拍卖的商品（包含：当前出价、出价次数）
        $bidders_list = Db::name('bidders')->alias('b')
            ->join('goods g', 'b.g_id=g.g_id')
            ->where('b.b_status',1)
            ->order('b.b_id desc')
            ->limit('' . ($pageIndex - 1) * $pageSize . ',' . $pageSize . '')
            ->field('g.g_id,g.g_name,b.b_user_highpay,g_price,g.g_low_price,g.g_img,b.b_count_pay,b.b_create_time,g.g_auction_time')
            ->select();

        for ($i = 0; $i < count($bidders_list); $i++) {
            $bidders_list[$i]['g_img'] = 'uploads/'.$bidders_list[$i]['g_img'];
            //无人拍卖时，以拍卖表中的最高价，进行前台渲染当前价，后续有人出价，则动态显示当前价
            //这里判断：无人出价时，拍卖表中的最高价为0.00，则把当前商品拍卖价格，赋值给卖表中的最高价
            if (empty($bidders_list[$i]['b_user_highpay']) || $bidders_list[$i]['b_user_highpay'] == 0.00){
                $bidders_list[$i]['b_user_highpay'] = $bidders_list[$i]['g_price'];
            }

            if (time() > strtotime($bidders_list[$i]['g_auction_time'])) {
                $bidders_list[$i]['g_auction_time'] = '该商品已结束拍卖';
            }
        }

        //判断 结果集 是否有数据
        if (!empty($bidders_list)) {
            $paginate_feedbook = ['code' => 200, 'mess' => '处理数据成功', "count" => $totalCount, 'data' => $bidders_list];
        } else {
            $paginate_feedbook = ['code' => 500, 'mess' => '处理数据失败', "count" => '无数据', 'data' => '无数据'];
        }
        echo json_encode($paginate_feedbook, JSON_UNESCAPED_UNICODE);
    }
}

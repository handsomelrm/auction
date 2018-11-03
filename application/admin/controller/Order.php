<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 2018/11/3
 * Time: 11:53
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;

class Order extends Controller
{
    //所有用户的订单列表
    public function lists(){
        $order_count = Db::name('order')->count('o_id');
        $order_data = Db::name('order')->alias('o')
            ->join('user u','o.u_id=u.u_id')
            ->join('goods g','o.g_id=g.g_id')
            ->join('harvest h','o.h_id=h.h_id')
            ->join('goods_type gt','g.gt_id=gt.gt_id')
            ->select();

        if (!empty($order_data)){
            $order_feedbook = ['code'=>200,'mess'=>'处理数据成功','count'=>$order_count,'data'=>$order_data];
        }else{
            $order_feedbook = ['code'=>500,'mess'=>'处理数据失败','data'=>'无数据'];
        }
        echo json_encode($order_feedbook,JSON_UNESCAPED_UNICODE);
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/22/022
 * Time: 15:21
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;

class Index extends Controller
{
    public function index(){
        //订单总数
        $order_count = Db::name('order')->count('o_id');
        //正在拍卖商品总数
        $bidders_count = Db::name('bidders')->where('b_status',1)->count('b_id');
        //用户总数
        $user_count = Db::name('user')->count('u_id');
        $count_feedbook = ['code'=>200,'mess'=>'处理数据成功','order_count'=>$order_count,'bidders_count'=>$bidders_count,'user_count'=>$user_count];
        echo json_encode($count_feedbook,JSON_UNESCAPED_UNICODE);
    }
}
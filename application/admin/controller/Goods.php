<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 2018/11/3
 * Time: 12:03
 */

namespace app\admin\controller;


use think\Controller;
use think\Db;

class Goods extends Controller
{
    //所有用户的商品列表
    public function lists(){
        $goods_count = Db::name('goods')->count('g_id');
        $goods_data = Db::name('goods')->alias('g')
            ->join('user u','g.u_id=u.u_id')
            ->join('goods_type gt','g.gt_id=gt.gt_id')
            ->select();

        if (!empty($goods_data)){
            $goods_feedbook = ['code'=>200,'mess'=>'处理数据成功','count'=>$goods_count,'data'=>$goods_data];
        }else{
            $goods_feedbook = ['code'=>500,'mess'=>'处理数据失败','data'=>'无数据'];
        }
        echo json_encode($goods_feedbook,JSON_UNESCAPED_UNICODE);
    }
}
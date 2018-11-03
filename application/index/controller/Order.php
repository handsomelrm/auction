<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 2018/10/27
 * Time: 14:08
 */

namespace app\index\controller;


use alipay\Pagepay;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Order extends Controller
{
    //1.生成订单信息
    public function order()
    {
        //1.判断：如果倒计时为00:00:00时，取当前拍卖表中用户和金额
        //1.1.接收前台传递的结束时间，接收前台传递的商品 ID
        $data = Request::instance()->param();
        if (!empty($data['end_time']) && !empty($data['g_id'])) {
            if ($data['end_time'] == '00:00:00') {
                $bidder_data = Db::name('bidders')->where('g_id', $data['g_id'])->find();
                //1.2.如果有数据，表示商品在拍卖期间，有用户出价拍卖
                if (!empty($bidder_data['u_id']) && !empty($bidder_data['b_user_highpay'])) {
                    //2.生成订单
                    $order_data = [
                        'o_trade_no' => time() . rand(1000, 9999),
                        'o_payment' => $bidder_data['b_user_highpay'],
                        'o_create_time' => date('Y-m-d H:i:s', time()),
                        'u_id' => $bidder_data['u_id'],
                        'g_id' => $bidder_data['g_id']
                    ];
                    $order_result = Db::name('order')->insert($order_data);
                    if ($order_result) {
                        $user_order_feedbook = ['code' => 200, 'mess' => '订单已生成'];
                    } else {
                        $user_order_feedbook = ['code' => 500, 'mess' => '订单已生成失败'];
                    }
                } else {  //如果无数据，表示商品在拍卖期间，无用户出价拍卖
                    //执行下架
                    $goods_data = [
                        'g_status' => 4
                    ];
                    $bidders_data = [
                        'b_status' => 0
                    ];
                    $goods_result = Db::name('goods')->where('g_id', $data['g_id'])->update($goods_data);
                    $bidders_result = Db::name('bidders')->where('g_id', $data['g_id'])->update($bidders_data);
                    if ($goods_result && $bidders_result) {
                        $user_order_feedbook = ['code' => 500, 'mess' => '该商品没有用户出价，商品自动下架'];
                    }
                }
            } else {
                $user_order_feedbook = ['code' => 500, 'mess' => '拍卖未结束'];
            }
        } else {
            $user_order_feedbook = ['code' => 500, 'mess' => '未请求参数，或少参数'];
        }
        echo json_encode($user_order_feedbook, JSON_UNESCAPED_UNICODE);
    }

    //2.生成之后，通知栏通知用户消息
    public function notice()
    {
        //接收前台传递的 token 值，根据这个值进行查询
        $u_token = input('u_token');
        if (!empty($u_token)) {
            $u_id = Db::name('user')->where('u_token', $u_token)->value('u_id');
            //查询当前用户，未操作的，有几条订单
            $order_count = Db::name('order')->where('u_id', $u_id)->where('o_status', 0)->count();

            $notice_feedbook = ['code' => 200, 'count' => $order_count];
        } else {
            $notice_feedbook = ['code' => 200, 'mess' => '未请求参数'];
        }
        echo json_encode($notice_feedbook, JSON_UNESCAPED_UNICODE);
    }

    //3.买家的订单列表
    public function orderLists()
    {
        //接收前台传递的 token 值，根据这个值进行查询
        $u_token = input('u_token');
        $u_id = Db::name('user')->where('u_token', $u_token)->value('u_id');

        //总数
        $totalCount = Db::name('order')->alias('o')
            ->join('goods g', 'o.g_id=g.g_id')
            ->join('user u', 'o.u_id=u.u_id')
            ->join('harvest h', 'o.h_id=h.h_id')
            ->where('o.u_id', $u_id)
            ->count('o_id');

        $order_list = Db::name('order')->alias('o')
            ->join('goods g', 'o.g_id=g.g_id')
            ->join('user u', 'o.u_id=u.u_id')
            ->join('harvest h', 'o.h_id=h.h_id')
            ->where('o.u_id', $u_id)
            ->order('o_id desc')
            ->select();

        //判断 结果集 是否有数据
        if (!empty($order_list)) {
            $paginate_feedbook = ['code' => 200, 'mess' => '处理数据成功', "count" => $totalCount, 'data' => $order_list];
        } else {
            $paginate_feedbook = ['code' => 500, 'mess' => '处理数据失败', "count" => '无数据', 'data' => '无数据'];
        }
        echo json_encode($paginate_feedbook, JSON_UNESCAPED_UNICODE);
    }

    //4.点击确定订单时，未选择地址，提示，选择完毕后，点击确定，进行扣款操作
    public function confirm()
    {
        //接收订单ID 和 用户令牌
        $data = Request::instance()->param();
        if (!empty($data['o_id']) && !empty($data['u_token'])) {
            //查询当前订单表的信息
            $order_data = Db::name('order')->where('o_id', $data['o_id'])->find();
            //查询当前用户，用户ID
            $u_id = Db::name('user')->where('u_id', $data['u_token'])->value('u_id');
            //判断：未选择地址，提示
            if (empty($order_data['h_id'])) {
                echo json_encode(['code' => 500, 'mess' => '请选择收货地址'], JSON_UNESCAPED_UNICODE);
                die();
            } else {
                //1.进行扣款操作 （平台扣款，或用户付款）

                //2.扣款成功后，更改 已售 状态 和 操作
                //2.2.记录当前用户已购买的商品
                $already_buy_result = Db::name('already_buy')->insert(['u_id' => $u_id, 'g_id' => $order_data['g_id']]);
                //2.3.记录卖家已售的商品
                $goods_u_id = Db::name('goods')->where('g_id', $order_data['g_id'])->value('u_id');
                $already_sales_result = Db::name('already_sales')->insert(['u_id' => $goods_u_id, 'g_id' => $order_data['g_id']]);
                if ($already_buy_result && $already_sales_result) {
                    //更改已售状态值
                    $goods_result = Db::name('goods')->where('g_id', $order_data['g_id'])->update(['g_status' => 1]);
                    $bidders_result = Db::name('bidders')->where('g_id', $order_data['g_id'])->update(['b_status' => 2]);
                    $order_result = Db::name('order')->where('g_id', $order_data['g_id'])->update(['o_status' => 1]);
                    if ($goods_result && $bidders_result && $order_result) {
                        $confirm_feedbook = ['code' => 200, 'mess' => '恭喜您，商品已成功拍卖'];
                    } else {
                        $confirm_feedbook = ['code' => 500, 'mess' => '商品拍卖失败'];
                    }
                }
            }
        } else {
            $confirm_feedbook = ['code' => 500, 'mess' => '未请求参数'];
        }
        echo json_encode($confirm_feedbook, JSON_UNESCAPED_UNICODE);
    }


    //5.当用户选中地址后，点击确定，在订单表，执行一条添加地址
    public function chooseAddress()
    {
        //用户选择地址后，点击确定，这里接收前台传递的 订单 ID 和 地址 ID
        $data = Request::instance()->param();
        $user_address = Db::name('order')->where('o_id', $data['o_id'])->update($data);
        if ($user_address) {
            echo json_encode(['code' => 200, 'mess' => '选择收货地址成功'], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(['code' => 500, 'mess' => '选择收货地址失败'], JSON_UNESCAPED_UNICODE);
        }
    }


    //付款
    public function pagePay()
    {
        $o_id = input('o_id');
        $order_list = Db::name('order')->alias('o')
            ->join('goods g', 'o.g_id=g.g_id')
            ->join('user u', 'o.u_id=u.u_id')
            ->where('o_id', $o_id)
            ->find();
        $params = [
            'out_trade_no' => 222222200,
            'total_amount' => $order_list['o_payment'],
            'subject' => $order_list['g_name']
        ];
        Pagepay::pay($params);
    }

    //同步
    public function return_url()
    {
        $data = Request::instance()->get();
        if (is_array($data) && count($data) > 0) {
            //验签支付宝返回的信息
            $result = \alipay\Notify::check($data);
//            $alipaySevice = new A;
//            $result = \AlipayTradeService::check($data);
//            $result = $alipaySevice->check($data);
            if ($result) {
                //商户订单号
                $out_trade_no = htmlspecialchars($data['out_trade_no']);

                //支付宝交易号
                $trade_no = htmlspecialchars($data['trade_no']);

                //接口名称
                $method = htmlspecialchars($data['method']);

                //收款支付宝账号对应的支付宝唯一用户号，以2088开头的纯16位数字
                $seller_id = htmlspecialchars($data['seller_id']);

                $order_data = Db::name('order')->where('o_trade_no', $out_trade_no)->find();
                if (empty($order_data)) {
                    echo json_encode('订单号有误', JSON_UNESCAPED_UNICODE);
                }

                $update_order_data = [
                    'o_trade_no' => $out_trade_no,
                    'o_status' => 1
                ];
                $order_result = Db::name('order')->where('o_trade_no', $out_trade_no)->update($update_order_data);
                if ($order_result) {
                    header("Location: https://www.cnblogs.com/rask");
                }
            }
        } else {
            echo json_encode('验证失败', JSON_UNESCAPED_UNICODE);
        }
    }

    //异步
    public function notify_url()
    {
        $data = Request::instance()->post();
        var_dump($data);
    }
}
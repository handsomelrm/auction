<?php
/**
 * Created by PhpStorm.
 * User: liu
 * Date: 2018/10/25
 * Time: 23:17
 */

namespace app\index\controller;


//商品详情控制器
use think\Controller;
use think\Db;
use think\Request;


class Details extends Controller
{
    //商品详情信息渲染
    public function lists()
    {
        //接收传递，用户点击该商品的商品id
        $g_id = input('g_id');
        if (!empty($g_id)) {
            //1.商品详情信息渲染
            $goods_data = Db::name('goods')->alias('g')->join('bidders b', 'b.g_id=g.g_id')
                ->where('g.g_id', $g_id)
                ->find();
            $goods_data['g_img'] = 'uploads/' . $goods_data['g_img'];
            //无人拍卖时，以拍卖表中的最高价，进行前台渲染当前价，后续有人出价，则动态显示当前价
            //这里判断：无人出价时，拍卖表中的最高价为0.00，则把当前商品拍卖价格，赋值给卖表中的最高价
            if ($goods_data['b_user_highpay'] == 0.00 || empty($goods_data['b_user_highpay'])) {
                $goods_data['b_user_highpay'] = $goods_data['g_price'];
            }

            //2.渲染当前商品所有出价信息数据
            $user_pay_data = Db::name('user_pay')->where('g_id', $g_id)->where('u_id != 0 or u_id != null')
                ->order('up_pay desc')
                ->select();

            //1.3.判断：用户出价时，该商品拍卖是否已结束
            if (time() < strtotime($goods_data['g_auction_time'])) {
                $paginate_feedbook = ['code' => 200, 'mess' => '处理数据成功', 'data' => $goods_data, 'pay_data' => $user_pay_data];
            } else {
                $paginate_feedbook = ['code' => 200, 'mess' => '该商品已结束拍卖', 'data' => $goods_data, 'pay_data' => $user_pay_data];
            }
        } else {
            $paginate_feedbook = ['code' => 500, 'mess' => '处理数据失败', 'data' => '未请求参数，数据接口异常'];
        }
        echo json_encode($paginate_feedbook, JSON_UNESCAPED_UNICODE);
    }

    //记录不同用户出价
    public function userpay()
    {
        if (Request::instance()->isPost()) {
            //1.接收用户对该商品输入的价格
            //1.1.接收哪个商品，接收对商品的出价
            $data = Request::instance()->param();


            //1.2.接收哪个用户。接收前台传递的 token 值，根据这个值进行查询
            $u_token = $data['u_token'];
            $u_id = Db::name('user')->where('u_token', $u_token)->value('u_id');

            //1.3.判断：用户出价时，该商品拍卖是否已结束
            $g_auction_time = Db::name('goods')->where('g_id', $data['g_id'])->value('g_auction_time');
            if (time() > strtotime($g_auction_time)) {
                echo json_encode(['code' => 500, 'mess' => '该商品已结束拍卖'], JSON_UNESCAPED_UNICODE);
                die();
            }

            //2.判断用户输入的价格要比商品最低价高
            //2.1.先查出该商品的最低价
            $price = Db::name('goods')->where('g_id', $data['g_id'])->field('g_price,g_low_price')->find();
            //2.2.再查出上一次的出价金额
            $last_price = Db::name('user_pay')->where('g_id', $data['g_id'])->order('up_id desc')->field('up_pay')->find();
            //2.3.再判断
            if ($data['up_pay'] < $last_price['up_pay'] + $price['g_low_price']) {  //是否满足最低加价
                $user_pay_feedbook = ['code' => 500, 'mess' => '追加价不能低于商品最低追加价'];
            } elseif ($data['up_pay'] == $last_price['up_pay']) {   //是否满足高于上次出价
                $user_pay_feedbook = ['code' => 500, 'mess' => '追加价不能等于当前价'];
            } else {
                //2.4.进行不同用户的出价，记录数据
                $data['u_id'] = $u_id;
                $data['up_pay_time'] = date('Y-m-d H:i:s', time());
                unset($data['u_token']);
                $user_pay_result = Db::name('user_pay')->insert($data);     //$data里本身包含出价金额，商品ID
                if ($user_pay_result) {  //出价成功
                    //2.5.记录当前商品，所有用户对该商品出价的次数
                    $user_pay_count = Db::name('user_pay')->where('g_id', $data['g_id'])->where('u_id != 0 or u_id != null')->count('up_pay');
                    //3.拍卖表记录当前最高出价，和用户
                    //3.1.查询出价表，最高的出价和用户
                    $high = Db::name('user_pay')->where('g_id', $data['g_id'])->order('up_pay desc')->field('u_id,up_pay')->find();
                    if (isset($high)) {
                        //3.2.记录最高的出价和用户（赋值），和当前商品总共出价的次数
                        $bidders_high = [
                            'u_id' => $high['u_id'],
                            'b_user_highpay' => $high['up_pay'],
                            'b_count_pay' => $user_pay_count
                        ];
                        //3.3.记录
                        $bidders_result = Db::name('bidders')->where('g_id', $data['g_id'])->update($bidders_high);
                        if ($bidders_result) {
                            $user_pay_feedbook = ['code' => 200, 'mess' => '出价成功'];
                        }
                    }
                } else {
                    $user_pay_feedbook = ['code' => 500, 'mess' => '出价失败'];
                }
            }
        } else {
            $user_pay_feedbook = ['code' => 500, 'mess' => '未请求参数'];
        }
        echo json_encode($user_pay_feedbook, JSON_UNESCAPED_UNICODE);
    }


}
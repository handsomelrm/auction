<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/10/22/022
 * Time: 16:49
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Request;
use think\Session;

//卖家商品管理控制器
class Goods extends Controller
{
    //添加商品基本信息
    public function add()
    {
        if (Request::instance()->isPost()) {
            $data = Request::instance()->post();
            $add_goods_feedbook = ['code' => 200, 'mess' => '添加商品成功'];
            //接收前台传递的 token 值，根据这个值进行查询
            $u_token = $data['u_token'];
            $u_id = Db::name('user')->where('u_token',$u_token)->value('u_id');

            //判断文件是否上传
            if (empty($_FILES['g_img']['tmp_name'])) {
                echo json_encode(['code' => 500, 'mess' => '请选择上传的图片'], JSON_UNESCAPED_UNICODE);
                die();
            } else {
                //获取表单上传文件 例如上传了001.jpg
                $file = request()->file('g_img');

                //如果 $file=true，移动到框架应用根目录/public/uploads/ 目录下
                if ($file) {
                    //移动文件，返回文件信息
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                    //如果 $info=true，表示上传成功（移动成功）
                    if ($info) {
                        // 成功上传后 获取上传信息
                        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                        $filePath = $info->getSaveName();
                        $data['g_img'] = str_replace("\\", "/", $filePath);
                    }
                }
            }
            //时间戳
            $data['g_create_time'] = date('Y-m-d H:i:s', time());
            $data['u_id'] = $u_id;
            //添加商品前，销毁用户令牌，商品表无此字段
            unset($data['u_token']);
            $result = Db::name('goods')->insert($data);
            if (!$result) {
                $add_goods_feedbook = ['code' => 500, 'mess' => '添加商品失败'];
            }
            echo json_encode($add_goods_feedbook, JSON_UNESCAPED_UNICODE);
        } else {
            //加载分类信息
            $goods_type_data = Db::name('goods_type')->select();
            //1.判断 结果集 是否有数据
            if (isset($goods_type_data)) {
                $goods_type_feedbook = ['code' => 200, 'mess' => '处理数据成功', 'goods_type_data' => $goods_type_data];
            } else {
                $goods_type_feedbook = ['code' => 500, 'mess' => '处理数据失败', 'goods_type_data' => '暂无数据'];
            }
            echo json_encode($goods_type_feedbook, JSON_UNESCAPED_UNICODE);
        }
    }

    //商品基本信息列表
    public function lists()
    {
        //接收前台传递的 token 值，根据这个值进行查询
        $u_token = input('u_token');
        $u_id = Db::name('user')->where('u_token',$u_token)->value('u_id');

        //总数
        $totalCount = Db::name('goods')->alias('g')
            ->join('goods_type gt', 'gt.gt_id=g.gt_id')
            ->where('g.u_id', $u_id)
            ->count('g.g_id');

        $goods_list = Db::name('goods')->alias('g')
            ->join('goods_type gt', 'gt.gt_id=g.gt_id')
            ->where('g.u_id', $u_id)
            ->order('g.g_id desc')->select();

        for ($i = 0; $i < count($goods_list); $i++) {
            $goods_list[$i]['g_img'] = 'uploads/' . $goods_list[$i]['g_img'];
        }
        //判断 结果集 是否有数据
        if (!empty($goods_list)) {
            $paginate_feedbook = ['code' => 200, 'mess' => '处理数据成功', "count" => $totalCount, 'data' => $goods_list];
        } else {
            $paginate_feedbook = ['code' => 500, 'mess' => '处理数据失败', "count" => '无数据', 'data' => '无数据'];
        }
        echo json_encode($paginate_feedbook, JSON_UNESCAPED_UNICODE);
    }

    //修改商品
    public function edit()
    {
        if (Request::instance()->isPost()) {
            $data = Request::instance()->post();
            $edit_goods_feedbook = ['code' => 200, 'mess' => '修改商品成功'];
            //当用户未选择图片时，除了图片，只做其他数据操作
            if (!empty($_FILES['g_img']['tmp_name'])) {  //没有上传文件时。不执行
                //1.先找到数据库的旧图片
                $res = Db::name('goods')->where('g_id', $data['g_id'])->find();
                //2.拼接地址
                $fp = 'uploads/' . $res['g_img'];
                //3.删除文件，如果没有上传照片，删除时会报错，加@抑制一下
                @unlink($fp);
                //4.获取表单上传文件
                $file = request()->file('g_img');
                //5.如果 $file=true，移动到框架应用根目录/public/uploads/ 目录下，返回文件信息
                if ($file) {
                    $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
                    //如果 $info=true，表示上传成功（移动成功）
                    if ($info) {
                        // 成功上传后 获取上传信息
                        // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                        $filePath = $info->getSaveName();
                        $data['g_img'] = str_replace("\\", "/", $filePath);
                    }
                }
            }
            //6.持久化数据
            $data['g_update_time'] = date('Y-m-d H:i:s', time());
            $result = Db::name('goods')->where('g_id', $data['g_id'])->update($data);
            if (!$result) {
                $edit_goods_feedbook = ['code' => 500, 'mess' => '修改商品失败'];
            }
            echo json_encode($edit_goods_feedbook, JSON_UNESCAPED_UNICODE);
        } else {
            //加载分类信息
            $goods_type_data = Db::name('goods_type')->select();
            //1.判断 结果集 是否有数据
            if (isset($goods_type_data)) {
                $goods_type_feedbook = ['code' => 200, 'mess' => '处理数据成功', 'goods_type_data' => $goods_type_data];
            } else {
                $goods_type_feedbook = ['code' => 500, 'mess' => '处理数据失败', 'goods_type_data' => '暂无数据'];
            }
            echo json_encode($goods_type_feedbook, JSON_UNESCAPED_UNICODE);
        }
    }

    //删除商品
    public function del()
    {
        $del_goods_feedbook = ['code' => 200, 'mess' => '删除商品成功'];
        $g_id = input('g_id');
        //1.判断 get 传递的值是否存在
        if (!empty($g_id)) {
            //2.删除旧图片，上传新图片。先找到数据库的旧图片
            $res = Db::name('goods')->where('g_id', $g_id)->find();
            //3.拼接地址
            $fp = 'uploads/' . $res['g_img'];
            //4.删除文件。如果没有上传照片，删除时会报错，加@抑制一下
            @unlink($fp);
            //5.删除数据
            $result = Db::name('goods')->where('g_id', $g_id)->delete();
            if (!$result) {
                $del_goods_feedbook = ['code' => 500, 'mess' => '删除商品失败'];
            }
        } else {
            $del_goods_feedbook = ['code' => 500, 'mess' => '未请求参数，数据接口异常'];
        }
        echo json_encode($del_goods_feedbook, JSON_UNESCAPED_UNICODE);
    }

    //上架商品
    public function up()
    {
        if (Request::instance()->isPost()) {
            //1.接收前台用户，点击上架按钮传递的商品ID和POST过来的表单数据
            $data = Request::instance()->param();
            $up_result = Db::name('goods')->where('g_id', $data['g_id'])->update($data);
            if ($up_result) {
                //2.判断：商品的状态 3 是否为拍卖商品
                if ($data['g_status'] == 3) {   //上架，并返回结束时间
                    $bidders_data = [
                        'g_id' => (int)$data['g_id'],
                        'b_create_time' => date('Y-m-d H:i:s', time()), //上架时间
                        'b_status' => 1
                    ];
                    //3.执行上架
                    $bidders_result = Db::name('bidders')->insert($bidders_data);
                    if ($bidders_result) {
                        //4.判断：上架的同件商品，如有重复，全部删除，在添加，否则不操作且保留数据，并提示
                        $bidders_goods_count = Db::name('bidders')->where('g_id', $data['g_id'])->count('g_id');
                        if ($bidders_goods_count > 1) {
                            Db::name('bidders')->where('g_id', $data['g_id'])->delete();
                            Db::name('bidders')->insert($bidders_data);
                        }


                        //5.上架的时候，添加数据到出价表，表示：商品起步价就是用户追加的标准
                        //5.1.先查询当前商品的起步价，和当前商品ID
                        $goods_result = Db::name('goods')->where('g_id', $data['g_id'])->field('g_id,g_price')->find();
                        if (!empty($goods_result)) {
                            //6.添加出价数据
                            $user_pay_data = [
                                'g_id' => $goods_result['g_id'],
                                'up_pay' => $goods_result['g_price'],
                            ];
                            //6.执行添加
                            $user_pay_result = Db::name('user_pay')->insert($user_pay_data);
                            if ($user_pay_result) {
                                //7.判断：出价表的同件商品，如有重复，全部删除，在添加，否则不操作且保留数据，并提示
                                $user_pay_count = Db::name('user_pay')->where('g_id', $data['g_id'])->count('g_id');
                                if ($user_pay_count > 1) {
                                    Db::name('user_pay')->where('g_id', $data['g_id'])->delete();
                                    Db::name('user_pay')->insert($user_pay_data);
                                }
                                $up_goods_feedbook = ['code' => 200, 'mess' => '商品已上架竞拍'];
                            }
                        }
                    }
                }
            }
        } else {
            $up_goods_feedbook = ['code' => 500, 'mess' => '未请求参数'];
        }
        echo json_encode($up_goods_feedbook, JSON_UNESCAPED_UNICODE);
    }
}
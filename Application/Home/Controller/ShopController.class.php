<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

use OT\DataDictionary;
use api\controller\ApiShopController;

class ShopController extends HomeController {

    public function __construct() {
        parent::__construct();
        // session('my_shop_id',null);die;
        $shop_id = session('my_shop_id');
        if (empty($shop_id)) {
//            $url = U('Api/Shop/GetShop', '', true, true);
//            $url = 'http://xiaotian.hzxmnet.com' . U('Api/Shop/GetShop', '', true, false);
            $url = 'http://' . WEB_DOMAIN . U('Api/Shop/GetShop', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
                'time' => NOW_TIME
            );
            $url_data = CurlHttp($url, $param, 'POST');
            $url_data = (array) json_decode($url_data);
            // if ($url_data['Code'] != '0') {
            //     redirect(U('applyShop'));
            // }
            $info = (array) $url_data['data']['0'];
            session('my_shop_id', $info['id']);
        }
    }

    //店铺首页
    public function Index() {
        // echo "111";exit;
//        $url = U('Api/Shop/GetShop', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/Shop/GetShop', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
            'time' => NOW_TIME
        );
        $url_data = CurlHttp($url, $param, 'POST');
        $url_data = (array) json_decode($url_data);
        if ($url_data['Code'] != '0') {
            redirect(U('applyShop'));
        }
        $money = $url_data['money'];
        $info = (array) $url_data['data']['0'];
        if($info['is_lock'] == 1){
            $this->AlertMsg('当前店铺已被禁止访问', U('Index/index'));
        }
        if($info['is_delete'] == 1){
            $this->AlertMsg('当前店铺已被删除或不存在', U('Index/index'));
        }
        $this->assign('info', $info);
        $this->assign('money', $money);
        $this->display();
    }

    //申请店铺
    public function applyShop() {
        if (IS_POST) {
            $sfz_images = I('post.sfz_images', '');
            $xsz_images = I('post.xsz_images', '');
            $phone      =   I('post.phone',0,'intval');
//            $url = U('Api/Shop/applyShop_ok', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/Shop/applyShop_ok', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => '575fa342ab74583c514be1f4b06bc938',
                'time' => NOW_TIME,
                'sfz_images' => json_encode($sfz_images),
                'xsz_images' => json_encode($xsz_images),
                'phone'     =>  $phone
            );
            $url_data = CurlHttp($url, $param, 'POST');
            $url_data = (array) json_decode($url_data);
            if ($url_data['Code'] == 10001) {
                $this->AlertMsg('您的申请正在审核中，请耐心等待', U('applyShop'));
            } else {
                redirect(U('applyShopWaiting'));
            }
        } else {
            $reply = !empty($_GET['reply']) ? I('get.reply') : 2;
//            $url = U('Api/Shop/applyShop', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/Shop/applyShop', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
                'time' => NOW_TIME
            );
            $url_data = CurlHttp($url, $param, 'POST');
            $url_data = (array) json_decode($url_data);
            if ($url_data['Code'] == 10001) {
                redirect(U('Home/Index/index'));
            } elseif ($url_data['Code'] == 10002) {
                if ($reply == 2) {
                    redirect(U('applyShopWaiting'));
                }
            }
            $AuthInfo = R('Home/Weixin/wxJs');
            $this->assign('signPackage', $AuthInfo);
            $this->display();
        }
    }

    public function order_del() {
//                $url = U('Api/Order/order_del', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/Order/order_del', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => '6afb008e50039bb66588b9249f6376b8',
            'time' => NOW_TIME,
            'order_id' => I('post.id'),
            'type' => 2,
        );
        // var_dump($param);die;
        // die($param);
        $url_data = CurlHttp($url, $param, 'POST');
        die($url_data);
    }

    public function applyShopWaiting() {
        $where = array();
        $where['userid'] = $this->userid;
        $agent_info = M('agent')->where($where)->find();
        if (!empty($agent_info)) {
            redirect(U('Index'));
            exit();
        }
        $where = array();
        $where['userid'] = $this->userid;
        $agent_applying_info = M('agent_applying')->where($where)->order("id DESC")->find();
        if (!empty($agent_applying_info)) {
            $waiting_msg = '';
            switch ($agent_applying_info['status']) {
                case 1:
                    $waiting_msg .= '审核通过';
                    $waiting_msg .= '<br>';
                    $waiting_msg .= '<a href="' . U('index') . '">[进入店铺]</a>';
                    break;
                case 2:
                    $waiting_msg .= '审核不通过';
                    $waiting_msg .= '<br>';
                    $waiting_msg .= '<a href="' . U('applyShop', array('reply' => '1')) . '">[重新申请]</a>';
                    break;
                case 3:
                    $waiting_msg = '等待系统审核';
                    $waiting_msg .= '<br>';
                    $waiting_msg .= '<a href="' . U('Index/index') . '">[去逛逛]</a>';
                    break;

                default:
                    break;
            }
            $this->assign('waiting_msg', $waiting_msg);
            $this->display('applyShop_waiting');
            exit();
        }
    }

    public function AlertMsg($Msg, $Url) {
        echo '<script>alert("' . $Msg . '");location.href="' . $Url . '";</script>';
        exit();
    }

    //微信上传图片
    public function getImage() {
//        $url = U('Api/Shop/getImage', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/Shop/getImage', '', true, false);
        $param = array(
            'media_id' => I('post.media_id'),
            'hash' => 'ff8e85efc143d98c4fa3eb2fd5548421',
            'time' => NOW_TIME,
            'type' => I('post.type')
        );
        $url_data = CurlHttp($url, $param, 'POST');
        die($url_data);
    }

    //我的店铺
    public function Myshop() {
        $page = I('post.page');
        $shop_id = I('get.shop_id');
        if ($page) {
//            $url = U('Api/Shop/Get_goods', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/Shop/Get_goods', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => '778382f31fe41430bb59a063d85d8503',
                'time' => NOW_TIME,
                'page' => $page,
                'shop_id' => I('post.shop_id'),
            );
            $url_data = CurlHttp($url, $param, 'POST');
            die($url_data);
        } else {
//            $url = U('Api/Shop/Myshop', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/Shop/Myshop', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => '0411695f45e3dd0ddfb472e07177b748',
                'time' => NOW_TIME,
                'shop_id' => $shop_id,
            );
            $url_data = CurlHttp($url, $param, 'POST');
            $url_data = (array) json_decode($url_data,true);
            if($url_data['Code'] == 2000){
                $this->AlertMsg($url_data['Msg'], U('Index/index'));
            }
            $info = (array) $url_data['info'];
            $count = $url_data['count'];
            foreach ($url_data['goods_data'] as $key => $value) {
                $goods[$key] = (array) $value;
            }
            foreach ($url_data['goods_new'] as $key => $value) {
                $goods_new[$key] = (array) $value;
            }
            foreach ($url_data['all_goods'] as $key => $value) {
                $all_goods[$key] = (array) $value;
            }

//            dump($url_data['shop_config']);die;
            $this->assign('collect', $url_data['collect']); //用于判断是否收藏
            $this->assign('info', $info);
            $this->assign('shop_id', $shop_id);
            $this->assign('goods', $goods);
            $this->assign('goods_new', $goods_new);
            $this->assign('all_goods', $all_goods);
            $this->assign('num', $count);
            $this->assign('is_shop', $url_data['is_shop']);
            $this->assign('shop_config', $url_data['shop_config']);
            
            $this->assign('seller_user_id', $info['userid']);
            $this->assign('buyer_user_id', $this->userid);
            
            $this->display();
        }
    }

    public function Collect() {
        if ($_POST['status']) {
//            $url = U('Api/Shop/Collect', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/Shop/Collect', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c2fee12291662cf0a2b97699e9066fdc',
                'time' => NOW_TIME,
                'collect' => I('post.status'),
                'shop_id' => I('post.shop_id'),
                'type' => I('post.type'),
            );
            $url_data = CurlHttp($url, $param, 'POST');
            die($url_data);
        }
    }

    //店铺介绍
    public function Shopintro() {
        $this->display();
    }

    //新品上架
    public function Goodadd() {
        $this->display();
    }

    //全部产品
    public function Prolist() {
        $this->display();
    }

    //审核等待
    public function Waits() {
        $this->display();
    }

    //产品分类
    public function Kand() {
        $this->display();
    }

    //购物车
    public function Cart() {
        $this->display();
    }

    //结算
    public function Order() {
        $this->display();
    }

    //累计收入
    public function sum() {
//        $url = U('Api/shop/sum', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/shop/sum', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
            'time' => NOW_TIME,
        );
        $url_data = CurlHttp($url, $param, 'POST');
        $url_data = (array) json_decode($url_data);
        $money = (array) $url_data['info'];
        $this->assign('info', $money);
        $this->display();
    }

    //账单管理
    public function billlist() {
//        $url = U('Api/shop/billlist', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/shop/billlist', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
            'time' => NOW_TIME,
        );
        $url_data = CurlHttp($url, $param, 'POST');
        $url_data = (array) json_decode($url_data);
        if ($url_data['info']) {
            $info = $url_data['info'];
            foreach ($info as $key => $value) {
                $info[$key] = (array) $value;
            }
        }
        $this->assign('info', $info);
        $this->display();
    }

    //提现
    public function crash() {
        if (I('post.card_id') && I('post.money')) {
//            $url = U('Api/shop/crash', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/shop/crash', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => '416b6ac70b92bdbec1a71463cd50268a',
                'time' => NOW_TIME,
                'card_id' => I('post.card_id'),
                'money' => I('post.money')
            );
            $url_data = CurlHttp($url, $param, 'POST');
            die($url_data);
        } else {
            $info = $this->getbank();
            $crash = I('get.stil');
            $this->assign('info', $info['info']);
            $this->assign('data', $info['data']);
            $this->assign('crash', $crash);
            $this->display();
        }
    }

    //商品管理
    public function Manage() {
        if (IS_POST) {
//            $url = U('Api/shop/GoodsCheck', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/shop/GoodsCheck', '', true, false);
            $data = I('post.data');
            $param = array('goods_id' => $data['id'], 'is_sale' => $data['is_sale'], 'hash' => '8b102b584cf292db606613a5b46ef418', 'time' => NOW_TIME);
            $url_data = CurlHttp($url, $param, 'POST');
            echo $url_data;
        } else {
            $this->get_data();
            $this->display();
        }
    }

    //客户订单
    public function orderlist() {
        if (IS_POST) {
//                 $url = U('Api/Order/sellerOrderList','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/Order/sellerOrderList', '', true, false);
            $param = array(
                'hash' => '00140f0d379c66ff83717238001f88cd',
                'time' => NOW_TIME,
                'uid' => session('my_shop_id'),
                'status_type' => I('post.status_type'),
                'page' => I('post.page'),
            );
            $url_data = CurlHttp($url, $param, 'POST');
            die($url_data);
        } else {
            $status_type = I('get.status_type', '');
            //    $url = U('Api/Order/buyerOrderList','',true,true);
            //    'http://xiaotian.hzxmnet.com' . 
//                $url = U('Api/Order/sellerOrderList', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/Order/sellerOrderList', '', true, false);
            //                dump($url);die;
            $param = array(
                'hash' => '00140f0d379c66ff83717238001f88cd',
                'time' => NOW_TIME,
                'uid' => session('my_shop_id'),
                'status_type' => $status_type,
                'page' => 0,
            );
            $url_data = CurlHttp($url, $param, 'POST');

            $result_data = (array) json_decode($url_data, true);
            $data = (array) $result_data['Data'];
            $order_list = $data['list'];

            // dump($result_data);die;
            $this->assign('title', '我的订单');
            $this->assign('totalpage', $data['totalpage']);
            $this->assign('order_list', $order_list);
            $this->assign('status_type', $status_type);
            $this->display();
        }
    }

    //订单详情
    public function orderdetail() {
        $order_id = I('get.order_id', 0, 'intval');
//                $url = U('Api/Order/buyerOrderList','',true,true);
//            $url = 'http://xiaotian.hzxmnet.com'.U('Api/Order/sellerOrderDetail','',true,false);
        $url = 'http://' . WEB_DOMAIN . U('Api/Order/sellerOrderDetail', '', true, false);
//                dump($url);die;
        $param = array(
            'hash' => '1cd64870ed7fa650ebd454e5753ac178',
            'time' => NOW_TIME,
            'uid' => session('my_shop_id'),
            'order_id' => $order_id,
        );
        dblog(array('Shop/orderdetail', '$param' => $param));
        $url_data = CurlHttp($url, $param, 'POST');
        $result_data = json_decode($url_data, true);
        $data = $result_data['Data'];
        $order_info = $data['order_info'];
        $this->assign('title', '订单详情');
        $this->assign('order_info', $order_info);
        dblog(array('Shop/orderdetail', '$order_info' => $order_info));
        if (in_array($order_info['order_status_code'], array('readyship'))) {

            //获取物流列表
            //                $url = U('Api/Order/getExpressList','',true,true);
//                $url = 'http://xiaotian.hzxmnet.com'.U('Api/Order/getExpressList','',true,false);
            $url = 'http://' . WEB_DOMAIN . U('Api/Order/getExpressList', '', true, false);
            //                dump($url);die;
            $param = array(
                'hash' => '70ffcb2a3569065c4420776b8a81809b',
                'time' => NOW_TIME,
            );
            $url_data = CurlHttp($url, $param, 'POST');
            $result_data = json_decode($url_data, true);
            $data = $result_data['Data'];
            $expressList = $data['list'];
//                dump($expressList);die;
            $this->assign('express_list', $expressList);
            $this->display('confirmShip');
            exit();
        }

        $this->display();
    }

    /*
     * 【确认订单】
     * 丁马利
     */

    public function confirmOrder() {
        if (IS_POST) {
            $order_id = I('post.order_id', 0, 'intval');
            $uid = session('my_shop_id');

            //                $url = U('Api/Order/sellerConfirmOrder','',true,true);
//            $url = 'http://xiaotian.hzxmnet.com' . U('Api/Order/sellerConfirmOrder', '', true, false);
            $url = 'http://' . WEB_DOMAIN . U('Api/Order/sellerConfirmOrder', '', true, false);
            $param = array(
                'hash' => '1cd64870ed7fa650ebd454e5753ac178',
                'time' => NOW_TIME,
                'uid' => session('my_shop_id'),
                'order_id' => $order_id
            );
            // die(json_encode($param));
            $url_data = CurlHttp($url, $param, 'POST');
            die($url_data);
        }
    }

    /*
     * 【订单发货】
     */

    public function confirmShip() {
        if (IS_POST) {
            $order_id = I('post.order_id', 0, 'intval');
            $express_id = I('post.express_id', 1, 'intval');
            $express_no = I('post.express_no');
            $uid = session('my_shop_id');

            //                $url = U('Api/Order/sellerConfirmOrder','',true,true);
//            $url = 'http://xiaotian.hzxmnet.com' . U('Api/Order/sellerConfirmShip', '', true, false);
            $url = 'http://' . WEB_DOMAIN . U('Api/Order/sellerConfirmShip', '', true, false);
            $param = array(
                'hash' => '8704c8c71a9e7447a6a305b7b27ee606',
                'time' => NOW_TIME,
                'uid' => session('my_shop_id'),
                'order_id' => $order_id,
                'express_id' => $express_id,
                'express_no' => $express_no,
            );
            $url_data = CurlHttp($url, $param, 'POST');
            die($url_data);
        } else {
            $order_id = I('get.order_id', 0, 'intval');
//                $url = U('Api/Order/buyerOrderList','',true,true);
//            $url = 'http://xiaotian.hzxmnet.com'.U('Api/Order/sellerOrderDetail','',true,false);
            $url = 'http://' . WEB_DOMAIN . U('Api/Order/sellerOrderDetail', '', true, false);
//                dump($url);die;
            $param = array(
                'hash' => '1cd64870ed7fa650ebd454e5753ac178',
                'time' => NOW_TIME,
                'uid' => session('my_shop_id'),
                'order_id' => $order_id,
            );
            $url_data = CurlHttp($url, $param, 'POST');
            $result_data = json_decode($url_data, true);
            $data = $result_data['Data'];
            $order_info = $data['order_info'];

            //获取物流列表
//                $url = U('Api/Order/getExpressList','',true,true);
//            $url = 'http://xiaotian.hzxmnet.com'.U('Api/Order/getExpressList','',true,false);
            $url = 'http://' . WEB_DOMAIN . U('Api/Order/getExpressList', '', true, false);
//                dump($url);die;
            $param = array(
                'hash' => '70ffcb2a3569065c4420776b8a81809b',
                'time' => NOW_TIME,
            );
            $url_data = CurlHttp($url, $param, 'POST');
            $result_data = json_decode($url_data, true);
            $data = $result_data['Data'];
            $expressList = $data['list'];
//            dump($expressList);die;
            $this->assign('title', '订单详情');
            $this->assign('order_info', $order_info);
            $this->assign('express_list', $expressList);
            $this->display();
        }
    }

    //店铺管理
    public function Shopinfo() {
//        $url = U('Api/Shop/GetShop', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/Shop/GetShop', '', true, false);
        $param = array('user_id' => $this->userid, 'hash' => 'c94ffcec8db680a0ac1a466e3e218046', 'time' => NOW_TIME);
        $url_data = CurlHttp($url, $param, 'POST');
        $url_data = (array) json_decode($url_data);
        $info = (array) $url_data['data']['0'];
        $this->assign('info', $info);
        $this->display();
    }

    //客户管理
    public function Customer() {
//        $url = U('Api/Shop/customerList', '', true, true);
//        $url = 'http://xiaotian.hzxmnet.com'.U('Api/Shop/customerList','',true,false);
        $url = 'http://' . WEB_DOMAIN . U('Api/Shop/customerList', '', true, false);
        $param = array('uid' => intval(session('my_shop_id')), 'hash' => '4b50512c9c732419a0d992ab9cd202bc', 'time' => NOW_TIME);
        $url_result = CurlHttp($url, $param, 'POST');
        $url_data = json_decode($url_result, true);
        $customer_list = $url_data['Data']['list'];
        $this->assign('customer_list', $customer_list);
        $this->display();
    }

    //销售统计
    public function Selldata() {
//            $url = U('Api/Shop/Selldata', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/Shop/Selldata', '', true, false);
        $param = array(
            'shop_id' => I("get.shop_id"),
            'hash' => 'b1ea90c3e43c4ff9580c3b4c711e7bf2',
            'time' => NOW_TIME);
        $url_data = CurlHttp($url, $param, 'POST');
        $url_data = (array) json_decode($url_data);
        $this->assign('date', $url_data['date']);
        $this->assign('count', $url_data['count']);
        $this->assign('all', json_encode($url_data['all']));
        $this->display();
    }

    //银行卡管理
    public function getbank() {
//        $url = U('Api/shop/Blanks', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/shop/Blanks', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
            'time' => NOW_TIME,
        );
        $url_data = CurlHttp($url, $param, 'POST');
        $url_data = (array) json_decode($url_data);
        $info = $url_data['info'];
        foreach ($info as $key => $value) {
            if (is_int($key / 2)) {
                $info_data[$key] = (array) $value;
            } else {
                $data[$key] = (array) $value;
            }
        }
        $getbank = array(
            'info' => $info_data,
            'data' => $data
        );

        return $getbank;
    }

    public function blanks() {
        $info = $this->getbank();
        $this->assign('info', $info['info']);
        $this->assign('data', $info['data']);
        $this->display();
    }

    //商品管理下的批量管理
    public function Allmanage() {
        if (I('post.search')) {
            $this->get_search();
        } else {
            $this->get_data();
        }

        $this->display();
    }

    //银行卡添加
    public function Blankadd() {
        if (IS_POST) {
//            $url = U('Api/shop/Blankadd', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/shop/Blankadd', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => '1233dd628bb71ce64c1177cfcadf12f1',
                'time' => NOW_TIME,
                'card' => I('post.card'),
                'card_type' => I('post.name'),
                'cardname' => I('post.user'),
                'id_card' => I('post.idcard'),
            );
            $url_data = CurlHttp($url, $param, 'POST');
            die($url_data);
        }
        $this->display();
    }

    public function blank_msg() {
        if ($_POST['type'] == 1) {
//            $url = U('Api/shop/Sendcode', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/shop/Sendcode', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
                'time' => NOW_TIME
            );
            $url_data = CurlHttp($url, $param, 'POST');
            die($url_data);
        } else {
            $id = I('get.id');
//            $url = U('Api/shop/blank_msg', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/shop/blank_msg', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
                'time' => NOW_TIME
            );
            $url_data = CurlHttp($url, $param, 'POST');
            $url_data = (array) json_decode($url_data);
            $this->assign('phone', $url_data['phone']);
            $this->assign('id', $id);
            $this->display();
        }
    }

    public function blank_sure() {
        if (I('post.data')) {
//            $url = U('Api/shop/blank_sure', '', true, true);
            $url = 'http://' . WEB_DOMAIN . U('Api/shop/blank_sure', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => '7f7139bdc1c03b790d5979c317901fbe',
                'time' => NOW_TIME,
                'checkcode' => I('post.data'),
                'mobile' => I('post.phone'),
                'id' => I('post.bank_id')
            );
            $url_data = CurlHttp($url, $param, 'POST');
            die($url_data);
        }
    }

    public function get_data() {
//        $url = U('Api/shop/Manage', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/shop/Manage', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => 'df51228db5c1d6d2b0fd94ed1e022fe3',
            'get_sale_status' => I('get.get_sale_status'),
            'get_order' => I('get.get_order'),
            'get_category' => I('get.get_category'),
            'time' => NOW_TIME
        );
        $url_data = CurlHttp($url, $param, 'POST');

        $url_data = (array) json_decode($url_data);
        foreach ($url_data['data'] as $key => $value) {
            $info[$key] = (array) $value;
        }
        $category['999'] = array('id' => '999', 'category_name' => '全部');
        foreach ($url_data['category'] as $key => $value) {
            $category[$key] = array('id' => $key, 'category_name' => $value);
        }

        //分类参数
        $get_category = empty($_GET['get_category']) ? 999 : I('get.get_category');
        //上架参数
        $get_sale_status = empty($_GET['get_sale_status']) ? 1 : I('get.get_sale_status');
        $sale_arr = array(
            '1' => '销售中',
            '2' => '未上架'
        );

        $order = array(
            '1' => '销量排行',
            '2' => '售价排行',
            '3' => '最新上架',
            '4' => '收藏最多',
        );
        $get_order = empty($_GET['get_order']) ? 1 : I('get.get_order');
        $this->assign('get_order', $get_order);
        $this->assign('order', $order[$get_order]);
        $this->assign('category', $category);
        $this->assign('get_category', $category[$get_category]['category_name']);

        $this->assign('get_categoryid', $get_category);
        $this->assign('get_sale_status', $get_sale_status);
        $this->assign('sale_status', $sale_arr[$get_sale_status]);
        $this->assign('info', $info);
    }

    public function get_search() {
//        $url = U('Api/shop/Search', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/shop/Search', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => 'b5a903e4942d26f38de7aed40341f76c',
            'get_sale_status' => I('get.get_sale_status'),
            'get_order' => I('get.get_order'),
            'get_category' => I('get.get_category'),
            'search' => I('post.search'),
            'time' => NOW_TIME
        );
        $url_data = CurlHttp($url, $param, 'POST');
        $url_data = (array) json_decode($url_data);
        foreach ($url_data['data'] as $key => $value) {
            $info[$key] = (array) $value;
        }
        $category['999'] = array('id' => '999', 'category_name' => '全部');
        foreach ($url_data['category'] as $key => $value) {
            $category[$key] = array('id' => $key, 'category_name' => $value);
        }

        //分类参数
        $get_category = empty($_GET['get_category']) ? 999 : I('get.get_category');
        //上架参数
        $get_sale_status = empty($_GET['get_sale_status']) ? 1 : I('get.get_sale_status');
        $sale_arr = array(
            '1' => '销售中',
            '2' => '未上架'
        );

        $order = array(
            '1' => '销量排行',
            '2' => '售价排行',
            '3' => '最新上架',
            '4' => '收藏最多',
        );
        $get_order = empty($_GET['get_order']) ? 1 : I('get.get_order');
        $this->assign('get_order', $get_order);
        $this->assign('order', $order[$get_order]);
        $this->assign('category', $category);
        $this->assign('get_category', $category[$get_category]['category_name']);

        $this->assign('get_categoryid', $get_category);
        $this->assign('get_sale_status', $get_sale_status);
        $this->assign('sale_status', $sale_arr[$get_sale_status]);
        $this->assign('info', $info);
    }

    public function data_del() {
        $delid = I('post.delId');
        $id = '';
        foreach ($delid as $key => $value) {
            $id = $value . ',' . $id;
        }
        $id = substr($id, 0, strlen($id) - 1);
//        $url = U('Api/Shop/data_del', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/Shop/data_del', '', true, false);
        $param = array('id' => $id, 'hash' => '39a851a7218472226ece36d1377f3cf0', 'time' => NOW_TIME);
        $url_data = CurlHttp($url, $param, 'POST');
        echo $url_data;
    }

    public function up_down() {
        $delid = I('post.delId');
        $status = I('post.status');
        $id = '';
        foreach ($delid as $key => $value) {
            $id = $value . ',' . $id;
        }
        $id = substr($id, 0, strlen($id) - 1);
//        $url = U('Api/Shop/up_down', '', true, true);
        $url = 'http://' . WEB_DOMAIN . U('Api/Shop/up_down', '', true, false);
        $param = array('id' => $id, 'hash' => '58f2716f222d5606612ba2ff9bc952ec', 'status' => $status, 'time' => NOW_TIME);
        $url_data = CurlHttp($url, $param, 'POST');
        echo $url_data;
    }

    //售后详情
    public function aftersalesdetail() {
        $return_id = I('get.return_id', 0, 'intval');
//                $url = U('Api/Order/aftersalesdetail','',true,true);
//                $url = 'http://xiaotian.hzxmnet.com'.U('Api/Order/sellerAftersalesdetail','',true,false);
        $url = 'http://' . WEB_DOMAIN . U('Api/Order/sellerAftersalesdetail', '', true, false);
        //                dump($url);die;
        $param = array(
            'hash' => '737f8d552c22e7831f8f1acd74a3bd23',
            'time' => NOW_TIME,
            'uid' => session('my_shop_id'),
            'return_id' => $return_id,
        );
        $url_data = CurlHttp($url, $param, 'POST');
        $result_data = json_decode($url_data, true);
        $data = $result_data['Data'];
        $order_return = $data['order_return'];
        $order_detail = $data['order_detail'];
        $order_info = $data['order_info'];
        $return_status = $data['return_status'];
//                dump($order_detail);die;  
        $this->assign('title', '售后详情');
        $this->assign('order_return', $order_return);
        $this->assign('return_status', $return_status);
        $this->assign('order_detail', $order_detail);
        $this->assign('order_info', $order_info);
        $this->display();
    }

}

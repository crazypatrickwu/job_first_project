<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Pcweb\Controller;

use OT\DataDictionary;

class UserController extends HomeController {

    //个人中心
    public function index() {
        if (IS_POST) {
//          $url = U('Api/User/getIndex','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/getIndex', '', true, false);
            // $url = 'http://xiaotian.hzxmnet.com'.U('Api/User/getIndex','',true,true);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
                'time' => NOW_TIME
            );
            $result = CurlHttp($url, $param, 'POST');
            // var_dump($result);die;
            $info = json_decode($result, true);
            // dump($info);die;
//            $target_info = $info['info'];
//            foreach ($target_info as $key => $value) {
//                $target[] = $value['name'];
//            }
            $target = json_decode($info['Data']['user_info']['lable_list']);
            $data = json_decode($info['Data']['user_info']['lable']);
            $target_data = $this->Compare($target, $data);
            $info['Data']['user_info']['lable'] = $target_data;

            $result = array();
            //个人资料
            $result['avatar'] = $info['Data']['user_info']['avatar'];
            $result['nickname'] = $info['Data']['user_info']['nickname'];
            $result['lable'] = $info['Data']['user_info']['lable'];
            //签到
            $result['sign'] = $info['Data']['user_sign'];
            //消息
            $result['count'] = $info['count'];
            //我的收藏
            $result['collect'] = $info['Data']['user_collection_count'];
            //购物车
            $result['cart_count'] = $info['Data']['user_cart_count'];
            //浏览记录
            $result['view_count'] = $info['Data']['user_browser_history_count'];
            //账户余额
            $result['balance'] = $info['Data']['user_info']['account_balance'];
            //我的积分
            $result['score'] = $info['Data']['user_info']['score'];
            //我的抵用券
            $result['voucher'] = $info['Data']['user_info']['voucher'];
            dblog(array('user/index','$result'=>$result));
            die(json_encode($result));
        } else {
            $this->display();
        }
    }

    public function sign() {
        if (IS_POST) {
            # code...
//          $url = U('Api/User/getSign','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/getSign', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
                'time' => NOW_TIME
            );
            $result = CurlHttp($url, $param, 'POST');
            die($result);
        }
    }

    //个人资料编辑
    public function person() {
        if (IS_POST) {
//            dump(I('post.'));die;
            $target = json_encode(I('post.target'));
//          $url = U('Api/User/Person_save','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/Person_save', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => '66c35e8e532f19dfdad13d7806172878',
                'time' => NOW_TIME,
                'label' => $target,
                'sex' => I('post.sex'),
                'nickname' => base64_encode(I('post.nickname')),
                'phone' => I('post.phone'),
                'school' => I('post.school'),
                'age' => I('post.age'),
                'birthday' => I('post.birthday'),
            );
            $result = CurlHttp($url, $param, 'POST');
            die($result);
        } else {
//          $url = U('Api/User/getPerson','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/getPerson', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
                'time' => NOW_TIME
            );
            $result = CurlHttp($url, $param, 'POST');
            $info = json_decode($result, true);
//            dump($info);die;
//            $target = $info['target'];
            $info = $info['Data'];
            $info_target = json_decode($info['user_person']['lable']);
            $target = json_decode($info['user_person']['lable_list']);
            $target_data = $this->Compare($target, $info_target);
//            dump(array('$target'=>$target,'$info_target'=>$info_target,'$target_data'=>$target_data));die;
            $this->assign('target', $target_data);
            $this->assign('info', $info);
            $this->display();
        }
    }
    
    /*
     * 用户标签
     */
    public function ajaxLabel(){
            $option     =   I('post.option','','trim');
            $label_name =   I('post.label_name','','trim');
            switch ($option) {
                case 'add': //新增
                    $user_labels    =   M('public_user')->where(array('id'=>$this->userid))->field('lable,lable_list')->find();
                    if(!empty($user_labels['lable_list'])){
                        $lable          =   json_decode($user_labels['lable']);
                        $lable_list     =   json_decode($user_labels['lable_list']);
                        if(in_array($label_name, $lable_list)){
                            die(json_encode(array('code'=>0,'msg'=>'您已添加过此标签')));
                        }
                        array_push($lable_list, $label_name);
                        $labelData      =   json_encode($lable_list);
                    }  else {
                        $labelData      = json_encode(array($label_name));
                    }
                        
                    $res    =   M('public_user')->where(array('id'=>$this->userid))->setField(array('lable_list'=>$labelData));
                    if($res){
                        die(json_encode(array('code'=>1,'msg'=>'添加成功')));
                    }
                    die(json_encode(array('code'=>0,'msg'=>'添加失败')));
                    break;
                case 'check':   //选中
                    
                    break;

                default:
                    break;
            }
    }

    //浏览记录
    public function looked() {
        if (IS_POST) {
            $page = I('post.page');
//         $url = U('Api/User/getLooked','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/getLooked', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c035a0fe11927bb4603aa1bbd87bad49',
                'time' => NOW_TIME,
                'page' => $page,
                'limit' => 10
            );
            $result = CurlHttp($url, $param, 'POST');
            $info = json_decode($result, true);
            die(json_encode(array('code' => 0, 'data' => $info['Data'])));
        } else {
//        $url = U('Api/User/getLooked','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/getLooked', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c035a0fe11927bb4603aa1bbd87bad49',
                'time' => NOW_TIME,
                'page' => 0,
                'limit' => 10
            );
            $result = CurlHttp($url, $param, 'POST');
            $info = json_decode($result, true);
            //  dump($info);die;
            $this->assign('info', $info['Data']);
            $this->display();
        }
    }

    //我的收藏
    public function collect() {
//        $url = U('Api/User/getCollect','',true,true);
        $url = 'http://' . WEB_DOMAIN . U('Api/User/getCollect', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
            'time' => NOW_TIME
        );
        $result = CurlHttp($url, $param, 'POST');
        $info = json_decode($result, true);
        $this->assign('info', $info['Data']);
        $this->display();
    }

    //留言
    public function service() {
        if (IS_POST) {
//            $url = U('Api/User/getService','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/getService', '', true, false);
            $content = I('post.msg');
            $param = array(
                'user_id' => $this->userid,
                'hash' => '1a7423692462d8739f8c00cccf2bff27',
                'time' => NOW_TIME,
                'content' => $content
            );
            $result = CurlHttp($url, $param, 'POST');
            die($result);
        } else {
            $this->display();
        }
    }

    //个人中心消息
    public function News() {
//         $url = U('Api/User/News','',true,true);
        $url = 'http://' . WEB_DOMAIN . U('Api/User/News', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
            'time' => NOW_TIME
        );
        $result = CurlHttp($url, $param, 'POST');
        $info = json_decode($result, true);

        $this->assign('system', $info['system']);
        $this->assign('order', $info['order']);
        $this->display();
    }

    //购物车
    public function cart_add() {
//         $url = U('Api/User/cart_add','',true,true);
        $url = 'http://' . WEB_DOMAIN . U('Api/User/cart_add', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'openid' => $this->openid,
            'goods_id' => I('post.gid'),
            'red_id' => I('post.sid'),
            'goods_number' => I('post.num'),
            'goods_price' => I('post.price'),
            'sku_id' => I('post.bid'),
            'hash' => '4b68cf90b43693fef2129bfdf73787c0',
            'time' => NOW_TIME
        );
        $result = CurlHttp($url, $param, 'POST');
        die($result);
    }

    public function cart() {
//         $url = U('Api/User/cart','',true,true);
//        $url = 'http://xiaotian.hzxmnet.com'.U('Api/User/cart','',true,false);
        $url = 'http://' . WEB_DOMAIN . U('Api/User/cart', '', true, false);
        $param = array(
            'user_id' => $this->userid,
            'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
            'time' => NOW_TIME
        );
        $result = CurlHttp($url, $param, 'POST');
        $result = (array) json_decode($result, true);
//        dump($result);die;
        $info = $result['info'];
        foreach ($info as $key => $value) {
            $info[$key] = (array) $value;
        }
        $brands_arr = array();
        foreach ($info as $key => $value) {
            $brands_arr[$value['nickname']][] = $value;
        }
        // dump($brands_arr);
        $this->assign('info', $brands_arr);
        $this->display();
    }

    public function cart_del() {
        $id = I('post.id');
        $id = implode(",", $id);
        if ($id) {
//            $url = U('Api/User/cart_del','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/cart_del', '', true, false);
            $param = array(
                'id' => $id,
                'hash' => '39a851a7218472226ece36d1377f3cf0',
                'time' => NOW_TIME
            );
            $result = CurlHttp($url, $param, 'POST');
            die($result);
        } else {
            die(json_encode(array('Code' => '2000', 'Msg' => '删除失败')));
        }
    }

    public function cart_edit() {
        $data = json_encode(I('post.end_data'));
//            $url = U('Api/User/cart_edit','',true,true);
        $url = 'http://' . WEB_DOMAIN . U('Api/User/cart_edit', '', true, false);
        $param = array(
            'data' => $data,
            'hash' => 'e8d19324079d8e95d37791413fd9439e',
            'time' => NOW_TIME
        );
        $result = CurlHttp($url, $param, 'POST');
        die($result);
    }

    //系统消息
    public function sysnews() {
        if (IS_POST) {
//        $url = U('Api/User/sysnews','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/sysnews', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => '47e23da2dc40c72a5c2887a79497c850',
                'time' => NOW_TIME,
                'type' => I('post.type'),
                'page' => I('post.page'),
            );
            $result = CurlHttp($url, $param, 'POST');
            die($result);
        } else {
//       $url = U('Api/User/sysnews','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/sysnews', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => '47e23da2dc40c72a5c2887a79497c850',
                'time' => NOW_TIME,
                'type' => 0,
                'page' => 0
            );
            $result = CurlHttp($url, $param, 'POST');

            $result = (array) json_decode($result);
            $info = $result['data'];
            foreach ($info as $key => $value) {
                $system[] = (array) $value;
            }
            // dump($system);exit;
            $this->assign('count', $result['list']);
            $this->assign('system', $system);
            $this->display();
        }
    }

    //订单消息
    public function Ordernews() {
        if (IS_POST) {
//        $url = U('Api/User/scroll','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/scroll', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c57b7241c1c3653b2d1a07c3c4348dec',
                'time' => NOW_TIME,
                'page' => I('post.page'),
            );
            $result = CurlHttp($url, $param, 'POST');
            die($result);
        } else {
//        $url = U('Api/User/Ordernews','',true,true);
            $url = 'http://' . WEB_DOMAIN . U('Api/User/Ordernews', '', true, false);
            $param = array(
                'user_id' => $this->userid,
                'hash' => 'c94ffcec8db680a0ac1a466e3e218046',
                'time' => NOW_TIME
            );
            $result = CurlHttp($url, $param, 'POST');

            $result = (array) json_decode($result);
// dump($result);exit;
            foreach ($result['info'] as $key => $value) {
                $info[] = (array) $value;
            }
            $count = $result['count'];

            $this->assign('count', $count);
            $this->assign('info', $info);
            $this->display();
        }
    }

    public function system_detail() {
//         $url = U('Api/User/system_detail','',true,true);
        $url = 'http://' . WEB_DOMAIN . U('Api/User/system_detail', '', true, false);
        $param = array(
            'id' => I('get.id'),
            'hash' => '39a851a7218472226ece36d1377f3cf0',
            'time' => NOW_TIME
        );
        $result = CurlHttp($url, $param, 'POST');

        $result = (array) json_decode($result);
        $info = (array) $result['info'];
        $this->assign('info', $info);
        $this->display();
    }

    //全部订单
    public function Orderlist() {
        $this->display();
    }

    //待付款
    public function orderpay() {
        $this->display();
    }

    //待发货
    public function orderdeliver() {
        $this->display();
    }

    //待收货
    public function orderreceive() {
        $this->display();
    }

    //退款
    public function orderrefund() {
        $this->display();
    }

    //退款详情
    public function orderrsuccess() {
        $this->display();
    }

    //评价
    public function ordercomment() {
        $this->display();
    }

    //结算
    public function order() {
        $this->display();
    }

    public function Compare($target, $data) {
        foreach ($target as $key => $value) {
            $target_data[$key]['status'] = 2;
            $target_data[$key]['name'] = $value;
            foreach ($data as $k => $v) {
                if ($value == $v) {
                    $target_data[$key]['status'] = 1;
                    $target_data[$key]['name'] = $v;
                }
            }
        }

        return $target_data;
    }

}

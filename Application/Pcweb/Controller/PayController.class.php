<?php

/**
 * 支付控制器
 * 王远庆
 */

namespace Pcweb\Controller;

use Think\Controller;
use Vendor\WeiXin\WechatConfig;

class PayController extends Controller {

    public $profit_user = array();
    public $parent_num = 1;

    public function __construct() {
        parent::__construct();
        $this->Rid = intval(I('get.Rid', 0));
        Vendor('WeiXin.WechatConfig');
        $PayConfig = new WechatConfig($this->Rid);
        $PayConfig->setConfig();
    }

    //开始支付
    public function index() {
        $tag = date('YmdHis', NOW_TIME) . randomString('6', 0); //支付编号
        $descs = '支付测试';
        $Pay = array();
        $trade_sn = $tag; //支付单号
        $body = $descs; //TODO 需要完善描述
        $attach = $descs;
        $free = 0.01; //最终支付金额
        $paytype = 1;
        $this->pay($trade_sn, $body, $attach, $free, $paytype);
        exit();
    }

    /*
     * 支付回调
     *
     */

    public function wxPaySuccess() {
        dblog(array('wxPaySuccess start'));
        Vendor('WeiXin.WeixinPayPubHelper');
        $notify = new \Notify_pub();
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        $temp = $notify->checkSign();
        if ($temp == FALSE) {
            //logit('签名失败：FAIL');
            $notify->setReturnParameter("return_code", "FAIL"); //返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); //返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); //设置返回码
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;
        //==商户根据实际情况设置相应的处理流程=======
        if ($temp == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                dblog("【通信出错】:\n" . $xml . "\n");
            } elseif ($notify->data["result_code"] == "FAIL") {
                dblog("【业务出错】:\n" . $xml . "\n");
            } else {
                //支付回调，更新订单相关信息
                $out_trade_no = $notify->data['out_trade_no'];
                $order_payment = M('order_payment')->where(array('pay_order_sn' => $out_trade_no))->find();
                dblog(array('wxPaySuccess', '$order_payment' => $order_payment));
                if ($order_payment && !$order_payment['end_time']) {
                    $Model = M();
                    $Model->startTrans();
                    try {
                        //获取当前用户信息
                        $user_info = M('public_user')->where(array('id' => $order_payment['user_id']))->field('id,red_id,scenario_id,parent_id,openid,nickname')->find();
                        if (!$user_info) {
                            $Model->rollback();
                            dblog('ERROR[$user_info]');
                            return FALSE;
                        }
                        $user_info['nickname'] = base64_decode($user_info['nickname']);
                        //更新交易wechat_order_payment表
                        $order_payment_data = array(
                            'bank_type' => $notify->data['bank_type'],
                            'cash_fee' => $notify->data['cash_fee'],
                            'fee_type' => $notify->data['fee_type'],
                            'is_subscribe' => $notify->data['is_subscribe'],
                            'mch_id' => $notify->data['mch_id'],
                            'time_end' => $notify->data['time_end'],
                            'total_fee' => $notify->data['total_fee'],
                            'trade_type' => $notify->data['trade_type'],
                            'transaction_id' => $notify->data['transaction_id'],
                            'end_time' => time(),
                            'status' => 1,
                        );
                        $order_payment_update = M('order_payment')->where(array('pay_order_sn' => $out_trade_no))->save($order_payment_data);
                        if (!$order_payment_update) {
                            $Model->rollback();
                            dblog('ERROR[order_payment_update]');
                            return FALSE;
                        }

                        $order_ids = json_decode($order_payment['order_id'], true);
                        if ($order_payment['order_type'] == 1) {
                            $data['status'] = 1;
                            $info = M('reward_money')->where(array('out_trade_no' => $order_payment['pay_order_sn']))->data($data)->save();
                            if ($info) {
                                $where = array('m.out_trade_no' => $order_payment['pay_order_sn']);
                                $fields = string_fields(array(
                                    'm' => array('user_id', 'to_persion_id', 'money', 'discuz_id'),
                                    'u' => array('accumulated_money', 'account_balance', 'nickname'),
                                ));
                                $model = M('reward_money')->alias('m')->join(array(C('DB_PREFIX') . ('public_user') . ' u ON u.id=m.to_persion_id'), 'LEFT');

                                $data = $model->where($where)->field($fields)->find();
                                if ($data) {
                                    $user = M('public_user')->where(array('id' => $data['user_id']))->field('nickname')->find();
                                    $savedata['accumulated_money'] = $data['accumulated_money'] + $data['money'];
                                    $savedata['account_balance'] = $data['account_balance'] + $data['money'];
                                    M('public_user')->where(array('id' => $data['to_persion_id']))->data($savedata)->save();
                                    $content = "" . base64_decode($user['nickname']) . "打赏了您" . $data['money'] . "元";
                                    $discuz_msg = array(
                                        'discuz_id' => $data['discuz_id'],
                                        'user_id' => $data['user_id'],
                                        'to_discuzid' => $data['to_persion_id'],
                                        'type' => 1,
                                        'time' => NOW_TIME,
                                        'content' => base64_encode($content),
                                    );
                                    M('discuz_mes')->data($discuz_msg)->add();
                                }
                            }
                        } else {
                            //1、查询订单商品详情数据
                            if (!empty($order_ids)) {
                                foreach ($order_ids as $k1 => $v1) {
                                    $order_info = array();
                                    $order_info = D('OrderInfo')->where(array('id' => $v1))->relation('OrderDetail')->find();

                                    $order_detail_list = $order_info['OrderDetail'];

                                    //更新订单状态
                                    $order_info_data = array(
                                        'pay_status' => '1',
                                        'pay_time' => $order_payment_data['end_time'],
//                                                            'paid_money'    =>  $notify->data['total_fee']/100
                                        'paid_money' => $order_info['order_amount']
                                    );
                                    $order_info_update = M('order_info')->where(array('id' => $order_info['id']))->setField($order_info_data);
                                    dblog(array('order_info_update', $order_info_update));
                                    if (!$order_info_update) {
                                        $Model->rollback();
                                        dblog('ERROR[order_info_update]');
                                        return FALSE;
                                    }

                                    //订单日志
                                    $order_log_data = array();
                                    $order_log_data['type'] = 0;
                                    $order_log_data['user_id'] = $order_info['user_id'];
                                    $order_log_data['order_id'] = $order_info['id'];
                                    $order_log_data['msg'] = '支付成功';
                                    $order_log_data['dateline'] = NOW_TIME;
                                    $order_log = M('order_log')->add($order_log_data);
                                    if (!$order_log) {
                                        dblog(array('【wxPaySuccess ERROR】','[$order_log]'=>M('order_log')->getLastSql()));
                                    }
                                    //订单消息【提醒卖家】
                                    $agentUser  =   M('order_info')->alias('o')
                                            ->join(C('DB_PREFIX') . 'agent AS a ON `a`.`id`=`o`.`uid`')
                                            ->join(C('DB_PREFIX') . 'public_user AS u ON `a`.`userid`=`u`.`id`')
                                            ->field('u.id as seller_user_id,o.user_id as buyer_user_id,u.wait_sure')
                                            ->where(array('o.id'=>$order_info['id']))
                                            ->find();
                                    $order_mes_data = array();
                                    $order_mes_data['type']         = 1;    //平台
                                    $order_mes_data['user_id']          = $agentUser['seller_user_id'];
                                    $order_mes_data['order_id']     = $order_info['id'];
                                    $order_mes_data['msg']          = '您有新的客户订单，买家已付款';
                                    $order_mes_data['dateline']     = NOW_TIME;
                                    $order_mes_data['mes_type']     =  2;
                                    $order_mes_data['thumb']        =   $order_detail_list[0]['goods_image'];
                                    $order_mes                      = M('order_message')->add($order_mes_data);
                                    if (!$order_mes) {
                                        dblog(array('【wxPaySuccess ERROR】','[$order_mes]'=>M('order_message')->getLastSql()));
                                    }

                                    //付款成功减商品库存
                                    foreach ($order_detail_list as $k2 => $v2) {
                                        M('goods_sku')->where(array('id' => $v2['sku_id']))->setDec('sku_num', $v2['goods_number']);
                                    }
                                }
                            }
                        }

                        //事务提交
                        $Model->commit();

//						$parame =   array();
//						$parame['openid']		= $user_info['openid'];
//						$SendKfMsgContent               = '[小提示]';
//						$SendKfMsgContent               .= '尊敬的'.$user_info['nickname'].'，您的订单号'.$order_info['order_sn'].'已下单成功，感谢您的信任！我会按下单顺序尽快为您发货，谢谢！\n';
//						$parame['content']		= $SendKfMsgContent;
//						$DataInfo				= R('Home/Weixin/SendKfMsg',$parame);
                    } catch (Exception $exc) {
                        $Model->rollback();
                        dblog($exc->getTraceAsString());
                        echo $exc->getTraceAsString();
                        return FALSE;
                    }
                }
            }
        }
    }

    /*
     * 未支付，自动取消订单
     * 【定时脚本任务】
     * 暂定20分钟跑1次，20分钟20个订单，1分钟1个订单，1天=24小时=24*60分钟，即为24*60*1=1440个订单
     */

    public function autoOrderCancel() {
        $where = array();
        $where['pay_status'] = 0; //未支付
        $where['order_status'] = 0; //正常订单
        $hour = 2;  //2小时
        $where['create_time'] = array('lt', NOW_TIME - 60 * 60 * $hour);
        //查找2小时内未支付的订单，自动取消订单
        $order_list = M('order_info')->where($where)->field('id,order_sn,pay_status,create_time')->limit(20)->select();
        dump($order_list);
        die;
        foreach ($order_list as $key => $value) {
            $order_info_data = array();
            $order_info_data['order_status'] = 2;
            $order_info_data['cancel_time'] = NOW_TIME;
            $order_info_update = M('order_info')->where(array('id' => $value['id']))->save($order_info_data);
            if (!$order_info_update) {
                continue;
            }
            $order_log_data = array();
            $order_log_data['type'] = 1;
            $order_log_data['user_id'] = 0;
            $order_log_data['order_id'] = $value['id'];
            $order_log_data['msg'] = '超过[' . $hour . ']小时，系统自动取消订单';
            $order_log_data['dateline'] = NOW_TIME;
            M('order_log')->add($order_log_data);
        }
    }

    /*
     * 自动分佣
     * 【定时脚本任务】
     * 暂定4分钟跑1次
     * 4分钟20个订单，1分钟5个订单，1天=24小时=24*60分钟，即为24*60*5=7200个订单
     */

    public function autoOrderProfit() {
        $where = array();
        $where['pay_status'] = 1;  //已支付
        //            $where['order_status']      =   1;  //已确认
        //            $where['shipping_status']   =   2;  //已收货
        //            $where['is_commission']     =   0;  //待分佣
        $where['auto_commission_time'] = array('lt', NOW_TIME);  //自动分佣时间
        $order_list = D('OrderInfo')->where($where)->relation(true)->order('id DESC')->limit(20)->select();
        //            dump($order_list);die;
        foreach ($order_list as $k1 => $v1) {
            //如果当前订单用户有网红和客服，则分佣
            //申请售后商品数量
            $return_num = 0;
            foreach ($v1['OrderDetail'] as $k2 => $v2) {
                $Customerservice = array();
                if ($v1['PublicUser']['red_id'] && $v1['PublicUser']['scenario_id']) {
                    $Customerservice = D('Customerservice')->where(array('id' => $v1['PublicUser']['scenario_id']))->relation(true)->find();
                }
                $odetail = D('OrderDetail')->where(array('id' => $v2['id']))->relation(true)->find();
                if ($v2['status'] == '0') {   //状态为0即表示当前商品为正常状态
                    if (!empty($Customerservice)) {
                        //网红分佣
                        M('order_profit_red')->where(array('id' => $odetail['OrderProfitRed']['id']))->setField(array('type' => 1));
                        M('red_net')->where(array('id' => $odetail['OrderProfitRed']['uid']))->setField(array('account' => $Customerservice['RedNet']['account'] + ($odetail['OrderProfitRed']['money'] * $odetail['OrderProfitRed']['num'])));

                        //客服分佣
                        M('order_profit_scenario')->where(array('id' => $odetail['OrderProfitScenario']['id']))->setField(array('type' => 1));
                        M('red_net')->where(array('id' => $odetail['OrderProfitScenario']['uid']))->setField(array('account' => $Customerservice['account'] + ($odetail['OrderProfitScenario']['money'] * $odetail['OrderProfitScenario']['num'])));
                    }

                    //上级分佣
                    foreach ($odetail['OrderProfitUser'] as $key => $value) {
                        //更新分佣状态
                        M('order_profit_user')->where(array('id' => $value['id']))->setField(array('type' => 1));
                        $profit_user = M('public_user')->where(array('id' => $value['uid']))->find();
                        //更新用户账户
                        $profit_user_data = array();
                        $profit_user_data['accumulated_money'] = $profit_user['accumulated_money'] + $value['money'] * $value['num'];
                        $profit_user_data['account_balance'] = $profit_user['account_balance'] + $value['money'] * $value['num'];
                        M('public_user')->where(array('id' => $profit_user['id']))->setField($profit_user_data);
                    }
                } else {   //不为0即表未当前商品为售后状态
                    $where = array();
                    $where['order_id'] = $v1['id'];
                    $where['order_detail_id'] = $v2['id'];
                    $order_return = M('order_return')->where($where)->field('id,order_id,order_detail_id,status,apply_number')->find();
                    //如果当前商品申请售后数量
                    $return_num += $order_return['apply_number'];
                    if ($order_return['apply_number'] == $v2['goods_number']) {   //申请售后数量和购买数量相等，则全退
                        $profit_data = array();
                        $profit_data['type'] = 2;

                        M('order_profit_red')->where(array('id' => $odetail['OrderProfitRed']['uid']))->setField($profit_data); //网红
                        M('order_profit_scenario')->where(array('id' => $odetail['OrderProfitScenario']['uid']))->setField($profit_data); //客服
                        M('order_profit_user')->where($where)->setField($profit_data); //用户上级
                    } else {
                        $profit_data = array();
                        $profit_data['type'] = 2;
                        $profit_data['num'] = $v2['goods_number'] - $order_return['apply_number'];
                        M('order_profit_red')->where($where)->setField($profit_data); //网红
                        M('order_profit_scenario')->where($where)->setField($profit_data); //客服
                        M('order_profit_user')->where($where)->setField($profit_data); //用户上级

                        if (!empty($Customerservice)) {
                            //网红分佣
                            M('order_profit_red')->where(array('id' => $odetail['OrderProfitRed']['id']))->setField(array('type' => 1));
                            M('red_net')->where(array('id' => $odetail['OrderProfitRed']['uid']))->setField(array('account' => $Customerservice['RedNet']['account'] + ($odetail['OrderProfitRed']['money'] * $profit_data['num'])));

                            //客服分佣
                            M('order_profit_scenario')->where(array('id' => $odetail['OrderProfitScenario']['id']))->setField(array('type' => 1));
                            M('red_net')->where(array('id' => $odetail['OrderProfitScenario']['uid']))->setField(array('account' => $Customerservice['account'] + ($odetail['OrderProfitScenario']['money'] * $profit_data['num'])));
                        }

                        //上级分佣
                        foreach ($odetail['OrderProfitUser'] as $key => $value) {
                            //更新分佣状态
                            M('order_profit_user')->where(array('id' => $value['id']))->setField(array('type' => 1));
                            $profit_user = M('public_user')->where(array('id' => $value['uid']))->find();
                            //更新用户账户
                            $profit_user_data = array();
                            $profit_user_data['accumulated_money'] = $profit_user['accumulated_money'] + $value['money'] * $value['num'];
                            $profit_user_data['account_balance'] = $profit_user['account_balance'] + $value['money'] * $value['num'];
                            M('public_user')->where(array('id' => $profit_user['id']))->setField($profit_user_data);
                        }
                    }
                }
            }
            //如果申请售后商品总数量等于订单商品总数量，则分佣取消
            if ($return_num == $v1['goods_number']) {
                $order_info_update = M('order_info')->where(array('id' => $v1['id']))->setField(array('is_commission' => 2));
                if (!$order_info_update) {
                    continue;
                }
                $order_log_data = array();
                $order_log_data['type'] = 2;
                $order_log_data['user_id'] = 0;
                $order_log_data['order_id'] = $v1['id'];
                $order_log_data['msg'] = '订单商品全部申请售后，订单取消分佣';
                $order_log_data['dateline'] = NOW_TIME;
                M('order_log')->add($order_log_data);
            } else {

                $order_info_update = M('order_info')->where(array('id' => $v1['id']))->setField(array('is_commission' => 1));
                if (!$order_info_update) {
                    continue;
                }
                $order_log_data = array();
                $order_log_data['type'] = 1;
                $order_log_data['user_id'] = 0;
                $order_log_data['order_id'] = $v1['id'];
                $order_log_data['msg'] = '订单已分佣';
                $order_log_data['dateline'] = NOW_TIME;
                M('order_log')->add($order_log_data);
            }
        }
    }

    //分佣测试
    public function profit() {
        //            $parent_id_arr  = $this->get_parents(30);
        //            dump($parent_id_arr);die;

        try {
            $uid = 30;
            //获取当前用户信息
            $user_info = M('public_user')->where(array('id' => $uid))->field('id,red_id,scenario_id,parent_id')->find();
            if (!$user_info) {
                dblog('ERROR[$user_info]');
                exit();
            }

            /*
             * 支付成功，产生分佣
             *
             * 分佣开始
             */
            $order_id = 97;
            //1、查询订单商品详情数据
            $order_detail_list = M('order_detail')->where(array('order_id' => $order_id))
                    ->field('id,goods_price,goods_number')
                    ->select();

            $user_profit_config = array(
                '1' => 15,
                '2' => 5,
                '3' => 0
            );

            //获取当前用户父级UID，一级、二级、三级，最多3级
            $user_parent_id_arr = $this->get_parents($user_info['parent_id']);

            //获取当前用户父级数量
            $user_parent_count = count($user_parent_id_arr);
            //
            //当父级用户数量 $user_parent_count = 3时，则分佣只分给3个父级用户，不分给网红客服；
            //当父级用户数量 $user_parent_count < 3时，则$user_parent_count份分佣给上级，再分给网红客服
            //
			if ($user_parent_count == 3) {
                //循环遍历父级用户
                foreach ($user_parent_id_arr as $k1 => $v1) {
                    foreach ($order_detail_list as $k2 => $v2) {
                        $profit_data = array();
                        $profit_data['uid'] = $v1;
                        $profit_data['order_id'] = $order_id;
                        $profit_data['order_detail_id'] = $v2['id'];
                        $profit_data['money'] = $v2['goods_price'] * $user_profit_config[$k1] / 100;
                        $profit_data['num'] = $v2['goods_number'];
                        $profit_data['msg'] = '支付成功，初始化分佣数据';
                        $profit_data['dateline'] = NOW_TIME;
                        $profit_data['buyer'] = NOW_TIME;
                        $profitAdd = M('order_profit_user')->add($profit_data);
                        if (!$profitAdd) {
                            dblog('ERROR[profitAdd]');
                            exit();
                        }
                    }
                }
            } else {
                //当父级不足3级时，则需再分给网红客服
                //查找信息
                $red_net_info = M('red_net')->where(array('id' => $user_info['red_id']))->field('id,red_profit_level1,red_profit_level2,red_profit_level3')->find();
                if (!$red_net_info) {
                    dblog('ERROR[red_net_info]');
                    exit();
                }

                if ($user_parent_count == 0) {    //只分给网红和客服
                    foreach ($order_detail_list as $k2 => $v2) {
                        //分给网红
                        $profit_data = array();
                        $profit_data['uid'] = $red_net_info['id'];
                        $profit_data['order_id'] = $order_id;
                        $profit_data['order_detail_id'] = $v2['id'];
                        $profit_data['money'] = $v2['goods_price'] * $red_net_info['red_profit_level1'] * 80 / 100 / 100;
                        $profit_data['num'] = $v2['goods_number'];
                        $profit_data['msg'] = '支付成功，初始化分佣数据';
                        $profit_data['dateline'] = NOW_TIME;
                        $profitAdd = M('order_profit_red')->add($profit_data);
                        if (!$profitAdd) {
                            dblog('ERROR[profitAdd]');
                            exit();
                        }

                        //分给客服
                        $profit_data = array();
                        $profit_data['uid'] = $red_net_info['id'];
                        $profit_data['order_id'] = $order_id;
                        $profit_data['order_detail_id'] = $v2['id'];
                        $profit_data['money'] = $v2['goods_price'] * $red_net_info['red_profit_level3'] * 20 / 100 / 100;
                        $profit_data['num'] = $v2['goods_number'];
                        $profit_data['msg'] = '支付成功，初始化分佣数据';
                        $profit_data['dateline'] = NOW_TIME;
                        $profitAdd = M('order_profit_scenario')->add($profit_data);
                        if (!$profitAdd) {
                            dblog('ERROR[profitAdd]');
                            exit();
                        }
                    }
                } elseif ($user_parent_count == 1) {   //分给父级用户和网红客服
                    //分给父级
                    foreach ($user_parent_id_arr as $k1 => $v1) {
                        foreach ($order_detail_list as $k2 => $v2) {
                            $profit_data = array();
                            $profit_data['uid'] = $v1;
                            $profit_data['order_id'] = $order_id;
                            $profit_data['order_detail_id'] = $v2['id'];
                            $profit_data['money'] = $v2['goods_price'] * $user_profit_config[$k1] / 100;
                            $profit_data['num'] = $v2['goods_number'];
                            $profit_data['msg'] = '支付成功，初始化分佣数据';
                            $profit_data['dateline'] = NOW_TIME;
                            $profitAdd = M('order_profit_user')->add($profit_data);
                            if (!$profitAdd) {
                                dblog('ERROR[profitAdd]');
                                exit();
                            }
                        }
                    }

                    foreach ($order_detail_list as $k2 => $v2) {
                        //分给网红
                        $profit_data = array();
                        $profit_data['uid'] = $red_net_info['id'];
                        $profit_data['order_id'] = $order_id;
                        $profit_data['order_detail_id'] = $v2['id'];
                        $profit_data['money'] = $v2['goods_price'] * $red_net_info['red_profit_level2'] * 80 / 100 / 100;
                        $profit_data['num'] = $v2['goods_number'];
                        $profit_data['msg'] = '支付成功，初始化分佣数据';
                        $profit_data['dateline'] = NOW_TIME;
                        $profitAdd = M('order_profit_red')->add($profit_data);
                        if (!$profitAdd) {
                            dblog('ERROR[profitAdd]');
                            exit();
                        }

                        //分给客服
                        $profit_data = array();
                        $profit_data['uid'] = $red_net_info['id'];
                        $profit_data['order_id'] = $order_id;
                        $profit_data['order_detail_id'] = $v2['id'];
                        $profit_data['money'] = $v2['goods_price'] * $red_net_info['red_profit_level2'] * 20 / 100 / 100;
                        $profit_data['num'] = $v2['goods_number'];
                        $profit_data['msg'] = '支付成功，初始化分佣数据';
                        $profit_data['dateline'] = NOW_TIME;
                        $profitAdd = M('order_profit_scenario')->add($profit_data);
                        if (!$profitAdd) {
                            dblog('ERROR[profitAdd]');
                            exit();
                        }
                    }
                } elseif ($user_parent_count == 2) {
                    //分给父级
                    foreach ($user_parent_id_arr as $k1 => $v1) {
                        foreach ($order_detail_list as $k2 => $v2) {
                            $profit_data = array();
                            $profit_data['uid'] = $v1;
                            $profit_data['order_id'] = $order_id;
                            $profit_data['order_detail_id'] = $v2['id'];
                            $profit_data['money'] = $v2['goods_price'] * $user_profit_config[$k1] / 100;
                            $profit_data['num'] = $v2['goods_number'];
                            $profit_data['msg'] = '支付成功，初始化分佣数据';
                            $profit_data['dateline'] = NOW_TIME;
                            $profitAdd = M('order_profit_user')->add($profit_data);
                            if (!$profitAdd) {
                                dblog('ERROR[profitAdd]');
                                exit();
                            }
                        }
                    }

                    foreach ($order_detail_list as $k2 => $v2) {
                        //分给网红
                        $profit_data = array();
                        $profit_data['uid'] = $red_net_info['id'];
                        $profit_data['order_id'] = $order_id;
                        $profit_data['order_detail_id'] = $v2['id'];
                        $profit_data['money'] = $v2['goods_price'] * $red_net_info['red_profit_level3'] * 80 / 100 / 100;
                        $profit_data['num'] = $v2['goods_number'];
                        $profit_data['msg'] = '支付成功，初始化分佣数据';
                        $profit_data['dateline'] = NOW_TIME;
                        $profitAdd = M('order_profit_red')->add($profit_data);
                        if (!$profitAdd) {
                            dblog('ERROR[profitAdd]');
                            exit();
                        }

                        //分给客服
                        $profit_data = array();
                        $profit_data['uid'] = $red_net_info['id'];
                        $profit_data['order_id'] = $order_id;
                        $profit_data['order_detail_id'] = $v2['id'];
                        $profit_data['money'] = $v2['goods_price'] * $red_net_info['red_profit_level3'] * 20 / 100 / 100;
                        $profit_data['num'] = $v2['goods_number'];
                        $profit_data['msg'] = '支付成功，初始化分佣数据';
                        $profit_data['dateline'] = NOW_TIME;
                        $profitAdd = M('order_profit_scenario')->add($profit_data);
                        if (!$profitAdd) {
                            dblog('ERROR[profitAdd]');
                            exit();
                        }
                    }
                } else {
                    dblog('ERROR[user_parent_count]');
                    exit();
                }
            }

            exit('ok');
            /*
             *
             * 分佣结束
             */
        } catch (Exception $exc) {
            dblog($exc->getTraceAsString());
            echo $exc->getTraceAsString();
            exit();
        }
    }

    //递归查找普通用户父级
    public function get_parents($pid) {
        if ($this->parent_num <= 3) {
            if ($pid) {
                $puser = M('public_user')->where(array('id' => $pid))->field('id,parent_id')->find();
                if (!empty($puser)) {
                    $this->profit_user[$this->parent_num] = $puser['id'];  //key从1，2，3开始，分别对应一级，二级，三级
                    $this->parent_num +=1;
                    if ($puser['parent_id']) {
                        $this->get_parents($puser['parent_id']);
                    }
                }
            }
        }
        return $this->profit_user;
    }

    //地址测试
    public function address() {
        $callBack = base64_encode(U('', '', true, true));
        $calldata = cookie(md5($callBack));
        if (empty($calldata)) {
            R('Home/Weixin/wxAddress', array('callBack' => $callBack));
            exit();
        } else {
            $AuthInfo = json_decode(base64_decode(FauthCode($calldata, 'DECODE')), true);
        }
        $this->assign('signPackage', $AuthInfo);
        $this->display();
    }

    //JS测试
    public function wxjs() {
        $AuthInfo = R('Home/Weixin/wxJs');
        $this->assign('signPackage', $AuthInfo);
        $this->display();
    }

    //微信支付测试
    public function wxPay() {
        $price = '0.01';
        $payInfo['openid'] = 'oAbYqxITr64xuQ1mjxB84TpoQ5Xk';
        $payInfo['out_trade_no'] = date('YmdHis', NOW_TIME) . $ShopInfo['cid'] . randomString('6', 0); //支付编号
        $payInfo['total_fee'] = (int) ($price * 100);
        $PayInfo = R('Home/Weixin/wxPay', $payInfo);
        $this->assign('PayInfo', $PayInfo);
        $this->display();
    }

}

?>
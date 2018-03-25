<?php

namespace System\Controller;

use Think\Controller;

class OrderController extends Controller {

    private $nums = 0;

    public function _initialize() {
        $this->nums = 0;
        
        // 读取数据库配置
        $config = M('config')->where(array('status' => 1))->getField('config_sign, config_value');
        C($config);
    }

    /*
     * 支付回调
     * DML.
     */

    public function paySuccess() {
        $this->alipay_success();
    }

    //阿里通知
    protected function alipay_success() {
        include_once(CONF_PATH . 'alipay.config.php');
        include_once(VENDOR_PATH . '/Alipay/lib/alipay_notify.class.php');
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {//验证成功
            //商户订单号
            $out_trade_no = $_POST['out_trade_no'];
            //支付宝交易号
            $trade_no = $_POST['trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                //交易结束
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                //交易成功
                $paid_money = $_POST['total_fee'];
                $this->paid_update_order($out_trade_no, $paid_money, $trade_no);
            }
            echo "success";  //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }
    }

    protected function paid_update_order($out_trade_no, $paid_money, $trade_no) {
        $order_sn = $out_trade_no;
        $order_info = D('Order')->relation(true)->where(array('order_sn' => $order_sn))->field('id,agent_id,order_sn,pay_status')->find();
        if (!empty($order_info)) {
            $agent_info = $order_info['Agent'];
            if ($order_info['pay_status'] == '0') {

                $order_data = array();
                $order_data['pay_status'] = 1;
                $order_data['pay_time'] = NOW_TIME;
                $order_data['paid_money'] = $paid_money;
                $order_data['trade_no'] = $trade_no;
                M('order')->where(array('order_sn' => $order_sn))->setField($order_data);

                //订单日志
                $log_data = array();
                $log_data['agent_id'] = $order_info['agent_id'];
                $log_data['order_id'] = $order_info['id'];
                $log_data['desc'] = '订单支付成功';
                $log_data['add_time'] = NOW_TIME;
                M('order_log')->add($log_data);
                
                //更新元宝记录
                $agentCardNums = $agent_info['room_card'] + $order_info['OrderDetail']['goods_nums']+$order_info['OrderDetail']['give_goods_nums'];
                M('agent')->where(array('id'=>$order_info['agent_id']))->setField(array('room_card'=>$agentCardNums));
                
                //充值记录
                $agent_recharge_recored_data  =   array();
                $agent_recharge_recored_data['type']            =   0;
                $agent_recharge_recored_data['order_id']        =   $order_info['id'];
                $agent_recharge_recored_data['agent_id']        =   $order_info['agent_id'];
                $agent_recharge_recored_data['pay_nums']        =   $order_info['OrderDetail']['goods_nums'];
                $agent_recharge_recored_data['desc']            =   '代理['.$agent_info['phone'].']购买元宝：'.$order_info['OrderDetail']['goods_nums'].'颗';
                $agent_recharge_recored_data['add_time']        =   NOW_TIME;
                M('agent_recharge_recored')->add($agent_recharge_recored_data);

                if (!empty($agent_info)) {
                    //上级
                    if (!empty($agent_info['pid'])) {
                        $parent_agent_info = M('agent')->where(array('id' => $agent_info['pid']))->field('id,user_id,pid,nickname,room_card')->find();
                        if (!empty($parent_agent_info)) {
                            if (C('rebate_percent')) {
                                $rebate_percent = C('rebate_percent') / 100;
                            } else {
                                $rebate_percent = 1 / 100;
                            }
                            $add_InsureScore = intval($order_info['OrderDetail']['goods_nums'] * $rebate_percent);
                            
                            //更新元宝数量
                            $InsureScore = $parent_agent_info['room_card'] + $add_InsureScore;
                            M('agent')->where(array('id'=>$parent_agent_info['id']))->setField(array('room_card'=>$InsureScore));
                            
                            //返卡记录
                            $agent_recharge_recored_data  =   array();
                            $agent_recharge_recored_data['type']            =   2;  //元宝来源：0代理购买元宝，1商家给代理充元宝，2下级代理返卡
                            $agent_recharge_recored_data['order_id']        =   $order_info['id'];
                            $agent_recharge_recored_data['agent_id']        =   $parent_agent_info['id'];
                            $agent_recharge_recored_data['pay_nums']        =   $add_InsureScore;
                            $agent_recharge_recored_data['desc']            =   '代理[' . $agent_info['phone'] . ']购买套餐[' . $order_info['OrderDetail']['goods_name'] . ']，上级代理[' . $parent_agent_info['nickname'] . ']获得返卡[' . $add_InsureScore . ']颗';
                            $agent_recharge_recored_data['add_time']        =   NOW_TIME;
                            M('agent_recharge_recored')->add($agent_recharge_recored_data);
                        }
                    }
                }
            }
        }
    }

}

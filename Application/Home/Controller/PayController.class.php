<?php

/**
 * 支付控制器
 * 王远庆
 */

namespace Home\Controller;

use Think\Controller;
use Vendor\WeiXin\WechatConfig;


class PayController extends Controller {

    public $profit_user = array();
    public $parent_num = 1;

    public function __construct() {
        parent::__construct();

        // 读取数据库配置
        $config = M('config')->where(array('status' => 1))->getField('config_sign, config_value');
        C($config);
        $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
        
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
        dblog(array('wxPaySuccess 1001','get'=>I('get.')));
        $order_type = I('get.order_type',1,'intval');
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
                $transaction_id = $notify->data['transaction_id'];
                $paid_money = $notify->data['total_fee']/100;
                dblog(array('wxPaySuccess 1001', '$out_trade_no' => $out_trade_no));
                

                switch ($order_type) {
                    case '1'://1=代理购买房卡
                        dblog(array('wxPaySuccess order_type 1002'));
                        $this->agent_update_order($out_trade_no, $paid_money, $transaction_id);
                        break;
                    case '2'://2=玩家购买房卡
                        dblog(array('wxPaySuccess order_type 1003'));
                        $this->player_update_order($out_trade_no);
                        break;
                    case '3'://3=购买代理套餐,申请成为代理
                        dblog(array('wxPaySuccess order_type 1003'));
                        $this->join_agent_update_order($out_trade_no, $paid_money, $transaction_id);
                        break;
                    
                    default:
                        # code...
                        break;
                }

            }
        }
    }

    //所有代理套餐
    protected function packageAll(){
            $package = array(
                1=>array('id'=>1,'level'=>1,'goods_name'=>'一级代理套餐','price'=>5000,'goods_nums'=>15000),
                2=>array('id'=>2,'level'=>2,'goods_name'=>'二级代理套餐','price'=>3000,'goods_nums'=>7500),
                3=>array('id'=>3,'level'=>2,'goods_name'=>'二级代理套餐','price'=>1000,'goods_nums'=>2250),
            );

            return $package;
    }

    //代理购买房卡
    protected function agent_update_order($out_trade_no, $paid_money, $transaction_id) {
        dblog(array('agent_update_order start',array($out_trade_no, $paid_money, $transaction_id)));
        $order_sn = $out_trade_no;
        $order_info = M('order')->where(array('order_sn' => $order_sn))->field('id,agent_id,order_sn,pay_status,total_amount')->find();
                    dblog(array('agent_update_order 1000','$order_info'=>$order_info));
        if (!empty($order_info)) {
            if ($order_info['pay_status'] == '0') {
                $agent_info = M('agent')->where(array('id'=>$order_info['agent_id']))->field(true)->find();
                $order_info['OrderDetail'] = M('order_detail')->where(array('order_id'=>$order_info['id']))->field(true)->find();

                $order_data = array();
                $order_data['pay_status'] = 1;
                $order_data['pay_time'] = NOW_TIME;
                $order_data['paid_money'] = $paid_money;
                $order_data['trade_no'] = $transaction_id;
                $res1001 = M('order')->where(array('order_sn' => $order_sn))->setField($order_data);

                //订单日志
                $log_data = array();
                $log_data['agent_id'] = $order_info['agent_id'];
                $log_data['order_id'] = $order_info['id'];
                $log_data['desc'] = '订单支付成功';
                $log_data['add_time'] = NOW_TIME;
                $res1002 = M('order_log')->add($log_data);
                
                //更新房卡记录
                $agentCardNums = $agent_info['room_card'] + $order_info['OrderDetail']['goods_nums']+$order_info['OrderDetail']['give_goods_nums'];
                $res1003 = M('agent')->where(array('id'=>$order_info['agent_id']))->setField(array('room_card'=>$agentCardNums));
                
                //充值记录
                $agent_recharge_recored_data  =   array();
                $agent_recharge_recored_data['type']            =   0;
                $agent_recharge_recored_data['order_id']        =   $order_info['id'];
                $agent_recharge_recored_data['agent_id']        =   $order_info['agent_id'];
                $agent_recharge_recored_data['pay_nums']        =   $order_info['OrderDetail']['goods_nums'];
                $agent_recharge_recored_data['desc']            =   '代理['.$agent_info['phone'].']购买房卡：'.$order_info['OrderDetail']['goods_nums'].'个';
                $agent_recharge_recored_data['add_time']        =   NOW_TIME;
                $res1004 = M('agent_recharge_recored')->add($agent_recharge_recored_data);

                //上级
                dblog(array('agent_update_order 1001','$agent_info'=>$agent_info));
                if (!empty($agent_info['pid']) && $agent_info['level'] == 2) {
                    $parent_agent_info = M('agent')->where(array('id' => $agent_info['pid']))->field(true)->find();
                    dblog(array('agent_update_order 1002','$parent_agent_info'=>$parent_agent_info));
                    if (!empty($parent_agent_info) && $parent_agent_info['level'] == 1) {
                        $rebate_percent = 10 / 100;
                        $add_InsureScore = ($order_info['total_amount'] * $rebate_percent);
                        
                        //返利记录
                        $agent_rebate_recored_data  =   array();
                        $agent_rebate_recored_data['type']              =   1;  //1=下级返利，2=推荐人返利，3=会员返利
                        $agent_rebate_recored_data['agent_id']          =   $parent_agent_info['id'];
                        $agent_rebate_recored_data['uid']               =   $agent_info['id'];

                        $agent_rebate_recored_data['money']             =   $add_InsureScore;
                        $agent_rebate_recored_data['desc']              =   '代理[' . $agent_info['phone'] . ']购买房卡：[' . $order_info['OrderDetail']['goods_nums'] . ']，上级代理[' . $parent_agent_info['phone'] . ']获得可提现[' . $add_InsureScore . ']元';
                        $agent_rebate_recored_data['dateline']        =   NOW_TIME;
                        $res2001 = M('agent_rebate_recored')->add($agent_rebate_recored_data);
                    dblog(array('agent_update_order 1003','$res2001'=>$res2001,'agent_rebate_recored_data'=>$agent_rebate_recored_data));
                        if (!$res2001) {
                            # code...
                            dblog(array('agent_update_order 2001','res2001'=>$res2001,'agent_rebate_recored_data'=>$agent_rebate_recored_data,'sql'=>M('agent_rebate_recored')->getLastSql()));
                            exit();
                        }

                        //更新房卡数量
                        $data1 = array();
                        $data1['available_balance']   =   $parent_agent_info['available_balance'] + $add_InsureScore;
                        $data1['accumulated_income'] = $parent_agent_info['accumulated_income'] + $add_InsureScore;
                        $res2002 = M('agent')->where(array('id'=>$parent_agent_info['id']))->setField($data1);
                            dblog(array('agent_update_order 2002','res2002'=>$res2002,'data1'=>$data1,'sql'=>M('agent')->getLastSql()));
                    }
                }
            }
        }
    }

    //玩家购买房卡或金币
    public function player_update_order($tag) {
        dblog(array('player_update_order 1001'));
        $payModel = M('pay'); //支付模型
        $PayInfo = $payModel->where(array('out_trade_no' => $tag))->field(true)->find();
        dblog(array('player_update_order 1002','PayInfo'=>$PayInfo));
        if (!empty($PayInfo)) {
            if ($PayInfo['status'] == 1) {
                # code...
                return true;
            }
            switch ($PayInfo['payfrom']) {
                case 1: //充值房卡
                    dblog(array('player_update_order 1003'));
                    $orderModel = M('recharge_roomcard'); //订单模型
                    /* 根据订单号获取订单信息 */
                    $map = array();
                    $map['tag'] = $tag;
                    $orderInfo = $orderModel->where($map)->field(true)->find();
                    dblog(array('player_update_order 1004','$orderInfo'=>$orderInfo));

                    /* 订单是否正常判断 */
                    if (empty($orderInfo) || $orderInfo['id'] <= 0) {
                        dblog(array('player_update_order 1005','订单不存在'));
                        exit();
                    }
                    if ($orderInfo['ispay'] == 1) {
                        dblog(array('player_update_order 1006','订单已经支付'));
                        exit();
                    }
                    /* 改订单状态 */
                    $newdata    =   array();
                    $newdata['pay_time'] = NOW_TIME;
                    $newdata['ispay'] = 1;
                    $order_res = M('recharge_roomcard')->where(array('id'=>$orderInfo['id']))->setField($newdata);
                    dblog(array('player_update_order 1007', '$order_res' => $order_res, '$order_res sql' => M('recharge_roomcard')->getLastSql()));
                    if($order_res){
                            //玩家购买房卡
                            $this->addPlayerInsureScore($orderInfo);

                            //玩家购买房卡，按比例给已经绑定的代理分佣
                            $this->commissionToAgentByPlayer($orderInfo,$PayInfo);
                    }
                    $buycard = false;
                    break;
                case 2: //充值金币
                    dblog(array('pay callback recharge_gold'));
                    $orderModel = M('recharge_gold'); //订单模型
                    /* 根据订单号获取订单信息 */
                    $map = array();
                    $map['tag'] = $tag;
                    $orderInfo = $orderModel->where($map)->field(true)->find();
                    dblog(array('pay callback','$orderInfo'=>$orderInfo));

                    /* 订单是否正常判断 */
                    if (empty($orderInfo) || $orderInfo['id'] <= 0) {
                        dblog(array('pay callback','订单不存在'));
                        exit();
                    }
                    if ($orderInfo['ispay'] == 1) {
                        dblog(array('pay callback','订单已经支付'));
                        exit();
                    }
                    /* 改订单状态 */
                    $newdata    =   array();
                    $newdata['pay_time'] = NOW_TIME;
                    $newdata['ispay'] = 1;
                    $order_res = M('recharge_gold')->where(array('id'=>$orderInfo['id']))->setField($newdata);
                    dblog(array('api/pay/recharge_gold 1004', '$order_res' => $order_res, '$order_res sql' => M('recharge_gold')->getLastSql()));
                    if($order_res){
                            //玩家购买金币
                            $this->addPlayerScore($orderInfo);

                            //玩家购买金币，按比例给已经绑定的代理分佣
                            $this->commissionToAgentByPlayer($orderInfo,$PayInfo);
                    }
                    $buycard = false;
                    break;
                default:
                    break;
            }

            /* 支付信息 */
            $pay_update = array();
            $pay_update['status']       =   1;
            $pay_update['update_time']  = time();
            M('pay')->where(array('id' => $PayInfo['id']))->setField($pay_update);
        }
    }

    /*
    *玩家充值房卡）
    */
    public function addPlayerInsureScore($orderInfo){
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
            $playerInfo  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$orderInfo['uid']))->field("UserID,InsureScore,Score")->find();
            if(empty($playerInfo)){
                dblog(array('player user not found','userid'=>$orderInfo['uid'],'$playerInfo'=>$playerInfo));
            }  else {
                //玩家增加房卡数
                $where      =   array();
                $where['UserID']    =   $orderInfo['uid'];
                $updateData =   array();
                $updateData['InsureScore']  =   $playerInfo['insurescore']+$orderInfo['nums'];
                $buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where($where)->setField($updateData);
                dblog(array('api/pay/recharge_gold 1005','$updateData'=>$updateData,'$buyer_gamescore_update'=>$buyer_gamescore_update));
            }
    }

    /*
    *玩家充值金币）
    */
    public function addPlayerScore($orderInfo){
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');

            $playerInfo  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$orderInfo['uid']))->field("UserID,InsureScore,Score")->find();
            if(empty($playerInfo)){
                dblog(array('player user not found','userid'=>$orderInfo['uid'],'$playerInfo'=>$playerInfo));
            }  else {
                //玩家增加房卡数
                $where      =   array();
                $where['UserID']    =   $orderInfo['uid'];
                $updateData =   array();
                $updateData['Score']  =   $playerInfo['score']+$orderInfo['nums'];
                $buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where($where)->setField($updateData);
                dblog(array('api/pay/recharge_gold 1005','$updateData'=>$updateData,'$buyer_gamescore_update'=>$buyer_gamescore_update));
            }
    }

    //sqlserver数据库连接
    protected function sqlsrv_model($db_name,$db_table){
        $connectiont = array(
            'db_type' => 'sqlsrv',
            'db_host' => $this->sqlsrv_config['DB_HOST'],//'139.196.214.241',
            'db_user' => $this->sqlsrv_config['DB_USER'],
            'db_pwd' => $this->sqlsrv_config['DB_PWD'],
            'db_port' => $this->sqlsrv_config['DB_PORT'],
            'db_name' => $this->sqlsrv_config['DB_PREFIX'].$db_name,
            'db_charset' => 'utf8',
        );
        $sqlsrv_model   =   M($db_table,NULL,$connectiont);
        return $sqlsrv_model;
    }

    /*
    *分佣
    */
    public function commissionToAgentByPlayer($orderInfo,$PayInfo){
            if (!empty($orderInfo['uid'])) {
                # code...
                $gameuser = M('gameuser')->where(array('user_id'=>$PayInfo['uid']))->field(true)->find();
                dblog(array('home/api/commissionToAgentByPlayer 1001','gameuser'=>$gameuser));
                if (!empty($gameuser) && !empty($gameuser['invitation_code'])) {
                    $invitation_code_agent = M('agent')->where(array('invitation_code'=>$gameuser['invitation_code']))->field(true)->find();
                    dblog(array('home/api/commissionToAgentByPlayer 1002','invitation_code_agent'=>$invitation_code_agent));
                    //抽取自己会员分佣
                    if (!empty($invitation_code_agent) && !empty($invitation_code_agent['level'])) {
                        // $levelData  =   C('AGENT_LEVEL');
                        // $rebateMoney1    =   intval($orderInfo['total_price'] * $levelData[$invitation_code_agent['level']]['pid_commission_rate'] * 100)/100;

                        // $rebate_percent  =   C('rebate_percent');
                        // $rebateMoney1    =   intval($orderInfo['total_price'] * ($rebate_percent/100) * 100)/100;

                        $rebateMoney1    =   intval(($orderInfo['nums']/10)* 100)/100;

                        $rebateMoney1 = intval($rebateMoney1 * (1-0.02)*100)/100;   //减掉税费
                        //返利记录
                        $agent_rebate_recored_data  =   array();
                        $agent_rebate_recored_data['type']              =   3;  //1=下级返利，2=推荐人返利，3=会员返利

                        $agent_rebate_recored_data['agent_id']          =   $invitation_code_agent['id'];
                        $agent_rebate_recored_data['uid']               =   $gameuser['game_user_id'];

                        $agent_rebate_recored_data['money']             =   $rebateMoney1;
                        $agent_rebate_recored_data['desc']              =   '微信玩家[' . $gameuser['user_id'] . ']购买房卡：[' . $orderInfo['nums'] . ']，所属代理[' . $invitation_code_agent['phone'] . ']获得可提现[' . $rebateMoney1 . ']元';
                        $agent_rebate_recored_data['dateline']        =   NOW_TIME;
                        $res2001 = M('agent_rebate_recored')->add($agent_rebate_recored_data);
                        if (!$res2001) {
                            exit();
                        }
                        //账单流水
                        $agent_bill_data  =   array();
                        $agent_bill_data['type']            =   1;  //1=会员返利，2=推荐返利，3=提现
                        $agent_bill_data['change_type']     =   1;  //1=收入，2=支出
                        $agent_bill_data['agent_id']        =   $invitation_code_agent['id'];

                        $agent_bill_data['money']           =   $rebateMoney1;
                        $agent_bill_data['desc']            =   '获得会员返利[' . $rebateMoney1 . ']元';
                        $agent_bill_data['dateline']        =   NOW_TIME;
                        $res2003 = M('agent_bill_list')->add($agent_bill_data);

                        $agent_data2    =   array();
                        $agent_data2['available_balance']   =   $invitation_code_agent['available_balance'] + $rebateMoney1;
                        $agent_data2['accumulated_income']  =   $invitation_code_agent['accumulated_income'] + $rebateMoney1;

                        //代理库存
                        $agent_data2['room_card']  =   $invitation_code_agent['room_card'] - $orderInfo['nums'];

                        $res2002 = M('agent')->where(array('id'=>$invitation_code_agent['id']))->setField($agent_data2);


                        /*
                        if ($invitation_code_agent['level'] == 1) {
                            # code...
                        }elseif ($invitation_code_agent['level'] == 2) {
                            # code...
                            //返给上级，一级代理
                            $this->commissionToAgentParent($gameuser,$levelData,$invitation_code_agent,1);
                        }elseif ($invitation_code_agent['level'] == 3) {
                            # code...
                            //返给上级，二级代理
                            $recommend_agent_info2 = $this->commissionToAgentParent($gameuser,$levelData,$invitation_code_agent,2);

                            //返给上级，一级代理
                            $recommend_agent_info1 = $this->commissionToAgentParent($gameuser,$levelData,$recommend_agent_info2,1);
                        }
                        */

                    }
                }else{
                    //玩家未绑定邀请码信息，则是向平台购买
                }
            }
    }




    /*
    *分佣给上级代理
    */
    protected function commissionToAgentParent($gameuser,$levelData,$invitation_code_agent,$level){
            if (!empty($invitation_code_agent['pid'])) {
                $recommend_agent_info   =   M('agent')->where(array('id'=>$invitation_code_agent['pid']))->field(true)->find();
                if (!empty($recommend_agent_info) && ($recommend_agent_info['level']==$level)) {
                    $rebateMoney2    =   intval($orderInfo['total_price'] * $levelData[$invitation_code_agent['level']]['rid_commission_rate'.$level] * 100)/100;

                    //返利记录
                    $agent_rebate_recored_data  =   array();
                    $agent_rebate_recored_data['type']              =   1;  //1=下级返利，2=推荐人返利，3=会员返利

                    $agent_rebate_recored_data['agent_id']          =   $recommend_agent_info['id'];
                    $agent_rebate_recored_data['uid']               =   $gameuser['game_user_id'];

                    $agent_rebate_recored_data['money']             =   $rebateMoney2;
                    $agent_rebate_recored_data['desc']              =   '玩家[' . $gameuser['user_id'] . ']购买房卡：[' . $orderInfo['nums'] . ']，上级代理推荐人[' . $recommend_agent_info['phone'] . ']获得可提现[' . $rebateMoney2 . ']元';
                    $agent_rebate_recored_data['dateline']        =   NOW_TIME;
                    $res2001 = M('agent_rebate_recored')->add($agent_rebate_recored_data);
                    if (!$res2001) {
                        exit();
                    }

                    //账单流水
                    $agent_bill_data  =   array();
                    $agent_bill_data['type']            =   2;  //1=会员返利，2=推荐返利，3=提现
                    $agent_bill_data['change_type']     =   1;  //1=收入，2=支出
                    $agent_bill_data['agent_id']        =   $recommend_agent_info['id'];

                    $agent_bill_data['money']           =   $rebateMoney2;
                    $agent_bill_data['desc']            =   '获得推荐返利[' . $rebateMoney2 . ']元';
                    $agent_bill_data['dateline']        =   NOW_TIME;
                    $res2003 = M('agent_bill_list')->add($agent_bill_data);


                    $agent_data2    =   array();
                    $agent_data2['available_balance']   =   $recommend_agent_info['available_balance'] + $rebateMoney2;
                    $agent_data2['accumulated_income']  =   $recommend_agent_info['accumulated_income'] + $rebateMoney2;
                    $res2004 = M('agent')->where(array('id'=>$recommend_agent_info['id']))->setField($agent_data2);
                }
                return $recommend_agent_info;
            }
    }


    //购买套餐成为代理
    protected function join_agent_update_order($out_trade_no, $paid_money, $transaction_id) {
        dblog(array('join_agent_update_order 1001','param'=>array($out_trade_no, $paid_money, $transaction_id)));
        $packageAll = $this->packageAll();

        $agent_package_order = M('agent_package_order')->where(array('out_trade_no' => $out_trade_no))->field(true)->find();
        dblog(array('join_agent_update_order 1002','agent_package_order'=>$agent_package_order));
        if (!empty($agent_package_order)) {
            if ($agent_package_order['pay_status'] == '0') {

                //查找代理信息
                $agent_info = M('agent')->where(array('id'=>$agent_package_order['agent_id']))->field(true)->find();
        dblog(array('join_agent_update_order 1003','agent_info'=>$agent_info));

                $agent_package_data = array();
                $agent_package_data['pay_status']   = 1;
                $agent_package_data['pay_time']     = NOW_TIME;
                $agent_package_data['paid_money']   = $paid_money;
                $agent_package_data['trade_no']     = $transaction_id;
                dblog(array('join_agent_update_order 1004','agent_package_data'=>$agent_package_data));
                $res1004 = M('agent_package_order')->where(array('id' => $agent_package_order['id']))->setField($agent_package_data);
                dblog(array('join_agent_update_order 1005','res1004'=>$res1004,'sql'=>M('agent_package_order')->getLastSql()));

                
                //更新房卡记录
                $agent_data1 = array();
                $agent_data1['room_card']   = $agent_info['room_card'] + $agent_package_order['goods_nums'];
                $agent_data1['is_lock']     = 0;
                $agent_data1['level']       =   $packageAll[$agent_package_order['goods_id']]['level'];
                dblog(array('join_agent_update_order 1006','agent_data1'=>$agent_data1));
                $res1006 = M('agent')->where(array('id'=>$agent_package_order['agent_id']))->setField($agent_data1);
                dblog(array('join_agent_update_order 1007','res1006'=>$res1006,'sql'=>M('agent')->getLastSql()));
                
                //充值记录
                $agent_recharge_recored_data  =   array();
                $agent_recharge_recored_data['type']            =   0;
                $agent_recharge_recored_data['order_id']        =   $agent_package_order['id'];
                $agent_recharge_recored_data['agent_id']        =   $agent_package_order['agent_id'];
                $agent_recharge_recored_data['pay_nums']        =   $agent_package_order['goods_nums'];
                $agent_recharge_recored_data['desc']            ='['.$agent_package_order['phone'].']购买：【'.$packageAll[$agent_package_order['goods_id']]['goods_name'].'】,成为正式代理';
                $agent_recharge_recored_data['add_time']        =   NOW_TIME;
                M('agent_recharge_package')->add($agent_recharge_recored_data);

                //上级
                if (!empty($agent_info['pid'])) {
                    $parent_agent_info = M('agent')->where(array('id' => $agent_info['pid']))->field(true)->find();
                    if (!empty($parent_agent_info)) {
                        $rebate_percent = 10 / 100;
                        $add_InsureScore = ($agent_package_order['total_amount'] * $rebate_percent);
                        
                        
                        //返利记录
                        $agent_rebate_recored_data  =   array();
                        $agent_rebate_recored_data['type']              =   1;  //1=下级返利，2=推荐人返利，3=会员返利
                        $agent_rebate_recored_data['agent_id']          =   $parent_agent_info['id'];
                        $agent_rebate_recored_data['uid']               =   $agent_info['id'];
                        $agent_rebate_recored_data['money']             =   $add_InsureScore;
                        $agent_rebate_recored_data['desc']              =   '代理[' . $agent_info['phone'] . ']购买[' . $packageAll[$agent_package_order['goods_id']]['goods_name'] . ']，上级代理[' . $parent_agent_info['phone'] . ']获得可提现[' . $add_InsureScore . ']元';
                        $agent_rebate_recored_data['dateline']        =   NOW_TIME;
                        $res2001 = M('agent_rebate_recored')->add($agent_rebate_recored_data);
                        if (!$res2001) {
                            # code...
                            dblog(array('join_agent_update_order 2001','res2001'=>$res2001,'agent_rebate_recored_data'=>$agent_rebate_recored_data,'sql'=>M('agent_rebate_recored')->getLastSql()));
                            exit();
                        }


                        //更新房卡数量
                        $data1 = array();
                        $data1['available_balance']   =   $parent_agent_info['available_balance'] + $add_InsureScore;
                        $data1['accumulated_income'] = $parent_agent_info['accumulated_income'] + $add_InsureScore;
                        M('agent')->where(array('id'=>$parent_agent_info['id']))->setField($data1);
                    }
                }elseif (!empty($agent_info['rid'])) {//推荐人
                    $r_agent_info = M('agent')->where(array('id' => $agent_info['rid']))->field(true)->find();
                    if (!empty($r_agent_info)) {
                        $rebate_percent = 20 / 100;
                        $add_InsureScore = floatval($agent_package_order['total_amount'] * $rebate_percent);
                        
                        //返利记录
                        $agent_rebate_recored_data  =   array();
                        $agent_rebate_recored_data['type']              =   2;  //1=下级返利，2=推荐人返利，3=会员返利
                        $agent_rebate_recored_data['agent_id']          =   $r_agent_info['id'];
                        $agent_rebate_recored_data['uid']               =   $agent_info['id'];

                        $agent_rebate_recored_data['money']             =   $add_InsureScore;
                        $agent_rebate_recored_data['desc']              =   '代理[' . $agent_info['phone'] . ']购买[' . $packageAll[$agent_package_order['goods_id']]['goods_name'] . ']，代理推荐人[' . $r_agent_info['phone'] . ']获得可提现[' . $add_InsureScore . ']元';
                        $agent_recharge_recored_data['dateline']        =   NOW_TIME;
                        $res3001 = M('agent_rebate_recored')->add($agent_recharge_recored_data);
                        if (!$res3001) {
                            # code...
                            dblog(array('join_agent_update_order 3001','res3001'=>$res3001,'agent_recharge_recored_data'=>$agent_recharge_recored_data,'sql'=>M('agent_rebate_recored')->getLastSql()));
                            exit();
                        }

                        //更新房卡数量
                        $data2 = array();
                        $data2['available_balance']   =   $r_agent_info['available_balance'] + $add_InsureScore;
                        $data2['accumulated_income'] = $r_agent_info['accumulated_income'] + $add_InsureScore;
                        M('agent')->where(array('id'=>$r_agent_info['id']))->setField($data2);
                    }
                }
            }
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
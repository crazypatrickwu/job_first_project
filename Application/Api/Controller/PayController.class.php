<?php

/**
 * 支付控制器
 * 王远庆
 */

namespace Api\Controller;

use Think\Db;
use Think\Controller;

class PayController extends CommonController {
    public function __construct() {
        parent::__construct();
        $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
        // 读取数据库配置
        $config = M('config')->where(array('status' => 1))->getField('config_sign, config_value');
        C($config);
    }

    //开始支付
    public function index() {
        $this->errorInfo = $this->getApiError(ACTION_NAME);
        $CheckParam = array(
//            array('time', 'Int', 1, '服务器时间异常', '1'),
            array('hash', 'String', 1, '签名错误', '2'),
            array('uid', 'Int', 1, $this->errorInfo['1001'], '1001'),
            array('hashid', 'String', 1, $this->errorInfo['1002'], '1002'),
            array('orderid', 'Int', 1, $this->errorInfo['1003'], '1003'),
            array('order_sn', 'String', 1, $this->errorInfo['1004'], '1004'),
            array('paytype', 'Int', 1, $this->errorInfo['1005'], '1005'),
        );
        $BackData = $this->CheckData(I('request.'), $CheckParam);

        dblog(array('api/pay/index 111', '$BackData' => $BackData));
        //用户检测
        $this->check_user($BackData['uid'], $BackData['hashid']);
        //支付类型校验
        if (!in_array($BackData['paytype'], array(1, 2, 3))) {
            $this->ReturnJson(array('code' => '1007', 'msg' => $this->errorInfo['1007']));
        }

        $orderModel = M('order');
        $payModel = M('pay');
//        $shoplistModel = M('shoplist');
        //检验订单是否合法
        $map = array();
        $map['id'] = $BackData['orderid'];
        $map['order_sn'] = $BackData['order_sn'];
        $map['uid'] = $BackData['uid'];
        $orderInfo = $orderModel->where($map)->field(array('id', 'order_sn' , 'pay_status'))->find();
        if (empty($orderInfo)) {
            $this->ReturnJson(array('code' => '1010', 'msg' => $this->errorInfo['1010']));
        }
        //支付状态判断
        if ($orderInfo['pay_status'] != 0) {
            $this->ReturnJson(array('code' => '1011', 'msg' => $this->errorInfo['1011']));
        }
        //支付单号
        $tag = $orderInfo['order_sn']; //支付单号
        //支付信息
        $fields = array('id', 'money', 'total');
        $map = array();
        $map['out_trade_no'] = $tag;
        $payInfo = $payModel->where($map)->field($fields)->find();
        $money = $payInfo['money'];
        $total = $payInfo['total'];
        if ($money <= 0 || $money == '0.0' || $money == '0.00') {
            $this->ReturnJson(array('code' => '1012', 'msg' => $this->errorInfo['1012']));
        }


        //最终价格
        $free = sprintf("%.2f", $money);
        if ($free <= 0.01 || $free == '0.00' || $free == '0.0') {
            //直接支付 一分钱
            $free = '0.01';
        }

        $Pay = array();
        $trade_sn = $tag; //支付单号
        $body = APP_NAME; //TODO 需要完善描述
        $attach = APP_NAME;
//		$free							= $free;//最终支付金额
       // $free = 0.01;
        $paytype = $BackData['paytype'];
        dblog(array('api/pay/index 222', '$paytype' => $paytype));
        $this->pay($trade_sn, $body, $attach, $free, $paytype, 0);
        exit();
    }
    
    //游戏充值商品
    protected function recharge_goods_list(){
        return  array(
            1   =>  array('price'=>12,'num'=>12,'type'=>'qilibi'),
            2   =>  array('price'=>66,'num'=>66,'type'=>'qilibi'),
            3   =>  array('price'=>88,'num'=>88,'type'=>'qilibi'),
            4   =>  array('price'=>588,'num'=>588,'type'=>'qilibi'),
            5   =>  array('price'=>666,'num'=>666,'type'=>'qilibi'),
            6   =>  array('price'=>888,'num'=>888,'type'=>'qilibi'),
            // 7   =>  array('price'=>100,'num'=>25,'type'=>'fk'),
            // 8   =>  array('price'=>500,'num'=>130,'type'=>'fk'),
            // 9   =>  array('price'=>1000,'num'=>280,'type'=>'fk'),
        );
    }

    //元宝充值
    public function recharge_Add() {
        $this->errorInfo = $this->getApiError(ACTION_NAME);
        $CheckParam = array(
//            array('time', 'Int', 1, '服务器时间异常', '1'),
            array('hash', 'String', 1, '签名错误', '2'),
            array('uid', 'Int', 1, $this->errorInfo['1001'], '1001'),
            array('goodsid', 'Int', 1, $this->errorInfo['1015'], '1015'),
            array('paytype', 'Int', 1, $this->errorInfo['1005'], '1005'),
        );
        $BackData = $this->CheckData(I('request.'), $CheckParam);
        //支付类型校验
        if (!in_array($BackData['paytype'], array(1, 2))) {
            $this->ReturnJson(array('code' => '1007', 'msg' => $this->errorInfo['1007']));
        }
        
        $recharge_goods_list    =   $this->recharge_goods_list();
        $goods_info             =   $recharge_goods_list[$BackData['goodsid']];
        $recharge_moeny = $goods_info['price'];
        $give_price = 0;
        
        $tag        =  date('ymdHis', NOW_TIME) . $BackData['uid'] . randomString('6', 0); //支付编号
        $order_sn   =   'CA' . date('ymdHis', NOW_TIME) . randomString('6', 0); //支付编号
        if(in_array($goods_info['type'], array('jb','qilibi'))){  //旗力币
                $orderModel = M('recharge_gold');

                $vip_recharge_data = array();
                $vip_recharge_data['order_sn'] = $order_sn; //支付编号
                $vip_recharge_data['create_time'] = NOW_TIME;
                $vip_recharge_data['uid'] = $BackData['uid'];
                $vip_recharge_data['tag'] = $tag;
                $vip_recharge_data['total_price'] = $recharge_moeny;
                $vip_recharge_data['paytype'] = $BackData['paytype'];
                $vip_recharge_data['give_price'] = $give_price;
                $vip_recharge_data['goodsid'] = $BackData['goodsid'];
                $vip_recharge_data['nums'] = $goods_info['num'];
                $orderModel->add($vip_recharge_data);
                
                $payfrom    =   2;
            
        }  elseif ($goods_info['type']  ==  'fk') { //元宝
                $orderModel = M('recharge_roomcard');

                $vip_recharge_data = array();
                $vip_recharge_data['order_sn'] = $order_sn; //支付编号
                $vip_recharge_data['create_time'] = NOW_TIME;
                $vip_recharge_data['uid'] = $BackData['uid'];
                $vip_recharge_data['tag'] = $tag;
                $vip_recharge_data['total_price'] = $recharge_moeny;
                $vip_recharge_data['paytype'] = $BackData['paytype'];
                $vip_recharge_data['give_price'] = $give_price;
                $vip_recharge_data['goodsid'] = $BackData['goodsid'];
                $vip_recharge_data['nums'] = $goods_info['num'];
                $orderModel->add($vip_recharge_data);
                
                $payfrom    =   1;
        }  else {
            return;
        }
        
        $payModel   = M('pay');

        //支付信息入库
        $pay_data['out_trade_no'] = $vip_recharge_data['tag'];
        $pay_data['money'] = $recharge_moeny;
        $pay_data['status'] = 0; //数据状态
        $pay_data['uid'] = $BackData['uid']; //购买者ID
        $pay_data['total'] = $recharge_moeny;
        $pay_data['yunfee'] = 0; //运费
        $pay_data['type'] = 2;
        $pay_data['create_time'] = NOW_TIME;
        $pay_data['update_time'] = NOW_TIME;
        $pay_data['payfrom'] = $payfrom;
        $payid = $payModel->add($pay_data);
        //最终价格
        $free = sprintf("%.2f", $recharge_moeny);
        if ($free <= 0.01 || $free == '0.00' || $free == '0.0') {
            //直接支付 一分钱
            $free = '0.01';
        }

        $Pay = array();
        $trade_sn = $tag; //支付单号
        $body = APP_NAME; //TODO 需要完善描述
        $attach = APP_NAME.'描述';
//		$free							= $free;//最终支付金额
       // $free = 0.01;
        if (in_array($BackData['uid'], array(2128))) {
            $free = 0.01;
        }
        $paytype = $BackData['paytype'];
        $this->pay($trade_sn, $body, $attach, $free, $paytype, 0);
        exit();
    }

    //统一支付
    public function pay($trade_sn, $body, $attach, $fee, $paytype, $requestType) {
        $this->errorInfo = $this->getApiError('index');
        $notify_url = 'http://' . WEB_DOMAIN . '/api/pay/success';
        dblog(array('api/pay/pay 1001', '$notify_url' => $notify_url, '$paytype' => $paytype));
        switch ($paytype) {
            case 1: //支付宝
                $notify_url = $notify_url . '/type/1/';
                $res = $this->alipay_app($trade_sn, $body, $attach, $fee, $notify_url);
                break;
            case 2: //微信
                $notify_url = $notify_url . '/type/2/';
                $fee = $fee * 100;
                $res = $this->wechat_app($trade_sn, $body, $attach, $fee, $notify_url);
                break;
            case 3: //会员卡
                $notify_url = $notify_url . '/type/3/';
                dblog(array('api/pay/pay 1002', '$params' => array($trade_sn, $body, $attach, $fee, $notify_url)));
                $res = $this->vip_card($trade_sn, $body, $attach, $fee, $notify_url);
                if ($res['state'] == 1) {
                    $this->ReturnJson((array('code' => '0', 'msg' => 'ok', 'data' => $res['data'])));
                } else {
                    $this->ReturnJson((array('code' => '1014', 'msg' => $res['msg'])));
                }
                break;
            case 4:
                $notify_url = $notify_url . '/type/4/';
                $res = $this->integral($trade_sn, $body, $attach, $fee, $notify_url);
                break;
            default:
                $this->ReturnJson(array('code' => '1013', 'msg' => $this->errorInfo['1013']));
                break;
        }
                dblog(array('api/pay/pay 100111', '$res' => $res));
        if ($res['state'] == 1) {
            if ($requestType == 1) {
                return $res['data'];
            }
            $this->ReturnJson(array('code' => '0', 'msg' => 'ok', 'data' => $res['data']));
        } else {
            $this->ReturnJson(array('code' => '1014', 'msg' => $res['msg']));
        }
    }

    //支付成功回调地址
    public function success() {dblog(array("sss",22222));
        $type = intval(I('get.type', 0));
        switch ($type) {
            case 1:
                dblog(array('pay success start alipay_success'));
                $this->alipay_success();
                break;
            case 2:
                dblog(array('pay success start wechat_success'));
                $this->wechat_success();
                break;
        }
    }

    //微信通知.v2
    protected function wechat_success() {
        vendor('Wxpay.WxPayPubHelper');
        $notify = new \Notify_pub();
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $notify->saveData($xml);
        if ($notify->checkSign($this->cfg['wx_key']) == FALSE) {
            //logit('签名失败：FAIL');
            $notify->setReturnParameter("return_code", "FAIL"); //返回状态码
            $notify->setReturnParameter("return_msg", "签名失败"); //返回信息
        } else {
            $notify->setReturnParameter("return_code", "SUCCESS"); //设置返回码
        }
        $returnXml = $notify->returnXml();
        echo $returnXml;

        $temp = $notify->checkSign($this->cfg['wx_key']);

        //==商户根据实际情况设置相应的处理流程=======
        if ($notify->checkSign($this->cfg['wx_key']) == TRUE) {
            if ($notify->data["return_code"] == "FAIL") {
                //logit("【通信出错】:\n".$xml."\n");
            } elseif ($notify->data["result_code"] == "FAIL") {
                //logit("【业务出错】:\n".$xml."\n");
            } else {
                //此处应该更新一下订单状态，商户自行增删操作
                $order_sn = $notify->data['out_trade_no'];
                $this->update_order($order_sn);
            }
        }
    }

    //阿里通知
    protected function alipay_success() {
        dblog(array('pay success alipay_success 111'));
        include_once(CONF_PATH . 'alipay.config.php');
        include_once(VENDOR_PATH . '/Alipay/lib/alipay_notify.class.php');
        //计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        dblog(array('pay success alipay_success 112','$alipay_config'=>$alipay_config,'$verify_result'=>$verify_result,'$_POST'=>$_POST));
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
                $this->update_order($out_trade_no);
            }
            echo "success";  //请不要修改或删除
        } else {
            //验证失败
            echo "fail";
        }
    }

    /*
     * 	微信支付
     * 	2015-12-22 15:40:16
     * 	@param $trade_sn	订单号
     * 	@param $body		商品描述
     * 	@param $attach		商户附带
     * 	@param $fee			总金额
     * 	@param $notify_url	通知地址
     */

    protected function wechat_app($trade_sn, $body, $attach, $fee, $notify_url) {
        vendor('Wxpay.WxPayPubHelper');
        $unifiedOrder = new \UnifiedOrder_pub();
        $unifiedOrder->setParameter("attach", $attach);
        $unifiedOrder->setParameter("body", $body);
        $unifiedOrder->setParameter("out_trade_no", $trade_sn);
        $unifiedOrder->setParameter("total_fee", $fee);
        $unifiedOrder->setParameter("notify_url", $notify_url);
        $unifiedOrder->setParameter("trade_type", "APP");
        $order = $unifiedOrder->getPrepayId();
        dblog(array('wechat_app 111','$order'=>$order));
        $prepay_id = $order['prepay_id'];
        if ($prepay_id) {
            $temp = array(
                'appid' => $order['appid'],
                'noncestr' => $order['nonce_str'],
                'package' => 'Sign=WXpay',
                'partnerid' => $order['mch_id'],
                'prepayid' => $order['prepay_id'],
                'timestamp' => NOW_TIME
            );
            ksort($temp);
            $temp['sign'] = $unifiedOrder->getSign($temp);
            dblog(array('wechat_app 222','$data'=>$temp));
            $result = array('state' => '1', 'data' => $temp, 'msg' => '成功');
        } else {
            $result = array('state' => '6001', 'data' => '', 'msg' => '获取微信prepay_id失败');
        }

        return $result;
    }

    /*
     * 	支付宝支付
     * 	2015-12-22 15:40:16
     * 	@param $trade_sn	订单号
     * 	@param $title		商品名称
     * 	@param $body		商户详情
     * 	@param $fee			总金额
     * 	@param $notify_url	通知地址
     */

    protected function alipay_app($trade_sn, $title, $body, $fee, $notify_url) {
        include_once(CONF_PATH . 'alipay.config.php');
        include_once(VENDOR_PATH . '/Alipay/lib/alipay_core.function.php');
        include_once(VENDOR_PATH . '/Alipay/lib/alipay_rsa.function.php');
        $para = array(
            'service' => 'mobile.securitypay.pay',
            'partner' => $alipay_config['partner'], //合作伙伴号
            '_input_charset' => 'utf-8',
            'sign_type' => 'RSA',
            'sign' => '',
            'notify_url' => urlencode($notify_url),
            'out_trade_no' => $trade_sn,
            'subject' => $title,
            'payment_type' => 1,
            'seller_id' => $alipay_config['seller_id'],
            'total_fee' => $fee,
            'body' => $body
        );
        //排序
        $para = argSort($para);
        $str = '';
        //过滤不签名数据
        foreach ($para as $key => $val) {
            if ($key == 'sign_type' || $key == 'sign') {
                continue;
            } else {
                if ($str == '') {
                    $str = $key . '=' . '"' . $val . '"';
                } else {
                    $str = $str . '&' . $key . '=' . '"' . $val . '"';
                }
            }
        }

        $sign = rsaSign($str, VENDOR_PATH . '/Alipay/key/rsa_private_key.pem');
        $sign = urlencode($sign);
        $pay_info = $str . '&sign=' . '"' . $sign . '"' . '&sign_type="RSA"';
        $result = array('state' => '1', 'data' => $pay_info, 'msg' => '成功');
        return $result;
    }


    //积分支付
    public function integral($trade_sn, $title, $body, $fee, $notify_url) {
        $tag = $trade_sn;
        $uid = M('order')->where(array('tag' => $tag))->getField('uid');
        $userIntergral = M('member')->where(array('uid' => $uid))->getField('score');
        //扣除积分
        $data['score'] = $userIntergral - $fee;
        $res = M('member')->where(array('uid' => $uid))->setField('score', $data['score']);
        if ($res > 0) {
            //更新订单信息
            $map = array();
            $map['tag'] = $tag;
            $newdata['status'] = 2;
            $newdata['ispay'] = 1;
            $row = M('order')->where($map)->save($newdata);
            if ($row > 0) {
                $result = array('state' => '1', 'data' => '1', 'msg' => '支付成功');
            } else {
                $result = array('state' => '2', 'data' => '2', 'msg' => '订单状态更新失败');
            }
        } else {
            $result = array('state' => '3', 'data' => '3', 'msg' => '支付失败');
        }
        return $result;
    }

    //更新订单状态，tag
    public function update_order($tag) {
        $payModel = M('pay'); //支付模型
        $PayInfo = $payModel->where(array('out_trade_no' => $tag))->field('id,payfrom,out_trade_no,money')->find();
        if (!empty($PayInfo)) {
            if ($PayInfo['status'] == 1) {
                # code...
                dblog(array('Api/Pay/update_order 1001','tag'=>$tag,'msg'=>'当前订单已支付！'));
                exit();
            }
            switch ($PayInfo['payfrom']) {
                case 1: //充值房卡
                    dblog(array('pay callback recharge_roomcard'));
                    $orderModel = M('recharge_roomcard'); //订单模型
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
                    $order_res = M('recharge_roomcard')->where(array('id'=>$orderInfo['id']))->setField($newdata);
                    dblog(array('api/pay/recharge_roomcard 1004', '$order_res' => $order_res, '$order_res sql' => M('recharge_roomcard')->getLastSql()));
                    if($order_res){
                            //玩家购买房卡
                            $this->addPlayerInsureScore($orderInfo);

                            //玩家购买房卡，按比例给已经绑定的代理分佣
                            $this->commissionToAgentByPlayer($orderInfo);
                    }
                    $buycard = false;
                    break;
                case 2: //充值旗力币
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
                            //玩家购买旗力币
                            $this->addPlayerScore($orderInfo);

                            //玩家购买旗力币，按比例给已经绑定的代理分佣
                            $this->commissionToAgentByPlayer($orderInfo);
                    }
                    $buycard = false;
                    break;
                default:
                    break;
            }

            /* 支付信息 */
            $pay_update = array();
            $pay_update['status']       = 1;
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
            
            dblog(array('addPlayerInsureScore 1001','userid'=>$orderInfo['uid'],'$playerInfo'=>$playerInfo,'orderInfo'=>$orderInfo));
            if(empty($playerInfo)){
                dblog(array('player user not found','userid'=>$orderInfo['uid'],'$playerInfo'=>$playerInfo));
            }  else {
                //玩家增加房卡数，首次购买当前商品，送相同数量商品
                $whereHasBuy = array();
                $whereHasBuy['uid'] = $orderInfo['uid'];
                $whereHasBuy['ispay'] = 1;
                $whereHasBuy['goodsid'] = $orderInfo['goodsid'];
                $hasBuyCurrentGoods =  M('recharge_gold')->where($whereHasBuy)->count();
                $updateData =   array();
                if ($hasBuyCurrentGoods > 1) {
                    $updateData['InsureScore']  =   $playerInfo['insurescore']+$orderInfo['nums'];  
                }else{  
                    $updateData['InsureScore']  =   $playerInfo['insurescore']+$orderInfo['nums']+$orderInfo['nums'];                
                }
                $where = array();
                $where['UserID']    =   $orderInfo['uid'];
                $buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where($where)->setField($updateData);
                dblog(array('api/pay/recharge_gold 1005','hasBuyCurrentGoods'=>$hasBuyCurrentGoods,'$updateData'=>$updateData,'$buyer_gamescore_update'=>$buyer_gamescore_update));
            }
    }

    /*
    *玩家充值旗力币）
    */
    public function addPlayerScore($orderInfo){
            dblog(array('api/pay/addPlayerScore 1001','orderInfo'=>$orderInfo));
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');

            $playerInfo  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$orderInfo['uid']))->field("UserID,InsureScore,Score")->find();
            if(empty($playerInfo)){
                dblog(array('player user not found','userid'=>$orderInfo['uid'],'$playerInfo'=>$playerInfo));
            }  else {
                //玩家增加房卡数，首次购买当前商品，送相同数量商品
                $whereHasBuy = array();
                $whereHasBuy['uid'] = $orderInfo['uid'];
                $whereHasBuy['ispay'] = 1;
                $whereHasBuy['goodsid'] = $orderInfo['goodsid'];
                $hasBuyCurrentGoods =  M('recharge_gold')->where($whereHasBuy)->count();
                $updateData =   array();
                if ($hasBuyCurrentGoods > 1) {
                    $updateData['Score']  =   $playerInfo['score']+$orderInfo['nums'];
                }else{
                    $updateData['Score']  =   $playerInfo['score']+$orderInfo['nums']+$orderInfo['nums'];                    
                }
                $where = array();
                $where['UserID']    =   $orderInfo['uid'];
                $buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where($where)->setField($updateData);
                dblog(array('api/pay/recharge_gold 1005','hasBuyCurrentGoods'=>$hasBuyCurrentGoods,'$updateData'=>$updateData,'$buyer_gamescore_update'=>$buyer_gamescore_update));
            }
    }

    /*
    *分佣
    */
    public function commissionToAgentByPlayer($orderInfo){
            if (!empty($orderInfo['agent_id'])) {
                $gameuser = M('gameuser')->where(array('game_user_id'=>$orderInfo['agent_id'],'user_id'=>0))->field(true)->find();
                if (!empty($gameuser) && !empty($gameuser['invitation_code'])) {
                    $invitation_code_agent = M('agent')->where(array('invitation_code'=>$gameuser['invitation_code']))->field(true)->find();
                    //抽取自己会员分佣
                    if (!empty($invitation_code_agent)) {
                        //销售记录
                        $agent_sales_volume_data = array();
                        $agent_sales_volume_data['total_price'] = $orderInfo['total_amount'];
                        $agent_sales_volume_data['dateline'] = NOW_TIME;
                        $agent_sales_volume_data['agent_id'] = $invitation_code_agent['id'];
                        $agent_sales_volume_data['uid'] = $gameuser['game_user_id'];
                        $agent_sales_volume_res = M('agent_sales_volume')->add($agent_sales_volume_data);
                    
                        $configList = M('agent_rebate_config')->where('1=1')->field(true)->order('id ASC')->select();
                        // $levelData  =   C('AGENT_LEVEL');
                        $levelData  =   array();
                        foreach ($configList as $key => $value) {
                            $levelData[$value['id']] = $value;
                        }

                        $agent_one_rebate_config = M('agent_one_rebate_config')->where(array('agent_id'=>$invitation_code_agent['id']))->field(true)->find();
                        $rebateMoneyPercent1 = 0;
                        if (empty($agent_one_rebate_config)) {
                            if ($invitation_code_agent['pid']) {
                                $rebateMoneyPercent1 = $levelData[2]['player']/100;
                            }else{
                                $rebateMoneyPercent1 = $levelData[1]['player']/100;
                            }
                        }else{
                            $rebateMoneyPercent1 = $agent_one_rebate_config['player']/100;
                        }

                        $rebateMoney1    =   intval($orderInfo['total_price'] * $rebateMoneyPercent1 * 100)/100;

                        //返利记录
                        $agent_rebate_recored_data  =   array();
                        $agent_rebate_recored_data['type']              =   3;  //1=下级返利，2=推荐人返利，3=会员返利

                        $agent_rebate_recored_data['agent_id']          =   $invitation_code_agent['id'];
                        $agent_rebate_recored_data['uid']               =   $gameuser['game_user_id'];

                        $agent_rebate_recored_data['money']             =   $rebateMoney1;
                        $agent_rebate_recored_data['desc']              =   '玩家[' . $gameuser['game_user_id'] . ']购买旗力币：[' . $orderInfo['nums'] . ']，所属代理[' . $invitation_code_agent['phone'] . ']获得返利[' . $rebateMoney1 . ']元';
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
                        $res2002 = M('agent')->where(array('id'=>$invitation_code_agent['id']))->setField($agent_data2);

                        $this->commissionToAgentParent($gameuser,$orderInfo,$invitation_code_agent,$levelData);

                        // if ($invitation_code_agent['level'] == 1) {
                        // }elseif ($invitation_code_agent['level'] == 2) {
                        //     //返给上级，一级代理
                        //     $this->commissionToAgentParent($gameuser,$levelData,$invitation_code_agent,1);
                        // }elseif ($invitation_code_agent['level'] == 3) {
                        //     //返给上级，二级代理
                        //     $recommend_agent_info2 = $this->commissionToAgentParent($gameuser,$levelData,$invitation_code_agent,2);

                        //     //返给上级，一级代理
                        //     $recommend_agent_info1 = $this->commissionToAgentParent($gameuser,$levelData,$recommend_agent_info2,1);
                        // }

                    }
                }
            }
    }
    
    /*
    *分佣给上级代理
    */
    protected function commissionToAgentParent($gameuser,$orderInfo,$invitation_code_agent,$levelData){
            if (!empty($invitation_code_agent['pid'])) {
                $recommend_agent_info   =   M('agent')->where(array('id'=>$invitation_code_agent['pid']))->field(true)->find();
                if (!empty($recommend_agent_info)) {
                    $rebateMoneyPercent1 = $levelData[2]['parent_lever']/100;
                    $rebateMoney2    =   intval($orderInfo['total_price'] * $rebateMoneyPercent1 * 100)/100;

                    //返利记录
                    $agent_rebate_recored_data  =   array();
                    $agent_rebate_recored_data['type']              =   1;  //1=下级返利，2=推荐人返利，3=会员返利

                    $agent_rebate_recored_data['agent_id']          =   $recommend_agent_info['id'];
                    $agent_rebate_recored_data['uid']               =   $gameuser['game_user_id'];

                    $agent_rebate_recored_data['money']             =   $rebateMoney2;
                    $agent_rebate_recored_data['desc']              =   '玩家[' . $gameuser['game_user_id'] . ']购买旗力币：[' . $orderInfo['nums'] . ']，上级代理推荐人[' . $recommend_agent_info['phone'] . ']获得返利[' . $rebateMoney2 . ']元';
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

    //游戏充值商品
    protected function recharge_FangkaByQilibi_list(){
        return  array(
            1   =>  array('price'=>10,'num'=>28,'type'=>'fk'),
            2   =>  array('price'=>30,'num'=>84,'type'=>'fk'),
            3   =>  array('price'=>60,'num'=>168,'type'=>'fk'),
            4   =>  array('price'=>120,'num'=>336,'type'=>'fk'),
            5   =>  array('price'=>300,'num'=>840,'type'=>'fk'),
            // 6   =>  array('price'=>888,'num'=>888,'type'=>'fk'),
            // 7   =>  array('price'=>100,'num'=>25,'type'=>'fk'),
            // 8   =>  array('price'=>500,'num'=>130,'type'=>'fk'),
            // 9   =>  array('price'=>1000,'num'=>280,'type'=>'fk'),
        );
    }

    //旗力币购买房卡
    public function rechargeFangkaByQilibi_Add() {
        $CheckParam = array(
            array('time', 'Int', 1, '服务器时间异常', '1'),
            array('hash', 'String', 1, '签名错误', '2'),
            array('uid', 'Int', 1, '用户ID', '1001'),
            array('goodsid', 'Int', 1, '商品ID', '1015'),
            array('paytype', 'Int', 1, '支付参数', '1005'),
        );
        $BackData = $this->CheckData(I('request.'), $CheckParam);
        
        $recharge_goods_list    =   $this->recharge_FangkaByQilibi_list();
        $goods_info             =   $recharge_goods_list[$BackData['goodsid']];
        $recharge_moeny = $goods_info['price'];
        $give_price = 0;
        
        $tag        =  date('ymdHis', NOW_TIME) . $BackData['uid'] . randomString('6', 0); //支付编号
        $order_sn   =   'CA' . date('ymdHis', NOW_TIME) . randomString('6', 0); //支付编号
        
        $orderModel = M('recharge_roomcard');

        $vip_recharge_data = array();
        $vip_recharge_data['order_sn'] = $order_sn; //支付编号
        $vip_recharge_data['create_time'] = NOW_TIME;
        $vip_recharge_data['uid'] = $BackData['uid'];
        $vip_recharge_data['tag'] = $tag;
        $vip_recharge_data['total_price'] = $recharge_moeny;
        $vip_recharge_data['paytype'] = 3;  //旗力币支付
        $vip_recharge_data['give_price'] = $give_price;
        $vip_recharge_data['goodsid'] = $BackData['goodsid'];
        $vip_recharge_data['nums'] = $goods_info['num'];
        $recharge_gold_res = $orderModel->add($vip_recharge_data);
        
        $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
        $playerInfo  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$vip_recharge_data['uid']))->field("UserID,InsureScore,Score")->find();
        if(empty($playerInfo)){
            dblog(array('player user not found','userid'=>$vip_recharge_data['uid'],'$playerInfo'=>$playerInfo));
            $this->ReturnJson(array('code' => '2001', 'msg' => '玩家信息错误', 'data' => '0'));
        }  else {
            if ($playerInfo['score'] < $recharge_moeny) {
                $this->ReturnJson(array('code' => '2002', 'msg' => '旗力币不足', 'data' => '0'));
            }

            //玩家增加房卡数
            $where      =   array();
            $where['UserID']    =   $vip_recharge_data['uid'];
            $updateData['Score']  =   $playerInfo['score']-$recharge_moeny;  
            $updateData['InsureScore']  =   $playerInfo['insurescore']+$vip_recharge_data['nums'];   
            $buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where($where)->setField($updateData);
            if ($buyer_gamescore_update) {
                $orderModel->where(array('id'=>$recharge_gold_res))->setField(array('ispay'=>1,'pay_time'=>NOW_TIME));
                $this->ReturnJson(array('code' => '0', 'msg' => '购买成功', 'data' => '0'));
            }
            $this->ReturnJson(array('code' => '2003', 'msg' => '购买失败', 'data' => '0'));
            dblog(array('api/pay/rechargeFangkaByQilibi_Add 1005','$updateData'=>$updateData,'$buyer_gamescore_update'=>$buyer_gamescore_update));
        }
    }

    //错误信息总会
    protected function getApiError($apiname) {
        $ApiError = array();
        //店铺广告列表
        $ApiError['index']['1001'] = '用户ID';
        $ApiError['index']['1002'] = 'hashid';
        $ApiError['index']['1003'] = '订单ID';
        $ApiError['index']['1004'] = '订单号';
        $ApiError['index']['1005'] = '支付类型';
        $ApiError['index']['1006'] = '店铺ID';
        $ApiError['index']['1007'] = '非法支付类型';
        $ApiError['index']['1008'] = '店铺不存在或被禁用';
        $ApiError['index']['1009'] = '非法支付类型';
        $ApiError['index']['1010'] = '订单不存在';
        $ApiError['index']['1011'] = '订单已经支付,无需再支付';
        $ApiError['index']['1012'] = '支付金额有误';
        $ApiError['index']['1013'] = '支付类型错误';
        $ApiError['index']['1014'] = '支付系统提示：以返回结果为准';
        $ApiError['index']['1015'] = '订单无商品购买';
        return $ApiError[$apiname];
    }

}

?>
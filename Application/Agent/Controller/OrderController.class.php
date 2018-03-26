<?php
namespace Agent\Controller;
use Think\Controller;
// 订单信息
class OrderController extends BaseController {
	/**
	 * [orderList 订单列表]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function orderList() {
		$agentId        = session('agentId');
		$startTime      = I('get.start_time');
		$endTime        = I('get.end_time');
		$pay_status     = I('get.pay_status');
		$orderSn        = I('get.order_sn');

		$where      = array();
		$whereStr   = '';

		if( !empty($orderSn) ) {
			$orderSn   = addslashes($orderSn);
			$orderSn   = urldecode($orderSn);
			$whereStr .= "AND o.order_sn LIKE \"%{$orderSn}%\" ";
			$where['order_sn'] = array('LIKE', "%{$orderSn}%");
		}
		if( $pay_status !== '' ) {
			$pay_status    = addslashes($pay_status);
			$pay_status    = urldecode($pay_status);
			$where['pay_status'] = "{$pay_status}";
			$whereStr  .= "AND o.pay_status = {$pay_status} ";
		}

		if( !empty($startTime) && !empty($endTime) ) {
			$startTime = addslashes($startTime);
			$startTime = urldecode($startTime);
			$startTime = str_replace('+', ' ', $startTime);
			$startTime = strtotime($startTime);

			$endTime   = addslashes($endTime);
			$endTime   = urldecode($endTime);
			$endTime   = str_replace('+', ' ', $endTime);
			$endTime   = strtotime($endTime);
			$whereStr  .= "AND o.add_time BETWEEN {$startTime} AND {$endTime} ";
			$where['add_time'] = array('BETWEEN', array($startTime, $endTime));
		}


		$getData = array();
		$get     = I('get.');
		foreach ($get as $key => $value) {
			if ( !empty($key) ) {
				$getData[$key] = urldecode($value);
			}
		}

		if ( !empty($getData['add_time']) ) {
			$getData['add_time'] = search_time_format($getData['add_time']);
		}

		if ( !empty($getData['end_time']) ) {
			$getData['end_time'] = search_time_format($getData['end_time']);
		}
		$where['agent_id'] = $agentId;
		$whereStr  .= "AND o.agent_id = {$agentId} ";
		$count    = M('order')->where($where)->count();
		$page     = new \Think\Page($count, 10, $getData);

		if ($this->iswap()) {
			$page->rollPage	= 5;
		}

		$show     = $page->show();
		$dbPrefix = C('DB_PREFIX');
		$sql      = "SELECT o.id, o.agent_id,o.order_sn, o.buyer, o.telephone,o.total_amount,o.paid_money, o.pay_status, o.pay_time, o.`status`,o.add_time,od.goods_name " .
                    "FROM {$dbPrefix}order AS o " . 
                    "LEFT JOIN {$dbPrefix}agent AS a ON o.agent_id = a.id " . 
                    "LEFT JOIN {$dbPrefix}order_detail AS od ON od.order_id = o.id " . 
                    "WHERE 1 {$whereStr} " . 
                    "ORDER BY o.id DESC " . 
                    "LIMIT {$page->firstRow}, {$page->listRows}";
		//        dump($sql);die;
		$orderList = M('order')->query($sql);
		//        dump($orderList);die;
		$this->assign('orderList', $orderList);
		//        dump($show);die;
		$this->assign('show', $show);

		if ( $agentId == '1' ) {
			// $this->display('qimenOrderList');
			$this->display('orderList');
		} else {
			$this->display('orderList');
		}
	}

	/**
	 * [orderDetail 订单详情]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function orderDetail() {
		$order_sn         = I('get.order_sn', '', 'trim');
		if ( empty($order_sn) ) {
			$this->error('参数丢失！');
		}

		$agentId = session('agentId');
		$order      = D('order');
		$orderInfo  = $order->where(array('order_sn'=>$order_sn, 'agent_id'=>$agentId))->relation(true)->find();
		if ( empty($orderInfo) ) {
			$this->error('找不到该订单！');
		}
		$this->assign('orderInfo', $orderInfo);
		$this->display();
	}

	/*
	 * 订单支付
	 */
	public function payConfirm(){
		$order_sn   =   I('get.order_sn','','trim');
		if(empty($order_sn)){
			$this->error('订单参数错误');
		}

		$orderInfo =   D('Order')->where(array('order_sn'=>$order_sn))->field(true)->relation(true)->find();
		if(empty($orderInfo)){
			$this->error('订单不存在');
		}
		$orderInfo['OrderDetail']['total_nums'] =   $orderInfo['OrderDetail']['goods_nums']+$orderInfo['OrderDetail']['give_goods_nums'];
		//            dump($orderInfo);die;
		$this->assign('orderInfo', $orderInfo);
		$this->display();
	}


	public function pay(){

                    dblog(array('pay start'));
		$order_sn   =   I('get.order_sn','','trim');
		if(empty($order_sn)){
			$this->error('订单参数错误');
		}

		$orderInfo =   D('Order')->where(array('order_sn'=>$order_sn))->field(true)->relation(true)->find();
		if(empty($orderInfo)){
			$this->error('订单不存在');
		}
		//支付宝支付
		$notify_url			=   'http://'.$_SERVER['HTTP_HOST'].'/System/Order/paySuccess';
		$descs                          =   '元宝支付_'.$orderInfo['OrderDetail']['goods_name'];
		$tag                            =   $orderInfo['order_sn'];
//		$fee				=   0.01; //支付金额
                $fee				=   $orderInfo['total_amount']; //支付金额
		$trade_sn			=   $tag;//支付单号
		$body				=   $descs;//TODO 需要完善描述
		$attach				=   $descs;
		$return_url                     =   '';
                    dblog(array('pay start 111',$trade_sn,$body,$attach,$fee,$notify_url,$return_url));
		$res                            =   $this->alipay_web($trade_sn,$body,$attach,$fee,$notify_url,$return_url);
		dump($res);die;
	}

	protected function alipay_web($trade_sn,$title,$body,$fee,$notify_url,$return_url){
                header("Content-type:text/html;charset=utf-8");
		include_once(CONF_PATH.'alipay.config.php');
		include_once(VENDOR_PATH.'/Alipay/lib/alipay_submit.class.php');
		$service					= $this->iswap() ? 'alipay.wap.create.direct.pay.by.user' : 'create_direct_pay_by_user';
		$parameter = array(
                    "service"           => $service,
                    "partner"           => trim($alipay_config['partner']),
                    "seller_id"  		=> $alipay_config['seller_id'],
                    "payment_type"		=> '1',
                    "notify_url"		=> $notify_url,
                    "return_url"		=> $return_url,
                    "out_trade_no"		=> $trade_sn,
                    "subject"           => $title,
                    "total_fee"         => $fee,
                    "body"              => $body,
                    "anti_phishing_key"	=> '',
                    "exter_invoke_ip"	=> '',
                    "app_pay"			=> "Y",//启用此参数能唤起钱包APP支付宝
                    "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
		);
                    dblog(array('pay start 222','$parameter'=>$parameter));
		$alipaySubmit = new \AlipaySubmit($alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter,"post", "确认");
		echo $html_text;die;
		return $result;
	}

	public function getPaystatus(){
		$order_sn   =   I('post.order_sn','','trim');
		//        dump($order_sn);die;
		if(empty($order_sn)){
			$this->error('订单参数错误');
		}
		$order_info =   M('order')->where(array('order_sn'=>$order_sn))->field('id,pay_status')->find();
		if(!empty($order_info)){
			if($order_info['pay_status'] == 1){
				die(json_encode(array('code'=>1,'msg'=>'支付成功')));
			}
		}
		die(json_encode(array('code'=>0,'msg'=>'未支付')));
	}

	/**
	 * [orderGoodsList 订单商品列表]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function orderGoodsList() {
		if ( IS_POST ) {
			$orderSn    = I('post.order_sn');
			if ( empty($orderSn) ) {
				$this->error('参数丢失！');
			}

			$agentId = session('agentId');
			$orderSn    = M('order')->where(array('order_sn'=>$orderSn, 'agent_id'=>$agentId))->getField('order_sn');
			if ( empty($orderSn) ) {
				$this->error('找不到订单信息！');
			}


			$goodsList = M('order_detail')->where(array('order_sn'=>$orderSn))->select();
			echo json_encode($goodsList);
		} else {
			$this->error('非法访问！');
		}
	}
}
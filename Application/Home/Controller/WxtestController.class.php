<?php
/**
 * 支付控制器
 * 王远庆
 */
namespace Home\Controller;
class WxtestController extends HomeController {
	public function index(){
		$wx_user = S('wx_user');
		print_r($wx_user);exit();
	}
	public function clearS(){
		$wx_user = S('wx_user',null);exit();
	}
	//开始支付
	public function auth()
	{
		//R('Home/Weixin/wxAddress',array('callBack'=>$callBack,'authType'=>'snsapi_base'));exit();
	}

	//地址测试
	public function address(){
		$callBack 	= base64_encode(U('','',true,true));
//                cookie(md5($callBack),null);die;
		$calldata	= cookie(md5($callBack));
                dblog(array('$calldata',$calldata));
		if (empty($calldata)){
                        dblog(array('111','address'));
			R('Home/Weixin/wxAddress',array('callBack'=>$callBack));exit();
		}
		else{
			$AuthInfo	= json_decode(base64_decode(FauthCode($calldata,'DECODE')),true);
		}
                        dblog(array('222',$AuthInfo));
		$this->assign('signPackage',$AuthInfo);
		$this->display();
	}
	//JS测试
	public function wxjs(){
		$AuthInfo		= R('Home/Weixin/wxJs');
		$this->assign('signPackage',$AuthInfo);
		$this->display();
	}
	//微信支付测试
	public function wxPay(){
		$payInfo['openid']			= 'oAbYqxCg9KFnP6XH57dj3J1v-QPc';
		$payInfo['out_trade_no']	= date('YmdHis',NOW_TIME).$ShopInfo['cid'].randomString('6',0);//支付编号
		$payInfo['total_fee']		= 1;
		$PayInfo					= R('Home/Weixin/wxPay',$payInfo);
		$this->assign('PayInfo',$PayInfo);dblog($PayInfo);
		$this->display();
	}
	public function wxPayScuuess(){
            dblog('wxPayScuuess');
            dblog($_REQUEST);
	}
	public function markQrcode(){
		$parame['type']		= 0;
		$parame['scene']	= 1;
		$DataInfo			= R('Home/Weixin/getWxQrcode',$parame);
		$qrcodePath			= 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$DataInfo;
		$this->assign('qrcodePath',$qrcodePath);
		$this->display();
	}
	//微信客服消息
	public function kfMsg(){
		$parame['openid']		= 'oAbYqxCg9KFnP6XH57dj3J1v-QPc';
		$parame['content']		= '<font color=\"red\">你的快递已发送，请及时查收</font>';
		$DataInfo				= R('Home/Weixin/SendKfMsg',$parame);
	}
	//退款测试
	public function wxRefund(){
		$parame['out_trade_no']		= 'PAY20160813111226632321';//商户订单号
		$parame['out_refund_no']	= 'RE20160813111241664656';//商户退款单号
		$parame['total_fee']		= 0.01*100;//总金额
		$parame['refund_fee']		= 0.01*100;//退款金额
		$resRefund					= R('Home/Weixin/wxRefund',$parame);
		print_r($resRefund);exit();
	}
	//退款进度查询
	public function RefundQuery(){
		$parame['out_trade_no']		= 'PAY20160711102900387251';//商户订单号
		$resRefundQuery				= R('Home/Weixin/RefundQuery',$parame);
		print_r($resRefundQuery);exit();
	}
        
        
	//企业付款测试
	public function wxCopay(){
		$parame['partner_trade_no']     = 'PAY20160711102900387253';//商户订单号，需保持唯一性
		$parame['openid']               = 'oAbYqxCg9KFnP6XH57dj3J1v-QPc';//商户appid下，某用户的openid
		$parame['check_name']		= 'NO_CHECK';//校验用户姓名选项
		$parame['amount']		= 1*100;//企业付款金额，单位为分
		$parame['desc']                 = '提现测试';//企业付款操作说明信息
		$parame['spbill_create_ip']     = getip();//调用接口的机器Ip地址
		$resRefund					= R('Home/Weixin/wxCopay',$parame);
		dump($resRefund);exit();
	}
}
?>
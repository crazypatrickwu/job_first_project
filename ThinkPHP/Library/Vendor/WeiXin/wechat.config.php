<?php
class WxPayConf_pub{
	const APPID 			= 'wxb50d54232847a2fa';
	const MCHID 			= '1314461601';
	const KEY 				= '3833fd350c0492801a0ee7b54ff60aaf';
	const APPSECRET 		= '116547db9dfc85dd6f1224bd3e0b0950';
	const JS_API_CALL_URL 	= '';
	const SSLCERT_PATH 		= 'ThinkPHP/Library/Vendor/WeiXin/cert/apiclient_cert.pem';
	const SSLKEY_PATH 		= 'ThinkPHP/Library/Vendor/WeiXin/cert/apiclient_key.pem';
	const NOTIFY_URL 		= 'http://mz.gochehui.com/Pay/wxPaySuccess/';
	const CURL_TIMEOUT 		= 30;

	private $config			= array();
	public function __construct($rid){
		$this->rid			= $rid;
	}
	public function getConfig($key){
		$public_account		= $this->public_account();
		return $public_account[$key];
	}

	private function public_account(){
		$public_account =   M('public_account')->where(array('redid'=>$this->rid))->field(array('app_id','app_secret','wx_pay_key','wx_pay_mchid'))->find();
		return $public_account;
	}
}
?>
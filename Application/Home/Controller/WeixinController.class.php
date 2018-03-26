<?php
namespace Home\Controller;
use Think\Controller;
use Vendor\WeiXin\WebChatCallHelper;
use Vendor\WeiXin\WeixinHelper;
use Vendor\WeiXin\WechatConfig;

class WeixinController extends Controller{

	public function __construct() {
		parent::__construct();
                $rid    =   I('get.rid',0,'intval');
                if($rid){
                    dblog(array('rid'=>$rid,'WeixinController 000'));
                    $this->Rid  =   $rid;
                }  else {
                    if(session('Rid')){
                        dblog(array('rid'=>session('Rid'),'WeixinController 111'));
                        $this->Rid              =   session('Rid');
                    }elseif (session('agentId')) {
                        dblog(array('rid'=>session('agentId'),'WeixinController 222'));
                        $this->Rid              =   session('agentId');
                    }elseif (isset ($_POST['rid'])) {
                        dblog(array('rid'=>$_POST['rid'],'WeixinController 333'));
                        $this->Rid              =   I('post.rid');
                    }elseif (S('autoscript_rid')) {
                        dblog(array('rid'=>S('autoscript_rid'),'WeixinController 444'));
                        $this->Rid              =   S('autoscript_rid');
                    }else {
                        $this->Rid = 8;
                    }
                }
                $this->Rid = 8;
		Vendor('WeiXin.WechatConfig');
		$PayConfig = new WechatConfig($this->Rid);
		$PayConfig->setConfig();
                dblog(array('WeixinController $this->Rid',  $this->Rid));
	}

	//微信交互入口
	public function index()
	{
		Vendor('WeiXin.WebChatCallHelper');
		$this->WebChatCallHelper	= new WebChatCallHelper();
		if(empty($GLOBALS['HTTP_RAW_POST_DATA'])){
			$CheckToken	= M('public_account')->where(array('redid'=>$this->Rid))->getField('token');
			$this->WebChatCallHelper->valid($CheckToken);
		}
		$this->GetAccessToken();
		$this->WebChatCallHelper->PostObj=simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'],'SimpleXMLElement',LIBXML_NOCDATA);
		//用户更新
            dblog(array('weixin index','$this->WebChatCallHelper->PostObj'=>$this->WebChatCallHelper->PostObj,'$this->Rid'=>$this->Rid));
		$WxUser		= S('wx_user_vxiaotian_'.$this->WebChatCallHelper->PostObj->FromUserName);
		if (empty($WxUser)){
			$UserInfo	= $this->WeixinHelper->UserInfo($this->WebChatCallHelper->PostObj->FromUserName);
            dblog(array('weixin index','$UserInfo'=>$UserInfo));
			$wxid		= $this->saveWxUser($UserInfo);
            dblog(array('weixin index','$wxid'=>$wxid));
			S('wx_user_vxiaotian_'.$this->WebChatCallHelper->PostObj->FromUserName,$UserInfo->openid,3600*24*30);
		}
		switch($this->WebChatCallHelper->PostObj->MsgType){
			case 'location': 	$Helper = $this->Helper($this->WebChatCallHelper->PostObj->MsgType);break;//定位
			case 'image': 		$Helper = $this->Helper($this->WebChatCallHelper->PostObj->MsgType);break;//图片
			case 'event'://事件
				$this->WebChatCallHelper->PostObj->Event=$this->WebChatCallHelper->SetTrim($this->WebChatCallHelper->PostObj->Event);
				switch($this->WebChatCallHelper->PostObj->Event){
					case 'subscribe': 			$Helper = $this->Helper(strtolower('event_'.$this->WebChatCallHelper->PostObj->Event));break;//关注事件
					case 'unsubscribe': 		$Helper = $this->Helper(strtolower('event_'.$this->WebChatCallHelper->PostObj->Event));break;//取消关注事件
					case 'SCAN': 				$Helper = $this->Helper(strtolower('event_'.$this->WebChatCallHelper->PostObj->Event));break;//扫码事件
					case 'LOCATION': 			$Helper = $this->Helper(strtolower('event_'.$this->WebChatCallHelper->PostObj->Event));break;//定位事件
					case 'CLICK': 				$Helper = $this->Helper(strtolower('event_'.$this->WebChatCallHelper->PostObj->Event));break;//点击事件
					case 'MASSSENDJOBFINISH': 	$Helper = $this->Helper(strtolower('event_'.$this->WebChatCallHelper->PostObj->Event));break;//接收结果事件
					default:die('success');break;
				}
				break;
			case 'voice': 		$Helper = $this->Helper($this->WebChatCallHelper->PostObj->MsgType);break;//语音
			case 'shortvideo': 	$Helper = $this->Helper($this->WebChatCallHelper->PostObj->MsgType);break;//视频
			case 'link': 		$Helper = $this->Helper($this->WebChatCallHelper->PostObj->MsgType);break;//链接
			case 'text': 		$Helper = $this->Helper($this->WebChatCallHelper->PostObj->MsgType);break;//文本
			default: $this->Helper('text');break;
		}
		$Helper->wxRun();
		die('success');
	}
	//微信js-sdk
	public function wxJs(){
		
		$this->GetAccessToken();
		$public_account		= M('public_account')->where(array('redid'=>$this->Rid))->field('ticket,expires_in_js')->find();
		if (!empty($public_account['ticket']) && $public_account['expires_in_js'] > NOW_TIME+600){
			$rawString		= 'yescache';
			$JsapiTicket	= $public_account['ticket'];
		}
		else{
			$JsApiJson		= $this->WeixinHelper->GetJsApiTicket();
			$JsapiTicket	= $JsApiJson->ticket;
			$rawString		= 'nocache';
			$TicketRow		= array('expires_in_js'=>(NOW_TIME+7000),'ticket'=>$JsapiTicket);
			M('public_account')->where(array('redid'=>$this->Rid))->setField($TicketRow);
		}
		$url 				= 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
          $url=str_replace('/Api','', $url);
		$nonceStr 			= $this->WeixinHelper->createNonceStr();
		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string 			= 'jsapi_ticket='.$JsapiTicket.'&noncestr='.$nonceStr.'&timestamp='.NOW_TIME.'&url='.$url;
		$signature 			= sha1($string);
		$Parameters['appid'] 		= $this->WeixinHelper->AppId;
		$Parameters['url'] 			= $url;
		$Parameters['noncestr'] 	= $nonceStr;
		$Parameters['timestamp'] 	= NOW_TIME;
		$Parameters['signature'] 	= $signature;

		//$Parameters['rawString'] 	= $this->WeixinHelper->Token.'|'.$JsapiTicket.'|'.$rawString;
		return $Parameters;
	}
	//微信授权
	public function wxAuth($callBack = '',$authType = 'snsapi_userinfo'){
		//初始化微信接口和access_token
		$this->GetAccessToken();
		$wxCode			= I('get.code','');
		if (empty($wxCode)){
			$url		= U('Weixin/WxAuth','callBack='.  $callBack,false,true);
			$authUrl 	= $this->WeixinHelper->oauth2_authorize($url, $authType, "wxAuth");
			Header("Location: $authUrl");
		}else{
                        $callBack       = str_replace('XMCODE', '/', $callBack);
                        
			$AuthToken	= $this->WeixinHelper->GetWxAuthToken($wxCode);
			$AuthUser	= $this->WeixinHelper->GetWxAuthUser($AuthToken);
			if (!empty($AuthUser->openid)){
				$wxid	= $this->saveWxUser($AuthUser, $AuthUser->scope);
				cookie('wx_user_vxiaotian_'.$this->Rid,FauthCode(base64_encode(json_encode(array('OpenId'=>$AuthUser->openid,'WxUserId'=>$wxid))),'ENCODE'),3600*24*30);
			}
			else{
				cookie('wx_user_vxiaotian_'.$this->Rid,null);
			}
                        
			$callBack   = base64_decode($callBack);
			Header("Location: $callBack");
		}
	}
	//微信地址共享
	public function wxAddress($callBack){
		$this->GetAccessToken();
		$wxCode			= I('get.code','');
		if (empty($wxCode)){
			$url		= U('Weixin/wxAddress','callBack='.$callBack,false,true);
			$authUrl 	= $this->WeixinHelper->oauth2_authorize($url, 'snsapi_base', "wxAuth");
			Header("Location: $authUrl");exit();
		}else{
                        $callBack       = str_replace('XMCODE', '/', $callBack);
			$redirect_uri				= base64_decode($callBack);
			$AuthToken	= $this->WeixinHelper->GetWxAuthToken(I('get.code'));
			$nonceStr 					= rand(100000,999999);
			$timestamp					= time();
			$Parameters['appid'] 		= $this->WeixinHelper->AppId;
			$Parameters['url'] 			= $redirect_uri;
			$Parameters['timestamp'] 	= $timestamp;
			$Parameters['noncestr'] 	= $nonceStr;
			$Parameters['accesstoken'] 	= $AuthToken->access_token;
			// 生成 SING
			$addrSign 					= $this->WeixinHelper->MakeSign($Parameters);
			$Parameters['addrSign']		= $addrSign;
			$calldata					= base64_encode(json_encode($Parameters));

			cookie(md5($callBack).'_'.$this->Rid,FauthCode($calldata,'ENCODE'),7000);
		}
		Header("Location: $redirect_uri");exit();
	}

	public function wxGetImage($media_id){
		$this->GetAccessToken();
		//            $access_token   = 'XoIzAXMHwxnhwMhDFmREt_78yIwGbJDryEAHtYeM4oRRM04wRza5S4jserC6DzyO4SmTk7AL1_HL59bR2SjcYWq_fD2Pb-O-jvu0b1oq45zSfgsHsrXouzCpAKtmcNbyODIhAHAGVA';
		$url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token=".$this->WeixinHelper->Token."&media_id=".$media_id;
		return $url;
	}


	/*
	 * 微信支付
	 * */
	public function wxPay($openid,$out_trade_no,$total_fee,$order_type=1){
		Vendor('WeiXin.WeixinPayPubHelper');
		//使用jsapi接口
		//使用统一支付接口
		$unifiedOrder = new \UnifiedOrder_pub();
		$unifiedOrder->setParameter("openid","$openid");//用户openid
		$unifiedOrder->setParameter("body","支付金额");//商品描述
		$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
		$unifiedOrder->setParameter("total_fee","$total_fee");//总金额
		$unifiedOrder->setParameter("notify_url",C("NOTIFY_URL").'/order_type/'.$order_type);
		$unifiedOrder->setParameter("trade_type","JSAPI");//交易类型
		$prepay_id 				= $unifiedOrder->getResult();
		$jsApi 					= new \JsApi_pub();
		$jsApi->setPrepayId($prepay_id['prepay_id']);
		$jsApiParameters 		= $jsApi->getParameters();
		return $jsApiParameters;
	}
	/*
	 * 微信退款
	 * */
	public function wxRefund($out_trade_no,$out_refund_no,$total_fee,$refund_fee){
            dblog(array('wxRefund',$out_trade_no,$out_refund_no,$total_fee,$refund_fee));
		Vendor('WeiXin.WeixinPayPubHelper');
		$unifiedOrder = new \Refund_pub();
		$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
		$unifiedOrder->setParameter("out_refund_no","$out_refund_no");//商户退款单号
		$unifiedOrder->setParameter("total_fee","$total_fee");//订单总金额
		$unifiedOrder->setParameter("refund_fee",$refund_fee);//总金额
		$unifiedOrder->setParameter("op_user_id",C("MCHID"));//操作员
		$resRefund 				= $unifiedOrder->getResult();
		return $resRefund;
	}
	/*
	 * 微信退款进度查询
	 * */
	public function RefundQuery($out_trade_no){
		Vendor('WeiXin.WeixinPayPubHelper');
		$unifiedOrder = new \RefundQuery_pub();
		$unifiedOrder->setParameter("out_trade_no","$out_trade_no");//商户订单号
		$resRefundQuery 		= $unifiedOrder->getResult();
		return $resRefundQuery;
	}


	/*
	 * 企业付款
	 * */
	public function wxCopay($partner_trade_no,$openid,$check_name,$amount,$desc,$spbill_create_ip){
		Vendor('WeiXin.WeixinPayPubHelper');
		$unifiedOrder = new \Copay_pub();
		$unifiedOrder->setParameter("partner_trade_no","$partner_trade_no");//商户订单号，需保持唯一性
		$unifiedOrder->setParameter("openid","$openid");//商户appid下，某用户的openid
		$unifiedOrder->setParameter("check_name","$check_name");//校验用户姓名选项
		$unifiedOrder->setParameter("amount",$amount);//企业付款金额，单位为分
		$unifiedOrder->setParameter("desc",$desc);//企业付款操作说明信息
		$unifiedOrder->setParameter("spbill_create_ip",$spbill_create_ip);//调用接口的机器Ip地址
		$resRefund 				= $unifiedOrder->getResult();
		return $resRefund;
	}
	//微信创建菜单
	public function createMenu(){
		$this->GetAccessToken();
		$Helper = $this->Helper('createmenu');
		return $Helper->wxRun();
	}
	public function getWxQrcode($type=0,$scene,$parame,$validity_time=2592000,$rid){
		$qrcodePath			= '';
		$type				= intval($type);
		if (!empty($scene)){
			$this->GetAccessToken();
			$where				= array();
			$where['type']		= $type;
			$where['scene']		= $scene;
			$qrcodeModel		= M('public_qrcode');
			$info				= $qrcodeModel->where($where)->field('id,validity_time,type,url')->find();
			$updata['id']		= (!empty($info) && $info['id'] > 0) ? $info['id'] : 0;
			$updata['url']		= (!empty($info) && !empty($info['url'])) ? $info['url'] : '';
			if (empty($info)){
				$ticket						= $this->WeixinHelper->GetWxQrcode($type,$scene,$validity_time);
				$updata['ticket']			= $ticket;
				$updata['url']				= 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
				$updata['validity_time']	= $type == 1 ? 0 : (NOW_TIME+$validity_time-200);//提早200过期
			}else{
				if ($info['id'] >0 && $info['type'] == 0 && $info['validity_time'] < NOW_TIME){
					$ticket						= $this->WeixinHelper->GetWxQrcode($type,$scene,$validity_time);
					$updata['ticket']			= $ticket;
					$updata['url']				= 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
					$updata['validity_time']	= NOW_TIME+$validity_time-200;//提早200过期
				}
			}
			$updata['type']					= $type;
			$updata['scene']				= $scene;
			$updata['parame']				= !empty($parame) ? $parame : '';
			if ($updata['id'] >0){
				$qrcodeModel->where(array('id'=>$updata['id']))->save($updata);
			}else{
				$qrcodeModel->add($updata);
			}
		}
		return $updata['url'];
	}
	//发送客服消息
	public function SendKfMsg($openid,$content){
		$this->GetAccessToken();
		$sendData 	= '{"touser":"'.$openid.'","msgtype":"text","text":{"content":"'.$content.'"}}';
		$res		= $this->WeixinHelper->SendMesg($sendData);
                return  $res;
		wr($res);
	}
	protected function saveWxUser($user,$scope){
		$_POST	= array();
		//用户信息整理
		$_POST['openid']			= $user->openid;
		if (!empty($user->nickname)){
			$user->nickname		= base64_encode($user->nickname);
                        $_POST['nickname']              =   $user->nickname;
		}
		if (!empty($user->headimgurl)){
			$_POST['avatar']		= $user->headimgurl;
		}
		if (!empty($user->sex)){
			$_POST['sex']			= $user->sex;
		}
		if (!empty($user->province)){
			$_POST['province']		= $user->province;
		}
		if (!empty($user->city)){
			$_POST['city']			= $user->city;
		}
		if (!empty($user->country)){
			$_POST['country']		= $user->country;
		}
		if (!empty($this->Rid)){
			$_POST['red_id']		= $this->Rid;
		}
		//通过openid检查用户是否存在
		$userModel					= M('public_user');
		$wxInfo						= $userModel->where(array('openid'=>$user->openid))->field('id')->find();
		if (!empty($wxInfo)){
			$_POST['id']			= $wxInfo['id'];
		}else{
			$_POST['create_time']	= NOW_TIME;
		}
		$data						= $userModel->create();
                dblog(array('301',$data));
		if ($data['id'] > 0){
			$userModel->save($data);
		}else{
			$data['id'] = $userModel->add($data);
		}
		return $data['id'];
	}
	protected function GetAccessToken(){
		dblog(array('GetAccessToken 1001'));
		Vendor('WeiXin.WeixinHelper');
		$this->WeixinHelper	= new WeixinHelper();
		$public_account		= M('public_account')->where(array('redid'=>$this->Rid))->field('app_id,app_secret,access_token,expires_in,auth_token,expires_in_auth,expires_in_js,ticket')->find();
		
		dblog(array('GetAccessToken 1002','public_account'=>$public_account));
		if(!empty($public_account['app_id']) && !empty($public_account['app_secret'])){
			$this->WeixinHelper->AppId			= $public_account['app_id'];
			$this->WeixinHelper->AppSecret		= $public_account['app_secret'];
			$this->WeixinHelper->Token			= $public_account['access_token'];
			if( empty($public_account)|| empty($this->WeixinHelper->Token) || $public_account['expires_in'] <= NOW_TIME+600){
				$GetToken						= $this->WeixinHelper->GetToken();
				dblog(array('GetAccessToken 1003','GetToken'=>$GetToken));
				$TokenRow						= array('expires_in'=>NOW_TIME+$GetToken->expires_in,'access_token'=>$GetToken->access_token);
				M('public_account')->where(array('redid'=>$this->Rid))->setField($TokenRow);
			}
		}
	}
        
        //获取图文素材
	public function GetWxNewsMaterial($offset,$count){
		$this->GetAccessToken();
                $access_token   =   $this->WeixinHelper->Token;
//                $access_token   =   '-t_lNtA-f9902kJfhKcPqPHnu1DGp7WQoLboLuPn0EtpBPSnTjFiFtqkln3NI_Agjh4tsWxIA3CyxqYFshqNWKLL8Ixo626uZQH6jzxnN39ww1ZRDnyjFYaIvy3G8rGoILRjABAADJ';
                $url    =   'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$access_token;
                $param  =   array();
                $param['type']      =   'news';
                $param['offset']    =   $offset;
                $param['count']     =   $count;
                $result   =   CurlHttp($url, json_encode($param),'POST');
                $news_result     = json_decode($result,true);
                $news_list  =   $news_result['item'];
//                        dump($news_result);die;
                $news_arr   =   array();
                $news_arr['total_count']    =   $news_result['total_count'];
                $news_arr['item_count']     =   $news_result['item_count'];
                foreach ($news_list as $k1 => $v1) {
                    $news_arr['content'][$k1]['media_id']          =   $v1['media_id'];
                    $news_arr['content'][$k1]['create_time']  =   $v1['content']['create_time'];
                    $news_arr['content'][$k1]['update_time']  =   $v1['content']['update_time'];
                    foreach ($v1['content']['news_item'] as $k2 => $v2) {
                        $news_arr['content'][$k1]['items'][$k2]['title']        =   $v2['title'];
                        $news_arr['content'][$k1]['items'][$k2]['digest']       =   $v2['digest'];
                        $news_arr['content'][$k1]['items'][$k2]['thumb_url']    =   $v2['thumb_url'];
                        $news_arr['content'][$k1]['items'][$k2]['url']          =   $v2['url'];
                    }
                }
//                dump($news_arr);die;
                return  $news_arr;
	}
        
        
        //获取图文素材
	public function GetWxImageMaterial($offset,$count){
		$this->GetAccessToken();
                $access_token   =   $this->WeixinHelper->Token;
//                $access_token   =   '-t_lNtA-f9902kJfhKcPqPHnu1DGp7WQoLboLuPn0EtpBPSnTjFiFtqkln3NI_Agjh4tsWxIA3CyxqYFshqNWKLL8Ixo626uZQH6jzxnN39ww1ZRDnyjFYaIvy3G8rGoILRjABAADJ';
                $url    =   'https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token='.$access_token;
                $param  =   array();
                $param['type']      =   'image';
                $param['offset']    =   $offset;
                $param['count']     =   $count;
                $result   =   CurlHttp($url, json_encode($param),'POST');
                $news_result     = json_decode($result,true);
                $news_list  =   $news_result['item'];
//                        dump($news_result);die;
                $news_arr   =   array();
                $news_arr['total_count']    =   $news_result['total_count'];
                $news_arr['item_count']     =   $news_result['item_count'];
                foreach ($news_list as $k1 => $v1) {
                    $news_arr['content'][$k1]['media_id']       =   $v1['media_id'];
                    $news_arr['content'][$k1]['name']           =   $v1['name'];
                    $news_arr['content'][$k1]['update_time']    =   $v1['update_time'];
                    $news_arr['content'][$k1]['cover_image']    =   $v1['url'];
                }
//                dump($news_arr);die;
                return  $news_arr;
	}
        
	private function Helper($controller){
		$name  = parse_name($controller, 1);
		$class = is_file('./Application/Home/' . 'Helper/' . $name . 'Helper' . EXT) ? $name : 'Base';
		$class = '\\Home\\Helper\\' . $class . 'Helper';
		return new $class($name);
	}
}
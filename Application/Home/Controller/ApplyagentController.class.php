<?php
namespace Home\Controller;
use Think\Controller;
//玩家控制器
class ApplyagentController extends HomeController {

	public function __construct() {
        parent::__construct();
        $this->platform_invitation_code = C('PLATFORM_INVITATION_CODE');
        $agentId                =   session('agentId');
        $agentAccount                =   session('agentAccount');
        if($agentId && $agentAccount){
            redirect(U('Index/index'));
        }
    }
	/*
	*检测邀请码
	*/
	protected function checkInvitationCode($invitation_code){
		$agent = M('agent')->where(array('invitation_code'=>$invitation_code))->field(true)->find();
		if (empty($agent)) {
			# code...
			die(json_encode(array('code'=>0,'msg'=>'请输入正确的邀请码')));
		}else{
			if ($agent['is_delete'] == 1) {
				# code...
				die(json_encode(array('code'=>0,'msg'=>'当前邀请码已被禁用')));
			}
			if ($agent['is_lock'] == 1) {
				# code...
				die(json_encode(array('code'=>0,'msg'=>'当前邀请码已被禁用')));
			}
		}
		return $agent;
	}

    public function payConfirm(){
        if (IS_POST) {
                # code...
	        	$gameuser = M('gameuser')->where(array('user_id'=>$this->userid))->field(true)->find();
				if (empty($gameuser)) {
					# code...
					redirect(U('playerInfoReg'));
				}
				if (empty($gameuser['game_user_id'])) {
					# code...
					redirect(U('playerInfoReg'));
				}
				if (empty($gameuser['invitation_code'])) {
					# code...
					redirect(U('playerInfoReg'));
				}
				$wx_user = M('public_user')->where(array('id'=>$this->userid))->field(true)->find();

                $goods_id   =   I('post.goods_id',0,'intval');
                if($goods_id == 0){
                    $this->error('参数错误！');
                }
                $info = D('goods')->where(array('id'=>$goods_id))->field(true)->find();
                if(!empty(C('PLAYER_ROOM_PRICE'))){
                    $room_price =   floatval(C('PLAYER_ROOM_PRICE'));
                }  else {
                    $room_price =   1;
                }

                // $agentId                =   session('agentId');
                // $agent_info             =   M('agent')->where(array('id'=>$agentId))->field(true)->find();

                $order_model  =   M();
                $order_model->startTrans();
                
                //生成订单
                $order_data =   array();
                $order_data['user_id']         =    $this->userid;
                $order_data['buyer_type']       =  'player';
                $order_data['order_sn']         =   'MJ'.date('YmdHis',NOW_TIME).randomString('6',0);//订单编号
                $order_data['buyer']            =   base64_decode($wx_user['nickname']);
                $order_data['total_amount']     =   $room_price*$info['goods_nums'];
                $order_data['add_time']         =   NOW_TIME;
                $order_id                       =   M('order')->add($order_data);
                if(!$order_id){
                    $order_model->rollback();
                    die(json_encode(array('code'=>'0','message'=>'生成订单错误！')));
                }
                
                //订单详情
                $detail_data                       =   array();
                $detail_data['order_id']           =   $order_id;
                $detail_data['order_sn']           =   $order_data['order_sn'];
                $detail_data['goods_id']           =   $goods_id;
                $detail_data['goods_name']         =   $info['goods_name'];
                $detail_data['goods_image']        =   $info['goods_image'];
                $detail_data['goods_nums']         =   $info['goods_nums'];
                $detail_data['goods_price']        =   $info['goods_nums']*$room_price;
                $detail_data['give_goods_nums']    =   $info['give_goods_nums'];
                $detail_id                         =   M('order_detail')->add($detail_data);
                if(!$detail_id){
                    $order_model->rollback();
                    die(json_encode(array('code'=>'0','message'=>'生成订单错误！')));
                }
                
                //订单日志
                $log_data               =   array();
                $log_data['user_id']         =    $this->userid;
                $log_data['order_id']   =   $order_id;
                $log_data['desc']       =   '生成订单:会员'.'【'.$this->userid.'】购买套餐，当前玩家游戏ID为'.'【'.$gameuser['game_user_id'].'】';
                $log_data['add_time']   =   NOW_TIME;
                $log_id                 =   M('order_log')->add($log_data);
                if(!$log_id){
                    $order_model->rollback();
                    $this->error('生成订单错误！');
                }
                
                //订单提交
                $order_model->commit();

                //调用支付
                $payInfo['openid']          = $this->openid;
                $payInfo['out_trade_no']            = $order_data['order_sn'];//支付编号
                $payInfo['total_fee']       = 0.01*100;
//                                $payInfo['total_fee']     = $param_array['order_amount']*100;
//                            dblog($payInfo);
                $PayInfo                    = R('Home/Weixin/wxPay',$payInfo);
                die(json_encode(array('code'=>'1','message'=>$cart_id,'PayInfo'=>$PayInfo)));
        }
        
    }


	/*
	*申请代理
	*/
	public function agentReg(){
		if (IS_POST) {
			// dump(I('post.'));die;
			# code...
			$invitation_code	=	I('post.invitation_code','','trim');
			$nickname			=	I('post.nickname','','trim');
			$phone				=	I('post.phone','','trim');
			$mobile				=	I('post.mobile','','trim');
			$address			=	I('post.address','','trim');
			$desc				=	I('post.desc','','trim');
			$invitation_code    =   strval($invitation_code);
			if (empty($nickname)) {
				# code...
				die(json_encode(array('code'=>0,'msg'=>'请输入姓名')));
			}
			if (empty($phone)) {
				# code...
				die(json_encode(array('code'=>0,'msg'=>'请输入手机号')));
			}

			$agent_info	= M('agent')->where(array('phone'=>$phone))->field(true)->find();
			if (!empty($agent_info)) {
				# code...
				die(json_encode(array('code'=>0,'msg'=>'当前手机号已被注册')));
			}

			$where = array();
			$where['phone']		=	$phone;
			$where['status']	=	array('in',array(0,1));	
			$agent_applying_info	= M('agent_applying')->where($where)->field(true)->find();
			$data = array();
			if (!empty($agent_applying_info)) {
				# code...
				die(json_encode(array('code'=>0,'msg'=>'当前手机号已被注册')));
			}

			if (!empty($invitation_code)) {
				$agentinfo=M('agent')->where(array('invitation_code'=>$invitation_code))->find();
				if ($agentinfo['is_delete'] == 1) {
					# code...
					die(json_encode(array('code'=>0,'msg'=>'当前邀请码已被禁用')));
				}
				if ($agentinfo['is_lock'] == 1) {
					# code...
					die(json_encode(array('code'=>0,'msg'=>'当前邀请码已被禁用')));
				}
				$data['rid']           =$agentinfo['id'];
				$data['apply_type']	   =1;
				if($agentinfo['level']==3){
					die(json_encode(array('code'=>0,'msg'=>'三级代理下面不能添加代理')));
				}
				$data['level']=$agentinfo['level']+1;
			}
			if(empty($invitation_code)){
					$data['level']		=1;
					$data['apply_type']	=2;
			}

			//检测代理邀请码
			// $agent = $this->checkInvitationCode($invitation_code);

			$data['phone'] 		= $phone;
			$data['nickname'] 	= $nickname;
			$data['mobile'] 	= $mobile;
			$data['address'] 	= $address;
			$data['desc'] 		= $desc;
			$data['wechat_id'] 	= $phone;
			$data['user_id'] 	= $this->userid;
		    $data['invitation_code']  = $invitation_code;
			$data['dateline']            = NOW_TIME;
		 	$res = M('agent_applying')->add($data);
			if ($res) {
				# code...
				die(json_encode(array('code'=>1,'msg'=>'提交成功，等待平台审核')));
			}
			die(json_encode(array('code'=>0,'msg'=>'提交失败')));
		}else{

			$where = array();
			$where['user_id'] 	= $this->userid;
			$agent_applying = M('agent_applying')->where($where)->field(true)->order('id DESC')->find();
			if (!empty($agent_applying)) {
				switch ($agent_applying['status']) {
					case '0':
						echo "<h1>您的信息已经提交，平台审核中...</h1>";
						exit();
						break;
					case '1':
						redirect(U('Index/index'));
						break;
					case '2':
						# code...
						break;
					
					default:
						# code...
						break;
				}
			}

			$this->display('agentReg');
		}
	}

}
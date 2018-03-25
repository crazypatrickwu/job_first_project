<?php
namespace Home\Controller;
use Think\Controller;
//玩家控制器
class GameplayerController extends HomeController {

	public function __construct() {
        parent::__construct();
        $this->platform_invitation_code = C('PLATFORM_INVITATION_CODE');
    }

	/*
	*填写邀请码和游戏ID
	*/
	public function playerInfoReg(){
		if (IS_POST) {
			# code...
			$invitation_code	=	I('post.invitation_code','','trim');
			$game_user_id		=	I('post.game_user_id','','trim');
			if (empty($invitation_code)) {
				# code...
				// die(json_encode(array('code'=>0,'msg'=>'请输入代理邀请码')));
			}
			if (empty($game_user_id)) {
				# code...
				die(json_encode(array('code'=>0,'msg'=>'请输入玩家游戏ID')));
			}

			//检测代理邀请码
			if (!empty($invitation_code)) {
				//查找新代理信息
				$new_agent = M('agent')->where(array('invitation_code'=>$invitation_code))->field(true)->find();
				if (empty($new_agent)) {
					die(json_encode(array('code'=>0,'msg'=>'请输入正确的邀请码')));
				}else{
					if ($new_agent['is_delete'] == 1) {
						die(json_encode(array('code'=>0,'msg'=>'当前代理已删除或被禁用')));
					}
					if ($new_agent['is_lock'] == 1) {
						die(json_encode(array('code'=>0,'msg'=>'当前代理还未被激活')));
					}
				}
			}

			//检测玩家游戏ID
			$this->checkGameUserId($game_user_id);

			$data				=	array();
			$data['invitation_code']	=	$invitation_code;
			$data['game_user_id']		=	$game_user_id;
			$data['update_time']		=	NOW_TIME;

			$gameuser = M('gameuser')->where(array('user_id'=>$this->userid))->field(true)->find();
			if (empty($gameuser)) {
				# code...
				$data['user_id']	=	$this->userid;
				$data['add_time']	=	NOW_TIME;
				$res	=	M('gameuser')->add($data);

				//更新代理会员数量
				if (!empty($invitation_code)) {
					M('agent')->where(array('id'=>$new_agent['id']))->setInc('player_num');
				}
			}else{
				$res	=	M('gameuser')->where(array('user_id'=>$this->userid))->setField($data);

				//更新代理会员数量
				if (!empty($invitation_code)) {
					if ($invitation_code != $gameuser['invitation_code']) {
						# code...
						//新的代理人，会员数+1
						M('agent')->where(array('id'=>$new_agent['id']))->setInc('player_num');

						//新的代理人，会员数-1
						M('agent')->where(array('invitation_code'=>$gameuser['invitation_code']))->setDec('player_num');
					}
				}else{
					if (!empty($gameuser['invitation_code'])) {
						M('agent')->where(array('invitation_code'=>$gameuser['invitation_code']))->setDec('player_num');
					}
				}
			}
			if ($res) {
				# code...
				if (!empty($invitation_code)) {
					M('gameuser')->where(array('game_user_id'=>$game_user_id))->setField(array('invitation_code'=>$invitation_code));
				}

				die(json_encode(array('code'=>1,'msg'=>'绑定成功')));
			}
			die(json_encode(array('code'=>0,'msg'=>'绑定失败')));
		}else{


			$gameuser = M('gameuser')->where(array('user_id'=>$this->userid))->field(true)->find();
			$this->assign('gameuser',$gameuser);
			$this->display('playerInfoReg');
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
				die(json_encode(array('code'=>0,'msg'=>'当前代理已删除或被禁用')));
			}
			if ($agent['is_lock'] == 1) {
				# code...
				die(json_encode(array('code'=>0,'msg'=>'当前代理还未被激活')));
			}
		}
	}

	/*
	*检测玩家游戏ID
	*/
	protected function checkGameUserId($game_user_id){
		$sqlsrv_model = $this->sqlsrv_model('AccountsDB','AccountsInfo');
		$player_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();
		if (empty($player_accounts_info)) {
			# code...
			die(json_encode(array('code'=>0,'msg'=>'当前游戏玩家已删除或不存在')));
		}
	}

	/*
	*商品页面
	*/
	public function goodsList(){
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
		
		$list = D('goods')->where(array('buyer'=>'player'))->select();
        $room_price = floatval(C('room_price'));
        foreach ($list as $key => $value) {
            $list[$key]['total_money']  = floatval($value['goods_nums']*$room_price);
            $list[$key]['total_nums']  = $value['goods_nums']+$value['give_goods_nums'];
        }
//        dump($list);die;
        $this->assign("list", $list);
		$this->display('goodsList');
	}

    public function detail(){
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

            $id = I('get.id');
            $info = D('goods')->where("id = $id")->find();
            if(!empty(C('PLAYER_ROOM_PRICE'))){
                $room_price =   floatval(C('PLAYER_ROOM_PRICE'));
            }  else {
                $room_price =   1;
            }
            $info['total_money']    =   floatval($room_price*$info['goods_nums']);
            $info['total_nums']     =   $info['goods_nums']+$info['give_goods_nums'];
            
            $this->assign("info", $info);
            $this->display();
    }


    /*
    *玩家套餐页面
    */
    public function package(){
    	$gameuser = M('gameuser')->where(array('user_id'=>$this->userid))->field(true)->find();
		if (empty($gameuser['game_user_id'])) {
            redirect(U('playerInfoReg'),3,'<h1>当前微信账号未绑定，立即绑定...</h1>');
		}
		
		$list = D('goods')->where(array('buyer'=>'player'))->select();
        foreach ($list as $key => $value) {
            $list[$key]['total_money']  = $value['goods_price'];
            $list[$key]['total_nums']  = $value['goods_nums']+$value['give_goods_nums'];
        }
        // dump($list);die;
		
        $sqlsrv_model = $this->sqlsrv_model('TreasureDB','GameScoreInfo');
        $player_accounts_info = $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$gameuser['game_user_id']))->field('UserID,InsureScore,Score')->find();
       // dump($player_accounts_info);die;
        $this->assign("list", $list);
        $this->assign("player_accounts_info", $player_accounts_info);
		$this->display('package');
    }

    //确认支付

    public function payConfirm(){
        if (IS_POST) {
                # code...
        		$goodsid = I('post.goodsid',0,'intval');
        		if (empty($goodsid)) {
        			die(json_encode(array('code'=>0,'msg'=>'参数错误')));
        		}

	        	$gameuser = M('gameuser')->where(array('user_id'=>$this->userid))->field(true)->find();
				if (empty($gameuser)) {
        			die(json_encode(array('code'=>0,'msg'=>'玩家参数错误')));
				}
				if (empty($gameuser['game_user_id'])) {
        			die(json_encode(array('code'=>0,'msg'=>'玩家参数错误')));
				}

                $info = D('goods')->where(array('id'=>$goodsid))->field(true)->find();
                $buy_nums = $info['goods_nums'] + $info['give_goods_nums'];
                //判断玩家是否绑定代理邀请码
                if (!empty($gameuser) && $gameuser['invitation_code']) {
		                $invitation_code_agent = M('agent')->where(array('invitation_code'=>$gameuser['invitation_code']))->field(true)->find();
		                if (!empty($invitation_code_agent)) {
	                			if ($invitation_code_agent['room_card'] < $buy_nums) {
                					die(json_encode(array('code'=>0,'msg'=>'库存不足，请联系代理')));
	                			}
		                }
                }

		        $give_price = 0;
		        
		        $tag        =  date('ymdHis', NOW_TIME) . $gameuser['game_user_id'] . randomString('6', 0); //支付编号
		        $order_sn   =   'CA' . date('ymdHis', NOW_TIME) . randomString('6', 0); //支付编号
		        $recharge_moeny = $info['goods_price'];


                $payfrom    =   2;	//1=房卡，2=金币
                if ($payfrom == 1) {
                	# code...
                	$orderModel = M('recharge_roomcard');
                }elseif ($payfrom == 2) {
                	# code...
                	$orderModel = M('recharge_gold');
                }else{
                	die(json_encode(array('code'=>0,'msg'=>'参数错误')));
                }


		        $vip_recharge_data = array();
                $vip_recharge_data['order_sn'] 		= $order_sn; //支付编号
                $vip_recharge_data['create_time'] 	= NOW_TIME;
                $vip_recharge_data['uid'] 			= $gameuser['game_user_id'];
                $vip_recharge_data['tag'] 			= $tag;
                $vip_recharge_data['total_price'] 	= $recharge_moeny;
                $vip_recharge_data['paytype'] 		= 2;
                $vip_recharge_data['give_price'] 	= $give_price;
                $vip_recharge_data['goodsid'] 		= $goodsid;
                $vip_recharge_data['nums'] 			= $info['goods_nums']+$info['give_goods_nums'];
                $orderModel->add($vip_recharge_data);
                
		        
		        //支付信息入库
		        $payModel   = M('pay');
    			$pay_data = array();
		        $pay_data['out_trade_no'] = $vip_recharge_data['tag'];
		        $pay_data['money'] = $recharge_moeny;
		        $pay_data['status'] = 0; //数据状态
		        $pay_data['uid'] = $gameuser['user_id']; //购买者ID
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

                //调用支付
                $payInfo['openid']          = $this->openid;
                $payInfo['out_trade_no']    = $vip_recharge_data['tag'];//支付编号
                //$payInfo['total_fee']       = 0.01*100;

                if(in_array($this->userid, array(3))){
                    $payInfo['total_fee']       = 0.01*100;
                }else{
               		$payInfo['total_fee']     = $recharge_moeny*100;
                }
                $payInfo['order_type']      = 2;    //订单类型,1=代理购买房卡，2=玩家购买房卡
                           dblog($payInfo);
                $PayInfo                    = R('Home/Weixin/wxPay',$payInfo);
                die(json_encode(array('code'=>'1','msg'=>$payid,'PayInfo'=>$PayInfo)));
        }
        
    }

	/*
	*商品支付页面
	*/
	public function pay(){

		$this->display();
	}

	/*
	*修改邀请码
	*/
	public function modifyInvitationCode(){

		$this->display();
	}

	/*
	*修改游戏ID
	*/
	public function modifyGameUserId(){

		$this->display();
	}


    //ajax查找玩家所属代理
    public function ajaxGetAgentInvitationCodeByGameuserid(){
    	if (IS_POST) {
    		# code...
    		$game_user_id =	I('post.game_user_id','','trim');
    		$where = array();
    		$where['game_user_id'] 	= 	$game_user_id;
    		$where['user_id']		=	array('neq',$this->userid);
    		$where['invitation_code'] 	= 	array('neq','');
    		$gameuser = M('gameuser')->where($where)->field(true)->find();
    		$invitation_code = !empty($gameuser['invitation_code']) ? $gameuser['invitation_code'] : '';
			die(json_encode(array('code'=>1,'invitation_code'=>$invitation_code)));
    	}
    }

}
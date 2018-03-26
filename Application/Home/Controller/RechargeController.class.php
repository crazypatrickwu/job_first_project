<?php
namespace Home\Controller;
use Think\Controller;
// 玩家充卡控制器
class RechargeController extends HomeController {
    
        public function __construct() {
            parent::__construct();
        }
        
        public function index(){
            $this->display();
        }

        public function playerindex(){
            $this->display();
        }

        //我的返卡
	public function myRechargeRecored(){
		$agentId                =   session('agentId');
		$type                   =   I('get.type',0,'intval');


		$startTime      = I('get.start_time');
		$endTime        = I('get.end_time');

		$where      = array();
		$where['type']          =   $type;
		$where['agent_id']      =   $agentId;
		if( !empty($startTime) && !empty($endTime) ) {
			$startTime = addslashes($startTime);
			$startTime = urldecode($startTime);
			$startTime = str_replace('+', ' ', $startTime);
			$startTime = strtotime($startTime);

			$endTime   = addslashes($endTime);
			$endTime   = urldecode($endTime);
			$endTime   = str_replace('+', ' ', $endTime);
			$endTime   = strtotime($endTime);
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

		$count    = M('agent_recharge_recored')->where($where)->count();
		$page     = new \Think\Page($count, 25,$getData);

                $page->rollPage	= 5;

		$show     = $page->show();
		$this->assign('show', $show);

		$myRechargeRecored  =   D('AgentRechargeRecored')->where($where)->relation(true)->field(true)->select();
                $isAjax =   I('get.isAjax',0,'intval');
                if(!empty($isAjax) && $isAjax == 1){
                    foreach ($myRechargeRecored as $key => $value) {
                        $myRechargeRecored[$key]['add_time']        = date('Y-m-d H:i:s',$value['add_time']);
                    }
                    die(json_encode(array('code'=>1,'msg'=>'获取成功','info'=>$myRechargeRecored)));
                }
                
		$this->assign('myRechargeRecored', $myRechargeRecored);
		//        dump($myRechargeRecored);die;
		switch ($type) {
			case '0':   //个人购买
				$this->display('myPayRecored');
				break;
			case '1':   //平台充卡
				$this->display('myIssueRecored');
				break;
			case '2':   //下级返卡
				$this->display('myRebateRecored');
				break;
			default:
				break;
		}
	}

	/**
	 * [agentList 玩家列表]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function players() {
			//当前用户代理agent_id
			$agentId                =   session('agentId');
			$agent_info = M('agent')->where(array('id'=>$agentId))->field('id,invitation_code')->find();
			$gameuserList = M('gameuser')->where(array('invitation_code'=>$agent_info['invitation_code']))->field(true)->select();
			if (!empty($gameuserList)) {
					$game_user_id_list = array();
					foreach ($gameuserList as $key => $value) {
						$game_user_id_list[] = $value['game_user_id'];
					}
					$game_user_id_list = array_unique($game_user_id_list);
			}
			if (!empty($game_user_id_list)) {

			        $userid         =   I('get.keyword',0,'trim');
			        $where          =   array();
			//        $where['agent_id']  =   $agentId;
			        if($userid != 0){
			        	if (in_array($userid, $game_user_id_list)) {
				            $userid  = addslashes($userid);
				            $userid  = urldecode($userid);
				            $where['UserID'] = $userid;
			        	}else{
				            $where['UserID'] = 0;
			        	}
			        }else{
			            $where['UserID'] = array('in',$game_user_id_list);
			        }
					//        dump($where);die;
		                dblog(array('players 1002','where'=>$where,'game_user_id_list'=>$game_user_id_list));
                	$sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
					$count    = $sqlsrv_model->table('AccountsInfo')->where($where)->count();
		            $limit      =   I('limit',10,'intval');
		            $totalPages = ceil($count/$limit);
		            $p  =   I('get.page',1,'intval');
					$game_user_list = $sqlsrv_model->table('AccountsInfo')->where($where)->field("UserID,NickName,LastLogonDate,RegisterDate")->page($p, $limit)->order('UserID DESC')->select();
					if(!empty($game_user_list)){
                		$sqlsrv_model2   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
						foreach ($game_user_list as $key => $value) {
							$QPTreasureDB = $sqlsrv_model2->table('GameScoreInfo')->where(array('UserID'=>$value['userid']))->field('UserID,Score')->find();
							$game_user_list[$key]['THTreasureDB']   =   $QPTreasureDB;
						}
			//			            dump($game_user_list);die;
					}
		                
		            $isAjax =   I('get.isAjax',0,'intval');
		            if(!empty($isAjax) && $isAjax == 1){
		                foreach ($game_user_list as $key => $value) {
		                    $game_user_list[$key]['add_card_url']    =   U('addInsureScore',array('user_id'=>$value['userid']));
		                    $game_user_list[$key]['dateline']        = date('Y/m/d H:i:s',$value['registerdate']);
		                }
		                dblog(array('players 1001','where'=>$where,'game_user_list'=>$game_user_list));
		                die(json_encode(array('code'=>1,'msg'=>'获取成功','info'=>$game_user_list)));
		            }
			}
                
			$this->assign('game_user_list', $game_user_list);
			$this->assign('totalpage', $totalPages);
			$this->display();
	}

	/**
	 * [addAgent 充卡]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function addInsureScore() {
		//当前用户代理agent_id
		if( IS_POST ) {
                        
                        $agentId                =   session('agentId');
                        if(empty($agentId)){
                                die(json_encode(array('code'=>0,'msg'=>'代理信息错误','url'=>U('Home/Login'))));
                        }
                        $agent_info             =   M('agent')->where(array('id'=>$agentId))->field('id,user_id,phone,agent_password,room_card')->find();
                        if(empty($agent_info)){
                                die(json_encode(array('code'=>0,'msg'=>'代理信息错误','url'=>U('Home/Login'))));
                        }

			$game_user_id           =   I('post.user_id',0,'intval');
			if($game_user_id == 0){
                                die(json_encode(array('code'=>0,'msg'=>'玩家参数错误','url'=>U('Recharge/players'))));
			}
			$pay_nums               =   I('post.pay_nums',0,'intval');
			if($pay_nums == 0){
                                die(json_encode(array('code'=>0,'msg'=>'充值数量必填','url'=>'')));
			}

			$agent_password               =   I('post.agent_password','','trim');
			if($agent_password == ''){
                                die(json_encode(array('code'=>0,'msg'=>'请输入登录密码','url'=>'')));
			}

			if($agent_info['agent_password'] != think_ucenter_md5($agent_password)){
                                die(json_encode(array('code'=>0,'msg'=>'密码错误','url'=>'')));
			}

			if($agent_info['room_card'] <= 0 || $agent_info['room_card'] < $pay_nums){
                                die(json_encode(array('code'=>0,'msg'=>'代理房卡数量不足','url'=>'')));
			}

			//查当前代理房卡数量
    		$sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
			$playerCard  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$game_user_id))->field("UserID,Score")->find();
			if(empty($playerCard)){
                                die(json_encode(array('code'=>0,'msg'=>'玩家游戏数据错误','url'=>'')));
			}


			//代理减少房卡数
			$game_update                =   M('agent')->where(array('id'=>$agent_info['id']))->setField(array('room_card'=>$agent_info['room_card']-$pay_nums));
			if(!$game_update){
                                die(json_encode(array('code'=>0,'msg'=>'充值失败','url'=>'')));
			}
			//玩家增加房卡数
			$buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$playerCard['userid']))->setField(array('Score'=>$playerCard['score']+$pay_nums));

			//玩家信息
            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
			$player_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();

			//充值记录
			$user_recharge_recored_data  =   array();
			$user_recharge_recored_data['agent_id']      =   $agentId;
			$user_recharge_recored_data['user_id']       =   $player_accounts_info['userid'];
			$user_recharge_recored_data['pay_nums']      =   $pay_nums;
			$user_recharge_recored_data['desc']          =   '代理['.$agent_info['phone'].']成功为玩家['.$player_accounts_info['nickname'].']充值房卡：'.$pay_nums.'颗';
			$user_recharge_recored_data['add_time']      =   NOW_TIME;
			M('user_recharge_recored')->add($user_recharge_recored_data);

//			$this->success('充值成功', U('playersRecored'));
                        die(json_encode(array('code'=>1,'msg'=>'充值成功','url'=>U('Recharge/addInsureScoreSuccess'))));

		} else {
                    
                        $agentId                =   session('agentId');
                        $agent_info             =   M('agent')->where(array('id'=>$agentId))->field('id,user_id,phone,agent_password,room_card')->find();
                        if(empty($agent_info)){
                                $this->error('代理信息错误！');
                        }

			//玩家信息
			$game_user_id           =   I('get.user_id');

            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
			$game_user_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();

			//查当前代理房卡数量
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
			$user_treasure  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$game_user_id))->field("UserID,Score")->find();
			if(empty($user_treasure)){
				$this->error('玩家游戏数据错误');
			}
			$this->assign('user_info', $game_user_accounts_info);
			$this->assign('agent_info', $agent_info);
			$this->assign('user_treasure', $user_treasure);

			$this->display();
		}
	}
        
        public function addInsureScoreSuccess(){
                $this->display();
        }

        public function playersRecored(){

		$userid         =   I('get.userid',0,'intval');
		$agentId        =   session('agentId');
		$keyword		= 	I('get.keyword');
		$where          =   array();
		$where['agent_id'] = $agentId;
                if( !empty($keyword) ) {
			$keyword   = addslashes($keyword);
			$keyword   = urldecode($keyword);
                        $where['_complex'] 		= array(
				'user_id' 	=> array('eq', $keyword),
				'to_agent_id' 	=> array('eq', $keyword),
				'_logic' 	=> 'OR',
			);
		}

		$count    = M('user_recharge_recored')->where($where)->count();
                $limit      =   I('limit',10,'intval');
                $totalPages = ceil($count/$limit);
                $p  =   I('get.page',1,'intval');
		$user_recharge_recored_list   =   M('user_recharge_recored')->where($where)->field(true)->page($p, $limit)->order('id desc')->select();
		$isAjax =   I('get.isAjax',0,'intval');
                if(!empty($isAjax) && $isAjax == 1){
                    foreach ($user_recharge_recored_list as $key => $value) {
                        $user_recharge_recored_list[$key]['add_time']        = date('Y/m/d H:i:s',$value['add_time']);
                    }
                    die(json_encode(array('code'=>1,'msg'=>'获取成功','info'=>$user_recharge_recored_list)));
                }
                
                $this->assign('user_recharge_recored_list', $user_recharge_recored_list);
		$this->assign('totalpage', $totalPages);
		$this->display();
	}
	public function myPayDetail(){
		$id					= intval(I('get.id'));
		$type				= intval(I('get.type'));
		$where['id']		= $id;
		switch ($type) {
			case '0':   //个人购买
				$myRechargeRecored  = D('AgentRechargeRecored')->where($where)->relation(true)->field(true)->find();
				$this->assign('myRechargeRecored', $myRechargeRecored);
				$this->display('myPayRecoredDetail');
				break;
			case '1':   //平台充卡
				$myRechargeRecored  = D('AgentRechargeRecored')->where($where)->relation(true)->field(true)->find();
				$this->assign('myRechargeRecored', $myRechargeRecored);
				$this->display('myIssueRecoredDetail');
				break;
			case '2':   //下级返卡
				$myRechargeRecored  = D('AgentRechargeRecored')->where($where)->relation(true)->field(true)->find();
				$this->assign('myRechargeRecored', $myRechargeRecored);
				$this->display('myRebateRecoredDetail');
				break;
			case '11':   //充卡记录
				$myRechargeRecored   =   M('user_recharge_recored')->where($where)->field(true)->find();
				$this->assign('myRechargeRecored', $myRechargeRecored);
				$this->display('playersRecoredDetail');
				break;
			default:
				$this->display('myPayRecoredDetail');
				break;
		}
	}


	
    /*
     * 【代理】
     * 代理抽成
     */
    public function agentRebate(){
            $where      =   array();
            $where['type']  =   array('in',array(1,3));
            $where['agent_id'] = session('agentId');
            
            $startTime      = I('get.start_time');
            $endTime        = I('get.end_time');
            if( !empty($startTime) && !empty($endTime) ) {
                $startTime = addslashes($startTime);
                $startTime = urldecode($startTime);
                $startTime = str_replace('+', ' ', $startTime);
                $startTime = strtotime($startTime);

                $endTime   = addslashes($endTime);
                $endTime   = urldecode($endTime);
                $endTime   = str_replace('+', ' ', $endTime);
                $endTime   = strtotime($endTime);
                $where['dateline'] = array('BETWEEN', array($startTime, $endTime));
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
            
            $count    = M('agent_rebate_recored')->where($where)->count();
            $page     = new \Think\Page($count, 25);

            $show     = $page->show();
            $this->assign('show', $show);

            $myRechargeRecored  =   D('AgentRebateRecored')->where($where)->relation(true)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
            
            $isAjax =   I('get.isAjax',0,'intval');
            if(!empty($isAjax) && $isAjax == 1){
                foreach ($myRechargeRecored as $key => $value) {
                    $myRechargeRecored[$key]['dateline']        = date('Y-m-d H:i:s',$value['dateline']);
                }
                die(json_encode(array('code'=>1,'msg'=>'获取成功','info'=>$myRechargeRecored)));
            }
//            dump($myRechargeRecored);die;
            $this->assign('myRechargeRecored', $myRechargeRecored);
            
            $this->display('agentRebate');
    }

    /*
     * 【代理】
     * 推荐奖励
     */
    public function agentReward(){
            $where      =   array();
            $where['type']  =   array('in',array(2));
            $where['agent_id'] = session('agentId');
            
            $startTime      = I('get.start_time');
            $endTime        = I('get.end_time');
            if( !empty($startTime) && !empty($endTime) ) {
                $startTime = addslashes($startTime);
                $startTime = urldecode($startTime);
                $startTime = str_replace('+', ' ', $startTime);
                $startTime = strtotime($startTime);

                $endTime   = addslashes($endTime);
                $endTime   = urldecode($endTime);
                $endTime   = str_replace('+', ' ', $endTime);
                $endTime   = strtotime($endTime);
                $where['dateline'] = array('BETWEEN', array($startTime, $endTime));
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
            
            $count    = M('agent_rebate_recored')->where($where)->count();
            $page     = new \Think\Page($count, 25);

            $show     = $page->show();
            $this->assign('show', $show);

            $myRechargeRecored  =   D('AgentRebateRecored')->where($where)->relation(true)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();

            $isAjax =   I('get.isAjax',0,'intval');
            if(!empty($isAjax) && $isAjax == 1){
                foreach ($myRechargeRecored as $key => $value) {
                    $myRechargeRecored[$key]['dateline']        = date('Y-m-d H:i:s',$value['dateline']);
                }
                die(json_encode(array('code'=>1,'msg'=>'获取成功','info'=>$myRechargeRecored)));
            }
//            dump($myRechargeRecored);die;
            $this->assign('myRechargeRecored', $myRechargeRecored);
            
            $this->display('agentReward');
    }


    //玩家购买代理房卡
    public function playersBuyRecored(){
    		$agentId                =   session('agentId');
    		// $agentId = 1;
			$agent_info = M('agent')->where(array('id'=>$agentId))->field('id,invitation_code')->find();
			$gameuserList = M('gameuser')->where(array('invitation_code'=>$agent_info['invitation_code']))->field(true)->group('game_user_id')->select();
			$game_user_id_list = array();
			if (!empty($gameuserList)) {
					foreach ($gameuserList as $key => $value) {
						$game_user_id_list[] = $value['game_user_id'];
					}
			}
			// dump($game_user_id_list);
			if (!empty($game_user_id_list)) {
					$where = array();
					$where['uid'] = array('in',$game_user_id_list);
					$where['ispay'] = 1;
					$recharge_gold_list = M('recharge_gold')->cache(true,30)->where($where)->limit(100)->field(true)->order("id DESC")->select();

			}
			// dump($recharge_gold_list);
			$this->assign('recharge_gold_list',$recharge_gold_list);
			$this->display('playersBuyRecored');
    }

}
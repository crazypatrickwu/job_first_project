<?php
namespace Agent\Controller;
use Think\Controller;
// 玩家充卡控制器
class RechargeController extends BaseController {
    
        public function __construct() {
            parent::__construct();
            $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
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

		if ($this->iswap()) {
			$page->rollPage	= 5;
		}

		$show     = $page->show();
		$this->assign('show', $show);

		$myRechargeRecored  =   D('AgentRechargeRecored')->where($where)->relation(true)->field(true)->select();
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
        $userid         =   I('get.userid',0,'intval');
        $where          =   array();
//        $where['agent_id']  =   $agentId;
        if($userid != 0){
            $userid  = addslashes($userid);
            $userid  = urldecode($userid);
            $where['UserID'] = $userid;
        }else{
            $where['UserID'] = 0;
        }
		//        dump($where);die;
        $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
		$count    = $sqlsrv_model->table('AccountsInfo')->where($where)->count();
		$page     = new \Think\Page($count, 10);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
		}
		
		$show     = $page->show();

		$game_user_list = $sqlsrv_model->table('AccountsInfo')->where($where)->field("UserID,NickName,LastLogonDate,RegisterDate")->limit($page->firstRow . ',' . $page->listRows)->order('UserID DESC')->select();
		if(!empty($game_user_list)){
        	$sqlsrv_model2   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
			foreach ($game_user_list as $key => $value) {
				$QPTreasureDB = $sqlsrv_model2->table('GameScoreInfo')->where(array('UserID'=>$value['userid']))->field('UserID,InsureScore')->find();
				$game_user_list[$key]['THTreasureDB']   =   $QPTreasureDB;
			}
			//            dump($game_user_list);die;
		}
		$this->assign('game_user_list', $game_user_list);
		$this->assign('show', $show);
		$this->display();
	}

	/**
	 * [addAgent 充卡]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function addInsureScore() {
		//当前用户代理agent_id
		$agentId                =   session('agentId');
		$agent_info             =   M('agent')->where(array('id'=>$agentId))->field('id,user_id,phone,agent_password,room_card')->find();
		if(empty($agent_info)){
			$this->error('代理信息错误！');
		}
		if( IS_POST ) {
			//            dump($_POST);die;
			$game_user_id           =   I('post.user_id',0,'intval');
			if($game_user_id == 0){
				$this->error('玩家参数错误');
			}
			$pay_nums               =   I('post.pay_nums',0,'intval');
			if($pay_nums == 0){
				$this->error('充值数量必填');
			}

			$agent_password               =   I('post.agent_password','','trim');
			if($agent_password == ''){
				$this->error('请输入登录密码');
			}

			if($agent_info['agent_password'] != think_ucenter_md5($agent_password)){
				$this->error('密码错误！');
			}

			if($agent_info['room_card'] <= 0 || $agent_info['room_card'] < $pay_nums){
				$this->error('代理元宝数量不足！');
			}

            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
			//查当前代理元宝数量
			$playerCard  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$game_user_id))->field("UserID,InsureScore,Score")->find();
			if(empty($playerCard)){
				$this->error('玩家游戏数据错误');
			}


			//代理减少元宝数
			$game_update                =   M('agent')->where(array('id'=>$agent_info['id']))->setField(array('room_card'=>$agent_info['room_card']-$pay_nums));
			if(!$game_update){
				$this->error('充值失败');
			}
			//玩家增加元宝数
			$buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$playerCard['userid']))->setField(array('Score'=>$playerCard['score']+$pay_nums));

			//玩家信息
            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
			$player_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();

			//充值记录
			$user_recharge_recored_data  =   array();
			$user_recharge_recored_data['agent_id']      =   $agentId;
			$user_recharge_recored_data['user_id']       =   $player_accounts_info['userid'];
			$user_recharge_recored_data['pay_nums']      =   $pay_nums;
			$user_recharge_recored_data['desc']          =   '代理['.$agent_info['phone'].']成功为玩家['.$player_accounts_info['nickname'].']充值房卡'.$pay_nums.'颗';
			$user_recharge_recored_data['add_time']      =   NOW_TIME;
			M('user_recharge_recored')->add($user_recharge_recored_data);

			$this->success('充值成功', U('Player/playersRecored'));

		} else {

			//玩家信息
			$game_user_id           =   I('get.user_id');

            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
			$game_user_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();

			//查当前代理元宝数量
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
			$user_treasure  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$game_user_id))->field("UserID,InsureScore,Score")->find();
			if(empty($user_treasure)){
				$this->error('玩家游戏数据错误');
			}
			$this->assign('user_info', $game_user_accounts_info);
			$this->assign('agent_info', $agent_info);
			$this->assign('user_treasure', $user_treasure);

			$this->display();
		}
	}

	public function playersRecored(){

		$userid         =   I('get.userid',0,'intval');
		$buyer       =   I('get.buyer','','trim');
        $where          =   array();
        if ($buyer == 'agent') {
            $where['type']  =   2;
        }else{
            $where['type']  =   1;
        }
		$agentId        =   session('agentId');
		$keyword		= 	I('get.keyword');
		$where['agent_id'] = $agentId;
		if ( $userid != '0' ) {
			$userid  = addslashes($userid);
			$userid  = urldecode($userid);
			$where['user_id'] = $userid;
		}
		if (!empty($keyword)){
			$where['user_id'] = $keyword;
		}
		// dump($where);die;
		$count    = M('user_recharge_recored')->where($where)->count();
		$page     = new \Think\Page($count, 10);

		if ($this->iswap()) {
			$page->rollPage	= 5;
		}

		$show     = $page->show();
		$user_recharge_recored_list   =   M('user_recharge_recored')->where($where)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id desc')->select();
		$this->assign('user_recharge_recored_list', $user_recharge_recored_list);
		$this->assign('show', $show);
		//            dump($user_recharge_recored_list);die;
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

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }

            $show     = $page->show();
            $this->assign('show', $show);

            $myRechargeRecored  =   D('AgentRebateRecored')->where($where)->relation(true)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
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

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }

            $show     = $page->show();
            $this->assign('show', $show);

            $myRechargeRecored  =   D('AgentRebateRecored')->where($where)->relation(true)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
//            dump($myRechargeRecored);die;
            $this->assign('myRechargeRecored', $myRechargeRecored);
            
            $this->display('agentReward');
    }

}
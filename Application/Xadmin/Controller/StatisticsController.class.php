<?php
namespace Xadmin\Controller;
use Think\Controller;
// 玩家充卡控制器
class StatisticsController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
    }

    /*
    *@function:图表展示
    *@author:dingmali
    */
    public function fansChart(){
        $type  = I('get.type','all','trim');
        $date_type  = I('get.date_type','7day','trim');

        switch ($type) {
            case 'player':  //玩家
                $list   =   $this->fansChartPlayer($date_type);
                $this->assign('result',$list);
                $this->display('fansChart_player');
                break;
            case 'transaction': //交易
                $list   =   $this->fansChartTransaction($date_type);
                $this->assign('result',$list);
                $this->display('fansChart_transaction');
                break;
            case 'gameCurrency': //游戏币统计
                $list   =   $this->fansChartGameCurrency();
                $this->assign('result',$list);
                $this->display('fansChart_gameCurrency');
                break;
            default:
                $chartList   =   $this->fansChartAll();
                
                //总交易量
                $count  =   M('user_recharge_recored')->count();
                $transactionCount   = intval($count);
                $list['transactionCount']    =   $transactionCount;

                //代理总数
                $count  =   M('agent')->count();
                $agentCount   = intval($count);
                $list['agentCount']    =   $agentCount;
                
                //总玩家数量
                $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
                $count    = $sqlsrv_model->table('AccountsInfo')->count();
                $wxuserCount            = intval($count);
                $list['wxuserCount']    =   $wxuserCount;

                //当日交易量
                $start_time     = strtotime(date('Y-m-d'));
                $end_time     = strtotime(date('Y-m-d',  strtotime('+1 day')));
                $where  =   array();
                $where['add_time'] = array('BETWEEN', array($start_time, $end_time));
                $count  =   M('user_recharge_recored')->where($where)->count();
                $todayTransactionCount  = intval($count);
                $list['todayTransactionCount']    =   $todayTransactionCount;

                //当日新增玩家
                $where          =   array();
                $where['RegisterDate'] = array('BETWEEN', array(date('Y-m-d'), date('Y-m-d',  strtotime('+1 day'))));
                $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
                $count    = $sqlsrv_model->table('AccountsInfo')->where($where)->count();
        //        dump($sqlsrv_model->table('AccountsInfo')->getLastSql());die;
                $todayWxuserCount       = intval($count);
                $list['todayWxuserCount']    =   $todayWxuserCount;
//                dump($chartList);die;
                $this->assign('result',$list);
                $this->assign('chartList',$chartList);
                $this->display('fansChart_all');
                break;
        }
        
    }

    //游戏币统计
    public function fansChartGameCurrency(){
            // echo "<h1 style='text-align:center;'>敬请期待</h1>";die;
            $list = array();
            //旗力币总量
            //1、代理数据
            $agent_list = M('agent')->where(array('is_delete'=>0))->field('id,gold_coins,room_card')->select();

            $agent_qilibi_count = 0;
            $agent_fangka_count = 0;
            foreach ($agent_list as $key => $value) {
                $agent_qilibi_count += $value['gold_coins'];    //代理旗力币
                $agent_fangka_count += $value['room_card'];     //代理房卡
            }

            //2、玩家数据
            $sqlsrv_model = $this->sqlsrv_model('TreasureDB','GameScoreInfo');
            $GameScoreInfo    = $sqlsrv_model->table('GameScoreInfo')->where($where)->field('UserID,Score,InsureScore')->select();
            
            $player_qilibi_count = 0;
            $player_fangka_count = 0;
            foreach ($GameScoreInfo as $key => $value) {
                $player_qilibi_count += $value['score'];  //玩家旗力币
                $player_fangka_count += $value['insurescore'];        //玩家房卡
            }

            $list['qilibiTotalCount'] = $player_qilibi_count;

            //平台房卡总量
            $roomcardTotalCount = 0;
            $list['roomcardTotalCount'] = $player_fangka_count;

            //人民币充值总金额
            $rmbTotalSum = 0;
            $where = array();
            $where['status'] = 1;
            $where['type'] = 2;
            $rmbTotalSum = M('pay')->where($where)->sum('money');
            $list['rmbTotalSum'] = $rmbTotalSum;

            //房卡消耗总量
            $roomcardConsumeTotalCount = 0;
            $sqlsrv_model = $this->sqlsrv_model('PlatformDB', 'PrivateCost');
            $roomcardConsumeTotalCount    = $sqlsrv_model->table('PrivateCost')->where($where)->sum('InsureScore');
            $list['roomcardConsumeTotalCount'] = abs($roomcardConsumeTotalCount);

            //赠送房卡总量
            $giveRoomcardTotalCount = 0;
            $giveRoomcardTotalCount = M('user_recharge_recored')->where(array('type'=>array('in',array(3,4,5))))->sum('pay_nums');
            $list['giveRoomcardTotalCount'] = intval($giveRoomcardTotalCount);


//                dump($list);die;
            return $list;
    }
    
    public function fansChartAll(){
        
        //30天玩家数据
        $dateArr    =   array();
        $dataArrWan =   array();
        for($i = 30;$i>0;$i--){
            $dateArr[]  =   date("Ymd",strtotime("-$i day"));
            $start  =   $i;
            $end    =   $i-1;
            $dataArrWan[]   =   array(
                'start_time'    =>  date("Y-m-d H:i:s",strtotime("-$start day")),
                'end_time'      =>  date("Y-m-d H:i:s",strtotime("-$end day"))
            );
        }
        $wxuserCountArr    =   S('wxuserCountArr_30day');
        if(empty($wxuserCountArr)){
                foreach ($dateArr as $key => $value) {
                    //日增玩家
                    $where          =   array();
                    $where['RegisterDate'] = array('elt', $dataArrWan[$key]['end_time']);

                    $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
                    $count    = $sqlsrv_model->table('AccountsInfo')->where($where)->count();
                    $wxuserCountArr[]  = intval($count);
                    //日活跃粉丝统计
                }
                S('wxuserCountArr_30day',$wxuserCountArr,3600);
        }
        $list['wxuserCountArr']    = $wxuserCountArr;
        
        //30天交易数据
        $transactionCountArr    =   S('transactionCountArr_30day');
        if(empty($transactionCountArr)){
                foreach ($dateArr as $key => $value) {
                    //日增交易
                    $start_time     = strtotime(intval($value));
                    $end_time       =   strtotime(intval($value)+1);
                    $where  =   array();
                    $where['add_time'] = array('elt', $end_time);
                    $count  =   M('user_recharge_recored')->where($where)->count();
                    $transactionCountArr[] 					= intval($count);

                }
                S('transactionCountArr_30day',$transactionCountArr,3600);
        }
        $list['transactionCountArr']    = $transactionCountArr;
        $list['dateArr']    = $dateArr;
        
//        dump($list);die;
        $list =   json_encode($list);
        return $list;
    }


    /*
     * 玩家统计
     */
    public function fansChartPlayer($date_type){
        $ChartDataPlayer    =   S('ChartDataPlayer_'.$date_type);
        if(!empty($ChartDataPlayer)){
            return $ChartDataPlayer;
        }
        switch ($date_type) {
          case '7day':  //7天
            # code...
            $list   =   $this->getChartDataPlayer(7);
            break;
          case '30day'://30天
            # code...
            $list   =   $this->getChartDataPlayer(30);
            break;
          case '3month'://3个月
            # code...
            $list   =   $this->getChartDataPlayer(90);
            break;
          default:
            # code...
            break;
        }
        S('ChartDataPlayer_'.$date_type,$list,3600);
        return $list;
    }
    
    /*
     * 交易统计
     */
    public function fansChartTransaction($date_type){
        $ChartDataTransaction    =   S('ChartDataTransaction_'.$date_type);
        if(!empty($ChartDataTransaction)){
            return $ChartDataTransaction;
        }
        switch ($date_type) {
          case '7day':  //7天
            # code...
            $list = $this->getChartDataTransaction(7);
            break;
          case '30day'://30天
            # code...
            $list = $this->getChartDataTransaction(30);
            break;
          case '3month'://3个月
            # code...
            $list = $this->getChartDataTransaction(90);
            break;
          default:
            # code...
            break;
        }
        S('ChartDataTransaction_'.$date_type,$list,3600);
        return $list;
    }

    /*
    *获取图表相关数据
    */
    protected function getChartDataTransaction($how_day){
        $dateArr    =   array();
        $dataArrWan =   array();
        for($i = $how_day;$i>0;$i--){
            $dateArr[]  =   date("Ymd",strtotime("-$i day"));
            
            $start  =   $i;
            $end    =   $i-1;
            $dataArrWan[]   =   array(
                'start_time'    =>  date("Y-m-d H:i:s",strtotime("-$start day")),
                'end_time'      =>  date("Y-m-d H:i:s",strtotime("-$end day"))
            );
        }
        $oneAddTransactionArr   =   array();
        foreach ($dateArr as $key => $value) {
            //日增交易
            $start_time     = strtotime(intval($value));
            $end_time       =   strtotime(intval($value)+1);
            $where  =   array();
            $where['add_time'] = array('BETWEEN', array($start_time, $end_time));
            $count  =   M('user_recharge_recored')->where($where)->count();
            $oneAddTransactionArr[] 					= intval($count);
            
        }
        $list['oneAddTransactionArr']    =   $oneAddTransactionArr;
        $list['dateArr']    =   $dateArr;
//        dump($list);die;
        $result =   json_encode($list);
        return $result;
    }
    
    
    /*
    *获取图表相关数据
    */
    protected function getChartDataPlayer($how_day){
        $dateArr    =   array();
        $dataArrWan =   array();
        for($i = $how_day;$i>0;$i--){
            $dateArr[]  =   date("Ymd",strtotime("-$i day"));
            
            $start  =   $i;
            $end    =   $i-1;
            $dataArrWan[]   =   array(
                'start_time'    =>  date("Y-m-d H:i:s",strtotime("-$start day")),
                'end_time'      =>  date("Y-m-d H:i:s",strtotime("-$end day"))
            );
        }
        $oneAddWxuserArr   =   array();

        $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
        foreach ($dateArr as $key => $value) {
            //日增玩家
            $where          =   array();
            $where['RegisterDate'] = array('BETWEEN', array($dataArrWan[$key]['start_time'], $dataArrWan[$key]['end_time']));
            $count    = $sqlsrv_model->table('AccountsInfo')->where($where)->count();
            $oneAddWxuserArr[]  = intval($count);
            //日活跃粉丝统计
        }
        $list['oneAddWxuserArr']    =   $oneAddWxuserArr;
        $list['dateArr']    =   $dateArr;
//        dump($list);die;
        $result =   json_encode($list);
        return $result;
    }
    

    /**
     * [agentList 供货商列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function players() {
        //当前用户代理agent_id
        $userid         =   I('userid',0,'intval');
        $where          =   array();
        if ( $userid != '0' ) {
            $userid  = addslashes($userid);
            $userid  = urldecode($userid);
            $where['UserID'] = $userid;
        }
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
                $QPTreasureDB = $sqlsrv_model2->table('GameScoreInfo')->where(array('UserID'=>$value['userid']))->field('UserID,InsureScore,Score')->find();
                $game_user_list[$key]['THTreasureDB']   =   $QPTreasureDB;
            }
//            dump($game_user_list);die;
        }
        $this->assign('game_user_list', $game_user_list);
        $this->assign('show', $show);
        $this->display();
    }

    /**
     * [addAgent 添加供货商]
     * @author TF <[2281551151@qq.com]>
     */
    public function addInsureScore() {
        //当前用户代理agent_id
        $agentId                =   session('agentId');
        $agent_info             =   M('agent')->where(array('id'=>$agentId))->field('id,user_id')->find();
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

            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
            //查当前代理房卡数量
            $GameScoreInfoList  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>array('in',array($agent_info['user_id'],$game_user_id))))->field("UserID,InsureScore")->select();

            $agent_treasure     =   array();    //代理游戏数据
            $user_treasure      =   array();    //玩家游戏数据
            foreach ($GameScoreInfoList as $key => $value) {
                if($value['userid'] == $agent_info['user_id']){
                    $agent_treasure =   $value;
                }  elseif ($value['userid'] == $game_user_id) {
                    $user_treasure  =   $value;
                }
            }

            //验证
            if(empty($agent_treasure)){
                    $this->error('代理游戏数据错误');
            }
            if(empty($user_treasure)){
                    $this->error('玩家游戏数据错误');
            }

            if($agent_treasure['insurescore'] <= 0 || $agent_treasure['insurescore'] < $pay_nums){
                    $this->error('代理房卡数量不足！');
            }

            //代理减少房卡数
            $seller_gamescore_update    =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$agent_treasure['userid']))->setField(array('InsureScore'=>$agent_treasure['insurescore']-$pay_nums));

            //玩家增加房卡数
            $buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$user_treasure['userid']))->setField(array('InsureScore'=>$user_treasure['insurescore']+$pay_nums));

            //玩家信息
            $game_user_id           =   I('get.user_id');

            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
            $game_user_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();

            //充值记录
            $user_recharge_recored_data  =   array();
            $user_recharge_recored_data['agent_id']      =   $agentId;
            $user_recharge_recored_data['seller_user_id']=   $agent_treasure['userid'];
            $user_recharge_recored_data['buyer_user_id'] =   $user_treasure['userid'];
            $user_recharge_recored_data['pay_nums']      =   $pay_nums;
            $user_recharge_recored_data['desc']          =   '成功为用户['.$game_user_accounts_info['nickname'].']充值房卡：'.$pay_nums.'颗';
            $user_recharge_recored_data['add_time']      =   NOW_TIME;
            M('user_recharge_recored')->add($user_recharge_recored_data);

            $this->success('充值成功', U('recored'));

        } else {

            //玩家信息
            $game_user_id           =   I('get.user_id');

            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
            $game_user_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();

            //查当前代理房卡数量
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
            $GameScoreInfoList  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>array('in',array($agent_info['user_id'],$game_user_id))))->field("UserID,InsureScore")->select();
            if(empty($GameScoreInfoList)){
                    $this->error('游戏数据错误');
            }

            $agent_treasure     =   array();    //代理游戏数据
            $user_treasure      =   array();    //玩家游戏数据
            foreach ($GameScoreInfoList as $key => $value) {
                if($value['userid'] == $agent_info['user_id']){
                    $agent_treasure =   $value;
                }  elseif ($value['userid'] == $game_user_id) {
                    $user_treasure  =   $value;
                }
            }

            if(empty($agent_treasure)){
                    $this->error('代理游戏数据错误');
            }
            if(empty($user_treasure)){
                    $this->error('玩家游戏数据错误');
            }

//            dump($game_user_accounts_info);die;
            $this->assign('game_user_accounts_info', $game_user_accounts_info);
            $this->assign('agent_treasure', $agent_treasure);
            $this->assign('user_treasure', $user_treasure);

            $this->display();
        }
    }
    //游戏充值商品
    protected function recharge_goods_list(){
        return  array(
            1   =>  array('price'=>10,'num'=>100000,'type'=>'jb'),
            2   =>  array('price'=>20,'num'=>200000,'type'=>'jb'),
            3   =>  array('price'=>100,'num'=>1000000,'type'=>'jb'),
            4   =>  array('price'=>5,'num'=>1,'type'=>'fk'),
            5   =>  array('price'=>25,'num'=>5,'type'=>'fk'),
            6   =>  array('price'=>50,'num'=>10,'type'=>'fk'),
            7   =>  array('price'=>100,'num'=>25,'type'=>'fk'),
            8   =>  array('price'=>500,'num'=>130,'type'=>'fk'),
            9   =>  array('price'=>1000,'num'=>280,'type'=>'fk'),
        );
    }

    /*
     * 玩家房卡记录
     */
    public function playersRecored(){
            $type       =   I('get.type',0,'intval');

            switch ($type) {
                case '0':   //代理充房卡
                    $keyword         =   I('keyword','','trim');
                    $where          =   array();
                    if( !empty($keyword) ) {
                            $keyword   = addslashes($keyword);
                            $keyword   = urldecode($keyword);
                            $where['_complex'] 		= array(
                                    'user_id' 	=> array('eq', $keyword),
                                    'to_agent_id' 	=> array('eq', $keyword),
                                    '_logic' 	=> 'OR',
                            );
                    }
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

                    $count    = M('user_recharge_recored')->where($where)->count();
                    $page     = new \Think\Page($count, 10,$getData);

                                if ($this->iswap()) {
                                        $page->rollPage	= 5;
                                }

                    $show     = $page->show();
                    $user_recharge_recored_list   =   D('UserRechargeRecored')->where($where)
                                                ->field(true)
                                                ->relation(true)
                                                ->limit($page->firstRow . ',' . $page->listRows)
                                                ->order('id desc')
                                                ->select();
                    $this->assign('user_recharge_recored_list', $user_recharge_recored_list);
                    $this->assign('show', $show);
                    $this->display('playersRecored');
                    break;
                case '1':   //手机充房卡
                    $userid         =   I('userid',0,'intval');
                    $where          =   array();
                    if ( $userid != '0' ) {
                        $userid  = addslashes($userid);
                        $userid  = urldecode($userid);
                        $where['uid'] = $userid;
                    }
                    $where['ispay']    =   1;
                    $count    = M('recharge_roomcard')->where($where)->count();
                    $page     = new \Think\Page($count, 10);

                                if ($this->iswap()) {
                                        $page->rollPage	= 5;
                                }

                    $show     = $page->show();
                    $user_recharge_recored_list   =   M('recharge_roomcard')->where($where)
                                                ->field(true)
                                                ->limit($page->firstRow . ',' . $page->listRows)
                                                ->order('id desc')
                                                ->select();
//                                        dump($user_recharge_recored_list);die;
                    $this->assign('user_recharge_recored_list', $user_recharge_recored_list);
                    $this->assign('show', $show);
                    $this->display('playersRoomCard');
                    break;
                case '2':   //手机充金币
                    $userid         =   I('userid',0,'intval');
                    $where          =   array();
                    if ( $userid != '0' ) {
                        $userid  = addslashes($userid);
                        $userid  = urldecode($userid);
                        $where['uid'] = $userid;
                    }
                    $where['ispay']    =   1;
                    $count    = M('recharge_gold')->where($where)->count();
                    $page     = new \Think\Page($count, 10);

                                if ($this->iswap()) {
                                        $page->rollPage	= 5;
                                }

                    $show     = $page->show();
                    $user_recharge_recored_list   =   M('recharge_gold')->where($where)
                                                ->field(true)
                                                ->limit($page->firstRow . ',' . $page->listRows)
                                                ->order('id desc')
                                                ->select();
//                                        dump($user_recharge_recored_list);die;
                    $this->assign('user_recharge_recored_list', $user_recharge_recored_list);
                    $this->assign('show', $show);
                    $this->display('playersGold');
                    break;
                default:
                    break;
            }
    }

    /*
     * 代理房卡记录
     */
    public function agentsRecored(){
        $type       =   I('get.type',0,'intval');
        $where      =   array();
        $where['type']  =   $type;

        $count    = M('agent_recharge_recored')->where($where)->count();
        $page     = new \Think\Page($count, 25);

		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}

        $show     = $page->show();
        $this->assign('show', $show);

        $myRechargeRecored  =   D('AgentRechargeRecored')->where($where)->relation(true)->field(true)->order('id DESC')->select();
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

}

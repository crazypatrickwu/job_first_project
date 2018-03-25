<?php
namespace Xadmin\Controller;
use Think\Controller;
// 玩家充卡控制器
class RechargeController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
    }
    /**
     * [agentList 供货商列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function players() {

        $startTime      = I('get.start_time');
        $endTime        = I('get.end_time');
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

                //实名认证
                $gameuser_authentication = M('gameuser_authentication')->where(array('game_user_id'=>$value['userid']))->field('id,name,idcard')->find();
                $game_user_list[$key]['is_authentication']   =   !empty($gameuser_authentication['idcard']) ? '<font color="green">已认证√</font>' : '<font color="red">未认证</font>';
                $game_user_list[$key]['authentication_name']    =   $gameuser_authentication['name'];
                $game_user_list[$key]['authentication_idcard']    =   $gameuser_authentication['idcard'];

                //邀请码
                $gameuser = M('gameuser')->where(array('game_user_id'=>$value['userid'],'invitation_code'=>array('neq','')))->field(true)->find();
                $game_user_list[$key]['invitation_code']   =   !empty($gameuser['invitation_code']) ? $gameuser['invitation_code'] : '-';
            }
//            dump($game_user_list);die;
        }
        $this->assign('game_user_list', $game_user_list);
        $this->assign('show', $show);
        $this->display();
    }

    //修改玩家邀请码
    public function invitationCodeBind()
    {
        if (IS_POST) {
            $adminId                =   session('adminId');
            if(!$adminId){
                $this->error('登录信息已过期', U('Login/login'));
            }
            $admin_info             =   M('admin')->where($adminId)->field(true)->find();
            if(empty($admin_info)){
                $this->error('登录信息不存在或已过期', U('Login/login'));
            }
            $admin_password               =   I('post.admin_password','','trim');
            if($admin_password == ''){
                $this->error('请输入登录密码');
            }
            
            if($admin_info['admin_password'] != think_ucenter_md5($admin_password)){
                $this->error('密码错误！');
            }

            $game_user_id = I('post.userid',0,'intval');
            if (empty($game_user_id)) {
                    $this->error('参数错误');
            }
            $invitation_code = I('post.invitation_code','','trim');
            if (empty($invitation_code)) {
                    $this->error('邀请码不得为空');
            }
            $agent_info = M('agent')->where(array('invitation_code'=>$invitation_code))->field(true)->find();
            if (empty($agent_info)) {
                    $this->error('此邀请码不存在');
            }
            if ($agent_info['is_delete'] == 1 || $agent_info['is_lock'] == 1) {
                    $this->error('请输入正确的邀请码');
            }


            $where = array();
            $where['game_user_id'] =  $game_user_id;
            $res = M('gameuser')->where($where)->setField(array('invitation_code'=>$invitation_code));

            if ($res) {
                $this->success('操作成功', U('players'));
            }else{
                $this->error('操作失败');
            }
        }else{

            $game_user_id = I('get.userid',0,'intval');

            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
            $game_user_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();
            // dump($game_user_accounts_info);die;
            $where = array();
            $where['game_user_id'] =  $game_user_id;
            $where['user_id'] =  0;
            $gameuser = M('gameuser')->where($where)->field(true)->find();

            $this->assign('game_user_accounts_info',$game_user_accounts_info);
            $this->assign('gameuser',$gameuser);
            $this->display();
        }
    }

    /**
     * [agentList 玩家充值记录]
     * @author TF <[2281551151@qq.com]>
     */
    public function playersChargeRecored() {
        //当前用户代理agent_id
        $line_type      =I('get.type', 1);
        $startTime      = I('get.start_time');
        $endTime        = I('get.end_time');
        $id             = I('get.userid',0,'intval');
        $where = array();        

        if( !empty($startTime) && !empty($endTime) ) {
            $startTime = addslashes($startTime);
            $startTime = urldecode($startTime);
            $startTime = str_replace('+', ' ', $startTime);
            $startTime = strtotime($startTime);

            $endTime   = addslashes($endTime);
            $endTime   = urldecode($endTime);
            $endTime   = str_replace('+', ' ', $endTime);
            $endTime   = strtotime($endTime);
            $where['datetime'] = array('BETWEEN', array($startTime, $endTime));
        }
        if($id >0){
            $where['UserID']=   $id;            
        }
        $getData = array();
        $get = I('get.');
        foreach ($get as $key => $value) {
            if (!empty($key)) {
                $getData[$key] = urldecode($value);
            }
        }

        $sqlsrv_model   = $this->sqlsrv_model('TreasureDB', 'PrivateCostInfo');
        $count    = $sqlsrv_model->table('PrivateCostInfo')->where($where)->count();
        $page     = new \Think\Page($count, 10,$getData);

        if ($this->iswap()) {
            $page->rollPage = 5;
        }

        $show     = $page->show();

        $game_user_list = $sqlsrv_model->table('PrivateCostInfo')->where($where)->field("UserID,InsureScore,Time")->limit($page->firstRow . ',' . $page->listRows)->order('Time DESC')->select();
        if(!empty($game_user_list)){
            $sqlsrv_model2   = $this->sqlsrv_model('AccountsDB', 'AccountsInfo');
            foreach ($game_user_list as $key => $value) {
                $QPAccountsDB = $sqlsrv_model2->table('AccountsInfo')->where(array('UserID'=>$value['userid']))->field('UserID,Accounts,NickName,MemberOrder')->find();
                $game_user_list[$key]['THAccountsDB']   =   $QPAccountsDB;
            }

        }

        $InsureScoreCount    = $sqlsrv_model->table('PrivateCostInfo')->where($where)->sum('InsureScore');
        //******导出excel数据整理start******
        $export_type = I('get.export_type','');
        if($export_type !== ''){
            $info=array();
            foreach($game_user_list as $k=>$v){
                $info[$k]['userid']=$v['userid'];
                $info[$k]['nickname']=$v['THAccountsDB']['nickname'];
                $info[$k]['accounts']=$v['THAccountsDB']['accounts'];
                $info[$k]['score']=$v['score'];
                $info[$k]['datetime']=date("Y-m-d H:i:s ",$v['datetime']);

            }

            $dataResult = $info;
            $title = "玩家消费列表";

            $titlename = "<tr style='text-align: center;'><th style='width:100px;'>玩家ID</th><th style='width:110px;'>玩家昵称</th><th style='width:180px;'>玩家账号</th><th style='width:100px;'>充值数量</th><th style='width:180px;'>充值时间</th></tr>"; 
            $filename = $title.".xls"; 
            $this->excelData($dataResult,$titlename,$headtitle,$filename);
        }
        //******导出excel数据整理end******
        $this->assign('game_user_list', $game_user_list);
        $this->assign('line_type', $line_type);
        $this->assign('InsureScoreCount', intval($InsureScoreCount));
        $this->assign('show', $show);
        $this->display();
    }

    /**
     * [addAgent 充值旗力币]
     * @author TF <[2281551151@qq.com]>
     */
    public function addInsureScore() {
        if( IS_POST ) {
            $agent_info      =M('admin')->where(array('id'=>1))->find();      
            $game_user_id    =   I('post.id',0,'intval');
            if($game_user_id == 0){
                $this->error('玩家参数错误');
            }
            $pay_nums               =   I('post.pay_nums',0,'intval');
            if($pay_nums == 0){
                $this->error('充值数量必填');
            }

            $admin_password               =   I('post.admin_password','','trim');
            if($admin_password == ''){
                $this->error('请输入登录密码');
            }

            if($agent_info['admin_password'] != think_ucenter_md5($admin_password)){
                $this->error('密码错误！');
            }

            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
            //查当前玩家元宝数量
            $playerCard  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$game_user_id))->field("UserID,InsureScore")->find();
            //玩家增加旗力币数
            $buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$playerCard['userid']))->setField(array('InsureScore'=>$playerCard['insurescore']+$pay_nums));

            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
            $player_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();
            // print_r($player_accounts_info);exit;
            //充值记录
            $user_recharge_recored_data  =   array();
            $user_recharge_recored_data['type']          =   1;
            $user_recharge_recored_data['agent_id']      =   0;
            $user_recharge_recored_data['user_id']       =   $player_accounts_info['userid'];
            $user_recharge_recored_data['pay_nums']      =   $pay_nums;
            $user_recharge_recored_data['desc']          =   '平台成功为玩家['.$player_accounts_info['nickname'].']充值旗力币：'.$pay_nums.'个';
            $user_recharge_recored_data['add_time']      =   NOW_TIME;
            $res=M('user_recharge_recored')->add($user_recharge_recored_data);
            $orderInfo['uid'] =$game_user_id;
            $orderInfo['nums']=$pay_nums;
            if($res>0){
                $this->success('充值成功', U('playersRecored',array('type'=>5)));
                // $this->commissionToAgentByPlayer($orderInfo);
            }

        } else {

            //玩家信息
            $game_user_id           =   I('get.user_id');

            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
            $game_user_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();

            //查当前玩家数量
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
            $user_treasure  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$game_user_id))->field("UserID,InsureScore")->find();
            if(empty($user_treasure)){
                $this->error('玩家游戏数据错误');
            }
            $this->assign('user_info', $game_user_accounts_info);
            $this->assign('user_treasure', $user_treasure);

            $this->display();
        }
    }


    /**
     * [addScore 充值房卡]
     * @author TF <[2281551151@qq.com]>
     */
    public function addScore() {
        if( IS_POST ) {
            $agent_info      =M('admin')->where(array('id'=>1))->find();      
            $game_user_id    =   I('post.id',0,'intval');
            if($game_user_id == 0){
                $this->error('玩家参数错误');
            }
            $pay_nums               =   I('post.pay_nums',0,'intval');
            if($pay_nums == 0){
                $this->error('充值数量必填');
            }

            $admin_password               =   I('post.admin_password','','trim');
            if($admin_password == ''){
                $this->error('请输入登录密码');
            }

            if($agent_info['admin_password'] != think_ucenter_md5($admin_password)){
                $this->error('密码错误！');
            }

            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
            //查当前玩家元宝数量
            $playerCard  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$game_user_id))->field("UserID,InsureScore,Score")->find();
            //玩家增加旗力币数
            $buyer_gamescore_update     =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$playerCard['userid']))->setField(array('Score'=>$playerCard['score']+$pay_nums));

            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
            $player_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();
            // print_r($player_accounts_info);exit;
            //充值记录
            $user_recharge_recored_data  =   array();
            $user_recharge_recored_data['type']          =   1;
            $user_recharge_recored_data['agent_id']      =   0;
            $user_recharge_recored_data['user_id']       =   $player_accounts_info['userid'];
            $user_recharge_recored_data['pay_nums']      =   $pay_nums;
            $user_recharge_recored_data['desc']          =   '平台成功为玩家['.$player_accounts_info['nickname'].']充值房卡：'.$pay_nums.'个';
            $user_recharge_recored_data['add_time']      =   NOW_TIME;
            $res=M('user_recharge_recored')->add($user_recharge_recored_data);
            $orderInfo['uid'] =$game_user_id;
            $orderInfo['nums']=$pay_nums;
            if($res>0){
                $this->success('充值成功', U('playersRecored',array('type'=>5)));
                // $this->commissionToAgentByPlayer($orderInfo);
            }

        } else {

            //玩家信息
            $game_user_id           =   I('get.user_id');

            $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
            $game_user_accounts_info   =   $sqlsrv_model->table('AccountsInfo')->where(array('UserID'=>$game_user_id))->field("UserID,NickName,LastLogonDate,RegisterDate")->find();

            //查当前玩家数量
            $sqlsrv_model   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
            $user_treasure  =   $sqlsrv_model->table('GameScoreInfo')->where(array('UserID'=>$game_user_id))->field("UserID,InsureScore,Score")->find();
            if(empty($user_treasure)){
                $this->error('玩家游戏数据错误');
            }
            $this->assign('user_info', $game_user_accounts_info);
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

    //平台给玩家充值旗力币记录
    public function platformPlayersInsurescoreRecored(){
            $keyword         =   I('keyword','','trim');
            $where['type']=1;
            if( !empty($keyword) ) {
                    $keyword   = addslashes($keyword);
                    $keyword   = urldecode($keyword);
                    $where['_complex']      = array(
                            'user_id'   => array('eq', $keyword),
                            'to_agent_id'   => array('eq', $keyword),
                            '_logic'    => 'OR',
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
                                $page->rollPage = 5;
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
            $this->display('playersRecoreds');

    }

    //玩家购买旗力币
    public function playersBuyInsurescore(){
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
                                $page->rollPage = 5;
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
    }


    //玩家购买房卡
    public function playersBuyScore(){
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
                                $page->rollPage = 5;
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
    }


    /*
     * 玩家房卡记录
     */
    public function playersRecored(){
            $type       =   I('get.type',0,'intval');

            switch ($type) {
                case '0':   //代理充房卡
                    $keyword         =   I('keyword','','trim');
                    $buyer       =   I('get.buyer',0,'trim');
                    $where          =   array();
                    if ($buyer == 'agent') {
                        $where['type']  =   2;
                    }else{
                        $where['type']  =   1;
                    }
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
                case '2':   //手机充旗力币
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
                case '5':   //代理充房卡
                    $keyword         =   I('keyword','','trim');
                    $buyer       =   I('get.buyer',0,'trim');
                    $where['type']=1;
                    if( !empty($keyword) ) {
                            $keyword   = addslashes($keyword);
                            $keyword   = urldecode($keyword);
                            $where['_complex']      = array(
                                    'user_id'   => array('eq', $keyword),
                                    'to_agent_id'   => array('eq', $keyword),
                                    '_logic'    => 'OR',
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
                                        $page->rollPage = 5;
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
                    $this->display('playersRecoreds');
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
    
    
    /*
     * 【代理】
     * 激活礼包
     */
    public function agentGift(){
//            $type       =   I('get.type',0,'intval');
            $where      =   array();
//            $where['type']  =   $type;
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

            $count    = M('agent_recharge_package')->where($where)->count();
            $page     = new \Think\Page($count, 20,$getData);

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }

            $show     = $page->show();
            $this->assign('show', $show);

            $myRechargeRecored  =   D('AgentRechargePackage')->where($where)->relation(true)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
//            dump($myRechargeRecored);die;
            $this->assign('myRechargeRecored', $myRechargeRecored);
            
            $this->display('agentGift');
    }
    
    /*
     * 【代理】
     * 代理抽成
     */
    public function agentRebate(){
            $where      =   array();
            $where['type']  =   array('in',array(1,3));
            
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

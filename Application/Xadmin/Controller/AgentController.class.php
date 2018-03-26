<?php
namespace Xadmin\Controller;
use Think\Controller;
// 供货商控制器
class AgentController extends BaseController {

    public function __construct() {
        parent::__construct();
        $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
    }



    /**
     * [agentList 供货商列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function agentList() {
        $startTime      = I('get.start_time');
        $endTime        = I('get.end_time');
        $nickname       = I('get.nickname');
        $phone          = I('get.phone');

        $where = array();
        $whereStr = '';
        
        // $where['is_delete'] =   0;
        if( !empty($nickname) ) {
            $nickname   = addslashes($nickname);
            $nickname   = urldecode($nickname);
            $whereStr .= "AND o.nickname LIKE \"%{$nickname}%\" ";
            $where['nickname'] = array('LIKE', "%{$nickname}%");
        }
        if( !empty($phone) ) {
            $phone   = addslashes($phone);
            $phone   = urldecode($phone);
            $whereStr .= "AND o.phone LIKE \"%{$phone}%\" ";
            $where['phone'] = array('LIKE', "%{$phone}%");
        }

        if (!empty($startTime) && !empty($endTime)) {
            $startTime = addslashes($startTime);
            $startTime = urldecode($startTime);
            $startTime = str_replace('+', ' ', $startTime);
            $startTime = strtotime($startTime);

            $endTime = addslashes($endTime);
            $endTime = urldecode($endTime);
            $endTime = str_replace('+', ' ', $endTime);
            $endTime = strtotime($endTime);
            $whereStr .= "AND o.dateline BETWEEN {$startTime} AND {$endTime} ";
            $where['dateline'] = array('BETWEEN', array($startTime, $endTime));
        }


        $getData = array();
        $get = I('get.');
        foreach ($get as $key => $value) {
            if (!empty($key)) {
                $getData[$key] = urldecode($value);
            }
        }

        if (!empty($getData['add_time'])) {
            $getData['add_time'] = search_time_format($getData['add_time']);
        }

        if (!empty($getData['end_time'])) {
            $getData['end_time'] = search_time_format($getData['end_time']);
        }
        
        $AgentModel = M('agent');

        $count    = $AgentModel->where($where)->count();
        $page     = new \Think\Page($count, 25,$getData);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show     = $page->show();

        $agentList = $AgentModel->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('dateline DESC')->select();
        $AGENT_LEVEL = C('AGENT_LEVEL');

        $configList = M('agent_rebate_config')->where('1=1')->field(true)->order('id ASC')->select();
        // $levelData  =   C('AGENT_LEVEL');
        $levelData  =   array();
        foreach ($configList as $key => $value) {
            $levelData[$value['id']] = $value;
        }

        foreach ($agentList as $key => $value) {
            //下级人数
            $son_agent_count = M('agent')->where(array('pid'=>$value['id']))->count();
            $agentList[$key]['son_count'] = intval($son_agent_count);

            //玩家人数
            $player_count = M('gameuser')->where(array('invitation_code'=>$value['invitation_code'],'user_id'=>0))->count();
            $agentList[$key]['player_count'] = intval($player_count);


            if(!empty($value['pid'])){
                $parent_agent_info  =   M('agent')->where(array('id'=>$value['pid']))->field('id,nickname')->find();
                $agentList[$key]['p_info']  =   $parent_agent_info;

                $rebateMoneyPercent1 = $levelData[2]['player']; //平台返佣
            }else{

                $rebateMoneyPercent1 = $levelData[1]['player']; //平台返佣
            }

            $agent_one_rebate_config = M('agent_one_rebate_config')->where(array('agent_id'=>$value['id']))->field(true)->find();


            $agentList[$key]['rebateMoneyPercent1']  =   !empty($agent_one_rebate_config) ? $agent_one_rebate_config['player'] : $rebateMoneyPercent1;
            $agentList[$key]['rebateMoneyPercent2']  =   $levelData[2]['parent_lever'];

            $agentList[$key]['level_txt']  =   !empty($AGENT_LEVEL[$value['level']]['level_name']) ? $AGENT_LEVEL[$value['level']]['level_name'] : '未知';
        }
//        dump($agentList);die;
        $this->assign('agentList', $agentList);
        $this->assign('show', $show);
        $this->display('agentList');
    }



    /**
     * [addAgent 添加供货商]
     * @author TF <[2281551151@qq.com]>
     */
    public function addAgent() {
        if( IS_POST ) {
            $AgentModel = D('Agent');
            $data     = $AgentModel->create(I('post.'), 1);

            if ( empty($data) ) {
                $this->error($AgentModel->getError());
            } else {
                
                $phone                  =   I('post.phone',0,'trim');
                if($phone == 0){
                        $this->error('手机号码不得为空');
                }
                
                // $phone_agent_count      =   M('agent')->where(array('phone'=>$phone))->count();
                $phone_agent_count      =   M('agent')->where(array('phone'=>$phone,'is_delete'=>0))->count();
                if($phone_agent_count){
                        $this->error('当前手机号码已存在');
                }
                
                $data['agent_password'] = think_ucenter_md5('12345');
                $data['level']          =   1;
                // $data['invitation_code']            = randomString(6);
                $res = $AgentModel->add($data);
                if ( $res ) {
                    $invitation_code = 880000+$res;
                    $res2 = M('Agent')->where(array('id'=>$res))->setField(array('invitation_code'=>$invitation_code));
                    if (!$res2) {
                        $this->error('邀请码生成失败！', U('Agent/agentList'));
                    }
                    $this->success('添加成功！', U('Agent/agentList'));
                } else {
                    $this->error('添加失败！', U('Agent/agentList'));
                }
            }
        } else {
            $region     = M('region');
            // 省
            $province   = $region->where(array('pid'=>1))->select();

            $this->assign('province', $province);
            $this->display('addAgent');
        }
    }
    
    
    /**
     * [agentInfo 代理信息]
     * @author TF <[2281551151@qq.com]>
     */
    public function agentInfo() {
        $id     = I('get.id');
        if ( empty($id) ) {
            $this->error('参数丢失！');
        }

        $agent     = M('agent');
        $agentInfo = $agent->where(array('id'=>$id))->find();
        $agentInfo['pid_name'] = $agent->where(array('id'=>$agentInfo['pid']))->find();


        $this->assign('province', $province);
        $this->assign('city', $city);
        $this->assign('county', $county);
        $this->assign('agentInfo', $agentInfo);
        $this->display('agentInfo');
    }
    
    
    /**
     * [getChildZone 得到下级地区]
     * @author TF <[2281551151@qq.com]>
     */
    public function getChildZone() {
        if ( IS_POST ) {
            $parentId = I('post.pid');

            if ( empty($parentId) ) {
                $this->error('参数丢失！');
            }

            $data     = M('region')->where(array('pid'=>$parentId))->select();
            echo json_encode($data);
        }
    }

    /**
     * [delAgent 删除供货商]
     * @author TF <[2281551151@qq.com]>
     */
    public function delAgent() {
            $id = I('get.id', '', 'int');

            if ( empty($id) ) {
                $this->error('参数丢失！');
            }

            $data = array(
                'is_delete' => '1',
            );

            $goodsAgent = M('agent');
            if ( $goodsAgent->where(array('id'=>$id))->data($data)->save() >= 0 ) {
                $this->success('删除成功！', U('Agent/agentList'));
            } else {
                $this->error('删除失败！', U('Agent/agentList'));
            }
    }

    /**
     * [recoveryAgent 代理恢复]
     * @author TF <[2281551151@qq.com]>
     */
    public function recoveryAgent() {
            $id = I('get.id', '', 'int');

            if ( empty($id) ) {
                $this->error('参数丢失！');
            }
            $data = array(
                'is_delete' => '0',
            );
            $goodsAgent = M('agent');
            $res = $goodsAgent->where(array('id'=>$id))->setField($data);
            if ( $res ) {
                $this->success('账号恢复！', U('Agent/agentList'));
            } else {
                $this->error('恢复失败！', U('Agent/agentList'));
            }
    }
    /**
     * [resetPwd 重置密码]
     * @author TF <[2281551151@qq.com]>
     */
    public function resetPwd() {
            $id = I('get.id', '', 'int');

            if ( empty($id) ) {
                $this->error('参数丢失！');
            }

            $data = array(
                'agent_password' => think_ucenter_md5('12345'),
            );
            $goodsAgent = M('agent');
            if ( $goodsAgent->where(array('id'=>$id))->data($data)->save() >= 0 ) {
                $this->success('操作成功！', U('Agent/agentList'));
            } else {
                $this->error('操作失败！', U('Agent/agentList'));
            }
    }

    /**
     * [editAgent 编辑供货商]
     * @author TF <[2281551151@qq.com]>
     */
    public function editAgent() {
        if( IS_POST ) {
            $AgentModel = D('Agent');
            $data     = $AgentModel->create(I('post.'), 2);

            if ( empty($data) ) {
                $this->error($AgentModel->getError());
            } else {
                $id = I('post.id', '', 'int');

                if ( empty($id) ) {
                    $this->error('参数丢失！');
                }

                if ( $AgentModel->where(array('id'=>$id))->data($data)->save() >= 0 ) {
                    $this->success('保存成功！', U('Agent/agentList'));
                } else {
                    $this->error('保存失败！', U('Agent/agentList'));
                }
            }
        } else {
            $id = I('get.id', '', 'int');

            if ( empty($id) ) {
                $this->error('参数丢失！');
            }

            $AgentModel = D('Agent');
            $agentInfo = $AgentModel->where(array('id'=>$id))->find();
            
            $region     = M('region');
            // 省
            $province   = $region->where(array('pid'=>1))->select();
//            dump($agentInfo);
//            dump($province);die;
            $this->assign('province', $province);

            $this->assign('agentInfo', $agentInfo);
            $this->display('editAgent');
        }
    }
    
    /*
     * 获得的返利统计
     */
    public function getGebate(){
            $startTime      = I('get.start_time');
            $endTime        = I('get.end_time');
            $where          =   array();            

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

            $count    = M('order_rebate_recored')->where($where)->count();
            $page     = new \Think\Page($count, 10);
		
			if ($this->iswap()) {
				$page->rollPage	= 5;
			}
		
            $show     = $page->show();
            $order_rebate_recored_list   =   M('order_rebate_recored')->where($where)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id desc')->select();
            foreach ($order_rebate_recored_list as $key => $value) {
                if(!empty($value['from_agent_id']) && !empty($value['to_agent_id'])){
                    $agent_relation_list    =   M('agent')->where(array('id'=>array('in',array($value['from_agent_id'],$value['to_agent_id']))))->field('id,nickname,phone,user_id')->select();
                    $agent_from_info        =   array();
                    $agent_to_info          =   array();
                    foreach ($agent_relation_list as $k2 => $v2) {
                        if($v2['id'] == $value['from_agent_id']){
                            $agent_from_info    =   $v2;
                        }  elseif ($v2['id'] == $value['to_agent_id']) {
                            $agent_to_info      =   $v2;
                        }
                    }
                    $order_rebate_recored_list[$key]['from_agent_info']     =   $agent_from_info;
                    $order_rebate_recored_list[$key]['to_agent_info']       =   $agent_to_info;
                }
            }
            
            $this->assign('order_rebate_recored_list', $order_rebate_recored_list);
            $this->assign('show', $show);
//            dump($order_rebate_recored_list);die;
            $this->display();
    }
    
    /*
     * 代理充卡
     */
    public function addInsureScore() {
        if( IS_POST ) {
                
                $adminId                =   session('adminId');
                if(!$adminId){
                    $this->error('登录信息已过期', U('Login/login'));
                }
                $admin_info             =   M('admin')->where($adminId)->field(true)->find();
                if(empty($admin_info)){
                    $this->error('登录信息不存在或已过期', U('Login/login'));
                }
            
                $agentId                =   I('post.id',0,'intval');
                if($agentId == 0){
                    $this->error('参数错误');
                }
                $agent_info             =   M('agent')->where(array('id'=>$agentId))->field('id,pid,nickname,phone,room_card')->find();
                if(empty($agent_info)){
                    $this->error('代理数据错误或不存在');
                }
                $pay_nums               =   I('post.pay_nums',0,'intval');
                if($pay_nums == 0){
                    $this->error('充值数量必填');
                }
                
                $admin_password               =   I('post.admin_password','','trim');
                if($admin_password == ''){
                    $this->error('请输入登录密码');
                }
                
                if($admin_info['admin_password'] != think_ucenter_md5($admin_password)){
                    $this->error('密码错误！');
                }
                
                //代理充卡到账
                M('agent')->where(array('id'=>$agentId))->setField(array('room_card'=>$agent_info['room_card']+$pay_nums));
            
                //充值记录
                $agent_recharge_recored_data  =   array();
                $agent_recharge_recored_data['type']            =   1;      //房卡来源：0代理购买房卡，1商家给代理充房卡，2下级代理返卡
                $agent_recharge_recored_data['admin_id']        =   $adminId;
                $agent_recharge_recored_data['agent_id']        =   $agentId;
                $agent_recharge_recored_data['pay_nums']        =   $pay_nums;
                $agent_recharge_recored_data['desc']            =   '平台成功为代理['.$agent_info['phone'].']充值房卡：'.$pay_nums.'颗';
                $agent_recharge_recored_data['add_time']        =   NOW_TIME;
                M('agent_recharge_recored')->add($agent_recharge_recored_data);

                //代理上级返卡
                if (!empty($agent_info['pid'])) {
                    $parent_agent_info = M('agent')->where(array('id' => $agent_info['pid']))->field('id,user_id,pid,nickname,room_card')->find();
                    if (!empty($parent_agent_info)) {
                        if (C('rebate_percent')) {
                            $rebate_percent = C('rebate_percent') / 100;
                        } else {
                            $rebate_percent = 1 / 100;
                        }
                        $add_InsureScore = intval($pay_nums * $rebate_percent);
                        
                        //更新房卡数量
                        $InsureScore = $parent_agent_info['room_card'] + $add_InsureScore;
                        M('agent')->where(array('id'=>$parent_agent_info['id']))->setField(array('room_card'=>$InsureScore));
                        
                        //返卡记录
                        $agent_recharge_recored_data  =   array();
                        $agent_recharge_recored_data['type']            =   2;  //房卡来源：0代理购买房卡，1商家给代理充房卡，2下级代理返卡
                        $agent_recharge_recored_data['admin_id']        =   $adminId;
                        $agent_recharge_recored_data['agent_id']        =   $parent_agent_info['id'];
                        $agent_recharge_recored_data['pay_nums']        =   $add_InsureScore;
                        $agent_recharge_recored_data['desc']            =   '平台为代理[' . $agent_info['phone'] . ']充值房卡，上级代理[' . $parent_agent_info['nickname'] . ']获得返卡[' . $add_InsureScore . ']颗';
                        $agent_recharge_recored_data['add_time']        =   NOW_TIME;
                        M('agent_recharge_recored')->add($agent_recharge_recored_data);
                    }
                }

                $this->success('充值成功', U('Recharge/agentsRecored',array('type'=>1)));
            
        } else {
            //代理信息
            $agentId            =   I('get.id',0,'intval');
            if($agentId == 0){
                $this->error('参数错误');
            }
            $agent_info         =   M('agent')->where(array('id'=>$agentId))->field('id,nickname,phone,room_card')->find();
            if(empty($agent_info)){
                $this->error('代理数据错误或不存在');
            }
            
//            dump($game_user_accounts_info);die;
            $this->assign('agent_info', $agent_info);
            
            $this->display();
        }
    }

    //代理销售业绩
    public function agentSalesVolumeRecored(){

        //当前用户代理agent_id
        $agentId                =   I('get.agentId');

        $startTime      = I('get.start_time');
        $endTime        = I('get.end_time');
        $where          =   array();
        $where['agent_id'] = $agentId;    
        if (!empty($startTime) && !empty($endTime)) {
            $startTime = addslashes($startTime);
            $startTime = urldecode($startTime);
            $startTime = str_replace('+', ' ', $startTime);
            $startTime = strtotime($startTime);

            $endTime = addslashes($endTime);
            $endTime = urldecode($endTime);
            $endTime = str_replace('+', ' ', $endTime);
            $endTime = strtotime($endTime);
            $where['dateline'] = array('BETWEEN', array($startTime, $endTime));
        }

        $userid         =   I('get.userid',0,'intval');
        if($userid != 0){
                $where['uid'] = $userid;    
        }
        $count    = M('agent_sales_volume')->where($where)->count();
        $page     = new \Think\Page($count, 10);
        
        if ($this->iswap()) {
            $page->rollPage = 5;
        }
        $show     = $page->show();

        $agent_sales_volume_recored = M('agent_sales_volume')->where($where)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();

        $agent_sales_volume_total    = M('agent_sales_volume')->where($where)->sum('total_price');

        $this->assign('agent_sales_volume_recored', $agent_sales_volume_recored);
        $this->assign('agent_sales_volume_total', floatval($agent_sales_volume_total));
        $this->assign('data', $data);
        $this->assign('show', $show);
        $this->display();
    }

    /**
     * [agentList 玩家列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function erplayers() {

        $startTime      = I('get.start_time');
        $endTime        = I('get.end_time');
        //当前用户代理agent_id
        $agentId                =   I('get.agentId');
        if (!empty($startTime) && !empty($endTime)) {
            $startTime = addslashes($startTime);
            $startTime = urldecode($startTime);
            $startTime = str_replace('+', ' ', $startTime);
            $startTime = strtotime($startTime);

            $endTime = addslashes($endTime);
            $endTime = urldecode($endTime);
            $endTime = str_replace('+', ' ', $endTime);
            $endTime = strtotime($endTime);
            // $whereStr .= "AND o.dateline BETWEEN {$startTime} AND {$endTime} ";
            $map['pay_time'] = array('BETWEEN', array($startTime, $endTime));
        }
        $agent_info = M('agent')->where(array('id'=>$agentId))->field('id,invitation_code')->find();
        $gameuserList = M('gameuser')->where(array('invitation_code'=>$agent_info['invitation_code']))->field(true)->select();
        if (!empty($gameuserList)) {
                $game_user_id_list = array();
                foreach ($gameuserList as $key => $value) {
                    $game_user_id_list[] = $value['game_user_id'];
                }
        }
        if (!empty($game_user_id_list)) {
                $userid         =   I('get.userid',0,'intval');
                $where          =   array();
                //        $where['agent_id']  =   $agentId;
                
                if($userid != 0){
                    if (in_array($userid, array($game_user_id_list))) {
                        $userid  = addslashes($userid);
                        $userid  = urldecode($userid);
                        $where['UserID'] = $userid;
                    }else{
                        $where['UserID'] = $userid;                        
                    }
                }else{
                    $where['UserID'] = array('in',$game_user_id_list);
                }
        //         //        dump($where);die;
                $sqlsrv_model   =   $this->sqlsrv_model('AccountsDB','AccountsInfo');
                $count    = $sqlsrv_model->table('AccountsInfo')->where($where)->count();
                $page     = new \Think\Page($count, 10);
                
                if ($this->iswap()) {
                    $page->rollPage = 5;
                }
                
                $show     = $page->show();

                $game_user_list = $sqlsrv_model->table('AccountsInfo')->where($where)->field("UserID,NickName,LastLogonDate,RegisterDate")->limit($page->firstRow . ',' . $page->listRows)->order('UserID DESC')->select();
                if(!empty($game_user_list)){
                    $sqlsrv_model2   =   $this->sqlsrv_model('TreasureDB','GameScoreInfo');
                    foreach ($game_user_list as $key => $value) {
                        $QPTreasureDB = $sqlsrv_model2->table('GameScoreInfo')->where(array('UserID'=>$value['userid']))->field('UserID,InsureScore,Score')->find();
                        $map['uid']=$value['userid'];
                        $map['ispay']=1;
                        $game_user_list[$key]['money']          =   M('recharge_gold')->where($map)->sum('total_price');
                        if(empty($game_user_list[$key]['money'])){
                            $game_user_list[$key]['money']=0;
                        }

                        $data['total'] += $game_user_list[$key]['money'];
                        $game_user_list[$key]['THTreasureDB']   =   $QPTreasureDB;
                    }
                }
        }
        // print_r($game_user_list);die;
        $this->assign('game_user_list', $game_user_list);
        $this->assign('data', $data);
        $this->assign('show', $show);
        $this->display();
    }
    
    /*
     * 代理充卡记录
     */
    public function recored(){
            
            $agenId         =   I('get.agenId',0,'intval');
            $where          =   array();
            if ( $agenId != '0' ) {
                $agenId  = addslashes($agenId);
                $agenId  = urldecode($agenId);
                $where['agent_id'] = $agenId;
            }
        
            $count    = M('agent_recharge_recored')->where($where)->count();
            $page     = new \Think\Page($count, 10);
		
			if ($this->iswap()) {
				$page->rollPage	= 5;
			}
		
            $show     = $page->show();
            $agent_recharge_recored_list   =   M('agent_recharge_recored')->where($where)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id desc')->select();
            $this->assign('agent_recharge_recored_list', $agent_recharge_recored_list);
            $this->assign('show', $show);
//            dump($user_recharge_recored_list);die;
            $this->display();
    }

    /*
    *代理提现申请
    */
    public function incomeReport(){
            $startTime      = I('get.start_time');
            $endTime        = I('get.end_time');
            $nickname       = I('get.nickname');
            $phone          = I('get.phone');

            $where = array();
            $whereStr = '';
            
            if( !empty($nickname) ) {
                $nickname   = addslashes($nickname);
                $nickname   = urldecode($nickname);
                $where['a.nickname'] = array('LIKE', "%{$nickname}%");
            }
            if( !empty($phone) ) {
                $phone   = addslashes($phone);
                $phone   = urldecode($phone);
                $where['a.phone'] = array('LIKE', "%{$phone}%");
            }

            if (!empty($startTime) && !empty($endTime)) {
                $startTime = addslashes($startTime);
                $startTime = urldecode($startTime);
                $startTime = str_replace('+', ' ', $startTime);
                $startTime = strtotime($startTime);

                $endTime = addslashes($endTime);
                $endTime = urldecode($endTime);
                $endTime = str_replace('+', ' ', $endTime);
                $endTime = strtotime($endTime);
                $where['w.dateline'] = array('BETWEEN', array($startTime, $endTime));
            }


            $getData = array();
            $get = I('get.');
            foreach ($get as $key => $value) {
                if (!empty($key)) {
                    $getData[$key] = urldecode($value);
                }
            }

            if (!empty($getData['add_time'])) {
                $getData['add_time'] = search_time_format($getData['add_time']);
            }

            if (!empty($getData['end_time'])) {
                $getData['end_time'] = search_time_format($getData['end_time']);
            }
            
            $AgentModel = M('agent_withdrawals');

            $count    = $AgentModel->alias('w')->join('LEFT JOIN '.C('DB_PREFIX').'agent as a ON w.agent_id=a.id')->where($where)->count();
            $page     = new \Think\Page($count, 10,$getData);
            
            if ($this->iswap()) {
                $page->rollPage = 5;
            }
            
            $show     = $page->show();

            $join = ' LEFT JOIN '.C('DB_PREFIX').'agent as a ON w.agent_id=a.id';
            $join .= ' LEFT JOIN '.C('DB_PREFIX').'agent_withdrawals_account as awa ON awa.agent_id=a.id';
            $limit  = $page->firstRow . ',' . $page->listRows;
            $order = 'w.id DESC';
            $fields = 'w.id,w.money,w.dateline,w.status,a.nickname,a.phone,a.wechat_id,awa.truename,awa.alipay_account,awa.bank_name,awa.bank_card,awa.bank_subbranch';
            $agentList = $AgentModel->alias('w')->join($join)->where($where)->limit($limit)->order($order)->field($fields)->select();

           // dump($agentList);die;
            $this->assign('agentList', $agentList);
            $this->assign('show', $show);
            $this->display('incomeReport');
    }



    /*
    *处理当前提现信息
    */
    public function incomeReportHandle(){
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

                $id = I('post.id',0,'intval');
                if (empty($id)) {
                    $this->error('参数错误');
                }

                $status = I('post.status',0,'intval');
                $reason = I('post.reason','','trim');
                if ($status == 0) {
                    $this->error('请选择操作结果');
                }elseif ($status == 2) {
                    if (empty($reason)) {
                        $this->error('请输入驳回理由');
                    }
                }

                $where = array('id'=>$id);
                $agent_withdrawals = M('agent_withdrawals')->where($where)->field(true)->find();
                if (empty($agent_withdrawals)) {
                    $this->error('信息检索失败');
                }
                if ($agent_withdrawals['status']) {
                    $this->error('当前提现信息已处理');
                }

                $data = array();
                $data['status'] = $status;
                $data['update_time'] = NOW_TIME;
                $data['admin_id'] = $adminId;
                $data['reason'] = $reason;
                switch ($status) {
                    case '1':   //同意提现申请

                        $agent_withdrawals_account = M('agent_withdrawals_account')->where(array('agent_id'=>$agent_withdrawals['agent_id']))->field(true)->find();
                        if (empty($agent_withdrawals_account['alipay_account']) && empty($agent_withdrawals_account['bank_card'])) {
                            $this->error('提现账户信息不全');
                        }
                        $res2 = M('agent_withdrawals')->where($where)->setField($data);

                        //账单流水
                        $agent_bill_data  =   array();
                        $agent_bill_data['type']            =   3;  //1=会员返利，2=推荐返利，3=提现
                        $agent_bill_data['change_type']     =   2;  //1=收入，2=支出
                        $agent_bill_data['agent_id']        =   $agent_withdrawals['agent_id'];

                        $agent_bill_data['money']           =   $agent_withdrawals['money'];
                        $agent_bill_data['desc']            =   '提现[' . $agent_withdrawals['money'] . ']元';
                        $agent_bill_data['dateline']        =   NOW_TIME;
                        $res2003 = M('agent_bill_list')->add($agent_bill_data);
                        break;
                    case '2':   //驳回提现申请
                        $agent_info = M('agent')->where(array('id'=>$agent_withdrawals['agent_id']))->field(true)->find();
                        if (empty($agent_info)) {
                            $this->error('代理数据有误');
                        }
                        $agent_Data = array();
                        $agent_Data['available_balance'] = $agent_info['available_balance'] + $agent_withdrawals['money'] + 5;
                        $res1 = M('agent')->where(array('id'=>$agent_withdrawals['agent_id']))->setField($agent_Data);
                        if (!$res1) {
                            $this->error('操作失败');

                        }
                        $res2 = M('agent_withdrawals')->where($where)->setField($data);
                        break;
                    default:
                        break;
                }
                if ($res2) {
                    $this->success("操作成功",U('incomeReport'));
                }else{
                    $this->error('操作失败');
                }
            }else{

                $id = I('get.id',0,'intval');
                if (empty($id)) {
                    $this->error('参数错误');
                }

                $sql_prefix =   C('DB_PREFIX');

                $where = array('aw.id'=>$id);
                $agent_withdrawals = M('agent_withdrawals')->alias('aw')
                ->join($sql_prefix.'agent AS a ON a.id = aw.agent_id')
                ->join('LEFT JOIN '.$sql_prefix.'agent_withdrawals_account AS awa ON awa.agent_id = a.id')
                ->field('a.id as agent_id,a.nickname,a.phone,aw.id,aw.money,aw.dateline,awa.truename,awa.alipay_account,awa.bank_card')
                ->where($where)->find();
                if (empty($agent_withdrawals)) {
                    $this->error('信息检索失败');
                }
                if ($agent_withdrawals['status']) {
                    $this->error('当前提现信息已处理');
                }
                // dump($agent_withdrawals);die;
                $this->assign('agent_withdrawals',$agent_withdrawals);
                $this->display();
            }
    }

    /*
    *代理审核
    */
    public function agentExamine(){

            $startTime      = I('get.start_time');
            $endTime        = I('get.end_time');
            $nickname       = I('get.nickname');
            $phone          = I('get.phone');

            $where = array();
            $whereStr = '';
            
            if( !empty($nickname) ) {
                $nickname   = addslashes($nickname);
                $nickname   = urldecode($nickname);
                $whereStr .= "AND o.nickname LIKE \"%{$nickname}%\" ";
                $where['nickname'] = array('LIKE', "%{$nickname}%");
            }
            if( !empty($phone) ) {
                $phone   = addslashes($phone);
                $phone   = urldecode($phone);
                $whereStr .= "AND o.phone LIKE \"%{$phone}%\" ";
                $where['phone'] = array('LIKE', "%{$phone}%");
            }

            if (!empty($startTime) && !empty($endTime)) {
                $startTime = addslashes($startTime);
                $startTime = urldecode($startTime);
                $startTime = str_replace('+', ' ', $startTime);
                $startTime = strtotime($startTime);

                $endTime = addslashes($endTime);
                $endTime = urldecode($endTime);
                $endTime = str_replace('+', ' ', $endTime);
                $endTime = strtotime($endTime);
                $whereStr .= "AND o.dateline BETWEEN {$startTime} AND {$endTime} ";
                $where['dateline'] = array('BETWEEN', array($startTime, $endTime));
            }


            $getData = array();
            $get = I('get.');
            foreach ($get as $key => $value) {
                if (!empty($key)) {
                    $getData[$key] = urldecode($value);
                }
            }

            if (!empty($getData['add_time'])) {
                $getData['add_time'] = search_time_format($getData['add_time']);
            }

            if (!empty($getData['end_time'])) {
                $getData['end_time'] = search_time_format($getData['end_time']);
            }
            
            $AgentModel = M('agent_applying');

            $count    = $AgentModel->where($where)->count();
            $page     = new \Think\Page($count, 25,$getData);
            
            if ($this->iswap()) {
                $page->rollPage = 5;
            }
            
            $show     = $page->show();

            $agentList = $AgentModel->where($where)->limit($page->firstRow . ',' . $page->listRows)->order('status ASC,dateline DESC')->select();
            
            $apply_type_txt = array('1'=>'上级添加','2'=>'个人申请');
            $AGENT_LEVEL = C('AGENT_LEVEL');
            foreach ($agentList as $key => $value) {
                if(!empty($value['pid'])){
                    $parent_agent_info  =   M('agent')->where(array('id'=>$value['pid']))->field('id,nickname')->find();
                    $agentList[$key]['p_info']  =   $parent_agent_info;
                }
                $agentList[$key]['level_txt']  =   !empty($AGENT_LEVEL[$value['level']]['level_name']) ? $AGENT_LEVEL[$value['level']]['level_name'] : '未知';
                $agentList[$key]['from_txt']  =   $apply_type_txt[$value['apply_type']];
            }
    //        dump($agentList);die;
            $this->assign('agentList', $agentList);
            $this->assign('show', $show);

            $this->display();
    }


    //代理审核通过
    public function agentExamineHandle(){
            $id = I('get.id',0,'intval');
            if (empty($id)) {
                # code...
                $this->error('参数错误');
            }

            $where = array('id'=>$id);
            $agent_applying = M('agent_applying')->where($where)->field(true)->find();
            if (empty($agent_applying)) {
                # code...
                $this->error('信息检索失败');
            }
            if ($agent_applying['status']) {
                # code...
                $this->error('当前提现信息已处理');
            }

            $this->agentExamineHandleToAgent($agent_applying);

            $res = M('agent_applying')->where($where)->setField(array('status'=>1,'status_time'=>NOW_TIME));
            if ($res) {
                # code...
                $this->success("操作成功",U('agentExamine'));
            }else{
                $this->error('操作失败');
            }
    }

    protected function agentExamineHandleToAgent($agent_applying){
            if ($agent_applying['apply_type']) {
                # code...
                $agentData['pid'] = ($agent_applying['apply_type'] == 1) ? $agent_applying['pid'] : 0;
                $agentData['rid'] = ($agent_applying['apply_type'] == 2) ? $agent_applying['rid'] : 0;
                $agentData['agent_password'] = think_ucenter_md5('12345');
                $agentData['nickname'] = $agent_applying['nickname'];
                $agentData['phone'] = $agent_applying['phone'];
                $agentData['wechat_id'] = $agent_applying['wechat_id'];
                $agentData['dateline'] = NOW_TIME;
                $agentData['is_lock'] = 0;
                $agentData['level'] = $agent_applying['level'];
                // $agentData['invitation_code'] = randomString(6);
                $res2 = M('agent')->add($agentData);
                if (!$res2) {
                    $this->error('操作失败');
                    # code...
                }else{
                    $invitation_code = 880000+$res2;
                    $res3 = M('Agent')->where(array('id'=>$res2))->setField(array('invitation_code'=>$invitation_code));
                    if (!$res3) {
                        $this->error('邀请码生成失败！', U('Agent/agentList'));
                    }
                }
            }
    }

    //返佣配置
    public function oneRebateConfig(){
        if (IS_POST) {

            $agent_id = I('post.id',0,'intval');
            $player = I('post.player',0,'floatval');
            $agent_one_rebate_config = M('agent_one_rebate_config')->where(array('agent_id'=>$agent_id))->field(true)->find();
            if ($player > 100 || $player < 0) {
                $this->error('分佣比例在0到100之间');
            }
            if (empty($agent_one_rebate_config)) {
                $data = array();
                $data['agent_id'] = $agent_id;
                $data['player']   = $player;
                $data['update_time'] = NOW_TIME; 
                $res = M('agent_one_rebate_config')->add($data);
            }else{
                $data = array();
                $data['player']   = $player;
                $data['update_time'] = NOW_TIME; 
                $res = M('agent_one_rebate_config')->where(array('agent_id'=>$agent_id))->setField($data);
            }
            if ($res) {
                $this->success("操作成功",U('agentList'));
            }else{
                $this->error('操作失败');
            }

        }else{

            $agent_id = I('get.id',0,'intval');
            $agent_one_rebate_config = M('agent_one_rebate_config')->where(array('agent_id'=>$agent_id))->field(true)->find();
            // dump($agent_id);die;
            $agentInfo = M('agent')->where(array('id'=>$agent_id))->field(true)->find();

            $configList = M('agent_rebate_config')->where(array('agent_id'=>$agent_id))->field(true)->order('id ASC')->select();
            // $levelData  =   C('AGENT_LEVEL');
            $levelData  =   array();
            foreach ($configList as $key => $value) {
                $levelData[$value['id']] = $value;
            }

            if(!empty($agentInfo['pid'])){

                $rebateMoneyPercent1 = $levelData[2]['player']; //平台返佣
            }else{

                $rebateMoneyPercent1 = $levelData[1]['player']; //平台返佣
            }
            $agentInfo['rebateMoneyPercent1']  =  !empty($agent_one_rebate_config) ? $agent_one_rebate_config['player'] : $rebateMoneyPercent1;

            $this->assign('agentInfo',$agentInfo);
            $this->assign('agent_one_rebate_config',$agent_one_rebate_config);
            $this->display();
        }
    }
}
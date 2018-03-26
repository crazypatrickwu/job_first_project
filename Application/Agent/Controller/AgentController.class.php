<?php
namespace Agent\Controller;
use Think\Controller;
// 供货商控制器
class AgentController extends BaseController {
	/**
	 * [agentList 供货商列表]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function agentList() {
		$startTime      = I('get.start_time');
		$endTime        = I('get.end_time');
		$nickname       = I('get.nickname');
		$phone          = I('get.phone');
		$keyword		= I('get.keyword');

		$where = array();
		$whereStr = '';

		$where['is_delete'] =   0;
		$where['pid']       =   session('agentId');
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

		if (!empty($keyword)){
			$where['nickname|phone|wechat_id']	= array('eq',$keyword);
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
				
			$where = array();
			$where['id']	=	session('agentId');
			$agent_info      =   M('agent')->where($where)->field(true)->find();
			if (!empty($agent_info) && $agent_info['level'] == 3) {
				# code...
				$this->error('无权添加下级代理');
			}
			
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
				// dump($phone);die;
				if($phone_agent_count){
					$this->error('当前手机号码已存在');
				}
				$data['agent_password'] = think_ucenter_md5('12345');
				$data['pid']            = session('agentId');
				$data['level']            = $agent_info['level']+1;
				$data['invitation_code']            = randomString(6);
				if ( $AgentModel->data($data)->add() ) {
					$this->success('添加成功！', U('Agent/agentList'));
				} else {
					$this->error('添加失败！', U('Agent/agentList'));
				}
			}
		} else {

			$where = array();
			$where['id']	=	session('agentId');
			$agent_info      =   M('agent')->where($where)->field(true)->find();
			if (!empty($agent_info) && $agent_info['level'] == 3) {
				# code...
				$this->error('您暂时不能添加下级代理');
			}

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
					$this->success('保存成功！');
				} else {
					$this->error('保存失败！');
				}
			}
		} else {
			$agentId    =   session('agentId');
			$AgentModel =   M('Agent');
			$agentInfo  =   $AgentModel->where(array('id'=>$agentId))->find();
			if(empty($agentInfo)){
				$this->error('个人数据异常');
			}

			$region     =   M('region');
			// 省
			$province   =   $region->where(array('pid'=>1))->select();
			//            dump($agentInfo);
			//            dump($province);die;
			$this->assign('province', $province);

			$this->assign('agentInfo', $agentInfo);
			$this->display('agentInfo');
		}
	}


	/*
	 * 修改密码
	 * DML.
	 */
	public function editPwd(){
		if( IS_POST ) {
			$AgentModel = M('agent');
			$agentId    =   session('agentId');
			if ( empty($agentId) ) {
				$this->error('参数丢失！');
			}

			$agent_info =   $AgentModel->where(array('id'=>$agentId))->field('id,agent_password')->find();
			if(empty($agent_info)){
				$this->error('个人代理数据错误！');
			}

			$old_password       =   I('post.old_password','');
			$new_password       =   I('post.new_password','');
			$re_new_password    =   I('post.re_new_password','');

			if($agent_info['agent_password'] != think_ucenter_md5($old_password)){
				$this->error('原密码输入错误！');
			}

			if($re_new_password != $new_password){
				$this->error('确认密码不一致！');
			}

			$agent_update   =   M('agent')->where(array('id'=>$agentId))->setField(array('agent_password'=>  think_ucenter_md5($new_password)));
			session_start();
			session('agentId', null);
			session('agentAccount', null);
			session('rememberPassword', null);

			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie( session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"] );
			}
			$this->success('密码修改成功，请重新登录！',U('Login/login'));

		} else {
			$agentId    =   session('agentId');
			$AgentModel =   M('Agent');
			$agentInfo  =   $AgentModel->where(array('id'=>$agentId))->find();

			$region     =   M('region');
			// 省
			$province   =   $region->where(array('pid'=>1))->select();
			//            dump($agentInfo);
			//            dump($province);die;
			$this->assign('province', $province);

			$this->assign('agentInfo', $agentInfo);
			$this->display();
		}
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
		$agentId    =   session('agentId');
		$startTime      = I('get.start_time');
		$endTime        = I('get.end_time');
		$where          =   array();
		$where['to_agent_id']   =   $agentId;
		//            $where['to_agent_id']   =   5;

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
	*申请提现
	*/
	public function applyWithdrawals(){
		if (IS_POST) {

			$pay_nums = I('post.pay_nums',0,'floatval');

			$agent_info	= M('agent')->where(array('id'=>session('agentId')))->field(true)->find();
			if (empty($agent_info)) {
				$this->error('代理信息错误');
			}

			$limit_quota = 500;	//最低限额
			$fee = 5;			//手续费

			if ($agent_info['available_balance'] < $limit_quota) {
				$this->error('账户余额不得少于'.$limit_quota.'元');
			}
			// if ($pay_nums < $limit_quota) {
			// 	$this->error('提现金额不得少于'.$limit_quota.'元');
			// }

			if ($agent_info['available_balance']  < ($limit_quota+$fee)) {
				$min_pay_nums = $agent_info['available_balance'] - $fee;
				if ($pay_nums > $min_pay_nums) {
					$this->error('当前余额最多能提现'.$min_pay_nums.'元');
				}
			}
			if ($agent_info['available_balance'] < $pay_nums) {
				$this->error('账户余额不足');
			}
			if ($pay_nums < ($limit_quota-$fee)) {
				$this->error('实际提现金额不得少于'.($limit_quota-$fee).'元');
			}

			if ($agent_info['available_balance'] == $pay_nums) {
				$pay_nums = $pay_nums - $fee;
			}


			$apply_hour_now 	= 	intval(date('His'));
			if ($apply_hour_now < 90000 || $apply_hour_now > 160000) {
				$this->error('平台可提现时间在上午9点到下午4点之间');
			}
			$agent_password               =   I('post.agent_password','','trim');
			if($agent_password == ''){
				$this->error('请输入登录密码');
			}

			if($agent_info['agent_password'] != think_ucenter_md5($agent_password)){
				$this->error('密码错误！');
			}

			$where = array();
			$where['agent_id']	=	session('agentId');
			$where['status']	=	0;
			$agent_withdrawals_count = M('agent_withdrawals')->where($where)->count();
			if ($agent_withdrawals_count > 0) {
				$this->error('您当前有提现申请等待处理，请稍候');
			}


			$where = array();
			$where['agent_id']	=	session('agentId');
			$where['status']	=	1;
			$where['dateline']	=	array('egt',strtotime(date('Y-m-d')));
			$agent_withdrawals_count = M('agent_withdrawals')->where($where)->count();
			if ($agent_withdrawals_count > 0) {
				$this->error('每天只能申请提现一次');
			}

			$data 		=	array();
			$data['agent_id']	=	session('agentId');
			$data['money']		=	$pay_nums;
			$data['dateline']	=	NOW_TIME;
			$data['status']		=	0;
			$res = M('agent_withdrawals')->add($data);
			if ($res) {
				$data = array();
				$data['available_balance'] = $agent_info['available_balance'] - $pay_nums - $fee;
				M('agent')->where(array('id'=>session('agentId')))->setField($data);

				$this->success('提交成功，等待平台审核！');
			} else {
				$this->error('提交失败！');
			}

		}else{

			$agent_info = M('agent')->where(array('id'=>session('agentId')))->field(true)->find();
			$this->assign('agent_info',$agent_info);
			$this->display('applyWithdrawals');
		}
	}

	
	/*
	*银行列表
	*/
	public function getBankList(){
			return array(
				'工商银行',
				'中国银行',
				'农业银行',
				'建设银行',
				'招商银行',
				'兴业银行',
				'浦发银行',
				'中信银行',
				'华夏银行',
				'民生银行',
				'交通银行',
				'光大银行',
				'广发银行',
				'平安银行',
				'邮储银行',
			);
	}

	/*
	*分佣账号
	*/
	public function agentWithdrawalsAccount(){
		if (IS_POST) {
			$truename		= 	I('post.truename','','trim');
			$alipay_account   	=	I('post.alipay_account','','trim');
			$bank_name		=	I('post.bank_name','','trim');
			$bank_card		=	I('post.bank_card','','trim');
			$bank_subbranch	=	I('post.bank_subbranch','','trim');

			$agentId = session('agentId');
			$agent_info =   M('agent')->where(array('id'=>$agentId))->field('id,agent_password')->find();
			if(empty($agent_info)){
				$this->error('个人数据错误！');
			}

			$agent_password               =   I('post.agent_password','','trim');
			if($agent_password == ''){
				$this->error('请输入账户密码');
			}

			if($agent_info['agent_password'] != think_ucenter_md5($agent_password)){
				$this->error('账户密码错误');
			}
			if(empty($truename)){
				$this->error('真实姓名不能为空');
			}
			// if(empty($alipay_account)){
			// 	$this->error('支付宝账号不能为空');				
			// }
			if(empty($bank_name)){
				$this->error('提现银行不能为空');					
			}
			if(empty($bank_card)){
				$this->error('银行卡号不能为空');					
			}
			if(empty($bank_subbranch)){
				$this->error('支行名称不能为空');					
			}	
			$data = array();
			$data['truename']		=	$truename;
			$data['alipay_account']	=	$alipay_account;
			$data['bank_name']		=	$bank_name;
			$data['bank_card']		=	$bank_card;
			$data['bank_subbranch']	=	$bank_subbranch;
			$data['dateline']		=	NOW_TIME;

			$agent_withdrawals_account = M('agent_withdrawals_account')->where(array('agent_id'=>$agentId))->field(true)->find();
			if (!empty($agent_withdrawals_account)) {
				$res = M('agent_withdrawals_account')->where(array('agent_id'=>$agentId))->setField($data);
			}else{
				$data['agent_id']	=	$agentId;
				$res = M('agent_withdrawals_account')->add($data);
			}

			if ($res) {
				$this->success('保存成功', U('Agent/agentWithdrawalsAccount'));
			} else {
				$this->error('保存失败！');
			}

		}else{

			$agent_withdrawals_account = M('agent_withdrawals_account')->where(array('agent_id'=>session('agentId')))->field(true)->find();
			$this->assign('agent_withdrawals_account',$agent_withdrawals_account);

			$bankList = $this->getBankList();
			$this->assign('bankList',$bankList);
			$this->display('agentWithdrawalsAccount');
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
                    $page->listRows	= 100;
            }
            $show     = $page->show();
            $this->assign('show', $show);

            $myRechargeRecored  =   D('AgentRebateRecored')->where($where)->relation(true)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
//            dump($myRechargeRecored);die;
            $this->assign('myRechargeRecored', $myRechargeRecored);
            
            $this->display('agentRebate');
    }
}
<?php
namespace Home\Controller;
use Think\Controller;
// 供货商控制器
class AgentController extends HomeController {
    public function __construct() {
        parent::__construct();
        $agentId                =   session('agentId');
        $agentAccount                =   session('agentAccount');
        if(!$agentId || !$agentAccount){
            redirect(U('Login/index'));
        }
        if(!in_array(ACTION_NAME, array('packageSelect','packageAll','packageDetail','packageBuy'))){
			$this->isTrueAgent();
        }
    }


    /*
    *判断当前登录代理账户是否激活
    */
    protected function isTrueAgent(){
    	$agentId                =   session('agentId');
    	$agent_info = M('agent')->where(array('id'=>$agentId))->field(true)->find();
    	if (empty($agent_info)) {
    		redirect(U('Login/index'));
    	}
    	if ($agent_info['is_lock'] == 1) {
    		redirect(U('packageSelect'));
    	}
    }

    /*
    *申请代理-套餐选择
    */
    public function packageSelect(){

	    	//判断是否已被激活
	    	$agentId                =   session('agentId');
	    	$agent_info = M('agent')->where(array('id'=>$agentId))->field(true)->find();
	    	if ($agent_info['is_lock'] == 0) {
	    		redirect(U('Index/index'));
	    	}

	    	// $agent_applying = M('agent_applying')->where(array('user_id'=>$this->userid,'status'=>1))->field(true)->find();
	    	$agent_applying = M('agent_applying')->where(array('phone'=>$agent_info['phone'],'status'=>1))->field(true)->find();
	    	// dump($agent_applying);die;
	    	if (empty($agent_applying)) {
	    		# code...
	    		redirect(U('Applyagent/agentReg'));
	    	}


	    	$packageAll = $this->packageAll();
	    	if ($agent_applying['apply_type'] == 1) {	//上级添加，二级代理
	    		# code...
	    		foreach ($packageAll as $key => $value) {
	    			if ($value['level'] == 2) {
	    				# code...
	    				$goodsList[$key] = $value;
	    			}
	    		}

	    	}elseif ($agent_applying['apply_type'] == 2) {	//自主申请，一级代理或二级代理
	    		# code...
	    		$goodsList = $packageAll;
	    	}
	    	// dump($goodsList);die;

	    	$this->assign('list',$goodsList);
	    	$this->display('packageSelect');
    }

    //所有代理套餐
    protected function packageAll(){
	    	$package = array(
	    		1=>array('id'=>1,'level'=>1,'goods_name'=>'一级套餐','price'=>5000,'goods_nums'=>15000),
	    		2=>array('id'=>2,'level'=>2,'goods_name'=>'二级套餐','price'=>3000,'goods_nums'=>7500),
	    		3=>array('id'=>3,'level'=>2,'goods_name'=>'二级套餐','price'=>1000,'goods_nums'=>2250),
			);

			return $package;
    }

    /*
    *套餐详情
    */
    public function packageDetail(){
	    	//判断是否已被激活
	    	$agentId                =   session('agentId');
	    	$agent_info = M('agent')->where(array('id'=>$agentId))->field(true)->find();
	    	if ($agent_info['is_lock'] == 0) {
	    		redirect(U('Index/index'));
	    	}

	    	$id = I('get.id',0,'intval');
	    	if (!$id) {
	    		# code...
	    		redirect(U('packageSelect'));
	    	}
	    	$packageAll = $this->packageAll();

	    	$goodsDetail = $packageAll[$id];

	    	$this->assign('info',$goodsDetail);
	    	$this->display();
    }


    /*
    *申请代理，套餐支付
    */
    public function packageBuy(){
        if (IS_POST) {
                # code...
                $agentId                =   session('agentId');
                $agent_info             =   M('agent')->where(array('id'=>$agentId))->field(true)->find();

            	$goods_id = I('post.goods_id',0,'intval');
            	if (empty($goods_id)) {
            		# code...
                    die(json_encode(array('code'=>'0','message'=>'套餐参数错误')));

            	}
		    	$packageAll = $this->packageAll();

		    	$goodsDetail = $packageAll[$goods_id];


                //生成订单
		        $tag        =  date('ymdHis', NOW_TIME) . $gameuser['game_user_id'] . randomString('6', 0); //支付编号
                $order_data =   array();
                $order_data['user_id']         	=    $this->userid;
                $order_data['order_sn']         =   'PG'.date('YmdHis',NOW_TIME).randomString('6',0);//订单编号
                $order_data['out_trade_no']		=	$tag;
                $order_data['goods_id']         =   $goodsDetail['id'];
                $order_data['goods_nums']     	=   $goodsDetail['goods_nums'];
                $order_data['goods_name']     	=   $goodsDetail['goods_name'];
                $order_data['total_amount']     =   $goodsDetail['price'];
                $order_data['agent_id']     	=   $agentId;
                $order_data['dateline']         =   NOW_TIME;
                // dump($order_data);die;
                $order_id                       =   M('agent_package_order')->add($order_data);
                if(!$order_id){
                    die(json_encode(array('code'=>'0','message'=>'生成订单错误！')));
                }
                

                //调用支付
                $payInfo['openid']          = $this->openid;
                $payInfo['out_trade_no']    = $order_data['out_trade_no'];//支付编号
                $payInfo['total_fee']       = 0.01*100;
               	// $payInfo['total_fee']     = $param_array['order_amount']*100;
                $payInfo['order_type']      = 3;    //订单类型,1=代理购买房卡，2=玩家购买房卡,3=购买套餐成为申请代理
                $PayInfo                    = R('Home/Weixin/wxPay',$payInfo);
                die(json_encode(array('code'=>'1','message'=>$order_id,'PayInfo'=>$PayInfo)));
        }
        
    }

	/**
	 * [agentList 供货商列表]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function agentList() {
			$startTime      = I('get.start_time');
			$endTime        = I('get.end_time');
			$keyword		= I('get.keyword');

			$where = array();
			$whereStr = '';

			$where['is_delete'] =   0;
			$where['pid']       =   session('agentId');
			if( !empty($keyword) ) {
				$keyword   = addslashes($keyword);
				$keyword   = urldecode($keyword);
				$whereStr .= "AND phone LIKE \"%{$keyword}%\" ";
	                        
				$whereStr .= "OR id = {$keyword} ";
	                        
                $where['_complex'] 		= array(
					'phone' => array('like', '%'.$keyword.'%'),
					'nickname' => array('like', '%'.$keyword.'%'),
					'id' 	=> array('eq', $keyword),
					'_logic' 	=> 'OR',
				);
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
				$whereStr .= "AND dateline BETWEEN {$startTime} AND {$endTime} ";
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
			$limit      =   I('limit',10,'intval');
	                $totalPages = ceil($count/$limit);
	                $p  =   I('get.page',1,'intval');

			$agentList = $AgentModel->where($where)->page($p, $limit)->order('dateline DESC')->select();
	                
	        $isAjax =   I('get.isAjax',0,'intval');
	        if(!empty($isAjax) && $isAjax == 1){
	            foreach ($agentList as $key => $value) {
	                $agentList[$key]['add_card_url']    =   U('Agent/addInsureScore',array('agent_id'=>$value['id']));
	                $agentList[$key]['dateline']        = date('Y/m/d H:i:s',$value['dateline']);
	            }
	            die(json_encode(array('code'=>1,'msg'=>'获取成功','info'=>$agentList)));
	        }
			$this->assign('agentList', $agentList);
			$this->assign('totalpage', $totalPages);

			//当前代理信息
			$agent_info = M('agent')->where(array('id'=>session('agentId')))->field(true)->find();
			$this->assign('agent_info', $agent_info);


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
			if (!empty($agent_info) && $agent_info['level'] != 1) {
					# code...
					die(json_encode(array('code'=>0,'msg'=>'无权添加下级代理')));
			}

			$phone                  =   I('post.phone',0,'trim');
			if($phone == 0){
					die(json_encode(array('code'=>0,'msg'=>'手机号码不得为空')));
			}

			//判断代理是否存在
			// $phone_agent_count      =   M('agent')->where(array('phone'=>$phone))->count();
			$phone_agent_count      =   M('agent')->where(array('phone'=>$phone,'is_delete'=>0))->count();
			if($phone_agent_count){
					die(json_encode(array('code'=>0,'msg'=>'当前手机号码已存在')));
			}

			//判断是否有申请过代理
			$agent_applying_count      =   M('agent_applying')->where(array('phone'=>$phone,'status'=>array('in',array(0,1))))->count();
			if($agent_applying_count){
					die(json_encode(array('code'=>0,'msg'=>'当前手机号码已存在')));
			}

			$nickname = I('post.nickname','','trim');
			if (empty($nickname)) {
					# code...
					die(json_encode(array('code'=>0,'msg'=>'请填写代理姓名')));
			}
			$wechat_id = I('post.wechat_id','','trim');
			$data = array();
			$data['phone'] = $phone;
			$data['nickname'] = $nickname;
			$data['wechat_id'] = $wechat_id;
			$data['user_id'] = 0;
			$data['level'] = 2;
			$data['agent_password'] = think_ucenter_md5('12345');
			$data['pid']            = session('agentId');
			$data['dateline']            = NOW_TIME;
			$data['apply_type']	=	1;
			if ( M('agent_applying')->add($data) ) {
					die(json_encode(array('code'=>1,'msg'=>'添加成功，等待平台审核')));
			} else {
					die(json_encode(array('code'=>0,'msg'=>'添加失败')));
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
		if( IS_POST ) {
			$AgentModel = D('Agent');
			$data     = $AgentModel->create(I('post.'), 2);

			if ( empty($data) ) {
				$this->publicError($AgentModel->getError());
			} else {
				$id = I('post.id', '', 'int');
				if ( empty($id) ) {
                    $tips   =   array(
                        'msg'   =>  '参数丢失',
                        'goUrl' =>  U('Agent/agentInfo')
                    );
                    $this->assign('tips',$tips);
                    $this->display('index:public_error');
                    exit();
				}
				if ( $AgentModel->where(array('id'=>$id))->data($data)->save() >= 0 ) {
                        $this->publicSuccess('保存成功',U('Agent/agentInfo'));
				} else {
                        $this->publicError('保存失败',U('Agent/agentInfo'));
				}
			}
		} else {
			$agentId    =   session('agentId');
			$AgentModel =   M('Agent');
			$agentInfo  =   $AgentModel->where(array('id'=>$agentId))->find();
			if(empty($agentInfo)){
                    $this->publicError('个人数据异常',U('Agent/agentInfo'));
			}

			if ($agentInfo['is_delete']) {
					# code...
                	$this->publicError('个人数据异常',U('Agent/agentInfo'));
			}
			if(empty($agentInfo['invitation_code'])){
					$invitation_code	=	randomString(6, 3);
					$agentInfo['invitation_code']	=	$invitation_code;
					$AgentModel->where(array('id'=>$agentId))->setField(array('invitation_code'=>$invitation_code));
			}

			//统计会员人数
			$game_user_ids = M('gameuser')->where(array('invitation_code'=>$agentInfo['invitation_code']))->field('game_user_id')->select();
			$agentInfo['player_num']	=	count($game_user_ids);



			$region     =   M('region');
			// 省
			$province   =   $region->where(array('pid'=>1))->select();
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
                                die(json_encode(array('code'=>0,'msg'=>'参数丢失','url'=>'')));
			}

			$agent_info =   $AgentModel->where(array('id'=>$agentId))->field('id,agent_password')->find();
			if(empty($agent_info)){
                                die(json_encode(array('code'=>0,'msg'=>'个人代理数据错误','url'=>'')));
			}

			$old_password       =   I('post.old_password','','trim');
			$new_password       =   I('post.new_password','','trim');
			$re_new_password    =   I('post.re_new_password','','trim');
                        if(empty($old_password) || empty($new_password) || empty($re_new_password)){
                                die(json_encode(array('code'=>0,'msg'=>'请输入密码','url'=>'')));
                        }

			if($agent_info['agent_password'] != think_ucenter_md5($old_password)){
                                die(json_encode(array('code'=>0,'msg'=>'原密码输入错误','url'=>'')));
			}

			if($re_new_password != $new_password){
                                die(json_encode(array('code'=>0,'msg'=>'确认密码不一致','url'=>'')));
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
                        die(json_encode(array('code'=>1,'msg'=>'密码修改成功，请重新登录','url'=>U('Login/login'))));
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
	 * [addAgent 充卡]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function addInsureScore() {
			//当前用户代理agent_id
			if( IS_POST ) {
		            $agentId                =   session('agentId');
		            if(empty($agentId)){
		                    die(json_encode(array('code'=>0,'msg'=>'未登录','url'=>U('Home/Login'))));
		            }
		            $agent_info             =   M('agent')->where(array('id'=>$agentId))->field('id,user_id,phone,agent_password,room_card')->find();
		            if(empty($agent_info)){
		                    die(json_encode(array('code'=>0,'msg'=>'您的信息错误','url'=>U('Home/Login'))));
		            }
		                        
					$pay_nums               =   I('post.pay_nums',0,'intval');
					if($pay_nums <= 0){
		                    die(json_encode(array('code'=>0,'msg'=>'请填写充值数量','url'=>'')));
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

					$agent_id           =   I('post.agent_id',0,'intval');
					if($agent_id == 0){
		                    die(json_encode(array('code'=>0,'msg'=>'代理参数错误','url'=>U('Agent/agentList'))));
					}
		            $to_agent_info             =   M('agent')->where(array('id'=>$agent_id))->field('id,user_id,phone,agent_password,room_card')->find();
		            if(empty($to_agent_info)){
		                    die(json_encode(array('code'=>0,'msg'=>'代理信息错误','url'=>U('Agent/agentList'))));
		            }

					//代理减少房卡数
		            M('agent')->where(array('id'=>$agent_info['id']))->setDec('room_card',$pay_nums);
		            //下级代理增加房卡数
		            M('agent')->where(array('id'=>$to_agent_info['id']))->setInc('room_card',$pay_nums);
		                        
					//充值记录
					$user_recharge_recored_data  =   array();
					$user_recharge_recored_data['agent_id']      =   $agentId;
					$user_recharge_recored_data['pay_nums']      =   $pay_nums;
					$user_recharge_recored_data['desc']          =   '代理['.$agent_info['phone'].']成功为下级代理['.$to_agent_info['phone'].']充值房卡：'.$pay_nums.'颗';
					$user_recharge_recored_data['add_time']      =   NOW_TIME;
		            $user_recharge_recored_data['to_agent_id']   =   $agent_id;
		            $user_recharge_recored_data['type']          =   2;
					M('user_recharge_recored')->add($user_recharge_recored_data);

		            die(json_encode(array('code'=>1,'msg'=>'充值成功','url'=>U('Agent/agentList'))));

			} else {
	                //当前代理
	                $agentId                =   session('agentId');
	                if(empty($agentId)){
	                        redirect(U('Home/Login'));
	                }
	                $agent_info             =   M('agent')->where(array('id'=>$agentId))->field(true)->find();
	                if(empty($agent_info)){
	                        redirect(U('Home/Login'));
	                }

					//下级代理信息
					$agent_id           =   I('get.agent_id',0,'intval');
		            if(empty($agent_id)){
		                    redirect(U('Agent/agentList'));
		            }
		            $to_agent_info             =   M('agent')->where(array('id'=>$agent_id))->field(true)->find();
		            if(empty($to_agent_info)){
		                    redirect(U('Agent/agentList'));
		            }
					
					$this->assign('agent_info', $agent_info);
					$this->assign('to_agent_info', $to_agent_info);

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
					$this->publicError('参数丢失！');
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
				$this->publicError('参数丢失！');
			}

			$data = array(
	                'is_delete' => '1',
			);

			$goodsAgent = M('agent');
			if ( $goodsAgent->where(array('id'=>$id))->data($data)->save() >= 0 ) {
				$this->publicSuccess('删除成功！', U('Agent/agentList'));
			} else {
				$this->publicError('删除失败！', U('Agent/agentList'));
			}
	}

    /**
     * [resetPwd 重置密码]
     * @author TF <[2281551151@qq.com]>
     */
    public function resetPwd() {
            $id = I('get.id', '', 'int');

            if ( empty($id) ) {
                $this->publicError('参数丢失！');
            }

            $data = array(
                'agent_password' => think_ucenter_md5('12345'),
            );
            $goodsAgent = M('agent');
            if ( $goodsAgent->where(array('id'=>$id))->data($data)->save() >= 0 ) {
                $this->publicSuccess('操作成功！', U('Agent/agentList'));
            } else {
                $this->publicError('操作失败！', U('Agent/agentList'));
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
				$this->publicError($AgentModel->getError());
			} else {
				$id = I('post.id', '', 'int');

				if ( empty($id) ) {
					$this->publicError('参数丢失！');
				}

				if ( $AgentModel->where(array('id'=>$id))->data($data)->save() >= 0 ) {
					$this->publicSuccess('保存成功！', U('Agent/agentList'));
				} else {
					$this->publicError('保存失败！', U('Agent/agentList'));
				}
			}
		} else {
			$id = I('get.id', '', 'int');

			if ( empty($id) ) {
				$this->publicError('参数丢失！');
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
	*收入报表/微信提现
	*/
	public function incomeReport(){
		$agentId                =   session('agentId');
		if (IS_POST) {
			# code...
			$money = I('post.money',0,'floatval');
			$money = (intval($money * 100))/100;
			$agent_info = M('agent')->where(array('id'=>$agentId))->field(true)->find();
			if (empty($agent_info)) {
				# code....
				die(json_encode(array('code'=>0,'msg'=>'信息有误')));
			}


			//若存在未处理申请，则不能再申请
			$where = array();
			$where['agent_id'] = $agentId;
			$where['status']   = 0;
			$untreated_agent_withdrawals = M('agent_withdrawals')->where($where)->field(true)->find();
			if (!empty($untreated_agent_withdrawals)) {
				# code...
				die(json_encode(array('code'=>0,'msg'=>'您有未处理的提现申请，请稍候再试')));
			}


			//判断余额账户，不得少于1元
			if (floatval($agent_info['available_balance']) < 1) {
				# code....
				die(json_encode(array('code'=>0,'msg'=>'余额不足')));
			}
			if (floatval($agent_info['available_balance']) < $money) {
				# code....
				die(json_encode(array('code'=>0,'msg'=>'余额不足')));
			}

			if (($money%100) != 0) {
				# code....
				die(json_encode(array('code'=>0,'msg'=>'提现金额为100的倍数')));
			}
			//写入提现记录
			$agent_withdrawals_data	=	array();
			$agent_withdrawals_data['agent_id']	=	$agentId;
			$agent_withdrawals_data['openid']	=	$this->openid;
			$agent_withdrawals_data['money']	=	$money;
			$agent_withdrawals_data['dateline']	=	NOW_TIME;
			$agent_withdrawals_data['status']	=	0;
			$res = M('agent_withdrawals')->add($agent_withdrawals_data);
			
			if ($res) {
				# code...
				$new_available_balance = floatval($agent_info['available_balance']) - $money;
				$res2 = M('agent')->where(array('id'=>$agentId))->setField(array('available_balance'=>$new_available_balance)); // 用户的积分加3
				if (!$res2) {
					# code...
					//若操作不成功，删除提现记录
					M('agent_withdrawals')->where(array('id'=>$res))->delete();
					die(json_encode(array('code'=>0,'msg'=>'系统繁忙')));
				}
				die(json_encode(array('code'=>1,'msg'=>'您的提现申请，我司已收到，稍后将有平台工作人员与您联系核对转账信息')));
			}
			die(json_encode(array('code'=>0,'msg'=>'系统繁忙')));
		}else{

			$agent_info = M('agent')->where(array('id'=>$agentId))->field(true)->find();

			//判断是否完善银行信息
	        $agent_withdrawals_account = M('agent_withdrawals_account')->where(array('agent_id'=>$agentId))->field(true)->find();
	        if (empty($agent_withdrawals_account) || empty($agent_withdrawals_account['alipay_account']) || empty($agent_withdrawals_account['bank_card'])) {
	            // redirect(U('Agent/agentWithdrawalsAccount'),5,'<h2>为保证代理正常取款，请立即进入个人中心完善银行信息...</h2>');
	            echo "<script>alert('为保证代理正常取款，请立即进入个人中心完善银行信息');location.href='/Home/Agent/agentWithdrawalsAccount';</script>";
	            exit();
	        }
			$this->assign('agent_info',$agent_info);
			$this->display('incomeReport');
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
				die(json_encode(array('code'=>0,'msg'=>'个人数据错误！')));
			}

			$agent_password               =   I('post.agent_password','','trim');
			if($agent_password == ''){
				die(json_encode(array('code'=>0,'msg'=>'请输入账户密码')));
			}

			if($agent_info['agent_password'] != think_ucenter_md5($agent_password)){
				die(json_encode(array('code'=>0,'msg'=>'账户密码错误')));
			}
			if(empty($truename)){
				die(json_encode(array('code'=>0,'msg'=>'真实姓名不能为空')));		
			}
			if(empty($alipay_account)){
				die(json_encode(array('code'=>0,'msg'=>'支付宝账号不能为空')));			
			}
			if(empty($bank_name)){
				die(json_encode(array('code'=>0,'msg'=>'提现银行不能为空')));				
			}
			if(empty($bank_card)){
				die(json_encode(array('code'=>0,'msg'=>'银行卡号不能为空')));				
			}
			if(empty($bank_subbranch)){
				die(json_encode(array('code'=>0,'msg'=>'支行名称不能为空')));				
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
				die(json_encode(array('code'=>1,'msg'=>'保存成功')));
			} else {
				die(json_encode(array('code'=>0,'msg'=>'保存失败')));	
			}

		}else{

			$agent_withdrawals_account = M('agent_withdrawals_account')->where(array('agent_id'=>session('agentId')))->field(true)->find();
			$this->assign('agent_withdrawals_account',$agent_withdrawals_account);

			$bankList = $this->getBankList();
			$this->assign('bankList',$bankList);
			$this->display('agentWithdrawalsAccount');
		}
	}
}
<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;
use OT\DataDictionary;

class IndexController extends HomeController {

    public function __construct() {
        parent::__construct();
    }

    /*
     * 首页-精选
     */

    public function index() {
        $this->checkAgentLogin();
        $agentId                =   session('agentId');
        $agent_info             =   M('agent')->where(array('id'=>$agentId))->field(true)->find();

        if (empty($agent_info)) {
            redirect(U('Login/index'));
        }

        if ($agent_info['is_delete'] == 1) {
            redirect(U('Login/index'),3,'<h1>当前账号不存在或已删除</h1>');

        }
        if ($agent_info['is_lock'] == 1) {
            redirect(U('Agent/packageSelect'),3,'<h1>当前账号未激活，马上去激活...</h1>');
            
        }
        if ($agent_info['agent_password'] == think_ucenter_md5('12345')) {
            // redirect(U('Agent/editPwd'),3,'<h1>请重新设置密码...</h1>');
        }

        
        //判断是否完善银行信息
        $agent_withdrawals_account = M('agent_withdrawals_account')->where(array('agent_id'=>$agentId))->field(true)->find();
        $indexMsg = '';
        if (empty($agent_withdrawals_account)) {
            $indexMsg = '为保证代理正常取款，请立即进入个人中心完善银行信息';
        }

        $avatar = M('public_user')->where(array('id' => $this->userid))->getField('avatar');
        $agent_info['avatar']   =   $avatar;
//        dump($agent_info);die;
        $this->assign('agent_info', $agent_info);
        $this->assign('indexMsg', $indexMsg);
        $this->display();
    }

    protected function checkAgentLogin(){
        $agentId                =   session('agentId');
        $agentAccount                =   session('agentAccount');
        if(!$agentId || !$agentAccount){
            redirect(U('Login/index'));
        }
    }

    /*
    *门店管理
    */
    public function store(){
            $list = M('offline_stores')->field(true)->select();
    //        dump($list);die;
            $this->assign("list", $list);
            $this->display('store');
    }
}

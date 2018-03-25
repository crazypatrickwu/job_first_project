<?php
namespace Agent\Controller;
use Think\Controller;
// 供货商信息
class AgentController extends BaseController {
    /**
    * [agentInfo 供货商信息]
    * @author TF <[2281551151@qq.com]>
    */
    public function agentInfo() {
    	if ( IS_POST ) {

    	} else {
            $agentInfo = M('goods_agent')->where(array('id'=>session('agentId')))->find();
            $this->assign('agentInfo', $agentInfo);
	        $this->display('agentInfo');
    	}
    }

    /**
    * [changePassword 修改供货商密码]
    * @author TF <[2281551151@qq.com]>
    */
    public function changePassword() {
    	if ( IS_POST ) {
            $goodsAgent = M('goodsAgent');

            $oldPassword   = I('post.old_password');
            $password      = I('post.password');
            $repassword    = I('post.repassword');

            if ( $password != $repassword ) {
                $this->error('重复输入的密码有误！');
            }

            $length = strlen($password);
            if ( $length < 6 || $length > 16 ) {
                $this->error('密码长度范围错误！');
            }


            $where = array(
                'agent_id'       => session('agentId'),
                'agent_password' => encrypt($oldPassword),
            );

            $data = array(
                'agent_password' => encrypt($password),
            );

            if ( $goodsAgent->where($where)->count() <= 0 ) {
                $this->error('旧密码错误！');
            }

            if ( $goodsAgent->where($where)->data($data)->save() >= 0 ) {
                $this->success('密码修改成功, 请重新登录！');
                session('agentId', null);
                session('agentAccount', null);
                session('rememberPassword', null);
            } else {
                $this->error('密码修改失败！');
            }
    	} else {
    		$this->display('changePassword');
    	}
    }
}
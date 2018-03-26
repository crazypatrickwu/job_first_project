<?php

namespace Pcweb\Controller;

use Think\Controller;
use User\Api\UserApi;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class LoginController extends Controller {
    
    public function __construct() {
        parent::__construct();
        
        $AuthInfo = R('Home/Weixin/wxJs');
        $this->assign('signPackage', $AuthInfo);
    }

    public function index() {
        session_start();
        if (IS_POST) {
            // 记住密码
            $rememberPassword = I('post.rememberPassword');
            if ( $rememberPassword == '1' ) {
                $nextWeekTime = 3600 * 24 * 7;
                session_cache_expire($nextWeekTime / 60);
                session_set_cookie_params($nextWeekTime);
            }
            $account  = I('post.account');
            $password = I('post.password');

            // 采用系统加密

            $agentInfo = M('pcweb_user')->where(array('username'=>$account))->field(true)->find();
//            dump($agentInfo);die;
            if(empty($agentInfo)){
                die(json_encode(array('code'=>0,'msg'=>'账号不存在')));
            }
            if(think_ucenter_md5($password) != $agentInfo['password']){
                die(json_encode(array('code'=>0,'msg'=>'账号或密码错误')));
            }

            session('pcwebUserId',      $agentInfo['id']);
            session('pcwebAccount', $agentInfo['nickname']);
            if ( $rememberPassword == '1' ) {
                session('rememberPassword', 1);
            }

            die(json_encode(array('code'=>1,'msg'=>'登录成功')));

//            redirect(U('Index/index'));
        }  else {
            $pcwebUserId                =   session('pcwebUserId');
            dump($pcwebUserId);exit;
            if($pcwebUserId){
                redirect(U('Index/index'));
            }
        }
    }
    
    /*
     * 注册
     */
    public function register(){
            if(IS_POST){
//                dump(I('post.'));die;
                $data   =   array();
                $data['username']   =   I('post.username','','trim');
                $data['nickname']   =   I('post.nickname','','trim');
                $data['password']   =   I('post.password','','trim,think_ucenter_md5');
                $data['question']   =   I('post.question','','trim');
                $data['answer']   =   I('post.answer','','trim');
                $data['phone']   =   I('post.phone','','trim');
                $data['email']   =   I('post.email','','trim');
                $data['truename']   =   I('post.truename','','trim');
                $data['idcard']   =   I('post.idcard','','trim');
                $data['dateline']   =   NOW_TIME;
                $res    =   M('pcweb_user')->add($data);
                if($res){
                    die(json_encode(array('code'=>1,'msg'=>'注册成功','url'=>U('Index/index'))));
                }  else {
                    die(json_encode(array('code'=>0,'msg'=>'注册失败')));
                }
            }  else {
                $this->display();
            }
    }

        /**
     * [logout 管理员退出]
     * @author TF <[2281551151@qq.com]>
     */
    public function logout() {
        
//        session_start();
        session('pcwebUserId', null);
        session('pcwebAccount', null);
//        die(json_encode(array('code'=>1,'msg'=>'退出成功','url'=>U('Index/index'))));
//        redirect(U('Index/index'));
    }
    
    /*
     * 忘记密码
     */
    public function forgetPwd(){
        if(IS_POST){
                $step   =   I('post.step',1,'intval');
                if($step){
                    switch ($step) {
                        case 1:
                            $phone  =   I('post.phone','','trim');
                            $code   =   I('post.yzmcode','','trim');
                            
                            /*上线删除这一段开始*/
                            die(json_encode(array('code' => '1', 'msg' => '验证成功','url'=>U('Login/forgetPwd',array('step'=>2,'phone'=>$phone)))));
                            /*上线删除这一段结束*/
                            
                            $check = M('sms')->where(array('mobile' => $phone))->field('mobile,checkcode')->order("id DESC")->find();
                            if ($code != $check['checkcode']) {
                                die(json_encode(array('code' => '0', 'msg' => '验证有误')));
                            }
                            die(json_encode(array('code' => '1', 'msg' => '验证成功','url'=>U('Login/forgetPwd',array('step'=>2,'phone'=>$phone)))));
                            break;
                        case 2:
                            $phone   =   I('post.phone','','trim');
                            if(empty($phone)){
                                die(json_encode(array('code' => '0', 'msg' => '参数错误')));
                            }
                            $pwd1   =   I('post.pwd1','','trim');
                            $pwd2   =   I('post.pwd2','','trim');
                            if(empty($pwd1) || empty($pwd2)){
                                die(json_encode(array('code' => '0', 'msg' => '请输入密码')));
                            }
                            if($pwd1 != $pwd2){
                                die(json_encode(array('code' => '0', 'msg' => '两次密码输入不一致')));
                            }
                            
                            $data   =   array();
                            $data['agent_password']   =   think_ucenter_md5($pwd1);
                            M('agent')->where(array('phone'=>$phone))->setField($data);
                            die(json_encode(array('code' => '1', 'msg' => '验证成功','url'=>U('Login/index'))));
                            break;
                        default:
                            break;
                    }
                }
        }  else {
                $step   =   I('get.step',1,'intval');
                if($step){
                    switch ($step) {
                        case 1:
                            $this->display('forgetpwd_step1');
                            break;
                        case 2:
                            $phone  =   I('get.phone','','trim');
                            $this->assign('phone', $phone);
                            $this->display('forgetpwd_step2');
                            break;

                        default:
                            break;
                    }
                }
        }
    }

    public function sendcode() {
        if(IS_POST){
            $phone  =   I('post.phone','','trim');
            if(empty($phone)){
                    die(json_encode(array('code'=>0,'msg'=>'手机号码不得为空')));
            }
            $agentInfo     =   M('agent')->where($phone)->field($phone)->find();
            if(empty($agentInfo)){
                die(json_encode(array('code'=>0,'msg'=>'账号不存在')));
            }
            if ($agentInfo['is_lock'] == '1') {
                die(json_encode(array('code'=>0,'msg'=>'账户被锁定')));
            }
            if ($agentInfo['is_delete'] == '1') {
                die(json_encode(array('code'=>0,'msg'=>'账户不存在或已删除')));
            }
            
            /*上线删除这一段开始*/
            die(json_encode(array('code'=>1,'msg'=>'短信发送成功')));
            /*上线删除这一段结束*/
            
            $create_time    =   M('sms')->where(array('mobile' => $phone))->field('create_time,mobile')->order("id DESC")->find();
            $now_time = NOW_TIME;
            if ($create_time['create_time']) {
                $create = $create_time['create_time'] + 60;
                if ($create >= $now_time) {
                    die(json_encode(array('code'=>0,'msg'=>'短信发送太过频繁')));
                } else {
                    $rand = msg_rand();
                    $content = "您的验证码为：" . $rand;
                    $phone = $create_time['mobile'];
                    $res = msg_sendcode($phone, $content);
                    if ($res == true) {
                        $data['create_time'] = NOW_TIME;
                        $data['content'] = $content;
                        $data['checkcode'] = $rand;
                        if (M('sms')->where(array('mobile' => $phone))->data($data)->save()) {
                            die(json_encode(array('code'=>1,'msg'=>'短信发送成功')));
                        }
                    }
                }
            } else {
                $rand = msg_rand();
                $content = "您的验证码为：" . $rand;
                $res = msg_sendcode($phone, $content);
                if ($res == true) {
                    $data['mobile']     = $phone;
                    $data['checkcode']  = $rand;
                    $data['content']    = $content;
                    $data['create_time']= NOW_TIME;
                    $data['mobile']     = $phone;
                    if (M('sms')->data($data)->add()) {
                        die(json_encode(array('code'=>1,'msg'=>'短信发送成功')));
                    }
                }
            }
        }
    }
        
        /* @function:代理申请
         * @aother  :丁马利
         * @date    :20170429
         */
        public function applyAgent(){
            if (IS_POST){
//                dump(I('post.'));die;
                $AgentApplyingModel = D('AgentApplying');
                $data     = $AgentApplyingModel->create(I('post.'), 1);
                if ( empty($data) ) {
                                $this->publicError($AgentApplyingModel->getError());
                } else {
                        $phone                  =   I('post.phone',0,'trim');
                        if($phone == 0){
                                $this->publicError('手机号码不得为空');
                        }

                        $phone_agent_count      =   M('agent')->where(array('phone'=>$phone))->count();
                        if($phone_agent_count){
                                $this->publicError('当前手机号码已注册');
                        }
                        $phone_agent_applying_info      =   M('agent_applying')->where(array('phone'=>$phone))->field(true)->find();
                        if(!empty($phone_agent_applying_info)){
                                if($phone_agent_applying_info['status'] == 1){
                                    $this->publicError('当前手机号码已注册');
                                }  elseif ($phone_agent_applying_info['status'] == 0) {
                                    $this->publicError('当前手机号码已注册，在审核中，请耐心等待');
                                }
                        }
                        $data['agent_password'] = think_ucenter_md5('12345');
                        $data['pid']            = session('agentId');
                        if ( $AgentApplyingModel->data($data)->add() ) {
                                $this->publicSuccess('您的信息已经提交成功，准备审核中，请耐心等待');
                        } else {
                                $this->publicError('提交失败');
                        }
                }
            }  else {
                $this->display();
            }
        }
        
        
        public function publicSuccess($msg='操作成功',$url=''){
            $tips   =   array(
                'msg'   =>  $msg,
                'goUrl' =>  $url
            );
            $this->assign('tips',$tips);
            $this->display('index:public_success');
            exit();
        }
        
        public function publicError($msg='操作失败',$url=''){
            $tips   =   array(
                'msg'   =>  $msg,
                'goUrl' =>  $url
            );
            $this->assign('tips',$tips);
            $this->display('index:public_error');
            exit();
        }

}

?>
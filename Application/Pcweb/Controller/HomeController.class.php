<?php
namespace Pcweb\Controller;
use Think\Controller;
use User\Api\UserApi;

/**
 * 前台公共控制器
 * 为防止多分组Controller名称冲突，公共Controller名称统一使用分组名称
 */
class HomeController extends Controller {
	/* 空操作，用于输出404页面 */
	public function _empty(){
//		$this->redirect('Index/index');
	}
	public function _initialize(){
                header("Content-type:text/html;charset=utf8");
                session_start();
//                $agentId                =   session('agentId');
//                $agentAccount           =   session('agentAccount');
//                if(!$agentId || !$agentAccount){
//                    redirect(U('Login/index'));
//                }
                echo "<h3>Page Not Found !</h3>";
                exit();
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
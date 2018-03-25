<?php
namespace Home\Controller;
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

    public function _initialize()
    {
        session_start();
    }


	public function initialize(){
                header("Content-type:text/html;charset=utf8");
                session_start();
                $this->Rid    =   S('Rid');
                if(!$this->Rid){
                    $this->Rid = 8;
                    S('Rid',$this->Rid);
                }
                $this->debug    =   false;   //true开启调试，false关闭调试
                $this->prefix = C('DB_PREFIX');
                if($this->debug){
                    $this->userid = '126';
                    $this->openid = 'o7d5cv7JIlJxfJfkmEEoNPCWhKCw';
                }  else {
                    $wx_user					= $this->Auth();
                    $this->userid 				= $wx_user['WxUserId'];
                    $this->openid 				= $wx_user['OpenId'];
                }
                $this->cart_invalid_time    =   3600;
                // 读取数据库配置
                $config = M('config')->where(array('status' => 1))->getField('config_sign, config_value');
                C($config);
                
                $AuthInfo = R('Home/Weixin/wxJs');
                $this->assign('signPackage', $AuthInfo);
                $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
	}
	protected function getWxUser($openid){
		$wxuser		= M('public_user')->where(array('openid'=>$openid))->field(true)->find();
		if (empty($wxuser) || $wxuser['id'] <= 0) {
			return array();
		}
                $wxuser['nickname'] = base64_decode($wxuser['nickname']);
		return $wxuser;
	}
	protected function Auth(){
		//cookie('wx_user');exit();
		$wx_user    				= json_decode(base64_decode(FauthCode(cookie('wx_user_vxiaotian_'.$this->Rid),'DECODE')),true);
//                $wx_user    =   array();
		if(!$wx_user || !$wx_user['WxUserId'] || !$wx_user['OpenId']){
			$callBack   			= base64_encode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
                        $callBack       = str_replace('/', 'XMCODE', $callBack);
			R('Home/Weixin/wxAuth',array('callBack'=>  $callBack));exit();
		}
		return $wx_user;
	}
        
    public function clearWx(){
            cookie('wx_user_vxiaotian_'.$this->Rid,null);
            session('my_shop_id', null);
            echo 'clear wx data';die;
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

        
    public function sqlsrv_model($db_name,$db_table){
            $connectiont = array(
                'db_type' => 'sqlsrv',
                'db_host' => $this->sqlsrv_config['DB_HOST'],//'139.196.214.241',
                'db_user' => $this->sqlsrv_config['DB_USER'],
                'db_pwd' => $this->sqlsrv_config['DB_PWD'],
                'db_port' => $this->sqlsrv_config['DB_PORT'],
                'db_name' => $this->sqlsrv_config['DB_PREFIX'].$db_name,
                'db_charset' => 'utf8',
            );
            $sqlsrv_model   =   M($db_table,NULL,$connectiont);
            return $sqlsrv_model;
    }
}
?>
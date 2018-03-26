<?php
namespace Agent\Controller;
use Think\Controller;
// 登录控制器
class LoginController extends Controller {
	/**
	 * [_initialize 初始化]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function _initialize() {
			C('DEFAULT_THEME','default');
			if ($this->iswap()) {
				C('DEFAULT_THEME','wap');
			}
	}
    /**
     * [login 管理员登录]
     * @author TF <[2281551151@qq.com]>
     */
	public function login() {
        if ( IS_POST ) {
            // 记住密码
            $rememberPassword = I('post.rememberPassword');
            if ( $rememberPassword == '1' ) {
                $nextWeekTime = 3600 * 24 * 7;
                session_cache_expire($nextWeekTime / 60);
                session_set_cookie_params($nextWeekTime);
            }
            session_start();

            $account  = I('post.account');
            $password = I('post.password');

            // 采用系统加密
            $password = think_ucenter_md5($password);

            $agentInfo = M('Agent')->where(array('phone'=>$account, 'agent_password'=>$password,'is_delete'=>0))->order('id DESC')->find();
            if (empty($agentInfo)) {
                $this->error('账户不存在或已删除！');
            }
            if ($agentInfo['is_lock'] == '1') {
            	$this->error('账户未被激活！');
            }
            if ($agentInfo['is_delete'] == '1') {
            	$this->error('账户不存在或已删除！');
            }

    		if ( !empty($agentInfo) ) {
    			session('agentId',      $agentInfo['id']);
                session('agentAccount', $agentInfo['phone']);
                if ( $rememberPassword == '1' ) {
                    session('rememberPassword', 1);
                }

                $this->success('登录成功！', U('Index/statistics'));
            } else {
    			$this->error('密码或用户出错！', U('Login/login'));
            }
    	} else {
			$this->display('login');
    	}
	}

    /**
     * [logout 管理员退出]
     * @author TF <[2281551151@qq.com]>
     */
    public function logout() {
        session_start();
        session('agentId', null);
        session('agentAccount', null);
        session('rememberPassword', null);

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie( session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"] );
        }

        $this->success('退出成功！', U('Login/login'));
    }
	protected function iswap() {
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
			return true;
		}
		//此条摘自TPM智能切换模板引擎，适合TPM开发
		if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT']){
			return true;
		}
		//如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset ($_SERVER['HTTP_VIA'])){
			//找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
		}
		//判断手机发送的客户端标志,兼容性有待提高
		if (isset ($_SERVER['HTTP_USER_AGENT'])) {
			$clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
			//从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				return true;
			}
		}
		//协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT'])) {
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
				return true;
			}
		}
		return false;
	}
}
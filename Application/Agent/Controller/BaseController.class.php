<?php

namespace Agent\Controller;

use Think\Controller;

// 基础控制器
class BaseController extends Controller {

	/**
	 * [_initialize 初始化]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function _initialize() {
		session_start();
		$agentId = session('agentId');         // 管理员用户id
		$agentAccount = session('agentAccount');    // 管理员用户名
		// 其他条件
		$otherCondition = !in_array(CONTROLLER_NAME, array('Login'));
		if (empty($agentId) && empty($agentAccount) && $otherCondition) {
			header('LOCATION:' . U('Login/login'));
			exit();
		} else {
			// 登录时选择【保存一周】。若用户访问，则时间在用户登录那一刻顺延
			$rememberPassword = session('rememberPassword');
			if ($rememberPassword == '1') {
				session_write_close();
				$nextWeekTime = 3600 * 24 * 7;
				session_cache_expire($nextWeekTime / 60);
				session_set_cookie_params($nextWeekTime);
				session_start();
			}

			// 读取数据库配置
			$config = M('config')->where(array('status' => 1))->getField('config_sign, config_value');
			C($config);
			C('DEFAULT_THEME','default');
			if ($this->iswap()) {
				C('DEFAULT_THEME','wap');
			} 
		}
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

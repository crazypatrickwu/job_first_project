<?php
namespace Xadmin\Controller;
use Think\Controller;
// 基础控制器
class BaseController extends Controller {
	/**
	 * [_initialize 初始化]
	 * @author TF <[2281551151@qq.com]>
	 */
	public function _initialize() {
		session_start();
		$adminId 	  = session('adminId');         // 管理员用户id
		$adminAccount = session('adminAccount');    // 管理员用户名
	
		// 其他条件
		$otherCondition = !in_array(CONTROLLER_NAME , array('Login'));
		if ( empty($adminId) && empty($adminAccount) && $otherCondition ) {
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
			$config = M('config')->where(array('status'=>1))->getField('config_sign, config_value');
			C($config);

			//R('Public/auth', array($adminId));

		}
		C('DEFAULT_THEME','default');
// 		if ($this->iswap()) {
// 			C('DEFAULT_THEME','wap');
// 		}

		$getAuthMenu = $this->getAuthMenu();
        //顶部菜单
        $this->admin_menu_list = $getAuthMenu['admin_menu_list'];
		//左侧菜单
        $this->left_menu = $getAuthMenu['left_menu'];
	}


	//获取菜单
	protected function getAuthMenu(){
			$adminId 	  = session('adminId');         // 管理员用户id
			$adminAccount = session('adminAccount');    // 管理员用户名
			$auth        = new \Think\Auth();
	        // 普通用户得到权限列表

	        //顶部菜单
	        $Groups = $auth->getGroups($adminId);
        	$rules_menu = explode(',', trim($Groups[0]['rules_menu']));
        	$admin_where = array();
        	$admin_where['status'] = 1;
        	$admin_where['level'] = 1;
	        if ($Groups[0]['group_id'] != 1) {
	        	if (!empty($rules_menu)) {
        			$admin_where['id'] = array('in',$rules_menu);
	        	}else{
        			$admin_where['id'] = 0;
	        	}
	        }
        	$admin_menu_list = M('admin_menu')->where($admin_where)->field(true)->select();


        	//左侧菜单
        	$where_left_menu = array();
        	$where_left_menu['status'] = 1;
        	$where_left_menu['level'] = array('gt',1);
        	$where_left_menu['m_controller'] = CONTROLLER_NAME;
        	if ($Groups[0]['group_id'] != 1) {
	        	if (!empty($rules_menu)) {
        			$where_left_menu['id'] = array('in',$rules_menu);
	        	}else{
        			$where_left_menu['id'] = 0;
	        	}
        	}

	        $left_menu = M('admin_menu')->where($where_left_menu)->field(true)->select();
	        $left_big_menu = array();
	        foreach ($left_menu as $key => $value) {
	            if ($value['level'] == 2) {
	                $left_big_menu[]['big'] = $value;
	            }elseif ($value['level'] == 3) {
	                $left_son_menu[] = $value;   
	            }
	        }

	        foreach ($left_son_menu as $k1 => $v1) {
	            foreach ($left_big_menu as $k2 => $v2) {
	                if ($v1['pid'] == $v2['big']['id']) {
	                    if (!empty($v1['m_param'])) {
	                        $v1['params_arr_tmp'] = explode('&', $v1['m_param']);
	                        foreach ($v1['params_arr_tmp'] as $k3 => $v3) {
	                            $param_v = explode('=', $v3);
	                            $v1['params_arr'][$param_v[0]] = $param_v[1];
	                        }
	                    }
	                    $left_big_menu[$k2]['son'][] = $v1;
	                }
	            }
	        }
        	return array('admin_menu_list'=>$admin_menu_list,'left_menu'=>$left_big_menu);
	}

	//获取左侧菜单
    protected function getLeftMenu(){
	        $where_left_menu = array('status'=>1,'level'=>array('gt',1),'m_controller'=>CONTROLLER_NAME);
	        $left_menu = M('admin_menu')->where($where_left_menu)->field(true)->select();
	        $left_big_menu = array();
	        foreach ($left_menu as $key => $value) {
	            if ($value['level'] == 2) {
	                $left_big_menu[]['big'] = $value;
	            }elseif ($value['level'] == 3) {
	                $left_son_menu[] = $value;   
	            }
	        }

	        foreach ($left_son_menu as $k1 => $v1) {
	            foreach ($left_big_menu as $k2 => $v2) {
	                if ($v1['pid'] == $v2['big']['id']) {
	                    if (!empty($v1['m_param'])) {
	                        $v1['params_arr_tmp'] = explode('&', $v1['m_param']);
	                        foreach ($v1['params_arr_tmp'] as $k3 => $v3) {
	                            $param_v = explode('=', $v3);
	                            $v1['params_arr'][$param_v[0]] = $param_v[1];
	                        }
	                    }
	                    $left_big_menu[$k2]['son'][] = $v1;
	                }
	            }
	        }
	        return $left_big_menu;
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
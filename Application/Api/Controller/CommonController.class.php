<?php
namespace Api\Controller;
use Think\Controller;
/**
 * Common控制器
 * @author 王远庆
 */
class CommonController extends Controller {
	/**
	 * 后台控制器初始化
	 */
	protected $ApiParam = array();
	protected $ApiHash = '';
	protected function _initialize()
	{
		/* 读取数据库中的配置 */
		$config 	= S('DB_CONFIG_DATA');
		if(!$config){
			$config = api('Config/lists');
			S('DB_CONFIG_DATA',$config);
		}
		C($config); //添加配置
	}
	/**
	 * 返回JSON数据
	 */
	protected function ReturnJson($data=array())
	{
		$ApiHash	= empty($this->ApiHash) ? '' : $this->ApiHash;
		$base		= array('code' => '-1', 'msg'=>'系统错误', 'time'=>date('Y-m-d H:i:s',NOW_TIME),'apiurl'=>U(),'ApiHash'=>$ApiHash,'explain'=>'','data' =>'');
		$BackData	= array_merge($base,$data);
		if (C('IS_EXPLAIN') !== true) unset($BackData['explain']);//删除接口说明
		$jsondata	= json_encode($BackData);
		$IsLogs		= C('IS_API_LOGS');
		if ($IsLogs == true)
		{
			//记录接口调用日志
			$Logs['api_url']		= U();
			$Logs['create_time'] 	= NOW_TIME;
			$Logs['ip']				= getip();
			$Logs['msg']			= $BackData['msg'];
			$Logs['parames']		= json_encode($this->GetApiParam());
			M('api_logs')->add($Logs);
		}
		die($jsondata);
	}
	/**
	 *数据安全加密
	 */
	protected function ArraySort($data=array(),$hash){
		if (empty($data))
		{
			return md5(rand(1, 5).C('DATA_AUTH_KEY')) === $hash ? true : false;
		}
		ksort($data);
		$value='';
		foreach($data as $v){
			$value .= $v;
		}
		$this->ApiHash = md5($value.C('DATA_AUTH_KEY'));
		return $this->ApiHash === $hash ? true : false;
	}
	/**
	 * 数据安全校验
	 */
	protected function CheckData($PostData,$Fields)
	{
		if (empty($Fields) || empty($PostData)) $this->ReturnJson();
		//获取接口信息
		if (I('get.getapiinfo') === '1') $this->ReturnJson(array('code' => '0', 'msg'=>'ok','data'=>array($this->errorInfo,$Fields)));
		$sign		= array();
		$BackData 	= array();
		//先判断数据传递是否完整合法
		foreach ($Fields as $val)
		{
			//不能为空
			if ($val[2] == 1)
			{
				if (($val[1] == 'Int' && intval($PostData[$val[0]]) <= 0) || ($val[1] == 'String' && empty($PostData[$val[0]])))
				{
					$this->ReturnJson(array('code' => $val[4], 'msg'=>$val[3].'不能为空'));
				}
			}
			//hash不参与签名
			if ($val[0] != 'hash')
			{
				$sign[]	= $val[0];
			}
			else
			{
				$hash = $PostData[$val[0]];
			}
			$BackData[$val[0]]	= $val[1] == 'Int' ? intval($PostData[$val[0]]) : trim($PostData[$val[0]]);
		}
		//判断签名校验是否通过
		if ($this->ArraySort($sign,$hash) == false)
		{
			$this->ReturnJson(array('code' => '3', 'msg'=>'参数校验失败'));
		}
		return $this->SetApiParam($BackData);
	}
	//用户ID校验
	protected function check_user($uid,$hashid)
	{
		if (md5($uid.C('DATA_AUTH_KEY')) != $hashid)
		{
			$this->ReturnJson(array('code' =>'4','msg'=>'用户ID不合法'));
		}
		//校验用户是否存在
		$user 		= new UserApi;
		$userinfo	= $user->info($uid);
		if ($userinfo == '-1')
		{
			$this->ReturnJson(array('code' =>'5','msg'=>'用户不存在'));
		}
	}
	//加密校验串
	protected function check_hashid($str,$hashid,$msg='')
	{
		$msg		= empty($msg) ? '数据串校验错误' : $msg;
		if (md5($str.C('DATA_AUTH_KEY')) != $hashid || empty($str) || empty($hashid))
		{
			$msg	= "校验串:".$msg;
			$this->ReturnJson(array('code' =>'6','msg'=>$msg));
		}
	}
	//生成校验串
	protected function make_hashid($str)
	{
		return md5($str.C('DATA_AUTH_KEY'));
	}
	//设置接口参数
	protected function SetApiParam($param)
	{
		$this->ApiParam =  $param;
		return $this->ApiParam;
	}
	//获取接口参数
	protected function GetApiParam()
	{
		return $this->ApiParam;
	}
	//通用分页列表数据集获取方法
	protected function lists ($model,$where=array(),$order='',$field=true,$page=1){
		$options    = array();
		$REQUEST    = (array)I('request.');
		//数据对象初始化
		$model  	= is_string($model) ? M($model) : $model;
		$OPT        = new \ReflectionProperty($model,'options');
		$OPT->setAccessible(true);
		//获取主键
		$pk         = $model->getPk();
		if($order===null){
			//order置空
		}elseif( $order==='' && empty($options['order']) && !empty($pk) ){
			$options['order'] = $pk.' desc';
		}elseif($order){
			$options['order'] = $order;
		}

		$where  			= empty($where) ?  array() : $where;
		$options['where']   = $where;
		$options      		= array_merge( (array)$OPT->getValue($model), $options );
		$total        		= $model->where($options['where'])->count();
		$defLimit			= C('LIST_ROWS');
		$listLimit 			= $defLimit > 0 ? $defLimit : 10;
		$remainder			= intval($total-$listLimit*$page);
		//分页
		$model->setProperty('options',$options);
		$this->remainder	= $remainder >= 0 ? $remainder : 0;
		$this->total		= $total;
		return $model->field($field)->limit($listLimit)->page($page)->select();
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
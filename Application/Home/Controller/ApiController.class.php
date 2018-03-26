<?php  
namespace Home\Controller;
use Think\Controller;

class IndexController extends HomeController {

	//系统首页
    public function index(){
    	
		$result = curlHttp('http://dev.xiaotiao.com/Api/Index/Index')
		//R('Api/Index/Index',array('time'=>NOW_TIME,'hash'=>md5($info['id'].C('DATA_AUTH_KEY'))));
		//$this->assgin('result','$result');
        $this->display();
    }


?>
<?php
namespace Agent\Controller;
use Think\Controller;
// 首页
class IndexController extends BaseController {
    
    //首页
    public function index(){
        if(session('agentId') && session('agentAccount')){
            redirect(U("statistics"));
        }  else {
            redirect(U("Login/login"));
        }
    }

    /**
     * [statistics 统计]
     * @author TF <[2281551151@qq.com]>
     */
    public function statistics() {
        //redirect(U("Agent/agentList"));
        $info = array(
            '操作系统' => PHP_OS,
            '运行环境' => $_SERVER["SERVER_SOFTWARE"],
            'PHP运行方式' => php_sapi_name(),
            '上传附件限制' => ini_get('upload_max_filesize'),
            '执行时间限制' => ini_get('max_execution_time').'秒',
            '服务器时间' => date("Y年n月j日 H:i:s"),
            '北京时间' => gmdate("Y年n月j日 H:i:s",time() + 8 * 3600),
            '服务器域名/IP' => $_SERVER['SERVER_NAME'].' [ '.gethostbyname($_SERVER['SERVER_NAME']).' ]',
            '剩余空间' => round((@disk_free_space(".") / (1024 * 1024)),2).'M',
            'register_globals' => get_cfg_var("register_globals")=="1" ? "ON" : "OFF",
            'magic_quotes_gpc' => (1 === get_magic_quotes_gpc()) ? 'YES' : 'NO',
            'magic_quotes_runtime' => (1 === get_magic_quotes_runtime())?'YES':'NO',
        );
        $this->assign('info',$info);

        //当前代理
        $agent_info = M('agent')->where(array('id'=>session('agentId')))->field(true)->find();
        $this->assign('agent_info',$agent_info);
        
    	$this->display('statistics');
    }
}
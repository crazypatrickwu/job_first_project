<?php
namespace Pcweb\Controller;
use Think\Controller;
class DownloadController extends Controller {
    
    public function __construct() {
        parent::__construct();
        $a  =   I('get.a');
        if(!empty($a)){
            $this->redirect('about');
            redirect($url);
        }
        $this->apiUrl   =   'http://'.WEB_DOMAIN;
        $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
    }

    public function index(){
        $this->display('download');
    }
    
    public function about(){
        $this->display();
    }
    
    public function download(){
        $this->display();
    }
}
<?php

// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Pcweb\Controller;

use OT\DataDictionary;

class IndexController extends HomeController {

    public function __construct() {
        parent::__construct();
    }

    /*
     * 首页
     */

    public function index() {
//        dump(session('pcwebUserId'));die;
        //游戏介绍
        $pcweb_games_list   =   M('pcweb_games')->where('1=1')->field(true)->limit(8)->order('sort DESC')->select();
        $this->assign('pcweb_games_list', $pcweb_games_list);
        $this->display();
    }

    /*
     * 关于我们
     */
    public function about() {
        $pcweb_about   =   M('pcweb_about')->where('1=1')->field(true)->find();
        $this->assign('pcweb_about', $pcweb_about);
        $this->display();
    }

    /*
     * 客服中心
     */
    public function contact() {
        $pcweb_contact   =   M('pcweb_contact')->where('1=1')->field(true)->find();
        $this->assign('pcweb_about', $pcweb_contact);
        $this->display();
    }

    /*
     * 游戏介绍
     */
    public function games() {
        //游戏介绍
        $pcweb_games_list   =   M('pcweb_games')->where('1=1')->field(true)->order('sort DESC')->select();
        $this->assign('pcweb_games_list', $pcweb_games_list);
        $this->display();
    }
    
    public function gamesDetail(){
        //游戏介绍
        $id =   I('get.id',0,'intval');
        $pcweb_games_detail   =   M('pcweb_games')->where(array('id'=>$id))->field(true)->find();
        $this->assign('pcweb_games_detail', $pcweb_games_detail);
        $this->display();
    }

    /*
     * 游戏下载
     */
    public function download() {
        $this->display();
    }

    /*
     * 用户服务
     */
    public function service() {
        
        $pcweb_user_service_list   =   M('pcweb_user_service')->where('1=1')->field(true)->order('sort DESC')->select();
        
        foreach ($pcweb_user_service_list as $key => $value) {
            # code...
            $pcweb_user_service_list[$key]['content']   =   htmlspecialchars_decode($value['content']);
        }
        $this->assign('pcweb_user_service_list', $pcweb_user_service_list);
       // dump($pcweb_user_service_list);die;
        $this->display();
    }

    /*
     * 家长监护
     */
    public function guardianship() {
        $pcweb_guardianship_list   =   M('pcweb_guardianship')->where('1=1')->field(true)->order('sort DESC')->select();
        $this->assign('pcweb_guardianship_list', $pcweb_guardianship_list);
        $this->display();
    }

    /*
     * 充值中心
     */
    public function voucher() {
        $this->display();
    }
}
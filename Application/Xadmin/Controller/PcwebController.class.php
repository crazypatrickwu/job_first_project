<?php
namespace Xadmin\Controller;
use Think\Controller;
// 系统控制器
class PcwebController extends BaseController {
    public function __construct() {
        parent::__construct();
        $this->sqlsrv_config   =   C('SQLSRV_CONFIG');
    }
    
    /*
     * 关于
     */
    public function about(){
        if ( IS_POST ) {
    		$data   = array();
                $id =   I('post.id',0,'intval');
                $content     =   I('post.content');
                $data['content']        =   $content;
                $data['dateline']       =   NOW_TIME;
	    	$pcwebAboutModel    = M('pcweb_about');
                if(empty($id)){
                    $pcwebAboutModel->add($data);
                }  else {
                    $pcwebAboutModel->where(array('id'=>1))->setField($data);
                }
    		$this->success('保存成功！');
    	} else {
	    	$pcwebAboutModel    = M('pcweb_about');
	    	$pcweb_about = $pcwebAboutModel->where(array('id'=>1))->field(true)->find();
//                    dump($pcweb_about);die;
	    	$this->assign('info', $pcweb_about);
	    	$this->display();
    	}
    }
    /*
     * 客服中心
     */
    public function contact(){
        if ( IS_POST ) {
            $data   = array();
                $id =   I('post.id',0,'intval');
                $content     =   I('post.content');
                $data['content']        =   $content;
                $data['dateline']       =   NOW_TIME;
                $pcwebAboutModel    = M('pcweb_contact');
                if(empty($id)){
                    $pcwebAboutModel->add($data);
                }  else {
                    $pcwebAboutModel->where(array('id'=>1))->setField($data);
                }
            $this->success('保存成功！');
        } else {
            $pcwebAboutModel    = M('pcweb_contact');
            $pcweb_contact = $pcwebAboutModel->where(array('id'=>1))->field(true)->find();
//                    dump($pcweb_about);die;
            $this->assign('info', $pcweb_contact);
            $this->display();
        }
    }
    
    /*
     * 游戏介绍
     */
    public function games(){
            $count    = M('pcweb_games')->count();
            $page     = new \Think\Page($count, 10);

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }
            $show     = $page->show();

            $gamesList = M('pcweb_games')->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
//            dump($gamesList);die;
            $this->assign('list', $gamesList);
            $this->assign('show', $show);
            $this->display();
    }
    
    public function gamesAdd() {
        if (IS_POST) {
            $data                       =   array();
            $data['title']              =   I('post.title');
            $data['thumb']              =   I('post.thumb');
            $data['content']            =   I('post.content');
            $data['dateline']           =   NOW_TIME;
            $data['sort']               =   I('post.sort',0,'intval');
            $res = M('pcweb_games')->add($data);
            if ($res) {
                $this->success("保存成功",U('games'));
            } else {
                $this->error("保存失败");
            }
        } else {
            $this->display();
        }
    }

    public function gamesEdit() {
        if (IS_POST) {
                $id = I('post.id');
                $data                       =   array();
                $data['title']              =   I('post.title');
                $data['thumb']              =   I('post.thumb');
                $data['content']            =   I('post.content');
                $data['dateline']           =   NOW_TIME;
                $data['sort']               =   I('post.sort',0,'intval');
                $res = M('pcweb_games')->where("id = $id")->setField($data);
                if ($res) {
                    $this->success("保存成功",U('games'));
                } else {
                    $this->error("保存失败");
                }
        } else {
                $id = I('get.id');
                $info = M('pcweb_games')->where("id = $id")->find();
    //            dump($info);die;
                $this->assign("info", $info);

                $this->display();
        }
    }
    
     //游戏介绍删除
    public function gamesDel() { 
        $id = I('get.id',0,'intval');
        if(empty($id)){
            $this->error('参数错误');
        }
        $info = M('pcweb_games')->where(array('id'=>$id))->delete();
        if ($info) {
            $this->success('删除成功');
        } else {
             $this->error("删除失败");
        }
    }
    
    
    /*
     * 游戏下载
     */
    public function download(){
        
    }
    
    /*
     * 家长监护添加
     */
    public function downloadAdd() {
        if (IS_POST) {
            $data                       =   array();
            $data['title']              =   I('post.title');
            $data['content']            =   I('post.content');
            $data['dateline']           =   NOW_TIME;
            $data['sort']               =   I('post.sort',0,'intval');
            $res = M('pcweb_guardianship')->add($data);
            if ($res) {
                $this->success("保存成功",U('guardianship'));
            } else {
                $this->error("保存失败");
            }
        } else {
            $this->display();
        }
    }
    
    /*
     * 家长监护编辑
     */
    public function downloadEdit() {
        if (IS_POST) {
                $id = I('post.id');
                $data                       =   array();
                $data['title']              =   I('post.title');
                $data['content']            =   I('post.content');
                $data['dateline']           =   NOW_TIME;
                $data['sort']               =   I('post.sort',0,'intval');
//                dump($data);die;
                $res = M('pcweb_guardianship')->where("id = $id")->setField($data);
                if ($res) {
                    $this->success("保存成功",U('guardianship'));
                } else {
                    $this->error("保存失败");
                }
        } else {
                $id = I('get.id');
                $info = M('pcweb_guardianship')->where("id = $id")->find();
    //            dump($info);die;
                $this->assign("info", $info);

                $this->display();
        }
    }
    
    /*
     * 家长监护删除
     */
    public function downloadDel() {
        $id = I('get.id',0,'intval');
        if(empty($id)){
            $this->error('参数错误');
        }
        $info = M('pcweb_guardianship')->where(array('id'=>$id))->delete();
        if ($info) {
            $this->success('删除成功');
        } else {
             $this->error("删除失败");
        }
    }
    
    /*
     * 用户服务
     */
    public function service(){
            $count    = M('pcweb_user_service')->count();
            $page     = new \Think\Page($count, 10);

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }
            $show     = $page->show();

            $gamesList = M('pcweb_user_service')->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
//            dump($gamesList);die;
            $this->assign('list', $gamesList);
            $this->assign('show', $show);
            $this->display();
    }
    /*
     * 用户服务添加
     */
    public function serviceAdd() {
        if (IS_POST) {
            $data                       =   array();
            $data['title']              =   I('post.title');
            $data['content']            =   I('post.content');
            $data['dateline']           =   NOW_TIME;
            $data['sort']               =   I('post.sort',0,'intval');
            $res = M('pcweb_user_service')->add($data);
            if ($res) {
                $this->success("保存成功",U('service'));
            } else {
                $this->error("保存失败");
            }
        } else {
            $this->display();
        }
    }
    
    /*
     * 用户服务编辑
     */
    public function serviceEdit() {
        if (IS_POST) {
                $id = I('post.id');
                $data                       =   array();
                $data['title']              =   I('post.title');
                $data['content']            =   I('post.content');
                $data['dateline']           =   NOW_TIME;
                $data['sort']               =   I('post.sort',0,'intval');
//                dump($data);die;
                $res = M('pcweb_user_service')->where("id = $id")->setField($data);
                if ($res) {
                    $this->success("保存成功",U('service'));
                } else {
                    $this->error("保存失败");
                }
        } else {
                $id = I('get.id');
                $info = M('pcweb_user_service')->where("id = $id")->find();
    //            dump($info);die;
                $this->assign("info", $info);

                $this->display();
        }
    }
    
    /*
     * 用户服务删除
     */
    public function serviceDel() { 
        $id = I('get.id',0,'intval');
        if(empty($id)){
            $this->error('参数错误');
        }
        $info = M('pcweb_user_service')->where(array('id'=>$id))->delete();
        if ($info) {
            $this->success('删除成功');
        } else {
             $this->error("删除失败");
        }
    }
    
    /*
     * 家长监护
     */
    public function guardianship(){
        
            $count    = M('pcweb_guardianship')->count();
            $page     = new \Think\Page($count, 10);

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }
            $show     = $page->show();

            $gamesList = M('pcweb_guardianship')->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
//            dump($gamesList);die;
            $this->assign('list', $gamesList);
            $this->assign('show', $show);
            $this->display();
    }
    
    /*
     * 家长监护添加
     */
    public function guardianshipAdd() {
        if (IS_POST) {
            $data                       =   array();
            $data['title']              =   I('post.title');
            $data['content']            =   I('post.content');
            $data['dateline']           =   NOW_TIME;
            $data['sort']               =   I('post.sort',0,'intval');
            $res = M('pcweb_guardianship')->add($data);
            if ($res) {
                $this->success("保存成功",U('guardianship'));
            } else {
                $this->error("保存失败");
            }
        } else {
            $this->display();
        }
    }
    
    /*
     * 家长监护编辑
     */
    public function guardianshipEdit() {
        if (IS_POST) {
                $id = I('post.id');
                $data                       =   array();
                $data['title']              =   I('post.title');
                $data['content']            =   I('post.content');
                $data['dateline']           =   NOW_TIME;
                $data['sort']               =   I('post.sort',0,'intval');
//                dump($data);die;
                $res = M('pcweb_guardianship')->where("id = $id")->setField($data);
                if ($res) {
                    $this->success("保存成功",U('guardianship'));
                } else {
                    $this->error("保存失败");
                }
        } else {
                $id = I('get.id');
                $info = M('pcweb_guardianship')->where("id = $id")->find();
    //            dump($info);die;
                $this->assign("info", $info);

                $this->display();
        }
    }
    
    /*
     * 家长监护删除
     */
    public function guardianshipDel() { 
        $id = I('get.id',0,'intval');
        if(empty($id)){
            $this->error('参数错误');
        }
        $info = M('pcweb_guardianship')->where(array('id'=>$id))->delete();
        if ($info) {
            $this->success('删除成功');
        } else {
             $this->error("删除失败");
        }
    }
    
    /*
     * 会员注册
     */
    public function user(){
        
            $count    = M('pcweb_user')->count();
            $page     = new \Think\Page($count, 10);

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }
            $show     = $page->show();

            $gamesList = M('pcweb_user')->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
//            dump($gamesList);die;
            $this->assign('list', $gamesList);
            $this->assign('show', $show);
            $this->display();
        
    }

    //轮播图列表
    public function picture() {
        $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB', 'PicGameStart');
        $list   =   $sqlsrv_model->table('PicGameStart')->field(true)->order('id DESC')->select();
//        dump($list);die;
        $stateArr   =   array('未启用','已启用');
        foreach ($list as $key => $value) {
            $list[$key]['state_text']   =   $stateArr[$value['state']];
        }
        $this->assign('list', $list);
        $this->display();
    }
    
    protected function sqlsrvModel($db_name,$tb_name){
        $connectiont = array(
            'db_type' => 'sqlsrv',
            'db_host' => $this->sqlsrv_config['DB_HOST'],//'139.196.214.241',
            'db_user' => $this->sqlsrv_config['DB_USER'],
            'db_pwd' => $this->sqlsrv_config['DB_PWD'],
            'db_port' => $this->sqlsrv_config['DB_PORT'],
            'db_name' => $db_name,
            'db_charset' => 'utf8',
        );
        $sqlsrv_model   =   M($tb_name,NULL,$connectiont);
        return  $sqlsrv_model;
    }

    public function pictureAdd() {
         if (IS_POST) {
//             dump(I('post.'));die;
             $picture_image =   I('post.picture_image','','trim');
             if(empty($picture_image)){
                $this->error("请上传图片");
             }
             $State =   I('post.State',0,'intval');
             $sort  =   I('post.sort',0,'intval');
             $data  =   array();
             $data['PicPath']   =   $picture_image;
             $data['Time']      =   NOW_TIME;
             $data['State']     =   $State;
             $data['sort']      =   $sort;
            //dump($data);die;
            $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB', 'PicGameStart');
            $res = $sqlsrv_model->table('PicGameStart')->add($data);
            if ($res) {
                $this->success("保存成功",U('System/picture'));
            } else {
                $this->error("保存失败");
            }
        } else {
            $this->display();
        }
    }
    public function pictureEdit() {
        $sqlsrv_model   =   $this->sqlsrv_model('PlatformDB', 'PicGameStart');
        if (IS_POST) {
//             dump(I('post.'));die;
            $id =   I('post.id',0,'intval');
            if(empty($id)){
                $this->error("参数错误");
            }
            $picture_image =   I('post.picture_image','','trim');
            if(empty($picture_image)){
                $this->error("请上传图片");
            }
            $State =   I('post.State',0,'intval');
            $sort  =   I('post.sort',0,'intval');
            $data  =   array();
            $data['PicPath']   =   $picture_image;
            $data['Time']      =   NOW_TIME;
            $data['State']     =   $State;
            $data['sort']      =   $sort;
            //dump($data);die;
            $res = $sqlsrv_model->table('PicGameStart')->where(array('ID'=>$id))->setField($data);
            if ($res) {
                $this->success("保存成功",U('System/picture'));
            } else {
                $this->error("保存失败");
            }
        } else {
            $id =   I('get.id',0,'intval');
            if(empty($id)){
                $this->error("保存失败");
            }
            $info   =   $sqlsrv_model->table('PicGameStart')->where(array('ID'=>$id))->field(true)->find();
            $this->assign('info',$info);
            $this->display();
        }
    }
     //轮播图删除
    public function pictureDel() { 
        $id = I('get.id',0,'intval');
        if(empty($id)){
            $this->error('参数错误');
        }
        $info = M('module_picture')->where(array('id'=>$id))->delete();
        if ($info) {
            $this->success('删除成功');
        } else {
             $this->error("删除失败");
        }
    }
    
        //系统消息列表
    public function broadcast(){
            $where    = array('1=1');
            $sqlsrv_model   = $this->sqlsrv_model('PlatformDB', 'RacintStrip');
            $count    = $sqlsrv_model->table('RacintStrip')->where($where)->count();
            $page     = new \Think\Page($count, 10);

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }
            $show     = $page->show();

            $broadcastList = $sqlsrv_model->table('RacintStrip')->where($where)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('ID DESC')->select();
//            dump($broadcastList);die;
            $this->assign('list', $broadcastList);
            $this->assign('show', $show);
            $this->display();
    }

    //新增发布
    public function broadcastAdd(){
            if(IS_POST){
                    $content    =   I('post.content','','trim');
                    if(empty($content)){
                        $this->error('广播内容不得为空');
                    }

                    $sqlsrv_model   = $this->sqlsrv_model('PlatformDB', 'RacintStrip');
                    $data   =   array(
                        'Time'      => NOW_TIME,
                        'Context'   =>$content,
                        'State'     =>1,
                        'Sort'      =>  0
                    );
                    $res    =   $sqlsrv_model->table('RacintStrip')->add($data);
                    if($res){
                      $this->success('保存成功',U('broadcast'));
                    }else{
                        $this->error('保存失败');
                    }
                   exit;
            }  else {
                    $this->display();
            }
    }
    //编辑系统消息
      public function broadcastEdit(){
                if(IS_POST){
                    $id=I('post.id',0,'intval');
                    if(empty($id)){
                        $this->error('参数错误');
                    }
                    $content    =   I('post.content','','trim');
                    if(empty($content)){
                        $this->error('广播内容不得为空');
                    }
                    
                    $sqlsrv_model   = $this->sqlsrv_model('PlatformDB', 'RacintStrip');
                    $data   =   array(
                        'Time'      => NOW_TIME,
                        'Context'   =>$content,
                        'State'     =>1,
                        'Sort'      =>0
                    );
                    $res    =   $sqlsrv_model->table('RacintStrip')->where(array('ID'=>$id))->setField($data);
                    if($res){
                      $this->success('保存成功',U('broadcast'));
                    }else{
                        $this->error('保存失败');
                    }
                    exit;
                }else{
                    $id=I('get.id',0,'intval');
                    if(empty($id)){
                        $this->error('参数错误');
                    }
                    $sqlsrv_model   = $this->sqlsrv_model('PlatformDB', 'RacintStrip');
                    $info=$sqlsrv_model->table('RacintStrip')->where(array('ID'=>$id))->field(true)->find();
                    // dump($info);
                    // exit;
                    $this->assign('info',$info);
                    $this->display();
                }
      }
    //删除系统消息
    public function broadcastDel(){
        $id=I('get.id');
        $data['is_delete']=1;
        if(M('article')->where(array('id'=>$id))->data($data)->save()){
              $this->success('删除成功',U('messagelist'));
        }else{
            $this->error('删除失败');
        }
    }
    
    /*
     * 玩家反馈
     */
    public function feedback(){;
            $where    = array('1=1');
            $userid         =   I('userid',0,'intval');
            $where          =   array();
            if ( $userid != '0' ) {
                $userid  = addslashes($userid);
                $userid  = urldecode($userid);
                $where['userid'] = $userid;
            }
            
            $startTime      = I('get.start_time');
            $endTime        = I('get.end_time');
            if( !empty($startTime) && !empty($endTime) ) {
                $startTime = addslashes($startTime);
                $startTime = urldecode($startTime);
                $startTime = str_replace('+', ' ', $startTime);
                $startTime = strtotime($startTime);

                $endTime   = addslashes($endTime);
                $endTime   = urldecode($endTime);
                $endTime   = str_replace('+', ' ', $endTime);
                $endTime   = strtotime($endTime);
                $where['add_time'] = array('BETWEEN', array($startTime, $endTime));
            }

            $getData = array();
            $get     = I('get.');
            foreach ($get as $key => $value) {
                    if ( !empty($key) ) {
                            $getData[$key] = urldecode($value);
                    }
            }

            if ( !empty($getData['add_time']) ) {
                    $getData['add_time'] = search_time_format($getData['add_time']);
            }

            if ( !empty($getData['end_time']) ) {
                    $getData['end_time'] = search_time_format($getData['end_time']);
            }
            $model   = M('player_feedback');
            $count    = $model->where($where)->count();
            $page     = new \Think\Page($count, 10);

            if ($this->iswap()) {
                    $page->rollPage	= 5;
            }
            $show     = $page->show();

            $feedbackList = $model->where($where)->field(true)->limit($page->firstRow . ',' . $page->listRows)->order('id DESC')->select();
//            dump($feedbackList);die;
            $this->assign('list', $feedbackList);
            $this->assign('show', $show);
            $this->display();
        
    }
}
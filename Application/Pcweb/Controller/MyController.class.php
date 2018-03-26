<?php
namespace Pcweb\Controller;
use OT\DataDictionary;

/**
 * 前台分类控制器
 * 主要获取首页聚合数据
 */
class MyController extends HomeController {
	public function _initialize(){
		parent::_initialize();
		$this->wxuser				= $this->getWxUser($this->openid);
                
                $this->limit    =   10;
	}
	public function index(){
		$wxuser                         = $this->wxuser;
		$wxuser['id']                   = 1000000+$this->wxuser['id'];
		$wxuser['total_reward']		= '0.00';//历史累计奖励，需要动态获取
		$wxuser['cash_money']		= '0.00';//可提现金额，需要动态获取
		$wxuser['fans']				= 0;//粉丝量，需要动态获取
		$wxuser['fans_money']		= '0.00';//粉丝销量，需要动态获取
                
                $statistics =   array();
                //累计奖励
                $statistics['accumulated_money']    =   $this->wxuser['accumulated_money'];
                //可提现金额
                $statistics['account_balance']      =   $this->wxuser['account_balance'];
                /*
                * 粉丝销量
                */
                //1、查找粉丝相关订单ID
                $funs_order_id      =   M('order_profit_user')->where(array('uid'=>  $this->userid))->field('id,order_id')->select();
                $order_id_arr       =   array();
                foreach ($funs_order_id as $key => $value) {
                    $order_id_arr[] =   $value['order_id'];
                }
                $order_id_arr       =   array_unique($order_id_arr);
                if(!empty($order_id_arr)){
                    //2、查找相关订单总金额
                    $where  =   array();
                    $where['id']        =   array('in',$order_id_arr);
                    $money_total_sum    =   M('order_info')->where($where)->field('SUM(goods_amount) as pm,SUM(refund_amount) as rm')->find();
                    $statistics['funsales_money']       = number_format($money_total_sum['pm'] - $money_total_sum['rm'],2);    //订单金额-退款金额
                }  else {
                    $statistics['funsales_money']       = number_format(0,2);    //订单金额-退款金额
                }
                
                //我的粉丝
                $statistics['funs_count']           =   M('public_user')->where(array('parent_id'=>  $this->userid))->count();
                //购物车商品数量统计
                $order_cart = M('order_cart');
                $cartCount = $order_cart->where(array('user_id' => $this->userid))->sum('goods_number');
		$this->assign('cartCount',$cartCount);
                
                $this->assign('title','代言人');
		$this->assign('wxuser',$wxuser);
		$this->assign('statistics',$statistics);
		$this->display();
	}
	//代言人粉丝列表
	public function fans(){
		$wxuser						= $this->wxuser;
		$wxuser['total_reward']		= '0.00';//历史累计奖励，需要动态获取
		$wxuser['fans_money']		= '0.00';//粉丝销量，需要动态获取
		$wxuser['fans_list']		= array();
                
                //一级
                $User   = M('public_user'); // 实例化User对象
                $where  =   array();
                $where['parent_id'] =   $this->userid;
                $fields         =   'id,avatar,nickname, subscribe_time';
                $funs_list      =   $User->cache('my_fans_one_user_'.$this->userid,60)->where($where)->field($fields)->limit($this->limit)->order('id desc')->select();
                $funscount      =   $User->where($where)->count();// 查询满足要求的总记录数
                $totalpage      =   ceil($funscount/$this->limit);
                
                
                //二级
                $funs_list_tmp          =   $User->where($where)->field('id,parent_id')->order('id desc')->select();
                $parent_id_tmp          =   array();
                foreach ($funs_list_tmp as $key => $value) {
                    $parent_id_tmp[]    =   $value['id'];
                }
                $funscountquan = 0;
                if(!empty($parent_id_tmp)){
                    $User   = M('public_user'); // 实例化User对象
                    $where  =   array();
                    $where['parent_id'] =   array('in',$parent_id_tmp);
                    $funscountquan      =   $User->where($where)->count();// 查询满足要求的总记录数
                }
                
		$this->assign('title','我的粉丝');
		$this->assign('wxuser',$wxuser);
		$this->assign('totalpage',$totalpage);
		$this->assign('funs_list',$funs_list);
		$this->assign('funscount',$funscount);
		$this->assign('funscountquan',$funscountquan);
		$this->display();
	}

        //加载更多粉丝信息
        public function moreFuns(){
            $page   =   I('get.page',0,'intval');
            
            $User   = M('public_user'); // 实例化User对象
            $where  =   array();
            $where['parent_id'] =   $this->userid;
            $fields         =   'id,avatar,nickname, subscribe_time';
            $funs_list      =   $User->where($where)->field($fields)->limit($this->limit*$page,$this->limit)->order('id desc')->select();
            foreach ($funs_list as $key => $value) {
                $funs_list[$key]['subscribe_time'] = !empty($value['subscribe_time']) ? date('Y-m-d H:i:s',$value['subscribe_time']) : 0;
            }
            die(json_encode(array('code'=>200,'list'=>$funs_list)));
        }

        
	//代言人粉丝列表
	public function fansquan(){
		$wxuser						= $this->wxuser;
		$wxuser['total_reward']		= '0.00';//历史累计奖励，需要动态获取
		$wxuser['fans_money']		= '0.00';//粉丝销量，需要动态获取
		$wxuser['fans_list']		= array();
                
                //一级
                $User   = M('public_user'); // 实例化User对象
                $where  =   array();
                $where['parent_id'] =   $this->userid;
                $funscount      =   $User->where($where)->count();// 查询满足要求的总记录数
                
                //二级
                $funs_list_tmp          =   $User->where($where)->field('id,parent_id')->order('id ASC')->select();
                $parent_id_tmp          =   array();
                foreach ($funs_list_tmp as $key => $value) {
                    $parent_id_tmp[]    =   $value['id'];
                }
                $funscountquan = 0;
                if(!empty($parent_id_tmp)){
                    $User   = M('public_user'); // 实例化User对象
                    $where  =   array();
                    $where['parent_id'] =   array('in',$parent_id_tmp);
                    $funscountquan      =   $User->where($where)->count();// 查询满足要求的总记录数
                    $fields         =   'id,avatar,nickname, subscribe_time';
                    $funs_list      =   $User->cache('my_fans_two_user_'.$this->userid,60)->where($where)->field($fields)->limit($this->limit)->order('id ASC')->select();
                    $totalpagequan      =   ceil($funscountquan/$this->limit);
                }
                
		$this->assign('title','我的粉丝');
		$this->assign('wxuser',$wxuser);
		$this->assign('funs_list',$funs_list);
		$this->assign('funscount',$funscount);
		$this->assign('funscountquan',$funscountquan);
		$this->assign('totalpagequan',$totalpagequan);
		$this->display();
	}

        //加载更多粉丝信息
        public function moreFunsquan(){
            $page   =   I('get.page',0,'intval');
            
            $User   = M('public_user'); // 实例化User对象
            $where  =   array();
            $where['parent_id'] =   $this->userid;
            
            $funs_list_tmp          =   $User->where($where)->field('id,parent_id')->order('id ASC')->select();
            $parent_id_tmp          =   array();
            foreach ($funs_list_tmp as $key => $value) {
                $parent_id_tmp[]    =   $value['id'];
            }
            $where  =   array();
            $where['parent_id'] =   array('in',$parent_id_tmp);
            $funscountquan      =   $User->where($where)->count();// 查询满足要求的总记录数
            $fields         =   'id,avatar,nickname, subscribe_time';
            $funs_list      =   $User->where($where)->field($fields)->limit($this->limit*$page,$this->limit)->order('id ASC')->select();
            foreach ($funs_list as $key => $value) {
                $funs_list[$key]['subscribe_time'] = !empty($value['subscribe_time']) ? date('Y-m-d H:i:s',$value['subscribe_time']) : 0;
            }
            die(json_encode(array('code'=>200,'list'=>$funs_list)));
        }
        
        //代言人提现
	public function cash(){
                if(IS_POST){
                    $apply_cash_money =   I('post.apply_cash_money',0);
                    if($apply_cash_money < 50){
                            die(json_encode(array('code'=>0,'message'=>'申请提现金额不得少于50元！')));
                    }
                    
                    if($apply_cash_money > $this->wxuser['account_balance']){
                            die(json_encode(array('code'=>0,'message'=>'申请提现金额不得超过'.$this->wxuser['account_balance'].'元！')));
                    }
                    
                    if($apply_cash_money > 2000){
                            die(json_encode(array('code'=>0,'message'=>'申请提现金额不得超过2000元！')));
                    }
                    $model  =   M();
                    $model->startTrans();
                    if(!empty($this->wxuser)){
                        //判断当前用户是否可以提现
                        if($this->wxuser['forbidden_cash'] == '1'){
                            die(json_encode(array('code'=>0,'message'=>'您已被系统禁止提现！')));
                        }
                        
                        //判断当前用户是否还有申请未审核
                        if($this->wxuser['apply_cash'] > 0){
                            die(json_encode(array('code'=>0,'message'=>'您有未审核的提现，请耐心等待！')));
                        }
                        $where                  =   array();
                        $where['uid']           =   $this->wxuser['id'];
                        $where['status']        =   0;  //待审核
                        $withdraw_cash          =   M('withdraw_cash')->where($where)->count();
                        if($withdraw_cash){
                            die(json_encode(array('code'=>0,'message'=>'您有未审核的提现，请耐心等待！')));
                        }
                        
                        //判断用户当前的账户是否有足够的可提现余额
//                        if($this->wxuser['account_balance'] < 50){
//                            die(json_encode(array('code'=>0,'message'=>'您的余额不足50元，还不能申请提现！')));
//                        }
                        
                        //判断分佣总金额是否与可提现余额相匹配
//                        $where          =   array();
//                        $where['uid']   =   $this->wxuser['id'];
//                        $where['type']  =   1;
//                        $profit_user_sum=   M('order_profit_user')->where($where)->sum('');
//                        if($profit_user_sum != $this->wxuser['accumulated_money']){
//                            die(json_encode(array('code'=>0,'msg'=>'您的账户信息需要审查！')));
//                        }
                        
                        $cash_data  =   array();
                        $cash_data['partner_trade_no']       =   'CASH'.date('YmdHis',NOW_TIME).randomString('6',0);//订单编号
                        $cash_data['cash_acount']   =   $apply_cash_money;
                        $cash_data['uid']           =   $this->wxuser['id'];
                        $cash_data['openid']        =   $this->wxuser['openid'];
                        $cash_data['cash_enroll_time']  =   NOW_TIME;
                        $cash_data['cash_ip']       = getip();
                        $cash_id    =   M('withdraw_cash')->add($cash_data);
                        if($cash_id){
                            //减掉可提现余额
                            $account_data       =   array();
                            $account_data['account_balance']    =   $this->wxuser['account_balance'] - $apply_cash_money;
                            $account_data['apply_cash_now']     =   $this->wxuser['apply_cash_now'] + $apply_cash_money;
                            $public_user_update = M('public_user')->where(array('id'=>$this->wxuser['id']))->setField($account_data);
                            if(!$public_user_update){
                                $model->rollback();
                                die(json_encode(array('code'=>0,'message'=>'提交失败！')));
                            }
                            //记录提现日志
                            $cash_log_data = array();
                            $cash_log_data['cash_id']       =   $cash_id;
                            $cash_log_data['partner_trade_no']       =   $cash_data['partner_trade_no'];
                            $cash_log_data['cash_acount']   =   $cash_data['cash_acount'];
                            $cash_log_data['cash_ip']       =   $cash_data['cash_ip'];
                            $cash_log_data['add_time']      =   $cash_data['cash_enroll_time'];
                            $cash_log_data['note']          =   '申请提现';
                            $cash_log_data['utype']         =   0;
                            $cash_log_data['uid']           =   $this->wxuser['id'];
                            $cash_log_id    =   M('withdraw_cash_log')->add($cash_log_data);
                            if(!$cash_log_id){
                                $model->rollback();
                                die(json_encode(array('code'=>0,'message'=>'提交失败！')));
                            }
                            
                            //提交事务
                            $model->commit();
                            
                            //客服消息
                            $parame =   array();
                            $parame['openid']               = $this->openid;
                            $SendKfMsgContent               = '';
                            $SendKfMsgContent               .= '提现申请\n';
                            $SendKfMsgContent               .= '\n\n';
                            $SendKfMsgContent               .= '亲爱的主人'.$this->wxuser['nickname'].'，您的提现申请已经提交，请耐心等待平台审核，如有疑问请联系客服。';
                            $SendKfMsgContent               .= '\n\n';
                            $SendKfMsgContent               .= '请相信缘分来了挡也挡不住！';
                            $parame['content']              = $SendKfMsgContent;
                            $DataInfo				= R('Home/Weixin/SendKfMsg',$parame);
                            die(json_encode(array('code'=>1,'message'=>'提交成功，请耐心等待审核！')));
                        }
                        $model->rollback();
                        die(json_encode(array('code'=>0,'message'=>'提交失败！')));
                    }  else {
                            $model->rollback();
                            die(json_encode(array('code'=>0,'message'=>'您的个人信息有误！')));
                    }
                }  else {
                    $wxuser						= $this->wxuser;
                    $this->assign('title','提现');
                    $this->assign('wxuser',$wxuser);
                    $this->display();
                }
	}
	//代言人销量记录
	public function salse(){
                if(IS_POST){
                    $page   =   I('post.page',0,'intval');
//                    dump($page);die;
                    //查找分佣数据
                    $order_profit_user_model    =   D('OrderProfitUser');
                    $order_profit_user_model->delLink('OrderDetail');
    //                unset();
                    $where  =   array();
                    $where['uid']   = $this->userid;
                    $where['money'] = array('gt',0);
                    $profitUserList             =   $order_profit_user_model->where($where)->relation(true)->limit($this->limit*$page,$this->limit)->order('id desc')->select();
//                    dump($profitUserList);die;
                    foreach ($profitUserList as $key => $value) {
                        $order_status_arr   =   array();
                        $order_status_arr   = \Pcweb\Controller\OrderController::get_order_status($value['OrderInfo']);
                        $profitUserList[$key]['order_status_code']    =   $order_status_arr['order_status_code'];
                        $profitUserList[$key]['order_status_title']   =   $order_status_arr['order_status_title'];
                        
                        $profitUserList[$key]['dateline'] = date('Y-m-d H:i;s',$value['dateline']);
                        
                        $profitUserList[$key]['total_profit_money'] =   $value['money']*$value['num'];
                    }
                    die(json_encode(array('code'=>200,'list'=>$profitUserList)));
                }  else {
                    $wxuser						= $this->wxuser;
                    $wxuser['cash_money']		= '0.00';//可提现金额，需要动态获取

                    
                    //查找分佣数据
                    $order_profit_user_model    =   D('OrderProfitUser');
                    $where  =   array();
                    $where['uid']   = $this->userid;
                    $where['money'] = array('gt',0);
                    $totalcount     =   $order_profit_user_model->where($where)->count();    //总数据量
                    $totalpage      =   ceil($totalcount/$this->limit);
                    $order_profit_user_model->delLink('OrderDetail');
    //                unset();
                    $profitUserList             =   $order_profit_user_model->where($where)->relation(true)->limit($this->limit)->order('id desc')->select();
                    foreach ($profitUserList as $key => $value) {
                        $order_status_arr   =   array();
                        $order_status_arr   = \Pcweb\Controller\OrderController::get_order_status($value['OrderInfo']);
                        $profitUserList[$key]['order_status_code']    =   $order_status_arr['order_status_code'];
                        $profitUserList[$key]['order_status_title']   =   $order_status_arr['order_status_title'];
                        
                        $profitUserList[$key]['total_profit_money'] =   $value['money']*$value['num'];
                    }
                    
                    $statistics =   array();
                    //累计奖励
                    $statistics['accumulated_money']    =   $this->wxuser['accumulated_money'];
                    
                    /*
                     * 粉丝销量
                     */
                    //1、查找粉丝相关订单ID
                    $funs_order_id      =   M('order_profit_user')->where(array('uid'=>  $this->userid))->field('id,order_id')->select();
                    $order_id_arr       =   array();
                    foreach ($funs_order_id as $key => $value) {
                        $order_id_arr[] =   $value['order_id'];
                    }
                    $order_id_arr       =   array_unique($order_id_arr);
                    $statistics['funsales_money'] = '0.00';
                    if(!empty($order_id_arr)){
                        //2、查找相关订单总金额
                        $where  =   array();
                        $where['id']        =   array('in',$order_id_arr);
                        $money_total_sum    =   M('order_info')->where($where)->field('SUM(goods_amount) as pm,SUM(refund_amount) as rm')->find();
                        $statistics['funsales_money']   = number_format($money_total_sum['pm'] - $money_total_sum['rm'], 2);    //订单金额-退款金额
                    }
                    
                    $this->assign('title','粉丝销量');
                    $this->assign('wxuser',$wxuser);
                    $this->assign('statistics',$statistics);
                    $this->assign('totalpage',$totalpage);
                    $this->assign('profitUserList',$profitUserList);
                    $this->display();
                }
	}
	
	//代言人推广二维码模板
	public function code(){
		//获取网红信息
                $where  =   array();
                $where['uid']           = $this->userid;
                $where['is_commission'] = 1;
                $where['refund_amount'] = 0;
                $finish_order_count =   M('order_info')->where($where)->count();
                if($finish_order_count > 0){
                    
                        //获取二维码
                        $qrcode		= $this->getUserQrcode();
                        //////////
                        //合成图片//
                        //////////
                        $out_img        = $this->makeUserQrcode($qrcode);

                        $qrcode    =   'http://'.$_SERVER['HTTP_HOST'].$out_img;
                        //发送数据
                        $this->assign('title','我的推广二维码');
                        $this->assign('userInfo',$this->wxuser);
                        $this->assign('qrcode',$qrcode);
                        $this->display();
                }  else {
                        //发送数据
                        $this->assign('title','我的推广二维码');
                        $this->display('code_nobuy');
                }
	}
        
        //合成代言人分享图片
        public function makeUserQrcode($qrcode){
//            dump($this->wxuser);die;
                $foldername1 = './Uploads/Qrcode/userQrcode/';
                $filename = $foldername1.$this->userid.'.jpg'; //新文件名字
                if (!file_exists($foldername1)) {
                    mkdir($foldername1, 0777, true);
                }
                if(!file_exists($filename)){
                //永久二维码
                $imageinfo = $this->downloadimagefromweixin($qrcode);
                $local_file = fopen($filename, 'w');
                //如果没有打开文件，进行写入操作
                    if(false !==$local_file){
                        if(false !==fwrite($local_file, $imageinfo['body'])){
                            fclose($local_file);
                        }
                    }
                }else{
                    //已经存在的二维码不执行
                }
                $foldername2 = './Uploads/Qrcode/userQrcodeCode/';
                if (!file_exists($foldername2)) {
                    mkdir($foldername2, 0777, true);
                }
                $back_img  = './Public/Home/images/code-user_bg.jpg';
                $user_qrcode = $foldername2.$this->userid;
//                dump($user_qrcode);die;
                $dst_im = imagecreatefromjpeg($back_img);
                if(!$dst_im){
                    die(json_encode(array('code' => -1100)));
                }
                
                //压缩二维码图片
                $src = $filename; //必须绝对路径！
                $src_im = imagecreatefromjpeg($src);
                if (!$src_im) {
                    die(json_encode(array('code' => -1200)));
                }
                $thumb_img = $this->getThumb($src_im, 222, 222, $user_qrcode, 'jpg'); //压缩二维码
                $src = $user_qrcode . '.jpg';
                $thumb_info = getimagesize($src);
                $alpha = 100;
                imagecopymerge($dst_im, $thumb_img, 450, 960, 0, 0, $thumb_info[0], $thumb_info[1], $alpha);
                
                //添加文字-用户昵称
                $color = imagecolorallocate($dst_im, 255, 255, 255);
                $font = "./Public/Common/simhei.ttf"; //要路径，不能url
                $str = $this->wxuser['nickname'];
                imagettftext($dst_im, 20, 0, 196, 981, $color, $font, $str);
                
                
                //压缩用户头像
                $user_photo = $this->wxuser['avatar'];
                $user_photo_filename = $foldername1.'wxuser_photo_'.$this->userid.'.jpg'; //新文件名字
                $user_photo_targetThumb = $foldername1.'wxuser_photo_'.$this->userid;
                if (!file_exists($foldername1)) {
                    mkdir($foldername1, 0777, true);
                }
                //永久二维码
                $imageinfo = $this->downloadimagefromweixin($user_photo);
                $local_file = fopen($user_photo_filename, 'w');
                //如果没有打开文件，进行写入操作
                if(false !==$local_file){
                    if(false !==fwrite($local_file, $imageinfo['body'])){
                        fclose($local_file);
                    }
                }
                $src_photo = $user_photo_filename; //必须绝对路径！
                $src_photo_im = imagecreatefromjpeg($src_photo);
                if (!$src_photo_im) {
                    die(json_encode(array('code' => -1200)));
                }
                $thumb_img = $this->getThumb($src_photo_im, 70, 70, $user_photo_targetThumb, 'jpg'); //压缩二维码
                $src = $user_photo_targetThumb . '.jpg';
                $thumb_info = getimagesize($src);

                //圆角长度，最好是图片宽，高的一半
                $image_width  = $thumb_info[0];
                $image_height = $thumb_info[1];
                $halfWidth     = 70 / 2;

                //获取四分之一透明圆角
                $lt_img    = $this->get_lt($halfWidth);
                //改造$img2 左上角圆角透明
                imagecopymerge($thumb_img, $lt_img, 0, 0, 0, 0, $halfWidth, $halfWidth, 100);
                //旋转图片
                $lb_corner    = imagerotate($lt_img, 90, 0);
                //左下角
                imagecopymerge($thumb_img, $lb_corner, 0, $image_height - $halfWidth, 0, 0, $halfWidth, $halfWidth, 100);
                //旋转图片
                $rb_corner    = imagerotate($lt_img, 180, 0);
                //右上角
                imagecopymerge($thumb_img, $rb_corner, $image_width - $halfWidth, $image_height - $halfWidth, 0, 0, $halfWidth, $halfWidth, 100);
                //旋转图片
                $rt_corner    = imagerotate($lt_img, 270, 0);
                //右下角
                imagecopymerge($thumb_img, $rt_corner, $image_width - $halfWidth, 0, 0, 0, $halfWidth, $halfWidth, 100);

                //生成红色
                $red = imagecolorallocate($dst_im, 223, 0, 0);
                //去除参数二中红色设成透明
                imagecolortransparent($dst_im, $red);

                
                imagecopymerge($dst_im, $thumb_img, 40, 950, 0, 0, $thumb_info[0], $thumb_info[1], $alpha);
                //////////
                //合成完毕/
                //////////
                $out_img    =   $foldername2.$this->userid.'.jpg';
                $status = imagejpeg($dst_im, $out_img);
                imagedestroy($src_im);
                imagedestroy($dst_im);
                imagedestroy($src_photo_im);
                unlink($user_photo_filename);
                unlink($filename);

                return $out_img;
        }


        //==================================================合成圆角图片
        //每次生成圆的四个角中一个角，中心透明，边角有色的图片，用于合成
        function get_lt($halfWidth) {
            //根据圆形弧度创建一个正方形的图像
            $img     = imagecreatetruecolor($halfWidth, $halfWidth);
            //图像的背景
            $bgcolor = imagecolorallocate($img, 122, 96, 81);
            //填充颜色
            imagefill($img, 0, 0, $bgcolor);
            //定义圆中心颜色
            $fgcolor = imagecolorallocate($img, 0, 0, 0);
            // $halfWidth,$halfWidth：以图像的右下角开始画弧
            // $halfWidth*2, $halfWidth*2：已宽度、高度画弧
            // 180, 270：指定了角度的起始和结束点
            // fgcolor：指定画弧内的颜色
            imagefilledarc($img, $halfWidth, $halfWidth, $halfWidth*2, $halfWidth*2, 180, 270, $fgcolor, IMG_ARC_PIE);
            //将图片中指定色设置为透明
            imagecolortransparent($img, $fgcolor);
            //变换角度
            // $img    = imagerotate($img, 90, 0);
            // $img    = imagerotate($img, 180, 0);
            // $img    = imagerotate($img, 270, 0);
            // header('Content-Type: image/png');
            // imagepng($img);
            return $img;
        }
        
        function https_post($url,$data=null){
            $curl = curl_init();
            curl_setopt($curl,CURLOPT_URL,$url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,FALSE);
            if(!empty($data)){
                curl_setopt($curl, CURLOPT_POST,1);
                curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
            }
            curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        }
        //获得二维码图片
        function downloadimagefromweixin($url){
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER,0);
            curl_setopt($ch, CURLOPT_NOBODY,0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            $package = curl_exec($ch);
            $httpinfo = curl_getinfo($ch);
            curl_close($ch);
            return array_merge(array('body'=>$package),array('header'=>$httpinfo));
        }
        
        /**
         * @author        chenrunsong
         * @im            需要等比例缩放的图片资源
         * @dstimW        缩放的图片的宽度
         * @dstimH        缩放的图片的高度
         * @targetThumb   缩略图文件保存路径
         * @filetype      缩略图文件类型
         * 2015-12-05
         */
        public function getThumb($im, $dstimgW, $dstimgH, $targetThumb, $filetype)
        {
            //获取im的宽度和高度
            $pic_W = imagesx($im);
            $pic_H = imagesy($im);
            $arr = array();
            switch ($filetype) {
                case 'jpg':
                    $arr[$filetype] = "imagejpeg";
                    break;
                case 'png';
                    $arr[$filetype] = "imagepng";
                    break;
                case 'gif';
                    $arr[$filetype] = "imagegif";
                    break;
                default:
                    echo "";
            }
            if (($dstimgW && $dstimgW < $pic_W) || ($dstimgH && $dstimgH < $pic_H)) {
                if ($dstimgW && $dstimgW < $pic_W) {
                    $dsimgWratio = $dstimgW / $pic_W;
                    $resizereagW = true;
                }
                if ($dstimgH && $dstimgH < $pic_H) {
                    $dsimgHratio = $dstimgH / $pic_H;
                    $resizerreagH = true;
                }
                //缩略图宽高和原图宽高比，取最小的那个
                if ($resizereagW && $resizerreagH) {
                    if ($dsimgWratio < $dsimgHratio)
                        $radio = $dsimgWratio;
                    else
                        $radio = $dsimgHratio;
                }
                if ($resizereagW && !$resizerreagH) {
                    $radio = $dsimgWratio;
                }
                if (!$resizereagW && $resizerreagH) {
                    $radio = $dsimgHratio;
                }
                $imgnewW = $pic_W * $radio;
                $imgnewH = $pic_H * $radio;
                if (function_exists("imgcopyresampled")) {
                    //创建目标资源画布
                    $dst = imagecreatetruecolor($imgnewW, $imgnewH);
                    imagecopyresampled($dst, $im, 0, 0, 0, 0, $imgnewW, $imgnewH, $pic_W, $pic_H);
                } else {
                    $dst = imagecreate($imgnewW, $imgnewH);
                    imagecopyresized($dst, $im, 0, 0, 0, 0, $imgnewW, $imgnewH, $pic_W, $pic_H);
                }
                $arr[$filetype]($dst, $targetThumb . ".$filetype");
                return $dst;
                imagedestroy($dst);
            } else {
                //缩略图自身的宽和高已经大于了原图的宽和高
                //则缩略图的宽和缩略图的高就是原图的宽和原图的高
                $arr[$filetype]($im);
                imagedestroy();
            }
        }
	
	//代言人推广二维码
	public function getUserQrcode(){
		$qrPath						= '';
		if (!empty($this->openid)){
                        
        	$public_user    =   M('public_user')->where(array('openid'=>"$this->openid"))->find();
                        
            if(!empty($public_user)){
                //用户二维码数据
                dblog($public_user);
                $parame     = array(
                    'red_id'        =>  $public_user['red_id'],
                    'scenario_id'   =>  $public_user['scenario_id'],
                    'parent_id'     =>  $public_user['id'],
                    'from'          =>  'buyers'
            	);
	            dblog($parame);
	            $qrData['type']				= 0;
	            $qrData['scene']            = $public_user['id'];
	            $qrData['parame']           = json_encode($parame);
	            $qrData['validity_time']    = 2592000;
	            $qrPath						= R('Home/Weixin/getWxQrcode',$qrData);
            }
		}
		//$data['qrPath']					= $qrPath;
		return $qrPath;
	}
	
	//代言人推广二维码
	public function getUserQrcode2(){
		$qrPath						= '';
		if (!empty($this->openid)){
                        
        	$public_user    =   M('public_user')->where(array('openid'=>"$this->openid"))->find();
                        
            if(!empty($public_user)){
                //用户二维码数据
                dblog($public_user);
                $parame     = array(
                    'red_id'        =>  $public_user['red_id'],
                    'scenario_id'   =>  $public_user['scenario_id'],
                    'parent_id'     =>  $public_user['id'],
                    'from'          =>  'buyers'
            	);
	            dblog($parame);
	            $qrData['type']				= 0;
	            $qrData['scene']            = $public_user['id'];
	            $qrData['parame']           = json_encode($parame);
	            $qrData['validity_time']    = 2592000;
	            $qrPath						= R('Home/Weixin/getWxQrcode',$qrData);
            }
		}
		$data['qrPath']					= $qrPath;
		$this->ajaxReturn($data);
	}
}
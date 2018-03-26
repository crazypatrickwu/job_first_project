<?php  
namespace Home\Controller;

use Think\Controller;

class GoodsController extends HomeController {
    public function __construct() {
        parent::__construct();
        $agentId                =   session('agentId');
        $agentAccount                =   session('agentAccount');
        if(!$agentId || !$agentAccount){
            redirect(U('Login/index'));
        }
    }

    public function index() {
        $list = M('goods')->where(array('buyer'=>'agent'))->select();
        foreach ($list as $key => $value) {
            $list[$key]['total_money']  = floatval($value['goods_price']);
            $list[$key]['total_nums']  = $value['goods_nums']+$value['give_goods_nums'];
        }
//        dump($list);die;
        $this->assign("list", $list);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $data                       =   array();
            $data['goods_name']         =   I('post.goods_name');
            $data['goods_nums']         =   I('post.goods_nums');
            $data['give_goods_nums']    =   I('post.give_goods_nums');
            $data['goods_image']        =   I('post.goods_image');
            $data['info']               =   I('post.info');
            $data['add_time']           =   NOW_TIME;
            $res = D('goods')->add($data);
            if ($res) {
                $this->success("添加成功",U('index'));
            } else {
                $this->error("添加失败");
            }
        } else {
            
            if(!empty(C('ROOM_PRICE'))){
                $room_price =   floatval(C('ROOM_PRICE'));
            }  else {
                $room_price =   1;
            }
            $this->assign("room_price", $room_price);
            $this->display();
        }
    }

    public function edit() {
        if (IS_POST) {
            $id = I('post.id');
            $data                       =   array();
            $data['goods_name']         =   I('post.goods_name');
            $data['goods_nums']         =   I('post.goods_nums');
            $data['give_goods_nums']    =   I('post.give_goods_nums');
            $data['goods_image']        =   I('post.goods_image');
            $data['info']               =   I('post.info');
            $data['add_time']           =   NOW_TIME;
            $res = D('goods')->where("id = $id")->setField($data);
            if ($res) {
                $this->success("编辑成功",U('index'));
            } else {
                $this->error("编辑失败");
            }
        } else {
            $id = I('get.id');
            $info = D('goods')->where("id = $id")->find();
            if(!empty(C('ROOM_PRICE'))){
                $room_price =   floatval(C('ROOM_PRICE'));
            }  else {
                $room_price =   1;
            }
            $info['goods_price']    =   floatval($room_price*$info['goods_nums']);
            
            $this->assign("room_price", $room_price);
//            dump($info);die;
            $this->assign("info", $info);
            
            
            $this->display();
        }
    }
    
    public function detail(){
            $id = I('get.id');
            $info = M('goods')->where("id = $id")->find();
            $info['total_money']    =   floatval($info['goods_price']);
            $info['total_nums']     =   $info['goods_nums']+$info['give_goods_nums'];
            
            $this->assign("info", $info);
            $this->display();
    }

    public function del() {
        $id = (int) $_GET['id'];
        $res = D('goods')->where("id = $id")->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error("删除失败");
        }
    }

    static function getGoodsNameById($goods_id) {
        $info = D("goods")->where("id = $goods_id")->find();
        $info['goods_name'] = $info['goods_name'] ? $info['goods_name'] : '暂无商品';
        return $info['goods_name'];
    }


    public function payConfirm(){
        if (IS_POST) {
                # code...

                $goods_id   =   I('post.goods_id',0,'intval');
                if($goods_id == 0){
                    $this->error('参数错误！');
                }
                $info = M('goods')->where(array('id'=>$goods_id))->field(true)->find();

                $agentId                =   session('agentId');
                $agent_info             =   M('agent')->where(array('id'=>$agentId))->field(true)->find();

                $order_model  =   M();
                $order_model->startTrans();
                
                //生成订单
                $order_data =   array();
                $order_data['agent_id']         =   $agentId;
                $order_data['order_sn']         =   'MJ'.date('YmdHis',NOW_TIME).randomString('6',0);//订单编号
                $order_data['buyer']            =   $agent_info['nickname'];
                $order_data['telephone']        =   $agent_info['phone'];
                $order_data['total_amount']     =   $info['goods_price'];
                $order_data['add_time']         =   NOW_TIME;
                $order_id                       =   M('order')->add($order_data);
                if(!$order_id){
                    $order_model->rollback();
                    die(json_encode(array('code'=>'0','message'=>'生成订单错误！')));
                }
                
                //订单详情
                $detail_data                       =   array();
                $detail_data['order_id']           =   $order_id;
                $detail_data['order_sn']           =   $order_data['order_sn'];
                $detail_data['goods_id']           =   $goods_id;
                $detail_data['goods_name']         =   $info['goods_name'];
                $detail_data['goods_image']        =   $info['goods_image'];
                $detail_data['goods_nums']         =   $info['goods_nums'];
                $detail_data['goods_price']        =   $info['goods_price'];
                $detail_data['give_goods_nums']    =   $info['give_goods_nums'];
                $detail_id                         =   M('order_detail')->add($detail_data);
                if(!$detail_id){
                    $order_model->rollback();
                    die(json_encode(array('code'=>'0','message'=>'生成订单错误！')));
                }
                
                //订单日志
                $log_data               =   array();
                $log_data['agent_id']   =   $agentId;
                $log_data['order_id']   =   $order_id;
                $log_data['desc']       =   '生成订单';
                $log_data['add_time']   =   NOW_TIME;
                $log_id                 =   M('order_log')->add($log_data);
                if(!$log_id){
                    $order_model->rollback();
                    $this->error('生成订单错误！');
                }
                
                //订单提交
                $order_model->commit();

                //调用支付
                $payInfo['openid']          = $this->openid;
                $payInfo['out_trade_no']    = $order_data['order_sn'];//支付编号
                // $payInfo['total_fee']       = 0.01*100;
                if(in_array($this->userid, array(3))){
                    $payInfo['total_fee']       = 0.01*100;
                }else{
                    $payInfo['total_fee']     = $info['goods_price']*100;
                }
                $payInfo['order_type']      = 1;    //订单类型,1=代理购买房卡，2=玩家购买房卡
//                            dblog($payInfo);
                $PayInfo                    = R('Home/Weixin/wxPay',$payInfo);
                die(json_encode(array('code'=>'1','message'=>$cart_id,'PayInfo'=>$PayInfo)));
        }
        
    }
    
    
    //订单支付
    public function confirmPay(){
        $goods_id   =   I('get.goods_id',0,'intval');
        if($goods_id == 0){
            $this->error('参数错误！');
        }
        $info = D('goods')->where(array('id'=>$goods_id))->find();
        if(!empty(C('ROOM_PRICE'))){
            $room_price =   floatval(C('ROOM_PRICE'));
        }  else {
            $room_price =   1;
        }
        
        $agentId    =   session('agentId');
        if(empty($agentId)){
            $this->error('用户信息错误！');
        }
        
        //查找当前代理信息
        $agentInfo  =   M('agent')->where(array('id'=>$agentId))->field('id,nickname,phone,is_lock')->find();
        if(empty($agentInfo)){
            $this->error('用户信息错误！');
        }
        if($agentInfo['is_lock'] != 0){
            $this->error('您的账户已被锁定！');
        }
        
        $order_model  =   M();
        $order_model->startTrans();
        
        //生成订单
        $order_data =   array();
        $order_data['agent_id']         =   $agentId;
        $order_data['order_sn']         =   'MJ'.date('YmdHis',NOW_TIME).randomString('6',0);//订单编号
        $order_data['buyer']            =   $agentInfo['nickname'];
        $order_data['telephone']        =   $agentInfo['phone'];
        $order_data['total_amount']     =   $room_price*$info['goods_nums'];
        $order_data['add_time']         =   NOW_TIME;
        $order_id                       =   M('order')->add($order_data);
        if(!$order_id){
            $order_model->rollback();
            $this->error('生成订单错误！');
        }
        
        //订单详情
        $detail_data                       =   array();
        $detail_data['order_id']           =   $order_id;
        $detail_data['order_sn']           =   $order_data['order_sn'];
        $detail_data['goods_id']           =   $goods_id;
        $detail_data['goods_name']         =   $info['goods_name'];
        $detail_data['goods_image']        =   $info['goods_image'];
        $detail_data['goods_nums']         =   $info['goods_nums'];
        $detail_data['goods_price']        =   $info['goods_nums']*C('ROOM_PRICE');
        $detail_data['give_goods_nums']    =   $info['give_goods_nums'];
        $detail_id                         =   M('order_detail')->add($detail_data);
        if(!$detail_id){
            $order_model->rollback();
            $this->error('生成订单错误！');
        }
        
        //订单日志
        $log_data               =   array();
        $log_data['agent_id']   =   $agentId;
        $log_data['order_id']   =   $order_id;
        $log_data['desc']       =   '生成订单:代理'.'【'.$agentId.'】购买套餐';
        $log_data['add_time']   =   NOW_TIME;
        $log_id                 =   M('order_log')->add($log_data);
        if(!$log_id){
            $order_model->rollback();
            $this->error('生成订单错误！');
        }
        
        //订单提交
        $order_model->commit();
        
        redirect(U('Order/payConfirm',array('order_sn'=>$order_data['order_sn'])));
    }
        
    public function alipay_web($trade_sn,$title,$body,$fee,$notify_url,$return_url){
            include_once(CONF_PATH.'alipay.config.php');
            include_once(VENDOR_PATH.'/Alipay/lib/alipay_submit.class.php');
            $parameter = array(
                    "service"           => "create_direct_pay_by_user",
                    "partner"           => trim($alipay_config['partner']),
                    "payment_type"	=> '1',
                    "notify_url"	=> $notify_url,
                    "return_url"	=> $return_url,
                    "seller_email"	=> $alipay_config['seller_id'],
                    "out_trade_no"	=> $trade_sn,
                    "subject"           => $title,
                    "total_fee"         => $fee,
                    "body"              => $body,
            //  			"show_url"	=> $show_url,
                    "anti_phishing_key"	=> '',
                    "exter_invoke_ip"	=> '',
                    "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
            );
            $alipaySubmit = new \AlipaySubmit($alipay_config);
            $html_text = $alipaySubmit->buildRequestForm($parameter,"post", "确认");
            echo $html_text;die;
            $result 	= array('state'=>'1','data'=>$html_text,'msg'=>'成功');
            return $result;
    }
}

<?php
namespace Home\Controller;
use Think\Controller;

class AutoscriptController extends Controller{
        public function __construct() {
            parent::__construct();
            $this->prefix   =   C('DB_PREFIX');
            $this->counter  =   0;
            header("Content-type:text/html;charset=utf8");
        }
        
        /*
         * 【ERP】
         * 自动同步库存到本地
         */
        public function syncGoodsSkuNum(){
                dblog('自动同步库存到本地');
		Vendor('Efast.EfastHelper');
                $goods_list =   M('document')->where(array('status'=>1))->field('id')->select();
                $goods_ids  =   array();
                foreach ($goods_list as $key => $value) {
                    $goods_ids[]    =   $value['id'];
                }
                $where      =   array();
                $where['goodsid']       =   array('in',$goods_ids);
                $where['status']        =   1;
                $where['goods_sku_sn']  =   array('neq','');
                $goods_sku_list =   M('goods_attr')->where($where)->field('id,sku_num,goods_sku_sn')->select();
//                dump($goods_sku_list);die;
                foreach ($goods_sku_list as $key => $value) {
                    $EfastHelper	= new EfastHelper();
                    $EfastHelper->setParameter('app_act','efast.sku.stock.get');
                    $EfastHelper->setParameter('sd_id', 1);
                    $EfastHelper->setParameter('sku', $value['goods_sku_sn']);
                    $BacData			= $EfastHelper->GetData();
                    $BacData			= json_decode($BacData,true);
                    if(!empty($BacData['resp_data'][$value['goods_sku_sn']])){
                        $sku_update =   M('goods_attr')->where(array('id'=>$value['id']))->setField(array('sku_num'=>$BacData['resp_data'][$value['goods_sku_sn']]['sl']));
                        if($sku_update){
                            $this->counter += 1;
                        }
                    }  else {
                        dblog('--库存同步异常--');
                        dblog($value);
                        dblog($BacData);
                    }
                }
                echo '成功更新：'.$this->counter;
                die;
	}
        
        /*
         * 【ERP】
         * 自动推送订单到ERP
         */
        public function autoSendOrderToErp(){
            dblog('自动推送订单到ERP脚本');
            Vendor('Efast.EfastHelper');
            $where  =   array();
            $where['pay_status']        =   1;
            $where['order_status']      =   array('in',array(0,1));
            $where['shipping_status']   =   0;
            $where['erp_order_sn']      =   '';
            $shippedList    =   D('OrderInfo')->where($where)->relation(true)->limit(3)->order('id desc')->select();
//            dump($shippedList);die;
            
            $express_arr    =   C('ORDER_EXPRESSES');//物流配置信息
            foreach ($shippedList as $k1 => $v1) {
//                sleep(5);
                $EfastHelper	= new EfastHelper();
                $EfastHelper->setParameter('app_act','efast.trade.new.add');
                $EfastOrderDetail   =   array();
                foreach ($v1['OrderDetail'] as $key => $value) {
                    # code...
                    $EfastOrderDetail[$key]['outer_sku']    =   $value['bar_code'];
                    $EfastOrderDetail[$key]['goods_name']   =   $value['goods_name'];
                    $EfastOrderDetail[$key]['goods_number'] =   $value['goods_number'];
                    $EfastOrderDetail[$key]['goods_price']  =   $value['goods_price'];
                    $EfastOrderDetail[$key]['payment_ft']   =   $value['goods_price']*$value['goods_number'];
                    $EfastOrderDetail[$key]['is_gift']      =   0;
                }
                $info   =   array(
                    'sd_id'                 =>  19,  //对应efast店铺id
                    'province_name'         =>  $v1['province'],  //省份
                    'city_name'             =>  $v1['city'],  //城市
                    'district_name'         =>  $v1['county'],  //地区
                    'shipping_name'         =>  'yto',  //快递公司代码
                    'pay_name'              =>  'weixinPay',  //支付方式代码
                    'oid'                   =>  $v1['order_sn'],  //交易号
                    // 'consignee'             =>$order_info['consignee'],  //收货人
                    'consignee'             =>  '不发货',  //收货人
                    'address'               =>  $v1['address'],  //收货地址
                    'zipcode'               =>  '',  //邮编
                    'mobile'                =>  $v1['tel'],  //手机
                    'tel'                   =>  $v1['tel'],  //电话
                    'user_name'             =>  $v1['PublicUser']['openid'],  //买家账号
                    'email'                 =>  '',  //email
                    'postscript'            =>  '',  //买家留言
                    'to_buyer'              =>  '',  //商家备注
                    'add_time'              =>  date('Y-m-d H:i:s',$v1['create_time']),  //创建时间
                    'pay_time'              =>  date('Y-m-d H:i:s',$v1['pay_time']),  //支付时间
                    'goods_count'           =>  $v1['goods_number'],  //商品总数量
                    'goods_amount'          =>  $v1['goods_amount'],  //商品金额
                    'total_amount'          =>  $v1['total_amount'],  //总金额
                    'shipping_fee'          =>  0,  //快递费
                    'order_amount'          =>  $v1['total_amount'],  //应付款
                    'money_paid'            =>  $v1['paid_money'],  //已付款
                    'orders'                =>  $EfastOrderDetail,
                  );
                $EfastHelper->setParameter('info', json_encode($info));
                $BacData    =   NULL;
                $BacData			= $EfastHelper->GetData();
                $BacData			= json_decode($BacData,true);
//                dump($BacData);die;
                if(!empty($BacData['resp_data']['oder_sn'])){
                    $order_info_data    =   array();
                    $order_info_data['order_status']    =   1;  //确认订单
                    $order_info_data['ready_time']      =   NOW_TIME;
                    $order_info_data['express_name']    =   $express_arr[$info['shipping_name']];
                    $order_info_data['express_code']    =   $info['shipping_name'];
                    $order_info_data['erp_order_sn']    =   $BacData['resp_data']['oder_sn'];   //ERP返回单号
                    //更新订单状态
                    $order_info_update = M('order_info')-> where(array('id'=>$v1['id']))->setField($order_info_data);
//                    $parame =   array();
//                    $parame['openid']		= $v1['PublicUser']['openid'];
//                    $SendKfMsgContent               = '';
//                    $SendKfMsgContent               .= '配货中\n';
//                    $SendKfMsgContent               .= '\n\n';
//                    $SendKfMsgContent               .= '亲爱的主人'.$v1['PublicUser']['nickname'].'，我是订单'.$v1['order_sn'].'鞋履，感谢宠幸，即将成为您的双脚新伴侣！24小时内我将启程前往主人目的地。\n';
//                    $SendKfMsgContent               .= '\n\n';
//                    $SendKfMsgContent               .= '主人请耐心等待，我这就来！';
//                    $parame['content']              = $SendKfMsgContent;
//                    $DataInfo				= R('Home/Weixin/SendKfMsg',$parame);
                }  else {
                    dblog($v1);
                    dblog($BacData);
                }
            }
                
        }


        /*
         * 【ERP】
         * 自动获取ERP发货订单脚本
         */
        public function autoGetOrderShipped(){
            dblog('自动获取ERP发货订单脚本');
            Vendor('Efast.EfastHelper');
            $where  =   array();
            $where['pay_status']        =   1;
            $where['order_status']      =   1;
            $where['shipping_status']   =   0;
            $where['erp_order_sn']      =   array('neq','');
            $shippedList    =   D('OrderInfo')->where($where)->relation(true)->limit(20)->order('id asc')->select();
            foreach ($shippedList as $key => $v1) {
                $EfastHelper	= new EfastHelper();
                $EfastHelper->setParameter('app_act','efast.trade.detail.get');
                $EfastHelper->setParameter('oid',$v1['order_sn']);
                $EfastHelper->setParameter('feilds','order_sn,deal_code,order_status,shipping_status,'
                        . 'pay_status,process_status,is_send,is_locked,is_separate,consignee,address,'
                        . 'zipcode,tel,mobile,shipping_name,shipping_time,status,pay_name,invoice_no,order_amount ,money_paid,'
                        . 'user_id ,orders.goods_sn,orders.goods_name,orders.goods_number,orders.goods_price,'
                        . 'orders.goods_barcode,orders.payment_ft,pay_time,to_buyer,postscript,order_amount,'
                        . 'user_nick,money_paid,shipping_fee,cz_shipping_fee');

                $BacData			= $EfastHelper->GetData();
                $BacData			= json_decode($BacData,true);
                $shipped    =   $BacData['resp_data']['shipping_status'];
                if($shipped == '1'){    //如果已经发货，则同步平台订单状态更新为已发货
                    $order_info_data    =   array();
                    $order_info_data['order_status']    =   1;
                    $order_info_data['shipping_status'] =   1;
                    $order_info_data['shipping_time']   =   NOW_TIME;
                    $order_info_data['ready_time']      =   NOW_TIME;
                    $order_info_data['invoice_no']      =   $BacData['resp_data']['invoice_no'];
                    $order_info_update  =   M('order_info')->where(array('id'=>$v1['id']))->save($order_info_data);
                    if($order_info_update){
                        //订单操作日志
                        $order_log_data = array();
                        $order_log_data['type']         =   1;    //平台
                        $order_log_data['uid']          =   1;
                        $order_log_data['order_id']     =   $v1['id'];
                        $order_log_data['msg']          =   'ERP发货成功';
                        $order_log_data['dateline']     =   $order_info_data['shipping_time'];
                        M('order_log')->add($order_log_data);
                        
                        //发送客服消息
                        $parame =   array();
                        $parame['openid']		= $v1['PublicUser']['openid'];
                        $SendKfMsgContent               = '';
//                        $SendKfMsgContent               .= '发货啦\n';
//                        $SendKfMsgContent               .= '\n\n';
                        $SendKfMsgContent               .= '亲爱的主人'.$v1['PublicUser']['nickname'].'，订单'.$v1['order_sn'].'的物流单号为'.$BacData['resp_data']['invoice_no'].'主人可在公众号底部“服务-我的订单”位置查看我的全路程。 \n';
                        $SendKfMsgContent               .= '\n\n';
                        $SendKfMsgContent               .= '记得接我哦~！';
                        $parame['content']              = $SendKfMsgContent;
                        $DataInfo				= R('Home/Weixin/SendKfMsg',$parame);
                    }
                }
            }
        }
        
        /*
         * 【分佣】
         * [自动生成订单分佣脚本]
         */
        public function autoCreateProfit(){
            
        }
        

        /*
         * 【分佣】
         * [自动执行订单可分佣到可提现脚本]
         * 暂定4分钟跑1次
         * 4分钟20个订单，1分钟5个订单，1天=24小时=24*60分钟，即为24*60*5=7200个订单
         */
        public function autoComleteProfit(){
            $where  =   array();
            $where['pay_status']        =   1;  //已支付
            $where['order_status']      =   1;  //已确认
            $where['shipping_status']   =   2;  //已收货
            $where['is_commission']     =   0;  //待分佣
//            $where['auto_commission_time']   =   array('lt',NOW_TIME);  //自动分佣时间
            $where['uid']               =   2;
            $order_list             =   D('OrderInfo')->where($where)->relation(true)->order('id DESC')->limit(20)->select();
            dump($order_list);die;
            foreach ($order_list as $k1 => $v1) {
                    $this->runCreateOrderProfit($v1);   //执行订单分佣到账程序
                    dblog('autoComleteProfit');
                    dblog($v1);
//                dump($v1);die;
            }
//            dump($order_list);die;
        }
        
        //执行订单分佣到账程序
        protected function runCreateOrderProfit($v1){
            //申请售后商品数量
            $model  =   M();
            $model->startTrans();
            try {
                $return_num =   0;
                foreach ($v1['OrderDetail'] as $k2 => $v2) {
                    $Customerservice = array();
                    if($v1['PublicUser']['red_id'] && $v1['PublicUser']['scenario_id']){
                            $Customerservice = D('Customerservice')->where(array('id'=>$v1['PublicUser']['scenario_id']))->relation(true)->find();
                            if(empty($Customerservice)){
                                $model->rollback();
                                dblog('runCreateOrderProfit_4001');
                                return false;
                            }
                    }
                    $odetail = D('OrderDetail')->where(array('id'=>$v2['id']))->relation(true)->find();
                    if(empty($odetail)){
                            $model->rollback();
                            dblog('runCreateOrderProfit_4002');
                            return false;
                    }
                    if($v2['status'] == '0'){   //状态为0即表示当前商品为正常状态
                            if(!empty($Customerservice)){
                                    //网红分佣
                                    $order_profit_red_update = M('order_profit_red')->where(array('id'=>$odetail['OrderProfitRed']['id']))->setField(array('type'=>1));
//                                    dump(M('order_profit_red')->getLastSql());
                                    if(!$order_profit_red_update){
                                        $model->rollback();
                                        dblog('runCreateOrderProfit_4003');
                                        return false;
                                    }
                                    $red_net_update = M('red_net')->where(array('id'=>$odetail['OrderProfitRed']['uid']))->setField(array('account'=>$Customerservice['RedNet']['account']+($odetail['OrderProfitRed']['money']*$odetail['OrderProfitRed']['num'])));
//                                    dump(M('red_net')->getLastSql());
                                    if(!$red_net_update){
                                        $model->rollback();
                                        dblog('runCreateOrderProfit_4004');
                                        return false;
                                    }
                                    //客服分佣
                                    $order_profit_scenario_update = M('order_profit_scenario')->where(array('id'=>$odetail['OrderProfitScenario']['id']))->setField(array('type'=>1));
//                                    dump(M('order_profit_scenario')->getLastSql());
                                    if(!$order_profit_scenario_update){
                                        $model->rollback();
                                        dblog('runCreateOrderProfit_4005');
                                        return false;
                                    }
                                    $customerservice_update = M('customerservice')->where(array('id'=>$odetail['OrderProfitScenario']['uid']))->setField(array('account'=>$Customerservice['account']+($odetail['OrderProfitScenario']['money']*$odetail['OrderProfitScenario']['num'])));
//                                    dump(M('customerservice')->getLastSql());
                                    if(!$customerservice_update){
                                        $model->rollback();
                                        dblog('runCreateOrderProfit_4006');
                                        return false;
                                    }
                            }
//                            dump($odetail['OrderProfitUser']);die;
                            //上级分佣
                            foreach ($odetail['OrderProfitUser'] as $key => $value) {
                                    //更新分佣状态
                                    $order_profit_user_update = M('order_profit_user')->where(array('id'=>$value['id']))->setField(array('type'=>1));
//                                    dump(M('order_profit_user')->getLastSql());
                                    if(!$order_profit_user_update){
                                        $model->rollback();
                                        dblog('runCreateOrderProfit_4007');
                                        return false;
                                    }
                                    $profit_user    =   M('public_user')->where(array('id'=>$value['uid']))->find();
                                    if(!$profit_user){
                                        $model->rollback();
                                        dblog('runCreateOrderProfit_4008');
                                        return false;
                                    }
                                    //更新用户账户
                                    $profit_user_data   =   array();
                                    $profit_user_data['accumulated_money']  =   $profit_user['accumulated_money']+$value['money']*$value['num'];
                                    $profit_user_data['account_balance']    =   $profit_user['account_balance']+$value['money']*$value['num'];
                                    $public_user_update = M('public_user')->where(array('id'=>$profit_user['id']))->setField($profit_user_data);
//                                    dump(M('public_user')->getLastSql());
                                    if(!$public_user_update){
                                        $model->rollback();
                                        dblog('runCreateOrderProfit_4009');
                                        return false;
                                    }
                            }
                    }  else {   //不为0即表未当前商品为售后状态
                            $where  =   array();
                            $where['order_id']          =   $v1['id'];
                            $where['order_detail_id']   =   $v2['id'];
                            $order_return = M('order_return')->where($where)->field('id,order_id,order_detail_id,status,apply_number')->find();
                            if(!$order_return){
                                    $model->rollback();
                                    dblog('runCreateOrderProfit_4010');
                                    return false;
                            }
//                    dump(array($order_return['apply_number'],$v2['goods_number']));die;
                            //如果当前商品申请售后数量
                            $return_num += $order_return['apply_number'];
                            if($order_return['apply_number'] == $v2['goods_number']){   //申请售后数量和购买数量相等，则全退
                                    $profit_data            =   array();
                                    $profit_data['type']    =   2;
                                    $profit_data['num']     =   0;
                                    if(!empty($Customerservice)){
                                            $order_profit_red_update = M('order_profit_red')->where($where)->setField($profit_data); //网红
                                            if(!$order_profit_red_update){
                                                $model->rollback();
                                                dblog('runCreateOrderProfit_4011');
                                                return false;
                                            }
                                            $order_profit_scenario_update = M('order_profit_scenario')->where($where)->setField($profit_data);//客服
                                            if(!$order_profit_scenario_update){
                                                $model->rollback();
                                                dblog('runCreateOrderProfit_4012');
                                                return false;
                                            }
                                    }
                                    $order_profit_user_update = M('order_profit_user')->where($where)->setField($profit_data);//用户上级
//                    if($v1['id'] == 239){
//                        dump($order_profit_user_update);die;
//                    }
                                    if(!$order_profit_user_update){
                                            $model->rollback();
                                            dblog('runCreateOrderProfit_4013');
                                            return false;
                                    }
                            }  else {
                                    $profit_data            =   array();
                                    $profit_data['type']    =   1;
                                    $profit_data['num']     =   $v2['goods_number']-$order_return['apply_number'];
                                    if(!empty($Customerservice)){
                                            //网红分佣
                                            $order_profit_red_update = M('order_profit_red')->where($where)->setField($profit_data);
                                            if(!$order_profit_red_update){
                                                    $model->rollback();
                                                    dblog('runCreateOrderProfit_4014');
                                                    return false;
                                            }
                                            $red_net_update = M('red_net')->where(array('id'=>$odetail['OrderProfitRed']['uid']))->setField(array('account'=>$Customerservice['RedNet']['account']+($odetail['OrderProfitRed']['money']*$profit_data['num'])));
                                            if(!$red_net_update){
                                                    $model->rollback();
                                                    dblog('runCreateOrderProfit_4015');
                                                    return false;
                                            }
                                            //客服分佣
                                            $order_profit_scenario_update = M('order_profit_scenario')->where($where)->setField($profit_data);
                                            if(!$order_profit_scenario_update){
                                                    $model->rollback();
                                                    dblog('runCreateOrderProfit_4016');
                                                    return false;
                                            }
                                            $customerservice_update = M('customerservice')->where(array('id'=>$odetail['OrderProfitScenario']['uid']))->setField(array('account'=>$Customerservice['account']+($odetail['OrderProfitScenario']['money']*$profit_data['num'])));
                                            if(!$customerservice_update){
                                                    $model->rollback();
                                                    dblog('runCreateOrderProfit_4017');
                                                    return false;
                                            }
                                    }

                                    //上级分佣
                                    foreach ($odetail['OrderProfitUser'] as $key => $value) {
                                            //更新分佣状态
                                            $order_profit_user_update = M('order_profit_user')->where($where)->setField($profit_data);
                                            if(!$order_profit_user_update){
                                                    $model->rollback();
                                                    dblog('runCreateOrderProfit_4018');
                                                    return false;
                                            }
                                            $profit_user    =   M('public_user')->where(array('id'=>$value['uid']))->find();
                                            if(!$profit_user){
                                                    $model->rollback();
                                                    dblog('runCreateOrderProfit_4019');
                                                    return false;
                                            }
                                            //更新用户账户
                                            $profit_user_data   =   array();
                                            $profit_user_data['accumulated_money']  =   $profit_user['accumulated_money']+$value['money']*$profit_data['num'];
                                            $profit_user_data['account_balance']    =   $profit_user['account_balance']+$value['money']*$profit_data['num'];
                                            $public_user_update = M('public_user')->where(array('id'=>$profit_user['id']))->setField($profit_user_data);
                                            if(!$public_user_update){
                                                    $model->rollback();
                                                    dblog('runCreateOrderProfit_4020');
                                                    return false;
                                            }
                                    }
                            }
                    }
                }
                //如果申请售后商品总数量等于订单商品总数量，则分佣取消
//                dump(array($return_num,$v1['goods_number']));die;
                if($return_num == $v1['goods_number']){
                        $is_commission = 2;
                        $order_log_data = array();
                        $order_log_data['type']         = 2;
                        $order_log_data['uid']          = 0;
                        $order_log_data['order_id']     = $v1['id'];
                        $order_log_data['msg']          = '订单商品全部申请售后，订单取消分佣';
                        $order_log_data['dateline']     = NOW_TIME;
                        M('order_log')->add($order_log_data);
                        dblog('runCreateOrderProfit_4021');
                }  else {
                        $is_commission = 1;
                        $order_log_data = array();
                        $order_log_data['type']         = 1;
                        $order_log_data['uid']          = 0;
                        $order_log_data['order_id']     = $v1['id'];
                        $order_log_data['msg']          = '订单已分佣';
                        $order_log_data['dateline']     = NOW_TIME;
                        M('order_log')->add($order_log_data);
                        dblog('runCreateOrderProfit_4022');
                }
                $order_info_update  =   M('order_info')->where(array('id'=>$v1['id']))->setField(array('is_commission'=>$is_commission));
                if(!$order_info_update){
                        $model->rollback();
                        dblog('runCreateOrderProfit_4023');
                        return false;
                }
                $model->commit();
                dblog('runCreateOrderProfit ok!');
            } catch (Exception $ex) {
                    $model->rollback();
                    dblog($ex);
                    return false;
            }
            return true;
        }


        /*
         * 未支付，自动取消订单
         * 【定时脚本任务】
         * 暂定20分钟跑1次，20分钟20个订单，1分钟1个订单，1天=24小时=24*60分钟，即为24*60*1=1440个订单
         */
        public function autoOrderCancel(){
            $where  =   array();
            $where['pay_status']    =   0;//未支付
            $where['order_status']  =   0;//正常订单
            $hour   =   0.5;  //2小时
            $where['create_time']   =   array('lt',NOW_TIME-60*60*$hour);
            //查找2小时内未支付的订单，自动取消订单
            $order_list             =   M('order_info')->where($where)->field('id,user_id,order_sn,pay_status,create_time,account_balance_deduction')->limit(5)->select();
//            dump($order_list);die;
            foreach ($order_list as $key => $value) {
                $order_info_data    = array();
                $order_info_data['order_status']    = 2;
                $order_info_data['cancel_time']     = NOW_TIME;
                $order_info_update    = M('order_info')->where(array('id'=>$value['id']))->save($order_info_data);
                if(!$order_info_update){
                    continue;
                }
                $order_log_data = array();
                $order_log_data['type']         = 1;
                $order_log_data['uid']          = 0;
                $order_log_data['order_id']     = $value['id'];
                $order_log_data['msg']          = '超过['.$hour.']小时，系统自动取消订单';
                $order_log_data['dateline']     = NOW_TIME;
                M('order_log')->add($order_log_data);
                
                if(floatval($value['account_balance_deduction']) > 0){
                    M('public_user')->where(array('id'=>$value['user_id']))->setInc('account_balance',$value['account_balance_deduction']);
                    //退余额记录
                    $balance_log_data   =   array();
                    $balance_log_data['user_id']    =   $value['user_id'];
                    $balance_log_data['order_id']    =   $value['id'];
                    $balance_log_data['type']       =   1;
                    $balance_log_data['desc']       =   '系统自动取消订单，余额退回到账户';
                    $balance_log_data['balance']    =   $value['account_balance_deduction'];
                    $balance_log_data['datetime']   =   NOW_TIME;
                    M('public_balance_log')->add($balance_log_data);
                }
            }
        }
        
        /*
         * 【订单】
         * [自动收货脚本]
         */
        public function autoReceiptOrder(){
            $where  =   array();
            $where['pay_status']        =   1;//已支付
            $where['order_status']      =   1;//正常订单
            $where['shipping_status']   =   1;//已发货
            $days   =   10;
            $times   =   $days*86400;  //10天
//            $times   =   300;  //秒
            $where['shipping_time']     =   array('lt',NOW_TIME-$times);
            //查找{$days}天未收货的订单，系统自动收货
            $order_list             =   M('order_info')->where($where)->field('id,order_sn,pay_status,shipping_time,create_time')->limit(20)->select();
//            dump($order_list);die;
            foreach ($order_list as $key => $value) {
                $order_info_data    = array();
                $order_info_data['shipping_status']     = 2;                //已收货
                $order_info_data['receipt_time']        = NOW_TIME;         //收货时间
                $order_info_data['auto_commission_time']= NOW_TIME+$times;   //自动分佣时间
                $order_info_update    = M('order_info')->where(array('id'=>$value['id']))->setField($order_info_data);
                if(!$order_info_update){
                    continue;
                }
                $order_log_data = array();
                $order_log_data['type']         = 1;
                $order_log_data['user_id']      = 0;
                $order_log_data['order_id']     = $value['id'];
                $order_log_data['msg']          = '发货时间超过['.$times.']秒，系统自动确认收货';
                $order_log_data['dateline']     = NOW_TIME;
                M('order_log')->add($order_log_data);
            }
        }
        
        /*
         * 【购物提醒】
         * [自动脚本]
         */
        public function autobuyRemind(){
            $where  =   array();
            $where['b.next_buy_time'] =   array('elt',NOW_TIME);
            $where['u.red_id']  =   array('neq',0);
            $remind_list = M('buy_remind')->alias('b')
                    ->join($this->prefix . 'order_detail AS od ON od.id=b.last_order_detail_id')
                    ->join($this->prefix . 'public_user AS u ON u.id=b.user_id')
                    ->field('u.nickname,u.red_id,b.id,b.openid,b.goods_id,b.sku_id,b.last_buy_time,b.next_cycle_time,b.next_buy_time,od.goods_name,od.goods_number,od.goods_price,od.goods_image,od.specifications_text')
                    ->where($where)
                    ->limit(10)
                    ->order('b.dateline DESC')
                    ->select();
//            dump($remind_list);die;
            foreach ($remind_list as $key => $value) {
                S('autoscript_rid',$value['red_id']);
                $parame =   array();
                $parame['openid']		= $value['openid'];
                $SendKfMsgContent               = '[服务提醒]';
                $value['nickname']              = base64_decode($value['nickname']);
                $SendKfMsgContent               .= $value['nickname'].'您好！'.'您上次购买宝贝选择了'.$value['next_cycle_time'].'天再次购买提醒，时间已到，祝您购物愉快！\n';
                $parame['content']		= $SendKfMsgContent;
//                dump(S('autoscript_rid'));die;
                $DataInfo				= R('Home/Weixin/SendKfMsg',$parame);
                if($DataInfo == true){
                    M('buy_remind')->where(array('id'=>$value['id']))->delete();
                }
                S('autoscript_rid',null);
            }
        }
        
}
<?php
namespace Home\Controller;
use Think\Controller;
use Vendor\Efast\EfastHelper;

class EfastController extends Controller{
        public function __construct() {
            parent::__construct();
            header("Content-type:text/html;charset=utf8");
            $this->counter  =   0;
        }
        
        /*
         * 同步库存到本地
         * 
         */
        public function syncGoodsSkuNum(){
		Vendor('Efast.EfastHelper');
                $goods_sku_list =   M('goods_attr')->where(array('goods_sku_sn'=>array('neq','')))->field('id,sku_num,goods_sku_sn')->select();
                foreach ($goods_sku_list as $key => $value) {
                    $EfastHelper	= new EfastHelper();
                    $EfastHelper->setParameter('app_act','efast.sku.stock.get');
                    $EfastHelper->setParameter('sd_id', 1);
                    $EfastHelper->setParameter('sku', $value['goods_sku_sn']);
                    $BacData			= $EfastHelper->GetData();
                    $BacData			= json_decode($BacData,true);
                    $sku_update =   M('goods_attr')->where(array('id'=>$value['id']))->setField(array('sku_num'=>$BacData['resp_data'][$value['goods_sku_sn']]['sl']));
                    if($sku_update){
                        $this->counter += 1;
                    }
                }
                echo '成功更新：'.$this->counter;
                die;
	}
        
        /*
         * 查看商品库存
         */
        public function skuCheck(){
                Vendor('Efast.EfastHelper');
                
                $EfastHelper	= new EfastHelper();
                $EfastHelper->setParameter('app_act','efast.sku.stock.get');
                $EfastHelper->setParameter('sd_id', 1);
                $EfastHelper->setParameter('sku', 'G62217200235');
                $BacData			= $EfastHelper->GetData();
                $BacData			= json_decode($BacData,true);
                if(empty($BacData['resp_data']['G62217200235'])){
                    
                }
                dump(empty($BacData['resp_data']['G62217200235']));die;
                die;
        }

        

        //测试用
        public function syncTest(){
            Vendor('Efast.EfastHelper');
            $EfastHelper	= new EfastHelper();
            $EfastHelper->setParameter('app_act','efast.trade.new.add');
//            $a = '[{"sd_id":19,"province_name":"\u6d59\u6c5f\u7701","city_name":"\u676d\u5dde\u5e02","district_name":"\u6c5f\u5e72\u533a","shipping_name":"yto","pay_name":"weixinPay","oid":"fsdafsdf4234","consignee":"\u4e0d\u53d1\u8d27","address":"\u9f99\u6e56\u6edf\u6f9c\u5c71","zipcode":"","mobile":"13738084633","tel":"13738084633","user_name":"haha","email":"","postscript":"","to_buyer":"","add_time":"2016-07-22 17:17:04","pay_time":"2016-07-22 17:17:09","goods_count":1,"goods_amount":398,"total_amount":398,"shipping_fee":0,"order_amount":398,"money_paid":398,"orders":[{"outer_sku":"G60004109240","goods_name":"\u4f11\u95f2\u725b\u76ae\u5973\u978b \u65b0\u6b3e\u5706\u5934\u539a\u5e95\u6a61\u80f6\u5e95\u6df1\u53e3\u5355\u978b","goods_number":1,"goods_price":398,"payment_ft":398,"is_gift":0}]},"2016-07-22 17:17:22"]';
            $a = '[{"sd_id":19,"province_name":"\u6d59\u6c5f\u7701","city_name":"\u676d\u5dde\u5e02","district_name":"\u6c5f\u5e72\u533a","shipping_name":"yto","pay_name":"weixinPay","oid":"'.NOW_TIME.'aadfd","consignee":"\u4e0d\u53d1\u8d27","address":"\u9f99\u6e56\u6edf\u6f9c\u5c71","zipcode":"","mobile":"13738084633","tel":"13738084633","user_name":"oAbYqxCg9KFnP6XH57dj3J1v-QPc","email":"","postscript":"","to_buyer":"","add_time":"2016-07-22 17:57:47","pay_time":"2016-07-22 17:58:02","goods_count":"1","goods_amount":"498.00","total_amount":"498.00","shipping_fee":0,"order_amount":"498.00","money_paid":0.01,"orders":[{"outer_sku":"G60004702225","goods_name":"\u65f6\u5c1a\u9542\u7a7a\u725b\u76ae\u5973\u978b \u65b0\u6b3e\u5c16\u5934\u9632\u6c34\u53f0\u539a\u5e95\u6df1\u53e3\u5355\u978b","goods_number":"1","goods_price":"498.00","payment_ft":498,"is_gift":0}]},"2016-07-22 17:58:02"]';
            $b = json_decode($a, true);
            $info   =   array(
                'sd_id'=>19,  //对应efast店铺id
                'province_name'=>'北京',  //省份
                'city_name'=>'北京市',  //城市
                'district_name'=>'西城区',  //地区
                'shipping_name'=>'yto',  //快递公司代码
                'pay_name'=>'weixinPay',  //支付方式代码
                'oid'=>'WH20160722181353645657',  //交易号
                'consignee'=>'不发货',  //收货人
                'address'=>'不发货',  //收货地址
                'zipcode'=>'',  //邮编
                'mobile'=>'1367679898',  //手机
                'tel'=>'1367679898',  //电话
                'user_name'=>'openid',  //买家账号
                'email'=>'',  //email
                'postscript'=>'',  //买家留言
                'to_buyer'=>'',  //商家备注
                'add_time'=>'2012-07-06 10:16:41',  //创建时间
                'pay_time'=>'2012-07-06 12:16:41',  //支付时间
                'goods_count'=>2,  //商品总数量
                'goods_amount'=>120,  //商品金额
                'total_amount'=>125,  //总金额
                'shipping_fee'=>5,  //快递费
                'order_amount'=>125,  //应付款
                'money_paid'=>125,  //已付款
//                'shipping_time'=>'2012-07-16 10:16:41',// 计划发货时间
//                'shipping_days'=>10,// 承诺发货天数
//                'is_yushou'=>0,// 是否预售
//                'inv_status'=>1,  //是否需要发票可不传
//                'inv_payee'=>'个人',  //发票抬头，依赖于inv_status
//                'inv_content'=>'电器',  //发票内容，依赖于inv_status
//                'weigh'=>250,  //订单重量（克）
                'orders'=>array(  
              //商品明细 outer_sku：匹配条码 goods_name：商品名称 goods_number：数量 goods_price：价格payment_ft: 商品分摊价 is_gift：是否礼品
                              array('outer_sku'=>'G60009100220','goods_name'=>'zzm','goods_number'=>1,'goods_price'=>60, 'payment_ft'=>60,'is_gift'=>1),
                              array('outer_sku'=>'G60004700245','goods_name'=>'zzm','goods_number'=>2,'goods_price'=>60,'payment_ft'=>120,'is_gift'=>0),
                              ),
              );
//              dump($info);
//            dump($b[0]);die;
//            $EfastHelper->setParameter('info', json_encode($b[0]));
            $EfastHelper->setParameter('info', json_encode($info));
            $BacData			= $EfastHelper->GetData();
            $BacData			= json_decode($BacData,true);
            dump($BacData);die;
        }
        
        
        /*
         * ERP接口查看订单详情
         */
        public function orderDetail(){
            $oid    =   I('get.oid');
            Vendor('Efast.EfastHelper');
            $EfastHelper	= new EfastHelper();
            $EfastHelper->setParameter('app_act','efast.trade.detail.get');
            $EfastHelper->setParameter('oid',$oid);
            $EfastHelper->setParameter('type','0');
            $EfastHelper->setParameter('feilds','order_sn,deal_code,order_status,shipping_status,'
                    . 'pay_status,process_status,is_send,is_locked,is_separate,consignee,address,'
                    . 'zipcode,tel,mobile,shipping_name,pay_name,invoice_no,order_amount ,money_paid,'
                    . 'user_id ,orders.goods_sn,orders.goods_name,orders.goods_number,orders.goods_price,'
                    . 'orders.goods_barcode,orders.payment_ft,pay_time,to_buyer,postscript,order_amount,'
                    . 'user_nick,money_paid,shipping_fee,cz_shipping_fee');
            
            $BacData			= $EfastHelper->GetData();
            $BacData			= json_decode($BacData,true);
            dump($BacData);die;
        }
        
        
        /*
         * 自动推送订单到ERP
         */
        public function orderPayedList(){
            Vendor('Efast.EfastHelper');
                
            $where  =   array();
            $where['pay_status']        =   1;
            $where['order_status']      =   0;
            $where['shipping_status']   =   0;
            $where['erp_order_sn']      =   '';
            
            $shippedList    =   D('OrderInfo')->where($where)->relation(true)->limit(20)->order('id asc')->select();
//            dump($shippedList);die;
            foreach ($shippedList as $k1 => $v1) {
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
                    'oid'                   =>  NOW_TIME.'AAAGG',  //交易号
                    // 'consignee'             =>$order_info['consignee'],  //收货人
                    'consignee'             =>  '不发货',  //收货人
                    'address'               =>  $v1['address'],  //收货地址
                    'zipcode'               =>  '',  //邮编
                    'mobile'                =>  $v1['tel'],  //手机
                    'tel'                   =>  $v1['tel'],  //电话
                    'user_name'             =>  $v1['openid'],  //买家账号
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
                $BacData			= $EfastHelper->GetData();
                $BacData			= json_decode($BacData,true);
                if(!empty($BacData['resp_data']['oder_sn'])){
                    $order_info_data    =   array();
                    $order_info_data['erp_order_sn']    =   $BacData['resp_data']['oder_sn'];   //ERP返回单号
                    //更新订单状态
                    $order_info_update = M('order_info')-> where(array('id'=>$v1['id']))->setField($order_info_data);
                }
            }
                
        }


        /*
         * 
         * 自动获取ERP发货订单脚本
         */
        public function orderShippedList(){
            Vendor('Efast.EfastHelper');
            $where  =   array();
            $where['pay_status']        =   1;
            $where['order_status']      =   0;
            $where['shipping_status']   =   0;
            $where['erp_order_sn']      =   array('neq','');
            $shippedList    =   M('order_info')->where($where)->limit(20)->order('id asc')->select();
            foreach ($shippedList as $key => $value) {
                $EfastHelper	= new EfastHelper();
                $EfastHelper->setParameter('app_act','efast.trade.detail.get');
                $EfastHelper->setParameter('oid',$value['order_sn']);
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
                    $order_info_update  =   M('order_info')->where(array('id'=>$value['id']))->save($order_info_data);
                    if($order_info_update){
                        //订单操作日志
                        $order_log_data = array();
                        $order_log_data['type']         =   1;    //平台
                        $order_log_data['uid']          =   1;
                        $order_log_data['order_id']     =   $value['id'];
                        $order_log_data['msg']          =   'ERP发货成功';
                        $order_log_data['dateline']     =   $order_info_data['shipping_time'];
                        M('order_log')->add($order_log_data);
                    }
                }
            }
        }
        
        //订单作废同步
        public function syncOrderInvalid(){
                $oid    =   I('get.oid');
                Vendor('Efast.EfastHelper');
                $EfastHelper	= new EfastHelper();
                $EfastHelper->setParameter('app_act','efast.trade.invalid');
                $EfastHelper->setParameter('oid', $oid);
                $BacData			= $EfastHelper->GetData();
                $BacData			= json_decode($BacData,true);
                dump($BacData);die;
        }
        
}
<?php
namespace System\Controller;
use Think\Controller;

class TmpController extends Controller {

    /**
     * [updateGiftOrderBarcode 修改礼包订单bar_code]
     * @author DDK <[695438516@qq.com]>
     */
    public function updateGiftOrderBarcode() {
        $gift_order     = M('gift_order');

        $where = array();
        $where['is_pay'] = 1;
        $where['add_time'] = array('between',array(1454774400,1455500100));

        $orderList = $gift_order->where($where)->select();
        $orderSnArr = array();
        foreach ($orderList as $key => $value) {
            $orderSnArr[] = $value['order_sn'];
        }

        $upWhere = array('order_sn'=>array('in',$orderSnArr));
        $upData = array(
            'bar_code'  =>  '1700310563',
            'sku'       =>  '1700310563'
        );

        $giftOrderDetail = M('gift_order_detail')->where($upWhere)->data($upData)->save();
        
        var_dump($giftOrderDetail);die;
        echo $gift_order->getLastSql();
        // echo $orderList;
    }
    
    public function Online(){
        $isOnline   =   1;//0为未上线，1为已上线
        return  $isOnline;
    }
    public function test(){
        $isOnline   =   $this->Online();
        dump($isOnline);die;
    }

    public function setUserBind(){
        $url = 'http://' . WEB_DOMAIN . U('Api/Game/setUserBind', '', true, false);
        $param = array(
            'hash'  => '5aee5c0f5bb8aecbe781e5f4ec3f827e',
            'time'  => NOW_TIME,
            'uid'   => 119113,
            'invitation_code'  => '123456',
        );
        // echo $url;die;
        $url_data = CurlHttp($url, $param, 'POST');
        die($url_data);
    }
    public function getGameUser(){
        $url = 'http://' . WEB_DOMAIN . U('Api/Game/getUserBindInfo', '', true, false);
        $param = array(
            'time'  => NOW_TIME,
            'hash'  => '4b50512c9c732419a0d992ab9cd202bc',
            'uid'   => 119113
        );
        // echo $url;die;
        $url_data = CurlHttp($url, $param, 'POST');
        die($url_data);
    }


    //分享活动
    public function shareActivity(){
        $url = 'http://' . WEB_DOMAIN . U('Api/Game/shareActivity', '', true, false);
        $param = array(
            'time'  => NOW_TIME,
            'hash'  => '4b50512c9c732419a0d992ab9cd202bc',
            'uid'   => 119112
        );
        // echo $url;die;
        $url_data = CurlHttp($url, $param, 'POST');
        die($url_data);
    }
    //支付测试
    public function recharge_Add(){
        $url = 'http://' . WEB_DOMAIN . U('Api/Pay/recharge_Add', '', true, false);
        $param = array(
            'time'  => NOW_TIME,
            'hash'  => 'b97319f083246f02aa99addd2de5036f',
            'uid'   => 119113,
            'goodsid'   => 1,
            'paytype'   => 2
        );
        // echo $url;die;
        $url_data = CurlHttp($url, $param, 'POST');
        die($url_data);
    }

    //分享活动
    public function getPaomatiao(){
        $url = 'http://' . WEB_DOMAIN . U('Api/Game/getPaomatiao', '', true, false);
        $param = array(
            'time'  => NOW_TIME,
            'hash'  => '4b50512c9c732419a0d992ab9cd202bc',
            'uid'   =>  1
        );
        // echo $url;die;
        $url_data = CurlHttp($url, $param, 'POST');
        die($url_data);
    }
}

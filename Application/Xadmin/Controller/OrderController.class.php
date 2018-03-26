<?php

namespace Xadmin\Controller;

use Think\Controller;

// 订单信息
class OrderController extends BaseController {

    /**
     * [orderList 订单列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function orderList() {
        $startTime = I('get.start_time');
        $endTime = I('get.end_time');
        $pay_status = I('get.pay_status');
        $orderSn        = I('get.order_sn');

        $where = array();
        $whereStr = '';

        if( !empty($orderSn) ) {
            $orderSn   = addslashes($orderSn);
            $orderSn   = urldecode($orderSn);
            $whereStr .= "AND o.order_sn LIKE \"%{$orderSn}%\" ";
            $where['order_sn'] = array('LIKE', "%{$orderSn}%");
        }
        if ($pay_status !== '') {
            $pay_status = addslashes($pay_status);
            $pay_status = urldecode($pay_status);
            $where['pay_status'] = "{$pay_status}";
            $whereStr .= "AND o.pay_status = {$pay_status} ";
        }

        if (!empty($startTime) && !empty($endTime)) {
            $startTime = addslashes($startTime);
            $startTime = urldecode($startTime);
            $startTime = str_replace('+', ' ', $startTime);
            $startTime = strtotime($startTime);

            $endTime = addslashes($endTime);
            $endTime = urldecode($endTime);
            $endTime = str_replace('+', ' ', $endTime);
            $endTime = strtotime($endTime);
            $whereStr .= "AND o.add_time BETWEEN {$startTime} AND {$endTime} ";
            $where['add_time'] = array('BETWEEN', array($startTime, $endTime));
        }


        $getData = array();
        $get = I('get.');
        foreach ($get as $key => $value) {
            if (!empty($key)) {
                $getData[$key] = urldecode($value);
            }
        }

        if (!empty($getData['add_time'])) {
            $getData['add_time'] = search_time_format($getData['add_time']);
        }

        if (!empty($getData['end_time'])) {
            $getData['end_time'] = search_time_format($getData['end_time']);
        }
        $count = M('order')->where($where)->count();
        $page = new \Think\Page($count, 10, $getData);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show = $page->show();

        $dbPrefix = C('DB_PREFIX');
        $sql = "SELECT o.id, o.agent_id,o.order_sn, o.buyer, o.telephone,o.total_amount,o.paid_money, o.pay_status, o.pay_time, o.`status`,o.add_time,od.goods_name " .
                "FROM {$dbPrefix}order AS o " .
                "LEFT JOIN {$dbPrefix}agent AS a ON o.agent_id = a.id " .
                "LEFT JOIN {$dbPrefix}order_detail AS od ON od.order_id = o.id " .
                "WHERE 1 {$whereStr} " .
                "ORDER BY o.pay_time DESC " .
                "LIMIT {$page->firstRow}, {$page->listRows}";
//        dump($sql);die;
        $orderList = M('order')->query($sql);
//        dump($orderList);die;
        $this->assign('orderList', $orderList);
//        dump($show);die;
        $this->assign('show', $show);

        $this->display('orderList');
    }

    /**
     * [orderDetail 订单详情]
     * @author TF <[2281551151@qq.com]>
     */
    public function orderDetail() {
        $id = I('get.id', '', 'int');
        if (empty($id)) {
            $this->error('参数丢失！');
        }

        $order = D('order');
        $orderInfo = $order->where(array('id' => $id))->relation(true)->find();
        if (empty($orderInfo)) {
            $this->error('找不到该订单！');
        }
//        dump($orderInfo);die;
        $this->assign('orderInfo', $orderInfo);
        $this->display('orderDetail');
    }

    /**
     * [orderGoodsList 订单商品列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function orderGoodsList() {
        if (IS_POST) {
            $orderSn = I('post.order_sn');
            if (empty($orderSn)) {
                $this->error('参数丢失！');
            }

            $order_info = M('order')->where(array('order_sn' => $orderSn))->getField('order_sn');
            if (empty($order_info)) {
                $this->error('找不到订单信息！');
            }


            $goodsList = M('order_detail')->where(array('order_sn' => $orderSn))->select();
            echo json_encode($goodsList);
        } else {
            $this->error('非法访问！');
        }
    }

}

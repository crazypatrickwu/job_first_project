<?php
namespace Xadmin\Controller;
use Think\Controller;
// 分账控制器
class AllocationController extends BaseController {
    /**
     * [finishAllocationLog 已完成的分账记录]
     * @author TF <2281551151@qq.com>
     */
    public function finishAllocationLog() {
        set_time_limit(0);
        $giftOrderModel = M('gift_order');
        $giftOrderDetailModel = M('gift_order_detail');
        $orderModel     = M('order');
        $orderDetailModel     = M('order_detail');
        $giftGoodsModel = M('gift_goods');
        $goodsModel = M('goods');
        
        $order_sn         = I('get.order_sn');
        $goods_name            = I('get.goods_name');
        $bar_code       = I('get.bar_code');
        $type        = I('get.type');
        $level          = I('get.level');
        $start_add_time    = I('get.start_add_time');
        $end_add_time      = I('get.end_add_time');
        $is_export              = I('get.is_export');
        $whereStr = '';
        if( !empty($order_sn) ) {
            $whereStr .= "AND aal.order_sn = \"{$order_sn}\" ";
        }
        if( !empty($goods_name) ) { //按照商品名称搜索记录
            $orderDetailTmp = $orderDetailModel->where(array('goods_name'=>array('like',array("%{$goods_name}%"))))->getField('goods_id',true);
            $orderDetailTmp = array_unique($orderDetailTmp);
            $orderDetailTmp = implode(",", $orderDetailTmp);
            if(!empty($orderDetailTmp)){
                $whereStr .= "AND aal.goods_id IN ({$orderDetailTmp}) ";
            }
            $giftOrderDetailTmp = $giftOrderDetailModel->where(array('goods_name'=>array('like',array("%{$goods_name}%"))))->getField('goods_id',true);
            $giftOrderDetailTmp = array_unique($giftOrderDetailTmp);
            $giftOrderDetailTmp = implode(",", $giftOrderDetailTmp);
            if(!empty($giftOrderDetailTmp)){
                $whereStr .= "AND aal.goods_id IN ({$giftOrderDetailTmp}) ";
            }
        }
        if( !empty($bar_code) ) {
            $orderDetailTmp = $orderDetailModel->where(array('bar_code'=>$bar_code))->getField('goods_id',true);
            $orderDetailTmp = array_unique($orderDetailTmp);
            $orderDetailTmp = implode(",", $orderDetailTmp);
            if(!empty($orderDetailTmp)){
                $whereStr .= "AND aal.goods_id IN ({$orderDetailTmp}) ";
            }
            $giftOrderDetailTmp = $giftOrderDetailModel->where(array('bar_code'=>$bar_code))->getField('goods_id',true);
            $giftOrderDetailTmp = array_unique($giftOrderDetailTmp);
            $giftOrderDetailTmp = implode(",", $giftOrderDetailTmp);
            if(!empty($giftOrderDetailTmp)){
                $whereStr .= "AND aal.goods_id IN ({$giftOrderDetailTmp}) ";
            }
        }
        if( isset($type) && $type !== '') {
            $whereStr .= "AND aal.type = \"{$type}\" ";
        }
        if( in_array($level, array('1', '2','3')) ) {
            $whereStr .= "AND a.level = {$level} ";
        }
        if( !empty($start_add_time) && !empty($end_add_time) ) {
            $start_add_time = addslashes($start_add_time);
            $start_add_time = urldecode($start_add_time);
            $start_add_time = str_replace('+', ' ', $start_add_time);
            $start_add_time = strtotime($start_add_time);

            $end_add_time   = addslashes($end_add_time);
            $end_add_time   = urldecode($end_add_time);
            $end_add_time   = str_replace('+', ' ', $end_add_time);
            $end_add_time   = strtotime($end_add_time);
            $whereStr  .= "AND aal.add_time BETWEEN {$start_add_time} AND {$end_add_time} ";
        }
        $mysql    = M();
        $dbPrefix = C('DB_PREFIX');
        $sql      = "SELECT __REPLACE__ " . 
                    "FROM {$dbPrefix}agent_allocation_log AS aal " . 
                    "LEFT JOIN {$dbPrefix}agent AS a ON aal.agent_id = a.id " . 
                    "LEFT JOIN {$dbPrefix}agent AS ap ON ap.id = a.pid " . 
                    "LEFT JOIN {$dbPrefix}agent AS ar ON ar.id = a.referee_id " . 
                    "WHERE aal.is_pass = 1 __LAST__";
        $sql1 = str_replace(
                    array('__REPLACE__', '__LAST__'), 
                    array('COUNT(*) AS count', $whereStr), 
                    $sql
                );
        $field = "aal.id, aal.order_sn, aal.goods_id, aal.money, a.id AS aid, a.truename, a.headimgurl, "
                . "a.level, ap.id AS pid, ap.truename AS ptruename, ap.headimgurl AS pheadimgurl, "
                . "ap.level AS plevel, ar.id AS rid, ar.truename AS rtruename, ar.headimgurl AS rheadimgurl,"
                . " ar.level AS rlevel, aal.add_time,aal.pass_time";

        if( !empty($is_export) ) {  //导出数据
            $export_sql = str_replace(
                    array('__REPLACE__', '__LAST__'), 
                    array($field, $whereStr . "ORDER BY aal.add_time DESC"), 
                    $sql
                );
            $export_allocation_log_List = $mysql->query($export_sql);
            $export_count = count($export_allocation_log_List);

            if($export_count > 5000){
                echo '<script>alert("由于当前导出数据量过多，已超时！请选择对应时间段来导出您需要的数据！");history.go(-1);</script>';
                exit();
            }
            $filename = '分佣列表'.date('Ymdhis');
            $data = array(
                array('ID', '订单编号', '订单状态', '订单类型', '商品名称', '条形码', '单价','数量','单种商品总分佣','订单总金额','订单总分佣','订单完成时间'),
            );
            $statusArr = array(
                '0' =>  '未发货',
                '1' =>  '已发货',
                '2' =>  '人为完成订单',
                '3' =>  '系统完成订单',
                '4' =>  '人为取消订单',
                '5' =>  '系统取消订单'
            );
            
            foreach ($export_allocation_log_List as $key => &$value) {
                if ( checkOrderType($value['order_sn']) == '1' ) {
                    $value['o_type'] = '礼包订单';
                    $giftOrderInfo = $giftOrderModel->where(array('order_sn'=>$value['order_sn']))->field('id,total,status,expense as o_expense')->find();
                    $value['total'] = $giftOrderInfo['total'];
                    $value['status'] = $giftOrderInfo['status'];
                    $value['o_expense'] = $giftOrderInfo['o_expense'];
                    $giftOrderDetailRow = $giftOrderDetailModel->where(array('order_sn'=>$value['order_sn'],'goods_id'=>$value['goods_id']))->field('bar_code,goods_price,goods_number,expense as od_expense,goods_name')->find();
                    $value['bar_code'] = $giftOrderDetailRow['bar_code'];
                    $value['goods_price'] = $giftOrderDetailRow['goods_price'];
                    $value['goods_number'] = $giftOrderDetailRow['goods_number'];
                    $value['od_expense'] = $giftOrderDetailRow['od_expense'];
                    $value['goods_name'] = $giftOrderDetailRow['goods_name'];
                } else {
                    $value['o_type'] = '普通订单';
                    $orderInfo = $orderModel->where(array('order_sn'=>$value['order_sn']))->field('id,total,status,expense as o_expense')->find();
                    $value['total'] = $orderInfo['total'];
                    $value['status'] = $orderInfo['status'];
                    $value['o_expense'] = $orderInfo['o_expense'];
                    $orderDetailRow = $orderDetailModel->where(array('order_sn'=>$value['order_sn'],'goods_id'=>$value['goods_id']))->field('bar_code,goods_price,goods_number,expense as od_expense,goods_name')->find();
    //                var_dump($orderDetailRow);die;
                    $value['bar_code'] = $orderDetailRow['bar_code'];
                    $value['goods_price'] = $orderDetailRow['goods_price'];
                    $value['goods_number'] = $orderDetailRow['goods_number'];
                    $value['od_expense'] = $orderDetailRow['od_expense'];
                    $value['goods_name'] = $orderDetailRow['goods_name'];
                }
            }
            foreach ($export_allocation_log_List as $key => $value) {
                $arr = [];
                $arr[] = $value['id'];  //ID
                $arr[] = $value['order_sn'];    //订单编号
                $arr[] = $statusArr[$value['status']];  //订单状态
                $arr[] = $value['o_type'];              //订单类型
                $arr[] = $value['goods_name'];          //商品名称
                $arr[] = $value['bar_code'];            //条形码
                $arr[] = $value['goods_price'];         //单价
                $arr[] = $value['goods_number'];        //数量
                $arr[] = $value['od_expense'];          //单种商品总分佣
                $arr[] = $value['total'];               //订单总金额
                $arr[] = $value['o_expense'];           //订单总分佣
                $arr[] = !empty($value['add_time']) ? time_format($value['add_time']) : '--';   //订单完成时间
                array_push($data, $arr);
            }
            exportExcel($filename, $data);
            exit();
        }

$getData = array();
        $get     = I('get.');
        foreach ($get as $key => $value) {
            if ( !empty($key) ) {
                $getData[$key] = urldecode($value);
            }
        }

        if ( !empty($getData['start_add_time']) ) {
          $getData['start_add_time'] = search_time_format($getData['start_add_time']);
        }

        if ( !empty($getData['end_add_time']) ) {
          $getData['end_add_time'] = search_time_format($getData['end_add_time']);
        }


        $count    = $mysql->query($sql1);
        $page     = new \Think\Page($count[0]['count'], 25, $getData);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show     = $page->show();

        
        $sql2 = str_replace(
                    array('__REPLACE__', '__LAST__'), 
                    array($field, $whereStr . "ORDER BY aal.add_time DESC LIMIT {$page->firstRow}, {$page->listRows}"), 
                    $sql
                );

//        $allocationList = $mysql->query($sql2);
        $allocationList = $mysql->query($sql2);
        foreach ($allocationList as $key => &$value) {
            if ( checkOrderType($value['order_sn']) == '1' ) {
                $value['o_type'] = '礼包订单';
                $giftOrderInfo = $giftOrderModel->where(array('order_sn'=>$value['order_sn']))->field('id as order_id,total,status,expense as o_expense')->find();
                $value['order_id'] = $orderInfo['order_id'];
                $value['total'] = $giftOrderInfo['total'];
                $value['status'] = $giftOrderInfo['status'];
                $value['o_expense'] = $giftOrderInfo['o_expense'];
                $giftOrderDetailRow = $giftOrderDetailModel->where(array('order_sn'=>$value['order_sn'],'goods_id'=>$value['goods_id']))->field('bar_code,goods_price,goods_number,expense as od_expense,goods_name')->find();
                $value['bar_code'] = $giftOrderDetailRow['bar_code'];
                $value['goods_price'] = $giftOrderDetailRow['goods_price'];
                $value['goods_number'] = $giftOrderDetailRow['goods_number'];
                $value['od_expense'] = $giftOrderDetailRow['od_expense'];
                $value['goods_name'] = $giftOrderDetailRow['goods_name'];
            } else {
                $value['o_type'] = '普通订单';
                $orderInfo = $orderModel->where(array('order_sn'=>$value['order_sn']))->field('id as order_id,total,status,expense as o_expense')->find();
                $value['order_id'] = $orderInfo['order_id'];
                $value['total'] = $orderInfo['total'];
                $value['status'] = $orderInfo['status'];
                $value['o_expense'] = $orderInfo['o_expense'];
                $orderDetailRow = $orderDetailModel->where(array('order_sn'=>$value['order_sn'],'goods_id'=>$value['goods_id']))->field('bar_code,goods_price,goods_number,expense as od_expense,goods_name')->find();
//                var_dump($orderDetailRow);die;
                $value['bar_code'] = $orderDetailRow['bar_code'];
                $value['goods_price'] = $orderDetailRow['goods_price'];
                $value['goods_number'] = $orderDetailRow['goods_number'];
                $value['od_expense'] = $orderDetailRow['od_expense'];
                $value['goods_name'] = $orderDetailRow['goods_name'];
            }
        }
//        var_dump($allocationList);die;
        $this->assign('allocationList', $allocationList);
        $this->assign('show', $show);
        $this->display('finishAllocationLog');
    }

    /**
     * [canceledAllocationLog 已取消的分账记录]
     * @author TF <2281551151@qq.com>
     */
    public function canceledAllocationLog() {
        $mysql    = M();
        $dbPrefix = C('DB_PREFIX');
        $sql      = "SELECT __REPLACE__ " . 
                    "FROM {$dbPrefix}agent_allocation_log AS aal " . 
                    "LEFT JOIN {$dbPrefix}agent AS a ON aal.agent_id = a.id " . 
                    "LEFT JOIN {$dbPrefix}agent AS ap ON ap.id = a.pid " . 
                    "LEFT JOIN {$dbPrefix}agent AS ar ON ar.id = a.referee_id " . 
                    "LEFT JOIN {$dbPrefix}order AS o ON aal.order_sn = o.order_sn " . 
                    "WHERE aal.is_pass = -1 __LAST__";
        $sql1 = str_replace(
                    array('__REPLACE__', '__LAST__'), 
                    array('COUNT(*) AS count', $whereStr), 
                    $sql
                );
        $count    = $mysql->query($sql1);
        $page     = new \Think\Page($count[0]['count'], 25, $getData);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show     = $page->show();

        $field = "aal.id, aal.order_sn, aal.goods_id, aal.money, a.id AS aid, a.truename, a.headimgurl, a.level, ap.id AS pid, ap.truename AS ptruename, ap.headimgurl AS pheadimgurl, ap.level AS plevel, ar.id AS rid, ar.truename AS rtruename, ar.headimgurl AS rheadimgurl, ar.level AS rlevel, aal.add_time";

        $sql2 = str_replace(
                    array('__REPLACE__', '__LAST__'), 
                    array($field, $whereStr . "ORDER BY aal.add_time DESC LIMIT {$page->firstRow}, {$page->listRows}"), 
                    $sql
                );

        $allocationList = $mysql->query($sql2);

        $giftOrder = M('gift_order');
        $order     = M('order');
        $allocationList = $mysql->query($sql2);
        foreach ($allocationList as $key => &$value) {
            if ( checkOrderType($value['order_sn']) == '1' ) {
                $value['total'] = $giftOrder->where(array('order_sn'=>$value['order_sn']))->getField('total');
            } else {
                $value['total'] = $order->where(array('order_sn'=>$value['order_sn']))->getField('total');
            }
        }
        $this->assign('allocationList', $allocationList);
        $this->assign('show', $show);
        $this->display('canceledAllocationLog');
    }

    /**
     * [notSetAllocationLog 未设置的分账记录]
     * @author TF <2281551151@qq.com>
     */
    public function notSetAllocationLog() {
        $mysql    = M();
        $dbPrefix = C('DB_PREFIX');
        $sql      = "SELECT __REPLACE__ " . 
                    "FROM {$dbPrefix}agent_allocation_log AS aal " . 
                    "LEFT JOIN {$dbPrefix}agent AS a ON aal.agent_id = a.id " . 
                    "LEFT JOIN {$dbPrefix}agent AS ap ON ap.id = a.pid " . 
                    "LEFT JOIN {$dbPrefix}agent AS ar ON ar.id = a.referee_id " . 
                    "LEFT JOIN {$dbPrefix}order AS o ON aal.order_sn = o.order_sn " . 
                    "WHERE aal.is_pass = 0 __LAST__";
        $sql1 = str_replace(
                    array('__REPLACE__', '__LAST__'), 
                    array('COUNT(*) AS count', $whereStr), 
                    $sql
                );
        $count    = $mysql->query($sql1);
        $page     = new \Think\Page($count[0]['count'], 25, $getData);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show     = $page->show();

        $field = "aal.id, aal.order_sn, aal.goods_id, aal.money, a.id AS aid, a.truename, a.headimgurl, a.level, ap.id AS pid, ap.truename AS ptruename, ap.headimgurl AS pheadimgurl, ap.level AS plevel, ar.id AS rid, ar.truename AS rtruename, ar.headimgurl AS rheadimgurl, ar.level AS rlevel, aal.add_time";

        $sql2 = str_replace(
                    array('__REPLACE__', '__LAST__'), 
                    array($field, $whereStr . "ORDER BY aal.add_time DESC LIMIT {$page->firstRow}, {$page->listRows}"), 
                    $sql
                );

        $allocationList = $mysql->query($sql2);

        $giftOrder = M('gift_order');
        $order     = M('order');
        $allocationList = $mysql->query($sql2);
        foreach ($allocationList as $key => &$value) {
            if ( checkOrderType($value['order_sn']) == '1' ) {
                $value['total'] = $giftOrder->where(array('order_sn'=>$value['order_sn']))->getField('total');
            } else {
                $value['total'] = $order->where(array('order_sn'=>$value['order_sn']))->getField('total');
            }
        }

        $this->assign('allocationList', $allocationList);
        $this->assign('show', $show);
        $this->display('notSetAllocationLog');
    }

    /**
     * [allAllocationLog 所有的分账记录]
     * @author TF <2281551151@qq.com>
     */
    public function allAllocationLog() {
        $orderSn    = I("get.order_sn");
        $pid        = I("get.pid");
        $isPass     = I("get.is_pass");
        $startTime  = I('get.start_time');
        $endTime    = I('get.end_time');

        $where    = array();
        $whereStr = '';
        if( $isPass != "" ) {
            $isPass = addslashes($isPass);
            $isPass = urldecode($isPass);
            $whereStr .= "AND aal.is_pass = {$isPass} ";
        }

        if( !empty($pid) ) {
            $pid = addslashes($pid);
            $pid = urldecode($pid);
            $whereStr .= "AND a.pid = {$pid} ";
        }

        if( !empty($orderSn) ) {
            $orderSn = addslashes($orderSn);
            $orderSn = urldecode($orderSn);
            $whereStr .= "AND aal.order_sn LIKE \"%{$orderSn}%\" ";
        }

        if( !empty($startTime) && !empty($endTime) ) {
            $startTime = addslashes($startTime);
            $startTime = urldecode($startTime);
            $startTime = str_replace('+', ' ', $startTime);
            $startTime = strtotime($startTime);

            $endTime   = addslashes($endTime);
            $endTime   = urldecode($endTime);
            $endTime   = str_replace('+', ' ', $endTime);
            $endTime   = strtotime($endTime);
            $whereStr .= "AND aal.add_time BETWEEN {$startTime} AND {$endTime} ";
        }

        $mysql    = M();
        $dbPrefix = C('DB_PREFIX');
        $sql      = "SELECT __REPLACE__ " . 
                    "FROM {$dbPrefix}agent_allocation_log AS aal " . 
                    "LEFT JOIN {$dbPrefix}agent AS a ON aal.agent_id = a.id " . 
                    "LEFT JOIN {$dbPrefix}agent AS ap ON ap.id = a.pid " . 
                    "LEFT JOIN {$dbPrefix}agent AS ar ON ar.id = a.referee_id " . 
                    "WHERE 1 __LAST__";
        $sql1 = str_replace(
                    array('__REPLACE__', '__LAST__'), 
                    array('COUNT(*) AS count', $whereStr), 
                    $sql
                );
        $count    = $mysql->query($sql1);
        $page     = new \Think\Page($count[0]['count'], 25, $getData);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show     = $page->show();

        $field = "aal.id, aal.order_sn, aal.goods_id, aal.money, aal.is_pass, a.id AS aid, a.truename, a.headimgurl, a.level, ap.id AS pid, ap.truename AS ptruename, ap.headimgurl AS pheadimgurl, ap.level AS plevel, ar.id AS rid, ar.truename AS rtruename, ar.headimgurl AS rheadimgurl, ar.level AS rlevel, aal.add_time";

        $sql2 = str_replace(
                    array('__REPLACE__', '__LAST__'), 
                    array($field, $whereStr . "ORDER BY aal.add_time DESC LIMIT {$page->firstRow}, {$page->listRows}"), 
                    $sql
                );

        $giftOrder = M('gift_order');
        $order     = M('order');
        $allocationList = $mysql->query($sql2);
        foreach ($allocationList as $key => &$value) {
            if ( checkOrderType($value['order_sn']) == '1' ) {
                $value['total'] = $giftOrder->where(array('order_sn'=>$value['order_sn']))->getField('total');
            } else {
                $value['total'] = $order->where(array('order_sn'=>$value['order_sn']))->getField('total');
            }
        }

        $this->assign('allocationList', $allocationList);
        $this->assign('show', $show);
        $this->display('allAllocationLog');
    }

    /**
     * [passAllocation 使之通过分账记录]
     * @author TF <2281551151@qq.com>
     */
    public function passAllocation() {
        $id = I('get.id', '', 'int');
        if ( empty($id) ) {
            $this->error('参数丢失！');
        }

        $mysql = M();
        $mysql->startTrans();


        // 寻找分账记录
        $agentAllocationLogInfo = $agentAllocationLog->where(array('id'=>$id))->find();
        if ( empty($agentAllocationLogInfo) ) {
            $mysql->rollback();
            $this->error('不存在分佣信息！');
        }

        // 找到本分账人的余额
        $agent        = M('agent');
        $currentMoney = $agent->where(array('id'=>$agentAllocationLogInfo['agent_id']))->getField('money');


        // 修改记录为通过
        $agentAllocationLog       = M('agent_allocation_log');
        if ( $agentAllocationLog->where(array("id"=>$id, 'is_pass'=>0))->count() <= 0 ) {
            $mysql->rollback();
            $this->error('本条记录已经被设置！');
        }
        $agentAllocationLogResult = $agentAllocationLog->where(array('id'=>$id))->data(array('is_pass'=>1, 'pass_time'=>time()))->save();
        if ( $agentAllocationLogResult < 0 ) {
            $mysql->rollback();
            $this->error('分账状态没变！');
            exit();
        }

        $dbPrefix = C('DB_PREFIX');
        if ( $agentAllocationLogInfo['money'] > 0 ) {
            // 更新本分账人的历史收益、余额、未激活金额
            $sql      = "UPDATE {$dbPrefix}agent " . 
                        "SET history_money_total = history_money_total + {$agentAllocationLogInfo['money']}, money = money + {$agentAllocationLogInfo['money']}, not_available_money = not_available_money - {$agentAllocationLogInfo['money']}  " . 
                        "WHERE id = {$agentAllocationLogInfo['agent_id']}";
            $updateMoneyResult = $mysql->execute($sql);
            if ( $updateMoneyResult <= 0 ) {
                $mysql->rollback();
                $this->error('代理佣金不能增加！');
            }
        }

        // 判断购物订单的类型
        if ( checkOrderType($agentAllocationLogInfo['order_sn']) == '1' ) {
            // 礼包订单
            $sql = "SELECT p.id AS pid, p.truename AS ptruename, a.truename, a.id, god.goods_name, god.bar_code, god.goods_number " . 
                   "FROM {$dbPrefix}gift_order_detail AS god " . 
                   "LEFT JOIN {$dbPrefix}gift_order AS o ON o.order_sn = god.order_sn " . 
                   "LEFT JOIN {$dbPrefix}agent AS a ON a.id = o.agent_id " . 
                   "LEFT JOIN {$dbPrefix}agent AS p ON p.id = a.pid " . 
                   "WHERE god.order_sn = \"{$agentAllocationLogInfo['order_sn']}\" AND god.goods_id = \"{$agentAllocationLogInfo['goods_id']}\" ";

            $agentInfo    = $mysql->query($sql);
            $agentInfoOne = $agentInfo[0];
            $explain      = "[{$agentInfoOne['truename']}({$agentInfoOne['id']})]在[{$agentInfoOne['ptruename']}({$agentInfoOne['pid']})]的店购买了[{$agentInfoOne['goods_name']}({$agentInfoOne['bar_code']})] X {$agentInfoOne['goods_number']} 获得佣金";
            $financeType = '1';
        } else {
            // 普通订单
            $sql = "SELECT s.id AS sid, s.truename AS struename, u.id, u.nickname, god.goods_name, god.bar_code, god.goods_number " . 
                   "FROM {$dbPrefix}order_detail AS god " . 
                   "LEFT JOIN {$dbPrefix}order AS o ON o.order_sn = god.order_sn " . 
                   "LEFT JOIN {$dbPrefix}user AS u ON u.id  = o.user_id " . 
                   "LEFT JOIN {$dbPrefix}agent AS s ON s.id = o.agent_id " . 
                   "WHERE god.order_sn = \"{$agentAllocationLogInfo['order_sn']}\" AND god.goods_id = \"{$agentAllocationLogInfo['goods_id']}\" ";

            $agentInfo    = $mysql->query($sql);
            $agentInfoOne = $agentInfo[0];

            if ( $agentAllocationLogInfo['type'] == '1' ) {
                $yj = '平推佣金';
            } else {
                $yj = '佣金';
            }

            $explain      = "[{$agentInfoOne['nickname']}({$agentInfoOne['id']})]在[{$agentInfoOne['struename']}({$agentInfoOne['sid']})]的店购买了[{$agentInfoOne['goods_name']}({$agentInfoOne['bar_code']})] X {$agentInfoOne['goods_number']} 获得{$yj}";
            $financeType  = '0';
        }


        // 增加财务收入记录
        $agentFinanceData = array(
            'agent_id'       => $agentAllocationLogInfo['agent_id'],
            'order_sn'       => $agentAllocationLogInfo['order_sn'],
            'finance_type'   => $financeType,
            'before_changes' => $currentMoney,
            'change_money'   => $agentAllocationLogInfo['money'],
            'after_changes'  => $currentMoney + $agentAllocationLogInfo['money'],
            'explain'        => $explain,
            'add_time'       => time(),
        );
        $agentFinanceResult = M('agentFinance')->data($agentFinanceData)->add();
        if ( $agentFinanceResult <= 0 ) {
            $mysql->rollback();
            exit();
        }

        // 若所有分账都完成则修改订单的分账状态
        if ( checkOrderType($agentAllocationLogInfo['order_sn']) == '1' ) {
            $count           = $agentAllocationLog->where(array('order_sn' => $orderSn ))->count();
            $hadStatusCount  = $agentAllocationLog->where(array('order_sn' => $orderSn, array('is_pass'=>array('NEQ', 0))))->count();
            if ( $count == $hadStatusCount ) {
                $orderSaveResult = M('giftOrder')->where(array('order_sn'=>$orderSn))->data(array('is_allocation'=>1))->save();
            }
        } else {
            $count           = $agentAllocationLog->where(array('order_sn' => $orderSn ))->count();
            $hadStatusCount  = $agentAllocationLog->where(array('order_sn' => $orderSn, array('is_pass'=>array('NEQ', 0))))->count();
            if ( $count == $hadStatusCount ) {
                $orderSaveResult = M('order')->where(array('order_sn'=>$orderSn))->data(array('is_allocation'=>1))->save();
            }
        }

        $mysql->commit();
        $this->success('已设置该订单的该分账通过！');
    }

    /**
     * [unpassAllocation 使之不通过分账记录]
     * @author TF <2281551151@qq.com>
     */
    public function unpassAllocation() {
        $id = I('get.id', '', 'int');
        if ( empty($id) ) {
            $this->error('参数丢失！');
        }

        $mysql = M();
        $mysql->startTrans();

        // 修改记录为不通过
        $agentAllocationLog       = M('agent_allocation_log');
        $agentAllocationLogResult = $agentAllocationLog->where(array('id'=>$id))->data(array('is_pass'=>-1, 'pass_time'=>time()))->save();
        if ( $agentAllocationLogResult < 0 ) {
            $mysql->rollback();
            $this->error('分账状态没变！');
        }

        // 寻找分佣记录
        $agentAllocationLogInfo = $agentAllocationLog->where(array('id'=>$id))->find();
        if ( empty($agentAllocationLogInfo) ) {
            $mysql->rollback();
            $this->error('不存在分佣信息！');
        }

        $dbPrefix = C('DB_PREFIX');
        // 判断购物订单的类型
        if ( checkOrderType($agentAllocationLogInfo['order_sn']) == '1' ) {
            // 礼包订单
            $sql = "SELECT p.id AS pid, p.truename AS ptruename, a.truename, a.id, god.goods_name, god.bar_code, god.goods_number " . 
                   "FROM {$dbPrefix}gift_order_detail AS god " . 
                   "LEFT JOIN {$dbPrefix}gift_order AS o ON o.order_sn = god.order_sn " . 
                   "LEFT JOIN {$dbPrefix}agent AS a ON a.id = o.agent_id " . 
                   "LEFT JOIN {$dbPrefix}agent AS p ON p.id = a.pid " . 
                   "WHERE god.order_sn = \"{$agentAllocationLogInfo['order_sn']}\" AND god.goods_id = \"{$agentAllocationLogInfo['goods_id']}\" ";

            $agentInfo    = $mysql->query($sql);
            $agentInfoOne = $agentInfo[0];
            $explain      = "[{$agentInfoOne['truename']}({$agentInfoOne['id']})]在[{$agentInfoOne['ptruename']}({$agentInfoOne['pid']})]的店购买了[{$agentInfoOne['goods_name']}({$agentInfoOne['bar_code']})] X {$agentInfoOne['goods_number']} 获得佣金不通过";
            $financeType = '4';
        } else {
            // 普通订单
            $sql = "SELECT s.id AS sid, s.truename AS struename, u.id, u.nickname, god.goods_name, god.bar_code, god.goods_number " . 
                   "FROM {$dbPrefix}order_detail AS god " . 
                   "LEFT JOIN {$dbPrefix}order AS o ON o.order_sn = god.order_sn " . 
                   "LEFT JOIN {$dbPrefix}user AS u ON u.id  = o.user_id " . 
                   "LEFT JOIN {$dbPrefix}agent AS s ON s.id = o.agent_id " . 
                   "WHERE god.order_sn = \"{$agentAllocationLogInfo['order_sn']}\" AND god.goods_id = \"{$agentAllocationLogInfo['goods_id']}\" ";

            $agentInfo    = $mysql->query($sql);
            $agentInfoOne = $agentInfo[0];

            if ( $agentAllocationLogInfo['type'] == '1' ) {
                $yj = '平推佣金';
            } else {
                $yj = '佣金';
            }

            $explain      = "[{$agentInfoOne['nickname']}({$agentInfoOne['id']})]在[{$agentInfoOne['struename']}({$agentInfoOne['sid']})]的店购买了[{$agentInfoOne['goods_name']}({$agentInfoOne['bar_code']})] X {$agentInfoOne['goods_number']} 获得{$yj}不通过";
            $financeType  = '4';
        }

        if ( $agentAllocationLogInfo['money'] > 0 ) {
            // 更新代理的历史收益、余额、未激活金额
            $dbPrefix = C('DB_PREFIX');
            $sql      = "UPDATE {$dbPrefix}agent " . 
                        "SET not_available_money = not_available_money - {$agentAllocationLogInfo['money']}  " . 
                        "WHERE id = {$agentAllocationLogInfo['agent_id']}";
            $updateMoneyResult = $mysql->execute($sql);
            if ( $updateMoneyResult <= 0 ) {
                $mysql->rollback();
                $this->error('冻结金额不能减少！');
            }
        }

        $agent        = M('agent');
        $currentMoney = $agent->where(array('id'=>$agentAllocationLogInfo['agent_id']))->getField('money');

        // 增加财务记录
        $agentFinanceData = array(
            'agent_id'       => $agentAllocationLogInfo['agent_id'],
            'order_sn'       => $agentAllocationLogInfo['order_sn'],
            'finance_type'   => $financeType,
            'before_changes' => $currentMoney,
            'change_money'   => $agentAllocationLogInfo['money'],
            'after_changes'  => $currentMoney,
            'explain'        => $explain,
            'add_time'       => time(),
        );
        $agentFinanceResult = M('agentFinance')->data($agentFinanceData)->add();
        if ( $agentFinanceResult <= 0 ) {
            $mysql->rollback();
            exit();
        }

        // 若所有分账都完成则修改订单的分账状态
        if ( checkOrderType($agentAllocationLogInfo['order_sn']) == '1' ) {
            $count           = $agentAllocationLog->where(array('order_sn' => $orderSn ))->count();
            $hadStatusCount  = $agentAllocationLog->where(array('order_sn' => $orderSn, array('is_pass'=>array('NEQ', 0))))->count();
            if ( $count == $hadStatusCount ) {
                $orderSaveResult = M('giftOrder')->where(array('order_sn'=>$orderSn))->data(array('is_allocation'=>1))->save();
            }
        } else {
            $count           = $agentAllocationLog->where(array('order_sn' => $orderSn ))->count();
            $hadStatusCount  = $agentAllocationLog->where(array('order_sn' => $orderSn, array('is_pass'=>array('NEQ', 0))))->count();
            if ( $count == $hadStatusCount ) {
                $orderSaveResult = M('order')->where(array('order_sn'=>$orderSn))->data(array('is_allocation'=>1))->save();
            }
        }

        $mysql->commit();
        $this->success('已设置该订单的该分账不通过！');
    }
}

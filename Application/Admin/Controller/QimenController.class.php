<?php
namespace Admin\Controller;
use Think\Controller;

class QimenController extends Controller {
    // 奇门提供的安全码(签名用)
    private $secret = '30c25d26b59bf4f1d1d2c5c02464fc38';

    /**
     * 签名
     * @param $secret   安全码
     * @param $param    提交参数
     * @param $body     提交文档内容
     */
    private function sign($secret, $param, $body) {
        if ( empty($body) ) {
            exit('Body can\'t empty!');
        }

        if ( empty($secret) ) {
            exit('Secret error!');
        }

        ksort($param);
        $outputStr = '';
        foreach ($param as $k => &$v) {
            if ( empty($v) ) {
                exit('Param can\'t error!');
            }
            $outputStr .= $k . $v;
        }

        $outputStr = $secret . $outputStr . $body . $secret;
        return strtoupper(md5($outputStr));
    }


    /**
     * [getMsg 奇门回调]
     * @author TF <[2281551151@qq.com]>
     */
    public function getMsg() {
        $response = $_GET;
        $body     = file_get_contents("php://input");
        file_put_contents('get', json_encode($response));
        file_put_contents('body', json_encode($body));

        $qimenLog = M('qimen_log');

        if ( empty($body) ) {
            $body = "error";
        }

        $data     = array(
            'get'            => json_encode($response),
            'qimen_response' => json_encode($body),
            'add_time'       => time(),
        );
        $qimenLog->data($data)->add();

        $param = array(
           "format"         => $response['format'],
           "app_key"        => $response['app_key'],
           "v"              => $response['v'],
           "sign_method"    => $response['sign_method'],
           "customerId"     => $response['customerId'],
           "method"         => $response['method'],
           "timestamp"      => $response['timestamp'],
        );

        $sign = $this->sign($this->secret, $param, $body);
        // 检查签名
        if ( $sign != $response['sign'] ) {
            return;
        }

        switch ($response['method']) {
            // case "entryorder.confirm":
            //     $this->entryorderConfirm($body);
            //     break;

            // case "returnorder.confirm":
            //     $this->returnorderConfirm($body);
            //     break;

            case "deliveryorder.confirm":
                $this->deliveryorderConfirm($body);
                break;

            // case "sn.report":
            //     $this->snReport($body);
            //     break;

            // case "orderprocess.report":
            //     $this->orderprocessReport($body);
            //     break;

            // case "itemlack.report":
            //     $this->itemlackReport($body);
            //     break;

            // case "inventory.report":
            //     $this->inventoryReport($body);
            //     break;

            // case "storeprocess.confirm":
            //     $this->storeprocessConfirm($body);
            //     break;

            // case "stockchange.report":
            //     $this->stockchangeReport($body);
            //     break;
        }
    }


    /**
     * [deliveryorderConfirm 奇门回调更新订单信息]
     * @author TF <[2281551151@qq.com]>
     */
    private function deliveryorderConfirm($data) {
        $data          = '<?xml version="1.0" encoding="utf-8"?>' . $data;
        $xmlObj        = simplexml_load_string($data);
        $orderSn       = (string)$xmlObj->deliveryOrder->deliveryOrderCode;
        $expressCode   = (string)$xmlObj->packages->package[0]->expressCode;
        $logisticsCode = (string)$xmlObj->packages->package[0]->logisticsCode;

        $string        = (string)$xmlObj->orderLines->orderLine[0]->itemId;
        $string        = trim($string, ',');

        switch ($logisticsCode) {
            case 'STO':
                $logisticsCode = '申通';
                break;
        }

        if ( checkOrderType($orderSn) == '1' ) {
            $saveData = array(
                'status'          => '1',
                'express'         => $logisticsCode,
                'express_sn'      => $expressCode,
                'express_time'    => time(),
                'qimen_qrcode'    => $string,
            );

            if ( M('gift_order')->where(array('order_sn'=>$orderSn, 'status'=>0, 'is_pay'=>1))->data($saveData)->save() ) {
                echo 'success';
            } else {
                echo 'error';
            }
        } else {
            $mysql = M();
            $mysql->startTrans();

            $saveData = array(
                'status'          => '1',
                'express'         => $logisticsCode,
                'express_sn'      => $expressCode,
                'express_time'    => time(),
                'qimen_qrcode'    => $string,
            );

            if ( M('order')->where(array('order_sn'=>$orderSn, 'status'=>0, 'is_pay'=>1))->data($saveData)->save() ) {

                // =================================屌炸天的统计1=====================================================
                $orderInfo = M('order')->where(array('order_sn'=>$orderSn))->find();
                M('agent')->where(array('id'=>$orderInfo['agent_id']))->setInc('delivery_order_number');
                M('user')->where(array('id'=>$orderInfo['user_id']))->setInc('delivery_order_number');
                M('goods_supplier')->where(array('id'=>$orderInfo['supplier_id']))->setInc('delivery_order_number');
                // =================================屌炸天的统计1=====================================================

                $mysql->commit();
                echo 'success';
            } else {
                $mysql->rollback();
                echo 'error';
            }
        }
    }
}

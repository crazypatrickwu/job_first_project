<?php
Class Qimenbody {
    // 得到发送XML
    public function getBody($name, $param) {
        switch ($name) {
            // 商品同步接口 
            case "taobao.qimen.singleitem.synchronize":
                return $this->singleitemSynchronize($param);
                break;

            // 组合商品接口 
            case "taobao.qimen.combineitem.synchronize":
                return $this->combineitemSynchronize($param);
                break;

            // 入库单创建接口 
            case "taobao.qimen.entryorder.create":
                return $this->entryorderCreate($param);
                break;

            // 入库单查询接口
            case "taobao.qimen.entryorder.query":
                return $this->entryorderQuery($param);
                break;

            // 退货入库单创建接口 
            case "taobao.qimen.returnorder.create":
                return $this->returnorderCreate($param);
                break;

            // 退货入库单查询接口 
            case "taobao.qimen.returnorder.query":
                return $this->returnorderQuery($param);
                break;

            // 出库单创建接口
            case "taobao.qimen.stockout.create":
                return $this->stockoutCreate($param);
                break;

            // 出库单查询接口
            case "taobao.qimen.stockout.query":
                return $this->stockoutQuery($param);
                break;

            // 发货单创建接口  
            case "taobao.qimen.deliveryorder.create":
                return $this->deliveryorderCreate($param);
                break;

            // 发货单查询接口 
            case "taobao.qimen.deliveryorder.query":
                return $this->deliveryorderQuery($param);
                break;

            // 订单流水查询接口 
            case "taobao.qimen.orderprocess.query":
                return $this->orderprocessQuery($param);
                break;

            // 订单状态查询接口 （批量）
            case "taobao.qimen.orderstatus.batchquery":
                return $this->orderstatusBatchquery($param);
                break;

            // 发货单缺货查询接口
            case "taobao.qimen.itemlack.query":
                return $this->itemlackQuery($param);
                break;

            // 取消某些创建的单据, 如入库单、出库单、退货单等
            case "taobao.qimen.order.cancel":
                return $this->orderCancel($param);
                break;

            // 库存查询接口
            case "taobao.qimen.inventory.query":
                return $this->inventoryQuery($param);
                break;

            // 库存盘点查询接口
            case "taobao.qimen.inventorycheck.query":
                return $this->inventorycheckQuery($param);
                break;

            // 仓内加工单创建接口  
            case "taobao.qimen.storeprocess.create":
                return $this->storeprocessCreate($param);
                break;

            // 菜鸟自动流转查询接口  （扩展）
            case "taobao.qimen.autotransfer.query":
                return $this->autotransferQuery($param);
                break;

            // // 菜鸟自动流转查询接口  （扩展）
            // case "deliveryorder.confirm":
            //     return $this->deliveryorderConfirm($param);
            //     break;


            default:
                exit('No match body!');
                break;
        }
    }

    // 创建发货单BODY, 其他BODY参照白皮书
    public function deliveryorderCreate($param) {
        if ( empty($param) ) {
            exit('参数为空！');
        }

        $deliveryOrderCode    = $param['order_sn'];          // 出库单号, string (50) , 必填
        // $preDeliveryOrderCode = $param['order_sn'];          // 原出库单号（ERP分配）, string (50) , 条件必填，条件为换货出库
        // $preDeliveryOrderId   = $param['order_sn'];          // 原出库单号（WMS分配）, string (50) , 条件必填，条件为换货出库
        $orderType            = "JYCK";                      // 出库单类型, string (50) , 必填, JYCK=一般交易出库单, HHCK=换货出库单, BFCK=补发出库单，QTCK=其他出库单
        $warehouseCode        = "01";                        // 仓库编码, string (50)，必填 ，统仓统配等无需ERP指定仓储编码的情况填OTHER
        $sourcePlatformCode   = "OTHER";                     // 订单来源平台编码, string (50) , 必填,TB= 淘宝 、TM=天猫 、JD=京东、DD=当当、PP=拍拍、YX=易讯、EBAY=ebay、QQ=QQ网购、AMAZON=亚马逊、SN=苏宁、GM=国美、WPH=唯品会、JM=聚美、LF=乐蜂、MGJ=蘑菇街、JS=聚尚、PX=拍鞋、YT=银泰、YHD=1号店、VANCL=凡客、YL=邮乐、YG=优购、1688=阿里巴巴、POS=POS门店、OTHER=其他, (只传英文编码)
        $createTime           = date('Y-m-d H:i:s');         // 发货单创建时间, string (19) , YYYY-MM-DD HH:MM:SS, 必填
        $placeOrderTime       = date('Y-m-d H:i:s', $param['add_time']);          // 前台订单 (店铺订单) 创建时间 (下单时间) , string (19) , YYYY-MM-DD HH:MM:SS, 必填
        $operateTime          = date('Y-m-d H:i:s');         // 操作 (审核) 时间, string (19) , YYYY-MM-DD HH:MM:SS, 必填
        $shopNick             = $param['shopNick'];          // 店铺名称, string (200) , 必填
        $logisticsCode        = "STO";                       // 物流公司编码, string (50) , SF=顺丰、EMS=标准快递、EYB=经济快件、ZJS=宅急送、YTO=圆通 、ZTO=中通 (ZTO) 、HTKY=百世汇通、UC=优速、STO=申通、TTKDEX=天天快递 、QFKD=全峰、FAST=快捷、POSTB=邮政小包 、GTO=国通、YUNDA=韵达、JD=京东配送、DD=当当宅配、OTHER=其他，必填, (只传英文编码)

        // 发货人信息
        $sendName             = $param['sendName'];          // 姓名, string (50) , 必填
        $sendMobile           = $param['sendMobile'];        // 移动电话, string (50) , 必填
        $sendProvince         = $param['sendProvince'];      // 省份, string (50) , 必填
        $sendCity             = $param['sendCity'];          // 城市, string (50) , 必填
        $sendDetailAddress    = $param['sendDetailAddress']; // 详细地址, string (200) , 必填

        // 收货人信息
        $name                 = $param['name'];              // 姓名, string (50) , 必填
        $mobile               = $param['mobile'];            // 移动电话, string (50) , 必填
        $province             = $param['province'];          // 省份, string (50) , 必填
        $city                 = $param['city'];              // 城市, string (50) , 必填

        if ( empty($param['county']) ) {
            $param['county'] = "无地区";
        }

        $area                 = $param['county'];            // 区域, string (50) , 必填
        // $city                 = $param['city'];              // , string (50) , 必填
        $detailAddress        = $param['address'];           // 详细地址, string (200) , 必填

        // 货主信息
        $ownerCode            = $param['ownerCode'];         // 货主编码, string (50) , 必填
        // $itemCode          = "";                          // 商品编码, string (50) , 必填
        // $itemId            = "";                          // 仓储系统商品编码, string (50) ,条件必填
        // $planQty           = "";                          // 应发商品数量, int, 必填
        // $actualPrice       = "";                          // 实际成交价, double (18, 2) , 必填

        $orderLines = "<orderLines>";
        foreach ($param['goodsInfo'] as $key => $value) {
            $price = $value['goods_number'] * $value['goods_price'];
            $orderLines .= "
        <orderLine>
            <ownerCode>{$ownerCode}</ownerCode>
            <itemCode>{$value['bar_code']}</itemCode>
            <itemId>{$value['sku']}</itemId>
            <planQty>{$value['goods_number']}</planQty>
            <actualPrice>{$price}</actualPrice>
        </orderLine>";
        }

        $orderLines  .= "\n    </orderLines>";
        $createOrder = "<?xml version=\"1.0\" encoding=\"utf-8\"?>
<request>
    <deliveryOrder>
        <deliveryOrderCode>{$deliveryOrderCode}</deliveryOrderCode>
        <orderType>{$orderType}</orderType>
        <warehouseCode>{$warehouseCode}</warehouseCode>
        <sourcePlatformCode>{$sourcePlatformCode}</sourcePlatformCode>
        <createTime>{$createTime}</createTime>
        <placeOrderTime>{$placeOrderTime}</placeOrderTime>
        <operateTime>{$operateTime}</operateTime>
        <shopNick>{$shopNick}</shopNick>
        <logisticsCode>{$logisticsCode}</logisticsCode>
        <senderInfo>
            <name>{$sendName}</name>
            <mobile>{$sendMobile}</mobile>
            <province>{$sendProvince}</province>
            <city>{$sendCity}</city>
            <detailAddress>{$sendDetailAddress}</detailAddress>
        </senderInfo>
        <receiverInfo>
            <name>{$name}</name>
            <mobile>{$mobile}</mobile>
            <province>{$province}</province>
            <city>{$city}</city>
            <area>{$area}</area>
            <detailAddress>{$detailAddress}</detailAddress>
        </receiverInfo>
    </deliveryOrder>
    {$orderLines}
</request>";
        return $createOrder;
    }



    // ........................................
    // ........................................
    // ........................................
    // ........................................
    // ........................................
    // 其余的得到BODY函数，请参照白皮书编写。
    // ........................................
    // ........................................
    // ........................................
    // ........................................
    // ........................................
}

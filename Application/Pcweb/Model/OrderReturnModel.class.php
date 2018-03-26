<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Pcweb\Model;
use Think\Model\RelationModel;

/**
 * 订单模型
 */
class OrderReturnModel extends RelationModel{
    protected $_link = array(
        'OrderInfo'=>array(
            'mapping_type'      =>  self::BELONGS_TO,
            'class_name'        =>  'OrderInfo',
            'foreign_key'       =>  'order_id',
            'mapping_fields'    =>  array(
                                        'id',
                                        'order_sn',
                                        'pay_status',
                                        'order_status',
                                        'shipping_status',
                                        'is_commission',
                                        'create_time',
                                        'out_trade_no',
                                        'paid_money',
                                        'consignee',
                                        'tel',
                                        'refund_amount',
                                        'goods_amount',
                                        'order_amount',
                                        'manjian_amount',
                                        'coupon_amount',
                                        'coupon_id',
                                        'manjian_id',
                                        'manjian_full_price'
                                    ),
            'parent_key'        =>  'id',
            // 定义更多的关联属性
            ),
        'OrderDetail'=>array(
            'mapping_type'      =>  self::BELONGS_TO,
            'class_name'        =>  'OrderDetail',
            'foreign_key'       =>  'order_detail_id',
            'mapping_fields'    =>  array(
                                    'id',
                                    'goods_id',
                                    'goods_name',
                                    'goods_image',
                                    'goods_price',
                                    'goods_number',
                                    'specifications_text',
                                    'sku_import_duties_price'
                                    ),
            'parent_key'        =>  'id',
            // 定义更多的关联属性
            ),
    );
}

<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Xadmin\Model;
use Think\Model\RelationModel;

/**
 * 订单模型
 */
class OrderModel extends RelationModel{
    protected $_link = array(
        'OrderDetail'=>array(
            'mapping_type'      =>  self::HAS_ONE,
            'class_name'        =>  'OrderDetail',
            'foreign_key'       =>  'order_id',
            'mapping_fields'    =>  array('*'),
            'parent_key'        =>  'id',
            // 定义更多的关联属性
            ),
    );
}

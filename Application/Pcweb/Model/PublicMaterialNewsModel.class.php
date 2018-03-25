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
class PublicMaterialNewsModel extends RelationModel{
    protected $_link = array(
        'PublicMaterialNewsDetail'=>array(
            'mapping_type'      =>  self::HAS_MANY,
            'class_name'        =>  'PublicMaterialNewsDetail',
            'foreign_key'       =>  'material',
            'mapping_fields'    =>  array(
                                        '*',
                                        ),
            'parent_key'        =>  'id',
            'mapping_order'     =>  'id ASC'
            // 定义更多的关联属性
            ),
    );
}

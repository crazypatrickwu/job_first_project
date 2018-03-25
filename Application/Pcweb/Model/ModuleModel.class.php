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
class ModuleModel extends RelationModel{
    protected $_link = array(
        'ModuleList'=>array(
            'mapping_type'      =>  self::HAS_MANY,
            'class_name'        =>  'ModuleList',
            'foreign_key'       =>  'module_id',
            'mapping_fields'    =>  array('*'),
            'parent_key'        =>  'id',
            // 定义更多的关联属性
            ),
    );
}

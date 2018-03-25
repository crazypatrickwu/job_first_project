<?php
// +----------------------------------------------------------------------
// | OneThink [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Model;
use Think\Model\RelationModel;

/**
 * 订单模型
 */
class AgentRebateRecoredModel extends RelationModel{
    protected $_link = array(
        'Agent'=>array(
            'mapping_type'      =>  self::BELONGS_TO,
            'class_name'        =>  'Agent',
            'foreign_key'       =>  'agent_id',
            'mapping_fields'    =>  array('*'),
            'parent_key'        =>  'id',
            // 定义更多的关联属性
            ),
    );
}

<?php
namespace Agent\Model;
use Think\Model;

class GoodsAgentModel extends Model {
    /**
     * [_map 自动映射]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_map = array(
		'account'  => 'agent_account',
		'password' => 'agent_password',
	);

    /**
     * [_validate 自动验证]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_validate = array(

	);

    /**
     * [_auto 自动完成]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_auto = array(

	);
}
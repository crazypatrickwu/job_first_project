<?php
namespace Xadmin\Model;
use Think\Model;

class FriendshipLinkModel extends Model {
    /**
     * [_map 自动映射]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_map = array(
		'account' => 'admin_account',
		'password' => 'admin_password',
	);

    /**
     * [_validate 自动验证]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_validate = array(
		// 新增
		array('link_name', 'require', '链接名称必须！', 1, 'regex', 1),
		array('link', 'require', '链接地址必须！', 1, 'regex', 1),
		array('is_lock', array(0, 1), '是否禁用状态错误！', 1, 'in', 1),

		// 编辑
		array('link_name', 'require', '链接名称必须！', 1, 'regex', 2),
		array('link', 'require', '链接地址必须！', 1, 'regex', 2),
		array('is_lock', array(0, 1), '是否禁用状态错误！', 1, 'in', 2),

	);

    /**
     * [_auto 自动完成]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_auto = array(
		array('add_time', 'time', 1, 'function'),
	);
}
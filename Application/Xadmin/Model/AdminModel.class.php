<?php
namespace Xadmin\Model;
use Think\Model;

class AdminModel extends Model {
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
		array('admin_account', 'require', '账户必须！', 1, 'regex', 1),
		array('admin_account', '3,15', '账户长度需在3~15之间！', 1, 'length', 1),
		array('admin_password', 'require', '密码必须！', 1, 'regex', 1),
		array('admin_password', '6,20', '密码长度需在6~20之间！', 1, 'length', 1),
		array('is_lock', array(0, 1), '是否锁定状态出错！', 1, 'in', 1),

		// 编辑
		array('admin_account', '3,15', '账户长度需在3~15之间！', 1, 'length', 2),
		array('admin_password', 'require', '密码必须！', 2, 'regex', 2),
		array('admin_password', '6,20', '密码长度需在6~20之间！', 2, 'length', 2),
		array('is_lock', array(0, 1), '是否锁定状态出错！', 1, 'in', 2),

		// 编辑个人信息
		array('admin_password', 'require', '密码必须！', 1, 'regex', 4),
		array('admin_password', '6,20', '密码长度需在6~16之间！', 1, 'length', 4),
	);

    /**
     * [_auto 自动完成]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_auto = array(
		array('add_time', 'time', 1, 'function'),
		array('admin_password', 'think_ucenter_md5', 3, 'function'),
	);
}
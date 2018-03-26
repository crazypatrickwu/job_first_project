<?php
namespace Agent\Model;
use Think\Model;

class AgentModel extends Model {
    /**
     * [_validate 自动验证]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_validate = array(
		// 新增
//		array('user_id', 'require','代理玩家ID必须！', 0, 'regex'),
//		array('user_id', 'require','代理玩家ID已存在！', 0, 'unique',1),
//		array('user_id', '3,20', '代理账号长度需在3~20之间！', 1, 'length'),
//		array('agent_password', 'require','代理密码必须！', 1, 'regex', 1),
//		array('agent_password', '6,20', '代理密码长度需在6~20之间！', 1, 'length', 1),
		array('nickname', 'require','代理昵称必须！', 0, 'regex'),
		array('nickname', '1,20', '代理昵称长度需在3~20之间！', 0, 'length'),
		array('wechat_id', 'require','代理微信号必须！', 0, 'regex'),
		array('wechat_id', '1,20', '代理微信号长度需在3~20之间！', 0, 'length'),
		array('phone', 'require','代理手机号必须！', 0, 'regex'),
		array('phone', '11', '请填写正确的手机号码！', 0, 'length'),
	);

    /**
     * [_auto 自动完成]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_auto = array(
		array('dateline', 'time', 1, 'function'),
	);
}
<?php
namespace Xadmin\Model;
use Think\Model;

class RegionModel extends Model {
    /**
     * [_validate 自动验证]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_validate = array(
		// 新增
		array('pid', 'require','父ID不得为空！', 1, 'regex', 1),
		array('region_name', 'require','分类名必须！', 1, 'regex', 1),

		// 编辑
		array('pid', 'require','父ID不得为空！', 1, 'regex', 2),
		array('region_name', 'require','分类名必须！', 1, 'regex', 2),
	);

    /**
     * [_auto 自动完成]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_auto = array(
		array('add_time', 'time', 1, 'function'),
		array('level', 'getLevel', 3, 'callback'),
	);

    /**
     * [getLevel 得到本条数据等级]
     * @author TF <[2281551151@qq.com]>
     */
	protected function getLevel() {
		$pid   = I('post.pid', '', 'int');
		$level = M('region')->where(array('id'=>$pid))->getField('level');
		return $level + 1;
	}
}
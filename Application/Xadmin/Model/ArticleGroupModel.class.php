<?php
namespace Xadmin\Model;
use Think\Model;

class ArticleGroupModel extends Model {
    /**
     * [_validate 自动验证]
     * @author Fu <[418382595@qq.com]>
     */
	protected $_validate = array(
		// 新增
		array('group_name', 'require','分类名必须！', 1, 'regex', 1),

		// 编辑
		array('group_name', 'require','分类名必须！', 1, 'regex', 2),
	);

    /**
     * [_auto 自动完成]
     * @author Fu <[418382595@qq.com]>
     */
	protected $_auto = array(
		array('add_time', 'time', 1, 'function'),
	);

}
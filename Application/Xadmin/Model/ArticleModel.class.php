<?php
namespace Xadmin\Model;
use Think\Model;

class ArticleModel extends Model {
    /**
     * [_validate 自动验证]
     * @author Fu <[418382595@qq.com]>
     */
	protected $_validate = array(
		// 新增
		array('group_id', 'require','所属分类不能为空！', 1, 'regex', 1),
		array('title', 'require','文章标题必须！', 1, 'regex', 1),
		array('author', 'require','文章作者必须！', 1, 'regex', 1),
		array('from', 'require','文章来源必须！', 1, 'regex', 1),
		array('content', 'require','文章内容必须！', 1, 'regex', 1),

		// 编辑
		array('group_id', 'require','所属分类不能为空！', 1, 'regex', 2),
		array('title', 'require','文章标题必须！', 1, 'regex', 2),
		array('author', 'require','文章作者必须！', 1, 'regex', 2),
		array('from', 'require','文章来源必须！', 1, 'regex', 2),
		array('content', 'require','文章内容必须！', 1, 'regex', 2),
	);

    /**
     * [_auto 自动完成]
     * @author Fu <[418382595@qq.com]>
     */
	protected $_auto = array(
		array('add_time', 'time', 1, 'function'),
	);

}
<?php
namespace Xadmin\Model;
use Think\Model;

class AdModel extends Model {

    /**
     * [_validate 自动验证]
     * @author xu <[565657400@qq.com]>
     */
	protected $_validate = array(
		// 新增
		array('box_id', 'require', '广告位不能为空！',1, 'regex', 1),
		array('ad_name', 'require', '广告名称不能为空！', 1, 'regex', 1),
		array('url', 'require', '链接地址必须！', 1, 'regex', 1),
		array('image', 'require', '图片地址必须！', 1, 'regex', 1),
		array('start_time','fixtime' , '时间段不正确！', 1, 'callback', 1),

		// 编辑
		array('box_id', 'require', '广告位不能为空！',1, 'regex', 2),
		array('ad_name', 'require', '广告名称不能为空！', 1, 'regex', 2),
		array('url', 'require', '链接地址必须！', 1, 'regex', 2),
		array('image', 'require', '图片地址必须！', 1, 'regex', 2),
		array('start_time','fixtime' , '时间段不正确！', 1, 'callback', 2),

	);

	// 判断时间
	 public function fixtime($field) {
	 	if ( $field > I('post.end_time')) {
	 		return false;
	 	} else {
	 		return true;
	 	}
	 }

    /**
     * [_auto 自动完成]
     * @author xu <[565657400@qq.com]>
     */
	protected $_auto = array(
		array('start_time', 'strtotime', 3, 'function'),
		array('end_time', 'strtotime', 3, 'function'),
		array('add_time', 'time', 1, 'function'),
	);
}
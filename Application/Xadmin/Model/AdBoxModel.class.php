<?php
namespace Xadmin\Model;
use Think\Model;

class AdBoxModel extends Model {
    /**
     * [_validate 自动验证]
     * @author xu <[565657400@qq.com]>
     */
	protected $_validate = array(
		// 新增
		array('group_name', 'require', '广告位名称不能为空！',1, 'regex', 1),
		array('sign', 'require', '广告位标识不能为空！', 1, 'regex', 1),
		array('sign', 'fixsign', '广告位标识有重复！', 1, 'callback', 1),
		array('width', 'require', '广告位宽度不能为空', 1, 'regex', 1),
		array('height', 'require', '广告位高度不能为空', 1, 'regex', 1),
		array('width', 'number', '广告位宽度只能填数字', 1, 'regex', 1),
		array('height', 'number', '广告位高度只能填数字', 1, 'regex', 1),
		// 编辑
		array('id', 'require', '广告位非法操作！',1, 'regex', 2),
		array('group_name', 'require', '广告位名称不能为空！',1, 'regex', 2),
		array('sign', 'require', '广告位标识不能为空！', 1, 'regex', 2),
		array('sign', 'fixsign', '广告位标识有重复！', 1, 'callback', 2),
		array('width', 'require', '广告位宽度不能为空', 1, 'regex', 2),
		array('height', 'require', '广告位高度不能为空', 1, 'regex', 2),
		array('width', 'number', '广告位宽度只能填数字', 1, 'regex', 2),
		array('height', 'number', '广告位高度只能填数字', 1, 'regex', 2),

	);

	// 判断标识是否重复
	 public function fixsign() {
	 	$count  = M('ad_box')->where(array('sign'=>I('post.sign'), 'id'=>array('neq', I('post.id'))))->count();
	 	if ( $count <= 0 ) {
	 		return true;
	 	} else {
	 		return false;
	 	}
	 }

    /**
     * [_auto 自动完成]
     * @author xu <[565657400@qq.com]>
     */
	protected $_auto = array(
		array('add_time', 'time', 1, 'function'),
	);
}
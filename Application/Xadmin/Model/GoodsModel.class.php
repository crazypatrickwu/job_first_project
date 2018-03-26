<?php
namespace Xadmin\Model;
use Think\Model;

class GoodsModel extends Model {
    /**
     * [_validate 自动验证]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_validate = array(
		// 新增
		array('supplier_id', 'require', '供货商必须！', 1, 'regex', 1),
		array('goods_name', 'require', '商品名称必须！', 1, 'regex', 1),
		array('sku', 'require', 'SKU必须！', 1, 'regex', 1),
		array('bar_code', 'require', '条形码必须！', 1, 'regex', 1),
		array('category_id', 'require', '分类必须！', 1, 'regex', 1),
		array('goods_market_price', 'require', '市场价必须！', 1, 'regex', 1),
		array('goods_price', 'require', '商品售价必须！', 1, 'regex', 1),
		array('level1_price', 'require', '盟主供货价必须！', 1, 'regex', 1),
		array('level2_price', 'require', '帮主供货价必须！', 1, 'regex', 1),
		array('level3_price', 'require', '美人供货价必须！', 1, 'regex', 1),
		array('goods_number', 'require', '商品数量必须！', 1, 'regex', 1),
		array('is_sale', 'require', '是否销售必须！', 1, 'regex', 1),
		array('is_sale', array(0, 1), '是否销售状态出错！', 1, 'in', 1),
	);

    /**
     * [_auto 自动完成]
     * @author TF <[2281551151@qq.com]>
     */
	protected $_auto = array(
		array('add_time', 'time', 1, 'function'),
	);
}
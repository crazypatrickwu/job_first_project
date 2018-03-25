<?php
namespace Think\Template\TagLib;
use Think\Template\TagLib;

// 自定义扩展标签
class Diy extends TagLib {
    // 标签定义
    protected $tags   =  array(
        'ad'        =>  array('attr'=>'name,sign', 'close'=>0),
        'goodslist' =>  array('attr'=>'name,ids', 'close'=>0),
        );

    /**
    * [_ad 广告位]
    * @author TF <[2281551151@qq.com]>
    */
    public function _ad($tag,$content) {
        $name     = $this->autoBuildVar($tag['name']);
        if ( empty($tag['sign']) ) {
            return;
        }
        
        $value    = 'getAdBox("' . $tag['sign'] . '")';
        $parseStr = '<?php '.$name.' = '.$value.'; ?>';
        return $parseStr;
    }


    /**
    * [_goodslist 商品列表]
    * @author TF <[2281551151@qq.com]>
    */
    public function _goodslist($tag,$content) {
        $name     = $this->autoBuildVar($tag['name']);
        if ( empty($tag['ids']) ) {
            return;
        }

        $value    = 'getGoodsList("' . $tag['ids'] . '")';
        $parseStr = '<?php '.$name.' = '.$value.'; ?>';
        return $parseStr;
    }
}

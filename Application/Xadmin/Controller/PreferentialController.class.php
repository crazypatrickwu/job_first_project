<?php
namespace Xadmin\Controller;
use Think\Controller;

class PreferentialController extends BaseController {
    /**
     * [preferentialCode 优惠卷列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function preferentialCodeList() {
        $preferentialCode = M('goods_preferential_code');

        $preferentialCode->count();

        $page = new \Think\Page($count, 25);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show = $page->show();

        $preferentialCodeList = $preferentialCode->limit($page->firstRow . ',' . $page->listRows)->select();

        $this->assign('show', $show);
        $this->assign('preferentialList', $preferentialList);
        $this->display('preferentialCodeList');
    }
}

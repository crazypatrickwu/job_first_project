<?php
namespace Xadmin\Controller;
use Think\Controller;
// 广告控制器
class AdController extends BaseController {
    /**
     * [adList 广告列表]
     * @author xu <[565657400@qq.com]>
     */
    public function adList() {
        $count = M('ad')->count();
        $Page = new \Think\Page($count, 20);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
		}
		
        $show = $Page->show();
         
        $dbPrefix = C('DB_PREFIX');
        $sql = "SELECT ab.group_name, ab.id AS adboxid, ab.sign, ad.id, ad.ad_name, ad.image, ad.url, ad.start_time , ad.end_time ". 
               "FROM {$dbPrefix}ad AS ad " . 
               "LEFT JOIN {$dbPrefix}ad_box AS ab ON ab.id = ad.box_id " . 
               "ORDER BY ad.box_id,ad.start_time DESC " . 
               "LIMIT {$Page->firstRow} , {$Page->listRows}";

        $ad_list = M()->query($sql);
        $this->assign('show',$show);
        $this->assign('adList',$ad_list);
        $this->display('adList');
    }

    /**
     * [addAd 添加广告]
     * @author xu <[565657400@qq.com]>
     */
    public function addAd() {
  		if (IS_POST) {
  			$ad   = D("ad");
  			$data = $ad->create(I('post.'), 1);

    		if( empty($data) ) {
                $this->error($ad->getError());
            } else {
                if( $ad->data($data)->add() ) {
                    $this->success('添加成功', U('Ad/adList'));
                } else {
                    $this->error('添加失败');
                }
            }
  		} else {
  			$ad_box 	   = M('ad_box');
  			$ad_box_list = $ad_box->order('id desc')->select();
  			$this->assign('ad_box_list',$ad_box_list);
  			$this->display('addAd');
  		}
    }

     /**
     * [delAd 删除广告]
     * @author xu <[565657400@qq.com]>
     */
    public function delAd() {
        $id = I('get.id');
        $ad = M('ad');

        if( $ad->where(array('id'=>$id))->delete() ) {
            $this->success('删除成功',U('Ad/adList'));
        } else {
            $this->error('删除失败');
        }
    }
    
    /**
     * [editAd 编辑广告]
     * @author xu <[565657400@qq.com]>
     */
    public function editAd() {
        if( IS_POST ) {
            $ad   = D("Ad");
            $data = $ad->create(I('post.'), 2);

            if( empty($data) ) {
                $this->error($ad->getError());
            } else {
                if( $ad->where(array('id'=>I('post.id')))->data($data)->save() >= 0 ) {
                    $this->success('修改成功',U('Ad/adList'));
                } else {
                    $this->error('修改失败');
                }
            }
        } else {
            $id = I('get.id');

            if ( empty($id) ) {
              $this->error('参数丢失！');
            }

            $ad = M('ad');
            $ad_box = M('ad_box');
            $ad_msg = $ad->where(array('id'=>$id))->find();
            $ad_box_msg = $ad_box->where(array('box_id'=>$id))->find();
            $ad_box_list = $ad_box->order('id desc')->select();
            $this->assign('ad_box_list',$ad_box_list);
            $this->assign('ad_msg',$ad_msg);
            $this->assign('ad_box_msg',$ad_box_msg);
            $this->display('editAd'); 
        }
    }

    /**
     * [adBoxList 广告位列表]
     * @author xu <[565657400@qq.com]>
     */
    public function adBoxList() {
      $ad_box    = M('ad_box');
      $ad_list_b = $ad_box ->limit($Page->firstRow , $Page->listRows)->order('add_time desc')->select();
      $count     = $ad_box->count();
      $Page      = new \Think\Page($count, 20);
	  
	  if ($this->iswap()) {
			$page->rollPage	= 5;
	  }
	  
      $show      = $Page->show();
      $this->assign('adList',$ad_list_b);
      $this->assign('show',$show);
      $this->display('adBoxList');
    }

    /**
     * [addAdBox 添加广告位]
     * @author xu <[565657400@qq.com]>
     */
    public function addAdBox() {
        if (IS_POST) {
            $ad_box = D('AdBox');
            $data   = $ad_box->create(I('post.'), 1);

            if( empty($data) ) {
                $this->error($ad_box->getError());
            } else {
                if( $ad_box->data($data)->add() ) {
                    $this->success('添加成功', U('Ad/adBoxList'));
                } else {
                    $this->error('添加失败');
                }
            }
        } else {
            $this->display('addAdBox');
        }

    }

    /**
     * [delAdBox 删除广告位]
     * @author xu <[565657400@qq.com]>
     */
    public function delAdBox() {
        $id     = I('get.id');
        $ad_box = M('ad_box');
        $del = $ad_box->where(array('id'=>$id))->delete();
        if ($del) {
            $this->success('删除成功！',U('Ad/AdBoxList'));
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * [editAdBox 编辑广告位]
     * @author xu <[565657400@qq.com]>
     */
    public function editAdBox() {
        if(IS_POST) {
            $ad_box = D('AdBox');
            $data   = $ad_box->create(I('post.'),2);

            if ( empty($data) ) {
                $this->error($ad_box->getError());
            } else {
                $save = $ad_box->where(array('id'=>I('post.id')))->data($data)->save();
                if($save) {
                    $this->success('编辑成功',U('Ad/adBoxList'));
                } else {
                    $this->error('编辑失败');
                }
            }
        } else {
            $id     = I('get.id');

            if ( empty($id) ) {
              $this->error('参数丢失！');
            }

            $ad_box = M('ad_box');
            $ad_box_m = $ad_box->where(array('id'=>$id))->find();
            $this->assign('ad_box_m',$ad_box_m);
            $this->display('editAdBox');
        }
    } 

    // 图片上传使用
    public function photoSave() {
        // 图片保存路径
        fileUpload('./Uploads/Ad/', function($e) {
            echo json_encode(array('error'=>'', 'src'=>trim($e['filePath'], '.')));
        });
    }
}
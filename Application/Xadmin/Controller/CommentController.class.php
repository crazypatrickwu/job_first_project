<?php
namespace Xadmin\Controller;
use Think\Controller;
// 评论控制器
class CommentController extends BaseController {
    /**
     * [commentList 评论列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function commentList() {
        $comment = M('goods_comment');
        $count   = $comment->count();

        $page    = new \Think\Page($count, 25);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show    = $page->show();

        $dbPrefix = C('DB_PREFIX');
        $sql      = "SELECT gc.id, gc.user_id, gc.goods_id, g.goods_name, g.goods_image, gc.star, gc.add_time, gc.order_sn, u.headimgurl " . 
                    "FROM {$dbPrefix}goods_comment AS gc " . 
                    "LEFT JOIN {$dbPrefix}user  AS u ON u.id = gc.user_id " . 
                    "LEFT JOIN {$dbPrefix}goods AS g ON g.id = gc.goods_id " . 
                    "ORDER BY gc.add_time DESC " . 
                    "LIMIT {$page->firstRow}, {$page->listRows}";

        $commentList = $comment->query($sql);

        $this->assign('show', $show);
        $this->assign('commentList', $commentList);
        $this->display('commentList');
    }

    /**
     * [delComment 删除评论]
     * @author TF <[2281551151@qq.com]>
     */
    public function delComment() {
        $id = I('get.id', '', 'int');

        if ( empty($id) ) {
            $this->error('ID 参数丢失！');
        }

        if ( M('goods_comment')->where(array('id'=>$id))->delete() ) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * [editcomment 编辑评论]
     * @author TF <[2281551151@qq.com]>
     */
    public function editComment() {
        if ( IS_POST ) {
            $id = I('post.id', '', 'int');

            if ( empty($id) ) {
                $this->error('参数丢失！');
            }

            $comment = D('GoodsComment');
            $data = $comment->create(I('post.'), 2);

            if ( empty($data) ) {
                $this->error($comment->getError());
            } else {
                if( $comment->where(array('id'=>$id))->data($data)->save() ) {
                    $this->success('编辑成功！', U('Comment/commentList'));
                } else {
                    $this->error('编辑失败！', U('Comment/commentList'));
                }
            }
        } else {
            $id = I('get.id', '', 'int');

            if ( empty($id) ) {
                $this->error('参数丢失！');
            }

            $goodsCommentInfo = M('GoodsComment')->where(array('id'=>$id))->find();
            $adminList        = M('admin')->field('id, admin_account')->select();
            $this->assign('adminList', $adminList);
            $this->assign('goodsCommentInfo', $goodsCommentInfo);
            $this->display('editComment');
        }
    }
}
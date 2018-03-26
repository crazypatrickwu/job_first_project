<?php
namespace Xadmin\Controller;
use Think\Controller;
// 文章控制器
class ArticleController extends BaseController {
    /**
     * [articleList 文章列表]
     * @author Fu <[418382595@qq.com]>
     */
    public function articleList() {
        $article        = M('Article');

        $keywords   = I('post.keywords');
        if ( !empty($keywords) ) {
            $where['title'] = array('LIKE', "%{$keywords}%");
        } else {
            $where = array();
        }

        
        $count = $article->where($where)->count();
        $page  = new \Think\Page($count, 20);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show  = $page->show();

        $articleList    = $article->order('id DESC')
                                  ->where($where)
                                  ->field('id,group_id,title,add_time')
                                  ->limit($page->firstRow .','. $page->listRows)
                                  ->select();
                      
        $this->assign('articleList',$articleList);
        $this->assign('show',$show); 
        $this->display('articleList');
    }

    /**
     * [addArticle 添加文章]
     * @author Fu <[418382595@qq.com]>
     */
    public function addArticle() {
        if ( IS_POST ) {
            $article    = D('Article');
            $data       = $article->create( I('post.'), 1);
           
            if (empty($data)) {
                $this->error($article->getError());
            } else {
                if ($article->data($data)->add() > 0) {
                    $this->success('添加成功！',U('Article/articleList'));
                } else {
                    $this->error('添加失败！');
                }
            }
        } else {
            $articleGroupList   = M('article_group')->field('id,group_name')
                                                    ->select();

            $this->assign('articleGroupList',$articleGroupList);
            $this->display('addArticle');
        }
    }

    /**
     * [delArticle 删除文章]
     * @author Fu <[418382595@qq.com]>
     */
    public function delArticle() {
        $id = I('get.id', '', 'int');

        if ( empty($id) ) {
            $this->error('ID 参数丢失！');
        }
        
        $article   = M('Article');
        if ( $article->where(array('id'=>$id))->delete() ) {
            $this->success('删除成功！' ,U('Article/articleList'));
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * [editArticle 编辑文章]
     * @author Fu <[418382595@qq.com]>
     */
    public function editArticle() {
        if ( IS_POST ) {
            $article = D('Article');
            $data = $article->create( I('post.'), 2);
            
            if ( empty($data) ) {
                $this->error($artilce->getError());
            } else {
                $id = I('post.id', '', 'int');
                
                if ($article->where(array('id'=>$id))->data($data)->save() >0) {
                    $this->success('保存成功！', U('Article/articleList'));
                } else {
                    $this->error('保存失败！');
                }
            }
        } else {
            $id          = I('get.id', '', 'int');
            if ( empty($id) ) {
                $this->error('ID 参数丢失！');
            }
            $articleInfo = M('article')->find($id);
            $articleGroupList   = M('article_group')->field('id,group_name')
                                                    ->select();

            $this->assign('articleInfo',$articleInfo);
            $this->assign('articleGroupList',$articleGroupList);
            $this->display('editArticle');
        }    
    }

    /**
     * [articleGroupList 文章分类列表]
     * @author Fu <[418382595@qq.com]>
     */
    public function articleGroupList() {
        $articleGroup = M('article_group');

        $count = $articleGroup->count();
        $page  = new \Think\Page($count, 20);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show  = $page->show();

        $articleGroupList = $articleGroup->order('id DESC')
                                         ->limit($page->firstRow .','. $page->listRows)
                                         ->select();
                                     
        $this->assign('articleGroupList',$articleGroupList);
        $this->assign('show',$show);  
        $this->display('ArticleGroup:articleGroupList');
    }

    /**
     * [addArticleGroup 添加文章分类]
     * @author Fu <[418382595@qq.com]>
     */
    public function addArticleGroup() {
        if ( IS_POST ) {
            $articleGroup = D('ArticleGroup');
            $data = $articleGroup->create( I('post.'), 1);

            if (empty($data)) {
                $this->error($articleGroup->getError());
            } else {
                if ($articleGroup->data($data)->add() > 0) {
                    $this->success('添加成功！',U('Article/articleGroupList'));
                } else {
                    $this->error('添加失败！');
                }
            }
        } else {
            $this->display('ArticleGroup:addArticleGroup');
        }
    }

    /**
     * [delArticleGroup 删除文章分类]
     * @author Fu <[418382595@qq.com]>
     */
    public function delArticleGroup() {
        $id = I('get.id', '', 'int');

        if ( empty($id) ) {
            $this->error('ID 参数丢失！');
        }
        
        $articleGroup   = M('article_group');
        if ( $articleGroup->where(array('id'=>$id))->delete() ) {
            $this->success('删除成功！' ,U('Article/articleGroupList'));
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * [editArticleGroup 编辑文章分类]
     * @author Fu <[418382595@qq.com]>
     */
    public function editArticleGroup() {
        if ( IS_POST ) {
            $articleGroup = D('ArticleGroup');
            $data = $articleGroup->create( I('post.'), 2);
            
            if ( empty($data) ) {
                $this->error($artilceGroup->getError());
            } else {
                $id = I('post.id', '', 'int');

                if ($articleGroup->where(array('id'=>$id))->data($data)->save() >0) {
                    $this->success('保存成功！', U('Article/articleGroupList'));
                } else {
                    $this->error('保存失败！');
                }
            }
        } else {
            $id           = I('get.id', '', 'int');
            if ( empty($id) ) {
                $this->error('ID 参数丢失！');
            }

            $articleGroupInfo = M('article_group')->find($id);

            $this->assign('articleGroupInfo',$articleGroupInfo);
            $this->display('ArticleGroup:editArticleGroup');
        }
    }

    // 图片上传使用
    public function photoSave() {
        // 图片保存路径
        fileUpload('./Uploads/Article/', function($e) {
            echo json_encode(array('error'=>0, 'url'=>trim($e['filePath'], '.')));
        });
    }


}
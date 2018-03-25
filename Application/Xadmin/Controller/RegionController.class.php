<?php
namespace Xadmin\Controller;
use Think\Controller;
// 区域控制器
class RegionController extends BaseController {
    /**
     * [regionList 地区列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function regionList(){
        $regionList = M('region')->where(array('pid'=>'1'))->order('pid DESC')->select();

        $this->assign('regionList', $regionList);
    	$this->display('regionList');
    }

    /**
    * [recursiveregion 递归地区信息]
    * @author TF <[2281551151@qq.com]>
    */
    private function recursiveRegion($pid, $list = '') {
        static $RegionList;
        if ( !empty($list) ) {
            $RegionList = $list;
        }

        $childList = array();
        foreach ($RegionList as $key => $value) {
            if ( $value['pid'] == $pid ) {
                $childList[]    = $value;
                unset($RegionList[$key]);
            }
        }

    	if (empty($childList)) {
    		return false;
    	} else {
            $result = array();
    		foreach ($childList as $key => &$value) {
                $result[] = $value;

    			$tempResult = $this->recursiveRegion($value['id']);
    			if (!empty($tempResult)) {
                    $result = array_merge($result, $tempResult);
                } 
    		}
    		return $result;
    	}
    }

    /**
    * [delRegion 删除地区]
    * @author TF <[2281551151@qq.com]>
    */
    public function delRegion() {
        $id = I('get.id', '', 'int');

        if ( empty($id) ) {
            $this->error('ID 参数丢失！');
        }

        $region = M('region');
        $count  = $region->where(array('id'=>$id))->count();
        if ( $count <= 0 ) {
            $this->error('不存在该地区！');
        }

        // 得到子地区
        $regionList = $region->select();
        $childList  = $this->recursiveRegion($id, $regionList);

        // 合并本地区
        $childRegionId   = array_column($childList, 'id');
        $childRegionId[] = $id;

        // 删除本地区与本地区下面的子地区
        if ( $region->where(array('id'=>array('in', $childRegionId)))->delete() ) {
            S('levelRegionList', null);
            $this->success('删除成功！', U('Region/regionList'));
        } else {
            S('levelRegionList', null);
            $this->error('删除失败！', U('Region/regionList'));
        }
    }


    /**
    * [addRegion 添加地区]
    * @author TF <[2281551151@qq.com]>
    */
    public function addRegion() {
        if ( IS_POST ) {
            $region = D('region');
            $data   = $region->create(I('post.'), 1);

            if (empty($data)) {
                $this->error($region->getError());
            } else {
                // 如果父级ID大于等于3 则不能再继续添加
                $level = $region->where(array('id'=>$data['pid']))->getField('level');
                if ( $level >= 4 ) {
                    $this->error('地区等级不得超过4层！');
                }

                if ( $region->data($data)->add() ) {
                    $this->success('添加成功！', U('region/regionList'));
                    S('levelRegionList', null);
                } else {
                    $this->error('添加失败！');
                }
            }
        } else {
            $levelRegionList = S('levelRegionList');
            if ( empty($levelRegionList) ) {
                $regionList = M('region')->order('pid DESC')->select();
                $levelRegionList = $this->recursiveregion('0', $regionList);
                S('levelRegionList', $levelRegionList, 3600);
            };

            $this->assign('regionList', $levelRegionList);
            $this->display('addRegion');
        }
    }

    /**
    * [editRegion 编辑地区]
    * @author TF <[2281551151@qq.com]>
    */
    public function editRegion() {
        if ( IS_POST ) {
            $region = D('region');
            $data     = $region->create(I('post.'), 2);

            if (empty($data)) {
                $this->error($region->getError());
            } else {
                // 如果父级ID大于等于3 则不能再继续添加
                $level = $region->where(array('id'=>$data['pid']))->getField('level');
                if ( $level >= 4 ) {
                    $this->error('地区等级不得超过4层！');
                }

                $id = I('post.id', '', 'int');

                if ( $region->where(array('id'=>$id))->data($data)->save() >= 0 ) {
                    $this->success('保存成功！', U('region/regionList'));
                    S('levelRegionList', null);
                } else {
                    $this->error('保存失败！');
                }
            }
        } else {
            $id         = I('get.id', '', 'int');
            $regionInfo = M('Region')->where(array('id'=>$id))->find();

            $levelRegionList = S('levelRegionList');
            if ( empty($levelRegionList) ) {
                $regionList = M('region')->order('pid DESC')->select();
                $levelRegionList = $this->recursiveregion('0', $regionList);
                S('levelRegionList', $levelRegionList, 3600);
            };

            $this->assign('regionList', $levelRegionList);
            $this->assign('regionInfo', $regionInfo);
            $this->display('editRegion');
        }
    }

    /**
    * [getChildRegion 得到下级地区]
    * @author TF <[2281551151@qq.com]>
    */
    public function getChildRegion() {
        if ( IS_POST ) {
            $id = I('post.id', '', 'int');

            if ( empty($id) ) {
                $this->error('参数丢失！');
            }

            $regionList = M('region')->where(array('pid'=>$id))->select();
            echo json_encode($regionList);
        }
    }
}
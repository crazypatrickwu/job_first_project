<?php
namespace Xadmin\Controller;
use Think\Controller;
// 管理员控制器
class AuthController extends BaseController {
    /**
     * [roleList 角色列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function roleList() {
        $auth_group = M('admin_auth_group');
        if( IS_POST ) {
            $groupName = I('groupName', '');
            if(empty($groupName)) {
                $this->error('分组名不能为空！');
            }
            $data = array(
                'title'     => $groupName,
                'status'    => '1',
                'add_time'  => time()
            );
            if ( $auth_group->data($data)->add() ) {
                $this->success('添加分组成功!');
            } else {
                $this->error('添加分组失败！');
            }
        } else {
            $group = $auth_group->select();
            $this->assign('group', $group);
            $this->display('roleList');
        }
    }

    /**
     * [addRole 添加角色]
     * @author TF <[2281551151@qq.com]>
     */
    public function addRole() {
        if ( IS_POST ) {
            $data = array(
                'title'  => I('post.title'),
                'status' => I('post.status'),
                'rules'  => ''
            );

            if ( M('admin_auth_group')->data($data)->add() ) {
                $this->success('添加成功！', U('Auth/roleList'));
            } else {
                $this->error('添加失败！', U('Auth/roleList'));
            }
        } else {
            $this->display('addRole');
        }
    }

    /**
     * [delRole 删除角色]
     * @author TF <[2281551151@qq.com]>
     */
    public function delRole() {
        $id = I('get.id', '', 'int');

        if ( empty($id) ) {
            $this->error('ID 参数丢失！');
        }

        if ( M('admin_auth_group_access')->where(array('group_id'=>$id))->count() > 0 ) {
            $this->error('请先将该组管理员删除，再进行操作！');
        }

        if ( M('admin_auth_group')->where(array('id'=>$id))->delete() ) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * [roleRename 改角色名称]
     * @author TF <[2281551151@qq.com]>
     */
    public function roleRename() {
        if ( IS_POST ) {
            $id     = I('post.id', '', 'int');
            $title  = I('post.title');
            $status = I('post.status');

            if ( M('admin_auth_group')->where(array('id'=>$id))->data(array('title'=>$title, 'status'=>$status))->save() >= 0 ) {
                $this->success('更改成功！', U('Auth/roleList'));
            } else {
                $this->error('更改失败！', U('Auth/roleList'));
            }
        } else {
            $id       = I('get.id', '', 'int');

            if ( empty($id) ) {
                $this->error('参数丢失！');
            }

            $authInfo = M('admin_auth_group')->where(array('id'=>$id))->find();
            $this->assign('authInfo', $authInfo);
            $this->display('roleRename');
        }
    }

   /**
     * [editRolePower 权限编辑]
     * @author TF <[2281551151@qq.com]>
     */
    public function editRolePower() {
        if ( IS_POST ) {
            $id = I('post.id');
            if ( empty($id) ) {
                $this->error('ID 参数丢失！');
            }

            $rules = I('post.rules');
            $rules = implode(',', $rules);


            if ( M('admin_auth_group')->where(array('id'=>$id))->data(array('rules_menu'=>$rules))->save() ) {
                $this->success('保存成功！');
            } else {
                $this->error('保存失败！');
            }
        } else {
            $id = I('get.id', '', 'int');
            $authRuleList = M('admin_menu')->where(array('status'=>1))->field('id, name, title,level,pid_list')->select();

            $result = array();

            foreach ($authRuleList as $key => $value) {
                if ($value['level'] == 1) {
                    $result[$key]['title'] = $value;
                }else{
                    $son_result[$value['id']] = $value;
                }
            }

            foreach ($son_result as $k1 => $v1) {
                $pid_list = array();
                $pid_list = explode(',',$v1['pid_list']);
                foreach ($result as $k2 => $v2) {
                    if (in_array('['.$v2['title']['id'].']', $pid_list)) {
                        $result[$k2]['rules'][$k1] = array(
                            'id'    => $v1['id'],
                            'title' => $v1['title'],
                        );
                    }
                }
            }
            // dump($result);
            // die;
            $authGroup = M('admin_auth_group')->where(array('id'=>$id))->getField('rules_menu');
            $this->assign('authGroup', explode(',', $authGroup));
            $this->assign('rules', $result);
            $this->display('editRolePower');
        }
    }
}
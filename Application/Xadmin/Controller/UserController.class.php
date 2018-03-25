<?php
namespace Xadmin\Controller;
use Think\Controller;
// 会员控制器
class UserController extends BaseController {
    /**
     * [userList 会员列表]
     * @author TF <[2281551151@qq.com]>
     */
    public function userList() {
        $id        = I('get.id');
        $username  = I('get.username');
        $province  = I('get.province');
        $city      = I('get.city');
        $startTime = I('get.start_time');
        $endTime   = I('get.end_time');

        if( !empty($id) ) {
            $where['id'] = array('LIKE', "%{$id}%");
        }

        if( !empty($username) ) {
            $username   = addslashes($username);
            $username   = urldecode($username);
            $where['nickname'] = array('LIKE', "%{$username}%");
        }

        if( !empty($province) ) {
            $province   = addslashes($province);
            $province   = urldecode($province);
            $where['province'] = array('LIKE', "%{$province}%");
        }

        if( !empty($city) ) {
            $city       = addslashes($city);
            $city       = urldecode($city);
            $where['city'] = array('LIKE', "%{$city}%");
        }

        if( !empty($startTime) && !empty($endTime) ) {
            $startTime = addslashes($startTime);
            $startTime = urldecode($startTime);
            $startTime = str_replace('+', ' ', $startTime);
            $startTime = strtotime($startTime);

            $endTime   = addslashes($endTime);
            $endTime   = urldecode($endTime);
            $endTime   = str_replace('+', ' ', $endTime);
            $endTime   = strtotime($endTime);
            $where['add_time'] = array('BETWEEN', array($startTime, $endTime));
        }

        $daochu = I("get.daochu");
        $user  = M('user');
        if ( empty($daochu) ) {
            $count = $user->where($where)->count();

            $getData = array();
            $get     = I('get.');
            foreach ($get as $key => $value) {
                if ( !empty($key) ) {
                    $getData[$key] = urldecode($value);
                }
            }
            
            if ( !empty($getData['add_time']) ) {
              $getData['add_time'] = search_time_format($getData['add_time']);
            }

            if ( !empty($getData['end_time']) ) {
              $getData['end_time'] = search_time_format($getData['end_time']);
            }


            $page = new \Think\Page($count, 25, $getData);
		
			if ($this->iswap()) {
				$page->rollPage	= 5;
			}
			
            $show = $page->show();

            $userList = $user->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();

            $this->assign('userList', $userList);
            $this->assign('show', $show);
            $this->display('userList');
        } else {
            $userList = $user->where($where)->limit(100)->select();
            export_csv('users.csv', $userList);
        }
    }

    /**
     * [userDetail 会员订单详情]
     * @author TF <[2281551151@qq.com]>
     */
    public function userDetail() {
        $id         = I('get.id', '', 'int');

        if ( empty($id) ) {
            $this->error('参数丢失！');
        }

        $where    = array();
        $orderSn  = I('get.order_sn', '');
        $whereStr = '';
        if( !empty($orderSn) ) {
            $orderSn   = addslashes($orderSn);
            $orderSn   = urldecode($orderSn);
            $whereStr .= "AND o.order_sn LIKE \"%{$orderSn}%\" ";
        }

        $userInfo    = M('user')->where(array('id'=>$id))->find();
     
        $dbPrefix    = C('DB_PREFIX');
        $sql         = "SELECT count(*) AS count " . 
                       "FROM `{$dbPrefix}order` AS o " . 
                       "LEFT JOIN `{$dbPrefix}agent` AS a ON a.id = o.agent_id " . 
                       "WHERE o.user_id = {$id} {$whereStr} ";
        $count       = M()->query($sql);
        $page        = new \Think\Page($count[0]['count'], 25);
		
		if ($this->iswap()) {
			$page->rollPage	= 5;
	  	}
		
        $show        = $page->show();

        $orderDetail = M('order_detail');
        $sql         = "SELECT o.id, o.order_sn, a.level, o.add_time " . 
                        "FROM `{$dbPrefix}order` AS o " . 
                        "LEFT JOIN `{$dbPrefix}agent` AS a ON a.id = o.agent_id " . 
                        "WHERE o.user_id = {$id} {$whereStr} " . 
                        "LIMIT {$page->firstRow}, {$page->listRows}";

        $orderList   = M()->query($sql);

        // dump($orderList);
        // exit();

        $this->assign('show', $show);
        $this->assign('userInfo', $userInfo);
        $this->assign('orderList', $orderList);
        $this->display('userDetail');
    }
}
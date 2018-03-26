<?php  namespace Xadmin\Controller;

use Think\Controller;

class GoodsController extends BaseController {

    public function _initialize(){
        parent::_initialize();
        $this->buyerArr = array('agent'=>'平台代理','player'=>'游戏玩家');
    }

    public function index() {
        $list = D('goods')->select();
        foreach ($list as $key => $value) {
            # code...
            $list[$key]['buyer'] = $this->buyerArr[$value['buyer']];
        }
//        dump($list);die;
        $this->assign("list", $list);
        $this->display();
    }

    public function add() {
        if (IS_POST) {
            $data                       =   array();
            $data['buyer']              =   I('post.buyer');
            $data['goods_name']         =   I('post.goods_name');
            $data['goods_nums']         =   I('post.goods_nums');
            $data['goods_price']         =   I('post.goods_price');
            $data['give_goods_nums']    =   I('post.give_goods_nums');
            $data['goods_image']        =   I('post.goods_image');
            $data['info']               =   I('post.info');
            $data['add_time']           =   NOW_TIME;
            $data['update_time']        =   NOW_TIME;
            $res = D('goods')->add($data);
            if ($res) {
                $this->success("添加成功",U('index'));
            } else {
                $this->error("添加失败");
            }
        } else {
            
            if(!empty(C('room_price'))){
                $room_price =   floatval(C('room_price'));
            }  else {
                $room_price =   1;
            }
            $this->assign("room_price", $room_price);
            $this->display();
        }
    }

    public function edit() {
        if (IS_POST) {
            $id = I('post.id');
            $data                       =   array();
            $data['goods_name']         =   I('post.goods_name');
            $data['goods_nums']         =   I('post.goods_nums');
            $data['goods_price']         =   I('post.goods_price');
            $data['give_goods_nums']    =   I('post.give_goods_nums');
            $data['goods_image']        =   I('post.goods_image');
            $data['info']               =   I('post.info');
            $data['add_time']           =   NOW_TIME;
            $data['update_time']        =   NOW_TIME;
            $res = D('goods')->where("id = $id")->setField($data);
            if ($res) {
                $this->success("编辑成功",U('index'));
            } else {
                $this->error("编辑失败");
            }
        } else {
            $id = I('get.id');
            $info = D('goods')->where("id = $id")->find();
            $info['buyer']  =   $this->buyerArr[$info['buyer']];
            
            $this->assign("room_price", $room_price);
//            dump($info);die;
            $this->assign("info", $info);
            
            
            $this->display();
        }
    }
    
    public function detail(){
        $id = I('get.id');
        $info = D('goods')->where("id = $id")->find();
        if(!empty(C('ROOM_PRICE'))){
            $room_price =   floatval(C('ROOM_PRICE'));
        }  else {
            $room_price =   1;
        }
        $info['total_money']    =   floatval($room_price*$info['goods_nums']);
        $info['total_nums']     =   $info['goods_nums']+$info['give_goods_nums'];
    
        $this->assign("info", $info);
        $this->display();
    }

    public function del() {
        $id = (int) $_GET['id'];
        $res = D('goods')->where("id = $id")->delete();
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error("删除失败");
        }
    }

    static function getGoodsNameById($goods_id) {
        $info = D("goods")->where("id = $goods_id")->find();
        $info['goods_name'] = $info['goods_name'] ? $info['goods_name'] : '暂无商品';
        return $info['goods_name'];
    }
    
    
    /**
    * [deletePic 删除商品图片]
    * @author StanleyYuen <[350204080@qq.com]>
    */
    public function deletePic() {
        if (IS_POST) {
            $id = I('post.picId');

            $goodsPic = M('GoodsImg');
            if ($goodsPic->where(array('id'=>$id))->delete()) {
                exit(json_encode(array('isDelete'=>'1')));
            } else {
                exit(json_encode(array('isDelete'=>'0')));
            }
        } else {
            $this->error('非法访问！');
        }
    }

    /**
    * [photoUpload 商品图片上传]
    * @author TF <[2281551151@qq.com]>
    */
    public function photoUpload() {
        // 图片保存路径
        fileUpload('./Uploads/Goods/', function($e) {
            $photoUrl = $e['filePath'];
            $photoUrl = trim($photoUrl, '.');
            echo json_encode(array('error'=>'', 'msg'=>"{$photoUrl}"));
        });
    }

    /**
    * [descUploadPic 商品图片上传]
    * @author TF <[2281551151@qq.com]>
    */
    public function descUploadPic() {
        // 图片保存路径
        fileUpload('./Uploads/Goods/', function($e) {
            $e['filePath'] = trim($e['filePath'], '.');
            echo json_encode(array('error'=>0, 'url'=>$e['filePath']));
        });
    }

}

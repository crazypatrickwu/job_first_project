<?php
namespace Home\Helper;
use Home\Controller;
/**
 * TEXT模型
 */
class EventSubscribeHelper extends BaseHelper{
	
	public function wxRun(){
		//设置用户为关注状态
		$openid			= $this->WebChatCallHelper->PostObj->FromUserName;
                $public_user    =   M('public_user')->where(array('openid'=>"$openid"))->find();
                if(!empty($public_user)){
                    dblog('EventSubscribeHelper111111111');
                    $user_data  =   array();
                    $user_data['subscribe']     =   1;
                    $user_data['subscribe_time']=   NOW_TIME;
                    dblog($user_data);
                    dblog('EventSubscribeHelper4444');
                    $user_id    =   M('public_user')->where(array('openid'=>"$openid"))->save($user_data);
                    if($user_id){
                        $Rid    =   $this->Rid;
                        $where  =   array();
                        $where['uid']       =   $Rid;
                        $where['status']    =   1;
                        $where['title']     =   '首次关注';
                        $replay =   M('public_material_text')->where($where)->find();
                        if(!empty($replay) && !empty($replay['desc'])){
                            $this->WebChatCallHelper->SendText(base64_decode($replay['desc']));
                        }
                        $this->WebChatCallHelper->SendText('hi,你好');
                    }
                }  else {
                    dblog('EventSubscribeHelper222222222222');
                }
	}
}
?>
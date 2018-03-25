<?php
namespace Home\Helper;
use Home\Controller;
/**
 * TEXT模型
 */
class EventScanHelper extends BaseHelper{
	
	public function wxRun(){
                $openid			= $this->WebChatCallHelper->PostObj->FromUserName;
                $public_user    =   M('public_user')->where(array('openid'=>"$openid"))->find();
                if(!empty($public_user)){
                    dblog('EventScanHelper111111111');
                    M('public_user')->where(array('openid'=>"$openid"))->setField(array('subscribe'=>1,'subscribe_time'=>NOW_TIME));
                    $this->WebChatCallHelper->SendText('欢迎您回来');
                }  else {
                    dblog('EventScanHelper222222222');
                    if(!empty($this->WebChatCallHelper->PostObj->Ticket)){
                        $Ticket =   $this->WebChatCallHelper->PostObj->Ticket;
                        $public_qrcode  =   M('public_qrcode')->where(array('ticket'=>"$Ticket"))->find();
                        if(!empty($public_qrcode)){
                            $parame         =   json_decode($public_qrcode['parame'], true);
                            $red_id         =   !empty($parame['red_id']) ? intval($parame['red_id']) : 0;
                            $scenario_id    =   !empty($parame['scenario_id']) ? intval($parame['scenario_id']) : 0;
                            $parent_id      =   !empty($parame['parent_id']) ? intval($parame['parent_id']) : 0;
                            
                            $user_data  =   array();
                            $user_data['openid']        =   "$openid";
                            $user_data['create_time']   =   NOW_TIME;
                            $user_data['subscribe']     =   1;
                            $user_data['subscribe_time']=   NOW_TIME;
                            $user_data['red_id']        =   $red_id;
                            $user_data['scenario_id']   =   $scenario_id;
                            $user_data['parent_id']     =   $parent_id;
                            $user_id    =   M('public_user')->add($user_data);
                            if($user_id){
                                $Rid    =   $this->Rid;
                                $where  =   array();
                                $where['uid']       =   $Rid;
                                $where['status']    =   1;
                                $where['title']     =   '首次关注';
                                $replay =   M('public_material_text')->where($where)->find();
                                if(!empty($replay) && !empty($replay['desc'])){
                //                    $this->WebChatCallHelper->SendText($this->WebChatCallHelper->PostObj->FromUserName);
                                    $this->WebChatCallHelper->SendText($replay['desc']);
                                }
                                $this->WebChatCallHelper->SendText('hi,你好');
                            }
                        }
                    }
                    
                    
                }
	}
}
?>
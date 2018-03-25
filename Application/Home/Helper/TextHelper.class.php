<?php
namespace Home\Helper;
use Home\Controller;
/**
 * TEXT模型
 */
class TextHelper extends BaseHelper{
        
        public function wxRun(){
            dblog(array('TextHelper wxRun 1001','$this->WebChatCallHelper->PostObj'=>$this->WebChatCallHelper->PostObj));
                $user_keywords      = trim($this->WebChatCallHelper->PostObj->Content);
                $where              =   array();
                $where['uid']       =   $this->Rid;
                $where['status']    =   1;
                $where['keywords']  =   array('like','%'.$user_keywords.'%');
                $keywords_list      =   M('public_keywords')->where($where)->order('id DESC,match_type DESC')->select();
                if(!empty($keywords_list)){
                        foreach ($keywords_list as $key => $value) {
                            $weixin_keywords    =   array();
                            $weixin_keywords    =   explode("|", $value['keywords']);
                            if($value['match_type'] == 1){  //全字匹配
                                if(in_array($user_keywords, $weixin_keywords)){
                                    $this->keywordReply($value);
                                }
                            }  else {                       //模糊匹配
                                $this->keywordReply($value);
                            }
                        }
                }
                
                if(in_array($user_keywords, array('openid','OPENID'))){
                    $this->WebChatCallHelper->SendText($this->WebChatCallHelper->PostObj->FromUserName);
                }
                
                //自动回复
                $this->autoReply();
                $this->WebChatCallHelper->SendText(date('Y年m月d日 H:i:s'));
                
        }
        
        public function keywordReply($public_keywords){
            if(!empty($public_keywords)){
                switch ($public_keywords['reply_type']) {
                    case 0: //文本
                        $this->WebChatCallHelper->SendText(base64_decode($public_keywords['reply_text']));
                        break;
                    case 1: //图片
                        $this->WebChatCallHelper->SendImage($public_keywords['reply_media_id']);
                        $materialImage   =   M('public_material_image')->where(array('media_id'=>$public_keywords['reply_media_id']))->find();
                        if(!empty($materialImage['media_id'])){
                            $this->WebChatCallHelper->SendImage($materialImage['media_id']);
                        }
                        
                        break;
                    case 2: //图文
                        $materialNews   =   D('PublicMaterialNews')->where(array('media_id'=>$public_keywords['reply_media_id']))->find();
                        if(!empty($materialNews)){
                            $materialNews['PublicMaterialNewsDetail'] = M('PublicMaterialNewsDetail')->where(array('material'=>$materialNews['id']))->field(true)->select();
                            $sendNewsArr    =   array();
                            foreach ($materialNews['PublicMaterialNewsDetail'] as $key => $value) {
                                $sendNewsArr[$key]['title']         =   $value['title'];
                                $sendNewsArr[$key]['description']   =   $value['desc'];
                                $sendNewsArr[$key]['picurl']        =   $value['pic'];
                                $sendNewsArr[$key]['url']           =   $value['url'];
                            }
                            $this->WebChatCallHelper->SendNews($sendNewsArr);
                        }
                        break;

                    default:
                        break;
                }
            }
        }
        
        public function autoReply(){
                $Rid    =   $this->Rid;
                $where  =   array();
                $where['uid']       =   $Rid;
                $where['status']    =   1;
                $where['title']     =   '自动回复';
                $replay =   M('public_material_text')->where($where)->find();
                if(!empty($replay) && !empty($replay['desc'])){
//                    $this->WebChatCallHelper->SendText($this->WebChatCallHelper->PostObj->FromUserName);
                    $this->WebChatCallHelper->SendText(base64_decode($replay['desc']));
                }
        }
}
?>
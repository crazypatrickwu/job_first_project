<?php
namespace Home\Helper;
use Home\Controller;
/**
 * TEXT模型
 */
class EventClickHelper extends BaseHelper{
	
	public function wxRun(){
            dblog(array('EventClickHelper wxRun','$this->WebChatCallHelper->PostObj'=>$this->WebChatCallHelper->PostObj,'$this->Rid'=>$this->Rid));
                if(preg_match ('/(.*)?\|[is_touch-is_app]/i', $this->WebChatCallHelper->PostObj->EventKey)){
                        $arr=explode('|',$this->WebChatCallHelper->PostObj->EventKey);
                        list($res['callback_model'],$res['callback_desc'])=$arr;
                        $this->InterfaceTouch=$res;	
                        
                        if($this->InterfaceTouch['callback_model'] == 'news'){
                            $media_id   =   $this->InterfaceTouch['callback_desc'];
                            $materialNews   =   D('PublicMaterialNews')->where(array('media_id'=>$media_id))->relation('PublicMaterialNewsDetail')->find();
                            
                            if(!empty($materialNews)){
                                $sendNewsArr    =   array();
                                foreach ($materialNews['PublicMaterialNewsDetail'] as $key => $value) {
                                    $sendNewsArr[$key]['title']         =   $value['title'];
                                    $sendNewsArr[$key]['description']   =   $value['desc'];
                                    $sendNewsArr[$key]['picurl']        =   $value['pic'];
                                    $sendNewsArr[$key]['url']           =   $value['url'];
                                }
                            dblog(array('EventClickHelper wxRun','$sendNewsArr'=>$sendNewsArr));
                                $this->WebChatCallHelper->SendNews($sendNewsArr);
                            }
                            dblog(array('EventClickHelper wxRun','$materialNews'=>$materialNews));
                        }

                dblog(array('EventClickHelper wxRun','$this->InterfaceTouch'=>$this->InterfaceTouch));
		$this->WebChatCallHelper->SendText('EventClick');
            }
        }
}
?>
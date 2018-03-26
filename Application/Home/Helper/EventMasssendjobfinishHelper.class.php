<?php
namespace Home\Helper;
use Home\Controller;
/**
 * TEXT模型
 */
class EventMasssendjobfinishHelper extends BaseHelper{
	
	public function wxRun(){
		$this->WebChatCallHelper->SendText('EventMasssendjobfinish');
	}
}
?>
<?php
namespace Admin\Controller;
use Think\Controller;

class WeixinController extends Controller {
    public function responseMsg(){
      $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
       if (!empty($postStr)){
           $postObj      = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
           $fromUsername = $postObj->FromUserName;
           $toUsername   = $postObj->ToUserName;
           $keyword      = trim($postObj->Content);
           $time         = time();

           $textTpl      = "<xml>
                               <ToUserName><![CDATA[%s]]></ToUserName>
                               <FromUserName><![CDATA[%s]]></FromUserName>
                               <CreateTime>%s</CreateTime>
                               <MsgType><![CDATA[%s]]></MsgType>
                               <Content><![CDATA[%s]]></Content>
                               <FuncFlag>0<FuncFlag>
                             </xml>";

           if(!empty($keyword) && $keyword == "open")
           {
               $msgType    = "text";
               $contentStr = 'test';
               $resultStr  = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
               echo $resultStr;
           }
       }
    }
}

<?php
namespace Home\Helper;
use Home\Controller;
/**
 * TEXT模型
 */
class CreatemenuHelper extends BaseHelper{

	public function wxRun(){
//            dump($this->WeixinHelper);die;
		if(!empty($this->WeixinHelper->AppId) && !empty($this->WeixinHelper->AppSecret)){
			$replace			= array();
			$MenuArray			= array();
			$ApiUri				= 'http://'.WEB_DOMAIN;
			$MenuInfo			= M('public_account')->where(array('app_id'=>$this->WeixinHelper->AppId,'app_secret'=>$this->WeixinHelper->AppSecret))->getField('menu');
//			dump($MenuInfo);die;
                        if(!empty($MenuInfo)){
				$MenuInfo		= unserialize(base64_decode($MenuInfo));
				foreach($MenuInfo AS $key=>$value){
					$MenuList	= array();
					if(!empty($value['list']) && !empty($value['name'])){
						foreach($value['list'] AS $k=>$v){
							if($v['type'] == 'view'){
								$MenuList[]	= array('type'=>'view','name'=>'{MENU:'.$key.'_'.$k.'}','url'=>'{PARAM:'.$key.'_'.$k.'}',);
								if($v['linktype'] != '2'){
									$v['actionParam']=$ApiUri.'/v2/oauth/?callback='.urlencode($v['actionParam'].(preg_match('/\?/si',$v['actionParam']) ? '&' : '?'));
								}
							}else{
								$MenuList[]	= array('type'=>$v['type'],'name'=>'{MENU:'.$key.'_'.$k.'}','key'=>'{PARAM:'.$key.'_'.$k.'}',);
							}
							$replace['/\{MENU:'.$key.'\_'.$k.'\}/si']=$v['name'];
							$replace['/\{PARAM:'.$key.'\_'.$k.'\}/si']=$v['actionParam'];
						}
					}
					if(!empty($MenuList)){
						$MenuArray[]	= array('name'=>'{MENU:'.$key.'}','sub_button'=>$MenuList);
						$replace['/\{MENU:'.$key.'\}/si']=$value['name'];
					}elseif(!empty($value['name'])){
						if($value['type'] == 'view'){
							$MenuArray[]=array('type'=>'view','name'=>'{MENU:'.$key.'}','url'=>'{PARAM:'.$key.'}',);
							if($value['linktype'] != '2'){
								$value['actionParam']=$ApiUri.'/v2/oauth/?callback='.urlencode($value['actionParam'].(preg_match('/\?/si',$v['actionParam']) ? '&' : '?'));
							}
						}else{
							$MenuArray[]	= array('type'=>$value['type'],'name'=>'{MENU:'.$key.'}','key'=>'{PARAM:'.$key.'}',);
						}
						$replace['/\{MENU:'.$key.'\}/si']=$value['name'];
						$replace['/\{PARAM:'.$key.'\}/si']=$value['actionParam'];
					}
				}
				//菜单发布
				$MenuJson					= preg_replace(array_keys($replace),array_values($replace),json_encode(array('button'=>$MenuArray)));
//                                dump($MenuJson);die;
				$GetReturn					= $this->WeixinHelper->MenuCreate($MenuJson);
				if(empty($GetReturn)){
					return array('error_code'=>'0','error'=>'发布成功');
				}
				return array('error_code'=>'1','error'=>$this->getWxError(substr($GetReturn,0,strpos($GetReturn,":"))));
			}
			else{
				return array('error_code'=>'1','error'=>'菜单数据不存在');
			}
		}else{
			return array('error_code'=>'1','error'=>'账号AppId或AppSecret不存在');
		}
	}
}
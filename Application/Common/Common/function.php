<?php
/*
 * 短信接口
 */
function msg_sendcode($phone,$content){
    header("Content-Type: text/html; charset=utf-8");
    $post_data = array();
    $post_data['account'] = '4e8kr9';   //帐号
    $post_data['pswd'] = 'I9xLJwq0';  //密码
    $post_data['msg'] =urlencode($content); //短信内容需要用urlencode编码下
    $post_data['mobile'] = $phone; //手机号码， 多个用英文状态下的 , 隔开
    $post_data['product'] = ''; //产品ID
    $post_data['needstatus']=true; //是否需要状态报告，需要true，不需要false
    $post_data['extno']='';  //扩展码   可以不用填写
    $url='http://send.18sms.com/msg/HttpBatchSendSM';
    $o='';
    foreach ($post_data as $k=>$v)
    {
       $o.="$k=".urlencode($v).'&';
    }
    $post_data=substr($o,0,-1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
    $result = curl_exec($ch);
    dblog(array('msg_sendcode','$result'=>$result));
    return $result;
}
/*
 * 字符串加密
 * */
function FauthCode($string,$operation='ENCODE')
{
    $ckey_length=4;
    // 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥
    $string=isset($string)?$string:'';
    $key=md5(C('DATA_AUTH_KEY'));
    $keya=md5(substr($key,0,16));
    $keyb=md5(substr($key,16,16));
    $keyc=$ckey_length?($operation=='DECODE'?substr($string,0,$ckey_length):substr(md5(microtime()),-$ckey_length)):'';
    $cryptkey=$keya.md5($keya.$keyc);
    $key_length=strlen($cryptkey);
    $string=$operation=='DECODE'?base64_decode(substr($string,$ckey_length)):sprintf('%010d',0).substr(md5($string.$keyb),0,16).$string;
    $string_length=strlen($string);
    $result='';
    $box=range(0,255);
    $rndkey=array();
    for($i=0;$i<=255;$i++){
        $rndkey[$i]=ord($cryptkey[$i%$key_length]);
    }
    for($j=$i=0;$i<256;$i++){
        $j=($j+$box[$i]+$rndkey[$i])%256;
        $tmp=$box[$i];
        $box[$i]=$box[$j];
        $box[$j]=$tmp;
    }
    for($a=$j=$i=0;$i<$string_length;$i++){
        $a=($a+1)%256;
        $j=($j+$box[$a])%256;
        $tmp=$box[$a];
        $box[$a]=$box[$j];
        $box[$j]=$tmp;
        $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256]));
    }
    if($operation=='DECODE'){
        if((substr($result,0,10)==0||substr($result,0,10)>0)&&substr($result,10,16)==substr(md5(substr($result,26).$keyb),0,16)){
            return substr($result,26);
        }else{
            return '';
        }
    }else{
        return $keyc.str_replace('=','',base64_encode($result));
    }
}
/**
 * 判断是否微信浏览器访问
 * @return boolean 
 */
function is_weixin(){
    if( strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false ){
        return true;
    }   
    return false;
}
function iswap() {
        // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
                return true;
        }
        //此条摘自TPM智能切换模板引擎，适合TPM开发
        if(isset ($_SERVER['HTTP_CLIENT']) &&'PhoneClient'==$_SERVER['HTTP_CLIENT']){
                return true;
        }
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset ($_SERVER['HTTP_VIA'])){
                //找不到为flase,否则为true
                return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
        }
        //判断手机发送的客户端标志,兼容性有待提高
        if (isset ($_SERVER['HTTP_USER_AGENT'])) {
                $clientkeywords = array('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile');
                //从HTTP_USER_AGENT中查找手机浏览器的关键字
                if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                        return true;
                }
        }
        //协议法，因为有可能不准确，放到最后判断
        if (isset ($_SERVER['HTTP_ACCEPT'])) {
                // 如果只支持wml并且不支持html那一定是移动设备
                // 如果支持wml和html但是wml在html之前则是移动设备
                if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                        return true;
                }
        }
        return false;
}
//从服务器获取访客ip
function getip(){
    $onlineip = "";
    if(getenv(HTTP_CLIENT_IP) && strcasecmp(getenv(HTTP_CLIENT_IP), unknown)) {
        $onlineip = getenv(HTTP_CLIENT_IP);
    } elseif(getenv(HTTP_X_FORWARDED_FOR) && strcasecmp(getenv(HTTP_X_FORWARDED_FOR), unknown)) {
        $onlineip = getenv(HTTP_X_FORWARDED_FOR);
    } elseif(getenv(REMOTE_ADDR) && strcasecmp(getenv(REMOTE_ADDR), unknown)) {
        $onlineip = getenv(REMOTE_ADDR);
    } elseif(isset($_SERVER[REMOTE_ADDR]) && $_SERVER[REMOTE_ADDR] && strcasecmp($_SERVER[REMOTE_ADDR], unknown)) {
        $onlineip = $_SERVER[REMOTE_ADDR];
    }
    return safe_replace($onlineip);
}

//高危字符替换
function safe_replace($string) {
    if(is_array($string)){
        $string=implode('，',$string);
        $string=htmlspecialchars(str_shuffle($string));
    } else{
        $string=htmlspecialchars($string);
    }
    $string = str_replace('%20','',$string);
    $string = str_replace('%27','',$string);
    $string = str_replace('%2527','',$string);
    $string = str_replace('*','',$string);
    $string = str_replace('"','&quot;',$string);
    $string = str_replace("'",'',$string);
    $string = str_replace('"','',$string);
    $string = str_replace(';','',$string);
    $string = str_replace('<','&lt;',$string);
    $string = str_replace('>','&gt;',$string);
    $string = str_replace("{",'',$string);
    $string = str_replace('}','',$string);
    return $string;
}

function CurlHttp($url,$body='',$method='DELETE',$headers=array()){
    $httpinfo=array();
    $ci=curl_init();
    /* Curl settings */
    curl_setopt($ci,CURLOPT_HTTP_VERSION,CURL_HTTP_VERSION_1_0);
    curl_setopt($ci,CURLOPT_USERAGENT,'toqi.net');
    curl_setopt($ci,CURLOPT_CONNECTTIMEOUT,30);
    curl_setopt($ci,CURLOPT_TIMEOUT,30);
    curl_setopt($ci,CURLOPT_RETURNTRANSFER,TRUE);
    curl_setopt($ci,CURLOPT_ENCODING,'');
    curl_setopt($ci,CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ci,CURLOPT_HEADER,FALSE);
    switch($method){
        case 'POST':
            curl_setopt($ci,CURLOPT_POST,TRUE);
            if(!empty($body)){
                curl_setopt($ci,CURLOPT_POSTFIELDS,$body);
            }
            break;
        case 'DELETE':
            curl_setopt($ci,CURLOPT_CUSTOMREQUEST,'DELETE');
            if(!empty($body)){
                $url=$url.'?'.str_replace('amp;', '', http_build_query($body));
            }
    }
    curl_setopt($ci,CURLOPT_URL,$url);
    curl_setopt($ci,CURLOPT_HTTPHEADER,$headers);
    curl_setopt($ci,CURLINFO_HEADER_OUT,TRUE);
    $response=curl_exec($ci);
    $httpcode=curl_getinfo($ci,CURLINFO_HTTP_CODE);
    $httpinfo=array_merge($httpinfo,curl_getinfo($ci));
    curl_close($ci);
    return $response;
}
/**
 * 调用系统的API接口方法（静态方法）
 * api('User/getName','id=5'); 调用公共模块的User接口的getName方法
 * api('Admin/User/getName','id=5');  调用Admin模块的User接口
 * @param  string  $name 格式 [模块名]/接口名/方法名
 * @param  array|string  $vars 参数
 */
function api($name,$vars=array()){
    $array     = explode('/',$name);
    $method    = array_pop($array);
    $classname = array_pop($array);
    $module    = $array? array_pop($array) : 'Common';
    $callback  = $module.'\\Api\\'.$classname.'Api::'.$method;
    if(is_string($vars)) {
        parse_str($vars,$vars);
    }
    return call_user_func_array($callback,$vars);
}
/**
 * [cny 小写转大写]
 * @author TF <[2281551151@qq.com]>
 */
function cny($ns) {
    static $cnums=array("零","壹","贰","叁","肆","伍","陆","柒","捌","玖"),
        $cnyunits=array("圆","角","分"),
           $grees=array("拾","佰","仟","万","拾","佰","仟","亿");
    list($ns1,$ns2)=explode(".",$ns,2);
    $ns2=array_filter(array($ns2[1],$ns2[0]));
    $ret=array_merge($ns2,array(implode("",_cny_map_unit(str_split($ns1),$grees)),""));
    $ret=implode("",array_reverse(_cny_map_unit($ret,$cnyunits)));
    return str_replace(array_keys($cnums),$cnums,$ret);
}

function _cny_map_unit($list,$units) {
    $ul=count($units);
    $xs=array();
    foreach (array_reverse($list) as $x) {
        $l=count($xs);
        if ($x!="0" || !($l%4)) $n=($x=='0'?'':$x).($units[($l-1)%$ul]);
        else $n=is_numeric($xs[0][0])?$x:'';
        array_unshift($xs,$n);
    }
    return $xs;
}

/**
 * [search_time_format 转换搜索中的时间]
 * @author TF <[2281551151@qq.com]>
 */
function search_time_format($str) {
    $str = urldecode($str);
    return str_replace('+', ' ', $str);
}

/**
 * [checkOrderType 检查订单类型]
 * @author TF <[2281551151@qq.com]>
 */
function checkOrderType($orderSn) {
    return substr($orderSn,6,1);
}

/**
 * [getFinanceTypeString 得到财务记录类型对应文字]
 * @author TF <[2281551151@qq.com]>
 */
function getFinanceTypeString($type) {
    switch ($type) {
        case '0':
                return '普通购物佣金订单';
            break;
        case '1':
                return '礼包佣金订单';
            break;
        case '2':
                return '保证金订单';
            break;
        case '3':
                return '财务提现订单';
            break;
        case '4':
                return '待审核金额';
            break;
    }
}


/**
 * [getAgentLevelName 得到代理等级名称]
 * @author TF <[2281551151@qq.com]>
 */
function getAgentLevelName($level) {
    switch ($level) {
        case 1:
            return '盟主';
        break;

        case 2:
            return '帮主';
        break;

        case 3:
            return '美人';
        break;
    }
}


/**
 * [checkActionAuth 检查方法权限]
 * @author TF <[2281551151@qq.com]>
 */
function checkActionAuth($controllerAction) {

    static $authListCache;
    if ( empty($authListCache) ) {
        $authListCache = session('authList');
    }
    if ( is_array($controllerAction) ) {
        foreach ($controllerAction as $value) {
            if ( in_array(strtolower($value), $authListCache) ) {
                return true;
            }
        }
    } else {
        if ( in_array(strtolower($controllerAction), $authListCache) ) {
            return true;
        }
    }
}

/**
 * [checkControllerAuth 检查全控制器权限]
 * @author TF <[2281551151@qq.com]>
 */
function checkControllerAuth($controllerAction) {
    static $authListCache;
    if ( empty($authListCache) ) {
      $temp = session('authList');

      foreach ($temp as $key => $tvalue) {
        $temp[$key] = substr($tvalue, 0, strpos($tvalue, '-'));
      }

      $temp = array_unique($temp);
      $authListCache = $temp;
    }


    if ( is_array($controllerAction) ) {
      // 或条件
        foreach ($controllerAction as $value) {
            if ( in_array(strtolower($value), $authListCache) ) {
                return true;
            }
        }
    } else {
        if ( in_array(strtolower($controllerAction), $authListCache) ) {
            return true;
        }
    }
}

/**
 * [getAuthUrl 得到权限地址]
 * @author TF <[2281551151@qq.com]>
 */
function getAuthUrl($controllerAction) {
    static $authListCache;
    if ( empty($authListCache) ) {
        $authListCache = session('authList');
    }

    if ( is_array($controllerAction) ) {
        foreach ($controllerAction as $value) {
            if ( in_array(strtolower($value), $authListCache) ) {
                $url = str_replace('-', '/', $value);
                return U($url);
            }
        }
    } else {
        if ( in_array(strtolower($controllerAction), $authListCache) ) {
            $url = str_replace('-', '/', $controllerAction);
            return U($url);
        }
        return false;
    }
}


/**
 * [encrypt 系统标准加密]
 * @author TF <[2281551151@qq.com]>
 */
function encrypt($string, $length = '') {
    $crypt = md5($string);

    if ( !empty($length) ) {
        return substr($crypt, 0, $length);
    } else {
        return $crypt;
    }
}

/**
 * [time_format 标准时间格式化]
 * @author TF <[2281551151@qq.com]>
 */
function time_format($time) {
    return date('Y/m/d H:i:s', $time);
}

/**
 * [array_column 取数组某列]
 * @version array_column 需要 (PHP 5 >= 5.5.0)
 */
if ( !function_exists('array_column') ) {
    function array_column($input, $columnKey, $indexKey = NULL) {
        $columnKeyIsNumber = (is_numeric($columnKey)) ? TRUE : FALSE;
        $indexKeyIsNull    = (is_null($indexKey))     ? TRUE : FALSE;
        $indexKeyIsNumber  = (is_numeric($indexKey))  ? TRUE : FALSE;
        $result            = array();

        foreach ((array)$input AS $key => $row) {
            if ($columnKeyIsNumber) {
                $tmp = array_slice($row, $columnKey, 1);
                $tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : NULL;
            } else {
                $tmp = isset($row[$columnKey]) ? $row[$columnKey] : NULL;
            }

            if ( ! $indexKeyIsNull) {
                if ($indexKeyIsNumber) {
                    $key = array_slice($row, $indexKey, 1);
                    $key = (is_array($key) && ! empty($key)) ? current($key) : NULL;
                    $key = is_null($key) ? 0 : $key;
                } else {
                    $key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
                }
            }
            $result[$key] = $tmp;
        }
        return $result;
    }
}

/**
 * [公共文件上传]
 * @author TF <[2281551151@qq.com]>
 */
function fileUpload($savePath, $callable) {
    if ( !file_exists($savePath) ) {
        mkdir($savePath, 0700, true);
    }

    $upload            = new \Think\Upload();
    $upload->maxSize   = 3145728 ;
    $upload->exts      = array('jpg', 'gif', 'png', 'jpeg');
    $upload->rootPath  = $savePath;
    $info              = $upload->upload();
    if ( !$info ) {
        echo $upload->getError();
    } elseif ( is_callable($callable) ) {
        $keys = array_keys($info);
        $key  = $keys[0];
        $one  = $info[$key];
        $one['filePath'] = $savePath . $one['savepath'] . $one['savename'];
        $callable($one);
    }
}

// 过滤javascript代码防止xss攻击
function remove_xss($val) {
   $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
   $search = 'abcdefghijklmnopqrstuvwxyz';
   $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
   $search .= '1234567890!@#$%^&*()';
   $search .= '~`";:?+/={}[]-_|\'\\';
   for ($i = 0; $i < strlen($search); $i++) {
      $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);
      $val = preg_replace('/(�{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
   }

   $ra1 = array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
   $ra2 = array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
   $ra = array_merge($ra1, $ra2);
   $found = true;
   while ($found == true) {
      $val_before = $val;
      for ($i = 0; $i < sizeof($ra); $i++) {
         $pattern = '/';
         for ($j = 0; $j < strlen($ra[$i]); $j++) {
            if ($j > 0) {
               $pattern .= '(';
               $pattern .= '(&#[xX]0{0,8}([9ab]);)';
               $pattern .= '|';
               $pattern .= '|(�{0,8}([9|10|13]);)';
               $pattern .= ')*';
            }
            $pattern .= $ra[$i][$j];
         }
         $pattern .= '/i';
         $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2);
         $val = preg_replace($pattern, $replacement, $val);
         if ($val_before == $val) {
            $found = false;
         }
      }
   }
   return $val;
}

/**
 * [getAdBox 得到广告位信息]
 * @author TF <[2281551151@qq.com]>
 */
function getAdBox($sign) {
    $adGroupInfo = M('ad_box')->where(array('sign'=>$sign))->find();
    $adInfo      = M('ad')->field('ad_name, url, image, target')
                          ->where(array('box_id'=>$adGroupInfo['id'], 'end_time'=>array('gt' ,time())))
                          ->order('start_time DESC')
                          ->find();

    $array       = array(
        'width'  => $adGroupInfo['width'],
        'height' => $adGroupInfo['height'],
    );

    if ( empty($adInfo) ) {
        $array['ad_name'] = '无';
        $array['url']     = C('webSite');
        $array['image']   = 'http://placehold.it/' . $adGroupInfo['width'] . 'x' . $adGroupInfo['height'];
        $array['target']  = '_blank';
    } else {
        $array['ad_name']     = $adInfo['ad_name'];
        $array['url']     = $adInfo['url'];
        $array['image']   = $adInfo['image'];
        $array['target']  = $adInfo['target'];
    }

    return $array;
}

/**
 * [getArticleGroupName 获取文章分类名称]
 * @author Fu <[418382595@qq.com]>
 */
function getArticleGroupName($ids) {
    $where    = array('id'=> $ids);
    $group_id = M('article')->where($where)
                            ->field('group_id')
                            ->find();
    $where    = array('id'=> $group_id['group_id']);
    $result   = M('article_group')->where($where)
                                  ->field('group_name')
                                  ->find();
    return $result['group_name'];
}

/**
 * [getRegionName 获取地区名称]
 * @author Fu <[418382595@qq.com]>
 */
function getRegionName($ids){
    $where    = array('id' => $ids);

    $result   = M('region')->where($where)->field('region_name')->find();
    return $result['region_name'];
}

/**
 * [mySubstr 截取字符串(中文截取)]
 * @author Fu <[418382595@qq.com]>
 */
function mySubstr($str,$len=20,$suffix='...',$charset='UTF-8'){
    $substr = mb_substr($str,0,$len,$charset);
    if($substr != $str)$substr .= $suffix;
    return $substr;
}

/**
 * [getGoodsList 得到广告位信息]
 * @author TF <[2281551151@qq.com]>
 */
function getGoodsList($ids) {
    $where  = array('id'=>array('in', $ids));
    $result = M('goods')->where($where)->select();
    return $result;
}

function curl($url, $data = '', $requestType = 'post') {
    //初始化curl
    $ch = curl_init();
    //设置超时
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    if (strtolower($requestType) == 'post') {
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );
    }

    $return = curl_exec($ch);
    curl_close($ch);
    return $return;
}

/**
 * [sendMSg 发送短信息]
 * @author TF <[2281551151@qq.com]>
 */
function sendMSg($phone, $msg, $tempId='46547') {
     // 初始化REST SDK
     $accountSid   = '8a48b55150b23c5b0150b2bca90b0326';
     $accountToken = '091cc8bf72ca48b5bb9d87c19cd4a79d';
     $appId        = '8a48b55150b86ee80150bd1a59ea0d1c';
     $serverIP     = 'sandboxapp.cloopen.com';
     $serverPort   = '8883';
     $softVersion  = '2013-12-26';

     import('Vendor.Sms');
     $rest = new \Common\Library\Sms($serverIP,$serverPort,$softVersion);
     $rest->setAccount($accountSid,$accountToken);
     $rest->setAppId($appId);

     // 发送模板短信
     $returnData = array('to' => $phone);
     $result = $rest->sendTemplateSMS($phone, $msg, $tempId);
     if($result == NULL ) {
         $returnData['err'] = 1;
         break;
     }
     if($result->statusCode!=0) {
         $returnData['err'] = 1;
         $returnData['statusCode'] = $result->statusCode;
         $returnData['statusMsg'] = $result->statusMsg;
         //TODO 添加错误处理逻辑
     }else{
         // 获取返回信息
         $returnData['err'] = 0;
         $smsmessage = $result->TemplateSMS;
         $returnData['dateCreated'] = $smsmessage->dateCreated;
         $returnData['smsMessageSid'] = $smsmessage->smsMessageSid;
         //TODO 添加成功处理逻辑
     }
     return $returnData;
}



function export_csv($filename,$data) {
    header("Content-type:text/csv");   
    header("Content-Disposition:attachment;filename=".$filename);   
    header('Cache-Control:must-revalidate,post-check=0,pre-check=0');   
    header('Expires:0');   
    header('Pragma:public');

    foreach ($data as $key => $value) {
        echo implode(',', $value) . "\n";
    }
}  

/**
 * 系统非常规MD5加密方法
 * @param  string $str 要加密的字符串
 * @return string 
 */
function think_ucenter_md5($str, $key = 'xinmiao2016!QAZ@WSX'){
    return '' === $str ? '' : md5(sha1($str) . $key);
}

/**
 * 系统加密方法
 * @param string $data 要加密的字符串
 * @param string $key  加密密钥
 * @param int $expire  过期时间 (单位:秒)
 * @return string 
 */
function think_ucenter_encrypt($data, $key, $expire = 0) {
    $key  = md5($key);
    $data = base64_encode($data);
    $x    = 0;
    $len  = strlen($data);
    $l    = strlen($key);
    $char =  '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x=0;
        $char  .= substr($key, $x, 1);
        $x++;
    }
    $str = sprintf('%010d', $expire ? $expire + time() : 0);
    for ($i = 0; $i < $len; $i++) {
        $str .= chr(ord(substr($data,$i,1)) + (ord(substr($char,$i,1)))%256);
    }
    return str_replace('=', '', base64_encode($str));
}

/**
 * 系统解密方法
 * @param string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param string $key  加密密钥
 * @return string 
 */
function think_ucenter_decrypt($data, $key){
    $key    = md5($key);
    $x      = 0;
    $data   = base64_decode($data);
    $expire = substr($data, 0, 10);
    $data   = substr($data, 10);
    if($expire > 0 && $expire < time()) {
        return '';
    }
    $len  = strlen($data);
    $l    = strlen($key);
    $char = $str = '';
    for ($i = 0; $i < $len; $i++) {
        if ($x == $l) $x = 0;
        $char  .= substr($key, $x, 1);
        $x++;
    }
    for ($i = 0; $i < $len; $i++) {
        if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
            $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
        }else{
            $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
        }
    }
    return base64_decode($str);
}

/**
 * 生成随机数
 * @param number $length 字符串长度
 * @param number $type 字符串类型
 * @return string
 */
function randomString($length, $type = 0) {
    $arr  = array(
    0 => '0123456789',
    1 => 'abcdefghjkmnpqrstuxy',
    2 => 'ABCDEFGHJKMNPQRSTUXY',
    3 => '123456789abcdefghjkmnpqrstuxy',
    4 => '123456789ABCDEFGHJKMNPQRSTUXY',
    5 => 'abcdefghjkmnpqrstuxyABCDEFGHJKMNPQRSTUXY',
    6 => '123456789abcdefghjkmnpqrstuxyABCDEFGHJKMNPQRSTUXY',
    7 => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
    );
    $chars = $arr[$type] ? $arr[$type] : $arr[7];
    $hash  = '';
    $max   = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
    return $hash;
}

//数据库写入，快捷调试
function dblog($data){
    //数据库
    $data   = array($data,date('Y-m-d H:i:s',NOW_TIME));
    $data   = is_array($data) ? json_encode($data) : $data;
    M('msg')->add(array('msg'=>$data,'date'=>date('Y-m-d H:i:s',NOW_TIME)));
}

//身份证号码验证
function is_qili_card($id){
    $id = strtoupper($id); 
    $regx = "/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/"; 
    if(!preg_match($regx, $id)) 
    { 
        return FALSE; 
    }else{
        return true;
    }
}
<?php
namespace Common\Controller;
use Think\Controller;
class WeixinController extends Controller {
    /**
     * [access_token 获取access_token]
     * @author TF <[2281551151@qq.com]>
     */
    static function access_token() {
        $result = S('czm_access_token');
        $result = json_decode($result, true);

        if ( $result['expire_time'] <= time() ) {
            $weixinAppID     = C('weixinAppID');
            $weixinAppSecret = C('weixinAppSecret');

            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$weixinAppID}&secret={$weixinAppSecret}";

            $result = self::curl($url, '', 'post');
            $result = json_decode($result, true);


            if ( $result['access_token'] ) {
                $result['expire_time'] = time() + 7000;
                // 进行缓存
                S('czm_access_token', json_encode($result));
            }
        }

        $access_token = $result['access_token'];
        return $access_token;
    }

    /**
     * [clearAccessToken 清楚clearAccessToken]
     * @author TF <[2281551151@qq.com]>
     */
    static function clearAccessToken() {
        $result = S('czm_access_token');
        $result = json_decode($result, true);
        $result['expire_time'] = time() - 1;
        S('czm_access_token', json_encode($result));
    }

    /**
     * [OauthCode 获取OauthCode码]
     * @author TF <[2281551151@qq.com]>
     */
    static function OauthCode($code) {
        $weixinAppID     = C('weixinAppID');
        $weixinAppSecret = C('weixinAppSecret');
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$weixinAppID}&secret={$weixinAppSecret}&code={$code}&grant_type=authorization_code";

        $code = self::curl($url, '', 'get');
        $code = json_decode($code, true);
        return $code;
    }

    /**
     * [refresh_token 恢复token]
     * @author TF <[2281551151@qq.com]>
     */
    static function refresh_token($refresh_token) {
        $weixinAppID = C('weixinAppID');
        $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$weixinAppID}&grant_type=refresh_token&refresh_token={$refresh_token}";
        $result = self::curl($url, '', 'GET');
        return json_decode($result);
    }

    /**
     * [getUserInfo 得到用户信息]
     * @author TF <[2281551151@qq.com]>
     */
    static function getUserInfo($openid) {
        $access_token = self::access_token();
        $url          = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $result       = self::curl($url, '', 'GET');
        $resultArray  = json_decode($result, true);
        if ( !empty($resultArray['errcode']) ) {
            self::clearAccessToken();
            return self::getUserInfo($openid);
        }
        return $resultArray;
    }

    /**
     * [authGetUserInfo 授权得到用户信息]
     * @author TF <[2281551151@qq.com]>
     */
    static function authGetUserInfo($access_token, $openid) {
        $url         = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $result      = self::curl($url, '', 'GET');
        $resultArray = json_decode($result, true);
        return $resultArray;
    }

    /**
     * [jumpOauth 跳转到oauth]
     * @author TF <[2281551151@qq.com]>
     */
    static function jumpOauth() {
        $uri   = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $appId = C('weixinAppID');
        $url   = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appId}&redirect_uri={$uri}&response_type=code&scope=snsapi_base&state=0#wechat_redirect";

        header("LOCATION:{$url}");
        exit();
    }

    /**
     * [authjumpOauth 授权跳转到oauth]
     * @author TF <[2281551151@qq.com]>
     */
    static function authjumpOauth() {
        $uri   = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

        $appId = C('weixinAppID');
        $url   = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$appId}&redirect_uri={$uri}&response_type=code&scope=snsapi_userinfo&state=0#wechat_redirect";

        header("LOCATION:{$url}");
        exit();
    }


    /**
     * [postToUser 发送消息到用户]
     * @author TF <[2281551151@qq.com]>
     */
    static function postToUser($msg) {
        $access_token = self::access_token();

        $url    = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
        $result = self::curl($url, $msg, 'post');
    }

    /**
     * [createMenu 创建菜单]
     * @author TF <[2281551151@qq.com]>
     */
    static function createMenu() {
      $string = <<<EOF
      {
      "button":[{
                    "name":"平台登录",
                    "sub_button":[
                        {
                            "type":"view",
                            "name":"买家登录",
                            "url":"http://m.hzjianjiao.com/home/"
                        },
                        {
                            "type":"view",
                            "name":"代理登录",
                            "url":"http://m.hzjianjiao.com/center"
                        }
                    ]
                },
                {     
                    "name":"代理服务",
                    "sub_button":[
                        {
                            "type":"view",
                            "name":"尖叫问答",
                            "url":"http://mp.weixin.qq.com/s?__biz=MzIyNDAzNzM3NA==&mid=401498269&idx=1&sn=15803fd3111315601015da698d1ace51#rd"
                        },
                        {
                            "type":"view",
                            "name":"产品信息",
                            "url":"http://mp.weixin.qq.com/s?__biz=MzIyNDAzNzM3NA==&mid=401498313&idx=1&sn=a80eb4d22cada4f64a4608b4921717a4#rd"
                        },
                        {
                            "type":"view",
                            "name":"物流发货",
                            "url":"http://mp.weixin.qq.com/s?__biz=MzIyNDAzNzM3NA==&mid=401498329&idx=1&sn=13a380721b7de5df20a4349755515c52#rd"
                        },
                        {
                            "type":"view",
                            "name":"售后服务",
                            "url":"http://mp.weixin.qq.com/s?__biz=MzIyNDAzNzM3NA==&mid=401498346&idx=1&sn=1a5c0627b312c821bc6169d8d5eb9a16#rd"
                        },
                        {
                            "type":"view",
                            "name":"操作指南",
                            "url":"http://mp.weixin.qq.com/s?__biz=MzIyNDAzNzM3NA==&mid=502575103&idx=1&sn=b3d8c356e93fc445daed5682ca2a9430#rd"
                        }]
                },
                {
                    "name":"关于我们",
                    "sub_button":[
                        {
                            "type":"view",
                            "name":"我要开店",
                            "url":"http://mp.weixin.qq.com/s?__biz=MzIyNDAzNzM3NA==&mid=401499632&idx=1&sn=2b45f87f0e3d13a679730fd092fb3a20#rd"
                        },
                        {
                            "type":"view",
                            "name":"尖叫云商动画",
                            "url":"http://v.qq.com/boke/page/x/0/u/x0178qjw6bu.html"
                        },
                        {
                            "type":"view",
                            "name":"开店礼包",
                            "url":"http://mp.weixin.qq.com/s?__biz=MzIyNDAzNzM3NA==&mid=502575104&idx=1&sn=2f7f9cf18e40181ff9930b22d349fc24#rd"
                        },
                        {
                            "type":"view",
                            "name":"提现解析",
                            "url":"http://mp.weixin.qq.com/s?__biz=MzIyNDAzNzM3NA==&mid=401902504&idx=1&sn=406384bc507bfb0b3b6935f781f8937b#rd"
                        }]
                    }
                ]}
EOF;

        $token  = self::access_token();
        $result = self::curl("https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$token}", $string);
        dump($result);
    }

    /**
     * [curl curl数据]
     * @author TF <[2281551151@qq.com]>
     */
    static function curl($url, $data = '', $requestType = 'post') {
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
}

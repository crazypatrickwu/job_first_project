<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <script>!function(n){var e=n.document,t=e.documentElement,i=750,d=i/100,o="orientationchange"in n?"orientationchange":"resize",a=function(){var n=t.clientWidth||320;n>750&&(n=750),t.style.fontSize=n/d+"px"};e.addEventListener&&(n.addEventListener(o,a,!1),e.addEventListener("DOMContentLoaded",a,!1))}(window);</script>
    <title>代理登录</title>
    <link rel="stylesheet" href="/Public/Home/Agent/css/taizhou.css">
</head>
<body>
<div>
    <div class="form_box login_box" id="app">
        <div class="login_banner">
            <img src="/Public/Home/Agent/img/login.png"/>
        </div>
        <form id="login" method="post" onsubmit="return false;">
            <div class="form_item login_name">
                <div class="item"><label>手机号码</label><input type="tel" name="account" id="account" /></div>
                <div class="item"><label>密码</label><input type="password" name="password" id="password" /></div>
            </div>
            <div class="form_btn">
                <button id="sub_btn">登录</button>
            </div>
        </form>
        
        <div class="forget_pwd">
            <font style="font-size:1.5em;color:#ABB1B6;">初始密码为12345</font>
            <!-- <a href="<?php echo U('Login/forgetPwd',array('step'=>1));?>">忘记密码?</a> -->
        </div>
    </div>
</div>
</body>
<script type="text/javascript" src="/Public/Home/Agent/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="/Public/Home/Agent/js/public.js"></script>
<script type="text/javascript">
    $(function(){
        //登录
        var sub_btn_status  =   false;
        $("#sub_btn").on('click',function(){
            if (sub_btn_status) {
                return;
            };
            var account     =   $("#account").val();
            var password    =   $("#password").val();

            if (account == '') {
                webTip('手机号码不得为空');
                return false;
            };
            if (password == '') {
                webTip('密码不得为空');
                return false;
            };

            var _url = "<?php echo U('index');?>";
            var _param = new Object();
                _param.account  =   account;
                _param.password =   password;
            sub_btn_status  =   true;
            $.post(_url,_param,function(res){

                if (res.code == 1) {
                    webTip(res.msg);
                    setTimeout(function(){
                       window.location.href    =   "<?php echo U('Home/Index/index');?>";
                    },1000);
                }else{
                    webTip(res.msg);
                    sub_btn_status  =   false;
                    setTimeout(function(){
                        window.location.reload();
                    },1000);
                    return false;
                }
            },'json');
        });
    })
</script>
<script src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script type="text/javascript">
        var AppId = "<?php echo ($signPackage['appid']); ?>";
        var timestamp = "<?php echo ($signPackage['timestamp']); ?>";
        var noncestr = "<?php echo ($signPackage['noncestr']); ?>";
        var signature = "<?php echo ($signPackage['signature']); ?>";
        // alert("AppId:"+AppId+"\n"+"timestamp:"+timestamp+"\n"+"noncestr:"+noncestr+"\n"+"signature:"+signature+"\n");
        wx.config({
            debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: AppId, // 必填，公众号的唯一标识
            timestamp: timestamp, // 必填，生成签名的时间戳
            nonceStr: noncestr, // 必填，生成签名的随机串
            signature: signature, // 必填，签名，见附录1
            jsApiList: ['onMenuShareAppMessage', 'onMenuShareTimeline'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
        });

        wx.hideMenuItems({
            menuList: ['menuItem:copyUrl','menuItem:share:email'] // 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮，所有menu项见附录3
        });

        wx.ready(function() {

            var shareTitle = "桐庐十三道";
            var shareDesc = "桐庐十三道,等你来战！";
            if (shareDesc == '') {
                shareDesc = shareTitle;
            }
            var shareLink = 'http://' + document.domain + "/Home/Download/index.html";
            var shareImgUrl = 'http://' + document.domain + "/Public/Game/download/img/logo_share.png";
            //     alert("shareTitle："+shareTitle+"\n"+"shareDesc:"+shareDesc+"\n"+"shareLink:"+shareLink+"\n"+"shareImgUrl："+shareImgUrl+"\n");
            wx.onMenuShareAppMessage({
                title: shareTitle,
                desc: shareDesc,
                link: shareLink,
                imgUrl: shareImgUrl,
                trigger: function(res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                    //                alert('用户点击发送给朋友');
                },
                success: function(res) {
                    //                alert('已分享');
                    // var _url = "<?php echo U('Index/wxshare');?>";
                    // var _param = new Object();
                    // _param.option = "goods";
                    // _param.id = "<?php echo ($info["id"]); ?>";
                    // $.post(_url, _param, function(res) {
                    //     showMessage(res.msg);
                    // }, 'json');
                },
                cancel: function(res) {
                    //                alert('已取消');
                },
                fail: function(res) {
                    //                alert(JSON.stringify(res));
                }
            });

            // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
            wx.onMenuShareTimeline({
                title: shareTitle,
                link: shareLink,
                imgUrl: shareImgUrl,
                trigger: function(res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                    //                alert('用户点击分享到朋友圈');
                },
                success: function(res) {
                    //                alert('已分享');
                    // var _url = "<?php echo U('Index/wxshare');?>";
                    // var _param = new Object();
                    // _param.option = "goods";
                    // _param.id = "<?php echo ($info["id"]); ?>";
                    // $.post(_url, _param, function(res) {
                    //     showMessage(res.msg);
                    // }, 'json');
                },
                cancel: function(res) {
                    //                alert('已取消');
                },
                fail: function(res) {
                    //                alert(JSON.stringify(res));
                }
            });

        });
    </script>
</html>
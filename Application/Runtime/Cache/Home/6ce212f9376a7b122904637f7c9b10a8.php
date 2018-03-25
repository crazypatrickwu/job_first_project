<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no" />
    <title>下载</title>
</head>
<link rel="stylesheet" type="text/css" href="/Public/Game/download/css/main.css"/>
<script type="text/javascript">
    !function(n){var e=n.document,t=e.documentElement,i=750,d=i/100,o="orientationchange"in n?"orientationchange":"resize",
            a=function(){var n=t.clientWidth||320;n>750&&(n=750),t.style.fontSize=n/d+"px"};e.addEventListener&&(n.addEventListener(o,a,!1),
            e.addEventListener("DOMContentLoaded",a,!1))}(window);
    sessionStorage.setItem("yes",'1');
</script>
<body>
    <div class="down_bg" style="background:url(/Public/Game/download/img/qili_bg.png);width:100%;height:100%;background-size:cover;">
        <!-- <div class="down_logo"><img src="/Public/Game/download/img/logo.png"/></div> -->
        <div class="down_btns">
            <!-- <div class="ios"><a href="/Public/Game/download/img/ios.gif" style="margin-bottom: 0.3rem;">IOS操作教程</a></div> -->
            <div class="ios"><a href="/Public/Game/download/img/ios.gif"><img src="/Public/Game/download/img/btn_ios_tutorials.png"></a></div>
            <div class="ios"><a href="https://www.pgyer.com/bxghios"><img src="/Public/Game/download/img/btn_android.png"></a></div>
            <div class="ios"><a href="https://www.pgyer.com/bxghios"><img src="/Public/Game/download/img/btn_ios.png"></a></div>
        </div>
        <!-- <div class="footer">
            <div class="footer_line">
                <span class="pad_rig"></span><span>日期:2017-12-1</span>
            </div>
        </div> -->
    </div>
</body>
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
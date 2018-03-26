<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <script>!function(n){var e=n.document,t=e.documentElement,i=750,d=i/100,o="orientationchange"in n?"orientationchange":"resize",a=function(){var n=t.clientWidth||320;n>750&&(n=750),t.style.fontSize=n/d+"px"};e.addEventListener&&(n.addEventListener(o,a,!1),e.addEventListener("DOMContentLoaded",a,!1))}(window);</script>
    <title>我的信息</title>
    <link href="/Public/Home/Agent/css/LArea.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Public/Home/Agent/css/taizhou.css">
</head>
<body style="background: #fff;">
<div>
    <div class="form_box form_box2">
        <form class="stdform stdform2" action="<?php echo U('Agent/agentInfo');?>" method="post">
        <div class="form_item form_my_item">
            <div class="item my_item address"><label>姓名</label><input type="text" id="nickname" name="nickname" value="<?php echo ($agentInfo['nickname']); ?>"/></div>
            <div class="item my_item address"><label>微信号</label><input type="text" id="wechat_id" name="wechat_id" value="<?php echo ($agentInfo['wechat_id']); ?>"/></div>
            <div class="item my_item address"><label>房卡数量</label><input type="text" value="<?php echo ($agentInfo['room_card']); ?>" readonly /></div>
            <div class="item my_item address"><label>手机号码</label><input type="text" value="<?php echo ($agentInfo['phone']); ?>" readonly /></div>
            <div class="item my_item address"><label>邀请码</label><input type="text" value="<?php echo ($agentInfo['invitation_code']); ?>" readonly /></div>
            <div class="item my_item address"><label>会员数</label><input type="text" value="<?php echo ($agentInfo['player_num']); ?>" readonly /></div>
            <!-- <div class="item my_item address"><label>代理等级</label><input type="text" value="<?php echo ($agentInfo['level']); ?>" readonly /></div> -->
            <!-- <div class="item my_item address"><label>选择地区</label>
                <input id="demo1" name="demo1" type="text" readonly="" placeholder="浙江杭州" />
                <input name="pca" id="value1" type="hidden" />
                <i><img src="/Public/Home/Agent/img/right.png"/></i>
            </div> -->
            <div class="item my_item address"><label>详细地址</label><input type="text" name="address" value="<?php echo ($agentInfo['address']); ?>"/></div>
            <div class="item my_item address"><label>服务城市</label><input type="text" name="service_city" value="<?php echo ($agentInfo['service_city']); ?>"/></div>
            <div class="item my_item address"><label>修改密码</label>
                <a href="<?php echo U('Agent/editPwd');?>"><i><img src="/Public/Home/Agent/img/right.png"/></i></a>
            </div>
            <div class="item my_item address"><label>银行信息</label>
                <a href="<?php echo U('Agent/agentWithdrawalsAccount');?>"><i><img src="/Public/Home/Agent/img/right.png"/></i></a>
            </div>
        </div>
        <div class="form_btn form_btn_out two">
            <input type="hidden" name="id" value="<?php echo ($agentInfo['id']); ?>" />
            <button>保存</button>
        </div>
        </form>
        <!-- 遮盖层   -->
        <div class="xba-blank"></div>
        <div class="modify_pwd">
            <div class="modify_pwd_box">
                <div class="model_title">
                    验证登录密码
                </div>
                <div class="model_content">为了保障您的资金安全，操作前需要验证您的密码</div>
                <div class="model_inp"><input type="password"/></div>
                <div class="model_btns clearfix">
                    <div class="left rig">
                        <button class="btn_yes">确定</button>
                    </div>
                    <div class="left">
                        <button class="btn_no">取消</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/Public/Home/Agent/js/LAreaData1.js"></script>
<script src="/Public/Home/Agent/js/LArea.js"></script>
<script>
    var area1 = new LArea();
    area1.init({
        'trigger': '#demo1', //触发选择控件的文本框，同时选择完毕后name属性输出到该位置
        'valueTo': '#value1', //选择完毕后id属性输出到该位置
        'keys': {
            id: 'id',
            name: 'name'
        }, //绑定数据源相关字段 id对应valueTo的value属性输出 name对应trigger的value属性输出
        'type': 1, //数据源类型
        'data': LAreaData //数据源
    });
</script>
<script src="/Public/Home/Agent/js/jquery.min.js"></script>
<script type="text/javascript">
    $(function(){

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
</body>
</html>
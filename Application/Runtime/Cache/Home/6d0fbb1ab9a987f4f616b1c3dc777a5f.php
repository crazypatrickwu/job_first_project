<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <script>!function(n){var e=n.document,t=e.documentElement,i=750,d=i/100,o="orientationchange"in n?"orientationchange":"resize",a=function(){var n=t.clientWidth||320;n>750&&(n=750),t.style.fontSize=n/d+"px"};e.addEventListener&&(n.addEventListener(o,a,!1),e.addEventListener("DOMContentLoaded",a,!1))}(window);</script>
    <title>银行信息</title>
    <link href="/Public/Home/Agent/css/LArea.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/Public/Home/Agent/css/taizhou.css">
    <style type="text/css">
    .ckf-select{
        width: 4rem;
        border: none;
        padding: 0;
        vertical-align: middle;
        line-height: 0.36rem;
        font-size: 1.4em;
        position: absolute;
        top: 0.23rem;
        right: 0.45rem;
        color: #999;
        text-align: right;
        -webkit-appearance: none;
        direction: rtl;
        background: #fff;
    }
    .ckf-select option{
        direction: rtl;
    }
    </style>
</head>
<body style="background: #fff;">
<div id="app">
        <div class="form_box form_box2">
            <div class="form_item form_my_item form_my_item2">
                <div class="item my_item address"><b>*</b><label>真实姓名</label><input type="text" id="truename" name="truename" placeholder="请输入真实姓名" value="<?php echo ($agent_withdrawals_account['truename']); ?>" maxlength="10"/></div>
                <div class="item my_item address"><b>*</b><label>支付宝账号</label><input type="text" id="alipay_account" name="alipay_account" placeholder="请输入支付宝账号" value="<?php echo ($agent_withdrawals_account['alipay_account']); ?>"  maxlength="20"/></div>
                <div class="item my_item address"><b>*</b><label>提现银行</label>
                    <!-- <input type="tel" id="bank_name" name="bank_name" placeholder="请输入提现银行"/> -->
                    <select class="ckf-select" id="bank_name" name="bank_name">
                        <option value="">请选择提现银行</option>
                        <?php if(is_array($bankList)): $i = 0; $__LIST__ = $bankList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$bank): $mod = ($i % 2 );++$i; if($bank == $agent_withdrawals_account['bank_name']): ?><option value="<?php echo ($bank); ?>" selected><?php echo ($bank); ?></option>
                            <?php else: ?>
                            <option value="<?php echo ($bank); ?>"><?php echo ($bank); ?></option><?php endif; endforeach; endif; else: echo "" ;endif; ?>
                    </select>
                </div>
                <div class="item my_item address"><b>*</b><label>支行名称</label><input type="text" id="bank_subbranch" name="bank_subbranch" placeholder="请输入支行名称" value="<?php echo ($agent_withdrawals_account['bank_subbranch']); ?>"  maxlength="20" /></div>
                <div class="item my_item address"><b>*</b><label>银行卡号码</label><input type="tel" id="bank_card" name="bank_card" placeholder="请输入银行卡号码"  value="<?php echo ($agent_withdrawals_account['bank_card']); ?>"   maxlength="20"/></div>
                <div class="item my_item address"><b>*</b><label>登录密码</label><input type="password" id="agent_password" name="agent_password" placeholder="请输入登录密码"/></div>
            </div>
            <div class="form_btn form_btn_out two">
                <input type="hidden" name="id" value="0" />
                <button id="btnAgentAdd">保存</button>
            </div>
        </div>
</div>
<script type="text/javascript" src="/Public/Home/Agent/js/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="/Public/Home/Agent/js/public.js"></script>
<script>


    //ajax入库判断
    $(function(){

        var btnAgentAdd = false;
        $("#btnAgentAdd").on('click',function(){
            if (btnAgentAdd) {
                return;
            };
            var truename = $("#truename").val();
            var alipay_account = $("#alipay_account").val();
            var bank_name = $("#bank_name").val();
            var bank_subbranch = $("#bank_subbranch").val();
            var bank_card = $("#bank_card").val();
            var agent_password = $("#agent_password").val();
            if (truename == '') {
                webTip('请输入真实姓名');
                return false;
            };
            if (alipay_account == '') {
                webTip('请输入正确的支付宝账号');
                return false;
            };
            if (bank_name == '') {
                webTip('请输入正确的银行名称');
                return false;
            };
            if (bank_subbranch == '') {
                webTip('请输入正确的支行名称');
                return false;
            };
            if (bank_card == '') {
                webTip('请输入正确的银行卡号');
                return false;
            };
            if (agent_password == '') {
                webTip('请输入正确的登录密码');
                return false;
            };

            var _url = "<?php echo U('agentWithdrawalsAccount');?>";
            var _param = new Object();
                _param.truename = truename;
                _param.alipay_account = alipay_account;
                _param.bank_name = bank_name;
                _param.bank_subbranch = bank_subbranch;
                _param.bank_card = bank_card;
                _param.agent_password = agent_password;
                // console.log(_param);
                // return;
            btnAgentAdd = true;
            $.post(_url,_param,function(res){
                if (res.code == 1) {
                    webTip(res.msg,function(){
                        window.location.href = "<?php echo U('Index/index');?>";
                    },3000);
                    return true;
                }else{
                    webTip(res.msg,function(){
                        window.location.href = window.location.href;
                    },3000);
                    return true;
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
</body>
</html>
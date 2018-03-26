<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <script>!function(n){var e=n.document,t=e.documentElement,i=750,d=i/100,o="orientationchange"in n?"orientationchange":"resize",a=function(){var n=t.clientWidth||320;n>750&&(n=750),t.style.fontSize=n/d+"px"};e.addEventListener&&(n.addEventListener(o,a,!1),e.addEventListener("DOMContentLoaded",a,!1))}(window);</script>
    <title>设置新密码</title>
    <link rel="stylesheet" href="/Public/Home/Agent/css/taizhou.css">
    <script type="text/javascript" src="/Public/Home/Agent/js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="/Public/Home/Agent/js/public.js"></script>
</head>
<body>
<div>
    <div class="form_box" id="app">
        <div class="form_item form_item_top">
            <div class="item new"><label>原密码</label><input type="password" id="old_pwd" placeholder="请输入原密码"/></div>
            <div class="item new"><label>新密码</label><input type="password" id="new_pwd1" placeholder="请输入新密码"/></div>
            <div class="item new"><label>确认密码</label><input type="password" id="new_pwd2" placeholder="请重新输入新密码"/></div>
        </div>
        <div class="form_btn">
            <button id="sub_btn">确认提交</button>
        </div>
    </div>
</div>
</body>
<script type="text/javascript">
    $(function(){
        $("#sub_btn").on('click',function(){
            var old_pwd   =   $("#old_pwd").val();
            if (old_pwd == '') {
                webTip('请输入原密码');
                return false;
            };
            var new_pwd1   =   $("#new_pwd1").val();
            if (new_pwd1 == '') {
                webTip('请输入新密码');
                return false;
            };
            var new_pwd2   =   $("#new_pwd2").val();
            if (new_pwd2 == '') {
                webTip('请确认密码');
                return false;
            };
            if (new_pwd1 != new_pwd2) {
                webTip('两次密码输入不一致');
                return false;
            };
            var _url = "<?php echo U('Agent/editPwd');?>";
            var _param = new Object();
                _param.old_password     =   old_pwd;
                _param.new_password     =   new_pwd1;
                _param.re_new_password  =   new_pwd2;
            $.post(_url,_param,function(res){
                if (res.code == '1') {
                    webTip(res.msg);
                    if (res.url != '') {
                        setTimeout(function(){
                            window.location.href = res.url;
                        },1500);
                    };
                }else{
                    webTip(res.msg);
                    if (res.url != '') {
                        setTimeout(function(){
                            window.location.href = res.url;
                        },1500);
                    }else{
                        setTimeout(function(){
                            window.location.href = window.location.href;
                        },1500);
                    }
                    return false;
                }
            },'json');
        });
    });
</script>
</html>
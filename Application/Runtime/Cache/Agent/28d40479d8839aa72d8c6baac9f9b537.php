<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>代理登录</title>
    <link rel="stylesheet" href="/Public/Agent/css/style.default.css" type="text/css" />
    <link href="/Public/fxw.ico" mce_href="/Public/fxw.ico" rel="bookmark" type="image/x-icon" /> 
    <link href="/Public/fxw.ico" mce_href="/Public/fxw.ico" rel="icon" type="image/x-icon" /> 
    <link href="/Public/fxw.ico" mce_href="/Public/fxw.ico" rel="shortcut icon" type="image/x-icon" />
    <!--[if IE 9]>
        <link rel="stylesheet" media="screen" href="/Public/Agent/css/style.ie9.css"/>
    <![endif]-->
    <!--[if IE 8]>
        <link rel="stylesheet" media="screen" href="/Public/Agent/css/style.ie8.css"/>
    <![endif]-->
    <!--[if lt IE 9]>
    	<script src="/Public/Agent/js/plugins/css3-mediaqueries.js"></script>
    <![endif]-->
</head>

<body class="loginpage">
	<div class="loginbox">
    	<div class="loginboxinner">
            <div class="logo">
                <h1 class="logo">
                    <!--<img src="/Public/Common/images/logo.png" width="340px" height="64px" />-->
                    <?php echo (APP_NAME); ?>代理系统
                </h1>
                <!--<span class="slogan"> Version 1.0</span>-->
            </div><!--logo-->
            
            <br clear="all" /><br />
            
            <div class="nousername">
				<div class="loginmsg">密码不正确.</div>
            </div><!--nousername-->
            
            <div class="nopassword">
				<div class="loginmsg">密码不正确.</div>
                <div class="loginf">
                    <div class="thumb"><img src="/Public/Admin/images/logo.png" /></div>
                    <div class="userlogged">
                        <h4></h4>
                        <a href="javascript:;">密码不正确<span></span>?</a> 
                    </div>
                </div>
            </div>
            
            <form id="login" action="" method="post">
            	
                <div class="username">
                	<div class="usernameinner">
                    	<input type="text" name="account" id="account" />
                    </div>
                </div>
                
                <div class="password">
                	<div class="passwordinner">
                    	<input type="password" name="password" id="password" />
                    </div>
                </div>
                
                <input type="submit" class="submit_btn" value="登录">
                
                <div class="keep">
                    <label>
                        <input type="checkbox" value="1" checked="checked" name="rememberPassword" />保存一周
                    </label>
                </div>
            
            </form>
            
        </div><!--loginboxinner-->
    </div><!--loginbox-->

    <script type="text/javascript" src="/Public/Agent/js/plugins/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="/Public/Agent/js/plugins/fxw-base.js"></script>
</body>
</html>
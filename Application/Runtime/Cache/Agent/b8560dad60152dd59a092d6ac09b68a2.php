<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>控制台-<?php echo (APP_NAME); ?>后台管理系统</title>
    <link rel="stylesheet" href="/Public/Agent/css/style.default.css" type="text/css" />
    <link rel="shortcut icon" href="/Uploads/favicon.ico" type="image/x-icon" />
    
</head>

<body class="withvernav">
    <div class="bodywrapper">
        <div class="topheader">
            <div class="left">
                <h1 class="logo">
                    <a href="<?php echo U('Index/statistics');?>" style="color: #b5afb1;line-height: 60px;">
                        <!--<img src="/Public/Common/images/logo.png" height="40px" />-->
                        <?php echo (APP_NAME); ?>代理系统
                    </a>
                </h1>

                <ul>
                    <li class="right">欢迎你 <?php echo session('agentAccount');?></li>
                </ul>
            </div>
        </div>
        
        <div class="header agent">
        	<ul class="headermenu">
<!--	<li class="<?php if(in_array(CONTROLLER_NAME, array('Index'))): ?>current<?php endif; ?>">
		<a href="<?php echo U('Index/statistics');?>">
			<span class="icon icon-chart"></span>
			<span class="tet">控制台</span>
		</a>
		<em></em>
	</li>-->

	<li class="<?php if(in_array(CONTROLLER_NAME, array('Agent'))): ?>current<?php endif; ?>">
		<a href="<?php echo U('Agent/agentInfo');?>">
			<!--<span class="icon icon-chart"></span>-->
			<span class="tet">代理首页</span>
		</a>
		<em></em>
	</li>
	<li class="<?php if(in_array(CONTROLLER_NAME, array('Player'))): ?>current<?php endif; ?>">
		<a href="<?php echo U('Player/players');?>">
			<!-- <span class="icon icon-chart"></span> -->
			<span class="tet">玩家管理</span>
		</a>
		<em></em>
	</li>
	<!-- <li class="<?php if(in_array(CONTROLLER_NAME, array('Recharge'))): ?>current<?php endif; ?>">
		<a href="<?php echo U('Recharge/myRechargeRecored');?>">
			<span class="icon icon-chart"></span>
			<span class="tet">房卡统计</span>
		</a>
		<em></em>
	</li> -->
	<!-- <li class="<?php if(in_array(CONTROLLER_NAME, array('Goods'))): ?>current<?php endif; ?>">
		<a href="<?php echo U('Goods/index');?>">
			<span class="icon icon-chart"></span>
			<span class="tet">商品套餐</span>
		</a>
		<em></em>
	</li> -->
	<!-- <li class="<?php if(in_array(CONTROLLER_NAME, array('Order', 'GiftOrder'))): ?>current<?php endif; ?>">
		<a href="<?php echo U('Order/orderList');?>">
			<span class="icon icon-flatscreen"></span>
			<span class="tet">购卡订单</span>
		</a>
		<em></em>
	</li> -->

	<li>
		<a href="<?php echo U('Login/logout');?>">
			<!--<span class="icon icon-exit"></span>-->
			<span class="tet">退出登录</span>
		</a>
		<em></em>
	</li>
</ul>
        </div>
        <div class="main-date-lr">
          <div class="vernav2 iconmenu">
            
	<ul>
	<li<?php if(in_array(CONTROLLER_NAME, array('Index'))): echo chr(32);?>class="current"<?php endif; ?>>
		<a class="date-tit sys-tj" href="<?php echo U('Index/statistics');?>" class="addons">控制台</a>
		<ul class="Jcon-ctr">
			<li class="<?php if(in_array(ACTION_NAME, array('statistics'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Index/statistics');?>">统计</a>
			</li>
		</ul>
	</li>
</ul>

            <a class="togglemenu"></a>
            <br /><br />
        </div>
        <div class="centercontent">
            
<div id="contentwrapper" class="contentwrapper">
	<!-- 商品信息 -->
	<table cellpadding="0" cellspacing="0" border="0" class="stdtable">
		<tr>
			<th colspan="6">
				系统信息
			</th>
		</tr>

		<tr>
	 	<?php if(is_array($info)): $keys = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($keys % 3 );++$keys; if($mod == 0 && $keys != 1): ?></tr><tr><?php endif; ?>
          	<th width="225px" align="right"><?php echo ($key); ?>:</th>
          	<td><?php echo ($v); ?></td><?php endforeach; endif; else: echo "" ;endif; ?>
        </tr>

	</table>
	<br />
</div>

        </div>
        </div>
    </div>
    
    <script type="text/javascript" src="/Public/Agent/js/plugins/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="/Public/Agent/js/plugins/fxw-base.js"></script>
    <script type="text/javascript" src="/Public/Agent/js/plugins/pop_window.js"></script>
    <script type="text/javascript">
        function msgBox(title, content, time) {
            var _title = title ? title : '提示';
            var _time = time ? time : 1500;

            var html='<div class="title">' + _title + '</div><div>' + content + '</div>';
            popbox(html);
            setTimeout(function() {
                window.location.reload();
            }, _time);
        }
    </script>
    
</body>
</html>
<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>申请提现-<?php echo (APP_NAME); ?>后台管理系统</title>
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
	
	<li<?php if(in_array(CONTROLLER_NAME, array('Agent'))): echo chr(32);?>class="current"<?php endif; ?>>
		<a class="date-tit sys-tj" href="<?php echo U('Agent/agentInfo');?>" class="addons">我的信息</a>
		<ul class="Jcon-ctr">
			<li class="<?php if(in_array(ACTION_NAME, array('agentInfo'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Agent/agentInfo');?>">基本信息</a>
			</li>
			<li class="<?php if(in_array(ACTION_NAME, array('editPwd'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Agent/editPwd');?>">修改密码</a>
			</li>
			<li class="<?php if(in_array(ACTION_NAME, array('agentWithdrawalsAccount'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Agent/agentWithdrawalsAccount');?>">银行信息</a>
			</li>
		</ul>
	</li>

	<li<?php if(in_array(CONTROLLER_NAME, array('Agent'))): echo chr(32);?>class="current"<?php endif; ?>>
		<a class="date-tit sys-tj" href="<?php echo U('Agent/agentList');?>" class="addons">代理管理</a>
		<ul class="Jcon-ctr">
			<li class="<?php if(in_array(ACTION_NAME, array('agentList'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Agent/agentList');?>">我的代理</a>
			</li>
			<li class="<?php if(in_array(ACTION_NAME, array('addAgent'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Agent/addAgent');?>">添加代理</a>
			</li>
			<!-- <li class="<?php if(in_array(ACTION_NAME, array('getGebate'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Agent/getGebate');?>">返利统计</a>
			</li> -->
			<li class="<?php if(in_array(ACTION_NAME, array('agentRebate'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Agent/agentRebate',array('type'=>2));?>">可提现记录</a>
			</li>
		</ul>
	</li>


<!--	<?php if($_SESSION['agentId']== 1): ?><li<?php if(in_array(CONTROLLER_NAME, array('GiftOrder'))): echo chr(32);?>class="current"<?php endif; ?>>
		<a class="date-tit sys-tj" href="<?php echo U('GiftOrder/giftOrderList');?>" class="addons">礼包订单管理</a>
		<ul class="Jcon-ctr">
			<li class="<?php if(in_array(ACTION_NAME, array('giftOrderList'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('GiftOrder/giftOrderList');?>">礼包订单</a>
			</li>
		</ul>
	</li><?php endif; ?>-->
</ul>

            <a class="togglemenu"></a>
            <br /><br />
        </div>
        <div class="centercontent">
            
    <div class="pageheader">
        <h1 class="pagetitle">申请提现</h1>
        <span class="pagedesc"></span>
    </div>
    <div id="contentwrapper" class="contentwrapper">
        <form class="stdform stdform2"  onsubmit="return checkform();" method="post">
            <div class="line-dete">
                <label>您的账户余额</label>
                <span class="field">
                    <?php echo ((isset($agent_info['available_balance']) && ($agent_info['available_balance'] !== ""))?($agent_info['available_balance']):'0.00'); ?>（元）
                </span>
            </div>

            <div class="line-dete">
                <label>申请金额</label>
                <span class="field">
                    <input style="display: none;" type="text" name="pay_nums"/>
                    <input type="text" id="pay_nums" name="pay_nums" class="smallinput" placeholder="请输入提现金额" />（元）
                </span>
            </div>
            <div class="line-dete">
                <label>账户密码</label>
                <span class="field">
                    <input style="display: none;" type="password" name="agent_password"/>
                    <input type="password" id="agent_password" name="agent_password" class="smallinput" placeholder="请输入您的登录密码" />
                </span>
            </div>
            <div class="line-dete">
                <label style="color:red;">注意：</label>
                <span class="field">
                    1、每笔提现收取固定手续费5元
                    <br>
                    2、可提现时间在上午9点到下午4点之间，超过这短时间不可提现
                    <br>
                    3、满500元可提现
                </span>
            </div>
            <div class="line-dete">
                <label></label>
                <span class="field">
                    <input type="submit" class="big-btn stdbtn" value="确认"  />
                    <input type="button" class="big-btn stdbtn" onclick="window.history.back(-1);" value="返回" />
                </span>
            </div>
        </form>
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
    
    <script type="text/javascript">
        var add_status = false;
        function checkform(){
            if (add_status) {
                return;
            };
            var pay_nums = $("#pay_nums").val();
            if(pay_nums == ''){
                alert('请输入提现金额');
                return false;
            }
            if (Number(pay_nums) < 1) {
                alert('账户余额不足1元，不能申请提现');
                return false;
            };
            if ((money%100) != 0) {
                webTip('提现金额为100的倍数');
                return false;
            };
            add_status = true;
        }
    </script>

</body>
</html>
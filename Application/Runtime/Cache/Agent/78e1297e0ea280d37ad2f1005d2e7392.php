<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>可提现记录-<?php echo (APP_NAME); ?>后台管理系统</title>
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
        <h1 class="pagetitle">可提现记录</h1>
        <span class="pagedesc"></span>
    </div>
    <div id="contentwrapper" class="contentwrapper">
        <div>
            <form action="" metod="get">
                <!--玩家ID&nbsp;:&nbsp;<input type="text" name="userid" id="userid" value="<?php echo remove_xss(I('get.userid'));?>">-->
                &nbsp;&nbsp;
                时间&nbsp;:&nbsp;<input type="text" name="start_time" id="start_time" value="<?php echo remove_xss(search_time_format(I('get.start_time')));?>" class="sang_Calender">-<input type="text" name="end_time" id="end_time" value="<?php echo remove_xss(search_time_format(I('get.end_time')));?>" class="sang_Calender">
                <input type="submit" value="查询" class="stdbtn">
                <!--<input type="submit" name="daochu" value="导出" class="stdbtn">-->
                <p></p>
                <p></p>
            </form>
        </div>
        <p></p>
        <table cellpadding="0" cellspacing="0" border="0" class="stdtable stdtablequick">
            <tr>
                <th width="10%">编号</th>
                <th>代理人</th>
                <th>提成金额</th>
                <th>描述</th>
                <th>时间</th>
            </tr>
            <?php $cols = 5; ?>
            <?php if(empty($myRechargeRecored)): ?><tr>
                    <td colspan="<?php echo ($cols); ?>">暂无记录~！</td>
                </tr>
                <?php else: ?>
                <?php if(is_array($myRechargeRecored)): $i = 0; $__LIST__ = $myRechargeRecored;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$recored): $mod = ($i % 2 );++$i;?><tr>
                        <td class="center"><?php echo ($recored['id']); ?></td>
                        <td class="center"><?php echo ($recored['Agent']['phone']); ?>（ID：<?php echo ($recored['Agent']['id']); ?>）</td>
                        <td class="center"><?php echo ($recored['money']); ?>（元）</td>
                        <td class="center"><?php echo ($recored['desc']); ?></td>
                        <td class="center"><?php echo (time_format($recored['dateline'])); ?></td>
                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                <tr>
                    <td colspan="<?php echo ($cols); ?>">
                        <div class="page-box"><?php echo ($show); ?></div>
                    </td>
                </tr><?php endif; ?>
            </tbody>
        </table>
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
    
    <script type="text/javascript" src="/Public/Admin/js/datetime.js"></script>

</body>
</html>
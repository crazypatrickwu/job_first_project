<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>购买房卡-<?php echo (APP_NAME); ?>后台管理系统</title>
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
	<li<?php if(in_array(CONTROLLER_NAME, array('Player'))): echo chr(32);?>class="current"<?php endif; ?>>
		<a class="date-tit sys-tj" href="<?php echo U('Player/players');?>" class="addons">玩家管理</a>
		<ul class="Jcon-ctr">
			<li class="<?php if(in_array(ACTION_NAME, array('players'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Player/players');?>">玩家列表</a>
			</li>
			<!-- <li class="<?php if(in_array(ACTION_NAME, array('playersRecored'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Player/playersRecored',array('type'=>0,'buyer'=>'player'));?>">充值记录</a>
			</li> -->


			<li class="<?php if(in_array(ACTION_NAME, array('playersBuyInsurescore'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Player/playersBuyInsurescore');?>">购买旗力币</a>
			</li>


			<li class="<?php if(in_array(ACTION_NAME, array('playersBuyScore'))): ?>on<?php endif; ?>">
				<a href="<?php echo U('Player/playersBuyScore');?>">购买房卡</a>
			</li>
		</ul>
	</li>
</ul>

            <a class="togglemenu"></a>
            <br /><br />
        </div>
        <div class="centercontent">
            
    <div class="pageheader">
        <h1 class="pagetitle">购买房卡</h1>
        <span class="pagedesc"></span>
    </div>
    <div id="contentwrapper" class="contentwrapper">
        <div>
            <form action="" metod="get">
                玩家ID&nbsp;:&nbsp;<input type="text" name="userid" id="userid" value="<?php echo remove_xss(I('get.userid'));?>">
                &nbsp;
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
                <th>充值编号</th>
                <th>交易单号</th>
                <th>玩家ID</th>
                <th>充值数量</th>
                <th>旗力币消耗</th>
                <th>支付状态</th>
                <th>支付时间</th>
                <th>充值时间</th>
            </tr>

            <?php if(empty($user_recharge_recored_list)): ?><tr>
                    <td colspan="9">暂无记录！</td>
                </tr>
                <?php else: ?>
                <?php if(is_array($user_recharge_recored_list)): $i = 0; $__LIST__ = $user_recharge_recored_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user_recharge_recored): $mod = ($i % 2 );++$i;?><tr>
                        <td class="center"><?php echo ($user_recharge_recored['id']); ?></td>
                        <td class="center"><?php echo ($user_recharge_recored['order_sn']); ?></td>
                        <td class="center"><?php echo ($user_recharge_recored['tag']); ?></td>
                        <td class="center"><?php echo ($user_recharge_recored['uid']); ?></td>
                        <td class="center"><?php echo ($user_recharge_recored['nums']); ?>（个）</td>
                        <td class="center"><?php echo (intval($user_recharge_recored['total_price'])); ?>（个）</td>
                        <td class="center">
                        <?php if($user_recharge_recored['ispay'] == 1): ?>已支付
                        <?php else: ?>
                                未付款<?php endif; ?> 
                        </td>
                        <td class="center">
                            <?php if($user_recharge_recored['ispay'] == 1): echo (time_format($user_recharge_recored['pay_time'])); ?>
                            <?php else: ?>
                                    --<?php endif; ?> 
                            
                        </td>
                        <td class="center"><?php echo (time_format($user_recharge_recored['create_time'])); ?></td>
                    </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                <tr>
                    <td colspan="9">
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
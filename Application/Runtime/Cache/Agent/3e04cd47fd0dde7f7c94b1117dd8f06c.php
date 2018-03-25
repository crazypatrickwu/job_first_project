<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>添加代理-<?php echo (APP_NAME); ?>后台管理系统</title>
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
        <h1 class="pagetitle">添加代理</h1>
        <span class="pagedesc"></span>
    </div>
    <div id="contentwrapper" class="contentwrapper">
        <form class="stdform stdform2" action="<?php echo U('Agent/addAgent');?>" method="post">
            <div class="line-dete">
                <label>新代理昵称</label>
                <span class="field">
                    <input type="text" id="nickname" name="nickname" class="smallinput" />
                </span>
            </div>
            <div class="line-dete">
                <label>新代理手机号</label>
                <span class="field">
                    <input type="text" id="phone" name="phone" class="smallinput"  />
                </span>
            </div>
            <div class="line-dete">
                <label>新代理微信号</label>
                <span class="field">
                    <input type="text" id="wechat_id" name="wechat_id" class="smallinput" />
                </span>
            </div>

            <div class="line-dete">
                <label>是否锁定</label>
                <span class="field">
                    <input type="radio" name="is_lock" value="1" />是
                    <input type="radio" name="is_lock" value="0" checked="checked" />否
                </span>
            </div>
            <div class="line-dete">
                <label></label>
                <span class="field">
                    <input type="hidden" name="id" value="0" />
                    <input type="submit" class="big-btn stdbtn" value="保存" />
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
    
    <script type="text/javascript" src="/Public/Admin/js/ajaxfileupload.js"></script>
    <script type="text/javascript">
                        var getZoneAddress = "<?php echo U('Agent/getChildZone');?>";
                        var zoneData = [];
                        //初始化
                        $("#province").change(function() {
                            var id = $(this).find("option:selected").attr('data-id')
                            var fullElement = $("#city");
                            if (zoneData[id] != undefined) {
                                fullData(zoneData[id], fullElement);
                            } else {
                                $.ajax({
                                    url: getZoneAddress,
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {pid: id}
                                }).done(function(data) {
                                    zoneData[id] = data;
                                    fullData(zoneData[id], fullElement);
                                });
                            }
                        });

                        // 填充数据
                        function fullData(data, element, name) {
                            var string = [];
                            for (var i in data) {
                                string.push('<option data-id="' + data[i].id + '" value="' + data[i].region_name + '">' + data[i].region_name + '</option>');
                            }
                            element.html(string.join(''));
                            element.trigger("change");
                        }

                        // 省列表改变
                        $('.ip-shop-dizi').on('change', "#province", function() {
                            var id = $(this).find("option:selected").attr('data-id')
                            var fullElement = $("#city");

                            if (zoneData[id] != undefined) {
                                fullData(zoneData[id], fullElement);
                            } else {
                                $.ajax({
                                    url: getZoneAddress,
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {pid: id}
                                }).done(function(data) {
                                    zoneData[id] = data;
                                    fullData(zoneData[id], fullElement);
                                });
                            }
                        });

                        // 市列表改变
                        $('.ip-shop-dizi').on('change', "#city", function() {
                            var id = $(this).find("option:selected").attr('data-id');
                            var fullElement = $("#county");

                            if (zoneData[id] != undefined) {
                                fullData(zoneData[id], fullElement);
                            } else {
                                $.ajax({
                                    url: getZoneAddress,
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {pid: id}
                                }).done(function(data) {
                                    if (data.length <= 0) {
                                        fullElement.remove();
                                    } else {
                                        zoneData[id] = data;
                                        fullData(zoneData[id], fullElement);
                                    }
                                });
                            }
                        });

                        $("#province").trigger('change');

    </script>

</body>
</html>
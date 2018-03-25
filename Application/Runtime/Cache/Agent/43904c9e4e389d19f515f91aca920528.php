<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>基本信息-<?php echo (APP_NAME); ?>后台管理系统</title>
    <link rel="stylesheet" href="/Public/Agent/css/style.default.css" type="text/css" />
    <link rel="shortcut icon" href="/Uploads/favicon.ico" type="image/x-icon" />
    
    <link rel="stylesheet" href="/Public/Admin/css/jquery.datetimepicker.css" type="text/css" />

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
        <h1 class="pagetitle">基本信息</h1>
        <span class="pagedesc"></span>
    </div>
    <div id="contentwrapper" class="contentwrapper">
        <form class="stdform stdform2" action="<?php echo U('Agent/agentInfo');?>" method="post">
            <div class="line-dete">
                <label>昵称</label>
                <span class="field">
                    <input type="text" id="nickname" name="nickname" class="smallinput" value="<?php echo ($agentInfo['nickname']); ?>" />
                </span>
            </div>
            
            <div class="line-dete">
                <label>手机号</label>
                <span class="field">
                    <input type="text" class="smallinput" value="<?php echo ($agentInfo['phone']); ?>" readonly />
                </span>
            </div>

            <div class="line-dete">
                <label>微信号</label>
                <span class="field">
                    <input type="text" id="wechat_id" name="wechat_id" class="smallinput" value="<?php echo ($agentInfo['wechat_id']); ?>" />
                </span>
            </div>
            
            <div class="line-dete">
                <label>邀请码</label>
                <span class="field">
                    <input type="text" class="smallinput" value="<?php echo ($agentInfo['invitation_code']); ?>" readonly />
                </span>
            </div>
            <!-- <div class="line-dete">
                <label>房卡数量</label>
                <span class="field">
                    <input type="text" class="smallinput" value="<?php echo ($agentInfo['room_card']); ?>" readonly />（个）
                </span>
            </div> -->
            
            <div class="line-dete">
                <label>累计收益</label>
                <span class="field">
                    <input type="text" class="smallinput accumulated_income" value="<?php echo ((isset($agentInfo['accumulated_income']) && ($agentInfo['accumulated_income'] !== ""))?($agentInfo['accumulated_income']):'0'); ?>" readonly />（元）
                </span>
            </div>
            
            <div class="line-dete">
                <label>账户余额</label>
                <span class="field">
                    <input type="text" class="smallinput available_balance" value="<?php echo ((isset($agentInfo['available_balance']) && ($agentInfo['available_balance'] !== ""))?($agentInfo['available_balance']):'0'); ?>" readonly />（元）
                    <input type="button" value="申请提现" class="stdbtn applyWithdrawals">
                </span>
            </div>

            <!-- <div class="line-dete">
                <label>所在区域</label>
                <span class="field">
                    <div class="ip-shop-dizi">
                        <select name="province" id="province" class="address-select Jselect">
                            <?php if(is_array($province)): $i = 0; $__LIST__ = $province;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$z1): $mod = ($i % 2 );++$i;?><option data-id="<?php echo ($z1['id']); ?>" value="<?php echo ($z1['region_name']); ?>"<?php if($agentInfo['province'] == $z1['region_name']): echo chr(32);?>selected="selected"<?php endif; ?>><?php echo ($z1['region_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select> 
                        <select name="city" id="city" class="address-select Js  elect">
                            <?php if(is_array($city)): $i = 0; $__LIST__ = $city;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$z2): $mod = ($i % 2 );++$i;?><option data-id="<?php echo ($z2['id']); ?>" value="<?php echo ($z2['region_name']); ?>"<?php if($agentInfo['city'] == $z2['region_name']): echo chr(32);?>selected="selected"<?php endif; ?>><?php echo ($z2['region_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select> 
                        <select name="county" id="county" class="address-select Jselect">
                            <?php if(is_array($county)): $i = 0; $__LIST__ = $county;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$z3): $mod = ($i % 2 );++$i;?><option data-id="<?php echo ($z3['id']); ?>" value="<?php echo ($z3['region_name']); ?>"<?php if($agentInfo['county'] == $z3['region_name']): echo chr(32);?>selected="selected"<?php endif; ?>><?php echo ($z3['region_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
                        </select> 
                    </div>
                </span>
            </div>
            <div class="line-dete">
                <label>详细地址</label>
                <span class="field">
                    <input type="text" name="address" class="smallinput"  value="<?php echo ($agentInfo['address']); ?>" />
                </span>
            </div>
            <div class="line-dete">
                <label>服务城市</label>
                <span class="field">
                    <input type="text" name="service_city" class="smallinput"  value="<?php echo ($agentInfo['service_city']); ?>" />
                </span>
            </div> -->
            <div class="line-dete">
                <label></label>
                <span class="field">
                    <input type="hidden" name="id" value="<?php echo ($agentInfo['id']); ?>" />
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
    var id = $("#province").find("option:selected").attr('data-id')
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

    $(".applyWithdrawals").on('click',function(){
            var available_balance = $(".available_balance").val();
            if (Number(available_balance) < 1) {
                alert('账户余额不足1元，不能申请提现');
                return false;
            };
            window.location.href = "<?php echo U('applyWithdrawals');?>";
    });
    </script>

</body>
</html>
<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>游戏币统计-<?php echo (APP_NAME); ?></title>
    <link rel="stylesheet" href="/Public/Admin/css/style.default.css" type="text/css" />
    <link rel="shortcut icon" href="/Uploads/favicon.ico" type="image/x-icon" />
    
</head>

<body class="withvernav">
    <div class="bodywrapper">
        <div class="topheader">
            <div class="left">
                <h1 class="logo">
                    <a href="<?php echo U('Index/statistics');?>" style="color:#0099cc;line-height: 60px;">
                        <!--<img src="/Public/Common/images/logo.png" height="40px" />-->
                        <?php echo (APP_NAME); ?>后台管理系统
                    </a>
                </h1>

                <ul class="right">
                    <li>欢迎你 <?php echo (session('adminAccount')); ?></li>
                </ul>
            </div>
        </div>
        
        <div class="header">
        	﻿<ul class="headermenu">
        <li class="<?php if(in_array(CONTROLLER_NAME, array('Index'))): ?>current<?php endif; ?>">
            <a href="<?php echo U('Index/statistics');?>">
                <span class="tet">控制台</span>
            </a>
            <em></em>
        </li>

        <?php if(is_array($admin_menu_list)): $i = 0; $__LIST__ = $admin_menu_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$top_menu): $mod = ($i % 2 );++$i;?><li class="<?php if(in_array(CONTROLLER_NAME, array($top_menu['m_controller']))): ?>current<?php endif; ?>">
                <a href="/Xadmin/<?php echo ($top_menu['name']); ?>">
                    <span class="tet"><?php echo ($top_menu['title']); ?></span>
                </a>
                <em></em>
            </li><?php endforeach; endif; else: echo "" ;endif; ?>

        <li>
            <a href="<?php echo U('Login/logout');?>">
                <span class="tet">退出登录</span>
            </a>
            <em></em>
        </li>
</ul>

        </div>
        <div class="main-date-lr">
            <div class="vernav2 iconmenu">
                
    <ul>
	<?php if(is_array($left_menu)): $i = 0; $__LIST__ = $left_menu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$big_menu): $mod = ($i % 2 );++$i;?><li>
			<a class="date-tit sys-tj" href="/Xadmin/<?php echo ($big_menu['big']['name']); ?>" class="addons"><?php echo ($big_menu['big']['title']); ?></a>
			<ul class="Jcon-ctr">
				<?php if(is_array($big_menu['son'])): $i = 0; $__LIST__ = $big_menu['son'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$son_menu): $mod = ($i % 2 );++$i;?><li<?php if(ACTION_NAME == $son_menu['m_action']): echo chr(32);?>class="on"<?php endif; ?>>
						<a href="/Xadmin/<?php echo ($son_menu['name']); ?>"><?php echo ($son_menu['title']); ?></a>
					</li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		</li><?php endforeach; endif; else: echo "" ;endif; ?>
</ul>

                <a class="togglemenu"></a>
                <br /><br />
            </div>
            <div class="centercontent">
                
    <style>
        .chart-head-count{
            background: #444444;
            line-height: 60px;
            width: 240px;
            display: block;
            text-align: center;
            color: #fff;
            float:left;
            margin-left:20px;
            margin-top:20px;
        }
    </style>
    <div class="pageheader">
        <h1 class="pagetitle">游戏币统计</h1>
        <span class="pagedesc"></span>
    </div>
    <div id="contentwrapper" class="contentwrapper">
        <div id="container0" style="width: 80%; margin: 0">
            <div class="chart-head-count">
                <h2><?php echo ($result["qilibiTotalCount"]); ?></h2>
                旗力币总量
            </div>
            <div class="chart-head-count">
                <h2><?php echo ($result["roomcardTotalCount"]); ?></h2>
                房卡总量
            </div>
            <div class="chart-head-count">
                <h2><?php echo ($result["rmbTotalSum"]); ?></h2>
                充值总金额
            </div>
            <div class="chart-head-count">
                <h2><?php echo ($result["roomcardConsumeTotalCount"]); ?></h2>
                房卡消耗总量
            </div>
            <div class="chart-head-count">
                <h2><?php echo ($result["giveRoomcardTotalCount"]); ?></h2>
                赠送房卡总量
            </div>
        </div>
        
        <div id="container1" style="height: 400px;width: 80%; margin: 150px 0"></div>
        <!--<br><br><br>-->
        <div id="container2" style="height: 400px;width: 80%; margin: 150px 0"></div>
    </div>

            </div>
        </div>
    </div>
    
    <script type="text/javascript" src="/Public/Admin/js/plugins/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/plugins/fxw-base.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/plugins/pop_window.js"></script>
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
        $(".togglemenu").click(function(){
            $(".Jcon-ctr").toggle();
        });
    </script>
    
    <script type="text/javascript" src="/Public/Admin/js/datetime.js"></script>
    <script src="http://cdn.hcharts.cn/highcharts/highcharts.js"></script>
    <script src="http://cdn.hcharts.cn/highcharts/modules/exporting.js"></script>
    <script>
        
        $(function () {
            
            var result =   $.parseJSON('<?php echo ($chartList); ?>');
//            console.log(jresult);return;
            var dateArr = result.dateArr;
            var transactionCountArr = result.transactionCountArr;
            var wxuserCountArr = result.wxuserCountArr;
            var categories  = dateArr;
            $('#container1').highcharts({
                chart: {
                    type: 'line'
                },
                title: {
                    text: '玩家统计（近30天）'
                },
                subtitle: {
                    text: '数据来源: 旗力游戏数据平台'
                },
                xAxis: {
                    categories:categories
                },
                yAxis: {
                    title: {
                        text: '数据报表'
                    }
                },
                plotOptions: {
                    line: {
                        dataLabels: {
                            enabled: true          // 开启数据标签
                        },
                        enableMouseTracking: false // 关闭鼠标跟踪，对应的提示框、点击事件会失效
                    }
                },
                series: [{
                    name: '总玩家数',
                    data: wxuserCountArr
                }]
            });
            
            $('#container2').highcharts({
                chart: {
                    type: 'line'
                },
                title: {
                    text: '交易统计（近30天）'
                },
                subtitle: {
                    text: '数据来源: 旗力游戏数据平台'
                },
                xAxis: {
                    categories:categories
                },
                yAxis: {
                    title: {
                        text: '数据报表'
                    }
                },
                plotOptions: {
                    line: {
                        dataLabels: {
                            enabled: true          // 开启数据标签
                        },
                        enableMouseTracking: false // 关闭鼠标跟踪，对应的提示框、点击事件会失效
                    }
                },
                series: [{
                    name: '总交易量',
                    data: transactionCountArr
                }]
            });
        });

    </script>

</body>
</html>
<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>首页-<?php echo (APP_NAME); ?></title>
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
                
<div class="pageheader">
    <h1 class="pagetitle">权限编辑</h1>
    <span class="pagedesc"></span>
</div>

<form action="<?php echo U('Admin/editRolePower');?>" method="post">
<input type="hidden" name="id" id="id" value="<?php echo remove_xss(I('get.id'));?>" />
<div class="big-all-sel">
	<input type="checkbox" id="JBigAllBtn">
	<label for="JBigAllBtn" class="big-all-font">全选/全不选</label>
</div>
<div id="contentwrapper" class="contentwrapper" style="padding-top: 0px; width:800px;">
	<?php if(is_array($rules)): $i = 0; $__LIST__ = $rules;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$rl): $mod = ($i % 2 );++$i;?><div style="width:100%; padding-bottom: 20px; float:left; ">
		<div class="contenttitle2" style="float:left;position: relative">
			<label class="lab_chack">
				<input type="checkbox"  name="rules[]" <?php if(in_array($rl['title']['id'], $authGroup)): ?>checked<?php endif; ?> value="<?php echo ($rl['title']['id']); ?>">
				<h3><?php echo ($rl['title']['title']); ?></h3>
			</label>
        </div>

		<ul style="list-style:none; float:left; width: 100%">
			<?php if(is_array($rl['rules'])): $i = 0; $__LIST__ = $rl['rules'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$ru): $mod = ($i % 2 );++$i;?><li style="float:left; margin-right:10px; width: 150px;">
					<label for="r_<?php echo ($ru['id']); ?>">
						<input class="sel-btn" type="checkbox" id="r_<?php echo ($ru['id']); ?>" name="rules[]" <?php if(in_array($ru['id'], $authGroup)): ?>checked<?php endif; ?> value="<?php echo ($ru['id']); ?>" />
						<?php echo ($ru['title']); ?>
					</label>
				</li><?php endforeach; endif; else: echo "" ;endif; ?>
		</ul>
	</div><?php endforeach; endif; else: echo "" ;endif; ?>

	<div class="submit-box" style="clear:both;">
		<input type="submit" class="stdbtn btn_orange big-btn" value="保存" />&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="button" class="stdbtn btn_orange big-btn" onclick="window.history.back(-1);" value="返回" class="stdbtn" />
	</div>
</div>
</form>


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
    
	<script type="text/javascript">
		$('.lab_chack').change(function() {
			if($(this).hasClass('on')){
				$(this).removeClass('on');
				$(this).parent().next().find('.sel-btn').prop("checked", false);  
			}else{
				$(this).addClass('on');
				$(this).parent().next().find('.sel-btn').prop("checked", true); 
			}
		});
		$('#JBigAllBtn').click(function() {
			if($(this).is(":checked")){
				$('#contentwrapper').find('input[type=checkbox]').prop("checked", true);  
			}else{
				$('#contentwrapper').find('input[type=checkbox]').prop("checked", false); 
			}			
		});
	</script>

</body>
</html>
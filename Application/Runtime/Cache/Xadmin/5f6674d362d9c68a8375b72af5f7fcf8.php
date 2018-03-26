<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>玩家冲卡-<?php echo (APP_NAME); ?></title>
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
        <h1 class="pagetitle">玩家列表</h1>
        <span class="pagedesc"></span>
    </div>
    <div id="contentwrapper" class="contentwrapper">
        <div>
            <form action="" metod="get">
                玩家ID&nbsp;:&nbsp;<input type="text" name="userid" id="userid" value="<?php echo remove_xss(I('get.userid'));?>">
                &nbsp;
                <input type="hidden" name="p" value="1">
                <input type="submit" value="查询" class="stdbtn">
                <!--<input type="submit" name="daochu" value="导出" class="stdbtn">-->
                <p></p>
                <p></p>
            </form>
        </div>
        <p></p>
        <table cellpadding="0" cellspacing="0" border="0" class="stdtable stdtablequick">
            <tr>
                <th width="5%">玩家ID</th>
                <th width="10%">玩家昵称</th>
                <th width="10%">旗力币</th>
                <th width="10%">房卡数量</th>
                <th width="10%">邀请码</th>
                <th width="15%">实名认证</th>
                <th width="15%">加入时间</th>
                <th>操作</th>
            </tr>
            <?php $cols=8; ?>
            <?php if(empty($game_user_list)): ?><tr>
                    <td colspan="<?php echo ($cols); ?>">没有玩家列表~！</td>
                </tr>
                <?php else: ?>
                <?php if(is_array($game_user_list)): $i = 0; $__LIST__ = $game_user_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$game_user): $mod = ($i % 2 );++$i;?><tr>
                        <td class="center"><?php echo ($game_user['userid']); ?></td>
                        <td class="center"><?php echo ($game_user['nickname']); ?></td>
                        <td class="center"><?php echo ($game_user['THTreasureDB']['score']); ?>（个）</td>
                        <td class="center"><?php echo ($game_user['THTreasureDB']['insurescore']); ?>（个）</td>
                        <td class="center"><?php echo ($game_user['invitation_code']); ?></td>
                        <td class="center" style="line-height:30px;">
                            <?php if(!empty($game_user['authentication_idcard'])): ?><font color="green">
                                姓名：<?php echo ($game_user['authentication_name']); ?>
                                <br>
                                身份证：<?php echo ($game_user['authentication_idcard']); ?>
                            </font>
                            <?php else: ?>
                            <?php echo ($game_user['is_authentication']); endif; ?>
                        </td>
                        <td class="center"><?php echo ($game_user['registerdate']); ?></td>
                        <td class="">
                            <a class="stdbtn btn_lime" href="<?php echo U('Recharge/addInsureScore', array('user_id'=>$game_user['userid']));?>">充房卡</a>&nbsp;&nbsp;
                            <a class="stdbtn btn_lime" href="<?php echo U('Recharge/addScore', array('user_id'=>$game_user['userid']));?>">充旗力币</a>&nbsp;&nbsp;
                            <?php if($game_user['invitation_code'] != '-'): ?><a class="stdbtn btn_lime" href="<?php echo U('Recharge/invitationCodeBind', array('userid'=>$game_user['userid']));?>">邀请码</a>&nbsp;&nbsp;<?php endif; ?>
                    </td>
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

</body>
</html>
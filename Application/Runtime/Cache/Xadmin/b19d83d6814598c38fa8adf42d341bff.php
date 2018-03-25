<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>系统消息-<?php echo (APP_NAME); ?></title>
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
        <h1 class="pagetitle">系统消息</h1>
        <span class="pagedesc"></span>
    </div>
    <div id="contentwrapper" class="contentwrapper">
        <div>
            <form href="" metod="get">
            <input type="button" id="skuAdd" data-goodsid="<?php echo ($moduleInfo['id']); ?>" class="stdbtn" value="新增消息╋" style="background: #09c;border: 1px solid #09c;" />
            </form>
        </div>
        <p></p>
        <form action="<?php echo U('Module/batchOperate');?>" method="post" id="operate">
            <input type="hidden" name="operate" id="operate_type" value="sale" />
            <table cellpadding="0" cellspacing="0" border="0" class="stdtable stdtablequick">
                <tr>
                    <th>ID</th>
                    <th>详细</th>
                    <th>创建时间</th>
                    <th>操作</th>
                </tr>

                <?php if(empty($list)): ?><tr>
                        <td colspan="4">没有模块列表~！</td>
                    </tr>
                    <?php else: ?>
                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$m): $mod = ($i % 2 );++$i;?><tr>
                            <td class="center"><?php echo ($m['id']); ?></td>
                            <td class="center"><?php echo ($m['messagestring']); ?></td>
                             <td class="center"><?php echo ($m['starttime']); ?></td>
                            <td class="center">
                                   <!-- <a class="stdbtn btn_lime" href="<?php echo U('messageEdit', array('id'=>$m['id']));?>">编辑</a>&nbsp;&nbsp; -->
                                    <a class="stdbtn btn_lime" href="<?php echo U('messageDel', array('id'=>$m['id']));?>">删除</a>&nbsp;&nbsp;
                            </td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    <tr>
                        <td colspan="4">
                            <div class="page-box">
                                <?php echo ($show); ?>
                            </div>
                        </td>
                    </tr><?php endif; ?>
                </tbody>
            </table>
        </form>
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
    <script type="text/javascript">
        $("#skuAdd").on('click',function(){
            window.location.href = "/Xadmin/System/messageAdd";
        });

        $("#select_all").change(function() {
            if ( this.checked ) {
                $(".goods_id").prop("checked", true);
            } else {
                $(".goods_id").prop("checked", false);
            }
        });

        // 上架
        $("#onsale_selected").click(function() {
            $("#operate_type").val('sale');
            $("#operate").submit();
        });

        // 下架
        $("#nosale_selected").click(function() {
            $("#operate_type").val('nosale');
            $("#operate").submit();
        });

        //单个删除
        $(".del_confirm").on('click',function(){
            var action_name = $(this).html();
            var action_url = $(this).attr("action_href");
            //                        alert("action_name:"+action_name+";action_url:"+action_url);return;
            if(confirm('是否确定【'+action_name+'】?')){
                location.href = action_url;
            }
        });

        // 多个删除
        $("#delete_selected").click(function() {
            var action_name = $(this).val();
            if(!confirm('是否确定【'+action_name+'】?')){
                return false;
            }
            $("#operate_type").val('remove');
            $("#operate").submit();
        });

        //支付
        $(".pay_btn").click(function(){
            var goods_id    =   $(this).attr('data-goodsid');
            window.location.href = "/Agent/Goods/confirmPay/goods_id/"+goods_id;
        });

    </script>

</body>
</html>
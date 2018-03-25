<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>商品列表-<?php echo (APP_NAME); ?></title>
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
        <h1 class="pagetitle">商品列表</h1>
        <span class="pagedesc"></span>
    </div>
    <?php $cols = 9; ?>
    <div id="contentwrapper" class="contentwrapper">
        <form action="<?php echo U('Goods/batchOperate');?>" method="post" id="operate">
            <input type="hidden" name="operate" id="operate_type" value="sale" />
            <table cellpadding="0" cellspacing="0" border="0" class="stdtable stdtablequick">
                <tr>
                    <th>
                        <label>
                          ID
                        </label>
                    </th>
                    <th>买家</th>
                    <th>套餐名称</th>
                    <th>封面图</th>
                    <th>套餐价格</th>
                    <th>套餐数量</th>
                    <th>赠送数量</th>
                    <th>添加时间</th>
                    <th>操作</th>
                </tr>

                <?php if(empty($list)): ?><tr>
                        <td colspan="<?php echo ($cols); ?>">没有内容列表~！</td>
                    </tr>
                    <?php else: ?>
                    <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$goods): $mod = ($i % 2 );++$i;?><tr>
                            <td class="center">
                                <label>
                                    <?php echo ($goods['id']); ?>
                                </label>
                            </td>
                            <td class="center"><?php echo ($goods['buyer']); ?></td>
                            <td class="center"><?php echo ($goods['goods_name']); ?></td>
                            <td class="center"><img src="<?php echo ($goods['goods_image']); ?>" height="60" /></td>
                            <td class="center"><?php echo ($goods['goods_price']); ?>（元）</td>
                            <td class="center"><?php echo ($goods['goods_nums']); ?>（颗）</td>
                            <td class="center"><?php echo ($goods['give_goods_nums']); ?>（颗）</td>
                            <td class="center"><?php echo time_format($goods['add_time']);?></td>
                            <td class="center">
                                    <a class="stdbtn btn_lime" href="<?php echo U('Goods/edit', array('id'=>$goods['id']));?>">编辑</a>&nbsp;&nbsp;
                                    <a class="stdbtn btn_lime del_confirm" action_href="<?php echo U('Goods/del', array('id'=>$goods['id']));?>">删除</a>&nbsp;&nbsp;
                            </td>
                        </tr><?php endforeach; endif; else: echo "" ;endif; ?>
                    <tr>
                        <td colspan="<?php echo ($cols); ?>">
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

    </script>

</body>
</html>
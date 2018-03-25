<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>小喇叭-<?php echo (APP_NAME); ?></title>
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
        <h1 class="pagetitle">小喇叭</h1>
        <span class="pagedesc"></span>
    </div>

    <div id="contentwrapper" class="contentwrapper">
        <form class="stdform stdform2" action="<?php echo U('System/notice');?>" method="post" id="JgoodsForm">
            <div class="">
                <label></label>
                <span class="">
                    <textarea class="text" name="GameWeixin" id="GameWeixin" rows="6"><?php echo ($GameWeixin); ?></textarea>
                </span>
            </div>
          
            <div class="line-dete">
                <label></label>
                <span class="">
                    <input type="submit" id="JgoodsSubmit" class="stdbtn" value="提交" />
                    <span id="wordage"></span>
                </span>
            </div>
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
    <script type="text/javascript" src="/Public/Common/js/json2.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/kindeditor/kindeditor-min.js"></script>
    <script type="text/javascript" src="/Public/Admin/js/kindeditor/lang/zh_CN.js"></script>
    <script type="text/javascript" src="/Public/Common/js/ajaxfileupload.js"></script>
    <script type="text/javascript">
    $(document).ready(function(){

            var limitNum = 30;

            var pattern = '还可以输入' + limitNum + '字';
            var remain1 = $("#GameWeixin").val().length;
                    console.log(remain1);
            if(remain1 > limitNum){
                    pattern = "字数超过"+limitNum+"个限制！";
            }else{
                    var result = limitNum - remain1;
                    pattern = '还可以输入' + result + '字';
            }
            $('#wordage').html(pattern);

            $('#GameWeixin').keyup(
                    function(){
                            var remain = $(this).val().length;
                            if(remain > limitNum){
                                    pattern = "字数超过"+limitNum+"个限制！";
                            }else{
                                    var result = limitNum - remain;
                                    pattern = '还可以输入' + result + '字';
                            }
                            $('#wordage').html(pattern);
                    }
            );

    });	
    </script>
    <script type="text/javascript">
        var firstUpload = true;

        var editor;
        KindEditor.ready(function(K) {
            editor = K.create('#goodsDetail', {
                height: "600px",
                allowFileManager: true,
                uploadJson: '<?php echo U("Goods/descUploadPic");?>',
                items : ['source','fontname','fontsize','forecolor','preview','selectall','justifyleft','justifycenter','justifyright','link','unlink','image'],
                afterCreate : function() {
                    this.loadPlugin('autoheight');
                }
            });
        });
    </script>
    <script type="text/javascript">
        $(function(){
                $("#goods_nums").change(function(){
                    var room_price  =   $("#room_price").val();
                    var goods_nums  =   $(this).val();
                    $("#goods_price").val(parseFloat(room_price*goods_nums));
                });
                
                //上传图片
                $("#photoList").on('click', 'img', function() {
			$("#photoList").find('img').removeClass('s-cover');
			$(this).addClass('s-cover');
			$('#JcoverPid').val($(this).attr('src'));
		});

		$('#photoList').on('click', '.del-pic', function() {
			$(this).parent().remove();
                        $('#JgoodsListWrap').append('<div class="upload-wrap"> <input type="file" id="fileToUpload" name="fileToUpload" class="f-upload" /> </div>');
		});

		$(document).on('change', '#fileToUpload', function() {
			ajaxFileUpload();
		});

		function ajaxFileUpload() {
			$.ajaxFileUpload({
				url: "<?php echo U('Goods/photoUpload');?>",
				secureuri: false,
				fileElementId: 'fileToUpload',
				dataType: 'json',
				success: function (data, status) {
					if(typeof(data.error) != 'undefined') {
						if(data.error != '') {
							alert(data.error);
						} else {
							$("#photoList").append('<div class="pic-wrap"><i class="del-pic"></i><img src="' + data.msg + '" /><input type="hidden" name="photo[]" value="' + data.msg + '" /></div>');
                                                        $('#JcoverPid').val(data.msg);
                                                        $("#photoList").find('img').addClass('s-cover');
						}
						$('.upload-wrap').remove();
//						$('#JgoodsListWrap').append('<div class="upload-wrap"> <input type="file" id="fileToUpload" name="fileToUpload" class="f-upload" /> </div>');
					}
				},
				error: function (data, status, e) {
					var html='<div class="title">提示</div><div>' + e + '</div>';
					popbox(html);
				}
			})
			return false;
		}
                
                
                
                
                
        });
    </script>

</body>
</html>
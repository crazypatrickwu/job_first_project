<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
<title><?php echo (APP_NAME); ?>后台管理</title>
<meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,mini-scale=1.0,user-scalable=no">
<meta name="description" content="">
<meta name="author" content="">
<link href="/Public/fxw.ico" mce_href="/Public/fxw.ico" rel="bookmark" type="image/x-icon" /> 
<link href="/Public/fxw.ico" mce_href="/Public/fxw.ico" rel="icon" type="image/x-icon" /> 
<link href="/Public/fxw.ico" mce_href="/Public/fxw.ico" rel="shortcut icon" type="image/x-icon" />
<link rel="stylesheet" type="text/css" href="/Public/Wap/lib/bootstrap/css/bootstrap.css">
<link rel="stylesheet" type="text/css" href="/Public/Wap/stylesheets/theme.css">
<link rel="stylesheet" href="/Public/Wap/lib/font-awesome/css/font-awesome.css">
<link rel="stylesheet" type="text/css" href="/Public/Wap/stylesheets/xnrcms_admin.css">
<script src="/Public/Wap/lib/jquery-1.7.2.min.js" type="text/javascript"></script>
<script src="/Public/Wap/lib/xnrcms_admin.js" type="text/javascript" ></script>
<script src="/Public/Wap/lib/xnrcms_check.js" type="text/javascript" ></script>
<script src="/Public/Wap/lib/jquery.validation/1.14.0/jquery.validate.min.js" type="text/javascript" ></script>
<script src="/Public/Wap/lib/layer/2.1/layer.js" type="text/javascript"></script>
<script src="/Public/Wap/lib/bootstrap/js/bootstrap.js"></script>
<!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<!-- Le fav and touch icons -->
<link rel="shortcut icon" href="../assets/ico/favicon.ico">
<link rel="apple-touch-icon-precomposed" sizes="144x144" href="../assets/ico/apple-touch-icon-144-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="114x114" href="../assets/ico/apple-touch-icon-114-precomposed.png">
<link rel="apple-touch-icon-precomposed" sizes="72x72" href="../assets/ico/apple-touch-icon-72-precomposed.png">
<link rel="apple-touch-icon-precomposed" href="../assets/ico/apple-touch-icon-57-precomposed.png">
<!--[if lt IE 7 ]> <body class="ie ie6"> <![endif]-->
 <!--[if IE 7 ]> <body class="ie ie7 "> <![endif]-->
 <!--[if IE 8 ]> <body class="ie ie8 "> <![endif]-->
 <!--[if IE 9 ]> <body class="ie ie9 "> <![endif]-->
 <!--[if (gt IE 9)|!(IE)]><!-->
 <style type="text/css">
#line-chart {
    height:300px;
    width:800px;
    margin: 0px auto;
    margin-top: 1em;
}
.brand { font-family: georgia, serif; }
.brand .first {
    color: #ccc;
    font-style: italic;
}
.brand .second {
    color: #fff;
    font-weight: bold;
}
</style>
<script type="text/javascript">
var CommonJs = {
		Loading:false,//数据加载标识
		JsStatus:function(delid,url,type){
			var actionName		= '';
				if(type == 4) actionName = '删除';
				if(type == 5) actionName = '启用';
				if(type == 6) actionName = '禁用';
				if(type == 7) actionName = '退出';
				layer.confirm('确认要'+actionName+'吗？',function(index){
					var ids			= [];
						if(delid > 0){
							//单个删除
							ids.push(delid); // 添加数组
						}else {
							//批量删除
							var idsObj		= $("input[name='ids[]']");
							idsObj.each(function(i,k){
								if(this.checked == true){
									ids.push(this.value); // 添加数组
								}
							});
						}
						if(ids.length <= 0){
							layer.msg('请选择要'+actionName+'的数据!',{icon: 3,time:2000});return false;
						}
						if(CommonJs.Loading){
							layer.msg('有操作在进行，请稍等...',{icon: 0,time:2000});return false;
						}
						CommonJs.Loading 	= true;
						layer.msg('数据'+actionName+'中...',{icon: 16});
						$.post(url, {'ids':ids}, function(data){
							layer.msg(data.info,{icon: 6,time:1000},function(){
		    					if(data.url != '') window.location.reload();
		    					CommonJs.Loading 	= false;
			    			});
			    		}, "json");
						return false;
				});
		},
		//数据保存
		SubmitData:{},
		FormCheck:function(){return true;},
		JsSave:function(obj,fn){ 
			var FormObj	= $(obj);
				FormObj.validate({
					submitHandler:function(){
						//验证数据是否合法
						if(!CommonJs.FormCheck($(this))){return false;};
						//验证是否有数据就提交
					    if (typeof CommonJs.SubmitData === "object" && !(CommonJs.SubmitData instanceof Array)){  
					        var hasProp = false;
					        for (var prop in CommonJs.SubmitData){ hasProp = true;break;}  
					        if (!hasProp){ _inform('无数据提交');return false;}  
					    }
					    //数据提交
						if(CommonJs.Loading){
							layer.msg('有操作在进行，请稍等...',{icon: 0,time:1000});return false;
						}
						CommonJs.Loading 	= true;
						layer.msg('数据处理中...',{icon: 16});
						$.post(FormObj.attr("action"), CommonJs.SubmitData, function(data){
							if(fn){
								fn(data);
							}else{
								var ic	= data.status == 1 ? 6 : 2;
									layer.msg(data.info,{icon: ic,time:2000},function(){
										parent.$('.btn-refresh').click();
						    			if(data.status == 1){
							    			if(data.info == '登录成功！' && data.url != ''){
								    			window.location.href = data.url;return false;
								    		}
											if(data.url != '') window.parent.location.reload();
											window.parent.location.reload();
											parent.layer.close(parent.layer.getFrameIndex(window.name));
							    		}
						    			CommonJs.Loading 	= false;
							    	});
							}
			    		}, "json");
						return false;
					}
				});
		},
		//调试用
		WO:function (obj){
		      var description = "";
		      for(var i in obj){  
		          var property=obj[i];  
		          description+=i+" = "+property+"\n";  
		      }  
		      alert(description);
		}
};
</script>
  </head>
  <body class=""> 
  <!--<![endif]-->
    <div class="navbar">
        <div class="navbar-inner">
        <ul class="nav pull-right">
            <li id="fat-menu" class="dropdown">
                <a href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">
                    <i class="icon-user"></i> <?php echo session('agentAccount');?>
                    <i class="icon-caret-down"></i>
                </a>
                <ul class="dropdown-menu">
                    <li><a tabindex="-1" class="visible-phone" href="<?php echo U('Agent/agentInfo');?>">基本信息</a></li>
                    <li class="divider visible-phone"></li>
                    <li><a tabindex="-1" href="<?php echo U('Agent/editPwd');?>">修改密码</a></li>
                    <li class="divider visible-phone"></li>
                    <li class="logout">
                        <a tabindex="-1" href="javascript:;">退出登录</a>
                    </li>
                </ul>
            </li>
        </ul>
        <a class="brand" href="javascript:;">
        <span class="first" style="font-size:30px;color:#FFF"><?php echo (APP_NAME); ?>代理系统</span>
        <!-- <span class="second"><?php echo (APP_NAME); ?>推广员系统</span> -->
        </a>
</div>
<script type="text/javascript">
$(document).on("touchend",".logout",function(e){
    e.preventDefault(); 
    CommonJs.JsStatus(<?php echo session('agentId');?>,'<?php echo U('Login/logout');?>',7);return false;
});
$(document).on("click",".logout",function(e){
    e.preventDefault(); 
    CommonJs.JsStatus(<?php echo session('agentId');?>,'<?php echo U('Login/logout');?>',7);return false;
});
$(document).on("touchstart",".dropdown-menu li",function(e){
    e.preventDefault();
    window.location.href = $(this).find("a").attr("href");
    return false;
});
</script>
    </div>
    <div class="sidebar-nav">
        <ul class="breadcrumb">
            <li><a href="<?php echo U('Index/index');?>">首页</a> <span class="divider">/</span></li>
            <li><a href="<?php echo U('Agent/agentList');?>">代理列表</a> <span class="divider">/</span></li>
            <li class="active">代理添加</li>
        </ul>

        <div class="container-fluid">
            <div class="row-fluid">
				<form id="AgentForm" action="<?php echo U('Agent/addAgent');?>" method="post">
						<input type="hidden" name="id" value="0" />
						<div class="well">
						    <div id="myTabContent" class="tab-content">
						      <div class="tab-pane active in" id="home">
							        <label>新代理昵称</label>
							        <input type="text" id="nickname" name="nickname" class="input-xlarge" value="<?php echo ((isset($agentInfo['nickname']) && ($agentInfo['nickname'] !== ""))?($agentInfo['nickname']):''); ?>" />
							        <label>新代理微信号</label>
							        <input type="text" id="wechat_id" name="wechat_id" class="input-xlarge" value="<?php echo ((isset($agentInfo['wechat_id']) && ($agentInfo['wechat_id'] !== ""))?($agentInfo['wechat_id']):''); ?>" />
							        <label>新代理手机号</label>
							        <input type="text" id="phone" name="phone" class="input-xlarge" value="<?php echo ((isset($agentInfo['phone']) && ($agentInfo['phone'] !== ""))?($agentInfo['phone']):''); ?>" />
							        <!-- <label>所在区域</label>
						               <div class="ip-shop-dizi">
								        <select name="province" id="province" class="input-xlarge Jselect">
								            <?php if(is_array($province)): $i = 0; $__LIST__ = $province;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$z1): $mod = ($i % 2 );++$i;?><option data-id="<?php echo ($z1['id']); ?>" value="<?php echo ($z1['region_name']); ?>"<?php if($agentInfo['province'] == $z1['region_name']): echo chr(32);?>selected="selected"<?php endif; ?>><?php echo ($z1['region_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
								        </select>
								        <select name="city" id="city" class="input-xlarge Jselect">
								            <?php if(is_array($city)): $i = 0; $__LIST__ = $city;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$z2): $mod = ($i % 2 );++$i;?><option data-id="<?php echo ($z2['id']); ?>" value="<?php echo ($z2['region_name']); ?>"<?php if($agentInfo['city'] == $z2['region_name']): echo chr(32);?>selected="selected"<?php endif; ?>><?php echo ($z2['region_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
								        </select>
								        <select name="county" id="county" class="input-xlarge Jselect">
								            <?php if(is_array($county)): $i = 0; $__LIST__ = $county;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$z3): $mod = ($i % 2 );++$i;?><option data-id="<?php echo ($z3['id']); ?>" value="<?php echo ($z3['region_name']); ?>"<?php if($agentInfo['county'] == $z3['region_name']): echo chr(32);?>selected="selected"<?php endif; ?>><?php echo ($z3['region_name']); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
								        </select>
								    </div>
							        <label>详细地址</label>
							        <input type="text" name="address" class="input-xlarge"  value="<?php echo ((isset($agentInfo['address']) && ($agentInfo['address'] !== ""))?($agentInfo['address']):''); ?>" />
							        <label>服务城市</label>
							        <input type="text" name="service_city" class="input-xlarge"  value="<?php echo ((isset($agentInfo['service_city']) && ($agentInfo['service_city'] !== ""))?($agentInfo['service_city']):''); ?>" /> -->
							        <label>是否锁定</label>
							        <div class="radio-box">
								        <input type="radio" name="is_lock" value="1" style="vertical-align:middle; margin-top:-2px; margin-bottom:1px;"/>
								        <label for="is_lock_1" style="vertical-align:middle; margin-top:-2px; margin-bottom:1px;display:initial;">是</label>&nbsp;
								        <input type="radio" name="is_lock" value="0" style="vertical-align:middle; margin-top:-2px; margin-bottom:1px;" checked="checked" />
								        <label for="is_lock_0" style="vertical-align:middle; margin-top:-2px; margin-bottom:1px;display:initial;">否</label>
							        </div>
						      </div>
						  	</div>
						</div>
						<div class="btn-toolbar" style="text-align:center;">
						    <button class="btn btn-primary" style="margin-left:2px;"><i class="icon-ok-sign"></i>保存</button>
					    	<button class="btn btn-primary" style="margin-left:2px;" onclick="javascript:history.back(-1);return false;"><i class="icon-step-backward"></i>返回</button>
						  	<div class="btn-group"></div>
						</div>
				</form>
            </div>
        </div>
    </div>
<script type="text/javascript">
$(function() {
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
});
CommonJs.FormCheck	= function(FormObj){
	if(CommonJs.Loading){ return false;}
	CommonJs.Loading 							= true;
	CommonJs.SubmitData.nickname				= $("input[name='nickname']").val();
	CommonJs.SubmitData.wechat_id				= $("input[name='wechat_id']").val();
	CommonJs.SubmitData.phone					= $("input[name='phone']").val();
	CommonJs.SubmitData.province				= $("select[name='province']").val();
	CommonJs.SubmitData.city					= $("select[name='city']").val();
	CommonJs.SubmitData.county					= $("select[name='county']").val();
	CommonJs.SubmitData.address					= $("input[name='address']").val();
	CommonJs.SubmitData.service_city			= $("input[name='service_city']").val();
	CommonJs.SubmitData.is_lock					= rcVal($("input[name='is_lock']"));
	CommonJs.SubmitData.id						= 0;
	if(!CheckJs.required(CommonJs.SubmitData.nickname)){
		_inform('请输入代理昵称',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.wechat_id)){
		_inform('请输入代理微信号',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.phone)){
		_inform('请输入代理手机号',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.mobile(CommonJs.SubmitData.phone)){
		_inform('请输入正确的手机号',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.province)){
		_inform('请选择所在省份',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.city)){
		_inform('请选择所在市/区',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.county)){
		_inform('请选择所在县/区',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.address)){
		_inform('请输入详细地址',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.service_city)){
		_inform('请输入服务城市',function(){CommonJs.Loading = false;});return false;
	}
	CommonJs.Loading = false
	return true;
};
CommonJs.JsSave("#AgentForm");
</script>
    
  </body>
</html>
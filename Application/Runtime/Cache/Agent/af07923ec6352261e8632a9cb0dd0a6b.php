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
            <li class="active">银行信息</li>
        </ul>

        <div class="container-fluid">
            <div class="row-fluid">
				<form id="AgentForm" action="<?php echo U('Agent/agentWithdrawalsAccount');?>" method="post">
				<div class="well">
				    <div id="myTabContent" class="tab-content">
				      <div class="tab-pane active in" id="home">
						        <label>真实姓名</label>
						        <input type="text" id="truename" name="truename" class="input-xlarge" value="<?php echo ($agent_withdrawals_account['truename']); ?>" />
						        <label>支付宝账号</label>
						        <input type="text" id="alipay_account" name="alipay_account" class="input-xlarge" value="<?php echo ($agent_withdrawals_account['alipay_account']); ?>" />
						        <label>提现银行</label>
				                <div class="ip-shop-dizi">
							        <select name="bank_name" id="bank_name" class="input-xlarge Jselect">
							        	<option value="">--请选择--</option>
							            <?php if(is_array($bankList)): $i = 0; $__LIST__ = $bankList;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$bank): $mod = ($i % 2 );++$i;?><option value="<?php echo ($bank); ?>"<?php if($bank == $agent_withdrawals_account['bank_name']): echo chr(32);?>selected="selected"<?php endif; ?>><?php echo ($bank); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
							        </select>
							    </div>
						        <label>支行名称</label>
						        <input type="text" id="bank_subbranch" name="bank_subbranch" class="input-xlarge"  value="<?php echo ($agent_withdrawals_account['bank_subbranch']); ?>" />
						        <label>银行卡号码</label>
						        <input type="text" name="bank_card" class="input-xlarge"  value="<?php echo ($agent_withdrawals_account['bank_card']); ?>" />
                                <label>账户密码</label>
                                <input type="password" name="agent_password" class="input-xlarge"  value="" placeholder="请输入您的登录密码" />
				      </div>
				  	</div>
				</div>
				<div class="btn-toolbar">
                    <input type="hidden" name="id" value="<?php echo ($agent_withdrawals_account['id']); ?>" />
				    <button class="btn btn-primary" style="margin-left:2px;"><i class="icon-ok-sign"></i>保存</button>
				    <!-- <button class="btn btn-primary applyWithdrawals" style="margin-left:2px;">申请提现</button> -->
				    <button class="btn btn-primary" style="margin-left:2px;" onclick="javascript:history.back(-1);return false;"><i class="icon-step-backward"></i>返回</button>
				  	<div class="btn-group"></div>
				</div>
				</form>
            </div>
        </div>
    </div>
<script type="text/javascript">

CommonJs.FormCheck	= function(FormObj){
	CommonJs.Loading 	= true;
	CommonJs.SubmitData.truename				= $("input[name='truename']").val();
	CommonJs.SubmitData.alipay_account				= $("input[name='alipay_account']").val();
	CommonJs.SubmitData.bank_name				= $("select[name='bank_name']").val();
	CommonJs.SubmitData.bank_subbranch					= $("input[name='bank_subbranch']").val();
	CommonJs.SubmitData.bank_card					= $("input[name='bank_card']").val();
	// CommonJs.SubmitData.address					= $("input[name='address']").val();
	// CommonJs.SubmitData.service_city			= $("input[name='service_city']").val();
	// CommonJs.SubmitData.is_lock					= rcVal($("input[name='is_lock']"));
	CommonJs.SubmitData.id						= $("input[name='id']").val();
	CommonJs.SubmitData.agent_password					= $("input[name='agent_password']").val();
	if(!CheckJs.required(CommonJs.SubmitData.truename)){
		_inform('请输入真实姓名',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.alipay_account)){
		_inform('请输入支付宝账号',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.bank_name)){
		_inform('请选择提现银行',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.bank_subbranch)){
		_inform('请输入支行名称',function(){CommonJs.Loading = false;});return false;
	}
	if(!CheckJs.required(CommonJs.SubmitData.bank_card)){
		_inform('请输入银行卡号码',function(){CommonJs.Loading = false;});return false;
	}
	CommonJs.Loading = false
	return true;
};
CommonJs.JsSave("#AgentForm");
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
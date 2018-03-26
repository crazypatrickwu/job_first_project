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
                    
                </ul>
		        <a class="brand" href="javascript:;">
		        <span class="first"><?php echo (APP_NAME); ?>代理系统</span>
		        <!-- <span class="second"><?php echo (APP_NAME); ?>推广员系统</span> -->
		        </a>
        </div>
    </div>
    <div class="row-fluid">
	    <div class="dialog">
	        <div class="block">
	            <p class="block-heading">登录</p>
	            <div class="block-body">
	                <form id="login" action="<?php echo U();?>" method="post">
	                    <label>用户名</label>
	                    	<input type="text" name="account" id="account" class="span12"/>
	                    <label>密码</label>
	                    <input type="password" name="password" id="password" class="span12"/>
	                    <input type="submit" class="btn btn-primary pull-right submit_btn" value="登录">
	                    <label class="remember-me">
	                    <input type="checkbox" value="1" checked="checked" name="rememberPassword" />保存一周</label>
	                    <div class="clearfix"></div>
	                </form>
	            </div>
	        </div>
	    </div>
	</div>
    <script type="text/javascript">
    CommonJs.FormCheck	= function(FormObj){
		if(CommonJs.Loading){ return false;}
		CommonJs.Loading 	= true;
		CommonJs.SubmitData.account				= $("input[name='account']").val();
		CommonJs.SubmitData.password			= $("input[name='password']").val();
		CommonJs.SubmitData.rememberPassword	= rcVal($("input[name='rememberPassword']"));
		if(!CheckJs.required(CommonJs.SubmitData.account)){
			_inform('请输入用户名',function(){CommonJs.Loading = false;});return false;
		}
		if(!CheckJs.required(CommonJs.SubmitData.password)){
			_inform('请输入登录密码',function(){CommonJs.Loading = false;});return false;
		}
		CommonJs.Loading = false
		return true;
};
CommonJs.JsSave("#login");
    </script>

  </body>
</html>
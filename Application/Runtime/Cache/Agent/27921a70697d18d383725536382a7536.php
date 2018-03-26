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
            <li class="active">购买房卡</li>
        </ul>

        <div class="container-fluid">
          <div class="row-fluid">
          <!-- 
          <div class="search-well">
              <form class="form-inline" action="" metod="get">
                  <input class="input-xlarge" id="appendedInputButton" type="text" name="keyword" value="<?php echo remove_xss(I('get.keyword'));?>" placeholder="请输入代理昵称/手机号/微信号进行检索" style="width:75%;margin-left:2px;">
                  <button class="btn" type="button" onclick="location.href='<?php echo U();?>'+'?keyword='+$(this).parent().find('input').val();"><i class="icon-search"></i>搜索</button>
              </form>
          </div>-->
<div class="well">
    <table class="table">
      <thead>
        <tr>
          <th width="20%">玩家ID</th>
          <th width="15%">金额</th>
          <th width="15%">数量</th>
          <th width="40%">时间</th>
        </tr>
      </thead>
      <?php $cols=4; ?>
      <tbody>
        <?php if(empty($user_recharge_recored_list)): ?><tr>
               <td colspan="<?php echo ($cols); ?>" align="center" style="text-align: center !important;">没有代理数据~！</td>
           </tr>
        <?php else: ?>
        <?php if(is_array($user_recharge_recored_list)): $i = 0; $__LIST__ = $user_recharge_recored_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$recored): $mod = ($i % 2 );++$i;?><tr>
                <td class="center1">
                    <div style="width:100%;word-wrap:break-word;"><?php echo ($recored['uid']); ?></div>
                </td>
                <td class="center"><div style="width:100%;word-wrap:break-word;"><?php echo ($recored['total_price']); ?></div></td>
                <td class="center"><div style="width:100%;word-wrap:break-word;"><?php echo ($recored['nums']); ?>(个)</div></td>
                <td class="center">
                    <?php echo (time_format($recored['create_time'])); ?>
                </td>
            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
        <tr>
            <td colspan="<?php echo ($cols); ?>" style="width: 100%;">
                <div class="pagination"><?php echo ($show); ?></div>
            </td>
        </tr><?php endif; ?>
      </tbody>
    </table>
</div>      
                    
            </div>
        </div>
    </div>
    

    <script type="text/javascript">
        
    </script>
    
  </body>
</html>
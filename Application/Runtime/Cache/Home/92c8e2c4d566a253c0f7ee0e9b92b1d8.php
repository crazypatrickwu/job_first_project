<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,minimum-scale=1.0,user-scalable=no">
    <script>!function(n){var e=n.document,t=e.documentElement,i=750,d=i/100,o="orientationchange"in n?"orientationchange":"resize",a=function(){var n=t.clientWidth||320;n>750&&(n=750),t.style.fontSize=n/d+"px"};e.addEventListener&&(n.addEventListener(o,a,!1),e.addEventListener("DOMContentLoaded",a,!1))}(window);</script>
    <title>充值记录</title>
    <link rel="stylesheet" href="/Public/Home/Agent/css/taizhou.css">
    <script type="text/javascript" src="/Public/Home/Agent/js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="/Public/Home/Agent/js/public.js"></script>
</head>
<body style="background: #fff;">
<div>
    <!-- <div class="bg_top">
        <div class="search_box"><input type="text" name="keyword" id="keyword" placeholder="输入玩家ID"/><i><img src="/Public/Home/Agent/img/clear.png"/></i><span id="search_btn">搜索</span></div>
    </div> -->
    <div class="list_box">

        <?php if(empty($recharge_gold_list)): ?><div align="center" style="text-align: center !important;line-height:80px;">没有相关数据~！</div>
        <?php else: ?>
            <div class="list_main_result">
                <?php if(is_array($recharge_gold_list)): $i = 0; $__LIST__ = $recharge_gold_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$user_recharge_recored): $mod = ($i % 2 );++$i;?><div class="item_box pur_box">
                        <div class="clearfix pur_title">
                            <div class="left">玩家ID:<?php echo ($user_recharge_recored['uid']); ?></div>
                        </div>
                        <div class="ash"><label>订单编号</label>：<?php echo ($user_recharge_recored['order_sn']); ?></div>
                        <div class="ash"><label>购买数量</label>：<?php echo ($user_recharge_recored['nums']); ?>（颗）</div>
                        <div class="ash"><label>购买时间</label>：<?php echo (time_format($user_recharge_recored['create_time'])); ?></div>
                    </div><?php endforeach; endif; else: echo "" ;endif; ?>
            </div><?php endif; ?>
    </div>
</div>
</body>
    <input type="hidden" value="1" id="page">
    <input type="hidden" id="totalpage" value="<?php echo $totalpage; ?>" />
    <div id="loading" style="margin:auto;text-align:center;margin:10px;margin-bottom:50px;"></div>
    <script>
    var _url ="<?php echo U('Home/Recharge/playersRecored');?>";
    var totalpage = parseInt($('#totalpage').val());
    var falg = false;
    $(function(){
        var page = 1;
        var loading  = '<span style="text-align:center;font-family: yahei;font-size: 14px;margin-left:5px;line-height:15px;color:#666;">加载中...</span>';
        //下拉分页
        $(window).scroll(function(){
            var totalheight = parseFloat($(window).height()) + parseFloat($(window).scrollTop()) + 20;
            if(totalheight >= $(document).height()){
                if(falg) return;
                falg = true;
                page = parseInt(page)+1;
                if(page > totalpage){
                    page = totalpage;
                    return false;
                }
                $('#loading').html(loading);
                loadContents(page);
            }
        });
    });
    function loadContents(page){
        var keyword = $("#keyword").val();
        var _param  =   new Object();
            _param.page = page;
            _param.keyword = keyword;
            _param.isAjax       =   1;
        setTimeout(function(){
            $.get(_url,_param,function(res){
                if (res.code == '1') {
                    timer=0;
                    $('#loading').html('');
                    var new_html = '';
                    $.each(res.info,function(i,list) {
                        new_html +='<div class="item_box pur_box">';
                        new_html +='<div class="clearfix pur_title">';
                        new_html +='<div class="left">编号：'+list.id+'</div>';
                        new_html +='</div>';
                        new_html +='<div class="ash"><label>充卡数量</label>：'+list.pay_nums+'</div>';
                        new_html +='<div class="ash"><label>描述</label>：'+list.desc+'</div>';
                        if(list.type == '2'){
                            new_html +='<div class="ash"><label>代理ID</label>：'+list.to_agent_id+'</div>';
                        }else{
                            new_html +='<div class="ash"><label>玩家ID</label>：'+list.user_id+'</div>';
                        }
                        new_html +='<div class="ash"><label>返卡时间</label>：'+list.add_time+'</div>';
                        new_html +='</div>';

                    });
                    $(".list_main_result").append(new_html);
                    // $("#totalpage").val(data.list.last_page);

                    if(page == totalpage){
                        timer = 1;
                        $("#loading").html('<span style="color:#999">没有更多的数据了！</span>');
                    }
                    falg = false;
                };
            },'json');
        },1500);
    };

    /*点击搜索*/
    $("#search_btn").on('click',function(){
        $(".list_main_result").html("");
        loadContents(1);
    });


    /*删除搜索文字*/
    $(".search_box").find("i").on("click",function(){
        $("#keyword").val("").focus();
    });
</script>
<script src="https://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
    <script type="text/javascript">
        var AppId = "<?php echo ($signPackage['appid']); ?>";
        var timestamp = "<?php echo ($signPackage['timestamp']); ?>";
        var noncestr = "<?php echo ($signPackage['noncestr']); ?>";
        var signature = "<?php echo ($signPackage['signature']); ?>";
        // alert("AppId:"+AppId+"\n"+"timestamp:"+timestamp+"\n"+"noncestr:"+noncestr+"\n"+"signature:"+signature+"\n");
        wx.config({
            debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
            appId: AppId, // 必填，公众号的唯一标识
            timestamp: timestamp, // 必填，生成签名的时间戳
            nonceStr: noncestr, // 必填，生成签名的随机串
            signature: signature, // 必填，签名，见附录1
            jsApiList: ['onMenuShareAppMessage', 'onMenuShareTimeline'] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
        });

        wx.hideMenuItems({
            menuList: ['menuItem:copyUrl','menuItem:share:email'] // 要隐藏的菜单项，只能隐藏“传播类”和“保护类”按钮，所有menu项见附录3
        });

        wx.ready(function() {

            var shareTitle = "桐庐十三道";
            var shareDesc = "桐庐十三道,等你来战！";
            if (shareDesc == '') {
                shareDesc = shareTitle;
            }
            var shareLink = 'http://' + document.domain + "/Home/Download/index.html";
            var shareImgUrl = 'http://' + document.domain + "/Public/Game/download/img/logo_share.png";
            //     alert("shareTitle："+shareTitle+"\n"+"shareDesc:"+shareDesc+"\n"+"shareLink:"+shareLink+"\n"+"shareImgUrl："+shareImgUrl+"\n");
            wx.onMenuShareAppMessage({
                title: shareTitle,
                desc: shareDesc,
                link: shareLink,
                imgUrl: shareImgUrl,
                trigger: function(res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                    //                alert('用户点击发送给朋友');
                },
                success: function(res) {
                    //                alert('已分享');
                    // var _url = "<?php echo U('Index/wxshare');?>";
                    // var _param = new Object();
                    // _param.option = "goods";
                    // _param.id = "<?php echo ($info["id"]); ?>";
                    // $.post(_url, _param, function(res) {
                    //     showMessage(res.msg);
                    // }, 'json');
                },
                cancel: function(res) {
                    //                alert('已取消');
                },
                fail: function(res) {
                    //                alert(JSON.stringify(res));
                }
            });

            // 2.2 监听“分享到朋友圈”按钮点击、自定义分享内容及分享结果接口
            wx.onMenuShareTimeline({
                title: shareTitle,
                link: shareLink,
                imgUrl: shareImgUrl,
                trigger: function(res) {
                    // 不要尝试在trigger中使用ajax异步请求修改本次分享的内容，因为客户端分享操作是一个同步操作，这时候使用ajax的回包会还没有返回
                    //                alert('用户点击分享到朋友圈');
                },
                success: function(res) {
                    //                alert('已分享');
                    // var _url = "<?php echo U('Index/wxshare');?>";
                    // var _param = new Object();
                    // _param.option = "goods";
                    // _param.id = "<?php echo ($info["id"]); ?>";
                    // $.post(_url, _param, function(res) {
                    //     showMessage(res.msg);
                    // }, 'json');
                },
                cancel: function(res) {
                    //                alert('已取消');
                },
                fail: function(res) {
                    //                alert(JSON.stringify(res));
                }
            });

        });
    </script>
</html>
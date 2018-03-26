// rem使用优化
!function(n){var e=n.document,t=e.documentElement,i=750,d=i/100,o="orientationchange"in n?"orientationchange":"resize",a=function(){var n=t.clientWidth||320;n>750&&(n=750),t.style.fontSize=n/d+"px"};e.addEventListener&&(n.addEventListener(o,a,!1),e.addEventListener("DOMContentLoaded",a,!1))}(window);

//请求http地址
var http="http://139.196.214.241:8105";
var userId=8;	//测试临时用的用户id;
var telReg = "^((13[0-9])|(14[5|7])|(15([0-3]|[5-9]))|(18[0,5-9]))\\d{8}$";
var isIDCard1=/^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$/;
var isIDCard2=/^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X|x)$/;

//提示函数
function webTip(txt,fn,time){
	var div=document.createElement("div");
	var app=document.getElementById("app");
	var t;
	if(time){
		t=time;
	}else{
		t=1000;
	}
	div.style.cssText="position:fixed;left:50%;top:50%;padding:0.2rem 0.3rem;background:rgba(0,0,0,0.7);-webkit-transform:translate(-50%,-50%);font-size:15px;color:#fff;text-align:center;border-radius:0.08rem;z-index:999;-webkit-transition:all 0.3s;";
	div.innerHTML=txt;
	app.appendChild(div);
	setTimeout(function(){
		app.removeChild(div);
		if(fn) fn();
	},t)
}
//倒计时
function secondLess(num){
	var time=setInterval(function(){
		num--;
		vm.num=num;
		if(num<1){
			clearInterval(time);
		}
	},1000)
};

//获取地址栏参数
function getQueryString(name) { 
	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
	var r = window.location.search.substr(1).match(reg); 
	if (r != null) return unescape(r[2]); return null; 
}

//获取地址栏的值
function UrlSearch()
{
	var name,value;
	var str=location.href; //取得整个地址栏
	var num=str.indexOf("?")
	str=str.substr(num+1); //取得所有参数   stringvar.substr(start [, length ]

	var arr=str.split("&"); //各个参数放到数组里
	for(var i=0;i < arr.length;i++){
		num=arr[i].indexOf("=");
		if(num>0){
			name=arr[i].substring(0,num);
			value=decodeURI(arr[i].substr(num+1));
			this[name]=value;
		}
	}
}
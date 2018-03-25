function webTip(txt,fn,time){
	var webTip=document.getElementById("webTip");
	var body = document.getElementsByTagName("body")[0];
	if(webTip){
		return false;
	}
	var div=document.createElement("div");
	var t;
	if(time){
		t=time;
	}else{
		t=1000;
	}
	div.style.cssText="position:fixed;left:50%;top:50%;padding:20px 30px;background:rgba(0,0,0,0.7);-webkit-transform:translate(-50%,-50%);font-size:15px;color:#fff;text-align:center;border-radius:8px;z-index:999;-webkit-transition:all 0.3s;";
	div.innerHTML=txt;
	div.id="webTip";
	body.appendChild(div);
	setTimeout(function(){
		body.removeChild(div);
		if(fn) fn();
	},t)
}
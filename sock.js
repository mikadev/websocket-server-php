
window.onload = function(){


	try{
		var ip = "192.168.1.12";
		var socket = new WebSocket("ws://"+ip+":1577");
	}catch(e){

	}

	var el = document.getElementsByTagName("button");
	var dis = document.getElementById("display");
	var mes = document.getElementById("message");

	el.button.onclick = function(){
		socket.send(mes.value);
	}
	socket.onopen = function (e) {
		console.log("openned : "+e);
		dis.value = "";
	}
	socket.onclose = function (e) {
		console.log("Socket connection closed : "+e);
		document.title = "! Closed !";
	}
	socket.onmessage = function(e) {
		dis.value = dis.value + "\n" + e.data;
		dis.scrollTop = dis.scrollHeight;
	}
}

	
	
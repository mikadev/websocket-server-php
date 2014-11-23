
window.onload = function(){


	try{
		var ip = "127.0.0.1";
		var socket = new WebSocket("ws://"+ip+":1577");
	}catch(e){
		console.error("error try to restart the server");
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
		dis.value = dis.value + e.data + "\n";
		dis.scrollTop = dis.scrollHeight;
	}
}

	
	
<?php
include_once('config.php');
?>
<!DOCTYPE HTML>
<html>
	<head>
		<style type="text/css">

		</style>
		<script>
			var websocket = null;
			
		</script>
	</head>
	<body>
		<fieldset>
			<legend>Socket connection form</legend>
			<form name="connect_socket_form">
				<div>
					<label for="server_address">Domain/IP Address:</label>
					<input type="text" name="server_address" id="server_address" value="<?= HOST_NAME ?>" required/>
				</div>
				<div>
					<label for="port_number">Port Number:</label>
					<input type="text" name="port_number" id="port_number" value="<?= PORT ?>" required/>
				</div>
				<div>
					<label for="client_name">Your name:</label>
					<input type="text" name="client_name" id="client_name" required/>
				</div>
				<div style="margin-top:10px;">
					<button type="submit">Connect Socket</button>
					<h5>Status: <span id="connection_status"></span></h5>
				</div>
			</form>
		</fieldset>
		
		<fieldset>
			<legend>Message</legend>
			<div id="chat_box">
				<ul id="message_list"></ul>
			</div>
			
			<form name="message_form">
				<div>
					<input type="text" name="message" id="message" required/>
					<button type="submit">Send</button>
				</div>
			</form>
		</fieldset>
	</body>
	<script type="text/javascript">
		var connect_socket_form = document.forms['connect_socket_form'];
		connect_socket_form.onsubmit = function(event){
			event.preventDefault();	
			let domain = `ws://${this.server_address.value}:${this.port_number.value}/chattingPHP/server.php`;
			connect(domain);			
		}
		document.forms['message_form'].onsubmit = function(event){
			event.preventDefault();		
			var data = {
				message: this.message.value,
				client_name: connect_socket_form.client_name.value,
				message_type: "chat"
			};	
			sendData(JSON.stringify(data));
			this.reset();
		};

		function connect(domain){
			if(websocket != null){
				return;
			}
			try{
				websocket = new WebSocket(domain); 
			
				websocket.onopen = function(event) { 
					document.querySelector("#connection_status").innerHTML = "Connection is established!";	
				}
				websocket.onmessage = function(event) {
					showMessage(event.data);
				};
				
				websocket.onerror = function(event){					
					console.log(event);
					document.querySelector("#connection_status").innerHTML = "An error has occured.";
					websocket = null;
				};
				websocket.onclose = function(event){
					document.querySelector("#connection_status").innerHTML = "Connection closed, click connect button to start communication.";
					websocket = null;
				}; 
			}
			catch(e){
				console.log("Socket Error: \n");
				console.log(e);
			}
		}
		
		function showMessage(data){

			var jsonData = JSON.parse(data);
			if(jsonData.message_type == "chat"){			
				var msgLi = document.createElement("li");
				let sender = (connect_socket_form.client_name.value == jsonData.client_name)?"You":jsonData.client_name;			

				msgLi.innerHTML = `<strong>${sender}: </strong>${jsonData.message}`;
				
				message_list = document.getElementById("message_list");
				message_list.append(msgLi);
			}
		}
		
		function sendData(message){
			//console.log(message);
			if(websocket == null){
				alert("Please click the connect button to start communication.")
			}
			websocket.send(message);
		}
	</script>
</html>
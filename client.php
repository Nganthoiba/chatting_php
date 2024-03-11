<!DOCTYPE HTML>
<html>
	<head>
		<script>
			var websocket = null;
			function reconnect(){
				let connect_socket_form = document.forms['connect_socket_form'];
				let domain = `ws://${connect_socket_form.server_address.value}:${connect_socket_form.port_number.value}/chattingPHP/php-socket.php`;
				connect(domain);
			}
			
			function connect(domain){
				if(websocket != null){
					return;
				}
				try{
					websocket = new WebSocket(domain); 
				
					websocket.onopen = function(event) { 
						showMessage("<div class='chat-connection-ack'>Connection is established!</div>");		
					}
					websocket.onmessage = function(event) {
						/*
						var Data = JSON.parse(event.data);
						showMessage("<div class='"+Data.message_type+"'>"+Data.message+"</div>");
						$('#chat-message').val('');
						*/
						showMessage(event.data);
						console.log(event);
					};
					
					websocket.onerror = function(event){
						//showMessage("<div class='error'>Problem due to some errors</div>");
						document.querySelector("connection_status").innerHTML = "An error occurs";
						websocket = null;
					};
					websocket.onclose = function(event){
						//showMessage("<div class='chat-connection-ack'>Connection Closed <button type='button' onclick='reconnect();'>Reconnect</button></div>");
						document.querySelector("connection_status").innerHTML = "Connection closed";
						websocket = null;
					}; 
				}
				catch(e){
					console.log("Socket Error: \n");
					console.log(e);
				}
			}
			
			function showMessage(message){
				var msgLi = document.createElement("li");
				msgLi.innerHTML = message;
				
				message_list = document.getElementById("message_list");
				message_list.append(msgLi);
			}
			
			function sendData(message){
				console.log(message);
				if(websocket == null){
					reconnect();
				}
				websocket.send(message);
			}
		</script>
	</head>
	<body>
		<fieldset>
			<legend>Socket connection form</legend>
			<form name="connect_socket_form">
				<div>
					<label for="server_address">Domain/IP Address:</label>
					<input type="text" name="server_address" id="server_address" value="192.168.137.1" required/>
				</div>
				<div>
					<label for="port_number">Port Number:</label>
					<input type="text" name="port_number" id="port_number" value="8080" required/>
				</div>
				<div>
					<button type="submit">Connect Socket</button>
				</div>
			</form>
		</fieldset>
		
		<fieldset>
			<legend>Message</legend>
			<div id="chat_box">
				<ul id="message_list"></ul>
			</div>
			<h5>Status: <span id="connection_status"></span></h5>
			<form name="message_form">
				<div>
					<input type="text" name="message" id="message" required/>
					<button type="submit">Send</button>
				</div>
			</form>
		</fieldset>
	</body>
	<script>
		document.forms['connect_socket_form'].onsubmit = function(event){
			event.preventDefault();	
			let domain = `ws://${this.server_address.value}:${this.port_number.value}/chattingPHP/php-socket.php`;
			connect(domain);
			
		}
		document.forms['message_form'].onsubmit = function(event){
			event.preventDefault();			
			sendData(this.message.value);
		};
	</script>
</html>
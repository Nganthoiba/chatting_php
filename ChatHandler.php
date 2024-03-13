<?php
class ChatHandler {
	public $clientSocketArray;

	function __construct($clientSockets = []){
		$this->clientSocketArray = $clientSockets;
	}

	function send($message) {
		//global $clientSocketArray;
		$messageLength = strlen($message);
		foreach($this->clientSocketArray as $clientSocket)
		{
			@socket_write($clientSocket,$message,$messageLength);
		}
		return true;
	}

	function unseal($socketData) {
		$length = ord($socketData[1]) & 127;
		if($length == 126) {
			$masks = substr($socketData, 4, 4);
			$data = substr($socketData, 8);
		}
		elseif($length == 127) {
			$masks = substr($socketData, 10, 4);
			$data = substr($socketData, 14);
		}
		else {
			$masks = substr($socketData, 2, 4);
			$data = substr($socketData, 6);
		}
		$socketData = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$socketData .= $data[$i] ^ $masks[$i%4];
		}
		return $socketData;
	}

	function seal($socketData) {
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($socketData);
		
		if($length <= 125)
			$header = pack('CC', $b1, $length);
		elseif($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
		elseif($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
		return $header.$socketData;
	}

	function handshake($received_header,$client_socket, $host_name, $port) {
		$headers = array();
		$lines = preg_split("/\r\n/", $received_header);
		foreach($lines as $line)
		{
			$line = chop($line);
			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
				$headers[$matches[1]] = $matches[2];
			}
		}

		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$request = 'HTTP/1.1 101 Web Socket Protocol Handshake' . "\r\n" .
        "Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"WebSocket-Origin: $host_name\r\n" .
		"WebSocket-Location: ws://$host_name:$port/chattingPHP\r\n".
		"Sec-WebSocket-Accept:$secAccept\r\n".
		"\r\n";
		socket_write($client_socket,$request,strlen($request));
	}
	
	//acknowledgement for new connection
	function sendConnectionACK($client_ip_address) {
		$message = 'New client ' . $client_ip_address.' has joined';
		$messageArray = [
			'message'=>$message,
			'message_type'=>'connection-ack'
		];
		$ack = $this->seal(json_encode($messageArray));
		$this->send($ack);
	}
	
	//acknowledgement for disconnection
	function sendDisconnectionACK($client_ip_address) {
		$message = "Client " . $client_ip_address." has left";
		$messageArray = [
			'message'=>$message,
			'message_type'=>'disconnection-ack'
		];
		$ack = $this->seal(json_encode($messageArray));
		$this->send($ack);
	}

	function createSocketMessage($message){
		return $this->seal($message);
	}
}
?>
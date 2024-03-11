<?php
//program for php socket server
class Chat
{
	public function _readLine(){
		return rtrim(fgets(STDIN));
	}
}

$host = '192.168.137.1';
$port = '8080';

//create socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

//binding socket with host name(IP address) and port number
$result = socket_bind($socket, $host, $port);

//listen for connection
$listen = socket_listen($socket, SOMAXCONN);

do{
	$accept = socket_accept($socket);
	$msg = socket_read($accept, 1024);
	
	echo "Client says: \t {$msg}\n\n";
	
	$line = new Chat();
	echo "Enter reply:\t";
	$reply = $line->_readLine();
	
	socket_write($accept, $reply, strlen($reply));
}while(true);

socket_close($accept, $socket);

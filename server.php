<?php
include_once('config.php');
$null = NULL;

require_once("ChatHandler.php");


$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die('Could not create socket connection');
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
$result = socket_bind($socket, HOST_NAME, PORT) or die('Unable to bind socket.');


socket_listen($socket, SOMAXCONN);

echo "Listening for connection at ".HOST_NAME.", and port ".PORT."\n";

$chatHandler = new ChatHandler();
$chatHandler->clientSocketArray = [$socket];

while (true) {
	$newSocketArray = $chatHandler->clientSocketArray;

	socket_select($newSocketArray, $null, $null, 0, 10);
	
	if (in_array($socket, $newSocketArray)) {
		$newSocket = socket_accept($socket);
		$chatHandler->clientSocketArray[] = $newSocket;
		
		$header = socket_read($newSocket, 1024);
		$chatHandler->handshake($header, $newSocket, HOST_NAME, PORT);
		
		socket_getpeername($newSocket, $client_ip_address);
		$chatHandler->sendConnectionACK($client_ip_address);
		echo "Client with {$client_ip_address} joined.\n";
		$newSocketIndex = array_search($socket, $newSocketArray);
		unset($newSocketArray[$newSocketIndex]);
	}
	
	foreach ($newSocketArray as $activeSocket) {
		if(socket_recv($activeSocket, $socketData, 1024, 0) >= 1){
			$socketMessage = $chatHandler->unseal($socketData);
			echo "Client {$client_ip_address} says: {$socketMessage}\n";
			//Sending back whatever comes via socket to all other connected sockets for clients
			$chatHandler->sendMessage($socketMessage);
			break;
		}
		else{
			socket_getpeername($activeSocket, $client_ip_address);
			$chatHandler->sendDisconnectionACK($client_ip_address);

			//Disconnected sockets are to be removed the client sockets
			$newSocketIndex = array_search($activeSocket, $chatHandler->clientSocketArray);
			unset($chatHandler->clientSocketArray[$newSocketIndex]);	
			echo "Client with {$client_ip_address} is disconnected.\n";
		}
	}
}
socket_close($socket);
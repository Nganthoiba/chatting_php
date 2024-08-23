<?php
include('config.php');
// Set the IP address and port for the server to listen on
$host = HOST_NAME;
$port = PORT;

// Create a TCP Stream socket
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// Bind the socket to the address and port
if (!socket_bind($socket, $host, $port)) {
    die("Could not bind socket\n");
}

// Listen for connections
socket_listen($socket);

// Set the socket to non-blocking mode
socket_set_nonblock($socket);

// Array of clients
$clients = [];

echo "Server started at $host:$port...\n";

while (true) {
    // Accept new client connections
    $newClient = @socket_accept($socket);
    if ($newClient !== false) {
        // Add the new client to the list
        $clients[] = $newClient;
        echo "Client connected\n";
    }

    // Check each client for incoming data
    foreach ($clients as $index => $client) {
        $input = @socket_read($client, 1024, PHP_NORMAL_READ);
        if ($input === false) {
            // Remove disconnected clients
            unset($clients[$index]);
            socket_close($client);
            echo "Client disconnected\n";
            continue;
        }
        // If data is received, send it back to all clients
        if (trim($input)) {
            echo "Received: $input\n";
            $message = "Client says: $input\n";
            foreach ($clients as $otherClient) {
                if ($otherClient != $client) {
                    socket_write($otherClient, $message);
                }
            }
        }
    }

    // Sleep briefly to reduce CPU usage
    usleep(100000);
}

// Close the server socket
socket_close($socket);

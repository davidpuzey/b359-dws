<?php
// Constants
define("TIMEOUT",20);
define("DEBUG",false);

require('functions.php');
add_autoloader_dir('RequestHandler');
/*
// Functions
require('println.php');
require('server_commands.php');
require('request_handler_functions.php');

// Objects
require('general/dbConnection.php');
require('general/easySocket.php');
require('general/IOStream.php');
require('general/basicTimer.php');
require('general/clientConnection.php');
require('general/nodeData.php');
require('general/configureDatabase.php');
*/

// Globals
$address = "127.0.0.1";
$port_tcp = 23118;
$port_udp = 23119;
$num_clients = 0;
$timer_heartbeat = new basicTimer(1,true);
$clientConnections = array();
$iostream = new IOStream(0);
$process = true;
$serving = false;
$mysock_tcp = null;
$mysock_udp = null;
$database = new configureDatabase;
$database->setup(SERVER_REVIEW);

//nodeData::getInstance()->add_node(456,"Node","CPE1704TKS","something",123456,"/location/",time(),7,1);

// Main loop
echo "> ";
while ($process) {
	usleep(100000);
	
	if ($input = $iostream->clean_read()) {
		handle_input($input);
		if ($process)
			echo "\n> ";
	}
	
	if ($serving) {
		//listen_to_clients();
		
		handle_incoming_connections();
		/*
		if ($timer_heartbeat->expired())
			broadcast_heartbeat();
		*/
	}
}

/**
 * Creates a listening socket on the global $address and $port_tcp
 */
function start_listening() {
	global $address;
	global $port_tcp;
	global $port_udp;
	global $mysock_tcp;
	global $mysock_udp;
	global $serving;
	
	// Create listening TCP socket
	try {
		$mysock_tcp = new easySocket($address,$port_tcp,SOL_TCP,SOCKET_SERVER);
		$mysock_tcp->set_blocking(false);
		println("Opened TCP socket on port ".$port_tcp);
	} catch (Exception $e) {
		println("Failed to start server. Error: ".$e->getMessage());
		shut_down_everything();
		return;
	}

	// Create listening UDP socket
	try {
		$mysock_udp = new easySocket($address,$port_udp,SOL_UDP,SOCKET_SERVER);
		$mysock_udp->set_blocking(false);
		println("Opened UDP socket on port ".$port_udp);
	} catch (Exception $e) {
		println("Failed to start server. Error: ".$e->getMessage());
		shut_down_everything();
		return;
	}
	
	$serving = true;
	println("Server started");
}

/**
 * Closes the listening port
 */
function stop_listening() {
	shut_down_everything();
	println("Server stopped");
}

/**
 * Close all sockets and stop serving requests
 */
function shut_down_everything() {
	global $clientConnections;
	global $serving;
	global $mysock_tcp;
	global $mysock_udp;
	
	$serving = false;
	$mysock_tcp = null;
	$mysock_udp = null;
	sleep(1);
	$clientConnections = array();
	sleep(1);
}
/**
 * Returns whether the server has a listening socket running
 */
function is_listening() {
	global $mysock_tcp;
	return ($mysock_tcp != null);
}

/**
 * Lists information about all connected clients
 */
function clients_list() {
	global $clientConnections;
	
	if (count($clientConnections)) {
		println("There are ".count($clientConnections)." client(s) connected.");
		$i = 0;
		foreach($clientConnections as $clientConnection) {
			println("Client[$i] - ".$clientConnection->get_failures()." failures (max ".$clientConnection->get_max_failures().")");
			$i++;
		}
	} else
		println("No clients.");
}

/**
 * Handle new clients
 */
function handle_incoming_connections() {
	global $clientConnections;
	global $mysock_tcp;
	global $mysock_udp;
	global $num_clients;
	
	while ($newclient = $mysock_tcp->accept()) {
		$newclient->set_blocking(true);
		$input = $newclient->read();
		$response = handle_message($input);
		$newclient->write($response);
		var_dump($response);
		usleep(100);
	}
	
	if ($data = $mysock_udp->receive(8)) {
		// Handle UDP data
		handle_message($data['buffer']);
		//println("Received '".$data['buffer']."' from remote address ".$data['from']." and remote port ".$data['port']);
	}
}

/*
function listen_to_clients() {
	global $clientConnections;
	
	if (count($clientConnections)) {
		$i = 0;
		foreach($clientConnections as $key => $clientConnection) {
			$input = $clientConnection->get_socket()->read();
			if (!empty($input)) {
				// Handle TCP data
				handle_message($input);
				//println("Client[$i] says: $input");
			}
			$i++;
		}
	}
}
*/

/*
function broadcast_heartbeat() {
	global $clientConnections;
	
	if (count($clientConnections)) {
		$i = 0;
		foreach($clientConnections as $key => $clientConnection) {
			try {
				$clientConnection->get_socket()->write("Roll of dice: ".rand(1,6));
				$clientConnection->reset_failures();
			} catch (Exception $e) {
				$clientConnection->increment_failures();
				
				if (DEBUG) {
					println("Client[$i] heartbeat failed. Error: ".$e->getMessage());
					println("Client[$i] has failed ".$clientConnection->get_failures()." time(s)");
				}
				
				if ($clientConnection->get_failures() >= TIMEOUT) {
					// Boot the client off if it fails too often
					unset($clientConnections[$key]);
					println("Lost connection to Client[$i]");
				}
			}
			$i++;
		}
	}
}
*/
?> 
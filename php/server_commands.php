<?php
/**
 * UI input
 */
function handle_input($input) {
	global $iostream;
	global $process;
	global $address;
	global $port_tcp;
	global $port_udp;
	global $serving;

	$all_input = explode(" ",$input);
	switch ($all_input[0]) {
		case "hero":
		case "exit":
			$process = false;
			break;
		case "clients":
			clients_list();
			break;
		case "start":
			if (is_listening())
				println("Already started");
			else
				start_listening();
			break;
		case "stop":
			stop_listening();
			break;
		case "setup":
			if (count($all_input) >= 4) {
				if (is_listening())
					stop_listening();
				$address = $all_input[1];
				$port_tcp = $all_input[2];
				$port_udp = $all_input[3];
				println("Address set to $address\nTCP port set to $port_tcp\nUDP port set to $port_udp");
				start_listening();
			}
			break;
		case "info":
			println("Address is $address");
			println("TCP port is $port_tcp");
			println("UDP port is $port_udp");
			if ($serving)
				println("Server is running");
			else
				println("Server is not running");
			break;
		case "clear":
			for ($i=0;$i<100;$i++)
				echo("\n");
			break;
		case "help":
			println("help                             : Show this help.");
			println("exit                             : Exit the server program.");
			println("setup [IP] [TCP port] [UDP port] : Set the current IP and port, and start the server.");
			println("cmd [command] [IP] [TCP port]    : Send a command and dump the result.");
			println("start                            : Start the server with the current IP and port.");
			println("stop                             : Stop the server.");
			println("clients                          : List connected clients.");
			println("info                             : Display current IP and port configuration.");
			println("clear                            : Clear the screen.");
			break;
		case "cmd":
			if (count($all_input) >= 4) {
				$message = $all_input[1];
				$address = $all_input[2];
				$port = $all_input[3];
				
				$obj = new stdClass();
				$obj->cmd = $message;
				
				$reply = send_object_direct($obj,$address,$port);
				var_dump($reply);
			}
			break;
		default:
			println("Type 'help'.");
	}
}

function send_object($object,$uuid,$timeout = 3000000) {
	if ($record = nodeData::getInstance()->get_data_record($uuid)) {
		return send_object_direct($object,$record['address'],$record['port'],$timeout);
	} else {
		throw new Exception("Invalid UUID");
	}
}

function send_object_direct($object,$address,$port,$timeout = 3000000) {
	try {
		$mysock = new easySocket($address,$port,SOL_TCP,false);
		$mysock->set_blocking(true);
		$mysock->set_receive_timeout($timeout);
		$mysock->write(object_to_response($object));
	} catch (Exception $e) {
		//throw new Exception("Failed to send message. Error: ".$e->getMessage()."\n");
		return null;
	}
	if ($mysock) {
		$input = response_to_object($mysock->read());
	} else {
		$input = null;
	}
	return $input;
}
?>
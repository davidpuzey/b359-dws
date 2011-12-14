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
			println("help                             : This.");
			println("exit                             : Self explanatory.");
			println("setup [IP] [TCP port] [UDP port] : Set the current IP and port, and start the server.");
			println("start                            : Start the server with the current IP and port.");
			println("stop                             : Stop the server.");
			println("clients                          : List connected clients.");
			println("info                             : Display current IP and port configuration.");
			println("clear                            : Blankity blanks everywhere.");
			break;
		case "cmd":
			if (count($all_input) >= 4) {
				$message = $all_input[1];
				$address = $all_input[2];
				$port = $all_input[3];
				
				try {
					$mysock = new easySocket($address,$port,SOL_TCP,false);
					$mysock->set_blocking(true);
					
					// Set timeout to 4.5 seconds
					$mysock->set_receive_timeout(4500000);
					$mysock->write(object_to_response(array("cmd" => $message)));
				} catch (Exception $e) {
					echo("Failed to send message. Error: ".$e->getMessage()."\n");
				}
				if ($mysock)
					$input = $mysock->read();
				else
					$input = null;
				
				var_dump($input);
			}
			break;
		default:
			println("Type 'help'.");
	}
}
?>
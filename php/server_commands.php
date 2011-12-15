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
	global $server_name;

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
			println("Name is $server_name");
			println("Address is $address");
			println("TCP port is $port_tcp");
			println("UDP port is $port_udp");
			println("UUID is ".Settings::getInstance()->getParam("uuid", -1));
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
			println("help                              : Show this help.");
			println("exit                              : Exit the server program.");
			println("reset                             : Clears local data and stops the server.");
			println("setname [name]                    : Sets the server name.");
			println("setup [IP] [TCP port] [UDP port]  : Set the current IP and port, and start the server.");
			println("join [IP] [TCP port]              : Connect to the specified server");
			println("cmd [command] [IP] [TCP port]     : Send a command and dump the result.");
			println("start                             : Start the server with the current IP and port.");
			println("stop                              : Stop the server.");
			//println("clients                           : List connected clients.");
			println("nodes                             : List known nodes.");
			println("info                              : Display current IP and port configuration.");
			println("clear                             : Clear the screen.");
			break;
		case "setname":
			if (count($all_input) >= 2) {
				$server_name = $all_input[1];
				println("Set name to $server_name");
				set_my_data($server_name,$address,$port_tcp,$port_udp);
			} else {
				println("Incorrect parameters");
			}
			break;
		case "join":
			if (count($all_input) >= 3) {
				$address = $all_input[1];
				$port = $all_input[2];
				
				$temp_uuid = 0;
				nodeData::getInstance()->add_node($temp_uuid,"Temp","Temp",$address,"$port",0,0,"/",time(),0,0);
				$reply = message_send_hello($temp_uuid,true,Settings::getInstance()->getParam("uuid", -1));
				println("Reply:");
				var_dump($reply);
				nodeData::getInstance()->remove_node($temp_uuid);
			} else {
				println("Incorrect parameters");
			}
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
			} else {
				println("Incorrect parameters");
			}
			break;
		case "nodes":
			$node_data = nodeData::getInstance()->get_all_data();
			foreach ($node_data as $node) {
				//println($node['uuid']);
				var_dump($node);
			}
			break;
		case "reset":
			stop_listening();
			$cdb = new configureDatabase();
			$cdb->reset();
			unset($cdb);
			println("Server reset");
			break;
		default:
			println("Type 'help'.");
	}
}

function set_my_data($server_name,$ip,$port_tcp,$port_udp) {
	$server_type = "Node";
	$port_http = 0;
	$uri = "/";
	$uuid = Settings::getInstance()->getParam("uuid", -1);
	nodeData::getInstance()->remove_node($uuid);
	//nodeData::getInstance()->add_node(array("uuid" => $uuid, "server_type" => $server_type, "server_name" => $server_name, "host_name" => $ip, "port_tcp" => $port_tcp, "port_udp" => $port_udp, "port_http" => $port_http, "uri" => $uri, "last_response" => time(), "num_failures" => 0, "is_up" => 0));
	nodeData::getInstance()->add_node($uuid,$server_type,$server_name,$ip,$port_tcp,$port_udp,$port_http,$uri,time(),0,0);		
}

function send_object($object,$uuid,$timeout = 3000000) {
	if ($record = nodeData::getInstance()->get_data_record($uuid)) {
		return send_object_direct($object,$record[3],$record[4],$timeout);
		//return send_object_direct($object,$record['address'],$record['port_tcp'],$timeout);
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
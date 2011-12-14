<?php
	require("constants.php");
	require("message_functions.php");
	require("JSON.php");
	
	require('println.php');
	require('server_commands.php');
	require('request_handler_functions.php');
	
	$autoload_dirs = array();
	$hash_length = 32;
	$hash_salt = "saltthing";
	
	spl_autoload_register('autoloader');
	add_autoloader_dir("general/");
	
	function autoloader($name) {
		global $autoload_dirs;
		foreach ($autoload_dirs as $dir) {
			$file = "$dir/$name.php";
			if (file_exists($file)) {
				include($file);
				return;
			}
		}
	}
	
	function add_autoloader_dir($dir) {
		global $autoload_dirs;
		
		if (!is_dir($dir))
			return false;
		
		$autoload_dirs[] = $dir;
		return true;
	}
	
	function get_post_data($var, $default = null) {
		return (isset($_POST[$var])) ? $_POST[$var] : $default;
	}
	
	function get_get_data($var, $default = null) {
		return (isset($_GET[$var])) ? $_GET[$var] : $default;
	}
	
	function get_request_data($var, $default = null) {
		return (isset($_REQUEST[$var])) ? $_REQUEST[$var] : $default;
	}
	
	function make_hash($string) {
		global $hash_salt;
		return md5($string.$hash_salt);
	}
	
	function get_object_from_response($response) {
		global $hash_length;
		$response_array = explode(":", $response, 3);
		
		// Stops it erroring if the explode fails to produce 3 items
		if (count($response_array) == 3) {
			$error = $response_array[0];
			$hash = $response_array[1];
			$json = $response_array[2];
		} else {
			$error = 9001;
		}
		if ($error != "0") { # Pretend to do error handling stuff
			//echo "Error: Something somewhere went horribly wrong! (error handling to come)\n<br>\n";
			$obj = new stdClass();
			$obj->success = "false";
			
			ob_start();
			var_dump($response);
			$obj->info = "error code: ".$error." var_dump: ".ob_get_contents();
			ob_end_clean();
			return $obj;
		}
		if ($hash != make_hash($json)) {
			$obj = new stdClass();
			$obj->success = "false";
			$obj->info = "Hashes do not match.";
			return $obj;
		}
		return json_decode($json);
	}
	
	function get_node($uuid) {
		$db = new dbConnection;
		$result = $db->query("SELECT * FROM dws_nodes WHERE uuid = '$uuid'");
		return $result ? $result[0] : false;
	}
	
	// Prepares a generic object for sending a message
	function message_object_create($cmd, $options = null) {
		$obj = new stdClass();
		$obj->type = (int) SERVER_TYPE;
		$obj->cmd = $cmd;
		$obj->uuid = UUID;
		$obj = (object) array_merge((array) $obj, (array) $options);
		return $obj;
	}
	
	// Sends the specified object
	function object_send($obj, $dest, $no_encode_plz = false) {
		if ($no_encode_plz === true) {
			$json_string = $obj;
		} else {
			$json_string = json_encode($obj);
		}
		$json_hash = make_hash($json_string);
		
		$context = stream_context_create(array(
			'http' => array( 
				'method'  => 'POST', 
				'content' => http_build_query(array('hash' => $json_hash,'json' => $json_string)), 
				'header' => "Content-type: application/x-www-form-urlencoded\r\n", 
				'timeout' => 5, 
				), 
			));

		// Supress any warnings with the @ sign
		return @file_get_contents($dest, false, $context);
	}
	
	// Sends a generic object
	function message_send($cmd, $dest, $options = null) {
		return object_send(message_object_create($cmd, $options), $dest);
	}
	
	/**
	 * Returns the number of review servers we are aware of
	 */
	function get_num_review_servers() {
		$db = new dbConnection;
		$result = $db->query("SELECT uuid FROM dws_nodes WHERE server_type = ".SERVER_REVIEW);
		$num = count($result);
		unset($db);
		unset($result);
		return $num;
	}
	
	/**
	 * Returns the minimum number of servers across which any product/review will be replicated
	 * Requires the number of servers to be passed in as an argument
	 * The minimum number is 2/3rds of the number of servers, rounded up
	 */
	function get_min_num_replications($num_servers) {
		$replication_fraction = 2/3;
		return (integer) round($num_servers*$replication_fraction,0,PHP_ROUND_HALF_UP);
	}
	
	/**
	 * Calculates TTL based upon number of nodes and number of repetitions
	 */
	function calculate_initial_ttl() {
		// initial ttl = number of review servers - number of times records are replicated + 1
		$num_servers = get_num_review_servers();
		return $num_servers-get_min_num_replications($num_servers)+1;
	}
	
	/**
	 * $visited should be an array of UUIDs of servers that have already been visited
	 * A random server is then chosen from dws_nodes that is not in $visited, and is added to $visited
	 * The UUID of this random server is returned
	 * Our own server is also added to $visited
	 * NOTE: $visited is passed by reference, and so will be modified
	 */
	function choose_random_unvisited_server(&$visited) {
		// Add ourselves to the visited array if it is not already in there
		if (!in_array(UUID,$visited))
			array_push($visited,UUID);
		
		// Choose a random unvisited server
		$db = new dbConnection;
		$result = $db->query("SELECT uuid FROM dws_nodes WHERE server_type = ".SERVER_REVIEW." AND is_up = 1 AND uuid NOT IN (".implode(", ",$visited).")");
		if (count($result) > 0) {
			$next_dest = $result[rand(0,count($result)-1)]['uuid'];
			array_push($visited,$next_dest);
		} else {
			$next_dest = null;
		}
		unset($db);
		return $next_dest;
	}
	
	function get_dest_from_uuid($uuid) {
		$db = new dbConnection;
		$result = $db->query("SELECT * FROM dws_nodes WHERE uuid = '$uuid'");
		if (count($result) < 1) {
			return false;
		}
		//return "http://{$result[0]['host_name']}:{$result[0]['port']}/{$result[0]['uri']}";
		return compile_URL($result[0]['host_name'],$result[0]['port'],$result[0]['uri']);
	}
	
	function message_send_by_id($cmd, $uuid, $options = null) {
		return object_send(message_object_create($cmd, $options), get_dest_from_uuid($uuid)."request_handler.php");
	}
	
	/**
	 * Does what it says
	 */
	function drop_database() {
		@unlink("db/dws.db");
		Settings::getInstance()->deleteSettings();
	}
	
	/**
	 * Returns true if the database exists
	 * Not very efficient so call sparingly
	 */
	 function check_database_exists() {
	 	// Cannot simply check if the file exists, because any call to sqlite_open creates a blank database
	 	//return file_exists("db/dws.db");
	 	$db = new dbConnection;
	 	$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='dws_nodes'");
	 	$success = !(!$result || empty($result[0]['name']));
	 	unset($db);
	 	unset($result);
	 	return $success;
	 }

	 /**
	  * requireDbSetup - dies if database is not setup
	  */
	 function requireDbSetup() {
		if (!check_database_exists()) {
			die("Please <a href='setup.php'>set up</a> database.");
		}
	 }
	 
	/**
	 * Set whether the server should handle requests. If false it will time out on all calls to request_handler.php
	 */
	 function set_server_running($r) {
	 	if ($r != true && $r != false) {
	 		die("set_database_running() parameter must be true or false");
	 	} else {
	 		$db = new dbConnection;
	 		$result = $db->query("UPDATE dws_nodes SET is_up = '$r' WHERE uuid = ".UUID);
	 		unset($db);
	 		unset($result);
	 		
	 		// Tell everyone we are alive
	 		broadcast_send_hello();
	 	}
	 }
	 
	/**
	 * Returns boolean of whether the server is handling incoming requests
	 */
	 function get_server_running() {
		 	$db = new dbConnection;
		 	$query = "SELECT is_up FROM dws_nodes WHERE uuid = ".UUID;
		 	$result = $db->query($query);
		 	$is_up = $result[0]['is_up'];
		 	unset($db);
		 	unset($result);
		 	return $is_up;
	 	}
	 
	/**
	 * Create uuid constant
	 */
	function get_my_details() {
		$db = new dbConnection;
		//$query = "SELECT host_name, port, uri, server_name, server_type, uuid FROM dws_nodes WHERE host_name = '{$_SERVER['SERVER_NAME']}' AND port = '{$_SERVER['SERVER_PORT']}' AND uri = '".dirname($_SERVER['REQUEST_URI'])."/'";
		$meta = Settings::getInstance()->getParam("uuid", -1);
		$query = "SELECT host_name, port, uri, server_name, server_type, uuid FROM dws_nodes WHERE uuid = '$uuid'";
		$result = $db->query($query);
		if (count($result) != 1) {
			echo($query);
			var_dump($result);
			die("<p>This server does not exist. Please <a href='setup.php'>run setup</a></p>");
		} else {	
			return $result[0];
		}
	}
	
	/*
	if (check_database_exists()) {
		$details = get_my_details();
		define("UUID", $details['uuid']);
		define("SERVER_TYPE", $details['server_type']);
		define("SERVER_NAME", $details['server_name']);
		define("HOST_NAME", $details['host_name']);
		define("PORT", $details['port']);
		define("URI", $details['uri']);
	} else {*/
		define("UUID", -1);
		define("SERVER_TYPE", -1);
		define("SERVER_NAME", null);
		define("HOST_NAME", null);
		define("PORT", -1);
		define("URI", null);
	/*}*/
	
	/**
	 * $nodes is an object containing lots of nodes to be added to dws_nodes
	 */
	function nodes_add($nodes, $update = false) {
		$db = new dbConnection;
		// Add each node into dws_nodes
		foreach ($nodes as $node) {
			// Don't add it if the uuid already exists
			$uuid = $node->uuid;
			$server_type = $node->server_type;
			$server_name = $node->server_name;
			$host_name = $node->host_name;
			$port = $node->port;
			$uri = $node->uri;
			$last_response = $node->last_response;
			$num_failures = 0;
			$is_up = $node->is_up;
			
			//DEBUG
			//echo("<p>Adding node; uuid = $uuid, server_name = $server_name</p>");
			
			$result = $db->query("SELECT uuid FROM dws_nodes WHERE uuid = ".$uuid);
			if (!$result) {
				// Node does not exist, add it into the table
				$db->query("INSERT INTO dws_nodes (uuid, server_type, server_name, host_name, port, uri, last_response, num_failures, is_up) VALUES ('$uuid', '$server_type', '$server_name', '$host_name', '$port', '$uri', '$last_response', '$num_failures', '$is_up')");
			} else if ($update) {
				// Node does exist, and we want to update it
				// IMPORTANT: num_failures is not updated to avoid it being reset
				$db->query("UPDATE dws_nodes SET (server_type = '$server_type', server_name = '$server_name', host_name = '$host_name', port = '$port', uri = '$uri', last_response = '$last_response', is_up = '$is_up') WHERE uuid = '$uuid'");
			}
		}
		unset($db);
	}
	
	function node_matrix_change_primary($review_uuid) {
		return node_matrix_update(UUID, $review_uuid);
	}
	
	function node_matrix_update($client_uuid, $review_uuid) {
		add_autoloader_dir("RequestHandler");
		$thing = new RH_updateNodeMatrix(array("cmd" => "updateNodeMatrix", "type" => SERVER_TYPE, "client_uuid" => $client_uuid, "review_uuid" => $review_uuid));
		$obj = (object) $thing->process();
		return $obj;
	}
	
	/**
	 * DEPRECATED
	 * Updates dws_node_matrix to link $client_uuid with $review_uuid
	 * If $remove is true then the relationship is deleted
	 */
	function node_matrix_set($client_uuid,$review_uuid,$remove = false) {
		$db = new dbConnection;
		//$result = $db->query("SELECT * FROM dws_node_matrix WHERE client_uuid = $client_uuid AND review_uuid = $review_uuid") or die("dws_node_matrix SELECT failed");

		
		$query = "SELECT * FROM dws_node_matrix";// WHERE client_uuid = '$client_uuid' AND review_uuid = '$review_uuid'";
		$result = $db->query($query);
		
		$result = false;
		if (!$result && !$remove) {
			// Relationship does not exist, so insert into table
			$query = "INSERT INTO dws_node_matrix VALUES ($client_uuid, $review_uuid)";
			$db->query($query);
		} else {
			// Relationship does exist...
			if ($remove) {
				// ...so delete it
				$db->query("DELETE FROM dws_node_matric WHERE client_uuid = '$client_uuid'");
			} else {
				// ...so update it
				$db->query("UPDATE dws_node_matrix SET review_uuid = '$review_uuid' WHERE client_uuid = '$client_uuid'");
			}
		}
	}
	
	/**
	 * Generate URL from supplied host name, port and uri. e.g. "something.com". "80" and "/folder/" go to "http://something.com:80/folder/".
	 */
	function compile_URL($host_name,$port,$uri) {
		return "http://".$host_name.":".$port."/".$uri."/";
	}
	
	/**
	 * Generate request_handler URL from supplied host name, port and uri. e.g. "something.com". "80" and "/folder/" go to "http://something.com:80/folder/request_handler.php".
	 */
	function compile_request_handler_URL($host_name,$port,$uri) {
		return compile_URL($host_name,$port,$uri)."request_handler.php";
	}
	
	/**
	 * Add server specific objects
	 */
	switch (SERVER_TYPE) {
		case SERVER_CLIENT:
			add_autoloader_dir("ClientObjects/");
			include("client_functions.php");
			break;
		case SERVER_REVIEW:
			add_autoloader_dir("ReviewObjects/");
			include("review_functions.php");
			break;
	}
	
	function type_decode($t) {
		switch ($t) {
			case SERVER_CLIENT:
				return "Client Server";
			break;
			case SERVER_REVIEW:
				return "Review Server";
			break;
		}
	}
?>

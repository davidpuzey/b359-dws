<?php
// requires functions.php

/**
 * Sends a message to $dest
 * $dest then gets all nodes in its dws_nodes to say hello back
 * Set $return_list to true and $dest will reply with a list containing its dws_nodes table
 */
 function message_send_hello($uuid, $return_list = false, $use_this_uuid = null) {
	// Use the defined UUID if none is specified
	if (!isset($use_this_uuid))
		$use_this_uuid = UUID;
		
	// Retrieve necessary items from the database
	$db = new dbConnection;
	$result = $db->query("SELECT port_tcp, port_udp, port_http, uri, server_name, server_type FROM dws_nodes WHERE uuid = ".$use_this_uuid);
	
	// Add host_name, port and uri to the message
	$obj = message_object_create("hello");
	$obj->host_name = $_SERVER['SERVER_NAME'];
	$obj->server_name = $result[0]['server_name'];
	$obj->port_tcp = $result[0]['port_tcp'];
	$obj->port_udp = $result[0]['port_udp'];
	$obj->port_http = $result[0]['port_http'];
	$obj->uri = $result[0]['uri'];
	$obj->return_list = $return_list;
	
	// Override the uuid from message_object_create with the one we want to use
	$obj->uuid = $use_this_uuid;
	
	// Override the server_type
	$obj->type = (integer) $result[0]['server_type'];

	// Send object, clear objects and return the reply
	$reply = send_object($obj,$uuid);
	unset($obj);
	unset($db);
	return $reply;
}

/**
 * calls message_send_hello() on all servers in dws_nodes
 */
function broadcast_send_hello($return_list = false, $use_this_uuid = null){
	// Use the defined UUID if none is specified
	if (!isset($use_this_uuid))
		$use_this_uuid = UUID;
		
	// Retrieve necessary items from the database
	$db = new dbConnection;
	$nodes = $db->query("SELECT uuid, host_name, port_tcp, uri FROM dws_nodes");
	foreach ($nodes as $node) {
		message_send_hello($node['uuid'],$return_list,$use_this_uuid);
	}
}

/**
 * Sends the entirety of dws_node_matrix to $dest
 */
function message_send_matrix($uuid,$return_list = false,$use_this_uuid = false) {
	// Use the defined UUID if none is specified
	if (!isset($use_this_uuid))
		$use_this_uuid = UUID;
	
	// Retrieve our entire node matrix
	$db = new dbConnection;
	$result = $db->query("SELECT * FROM dws_node_matrix");
	
	// Create object with relevant information
	$obj = message_object_create("matrix");
	$obj->return_list = $return_list;
	
	// Override the uuid from message_object_create with the one we want to use
	$obj->uuid = $use_this_uuid;
	
	// Add the matrix
	$obj->matrix = $result;
	
	$reply = send_object($obj,$uuid);
	unset($obj);
	unset($db);
	return $reply;
}

function message_send_goodbye($uuid) {
	return message_send_by_id("goodbye",$uuid);
}

function message_send_heartbeat($uuid) {
	return message_send_by_id("heartbeat",$uuid);
}

function message_broadcast_heartbeat() {
	// OBSOLETE
	/*
	$db = new dbConnection;
	$obj = message_object_create("heartbeat");
	$result = $db->query("SELECT uuid, host_name, port, uri FROM dws_nodes");
	
	foreach ($result as $row) {
		$obj->your_uuid = $row['uuid'];
		var_dump($obj);
		$reply = object_send($obj, "http://".$row['host_name'].":".$row['port']."/".$row['uri']."/request_handler.php");
		$reply_obj = get_object_from_response($reply);
		echo("Success: ".$reply_obj->success."<br />");
		if ($reply_obj->success != "true") {
			echo("Info: ".$reply_obj->info."<br />");
			// Increase number of failures
			
		}
		unset($reply_obj);
	}
	unset($db);
	unset($obj);
	*/
}
?>
<?php

// require('dbConnection.php);
class configureDatabase {
	function setup($server_type) {
		if (!$this->is_setup()) {
			if ($server_type == SERVER_REVIEW) {
				$sql[] = "CREATE TABLE dws_products(id integer, node_id integer, user_id text, name text, description text, image_url text, category text, rating real, timestamp integer, PRIMARY KEY (id, node_id))";
				$sql[] = "CREATE TABLE dws_reviews(id integer, node_id integer, product_id text, user_id text, review text, rating integer, timestamp integer, PRIMARY KEY (id, node_id))";
				$sql[] = "CREATE TABLE dws_users(id integer, node_id integer, name text, password text, email text, time_joined integer, PRIMARY KEY (id, node_id))";
				
				// Default user
				$sql[] = "INSERT INTO dws_users (id, node_id, name, password, email, time_joined) VALUES ('0', '0', 'Anonymous', '', '', '0')"; // Anonymous user for testing purposes
			}
			
			$sql[] = "CREATE TABLE dws_message_queue(id integer PRIMARY KEY, message text, num_failures integer, timestamp integer);";
			
			// Stores review servers and clients
			$sql[] = "CREATE TABLE dws_nodes(uuid integer, server_type text, server_name text, host_name text, port integer, uri text, last_response integer, num_failures integer, is_up integer, PRIMARY KEY (uuid))";
		
			// Which clients talk to which review servers
			$sql[] = "CREATE TABLE dws_node_matrix(client_uuid integer PRIMARY KEY, review_uuid integer)";
			
			$db = new dbConnection;
			foreach ($sql as $tbl) {
				if ($db->query($tbl) === false) {
					die("Cannot execute query. $error");
				}
			}
		}
	}
	
	function drop() {
		@unlink("db/dws.db");
	}
	
	 function is_setup() {
	 	// Cannot simply check if the file exists, because any call to sqlite_open creates a blank database
	 	$db = new dbConnection;
	 	$result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='dws_nodes'");
	 	$success = !(!$result || empty($result[0]['name']));
	 	unset($db);
	 	unset($result);
	 	return $success;
	 }
}
?>
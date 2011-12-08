<?php
	class heartbeat {
		function __construct() {
			$db = new dbConnection;
			$nodes = $db->query("SELECT uuid, host_name, port, uri FROM dws_nodes");
			foreach ($nodes as $node) {
				// Don't talk to yourself
				//TODO: Only do it to review servers
				if ($node['uuid'] != UUID) {
					//echo("<p><i>Sending heartbeat to ".$node['uuid']."</i></p>");
					$dest = compile_request_handler_URL($node['host_name'],$node['port'],$node['uri']);
					$this->send_heartbeat($db,$dest,$node['uuid']);
				}
			}
		}
		private function send_heartbeat($db,$dest,$their_uuid) {
			//$db = $this->db;
			//var_dump($db);
			$timestamp = time();
			$reply = message_send("heartbeat", $dest, array("your_uuid" => $their_uuid));
			//echo("<p><b>MESSAGE FROM ".UUID." TO ".$their_uuid." START REPLY</b>".$reply."<b>END REPLY</b></p>");
			$obj = get_object_from_response($reply);
			if ($obj->success == "true") {
				// Got a successful response from the heartbeat, so update the database
				//echo("<p>Heartbeat: Success from ".$their_uuid."</p>");
				$db->query("UPDATE dws_nodes SET last_response = '{$timestamp}' WHERE uuid = '{$their_uuid}'");
			} else if ($obj->success == "false") {
				// Got a response that was a failure, this means the uuids do not match, so delete them
				//echo("<p>Heartbeat: Failure from ".$their_uuid.". Info: ".$obj->info."</p>");
				$db->query("DELETE FROM dws_nodes WHERE uuid = '{$their_uuid}'");
			} else {
				// Did not get a response, this means there may be a lot of latency
				//echo("<p>Heartbeat: No response from ".$their_uuid."</p>");
				//Increase the failure counter
				$result = $db->query("SELECT num_failures FROM dws_nodes WHERE uuid = '{$their_uuid}'");
				$num_failures = $result[0]['num_failures']+1;
				$db->query("UPDATE dws_nodes SET num_failures = '{$num_failures}' WHERE uuid = '{$their_uuid}'");
			}
		}
	}
?>
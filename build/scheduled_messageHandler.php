<?php
require('functions.php');

/**
 * This processes all items in dws_message_queue, and sends them to all other servers in dws_nodes except itself
 */
$db = new dbConnection;
$queue = $db->query("SELECT id, message, num_failures FROM dws_message_queue");
$nodes = $db->query("SELECT uuid FROM dws_nodes WHERE uuid <> ".UUID);
foreach ($queue as $item) {
	foreach ($nodes as $node) {
		$reply = get_object_from_response(object_send($item['message'],get_dest_from_uuid($node['uuid'])."request_handler.php",true));
		var_dump($reply);
		$delete = false;
		if ($reply->success == "true") {
			// Successful, remove from queue
			$delete = true;
		} else {
			// Failed, increase failure number
			$num_failures = $item['num_failures']+1;
			if ($num_failures >= 10) {
				$delete = true;
			} else {
				$db->query("UPDATE dws_message_queue SET num_failures = $num_failures WHERE id = ".$item['id']);
			}
		}
		if ($delete === true) {
			$db->query("DELETE FROM dws_message_queue WHERE id = ".$item['id']);
		}
	}
}
?>
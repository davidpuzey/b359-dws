<?php
	/**
	 * BroadcastRequestHandler -	Will broadcast the command to
	 *								all other nodes.
	 */
	class BroadcastRequestHandler extends RequestHandler {
		/**
		 * broadcast -	Takes the command object and sends it out
		 *				to all other nodes, assuming the command
		 *				has not already been broadcast.
		 */
		function broadcast() {
			if ($this->getParam('is_broadcast', false) == "true")
				return true;
			$this->setParam('is_broadcast', "true");
			if ($this->getParam('send_to_all', false) != "true")
				$this->setParam('send_to_all', "false");
			#$message = json_encode($this->obj);
			#$timestamp = time();
			#$this->query("INSERT INTO dws_message_queue (message, timestamp) VALUES ('$message', '$timestamp')");
			$nodes = nodeData::getInstance()->get_all_data();
			foreach ($nodes as $node) {
				if ($node['server_type'] == SERVER_REVIEW) {
					send_object($this->obj, $node['uuid']);
				}
			}
		}
		
		/**
		 * process - Remake of the process method to add boardcast.
		 */
		function process() {
			parent::process();
			$this->broadcast();
			return $this->responseVar;
		}
	}
?>
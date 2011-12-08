<?php
	class BroadcastRequestHandler extends RequestHandler {
		function broadcast() {
			if ($this->getParam('is_broadcast', false) == "true")
				return true;
			$this->setParam('is_broadcast', "true");
			if ($this->getParam('send_to_all', false) != "true")
				$this->setParam('send_to_all', "false");
			$message = json_encode($this->obj);
			$timestamp = time();
			$this->query("INSERT INTO dws_message_queue (message, timestamp) VALUES ('$message', '$timestamp')");
		}
		
		function process() {
			parent::process();
			$this->broadcast();
			return $this->responseVar;
		}
	}
?>
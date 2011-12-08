<?php
	class RH_heartbeat extends RequestHandler {	
		function response() {
			/*
			* Updates the last_response time for uuid in dws_nodes
			*/
			$timestamp = time();
			$my_uuid = $this->getRequiredParam('your_uuid');
			$uuid = $this->getRequiredParam('uuid');
			
			if ($this->checkErrors())
				return false;
			if ($my_uuid == UUID) {
				// uuids match, update and return success
				$query = "UPDATE dws_nodes SET last_response = '$timestamp' WHERE uuid = '$uuid'";
				$this->query($query);
				return true;
			} else {
				// uuids do not match, tell them this
				$this->appendInfo("uuid mismatch. Was meant for ".$my_uuid.", I am ".UUID);
				return false;
			}
		}
	}
?>
<?php
	class RH_init extends RequestHandler {
		function response() {
			/*
			* Initalises this server with the supplied uuid and type
			* Returns false if it already exists
			*/
			$uuid = $this->getRequiredParam('uuid');
			$server_type = $this->getRequiredParam('type');
			
			if ($this->checkErrors())
				return false;
			
			if ($this->query("SELECT * FROM dws_nodes WHERE uuid = '$uuid'")) {
				$this->appendInfo("Unique ID already exists in table");
				return true
			}
			else {
				$this->query("INSERT INTO dws_nodes (uuid, server_type, ip, last_response, num_failures, is_up, is_me) VALUES ('$uuid', '$server_type', '', 0, 0, 1, 1)");
			}
			return true;
		}
	}
?>
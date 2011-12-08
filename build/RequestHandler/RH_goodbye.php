<?php
	class RH_goodbye extends RequestHandler {
		function response() {
			/*
			* Deletes the record for uuid from dws_nodes
			*/
			$uuid = $this->getRequiredParam('uuid');
			
			if ($this->checkErrors())
				return false;
			
			$this->query("DELETE FROM dws_nodes WHERE uuid = '$uuid'");
			return true;
		}
	}
?>
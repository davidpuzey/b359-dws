<?php
	class RH_matrix extends RequestHandler {	
		function response() {
			/*
			Updates our dws_node_matrix with the matrix it receives
			Sends back our entire matrix if necessary
			*/
			$uuid = $this->getRequiredParam("uuid");
			$matrix = $this->getRequiredParam("matrix");
			$return_list = $this->getRequiredParam("return_list");
			
			if ($this->checkErrors())
				return false;
			
			// Add the received nodes to our matrix
			foreach ($matrix as $item) {
				node_matrix_set($item->client_uuid,$item->review_uuid,false);
			}
			
			// Return the list to them
			if ($return_list === true) {
				//$dest = get_dest_from_uuid($uuid)."request_handler.php";
				if ($dest) {
					$reply = message_send_matrix($uuid, false);
				} else {
					$this->appendInfo("Supplied uuid $uuid is not in our dws_nodes table");
					return false;
				}
			}
			return true;
		}
	}
?>
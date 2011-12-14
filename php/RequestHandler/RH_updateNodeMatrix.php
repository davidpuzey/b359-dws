<?php
	class RH_updateNodeMatrix extends BroadcastRequestHandler {
		function response() {
			$client_uuid = $this->getRequiredParam("client_uuid");
			$review_uuid = $this->getRequiredParam("review_uuid");
			if ($this->checkErrors())
				return false;
			
			$result = $this->query("SELECT uuid FROM dws_nodes WHERE uuid = '$review_uuid' AND server_type = '".SERVER_REVIEW."'");
			if ($result === false || count($result) <= 0) {
				$this->addError("Review server '$review_uuid' doesn't exist.");
				return false;
			}
			$result = $this->query("SELECT client_uuid FROM dws_node_matrix WHERE client_uuid = '$client_uuid'");
			if ($result === false) {
				$result = $this->query("INSERT INTO dws_node_matrix (client_uuid, review_uuid) VALUES ('$client_uuid', '$review_uuid')");
				if ($result === false) {
					$this->addError("Node matrix could not be added for client '$client_uuid' with review server '$review_uuid'.");
					return false;
				}
			} else {
				$result = $this->query("UPDATE dws_node_matrix SET review_uuid = '$review_uuid' WHERE client_uuid = '$client_uuid'");
				if ($result === false) {
					$this->addError("Node matrix could not be updated for client '$client_uuid' with review server '$review_uuid'.");
					return false;
				}
			}
			
			$this->updateResponse('result', $result);
			
			$this->setParam("send_to_all", "true");
			
			return true;
		}
	}
?>
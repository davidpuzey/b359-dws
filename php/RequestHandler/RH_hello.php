<?php
	class RH_hello extends RequestHandler {
		function response() {
			/*
			* Checks if the uuid exists already in dws_nodes
			* returns failure if it does
			* else
			* inserts data about the node into dws_nodes and returns success
			*/
			
			$uuid = $this->getRequiredParam('uuid');
			$server_type = $this->getRequiredParam('type');
			$host_name = $this->getRequiredParam('host_name');//$_SERVER['REMOTE_HOST'];
			$last_response = time();
			$server_name = $this->getRequiredParam('server_name');
			$port_tcp = $this->getRequiredParam('port_tcp');
			$port_udp = $this->getRequiredParam('port_udp');
			$port_http = $this->getRequiredParam('port_http');
			$uri = $this->getParam('uri','');
			$return_list = $this->getParam('return_list', false);
			
			if ($this->checkErrors())
				return false;
			
			$this->updateResponse("uuid", UUID);
			
			if ($this->query("SELECT * FROM dws_nodes WHERE uuid = '$uuid'")) {
				$time = time();
				$this->query("UPDATE dws_nodes SET last_response = '$time', num_failures = '0', is_up = '1' WHERE uuid = '$uuid'");
				$this->appendInfo("Unique ID already exists in table");
				return false;
			}
			else {
				$this->query("DELETE FROM dws_nodes WHERE host_name = '$host_name' AND port_tcp = '$port_tcp' AND port_udp = '$port_udp' AND port_http = '$port_http'");
				$sql = "INSERT INTO dws_nodes (uuid, server_type, server_name, host_name, port_tcp, port_udp, port_http, uri, last_response, num_failures, is_up) VALUES ('$uuid', '$server_type', '$server_name', '$host_name', '$port_tcp', '$port_udp', '$port_http', '$uri', '$last_response', 0, 1)";
				$this->query($sql);
			}
			
			if ($return_list) {
				$response = $this->query("SELECT * FROM dws_nodes");
				$this->updateResponse("nodes", $response);
			}
			
			return true;
		}
	}
?>
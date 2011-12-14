<?php
//require('dbConnection.php');

class nodeData {
	function __construct() {
		// Create arrays of all data
		$this->db = new dbConnection;
		$node_data = $this->db->query("SELECT * FROM dws_nodes");
		$this->node_data = array();
		
		foreach ($node_data as $value) {
			$this->node_data[$value['uuid']] = $value;
		}
	}
	
	function __destruct() {
		unset($this->db);
	}
	
	function getInstance() {
		static $instance;
		if (!is_object($instance) || get_class($instance) !== __CLASS__) {
			$obj = __CLASS__;
			$instance = new $obj;
		}
		return $instance;
	}
	
	function get_data($uuid,$data) {
		if (!array_key_exists($uuid,$this->node_data)) {
			throw new Exception("Invalid UUID");
		}
		if (!array_key_exists($data,$this->node_data[$uuid])) {
			throw new Exception("Invalid data");
		}
		return $this->node_data[$uuid][$data];		
	}
	
	function set_data($uuid,$data,$value) {
		if (!array_key_exists($uuid,$this->node_data)) {
			throw new Exception("Invalid UUID");
		}
		if (!array_key_exists($data,$this->node_data[$uuid])) {
			throw new Exception("Invalid data");
		}
		$this->node_data[$uuid][$data] = $value;
		
		// Keep database updated
		$this->db->query("UPDATE dws_nodes SET $data = '$value' WHERE uuid = '$uuid'");
		
		// Update UUID key if necessary
		if ($data == 'uuid' && $uuid != $value) {
			$this->node_data[$value] = $this->node_data[$uuid];
			unset($this->node_data[$uuid]);
		}
	}
	
	function add_node() {
		// Check a UUID exists
		$node_data = func_get_args();
		if (!$new_uuid = $node_data[0]) {
			throw new Exception("No UUID supplied");
		}
		
		// Check the UUID is not already used
		if ($this->db->query("SELECT uuid FROM dws_nodes WHERE uuid = '$new_uuid'")) {
			throw new Exception("UUID '$new_uuid' already exists in database");
		}
		
		// Update the database
		$imploded = "'".implode("','",$node_data)."'";
		$this->db->query("INSERT INTO dws_nodes VALUES (".$imploded.")");
		
		// Update our array
		$this->node_data[$new_uuid] = $node_data;
	}
	
	function remove_node($uuid) {
		unset($this->node_data[$uuid]);
		$this->db->query("DELETE FROM dws_nodes WHERE uuid = $uuid");
	}
}
?>
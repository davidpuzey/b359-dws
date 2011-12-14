<?php
	class Settings {
		function __construct() {
			if (!file_exists(CONFIG_INI)){}
			$this->settings = parse_ini_file("configuration.ini");
		}
		
		function getInstance() {
			static $instance;
 			if (!is_object($instance) || get_class($instance) !== __CLASS__) {
				$obj = __CLASS__;
				$instance = new $obj;
			} 
			return $instance;
		}
	}
?>
<?php
class clientConnection {
	function __construct($mysock) {
		$this->mysock = $mysock;
		$this->failures = 0;
		$this->max_failures = 0;
		$this->max_failures_time = time();
	}
	
	function get_socket() {
		return $this->mysock;
	}
	
	function increment_failures() {
		$this->failures++;
		if ($this->failures > $this->max_failures) {
			$this->max_failures = $this->failures;
			$this->max_failures_time = time();
		}
	}
	
	function get_failures() {
		return $this->failures;
	}
	
	function get_max_failures() {
		return $this->max_failures;
	}
	
	function get_max_failures_time() {
		return $this->max_failures_time;
	}
	
	function reset_failures() {
		$this->failures = 0;
	}
}
?>
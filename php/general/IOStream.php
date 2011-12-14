<?php
class IOStream {
	function __construct($blocking = 1) {
		$this->stdin = fopen('php://stdin','r');
		$this->set_blocking($blocking);
	}
	
	function __destruct() {
		fclose($this->stdin);
	}
	
	function set_blocking($blocking) {
		stream_set_blocking($this->stdin, $blocking);
	}
	
	function read() {
		return fgets($this->stdin);
	}
	
	function write($msg) {
		echo $msg;
	}
	
	function writeln($msg) {
		$this->write($msg."\n");
	}
	
	function clean_read() {
		return trim($this->read());
	}
}
?>
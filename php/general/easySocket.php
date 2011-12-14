<?php

define("SOCKET_SERVER",true);
define("SOCKET_CLIENT",false);

class easySocket {
	/**
	 * basic_socket($address, $port, $protocol, SOCKET_SERVER)
	 * Create a listening socket (e.g. server)
	 *
	 * basic_socket($address, $port, $protocol, SOCKET_CLIENT)
	 * Connect to a socket (e.g. client)
	 *
	 * basic_socket($socket)
	 * Create a basic_socket using $socket
	 */
	function __construct() {
		if (func_num_args() == 4) {
			$this->address = func_get_arg(0);
			$this->port = func_get_arg(1);
			$this->protocol = func_get_arg(2);
			
			switch ($this->protocol) {
				case SOL_TCP:
					$this->mysock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
					break;
				case SOL_UDP:
					$this->mysock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
					break;
				default:
					throw new Exception("Invalid protocol: $protocol.");
					break;
			}
			
			if ($this->mysock === false) {
				throw new Exception("socket_create() failed. Reason: " . socket_strerror(socket_last_error($this->mysock)));
			} else {
				$socket_type = func_get_arg(3);
				if ($socket_type == SOCKET_SERVER) {
					// Server
					$bind = @socket_bind($this->mysock, $this->address, $this->port);
					if ($bind == false) {
						throw new Exception("socket_bind() failed. Reason: " . socket_strerror(socket_last_error($this->mysock)));
					}
					
					// UDP does not use this
					if ($this->protocol == SOL_TCP) {
						socket_listen($this->mysock, 5);
					}
				} elseif ($socket_type == SOCKET_CLIENT) {
					// Client
					if ($this->protocol == SOL_TCP) {
						$result = @socket_connect($this->mysock, $this->address, $this->port);
						if ($result === false) {
							throw new Exception("socket_connect() failed. Reason: " . socket_strerror(socket_last_error($this->mysock)));
						}
					}
				} else {
					throw new Exception("Invalid socket type: ".$socket_type);
				}
			}
		} else if (func_num_args() == 1) {
			$this->mysock = func_get_arg(0);
			$this->address = null;
			$this->port = null;
		} else {
			throw new Exception("Invalid number of parameters.");
		}
	}
	
	function set_blocking($b) {
		if ($b)
			socket_set_block($this->mysock);
		else
			socket_set_nonblock($this->mysock);
	}
	
	/**
	 * Close the socket
	 */
	function __destruct() {
		socket_close($this->mysock);
	}
	
	function write($msg) {
		$result = @socket_write($this->mysock,$msg,strlen($msg));
		if ($result === false) {
			throw new Exception("socket_write() failed. Reason: " . socket_strerror(socket_last_error($this->mysock)));
		}
		return $result;
	}
	
	function read() {
		$result = socket_read($this->mysock,2048);
		
		// Sleep for quarter of a second to avoid concatenation of strings
		//usleep(250000);
		return $result;
	}
	
	function sendto($msg, $address, $port) {
		return socket_sendto($this->mysock,$msg,strlen($msg),0,$address,$port);
	}
	
	/**
	 * Returns a new basic_socket if a connection is accepted, else returns null
	 * Only works for TCP
	 */
	function accept() {
		if ($this->protocol === SOL_TCP) {
			if ($client = @socket_accept($this->mysock)) {
				return new easySocket($client);
			} else {
				return null;
			}
		}
	}
	
	/**
	 * Returns an array of the received data, or null if nothing received
	 * Only works for UDP
	 */
	function receive($length) {
		if ($this->protocol === SOL_UDP) {
			$from = '';
			$port = 0;
			if (@socket_recvfrom($this->mysock,$buf,$length,0,$from,$port) > 0) {
				return array("buffer" => $buf, "from" => $from, "port" => $port);
			} else {
				return null;
			}
		}		
	}
}
?>
<?php
// Functions
require('println.php');

// Requires
require('easySocket.php');
require('IOStream.php');

// Globals
$address = "127.0.0.1";
$port = 23119;
$process = true;
$failures = 0;
$iostream = new iostream(1);

println("Enter the IP to connect to (default 127.0.0.1)");
echo "> ";
$input = $iostream->clean_read();
if ($input !== "")
	$address = $input;
println("IP set to: $address");

println("Enter the port to use (default 23119)");
echo "> ";
$input = $iostream->clean_read();
if ($input !== "")
	$port = $input;
println("Port set to: $port");

println("Connecting to server...");

try {
	$mysock = new easySocket($address,$port,SOL_UDP,SOCKET_CLIENT);
	$mysock->sendto("sockets are easy",$address,$port);
} catch (Exception $e) {
	die("Failed to start client. Error: ".$e->getMessage()."\n");
}

// Connect to server
/*
try {
	$mysock = new easySocket($address,$port,SOL_TCP,false);
	$mysock->write("Hello server");
} catch (Exception $e) {
	die("Failed to start client. Error: ".$e->getMessage()."\n");
}

while ($process) {
	$input = $mysock->read();
	if (empty($input)) {
		$failures++;
		println("Read failed. Failures: $failures");
		if ($failures >= 10) {
			$process = false;
		}
	} else {
		println("Response from server is: $input");
		$failures = 0;
		
		// Reply to server
		$mysock->write("Yo dawg");
	}
}
*/
?>
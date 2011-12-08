<?php
require 'functions.php';
require 'message_functions.php';

$db = new dbConnection;

if (isset($_GET['heartbeat_broadcast']))
	{
	message_broadcast_heartbeat();
	exit();
	}


if (isset($_GET['msg']))
	$msg = $_GET['msg'];
else
	$msg = "hello";

$reply = message_send($msg, "http://213.104.248.250:48881/b359/request_handler.php");
//$reply = message_send($msg, "http://rollaboutgame.com/pi/request_handler.php");

$reply_obj = get_object_from_response($reply);

echo("Success: ".$reply_obj->success."<br />");
if ($reply_obj->success != "true")
	echo("Info: ".$reply_obj->info);
?>
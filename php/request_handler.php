<?php
include("functions.php");

$hero = false;
if (!check_database_exists())
	$hero = true;
else if (!get_server_running())
	$hero = true;
	
if ($hero)
	die();

$hash = $_POST['hash'];
$json = $_POST['json'];

if ($hash != make_hash($json)) {
	echo $hash . "<br>" . make_hash($json) . "<br>" . $json . "<br>";
	echo "GRRRR don't try to be l33t.";
	exit();
}

$obj = json_decode($json);

if ((!$obj->type && $obj->type !== 0 && $obj->type !== "0") || !$obj->cmd) {
	echo "JSON not formed properly.";
	exit();
}

$folder_name = "RequestHandler";

if (!is_dir($folder_name)) { # Possibly obsolete, needs changing
	echo "Type \"{$obj->type}\" does not exist.";
	exit();
}

$full_path = "$folder_name/RH_{$obj->cmd}.php";

if (!file_exists($full_path)) {
	echo "Command \"{$obj->cmd}\" does not exist in $folder_name.";
	exit();
}
add_autoloader_dir("RequestHandler");
$boring = "RH_".$obj->cmd;
$cmd_obj = new $boring($obj);
$response = json_encode($cmd_obj->process());
echo "0:" . make_hash($response) . ":" . $response;
?>
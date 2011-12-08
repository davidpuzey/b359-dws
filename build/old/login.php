<?php
require('general_functions.php');
require('user_functions.php');

if (isset($_POST['username']) && isset($_POST['password']))
	{
	$username = $_POST['username'];
	$password = $_POST['password'];
	
	$login_state = user_login($username,$password);
	echo("Login: ".$login_state);

	}
else
	{
	echo("No post data");
	}
?>
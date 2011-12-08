<?php
require('general_functions.php');
require('user_functions.php');

if (!isset($_POST['register']))
	{
	echo("<h2>Register</h2>");
	echo("<form method='post' action='{$_SERVER['REQUEST_URI']}'>");
	echo("<p>Name: <input type='text' name='username'></p>");
	echo("<p>Email: <input type='text' name='email'></p>");
	echo("<p>Password: <input type='password' name='password'></p>");
	echo("<input type='submit' value='Register' name = 'register'></form>");
	}
else
	{
	$username = mysql_real_escape_string($_POST['username']);
	$email = mysql_real_escape_string($_POST['email']);
	$password = mysql_real_escape_string($_POST['password']);
	
	$error_code = user_register($username, $email, $password, time());
	
	if ($error_code > 0)
		{
		echo("Success");
		}
	else
		{
		echo("Failure: ".$error_code);
		}
	}
?>
<?php

function password_hash($p)
	{
	return mysql_real_escape_string(md5($p."mq380f57893f2m78wg6"));
	}

function key_hash($u, $p)
	{
	return mysql_real_escape_string(md5($u.$p."nt7vv9573298ff2tu"));
	}

function user_register($u_name, $u_email, $u_password, $u_timestamp)
	{
	$u_hash = password_hash($u_password);
	
	echo($u_name);
	
	$u_name = mysql_real_escape_string($u_name);
	$u_email = mysql_real_escape_string($u_email);
	$u_timestamp = mysql_real_escape_string($u_timestamp);
	
	if (strlen($u_name) < 5)
		{
		// Name is too short
		echo("Name: ".strlen($u_name));
		return -2;
		}
	else
		{
		$query = "SELECT * FROM dws_users WHERE name = '$u_name'";
		$result = mysql_query($query) or die("Error: ".mysql_error());
		
		/*
		$row = mysql_fetch_array($result);
		if (count($row) != 0)
			{
			// Error, name is already used
			return -1;
			}
		else
		*/
		
		$num_rows = 0;
		while ($row = mysql_fetch_array($result))
			{
			$num_rows++;
			}
		if ($num_rows > 0)
			{
			// Name is already used
			return -1;
			}
		else
			{
			// Register the user
			$query = "INSERT INTO dws_users VALUES (NULL, '$u_name', '$u_hash', '$u_email', '$u_timestamp')";
			$result = mysql_query($query) or die("Error: ".mysql_error());
			
			return 1;
			}
		}
	}

function user_login($u_name, $u_password)
	{
	$u_name = mysql_real_escape_string($u_name);
	$u_hash = password_hash($u_password);
	
	$query = "SELECT password FROM dws_users WHERE name = '$u_name'";
	$result = mysql_query($query) or die("Error: ".mysql_error());
	$row = mysql_fetch_array($result);
	if ($row['password'] == $u_hash)
		{
		// Hash matches, log in
		$_SESSION['loggedin'] = 1;
		$_SESSION['username'] = $u_name;
		$_SESSION['password'] = $u_hash;
		$_SESSION['key'] = key_hash($u_name,$u_password);
		
		return 1;
		}
	else
		{
		// Invalid password
		echo("Username: ".$u_name." supplied password: ".$u_hash." database hash: ".$row['password']);
		return -1;
		}
	}
?>
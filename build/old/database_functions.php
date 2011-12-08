<?php

// -- Product functions -- \\
function product_add($p_name, $p_description, $p_image_url)
	{
	/*
	Adds new product to the table
	*/
	$p_name = mysql_real_escape_string($p_name);
	$p_description = mysql_real_escape_string($p_description);
	$p_image_url = mysql_real_escape_string($p_image_url);
	
	echo("Inserting product ".$p_name."...");
	$query = "INSERT INTO dws_products VALUES (NULL, '$p_name', '$p_description', '$p_image_url')";
	echo("Query designed<br />");
	$result = mysql_query($query) or die("Error: ".mysql_error());
	echo("Query executed<br />");
	}

function product_list($l_from, $l_num)
	{
	/*
	List items from $l_from to $l_from+$l_num
	*/
	$l_from = mysql_real_escape_string($l_from);
	$l_num = mysql_real_escape_string($l_num);
	$query = "SELECT id, name, image_url FROM dws_products LIMIT $l_from,$l_num";
	$result = mysql_query($query) or die("Error: ".mysql_error());
	while ($row = mysql_fetch_array($result))
		{
		$p_name = $row['name'];
		$p_image_url = $row['image_url'];
		
		echo("<p><img src='$p_image_url'>$p_name <a href='products.php?pid=".$row['id']."'>reviews</a></p>");
		}
	}

function product_view($p_id)
	{
	/*
	Display information on a product
	*/
	$p_id = mysql_real_escape_string($p_id);
	$query = "SELECT name, image_url, description FROM dws_products WHERE id = '$p_id'";
	$result = mysql_query($query) or die("Error: ".mysql_error());
	$row = mysql_fetch_array($result);
	
	echo("<h2>".$row['name']."</h2> <br />");			// Product name
	echo("<img src='".$row['image_url']."'> <br />");	// Image
	echo($row['description']);							// Description
	}

// -- Review functions -- \\
function review_list($p_id)
	{
	/*
	List all reviews for the supplied product ID
	*/
	$p_id = mysql_real_escape_string($p_id);
	$query = "SELECT id, review, rating, timestamp FROM dws_reviews WHERE product_id = '$p_id'";
	$result = mysql_query($query) or die("Error: ".mysql_error());
	while ($row = mysql_fetch_array($result))
		{
		$username = uid_to_username($row['id']);
		
		$date = date("l jS \of F Y h:i:s A", $row['timestamp']);
		
		echo("<p>Review: ".$row['review']."</p>");	// Review
		echo("<p>Posted by ".$username."</p>");		// Who posted it
		}
	}

// -- User functions -- \\
function uid_to_username($uid)
	{
	$uid = mysql_real_escape_string($uid);
	$query = "SELECT name FROM dws_users WHERE id = '$uid'";
	$result = mysql_query($query) or die("Error: ".mysql_error());
	$row = mysql_fetch_array($result);
	return $row['name'];
	}
?>
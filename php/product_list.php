<?php
require('functions.php');
$db = new dbConnection;
$result = $db->query("SELECT * FROM dws_products");

echo("<table border='1'>");
foreach ($result as $row) {
	echo("<tr>");
	echo("<td>".$row['id']."</td>");
	echo("<td>".$row['name']."</td>");
	echo("</tr>");
	}
echo("</table>");
?>
<?php
require('functions.php');

$table = get_get_data('table', '');

if ($table == '')
	die("Please enter a table");

$db = new dbConnection;
$result = $db->query("SELECT * FROM $table");

if ($result === false)
	die("Table $table doesn't exist or something else went wrong.");

echo("<table border='1'>");
foreach ($result as $row) {
	echo("<tr>");
	foreach ($row as $key => $column) {
		if (!is_int($key))
			continue;
		echo("<td>".$column."</td>");
	}
	echo("</tr>");
	}
echo("</table>");
?>
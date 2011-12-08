<?php
// Connect
$db = mysql_connect('localhost', 'dws', 'XvGqhbtReYn6VCWH') or die('Could not connect: ' . mysql_error()); 
//$db = mysql_connect('localhost', 'root', 'space101') or die('Could not connect: ' . mysql_error()); 
mysql_select_db('dws',$db) or die('Could not select database');
?>
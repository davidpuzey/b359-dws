<?php
require('general_functions.php');
require('database_functions.php');

if (isset($_GET['pid']))
	{
	product_view($_GET['pid']);
	review_list($_GET['pid']);
	}
else
	{
	product_list(0,8);
	}
?>
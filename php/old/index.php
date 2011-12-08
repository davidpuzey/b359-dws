<?php
#require_once("product_functions.php");
?>

<!doctype html>
<html>
<head>
<title>Awesome Review Servers R Us</title>
<link rel="stylesheet" href="style/main.css" type="text/css">
</head>
<body>
<?require_once("header.php")?>
<div class="body">
	<ol class="products">
	<? for ($i = 0; $i < 4; $i++) {?>
		<li class="product">
			<a href="?pid=1">
				<img src="http://cache.ohinternet.com/images/1/13/Awesome.png">
				<div class="container">
					<div class="name">Awesome Product</div>
					<div class="category">Category A</div>
					<div>
						<div class="rating-background"><div class="rating" style="width: 50%"></div>5.3</div>
						<div class="reviews">(36 Reviews)</div>
					</div>
				</div>
			</a>
		</li>
	<?}?>
	</ol>
</div>
</body>
</html>

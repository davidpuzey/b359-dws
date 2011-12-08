<?php
	include("functions.php");
	requireDbSetup();
	$products = array();
	$products = get_products();
	#var_dump($products);
?>

<!doctype html>
<html>
<head>
<title>Awesome Review Servers R Us</title>
<link rel="stylesheet" href="style/main.css" type="text/css">
</head>
<body>
<?include("header.php")?>
<div class="body">
	<ol class="products">
	<? foreach ($products as $product) {
		$product = (array) $product;
	?>
		<li class="product">
			<a href="product.php?id=<?echo $product['id'] . "&node_id=" . $product['node_id']?>">
				<img src="<?echo $product['image_url']?>">
				<div class="container">
					<div class="name"><?echo $product['name']?></div>
					<div class="category"><?echo $product['category']?></div>
					<div>
						<div class="rating-background"><div class="rating" style="width: <?echo ($product['rating']*20)?>%"></div><?echo $product['rating']?></div>
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


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
<?php include("header.php")?>
<div class="body">
	<ol class="products">
	<?php foreach ($products as $product) {
		$product = (array) $product;
	?>
		<li class="product">
			<a href="product.php?id=<?php echo $product['id'] . "&node_id=" . $product['node_id']?>">
				<img src="<?php echo $product['image_url']?>">
				<div class="container">
					<div class="name"><?php echo $product['name']?></div>
					<div class="category"><?php echo $product['category']?></div>
					<div>
						<div class="rating-background"><div class="rating" style="width: <?php echo ($product['rating']*20)?>%"></div><?php echo $product['rating']?></div>
						<div class="reviews">(36 Reviews)</div>
					</div>
				</div>
			</a>
		</li>
	<?php }?>
	</ol>
</div>
</body>
</html>

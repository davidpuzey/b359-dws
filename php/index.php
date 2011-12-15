
<?php
	include("functions.php");
	requireDbSetup();
	
	$include = get_get_data('search', null);
	$products = array();
	// If there is anything other than a searchable value (ie a string or an integer) then just get all the products.
	if (!is_string($search))
		$search = null;
	$products = search_products($search);
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
						<div class="rating-background"><div class="rating" style="width: <?php echo ($product['avg_rating']*20)?>%"></div><?php echo $product['avg_rating']?></div>
						<div class="reviews"><?php echo $product['num_reviews']?></div>
					</div>
				</div>
			</a>
		</li>
	<?php }?>
	</ol>
</div>
</body>
</html>

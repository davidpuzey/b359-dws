<?
	include("functions.php");
	requireDbSetup();
	$id = get_get_data('id', -1);
	$node_id = get_get_data('node_id', -1);
	
	$product = get_product($id, $node_id);
	
	
	$error = "";
	if (!$product)
		$error = "Selected product doesn't exist.";
	else {
		$product = (array) $product[0];
		$reviews = get_reviews_by_product("$id:$node_id");
	}
?>


<!doctype html>
<html>
<head>
	<title><?echo $product['name'];?> - Awesome Review Servers R Us</title>
	<link rel="stylesheet" href="style/main.css" type="text/css">
	<script>
		function showAddReview() {
			var link = document.getElementById("make_new_review");
			var form = document.getElementById("new_review");
			link.style.display = "none";
			form.style.display = "block";
		}
		
		function hideAddReview() {
			var link = document.getElementById("make_new_review");
			var form = document.getElementById("new_review");
			link.style.display = "block";
			form.style.display = "none";
			
		}
	</script>
</head>
<body>
	<?include("header.php")?>
	<div class="body">
	<?if ($error) {?>
	<div class="error"><?echo $error?></div>
	<?die();
	}?>
	<h1><?echo $product['name'];?></h1>
	<img width="200px" src="<?echo $product['image_url'];?>">
	<div>Category: <?echo $product['category'];?></div>
	<div>Rating: <?echo $product['rating'];?></div>
	<img href="<?echo $product['image_url'];?>">
	<div><?echo $product['description'];?></div>
	<p />
	<h2>Reviews:</h2>
	<div id="make_new_review"><a href="add_review.php?product_id=<?echo $id;?>&node_id=<?echo $node_id;?>" onClick="showAddReview(); return false;">Add Review</a></div>
	<div id="new_review" style="display: none;">
		<form action="add_review.php?product_id=<?echo $id;?>&node_id=<?echo $node_id;?>" method="post">
			<input type="hidden" name="add" value="true">
			<label>Rating: <select name="rating">
				<option value="0">0
				<option value="1">1
				<option value="2">2
				<option value="3">3
				<option value="4">4
				<option value="5">5
			</select></label><br>
			<label>Review: <textarea name="review"></textarea></label>
			<br>
			<input type="submit" value="Add Review"> <input type="button" value="Cancel" onClick="hideAddReview()">
		</form>
	</div>
	<ol class="reviews">
	<?for ($i = count($reviews)-1; $i >= 0; $i--) {
		$review = (array) $reviews[$i];
	?>
		<li class="review">
			<div class="description"><?echo $review['review'];?></div>
			<div class="rating-background"><div class="rating" style="width: <?echo ($review['rating']*20);?>%"></div><?echo $review['rating'];?></div>
			<div class="user">Posted by <?echo $review['username'];?></div>
		</li>
	<?}?>
	</ol>
	</div>
</body>
</html>

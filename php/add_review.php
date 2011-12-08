<?php
	include("functions.php");
	requireDbSetup();

	$id = get_get_data('product_id', -1);
	$node_id = get_get_data('node_id', -1);
	
	$rating = get_post_data('rating', 0);
	$review = get_post_data('review', "");
	
	$review = htmlspecialchars($review,  ENT_QUOTES);
	
	if ($review != "") {
		$result = (array) add_review($id.":".$node_id, $rating, $review);
		var_dump($result);
		if ($result['success'] == "false")
			$error = $result['info'];
		else
			header("Location: product.php?id=".$id."&node_id=".$node_id) ;
	}
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
	<?
		if (isset($error)) {
			echo "<div class='error'>Error: $error</div>";
		}
	?>
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
		<input type="submit" value="Add Review"> <input type="button" value="Cancel">
	</form>
</div>
</body>
</html>

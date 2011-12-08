<?
	include("functions.php");
	requireDbSetup();
	
	$add =  get_post_data('add', "");
	$name = get_post_data('name', "");
	$description = get_post_data('description', "");
	$image_url = get_post_data('image_url', "");
	$category = get_post_data('category', "");
	
	$description = htmlspecialchars($description,  ENT_QUOTES);
	
	if ($add == "true" && $name != "" && $description != "") {
		$result = (array) add_product($name, $description, $image_url, $category);
		if ($result['success'] == "false")
			$error = $result['info'];
		else
			header("Location: index.php");
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
	<form action="add_product.php" method="POST">
		<input type="hidden" name="add" value="true">
		<label>Name: <input type="text" name="name"></label><br>
		<label>Description: <textarea name="description"></textarea></label><br>
		<label>Image URL: <input type="text" name="image_url"></label><br>
		<label>Category: <input type="text" name="category"></label><br>
		<input type="submit" value="Add product">
	</form>
</div>
</body>
</html>

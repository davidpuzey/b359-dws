<?php
	header("Content-type: image/jpg");
	$default = "http://s3.amazonaws.com/kym-assets/photos/images/original/000/092/705/tumblr_le9z72cFz71qdtnwjo1_400.jpg?1294689931";
	$pic = (isset($_GET['pic'])) ? $_GET['pic'] : $default;
	$result = @file_get_contents("http://img.pokemondb.net/artwork/$pic.jpg");
	if ($result === false)
		$result = @file_get_contents($default);
	echo $result;
?>
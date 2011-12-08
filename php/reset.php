<?php
require 'functions.php';
?>
<html>
	<head>
		<style type="text/css">
		.error {
			color:red;
		}
		.warning {
			color:orange;
		}
		</style>
	</head>
	<body>
		<h2>Reset</h2>
		<?php
		if (isset($_GET['reset'])) {
			if ($_GET['reset'] == "I_am_totally_sure") {
				drop_database();
				?>
				<p class='warning'>Configuration reset.</p>
				<?php
			}
		}
		?>
		<p><a href='admin.php'>Admin Control Panel</a></p>
	</body>
</html>
<?php
require 'functions.php';
?>
<html>
	<head>
		<link type="text/css" rel="stylesheet" href="style/generic.css">
	</head>
	<body>
		<h2>Admin Control Panel</h2>
		<p><a href='setup.php'>Setup</a></p>
		<p><a href='nodes.php'>Nodes</a></p>
		<p><a href='reset.php?reset=I_am_totally_sure'>Reset</a></p>
		
		<h2>Server status</h2>
		<?php
		if (isset($_GET['action'])) {
			if ($_GET['action'] == "stop")
				set_server_running(false);
			if ($_GET['action'] == "start")
				set_server_running(true);
			}
		if (check_database_exists()) {
			if (get_server_running()) {
				?>
				<p><span class='success'>Running</span> <a href='admin.php?action=stop'>Stop</a></p>
				<?php
			} else {
				?>
				<p><span class='warning'>Stopped</span> <a href='admin.php?action=start'>Start</a></p>
				<?php
				}
				?>
			<p>Name: <i><?php echo SERVER_NAME ?></i><br />
			UUID: <i><?php echo UUID ?></i><br />
			Type: <i><?php echo type_decode(SERVER_TYPE); ?></i></p>
			<img src="get_pic.php?pic=<?php echo strtolower(SERVER_NAME); ?>">
			<?php
		} else {
			?>
			<p class='red'>Not configured.</p>
			<?php
		}
		?>
	</body>
</html>
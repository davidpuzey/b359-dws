
<?php
require('functions.php');
if (isset($_POST['update_primary_node']) && SERVER_TYPE == SERVER_CLIENT) {
	echo("<h2>Updating Primary Node</h2>");
	$obj = node_matrix_change_primary($_POST['primary']);
	if ($obj->success == "true") {
		echo("<p>Updated successfully</p>");
	} else {
		echo("<p>Update failed</p>");
	}
	echo("<p><a href='nodes.php'>nodes</a></p>");
} else {
	echo("<h2>Network Nodes</h2>");
	if (check_database_exists()) {
		$db = new dbConnection;
		
		if (SERVER_TYPE == SERVER_CLIENT) {
			// Work out which one is our primary
			$result = $db->query("SELECT review_uuid FROM dws_node_matrix WHERE client_uuid = ".UUID);
			if (count($result) > 0) {
				$primary_uuid = $result[0]['review_uuid'];
			} else {
				$primary_uuid = -1;
			}
		}
		
		$result = $db->query("SELECT * FROM dws_nodes");
		echo("<form action='nodes.php' method='post'>");
		echo("<table border='1'>");
		echo("<tr><th>uuid</th><th>server_type</th><th>server_name</th><th>host_name</th><th>port</th><th>uri</th><th>last_response</th><th>num_failures</th><th>is_up</th><th>primary</th></tr>");
		foreach ($result as $row) {
			echo("<tr>");
			echo("<td>".$row['uuid']."</td>");
			echo("<td>".type_decode($row['server_type'])."</td>");
			echo("<td>".$row['server_name']."</td>");
			echo("<td>".$row['host_name']."</td>");
			echo("<td>".$row['port']."</td>");
			echo("<td>".$row['uri']."</td>");
			echo("<td>".$row['last_response']."</td>");
			echo("<td>".$row['num_failures']."</td>");
			echo("<td>".$row['is_up']."</td>");
			if (SERVER_TYPE == SERVER_CLIENT && $row['server_type'] == SERVER_REVIEW) {
				if ($primary_uuid == $row['uuid']) {
					$c = "checked";
				} else {
					$c = "";
				}
				echo('<td><input type="radio" name="primary" value="'.$row['uuid'].'" '.$c.'/></td>');
			} else {
				echo('<td>N/A</td>');
			}
			echo("</tr>");
			}
		echo("</table>");
		if (SERVER_TYPE == SERVER_CLIENT) {
			echo('<input type="submit" name="update_primary_node" value="Update primary node" />');
		}
		echo("</form>");
		?>
	<h2>Node Matrix</h2>
		<?php
		
		//$query = "INSERT INTO dws_node_matrix VALUES (".rand(0,1000000).", 456)";
		//$db->query($query);
		
		$result = $db->query("SELECT * FROM dws_node_matrix");
		echo("<table border='1'>");
		
		//uuid, server_type, server_name, host_name, port, uri, last_response, num_failures, is_up
		echo("<tr><th>client_uuid</th><th>review_uuid</th></tr>");
		foreach ($result as $row) {
			echo("<tr>");
			echo("<td>".$row['client_uuid']."</td>");
			echo("<td>".$row['review_uuid']."</td>");
			echo("</tr>");
			}
		echo("</table>");
	
		echo("<p><a href='admin.php'>Admin Control Panel</a></p>");
	} else {
		echo("<p>Database is not yet configured</p>");
		echo("<p><a href='admin.php'>Admin Control Panel</a></p>");
	}
}
?>
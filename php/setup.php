<html>
<head>
<style type="text/css">
.error
{
color:red;
}
.warning
{
color:orange;
}
</style>
</head>
<body>
<h2>Setup</h2>
<?php

require('functions.php');



function create_database() {
	if (!is_dir("db"))
		mkdir("db");
	else
		drop_database();
	
	$db = new dbConnection;
	//$dbhandle = sqlite_open('db/dws.db', 0777, $error);
	//if (!$dbhandle) die ($error);
	

	if ($_POST['server_type'] == "Node") {
		$sql[] = "CREATE TABLE dws_products(id integer, node_id integer, user_id text, name text, description text, image_url text, category text, rating real, timestamp integer, PRIMARY KEY (id, node_id))";
		$sql[] = "CREATE TABLE dws_reviews(id integer, node_id integer, product_id text, user_id text, review text, rating integer, timestamp integer, PRIMARY KEY (id, node_id))";
		$sql[] = "CREATE TABLE dws_users(id integer, node_id integer, name text, password text, email text, time_joined integer, PRIMARY KEY (id, node_id))";
		
		/* Insert some default data */
		$sql[] = "INSERT INTO dws_users (id, node_id, name, password, email, time_joined) VALUES ('0', '0', 'Anonymous', '', '', '0')"; // Anonymous user for testing purposes
	}
	
	$sql[] = "CREATE TABLE dws_message_queue(id integer PRIMARY KEY, message text, num_failures integer, timestamp integer);";
	
	// Stores review servers and clients
	//uuid server_type server_name host_name port_tcp port_udp port_http uri last_response num_failures is_up
	$sql[] = "CREATE TABLE dws_nodes(uuid integer, server_type text, server_name text, host_name text, port_tcp integer, port_udp integer, port_http integer, uri text, last_response integer, num_failures integer, is_up integer, PRIMARY KEY (uuid))";

	// Which clients talk to which review servers
	$sql[] = "CREATE TABLE dws_node_matrix(client_uuid integer PRIMARY KEY, review_uuid integer)";
	
	foreach ($sql as $tbl) {
		/*
		$ok = sqlite_exec($dbhandle, $tbl, $error);
		if (!$ok)
			die("Cannot execute query. $error");
		*/
		if ($db->query($tbl) === false) {
			die("Cannot execute query. $error");
		}
	}
	
	//echo "Database created.\n<br>\n";
	
	//sqlite_close($dbhandle);
}

function store_metadata($server_name, $uuid, $port, $uri) {
	
	file_put_contents("meta/uuid.meta",$uuid);
	
	$db = new dbConnection;
	//$dbhandle = sqlite_open('db/dws.db', 0777, $error);
	
	$server_type_string = $_POST['server_type'];
	
	if ($server_type_string == "Client")
		$server_type = 0;
	else
		$server_type = 1;

	$host_name = $_SERVER['SERVER_NAME'];
	$last_response = time();
	$num_failures = 0;
	$is_up = 1;
	$query = "INSERT INTO dws_nodes (uuid, server_type, server_name, host_name, port_tcp, port_udp, port_http, uri, last_response, num_failures, is_up) VALUES ('$uuid', '$server_type', '$server_name', '$host_name', '0', '0', '$port', '$uri', '$last_response', '$num_failures', '$is_up')";
	//$result = sqlite_query($dbhandle,$query);
	$result = $db->query($query);
	
	//sqlite_close($dbhandle);
}

function setup_form() {
	$pokemon = array("bulbasaur","ivysaur","venusaur","charmander","charmeleon","charizard","squirtle","wartortle","blastoise","caterpie","metapod","butterfree","weedle","kakuna","beedrill","pidgey","pidgeotto","pidgeot","rattata","raticate","spearow","fearow","ekans","arbok","pikachu","raichu","sandshrew","sandslash","nidoran-f","nidorina","nidoqueen","nidoran-m","nidorino","nidoking","clefairy","clefable","vulpix","ninetales","jigglypuff","wigglytuff","zubat","golbat","oddish","gloom","vileplume","paras","parasect","venonat","venomoth","diglett","dugtrio","meowth","persian","psyduck","golduck","mankey","primeape","growlithe","arcanine","poliwag","poliwhirl","poliwrath","abra","kadabra","alakazam","machop","machoke","machamp","bellsprout","weepinbell","victreebel","tentacool","tentacruel","geodude","graveler","golem","ponyta","rapidash","slowpoke","slowbro","magnemite","magneton","farfetchd","doduo","dodrio","seel","dewgong","grimer","muk","shellder","cloyster","gastly","haunter","gengar","onix","drowzee","hypno","krabby","kingler","voltorb","electrode","exeggcute","exeggutor","cubone","marowak","hitmonlee","hitmonchan","lickitung","koffing","weezing","rhyhorn","rhydon","chansey","tangela","kangaskhan","horsea","seadra","goldeen","seaking","staryu","starmie","mr-mime","scyther","jynx","electabuzz","magmar","pinsir","tauros","magikarp","gyarados","lapras","ditto","eevee","vaporeon","jolteon","flareon","porygon","omanyte","omastar","kabuto","kabutops","aerodactyl","snorlax","articuno","zapdos","moltres","dratini","dragonair","dragonite","mewtwo","mew");
	
	$default_name = $pokemon[rand(0,count($pokemon)-1)];
	$default_name_ucfirst = ucfirst($default_name);
	?>
	<form action="<?php echo "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>" method="post">
	
		<p>Server name: <input type="text" name="server_name" value="<?php echo $default_name_ucfirst; ?>" onChange="document.getElementById('serv_img').src='http://img.pokemondb.net/artwork/'+this.value.toLowerCase()+'.jpg';"/><br />
		<img id="serv_img" src="http://img.pokemondb.net/artwork/<?php echo $default_name; ?>.jpg">
		<p>Server UUID: <input type="text" name="uuid" value="<?php echo rand(0,2147483647); ?>"/> (0 to 2147483647, inclusive)<br />
		<p>Server port: <input type="text" name="port" value="<?php echo $_SERVER['SERVER_PORT']; ?>"/><br />
		<p>Server root: <input type="text" name="root" value="<?php echo dirname($_SERVER['REQUEST_URI']);?>/"/><br />
		<p>Server type:<br />
		<input type="radio" name="server_type" value="Client" checked="checked" /> Client<br />
		<input type="radio" name="server_type" value="Node" /> Node</p>
		
		<p>Network node:<br />
		<input type="radio" name="first_in_network" value="Yes" />This is the first node of the network.<br />
		<input type="radio" name="first_in_network" value="No" checked="checked" /> Connect to this network node <br /> IP: <input type="text" name="network_node_ip" size="100" value="127.0.0.1"/><br /> TCP port: <input type="text" name="network_node_port" size="100" value="23118"/></p>
		
		<p><input name="submit" type="submit" value="Initiate setup routine"></p>
		
	</form>
	<?php
}

function connect_to_network($network_node_ip,$network_node_port,$my_uuid) {
	
	// Create a temporary node with our IP and port, then use it to send the messages
	$temp_uuid = 0;
	nodeData::getInstance()->add_node($temp_uuid,"Temp","Temp",$network_node_ip,"$network_node_port",0,0,"/",time(),0,0);
	
	
	// second argument is true, meaning it asks for a list of all nodes to be returned
	$reply = message_send_hello($temp_uuid,true,$my_uuid);
	
	if ($reply->success == "true") {
		// If client, add the review server's uuid it just contacted to the node matrix
		if ($_POST['server_type'] == "Client") {
			echo("<p>Updating client node matrix...</p>");
			$node_uuid = $reply->uuid;
			node_matrix_set($my_uuid,$node_uuid);
			
			echo("<p>Sharing node matrix...</p>");
			message_send_matrix($temp_uuid, true, $my_uuid);
		}
		
		// If server, request a copy of the node matrix
		// This works by sending our empty matrix and requesting theirs in return!
		if ($_POST['server_type'] == "Node") {
			message_send_matrix($temp_uuid, true, $my_uuid);
		}
		
		// Add all the nodes
		echo("<p>Updating nodes table...</p>");
		nodes_add($reply->nodes,false);
		
		echo("<p>Broadcasting existence to network...</p>");
		broadcast_send_hello(false,$my_uuid);
		
		// Remove the temporary 
		nodeData::getInstance()->remove_node($temp_uuid);
		
		return 1;
	} else {
		echo("<p class='warning'>Failed to connect to network.</p>");
		echo("<p>Reason: ".$reply->info."</p>");
		return 0;
	}
	
}


if (check_database_exists()) {
	echo("<p>Server is already configured.</p>");
	echo("<p><a href='admin.php'>Admin Control Panel</a></p>");
	echo("<p><a href='reset.php?reset=I_am_totally_sure'>Reset</a></p>");
} else {
	if (isset($_POST['submit'])) {
		
		// Validate post data
		$retry = 0;
		
		if (empty($_POST['first_in_network'])) {
			echo("<p class='error'>Specifiy whether it is the first node or not.</p>");
			$retry = 1;
		} else {
			if ($_POST['first_in_network'] != "Yes" && $_POST['first_in_network'] != "No") {
				echo("<p class='error'>Choose a valid option for whether it is first in network or not.</p>");
			}
		}
		
		if (empty($_POST['server_name'])) {
			echo("<p class='error'>Enter a server name.</p>");
			$retry = 1;
		}
		
		if (empty($_POST['network_node_ip'])) {
			echo("<p class='error'>Enter a network node IP.</p>");
			$retry = 1;
		}
		
		if (empty($_POST['network_node_port'])) {
			echo("<p class='error'>Enter a network node TCP port.</p>");
			$retry = 1;
		}
		
		if (empty($_POST['root'])) {
			echo("<p class='error'>Enter a root.</p>");
			$retry = 1;
		}
		
		if (empty($_POST['port'])) {
			echo("<p class='error'>Enter a port.</p>");
			$retry = 1;
		} else if (!(is_numeric($_POST['port']))) {
			echo("<p class='error'>Port must be a number.<p>");
			$retry = 1;
		} else {
			$temp_port = intval($_POST['port']);
			if ($temp_port < 0) {
				echo("<p class='error'>Enter a valid port (non-negative).</p>");
				$retry = 1;
			}
		}
		
		if (empty($_POST['uuid'])) {
			echo("<p class='error'>Enter a UUID.</p>");
			$retry = 1;
		} else if (!(is_numeric($_POST['uuid']))) {
			echo("<p class='error'>UUID must be a number.<p>");
			$retry = 1;
		} else {
			$temp_port = intval($_POST['uuid']);
			if ($temp_port < 0) {
				echo("<p class='error'>Enter a valid UUID (non-negative).</p>");
				$retry = 1;
			}
		}
	
		if (empty($_POST['server_type'])) {
			echo("<p class='error'>Enter a server type.</p>");
			$retry = 1;
		} else {
			if ($_POST['server_type'] != "Client" && $_POST['server_type'] != "Node") {
				echo("<p class='error'>Invalid server type.</p>");
				$retry = 1;
			}
		}
		
		if ($retry) {
			// Data failed validation, show form again
			setup_form();
		} else {
			// Data validated, setup server
			echo("<p>Creating database...</p>");
			create_database();
			echo("<p>Storing metadata...</p>");
			store_metadata($_POST['server_name'],$_POST['uuid'],$_POST['port'],$_POST['root']);
			$connected = 0;
			if ($_POST['first_in_network'] == "No") {
				echo("<p>Connecting to network...</p>");
				if (connect_to_network($_POST['network_node_ip'],$_POST['network_node_port'],$_POST['uuid'])) {
					$connected = 1;
				}
			} else {
				echo("<p>Initializing network...</p>");
				$connected = 1;
			}
			if ($connected == 1) {
				echo("<p>Connected successfully!</p>");
				echo("<p>Setup is complete.</p>");
			} else {
				echo("<p>Setup finished with errors.</p>");
				echo("<p><a href='{$_SERVER['PATH_INFO']}'>Run setup again</a></p>");
			}
			echo("<p><a href='admin.php'>Admin Control Panel</a></p>");
		}
		
	} else {
		setup_form();
	}
}
?>
</body>
</html>
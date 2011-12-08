<?php
	function get_primary_review_server_id() {
		$db = new dbConnection;
		$result = $db->query("SELECT review_uuid FROM dws_node_matrix WHERE client_uuid = '". UUID ."'");
		return $result[0]['review_uuid'];
	}
	if (check_database_exists())
		$PRIMARY_REVIEW_SERVER_UUID = get_primary_review_server_id();
	else
		$PRIMARY_REVIEW_SERVER_UUID = -1;
	
	$USER_ID = "0:0";
	
	function change_primary_server($is_dead = false) {
		global $PRIMARY_REVIEW_SERVER_UUID;
		$db = new dbConnection;
		if ($is_dead === true)
			$db->query("UPDATE dws_nodes SET num_failures = num_failures + 1, is_up = '0' WHERE uuid = '$PRIMARY_REVIEW_SERVER_UUID'");
		$arr = array($PRIMARY_REVIEW_SERVER_UUID);
		$new_uuid = choose_random_unvisited_server($arr);
		if ($new_uuid === null)
			die("There are no review servers left :(.");
		$response = node_matrix_change_primary($new_uuid);
		$PRIMARY_REVIEW_SERVER_UUID = $new_uuid;
	}
	
	function client_message_send($cmd, $options = null, $attempts = 0) {
		global $PRIMARY_REVIEW_SERVER_UUID;
		global $USER_ID;
		$options['user_id'] = $USER_ID;
		$response = message_send_by_id($cmd, $PRIMARY_REVIEW_SERVER_UUID, $options);
		if ($response === "") {
			if ($attempts >= 10)
				die("There do no seem to be any available review servers, stick around for more fun after the break.");
			change_primary_server(true);
			return client_message_send($cmd, $options, $attempts+1);
		}
		return get_object_from_response($response);
	}
	
	function get_products($search = null) {
		$options = null;
		if (is_array($search) || is_object($search))
			$options['search'] = $search;
		$result = (array) client_message_send("getProducts", $options);
		if (isset($result['result']))
			return $result['result'];
		else
			return $result;
	}
	
	function get_product($id, $node_id) {
		return get_products(array('id' => $id, 'node_id' => $node_id));
	}
	
	function get_reviews($search = null) {
		$options = null;
		if (is_array($search) || is_object($search))
			$options['search'] = $search;
		$result = (array) client_message_send("getReviews", $options);
		if (isset($result['result']))
			return $result['result'];
		else
			return $result;
	}
	
	function get_reviews_by_product($id) {
		return get_reviews(array("product_id" => $id));
	}
	
	function add_product($name, $description, $image_url, $category) {
		$options = array();
		$options['name'] = $name;
		$options['description'] = $description;
		$options['image_url'] = $image_url;
		$options['category'] = $category;
		return client_message_send('addProduct', $options);
	}
	
	function add_review($product_id, $rating, $review) {
		$options = array();
		$options['product_id'] = $product_id;
		$options['rating'] = $rating;
		$options['review'] = $review;
		return client_message_send('addReview', $options);
	}
?>

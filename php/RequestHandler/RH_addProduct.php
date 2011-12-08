<?php
	class RH_addProduct extends BroadcastRequestHandler {
		function response() {
			$return = array('success' => 'true');
			
			$id = $this->getParam('id', rand(0, 30000));
			$node_id = $this->getParam('node_id', UUID);
			$user_id = $this->getRequiredParam('user_id');
			$name = $this->getRequiredParam('name');
			$description = $this->getParam('description', '');
			$image_url = $this->getParam('image_url', '');
			$category = $this->getParam('category', '');
			$timestamp = time();
			$rating = 0;
			if ($this->checkErrors())
				return false;
			
			$result = $this->query("INSERT INTO dws_products (id, node_id, user_id, name, description, image_url, category, rating, timestamp) VALUES ('$id', '$node_id', '$user_id', '$name', '$description', '$image_url', '$category', '$rating', '$timestamp')");
			if ($result === false) {
				$this->addError("Product $name could not be added.");
				return false;
			}
			
			$this->updateResponse('result', $result);
			
			$this->setParam('id', $id);
			$this->setParam('node_id', $node_id);
			
			return true;
		}
	}
?>
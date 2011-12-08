<?php
	class RH_addReview extends BroadcastRequestHandler {
		function response() {
			$return = array('success' => 'true');
			
			$id = $this->getParam('id', rand(0, 30000));
			$node_id = $this->getParam('node_id', UUID);
			$product_id = $this->getRequiredParam('product_id');
			$user_id = $this->getRequiredParam('user_id');
			$review = $this->getParam('review', '');
			$rating = $this->getRequiredParam('rating');
			$timestamp = time();
			if ($this->checkErrors())
				return false;
			
			$result = $this->query("INSERT INTO dws_reviews (id, node_id, product_id, user_id, review, rating, timestamp) VALUES ('$id', '$node_id', '$product_id', '$user_id', '$review', '$rating', '$timestamp')");
			if ($result === false) {
				$this->addError("Review could not be added.");
				return false;
			}
			
			$this->updateResponse('result', $result);
			
			$this->setParam('id', $id);
			$this->setParam('node_id', $node_id);
			
			return true;
		}
	}
?>
<?php
	class RH_getProducts extends CreepRequestHandler {
		function response() {
			$search = $this->getParam('search', array());
			if (is_string($search)) {
				$arr = explode(" OR ", $search);
				$str = "(." . implode("','", $arr) . "')";
				$where = " WHERE name IN $str OR description IN $str OR category IN $str";
			} else {
				if (is_object($search))
					$search = (array) $search;
				if (!is_array($search))
					$search = array();
				
				$where = array();
				foreach ($search as $key => $param) {
					$where[] = "$key='$param'";
				}
				$where = implode(" AND ", $where);
				if (!empty($where))
					$where = " WHERE $where";
			}
			$result = $this->query("SELECT * FROM dws_products$where");
			
			foreach ($result as $key => $value) {
				$product_id = "{$value['id']}:{$value['node_id']}";
				$reviews_obj = new RH_getReviews(array('search'=>array('product_id'=>$product_id)));
				$reviews = $reviews_obj->process();
				$reviews = $reviews['result'];
				$num_reviews = count($reviews);
				$avg_rating = 0;
				foreach ($reviews as $rev) {
					$avg_rating += intval($rev['rating']);
				}
				$avg_rating = $avg_rating / $num_reviews;
				$result[$key]['avg_rating'] = $avg_rating;
				$result[$key]['num_reviews'] = $num_reviews;
			}
			
			$this->updateResponse('result', $result);
			
			return true;
		}
	}
?>
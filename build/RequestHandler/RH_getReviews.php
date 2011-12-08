<?php
	class RH_getReviews extends CreepRequestHandler {
		function response() {
			$search = $this->getParam('search', array());
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
			$result = $this->query("SELECT * FROM dws_reviews$where");
			
			foreach ($result as $key => $value) {
				$user_id = explode(":", $value['user_id']);
				$uname = $this->query("SELECT name FROM dws_users WHERE id='{$user_id[0]}' AND node_id='{$user_id[1]}'");
				if ($uname === false)
					$result[$key]['username'] = "User Not Found";
				else
					$result[$key]['username'] = $uname[0]['name'];
			}
			
			$this->updateResponse('result', $result);
			
			return true;
		}
	}
?>
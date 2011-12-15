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
			
			$this->updateResponse('result', $result);
			
			return true;
		}
	}
?>
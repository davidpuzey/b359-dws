<?php
	class RH_getProducts extends CreepRequestHandler {
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
			$result = $this->query("SELECT * FROM dws_products$where");
			
			$this->updateResponse('result', $result);
			
			return true;
		}
	}
?>
<?php
	class CreepRequestHandler extends RequestHandler {
		function creep() {
			if (!$this->getParam('is_creeping', false)) {
				$this->setParam('is_creeping', true);
				$creepstuff = array('ttl' => creeper_calculate_initial_ttl(), 'visited' => array());
			} else
				$creepstuff = $this->getParam('creepstuff');
			$creepstuff['ttl']--;
			array_push($creepstuff['visited'], UUID);
			$this->setParam('creepstuff', $creepstuff);
			if ($creepstuff['ttl'] <= 0)
				return;
			
			$creep_result = creep_next_server($this->obj);
			if ($creep_result['success'] == false)
				return;
			$result = array_merge($result, $creep_result['result']);
		}
		
		function process() {
			parent::process();
			#$this->creep();
			return $this->responseVar;
		}
	}
?>
<?php
class basicTimer {
	/**
	 * Creates a timer and starts it
	 * $frequency: Every how many seconds the timer should expire
	 * $auto_reset: If true, timer will reset when it expires
	 */
	function __construct($frequency, $auto_reset = false) {
		$this->last_time = time();
		$this->frequency = $frequency;
		$this->auto_reset = $auto_reset;
	}
	
	/**
	 * Returns true when the timer has expired, and resets if auto_reset was set to true
	 */
	function expired() {
		if (time() > $this->last_time + $this->frequency) {
			if ($this->auto_reset)
				$this->reset();
			return true;
		} else
			return false;
	}
	
	/**
	 * Resets the timer
	 */
	function reset() {
		$this->last_time = time();
	}
}
?>
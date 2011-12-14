<?php
	/**
	 * ErrorHandler - Basic error handler.
	 * Constructor -	Accepts error string
	 * TODO
	 * Make more comprehensive
	 */
	class ErrorHandler {
		function __construct($error) {
			if (isset($error))
				$this->errors[] = $error;
		}
		
		/**
		 * addError -	Add an error
		 * Parameters:
		 *		$error (string)	The error to add
		 */
		function addError($error) {
			$this->errors[] = $error;
		}
		
		/**
		 * makeRequestHandlerObject -	Makes a request handler
		 *								friendly error object.
		 * Returns a request handler friendly object.
		 */
		function makeRequestHandlerObject() {
			return array("success" => "false", "info" => implode("\n", $this->errors), "errors" => $this->errors);
		}
	}
?>
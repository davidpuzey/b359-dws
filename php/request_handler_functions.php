<?
	/**
	 * response_to_object - Converts a response string to an object
	 *						while checking that the reponse is
	 *						correct.
	 * Parameters:
	 *		$response (string)	The response string to process
	 * Returns the object from the response
	 */
	function response_to_object($response) {
		$response_array = explode(":", $response, 2);
		
		// Check to make sure that there the 2 elements we expect
		// (Really that there aren't less than 2
		if (count($response_array) != 2)
			return new ErrorHandler("Either no hash or no JSON.");
		
		$hash = $response_array[1];
		$json = $response_array[2];
		
		// Check that the given hash matches as it should
		if ($hash != make_hash($json))
			return new ErrorHandler("Hashes do not match.");
		
		// Get us some JSON
		return json_decode($json);
	}
	
	/**
	 * object_to_response -	Return a response string from a given
	 *						object by making the json, hashing it
	 *						and appending them together.
	 * Parameters:
	 *		$obj (object)	The object to make response text out of
	 * Returns the response string.
	 */
	function object_to_response($obj) {
		$json = json_encode($obj);
		return make_hash($json) . ":$json";
	}
	
	
	/**
	 * handle_message -	Processes with a response string and returns
	 *					the result.
	 * Parameters:
	 *		$string (string)	The response string to process.
	 *		$asObj (bool)	Whether the result should be returned
	 *						as an object or as a string. (Optional)
	 *						Default is false.
	 * Returns the result as a response string.
	 */
	function handle_message($string, $asObj = false) {
		$obj = response_to_object($string);
		
		// Check that everything went well, if not return the problem
		if (get_class($obj) == "ErrorHandler")
			return object_to_response($obj->makeRequestHandlerObject());
		
		// Check that the object has the needed structure
		if (!is_string($obj->cmd)) {
			$error_handler_obj = new ErrorHandler("JSON not formed properly.");
			return object_to_response($error_handler_obj->makeRequestHandlerObject());
		}
		
		// Process relevant command
		$class = "RH_".$obj->cmd;
		$cmd_obj = new $class($obj);
		$response = $cmd_obj->process();
		if (!asObj)
			$response = object_to_response($response);
		
		return $response;
	}
?>
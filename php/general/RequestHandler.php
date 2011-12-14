<?php
	/**
	 * RequestHandler -	Base class that gives the structure of the
	 *					request handler classes and gives some
	 *					basic functionality. This is a shell to be
	 *					used with a RequestHandler command class
	 *					which will give the command functionality.
	 */
	class RequestHandler {
		/**
		 * Default constructor, sets up some default generic stuff
		 * Parameters:
		 *		$obj (object)	the object or assosiative array
		 *						containing command data.
		 */
		function __construct($obj)	{
			$this->obj = (array)$obj;
			// New database connection
			$this->db = new dbConnection;
			// Default response
			$this->responseVar = array('success' => 'false', 'info' => '');
			// If we create an error handler class we can use it here
			$this->errors = array();
		}
		
		function __destruct() {
			// Disconnect from the database
			unset($this->db);
		}
		
		/**
		 * query -	Helper function to pass a query onto the database
		 * 			handler.
		 * Parameters:
		 *		$sql (string)	the sql sting to execute in the
		 *						database
		 * Returns the result of the sql string
		 */
		function query($sql) {
			return $this->db->query($sql);
		}
		
		/**
		 * getParam -	Returns a parameter passed in by the object
		 *				or a default value if the parameter doesn't
		 *				exist.
		 * Parameters:
		 *		$var (string)	The name of the parameter to get
		 *		$default (mixed)	The default value to return if
		 *							no parameter exists. (optional)
		 *							null by default.
		 * Returns the value of the parameter or the default value
		 * 				
		 */
		function getParam($var, $default = null) {
			return (isset($this->obj[$var])) ? $this->obj[$var] : $default;
		}
		
		/**
		 * setParam - sets a parameter in the object
		 * Parameter:
		 *		$var (string)	The name of the parameter to set
		 *		$val (mixed)	The value of the parameter
		 */
		function setParam($var, $val) {
			$this->obj[$var] = $val;
		}
		
		/**
		 * getRequriedParam -	Returns a parameter from the inputed
		 *						object. Sets up an error if it is not
		 *						found.
		 * Paramaeters:
		 *		$var (string)	The parameter to return
		 *		$info (string)	The string to return if the parameter
		 *						does not exist.
		 * Returns the value of the parameter or false if it is not
		 * set.
		 */
		function getRequiredParam($var, $info = "") {
			if (isset($this->obj[$var]))
				return $this->obj[$var];
			// Set up the error if the parameter was not set
			$this->addError("The parameter ".$var." was not set. " . $info);
			return false;
		}
		
		/**
		 * addError -	Adds and error to the list and sets the return
		 *				message.
		 * Parameters:
		 *		$error (string)	The error message to return.
		 * TODO
		 *		Make this function use an error handler object
		 */
		function addError($error) {
			$this->errors[] = $error;
			$this->appendInfo($error);
		}
		
		/**
		 * checkErrors -	Returns whether there are any errors.
		 * Returns true if there are errors, false otherwise.
		 */
		function checkErrors() {
			if (!empty($this->errors)) {
				$this->updateResponse('errors', $this->errors);
				return true;
			}
			return false;
		}
		
		/**
		 * response -	Place holder, this should get replaced by
		 *				sub-classes.
		 * Returns false to show that this no a function.
		 */
		function response() {
			$this->setInfo('Function incorrectly structured.');
			return false;
		}
		
		/**
		 * updateResponse - Update the response array with a given
		 *					parameter and value. This is returned
		 *					once the response has been processed.
		 * Parameters:
		 *		$name (string)	The name of the parameter to return
		 *		$value (mixed)	The value the assign to the parameter
		 */
		function updateResponse($name, $value) {
			$this->responseVar[$name] = $value;
		}
		
		/**
		 * setSuccess - Set whether the execution was successful or not
		 * Parameters:
		 *		$value (bool)	true to the value to true, and false otherwise
		 */
		function setSuccess($value) {
			$ret = "true";
			if (!$value || $value === "false") { // If $value is one of the false values (an empty string, 0, false, etc etc) or it has the string "false" (which would normally be true, however we wish it to be false in this case) then return a string false
				$ret = "false";
			}
			$this->updateResponse('success', $ret);
		}
		
		/**
		 * appendInfo - Append text to the returned message
		 * Parameters:
		 *		$info (string)	The text to append to the return message
		 */
		function appendInfo($info) {
			$this->responseVar['info'] .= $info."\n<br>\n";
		}
		
		/**
		 * setInfo - Set the whole of the returned message
		 * Parameters:
		 *		$info (string)	The text to return
		 */
		function setInfo($info) {
			$this->updateResponse('info', $info);
		}
		
		/**
		 * process -	This is the main method that will get called
		 *				it deals will call the response method and
		 *				return the response object.
		 * Returns the object created in the response method.
		 */
		function process() {
			$response = $this->response();
			$this->setSuccess($response);
			return $this->responseVar;
		}
	}
?>
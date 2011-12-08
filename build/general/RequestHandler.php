<?php
	class RequestHandler {
		function __construct($obj)	{
			$this->obj = (array)$obj;
			$this->db = new dbConnection;
			$this->responseVar = array('success' => 'false', 'info' => '');
			$this->errors = array();
		}
		
		function __destruct() {
			unset($this->db);
		}
		
		function query($sql) {
			return $this->db->query($sql);
		}
		
		function getParam($var, $default = null) {
			return (isset($this->obj[$var])) ? $this->obj[$var] : $default;
		}
		
		function setParam($var, $val) {
			$this->obj[$var] = $val;
		}
		
		function getRequiredParam($var, $info = "") {
			if (isset($this->obj[$var]))
				return $this->obj[$var];
			$this->addError("The parameter ".$var." was not set. " . $info);
			return false;
		}
		
		function addError($error) {
			$this->errors[] = $error;
			$this->appendInfo($error);
		}
		
		function checkErrors() {
			if (!empty($this->errors)) {
				$this->updateResponse('errors', $this->errors);
				return true;
			}
			return false;
		}
		
		function response() {
			$this->setInfo('Function incorrectly structured.');
			return false;
		}
		
		function updateResponse($name, $value) {
			$this->responseVar[$name] = $value;
		}
		
		function setSuccess($value) {
			$ret = "true";
			if (!$value || $value === "false") { // If $value is one of the false values (an empty string, 0, false, etc etc) or it has the string "false" (which would normally be true, however we wish it to be false in this case) then return a string false
				$ret = "false";
			}
			$this->updateResponse('success', $ret);
		}
		
		function appendInfo($info) {
			$this->responseVar['info'] .= $info."\n<br>\n";
		}
		
		function setInfo($info) {
			$this->updateResponse('info', $info);
		}
		
		function process() {
			$response = $this->response();
			$this->setSuccess($response);
			return $this->responseVar;
		}
	}
?>
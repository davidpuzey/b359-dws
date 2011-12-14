<?php
	/**
	 * Settings - Get and set the servers settings
	 * Usage: Settings::getInstance();
	 */
	class Settings {
		function __construct() {
			if (!file_exists(CONFIG_INI)) {
				$fileHandle = fopen(CONFIG_INI,'w+') or die("Can't open file " . CONFIG_INI);
				fclose($fileHandle);
			}
			$this->settings = parse_ini_file(CONFIG_INI);
			$this->has_sections = false;
		}
		
		/**
		 * getInstance -	Get an existing instance of the class or
		 *					create a new instance
		 * Returns a class instance.
		 */
		function getInstance() {
			static $instance;
 			if (!is_object($instance) || get_class($instance) !== __CLASS__) {
				$obj = __CLASS__;
				$instance = new $obj;
			}
			return $instance;
		}
		
		/**
		 * getParam -	Gets an existing parameter or returns the
		 * 				default if it doens't exist.
		 * Parameters:
		 *		$param (string)	The name of the parameter to return
		 *		$default (mixed)	The default value to return
		 *							(optional) Default is null
		 * Returns the parameters value or the default value.
		 */
		function getParam($param, $default = null) {
			if (!isset($this->settings[$param]))
				return $default;
			return $this->settings[$param];
		}
		
		/**
		 * setParam -	Sets a parameter and writes the changes to
		 *				the settings file.
		 * Parameters:
		 *		$param (string)	The name of the parameter to set
		 *		$value (mixed)	The value of the parameter
		 */
		function setParam($param, $value) {
			$this->settings[$param] = $value;
			$this->writeSettings();
		}
		
		/**
		 * setParams -	Set multiple parameters passed in by an
		 *				associative array and write the changes
		 *				to the settings file.
		 * Parameters:
		 *		$params (array)	An associative array containing all
		 *						of the parameters and values to set.
		 */
		function setParams($params) {
			$this->settings = array_merge($this->settings, (array)$params);
			$this->writeSettings();
		}
		
		/**
		 * writeSettings -	Write the current settings to the config
		 *					file.
		 */
		function writeSettings() {
			$content = "";
			if ($this->has_sections) {
				foreach ($this->settings as $key=>$elem) {
					$content .= "[".$key."]\n";
					foreach ($elem as $key2=>$elem2) {
						if(is_array($elem2))
						{
							for($i=0;$i<count($elem2);$i++)
							{
								$content .= $key2."[] = \"".$elem2[$i]."\"\n";
							}
						}
						else if($elem2=="") $content .= $key2." = \n";
						else $content .= $key2." = \"".$elem2."\"\n";
					}
				}
			}
			else {
				foreach ($this->settings as $key=>$elem) {
					if(is_array($elem))
					{
						for($i=0;$i<count($elem);$i++)
						{
							$content .= $key2."[] = \"".$elem[$i]."\"\n";
						}
					}
					else if($elem=="") $content .= $key2." = \n";
					else $content .= $key2." = \"".$elem."\"\n";
				}
			}
			$fileHandle = fopen(CONFIG_INI,'w') or die("Can't open file " . CONFIG_INI);
			fwrite($fileHandle, $content) or die ("Can't write changes to config file");
			fclose($fileHandle);
		}
		/**
		 * deleteSettings - Deletes settings and removes the config ini
		 */
		function deleteSettings() {
			$this->settings = array();
			//fclose(fopen(CONFIG_INI,'w'));
			//@unlink(CONFIG_INI);
		}
	}
?>
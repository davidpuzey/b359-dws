<?php
	/**
	 * dbConnection -	Handle database connection stuff.
	 * Constructor opens the database, if the native functions don't
	 * work then we will attempt to use PDO.
	 * Destructor closes the database.
	 */
	class dbConnection {
		function __construct() {
			// If normal SQLite does not work, resort to PDO
			if (function_exists("sqlite_open")) {
				$this->using_pdo = false;
				$this->dbhandle = sqlite_open('db/dws.db', 0666, $this->error);
				if (!$this->dbhandle) die ($this->error);
			} else {
				$this->using_pdo = true;
				try {
					$this->dbhandle = new PDO('sqlite:db/dws.db');
				} catch (PDOException $e) {
					die($e->getMessage());
				}
			}
		}
		
		function __destruct() {
			if ($this->using_pdo) {
				unset($this->dbhandle);
			} else {
				sqlite_close($this->dbhandle);
			}
		}
		
		/**
		 * query - Process a given sql string.
		 * Parameters:
		 *		$sql (string)	The sql string to apply to the
		 *						database.
		 * Returns the result from the sql processing.
		 */
		function query($sql) {
			if ($this->using_pdo) {
				$sth = $this->dbhandle->prepare($sql);
				$sth->execute();
				return $sth->fetchAll();
			} else {
				//var_dump(debug_backtrace());
				return sqlite_array_query($this->dbhandle, $sql);
			}
		}
		
		// TODO
		function select() {
		}
		
		function insert() {
		}
		
		function update() {
		}
		
		function delete() {
		}
	}
?>

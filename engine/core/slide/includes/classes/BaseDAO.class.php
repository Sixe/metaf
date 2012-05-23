<?php

class BaseDAO {

	var $host = "";
	var $port = "";
	var $databasename = "";

	var $login = "";
	var $password = "";

    var $DB = null;
    var $RS = null;
    var $error_message = "";

    //**** CONSTRUCTOR ****
    function BaseDAO() {
		include("slide/includes/inc/config.inc.php");

		$this->host = $DB_HOST;
		$this->port = "3306";
		$this->databasename = $DB_NAME;
		$this->login	= $DB_USER;
		$this->password	= $DB_PASSWD;
    }

	//**** CONNECTION ****
    function connect() {
		$this->DB = mysql_connect("$this->host:$this->port", "$this->login", "$this->password")
			or $error_message = mysql_errno().": ".mysql_error();
		if(!@mysql_select_db($this->databasename, $this->DB))
			$error_message = mysql_errno().": ".mysql_error();
    }

    function exec($sql) {

		//** apply query **
        $this->connect();
		$this->RS = @mysql_query($sql, $this->DB)
			or $error_message = mysql_errno().": ".mysql_error();
		return $this->RS;
    }

    function query($sql) {
        return $this->exec($sql);
    }

    function close() {
        if($this->DB != null) {
            mysql_close($this->DB);
            $this->DB = null;
            if($this->RS != null) {
                mysql_free_result($this->RS);
                $this->RS = null;
            }
        }
    }

    function getErrors() {
    	return $this->error_message;
    }

    //**** FOR RESULTSET ****
    function getRow() {
        $obj = null;
        if($this->RS != null) {
            $obj = mysql_fetch_row($this->RS);
        }
        return $obj;
    }

    function getObject() {
        $obj = null;
        if($this->RS != null) {
            $obj = mysql_fetch_object($this->RS);
        }
        return $obj;
    }

    function getArray() {
        $obj = null;
        if($this->RS != null) {
            $obj = mysql_fetch_array($this->RS);
        }
        return $obj;
    }

    function getAssoc() {
        $obj = null;
        if($this->RS != null) {
            $obj = mysql_fetch_assoc($this->RS);
        }
        return $obj;
    }

    function numCols() {
        $r = 0;
        if($this->RS != null) {
            $r = mysql_num_fields($this->RS);
        }
        return $r;
    }

    function numRows() {
        $r = 0;
        if($this->RS != null) {
            $r = mysql_num_rows($this->RS);
        }
        return $r;
    }

    function affectedRows() {
        $r = 0;
        if($this->RS != null) {
            $r = mysql_affected_rows();
        }
        return $r;
    }

    function getInsertedId() {
        $id = null;
        if ($this->RS != null) {
            $id = mysql_insert_id ($this->DB);
        }
        return $id;
    }

	//**** TRANSACTIONS ****
    function beginTrans() {
        return @mysql_query($this->DB, "begin");
    }

    function commitTrans() {
        return @mysql_query($this->DB, "commit");
    }

    // returns true/false
    function rollbackTrans() {
        return @mysql_query($this->DB, "rollback");
    }

}

?>
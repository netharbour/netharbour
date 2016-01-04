<?php

// Class file for Properties

// include the database configuration and
// open connection to database


class Property {
	private $property_id;
	private $name;
	private $value;
	private $desc;
	private $error = false;
	
	function __construct($property_name = '') {
		if ($property_name != '') {
			$this->name = $property_id;
		}
	}

	public function get_properties() {
		$properties = array();
		$query = "SELECT name, value, description " .
			" FROM properties order by name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$properties[$obj->name] = $obj->value;
		}
		return $properties;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_property($name = '') {
		if ($name == '') {
			$name = $this->name;
		}
		
		if ($name == '') {
			return false;
			$this->error = "Invalid name";
		}
		
		$query = "SELECT value  FROM properties where name = '$name' ";
		$result = mysql_query($query);
		if (!$result) {
			$this->error = mysql_error();
        		return false;
		}
		if (mysql_num_rows($result) < 1) {
			$this->error = "property '$name' not found: " .mysql_num_rows($result) ;
        		return false;
		}
		// execute the query 
        	if (!$result)  {
        		return false;
        	}
 		while ($obj = mysql_fetch_object($result)){
			return  $obj->value;
		}
	}

	function get_desc($name) {
		if ($name == '') {
			$name = $this->name;
		}
		if ($name == '') {
			return false;
			$this->error = "Invalid name";
		}
		$query = "SELECT description " .
			" FROM properties where name = '$name' ";
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			return  $obj->description;
		}
	}


	// Set functions
	function set_property($name,$value,$desc='') {
		if ($name == '') {
			$this->error = "Invalid property";
			return false;
		}
		
		if ($this->get_property($name) === false) {
			$this->add_property($name, $value, $desc);
		}
		else {
		
			$query = "update properties " .
				" SET value = '$value' WHERE  name = '$name' ";
			$result =  mysql_query($query) ;
			// execute the query 
			if (!$result)  {
				$this->error = mysql_error() . "$query";
				return false;
			}
			return true;
		}
	}

	function add_property($name,$value,$desc ='') {
		if ($name == '') {
			return false;
		}
		if ($this->get_property($name)) {
			$this->error = "Property already exists";
			return false;
		}
		$query = "INSERT INTO properties " .
			" SET value = '$value',  name = '$name', description = '$desc'";
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
			$this->error =  mysql_error();
			return false;
		}
		return true;
	}



	function delete_property($name) {
		if ($name == '') {
			$name = $this->name;
		}
		if ($name == '') {
			$this->error = "Invalid name";
			return false;
		} 
		$query = "delete from properties where name = '$name'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

}

class Property_users {
	private $user_id;
	private $name;
	private $friendly_name;
	private $value;
	private $desc;
	private $error = false;
	
	function __construct($property_name = '') {
		if ($name != '') {
			$this->name = $property_id;
		}
	}

	public function get_properties() {
		$properties = array();
		$query = "SELECT name, friendly_name, user_id, value, description " .
			" FROM properties_user order by name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$properties[$obj->name] = $obj->value;
		}
		return $properties;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_property($name = '') {
		if ($name == '') {
			$name = $this->name;
		}
		
		if ($name == '') {
			return false;
			$this->error = "Invalid name";
		}
		
		$query = "SELECT value " .
			" FROM properties_user where name = '$name' ";
		$result =  mysql_query($query) ;
		if (mysql_num_rows($result) < 1) {
			$this->error = "property '$name' not found: " .mysql_num_rows($result) ;
        		return false;
		}
		// execute the query 
        	if (!$result)  {
        		return false;
        	}
 		while ($obj = mysql_fetch_object($result)){
			return  $obj->value;
		}
	}

	function get_desc($name) {
		if ($name == '') {
			$name = $this->name;
		}
		if ($name == '') {
			return false;
			$this->error = "Invalid name";
		}
		$query = "SELECT description " .
			" FROM properties_user where name = '$name' ";
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			return  $obj->description;
		}
	}


	// Set functions
	function set_property($name,$value, $desc = '') {
		if ($name == '') {
			$this->error = "Invalid property";
			return false;
		}
		
		if ($this->get_property($name) === false) {
			$this->add_property($name, $value, $desc);
		}
		else {
			$query = "update properties_user " .
				" SET value = '$value', description = '$desc' WHERE  name = '$name' AND user_id=".$_SESSION['userid']." ";
			$result =  mysql_query($query) ;
			// execute the query 
			if (!$result)  {
				$this->error = mysql_error() . "$query";
				return false;
			}
			return true;
		}
	}

	function add_property($name,$value,$desc ='') {
		if ($name == '') {
			return false;
		}
		if ($this->get_property($name)) {
			$this->error = "Property already exists";
			return false;
		}
		$query = "INSERT INTO properties_user " .
			" SET value = '$value',  name = '$name', description = '$desc', user_id=".$_SESSION['userid']."";
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
			$this->error =  mysql_error();
			return false;
		}
		return true;
	}



	function delete_property($name) {
		if ($name == '') {
			$name = $this->name;
		}
		if ($name == '') {
			$this->error = "Invalid name";
			return false;
		} 
		$query = "delete from properties_user where name = '$name'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

}
?>

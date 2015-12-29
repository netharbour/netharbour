<?php

// Class file for Locations

class Location {
	private $location_id;
	private $name;
	private $desc;
	private $country;
	private $province;
	private $city;
	private $addr_line1;
	private $addr_line2;
	private $zip_code;
	private $location_type;
	private $contact_group_id;
	private $notes;

	private $error = false;
	
	function __construct($location_id = '') {
		if (is_numeric($location_id)) {
			$this->get_location_info($location_id);
		}
	}

	protected function get_location_info($location_id){
		$query = "SELECT location_id, location_name, location_desc, ".
			"location_country, location_province, location_addr_line1, ".
			"location_addr_line2, location_zip_code, location_type, " .
			"location_notes, location_contact_group, location_city ".
			" FROM pop_locations where location_id = '$location_id' " ;
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$this->error = mysql_error() ."   -- query: $query ";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this location";
        	return false;
		}

 		while ($obj = mysql_fetch_object($result)){
			$this->location_id = $obj->location_id;
			$this->name = $obj->location_name;
			$this->desc = $obj->location_desc;
			$this->country = $obj->location_country;
			$this->province = $obj->location_province;
			$this->city = $obj->location_city;
			$this->zip_code = $obj->location_zip_code;
			$this->addr_line1 = $obj->location_addr_line1;
			$this->addr_line2 = $obj->location_addr_line2;
			$this->location_type = $obj->location_type;
			$this->contact_group_id = $obj->location_contact_group;
			$this->notes = $obj->location_notes;
		}
		return true;
	}

	public function get_locations($archived = 0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$locations = array();
		$query = "SELECT location_id, location_name " .
			" FROM pop_locations where archived = '$archived' order by
			location_name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$locations[$obj->location_id] = $obj->location_name;
		}
		return $locations;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_name() {
		return $this->name;
	}
	function get_desc() {
		return $this->desc;
	}
	function get_country() {
		return $this->country;
	}
	function get_province() {
		return $this->province;
	}
	function get_city() {
		return $this->city;
	}
	function get_addr_line1() {
		return $this->addr_line1;
	}
	function get_addr_line2() {
		return $this->addr_line2;
	}
	function get_zip_code() {
		return $this->zip_code;
	}
	function get_location_type() {
		return $this->location_type;
	}
	function get_contact_group_id() {
		return $this->contact_group_id;
	}
	function get_notes() {
		return $this->notes;
	}

	function get_rooms($archived = 0) {
		
		if ($archived != 0) {
			$archived = 1;
		}
		$rooms = array();
		$query = "SELECT room_id, room_name " .
			" FROM pop_rooms where archived = '$archived' AND location_id = $this->location_id order by room_name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$rooms[$obj->room_id] = $obj->room_name;
		}
		return $rooms;
	}


	// Set functions
	function set_name($value) {
		if ($value != '') {
			$this->name = $value;
			return true;
		} else {
			$this->error = "Name can not be empty";
			return false;
		}
	}
	function set_desc($value) {
		$this->desc = $value;
	}
	function set_country($value) {
		$this->country = $value;
	}
	function set_province($value) {
		$this->province = $value;
	}
	function set_city($value) {
		$this->city = $value;
	}
	function set_addr_line1($value) {
		$this->addr_line1 = $value;
	}
	function set_addr_line2($value) {
		$this->addr_line2 = $value;
	}
	function set_zip_code($value) {
		$this->zip_code = $value;
	}
	function set_location_type($value) {
		if (is_numeric($value)) {
			$this->location_type = $value;
			return true;
		} else {
			$this->error = "Invalid location type (should be an Integer)";
			return false;
		}
	}
	function set_contact_group_id($value) {
		if (is_numeric($value)) {
			$this->contact_group_id = $value;
			return true;
		} else {
			$this->error = "Invalid contact group id (should be an Integer)";
			return false;
		}
	}
	function set_notes($value) {
		$this->notes = $value;
	}


	function update() {
		// Update the info in the database

		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->location_type))) {
			$this->error = "Invalid location type (should be an Integer)";
			return false;
		}
		if (!(is_numeric($this->location_id))) {
			$this->error = "Invalid location id";
			return false;
		}

		// Contact Group may be NULL
		if ( is_numeric($this->contact_group_id)) {
			$contact_group = $this->contact_group_id;
		} else {	
			$contact_group = "NULL";
		}

		$query = "UPDATE pop_locations SET 
				location_name = '$this->name', 
				location_desc = '$this->desc',
				location_country = '$this->country', 
				location_province = '$this->province',
				location_city = '$this->city', 
				location_addr_line1 = '$this->addr_line1', 
				location_addr_line2 = '$this->addr_line2', 
				location_zip_code = '$this->zip_code', 
				location_type = '$this->location_type', 
				location_notes = '$this->notes', 
				location_contact_group = $contact_group
				WHERE location_id = '$this->location_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return $result;
	}

	function insert() {
	// Test mandatory fields
		if ($this->location_id != '') {
			$this->error = "This is an insert, location_id should be empty";
			return false;
		} 
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}

		// Contact Group may be NULL
		if ( is_numeric($this->contact_group_id)) {
			$contact_group = $this->contact_group_id;
		} else {	
			$contact_group = "NULL";
		}

		$query = "Insert into pop_locations SET 
				location_name = '$this->name', 
				location_desc = '$this->desc',
				location_country = '$this->country', 
				location_province = '$this->province',
				location_city = '$this->city', 
				location_addr_line1 = '$this->addr_line1', 
				location_addr_line2 = '$this->addr_line2', 
				location_zip_code = '$this->zip_code', 
				location_type = '$this->location_type', 
				location_notes = '$this->notes', 
				location_contact_group = $contact_group ";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		$id = mysql_insert_id();
		return $id;
	}

	function delete() {
		if (!is_numeric($this->location_id)) {
			$this->error = "Invalid location id";
			return false;
		} 

		$query = "update pop_locations set archived = '1' where location_id = '$this->location_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}

		return true;
	}

	function __toString() {
		//$mystring = print_r($this, $return = true);
		//return "<pre>$mystring</pre>";
		$mystring = "<table border=1>";
		foreach($this as $key => $value) {
			$mystring .= "<tr><td>$key</td><td> $value </td></tr>";
		}
		$mystring .= "</table>";
		return $mystring;
    }

}

class LocationType {
	private $location_type_id;
	private $name;
	private $desc;
	private $error = false;
	
	function __construct($location_type_id = '') {
		if (is_numeric($location_type_id)) {
			$this->get_location_type_info($location_type_id);
		}
	}

	private function get_location_type_info($location_type_id){
		$query = "SELECT location_type_id, 
			location_type_name,
			location_type_desc 
			FROM pop_location_types where location_type_id = '$location_type_id' " ;
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$this->error = mysql_error() ."   -- query: $query ";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this location";
        	return false;
		}

 		while ($obj = mysql_fetch_object($result)){
			$this->location_type_id = $obj->location_type_id;
			$this->name = $obj->location_type_name;
			$this->desc = $obj->location_type_desc;
		}
		return true;
	}

	public function get_location_types($archived = 0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$location_types = array();
		$query = "SELECT location_type_id, location_type_name " .
			" FROM pop_location_types where archived = '$archived' order by
			location_type_name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$location_types[$obj->location_type_id] = $obj->location_type_name;
		}
		return $location_types;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_name() {
		return $this->name;
	}
	function get_desc() {
		return $this->desc;
	}

	// Set functions
	function set_name($value) {
		if ($value != '') {
			$this->name = $value;
			return true;
		} else {
			$this->error = "Name can not be empty";
			return false;
		}
	}

	function set_desc($value) {
		$this->desc = $value;
	}


	function update() {
		// Update the info in the database

		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->location_type_id))) {
			$this->error = "Invalid location type id";
			return false;
		}

		$query = "UPDATE pop_location_types SET 
				location_type_name = '$this->name', 
				location_type_desc = '$this->desc' 
				WHERE location_type_id = '$this->location_type_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() ." Query was: $query";
			return false;
		}
		return $result;
	}

	function insert() {
	// Test mandatory fields
		if ($this->location_type_id != '') {
			$this->error = "This is an insert, location_type_id should be empty";
			return false;
		} 
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}

		$query = "Insert into pop_location_types SET 
				location_type_name = '$this->name', 
				location_type_desc = '$this->desc'";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		$id = mysql_insert_id();
		return $id;
	}

	function delete() {
		if (!is_numeric($this->location_type_id)) {
			$this->error = "Invalid location_type_id ";
			return false;
		} 

		$query = "update pop_location_types set archived = '1' where location_type_id = '$this->location_type_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

}

class Room extends Location  {
	private $room_id;
	private $room_name;
	private $room_desc;
	private $room_notes;
	private $room_location_id;
	private $room_no;
	private $room_type;

	private $error = false;
	
	function __construct($room_id = '') {
		if (is_numeric($room_id)) {
			$this->get_room_info($room_id);
			parent::get_location_info($this->room_location_id);
		}
	}

	private function get_room_info($room_id){
		$query = "SELECT room_id, 
				room_name, 
				room_desc, 
				room_no, 
				room_notes, 
				room_type, 
				location_id 
				FROM pop_rooms where room_id = '$room_id' " ;
		
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$this->error = mysql_error() ."   -- query: $query ";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this location";
        	return false;
		}

 		while ($obj = mysql_fetch_object($result)){
			$this->room_id = $obj->room_id;
			$this->room_name = $obj->room_name;
			$this->room_desc = $obj->room_desc;
			$this->room_notes = $obj->room_notes;
			$this->room_no = $obj->room_no;
			$this->room_type = $obj->room_type;
			$this->room_location_id = $obj->location_id;
		}
		return true;
	}

	public function get_rooms($archived = 0) {
		
		if ($archived != 0) {
			$archived = 1;
		}
		$rooms = array();
		$query = "SELECT room_id, room_name " .
			" FROM pop_rooms where archived = '$archived' order by room_name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$rooms[$obj->room_id] = $obj->room_name;
		}
		return $rooms;
	}

	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_name() {
		return $this->room_name;
	}
	function get_desc() {
		return $this->room_desc;
	}

	function get_notes() {
		return $this->room_notes;
	}
	function get_room_type() {
		return $this->room_type;
	}
	function get_location_id() {
		return $this->room_location_id;
	}
	function get_room_no() {
		return $this->room_no;
	}
	function get_location_name() {
		return  parent::get_name();
	}
	function get_location_desc() {
		return  parent::get_desc();
	}

	// Set functions
	function set_name($value) {
		if ($value != '') {
			$this->room_name = $value;
			return true;
		} else {
			$this->error = "Name can not be empty";
			return false;
		}
	}

	function set_desc($value) {
		$this->room_desc = $value;
	}

	function set_notes($value) {
		$this->room_notes = $value;
	}

	function set_location_id($value) {
		if (is_numeric($value)) {
			$this->room_location_id = $value;
			return true;
		} else {
			$this->error = "Invalid location id (should be an Integer)";
		}
	}

	function set_room_no($value) {
		$this->room_no = $value;
	}

	function set_room_type($value) {
		if (is_numeric($value)) {
			$this->room_type = $value;
			return true;
		} else {
			$this->error = "Invalid room type (should be an Integer)";
			return false;
		}
	}


	function update() {
		// Update the info in the database

		// Test mandatory fields
		if ($this->room_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->room_type))) {
			$this->error = "Invalid room type (should be an Integer)";
			return false;
		}
		if (!(is_numeric($this->room_location_id))) {
			$this->error = "Invalid location id";
			return false;
		}
		if (!(is_numeric($this->room_id))) {
			$this->error = "Invalid room id";
			return false;
		}

		$query = "UPDATE pop_rooms SET 
				room_name = '$this->room_name', 
				room_desc = '$this->room_desc',
				room_type = '$this->room_type', 
				room_notes = '$this->room_notes', 
				room_no = '$this->room_no', 
				location_id = '$this->room_location_id'
				WHERE room_id = '$this->room_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return $result;
	}

	function insert() {
	// Test mandatory fields
		if ($this->room_id != '') {
			$this->error = "This is an insert, location_id should be empty";
			return false;
		} 

		if ($this->room_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->room_type))) {
			$this->error = "Invalid room type (should be an Integer)";
			return false;
		}
		if (!(is_numeric($this->room_location_id))) {
			$this->error = "Invalid location id";
			return false;
		}

		$query = "INSERT INTO pop_rooms SET 
				room_name = '$this->room_name', 
				room_desc = '$this->room_desc',
				room_type = '$this->room_type', 
				room_notes = '$this->room_notes', 
				room_no = '$this->room_no', 
				location_id = '$this->room_location_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		$id = mysql_insert_id();
		return $id;
	}

	function delete() {
		if (!is_numeric($this->room_id)) {
			$this->error = "Invalid room id";
			return false;
		} 

		$query = "update pop_rooms set archived = '1' where room_id = '$this->room_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}

		return true;
	}
}

class RoomType {
	private $room_type_id;
	private $name;
	private $desc;
	private $error = false;
	
	function __construct($room_type_id = '') {
		if (is_numeric($room_type_id)) {
			$this->get_room_type_info($room_type_id);
		}
	}

	private function get_room_type_info($room_type_id){
		$query = "SELECT room_type_id, 
			room_type_name,
			room_type_desc 
			FROM pop_room_types where room_type_id = '$room_type_id' " ;
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$this->error = mysql_error() ."   -- query: $query ";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this room type";
        	return false;
		}

 		while ($obj = mysql_fetch_object($result)){
			$this->room_type_id = $obj->room_type_id;
			$this->name = $obj->room_type_name;
			$this->desc = $obj->room_type_desc;
		}
		return true;
	}

	public function get_room_types($archived = 0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$room_types = array();
		$query = "SELECT room_type_id, room_type_name " .
			" FROM pop_room_types where archived = '$archived' order by room_type_name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$room_types[$obj->room_type_id] = $obj->room_type_name;
		}
		return $room_types;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_name() {
		return $this->name;
	}
	function get_desc() {
		return $this->desc;
	}

	// Set functions
	function set_name($value) {
		if ($value != '') {
			$this->name = $value;
			return true;
		} else {
			$this->error = "Name can not be empty";
			return false;
		}
	}

	function set_desc($value) {
		$this->desc = $value;
	}


	function update() {
		// Update the info in the database

		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->room_type_id))) {
			$this->error = "Invalid room type id: $this->room_type_id";
			return false;
		}

		$query = "UPDATE pop_room_types SET 
				room_type_name = '$this->name', 
				room_type_desc = '$this->desc' 
				WHERE room_type_id = '$this->room_type_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return $result;
	}

	function insert() {
	// Test mandatory fields
		if ($this->room_type_id != '') {
			$this->error = "This is an insert, room_type_id should be empty";
			return false;
		} 
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}

		$query = "Insert into pop_room_types SET 
				room_type_name = '$this->name', 
				room_type_desc = '$this->desc'";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		$id = mysql_insert_id();
		return $id;
	}

	function delete() {
		if (!is_numeric($this->room_type_id)) {
			$this->error = "Invalid room_type_id ";
			return false;
		} 

		$query = "update pop_room_types set archived = '1' where room_type_id = '$this->room_type_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

}

?>

<?php

// Class file for Contact management

class Contact {
	private $group_id;
	private $group_type;
	private $group_type_name;
	private $name;
	private $desc;
	private $notes;
	private $custom_client_id;
	private $custom_client_group_id;

	private $error = false;
	
	function __construct($group_id = '') {
		if (is_numeric($group_id)) {
			$this->get_group_info($group_id);
		}
	}

	private function get_group_info($group_id){
		$query = "SELECT group_id, group_type, group_name, ".
			"group_desc, group_notes, custom_client_id, ".
			"custom_client_group_id, " .
			"contact_group_types.group_type_name ".
			" FROM contact_groups,contact_group_types WHERE
			group_id = '$group_id'
			AND contact_groups.group_type = contact_group_types.group_type_id" ;
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$this->error = mysql_error() ."   -- query: $query ";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this Group";
        	return false;
		}

 		while ($obj = mysql_fetch_object($result)){
			$this->group_id = $obj->group_id;
			$this->name = $obj->group_name;
			$this->desc = $obj->group_desc;
			$this->notes = $obj->group_notes;
			$this->custom_client_id = $obj->custom_client_id;
			$this->custom_client_group_id = $obj->custom_client_group_id;
			$this->group_type = $obj->group_type;
			$this->group_type_name = $obj->group_type_name;
		}
		return true;
	}

	public function get_groups($archived = 0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$groups = array();
		$query = "SELECT group_id, group_name " .
			" FROM contact_groups where archived = '$archived' order by
			group_name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        	if (!$result)  {
		//	$this->error = mysql_error() ."   -- query: $query ";
        		return false;
        	}
 		while ($obj = mysql_fetch_object($result)){
			$groups[$obj->group_id] = $obj->group_name;
		}
		return $groups;
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
	function get_group_type() {
		return $this->group_type;
	}
	function get_group_type_name() {
		return $this->group_type_name;
	}
	function get_notes() {
		return $this->notes;
	}
	function get_custom_client_id() {
		return $this->custom_client_id;
	}

	function get_custom_group_id() {
		return $this->custom_client_group_id;
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

	function set_group_type($value) {
		if (is_numeric($value)) {
			$this->group_type = $value;
			return true;
		} else {
			$this->error = "Invalid group type (should be an Integer)";
			return false;
		}
	}
	function set_notes($value) {
		$this->notes = $value;
	}
	function set_custom_client_id($value) {
		$this->custom_client_id = $value;
	}
	function set_custom_group_id($value) {
		$this->custom_client_group_id = $value;
	}


	function update() {
		// Update the info in the database

		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->group_type))) {
			$this->error = "Invalid group type (should be an Integer)";
			return false;
		}
		if (!(is_numeric($this->group_id))) {
			$this->error = "Invalid group id";
			return false;
		}

		$query = "UPDATE contact_groups SET 
				group_name = '$this->name', 
				group_desc = '$this->desc',
				group_notes = '$this->notes', 
				custom_client_id = '$this->custom_client_id', 
				custom_client_group_id = '$this->custom_client_group_id', 
				group_type = '$this->group_type' 
				WHERE group_id = '$this->group_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		$this->get_group_info($this->group_id);
		return $result;
	}

	function insert() {
	// Test mandatory fields
		if ($this->group_id != '') {
			$this->error = "This is an insert, group_id should be empty";
			return false;
		} 
		if (!(is_numeric($this->group_type))) {
			$this->error = "Invalid group type (should be an Integer)";
			return false;
		}
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}

		$query = "Insert into contact_groups SET 
				group_name = '$this->name', 
				group_desc = '$this->desc',
				group_notes = '$this->notes', 
				custom_client_id = '$this->custom_client_id', 
				custom_client_group_id = '$this->custom_client_group_id', 
				group_type = '$this->group_type'";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		$id = mysql_insert_id();
		$this->get_group_info($id);
		return $id;
	}

	function delete() {
		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		} 

		$query = "update contact_groups set archived = '1' where group_id = '$this->group_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}

		return true;
	}

	// Now we need to be able to add en remove contacts from groups
	// This is stored in the groups_contacts  table
	
	function update_contact_notes($contact_id,$contact_type_id,$notes) {


		// First check if this is a valid contactid
		$contact = new Person($contact_id);
		if (! $contact->get_contact_id()) {
			$this->error = "Invalid contact id, could not find contact information";
			return false;
		}

		// check if this is a valid contact type
		$contact_type = new PersonType($contact_type_id);
		if (! $contact_type->get_contact_type_id()) {
			$this->error = "Invalid contact type id, could not find contact type";
			return false;
		}

		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}

		$query = "UPDATE groups_contacts  SET 
				notes = '$notes 
				WHERE 
                group_id = '$this->group_id' AND
                contact_id = '$contact_id' AND
                contact_type = '$contact_type_id'";

		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		return true;
	}

	function add_contact($contact_id,$contact_type_id,$notes) {


		// First check if this is a valid contactid
		$contact = new Person($contact_id);
		if (! $contact->get_contact_id()) {
			$this->error = "Invalid contact id $contact_id, could not find contact information";
			return false;
		}

		// check if this is a valid contact type
		$contact_type = new PersonType($contact_type_id);
		if (! $contact_type->get_contact_type_id()) {
			$this->error = " Invalid contact type id $contact_type_id, could not find contact
			type.  ". $contact_type->get_error();
			return false;
		}

		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group id $this->group_id";
			return false;
		}

		$query = "Insert into groups_contacts  SET 
				group_id = '$this->group_id', 
				contact_id = '$contact_id',
				contact_type = '$contact_type_id', 
				notes = '$notes'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		return true;

	}

	function remove_contact($contact_id,$contact_type_id) {

		// First check if this is a valid contactid
		$contact = new Person($contact_id);
		if (! $contact->get_contact_id()) {
			$this->error = "Invalid contact id, could not find contact information";
			return false;
		}

		// check if this is a valid contact type
		$contact_type = new PersonType($contact_type_id);
		if (! $contact_type->get_contact_type_id()) {
			$this->error = "Invalid contact type id, could not find contact type";
			return false;
		}

		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}

		$query = "delete from groups_contacts  WHERE 
				group_id = '$this->group_id' AND
				contact_id = '$contact_id' AND
				contact_type = '$contact_type_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		return true;
	}

	function get_contacts() {
		$query = "SELECT groups_contacts.contact_id,
			groups_contacts.contact_type,
			groups_contacts.notes,
			contacts.name_first,
			contacts.name_last,
			contact_types.contact_type_name
			FROM contacts, contact_groups, contact_types, groups_contacts
            WHERE groups_contacts.group_id = '$this->group_id' 
			AND groups_contacts.contact_id = contacts.contact_id 
			AND groups_contacts.contact_type = contact_types.contact_type_id
			AND contact_types.archived = 0
			AND contacts.archived = 0
			GROUP BY groups_contacts.contact_id, groups_contacts.contact_type";
        // execute the query 
        $result =  mysql_query($query) ;
		$contacts = array();

        if (!$result)  {
            $this->error = mysql_error() ."   -- query: $query ";
            return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$contact['contact_name'] = $obj->name_first ." ".  $obj->name_last;
			$contact['contact_id'] = $obj->contact_id;
			$contact['notes'] = $obj->notes;
			$contact['contact_type'] = $obj->contact_type_name;
			$contact['contact_type_id'] = $obj->contact_type;
			array_push($contacts, $contact);
		}
		return $contacts;
	}
}

class ContactType {
	private $group_type_id;
	private $name;
	private $desc;
	private $error = false;
	
	function __construct($group_type_id = '') {
		if (is_numeric($group_type_id)) {
			$this->get_group_type_info($group_type_id);
		}
	}

	private function get_group_type_info($group_type_id){
		$query = "SELECT group_type_id, 
			group_type_name,
			group_type_desc 
			FROM contact_group_types  where group_type_id = '$group_type_id' " ;
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$this->error = mysql_error() ."   -- query: $query ";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this group type";
        	return false;
		}

 		while ($obj = mysql_fetch_object($result)){
			$this->group_type_id = $obj->group_type_id;
			$this->name = $obj->group_type_name;
			$this->desc = $obj->group_type_desc;
		}
		return true;
	}

	public function get_group_types($archived = 0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$group_types = array();
		$query = "SELECT group_type_id, group_type_name " .
			" FROM contact_group_types where archived = '$archived' order by
			group_type_name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$group_types[$obj->group_type_id] = $obj->group_type_name;
		}
		return $group_types;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_group_type_id() {
		return $this->group_type_id;
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
		if (!(is_numeric($this->group_type_id))) {
			$this->error = "Invalid group_type_id ";
			return false;
		}

		$query = "UPDATE contact_group_types SET 
				group_type_name = '$this->name', 
				group_type_desc = '$this->desc' 
				WHERE group_type_id = '$this->group_type_id'";
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
		if ($this->group_type_id != '') {
			$this->error = "This is an insert, group_type_id should be empty";
			return false;
		} 
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}

		$query = "Insert into contact_group_types SET 
				group_type_name = '$this->name', 
				group_type_desc = '$this->desc'";
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
		if (!is_numeric($this->group_type_id)) {
			$this->error = "Invalid group_type_id ";
			return false;
		} 

		$query = "update contact_group_types set archived = '1' where
		group_type_id = '$this->group_type_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

}

class PersonType {
	private $contact_type_id = false;
	private $name;
	private $desc;
	private $error = false;
	
	function __construct($contact_type_id = '') {
		if (is_numeric($contact_type_id)) {
			$this->get_contact_type_info($contact_type_id);
		}
	}

	private function get_contact_type_info($contact_type_id){
		$query = "SELECT contact_type_id, 
			contact_type_name,
			contact_type_desc 
			FROM contact_types  where contact_type_id = '$contact_type_id' " ;
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$this->error = mysql_error() ."   -- query: $query ";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this contact type";
        	return false;
		}

 		while ($obj = mysql_fetch_object($result)){
			$this->contact_type_id = $obj->contact_type_id;
			$this->name = $obj->contact_type_name;
			$this->desc = $obj->contact_type_desc;
		}
		return true;
	}

	public function get_contact_types($archived = 0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$contact_types = array();
		$query = "SELECT contact_type_id, contact_type_name " .
			" FROM contact_types where archived = '$archived' order by
			contact_type_name" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
			print "QUERY FAILED $query\n";
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$contact_types[$obj->contact_type_id] = $obj->contact_type_name;
		}
		return $contact_types;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_contact_type_id() {
		return $this->contact_type_id;
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
		if (!(is_numeric($this->contact_type_id))) {
			$this->error = "Invalid contact_type id ";
			return false;
		}

		$query = "UPDATE contact_types SET 
				contact_type_name = '$this->name', 
				contact_type_desc = '$this->desc' 
				WHERE contact_type_id = '$this->contact_type_id'";
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
		if ($this->contact_type_id != '') {
			$this->error = "This is an insert, contact_type id should be empty";
			return false;
		} 
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}

		$query = "Insert into contact_types SET 
				contact_type_name = '$this->name', 
				contact_type_desc = '$this->desc'";
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
		if (!is_numeric($this->contact_type_id)) {
			$this->error = "Invalid contact_type_id ";
			return false;
		} 

		$query = "update contact_types set archived = '1' where
		contact_type_id = '$this->contact_type_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}
}

class Person {
	private $contact_id = false;
	private $name_first;
	private $name_middle;
	private $name_last;
	private $country;
	private $province;
	private $city;
	private $addr_line1;
	private $addr_line2;
	private $zipcode;
	private $phone1;
	private $phone1_comment;
	private $phone2;
	private $phone2_comment;
	private $phone_cell;
	private $phone_cell_comment;
	private $phone_pager;
	private $phone_pager_comment;
	private $phone_fax;
	private $email;
	private $notes;
	private $external_id1;
	private $external_id2;
	private $external_id3;
	private $error = false;
	
	function __construct($contact_id = '') {
		if (is_numeric($contact_id)) {
			$this->get_contact_info($contact_id);
		}
	}

	private function get_contact_info($contact_id){
		$query = "SELECT contact_id, 
			name_first,
			name_middle,
			name_last,
			country,
			province,
			city,
			addr_line1,
			addr_line2,
			zipcode,
			phone1,
			phone1_comment,
			phone2,
			phone2_comment,
			phone_cell,
			phone_cell_comment,
			phone_pager,
			phone_pager_comment,
			phone_fax,
			email,
			notes,
			external_id1, external_id2, external_id3
			FROM contacts  where contact_id = '$contact_id' " ;
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$this->error = mysql_error() ."   -- query: $query ";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this contact ";
        	return false;
		}

 		while ($obj = mysql_fetch_object($result)){
			$this->contact_id = $obj->contact_id;
			$this->name_first = $obj->name_first;
			$this->name_middle = $obj->name_middle;
			$this->name_last = $obj->name_last;
			$this->country = $obj->country;
			$this->province = $obj->province;
			$this->city = $obj->city;
			$this->addr_line1 = $obj->addr_line1;
			$this->addr_line2 = $obj->addr_line2;
			$this->zipcode = $obj->zipcode;
			$this->phone1 = $obj->phone1;
			$this->phone1_comment = $obj->phone1_comment;
			$this->phone2 = $obj->phone2;
			$this->phone2_comment = $obj->phone2_comment;
			$this->phone_cell = $obj->phone_cell;
			$this->phone_cell_comment = $obj->phone_cell_comment;
			$this->phone_pager = $obj->phone_pager;
			$this->phone_pager_comment = $obj->phone_cell_comment;
			$this->phone_fax = $obj->phone_fax;
			$this->email = $obj->email;
			$this->notes = $obj->notes;
			$this->external_id1 = $obj->external_id1;
			$this->external_id2 = $obj->external_id2;
			$this->external_id3 = $obj->external_id3;
		}
		return true;
	}

	public function get_contacts($archived = 0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$contacts = array();
		$query = "SELECT contact_id, name_first, name_last " .
			" FROM contacts where archived = '$archived' order by
			name_first, name_last" ;
		$result =  mysql_query($query) ;
		// execute the query 
        if (!$result)  {
        	return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$contacts[$obj->contact_id] = $obj->name_first ." ". $obj->name_last;
		}
		return $contacts;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_contact_id() {
		return $this->contact_id;
	}
	function get_first_name() {
		return $this->name_first;
	}
	function get_middle_name() {
		return $this->name_middle;
	}
	function get_last_name() {
		return $this->name_last;
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
	function get_postal_code() {
		return $this->zipcode;
	}
	function get_phone1() {
		return $this->phone1;
	}
	function get_phone1_comment() {
		return $this->phone1_comment;
	}
	function get_phone2() {
		return $this->phone2;
	}
	function get_phone2_comment() {
		return $this->phone2_comment;
	}
	function get_phone_cell() {
		return $this->phone_cell;
	}
	function get_phone_cell_comment() {
		return $this->phone_cell_comment;
	}
	function get_phone_pager() {
		return $this->phone_pager;
	}
	function get_phone_pager_comment() {
		return $this->phone_pager_comment;
	}
	function get_phone_fax() {
		return $this->phone_fax;
	}
	function get_email() {
		return $this->email;
	}
	function get_notes() {
		return $this->notes;
	}
	function get_external_id1() {
		return $this->external_id1;
	}
	function get_external_id2() {
		return $this->external_id2;
	}
	function get_external_id3() {
		return $this->external_id3;
	}


	// Set functions
	function set_first_name($value) {
		$this->name_first = $value;
		if ($this->name_first != '')
		{return true;}
	}
	function set_middle_name($value) {
		$this->name_middle = $value;
	}
	function set_last_name($value) {
		return $this->name_last = $value;
	}
	function set_country($value) {
		return $this->country = $value;
	}
	function set_province($value) {
		return $this->province = $value;
	}
	function set_city($value) {
		return $this->city = $value;
	}
	function set_addr_line1($value) {
		return $this->addr_line1 = $value;
	}
	function set_addr_line2($value) {
		return $this->addr_line2 = $value;
	}
	function set_postal_code($value) {
		return $this->zipcode = $value;
	}
	function set_phone1($value) {
		return $this->phone1 = $value;
	}
	function set_phone1_comment($value) {
		return $this->phone1_comment = $value;
	}
	function set_phone2($value) {
		return $this->phone2 = $value;
	}
	function set_phone2_comment($value) {
		return $this->phone2_comment = $value;
	}
	function set_phone_cell($value) {
		return $this->phone_cell = $value;
	}
	function set_phone_cell_comment($value) {
		return $this->phone_cell_comment = $value;
	}
	function set_phone_pager($value) {
		return $this->phone_pager = $value;
	}
	function set_phone_pager_comment($value) {
		return $this->phone_pager_comment = $value;
	}
	function set_phone_fax($value) {
		return $this->phone_fax = $value;
	}
	function set_email($value) {
		return $this->email = $value;
	}
	function set_notes($value) {
		return $this->notes = $value;
	}
	function set_external_id1($value) {
		return $this->external_id1 = $value;
	}
	function set_external_id2($value) {
		return $this->external_id2 = $value;
	}
	function set_external_id3($value) {
		return $this->external_id3 = $value;
	}


	function update() {
		// Update the info in the database

		// Test mandatory fields
		if (($this->name_first == '') && ($this->name_last == '')) {
			$this->error = "First or last name required";
			return false;
		}
		if (!(is_numeric($this->contact_id))) {
			$this->error = "Invalid contact id ";
			return false;
		}

		$query = "UPDATE contacts SET 
				name_first = '$this->name_first', 
				name_last = '$this->name_last', 
				name_middle = '$this->name_middle', 
				country = '$this->country', 
				province = '$this->province', 
				city = '$this->city', 
				addr_line1 = '$this->addr_line1', 
				addr_line2 = '$this->addr_line2', 
				zipcode = '$this->zipcode', 
				phone1 = '$this->phone1', 
				phone1_comment = '$this->phone1_comment', 
				phone2 = '$this->phone2', 
				phone2_comment = '$this->phone2_comment', 
				phone_cell = '$this->phone_cell', 
				phone_cell_comment = '$this->phone_cell_comment', 
				phone_pager = '$this->phone_pager', 
				phone_pager_comment = '$this->phone_pager_comment', 
				phone_fax = '$this->phone_fax', 
				email = '$this->email', 
				notes = '$this->notes', 
				external_id1 = '$this->external_id1', 
				external_id2 = '$this->external_id2', 
				external_id3 = '$this->external_id3' 
				WHERE contact_id = '$this->contact_id'";
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
		if ($this->contact_type_id != '') {
			$this->error = "This is an insert, contact_type id should be empty";
			return false;
		} 
		if (($this->name_first == '') && ($this->name_last == '')) {
			$this->error = "First or last name required";
			return false;
		}

		$query = "Insert into contacts SET 
				name_first = '$this->name_first', 
				name_last = '$this->name_last', 
				name_middle = '$this->name_middle', 
				country = '$this->country', 
				province = '$this->province', 
				city = '$this->city', 
				addr_line1 = '$this->addr_line1', 
				addr_line2 = '$this->addr_line2', 
				zipcode = '$this->zipcode', 
				phone1 = '$this->phone1', 
				phone1_comment = '$this->phone1_comment', 
				phone2 = '$this->phone2', 
				phone2_comment = '$this->phone2_comment', 
				phone_cell = '$this->phone_cell', 
				phone_cell_comment = '$this->phone_cell_comment', 
				phone_pager = '$this->phone_pager', 
				phone_pager_comment = '$this->phone_pager_comment', 
				phone_fax = '$this->phone_fax', 
				email = '$this->email', 
				notes = '$this->notes', 
				external_id1 = '$this->external_id1', 
				external_id2 = '$this->external_id2', 
				external_id3 = '$this->external_id3' ";
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
		if (!is_numeric($this->contact_id)) {
			$this->error = "Invalid contact_id ";
			return false;
		} 

		$query = "update contacts set archived = '1' where
		contact_id = '$this->contact_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

	function get_groups() {
		$query = "SELECT groups_contacts.group_id, contact_groups.group_name 
			FROM groups_contacts, contact_groups
            WHERE groups_contacts.contact_id = '$this->contact_id' 
            AND groups_contacts.group_id = contact_groups.group_id";

        // execute the query 
        $result =  mysql_query($query) ;
		$groups = array();

        if (!$result)  {
            $this->error = mysql_error() ."   -- query: $query ";
            return false;
        }
 		while ($obj = mysql_fetch_object($result)){
			$groups[$obj->group_id] = $obj->group_name;
		}
		return $groups;
	}

}
?>

<?php

// Class file for Devices

// include the database configuration and
// open connection to database
//
ini_set('memory_limit', '-1');

class Device {
	// All interfaces	
	protected $interfaces = array();
	// All devices
	protected $devices = array();
	// errors
	private $error = false;

	protected $device_id;
	protected $device_name;
	protected $location_id;
	protected $location_name;
	protected $device_type;
	protected $snmp_ro;
	protected $snmp_rw;
	protected $snmp_version;
	protected $ro_user;
	protected $ro_password;
	protected $notes;
	protected $device_fqdn;
	protected $device_oob;

	function __construct($device_id = '') {
		if (is_numeric($device_id)) {
			$this->get_device_info($device_id);
			if ($this->device_id == '') {
				$this->error = "Device not found";
				return false;
			}
		}
	}

	function __toString() {
		$mystring = print_r($this, $return = true);
		return "<pre>$mystring</pre>";
	}

	private function get_device_info($device_id) {
         $query = "SELECT Devices.device_id, Devices.name, Devices.location, Devices.type, ".
                  " Devices.snmp_ro, Devices.snmp_rw, Devices.snmp_version, ".
                  " Devices.ro_user, Devices.ro_password, Devices.notes, Devices.device_fqdn, Devices.device_oob, ".
                  " pop_locations.location_name  ".
                  "FROM  Devices, pop_locations where device_id = '$device_id' AND Devices.location = pop_locations.location_id";     
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			$this->device_id = $obj->device_id;
			$this->device_name = $obj->name;
			$this->location_id = $obj->location;
			$this->location_name = $obj->location_name;
			$this->device_type = $obj->type;
			$this->snmp_ro = $obj->snmp_ro;
			$this->snmp_rw = $obj->snmp_rw;
			$this->snmp_version = $obj->snmp_version;
			$this->ro_user = $obj->ro_user;
			$this->ro_password = $obj->ro_password;
			$this->notes = $obj->notes;
			$this->device_fqdn = $obj->device_fqdn;
			$this->device_oob = $obj->device_oob;
		}
	}

	// Whole bunch of get functions	

	function get_error() {
		return $this->error;
	}

	function get_device_id() {
		return $this->device_id;
	}
	function get_name() {
		return $this->device_name;
	}
	function get_location_id() {
		return $this->location_id;
	}
	function get_location_name() {
		return $this->location_name;
	}
	function get_device_type() {
		return $this->device_type;
	}
	function get_snmp_ro() {
		return $this->snmp_ro;
	}
	function get_snmp_rw() {
		return $this->snmp_rw;
	}
	function get_snmp_version() {
		return $this->snmp_version;
	}
	function get_ro_user() {
		return $this->ro_user;
	}
	function get_ro_password() {
		return $this->ro_password;
	}
	function get_notes() {
		return $this->notes;
	}
	function get_device_fqdn() {
		return $this->device_fqdn;
	}
	function get_device_oob() {
		return $this->device_oob;
	}

	// Whole bunch of set functions	
	function set_name($value) {
		if ($value != '') {
			$this->device_name = $value;
			return true;
		} else {
			$this->error = "Name Can not be empty";
			return false;
		}
	}
	function set_location_id($value) {
		if (is_numeric($value)) {
			$this->location_id = $value;
			return true;
		} else {
			$this->error = "Location ID has to be a Number";
			return false;
		}
	}
	function set_device_type($value) {
		$this->device_type = $value;
	}
	function set_snmp_ro($value) {
		$this->snmp_ro = $value;
	}
	function set_snmp_rw($value) {
		$this->snmp_rw = $value;
	}
	function set_snmp_version($value) {
		$this->snmp_version = $value;
	}
	function set_ro_user($value) {
		$this->ro_user = $value;
	}
	function set_ro_password($value) {
		$this->ro_password = $value;
	}
	function set_notes($value) {
		$this->notes = $value;
	}
	function set_device_fqdn($value) {
		$this->device_fqdn = $value;
	}
	function set_device_oob($value) {
		$this->device_oob = $value;
	}

	function update() {
		// Update the info in the database
		// Test mandatory fields
		if ($this->device_name == '') {
		$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->device_id))) {
			$this->error = "Invalid device id";
			return false;
		}

		$query = "update Devices SET name = '$this->device_name', location = '$this->location_id',  " .
			" type = '$this->device_type', notes = '$this->notes', snmp_ro = '$this->snmp_ro', snmp_rw = '$this->snmp_rw', " .
			" snmp_version = '$this->snmp_version', ro_user = '$this->ro_user', ro_password = " .
			"'$this->ro_password', device_fqdn = '$this->device_fqdn', device_oob = " .
			"'$this->device_oob' " .
			" WHERE device_id = '$this->device_id'";
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
		if ($this->device_id != '') {
			$this->error = "This is an insert, device_id should be empty";
			return false;
		} 
		if ($this->device_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}

		$query = "INSERT INTO Devices (name, type, location, snmp_ro, snmp_rw, snmp_version, ".
			"ro_user, ro_password, notes, device_fqdn, device_oob) " .
			" Values ('$this->device_name', '$this->device_type', '$this->location_id', " .
			"'$this->snmp_ro', '$this->snmp_rw', '$this->snmp_version', '$this->ro_user', ".
			"'$this->ro_password', '$this->notes', '$this->device_fqdn', '$this->device_oob')";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
				return false;
			}
		$id = mysql_insert_id();
		return $id;
	}

	function delete() {
		if ($this->device_id == '') {
			$this->error = "Invalid device id";
			return false;
		} 
		$query = "update Devices SET archived = '1' where device_id = '$this->device_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			print mysql_error();
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

	public function get_devices($archived = 0) {
	//Return an array of devices names + id
		if ($archived != 0) {
			$archived == 1;
		}
		$all_devices = array();

		$query = "SELECT device_id, name ".
			"FROM  Devices where archived = '$archived' ORDER BY name, location ";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			#$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$all_devices[$obj->device_id] = $obj->name;
		}
		return $all_devices;
	}
	

	function get_control_ports() {
		// Will return a list of control ports
		// Control ports only exist on (power|console) devices

		if ($this->device_id == '') {
			$this->error = "Invalid device id";
			return false;
		} 

		$control_port_list = array();
		$query = "SELECT control_ports.control_port_id, control_ports.managed_device_id, " .
			" control_ports.control_port_description, control_ports.control_device_id, " .
			" control_ports.control_port_name, control_ports.control_group, " .
			" control_ports.control_port_type, Devices.name from control_ports, Devices " .
			" where control_device_id = '$this->device_id' " .
			" ORDER BY control_port_type, name ";   
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($result)){
			$control_port_list[$obj->control_port_id] = $obj->control_port_description;

			//  managed_device_id may be NULL.
			// If it's not NULL determine the name
			if ($obj->managed_device_id != '') {	
				$result_name = mysql_query("select name from Devices where device_id = '$obj->managed_device_id'");	
 				while ($objname = mysql_fetch_object($result_name)){
					$this->management_ports[$obj->control_port_id][manage_device_name] = $objname->name;
				}
			}
		}
		return $control_port_list;
	}

	function get_management_ports() {
		// returns list of management ports on this device
		// Management port is power or console

		$management_port_list = array();
		if ($this->device_id == '') {
			$this->error = "Invalid device id";
			return false;
		} 

		$query = "SELECT control_ports.control_port_id, control_ports.managed_device_id, " .
			" control_ports.control_port_description, control_ports.control_device_id, " .
			" control_ports.control_port_name, control_ports.control_group, " .
			" control_ports.control_port_type, Devices.name from control_ports, Devices " .
			" where managed_device_id = '$this->device_id' AND Devices.device_id = control_ports.control_device_id" .
			" ORDER BY control_port_type, name ";   
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($result)){
			$this->management_ports[$obj->control_port_id][control_port_id] = $obj->control_port_id;
			$this->management_ports[$obj->control_port_id][managed_device_id] = $obj->managed_device_id;
			$this->management_ports[$obj->control_port_id][control_port_description] = $obj->control_port_description;
			$this->management_ports[$obj->control_port_id][control_device_id] = $obj->control_device_id;
			$this->management_ports[$obj->control_port_id][control_device_name] = $obj->name;
			$this->management_ports[$obj->control_port_id][control_port_name] = $obj->control_port_name;
			$this->management_ports[$obj->control_port_id][control_group] = $obj->control_group;
			$this->management_ports[$obj->control_port_id][control_port_type] = $obj->control_port_type;
			$management_port_list[$obj->control_port_id] = $obj->control_port_name;
		}
		return $management_port_list;
	}

	function get_interfaces() {
		// returns array of interface id's (key) and interface name (value)
		return Port::get_device_interfaces($this->device_id);
	}

	function get_interface_id_by_name($name) {
		// Return interface id for an interface name;
		$result = false;

		$query = "SELECT interface_id from interfaces where interface_device =
			'$this->device_id' and (interface_name = '$name' or
			interface_descr = '$name')  AND active = '1'" ;
		// execute the query 
		$sql_result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($sql_result)){
			$result =  $obj->interface_id;
		}
		
		return $result;
	}

	function is_control_device(){
		/*
		 Returns true if device is a control device.
		Currently all devices from type console_server or power_control
		Are considered as control devices.
		*/
		if ($this->device_id == '') {
			$this->error = "Invalid device id";
			return false;
		} 
		
		$is_control_device = false;	
		$query = "select Devices.device_id from Devices, Device_types where device_id = '$this->device_id' " .
			" AND Device_types.device_type_id  = Devices.type and " .
			" (Device_types.type = 'console_server' or Device_types.type = 'power_control') " ;
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		$num_rows =  mysql_numrows($result);
		if ($num_rows > 0)  {
			$is_control_device = true;
		}
		return $is_control_device;
	}

	
	function get_devices_by_class($device_class, $archived=0) {
		//Return an array of devices of class 
		// device_id (key) device_name (value)
		if ($archived != 0) {
			$archived == 1;
		}
		$devices_by_class = array();
		$query = "SELECT distinct Devices.name, Devices.device_id  ".
			" FROM Devices, Device_types where Device_types.type = '$device_class' " .
			" AND Devices.type = Device_types.device_type_id AND Devices.archived = '$archived'" .
			" ORDER BY Devices.name desc ";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($result)){
			$devices_by_type[$obj->device_id] = $obj->name;
		}
		return $devices_by_type;
	}

	function get_type_name() {
		// returns device type for device id
		if ($this->device_id == '') {
			$this->error = "Invalid device id";
			return false;
		} 
		$query = "SELECT Device_types.name from Devices, Device_types where Devices.device_id = " .
			" '$this->device_id' And Device_types.device_type_id = Devices.type";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($result)){
			return $obj->name;
		}
	}

	function get_device_class() {
		// returns device type
		if ($this->device_id == '') {
			$this->error = "Invalid device id";
			return false;
		} 
		$query = "SELECT Device_types.type from Devices, Device_types where Devices.device_id = " .
			" '$this->device_id' And Device_types.device_type_id = Devices.type";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($result)){
			return $obj->type;
		}
	}
}

class Device_type {
	private $error = false;

	protected $type_id;
	protected $name;
	protected $description;
	protected $vendor;
	protected $device_class;
	protected $notes;

	function __construct($type_id = '') {
		if (is_numeric($type_id)) {
			$this->get_device_type_info($type_id);
			if ($this->type_id == '') {
				$this->error = "Device type not found";
				return false;
			}
		}
	}

	function __toString() {
		$mystring = print_r($this, $return = true);
		return "<pre>$mystring</pre>";
	}

	private function get_device_type_info($type_id) {
		$query = "SELECT  device_type_id, name, description, vendor, type, notes".
			" FROM  Device_types  where device_type_id  = '$type_id'";
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			$this->type_id = $obj->device_type_id;
			$this->name = $obj->name;
			$this->description = $obj->description;
			$this->vendor = $obj->vendor;
			$this->device_class = $obj->type;
			$this->notes = $obj->notes;
		}
	}

	// get functions
	function get_type_id() {
		return $this->type_id;
	}
	function get_name() {
		return $this->name;
	}
	function get_description() {
		return $this->description;
	}
	function get_vendor() {
		return $this->vendor;
	}
	function get_device_class() {
		return $this->device_class;
	}
	function get_notes() {
		return $this->notes;
	}
	
	// set functions
	function set_name($value) {
		if ($value == '') {
			$this->error = "Name can not be empty";
			return false;
		} else {
			$this->name = $value;
			return true;
		}
	}
	function set_description($value) {
		$this->description = $value;
	}
	function set_vendor($value) {
		$this->vendor = $value;
	}
	function set_device_class($value) {
		$this->device_class = $value;
	}
	function set_notes($value) {
		$this->notes = $value;
	}

	public function get_device_types($archived = 0) {
	//Return an array of devices names + id
		if ($archived != 0) {
			$archived == 1;
		}
		$all_device_types = array();

		$query = "SELECT device_type_id, name ".
			"FROM  Device_types where archived = '$archived' ORDER BY name ";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			#$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$all_device_types[$obj->device_type_id] = $obj->name;
		}
		return $all_device_types;
	}

	function update() {
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->type_id))) {
			$this->error = "Invalid device id";
			return false;
		}

		$query = "update Device_types SET name = '$this->name', ".
			" type = '$this->device_class', description = '$this->description', vendor = '$this->vendor', " .
			" notes = '$this->notes' " .
			" WHERE device_type_id = '$this->type_id'";
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
		if ($this->type_id != '') {
			$this->error = "This is an insert, device_type_id should be empty";
			return false;
		} 
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		$query = "insert into Device_types SET name = '$this->name', ".
			" type = '$this->device_class', description = '$this->description', vendor = '$this->vendor', " .
			" notes = '$this->notes' " ;

		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		$id = mysql_insert_id();
		return $id;
	}

	function delete() {
		if ($this->type_id == '') {
			$this->error = "Invalid device type id";
			return false;
		} 
		$query = "update Device_types SET archived = '1' where device_type_id = '$this->type_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			print mysql_error();
			$this->error = mysql_error();
			return false;
		}
		return true;
	}
	
	function get_error() {
		return $this->error;
	}
}


class ControlPort {
	protected $management_ports = array();
	protected $error = false;
	
	protected $control_port_id;
	protected $managed_device_id;
	protected $control_port_description;
	protected $manage_device_id;
	protected $control_port_name;
	protected $control_port;
	protected $control_group;
	protected $control_port_type;
	protected $control_device_id;
	protected $control_device_name;
	protected $managed_device_name ;

	function __construct($control_port_id = '') {
		if (is_numeric($control_port_id)) {
			$this->get_control_port_info($control_port_id);
		}
		else {
			#$this->error = "Control port not found";
			#return false;
		}
	}	

	function __toString() {
		$mystring = print_r($this, $return = true);
		return "<pre>$mystring</pre>";
	}

	function get_control_port_info($control_port_id){
		$query = "SELECT control_ports.control_port_id, control_ports.managed_device_id, " .
			" control_ports.control_port_description, control_ports.control_device_id, " .
			" control_ports.control_port_name, control_ports.control_group, " .
			" control_ports.control_port,  " .
			" control_ports.control_port_type, Devices.name from control_ports, Devices " .
			" where control_port_id = '$control_port_id' " .
			" AND Devices.device_id = control_ports.control_device_id " .
			" ORDER BY control_port_type, name ";   
		// execute the query 
		$result = mysql_query($query);
		if (!$result) {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$this->control_port_id = $obj->control_port_id;
			$this->managed_device_id = $obj->managed_device_id;
			$this->control_port_description = $obj->control_port_description;
			$this->manage_device_id = $obj->managed_device_id;
			$this->control_port_name = $obj->control_port_name;
			$this->control_port = $obj->control_port;
			$this->control_group = $obj->control_group;
			$this->control_port_type = $obj->control_port_type;
			$this->control_device_id= $obj->control_device_id;
			$this->control_device_name = $obj->name;

			//  managed_device_id may be NULL.
			// If it's not NULL determine the name
			if ($obj->managed_device_id != '') {	
				$result_name = mysql_query("select name from Devices where device_id = '$obj->managed_device_id'");	
 				while ($objname = mysql_fetch_object($result_name)){
					$this->managed_device_name = $objname->name;
				}
			}
		}
	}
	
	function get_control_device_id() {
		return $this->control_device_id;
	}
	function get_control_device_name() {
		return $this->control_device_name;
	}
	function get_managed_device_id() {
		return $this->managed_device_id;
	}
		
	function get_managed_device_name() {
		return $this->managed_device_name;
	}
	function get_description() {
		return $this->control_port_description;
	}
	function get_id() {
		return $this->control_port_id;
	}
	function get_type() {
		return $this->control_port_type;
	}
	function get_port() {
		return $this->control_port;
	}
	function get_group() {
		return $this->control_group;
	}
	function get_name() {
		return $this->control_port_name;
	}
	
	
	function set_control_device_id($control_device_id) {
		$this->control_device_id = $control_device_id;
	}
	function set_control_device_name($control_device_name) {
		$this->control_device_name = $control_device_name;
	}
	function set_managed_device_id($managed_device_id) {
		$this->managed_device_id = $managed_device_id;
	}
		
	function set_managed_device_name($managed_device_name) {
		$this->managed_device_name = $managed_device_name;
	}
	function set_description($description) {
		$this->control_port_description = $description;
	}
	function set_id($id) {
		$this->control_port_id = $id;
	}
	function set_type($type) {
		$this->control_port_type = $type;
	}
	function set_port($port) {
		$this->control_port = $port;
	}
	function set_group($group) {
		$this->control_group = $group;
	}
	function set_name($name) {
		$this->control_port_name = $name;
	}


	function delete() {
		if (!is_numeric($this->get_id())) {
			return false;
		} else {
			$query = "delete from control_ports where control_port_id = $this->control_port_id";
			// execute the query 
			$result = false;
			$result =  mysql_query($query);
			if (!$result) {
			print $query;
				$this->error = mysql_error();
				return false;
			}
			return $result;
		}
	}

	function update() {
		// expects an hash with the required fields.
		if (!is_numeric($this->managed_device_id)) {
			$this->managed_device_id = "NULL";
		}
		if (!is_numeric($this->control_port_id)) {
			$this->error = "Control port id is not valid, Should be a number";
			return false;
		}
		if (!is_numeric($this->control_device_id)) {
			$this->error = "Device id is not valid, Should be a number";
			return false;
		}
		if ($this->control_port_name == '') {
			$this->error = "Invalid port name, can not be empty";
			return false;
		}
		$query = "update control_ports SET managed_device_id = $this->managed_device_id, " .
			" control_port_description = '$this->control_port_description', " .
			" control_device_id = $this->control_device_id, " .
			" control_port_name = '$this->control_port_name' , ".
			"  control_port_type = '$this->control_port_type', " .
			"  control_port = '$this->control_port', " .
			"  control_group = '$this->control_group' " .
			" WHERE control_port_id = '$this->control_port_id'";
		// execute the query 
		$result = false;
		$result =  mysql_query($query);
		if (!$result) {
			return false;
			$this->error = mysql_error();
		}
		return $result;
	}

	function insert() {
		if (!is_numeric($this->managed_device_id)) {
			$this->managed_device_id = "NULL";
		}
		
		if (!is_numeric($this->control_device_id)) {
			$this->error = "Invalid control device id";
			return false;
		}
		if ($this->control_port_name == '') {
			$this->error = "Invalid control port name";
			return false;
		}
		$query = "INSERT INTO control_ports (managed_device_id, control_port_description, control_device_id, " .
			" control_port_name, control_port_type, control_port, control_group) ".
			" Values ($this->managed_device_id, " .
			" '$this->control_port_description', $this->control_device_id, ".	
			" '$this->control_port_name', '$this->control_port_type', ".
			" '$this->control_port', '$this->control_group') ";
		// execute the query 
		$id = false;
		$result =  mysql_query($query);
		if (!$result) {
			return false;
			$this->error = mysql_error();
		}
		$id = mysql_insert_id();
		return $id;
	}
	
	
	function get_error() {
		return $this->error;
	}
}

class Port {
	private $error = false;

	private $name;
	private $device_id;
	private $interface_id;
	private $ifindex;
	private $oper_status;
	private $speed;
	private $description;
	private $alias;
	private $mtu;
	private $type;
	private $inbits;
	private $outbits;
	private $inerrors;
	private $outerrors;
	private $inunicastpackets;
	private $outunicastpackets;
	private $innonunicastpacket;
	private $outnonunicastpackets;


    function __toString() {
		$mystring = print_r($this, $return = true);
		return "<pre>$mystring</pre>";
	}

	function set_ifindex($value) {
		$this->ifindex = $value;
	}
	function set_name($value) {
		$this->name = $value;
	}
	function set_oper_status($value) {
		$this->oper_status = $value;
	}
	function set_speed($value) {
		$this->speed = $value;
	}
	function set_descr($value) {
		$this->description = $value;
	}
	function set_alias($value) {
		$this->alias = $value;
	}
	function set_device_id($value) {
		$this->device_id = $value;
	}
	function set_mtu($value){
		$this->mtu = $value;
	}
	function set_type($value) {
		$this->type = $value;
	}
	function set_inbits($value) {
		$this->inbits = $value;
	}
	function set_outbits($value) {
		$this->outbits = $value;
	}
	function set_inunicast_packets($value) {
		$this->inunicastpackets = $value;
	}
	function set_outunicast_packets($value) {
		$this->outunicastpackets = $value;
	}

	// GET
	function get_interface_id() {
		return $this->interface_id;
	}
	function get_ifindex() {
		return $this->ifindex;
	}
	function get_name() {
		return $this->name;
	}
	function get_oper_status() {
		return $this->oper_status;
	}
	function get_speed() {
		return $this->speed;
	}
	function get_descr() {
		return $this->description;
	}
	function get_alias() {
		return $this->alias;
	}
	function get_device_id() {
		return $this->device_id;
	}
	function get_mtu() {
		return $this->mtu;
	}
	function get_type() {
		return $this->type;
	}
	function get_inbits() {
		return $this->inbits;
	}
	function get_outbits() {
		return $this->outbits;
	}
	function get_inunicast_packets() {
		return $this->inunicastpackets;
	}
	function get_outunicast_packets() {
		return $this->outunicastpackets;
	}
	function get_innonunicast_packets() {
		return $this->innonunicastpackets;
	}
	function get_outnonunicast_packets() {
		return $this->outnonunicastpackets;
	}
	function get_inerrors() {
		return $this->inerrors;
	}
	function get_outerrors() {
		return $this->outerrors;
	}
	
	function __construct($interface_id ='') {
		if (is_numeric($interface_id)) {
			$this->get_port_info($interface_id);
		}
	}

	private function get_port_info($interface_id) {
		if (! is_numeric($interface_id)) {
			return false;
		}
		$query = "SELECT interface_id, ifOperStatus, disc_interface_speed, interface_name, " .
			"interface_descr, interface_alias, disc_interface_speed, interface_device, " .
			"disc_interface_mtu, disc_interface_index, disc_interface_type,
			inbits, outbits, inerrors, outerrors, inunicastpackets,
			outunicastpackets,  innonunicastpackets, outnonunicastpackets FROM " .
			" interfaces where interface_id = '$interface_id' and active = '1' ";
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($result)){
			$this->interface_id = $obj->interface_id;
			$this->name = $obj->interface_name;
			$this->ifindex = $obj->disc_interface_index;
			$this->oper_status = $obj->ifOperStatus;
			$this->speed = $obj->disc_interface_speed;
			$this->description = $obj->interface_descr;
			$this->alias = $obj->interface_alias;
			$this->device_id = $obj->interface_device;
			$this->mtu = $obj->disc_interface_mtu;
			$this->type = $obj->disc_interface_type;
			$this->inbits = $obj->inbits;
			$this->outbits = $obj->outbits;
			$this->inerrors = $obj->inerrors;
			$this->outerrors = $obj->outerrors;
			$this->inunicastpackets = $obj->inunicastpackets;
			$this->outunicastpackets = $obj->outunicastpackets;
			$this->innonunicastpacket = $obj->innonunicastpackets;
			$this->outnonunicastpackets = $obj->outnonunicastpackets;
		}
	}
		
	function get_ipv6_addresses(){
		// Retrieve IP address  
		$interface_ipv6 = array();
		$query = "SELECT inet_address, inet_address_length, inet from interface_IPaddresses where device_id  = '$this->device_id' " .
			" and if_index  = '$this->ifindex' and inet = '6' and  inet_address NOT Like 'fe80:0000:%' and inet_address NOT Like 'fec0:0000:%'" ;
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($result)){
			$interface_ipv6["$obj->inet_address/$obj->inet_address_length"][interface_index] = $interface_index;
			$interface_ipv6["$obj->inet_address/$obj->inet_address_length"][address] = $obj->inet_address;
			$interface_ipv6["$obj->inet_address/$obj->inet_address_length"][address_length] = $obj->inet_address_length;
		}
		return $interface_ipv6;
	}


	function get_ipv4_addresses(){
		// Retrieve IP address  
		$interface_ipv4 = array();
		$query = "SELECT inet_address, inet_address_length, inet from interface_IPaddresses where device_id  = '$this->device_id' " .
			" and if_index  = '$this->ifindex' and inet = '4'" ;
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($result)){
			$interface_ipv4["$obj->inet_address/$obj->inet_address_length"][interface_index] = $interface_index;
			$interface_ipv4["$obj->inet_address/$obj->inet_address_length"][address] = $obj->inet_address;
			$interface_ipv4["$obj->inet_address/$obj->inet_address_length"][address_length] = $obj->inet_address_length;
		}
		return $interface_ipv4;
	}


	static function get_device_interfaces($device_id) {
		// returns array of interface id's (key) and interface name (value)
		$interface_list = array();

		if ($device_id == '') {
			$this->error = "Invalid device id";
			return false;
		} 

		$query = "SELECT interface_id, ifOperStatus, disc_interface_speed, interface_name, " .
			"interface_descr, interface_alias, disc_interface_speed, interface_device, " .
			"disc_interface_mtu, disc_interface_index, disc_interface_type,
			inbits, outbits, inerrors, outerrors, inunicastpackets,
			outunicastpackets,  innonunicastpackets, outnonunicastpackets FROM " .
			" interfaces where interface_device = '$device_id' and active = '1' " .
			"ORDER BY interface_name ";   
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
 		while ($obj = mysql_fetch_object($result)){
			$ifobj = new Port();
			$ifobj->interface_id = $obj->interface_id;
			$ifobj->name = $obj->interface_name;
			$ifobj->ifindex = $obj->disc_interface_index;
			$ifobj->oper_status = $obj->ifOperStatus;
			$ifobj->speed = $obj->disc_interface_speed;
			$ifobj->description = $obj->interface_descr;
			$ifobj->alias = $obj->interface_alias;
			$ifobj->device_id = $obj->interface_device;
			$ifobj->mtu = $obj->disc_interface_mtu;
			$ifobj->type = $obj->disc_interface_type;
			$ifobj->inbits = $obj->inbits;
			$ifobj->outbits = $obj->outbits;
			$ifobj->inerrors = $obj->inerrors;
			$ifobj->outerrors = $obj->outerrors;
			$ifobj->inunicastpackets = $obj->inunicastpackets;
			$ifobj->outunicastpackets = $obj->outunicastpackets;
			$ifobj->innonunicastpacket = $obj->innonunicastpackets;
			$ifobj->outnonunicastpackets = $obj->outnonunicastpackets;
			$interface_list[$obj->interface_id] = $ifobj;
		}
		return $interface_list;
	}

}

?>

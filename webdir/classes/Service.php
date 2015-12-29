<?php

// Class file for Services

// include the database configuration and
// open connection to database

class Service {
	protected $service_id;
	protected $name;
	protected $contact_id;
	protected $contact_name;
	protected $service_type;
	protected $service_layer;
	protected $service_type_name;
	protected $service_type_desc;
	protected $l2_service_id;
	protected $l3_service_id;
	protected $notes;
	protected $in_production;
	protected $out_production;
	protected $last_updated;
	protected $status;
	protected $portal_statistics = 1;

	protected $error = false;
	
	function __construct($service_id = '') {
		if (is_numeric($service_id)) {
			$this->get_service_info($service_id);
		}
	}

	function __toString() {
		$mystring = print_r($this, $return = true);
		return "<pre>$mystring</pre>";
    }

	protected function get_service_info($service_id){
		$query = "SELECT Services.service_id, Services.name, Services.cust_id
			as contact_id, Services.service_type,
			Services.l2_service_id, Services.l3_service_id, Services.notes,
			Services.portal_statistics, contact_groups.group_name as client_name,
			Service_types.name as service_type_name, Service_types.service_layer,
			DATE_FORMAT(Services.date_in_production,'%Y-%m-%d') as date_in_production,
			DATE_FORMAT(Services.date_out_production,'%Y-%m-%d') as date_out_production, 
			Services.last_updated, Services.status
			FROM Services, contact_groups, Service_types  WHERE Services.cust_id =
			contact_groups.group_id AND Service_types.service_type_id =
			Services.service_type AND 
			Services.service_id = '$service_id' " ;
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	die( mysql_error() ."   -- query: $query");
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this service";
        	return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$this->service_id =  $obj->service_id;
			$this->name = $obj->name;
			$this->contact_id = $obj->contact_id;
			$this->contact_name = $obj->client_name;
			$this->service_type = $obj->service_type;
			$this->service_layer = $obj->service_layer;
			$this->service_type_name = $obj->service_type_name;
			$this->l2_service_id = $obj->l2_service_id;
			$this->l3_service_id = $obj->l3_service_id;
			$this->notes = $obj->notes;
			$this->portal_statistics = $obj->portal_statistics;
			$this->in_production = $obj->date_in_production;
			$this->out_production = $obj->date_out_production;
			$this->last_updated = $obj->last_updated;
			$this->status = $obj->status;
		}
	}


	public function get_services($archived = 0) {
		if ($archived != 0) {
			$archived == 1;
		}
		$services = array();
		$query = "SELECT Services.service_id, Services.name, Services.cust_id,
			Services.service_type, contact_groups.group_name as client_name, Service_types.name as
			service_type_name 
			FROM Services, contact_groups, Service_types WHERE Services.archived = '$archived' AND
			Services.cust_id = contact_groups.group_id AND Service_types.service_type_id
			= Services.service_type order by service_id desc" ;
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			die('Error, query failed. ' .  mysql_error());
        	return false;
        }
		$service = array();
 		while ($obj = mysql_fetch_object($result)){
			$service['service_id'] = $obj->service_id;
			$service['name'] = $obj->name;
			$service['service_type_id'] = $obj->service_type;
			$service['service_type_name'] = $obj->service_type_name;
			$service['contact_name'] = $obj->client_name;
			$service['contact_id'] = $obj->cust_id;
			$services[$obj->service_id] = $service;
			//array_push ($services,$service);
		}
		return $services;
	}

	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_service_id() {
		return $this->service_id;
	}
	function get_name() {
		return $this->name;
	}
	function get_contact_id() {
		return $this->contact_id;
	}
	function get_contact_name() {
		return $this->contact_name;
	}
	function get_service_type() {
		return $this->service_type;
	}
	function get_service_type_name() {
		return $this->service_type_name;
	}
	function get_service_type_desc() {
		return $this->service_type_desc;
	}
	function get_service_layer() {
		return $this->service_layer;
	}
	function get_l2_service_id() {
		return $this->l2_service_id;
	}
	function get_l3_service_id() {
		return $this->l3_service_id;
	}
	function get_notes() {
		return $this->notes;
	}
	function get_in_production_date() {
		return $this->in_production;
	}
	function get_out_production_date() {
		return $this->out_production;
	}
	function get_last_updated() {
		return $this->last_updated;
	}
	function get_status() {
		return $this->status;
	}
	
	function get_portal_statistics() {
		return $this->portal_statistics;
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

	function set_contact_id($value) {
		if (is_numeric($value)) {
			// TODO check for valid contact id
			$this->contact_id = $value;
			return true;
		} else {
			$this->error = "Invalid contact ID";
			return false;
		}
	}
	function set_service_type($value) {
		if (is_numeric($value)) {
			// TODO check for valid service_type id
			$this->service_type = $value;
			return true;
		} else {
			$this->error = "Invalid service type ID";
			return false;
		}
	}

	function set_notes($value) {
		$this->notes = $value;
	}
	function set_status($value) {
		$this->status = $value;
	}

	function set_in_production_date($value) {
		$this->in_production = $value;
	}
	function set_out_production_date($value) {
		$this->out_production = $value;
	}

	function set_portal_statistics($value) {
		if ($value < 1) {
			$this->portal_statistics = 0;
		} else {
			$this->portal_statistics = 1;
		}
	}

	function update() {
		// Update the info in the database

		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->service_id))) {
			$this->error = "Invalid service id: $this->service_id";
			return false;
		}
		if (!(is_numeric($this->contact_id))) {
			$this->error = "Invalid contact id";
			return false;
		}
		if (!(is_numeric($this->service_type))) {
			$this->error = "Invalid service type id";
			return false;
		}
		if ($this->in_production == '') {
			$sql_in_production = "date_in_production = NULL";
		} else {
			$sql_in_production = "date_in_production = '$this->in_production'";
		}
		if ($this->out_production == '') {
			$sql_out_production = "date_out_production = NULL";
		} else {
			$sql_out_production = "date_out_production = '$this->out_production'";
		}
		if ($this->status == '') {
			$sql_status = "status = NULL";
		} else {
			$sql_status = "status = '$this->status'";
		}

		$query = "UPDATE Services SET name = '$this->name', cust_id =
			'$this->contact_id', service_type = '$this->service_type', notes =
			'$this->notes', portal_statistics  = '$this->portal_statistics',
			$sql_in_production, $sql_out_production,
			$sql_status
			WHERE service_id = '$this->service_id'";
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
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->contact_id))) {
			$this->error = "Invalid contact id";
			return false;
		}
		if (!(is_numeric($this->service_type))) {
			$this->error = "Invalid service type id";
			return false;
		}
		if ($this->service_id != '') {
			$this->error = "This is an insert, service_id should be empty";
			return false;
		} 
		if ($this->in_production == '') {
			$sql_in_production = "date_in_production = NULL";
		} else {
			$sql_in_production = "date_in_production = '$this->in_production'";
		}
		if ($this->out_production == '') {
			$sql_out_production = "date_out_production = NULL";
		} else {
			$sql_out_production = "date_out_production = '$this->out_production'";
		}
		if ($this->status == '') {
			$sql_status = "status = NULL";
		} else {
			$sql_status = "status = '$this->status'";
		}

		$query = "Insert INTO Services SET name = '$this->name', cust_id =
			'$this->contact_id', service_type = '$this->service_type', notes =
			'$this->notes', portal_statistics  = '$this->portal_statistics',
			$sql_in_production, $sql_out_production, $sql_status
			";
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
	if ($this->service_id == '') {
		$this->error = "Invalid service id";
		return false;
	} 
	$query = "update Services set archived = '1' where service_id =
		'$this->service_id'";
	$result =  mysql_query($query) ;
	if (!$result)  {
		$this->error = mysql_error();
		return false;
	}
	return true;
	}
	
	function get_locations() {
	
		$locations = array();
		if ($this->service_layer == 3) {
			$l3service = new Layer3_service($this->service_id);
			$device_id = $l3service->get_pe_id();
			$query = "Select Devices.location, pop_locations.location_name
                                FROM Devices, pop_locations
                                WHERE pop_locations.location_id = Devices.location
                                AND Devices.device_id = '$device_id'";
                        $result =  mysql_query($query) ;
                        if (!$result)  {
                                #die( mysql_error() ."   -- query: $query");
                                return false;
                        }
                        while ($obj = mysql_fetch_object($result)){
                                $locations[$obj->location] = $obj->location_name;
                        }
                }
                elseif ($this->service_layer == 2) {
                        $l2service = new Layer2_service($this->service_id);
                        $interfaces = $l2service->get_interfaces();
                        foreach ($interfaces as $ifid => $ifVal) {
                               // $service_port = new Layer2_service_port($ifid);
                                $device_id = $ifVal['device_id'];
                                $query = "Select Devices.location, pop_locations.location_name
                                FROM Devices, pop_locations
                                WHERE pop_locations.location_id = Devices.location
                                AND Devices.device_id = '$device_id'";
                                $result =  mysql_query($query) ;
								if (!$result)  {
                                        #die( mysql_error() ."   -- query: $query");
                                        #return false;
                                        continue;
                                }
                                while ($obj = mysql_fetch_object($result)){
                                        $locations[$obj->location] = $obj->location_name;
                                }
								
                        }
                }

                return $locations;
	} 

	public function get_inprod_services_at_date($date,$service_type = '') {
		// Returns and array with service id's that were in service at a certain time
        	$services = array();
        	if (is_numeric($service_type)) {
			$sql_service_type = "Services.service_type = '$service_type'";
		} else {
			$sql_service_type = "1";
		}
		$sql_to_date = "((Services.date_out_production > '$date') OR
			(Services.date_out_production IS NULL) OR
			(Services.date_out_production = '0000-00-00 00:00:00'))";
		$query = "select Services.service_id, Services.name as service_name, 
			Services.service_type, Services.cust_id, Service_types.name,
			Service_types.description
			FROM Services, Service_types
			WHERE 
			(Services.date_in_production <= '$date' AND $sql_to_date) 
			AND $sql_service_type
			AND Service_types.service_type_id = Services.service_type
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			die( mysql_error() ."   -- query: $query<br>");
			return false;
		}
		while ($obj = mysql_fetch_object($result)) {
			array_push($services,$obj->service_id);
		}
		return $services;
	}

	public function get_outprod_services_diff_date ($start_date, $end_date, $service_type = '') {
	// Will return added + lost services per interval for certail service type
		$services = array();
		if (is_numeric($service_type)) {
			$sql_service_type = "Services.service_type = '$service_type'";
		} else {
			$sql_service_type = "1";
		}
		$query = "select Services.service_id, Services.name as service_name, 
			Services.service_type, Services.cust_id, Service_types.name,
			Service_types.description
			FROM Services, Service_types
			WHERE 
			(Services.date_out_production >= '$start_date' AND Services.date_out_production < '$end_date') 
			AND $sql_service_type
			AND Service_types.service_type_id = Services.service_type
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			die( mysql_error() ."   -- query: $query<br>");
			return false;
		}
		while ($obj = mysql_fetch_object($result)) {
			array_push($services,$obj->service_id);
		}
		return $services;
	}

	public function get_inprod_services_diff_date ($start_date, $end_date, $service_type = '') {
	// Will return added + lost services per interval for certail service type
		$services=array();
		if (is_numeric($service_type)) {
			$sql_service_type = "Services.service_type = '$service_type'";
		} else {
			$sql_service_type = "1";
		}
		$query = "select Services.service_id, Services.name as service_name, 
			Services.service_type, Services.cust_id, Service_types.name,
			Service_types.description
			FROM Services, Service_types
			WHERE 
			(date_in_production >= '$start_date' AND date_in_production < '$end_date') 
			AND $sql_service_type
			AND Service_types.service_type_id = Services.service_type
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			die( mysql_error() ."   -- query: $query<br>");
			return false;
		}
		while ($obj = mysql_fetch_object($result)) {
			array_push($services,$obj->service_id);
		}
		return $services;
	}


}

class ServiceType {
	protected $type_id;
	protected $name;
	protected $desc;
	protected $service_layer;

	protected $error = false;
	
	function __construct($type_id = '') {
		if (is_numeric($type_id)) {
			$this->get_service_type_info($type_id);
		}
	}


	protected function get_service_type_info($service_type_id){
		$query = "SELECT service_type_id, name, description, service_layer
			FROM Service_types WHERE 
			service_type_id = '$service_type_id' " ;
		// execute the query 
		$result =  mysql_query($query) ;
        	if (!$result)  {
        		#die( mysql_error() ."   -- query: $query");
			$this->error = mysql_error();
       	 		return false;
		}
		if (mysql_numrows($result) < 1 ) {
        		$this->error = "No data found";
        		return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$this->type_id =  $obj->service_type_id;
			$this->name = $obj->name;
			$this->desc = $obj->description;
			$this->service_layer = $obj->service_layer;
		}
	}

	public function get_service_types($archived = 0) {
		if ($archived != 0) {
			$archived == 1;
		}
		$service_types = array();
		$query = "SELECT service_type_id, name, description, service_layer
			FROM Service_types where archived = $archived";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			die('Error, query failed. ' .  mysql_error());
        		return false;
        	}
		$service = array();
 		while ($obj = mysql_fetch_object($result)){
			$service_types[$obj->service_type_id] = $obj->name;
		}
		return $service_types;
	}

	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_service_type_id() {
		return $this->type_id;
	}

	function get_name() {
		return $this->name;
	}

	function get_description() {
		return $this->desc;
	}

	function get_service_layer() {
		return $this->service_layer;
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
	function set_service_layer($value) {
		$this->service_layer = $value;
	}

	function update() {
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		$sql_name = mysql_real_escape_string($this->name);
		$sql_desc = mysql_real_escape_string($this->desc);
		$query = "UPDATE Service_types  SET 
			name = '$sql_name', 
			description = '$sql_desc', 
			service_layer = '$this->service_layer'
			WHERE service_type_id = '$this->type_id'";
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
		if ($this->type_id != '') {
			$this->error = "This is an insert, type_id should be empty";
			return false;
		} 
 		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		$sql_name = mysql_real_escape_string($this->name);
		$sql_desc = mysql_real_escape_string($this->desc);

		$query = "Insert into Service_types SET 
			name = '$sql_name', 
			description = '$sql_desc',
			service_layer = '$this->service_layer'";
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
		if (!is_numeric($this->type_id)) {
			$this->error = "Invalid service type_id ";
			return false;
		} 

		$query = "update Service_types set archived = '1' where
                service_type_id = '$this->type_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

}


class Layer2_service extends Service {
		
	private $l2_interfaces = array();
	protected $vlan_id;

	function __construct($service_id = '') {
		if (is_numeric($service_id)) {
			parent::get_service_info($service_id);
			$this->get_l2_service_info($service_id);
		}
	}

	private function get_l2_service_info($service_id){
		$query = "SELECT L2_service_details.vlan
		FROM L2_service_details
		WHERE l2_service_id = '$this->l2_service_id'  ";
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	die( mysql_error() ."   -- query: $query");
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this service";
        	return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$this->vlan_id = $obj->vlan;
		}

		// 2ns query to get all interfaces for this L2 service
		$query = "SELECT  Services_Interfaces.id,Services_Interfaces.interface_name, 
			Services_Interfaces.service_id, Services_Interfaces.tagged, 
			Services_Interfaces.vlan, Services_Interfaces.device, Services_Interfaces.mtu,
			Devices.name as device_name
		FROM Services_Interfaces, Devices 
		WHERE Services_Interfaces.service_id = '$service_id' 
		AND Services_Interfaces.device = Devices.device_id order by
		interface_name";
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	die( mysql_error() ."   -- query: $query");
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this service";
        	return false;
		}
		
 		while ($obj = mysql_fetch_object($result)){
			$interface_info = array();
			$interface_info[service_interface_id] = $obj->id;
			$interface_info[device_id] = $obj->device;
			$interface_info[device_name] = $obj->device_name;
			$interface_info[port_name] = $obj->interface_name;
			$interface_info[tagged] = $obj->tagged;
			$interface_info[vlan] = $obj->vlan;
			$interface_info[mtu] = $obj->mtu;
			//$interface_info[service_id] = $obj->service_id;
			$this->l2_interfaces[$obj->id] = $interface_info;
			//array_push ($this->l2_interfaces,$interface_info);
		}
	}

	function get_vlan_id() {
		return $this->vlan_id;
	}
	function get_interfaces() {
		return $this->l2_interfaces;
	}

	function set_vlan_id($value) {
		if (is_numeric($value)) {
			$this->vlan_id = $value;
			return true;
		} else {
			$this->error = "Invalid VLAN ID";
			return false;
		}
	}


	function insert() {
	// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->contact_id))) {
			$this->error = "Invalid contact id";
			return false;
		}
		if (!(is_numeric($this->service_type))) {
			$this->error = "Invalid service type id";
			return false;
		}
		if (!(is_numeric($this->vlan_id))) {
			$this->error = "Invalid VLAN ID";
			return false;
		}
		if ($this->service_id != '') {
			$this->error = "This is an insert, service_id should be empty";
			return false;
		} 

		// We will be using a tramsaction for this
		// firts , start transaction
		mysql_query("BEGIN") or die("Error, start of transaction failed " . mysql_error());
		$commitok = 0;
		$query = "INSERT INTO L2_service_details 
			SET vlan = '$this->vlan_id'";

		$result = mysql_query($query) ; #  or die("Error, query failed. <br>$query<br> " . mysql_error());
		if (!$result) {
			$commitok = $commitok +1;
			$this->error .=  "Query failed: $query <br>Throwed: "  . mysql_error() ;
		}
		$l2_service_detail_id = mysql_insert_id();

		if ($this->in_production == '') {
			$sql_in_production = "date_in_production = NULL";
		} else {
			$sql_in_production = "date_in_production = '$this->in_production'";
		}
		if ($this->out_production == '') {
			$sql_out_production = "date_out_production = NULL";
		} else {
			$sql_out_production = "date_out_production = '$this->out_production'";
		}
		if ($this->status == '') {
			$sql_status = "status = NULL";
		} else {
			$sql_status = "status = '$this->status'";
		}
        
		// Query to add generic service to database
		$query = "Insert INTO Services SET name = '$this->name', cust_id =
			'$this->contact_id', service_type = '$this->service_type', notes =
			'$this->notes', l2_service_id = '$l2_service_detail_id',
			$sql_in_production, $sql_out_production, $sql_status
			";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$commitok = $commitok +1;
			$this->error .=  "Query: $query <br>Throwed: " . mysql_error();
		}
		$service_id = mysql_insert_id();
	
		// Now if all was successfull commit, otherwise rollback
		if ( $commitok > 0 ) {
			// not good do rollback
			mysql_query("ROLLBACK") or die("Error, Rollback failed " . mysql_error());
			$this->error .= "<br>Something went wrong, Database rollback was performced, service not commited to database";
			return false;
		} else {
			// all successfull, do commit
			$result = mysql_query("COMMIT") or die("Error, Commit failed " . mysql_error());
			if ($result) {
				return $service_id;
			} else {
				$this->error .= "<br>Commit failed";
				return false;
			}
		}
	}

	function update() {
	// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->contact_id))) {
			$this->error = "Invalid contact id";
			return false;
		}
		if (!(is_numeric($this->service_type))) {
			$this->error = "Invalid service type id";
			return false;
		}
		if ($this->service_id == '') {
			$this->error = "service_id can not be empty";
			return false;
		} 
		if (!is_numeric($this->l2_service_id)) {
			$this->error = "Invalid L2 service_id ";
			return false;
		}


		// We will be using a tramsaction for this
		// firts , start transaction
		mysql_query("BEGIN") or die("Error, start of transaction failed " . mysql_error());
		$commitok = 0;
		$query = "UPDATE L2_service_details SET 
			vlan = '$this->vlan_id'
			WHERE l2_service_id = '$this->l2_service_id'" ;

		$result = mysql_query($query) ; 
		if (!$result) {
			$commitok = $commitok +1;
			$this->error .=  "Query failed: $query <br>Throwed: "  . mysql_error() ;
		}
        
		if ($this->in_production == '') {
			$sql_in_production = "date_in_production = NULL";
		} else {
			$sql_in_production = "date_in_production = '$this->in_production'";
		}
		if ($this->out_production == '') {
			$sql_out_production = "date_out_production = NULL";
		} else {
			$sql_out_production = "date_out_production = '$this->out_production'";
		}
		if ($this->status == '') {
			$sql_status = "status = NULL";
		} else {
			$sql_status = "status = '$this->status'";
		}
		// Query to update generic service to database
		$query = "UPDATE Services SET name = '$this->name', cust_id =
			'$this->contact_id', service_type = '$this->service_type', notes =
			'$this->notes', portal_statistics  = '$this->portal_statistics', 
			$sql_in_production, $sql_out_production, $sql_status
			 WHERE service_id = '$this->service_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$commitok = $commitok +1;
			$this->error .=  "Query: $query <br>Throwed: " . mysql_error();
		}

        
		// Now if all was successfull commit, otherwise rollback
		if ( $commitok > 0 ) {
			// not good do rollback
			mysql_query("ROLLBACK") or die("Error, Rollback failed " . mysql_error());
			$this->error .= "Something went wrong, Database rollback was performced, service not commited to database";
			return false;
		} else {
			// all successfull, do commit
			$result = mysql_query("COMMIT") or die("Error, Commit failed " . mysql_error());
			if ($result) {
				return true;
			} else {
				$this->error .= "Commit failed";
				return false;
			}
		}
	}

	function delete() {
		if ($this->service_id == '') {
			$this->error = "Invalid service id";
			return false;
		} 
		$query = "update Services set archived = '1' where service_id =
			'$this->service_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

}

class Service_port {

	private $service_id;
	private $port_id;
	private $port_name = '';
	private $tagged;
	private $vlan_id;
	private $device_id;
	private $device_name;
	private $speed;
	private $mtu;
	private $ip_address;
	private $ip_mask;
	private $inet;
	private $duplex;
	private $error = false;
		
	function __construct($port_id = '') {
		if (is_numeric($port_id)) {
			$this->get_port_info($port_id);
		}
	}

	protected function get_port_info($port_id){
		$query = "SELECT  Services_Interfaces.id,Services_Interfaces.interface_name, 
			Services_Interfaces.service_id, Services_Interfaces.tagged, 
			Services_Interfaces.ip_address, Services_Interfaces.subnet_mask, 
			Services_Interfaces.vlan, Services_Interfaces.device, Services_Interfaces.mtu,
			Devices.name as device_name
		FROM Services_Interfaces, Devices 
		WHERE Services_Interfaces.id = '$port_id' 
		AND Services_Interfaces.device = Devices.device_id ";
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$error =  mysql_error() ."   -- query: $query";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "Port not found";
        	return false;
		}
		
 		while ($obj = mysql_fetch_object($result)){
			$this->port_id = $obj->id;
			$this->device_id = $obj->device;
			$this->device_name = $obj->device_name;
			$this->port_name = $obj->interface_name;
			$this->tagged = $obj->tagged;
			$this->vlan_id = $obj->vlan;
			$this->mtu = $obj->mtu;
			$this->service_id = $obj->service_id;
			$this->ip_address = $obj->ip_address;
			$this->ip_mask = $obj->subnet_mask;
		}
	}

	public function get_ports($service_id) {
		$interfaces = array();
		if (is_numeric($service_id)) {
			$query = "SELECT  id, interface_name from Services_Interfaces where
			service_id = '$service_id' order by interface_name";
			$result = mysql_query($query); 
			if (!$result) {
				return false;
			} else {
				while ($obj = mysql_fetch_object($result)){
					$interfaces[$obj->id] = $obj->interface_name;
				}
				return $interfaces;
			}
		} else {
			return false;
		}
	}

	function get_error() {
		return $this->error;
	}
	function get_device_id() {
		return $this->device_id;
	}
	function get_device_name() {
		return $this->device_nameid;
	}
	function get_port_name() {
		return $this->port_name;
	}
	function get_tagged() {
		if ($this->tagged > 0) {
			return true;
		} else {
			return false;
		}
	}

	function get_vlan_id() {
		return $this->vlan_id;
	}
	function get_mtu() {
		return $this->mtu;
	}
	function get_service_id() {
		return $this->service_id;
	}
	function get_ip_address() {
		$complete_ip = "";
		if ($this->ip_address != '') {
			$complete_ip = $this->ip_address ;
			if ($this->ip_mask != '') {
				$complete_ip = $this->ip_address  ."/". $this->ip_mask;
			}
		}
		return $complete_ip;
	}

	function set_ip_address($value) {
		list($ip_address, $ip_mask) = split('/', $value);
		$inet = $this->verify_prefix($ip_address);
		if (($inet != 4) && ($inet != 6)) {
			return false;
		}
		$this->ip_address = $ip_address;
		$this->ip_mask = $ip_mask;
		return true;
	}

	function set_device_id($value) {
		if (is_numeric($value)) {
			$this->device_id = $value;
			return true;
		} else {
			$this->error = "Invalid Device ID";
			return false;
		}
	}
	function set_port_name($value) {
		if ($value != '') {
			$this->port_name = $value;
			return true;
		} else {
			$this->error = "Invalid port name ";
			return false;
		}
	}

	function set_tagged($value) {
		if ($value) {
			$this->tagged = 1;
		} else {
			$this->tagged = 0;
		}
	}


	function set_vlan_id($value) {
		if (is_numeric($value)) {
			$this->vlan_id = $value;
			return true;
		}
	}

	function set_mtu($value) {
		if (is_numeric($value)) {
			$this->mtu = $value;
			return true;
		} else {
			$this->error = "Invalid mtu value";
			return false;
		}
	}

	function set_service_id($value) {
		if (is_numeric($value)) {
			$this->service_id = $value;
			return true;
		} else {
			$this->error = "Invalid service_id";
			return false;
		}
	}

	function verify_prefix($prefix) {
		// Ipv6 regex found here
		// http://www.pastie.org/1786639
                
		$IPv6Pattern = "/^\s*((([0-9A-Fa-f]{1,4}:){7}([0-9A-Fa-f]{1,4}|:))|(([0-9A-Fa-f]{1,4}:){6}(:[0-9A-Fa-f]{1,4}|((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){5}(((:[0-9A-Fa-f]{1,4}){1,2})|:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3})|:))|(([0-9A-Fa-f]{1,4}:){4}(((:[0-9A-Fa-f]{1,4}){1,3})|((:[0-9A-Fa-f]{1,4})?:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){3}(((:[0-9A-Fa-f]{1,4}){1,4})|((:[0-9A-Fa-f]{1,4}){0,2}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){2}(((:[0-9A-Fa-f]{1,4}){1,5})|((:[0-9A-Fa-f]{1,4}){0,3}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(([0-9A-Fa-f]{1,4}:){1}(((:[0-9A-Fa-f]{1,4}){1,6})|((:[0-9A-Fa-f]{1,4}){0,4}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:))|(:(((:[0-9A-Fa-f]{1,4}){1,7})|((:[0-9A-Fa-f]{1,4}){0,5}:((25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d\d|[1-9]?\d)){3}))|:)))(%.+)?\s*$/";
		
		$ipPattern = '/^\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.' .
		'(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.' .
		'(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.' .
		'(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b$/';

		if (preg_match("$ipPattern", $prefix, $match)) {
			return 4;
		} elseif (preg_match("$IPv6Pattern", $prefix, $match)) {
			return 6;
		} else {
			$this->error = "Invalid Prefix $prefix";
			return false;
		}
	}


	function update() {
	// Test mandatory fields
		if ($this->port_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->device_id))) {
			$this->error = "Invalid device id";
			return false;
		}
		if (!(is_numeric($this->service_id))) {
			$this->error = "Invalid service id";
			return false;
		}
		if ($this->service_id == '') {
			$this->error = "service_id can not be empty";
			return false;
		} 
		if (!(is_numeric($this->port_id))) {
			$this->error = "Invalid port id";
			return false;
		}
        

		$query = "UPDATE Services_Interfaces SET 
			interface_name = '$this->port_name',
			device = '$this->device_id',
			tagged = '$this->tagged',
			vlan = '$this->vlan_id',
			mtu = '$this->mtu',
			ip_address = '$this->ip_address',
			subnet_mask = '$this->ip_mask'
			WHERE
			service_id = '$this->service_id' 
			AND id = '$this->port_id'";

		$result = mysql_query($query); 
		if (!$result) {
			$this->error =  "Query: $query <br>Throwed: " . mysql_error();
			return false;
		}
		return true;	
	}


	function insert() {
	// Test mandatory fields
		if ($this->port_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->device_id))) {
			$this->error = "Invalid device id";
			return false;
		}
		if (!(is_numeric($this->service_id))) {
			$this->error = "Invalid service id";
			return false;
		}
		if ($this->service_id == '') {
			$this->error = "service_id can not be empty";
			return false;
		} 
		if (is_numeric($this->port_id)) {
			$this->error = "Already a port_id defined, You can only insert a new port, not an existing port";
			return false;
		}

		$query = "INSERT INTO Services_Interfaces SET 
			interface_name = '$this->port_name',
			device = '$this->device_id',
			tagged = '$this->tagged',
			vlan = '$this->vlan_id',
			mtu = '$this->mtu',
			ip_address = '$this->ip_address',
			subnet_mask = '$this->ip_mask',
			service_id = '$this->service_id'";

		$result = mysql_query($query); 
		if (!$result) {
			$this->error =  "Query: $query <br>Throwed: " . mysql_error();
			return false;
		}
		$this->port_id = mysql_insert_id();
		return $this->port_id;
	}

	function delete() {
		if (!is_numeric($this->port_id)) {
			$this->error = "Invalid port ID";
			return false;
		}
		$query = "delete from Services_Interfaces where id = '$this->port_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		unset($this->port_name);
		unset($this->device_id);
		unset($this->device_name);
		unset($this->vlan_id);
		unset($this->service_id);
		unset($this->ip_address);
		unset($this->subnet_mask);
		return true;
	}
} 

class Layer2_service_port {

	private $service_id;
	private $port_id;
	private $port_name = '';
	private $tagged;
	private $vlan_id;
	private $device_id;
	private $device_name;
	private $speed;
	private $mtu;
	private $ip_address;
	private $ip_mask;
	private $inet;
	private $duplex;
	private $error = false;
		
	function __construct($l2_port_id = '') {
		if (is_numeric($l2_port_id)) {
			$this->get_l2_port_info($l2_port_id);
		}
	}

	protected function get_l2_port_info($l2_port_id){
		$query = "SELECT  Services_Interfaces.id,Services_Interfaces.interface_name, 
			Services_Interfaces.service_id, Services_Interfaces.tagged, 
			Services_Interfaces.ip_address, Services_Interfaces.subnet_mask, 
			Services_Interfaces.vlan, Services_Interfaces.device, Services_Interfaces.mtu,
			Devices.name as device_name
		FROM Services_Interfaces, Devices 
		WHERE id = '$l2_port_id' 
		AND Services_Interfaces.device = Devices.device_id ";
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	$error =  mysql_error() ."   -- query: $query";
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "Port not found";
        	return false;
		}
		
 		while ($obj = mysql_fetch_object($result)){
			$this->port_id = $obj->id;
			$this->device_id = $obj->device;
			$this->device_name = $obj->device_name;
			$this->port_name = $obj->interface_name;
			$this->tagged = $obj->tagged;
			$this->vlan_id = $obj->vlan;
			$this->mtu = $obj->mtu;
			$this->service_id = $obj->service_id;
			$this->ip_address = $obj->ip_address;
			$this->ip_mask = $obj->subnet_mask;
		}
	}

	public function get_ports($l2_service_id) {
		$l2_interfaces = array();
		if (is_numeric($l2_service_id)) {
			$query = "SELECT  id, interface_name from Services_Interfaces where
			service_id = '$l2_service_id' order by interface_name";
			$result = mysql_query($query); 
			if (!$result) {
				return false;
			} else {
				while ($obj = mysql_fetch_object($result)){
					$l2_interfaces[$obj->id] = $obj->interface_name;
				}
				return $l2_interfaces;
			}
		} else {
			return false;
		}
	}

	function get_error() {
		return $this->error;
	}
	function get_device_id() {
		return $this->device_id;
	}
	function get_device_name() {
		return $this->device_nameid;
	}
	function get_port_name() {
		return $this->port_name;
	}
	function get_tagged() {
		if ($this->tagged > 0) {
			return true;
		} else {
			return false;
		}
	}

	function get_vlan_id() {
		return $this->vlan_id;
	}
	function get_mtu() {
		return $this->mtu;
	}
	function get_service_id() {
		return $this->service_id;
	}

	function set_device_id($value) {
		if (is_numeric($value)) {
			$this->device_id = $value;
			return true;
		} else {
			$this->error = "Invalid Device ID";
			return false;
		}
	}
	function set_port_name($value) {
		if ($value != '') {
			$this->port_name = $value;
			return true;
		} else {
			$this->error = "Invalid port name ";
			return false;
		}
	}

	function set_tagged($value) {
		if ($value) {
			$this->tagged = 1;
		} else {
			$this->tagged = 0;
		}
	}


	function set_vlan_id($value) {
		if (is_numeric($value)) {
			$this->vlan_id = $value;
			return true;
		} else {
			$this->error = "Invalid VLAN ID";
			return false;
		}
	}

	function set_mtu($value) {
		if (is_numeric($value)) {
			$this->mtu = $value;
			return true;
		} else {
			$this->error = "Invalid mtu value";
			return false;
		}
	}

	function set_service_id($value) {
		if (is_numeric($value)) {
			$this->service_id = $value;
			return true;
		} else {
			$this->error = "Invalid service_id";
			return false;
		}
	}

	function update() {
	// Test mandatory fields
		if ($this->port_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->device_id))) {
			$this->error = "Invalid device id";
			return false;
		}
		if (!(is_numeric($this->service_id))) {
			$this->error = "Invalid service id";
			return false;
		}
		if ($this->service_id == '') {
			$this->error = "service_id can not be empty";
			return false;
		} 
		if (!(is_numeric($this->port_id))) {
			$this->error = "Invalid port id";
			return false;
		}
        

		$query = "UPDATE Services_Interfaces SET 
			interface_name = '$this->port_name',
			device = '$this->device_id',
			tagged = '$this->tagged',
			vlan = '$this->vlan_id',
			mtu = '$this->mtu',
			service_id = '$this->service_id' 
			WHERE id = '$this->port_id'";

		$result = mysql_query($query); 
		if (!$result) {
			$this->error =  "Query: $query <br>Throwed: " . mysql_error();
			return false;
		}
		return true;	
	}


	function insert() {
	// Test mandatory fields
		if ($this->port_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->device_id))) {
			$this->error = "Invalid device id";
			return false;
		}
		if (!(is_numeric($this->service_id))) {
			$this->error = "Invalid service id";
			return false;
		}
		if ($this->service_id == '') {
			$this->error = "service_id can not be empty";
			return false;
		} 
		if (is_numeric($this->port_id)) {
			$this->error = "Already a port_id defined, You can only insert a new port, not an existing port";
			return false;
		}

		$query = "INSERT INTO Services_Interfaces SET 
			interface_name = '$this->port_name',
			device = '$this->device_id',
			tagged = '$this->tagged',
			vlan = '$this->vlan_id',
			mtu = '$this->mtu',
			service_id = '$this->service_id'";

		$result = mysql_query($query); 
		if (!$result) {
			$this->error =  "Query: $query <br>Throwed: " . mysql_error();
			return false;
		}
		$this->port_id = mysql_insert_id();
		return $this->port_id;
	}

	function delete() {
		if (!is_numeric($this->port_id)) {
			$this->error = "Invalid port ID";
			return false;
		}
		$query = "delete from Services_Interfaces where id = '$this->port_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		unset($this->port_name);
		unset($this->device_id);
		unset($this->device_name);
		unset($this->vlan_id);
		unset($this->service_id);
		return true;
	}
}


class Layer3_service extends Service {
	private $routing_type;
	private $logical_router;
	private $ipv4_unicast = false;
	private $ipv6_unicast = false;
	private $ipv4_multicast = false;
	private $ipv6_multicast = false;
	private $pe4_address;
	private $pe4_address_length;
	private $pe6_address;
	private $pe6_address_length;
	private $ce4_address;
	private $ce4_address_length;
	private $ce6_address;
	private $ce6_address_length;
	private $mtu = 1500;
	private $bgp_as;
	private $bgp_pass;
	private $traffic_policing = 0;
	private $pe_id;
	private $pe_name;
	private $ipv4_prefixes = array();
	private $ipv6_prefixes = array();
	private $port_name;
	private $tagged = 0;
	private $vlan_id;

	function __construct($service_id = '') {
		if (is_numeric($service_id)) {
			parent::get_service_info($service_id);
			$this->get_l3_service_info($service_id);
		}
	}
 
	private function get_l3_service_info(){
		// TODO
		// interface info is currentlty stored in 2 tables.
		// Need to fix that
		// currectlty Service_interfaces is used
		//
		$query = "SELECT L3_service_details.routing_type, 
		L3_service_details.logical_router, L3_service_details.IPv4_unicast, 
		L3_service_details.IPv4_multicast, L3_service_details.IPv6_unicast, 
		 L3_service_details.IPv6_multicast, L3_service_details.IPv4_prefixes, 
		L3_service_details.IPv6_prefixes, L3_service_details.BCNETrouterAddress4, 
		L3_service_details.CustrouterAddress4, L3_service_details.BCNETrouterAddress6, 
		L3_service_details.CustrouterAddress6, L3_service_details.mtu, 
		L3_service_details.bgp_as, L3_service_details.bgp_pass, L3_service_details.traffic_policing, 
		L3_service_details.router, Devices.name as router_name 
		FROM L3_service_details, Devices
		WHERE l3_service_id = '$this->l3_service_id' AND Devices.device_id = L3_service_details.router" ;
		// execute the query 
		$result =  mysql_query($query) ;
        if (!$result)  {
        	die( mysql_error() ."   -- query: $query");
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this service";
        	return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			// PE = provider equipment (our router)
			// CE = customer equipment (client router)
			$this->routing_type =  $obj->routing_type;
			$this->logical_router = $obj->logical_router;
			$this->ipv4_unicast = $obj->IPv4_unicast;
			$this->ipv4_multicast = $obj->IPv4_multicast;
			$this->ipv6_unicast = $obj->IPv6_unicast;
			$this->ipv6_multicast = $obj->IPv6_multicast;
			$this->pe4_address = $obj->BCNETrouterAddress4;
			$this->ce4_address = $obj->CustrouterAddress4;
			$this->pe6_address = $obj->BCNETrouterAddress6;
			$this->ce6_address = $obj->CustrouterAddress6;
			$this->bgp_as = $obj->bgp_as;
			$this->bgp_pass = $obj->bgp_pass;
			$this->traffic_policing = $obj->traffic_policing;
			$this->pe_id = $obj->router;
			$this->pe_name = $obj->router_name;

			if (preg_match("(((\d+)\.(\d+)\.(\d+)\.(\d+))(\/(\d+))?)", $obj->BCNETrouterAddress4, $match)) {
				$this->pe4_address= $match[1];
				$this->pe4_address_length = $match[7];
        	}
			if (preg_match("(((\d+)\.(\d+)\.(\d+)\.(\d+))(\/(\d+))?)", $obj->CustrouterAddress4, $match)) {
				$this->ce4_address = $match[1];
                $this->ce4_address_length = $match[7];
        	}
			if (preg_match("((.+)(\/(\d+)))", $obj->BCNETrouterAddress6, $match)) {
				$this->pe6_address = $match[1];
				$this->pe6_address_length = $match[3];
			}

			if (preg_match("((.+)(\/(\d+)))", $obj->CustrouterAddress6, $match)) {
				$this->ce6_address = $match[1];
				$this->ce6_address_length = $match[3];
			}


			$v4prefixes = preg_split("/\n|\s+/", $obj->IPv4_prefixes);
			$ipv4_prefixes = array();
			foreach ($v4prefixes as $prefix) {
				if (preg_match("(((\d+)\.(\d+)\.(\d+)\.(\d+))(\/(\d+)))",$prefix, $match)) {
					$ipv4_prefixes[$match[0]] = array("prefix"=> $match[1], "length" =>$match[7]);
				} else {
					#$this->error.="\n Warning, ignored prefix $prefix";
				}
			}
			$ipv6_prefixes = array();
			$v6prefixes = preg_split("/\n|\s+/", $obj->IPv6_prefixes);
			$IPv6Pattern = "((([0-9a-fA-F]{1,4})((:[0-9a-fA-F]{1,4}){0,7}::))(\/(\d+))?)";
			foreach ($v6prefixes as $prefix6) {
				if (preg_match($IPv6Pattern,$prefix6, $match6)) {
					$ipv6_prefixes[$match6[0]] = array("prefix"=> $match6[1], "length" =>$match6[6]);
				}
			}
			$this->ipv4_prefixes = $ipv4_prefixes;
			$this->ipv6_prefixes = $ipv6_prefixes;
		}

		// Now get Interfaces for this service
		// L3 so only 1 interface
		$query = "SELECT Services_Interfaces.interface_name,
			Services_Interfaces.device, Services_Interfaces.tagged,
			Services_Interfaces.vlan, Services_Interfaces.mtu, 
			Devices.name as router_name
			FROM Services_Interfaces, Devices 
			WHERE service_id = '$this->service_id' 
			AND Devices.device_id = Services_Interfaces.device";

		$result = mysql_query($query); 
        if (!$result)  {
        	die( mysql_error() ."   -- query: $query");
        	return false;
        }
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No interface found for this service";
		}
 		while ($obj = mysql_fetch_object($result)){
			// PE = provider equipment (our router)
			$this->port_name =  $obj->interface_name;
			$this->pe_id = $obj->device;
			$this->pe_name = $obj->router_name;
			$this->tagged = $obj->tagged;
			$this->vlan_id = $obj->vlan;
			$this->mtu = $obj->mtu;
		}
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_routing_type() {
		return $this->routing_type;
	}
	function get_logical_router() {
		if ($this->logical_router != '') {
			return $this->logical_router;
		} else {
			return false;
		}
	}
	function get_ipv4_unicast() {
		if ($this->ipv4_unicast == 1) {
			return true;
		} else {
			return false;
		}
	}	
	function get_ipv4_multicast() {
		if ($this->ipv4_multicast == 1) {
			return true;
		} else {
			return false;
		}
	}	
	function get_ipv6_unicast() {
		if ($this->ipv6_unicast == 1) {
			return true;
		} else {
			return false;
		}
	}	
	function get_ipv6_multicast() {
		if ($this->ipv6_multicast == 1) {
			return true;
		} else {
			return false;
		}
	}	
	function get_pe_address($inet=4) {
		if ($inet == 4) {
			return "$this->pe4_address/$this->pe4_address_length";
		} elseif ($inet == 6) {
			return "$this->pe6_address/$this->pe6_address_length";
		} else {
			$this->error = "Invalid address family, should be 4 or 6";
			return false;
		}
	}
	function get_ce_address($inet=4) {
		if ($inet == 4) {
			return "$this->ce4_address/$this->ce4_address_length";
		} elseif ($inet == 6) {
			return "$this->ce6_address/$this->ce6_address_length";
		} else {
			$this->error = "Invalid address family, should be 4 or 6";
			return false;
		}
	}
	function get_prefixes($inet=4) {
		if ($inet == 4) {
			return $this->ipv4_prefixes;
		} elseif ($inet == 6) {
			return $this->ipv6_prefixes;
		} else {
			$this->error = "Invalid address family, should be 4 or 6";
		}
	}
	function get_mtu() {
		return $this->mtu;
	}
	function get_bgp_as() {
		return $this->bgp_as;
	}
	function get_bgp_pass() {
		return $this->bgp_pass;
	}
	function get_traffic_policing() {
		if ((is_numeric($this->traffic_policing)) && ($this->traffic_policing > 0)) {
			return $this->traffic_policing;
		} else {
			return false;
		}
	}
	function get_pe_id() {
		return $this->pe_id;
	}
	function get_pe_name() {
		return $this->pe_name;
	}
	function get_port_name() {
		return $this->port_name;
	}
	function get_tagged() {
		if ($this->tagged > 0) {
			return true;
		} else {
			return false;
		}
	}
	function get_vlan_id() {
		return $this->vlan_id;
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

	function set_routing_type($value) {
		if ($value == '') {
			$this->error = "routing type can not be empty";
			return false;
		} else {
			$this->routing_type = $value;
			return true;
		}
	}
	function set_logical_router($value) {
		$this->logical_router = $value;
	}
	function set_ipv4_unicast($value) {
		if ($value) {
			$this->ipv4_unicast = 1;
		} else {
			$this->ipv4_unicast = 0; 
		}
	}
	function set_ipv4_multicast($value) {
		if ($value) {
			$this->ipv4_multicast = 1;
		} else {
			$this->ipv4_multicast = 0; 
		}
	}
	function set_ipv6_unicast($value) {
		if ($value) {
			$this->ipv6_unicast = 1;
		} else {
			$this->ipv6_unicast = 0; 
		}
	}
	function set_ipv6_multicast($value) {
		if ($value) {
			$this->ipv6_multicast = 1;
		} else {
			$this->ipv6_multicast = 0; 
		}
	}
	function set_pe4_address($value) {
		$this->pe4_address = $value;
	}
	function set_pe4_address_length($value) {
		if ((is_numeric($value)) && ($value <= 32)) {
			$this->pe4_address_length = $value;
		} else {
			$this->pe4_address_length = '';
		}
	}
	function set_pe6_address($value) {
		$this->pe6_address = $value;
	}
	function set_pe6_address_length($value) {
		if ((is_numeric($value)) && ($value <= 128)) {
			$this->pe6_address_length = $value;
		} else {
			$this->pe6_address_length = '';
		}
	}
	function set_ce4_address($value) {
		$this->ce4_address = $value;
	}
	function set_ce4_address_length($value) {
		if ((is_numeric($value)) && ($value <= 32)) {
			$this->ce4_address_length = $value;
		} else {
			$this->ce4_address_length = '';
		}
	}
	function set_ce6_address($value) {
	
		$this->ce6_address = $value;
	}
	function set_ce6_address_length($value) {
		if ((is_numeric($value)) && ($value <= 128)) {
			$this->ce6_address_length = $value;
		} else {
			$this->ce6_address_length = '';
		}
	}
	function set_mtu($value) {
		if (is_numeric($value)) {
			$this->mtu = $value;
			return true;
		} else {
			$this->error = "Invalid MTU size";
			return false;
		}
	}
	function set_bgp_as($value) {
		$this->bgp_as = $value;
	}
	function set_bgp_pass($value) {
		$this->bgp_pass = $value;
	}
	function set_traffic_policing($value) {
		if (is_numeric($value)) {
			$this->traffic_policing = $value;
			return true;
		} else {
			$this->error = "Invalid traffic policing number";
			return false;
		}
	}
	function set_pe_id($value) {
		if (is_numeric($value)) {
			$this->pe_id = $value;
			return true;
		} else {
			$this->error = "Invalid PE (Device) ID";
			return false;
		}
	}
	function clear_ipv4_prefixes() {
		$this->ipv4_prefixes = array();
	}
	function clear_ipv6_prefixes() {
		$this->ipv6_prefixes = array();
	}
	function add_ipv4_prefixes($value) {
		if (preg_match("(((\d+)\.(\d+)\.(\d+)\.(\d+))(\/(\d+)))",$value, $match)) {
			//print "<pre>"; print_r($match); print "</pre>";
			$this->ipv4_prefixes[$match[0]] = array("prefix"=> $match[1], "length" =>$match[7]);
			return true;
		} else {
			return false;
		}
	}

	function add_ipv6_prefixes($value) {
		$IPv6Pattern = "((([0-9a-fA-F]{1,4})((:[0-9a-fA-F]{1,4}){0,7}::))(\/(\d+))?)";
		if (preg_match($IPv6Pattern,$value, $match)) {
			//print "<pre>"; print_r($match); print "</pre>";
			$this->ipv6_prefixes[$match[0]] = array("prefix"=> $match[1], "length" =>$match[6]);
			return true;
		} else {
			return false;
		}
	}

	function set_port_name($value) {
		if ($value != '') {
			$this->port_name = $value;
			return true;
		} else {
			$this->error = "Port name can not be empty";
			return false;
		}
	}
	function set_tagged($value) {
		if ($value) {
			$this->tagged = 1;
		} else {
			$this->tagged = 0;
		}
	}

	function set_vlan_id($value) {
		if (is_numeric($value)) {
			$this->vlan_id = $value;
			return true;
		} else {
			$this->error = "Invalid vlan ID";
			return false;
		}
	}



	function insert() {
	// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->contact_id))) {
			$this->error = "Invalid contact id";
			return false;
		}
		if (!(is_numeric($this->service_type))) {
			$this->error = "Invalid service type id";
			return false;
		}
		if ($this->service_id != '') {
			$this->error = "This is an insert, service_id should be empty";
			return false;
		} 
		if ($this->routing_type == '') {
			$this->error = "routing type can not be empty";
			return false;
		} 
		if ($this->pe_id == '') {
			$this->error = "No PE (Device) ID specified";
			return false;
		} 
		if ($this->port_name == '') {
			$this->error = "No port specified";
			return false;
		} 
		if (($this->routing_type == 'BGP') && (!is_numeric($this->bgp_as))) {
			$this->error = "Invalid AS number";
			return false;
		} 
		if (($this->tagged > 0) && (!is_numeric($this->vlan_id))) {
			$this->error = "Invalid VLAN-ID";
			return false;
		} 
		if ($this->bgp_as == '') {
			$sql_bgp_as = "NULL";
		}  else {
			$sql_bgp_as = "'$this->bgp_as'";
		}

		// Now we''l need to set the Ipv4 and IPv6 prefixes array to a string,
		// delimited by \n
		$this->ipv4_prefix_string = '';
		foreach(array_keys($this->ipv4_prefixes) as $key) {
			$this->ipv4_prefix_string .= $key . "\n";
		}
		$this->ipv6_prefix_string = '';
		foreach(array_keys($this->ipv6_prefixes) as $key ) {
			$this->ipv6_prefix_string .= $key . "\n";
		}

		// Format PE addresses
		if ($this->pe4_address != '') {
			$pe4_address_string = $this->pe4_address."/".$this->pe4_address_length;
		} else {
			$pe4_address_string = '';
		}
		if ($this->pe6_address != '') {
			$pe6_address_string = $this->pe6_address."/".$this->pe6_address_length;
		} else {
			$pe6_address_string = '';
		}
		// Format CE addresses
		if ($this->ce4_address != '') {
			$ce4_address_string = $this->ce4_address."/".$this->ce4_address_length;
		} else {
			$ce4_address_string = '';
		}
		if ($this->ce6_address != '') {
			$ce6_address_string = $this->ce6_address."/".$this->ce6_address_length;
		} else {
			$ce6_address_string = '';
		}

		// We will be using a tramsaction for this
		// firts , start transaction
		mysql_query("BEGIN") or die("Error, start of transaction failed " . mysql_error());
		$commitok = 0;
		$query = "INSERT INTO L3_service_details (traffic_policing, logical_router,  
			routing_type, bgp_as,  IPv4_unicast, IPv4_multicast, BCNETrouterAddress4, 
			CustrouterAddress4, IPv4_prefixes,
			IPv6_unicast, IPv6_multicast, BCNETrouterAddress6,
			CustrouterAddress6, IPv6_prefixes,  router) 
			VALUES ('$this->traffic_policing',  '$this->logical_router', 
			'$this->routing_type', $sql_bgp_as, 
			'$this->ipv4_unicast',
			'$this->ipv4_multicast',
			'$pe4_address_string',
			'$ce4_address_string',
			'$this->ipv4_prefix_string',
			'$this->ipv6_unicast',
			'$this->ipv6_multicast',
			'$pe6_address_string',
			'$ce6_address_string',
			'$this->ipv6_prefix_string',
        	'$this->pe_id') "; 

		$result = mysql_query($query) ; #  or die("Error, query failed. <br>$query<br> " . mysql_error());
		if (!$result) {
			$commitok = $commitok +1;
			$this->error .=  "Query failed: $query <br>Throwed: "  . mysql_error() ;
		}
		$l3_service_detail_id = mysql_insert_id();
        
		// Query to add generic service to database
		if ($this->in_production == '') {
			$sql_in_production = "date_in_production = NULL";
		} else {
			$sql_in_production = "date_in_production = '$this->in_production'";
		}
		if ($this->out_production == '') {
			$sql_out_production = "date_out_production = NULL";
		} else {
			$sql_out_production = "date_out_production = '$this->out_production'";
		}
		if ($this->status == '') {
			$sql_status = "status = NULL";
		} else {
			$sql_status = "status = '$this->status'";
		}

		$query = "Insert INTO Services SET name = '$this->name', cust_id =
			'$this->contact_id', service_type = '$this->service_type', notes =
			'$this->notes', portal_statistics  = '$this->portal_statistics' ,
			l3_service_id = '$l3_service_detail_id',
			$sql_in_production, $sql_out_production, $sql_status
			";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$commitok = $commitok +1;
			$this->error .=  "Query: $query <br>Throwed: " . mysql_error();
		}
		$service_id = mysql_insert_id();

		$query = "INSERT INTO Services_Interfaces SET 
			interface_name = '$this->port_name',
			device = '$this->pe_id',
			tagged = '$this->tagged',
			vlan = '$this->vlan_id',
			mtu = '$this->mtu',
			service_id = '$service_id'";
		$result = mysql_query($query); 
		if (!$result) {
			$commitok = $commitok +1;
			$this->error .=  "Query: $query <br>Throwed: " . mysql_error();
		}
        
		// Now if all was successfull commit, otherwise rollback
		if ( $commitok > 0 ) {
			// not good do rollback
			mysql_query("ROLLBACK") or die("Error, Rollback failed " . mysql_error());
			$this->error .= "<br>Something went wrong, Database rollback was performced, service not commited to database";
			return false;
		} else {
			// all successfull, do commit
			$result = mysql_query("COMMIT") or die("Error, Commit failed " . mysql_error());
			if ($result) {
				return $service_id;
			} else {
				$this->error .= "<br>Commit failed";
				return false;
			}
		}
	}

	function update() {
	// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->contact_id))) {
			$this->error = "Invalid contact id";
			return false;
		}
		if (!(is_numeric($this->service_type))) {
			$this->error = "Invalid service type id";
			return false;
		}
		if ($this->service_id == '') {
			$this->error = "service_id can not be empty";
			return false;
		} 
		if (!is_numeric($this->l3_service_id)) {
			$this->error = "Invalid L3 service_id ";
			return false;
		}
		// Format PE addresses
		if ($this->pe4_address != '') {
			$pe4_address_string = $this->pe4_address."/".$this->pe4_address_length;
		} else {
			$pe4_address_string = '';
		}
		if ($this->pe6_address != '') {
			$pe6_address_string = $this->pe6_address."/".$this->pe6_address_length;
		} else {
			$pe6_address_string = '';
		}
		// Format CE addresses
		if ($this->ce4_address != '') {
			$ce4_address_string = $this->ce4_address."/".$this->ce4_address_length;
		} else {
			$ce4_address_string = '';
		}
		if ($this->ce6_address != '') {
			$ce6_address_string = $this->ce6_address."/".$this->ce6_address_length;
		} else {
			$ce6_address_string = '';
		}

		// Now we''l need to set the Ipv4 and IPv6 prefixes array to a string,
		// delimited by \n
		$this->ipv4_prefix_string = '';
		foreach(array_keys($this->ipv4_prefixes) as $key) {
			$this->ipv4_prefix_string .= $key . "\n";
		}
		$this->ipv6_prefix_string = '';
		foreach(array_keys($this->ipv6_prefixes) as $key ) {
			$this->ipv6_prefix_string .= $key . "\n";
		}

		if ($this->bgp_as == '') {
			$sql_bgp_as = "NULL";
		}  else {
			$sql_bgp_as = "'$this->bgp_as'";
		}
		//echo $this->ipv6_prefix_string;

		// We will be using a tramsaction for this
		// firts , start transaction
		mysql_query("BEGIN") or die("Error, start of transaction failed " . mysql_error());
		$commitok = 0;
		
		$query = "UPDATE L3_service_details SET 
			traffic_policing = '$this->traffic_policing',
			logical_router =  '$this->logical_router',  
			routing_type = '$this->routing_type',
			bgp_as = $sql_bgp_as, 
			IPv4_unicast = '$this->ipv4_unicast',
			IPv4_multicast = '$this->ipv4_multicast',
			BCNETrouterAddress4 = '$pe4_address_string',
			CustrouterAddress4 = '$ce4_address_string',
			IPv4_prefixes = '$this->ipv4_prefix_string',
			IPv6_unicast = '$this->ipv6_unicast',
			IPv6_multicast = '$this->ipv6_multicast',
			BCNETrouterAddress6 = '$pe6_address_string',
			CustrouterAddress6 = '$ce6_address_string', 
			IPv6_prefixes = '$this->ipv6_prefix_string',
			router = '$this->pe_id' 
			WHERE l3_service_id = '$this->l3_service_id'" ;

		$result = mysql_query($query) ; 
		if (!$result) {
			$commitok = $commitok +1;
			$this->error .=  "Query failed: $query <br>Throwed: "  . mysql_error() ;
		}
        
		// Query to update generic service to database

		if ($this->in_production == '') {
			$sql_in_production = "date_in_production = NULL";
		} else {
			$sql_in_production = "date_in_production = '$this->in_production'";
		}
		if ($this->out_production == '') {
			$sql_out_production = "date_out_production = NULL";
		} else {
			$sql_out_production = "date_out_production = '$this->out_production'";
		}
		if ($this->status == '') {
			$sql_status = "status = NULL";
		} else {
			$sql_status = "status = '$this->status'";
		}
			
		$query = "UPDATE Services SET name = '$this->name', cust_id =
			'$this->contact_id', service_type = '$this->service_type', notes =
			'$this->notes', portal_statistics  = '$this->portal_statistics' ,
			 $sql_in_production, $sql_out_production, $sql_status
			 WHERE service_id = '$this->service_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$commitok = $commitok +1;
			$this->error .=  "Query: $query <br>Throwed: " . mysql_error();
		}

		// Query to connect interfaces to this service
		$query = "UPDATE Services_Interfaces SET 
			interface_name = '$this->port_name', 
			device = '$this->pe_id',
			tagged = '$this->tagged',
			vlan = '$this->vlan_id',
			mtu = '$this->mtu' 
			WHERE service_id = '$this->service_id'";
		$result = mysql_query($query); 
		if (!$result) {
			$commitok = $commitok +1;
			$this->error .=  "Query: $query <br>Throwed: " . mysql_error();
		}
        
		// Now if all was successfull commit, otherwise rollback
		if ( $commitok > 0 ) {
			// not good do rollback
			mysql_query("ROLLBACK") or die("Error, Rollback failed " . mysql_error());
			$this->error .= "Something went wrong, Database rollback was performced, service not commited to database";
			return false;
		} else {
			// all successfull, do commit
			$result = mysql_query("COMMIT") or die("Error, Commit failed " . mysql_error());
			if ($result) {
				return true;
			} else {
				$this->error .= "Commit failed";
				return false;
			}
		}
	}

	function delete() {
		if ($this->service_id == '') {
			$this->error = "Invalid service id";
			return false;
		} 
		$query = "update Services set archived = '1' where service_id =
			'$this->service_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

}

class Virtual_router_service extends Service {
		
	private $interfaces = array();
	protected $vlan_id;
	protected $vrouter_name;
	protected $device_id;
	protected $device_name;

	function __construct($service_id = '') {
		if (is_numeric($service_id)) {
			parent::get_service_info($service_id);
			$this->get_service_info($service_id);
		}
	}

	protected function get_service_info($service_id){
		$query = "SELECT L0_virtual_router_service_details.id, 
				L0_virtual_router_service_details.device_id, 
				L0_virtual_router_service_details.virtual_router_name,
				Devices.name
		FROM  L0_virtual_router_service_details, Devices
		WHERE  L0_virtual_router_service_details.service_id = '$this->service_id' 
			AND L0_virtual_router_service_details.device_id = Devices.device_id ";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			die( mysql_error() ."   -- query: $query");
			return false;
		}
		if (mysql_numrows($result) < 1 ) {
			$this->error = "No data found for this service";
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$this->vrouter_name = $obj->virtual_router_name;
			$this->device_id = $obj->device_id;
			$this->device_name = $obj->name;
		}

		// 2ns query to get all interfaces for this L2 service
		$query = "SELECT  Services_Interfaces.id,Services_Interfaces.interface_name, 
			Services_Interfaces.service_id, Services_Interfaces.tagged, 
			Services_Interfaces.vlan, Services_Interfaces.device, Services_Interfaces.mtu,
			Services_Interfaces.ip_address, Services_Interfaces.subnet_mask,
			Devices.name as device_name
		FROM Services_Interfaces, Devices 
		WHERE Services_Interfaces.service_id = '$service_id' 
		AND Services_Interfaces.device = Devices.device_id order by
		interface_name";
		// execute the query 
		$result =  mysql_query($query) ;
        	if (!$result)  {
        		die( mysql_error() ."   -- query: $query");
        		return false;
        	}
		if (mysql_numrows($result) < 1 ) {
        	$this->error = "No data found for this service";
        	return false;
		}
		
 		while ($obj = mysql_fetch_object($result)){
			$interface_info = array();
			$interface_info[service_interface_id] = $obj->id;
			$interface_info[device_id] = $obj->device;
			$interface_info[device_name] = $obj->device_name;
			$interface_info[port_name] = $obj->interface_name;
			$interface_info[tagged] = $obj->tagged;
			$interface_info[vlan] = $obj->vlan;
			$interface_info[mtu] = $obj->mtu;
			$interface_info[ip_complete] = "";
			if ($obj->ip_address != '') {
				$interface_info[ip_complete] =  $obj->ip_address;
				if ($obj->subnet_mask != '') {
					$interface_info[ip_complete] = $obj->ip_address ."/".$obj->subnet_mask;
				}
			}
			$interface_info[ip_address] = $obj->ip_address;
			$interface_info[ip_mask] = $obj->ip_subnet_mask;
			$interface_info[service_id] = $obj->service_id;
			$this->interfaces[$obj->id] = $interface_info;
		}
	}

	function get_interfaces() {
		return $this->interfaces;
	}


	function insert() {
	// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if ($this->vrouter_name == '') {
			$this->error = "Vrouter name can not be empty";
			return false;
		}
		if (!(is_numeric($this->contact_id))) {
			$this->error = "Invalid contact id";
			return false;
		}
		if (!(is_numeric($this->service_type))) {
			$this->error = "Invalid service type id";
			return false;
		}
		if ($this->service_id != '') {
			$this->error = "This is an insert, service_id should be empty";
			return false;
		} 
		if (!is_numeric($this->device_id)) {
			$this->error = "No device id specified";
			return false;
		} 

		// We will be using a tramsaction for this
		// firts , start transaction
		mysql_query("BEGIN") or die("Error, start of transaction failed " . mysql_error());
		$commitok = 0;

		// Query to add generic service to database
		if ($this->in_production == '') {
			$sql_in_production = "date_in_production = NULL";
		} else {
			$sql_in_production = "date_in_production = '$this->in_production'";
		}
		if ($this->out_production == '') {
			$sql_out_production = "date_out_production = NULL";
		} else {
			$sql_out_production = "date_out_production = '$this->out_production'";
		}
		if ($this->status == '') {
			$sql_status = "status = NULL";
		} else {
			$sql_status = "status = '$this->status'";
		}
        
		$query = "Insert INTO Services SET name = '$this->name', cust_id =
			'$this->contact_id', service_type = '$this->service_type', notes =
			'$this->notes', $sql_in_production, $sql_out_production, $sql_status
			";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$commitok = $commitok +1;
			$this->error .=  "Query: $query <br>Throwed: " . mysql_error();
		}
		$service_id = mysql_insert_id();


		$query = "INSERT INTO L0_virtual_router_service_details SET 
			virtual_router_name = '$this->vrouter_name',
			device_id = '$this->device_id',
			service_id = '$service_id'
		";

		$result = mysql_query($query) ; #  or die("Error, query failed. <br>$query<br> " . mysql_error());
		if (!$result) {
			$commitok = $commitok +1;
			$this->error .=  "Query failed: $query <br>Throwed: "  . mysql_error() ;
		}
		$l0_service_detail_id = mysql_insert_id();

		if (!$result)  {
			$commitok = $commitok +1;
			$this->error .=  "Query: $query <br>Throwed: " . mysql_error();
		}
		$service_id = mysql_insert_id();
	
		// Now if all was successfull commit, otherwise rollback
		if ( $commitok > 0 ) {
			// not good do rollback
			mysql_query("ROLLBACK") or die("Error, Rollback failed " . mysql_error());
			$this->error .= "<br>Something went wrong, Database rollback was performced, service not commited to database";
			return false;
		} else {
			// all successfull, do commit
			$result = mysql_query("COMMIT") or die("Error, Commit failed " . mysql_error());
			if ($result) {
				return $service_id;
			} else {
				$this->error .= "<br>Commit failed";
				return false;
			}
		}
	}

	function update() {
	// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if ($this->vrouter_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->contact_id))) {
			$this->error = "Invalid contact id";
			return false;
		}
		if (!(is_numeric($this->service_type))) {
			$this->error = "Invalid service type id";
			return false;
		}
		if (!is_numeric($this->service_id)) {
			$this->error = "service_id can not be empty";
			return false;
		} 
		if (!is_numeric($this->device_id)) {
			$this->error = "No device id specified";
			return false;
		} 


		// We will be using a tramsaction for this
		// firts , start transaction
		mysql_query("BEGIN") or die("Error, start of transaction failed " . mysql_error());
		$commitok = 0;
		$query = "UPDATE L0_virtual_router_service_details SET 
			virtual_router_name = '$this->vrouter_name',
			device_id = '$this->device_id'
			WHERE service_id = '$this->service_id'" ;

		$result = mysql_query($query) ; 
		if (!$result) {
			$commitok = $commitok +1;
			$this->error .=  "Query failed: $query <br>Throwed: "  . mysql_error() ;
		}
        
		if ($this->in_production == '') {
			$sql_in_production = "date_in_production = NULL";
		} else {
			$sql_in_production = "date_in_production = '$this->in_production'";
		}
		if ($this->out_production == '') {
			$sql_out_production = "date_out_production = NULL";
		} else {
			$sql_out_production = "date_out_production = '$this->out_production'";
		}
		if ($this->status == '') {
			$sql_status = "status = NULL";
		} else {
			$sql_status = "status = '$this->status'";
		}
		// Query to update generic service to database
		$query = "UPDATE Services SET name = '$this->name', cust_id =
			'$this->contact_id', service_type = '$this->service_type', notes =
			'$this->notes', portal_statistics  = '$this->portal_statistics', 
			$sql_in_production, $sql_out_production, $sql_status
			 WHERE service_id = '$this->service_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$commitok = $commitok +1;
			$this->error .=  "Query: $query <br>Throwed: " . mysql_error();
		}

        
		// Now if all was successfull commit, otherwise rollback
		if ( $commitok > 0 ) {
			// not good do rollback
			mysql_query("ROLLBACK") or die("Error, Rollback failed " . mysql_error());
			$this->error .= "Something went wrong, Database rollback was performced, service not commited to database";
			return false;
		} else {
			// all successfull, do commit
			$result = mysql_query("COMMIT") or die("Error, Commit failed " . mysql_error());
			if ($result) {
				return true;
			} else {
				$this->error .= "Commit failed";
				return false;
			}
		}
	}

	function delete() {
		if ($this->service_id == '') {
			$this->error = "Invalid service id";
			return false;
		} 
		$query = "update Services set archived = '1' where service_id =
			'$this->service_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

	function set_device_id($value) {
		if (is_numeric($value)) {
			$this->device_id = $value;
			return true;
		} else {
			$this->error = "device id can not be empty";
			return false;
		}
	}

	function set_virtual_router_name($value) {
		if ($value != '') {
			$this->vrouter_name = $value;
			return true;
		} else {
			$this->error = "vrouter Name can not be empty";
			return false;
		}
	}


}
?>

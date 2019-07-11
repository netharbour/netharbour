<?php

class Event {

	// This is the notify email address


	private $event_id = false;
	private $status;
	private $info_msg;
	private $performance_data = null;
	private $insert_date;
	private $last_updated;
	private $active;
	private $check_id = false;
	private $hostname;
	private $check_name;
	private $key1;
	private $key2;
	private $script;
	private $notify_state;

	private $error = false;
	
	function __construct($event_id = '') {
		if (is_numeric($event_id)) {
			$this->get_event_info($event_id);
		}
		
		// Determine notify addresses;
		global $ini_array;
		if (! isset($ini_array)) {
			$ini_array = parse_ini_file("config/cmdb.conf");
		}
		$this->notify_email = $ini_array['email_to'];
		$this->notify_email_from = $ini_array['email_from'];
	}

	protected function get_event_info($event_id){
		$query = "SELECT 
				events.event_id,
				events.status,
				events.info_msg,
				events.insert_date,
				events.last_updated,
				events.active,
				events.check_id,
				events.host_name,
				events.check_name,
				events.key1,
				events.key2,
				events.script,
				events.notify_state
			FROM 
				events
			WHERE
				events.event_id = '$event_id' 
		 ";
		// execute the query 
		$result =  mysql_query($query) ;
        	if (!$result)  {
        		$this->error = mysql_error() ."   -- query: $query ";
			print "ANDREE $this->error <br>";
        		return false;
        	}
		if (mysql_numrows($result) < 1 ) {
        		$this->error = "No data found for this event";
        		return false;
		}

 		while ($obj = mysql_fetch_object($result)){
			$this->event_id = $obj->event_id;
			$this->status = $obj->status;
			$this->info_msg = $obj->info_msg;
			$this->insert_date = $obj->insert_date;
			$this->last_updated = $obj->last_updated;
			$this->active = $obj->active;
			$this->check_id = $obj->check_id;
			if (is_null($obj->check_id)) {
				$this->hostname = $obj->host_name;
				$this->check_name = $obj->check_name;
			} else {
				$check = new Check($obj->check_id);
				$this->hostname = $check->get_hostname();
				$this->check_name = $check->get_name();
			}
			$this->key1 = $obj->key1;
			$this->key2 = $obj->key2;
			$this->script = $obj->script;
			$this->notify_state = $obj->notify_state;
		}
		return true;
	}

	public function get_events($active = 1) {
		if ($active != 0) {
			$active = 1;
		}
		$events = array();
		$query = "SELECT event_id, check_name 
			FROM events where active = '$active' order by
			event_id desc" ;
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$events[$obj->event_id] = $obj->check_name;
		}
		return $events;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_check_id() {
		return $this->check_id;
	}
	function get_status() {
		return $this->status;
	}
	function get_event_id() {
		return $this->event_id;
	}
	function get_info_msg() {
		return $this->info_msg;
	}
	function get_performance_data() {
		return $this->performance_data;
	}
	function get_insert_date() {
		return $this->insert_date;
	}
	function get_last_updated() {
		return $this->last_updated;
	}
	function get_active() {
		return $this->active;
	}
	function get_hostname() {
		return $this->hostname;
	}
	function get_check_name() {
		return $this->check_name;
	}
	function get_key1() {
		return $this->key1;
	}
	function get_key2() {
		return $this->key2;
	}
	function get_script() {
		return $this->script;
	}

	// Set functions
	function set_check_id($value) {
		if (is_numeric($value)) {
			$this->check_id = $value;
			return true;
		} else {
			$this->error = "event id can not be empty";
			return false;
		}
	}
	function set_status($value) {
		$this->status = $value;
	}

	function set_info_msg($value) {
		$this->info_msg = $value;
	}
	function set_performance_data($value) {
		if ($value != '') {
			$this->performance_data = $value;
		}
	}
	function set_active($value) {
		$this->active = $value;
	}
	function set_hostname($value) {
		$this->hostname = $value;
	}
	function set_check_name($value) {
		$this->check_name = $value;
	}
	function set_key1($value) {
		$this->key1 = $value;
	}
	function set_key2($value) {
		$this->key2 = $value;
	}
	function set_script($value) {
		$this->script = $value;
	}
	function set_notify_state($value) {
		$this->notify_state = $value;
	}

	function event_exists() {
		// Query depends on if there's a event id
		// If there's an event id then use that as key
		// else use hostname, check_name, key1 key2 as keys
        
		if ($this->check_id === false) {
			// 1st we should try to find if there is a check, based on 
			// hostname, checkname and keys
			// This query will try to determine the check id.
			// very important the the check_name, hostname and keys are 
			// entered correctly
		
			$query_check = " select check_id from service_checks, Devices WHERE
				 service_checks.device_id = Devices.device_id AND
				Devices.name = '$this->hostname' AND
				service_checks.name = '$this->check_name' AND
				service_checks.key1 = '$this->key1' AND
                		service_checks.key2 = '$this->key2' ";
			$result = mysql_query($query_check) or error_log('Error, query failed. ' . mysql_error() ." $alert_query");
			if (mysql_num_rows($result) == 0) {
				// Could not find check based on this info.
				$this->check_id = false;
			} else  {
				$obj = mysql_fetch_object($result);
				$this->check_id = $obj->check_id;
			}
		}

		if ($this->check_id === false) {
			$query_where = " (host_name = '$this->hostname' AND
			check_name = '$this->check_name' AND
			key1 = '$this->key1' AND
			key2 = '$this->key2')
			";
		} else {
			$query_where = "check_id = '$this->check_id'";
		}
		$alert_query = " Select event_id FROM events
		WHERE
			$query_where AND
			status = '$this->status' AND
			active = '1'";
		$result = mysql_query($alert_query) or error_log('Error, query failed. ' . mysql_error() ." $alert_query");
		if (mysql_num_rows($result) == 0) {
		// event does not exist
			return false;
		} elseif (mysql_num_rows($result) == 1)  {
			// One event return id
			$obj = mysql_fetch_object($result);
			return $obj->event_id;
		} elseif (mysql_num_rows($result) > 1)  {
			error_log("More than 1 row returned in event_exists(). query: $alert_query");
			// Because more than one event exists, we clear all of them
			// so we can insert a clean one
			$clear_event_res = $this->clear_event();
			if ( $clear_event_res == false ){
				error_log('Clear event in event_exists() was not successful.');
			}
			return false;
		} else {
			$this->error = "Undefined state in event_exists()";
			error_log("Undefined state in event_exists()");
			return false;
		}
	}

	function update_event() {
		$alert_query = " 
			UPDATE events SET
			last_updated = NOW(),
			info_msg = '". mysql_real_escape_string($this->info_msg) ."',
			script = '$this->script'
			WHERE
			event_id = '$this->event_id'
		";
        
		$result = mysql_query($alert_query) or error_log('Error, query failed. ' . mysql_error());
	}

	function clear_event() {
		// Query depends on if there's a event id
		// If there's an event id then use that as key
		// else use hostname, check_name, key1 key2 as keys
		if ($this->check_id === false) {
			$query_where = " (host_name = '$this->hostname' AND
			check_name = '$this->check_name' AND
			key1 = '$this->key1' AND
			key2 = '$this->key2')";
		} else {
			$query_where = "check_id = '$this->check_id'";
		}
		$alert_query = "update events
			SET 
				active = '0',
				last_updated = NOW()
			WHERE
				$query_where AND
				active = '1';
			";
		// mysql documentation suggest that in case of a deadlock
		// the client should retry automatically, doing 4 times
		$NUM_RETRIES = 4;
		$MIN_TIMEOUT = 10;
		$retry = 1;
		while ( $retry <= $NUM_RETRIES ){
			$result = mysql_query($alert_query);
			if (!$result) {
				$mysql_error_message = mysql_error();
				error_log('Error, query failed. Retry '. $retry . ' - ' . $mysql_error_message . " $alert_query");
				if ( strpos($mysql_error_message, "eadlock") !== false ) {
					// Only retry when Deadlock error
					// Add a delay in msec based on an exponential backoff + a random jitter
					$backoff_delay = $MIN_TIMEOUT * pow(2, $retry) + mt_rand($MIN_TIMEOUT, $MIN_TIMEOUT * 3);
					$delay = $MIN_TIMEOUT + $backoff_delay;
					usleep( $delay * 1000 );
					$retry++;
				} else {
					error_log('It has been impossible to clean the event for ' . $query_where . ' because error was: ' . $mysql_error_message);
					return false;
				}
			} else {
				if ( $retry > 1 ) {
					// Logging second and third tries after Deadlock
					error_log('Event for ' . $query_where . ' updated after ' . $retry . ' tries');
				}
				break;
			}
		}
        if ( $retry > $NUM_RETRIES && !$result){
            error_log('Error, query failed after ' . $NUM_RETRIES . ' retries' . ' - ' . $mysql_error_message . " $alert_query");
            return false;
        }
		return $result;
	}

	function insert_event() {
		if (is_numeric($this->check_id)) {
			$check_id = "'$this->check_id'";
		} else {
			$check_id = "NULL";
		}

		$alert_query = " INSERT INTO events
			SET
			check_id = $check_id,
			host_name = '$this->hostname',
			check_name = '$this->check_name',
			key1 = '$this->key1',
			key2 = '$this->key2',
			status = '$this->status',
			info_msg = '". mysql_real_escape_string($this->info_msg) ."',
			script = '$this->script',
			insert_date = NOW(),
			last_updated = NOW(),
			active = '1';
		";
		$result = mysql_query($alert_query) or error_log('Error, query failed. ' . mysql_error() ." $alert_query");
		return mysql_insert_id();
	}

	function update_service_checks() {
		$check_query = " UPDATE service_checks
		SET
			last_event_id = '$this->event_id',
			last_check = NOW()
		WHERE
			check_id = '$this->check_id'
		";
		$result = mysql_query($check_query) or error_log('Error, query failed. ' . mysql_error());
	}

	function update_performance_data() {
		/*
		Expected format of $this->performance_data is multiple label value tuples
		seperated by comma whitespace ", "  example:
		percent_packet_loss=0, rta=0.80
		
		This is the nagios plugin perfomance data format:
		'label'=value[UOM];[warn];[crit];[min];[max] 
		For now we don't implement [warn];[crit];[min];[max] , just the data
		UOM (unit of measurement) is one of:
		
		no unit specified - assume a number (int or float) of things (eg, users, processes, load averages)
		s - seconds (also us, ms)
		% - percentage
		B - bytes (also KB, MB, TB)
		c - a continous counter (such as bytes transmitted on an interface)

		This will be implemented as all GAUGE RRD datatypes
		expecpt for c - a continous counter, this should be COUNTER/DERIVE 

		*/
		if (! is_null($this->performance_data) && (! empty($this->performance_data))) {

			// Now split perf data, and store in $rrd_data
			$rrd_data=array();
			$perfdata = trim($this->performance_data);
			$sections = preg_split("/,/",$perfdata, -1, PREG_SPLIT_NO_EMPTY);
			foreach ($sections as $k => $word) {

				// Now split the key value part
				$tupple = preg_split("/=/",$word, -1, PREG_SPLIT_NO_EMPTY);
				$label = trim($tupple[0]); // label
				$value = trim($tupple[1]); // value
				if (! (array_key_exists(0,$tupple)) || ! (array_key_exists(1,$tupple)) || ($label == '') || ($value == '')) {
					print "$label $value\n";
					print "Warning invalid performance data part '$tupple' in $perfdata\n";
					continue;
				}
				// Store data
				
				// A DS can not be longer than 19 chars, so we need to reformaat that
			        $tmp_label =  substr($label, 0, 18);
				$rrd_data[$label]['label']=$tmp_label;

				// Determine type based on UOM (unit of measurement)
				// Only if it ends with "c" (counter) do we need a counter
				// Use preg_replace to replace c$ (last c) if that's the case
				// it's a counter, we then have to correct value
				// all others are GAUGE
				// For clarity, spell out other cases, we need to strip all of the
				// UOM anyways
				if (preg_match("/c$/",$value)) {
					$new_val = preg_replace("/c$/","",$value);
					//print "Detected counter ,$value matched c$ new value is $new_val\n";
					// A counter always has to be a 'simple integer', no floats and positive
					$new_val = intval($new_val);
					$rrd_data[$label]['type'] = "COUNTER";
					$rrd_data[$label]['value']= $new_val;
				} elseif (preg_match("/(s)$|(ms)$|(us)$/",$value,$matches)) {
					$new_val = preg_replace("/s$|ms$|us$/","",$value);
					//print "Detected s - seconds (also us, ms)\n";
					// Rewrite to seconds
					if ($matches[0] == "s") {
						$new_val =$new_val * 1;
					}
					elseif ($matches[0] == "ms") {
						$new_val =$new_val * pow(10,-3);
					}
					elseif ($matches[0] == "us") {
						$new_val =$new_val * pow(10,-6);
					}
					$rrd_data[$label]['type'] = "GAUGE";
					$rrd_data[$label]['value']=$new_val;
				} elseif (preg_match("/%$/",$value)) {
					$new_val = preg_replace("/%$/","",$value);
					//print "Detected % $value matched % new value is $new_val\n";
					$rrd_data[$label]['type'] = "GAUGE";
					$rrd_data[$label]['value']=$new_val;
				} elseif (preg_match("/(B)$|(KB)$|(MB)$|(GB)$|(TB)$|(PB)$/",$value,$matches)) {
					$new_val = preg_replace("/B$|KB$|MB$|GB$|TB$|PB$/","",$value);
					// Rewrite to bytes
					if ($matches[0] == "B") {
						$new_val =$new_val * 1;
					}
					elseif ($matches[0] == "KB") {
						$new_val =$new_val * pow(10,3);
					}
					elseif ($matches[0] == "MB") {
						$new_val =$new_val * pow(10,6);
					}
					elseif ($matches[0] == "GB") {
						$new_val =$new_val * pow(10,9);
					}
					elseif ($matches[0] == "TB") {
						$new_val =$new_val * pow(10,12);
					}
					elseif ($matches[0] == "PB") {
						$new_val =$new_val * pow(10,15);
					}
					$rrd_data[$label]['type'] = "GAUGE";
					$rrd_data[$label]['value']=$new_val;
				} else {
					//print "Detected default\n";
					$rrd_data[$label]['type'] = "GAUGE";
					$rrd_data[$label]['value']=$value;
				}
				
			}

			// Now check if the rrd file already exists, or if we need to create it
			$property = new Property();
			$rrdtool = $property->get_property("path_rrdtool");
			if (! is_executable ($rrdtool)) {
				print "Can't execute RRD tool $rrdtool\n";
				$this->error = "Can't execute RRD tool $rrdtool";
				return false;
			}

			$rrd_dir = $property->get_property("path_rrddir");
			// strip last /, if any, then append it again, to prevent we have 2
			$rrd_dir = preg_replace("/\/$/", "",$rrd_dir);
			$rrd_file = "checks/checkid_". $this->check_id .".rrd";
			$rrd_filename = "$rrd_dir/$rrd_file";
			
			if (!file_exists($rrd_filename)) {
				$this->create_rrd_archive($rrd_filename,$rrdtool,$rrd_data);
				print "Creating $rrd_filename\n";
			}
			$rrd_template = "";
			$rrd_values = "";
			foreach ($rrd_data as $label => $val) {
				$rrd_template .= $rrd_data[$label]['label'] .":";
				$rrd_values .= $val['value'] .":";
			}
			// remove trailing colons :
			$rrd_template = rtrim($rrd_template, ":");
			$rrd_values = rtrim($rrd_values, ":");
			
			// Update RRD file
			$my_cli =  "$rrdtool update '$rrd_filename' -t '$rrd_template' N:$rrd_values";
			$e = escapeshellcmd($my_cli);
			$f =exec("$e 2>&1 ", $output, $return_var);
			$message = implode("\n",$output) ;
			if ($return_var != 0) {
				print "Warning, while creating RRD file $file: $message\n cli was:\n$e\n";
				return false;
			}
			return true;
		}
	}

	function handle_event() {
		// $events is an event object without event_id set
		$this->event_id = false;
		$this->event_id = $this->event_exists();
		$status = 'unknown';
		if ($this->event_id) {
			// exitsing event
			// do update
			// No state change
			$this->update_event();
			$status = 'update';
			//print "existing event $this->event_id\n";
		} else {
			// New event , i.e. state change
			// Insert new one and update old one
			// This means a state change
			$this->clear_event();
			$this->event_id = $this->insert_event();
			if ($this->event_id) {
				$this->notify();
			} else {
				error_log('Unable to insert Event'.print_r($this));
			}
			$status = 'insert';
			//print "new event $this->event_id\n";
		}
		// Update Service checks table and performance data
		// So that we can get current status easily
		if ($this->event_id === false) {
			error_log("Event_id is not set... unknow state.., bailing out\n");
		} else {
			if (is_numeric($this->check_id)) {
				$this->update_service_checks();
				$this->update_performance_data();
			}
		}
		return $status;
	}

	function notify() {
		// Call notifcation scripts
		$e = escapeshellcmd("./notify_by_email.php $this->event_id");
		$f =exec("$e", $output, $return_var);
		//print_r($output);
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

	public function get_events_for_checks($checks,$from =false,$to =false) {
		// Default from is 1 month ago
		if ($from === false || $from == '') {
		//	$startTime = mktime() - 30*3600*24;
		} else { 
			$startTime = $from;
		}
		if ($to === false || $to =='') {
		//	$endTime = mktime();
		} else {
			$endTime = $to;
		}
		$events = array();
		if (is_array($checks)) {
			$check_sql = '';
			$i = 0;
			foreach ($checks as $check_id) {
				$i++;
				if (is_numeric($check_id)) {
					$check_sql .= " check_id = '$check_id' ";
					if ($i != count($checks)) {
						 $check_sql .= "OR ";
					}
				}
			}
		} else {
			$check_sql .= " check_id = '$checks' ";
		}
		
		// Build query
		$query = "select event_id, status 
			FROM events
			WHERE ($check_sql) 
			AND last_updated >= FROM_UNIXTIME($startTime)
			AND insert_date <= FROM_UNIXTIME($endTime)
			order by insert_date desc

		";
		$result =  mysql_query($query) ;
		 if (!$result)  {
			//$this->error = mysql_error();
			return false;
		}

		while ($obj = mysql_fetch_object($result)){
			$events[$obj->event_id] = $obj->status; 
		}
		return $events;
	}

	public function get_last_events($max = 10) {
		// This will return the ID's of the last $max events for this check
		$events = array();
		$query = "Select event_id, status
			FROM  events
			Order By insert_date desc
			limit $max";
		$result =  mysql_query($query) ;
		if (!$result)  {
			//$this->error = mysql_error();
			return false;
		} 
		while ($obj = mysql_fetch_object($result)){
			$events[$obj->event_id] = $obj->status;
		}
		return $events;
	}

	private function create_rrd_archive($file,$rrdtool,$data_sources=array()) {
		
		// First loop through the datasources:
		$my_ds = "";
		foreach ($data_sources as $ds => $ds_array) {
			//print_r($data_sources[$ds]);
			$my_ds .= "DS:'".$data_sources[$ds]['label']."':".$data_sources[$ds]['type'].":600:0:U ";
		}

		# Based on http://oss.oetiker.ch/rrdtool/tut/rrdtutorial.en.html
		#  600 samples of 5 minutes  (2 days and 2 hours)
		#  700 samples of 30 minutes (2 days and 2 hours, plus 12.5 days)
		#  775 samples of 2 hours    (above + 50 days)
		#  797 samples of 1 day      (above + 732 days, rounded up to 797)

		$my_rra ="";
		$my_rra .= "RRA:AVERAGE:0.5:1:2880 ";  // 2 day of 1 min samples (1 sample per min * 60 *24 *2)
		$my_rra .= "RRA:AVERAGE:0.5:10:1008 ";  // 1 week of 10 min samples (6 samples per hour *24 * 7)
		$my_rra .= "RRA:AVERAGE:0.5:60:2400 ";  // 100 days of 1 hour samples (24 *100)
		$my_rra .= "RRA:AVERAGE:0.5:240:2190 ";  // 365 days of 4 hour samples (6 in a a day * 365 =  2190
		$my_rra .= "RRA:AVERAGE:0.5:1200:797 ";
		$my_rra .= "RRA:MAX:0.5:1:2880 ";
		$my_rra .= "RRA:MAX:0.5:10:1008 ";
		$my_rra .= "RRA:MAX:0.5:240:2190 ";
		$my_rra .= "RRA:MAX:0.5:9000:500 ";


		$my_cli = "$rrdtool create '$file' $my_ds$my_rra --step 60";
		#print $my_cli;
		$e = escapeshellcmd($my_cli);
		#print "ANDREE $e\n";
		$f =exec("$e 2>&1 ", $output, $return_var);
		$message = implode("\n",$output) ;
		if ($return_var != 0) {
			print "Warning, while creating RRD file $file: $message\n cli was:\n$e\n";
			return false;
		}
		return true;


	}

	public function init_notification($file = null) {

		if (is_null($file)) {
			print "Argument required, expecting file name\n";
			exit;
		} else {
			if (!is_readable($file)) {
				print "Can't read file $file, file does not exists or no permission\n";
				exit;
			}
			// Restore object serialized Check Object 
			// This will give us access to all properties
			$my_obj = new Check();
			$my_obj = unserialize(file_get_contents($file));
			//print_r($my_obj);
			return $my_obj;
		}

	}

 


}

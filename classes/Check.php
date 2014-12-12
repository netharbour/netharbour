<?php

// Class file for Checks
include_once 'Device.php';
include_once 'Event.php';

class Check {
	private $check_id = false;
	private $name;
	private $device_id = false;
	private $template_id = false;
	private $template_name;
	private $last_event_id;
	private $check_status;
	private $last_check;
	private $arguments;
	private $check_script;
	private $interval;
	private $key1;
	private $key2;
	private $key1_name;
	private $key2_name;
	private $desc;
	private $check_script_parsed;
	private $hostname;
	private $notes;

	private $error = false;
	
	function __construct($check_id = '') {
		if (is_numeric($check_id)) {
			$this->get_check_info($check_id);
		}
	}

	protected function get_check_info($check_id){
		$query = "SELECT 
				service_checks.check_id,
				service_checks.name as name,
				service_checks.device_id,
				service_checks.template_id,
				service_checks.last_event_id,
				service_checks.last_check,
				service_checks.arguments,
				service_checks.check_script,
				service_checks.check_interval,
				service_checks.key1,
				service_checks.key2,
				service_checks.notes,
				service_checks.description,
				service_checks_template.key1_name,
				service_checks_template.key2_name,
				service_checks_template.name as template_name,
				service_checks_template.description as template_desc,
				service_checks_template.script as template_script

			FROM 
				service_checks, service_checks_template
			WHERE
				service_checks_template.template_id = service_checks.template_id
				AND service_checks.check_id = '$check_id' 
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

 		$obj = mysql_fetch_object($result);
		if (is_numeric($obj->template_id)) {
		}
		$this->check_id = $obj->check_id;
		$this->name = $obj->name;
		$this->device_id = $obj->device_id;
		$this->template_id = $obj->template_id;
		$this->template_name = $obj->template_name;
		$this->last_event_id = $obj->last_event_id;
		$this->last_check = $obj->last_check;
		$this->arguments = $obj->arguments;
		$this->check_script = $obj->template_script;
		$this->desc = $obj->template_desc;
		$this->interval = $obj->check_interval;
		$this->key1 = $obj->key1;
		$this->key2 = $obj->key2;
		$this->key1_name = $obj->key1_name;
		$this->key2_name = $obj->key2_name;
		$this->desc = $obj->description;
		$this->notes = $obj->notes;

		$dev = new Device($this->device_id);
		$this->hostname = $dev->get_name();
		$this->parse_arguments();
		return true;
	}

	public function get_checks($archived=0,$device_id=NULL,$template_id=NULL) {
		$checks = array();
		$filter;
		if (is_numeric($device_id)) {
			$filter .= " AND device_id = '$device_id'";
		}
		if (is_numeric($template_id)) {
			$filter .= " AND template_id = '$template_id'";
		}
		$query = "SELECT check_id, name 
			FROM service_checks
			WHERE archived = '$archived' $filter";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$checks[$obj->check_id] = $obj->name;
		}
		return $checks;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_check_id() {
		return $this->check_id;
	}
	function get_name() {
		return $this->name;
	}
	function get_device_id() {
		return $this->device_id;
	}
	function get_hostname() {
		return $this->hostname;
	}
	function get_template_id() {
		return $this->template_id;
	}
	function get_template_name() {
		return $this->template_name;
	}
	function get_last_event_id() {
		return $this->last_event_id;
	}
	function get_last_check() {
		return $this->last_check;
	}
	function get_arguments() {
		return $this->arguments;
	}
	function get_check_script() {
		return $this->check_script;
	}
	function get_parsed_check_script() {
		return $this->check_script_parsed;
	}
	function get_interval() {
		return $this->interval;
	}
	function get_key1() {
		return $this->key1;
	}
	function get_key1_name() {
		return $this->key1_name;
	}
	function get_key2() {
		return $this->key2;
	}
	function get_key2_name() {
		return $this->key2_name;
	}
	function get_desc() {
		return $this->desc;
	}
	function get_notes() {
		return $this->notes;
	}
	function get_status() {
		$event =  new Event($this->last_event_id);
		return $event->get_status();
	}

	function set_name($value) {
		$this->name = $value;
	}

	function set_device_id($value) {
		$this->device_id = $value;
	}
	function set_template_id($value) {
		$this->template_id = $value;
	}
	function set_last_event_id($value) {
		$this->last_event_id = $value;
	}
	function set_last_check($value) {
		$this->last_check = $value;
	}
	function set_check_script($value) {
		$this->check_script = $value;
	}
	function set_interval($value) {
		$this->interval = $value;
	}
	function set_key1($value) {
		$this->key1 = $value;
	}
	function set_key2($value) {
		$this->key2 = $value;
	}
	function set_desc($value) {
		$this->desc = $value;
	}
	function set_notes($value) {
		$this->notes = $value;
	}
	function set_arguments($value) {
		$this->arguments = $value;
	}



	function update() {
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->template_id))) {
			$this->error = "Invalid template (should be an Integer)";
			return false;
		}
		if (!(is_numeric($this->check_id))) {
			$this->error = "Invalid check id";
			return false;
		}

		$query = "UPDATE service_checks SET 
				name = '$this->name', 
				description = '$this->desc',
				device_id = '$this->device_id',
				template_id = '$this->template_id',
				arguments = '$this->arguments',
				check_interval = '$this->interval',
				key1 = '$this->key1',
				key2 = '$this->key2',
				notes = '$this->notes'
				WHERE check_id = '$this->check_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . " $query";;
			return false;
		}
		return $result;
	}

	function insert() {
	// Test mandatory fields
		if ($this->check_id != false) {
			$this->error = "This is an insert, check_id should be empty";
			return false;
		} 
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!is_numeric($this->device_id)) {
			$this->error = "invalid device";
			return false;
		}
		if (!is_numeric($this->template_id)) {
			$this->error = "invalid template";
			return false;
		}


		$query = "Insert into service_checks SET 
				name = '$this->name', 
				description = '$this->desc',
				device_id = '$this->device_id',
				template_id = '$this->template_id',
				arguments = '$this->arguments',
				check_interval = '$this->interval',
				key1 = '$this->key1',
				key2 = '$this->key2',
				notes = '$this->notes'
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
		if (!is_numeric($this->check_id)) {
			$this->error = "Invalid check id";
			return false;
		} 

		$query = "update service_checks set archived = '1' 
			where check_id = '$this->check_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}

		return true;
	}
	
	function get_last_events($max = 10) {
		// This will return the ID's of the last $max events for this check
		$events = array();
		$query = "Select event_id, status
			FROM  events
			WHERE
				check_id ='$this->check_id'
			Order By insert_date desc
			limit $max";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		} 
		while ($obj = mysql_fetch_object($result)){
			$events[$obj->event_id] = $obj->status;
		}
                return $events;
	}
	
	function parse_arguments() {
		$script_parsed = "$this->check_script $this->arguments";
		// replace the $KEY1$ and $KEY2$ args with the required values
		$script_parsed = $this->parse_key_args($script_parsed);
		// replace the device variables  with the required values
		$script_parsed = $this->parse_device_args($script_parsed);
		$this->check_script_parsed = $script_parsed;
	}
	

	function parse_key_args($script) {

		// Start parsing
		if (strstr($script, '$KEY1$')) {
			$script = str_replace('$KEY1$', $this->key1, "$script");
		} else {
		}
		if (strstr($script, '$KEY2$')) {
			$script = str_replace('$KEY2$', $this->key2, "$script");
		}
		return $script;
	}

	function parse_device_args($script) {
		if(!is_numeric($this->device_id)) {
			return $script;
		}
		$device = new Device($this->device_id);
		// Start parsing
		if (strstr($script, '$HOSTADDRESS$')) {
			$fqdn = $device->get_device_fqdn();
			$script = str_replace('$HOSTADDRESS$', $fqdn, "$script");
		} else {
		}
		if (strstr($script, '$SNMP_RO$')) {
			$snmp = $device->get_snmp_ro();
			$script = str_replace('$SNMP_RO$', $snmp, "$script");
		}
		return $script;

	}

}

class CheckTemplate {
	private $template_id = false;
	private $name;
	private $description;
	private $notes;
	private $script;
	private $key1_name;
	private $key2_name;

	private $error = false;
	
	function __construct($template_id = '') {
		if (is_numeric($template_id)) {
			$this->get_template_info($template_id);
		}
	}

	protected function get_template_info($template_id){
		$query = "SELECT 
				template_id,
				key1_name,
				key2_name,
				name,
				description,
				script,
				notes

			FROM 
				service_checks_template
			WHERE
				template_id = '$template_id' 
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

 		$obj = mysql_fetch_object($result);
		$this->template_id = $obj->template_id;
		$this->name = $obj->name;
		$this->description = $obj->description;
		$this->notes = $obj->notes;
		$this->script = $obj->script;
		$this->key1_name = $obj->key1_name;
		$this->key2_name = $obj->key2_name;

		return true;
	}

	public function get_templates() {
		$templates = array();
		$query = "SELECT template_id, name 
			FROM service_checks_template";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$templates[$obj->template_id] = $obj->name;
		}
		return $templates;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_template_id() {
		return $this->template_id;
	}
	function get_name() {
		return $this->name;
	}

	function get_script() {
		return $this->script;
	}

	function get_key1_name() {
		return $this->key1_name;
	}
	function get_key2_name() {
		return $this->key2_name;
	}
	function get_desc() {
		return $this->description;
	}
	function get_notes() {
		return $this->notes;
	}

	function set_name($value) {
		$this->name = $value;
	}
	function set_script($value) {
		$this->script = $value;
	}
	function set_desc($value) {
		$this->desc = $value;
	}
	function set_notes($value) {
		$this->notes = $value;
	}
	function set_key1_name($value) {
		$this->key1_name= $value;
	}
	function set_key2_name($value) {
		$this->key2_name = $value;
	}

	function update() {
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->template_id))) {
			$this->error = "Invalid template (should be an Integer)";
			return false;
		}
		if ($this->script == '') {
			$this->error = "Invalid script , can not be empty";
			return false;
		}

		$query = "UPDATE service_checks_template SET 
				name = '$this->name', 
				description = '$this->desc',
				notes = '$this->notes',
				script = '$this->script',
				key1_name = '$this->key1_name',
				key2_name = '$this->key2_name'
				WHERE template_id = '$this->template_id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . " $query";;
			return false;
		}
		return $result;
	}

	function insert() {
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if ($this->script == '') {
			$this->error = "Invalid script , can not be empty";
			return false;
		}

		$query = "INSERT INTO service_checks_template 
			SET 
				name = '$this->name', 
				description = '$this->desc',
				notes = '$this->notes',
				script = '$this->script',
				key1_name = '$this->key1_name',
				key2_name = '$this->key2_name'";
		// execute the query 
		$result =  mysql_query($query) ;
		$template_id = mysql_insert_id();
		if (!$result)  {
			$this->error = mysql_error() . " $query";;
			return false;
		}
		return $template_id;
	}

	function delete() {
		if ($this->template_id == '') {
			$this->error = "Invalid template_id id";
			return false;
		} 
		$query = "Delete FROM service_checks_template
			 where template_id = '$this->template_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}


}
class CheckReportProfile {
	private $profile_id = false;
	private $name;
	private $contact_id;
	private $notes;
	private $report_type;

	private $error = false;
	
	function __construct($profile_id = '') {
		if (is_numeric($profile_id)) {
			$this->get_profile_info($profile_id);
		}
	}

	protected function get_profile_info($template_id){
		$query = "SELECT 
				profile_id,
				name,
				contact_id,
				notes,
				report_type
			FROM 
				service_checks_profiles
			WHERE
				profile_id = '$template_id' 
		 ";
		// execute the query 
		$result =  mysql_query($query) ;
        	if (!$result)  {
        		$this->error = mysql_error() ."   -- query: $query ";
			print "$this->error <br>";
        		return false;
        	}
		if (mysql_numrows($result) < 1 ) {
        		$this->error = "No data found";
        		return false;
		}

 		$obj = mysql_fetch_object($result);
		$this->profile_id = $obj->profile_id;
		$this->name = $obj->name;
		$this->notes = $obj->notes;
		$this->contact_id = $obj->contact_id;
		$this->report_type = $obj->report_type;

		return true;
	}

	public function get_profiles() {
		$profiles = array();
		$query = "SELECT profile_id, name 
			FROM service_checks_profiles";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$profiles[$obj->profile_id] = $obj->name;
		}
		return $profiles;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_profile_id() {
		return $this->profile_id;
	}
	function get_name() {
		return $this->name;
	}

	function get_notes() {
		return $this->notes;
	}

	function get_contact_id() {
		return $this->contact_id;
	}
	function get_report_type() {
		return $this->report_type;
	}


	function set_name($value) {
		$this->name = $value;
	}

	function set_notes($value) {
		$this->notes = $value;
	}

	function set_contact_id($value) {
		if (is_numeric($value)) {
			$this->contact_id = $value;
			return true;
		} else {
			$this->error = "Invalid client id";
			return false;
		}
	}
	function set_report_type($value) {
		$this->report_type = $value;
	}

	function insert() {
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if ($this->report_type == '') {
			$this->error = "Invalid report_type , can not be empty";
			return false;
		}
		if ((!is_numeric($this->contact_id)) || (is_null($this->contact_id)))
			$sql_contact_id = "NULL";
		else {
			$sql_contact_id = "'$this->contact_id'";
		}

		$query = "INSERT INTO service_checks_profiles 
			SET 
				name = '$this->name', 
				notes = '$this->notes',
				report_type = '$this->report_type',
				contact_id = $sql_contact_id
		";
		// execute the query 
		$result =  mysql_query($query) ;
		$profile_id = mysql_insert_id();
		if (!$result)  {
			$this->error = mysql_error() . " $query";;
			return false;
		}
		return $profile_id;
	}

	function delete() {
		if ($this->profile_id == '') {
			$this->error = "Invalid profile id";
			return false;
		} 
		$query = "Delete FROM service_checks_profiles
			 where profile_id = '$this->profile_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}
	function update() {
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (!(is_numeric($this->profile_id))) {
			$this->error = "Invalid profile_id (should be an Integer)";
			return false;
		}
		if ($this->report_type == '') {
			$this->error = "Invalid report_type , can not be empty";
			return false;
		}
		if ((!is_numeric($this->contact_id)) || (is_null($this->contact_id)))
			$sql_contact_id = "NULL";
		else {
			$sql_contact_id = "'$this->contact_id'";
		}


		$query = "UPDATE service_checks_profiles SET 
				name = '$this->name', 
				notes = '$this->notes',
				report_type = '$this->report_type',
				contact_id = $sql_contact_id
			WHERE 
				profile_id = '$this->profile_id'
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . " $query";;
			return false;
		}
		return $result;
	}
	
	function get_checks() {
		if (!(is_numeric($this->profile_id))) {
			$this->error = "Invalid profile_id (should be an Integer)";
			return false;
		}
		$checks = array();

		$query = "SELECT 
				 service_checks_profiles_checks.service_check_id,
				 service_checks.name
			FROM
				service_checks_profiles_checks, service_checks
			WHERE 
				report_id = '$this->profile_id'
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . " $query";;
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$checks[$obj->service_check_id] = $obj->name;
		}
		return $checks;
	}

	function add_check($check_id) {
		if (!(is_numeric($this->profile_id))) {
			$this->error = "Invalid profile_id (should be an Integer)";
			return false;
		}
		if (!(is_numeric($check_id))) {
			$this->error = "Invalid check_id (should be an Integer)";
			return false;
		}

		$query = "INSERT INTO service_checks_profiles_checks
			  SET
				 service_check_id = '$check_id',
				 report_id = '$this->profile_id'
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . " $query";;
			return false;
		}
		return mysql_affected_rows();
	}
	
	function delete_check($check_id) {
		if (!(is_numeric($this->profile_id))) {
			$this->error = "Invalid profile_id (should be an Integer)";
			return false;
		}
		if (!(is_numeric($check_id))) {
			$this->error = "Invalid check_id (should be an Integer)";
			return false;
		}

		$query = "DELETE FROM service_checks_profiles_checks
			  WHERE
				 service_check_id = '$check_id' AND
				 report_id = '$this->profile_id'
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . " $query";;
			return false;
		}
		return mysql_affected_rows();
	}
	
	
}

class Report {
	private $report_id = false;
	private $name;
	private $report_type;
	private $profile_id = false;
	private $start_time;
	private $end_time;
	private $ok =0;
	private $warning =0;
	private $critical =0;
	private $unknown =0;
	private $other =0;
	private $no_data =0;

	private $error = false;
	
	function __construct($report_id = '') {
		if (is_numeric($report_id)) {
			$this->get_report_info($report_id);
		}
	}

	protected function get_report_info($report_id){
		$query = "SELECT 
				report_id,
				report_name,
				profile_id,
				ok,
				warning,
				critical,
				unknown,
				other,
				no_data,
				start_time,
				end_time
			FROM 
				service_checks_reports
			WHERE
				report_id = '$report_id' 
		 ";
		// execute the query 
		$result =  mysql_query($query) ;
        	if (!$result)  {
        		$this->error = mysql_error() ."   -- query: $query ";
			print "$this->error <br>";
        		return false;
        	}
		if (mysql_numrows($result) < 1 ) {
        		$this->error = "No data found";
        		return false;
		}

 		$obj = mysql_fetch_object($result);
		$this->report_id = $obj->report_id;
		$this->name = $obj->report_name;
		$this->profile_id = $obj->profile_id;
		$this->report_type = $obj->report_type;
		$this->ok = $obj->ok;
		$this->warning = $obj->warning;
		$this->critical = $obj->critical;
		$this->unknown = $obj->unknown;
		$this->no_data = $obj->no_data;
		$this->other = $obj->other;
		$this->start_time = $obj->start_time;
		$this->end_time = $obj->end_time;
		return true;
	}

	public function get_report_names() {
		$report_names = array();
		$query = "SELECT distinct report_name 
			FROM service_checks_reports";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			array_push($report_names,$obj->report_name);
		}
		return $report_names;
	}

	public function get_reports_by_name($report_name ='') {
		$reports = array();
		$query = "SELECT report_id, profile_id 
			FROM service_checks_reports
			WHERE report_name = '$report_name'";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$reports[$obj->report_id] = $obj->profile_id;
		}
		return $reports;
	}


	// Get Functions
	function get_error() {
		return $this->error;
	}

	function get_report_id() {
		return $this->report_id;
	}
	function get_name() {
		return $this->name;
	}

	function get_profile_id() {
		return $this->profile_id;
	}
	function get_report_type() {
		return $this->report_type;
	}
	function get_start_time() {
		return $this->start_time;
	}
	function get_end_time() {
		return $this->end_time;
	}
	function get_ok_secs() {
		return $this->ok;
	}
	function get_warning_secs() {
		return $this->warning;
	}
	function get_critical_secs() {
		return $this->critical;
	}
	function get_unknown_secs() {
		return $this->unknown;
	}
	function get_other_secs() {
		return $this->other;
	}
	function get_no_data_secs() {
		return $this->no_data;
	}
	function get_up_percentage() {
		if ((strtotime($this->end_time))) {
			$end_time  = strtotime($this->end_time);
		} else {
			return false;
		}

		if ((strtotime($this->start_time))) {
			$start_time  = strtotime($this->start_time);
		} else {
			return false;
		}
		$total_secs = $end_time - $start_time;
		$perc = $this->ok / $total_secs;
		return $perc *100;
	}


	function set_name($value) {
		$this->name = $value;
	}

	function set_profile_id($value) {
		if (is_numeric($value)) {
			$this->profile_id = $value;
			return true;
		} else {
			$this->error = "Invalid profile id";
			return false;
		}
	}
	function set_report_type($value) {
		$this->report_type = $value;
	}
	function set_start_time($value) {
		if ((is_numeric($value))&&(date($value))) {
			$this->start_time = date($value);
		}
		elseif (($value != '') && (strtotime($value))) {
			$this->start_time  = strtotime($value);
		} else {
			$this->error =  "Invalid start time $value";
			return false;
		}
		return true;
	}

	function set_end_time($value) {
		if ((is_numeric($value))&&(date($value))) {
			$this->end_time = date($value);
		}
		elseif (($value != '') && (strtotime($value))) {
			$this->end_time  = strtotime($value);
		} else {
			$this->error =  "Invalid end time $value";
			return false;
		}
		return true;
	}
	function set_ok_secs($value) {
		if (is_numeric($value)) {
			$this->ok = $value;
			return true;
		} else {
			$this->error = "Invalid ok seconds value ";
			return false;
		}
	}
	function set_warning_secs($value) {
		if (is_numeric($value)) {
			$this->warning = $value;
			return true;
		} else {
			$this->error = "Invalid warning seconds value ";
			return false;
		}
	}
	function set_critical_secs($value) {
		if (is_numeric($value)) {
			$this->critical = $value;
			return true;
		} else {
			$this->error = "Invalid critical seconds value ";
			return false;
		}
	}
	function set_unknown_secs($value) {
		if (is_numeric($value)) {
			$this->unknown= $value;
			return true;
		} else {
			$this->error = "Invalid unknown seconds value ";
			return false;
		}
	}
	function set_no_data_secs($value) {
		if (is_numeric($value)) {
			$this->no_data= $value;
			return true;
		} else {
			$this->error = "Invalid no_data seconds value ";
			return false;
		}
	}
	function set_other_secs($value) {
		if (is_numeric($value)) {
			$this->other =  $value;
			return true;
		} else {
			$this->error = "Invalid other seconds value ";
			return false;
		}
	}
		

	function insert() {
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if ($this->report_type == '') {
			$this->error = "Invalid report_type , can not be empty";
			return false;
		}
		if (!is_numeric($this->profile_id)) {
			$this->error = "Invalid profile_id";
			return false;
		}

		$query = "INSERT INTO service_checks_reports  SET
			report_name = '$this->name',
			profile_id = '$this->profile_id',
			ok = '$this->ok',
			warning = '$this->warning',
			critical = '$this->critical',
			unknown = '$this->unknown',
			other = '$this->other',
			no_data = '$this->no_data',
			start_time = FROM_UNIXTIME('$this->start_time'),
			end_time = FROM_UNIXTIME('$this->end_time'),
			report_type = '$this->report_type',
			create_time = NOW()
		";
		// execute the query 
		$result =  mysql_query($query) ;
		$report_id = mysql_insert_id();
		if (!$result)  {
			$this->error = mysql_error() . " $query";
			return false;
		}
		return $report_id;
	}

	function delete() {
		if ($this->report_id == '') {
			$this->error = "Invalid report_id ";
			return false;
		} 
		$query = "Delete FROM service_checks_reports
			 where report_id = '$this->report_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}
}
?>

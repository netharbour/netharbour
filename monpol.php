#!/usr/bin/env php
<?php

// Check Options
$options = getopt("i:");
if (is_numeric($options['i'])) {
	$check_id = $options['i'];
} else {
	print "Error: Please specify a valid check_id\n".
 	"usage: ./". $_SERVER["SCRIPT_NAME"] ." -i 22\n";
	exit(1);

}

// Include required files and classes;
include_once "config/opendb.php";
include_once "classes/Event.php";
include_once "classes/Check.php";
include_once "classes/Property.php";

// Base dir for scripts
$script_dir = "check-scripts";

// Get check info 
// Create check object
$check = new Check($check_id);

// Start timer and exec check script
$time_start = microtime(true);
$script = "$script_dir/". $check->get_parsed_check_script();
$check_result = exec_check($script);

// Now create a new Event object
// Store the info we know from Check object
// and check results

$event = new Event();
$event->set_status($check_result{'status'});
$event->set_info_msg($check_result{'message'});
$event->set_performance_data($check_result{'perf_data'});
$event->set_check_id($check->get_check_id());
$event->set_script($script);
$event->set_check_name($check->get_name());
$event->set_hostname($check->get_hostname());
$event->set_key1($check->get_key1());
$event->set_key2($check->get_key2());

// Now  we can use handle_event() to figure out if this is a new
// or exitsing event. It will do all the updating for us
// and call notify() in the event class
$event->handle_event();
$time_end = microtime(true);
$time = $time_end - $time_start;

//print status line
print "$script => status: ". $event->get_status(). "  ". trim($event->get_info_msg()) ;
if ($check_result{'perf_data'} != ""){
	print " | ".$check_result{'perf_data'} ;
} 
print " -- ($time sec)\n";

// This function does the actual execution
function exec_check($script) {
	$return_var = 3;
	$error = false;

	// first check if script exist
	list($filename) = ( split(" ", $script));
	if (file_exists($filename)) {
		if (! is_executable  ($filename)) {
			$error = true;
			$return_var = 3;
			$message = "file is not executable";
		}
	} else {
		$error = true;
		$return_var = 3;
		$message = "the file $filename does not exist";
	}

	if ($error) {
		return array(
			'status' => $return_var,
			'message' => $message,
		);
	}
	$e = escapeshellcmd($script);
	$f =exec("$e 2>&1 ", $output, $return_var);
	$message = implode("\n",$output) ; 
	// array to string
	// If return status > 3, means invalid status, rewrite to unknown (3)
	if ((!is_numeric($return_var)) || ($return_var > 3)) {
		//print "rewrte retrun var";
		$return_var = 3;
	}

	// Now we need to parse the $message and filter out performance data
	// Message can have multiple perf_data fields
	// perf data always comes after a | sign
	// 'label'=value[UOM];[warn];[crit];[min];[max] 
	// For now we don't implement [warn];[crit];[min];[max] , just the data
	// UOM (unit of measurement) is one of:
	// no unit specified - assume a number (int or float) of things (eg, users, processes, load averages)
	//  s - seconds (also us, ms)
	//  % - percentage
	//  B - bytes (also KB, MB, TB)
	//  c - a continous counter (such as bytes transmitted on an interface)
	// 
	// Performance data should be submitted as comma white space seperated list ", "
	// example: percent_packet_loss=0, rta=0.80
	

	$mdata = "";
	$pdata = null;
	$expoded_data = explode("\n",$message);
	// first split up possible multiline output in individual lines
	foreach ($expoded_data as $substr) {
	// Now split message part from perf data
		list($msg,$perfdata)  =  preg_split("/\|/",$substr);
		$msg = trim($msg);
		if ($msg != '') { $mdata .= trim($msg) ."\n"; }

		// Now splitup perfdata string so that it's in correct format
		// with ", " as delimeter. That's default for most scripts, but might need
		// reformatting when it's on multiple lines.
		$perfdata = trim($perfdata);
		$keywords = preg_split("/,/",$perfdata, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($keywords as $k => $word) {
			$word = trim($word);
			$pdata .= "$word, ";
		}
	}

	// remove last ", " from pdata
	if (!is_null($pdata)) { $pdata = preg_replace("/, $/", "", $pdata); }
	$mdata = preg_replace("/\n$/", "", $mdata);
	return array(
		'status' => $return_var,
		'message' => $mdata,
		'perf_data' => $pdata,
	);
}

/*
function get_check_info($check_id) {
	global $script_dir;
	// Initialize event array
	$event_obj = new Event();
	$event = array(
		"script" => "",
		"check_name" => "", // Check name
		"check_id" => false,
		"host" => "",
		"key1" => false,
		"key2" => false,
		"device_id" => false,
		"status" => 3,
		"message" => "",
	);


	$query = "SELECT 
		service_checks.check_id, 
		service_checks.device_id, 
		service_checks.template_id,
		service_checks.arguments,
		service_checks.key1,
		service_checks.key2,
		service_checks.interval,
		service_checks_template.name,
		service_checks_template.desc,
		service_checks_template.script
	FROM 
		service_checks,
		service_checks_template
	WHERE
		service_checks_template.template_id = service_checks.template_id
		AND service_checks.check_id = '$check_id'
	";

	print "$query\n";
	// execute the query 
	$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
	
	while ($obj = mysql_fetch_object($result)){
	
		$script = "$obj->script $obj->arguments";
		print "ANDREE script is $script \n";
		// replace the $KEY1$ and $KEY2$ args with the required values
		$script_parsed = parse_key_args($obj->key1,$obj->key2,$script);
		// replace the device variables  with the required values
		$script_parsed = parse_device_args($obj->device_id,$script_parsed);
	
		// Need to know host name
		$device = new Device($obj->device_id);
		$hostname = false;
		$hostname = $device->get_name();

		// Fill event hash
		$event[script] = "$script_dir/$script_parsed";
		$event_obj->set_script("$script_dir/$script_parsed");

		$event[host] = $hostname;
		$event_obj->set_hostname($hostname);

		$event[check_name] = $obj->name;
		$event_obj->set_check_name($name);

		$event[device_id] = $obj->device_id;
		if ($obj->check_id) {
			$event[check_id] = $obj->check_id;
			$event_obj->set_check_id($obj->check_id);
		}
		if ($obj->key1) {
			$event[key1] = $obj->key1;
			$event_obj->set_key1($obj->key1);
		}
		if ($obj->key2) {
			$event[key2] = $obj->key2;
			$event_obj->set_key2($obj->key2);
		}
	}
	#return $event;
	return $event_obj;
}
	
function exec_check($script) {
	$return_var = 3;
	$error = false;

	// first check if script exist
	list($filename) = ( split(" ", $script));
	if (file_exists($filename)) {
		if (! is_executable  ($filename)) {
			$error = true;
			$return_var = 3;
			$message = "file is not executable";
		}
	} else {
		$error = true;
		$return_var = 3;
		$message = "the file $filename does not exist";
	}

	if ($error) {
		return array(
			status => $return_var,
			message => $message,
		);
	}

	$e = escapeshellcmd($script);
	$f =exec("$e 2>&1' ", $output, $return_var);
	$message = implode("\n",$output) ; 
	// array to string
	return array(
		status => $return_var,
		message => $message,
	);
}


function handle_event($check,$check_result) {
	$event_id = false;
	$event = new Event();
	
	// Now set all we know
	//$event_id = event_exists($event[check_id],$event[status],$event[$message],$event[device_id]);
	$event_id = event_exists($event);
	if ($event_id) {
		// exitsing event
		// do update
		update_event($event_id,$event);
	} else {
		// New event , i.e. state change
		// Insert new one and update old one
		clear_event($event);
		//clear_event($check_id,$event[status],$event[message],$event[device_id]);
		$event_id = insert_event($event);
	}
	// Update Service checks table 
	// So that we can get current status easily
	if ($event_id === false) {
		error_log("Event_id is not set... unknow state.., bailing out\n");
	} else {
		update_service_checks($event[check_id],$event_id);
	}
}


function event_exists($event) {
	// Query depends on if there's a event id
	// If there's an event id then use that as key
	// else use hostname, check_name, key1 key2 as keys
	
	if ($event[check_id] === false) {
		$query_where = " (host_name = '$event[host]' AND
                 check_name = '$event[check_name]' AND
                 key1 = '$event[key1]' AND
                 key2 = '$event[key2]')
		";
	} else {
		$query_where = "check_id = '$event[check_id]'";
	}
	$alert_query = " Select event_id FROM events
		WHERE
		 $query_where AND
		 status = '$event[status]' AND
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
		$obj = mysql_fetch_object($result);
		return $obj->event_id;
	} else {
		error_log("Undefined state in event_exists()");
		return false;
	}
}


function clear_event($event) {
	// Query depends on if there's a event id
	// If there's an event id then use that as key
	// else use hostname, check_name, key1 key2 as keys
	if ($event[check_id] === false) {
		$query_where = " (host_name = '$event[host]' AND
                 check_name = '$event[check_name]' AND
                 key1 = '$event[key1]' AND
                 key2 = '$event[key2]')";
	} else {
		$query_where = "check_id = '$event[check_id]'";
	}
	$alert_query = "update events
		SET 
		 active = '0',
		 last_updated = NOW()
		WHERE
		$query_where AND
		 active = '1';
		";
	$result = mysql_query($alert_query) or error_log('Error, query failed. ' . mysql_error() ." $alert_query");
	return $result;
}

function insert_event($event) {
	$alert_query = " INSERT INTO events
		SET
		 check_id = '$event[check_id]',
		 host_name = '$event[host]',
		 check_name = '$event[check_name]',
		 key1 = '$event[key1]',
		 key2 = '$event[key2]',
		 status = '$event[status]',
		 info_msg = '$event[message]',
		 script = '$event[script]',
		 insert_date = NOW(),
		 last_updated = NOW(),
		 active = '1';
	";
	$result = mysql_query($alert_query) or error_log('Error, query failed. ' . mysql_error() ." $alert_query");
	return mysql_insert_id();
}

function update_event($event_id, $event) {
	$alert_query = " 
	UPDATE events SET
	  	last_updated = NOW(),
		info_msg = '$event[message]',
		script = '$event[script]'
	WHERE
		event_id = '$event_id'
	";
	
	$result = mysql_query($alert_query) or error_log('Error, query failed. ' . mysql_error());
}

function update_service_checks($check_id,$event_id) {
	$check_query = " UPDATE service_checks
	 SET
	 last_event_id = '$event_id',
	 last_check = NOW()
	 WHERE
	check_id = '$check_id'";
	$result = mysql_query($check_query) or error_log('Error, query failed. ' . mysql_error());
}


function parse_key_args($key1,$key2,$args) {

	// Start parsing
	if (strstr($args, '$KEY1$')) {
		$args = str_replace('$KEY1$', $key1, "$args");
	} else {
	}
	if (strstr($args, '$KEY2$')) {
		$args = str_replace('$KEY2$', $key2, "$args");
	}

	return $args;
}

function parse_device_args($device_id,$args) {
	if(!is_numeric($device_id)) {
		return $args;
	}
	$device = new Device($device_id);

	// Start parsing
	if (strstr($args, '$HOSTADDRESS$')) {
		$fqdn = $device->get_device_fqdn();
		$args = str_replace('$HOSTADDRESS$', $fqdn, "$args");
	} else {
	}
	if (strstr($args, '$SNMP_RO$')) {
		$snmp = $device->get_snmp_ro();
		$args = str_replace('$SNMP_RO$', $snmp, "$args");
	}

	return $args;

}
*/
?>

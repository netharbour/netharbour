<?php

// Include required files and classes;
include_once "config/opendb.php";
include_once "classes/Event.php";
include_once "classes/Check.php";

// You can do either a GET or POST
//we just opy the data from GET or POST into mydata
$mydata = array();

if ((isset($_GET['action'])) && ($_GET['action']=='submit_event')) {
	$mydata = $_GET;
}
elseif ((isset($_POST['action'])) &&( $_POST['action']=='submit_event')) {
	$mydata = $_POST;
} else {
	// raise warning;
	//print "Invalid request\n";
	$msg = "Invalid request, please specify action";
	header("HTTP/1.0 400 $msg");
	print $msg;
	exit;
}

// New verify if we have enough info

if (! is_numeric($mydata['status'])) {
	$msg = "Invalid request: status not numeric";
	header("HTTP/1.0 400 $msg");
	print $msg;
	exit;
} else {
	// If return status > 3, means invalid status, rewrite to unknown (3)
	if ($mydata['status'] > 3) {
		//print "Warning rewrote status value from ". $mydata['status'] ." to 3\n";
		$mydata['status'] = 3;
	}
}

$event = new Event();
$event->set_status($mydata['status']);
$event->set_info_msg($mydata['message']);
$event->set_script("Submitted Check");
if (is_numeric($mydata['check_id'])) {
	// if this is the case we have the check_id that makes live easy.
	$event->set_check_id($mydata['check_id']);
	$event->set_check_name($mydata['checkname']);
	$event->set_hostname($mydata['hostname']);

} else {
	// Then we need hostname with checkname and keys
	if ((!isset($mydata['hostname'])) || ($mydata['hostname'] == '') || (!isset($mydata['checkname'])) || ($mydata['checkname'] == '')) {
		$msg = "Invalid request: Expecting either check_id or hostname AND checkname";
		header("HTTP/1.0 400 $msg");
		print $msg;
		exit;
	}
	//$event->set_check_id(null);
	$event->set_check_name($mydata['checkname']);
	$event->set_hostname($mydata['hostname']);

	$event->set_key1($mydata['key1']);
	$event->set_key2($mydata['key2']);
}

// Parse message data, to extract message and performance data
$msg_data = parse_perf_data($mydata['message']);
$event->set_info_msg($msg_data{'message'});
$event->set_performance_data($msg_data{'perf_data'});


// Now  we can use handle_event() to figure out if this is a new
// or exitsing event. It will do all the updating for us
// and call notify() in the event class

$status = $event->handle_event();

//print status line
$resp_mesg = "Successfull $status for event_id: ". $event->get_event_id()  ;
//header("HTTP/1.0 200 OK $resp_mesg");
print $resp_mesg;




function parse_perf_data($message) {

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
		'message' => $mdata,
		'perf_data' => $pdata,
	);
}
?>

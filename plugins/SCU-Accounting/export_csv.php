<?
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['password']))
{
        header("Location: ../../login.php");
}


$ini_array = parse_ini_file("../../config/cmdb.conf");
$dbhost = $ini_array['db_host'];
$dbport = $ini_array['db_port'];
$dbuser = $ini_array['db_user'];
$dbpass = $ini_array['db_pass'];
$dbname = $ini_array['db_name'];

$conn = mysql_connect("$dbhost:$dbport", $dbuser, $dbpass) or die  ('Error connecting to mysql');
mysql_select_db($dbname);

include_once '../../classes/Contact.php';


$file_name = trim($_GET['report_name']) . ".csv";
header("Content-type:text/octect-stream");
header("Content-Disposition:attachment;filename=\"$file_name\"");

$csv_data = export_csv();
print $csv_data ;

function export_csv() {
	if ((isset($_GET['report_name'])) && ($_GET['report_name'] != '')) {
			$name = $_GET['report_name'];
	} else {
		return "<b>Sorry invalid report name ". $_GET['report_name'] ."</b>";
	}

	$query = "select accounting_reports.id, accounting_reports.profile_id, accounting_reports.report_name, 
			accounting_reports.date1, accounting_reports.date2, 
			accounting_reports.avg_in, accounting_reports.avg_out, 
			accounting_reports.max_in, accounting_reports.max_out,95_in as in95,
			accounting_reports.95_out as out95 , accounting_reports.tot_in, accounting_reports.tot_out,
			accounting_reports.sample_date1,   accounting_reports.sample_date2, 
			accounting_reports.traffic_cap, accounting_profiles.client_id
			from 
			accounting_reports, accounting_profiles where report_name = '$name'
			AND accounting_profiles.profile_id =  accounting_reports.profile_id";
	$result =  mysql_query($query) ;
	if (!$result)  {
		return "failed to execute query $query<br>";
	}
	$heading = array( "Client","Accounting Profile", "Report Name", "Start date", "End date", 
		"Average In (b/s)", "Average out (b/s)", "Max In (b/s)", "Max out (b/s)", "95% In (b/s)",
		"95% out (b/s)","95% Billing (b/s)","95% Billing (Mb/s)","Committed Traffic (b/s)","Commitment Status","Total In (Bytes)","Total out (Bytes)","First Sample date",
		"Last Sample date");
	$content = implode(",", $heading);
	$content .= "\n";

	while ($obj = mysql_fetch_object($result)){
		$data = array();
		$pid = $obj->profile_id;
		if ($obj->in95 > $obj->out95) {
			$bill95 = $obj->in95;
		} else {
			$bill95 = $obj->out95;
		}
		// Create Mb/s number
		$bill95_mb = round($bill95 / (1000 * 1000),2);

		// get client name
		$contact_name = "N/A";
		$client_id = $obj->client_id;
		if (!is_null($client_id)) {
			$contact = new Contact($client_id);
			$contact_name = $contact->get_name();
			if ($contact_name != '') {
			} else {
				$contact_name = "N/A";
			}
		}
		if (!$obj->traffic_cap) {
			$cap_ok = "N/A";
		} elseif ($bill95 > $obj->traffic_cap) {
			$cap_ok = "Over Commitment";
		} else {
			$cap_ok = "Under Commitment";
		}
		$profile_name =  get_accounting_profile_name($pid);

		array_push($data,
			$contact_name,$profile_name, $obj->report_name, $obj->date1, $obj->date2, $obj->avg_in, $obj->avg_out, $obj->max_in, $obj->max_out,
			$obj->in95, $obj->out95, $bill95, $bill95_mb, $obj->traffic_cap, $cap_ok, $obj->tot_in, $obj->tot_out,
			$obj->sample_date1, $obj->sample_date2);
		$content .= stripslashes(implode(",",$data));
		$content .= "\n";
	}
	return $content;
}

function get_accounting_profile_name($profile_id) {
	$query = "select title from accounting_profiles
		WHERE profile_id = '$profile_id'";
	$result =  mysql_query($query) ;
	if (!$result)  {
		print "failed to execute query $query<br>";
		return false;
	}
	$obj = mysql_fetch_object($result);
	return $obj->title;
}

?>

<?
/*
 Andree Toonk, Feb 2, 2012
 This scripts exports all contacts for each groups in a CSV format.
 It's created so we can automatically create the outage email  list
*/
header("Content-Type: text/plain");
header("Pragma: no-cache");
header("Expires: 0"); 

$ini_array = parse_ini_file("../../config/cmdb.conf");
$dbhost = $ini_array['db_host'];
$dbport = $ini_array['db_port'];
$dbuser = $ini_array['db_user'];
$dbpass = $ini_array['db_pass'];
$dbname = $ini_array['db_name'];

$conn = mysql_connect("$dbhost:$dbport", $dbuser, $dbpass) or die  ('Error connecting to mysql');
mysql_select_db($dbname);

include_once '../../classes/Contact.php';

print "#\"Contact Name\",\"BCNET member Id\",\"Contact Type ID\",\"Contact Type\",\"Person Name\",\"Person Email\",\"Person Type ID\",\"Person Type\"\n";
$groups = Contact::get_groups();
foreach ($groups as $g_id => $value) {
	$g = null;
	$g = new Contact($g_id);
	//print_r($g);
	$group_type_name = $g->get_group_type_name();
	$group_type_id   = $g->get_group_type();
	$bcnet_client_id = $g->get_custom_client_id();
	$contacts = $g->get_contacts();
	//print_r($contacts);
	foreach ($contacts as $c_id => $c_array) {
		$p = null;
		$p = new Person($c_array['contact_id']);
		if ($p->get_error) {
			continue;
		}
		print "\"$value\",\"". $bcnet_client_id ."\",\"". $group_type_id ."\",\"". $group_type_name ."\",\"". $c_array['contact_name'] 
			."\",\"". $p->get_email() ."\",\"". $c_array['contact_type_id']."\",\"".$c_array['contact_type']."\"\n";
	}
}

?>

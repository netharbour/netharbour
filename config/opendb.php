<?php

$ini_array = parse_ini_file("config/cmdb.conf");
$dbhost = $ini_array['db_host'];
$dbport = $ini_array['db_port'];
$dbuser = $ini_array['db_user'];
$dbpass = $ini_array['db_pass'];
$dbname = $ini_array['db_name'];


$conn = mysql_connect("$dbhost:$dbport", $dbuser, $dbpass) or die  ('Error connecting to mysql' . mysql_error());
mysql_select_db($dbname);
//error_reporting(100);

?>

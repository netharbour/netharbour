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

include_once '../../plugins/IP Manager/IP_Database.php';
include_once '../../plugins/IP Manager/Vlan_manager.php';
include_once '../../classes/PopLocations.php';
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

	if(isset($_GET['vlan_id']))
	{
		if($_GET['vlan_id'] == 'showALL')
		{
			$vlan_manager = new Vlan_database();
			$all_vlans = $vlan_manager->get_all_vlans();
			$num_max = 4096;
			
			$heading = array("VLAN ID", "Name", "Status", "Location", "Assigned To", "VLAN Distinguisher");
			$content = implode(",", $heading);
			$content .= "\n";
			
			$arr = array();
			foreach ($all_vlans as $id => $v_id)
			{
				$arr[$v_id] = $id;
			}
			
			for ($i=1; $i<$num_max; $i++)
			{
				$data = array();
				if(in_array($i, $all_vlans))
				{
					foreach ($all_vlans as $id => $v_id)
					{
						if ($v_id == $i)
						{
							$my_vlan = new Vlan_database($id);
							if($my_vlan->get_id() !== NULL)
							{
								$data = array();
								array_push($data, $my_vlan->get_vlan_id());
								array_push($data, $my_vlan->get_name());
								array_push($data, $my_vlan->get_status());
								array_push($data, $my_vlan->get_location_name());
								array_push($data, $my_vlan->get_assigned_to_name());
								array_push($data, $my_vlan->get_vlan_distinguisher());
								$content .= stripslashes(implode(",",$data));
								$content .= "\n";
							}	
						}
					}
				}
				else
				{
					//if it doesn't create a free vlan
					array_push($data, $i);
					array_push($data, "");
					array_push($data, "FREE");
					array_push($data, "");
					array_push($data, "");
					array_push($data, "");
					$content .= stripslashes(implode(",",$data));
					$content .= "\n";
				}	
			}
		}
		else
		{
			$results = Vlan_database::Search($_GET['vlan_id'], $_GET['name'], $_GET['status'], $_GET['location'], $_GET['assign'], $_GET['distinguish'], $_GET['notes']);
			$heading = array("VLAN ID", "Name", "Status", "Location", "Assigned To", "VLAN Distinguisher");
			$content = implode(",", $heading);
			$content .= "\n";
			
			foreach ($results as $v_id =>$vlan_id) {
				$data = array();
				$my_vlan = new Vlan_database($v_id);
						   
				array_push($data, $my_vlan->get_vlan_id());
				array_push($data, $my_vlan->get_name());
				array_push($data, $my_vlan->get_status());
				array_push($data, $my_vlan->get_location_name());
				array_push($data, $my_vlan->get_assigned_to_name());
				array_push($data, $my_vlan->get_vlan_distinguisher());
				
				$content .= stripslashes(implode(",",$data));
				$content .= "\n";	   
			}
		}
	}
	else
	{
		$title = $_GET['title'];
		$str_tag = $_GET['tags'];
		while (strpos($str_tag, ", "))
		{
			$str_tag = str_replace(", ", ",", $str_tag);
		}
		$tags = explode(",", $str_tag);
		
		if(count($tags) == 1 && $tags[0] == "")
		{
			$tags = array();
		}
		$location = $_GET['location'];
		$owner = $_GET['owner'];
		$assigned_to = $_GET['assigned'];
		$status = $_GET['status'];
		
		
		$IP_Search = new IP_Database();
		$s_results = $IP_Search->search($title, $tags, $location, $owner, $assigned_to, $status);
		
		$heading = array("Master Block", "Prefix", "Length", "Family", "Status", "Location", "Owner", "Assigned To", "Tags");
		$content = implode(",", $heading);
		$content .= "\n";
		
		foreach ($s_results as $id =>$n_name) {
			$data = array();
			$my_netblock = new IP_Database($id);
			
			$master_id = IP_Database::get_master($id);
			$master_block = new IP_Database($master_id);
			
			$ip = explode("/", $my_netblock->get_address_ip());
			
			array_push($data, $master_block->get_address_ip());
			array_push($data, $ip[0]);
			array_push($data, $my_netblock->get_subnet_size());
			array_push($data, $my_netblock->get_family());
			array_push($data, $my_netblock->get_status());
			array_push($data, $my_netblock->get_location_name());
			array_push($data, $my_netblock->get_owner_name());
			array_push($data, $my_netblock->get_assigned_to_name());
			
			$tags = $my_netblock->get_tags();
			$n_tags = stripslashes(implode(" ",$tags));
			array_push($data, $n_tags);
			
			$content .= stripslashes(implode(",",$data));
			$content .= "\n";
		}
	}
		
	return $content;
}

?>

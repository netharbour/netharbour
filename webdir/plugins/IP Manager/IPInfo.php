<?
if (!isset($_GET['all_tags']))
{
?>
<style>
#head {
	font-size:14px;
}

#sub {
	font-size: 12px;
	color:#666;	
}

.box {
	width: 30%;
	border-left:#CCC solid thin;
	padding-left: 10px;		
	padding-right: 10px;
	float:left;
}

.desc {
	width: 90%;
	padding-left: 10px;		
	padding-right: 10px;
	float:left;
}

#left {
	margin-top: 5px;
	float:left;
	width:25%;
	min-width:150px;
	border-right:#999 thin solid;
}

#right {
	margin-top: 5px;
	float:left;
	width:60%;
}
#left img, #right img {
	width:15px;
	height:15px;	
}

</style>
<?
}
?>

<?
include_once 'Netblock.php';
include_once 'IP_Database.php';
include_once '../../classes/PopLocations.php';
include_once '../../classes/Contact.php';

// Opend DB
$ini_array = parse_ini_file("../../config/cmdb.conf");
$dbhost = $ini_array['db_host'];
$dbport = $ini_array['db_port'];
$dbuser = $ini_array['db_user'];
$dbpass = $ini_array['db_pass'];
$dbname = $ini_array['db_name'];

$conn = mysql_connect("$dbhost:$dbport", $dbuser, $dbpass) or die  ('Error connecting to mysql');
mysql_select_db($dbname);
if (isset($_GET['getIPInfo']))
{
	$ip_id = explode("@", $_GET['getIPInfo']);
	$netblock = new Netblock($ip_id[0]);
	$ip_db = new IP_Database($ip_id[1]);
	
	$color="black";
	$status = $ip_db->get_status();
	switch ($status)
	{
		case "FREE";
		$color = "green";
		break;
			
		case "RESERVED";
		$color = "blue";
		break;
			
		case "ASSIGNED";
		$color = "red";
		break;
	}
	
	if(isset($_GET['action']))
	{
		if($_GET['action'] == 'calc')
		{
			print "<span id='head' style='color:".$color.";'>".$netblock->get_IP()."</span><br / > ";
			print "<div class = 'box'>Subnet mask = <span id='sub'>".$netblock->get_netmask()."</span></div>";
			print "<div class = 'box'>Wildcard = <span id='sub'>".$netblock->get_wildcard()."</span></div>";
			print "<div class = 'box'>Network = <span id='sub'>".$netblock->get_network()."</span></div>";
			print "<div class = 'box'>Broadcast = <span id='sub'>".$netblock->get_broadcast()."</span></div>";
			print "<div class = 'box'>Hostmin = <span id='sub'>".$netblock->get_hostmin()."</span></div>";
			print "<div class = 'box'>Hostmax = <span id='sub'>".$netblock->get_hostmax()."</span></div>";
			print "<div class = 'box'>Host Per Net = <span id='sub'>".$netblock->get_hostPerNet();
		}
		else if($_GET['action'] == 'desc')
		{
			$name="NO OWNER";
			if($ip_db->get_owner_name() != "")
			{$name = $ip_db->get_owner_name();} 
			
			$assigned="NO ASSIGNED TO";
			if($ip_db->get_assigned_to_name() != "")
			{$assigned = $ip_db->get_assigned_to_name();}
			
			$location = "NO LOCATION";
			if($ip_db->get_location_name() != "")
			{$location = $ip_db->get_location_name();}
			
			print "<span id='head' style='color:".$color.";'>".$netblock->get_IP()."</span> - ".$ip_db->get_title()."<br / > ";
			print "<div id='left'>";
			print "<img src='icons/location.png' /> ".$location."<br /> 
					<img src='icons/Person.png' /> ".$name." <br />
					<img src='icons/People.png' /> ".$assigned."<br />";
			
			print "</div>";
			
			print "<div id='right'>";
			
			print "<div class = 'desc'>Comment: <span id='sub'>".nl2br($ip_db->get_description())."</span></div>";
			print "<div class = 'desc'>Tags: <span id='sub'>";
			foreach ($ip_db->get_tags() as $t_id => $t_name)
			{
				$tag_str .= $t_name.", ";
			}
			$tag_str = rtrim($tag_str, " ");
			print rtrim($tag_str, ",");
			print "</span></div>";
			
			print "</div>";		
		}
	}
}

if(isset($_GET['refresh_get']))
{
	$id = $_GET['refresh_get'];
	$ip_2 = new IP_Database($id);
	$ip_manager = new Netblock();
	$ip_manager->set_IP($ip_2->get_address_int()."/".$ip_2->get_subnet_size(), $ip_2->get_family());
	
	if($_GET["type"] == "status")
	{
		//$color="black";
		$status = $ip_2->get_status();
		switch ($status)
		{
			case "FREE";
			$color = "green";
			break;
			
			case "RESERVED";
			$color = "blue";
			break;
			
			case "ASSIGNED";
			$color = "red";
			break;
			
			case "PARENT";
			$color = "grey; font-style:italic;";
			break;
		}
		echo "<font style='color:".$color."'>".$ip_manager->get_IP()."</font>";
	}
	
	if($_GET["type"] == "stub_img")
	{	
		if ($ip_2->is_stub() ==1)
		{
			echo "<img id='stub' src='icons/stub2.png' />";
		}
		else
		{
			echo "";	
		}
	}
	
	if($_GET["type"] == "stub")
	{
		if ($ip_2->is_stub() ==1)
		{
			echo "<img src='icons/host.png' />";
		}
		else
		{
			echo "<img src='icons/gears.png' />";
		}
	}
	
	if($_GET["type"] == "title")
	{	
		$ip_title = "";
		$ip_loc = "";
		$ip_assign = "";
		if ($ip_2->get_title() != "")
		{
			$ip_title = " - ".$ip_2->get_title();
		}
			
		if ($ip_2->get_location_name() != "")
		{
			$ip_loc = " - <img src='icons/location.png' />".$ip_2->get_location_name();
		}
			
		if ($ip_2->get_assigned_to_name() != "")
		{
			$ip_assign = " - <img src='icons/People.png' /> ".$ip_2->get_assigned_to_name();
		}
		echo $ip_title.$ip_loc.$ip_assign;
	}
	
	if($_GET["type"] == "host_split")
	{
		if($ip_2->is_stub() == 0)
		{	
			if ($ip_manager->get_family()==4)
			{
				if ($ip_2->get_subnet_size()!=32)
				{
					echo "<select name='split'>";
					for ($i = $ip_manager->get_length(); $i<32; $i++)
					{
						echo "<option value='".($i+1)."'>".($i+1)."</option>";
					}
					echo"</select>";
					echo " <input type='submit' name='ip_split_submit' value='SPLIT NETWORK' />";
				}
				else {echo "<span style='color:red; font-size:14px'>CANNOT SPLIT ANY FURTHER</span>";}
			
				$parent_id = IP_Database::get_parent($id);
				if(is_numeric($parent_id))
				{
					$temp_ip = new IP_Database($parent_id);
					if($temp_ip->is_stub() != true)
					{
						echo "<br/>";
						echo "<select name='merge'>";
						for ($i = ($ip_manager->get_length()-1); $i>=$temp_ip->get_subnet_size(); $i--)
						{
							echo "<option value='".($i)."'>".($i)."</option>";
						}
						echo"</select>";
						echo " <input type='submit' name='ip_merge_submit' value='MERGE NETWORK' />";
					}
				}
			}
			else if ($ip_manager->get_family()==6)
			{
				if ($ip_2->get_subnet_size()!=128)
				{
					echo "<select name='split'>";
					for ($i = $ip_manager->get_length(); $i<128; $i++)
					{
						echo "<option value='".($i+1)."'>".($i+1)."</option>";
					}
					echo"</select>";
					echo " <input type='submit' name='ip_split_submit' value='SPLIT NETWORK' />";
					
				}
				else {echo "<span style='color:red; font-size:14px'>CANNOT SPLIT ANY FURTHER</span>";}
				$parent_id = IP_Database::get_parent($id);
				if(is_numeric($parent_id))
				{
					$temp_ip = new IP_Database($parent_id);
					if($temp_ip->is_stub() != true)
					{
						echo "<br/>";
						echo "<select name='merge'>";
						for ($i = ($ip_manager->get_length()-1); $i>=$temp_ip->get_subnet_size(); $i--)
						{
							echo "<option value='".($i)."'>".($i)."</option>";
						}
						echo"</select>";
						echo " <input type='submit' name='ip_merge_submit' value='MERGE NETWORK' />";
					}
				}
			}
		}
		else
		{
			if ($ip_manager->get_family()==4)
			{
				if ($ip_2->get_subnet_size()!=32)
				{
					echo "<input type='text' name='ip_hostip' style='width:200px;'/> / 
					<input type='hidden' name='ip_hostsubnet' value='32'/>
					<input type='submit' name='ip_host' value='ADD HOST' />";
				}
				else {echo "<span style='color:red; font-size:14px'>CANNOT CREATE ANYMORE HOST</span>";}
			}
			else if ($ip_manager->get_family()==6)
			{
				if ($ip_2->get_subnet_size()!=128)
				{
					echo "<input type='text' name='ip_hostip' style='width:200px;'/> / 
					<input type='text' name='ip_hostsubnet' value='128' style='width:50px;'/>
					<input type='submit' name='ip_host' value='ADD HOST' />";
					
				}
				else {echo "<span style='color:red; font-size:14px'>CANNOT CREATE ANYMORE HOST</span>";}
			}
		}
	}
		//$string.= "<input type='image' src='images/ip_switch_split.png' value='change_to_stub' alt='Submit' style='background-color:transparent; border:none;' />";
}

if(isset($_GET['all_tags']))
{
	$data_str ="";
	$all_tags = array();
	$all_tags = IP_Database::get_all_tags();
	foreach ($all_tags as $id => $tag)
	{
		$data_str .= $tag." ";	
	}
	echo rtrim($data_str, " ");;
}

//id='+n_id+'&title='+title+'&description='+description+'&location='+location+'&owner='+owner+'&assigned='+assigned+'&status='+status+'&tags='+tags
if(isset($_POST['id']))
{
	$id = $_POST['id'];
	$title = $_POST['title'];
	$desc = $_POST['description'];
	$location = $_POST['location'];
	$owner = $_POST['owner'];
	$assigned = $_POST['assigned'];
	$status = $_POST['status'];
	
	$tags = $_POST['tags'];
	
	$ip_2 = new IP_Database($id);
	
	$ip_2->set_title($title);
	$ip_2->set_description($desc);
	$ip_2->set_location_id($location);
	$ip_2->set_owner_id($owner);
	$ip_2->set_assigned_to_id($assigned);
	
	if(isset($_POST['status']))
	{
		if($status != 'undefined')
		{
			$ip_2->set_status($status);
		}
	}
	
	if(isset($_POST['stub']))
	{
		$stub = $_POST['stub'];
		$ip_2->set_stub($stub);
	}
	
	$ip_2->set_tags($tags);
	
	if(!$ip_2->update())
	{
		return false;	
	}
}



if (isset($_GET['stub']))
{
	$stub_id = explode("@", $_GET['stub']);
	$ip_db = new IP_Database($stub_id[1]);
	$ip_db->set_stub($stub_id[0]);
	
	$ip_manager = new Netblock();
	$ip_manager->set_IP($ip_db->get_address_int()."/".$ip_db->get_subnet_size(), $ip_db->get_family());
	
	if ($ip_db->update())
	{
		if($ip_db->is_stub() == 0)
		{	
			if ($ip_manager->get_family()==4)
			{
				if ($ip_db->get_subnet_size()!=32)
				{
					echo "Split <select name='split'>";
					for ($i = $ip_manager->get_length(); $i<32; $i++)
					{
						echo "<option value='".($i+1)."'>".($i+1)."</option>";
					}
					echo"</select>";
					echo " <input type='submit' name='ip_split_submit' value='SPLIT NETWORK' />";
				}
				else {echo "<span style='color:red; font-size:14px'>NO MORE SPLITS</span>";}
				}
			else if ($ip_manager->get_family()==6)
			{
				if ($ip_db->get_subnet_size()!=128)
				{
					echo "Split <select name='split'>";
					for ($i = $ip_manager->get_length(); $i<128; $i++)
					{
						echo "<option value='".($i+1)."'>".($i+1)."</option>";
					}
					echo"</select>";
					echo " <input type='submit' name='ip_split_submit' value='SPLIT NETWORK' />";
					
				}
				else {echo "<span style='color:red; font-size:14px'>NO MORE SPLITS</span>";}
			}
			
		}
		else{
			echo "<input type='text' name='ip_hostip' style='width:200px;'/> / 
					<input type='text' name='ip_hostsubnet' style='width:50px;'/>
					<input type='submit' name='ip_host' value='MAKE HOST' />";
		}
	}
	else {echo $ip_db->get_error();}
}



?>
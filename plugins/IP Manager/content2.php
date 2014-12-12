<script src="js/columnview/jquery.columnview.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
// starting the script on page load
$(document).ready(function(){
	$('#demo1').columnview({
	onchange: function(element) {
          $("#thecurrent").text($(element).text());}
	}
	);
});
</script>

<style>

input[type='text'] {
width: 50px;
}


</style>

<!--<form method='get'>
<input type='text' name='ip1' maxlength='3'/> . <input type='text' name='ip2' maxlength='3'/> . <input type='text' name='ip3' maxlength='3'/> . <input type='text' name='ip4' maxlength='3'/> / <input type='text' name='netmask' maxlength='2'/><br /><br />
Description <input type='text' name='desc' style='width:300px; height:200px;'/>
<input type='submit' value='Submit' />
</form>-->


<h1>Enter a IPV4 or IPV6 address</h1>
<form method='post'>
<input type='text' name='ipv6' style='width:200px;'/> / <input type='text' name='netmask6' maxlength='3'/><br /><br />
Description <input type='text' name='desc' style='width:300px; height:100px;'/>
<input type='submit' name='view' value='View IP' />
<input type='submit' name='add' value='Add IP' />
</form>

<?php
include_once 'IP_Database.php';
include_once 'Netblock.php';

$ip_manager = new Netblock();

if(!empty($_POST))
{
	if(isset($_POST['ipv6']))
	{
		$ip = $_POST['ipv6']."/".$_POST['netmask6'];
	}
	else if (isset($_POST['ip1']))
	{
		$ip = $_POST['ip1'].".".$_POST['ip2'].".".$_POST['ip3'].".".$_POST['ip4']."/".$_POST['netmask'];
	}
	else
	{
		$ip= "";
	}
	
	if (isset($_POST['desc']))
	{
		$desc = $_POST['desc'];
	}
	else {$desc ="";}
		
	if($ip =="/")
	{
		echo "<h1>Enter an IP!</h1>";
		exit;	
	}
	$ip_manager = new Netblock($ip, $desc);
	
	if(isset($_POST['view']))
	{	
		$ip_manager->print_all();
	}
	
	else if (isset($_POST['add']))
	{
		if($ip_manager->get_IP() != "INVALID")
		{
			IP_Database::add_ip_to_db($ip_manager->get_long(), $ip_manager->get_length(), $ip_manager->get_desc(), $ip_manager->get_family());
		}
		else
		{
			echo "INVALID INFO TRY AGAIN";
			exit;	
		}
	}
}
echo "<hr/>";


if(isset($_POST['ip_split_submit']))
{
	$ip_info = IP_Database::get_ip_by_id($_POST['id']);
	$ip_manager->set_IP($ip_info->get_address_ip()."/".$ip_info['subnet_size'], $ip_info['family']);
	$split = $ip_manager->split_IP($_POST['split']);
	foreach ($split as $id=>$ip)
	{
		$ip_manager = new Netblock($ip, $ip_info['description']);
		IP_Database::add_split_to_db($ip_manager->get_long(), $ip_manager->get_length(), $ip_info['description'], $ip_manager->get_family(), $ip_info['base_index']);
	}
}

else if(isset($_POST['ip_delete']))
{
	IP_Database::remove_ip_by_id($_POST['id']);
}

else if(isset($_POST['ip_assign']))
{
	IP_Database::assign_ip_by_id($_POST['id'], $_POST['ip_status']);
}



$arr = array();
$arr = IP_Database::get_all_ip();
$listType = "expanded";
$isID=true;

display_all_ip($arr, $ip_manager, $listType, $isID);

function display_all_ip($arr, $ip_manager, $listType="", $isID="")
{
	if ($isID)
	{
		print "\n<ul id='demo1' class='menu'>";
		$isID = false;
	}
	else
	{
		print "\n<ul class='menu'>";
	}
	
	foreach ($arr as $id=>$name)
	{	
		if(!IP_Database::is_parent($id))
		{
			$listType = "leaf";
			print "\n<li class='".$listType."'>";
			//<form method='post'>";
			$ip_2 = IP_Database::get_ip_by_id($id);
			$ip_manager->set_IP($ip_2['base_addr']."/".$ip_2['subnet_size'], $ip_2['family']);
			print "\n<a style='cursor:default' class='tooltip' title='Subnet mask = ".$ip_manager->get_netmask()." <br/> Wildcard = ".$ip_manager->get_wildcard()." <br/> Network = ".$ip_manager->get_network()." <br/> Broadcast = ".$ip_manager->get_broadcast()." <br/> Hostmin = ".$ip_manager->get_hostmin()." <br/> Hostmax = ".$ip_manager->get_hostmax()."<br/>Host Per Net = ".$ip_manager->get_hostPerNet()."'>".$ip_manager->get_ip()."</a><br/>";
			
			/*echo "Split <select name='split'>";
			if ($ip_manager->get_family()==4)
			{
				for ($i = $ip_manager->get_length(); $i<32; $i++)
				{
					echo "<option value='".($i+1)."'>".($i+1)."</option>";
				}
			}
			else if ($ip_manager->get_family()==6)
			{
				for ($i = $ip_manager->get_length(); $i<128; $i++)
				{
					echo "<option value='".($i+1)."'>".($i+1)."</option>";
				}
			}
			
			echo"</select>
			<input type='hidden' name='id' value='".$id."' />";
			
			if($ip_2['stub'] == 0)
			{
				echo "<input type='submit' name='ip_split_submit' value='SPLIT NETWORK' />";
			}
			else if ($ip_2['stub'] == 1)
			{
				echo "<input type='submit' disabled name='ip_split_submit' value='SPLIT NETWORK' />";
			}
			
			echo "<input type='submit' name='ip_delete' value='DELETE NETWORK' />
			
			<input type='text' name='ip_host' style='width:200px;'/> / 
			<input type='text' name='ip_subnet' style='width:50px;'/>
			<input type='submit' name='ip_host' value='MAKE HOST' />
			
			<input type='text' name='ip_status' style='width:200px;'/>
			<input type='submit' name='ip_assign' value='ASSIGN NETWORK' />
			 - ".$ip_2['status']."";
			echo "</form>";*/
		}
		else
		{
			$listType = "expanded";
			print "\n<li class='".$listType."'>";
			//<form method='post'>";
			$ip_2 = IP_Database::get_ip_by_id($id);
			$ip_manager->set_IP($ip_2['base_addr']."/".$ip_2['subnet_size'], $ip_2['family']);
			print "\n<a style='cursor:default' class='tooltip' title='Subnet mask = ".$ip_manager->get_netmask()." <br/> Wildcard = ".$ip_manager->get_wildcard()." <br/> Network = ".$ip_manager->get_network()." <br/> Broadcast = ".$ip_manager->get_broadcast()." <br/> Hostmin = ".$ip_manager->get_hostmin()." <br/> Hostmax = ".$ip_manager->get_hostmax()."<br/>Host Per Net = ".$ip_manager->get_hostPerNet()."'>".$ip_manager->get_ip()."</a><br/>";
			
			/*echo "<input type='hidden' name='id' value='".$id."' />
			<input type='submit' name='ip_delete' value='DELETE NETWORK' />
			<input type='text' name='ip_status' style='width:200px;'/>
			<input type='submit' name='ip_assign' value='ASSIGN NETWORK' />
			 - ".$ip_2['status']."
			</form>*/
			echo"<br/>";
			
			display_all_ip(IP_Database::get_all_ip($id),  $ip_manager, $listType, $isID);
		}
		
		echo "</li>";
	}
	echo "</ul>";
	echo "<div style='border:1px solid green' id='thecurrent'>&nbsp;</div>";
}
?>

<?php

/*$dec = Netblock::inet_ptod('1:3::4:5');
echo $dec."<br />";
$dec = explode(".", $dec);
echo Netblock::inet_dtop($dec[0]);
//echo inet_ntop();*/

//echo Netblock::binary_add("10000000","1000000");


?>

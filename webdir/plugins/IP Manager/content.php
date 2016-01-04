
<link rel="stylesheet" href="js/treeview/jquery.treeview.css" />
<script src="js/treeview/lib/jquery.cookie.js" type="text/javascript"></script>
<script src="js/treeview/jquery.treeview.js" type="text/javascript"></script>
<script type='text/javascript' src='open-flash-chart/js/json/json2.js'></script>
<script type='text/javascript' src='open-flash-chart/js/swfobject.js'></script>
<script type='text/javascript'>
swfobject.embedSWF('open-flash-chart/open-flash-chart.swf', 'my_chart', '300', '300', '9.0.0');
</script>

  


<script type="text/javascript">

//ipCalc function gives calculator information (dropped), and ip information at the bottom screen asynchronously
this.ipCalc = function(){	
	/*$("a.ipcalc").hover(function(e){
		var t = this.title;			  
		$("#calcIP").fadeOut('fast', function(){$("#calcIP").load("plugins/IP%20Manager/IPInfo.php?action=calc&getIPInfo="+t, function(){$("#calcIP").fadeIn('fast');});});	
    });*/
	
	$("a.ipdesc").hover(function(e){
		var t = this.rel;			  
		$("#calcIP").fadeOut('fast', function(){$("#calcIP").load("plugins/IP%20Manager/IPInfo.php?action=desc&getIPInfo="+t, function(){$("#calcIP").fadeIn('fast');});});	
    });
	
	
	/*$("a.ipdesc").hover(function(e){
		var t = this.title;			  
		$("#calcIP").fadeOut('fast', function(){$("#calcIP").load("plugins/IP%20Manager/IPInfo.php?action=desc&getIPInfo="+t, function(){$("#calcIP").fadeIn('fast');});});	
    });*/
};

//To confirm for deleting a network
function confirmSubmit()
{
var agree=confirm("Are you sure you want to delete?");
if (agree)
	return true ;
else
	return false ;
}

//To confirm for deleting a network
function confirmForceDelete(num)
{
var agree=confirm("Are you sure you want to force delete everything? This will delete all the children of this netblock regardless of their status.");
	if (agree){
		var verify=confirm("Are you really sure? You will be deleting "+num+" child netblocks from this parent netblock.");
		
		if(verify)
			return true ;
	}
	else{
		return false ;
	}
}
//Dropped asynchronous function where the function communicates with the database for stub
/*function switch_stub(d, a)	
{
//	$("#"+d+"").load("plugins/IP%20Manager/IPInfo.php?stub="+a);
}*/

//Advance reporting toggle
function toggle_search()	
{
	$("#report_filter").toggle();
	$("p").toggle();
}

//report for csv (Can be expanded with a open window for downloading
function csv_report(e)
{
	window.location.href=e;
}
//TESTING ASYNC AJAX
/*function add_host(id)
{
	var n_ip = $("input#ip_hostip"+id+"").val();
	var n_subnet = $("input#ip_hostsubnet"+id+"").val();
	var dataStr = 'hostip='+n_ip+'&hostsubnet='+n_subnet;
	alert(dataStr);
}*/

//Updates the information of a network asynchronously
function update_info(id)	
{
	//grabs all the value information
	var n_id = id;
	var title = $("input#title"+n_id+"").val();
	var description = $("textArea#description"+n_id+"").val();
	var location= $("select#location"+n_id+"").val();
	var owner= $("select#owner"+n_id+"").val();
	var assigned= $("select#assigned"+n_id+"").val();
	var status= $("select#status"+n_id+"").val();
	var stub= $("select#stub"+n_id+"").val();
	var tags= $("input#tags"+n_id+"").val();
	
	//this is the get string
	var dataStr = '';
	
	//make sure there is a stub
	if (stub != null)
	{
		var dataStr = 'stub='+stub+'&';
	}
	
	//create the get string
	dataStr = dataStr + 'id='+n_id+'&title='+title+'&description='+description+'&location='+location+'&owner='+owner+'&assigned='+assigned+'&status='+status+'&tags='+tags;
	//alert(dataStr);
	
	//send the information asynchronously
	$.ajax({  
	type: "POST",  
	url: "plugins/IP%20Manager/IPInfo.php",  
	data: dataStr,  
	success: function() { 
	
	//if the connection is successful update everything 
		$.get("plugins/IP%20Manager/IPInfo.php",{refresh_get: n_id, type: "stub"}, function(data){
			$("span#st_ho_img"+n_id+"").html(data);
		});
		$.get("plugins/IP%20Manager/IPInfo.php",{refresh_get: n_id, type: "title"}, function(data){
			$("span#ip_name"+n_id+"").html(data);
		});
		$.get("plugins/IP%20Manager/IPInfo.php",{refresh_get: n_id, type: "status"}, function(data){
			$("span#status"+n_id+"").html(data);
		});
		$.get("plugins/IP%20Manager/IPInfo.php",{refresh_get: n_id, type: "stub_img"}, function(data){
			$("span#stub_img"+n_id+"").html(data);
		});
		$.get("plugins/IP%20Manager/IPInfo.php",{refresh_get: n_id, type: "host_split"}, function(data){
			$("div#host_split"+n_id+"").html(data);
		});
		
		//show that this has been updated
		$("li#ip_row"+n_id+"").css("background-color", "yellow");
		$("p#update"+n_id+"").html("<img src='icons/Success.png' />");
	}
	});  
}

var super_str;
// starting the script on page load
$(document).ready(function(){
	//instantiate everything
	$("#browser").treeview({
		control: "#treecontrol",
		persist: "cookie",
		cookieId: "treeview-BCNETIP"
		});
	//load everything first, and then show everything afterwards
	$("#loading").html("");
	$("#disp_ip").css("visibility", "visible");
	$("#disp_networks").show();
	$("#disp_all_ip").css("visibility", "visible");
	ipCalc();	
	$("#report_filter").hide();
	
	//disable return/enter key so that for doesn't get auto returned
    $("form").keypress(function(e) {
		if (e.which == 13) {
      	return false;
		}
	});
	$("textArea").keypress(function(e) {
		if (e.which == 13) {
      	return true;
		}
	});
	
	//auto complete function that didn't work
	/*$.get("plugins/IP%20Manager/IPInfo.php",{all_tags: "wow"}, function(data){
		var a_data = data.split(" ");
	});
	var a_data = "code code".split(" ");
	$(".tags_input").autocomplete(a_data);*/
});

//THIS IS FOR THE CHART
function open_flash_chart_data() {
     return JSON.stringify(data);
}

function findSWF(movieName) {
	if (navigator.appName.indexOf('Microsoft')!= -1) {
		return window[movieName];
	} else {
		return document[movieName];
	}
}

</script>

<!--<script type="text/javascript">
DROPPED FUNCTION
function displayIP(ip)
{
	$("#ipInfo").fadeOut('slow', function(){$("#ipInfo").load("plugins/IP%20Manager/IPInfo.php?getIPInfo="+ip, function(){$("#ipInfo").fadeIn('slow');});});
}
</script>-->

<? 
//If there is an network ID, then go to the anchor you have last modified
if (isset($_POST['id']))
{
?>
<script type="text/javascript">

function goToAnchor(a) {
	location.href = document.location.href+"#"+a;
}

document.body.setAttribute("onload", "goToAnchor(<? 
echo $_POST['id'];
?>)");

<?
}
?>

</script>


<style>
input[type='text'] {
	width: 50px;
}

font.header {
	font-size:14px;
}

font.header2 {
	font-size:18px;
	font-weight:bolder;
}

#calc {
	position:fixed;
	width: 100%;
	height: 100px;
	border:thin #999 solid;
	left: 0%;
	bottom:0%;
	background-color:#FFF;
	overflow:auto;
}

#calcIP {
	padding:10px;	
}

#footer {
	margin-bottom: 100px;	
}

select {
	margin-bottom: 10px;	
}

#modalBox #myModalBox{
	width:40%;
	height:auto;
	padding:10px;
}

#modalBox #modal_search{
	width:60%;
	height:auto;
	padding:10px;
}

.ip_menu{
	font-size:14px;
	border-left:#CCC thin solid;
	padding-left:5px;
	padding-right:5px;
}

.ip_menu:hover{
	background:#999;
	color:#FFF;
}

.active_menu{
	background:#999;
	color:#FFF;
}


#ip_name {
	display:inline;	
}

#browser img{
	width:15px;
	height:15px;	
}

#browser img#stub{
	width:auto;
	height:15px;
	margin-right:5px;
}

#disp_ip {
	float:left;
	width:auto;
	min-width: 400px;
	visibility:hidden;
}

#disp_all_ip {
	visibility:hidden;
}

#disp_networks {
	float:left;
	width:auto;
	margin-bottom:20px;
	border-right: solid #999 thin;
	margin-right:10px;
	display:none;
}

#disp_networks .network_box{
	padding-top:5px;
	padding-bottom:5px;
	padding-right: 5px;
	border-bottom: thin #CCC solid;
}

#disp_networks .network_box:hover{
	background:#CCC;
	color:#FFF;
}

#disp_networks .active{
	background:#CCC;
	color:#FFF;
}

#loading img {
	width: 25px;
	height: 25px;	
}

#loading {
	color:#CCC;
	float:left;
	clear:both;
	font-size:14px;
}

#report {
	clear:both;	
}

#report #report_filter {
	width: 1024px;
	border: dashed thin #09F;
	padding: 10px;
	margin-bottom: 10px;
}

#report #toggle {
	clear:both;
}
#ip_update {
	float:left;	
}

#ip_delete {
	float:right;	
}
</style>

<!--DROPPED FORM
<form method='get'>
<input type='text' name='ip1' maxlength='3'/> . <input type='text' name='ip2' maxlength='3'/> . <input type='text' name='ip3' maxlength='3'/> . <input type='text' name='ip4' maxlength='3'/> / <input type='text' name='netmask' maxlength='2'/><br /><br />
Comment <input type='text' name='desc' style='width:300px; height:200px;'/>
<input type='submit' value='Submit' />
</form>-->

<!-- THIS IS THE MODAL FORM FOR ADDING NEW NETWORKS -->
<div id='modalBox'>
<div id='myModalBox' class='window'>
<a href='#'class='close' /><img src='icons/close.png'></a>
<font class='header2'>Enter an IPV4 or IPV6 network</font><hr />
<form method='post'>
<font class='header'>Name </font><input type='text' name='title' style='width:100%; margin-bottom:10px;'/>
<font class='header'>Prefix </font><br />
<input type='text' name='ipv6' style='width:200px;'/> / <input type='text' name='netmask6' maxlength='3'/>
<input type='submit' name='add' value='Add Network' style='float:right' />
</form>
</div>
<div id='mask'></div>
</div>


<?php
include_once 'IP_Database.php';
include_once 'Vlan_manager.php';
include_once 'Netblock.php';
include_once 'open-flash-chart/php-ofc-library/open-flash-chart.php';

//Instantiate all the variables
$ip_manager = new Netblock();
$string = "";

//----------------SEARCH MODAL BOX--------------------
echo "<div id='modalBox'>
	<div id='modal_search' class='window'>
	<div style='clear:both;'></div>";
echo "<a href='#'class='close' /><img src='icons/close.png'></a>";
display_search_box();
echo "</div>
	<div id='mask'></div>
	</div>";

//DROPPED VLAN MODAL FORM
/*echo "<div id='modalBox'>
	<div id='modalVLAN_search' class='window'>
	<div style='clear:both;'></div>";
echo "<a href='#'class='close' /><img src='icons/close.png'></a>";
display_search_box();
echo "</div>
	<div id='mask'></div>
	</div>";
*/

//Find the active tab so that the tab will be highlighted
$report4_tab = "ip_menu";
$report6_tab = "ip_menu";
$v4_tab = "ip_menu";
$v6_tab = "ip_menu";
$vlan_tab = "ip_menu";

//Checks what the current tab is
if (isset($_GET['report']))
{
	if ($_GET['report'] == 4)
	{
		$report4_tab = "ip_menu active_menu";
	}else if ($_GET['report'] ==6)
	{
		$report6_tab = "ip_menu active_menu";
	}
}
else if (isset($_GET['family']))
{
	if ($_GET['family'] == 4)
	{
		$v4_tab = "ip_menu active_menu";
	}else if ($_GET['family'] ==6)
	{
		$v6_tab = "ip_menu active_menu";
	}
	else if ($_GET['family'] =="vlan")
	{
		$vlan_tab = "ip_menu active_menu";
	}
}

//Create the links
echo "<div style='margin-bottom:10px;'><a class='".$report4_tab."' href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&report=4' >Report IPv4</a>

<a class='".$report6_tab."' href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&report=6' >Report IPv6</a>

<a class='".$v4_tab."' href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=4' >IPv4</a>

<a class='".$v6_tab."' href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=6' >IPv6</a>

<a class='".$vlan_tab."' href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan'>VLAN</a></div>";

//Create the toolbox for the IP Networks
$names = array("Add New Network", "Search");
$icons = array("add", "search");
$modalID = array("myModalBox", "modal_search");

//State to see if something is updated, added, or deleted successfully
if(isset($_GET['success']))
{
	if ($_GET['success'] == 'update')
	{
		echo Form::success("Updated successfully");
	}
	else if ($_GET['success'] == 'add')
	{
		echo Form::success("Added successfully");
	}
	else if ($_GET['success'] == 'delete')
	{
		echo Form::success("Deleted successfully");
	}
}

//Create new tools if it is VLAN
if(isset($_GET['family']))
{
	if($_GET['family'] == 'vlan')
	{
		//Different states based on where VLAN is in
		if (isset($_GET['v_id']))
		{
			$names = array("Add New VLAN", "Search", "Delete VLAN");
			$icons = array("add", "search", "delete");
			$handlers = array("window.location.href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&action=add'", 
			"window.location.href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&action=search'", 
			"window.location.href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&v_id=".$_GET['v_id']."&action=delete'");
		}
		else
		{
			$names = array("Add New VLAN", "Search", "Show All VLANs");
			$icons = array("add", "search", "icons/vlan-01.png");
			$handlers = array("window.location.href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&action=add'", 
			"window.location.href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&action=search'",
			"window.location.href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&action=showAll'");
		}
		echo EdittingTools::createNewTools($names, $icons, $handlers, "VLAN Tool");
	}
	else
	{
		echo EdittingTools::createNewModal($names, $icons, $modalID, "IP Tool");
	}
}
else
{
	echo EdittingTools::createNewModal($names, $icons, $modalID, "IP Tool");
}

//If it's the vlan overview, create filters
if(isset($_GET['family']))
{
	if($_GET['family'] == 'vlan' && !isset($_GET['action'])  && !isset($_GET['v_id']))
	{
		$listFilter = array("FREE"=>"FREE", "RESERVED"=>"RESERVED", "ASSIGNED"=>"ASSIGNED");
		echo EdittingTools::createNewFilters($listFilter);
	}
}

//CRUD detection for networks
if(!empty($_POST))
{
	//Check the network
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
		
	//If nothing is entered, a warning is issued
	if($ip =="/")
	{
		echo Form::warning("Enter an IP!");
		exit;	
	}
	
	//create a new manager to do the calculation
	$ip_manager = new Netblock($ip);
	
	//if it's adding a new network
	if (isset($_POST['add']))
	{
		//check if the ip is valid
		if($ip_manager->get_IP() != "INVALID")
		{
			//set all the values if it's valid
			$n_ip = new IP_Database();
			$n_ip->set_address_int($ip_manager->get_long());
			$n_ip->set_subnet_size($ip_manager->get_length());
			$n_ip->set_description("");
			$n_ip->set_family($ip_manager->get_family());
			$n_ip->set_title($_POST['title']);
			
			//IAM HERE TRYING TO INSTALL SEARCH AND ADD IP
			
			$i_match = explode("/", $ip_manager->get_IP());
			
			//check if the ip and subnet are valid combinations
			if ($ip_manager->get_long_ip() != $ip_manager->get_long())
			{
				echo Form::warning("Your IP is invalid, the IP is changed from ".$i_match[0]." to ".$ip_manager->get_network());	
			}
			
			//inserting the IP, if it fails show the error
			if(!$n_ip->insert())
			{
				echo Form::error($n_ip->get_error());
			}
		}
		else
		{
			//If IP info is invalid this message shows
			echo Form::warning("INVALID INFO TRY AGAIN");
			exit;	
		}
	}
	
	//If a network id is posted, it means a modification (update, split, host, or delete) is called
	if(isset($_POST['id']))
	{
		//Get this network information from the database
		$ip_info = new IP_Database($_POST['id']);
		
		//if it's a split
		if(isset($_POST['ip_split_submit']))
		{
			//do the split calculation first
			$ip_manager->set_IP($ip_info->get_address_ip(), $ip_info->get_family());
			$split = $ip_manager->split_IP($_POST['split']);
			
			//insert the split calculation into the database
			$add = true;
			foreach ($split as $id=>$ip)
			{
				//GETTING RID OF DESCRIPTION
				$ip_manager = new Netblock($ip);
				$n_ip = new IP_Database();
				$n_ip->set_address_int($ip_manager->get_long());
				$n_ip->set_subnet_size($ip_manager->get_length());
				$n_ip->set_family($ip_info->get_family());
				
				//set the parent id to know who the parent is
				$n_ip->set_parent_id($ip_info->get_netblock_id());
				
				//if there are any inheritance of the information, inherit them into the new network
				if($_POST['inh_loc'] == "on")
				{
					$n_ip->set_location_id($ip_info->get_location_id());
				}
				
				if($_POST['inh_owner'] == "on")
				{
					$n_ip->set_owner_id($ip_info->get_owner_id());
				}
				
				if($_POST['inh_assigned'] == "on")
				{
					$n_ip->set_assigned_to_id($ip_info->get_assigned_to_id());
				}
				
				if($_POST['inh_status'] == "on")
				{
					if($ip_info->get_status() == 'PARENT'){
					$n_ip->set_status('FREE');}
					else
					{$n_ip->set_status($ip_info->get_status());}
				}
				
				if($_POST['inh_tags'] == "on")
				{
					$n_ip->set_tags($ip_info->get_tags());
				}
				
				// if create fails show error
				if(!$n_ip->insert())
				{
					echo $n_ip->get_error();
					$add = false;
				}
				else
				{
					$add = true;	
				}
			}
			if($add)
			{
				//update status to parent
				$ip_info->set_status("PARENT");
				$ip_info->update();	
			}
		}
		//if it's a merge
		else if(isset($_POST['ip_merge_submit']))
		{
			//print_r($_POST);
			
			if(isset($_POST['merge']))
			{
				$merge = $_POST['merge'];
				$match = explode("/", $ip_info->get_address_ip());
				$address = $match[0];
				$ip_manager->set_IP($address."/".$merge, $ip_info->get_family());
				
				if ($ip_info->get_family() == 4)
				{
					$max_length = 32;
				}
				else
				if ($ip_info->get_family() == 6)
				{
					$max_length = 128;
				}
				
				$host_min = $ip_manager->get_hostmin();
				$host_max = $ip_manager->get_hostmax();
				$min_ip_manager = new Netblock($host_min."/".$max_length);
				$max_ip_manager = new Netblock($host_max."/".$max_length);
				$parent_id = $ip_info->get_parent_id();
				
				if(is_numeric($parent_id))
				{
					$all_child_ip = IP_Database::get_all_ip($parent_id, $ip_info->get_family());	
				}
				
				$to_remove = array();
				foreach ($all_child_ip as $ip => $addr)
				{
					$t_ip_info = new IP_Database($ip);
					
					if($ip_info->get_family()==4)
					{
						if($t_ip_info->get_address_int() >= $ip_manager->get_long() && $t_ip_info->get_address_int() <= $max_ip_manager->get_long_ip())
						{
							array_push($to_remove, $ip);
						}
					}
					else if($ip_info->get_family()==6)
					{
						//split the long ip address into five 10 digits strings
						$t_addr = str_pad($t_ip_info->get_address_int(), 40, 0, STR_PAD_LEFT);
						$t_str1 = substr($t_addr, 0, 10);
						$t_str2 = substr($t_addr, 9, 10);
						$t_str3 = substr($t_addr, 19, 10);
						$t_str4 = substr($t_addr, 29, 10);
						$t_str5 = substr($t_addr, 39, 10);
						
						//split the NEW long merged address into five 10 digits strings
						$i_addr = str_pad($ip_manager->get_long(), 40, 0, STR_PAD_LEFT);
						$i_str1 = substr($i_addr, 0, 10);
						$i_str2 = substr($i_addr, 9, 10);
						$i_str3 = substr($i_addr, 19, 10);
						$i_str4 = substr($i_addr, 29, 10);
						$i_str5 = substr($i_addr, 39, 10);
						
						//split the HOSTMAX long ip address into five 10 digits strings
						$m_addr = str_pad($max_ip_manager->get_long_ip(), 40, 0, STR_PAD_LEFT);
						$m_str1 = substr($m_addr, 0, 10);
						$m_str2 = substr($m_addr, 9, 10);
						$m_str3 = substr($m_addr, 19, 10);
						$m_str4 = substr($m_addr, 29, 10);
						$m_str5 = substr($m_addr, 39, 10);
						
						//checks if the first 10 digits are equivalent to each other, if they are then check the next 10
						if($t_str1 == $i_str1 && $t_str1 == $m_str1)
						{
							//checks the next 10 to see if they are equivalent
							if($t_str2 == $i_str2 && $t_str2 == $m_str2)
							{
								//checks the next 10 to see if they are equivalent
								if($t_str3 == $i_str3 && $t_str3 == $m_str3)
								{
									//checks the next 10 to see if they are equivalent
									if($t_str4 == $i_str4 && $t_str4 == $m_str4)
									{
										//the last set of numbers should NOT be equal, therefore check if the ip is inbetween host min and max
										if($t_str5 >= $i_str5 && $t_str5 < $m_str5)
										{
											array_push($to_remove, $ip);
										}
									}
									//if not equal check if ip is in between host min and max from the 4th set of strings
									else if($t_str4 >= $i_str4 && $t_str4 < $m_str4)
									{
										array_push($to_remove, $ip);
									}
								}
								//if not equal check if ip is in between host min and max from the 3rd set of strings
								else if ($t_str3 >= $i_str3 && $t_str3 < $m_str3)
								{
									array_push($to_remove, $ip);
								}
							}
							//if not equal check if ip is in between host min and max from the 2nd set of strings
							else if ($t_str2 >= $i_str2 && $t_str2 < $m_str2)
							{
								array_push($to_remove, $ip);
							}
						}
						//if not equal check if ip is in between host min and max from the 1st set of strings
						else if ($t_str1 >= $i_str1 && $t_str1 < $m_str1)
						{
							array_push($to_remove, $ip);
						}
					}				
				}
				
				$removeAll = true;
				foreach ($to_remove as $id => $ip)
				{
					$temp = new IP_Database($ip);
					if($temp->get_status() != 'FREE')
					{
						$removeAll = false;
						Form::error("All children of the network needs to be FREE. Please look at ". $temp->get_address_ip());
						break;
					}
				}
				
				$remove = false;
				if($removeAll)
				{
					foreach ($to_remove as $id => $ip)
					{
						$temp = new IP_Database($ip);
						if($temp->remove(true))
						{$remove = true;}
						else
						{$remove = false;}
					}
				}
					
				if($remove)
				{
					$n_ip = new IP_Database();
					$n_ip->set_address_int($ip_manager->get_long());
					$n_ip->set_subnet_size($merge);
					$n_ip->set_family($ip_info->get_family());
					$n_ip->set_parent_id($parent_id);
					
					if(!$n_ip->insert())
					{
						$parent_netblock = new IP_Database($parent_id);
						$parent_netblock->set_status("FREE");
						$parent_netblock->update();
					}
				}
			}
			//do the split calculation first
			/*$ip_manager->set_IP($ip_info->get_address_ip()."/".$ip_info->get_subnet_size(), $ip_info->get_family());
			$p_id = $ip_info->get_parent($ip_info->get_netblock_id());
			$p_ip_info = new IP_Database($p_id);
			
			$p_childs = IP_Database::get_all_ip($p_id);
			
			$cur_ip = $ip_info->get_address_ip()."/".$ip_info->get_subnet_size();
			$par_ip = $p_ip_info->get_address_ip()."/".$p_ip_info->get_subnet_size();
			$m_ip = $ip_manager->merge_IP($cur_ip, $par_ip);
			
			//insert the split calculation into the database
			/*$add = true;
			foreach ($split as $id=>$ip)
			{
				//GETTING RID OF DESCRIPTION
				$ip_manager = new Netblock($ip);
				$n_ip = new IP_Database();
				$n_ip->set_address_int($ip_manager->get_long());
				$n_ip->set_subnet_size($ip_manager->get_length());
				$n_ip->set_family($ip_info->get_family());
				
				//set the parent id to know who the parent is
				$n_ip->set_parent_id($ip_info->get_netblock_id());
				
				//if there are any inheritance of the information, inherit them into the new network
				if($_POST['inh_loc'] == "on")
				{
					$n_ip->set_location_id($ip_info->get_location_id());
				}
				
				if($_POST['inh_owner'] == "on")
				{
					$n_ip->set_owner_id($ip_info->get_owner_id());
				}
				
				if($_POST['inh_assigned'] == "on")
				{
					$n_ip->set_assigned_to_id($ip_info->get_assigned_to_id());
				}
				
				if($_POST['inh_status'] == "on")
				{
					$n_ip->set_status($ip_info->get_status());
				}
				
				if($_POST['inh_tags'] == "on")
				{
					$n_ip->set_tags($ip_info->get_tags());
				}
				
				// if create fails show error
				if(!$n_ip->insert())
				{
					echo $n_ip->get_error();
					$add = false;
				}
				else
				{
					$add = true;	
				}
			}
			if($add)
			{
				//update status to parent
				$ip_info->set_status("PARENT");
				$ip_info->update();	
			}*/
		}
		//if delete is called, remove this network
		else if(isset($_POST['ip_delete']))
		{
			$temp_block = new IP_Database($ip_info->get_parent_id());
			$force = false;
			if($temp_block->is_stub())
			{$force = true;}
			
			if(!$ip_info->remove($force))
			{
				echo Form::error($ip_info->get_error());	
			}
			else
			{
				//echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=".$_GET['family']."&success=delete\">";
			}
		}
		else if(isset($_POST['ip_force_delete']))
		{
			$all_children = get_all_children($ip_info->get_netblock_id(), $ip_info->get_family());
			$noerror = true;
			foreach ($all_children as $id=>$ip_id)
			{
				$temp_block = new IP_Database($ip_id);
				if(!$temp_block->remove(true))
				{
					echo Form::error($ip_info->get_error());
					$noerror = false;
					break;
				}	
			}
			
			if ($noerror)
			{
				$ip_info->set_status("FREE");
				$ip_info->update();
			}
			/*
			else
			{
				//echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=".$_GET['family']."&success=delete\">";
			}*/
		}
		//Dropped function I think...
		else if(isset($_POST['ip_assign']))
		{
			IP_Database::assign_ip_by_id($_POST['id'], $_POST['ip_status']);
		}
		
		//if it is a stub network, and host is called, then create a host
		else if(isset($_POST['ip_host']))
		{
			//get the ip and do some calculatins
			$ip = $_POST['ip_hostip']."/".$_POST['ip_hostsubnet'];
			$ip_manager->set_IP($ip, $ip_info->get_family());
			
			//check if the host is a valid host
			if($ip_manager->get_is_negative() == false)
			{
				//if not then create a new manager to calculate for more checks
				$test_manager = new Netblock($ip_info->get_address_ip());
				
				//if the address is part of the network, then create a new host
				if (substr($ip_manager->get_binary(), 0, $ip_info->get_subnet_size()) == substr($test_manager->get_binary(), 0, $ip_info->get_subnet_size()))
				{
					//set the information
					$host = new IP_Database();
					$host->set_address_int($ip_manager->get_long());
					$host->set_subnet_size($ip_manager->get_length());
					$host->set_description($ip_info->get_description());
					$host->set_family($ip_info->get_family());
					
					//set the parent
					$host->set_parent_id($ip_info->get_netblock_id());
					
					//if there are any inheritance of information, inherit them
					if($_POST['inh_loc'] == "on")
					{
						$host->set_location_id($ip_info->get_location_id());
					}
					
					if($_POST['inh_owner'] == "on")
					{
						$host->set_owner_id($ip_info->get_owner_id());
					}
					
					if($_POST['inh_assigned'] == "on")
					{
						$host->set_assigned_to_id($ip_info->get_assigned_to_id());
					}
					
					if($_POST['inh_status'] == "on")
					{
						if($ip_info->get_status() == 'PARENT'){
						$host->set_status('FREE');}
						else
						{$host->set_status($ip_info->get_status());}
					}
					
					if($_POST['inh_tags'] == "on")
					{
						$host->set_tags($ip_info->get_tags());
					}
					
					//if insert fails, show why
					if(!$host->insert("host"))
					{
						echo Form::error("CANNOT ADD HOST! Details: ".$host->get_error());
					}
					else
					{
						//update status to parent
						$ip_info->set_status("PARENT");
						$ip_info->update();	
					}
				}
				else
				{
					//if all checks fail, show that.
					echo Form::warning("INVALID HOST");	
				}
			}
			else {
				//if checks fail, show that
				echo Form::warning("INVALID HOST");
			}
		}
		
		//if it's an update, then set information and update
		//DROPPED FUNCTION FOR ASYNC Update
		else if(isset($_POST['ip_update']))
		{
			$ip_info->set_location_id($_POST['location']);
			$ip_info->set_owner_id($_POST['owner']);
			$ip_info->set_assigned_to_id($_POST['assigned']);
			$ip_info->set_description($_POST['description']);
			
			if(isset($_POST['status']))
			{
				$ip_info->set_status($_POST['status']);
			}
			
			$ip_info->set_tags($_POST['tags']);
			$ip_info->set_title($_POST['title']);
			$ip_info->set_stub($_POST['stub']);
			if (!$ip_info->update())
			{
				echo $ip_info->get_error();
			}
		}	
	}
}

//Check if a search is initiated
if(isset($_GET['search']))
{
	//if there is a search, set the data for the search field
	$title = $_GET['title'];
	$str_tag = $_GET['tags'];
	
	//turn tags back into an array
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
	
	//search for the results
	$IP_Search = new IP_Database();
	$s_results = $IP_Search->search($title, $tags, $location, $owner, $assigned_to, $status);

	//print_r($s_results);
	
	//display the results
	display_search_results($s_results, $ip_manager);
	
	//the bottom information identifier
	echo "<div id='calc'><div id='calcIP'></div></div>";
}
//if there is a family such as vlan, 4, or 6 then go to them
else if(isset($_GET['family'])){
	
	$arr = array();
	
	/*if(isset($_GET['family']))
	{
		$family = $_GET['family'];
	}
	else { $family = 4;}*/
	
	//get the family
	$family = $_GET['family'];
	
	//if family is not vlan, then that means it's either v4 or v6
	if ($family != "vlan")
	{
		//create the tree controls for the network manager
		echo "<div id='treecontrol' style='clear:both;'>
			<a href='?#'><img src='js/treeview/images/minus.gif' /> Collapse All Network</a> | 
			<a href='?#'><img src='js/treeview/images/plus.gif' /> Expand All Network</a> | 
		</div>";
		echo "<div id='loading'><img src='icons/loading.gif' />LOADING ALL IP...</div>";
		
		//display all the master networks on the left columns
		echo "<div id='disp_networks'>";
		$class = "network_box";
		
		//check which network is being selected right now
		if(isset($_GET['action']))
		{
			if($_GET['action']=='showAll')
			{
				$class = "network_box active";
			}	
		}
		
		//show the entire tree if selected
		echo "<a href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=".$_GET['family']."&action=showAll'><div class='".$class."'>Show Entire Network Tree</div></a>";
		
		//Display all networks on the left column
		$arr = IP_Database::get_all_ip(NULL, $family);
		echo display_all_networks($arr, $ip_manager);
		echo "</div>";
		echo "<div id='disp_ip'>";
		
		//if something is selected, display the tree on the right side
		if(isset($_GET['parent_id']))
		{
			$arr2 = array($_GET['parent_id']=>"test");
			echo display_all_ip($arr2, $ip_manager);
		}
		
		//or else show everything
		else if(isset($_GET['action']))
		{
			if($_GET['action']=='showAll')
			{
				$arr = IP_Database::get_all_ip(NULL, $family);
				echo display_all_ip($arr, $ip_manager);
			}
		}
			
		echo "</div>";
		//information box at the bottom
		echo "<div id='calc'><div id='calcIP'></div></div>";
	}
	else
	{
		//or else it is for vlan, if there is a vlan id
		if(isset($_GET['v_id']))
		{
			//if a modification action is called
			if(isset($_GET['action']))
			{
				//if this action is delete
				if($_GET['action']=='delete')
				{
					//create a form to prompt if they really want to delete
					echo "<div style='clear:both'>";
					$form = new Form("auto", 2);
					$form->setAction($_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&v_id=".$_GET['v_id']."&action=deleteConfirm");
					$form->prompt("Are you sure you want to delete this vlan?");
					echo "</div>";
				}
				
				//if they really want to delete then do this
				else if($_GET['action']=='deleteConfirm')
				{
					//get rid of the vlan from the database and refresh
					if (isset($_POST['deleteYes']))
					{
						$my_vlan = new Vlan_database($_GET['v_id']);
						if($my_vlan->delete())
						{
							echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&success=delete\">";
						}
						else
						{
							echo $my_vlan->get_error();
						}
					}
					else
					{
						//if not then go back to the current vlan
						echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&v_id=".$_GET['v_id']."\">";
					}
				}
			}
			else
			{
				//show the current vlan if nothing else is called
				$form = new Form("auto", 2);
				$v_id = $_GET['v_id'];
				$my_vlan = new Vlan_database($v_id);
				
				$data = array();
				
				//put data in the form
				array_push($data, $my_vlan->get_vlan_id());
				array_push($data, $my_vlan->get_name());
				array_push($data, $my_vlan->get_status());
				array_push($data, $my_vlan->get_location_name());
				array_push($data, $my_vlan->get_assigned_to_name());
				array_push($data, $my_vlan->get_notes());
				array_push($data, $my_vlan->get_vlan_distinguisher());
				
				//create the headings for these
				$heading = array("VLAN Information");
				
				//create the fields
				$titles = array("VLAN ID", "Name", "Status", "Location", "Assigned To", "Notes", "VLAN Distinguisher");
				$keys = array("id", "name", "status", "location", "assign", "notes", "distinguish");
				$fieldType = array("static", "default", "drop_down", "drop_down", "drop_down", "text_area", "default");
				$location = Location::get_locations();
				$status = array("RESERVED"=>"RESERVED", "ASSIGNED"=>"ASSIGNED");
				$assign = Contact::get_groups();
				
				//insert everything into the form
				$form->setSortable(true); // or false for not sortable
				$form->setHeadings($heading);
				$form->setType($status);
				$form->setType($location);
				$form->setType($assign);
				$form->setTitles($titles);
				$form->setData($data);
				$form->setDatabase($keys);
				$form->setFieldType($fieldType);
				
				//set the table size
				$form->setTableWidth("1024px");
				$form->setTitleWidth("20%");
				echo $form->editForm();
			}
			
		}
		//or else if it's an add action
		else if(isset($_GET['action']))
		{
			if(isset($_POST['addVLAN']))
			{
				//check if it's a valid vlan first
				if ($_POST['id'] > 0 && $_POST['id'] <=4096)
				{
					//then get all vlan to do a second check
					$my_vlan = new Vlan_database();
					$all_vlans = Vlan_database::get_all_vlans();
					$exist = false;
					
					//check if the distinguisher and vlan id are the same
					foreach ($all_vlans as $id => $vlan_id)
					{
						if ($vlan_id ==	$_POST['id'])
						{
							$t_vlan = new Vlan_database($id);
							if($t_vlan->get_vlan_distinguisher() == $_POST['distinguish'])
							{
								//if both are same then this vlan exists
								$exist = true;
							}
						}
					}
					
					//if it doesn't exist then start adding
					if (!$exist)
					{
						//set the information
						$my_vlan->set_vlan_id($_POST['id']);
						$my_vlan->set_name($_POST["name"]);
						$my_vlan->set_status($_POST["status"]);
						$my_vlan->set_location($_POST["location"]);
						$my_vlan->set_assigned_to($_POST["assign"]);
						$my_vlan->set_notes($_POST["notes"]);
						$my_vlan->set_vlan_distinguisher($_POST['distinguish']);
						
						//insert a new vlan, if inserted then show the new vlan
						if($new_id = $my_vlan->insert())
						{
							echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&success=add\">";
						}
						else
						{
							//if it failed show why
							Form::warning("INSERT Failed. Reason: ".$my_vlan->get_error());	
						}
					}
					else
					{
						//if check failed, show that
						Form::warning("The VLAN is already in use.");
					}
				}
				else
				{
					//if the id is invalid show that
					Form::warning("The VLAN ID is invalid.");						
				}
			}
			//if a search for vlan is initiated
			else if(isset($_POST['searchVLAN']))
			{
				//search the database with the information
				$results = Vlan_database::Search($_POST['id'], $_POST['name'], $_POST['status'], $_POST['location'], $_POST['assign'], $_POST['distinguish'], $_POST['notes']);
				
				//if search was successful, show it
				echo "<div style='float:left; clear:both'>".Form::success("Search successful. Found ".count($results)." results")."</div>";
				
				//set the default column
				$form = new Form("auto", 6);
				
				//create the headings for these
				$heading = array("VLAN ID", "Name", "Status", "Location", "Assigned To", "VLAN Distinguisher");
				
				$titles = array();
				
				$data = array();
				
				$handlers = array();
				$filters = array();
				
				// Loop through all vlans and set the name and status
				foreach ($results as $v_id =>$vlan_id) {
					   $my_vlan = new Vlan_database($v_id);
					   
					   array_push($handlers, "handleEvent('".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&v_id=".$v_id."')");
					   array_push($data, $my_vlan->get_vlan_id());
					   array_push($data, $my_vlan->get_name());
					   array_push($data, $my_vlan->get_status());
					   array_push($data, $my_vlan->get_location_name());
					   array_push($data, $my_vlan->get_assigned_to_name());
					   array_push($data, $my_vlan->get_vlan_distinguisher());
					   array_push($filters, $my_vlan->get_status());
					   
				}
				
				//show a table of all search results
				$form->setSortable(true); // or false for not sortable
				$form->setHeadings($heading);
				$form->setData($data);
				$form->setEventHandler($handlers);
				$form->setFilter($filters);
				
				//set the table size
				$form->setTableWidth("1024px");
				$form->setTitleWidth("20%");
				
				//export the search result into csv
				echo "<div id='csv_block' style='clear:both; float:left; margin-bottom:5px; margin-top:10px;'><input type='button' onclick='csv_report(\"plugins/IP%20Manager/export_csv.php?report_name=vlan_report&vlan_id=".$_POST['id']."&location=".$_POST['location']."&name=".$_POST['name']."&assigned=".$_POST['assigned']."&status=".$_POST['status']."&distinguish=".$_POST['distinguish']."&notes=".$_POST['notes']."\")' value='Export to CSV' /></div>";
				echo $form->showForm();
				
			}
			
			//create a form for adding vlan if that is called
			if($_GET['action'] == 'add')
			{
				$form = new Form("auto", 2);
				$v_id = $_GET['v_id'];
				$my_vlan = new Vlan_database($v_id);
				
				//create the headings for these
				$heading = array("New VLAN Information");
				
				//all the food
				$titles = array("VLAN ID", "Name", "Status", "Location", "Assigned To", "Notes", "VLAN Distinguisher");
				$vlan_id ="";
				
				if(isset($_GET['vlan_id']))
				{
					$vlan_id = $_GET['vlan_id'];	
				}
				
				//create the fields
				$data = array($vlan_id."","","ASSIGNED","","","","");
				$keys = array("id", "name", "status", "location", "assign", "notes", "distinguish");
				$fieldType = array("default", "default", "drop_down", "drop_down", "drop_down", "text_area", "default");
				$location = Location::get_locations();
				$status = array("RESERVED"=>"RESERVED", "ASSIGNED"=>"ASSIGNED");
				$assign = Contact::get_groups();
				
				$form->setSortable(true); // or false for not sortable
				$form->setHeadings($heading);
				$form->setType($status);
				$form->setType($location);
				$form->setType($assign);
				$form->setTitles($titles);
				$form->setData($data);
				$form->setDatabase($keys);
				$form->setFieldType($fieldType);
				
				//set the table size
				$form->setTableWidth("1024px");
				$form->setTitleWidth("20%");
				$form->setUpdateValue("addVLAN");
				$form->setUpdateText("Add VLAN");
				
				//create the form
				echo $form->editForm();
			}
			
			//create a form for searching
			if ($_GET['action'] == 'search' && !isset($_POST['searchVLAN']))
			{
				$form = new Form("auto", 2);
				$v_id = $_GET['v_id'];
				$my_vlan = new Vlan_database($v_id);
				
				//create the headings for these
				$heading = array("VLAN Search");
				
				//create the fields
				$titles = array("VLAN ID", "Name", "Status", "Location", "Assigned To", "Notes", "VLAN Distinguisher");
				$data = array("","","","","","","");
				$keys = array("id", "name", "status", "location", "assign", "notes", "distinguish");
				$fieldType = array("default", "default", "drop_down", "drop_down", "drop_down", "default", "default");
				$location = Location::get_locations();
				$status = array("RESERVED"=>"RESERVED", "ASSIGNED"=>"ASSIGNED");
				$assign = Contact::get_groups();
				
				$form->setSortable(false); // or false for not sortable
				$form->setHeadings($heading);
				$form->setType($status);
				$form->setType($location);
				$form->setType($assign);
				$form->setTitles($titles);
				$form->setData($data);
				$form->setDatabase($keys);
				$form->setFieldType($fieldType);
				
				//set the table size
				$form->setTableWidth("1024px");
				$form->setTitleWidth("20%");
				$form->setUpdateValue("searchVLAN");
				$form->setUpdateText("Search VLAN");
				//create the form
				echo $form->editForm();
			}
			
			//show all the vlans if that is called (ALL 4096 VLANS)
			if ($_GET['action'] == 'showAll')
			{
				$vlan_manager = new Vlan_database();
				$all_vlans = $vlan_manager->get_all_vlans();
				$num_max = 4096;
				
				$arr = array();
				foreach ($all_vlans as $id => $v_id)
				{
					$arr[$v_id] = $id;
				}
				//set the default column
				$form = new Form("auto", 6);
				
				//create the headings for these
				$heading = array("VLAN ID", "Name", "Status", "Location", "Assigned To", "VLAN Distinguisher");
				
				$titles = array();
				$data = array();
				$handlers = array();
				$filters = array();
				
				// Loop through all vlans and set the name and status
				//checks if vlan exists, if it does, skip this number
				for ($i=1; $i<$num_max; $i++)
				{
					if(in_array($i, $all_vlans))
					{
						foreach ($all_vlans as $id => $v_id)
						{
							if ($v_id == $i)
							{
								$my_vlan = new Vlan_database($id);
								if($my_vlan->get_id() !== NULL)
								{
									array_push($handlers, "handleEvent('".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&v_id=".$id."')");
									array_push($data, $my_vlan->get_vlan_id());
									array_push($data, $my_vlan->get_name());
									array_push($data, $my_vlan->get_status());
									array_push($data, $my_vlan->get_location_name());
									array_push($data, $my_vlan->get_assigned_to_name());
									array_push($data, $my_vlan->get_vlan_distinguisher());
									array_push($filters, $my_vlan->get_status());
								}	
							}
						}
					}
					else
					{
						//if it doesn't create a free vlan
						array_push($handlers, "handleEvent('".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&action=add&vlan_id=".$i."')");
						array_push($data, $i);
						array_push($data, "");
						array_push($data, "FREE");
						array_push($data, "");
						array_push($data, "");
						array_push($data, "");
						array_push($filters, "FREE");
					}
				}
				
				//create the form
				$form->setSortable(true); // or false for not sortable
				$form->setHeadings($heading);
				$form->setData($data);
				$form->setEventHandler($handlers);
				$form->setFilter($filters);
				
				//set the table size
				$form->setTableWidth("1024px");
				$form->setTitleWidth("20%");
				
				//export the search result into csv
				echo "<div id='csv_block' style='clear:both; float:left; margin-bottom:5px; margin-top:10px;'><input type='button' onclick='csv_report(\"plugins/IP%20Manager/export_csv.php?report_name=vlan_report&vlan_id=showALL\")' value='Export to CSV' /></div>";
				
				echo $form->showForm();
			}
		}
		//by default, show only assigned and reserved vlans
		else {
			$vlan_manager = new Vlan_database();
			$all_vlans = $vlan_manager->get_all_vlans(false);
		
			//set the default column
			$form = new Form("auto", 6);
			
			//create the headings for these
			$heading = array("VLAN ID", "Name", "Status", "Location", "Assigned To", "VLAN Distinguisher");
			
			$titles = array();
			$data = array();
			$handlers = array();
			$filters = array();
			
			// Loop through all vlans and set the name and status
			foreach ($all_vlans as $v_id =>$vlan_id) {
				   $my_vlan = new Vlan_database($v_id);
				   
				   array_push($handlers, "handleEvent('".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&v_id=".$v_id."')");
				   array_push($data, $my_vlan->get_vlan_id());
				   array_push($data, $my_vlan->get_name());
				   array_push($data, $my_vlan->get_status());
				   array_push($data, $my_vlan->get_location_name());
				   array_push($data, $my_vlan->get_assigned_to_name());
				   array_push($data, $my_vlan->get_vlan_distinguisher());
				   array_push($filters, $my_vlan->get_status());
				   
			}
			
			$form->setSortable(true); // or false for not sortable
			$form->setHeadings($heading);
			$form->setData($data);
			$form->setEventHandler($handlers);
			$form->setFilter($filters);
			
			//set the table size
			$form->setTableWidth("1024px");
			$form->setTitleWidth("20%");
			//export the search result into csv
			echo "<div id='csv_block' style='clear:both; float:left; margin-bottom:5px; margin-top:10px;'><input type='button' onclick='csv_report(\"plugins/IP%20Manager/export_csv.php?report_name=vlan_report&vlan_id=showALL\")' value='Export to CSV' /></div>";
			echo $form->showForm();
		}
		
		//update VLAN information when the update is called
		if(isset($_POST['updateInfo']))
		{
			$my_vlan = new Vlan_database($v_id);
			$my_vlan->set_name($_POST["name"]);
			$my_vlan->set_status($_POST["status"]);
			$my_vlan->set_location($_POST["location"]);
			$my_vlan->set_assigned_to($_POST["assign"]);
			$my_vlan->set_notes($_POST["notes"]);
			$my_vlan->set_vlan_distinguisher($_POST['distinguish']);
			
			if($my_vlan->update())
			{
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=vlan&v_id=".$v_id."&success=update\">";
			}
		}
	}
}
//if it's not part of family, then it's part of the reports
else
{
	echo "<div id='report'>";
	
	//create a button to toggle advance searches
	echo "<div id='toggle'><a onclick='toggle_search()'><p><input type='button' value='Open Advance Report'></p><p style='display:none'><input type='button' value='Close Advance Report'></p></a></div>";
	echo "<div id='report_filter'>";
	
	//display search box
	display_search_box("report_search", "Advance Report");
	echo $string;
	
	//go through the database to report
	echo "</div>";
	$IP_Search = new IP_Database();
	$family = 4;
	if(isset($_GET['report']))
	{
		$family = $_GET['report'];
	}
	
	//if the report is being searched
	if($_GET['report_search'])
	{
		//set the data
		$title = $_GET['title'];
		$str_tag = $_GET['tags'];
		
		//turn the tags back into an array
		while (strpos($str_tag, ", "))
		{
			$str_tag = str_replace(", ", ",", $str_tag);
		}
		$tags = explode(",", $str_tag);
		
		if(count($tags) == 1 && $tags[0] == "")
		{
			$tags = array();
		}
		
		//set the data
		$location = $_GET['location'];
		$owner = $_GET['owner'];
		$assigned_to = $_GET['assigned'];
		$status = $_GET['status'];
		
		//search the database
		$s_results = $IP_Search->search($title, $tags, $location, $owner, $assigned_to, $status, $family);
		$result_c_count = 0;
		$result_p_count = 0;
		
		//check how many results there are for children and parent netowkrs
		foreach ($s_results as $id=>$name)
		{
			if (!IP_Database::is_parent($id))
			{
				$result_c_count++;	
			}
			else
			{
				$result_p_count++;	
			}
		}
		
		//create the search string
		$search_str ="";
		if ($_GET['title'] != "") {$search_str .= " Name = ".$_GET['title'].", ";}
		if ($_GET['tags'] != "") {$search_str .= " Tags = ".$_GET['tags'].", ";}
		if ($_GET['location'] != "") {
			$loc = new Location($_GET['location']);
			$search_str .= " Location = ".$loc->get_name().", ";
		}
		if ($_GET['owner'] != "") {
			$own = new Contact($_GET['owner']);
			$search_str .= " Owner = ".$own->get_name().", ";
		}
		if ($_GET['assigned'] != "") {
			$assign = new Contact($_GET['assigned']);
			$search_str .= " Assigned To = ".$assign->get_name().", ";
		}
		if ($_GET['status'] != "") {$search_str .= " Status = ".$_GET['status'].", ";}
		
		$search_str = rtrim($search_str, ", ");
		
		//display what is searched and what is found
		Form::success("Searched for:".$search_str." | Results Found: ".$result_c_count." child network(s), ".$result_p_count." parent network(s)");
	}
	else
	{
		//just search for everything if nothing is searched
		$s_results = $IP_Search->search("", "", "", "", "", "", $family);
	}
	
	//if there's no result display nothing
	if (count($s_results)== 0)
	{
		$search_str= "";
	}
	
	//show the report
	display_report($ip_manager, $s_results, $search_str);
	echo "</div>";
}

//this functions retrieves all network from the database and displays them
function display_all_networks($arr, $ip_manager)
{
	//if there are no networks in the array show that
	$string = "";
	if(empty($arr))
	{
		$string.= Form::warning("NO Networks!");
		//return false;		
	}
	
	//loop through the database to see each individual network and their details
	foreach ($arr as $id=>$name)
	{
		$ip_2 = new IP_Database($id);
		
		$ip_manager->set_IP($ip_2->get_address_int()."/".$ip_2->get_subnet_size(), $ip_2->get_family());
		
		$class = "network_box";
		//a function to see which network is being selected right now
		if(isset($_GET['parent_id']))
		{
			if($_GET['parent_id'] == $id)
			{
				$class = "network_box active";
			}
		}
		$string.= "<a href='".$_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className']."&family=".$_GET['family']."&parent_id=".$id."' class='ipdesc tooltip' rel='".$ip_manager->get_IP()."@".$id."' title='".$ip_2->get_title()."'><div class='".$class."'><span id='status".$id."'><font style='color:".$color."'>".$ip_manager->get_IP()."</font></span></div></a>";	
	}
	return $string;
}

//This function displays all the ip----------------------------------------------------------------------------------------
function display_all_ip($arr, $ip_manager)
{
	global $string;
	
	//if nothing is given show no ip
	if(empty($arr))
	{
		$string.= Form::warning("NO IP!");
		//return false;		
	}
	
	//create the modal box style sheets
	$string.= "<style>";
	foreach ($arr as $id => $name)
	{
		$string.= "#modalBox #dialog".$id;
		$string.= "{
			width:auto;
			max-width: 80%;
			min-width:40%;
  			height:auto;
			padding:10px;
			padding-top:10px;
		}
		";
		
		$string.= "#modalBox #dialog_host".$id;
		$string.= "{
			width:auto;
			max-width: 80%;
			min-width:40%;
  			height:auto;
			padding:10px;
			padding-top:10px;
		}
		";
		
		$string.= "#modalBox #dialog_calc".$id;
		$string.= "{
			width:auto;
			max-width: 80%;
			min-width:40%;
  			height:auto;
			padding:10px;
			padding-top:10px;
		}";
	}
	$string.= "</style>";
	
	//start the tree view
	if(IP_Database::get_master($id) == $id)
	{
		$string.= "<ul id='browser' class='filetree'>";
	}
	else
	{
		$string.= "<ul>";	
	}
	
	//create individual modal views and tree roots for each networks
	foreach ($arr as $id=>$name)
	{
		//get ip info
		$ip_2 = new IP_Database($id);
		if($ip_2->get_netblock_id() == NULL)
		{
			return false;
		}
		//make a form
		$string.= "<li id='ip_row".$id."'><a name='".$id."'></a>";
		$string .= "<div id='netblock".$id."'><form method='post' id='form_ip".$id."'>";
		
		//do some ip calculation
		$ip_manager->set_IP($ip_2->get_address_int()."/".$ip_2->get_subnet_size(), $ip_2->get_family());
		
		//set the status color
		$color="black";
		$tag_str = "";
		$status = $ip_2->get_status();
		
		if(IP_Database::is_parent($id))
		{
			if($status != "PARENT")
			{
				$ip_2->set_status("PARENT");
				$ip_2->update();
			}
		}
		
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
		
		//make a modal window for ip calculator
		$string.= "<div id='modalBox'>
			<div id='dialog_calc".$id."' class='window'>
			<div style='clear:both;'></div>";
		$string.= "<a href='#'class='close' /><img src='icons/close.png'></a>";
			
		$string.= $ip_manager->print_all();
					
		$string.= "</div>
			<div id='mask'></div>
			</div>";
		
		//check if it's stub to give the network proper icons
		$string .= "<span id='stub_img".$id."'>";
		if ($ip_2->is_stub() ==1)
		{
			$string.= "<img id='stub' src='icons/stub2.png' />";
		}
		$string .= "</span>";
		
		//write the initial ip info and give it the status color
		$string.= "<a name='modal' href='#dialog".$id."' style='cursor:default;' class='ipdesc' rel='".$ip_manager->get_IP()."@".$id."'><span id='status".$id."'><font style='color:".$color."'>".$ip_manager->get_IP()."</font></span></a>";
		
		//display the calculator icon
		$string.= "<a name='modal' href='#dialog_calc".$id."' style='cursor:default;' class='ipcalc' rel='".$ip_manager->get_IP()."@".$id."'><img src='icons/calculator.png' /></a>";
		
		//have a hidden input
		$string.= "<input type='hidden' name='id' value='".$id."' />";
		
		//if it's a stub give it a host icon else give it a gear icon
		if ($ip_2->is_stub() ==1)
		{
			$string.= "<a name='modal' href='#dialog_host".$id."'><span id='st_ho_img".$id."'><img src='icons/host.png' /></span></a>";
		}
		else
		{
			if(!IP_Database::is_parent($id))
			{
				if(is_numeric($ip_2->get_parent_id()))
				{
					$p_netblock = new IP_Database($ip_2->get_parent_id());
					if(!$p_netblock->is_stub())
					{
						$string.= "<a name='modal' href='#dialog_host".$id."'><span id='st_ho_img".$id."'><img src='icons/gears.png' /></span></a>";	
					}
				}
				else {
					$string.= "<a name='modal' href='#dialog_host".$id."'><span id='st_ho_img".$id."'><img src='icons/gears.png' /></span></a>";
				}
			}
		}
			
		//give it proper icons if it has the location, or assigned to assigned
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
		
		//show the title, location and assigned to and the make an update icon
		$string.= "<span id='ip_name".$id."'>".$ip_title.$ip_loc.$ip_assign."</span><p id='update".$id."' style='display:inline'></p>";
			//$string.= "<input type='image' src='images/ip_switch_split.png' value='change_to_stub' alt='Submit' style='background-color:transparent; border:none;' />";
		
		//	
		$string.= "<div id='modalBox'>
			<div id='dialog".$id."' class='window'>
			<div style='clear:both;'></div>";
		$string.= "<a href='#'class='close' /><img src='icons/close.png'></a>";
			
		$string.= "<font class='header2'>".$ip_2->get_address_ip()."</font><br/><hr /><font class='header'>Name</font> ";
			
		$string.= "<input type='text' id='title".$id."' name='title' style='width:100%; margin-bottom:10px;' maxchar='100' value='".$ip_2->get_title()."' />";
			
		$string.= "<font class='header'>Comment</font><textArea id='description".$id."' name='description' style='width:100%; margin-bottom:10px;' rows='5' >".$ip_2->get_description()."</textArea>";
			
		
		$string.= "Location: <select name='location' id='location".$id."'>";
			
		if ($ip_2->get_location_id() === NULL){
			$string.= "<option selected value='NULL'>No Location</option>";
		}
		else {
			$string.= "<option value='NULL'>No Location</option>";
		}
			
		foreach (Location::get_locations() as $lid => $lname)
		{
			if ($lid == $ip_2->get_location_id())
			{$string.= "<option selected value='".$lid."'>".$lname."</option>";}
			else {$string.= "<option value='".$lid."'>".$lname."</option>";}
		}
							
		$string.="</select> ";
			
		$string.= "Owner: <select name='owner' id='owner".$id."'>";
		if ($ip_2->get_owner_id() === NULL){
			$string.= "<option selected value='NULL'>No Owner</option>";
		}
		else {
			$string.= "<option value='NULL'>No Owner</option>";
		}
			
		foreach (Contact::get_groups() as $g_id => $g_name)
		{
			if ($g_id == $ip_2->get_owner_id())
			{$string.= "<option selected value='".$g_id."'>".$g_name."</option>";}
			else {$string.= "<option value='".$g_id."'>".$g_name."</option>";}
		}
							
		$string.="</select> ";
			
		$string.= "Assigned To: <select name='assigned' id='assigned".$id."'>";
		if ($ip_2->get_owner_id() === NULL){
			$string.= "<option selected value='NULL'>No Assigned To</option>";
		}
		else {
			$string.= "<option value='NULL'>No Assigned To</option>";
		}
			
		foreach (Contact::get_groups() as $g_id => $g_name)
		{
			if ($g_id == $ip_2->get_assigned_to_id())
			{$string.= "<option selected value='".$g_id."'>".$g_name."</option>";}
			else {$string.= "<option value='".$g_id."'>".$g_name."</option>";}
		}
							
		$string.="</select> ";
		
		$string.="<div style='width:100%'>";
		if(!IP_Database::is_parent($id))
		{
			$string.= "Status: <select name='status' id='status".$id."'>";
				
			$all_status = array ("FREE", "RESERVED", "ASSIGNED");
				
			foreach ($all_status as $s_id => $s_name)
			{
				if ($ip_2->get_status() == $s_name)
				{$string.= "<option selected value='".$s_name."'>".$s_name."</option>";}
				else {$string.= "<option value='".$s_name."'>".$s_name."</option>";}
			}
			$string.= "</select> ";
		}
		
		if(is_numeric($ip_2->get_parent_id()))
		{
			$p_netblock = new IP_Database($ip_2->get_parent_id());
			if(!$p_netblock->is_stub())
			{
				if(!IP_Database::is_parent($id))
				{
					$string.= "Stub: <select name='stub' id='stub".$id."'>";
					if($ip_2->is_stub() == 0)
					{
						$string.= "<option value='0' selected >not stub</option>
						<option value='1' >stub</option>";
					}
					else
					{
						$string.= "<option value='0' >not stub</option>
						<option  value='1' selected >stub</option>";
					}
					$string.= "</select>";
				}	
			}
		}
		else
		{
			if(!IP_Database::is_parent($id))
			{
				$string.= "Stub: <select name='stub' id='stub".$id."'>";
				if($ip_2->is_stub() == 0)
				{
					$string.= "<option value='0' selected >not stub</option>
					<option value='1' >stub</option>";
				}
				else
				{
					$string.= "<option value='0' >not stub</option>
					<option  value='1' selected >stub</option>";
				}
				$string.= "</select>";
			}
		}
		$string.="</div>";
		$string.= "<br /><font class='header'>Tags</font><input type='text' id='tags".$id."' class='tags_input' name='tags' style='width:100%; margin-bottom:10px;' value='";
		foreach ($ip_2->get_tags() as $t_id => $t_name)
		{
			$tag_str .= $t_name.", ";
		}
		$tag_str = rtrim($tag_str, " ");
		$tag_str = str_replace("<", "", $tag_str);
		$tag_str = str_replace(">", "", $tag_str);
		$tag_str = str_replace("\"", "", $tag_str);
		$tag_str = str_replace("\'", "", $tag_str);
		$string.= rtrim($tag_str, ",");
			
		$string.= "' />";
			
		$string.= " <input type='button' class='close' id='ip_update' name='ip_update' value='UPDATE' onclick='return update_info(".$id.")' />";
		
		$temp_netblock = new IP_Database($ip_2->get_parent_id());
		if(!$temp_netblock->is_stub())
		{
			$all_child_netblock_num = count(get_all_children($ip_2->get_netblock_id(), $ip_2->get_family()));
			$string.= " <input type='submit' id='ip_force_delete' name='ip_force_delete' onclick='return confirmForceDelete(".$all_child_netblock_num.")' value='FORCE DELETE' style='float:right; background:red; color:white;'/>";
		}
		
		$string.= "<input type='hidden' name='id' value='".$id."' />
		<input type='submit' id='ip_delete' name='ip_delete' onclick='return confirmSubmit()' value='DELETE NETWORK' />";
		 $string.= "</div>
			 <div id='mask'></div>
				</div>";
		
		if(!IP_Database::is_parent($id))
		{
			$string.= "<div id='modalBox'>
			<div id='dialog_host".$id."' class='window'>
			<div style='clear:both;'></div>";
			$string.= "<a href='#'class='close' /><img src='icons/close.png' /></a>";
			$string.= "<div id='loader".$id."' style='display:inline'>";
			$string.= "<font class='header'>".$ip_2->get_address_ip()."</font><br/>";
			
			//DYNAMIC CHANGE HERE
			$string.= "<div id='host_split".$id."'>";
			if($ip_2->is_stub() == 0)
			{	
				if ($ip_manager->get_family()==4)
				{
					if ($ip_2->get_subnet_size()!=32)
					{
						$string.= "<select name='split'>";
						for ($i = $ip_manager->get_length(); $i<32; $i++)
						{
							$string.= "<option value='".($i+1)."'>".($i+1)."</option>";
						}
						$string.="</select>";
						$string.= " <input type='submit' name='ip_split_submit' value='SPLIT NETWORK' />";
					}
					else {$string.= "<span style='color:red; font-size:14px'>CANNOT SPLIT ANY FURTHER</span>";}
					
					$parent_id = IP_Database::get_parent($id);
					if(is_numeric($parent_id))
					{
						$temp_ip = new IP_Database($parent_id);
						if($temp_ip->is_stub() != true)
						{
							$string.= "<br/>";
							$string.= "<select name='merge'>";
							for ($i = ($ip_manager->get_length()-1); $i>=$temp_ip->get_subnet_size(); $i--)
							{
								$string.= "<option value='".($i)."'>".($i)."</option>";
							}
							$string.="</select>";
							$string .= " <input type='submit' name='ip_merge_submit' value='MERGE NETWORK' />";
						}
					}
				}
				else if ($ip_manager->get_family()==6)
				{
					if ($ip_2->get_subnet_size()!=128)
					{
						$string.= "<select name='split'>";
						for ($i = $ip_manager->get_length(); $i<128; $i++)
						{
							$string.= "<option value='".($i+1)."'>".($i+1)."</option>";
						}
						$string.="</select>";
						$string.= " <input type='submit' name='ip_split_submit' value='SPLIT NETWORK' />";
						
					}
					else {$string.= "<span style='color:red; font-size:14px'>CANNOT SPLIT ANY FURTHER</span>";}
					$parent_id = IP_Database::get_parent($id);
					if(is_numeric($parent_id))
					{
						$temp_ip = new IP_Database($parent_id);
						if($temp_ip->is_stub() != true)
						{
							$string.= "<br/>";
							$string.= "<select name='merge'>";
							for ($i = ($ip_manager->get_length()-1); $i>=$temp_ip->get_subnet_size(); $i--)
							{
								$string.= "<option value='".($i)."'>".($i)."</option>";
							}
							$string.="</select>";
							$string .= " <input type='submit' name='ip_merge_submit' value='MERGE NETWORK' />";
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
						$string.= "<input type='text' id='ip_hostip".$id."' name='ip_hostip' style='width:200px;'/> / 
						<input type='text' maxlength='3' id='ip_hostsubnet".$id."' name='ip_hostsubnet' style='width:50px;' value='32'/>
						<input type='submit' name='ip_host' value='ADD HOST' />";
						//<input type='button' class='close' id='ip_host_a' name='ip_host_a' value='ADD HOST ASYNC' onclick='return add_host(".$id.")' />
					}
					else {$string.= "<span style='color:red; font-size:14px'>CANNOT CREATE ANYMORE HOST</span>";}
				}
				else if ($ip_manager->get_family()==6)
				{
					if ($ip_2->get_subnet_size()!=128)
					{
						$string.= "<input type='text' name='ip_hostip' style='width:200px;'/> / 
						<input type='text' name='ip_hostsubnet' maxlength='3' style='width:50px;' value='128'/>
						<input type='submit' name='ip_host' value='ADD HOST' />";
					}
					else {$string.= "<span style='color:red; font-size:14px'>CANNOT CREATE ANYMORE HOST</span>";}
				}
			}
			$string.= "</div>";
			$string.= "<hr/>Inherit: <input type='checkbox' name='inh_loc' checked /> Location | <input type='checkbox' name='inh_owner' checked /> Owner | ";
			$string.= "<input type='checkbox' name='inh_assigned' /> Assigned to | <input type='checkbox' name='inh_status' /> Status |  <input type='checkbox' name='inh_tags' /> Tags ";
			
			$string.= "</div>";
					
			$string.= "</div>
			<div id='mask'></div>
			</div>";
			
			$string .= "</div>";
			$string.= "</form>";
		}//NEED TO MAKE THE INHERITANCE UPDATE
		else
		{
			if($ip_2->is_stub()==1)
			{	
				$string.= "<div id='modalBox'>
				<div id='dialog_host".$id."' class='window'>
				<div style='clear:both;'></div>";
				$string.= "<a href='#' class='close' /><img src='icons/close.png' /></a>";
				$string.= "<font class='header'>".$ip_2->get_address_ip()."</font><br/>";
				
				if ($ip_manager->get_family()==4)
				{
					if ($ip_2->get_subnet_size()!=32)
					{
						$string.= "<input type='text' id='ip_hostip' name='ip_hostip' style='width:200px;'/> / 
						<input type='text' id='ip_hostsubnet' name='ip_hostsubnet' style='width:50px;' value='32'/>
						<input type='submit' name='ip_host' value='ADD HOST' />";
					}
					else {$string.= "<span style='color:red; font-size:14px'>CANNOT CREATE ANYMORE HOST</span>";}
				}
				else if ($ip_manager->get_family()==6)
				{
					if ($ip_2->get_subnet_size()!=128)
					{
						$string.= "<input type='text' name='ip_hostip' style='width:200px;'/> / 
						<input type='text' name='ip_hostsubnet' style='width:50px;' value='128'/>
						<input type='submit' name='ip_host' value='ADD HOST' />";
						
					}
					else {$string.= "<span style='color:red; font-size:14px'>CANNOT CREATE ANYMORE HOST</span>";}
				}
				$string.= "<hr/>Inherit: <input type='checkbox' name='inh_loc' checked /> Location | <input type='checkbox' name='inh_owner' checked /> Owner | ";
				$string.= "<input type='checkbox' name='inh_assigned' /> Assigned to | <input type='checkbox' name='inh_status' /> Status |  <input type='checkbox' name='inh_tags' /> Tags ";
				$string.= "<div id='mask'></div>
				</div></div>";
			}
			
			$string .= "</div>";
			$string.= "</form>";
			
			display_all_ip(IP_Database::get_all_ip($id),  $ip_manager);
			
		}
		$string.= "\n</li></a>";
	}
	$string.= "</ul>";
	
	return $string;
}


//DISPLAY SEARCH RESULT!!!----------------------------------------------------------------------------------------
function display_search_results($s_results, $ip_manager)
{
	if(empty($s_results))
	{
		echo Form::warning("NO IP!");
		return false;		
	}
	echo "<style>";
	foreach ($s_results as $id => $name)
	{
		echo "#modalBox #dialog".$id;
		echo "{
			width:auto;
			max-width: 80%;
			min-width:40%;
  			height:auto;
			padding:10px;
			padding-top:10px;
		}
		";
		
		echo "#modalBox #dialog_calc".$id;
		echo "{
			width:auto;
			max-width: 80%;
			min-width:40%;
  			height:auto;
			padding:10px;
			padding-top:10px;
		}";
	}
	echo "</style>";
	
	//set the default column
	$form = new Form("auto", 6);
	
	//create the headings for these
	$heading = array("Netblock", "Name", "Location", "Owner", "Assigned To", "Status");
	
	$data = array();
	
	// Loop through all vlans and set the name and status
	foreach ($s_results as $id =>$n_name) {
		   $my_netblock = new IP_Database($id);
		   
		   $color="black";
		   $status = $my_netblock->get_status();
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
		   
		   array_push($data,"<a name='modal' href='#dialog".$id."' style='cursor:default; color:".$color."' class='ipdesc' rel='".$my_netblock->get_address_ip()."@".$id."'>".$my_netblock->get_address_ip()." </a><a name='modal' href='#dialog_calc".$id."' style='cursor:default; color:".$color."' class='ipcalc' rel='".$my_netblock->get_address_ip()."@".$id."'><img src='icons/calculator.png' /></a><input type='hidden' name='id' value='".$id."' />");
		   
		   array_push($data,$my_netblock->get_title());
		   array_push($data,$my_netblock->get_location_name());
		   array_push($data,$my_netblock->get_owner_name());
		   array_push($data,$my_netblock->get_assigned_to_name());
		   array_push($data,$my_netblock->get_status());
		   
		   echo "<div id='modalBox'>
			<div id='dialog".$id."' class='window'>
			<div style='clear:both;'></div>
			<form method='post'>";
			
			echo "<a href='#'class='close' /><img src='icons/close.png'></a>";
			echo "<input type='hidden' name='id' value='".$id."' />";
			
			echo "<font class='header2'>".$my_netblock->get_address_ip()."</font><hr /><br/><font class='header'>Name</font>";
			
			echo"</select> ";
			
			echo "<input type='text' name='title' style='width:100%; margin-bottom:10px;' maxchar='100' value='".$my_netblock->get_title()."' />";
			
			echo "<font class='header'>Comment</font><textArea name='description' style='width:100%; margin-bottom:10px;' rows='5' >".$my_netblock->get_description()."</textArea>";
			
			
			echo "Location: <select name='location'>";
			
			if ($my_netblock->get_location_id() === NULL){
				echo "<option selected value='NULL'>No Location</option>";
			}
			else {
				echo "<option value='NULL'>No Location</option>";
			}
			
			foreach (Location::get_locations() as $lid => $lname)
			{
				if ($lid == $my_netblock->get_location_id())
				{echo "<option selected value='".$lid."'>".$lname."</option>";}
				else {echo "<option value='".$lid."'>".$lname."</option>";}
			}
							
			echo"</select> ";
			
			echo "Owner: <select name='owner'>";
			if ($my_netblock->get_owner_id() === NULL){
				echo "<option selected value='NULL'>No Owner</option>";
			}
			else {
				echo "<option value='NULL'>No Owner</option>";
			}
			
			foreach (Contact::get_groups() as $g_id => $g_name)
			{
				if ($g_id == $my_netblock->get_owner_id())
				{echo "<option selected value='".$g_id."'>".$g_name."</option>";}
				else {echo "<option value='".$g_id."'>".$g_name."</option>";}
			}
							
			echo"</select> ";
			
			echo "Assigned To: <select name='assigned'>";
			if ($my_netblock->get_owner_id() === NULL){
				echo "<option selected value='NULL'>No Assigned To</option>";
			}
			else {
				echo "<option value='NULL'>No Assigned To</option>";
			}
			
			foreach (Contact::get_groups() as $g_id => $g_name)
			{
				if ($g_id == $my_netblock->get_assigned_to_id())
				{echo "<option selected value='".$g_id."'>".$g_name."</option>";}
				else {echo "<option value='".$g_id."'>".$g_name."</option>";}
			}
							
			echo"</select> ";
			
			if(!IP_Database::is_parent($id))
			{
				echo "Status: <select name='status'>";
				
				$all_status = array ("FREE", "RESERVED", "ASSIGNED");
				
				foreach ($all_status as $s_id => $s_name)
				{
					if ($my_netblock->get_status() == $s_name)
					{echo "<option selected value='".$s_name."'>".$s_name."</option>";}
					else {echo "<option value='".$s_name."'>".$s_name."</option>";}
				}
				echo "</select> ";
			}
			
			if(!IP_Database::is_parent($id))
			{
				echo "Stub: <select name='stub' id='stub".$id."'>";
				if($my_netblock->is_stub() == 0)
				{
					echo "<option value='0' selected >not stub</option>
					<option value='1' >stub</option>";
				}
				else
				{
					echo "<option value='0' >not stub</option>
					<option  value='1' selected >stub</option>";
				}
				echo "</select>";
			}
			
			echo "<br /><font class='header'>Tags</font><input type='text' name='tags'  class='tags_input' style='width:100%; margin-bottom:10px;' value='";
			
			foreach ($my_netblock->get_tags() as $t_id => $t_name)
			{
				echo $t_name.", ";
			}
			
			echo "' />";
			
			echo " <input type='submit' id='ip_update' name='ip_update' value='UPDATE' />
			<input type='submit' id='ip_delete' name='ip_delete' onclick='return confirmSubmit()' value='DELETE NETWORK' />
			</form>";
			
			echo "</div>
				<div id='mask'></div>
				</div>";
			
			$ip_manager->set_ip($my_netblock->get_address_ip());
			
			echo "<div id='modalBox'>
			<div id='dialog_calc".$id."' class='window'>
			<div style='clear:both;'></div>";
			echo "<a href='#'class='close' /><img src='icons/close.png'></a>";
					
			echo $ip_manager->print_all($my_netblock->get_title());
							
			echo "</div>
				<div id='mask'></div>
				</div>";
	}
	
	$form->setSortable(true); // or false for not sortable
	$form->setHeadings($heading);
	$form->setData($data);
	
	//set the table size
	$form->setTableWidth("1024px");
	
	echo "<div id='csv_block' style='clear:both; float:left; margin-bottom:5px; margin-top:10px;'><input type='button' onclick='csv_report(\"plugins/IP%20Manager/export_csv.php?report_name=ip_report&title=".$_GET['title']."&location=".$_GET['location']."&owner=".$_GET['owner']."&assigned=".$_GET['assigned']."&status=".$_GET['status']."&tags=".$_GET['tags']."\")' value='Export to CSV' /></div>";
	
	//$_GET['title'] $_GET['tags'] $_GET['location'] $_GET['owner'] $_GET['assigned'] $_GET['status'];
	//title=$_GET['title']&location=$_GET['location']&owner=$_GET['owner']&assigned=$_GET['assigned']&status=$_GET['status']&tags=$_GET['tags']
	echo $form->showForm();	
}

function display_report($ip_manager, $arr = array(), $search_str = "")
{
	$ip_fam = 4;
	if(isset($_GET['report']))
	{
		$ip_fam = $_GET['report'];
	}

	$all_ip = $arr;	
	
	$free = 0;
	$reserved = 0;
	$assigned = 0;
	$allstats = 0;
	
	$totalspace = "0";
	$limit = 0;
	
	foreach ($all_ip as $n_ip => $n_name)
	{
		if (!IP_Database::is_parent($n_ip))
		{
			$t_ip = new IP_Database($n_ip);
			$allstats++;
			$ip_manager->set_IP($t_ip->get_address_ip(), $ip_fam);
			
			switch ($t_ip->get_status())
			{
				case "FREE":
				$free = bcadd($ip_manager->get_ipPerNet(), $free);
				break;
				
				case "RESERVED":
				$reserved = bcadd($ip_manager->get_ipPerNet(), $reserved);
				break;
				
				case "ASSIGNED":
				$assigned= bcadd($ip_manager->get_ipPerNet(), $assigned);
				break;
			}
			
			$totalspace = bcadd($ip_manager->get_ipPerNet(), $totalspace);
		}		
	}
	
	$limit = strlen($totalspace)-10;
	if($limit < 10)
	{
		$limit = 0;
	}
	
	//set the default column
	$form = new Form("auto", 2);
	
	$temp_space = explode(".", $totalspace);
	$totalspace = $temp_space[0];
	
	if($totalspace == 0)
	{
		$divisor = 1;
	}
	else {
		$divisor = $totalspace;	
	}
	//their calories and locations correspondingly
	
	$num_slash = "";
	if($_GET['report'] == 6)
	{	
	
		$num_slash = "Number of /48s";
		$data_slash = bcdiv($totalspace, bcpow(2, 80), 2);
		$num_slash2 = "Number of /64s";
		$data_slash2 = bcdiv($totalspace, bcpow(2, 64), 2);
		//create the headings for these
		if($search_str !="")
		{
			$search_str = " for ".$search_str;
		}
		
		$heading = array("IPV6 Report". $search_str);
		
		$free = bcdiv($free, bcpow(10, $limit), 2);
		$assigned = bcdiv($assigned, bcpow(10, $limit), 2);
		$reserved = bcdiv($reserved, bcpow(10, $limit), 2);
		$divisor = bcdiv($divisor, bcpow(10, $limit), 2);
		if ($divisor ==0)
		{
			$divisor = 1;	
		}
		
		$titles = array("Number of IPs", $num_slash, $num_slash2, "Percent of free IPs", "Percent of assigned IPs", "Percent of reserved IPs");
	
		$data = array(number_format($totalspace)." IPs in ".number_format($allstats)." Networks", number_format($data_slash), number_format($data_slash2),bcmul(bcdiv($free, $divisor, 5),100,2)."%", bcmul(bcdiv($assigned, $divisor, 5),100, 2)."%" ,bcmul(bcdiv($reserved, $divisor, 5),100,2)."%");
	}
	else if($_GET['report'] == 4 || !isset($_GET['report']))
	{
		$num_slash = "Number of /24s";
		$data_slash = bcdiv($totalspace, 256, 2);
		//create the headings for these
		if($search_str !="")
		{
			$search_str = " for ".$search_str;
		}
		$heading = array("IPV4 Report".$search_str);
		
		$titles = array("Number of IPs", $num_slash,"Percent of free IPs", "Percent of assigned IPs", "Percent of reserved IPs");
	
		$data = array(number_format($totalspace)." IPs in ".number_format($allstats)." Networks", number_format($data_slash), bcmul(bcdiv($free, $divisor, 5),100,2)."%", bcmul(bcdiv($assigned, $divisor, 5),100, 2)."%" ,bcmul(bcdiv($reserved, $divisor, 5),100,2)."%");
	}
	
	
	$form->setSortable(false); // or false for not sortable
	$form->setTitles($titles);
	$form->setHeadings($heading);
	$form->setData($data);
			
	//set the table size
	$form->setTableWidth("600px");
	$form->setTitleWidth("20%");
	echo $form->showForm();
	$chart_arr = array(free=>$free, assigned=>$assigned, reserved=>$reserved);
	print report_chart($chart_arr);
}


function report_chart($data) {
	      
	$title = new title( "Availability Report " );

	$pie = new pie();
	$pie->set_alpha(0.9);
	$pie->radius(90);
	//$pie->start_angle(100);
	$pie->add_animation( new pie_fade() );
	$pie->set_tooltip( '#label#: #percent#<br>#val# of #total#<br>' );
    $status_colors= array(
	        free => '#77CC6D',    // (green)
		assigned => '#FF0000',    // Red
		reserved => '#6D86CC',    // profit (blue)
			free2 => '#77CC6D',    // (green)
		assigned2 => '#FF0000',    // Red
		reserved2 => '#6D86CC',    // profit (blue)
	);
    $status_name= array(
	        free => 'FREE',    
		assigned => 'ASSIGNED',    
		reserved => 'RESERVED',
			free2 => 'FREE',    
		assigned2 => 'ASSIGNED',    
		reserved2 => 'RESERVED',
	);
		

	$col = array();	
	$d = array();	
	foreach ($data as $name => $value) {
		if ($value > 0) {
			$d[] = new pie_value($value*1, "$status_name[$name]");
			array_push($col, $status_colors[$name]);
		}
	}
	$pie->set_values( $d );
	$pie->set_colours($col);

	$chart = new open_flash_chart();
	$chart->set_title( $title );
	$chart->add_element( $pie );
	$chart->x_axis = null;
	$chart->set_bg_colour('#202020');
        $title->set_style( "{font-size: 16px; font-family: Times New Roman; font-weight: bold; color: #000; text-align: center;}" );
        $chart->set_bg_colour( '#FFFFFF' );
	$chart->set_title( $title );


        // This is the VIEW section:
        // Should print this first.
        //
        $heading = "
        <script type='text/javascript'>
    
        var data = ". $chart->toPrettyString() ."

        </script>


        <script type=\"text/javascript\">
 
        OFC = {};
 
        OFC.jquery = {
        name: 'jQuery',
        version: function(src) { return $('#'+ src)[0].get_version() },
        rasterize: function (src, dst) { $('#'+ dst).replaceWith(OFC.jquery.image(src)) },
        image: function(src) { return \"<img src='data:image/png;base64,\" + $('#'+src)[0].get_img_binary() + \"' />\"},
        popup: function(src) {
        var img_win = window.open('', 'Charts: Export as Image')
        with(img_win.document) {
            write('<html><head><title>Charts: Export as Image<\/title><\/head><body>' + OFC.jquery.image(src) + '<\/body><\/html>') }
                // stop the 'loading...' message
                img_win.document.close();
        }
        }
 
        // Using_ an object as namespaces is JS Best Practice. I like the Control.XXX style.
        //if (!Control) {var Control = {}}
        //if (typeof(Control == \"undefined\")) {var Control = {}}
        if (typeof(Control == \"undefined\")) {var Control = {OFC: OFC.jquery}}
 
 
        // By default, right-clicking on OFC and choosing \"save image locally\" calls this function.
        // You are free to change the code in OFC and call my wrapper (Control.OFC.your_favorite_save_method)
        // function save_image() { alert(1); Control.OFC.popup('my_chart') }
        function save_image() { alert(\"Your image will be displayed in a new window\"); OFC.jquery.popup('my_chart') }
        </script>
        <div id='my_chart' style='float:left; margin-left:28px;'></div>
        ";
	return $heading;

}

function display_search_box($sb_name="search", $header="Search")
{
	echo "<form method='get'>";
	echo "<font class='header2'>".$header."</font><hr />";
	echo "<font class='header'>Name</font><input type='text' name='title' style='width:100%; margin-bottom:10px;' maxchar='100'/>";
	//tab=devices.php&pluginID=4&className=IPmanager
	echo "<input type='hidden' name='tab' value='".$_GET['tab']."' />";
	echo "<input type='hidden' name='pluginID' value='".$_GET['pluginID']."' />";
	echo "<input type='hidden' name='className' value='".$_GET['className']."' />";
	
	echo "Location: <select name='location'>";
	echo "<option value=''></option>";
	foreach (Location::get_locations() as $lid => $lname)
	{
		echo "<option value='".$lid."'>".$lname."</option>";
	}
								
	echo"</select> ";
				
	echo "Owner: <select name='owner'>";
	echo "<option value=''></option>";
	foreach (Contact::get_groups() as $g_id => $g_name)
	{
		echo "<option value='".$g_id."'>".$g_name."</option>";
	}
								
	echo"</select> ";
				
	echo "Assigned To: <select name='assigned'>";
	echo "<option value=''></option>";	
	foreach (Contact::get_groups() as $g_id => $g_name)
	{
		echo "<option value='".$g_id."'>".$g_name."</option>";
	}
								
	echo"</select> ";
		
	echo "Status: <select name='status'>";
				
	$all_status = array ("FREE", "RESERVED", "ASSIGNED");
	echo "<option value=''></option>";	
	foreach ($all_status as $s_id => $s_name)
	{
		echo "<option value='".$s_name."'>".$s_name."</option>";
	}
								
	echo"</select> ";
				
	
	echo "<br /><font class='header'>Tags</font><input type='text'  class='tags_input' name='tags' style='width:100%; margin-bottom:10px;' />";
				
	echo " <input type='submit' name='".$sb_name."' value='SEARCH' />";
	
	
	echo "</form>";
}

function get_all_children($id, $family)
{
	$all_child_ip = IP_Database::get_all_ip($id, $family);
	$ips_to_delete = array();
	foreach($all_child_ip as $a_id => $addr)
	{
		$temp_netblock = new IP_Database($a_id);
		$t_all_child = IP_Database::get_all_ip($temp_netblock->get_netblock_id(), $temp_netblock->get_family());
		if(!empty($t_all_child))
		{
			$ips_to_delete = array_merge($ips_to_delete, get_all_children($a_id, $family));
		}
		$ips_to_delete = array_merge($ips_to_delete, array($a_id));
	}
	return $ips_to_delete;
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

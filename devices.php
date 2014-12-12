<?php
include_once("sessionCheck.php");
//control bar, $_GET['mode'] is used to determine if this site is in Ajax mode, if it is don't repeat functionalities that are already executed without AJAX
if(!isset($_GET['mode']))
{include("controlBar.php");}

?>

<?
if(!isset($_GET['mode']))
{
?>
<div id="main">
<h1 id="mainTitle">DEVICES</h1>
<?
}

//inserts all the javascripts
?>
<script type="text/javascript" src="js/modal-message.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-content.js"></script>

<?
include_once "classes/Device.php";
include_once 'classes/DeviceForm.php';
include_once 'classes/EdittingTools.php';
include_once 'classes/PopLocations.php';
include_once 'classes/PrivateData.php';
include_once 'classes/AAA.php';
		
//Make a new contact, a new tool bar, and a new form
$tool = new EdittingTools();
$deviceForm = new DeviceForm("auto", 3);
$devices = new Device();
$status;

//infoKey generates a set of key names to store the key values
$deviceKey = array("name", "device_fqdn", "location", "device_type", "snmp_ro","device_oob", "notes" );
				
//heading is the array of headlines in the table
$headings = array("Device Information");
							
//titles are the subcategories for each headline. In the array "heading" means make a room there for the headline
$titles = array("Device Name", "Management IP/FQDN", "Device Location", "Device Type", 
	"SNMP Community String.tip.Read only SNMP community used for SNMP data collection","Out of Band Access", "Notes");

//make a list of device types and locations in the database
$deviceTypes = Device_type::get_device_types();
$location = Location::get_locations();
				
//checks the status of what's happening. If something was done successfully, it will be displayed
if(isset($_GET['mode']) || isset($_GET['delete']))
{
	switch (success)
	{
		case $_GET['update']:
		$deviceForm->success("Updated successfully");
		break;
		
		case $_GET['add']:
		$deviceForm->success("Added new data successfully");
		break;
		
		case $_GET['delete']:
		$deviceForm->success("Deleted and archived device successfully");
		break;
	}
}

//if the user is editting or viewing a device ID, check if there is an update being made. If there are no updates then show them the client information form.
if(($_GET['action'] == edit && $_SESSION['access'] >= 50) || $_GET['action'] == showID)
{
	
	//get the new device corresponding to the ID
	$devices = new Device($_GET['ID']);
	$id = $devices->get_device_id();
	
	//if this is a port we're editting get that ID instead
	if(isset($_POST['portID']))
	{
		$ports = new ControlPort($_POST['portID']);
	}
	
	//checks to see if this is a valid ID or not
	if($devices->get_device_id() =="")
	{
		$deviceForm->error("Warning: Failed to load. Reason: ".$devices->get_error(), $_GET['ID']);
	}
	
	//if this is an update then update the device or port
	if(isset($_POST['updateInfo']) && $_SESSION['access'] >= 50)
	{
		updateDevice($devices);		
	}
	else if(isset($_POST['updatePort']))
	{
		updatePort($ports, $id);
	}
	//if the user is adding a port, then add it... ***NOTE THIS IS BEING ADDED HERE IN THE DISPLAYING EDITTING SECTION BECAUSE OF AJAX FUNCTIONS.....***
	else if(isset($_POST['addPort']) && $_SESSION['access'] >= 50)
	{
		addPort($id);						
	}
	//or else display the device information
	else
	{
		displayDevice($devices);
	}
}
else if  ($_GET['action'] == "list_device_types") {
	displayDeviceTypes();
}
else if  ($_GET['action'] == "show_device_type") {
	DisplayDeviceType();
}
else if  ($_GET['action'] == "edit_device_type") {
	EditDeviceType();
}
else if  ($_GET['action'] == "archive_device_type") {
	ArchiveDeviceType();
}
else if  ($_GET['action'] == "add_device_type") {
	AddDeviceType();
}
//if the user prompts to add a new device
else if ($_GET['action'] == add && $_SESSION['access'] >= 50)
{	
	//add the device
	if(isset($_POST['addData']))
	{
		addDevice($devices);					
	}
	//if it is a new device type, add the new device type
	else if(isset($_POST['addType']))
	{
		$deviceType = new Device_type();
		addDeviceType($deviceType);
	}
	//if it is a new location, add the location
	else if(isset($_POST['addLocation']))
	{
		$location = new Location();
		addLocation($location);
	}
	//or else dispay the form necessary for adding
	else {
		addDeviceForm($devices);								
	}
}
				
//if the user prompts to remove a device
else if($_GET['action'] == remove && $_SESSION['access'] >= 50)
{
	//remove the port if it is a port
	if(isset($_GET['portID']))
	{
		$ports = new ControlPort($_GET['portID']);
		removePort($ports);
	}
	//or else get the device and remove it
	else
	{
		$devices = new Device($_GET['ID']);
		removeDevice($devices);
	}
}
//show an IP report
else if($_GET['action'] == ipReport)
{
	displayIP();
}
//display all the archived devices for the user to see
else if($_GET['action'] == showArchived)
{
	displayAllArchived($devices);
}
//If the user changes teh control port from power to console and vise verca, give them different options
else if ($_GET['action'] == changeControlledPort && $_SESSION['access'] >= 50)
{
	//give power controls a grouping value
	echo "<td class='info'>GROUP ";
	
	if ($_GET['mode']=='power_control')
	{
		echo "<input type='text' name='group' value='' />";
	}
	else {echo "NOT APPLICABLE";}
	echo "</td>";
	
	$types = $devices->get_devices_by_class($_GET['mode']);
	echo "<select name='controlledDevice'>";
	foreach ($types as $id=>$value)
	{
		echo "<option value='$id'>$value</option>";
		
	}
	echo "</select>";
}
				
//if nothing else, display all the devices for the user to see
else
{
	displayAll($devices);
}
?>
</div>        
<?php 
//Footer
if(!isset($_GET['mode']))
{include("footer.php");} ?>

<?
/*****************************************************FUNCTIONS************************************************/

//This function displays all the device
function displayAll($devices)
{
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $deviceForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Device", "All Archived Device", "IP Report","Device Types");
		$toolIcons = array("add", "device", "report","icons/checklist.png");
		$toolHandlers = array(
			"handleEvent('devices.php?action=add')", 
			"handleEvent('devices.php?action=showArchived')", 
			"handleEvent('devices.php?action=ipReport')",
			"handleEvent('devices.php?action=list_device_types')"
		);
	}
	else
	{
		$toolNames = array("All Archived Device", "IP Report");
		$toolIcons = array("device", "report");
		$toolHandlers = array("handleEvent('devices.php?action=showArchived')", "handleEvent('devices.php?action=ipReport')");
	}
					
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	foreach (Device::get_devices() as $id => $name)
	{
		$deviceInfo = new Device($id);
		array_push($keyHandlers, "handleEvent('devices.php?action=showID&ID=$id')");
		array_push($keyTitle, $name);
		array_push($keyData, $deviceInfo->get_type_name());
		array_push($keyData, $deviceInfo->get_location_name());
	}
	
	//get all the device and display them all in the 3 sections "Device Name", "Device Type", "Location".
	$headings = array("Device Name", "Device Type", "Location");
	echo $deviceForm->showAll($headings, $keyTitle, $keyData, $keyHandlers);	
}

//This function displays all the archived devices
function displayAllArchived($devices)
{
	//global the tool and make a tool bar for adding a device, and the IP Report
	global $tool, $deviceForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Device", "All Device", "IP Report");
		$toolIcons = array("add", "device", "report");
		$toolHandlers = array("handleEvent('devices.php?action=add')", "handleEvent('devices.php')");
	}
	else
	{
		$toolNames = array("All Device", "IP Report");
		$toolIcons = array("device", "report");
		$toolHandlers = array("handleEvent('devices.php')", "handleEvent('devices.php?action=ipReport')");
	}
	
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	foreach (Device::get_devices(1) as $id => $name)
	{
		$deviceInfo = new Device($id);
		array_push($keyHandlers, "handleEvent('devices.php?action=showID&ID=$id')");
		array_push($keyTitle, $name);
		array_push($keyData, $deviceInfo->get_type_name());
		array_push($keyData, $deviceInfo->get_location_name());
	}
	
	//get all the devices and display them all in the 3 sections "Device Name", "Device Type", "Location".
	$headings = array("Device Name", "Device Type", "Location");
	echo $deviceForm->showAll($headings, $keyTitle, $keyData, $keyHandlers);	
}

//Updating the device. This is where the devices stores updated values
function updateDevice($devices)
{
	//global all variables
	global $deviceKey, $deviceForm, $status, $deviceTypes, $location;
					
	//create an empty temporary array to store all the new values given from the form
	$tempDeviceInfo=array();
	$deviceKey = array("name", "device_fqdn", "location", "device_type", "snmp_ro","device_oob", "notes");
	foreach($deviceKey as $index => $key)
	{
		$tempDeviceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempDeviceInfo[notes] = addslashes($tempDeviceInfo[notes]);
	$tempDeviceInfo[name] = addslashes($tempDeviceInfo[name]);
	
	//check if this ID is valid
	if (!(is_numeric($devices->get_device_id())))
	{
		$deviceForm->error("Warning: Failed to update. Reason: ".$devices->get_error(), $_GET['ID']);
 	}
					
	//if it works then set all the values into the device and update it
	else{
		//makes sure the device has a name
		if ($devices->set_name($tempDeviceInfo[name]))
		{
			//set all the values to the query
			$devices->set_device_fqdn($tempDeviceInfo[device_fqdn]);
			$devices->set_location_id($tempDeviceInfo[location]);
			$devices->set_device_type($tempDeviceInfo[device_type]);
			$devices->set_device_oob($tempDeviceInfo[snmp_ro]);
			$devices->set_device_oob($tempDeviceInfo[device_oob]);
			$devices->set_notes($tempDeviceInfo[notes]);
								
			//if the update is sucessful go back to show the new updates or else show an error
			if($devices->update())
			{
				$status="success";
				$_SESSION['action'] = "Updated device: ".$tempDeviceInfo[name];
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]&update=$status\">";
			}
			else{
				$deviceForm->error("Warning: Failed to update. Reason: ".$devices->get_error(), $_GET['ID']);
			}
		}
		//if there are no names then show error
		else
		{								
			$deviceForm->error("Warning: Failed to update. Reason: ".$devices->get_error(), $_GET['ID']);
		}
	}
}

/*Updating the Ports. This is where the Ports stores updated values*/
function updatePort($ports ,$id)
{
	//global all variables
	global $deviceKey, $deviceForm, $status, $deviceTypes, $location;
					
	//create an empty temporary array to store all the new values given from the form
	$tempDeviceInfo=array();
	$pType = $_POST['pType'];
	if(empty($pType)) {
		$deviceForm->error("Warning: Failed to add port. Reason: Unable to determine port typ pType");
		return false;
	}
	if($pType == "cport") {
		$deviceKey = array("description", "physicalPort", "portName", "portType", "group", "managedDevice");
	}
	elseif($pType == "mport") {
		$deviceKey = array("description", "physicalPort", "portName", "portType", "group", "controlledDevice");
	}
	else {
		$deviceForm->error("Warning: Failed to add port. Reason: invalid pType");
		return false;
	}
	
	foreach($deviceKey as $index => $key)
	{
		$tempDeviceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempDeviceInfo[description] = addslashes($tempDeviceInfo[description]);
	$tempDeviceInfo[portName] = addslashes($tempDeviceInfo[portName]);
					
	//check if this ID is valid
	if (!(is_numeric($ports->get_id())))
	{
		$deviceForm->error("Warning: Failed to update. Reason: ".$ports->get_error(), $_GET['ID']);
 	}
					
	//if it works then set all the values into the Port and update it
	else{
		
		if (true)
		{
			$ports->set_name($tempDeviceInfo[portName]);
			//set all the values to the query
			$ports->set_port($tempDeviceInfo[physicalPort]);
			$ports->set_group($tempDeviceInfo[group]);
			if($pType == "cport") {
				$ports->set_control_device_id($id);
				$ports->set_managed_device_id($tempDeviceInfo[managedDevice]);
			}
			else 
			{
				$ports->set_managed_device_id($id);
				$ports->set_control_device_id($tempDeviceInfo[controlledDevice]);
			}
			
			$ports->set_type($tempDeviceInfo[portType]);
			$ports->set_description($tempDeviceInfo[description]);
								
			//if the update is sucessful go back to show the new updates or else show an error
			if($ports->update())
			{
				$status="success";
				$_SESSION['action'] = "Updated device port: ".$tempDeviceInfo[portName];
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]&update=$status&tab=2\">";
			}
			else{
				$deviceForm->error("Warning: Failed to update. Reason: ".$ports->get_error(), $_GET['ID']);
			}
		}
		//if there are no names then show error
		else
		{								
			$deviceForm->error("Warning: Failed to update. Reason: ".$ports->get_error(), $_GET['ID']);
		}
	}
}
				
//display the current device information
function displayDevice($devices)
{

	//global all variables
	global $deviceKey, $deviceForm, $tool, $headings, $titles, $deviceTypes, $location;
	
	//if this isn't in ajax mode display the Ajax buttons
	if(!isset($_GET['mode'])){
		if ($_GET['tab'] == 2)
		{$name = array($devices->get_name(), "Interface", "Device Control.first.");}
		else if ($_GET['tab'] == 1)
		{$name = array($devices->get_name(), "Interface.first.", "Device Control");}
		else
		{$name = array($devices->get_name().".first.", "Interface", "Device Control");}
		
		$page = array("devices.php?action=showID&ID=$_GET[ID]&mode=deviceInfo", "devices.php?action=showID&ID=$_GET[ID]&mode=deviceInterface", "devices.php?action=showID&ID=$_GET[ID]&mode=deviceControl");
		echo $tool->createNewButtons($name, "devicePart", $page);
	}
	//the division for the interfae, control port, and info page to show
	echo "<div id='devicePart'>";
	//success message for the ajax mode
	switch (success)
	{
		case $_GET['update']:
		$deviceForm->success("Updated successfully");
		break;
		
		case $_GET['add']:
		$deviceForm->success("Added new data successfully");
		break;
		
		case $_GET['delete']:
		$deviceForm->success("Deleted and archived data successfully");
		break;
	}
	
	//if ajax mod is part of displaying the interface
	if($_GET['mode'] == deviceInterface || $_GET['tab']==1)
	{
		//set the table attributes
		$deviceForm->setCols(11);
		$deviceForm->setTableWidth("100%");
		$deviceForm->setTitleWidth("10%");
		
		//create tools for this mode
		/*Taken out for user interface issues
		$toolNames = array("All Devices", "All Archived Device");
		$toolIcons = array("devices", "devices");
		$toolHandlers = array("handleEvent('devices.php')", "handleEvent('devices.php?action=showArchived')");
		
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);*/
		
		//can be displayed in both percent and bPS mode
		if($_GET['output']==percent)
		{
			$headings = array("Interface name", "Interface alias", "Interface description", "Status", 
						  "Discovered interface speed(bps)", 
						  "Current throughput in % 
						  <a href='#' style='color:yellow;' onclick=\"handleEvent('devices.php?action=showID&ID=$_GET[ID]&output=bps&tab=1');\">[switch to bps] </a>",
						  "Interface MTU", "IPv4/IPv6 address", 
						  "Interface duplex", "Interface type", "Discovered interface index");
		
		}
		else {
			$headings = array("Interface name", "Interface alias", "Interface description", "Status", 
							  "Discovered interface speed(bps)", 
							  "Current throughput in bps
							  <a href='#' style='color:yellow;' onclick=\"handleEvent('devices.php?action=showID&ID=$_GET[ID]&output=percent&tab=1');\">[switch to percent] </a>",
							  "Interface MTU", "IPv4/IPv6 address", 
							  "Interface duplex", "Interface type", "Discovered interface index");
		}
		
		//get all the interfacese
		$interfaces = $devices->get_interfaces();
		$info = array();
		$title = array();
		$handlers = array();
		
		//put all the interface information into the arrays
		foreach($interfaces as $id => $value)
		{
			//array_push($title, "");
			array_push($title, $value->get_name().'//'.$value->get_interface_id().'//'.$value->get_device_id());
			array_push($info, $value->get_alias());
			array_push($info, $value->get_descr());
			array_push($info, $value->get_oper_status());
			$speed = $tool->calculator("convertBits", $value->get_speed());
			array_push($info, $speed);
			
			//calculate the percentage if it's in percent mode, otherwise convert it in to the right bits	
			if ($value->get_inbits() > $value->get_outbits()){$highBits = $value->get_inbits();}
			else {$highBits = $value->get_outbits();}
			if($_GET['output']==percent)
			{
				if ($value->get_speed() > 0) {
					$percentage = $highBits/$value->get_speed();
					$percentage = $tool->calculator("convertPercent", $percentage);
				} else {
					$percentage = "0%";
				}
				array_push($info, $percentage);
			}
			else
			{
				$highBits = $tool->calculator("convertBits", $highBits);
				array_push($info, $highBits);
			}
			
			array_push($info, $value->get_mtu());
			
			$ipv4=$value->get_ipv4_addresses();
			$ipv6=$value->get_ipv6_addresses();
			$ip="";
			
			//store both ipv4 and ipv6 addresses
			foreach($ipv4 as $ipID=>$ipValue)
			{
				$ip.=$ipID."<br/>";	
			}
			
			foreach($ipv6 as $ipID=>$ipValue)
			{
				$ip.=$ipID."<br/>";	
			}
			
			array_push($info, $ip);
			array_push($info, "");
			array_push($info, $value->get_type());
			array_push($info, $value->get_ifindex());
			
			//prepare strings into html format to display the graph and push it into the handler
			$nameTitle = str_replace(" ", "%20", $value->get_name());
			$name = str_replace(" ", "-", $value->get_name());
			$name = str_replace("/", "-", $name);
			$graphLink="rrdgraph.php?file=deviceid".$value->get_device_id()."_".$name.".rrd&title=".$nameTitle."---Bits%20Per%20Second&type=traffic";
			array_push($handlers, $graphLink);
		}
		
		//If there are info, display the interfaces, else give a warniing message
		if (count($info)>0)
		{
			echo $deviceForm->showAll($headings, $title, $info, $handlers, 3);
		}
		else
		{
			$deviceForm->warning("You have no Interfaces");
		}
	}
	
	//if the ajax mode is in device control
	elseif ($_GET['mode'] == deviceControl || $_GET['tab'] == 2)
	{
		//set the form attributes
		$deviceForm->setCols(7);

		/*check if the device type is a power or console device
		*if it is a control or console device, the user can add both management and control port
		*Otherwise the user can only add management ports
		*/
		//if ($devices->get_device_type()==5 || $devices->get_device_type()==6)
		if ($devices->get_device_class()=='console_server' || $devices->get_device_class()=='power_control')
		{
			$cPorts = array();
			if ($_SESSION['access'] >= 50)
			{
				$toolNames = array("New management port.tip.Management Ports are ports that help you manage this device using power or console ports.", "New control port.tip.Control ports are ports that help you manage connected devices. Examples are power ports on remote power controls and serial ports on console servers.");
				$toolIcons = array("add", "add");
				$toolHandlers = array("return LoadPage('devices.php?action=add&item=mPort&mode=deviceControl&ID=$_GET[ID]', 'devicePart');", 																						
									"return LoadPage('devices.php?action=add&item=cPort&mode=deviceControl&ID=$_GET[ID]&deviceClass=".$devices->get_device_class()."', 'devicePart');");
			}
		}
		else
		{
			if ($_SESSION['access'] >= 50)
			{
				$toolNames = array("New management port.tip.Management Ports are ports that help you manage this device using power or console ports.");
				$toolIcons = array("add");
				$toolHandlers = array("return LoadPage('devices.php?action=add&item=mPort&mode=deviceControl&ID=$_GET[ID]', 'devicePart');");
			}
		}
		
		//create the tool				
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
		
		//create the headings titles and info emptyy arrays for both management and control ports
		$cHeadings = array("Description", "Physical port", "Port name", "Port type", "Group", "Managed device", "Action");
		$mHeadings = array("Description", "Physical port", "Port name", "Port type", "Group", "Control device", "Action");
		$cTitles = array();
		$mTitles = array();
		$cInfo = array();
		$mInfo = array();
		
		//get all the control and management ports to store in an array for display
		$management = $devices->get_management_ports();
		$control = $devices->get_control_ports();
		$mPorts = array();
		$index = 0;
		
		//insert the ports into an array
		foreach($management as $id => $value)
		{
			$mPorts[$index] = new ControlPort($id);	
			$index++;
		}
		foreach($control as $id => $value)
		{
			$cPorts[$index] = new ControlPort($id);	
			$index++;
		}
		
		//push the info of these ports into the table
		foreach($mPorts as $id=>$value)
		{
			array_push($mTitles, $mPorts[$id]->get_description());
		}
		if (isset($cPorts)) {
			foreach($cPorts as $id=>$value)
			{
				array_push($cTitles, $cPorts[$id]->get_description());
			}
		}
		
		foreach($mPorts as $id=>$value)
		{
			array_push($mInfo, $mPorts[$id]->get_port());
			array_push($mInfo, $mPorts[$id]->get_name());
			array_push($mInfo, $mPorts[$id]->get_type());
			array_push($mInfo, $mPorts[$id]->get_group());
			array_push($mInfo, $mPorts[$id]->get_control_device_name());
			$portID=$mPorts[$id]->get_id();
			if ($_SESSION['access'] >= 50)
			{array_push($mInfo, "<a href='#' onclick=\"return LoadPage('devices.php?action=edit&ID=$_GET[ID]&mode=deviceControl&mportID=$id&portID=$portID', 'devicePart');\">Edit</a> | <a href='#' onclick=\"handleEvent('devices.php?action=remove&ID=$_GET[ID]&mportID=$id&portID=$portID');\">Delete</a>");}
			else
			{array_push($mInfo, 'No Access');}
			
		}
		
		if (isset($cPorts)) {
		foreach($cPorts as $id=>$value)
		{
			array_push($cInfo, $cPorts[$id]->get_port());
			array_push($cInfo, $cPorts[$id]->get_name());
			array_push($cInfo, $cPorts[$id]->get_type());
			array_push($cInfo, $cPorts[$id]->get_group());
			array_push($cInfo, $cPorts[$id]->get_managed_device_name());
			$portID=$cPorts[$id]->get_id();
			if ($_SESSION['access'] >= 50)
			{array_push($cInfo, "<a href='#' onclick=\"return LoadPage('devices.php?action=edit&ID=$_GET[ID]&mode=deviceControl&cportID=$id&portID=$portID', 'devicePart');\">Edit</a> | <a href='#' onclick=\"handleEvent('devices.php?action=remove&ID=$_GET[ID]&cportID=$id&portID=$portID');\">Delete</a>");}
			else
			{array_push($cInfo, 'No Access');}
			
		}
		}
		
		//if the user is editting this information, make it all editable
		if ($_GET['action'] == edit)
		{	
			$deviceForm->setCols(2);
			$fieldType = array("hidden","", "", "", "static", "", "drop_down");
			
			//checks to see if it's management or control ports to give different forms
			if(isset($_GET['mportID']))
			{
				$name=$devices->get_name();
				$headings = array("Port Information for ".$name);
				$titles = array("pType","Description.tip.A descriptive name for this connection, i.e. \"console connection for router1, routing engine 2\" or \"Remote power cycle group for router1\"", 
								"Physical port.tip.Which port is this device physically connected to",
								"Port name.tip.Name of port. This will also be the name used for scripts",
								"Port type", "Group", "Control device");
				
				$group = $mPorts[$_GET['mportID']]->get_group();
				
				if($group == ''){
					$fieldType = array("hidden","", "", "", "static", "static", "drop_down");
					$group = "NOT APPLICABLE";
				}
				
				$info = array("mport",$mPorts[$_GET['mportID']]->get_description(), $mPorts[$_GET['mportID']]->get_port(),
								$mPorts[$_GET['mportID']]->get_name(), $mPorts[$_GET['mportID']]->get_type(),
								$group, $mPorts[$_GET['mportID']]->get_control_device_name());
				
				if ($mPorts[$_GET['mportID']]->get_type() == "console")
				{$portTypeName="console_server";}
				else
				{$portTypeName="power_control";}
				
				$types = $devices->get_devices_by_class($portTypeName);
				$deviceKey = array("pType","description", "physicalPort", "portName", "portType", "group", "controlledDevice");
	
			}
			else if (isset($_GET['cportID']))
			{
				$name=$devices->get_name();
				$headings = array("Port Information for ".$name);
				$titles = array("pType","Description.tip.A descriptive name for this connection, i.e. \"console connection for router1, routing engine 2\" or \"Remote power cycle group for router1\"", 
								"Physical port.tip.Which port is this device physically connected to", 
								"Port name.tip.Name of port. This will also be the name used for scripts", 
								"Port type", "Group", 
								"Managed device.tip.Select a device or select \"Other Device\" if you want to manage a device that is not in the database. If you select \"Other Device\" please make sure to have a good port description.");
				
				$group = $cPorts[$_GET['cportID']]->get_group();
				if($group == ''){
					$fieldType = array("hidden","", "", "", "static", "static", "drop_down");
					$group = "NOT APPLICABLE";
				}
				
				$info = array("cport",$cPorts[$_GET['cportID']]->get_description(), $cPorts[$_GET['cportID']]->get_port(),
								$cPorts[$_GET['cportID']]->get_name(), $cPorts[$_GET['cportID']]->get_type(),
								$group, $cPorts[$_GET['cportID']]->get_managed_device_name());
				$types = $devices->get_devices();
				array_push($types, "Other devices");
				$deviceKey = array("pType","description", "physicalPort", "portName", "portType", "group", "managedDevice");
			}
			
			$deviceForm->setFieldType($fieldType);
			echo $deviceForm->editPortForm($headings, $titles, $info, $deviceKey, $types);
		}
						
		//if the user is showing the information make it all uneditable
		else if($_GET['action'] == showID)
		{
			//show the ports
			if(isset($cPorts)) {
				echo "<div style='clear:both;'></div><h2>Control Ports</h2>";
				echo $deviceForm->showAll($cHeadings, $cTitles, $cInfo);
			}
			
			if(isset($mPorts)) {
				echo "<div style='clear:both;'></div><h2>Management  Ports</h2>";
				echo $deviceForm->showAll($mHeadings, $mTitles, $mInfo);
			}
			
			if(!isset($mPorts) && !isset($cPorts))
			{$deviceForm->warning("You have no Ports");}
		}
	}
	//display all the device info
	else {	
		$deviceForm->setCols(2);
		//make the tool bar for this page
		if ($_SESSION['access'] >= 50)
		{
			$toolNames = array("Edit Device", "Delete Device");
			$toolIcons = array("edit", "delete");
			$toolHandlers = array("handleEvent('devices.php?action=edit&ID=$_GET[ID]');",
								"handleEvent('devices.php?action=remove&ID=$_GET[ID]')",
			);
		}
		
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);


		
		//make the headings
		$headings = array("Device Information");
		
		//store all the device information values into an array
		$info = array($devices->get_name(), $devices->get_device_fqdn(), $devices->get_location_name(), 
					  $devices->get_type_name(), $devices->get_snmp_ro(), $devices->get_device_oob(), $devices->get_notes()
			);

			
		//if the user is editting this information, make it all editable
		if ($_GET['action'] == edit)
		{
			$deviceKey = array("name", "device_fqdn", "location", "device_type", 
				"SNMP Community String.tip.Read only SNMP community used for SNMP data collection", "device_oob", "notes");
			$fieldType = array("","","drop_down", "drop_down", "","", "text_area");
			
			$deviceForm->setFieldType($fieldType);
			
			$type = array($location, $deviceTypes);
			echo $deviceForm->editDeviceForm($headings, $titles, $info, $deviceKey, $type);
		}
						
		//if the user is showing the informating make it all uneditable
		elseif(($_GET['action'] == showID) )
		{
			//store all the device information values into an array
			$info = array($devices->get_name(), $devices->get_device_fqdn(), $devices->get_location_name(), 
					  $devices->get_type_name(), $devices->get_snmp_ro(), $devices->get_device_oob(), nl2br($devices->get_notes())
			);
			echo $deviceForm->showDeviceForm($headings, $titles, $info);

			// Everything below is for viewing & edditing Private Data for this device.

			$modalForms ="";
			echo "<div style='clear:both;'></div>";
			echo "<h2>Private Data</h2>";

			// Here we check if we just deleted a private data entry
			if (isset($_POST['delete_private_data'])) {
				$form = new Form();
				// Yes update
				$privDataObj = new PrivateData($_POST['private_data_id']);
				if ($privDataObj->delete($_POST['group_pass'])) {
					$form->success("Private entry Deleted") ;
					$_SESSION['action'] = "Removed private data for: ".$devices->get_name();
				} else {
					$form->error("Warning: Failed to delete Private data Reason: ".$privDataObj->get_error(), $_GET['ID']) ;
					unset($_POST['group_pass']);
				}
			}
			// Check if we just added a private data Type
			if (isset($_POST['add_private_data_type'])) {
				$form = new Form();
				$no_error = true;
				// Check mandotry fields
				if (($_POST['pdtype_name'] == '') ) {
					$form->error("Error: Private DataType name is empty") ;
					$no_error = false;
				}
				elseif (($_POST['pdtype_desc'] == '') ) {
					$form->error("Error: Private DataType Description is empty") ;
					$no_error = false;
				}
				if ($no_error) {	
					$privDataTypeObj = new PrivateDataType();
					$privDataTypeObj->set_name($_POST['pdtype_name']);
					$privDataTypeObj->set_desc($_POST['pdtype_desc']);
					if ($privDataTypeObj->insert()) {
						$form->success("Private data type '". $_POST['pdtype_name'] ."' Added") ;
						$_SESSION['action'] = "Added private data Type";
					} else {
						$form->error("Warning: Failed to Add Private data Reason: ".$privDataTypeObj->get_error());
					}
				}
			}

			// Check if we just added a private data entry
			if (isset($_POST['add_private_data_for_group'])) {
				$form = new Form();
				$no_error = true;
				// Check mandotry fields
				if ((!is_numeric($_POST['device_id'])) ) {
					$form->error("Error: Invalid device id") ;
					$no_error = false;
				}
				elseif ((!is_numeric($_POST['group_id']))  ) {
					$form->error("Error: No Group Specified") ;
					$no_error = false;
				}
				elseif ((!is_numeric($_POST['private_data_type'])) ) {
					$form->error("Error: No Private Data type specified") ;
					$no_error = false;
				}
				elseif (($_POST['private_data_password'] == '') ) {
					$form->error("Warning: Private Data string was empty") ;
					//$no_error = false;
				}
				if ($no_error) {	
					$privDataObj = new PrivateData();
					$privDataObj->set_group_id($_POST['group_id']);
					$privDataObj->set_type_id($_POST['private_data_type']);
					$privDataObj->set_device_id($_POST['device_id']);
					$privDataObj->set_notes($_POST['private_data_notes']);
					$privDataObj->set_name($_POST['private_data_desc']);
					$privDataObj->set_private_data($_POST['private_data_password']);
					
					if ($privDataObj->insert($_POST['group_pass'])) {
						$form->success("Private data entry Added") ; 
						$_SESSION['action'] = "Added private data for: ".$devices->get_name();
					} else {
						$form->error("Warning: Failed to Add Private data Reason: ".$privDataObj->get_error(), $_GET['ID']) ;
						unset($_POST['group_pass']);
					}
				}
			}
			echo "<a name='modal' href='#Add_privatedata_modal'><img src='icons/Add.png' height=18>Add Private Data</a><br>";


			// Add Modal for adding Private data types
			$modalForm = new Form("auto", 2);	
			$modalForm->setHeadings(array("<br><br>Add Private Data Type"));
			$modalForm->setTitles(array("Name.tip.Descriptive String for this type","Description"));
			$modalForm->setData(array("",""));
			$modalForm->setDatabase(array("pdtype_name","pdtype_desc"));
			// Change button text
			$modalForm->setUpdateValue("add_private_data_type");
			$modalForm->setUpdateText("Add Private Data Type");
			$modalForm->setModalID("add_pdtype_modal");
			$private_data_type_modal = $modalForm->modalForm();
			unset($modalForm);
			// End Modal for adding Private data types


			// Create modal for adding a new Private data entry
			// This modal should ask for which group to add it as and the password

			// We need to know all groups this user is in:
			$user = new User($_SESSION['userid']);
			$user_groups = $user->get_groups();
			if ((sizeof($user_groups)) == 1) {
				foreach ($user_groups as $gid => $gname) {
					$group_data = $gname;
				}
			} else {
				$group_data = "";
			}
			$modalForm = new Form("auto", 2);	
			$modalForm->setHeadings(array("For which group would you like to add private" ));
			$modalForm->setTitles(array("Group","Group Password.tip.This is the shared secret for the group you selected above.",
				"Fill in Private Data Details below:","Description",
				"Private Data<br><small>Stored encrypted</small>.tip.This is the data that will be encrypted",
				"Type <br><small><a name='modal' href='#add_pdtype_modal'>Add Private data type</a></small>",
				"Notes <br><small><i>Stored encrypted</i></small>.tip.This data will be AES encrypted","device_id"));
			$modalForm->setData(array("$group_data","","","","","","",$_GET['ID']));
			$modalForm->setDatabase(array("group_id","group_pass","dummy","private_data_desc","private_data_password",
						"private_data_type","private_data_notes","device_id"));
			$modalForm->setFieldType(array(0=>'drop_down',1=>'password_autocomplete_off',
					2=>'static',5=>'drop_down',6=>'text_area',7=>'hidden'));
			// Drop down
			// We need to know all groups this user is in:
			$modalForm->setType($user_groups);
			$dataTypes = PrivateDataType::get_private_data_types();
			$modalForm->setType($dataTypes);
			//End Dropdown
			// Change button text
			$modalForm->setUpdateValue("add_private_data_for_group");
			$modalForm->setUpdateText("Add");
			$modalForm->setModalID("Add_privatedata_modal");
			echo $modalForm->modalForm();
			unset($modalForm);
			// End modal


			// Also create a table with PrivateData
			// 1st get all entries for this device
			$all_private_data = PrivateData::get_private_data_by_device($_GET['ID']);
			// Only if there is any private data for this device
			if (($all_private_data) &&($_GET['action'] == showID)) {
				$i++;
				// Check if we just updated the info,
				// If so we need to update Private data
				if (isset($_POST['update_private_data'])) {
					// Yes update
					$tmpform = new Form();
					$privDataObj = new PrivateData($_POST['private_data_id']);
					$privDataObj->set_name($_POST['private_data_desc']);
					$privDataObj->set_notes($_POST['private_data_notes']);
					$privDataObj->set_type_id($_POST['private_data_type']);
					$privDataObj->set_private_data($_POST['private_data_password']);
					if ($privDataObj->update($_POST['group_pass'])) {
						$tmpform->success("Private data updated Succesfully") ;
						unset($tmpform);
						$_SESSION['action'] = "Updated private data for: ".$devices->get_name();
					} else {
						print "NOK " . $privDataObj->get_error();
						$tmpform->error("Warning: Failed to Update Private data Reason: ".$privDataObj->get_error(), $_GET['ID']) ;
					}
				}
				
							
				// Placeholder for modal forms
				$heading = array("Type","Description","Private Data","Group","Actions");
				$data = array();
				foreach($all_private_data as $id =>$group_id) {
					$privDataObj = new PrivateData($id);
					// Only show tooltip when data is available
					// This is for type description
					if ($privDataObj->get_type_desc() != '') {
						$type_tooltip = ".tip.Private Data Type keyword:<br> ".$privDataObj->get_type_name();
					} else {
						$type_tooltip = "";
					}
					// This is for type name + Notes
					//if ($privDataObj->get_notes() != '') {
					//	$name_tooltip = ".tip.<b>Notes:</b><br>".nl2br($privDataObj->get_notes());
					//} else {
					//	$name_tooltip = "";
					//}

	
					// We also need to create a modal that will Ask the user for a password
					// We only need one per group, as passwords are unqiue per group
					$modalForm = new Form("auto", 2);	
					$modalForm->setHeadings(array("Please provide group password for ". $privDataObj->get_group_name()));
					$modalForm->setTitles(array("Password","group_id"));
					$modalForm->setData(array("",$privDataObj->get_group_id()));
					$modalForm->setDatabase(array('group_pass','group_id'));
					$modalForm->setFieldType(array(0=>'password_autocomplete_off',1=>'hidden'));
					$myModalID = "modal_group_pass_". $privDataObj->get_group_id();
					// Change button text
					$modalForm->setUpdateValue("Decrypt_Private_Data");
					$modalForm->setUpdateText("Submit");

					$modalForm->setModalID($myModalID);
					$modalForms .= $modalForm->modalForm();
					unset($modalForm);
					// End modal

					$name_tooltip = "";
					// Here we check if the user submitted a group password
					// Only for the group for which the pasword has been provided

					if ((isset($_POST['group_pass'])) && ($_POST['group_pass'] != '') && ($privDataObj->get_group_id() == $_POST['group_id'])) {
							// now get private data (password)
						$password = $privDataObj->get_private_data($_POST['group_pass']);
						if ($password != false) {
	
							// Decrypted successful!

							// This is for type name + Notes
							if ($privDataObj->get_notes($_POST['group_pass']) != '') {
								$name_tooltip = ".tip.<b>Notes:</b><br>".nl2br($privDataObj->get_notes($_POST['group_pass']));
							}
							
							// Get historical data, and create modal
							$modalForm = new Form("auto", 2);	
							$modalForm->setHeadings(array("Changed (exipred) at:","Private Data"));

							// Loop through old data and fill arrays for form
							$Htitles=array();
							$Hdata=array();
							$HfieldType=array();
							$historical_passwords =  $privDataObj->get_history($_POST['group_pass']);
							if ($historical_passwords) {
								foreach ($historical_passwords as $old_date =>$old_data) {
									array_push($Htitles, $old_date);
									array_push($Hdata, $old_data);
									array_push($HfieldType, "static");
								}
								
							}
							$modalForm->setTitles($Htitles);
							$modalForm->setData($Hdata);
							$modalForm->setFieldType($HfieldType);
							unset($Htitles);
							unset($Hdata);
							unset($HfieldType);
							$modalForm->setTitleWidth("40%");
							$modalForm->setDatabase(array('date','old_data'));
							$myHistoryModalID = "modal_old_pass_". $id;

							// Change button text
							$modalForm->setUpdateValue("close");
							$modalForm->setUpdateText("Press cancel");
							$modalForm->setModalID($myHistoryModalID);
							$modalForms .= $modalForm->modalForm();
							unset($modalForm);
							// End modal
							
							
							// Now create a modal that allows us to update the private data object
							// Start Update Modal

							$PdataModal = new Form("auto", 2);	
							$PdataModal->setHeadings(array("Update Private Data"));
							$PdataModal->setTitles(array("Description",
									"Private Data <br><small><i>Stored encrypted</i></small>.tip.This data will be AES encrypted",
									"Type <br><small><a name='modal' href='#add_pdtype_modal'>Add Private data type</a></small>",
									"Notes<br><small><i>Stored encrypted</i></small>.tip.This data will be AES encrypted","PDid","",""));
							$PdataModal->setData(array( $privDataObj->get_name(),$password,$privDataObj->get_type_name(),
									$privDataObj->get_notes($_POST['group_pass']),$id,
									$_POST['group_id'],$_POST['group_pass']));
							$PdataModal->setDatabase(array('private_data_desc','private_data_password',
									'private_data_type','private_data_notes','private_data_id',
									'group_id','group_pass'));
							$PdataModal->setFieldType(array(2=>'drop_down',3=>'text_area',4=>'hidden',
									5=>'hidden',6=>'hidden'));
							// Creat dropdown
							$dataTypes = PrivateDataType::get_private_data_types();
							$PdataModal->setType($dataTypes);
							$PdataModal->setUpdateValue('update_private_data');
							$PdataModalID = "modal_private_data_id". $id;
							// Change button text

							$PdataModal->setModalID($PdataModalID);
							$modalForms .= $PdataModal->modalForm();
							// End Update modal

							// Now a Modal to Delete an Entry
							// We'll ask for the password again.
							$modalFormDelete = new Form("auto", 2);	
							$modalFormDelete->setHeadings(array("Delete ". $privDataObj->get_name() ."<br>Please provide group password for ". $privDataObj->get_group_name()));
							$modalFormDelete->setTitles(array("Password","group_id",""));
							$modalFormDelete->setData(array("",$privDataObj->get_group_id(),$id));
							$modalFormDelete->setDatabase(array('group_pass','group_id','private_data_id'));
							$modalFormDelete->setFieldType(array(0=>'password_autocomplete_off',1=>'hidden',2=>'hidden'));
							$myDeleteModalID = "modal_delete_pass_". $id;
							// Change button text
							$modalFormDelete->setUpdateValue("delete_private_data");
							$modalFormDelete->setUpdateText("Delete");

							$modalFormDelete->setModalID($myDeleteModalID);
							$modalForms .= $modalFormDelete->modalForm();
							// End Delete modal

							
							if (count($historical_passwords) > 0) {
								$history_string = "<a name='modal' href='#".$myHistoryModalID."'>History</a>";
							} else {
								$history_string = "<i>No History</i>";
							}
							array_push($data, $privDataObj->get_type_desc().$type_tooltip,
								$privDataObj->get_name() ."$name_tooltip", $password,
								$privDataObj->get_group_name(),
								"<a name='modal' href='#".$PdataModalID."'>Edit</a> &nbsp&nbsp&nbsp &nbsp&nbsp&nbsp
								<a name='modal' href='#".$myDeleteModalID."'>Delete</a> &nbsp&nbsp&nbsp &nbsp&nbsp&nbsp
								$history_string"
							);
							// Replace Heading of original Form, where used to be Group,
							// we now make the Edit / Delete fields
						} else {
							array_push($data, $privDataObj->get_type_desc().$type_tooltip,
								$privDataObj->get_name(),
								"*********",
								$privDataObj->get_group_name(),
								"<b>Could not retrieve Private Data. Reason: ".$privDataObj->get_error() .
								"</b><br><a name='modal' href='#".$myModalID."'>Unlock Private Data</a>"
							);
						}
					} else {
						array_push($data, $privDataObj->get_type_desc().$type_tooltip,
							$privDataObj->get_name().$name_tooltip,
							"*********",
							$privDataObj->get_group_name(),
							"<a name='modal' href='#".$myModalID."'>Unlock Private Data</a>"
						);
					}
	
				}
				$pdata_form = new Form("auto", 5);
				$pdata_form->setSortable(true);
				$pdata_form->setHeadings($heading);
				$pdata_form->setData($data);
				$pdata_form->setTableWidth("777px");
				echo $pdata_form->showForm();
				echo $modalForms;


			} else {
				//echo "No Private data for this Device";
			}
			echo $private_data_type_modal;
		}
	}
	echo "</div>";
}

//display all the device IP
function displayIP()
{
	//global all variables
	global $deviceKey, $deviceForm, $tool;
	$deviceForm->setCols(2);
	$deviceForm->setTableWidth('200px');
	
	//create the toolbar
	/*$toolNames = array("All Devices", "All Archived Device");
	$toolIcons = array("devices", "devices");
	$toolHandlers = array("handleEvent('devices.php')", "handleEvent('devices.php?action=showArchived')");
	
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);*/
	
	//make place holder arrays for the ips	
	$devices = Device::get_devices();
	$info = array();
	$info2 = array();
	$title = array();
	$title2 = array();
	$headings = array();
	$handlers = array();
	$isTitle = true;
	
	//push the ip addresses to the corresponding devices
	foreach($devices as $id => $value)
	{
		$curDevices = new Device($id);
		$interfaces = $curDevices->get_interfaces();
		
		array_push($headings, $curDevices->get_name());		
		array_push($headings, "*<break>*");
		foreach($interfaces as $iId => $iValue)
		{
			$ipv4=$iValue->get_ipv4_addresses();
			$ipv6=$iValue->get_ipv6_addresses();
			
			foreach($ipv4 as $ipID=>$ipValue)
			{
				$actualIP = explode("/", $ipID, 2);
				$hostname=gethostbyaddr($actualIP[0]);
				if (preg_match('/^(192\.168|10\.|172\.[1-3]|127\.0\.0\.1)/', $ipID)) {
					if ($hostname == $actualIP[0] || $hostname=='')
					{$hostname = "<b class='DNS RFC' style='color:#CCC; filter:alpha(opacity=30);'>No DNS entry for this IP</b>";}
					else {$hostname = "<span class='DNS RFC'>".$hostname."</span>";}
					$rfcIP = "<span class='RFC'>".$ipID."</span>";
					array_push($title, $rfcIP);
				}
				else
				{
					if ($hostname == $actualIP[0] || $hostname=='')
					{$hostname = "<b class='DNS' style='color:#CCC; filter:alpha(opacity=30);'>No DNS entry for this IP</b>";}
					else {$hostname = "<span class='DNS'>".$hostname."</span>";}
					array_push($title, $ipID);
				}
				
				array_push($info, $hostname);
			}
			
			foreach($ipv6 as $ipID=>$ipValue)
			{	
				$actualIP = explode("/", $ipID, 2);
				$hostname=gethostbyaddr($actualIP[0]);
				
				if (preg_match('/^(192\.168|10\.|172\.[1-3]|127\.0\.0\.1)/', $ipID)) {
					if ($hostname == $actualIP[0] || $hostname=='')
					{$hostname = "<b class='DNS RFC' style='color:#CCC; filter:alpha(opacity=30);'>No DNS entry for this IP</b>";}
					else {$hostname = "<span class='DNS RFC'>".$hostname."</span>";}
					$rfcIP = "<span class='RFC'>".$ipID."</span>";
					array_push($title2, $rfcIP);
				}
				else
				{
					if ($hostname == $actualIP[0] || $hostname=='')
					{$hostname = "<b class='DNS' style='color:#CCC; filter:alpha(opacity=30);'>No DNS entry for this IP</b>";}
					else {$hostname = "<span class='DNS'>".$hostname."</span>";}
					array_push($title2, $ipID);
				}
				array_push($info2, $hostname);
			}
		}
		array_push($title, "*<break>*");
		array_push($title2, "*<break>*");
	}
	echo "<input type='checkbox' id='hideDNS'>Disable DNS Resolving</input>";
	echo " | <input type='checkbox' id='hideRFC'>Suppress RFC1918 and link Local addresses</input>";
	
	//show all the IPv4 and IPv6 addresses
	$deviceForm->setFirst(true);
	echo $deviceForm->showAll($headings, $title, $info, $handlers);
	$deviceForm->setFirst(false);
	echo $deviceForm->showAll($headings, $title2, $info2, $handlers);
}

//This function displays the add contact form
function addDeviceForm($devices)
{
	//global all variables and make the tool bar
	global $tool, $deviceForm, $headings, $titles, $deviceKey, $deviceTypes, $location;
	
	/*Taken out for user interface issues
	$toolNames = array("All Devices", "All Archived Devices");
	$toolIcons = array("devices", "devices");
	$toolHandlers = array("handleEvent('devices.php')", "handleEvent('devices.php?action=showArchived')");
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);*/
		
	$deviceForm->setCols(2);
		
	if($_GET['item']==mPort)
	{
		$devices = new Device($_GET['ID']);
		$deviceForm->setCols(2);
		$name=$devices->get_name();
		$deviceKey = array("pType","description", "physicalPort", "portName", "portType", "controlledDevice");		
		$headings = array("Port Info for ".$name);
		$titles = array("pType","Description.tip.A descriptive name for this connection, i.e. \"console connection for router1, routing engine 2\" or \"Remote power cycle group for router1\"", 
								"Physical port.tip.Which port is this device physically connected to",
								"Port name.tip.Name of port. This will also be the name used for scripts",
								"Port type", "Control device");
		
		$dropDownAction = "LoadPage('devices.php?action=changeControlledPort&mode='+this.value, 'controlledPort');";
		$types = array("power_control"=>"Power Port", "console_server"=>"Console Port");
		
		$fieldType = array("hidden","", "", "", "drop_down.handler:".$dropDownAction, "custom");
		
		$fieldInfo = array("mport","", "", "", "", "<div id='controlledPort'></div>");
		$deviceForm->setFieldType($fieldType);
		$deviceForm->setData($fieldInfo);
		echo $deviceForm->newPortForm($headings, $titles, $deviceKey, $types);
		
	}
	
	else if($_GET['item']==cPort)
	{
		$devices = new Device($_GET['ID']);
		$deviceForm->setCols(2);
		$name=$devices->get_name();
		$deviceKey = array("pType","description", "physicalPort", "portName", "portType", "managedDevice");
		$headings = array("Port Info for ".$name);
		$titles = array("pType","Description.tip.A descriptive name for this connection, i.e. \"console connection for router1, routing engine 2\" or \"Remote power cycle group for router1\"", 
								"Physical port.tip.Which port is this device physically connected to", 
								"Port name.tip.Name of port. This will also be the name used for scripts", 
								"Port type",
								"Managed device.tip.Select a device or select \"Other Device\" if you want to manage a device that is not in the database. If you select \"Other Device\" please make sure to have a good port description.");
		$titles = array("pType","Description", "Physical port", "Port name", "Port type", "Managed device");
		
		$types = $devices->get_devices();
		
		$fieldType = array("hidden","", "", "", "static", "drop_down");
		
		if ($_GET['deviceClass']=="console_server")
		{$port = "console";}
		else {$port = "power";}
		
		$fieldInfo = array("cport","", "", "", $port, "");
		$deviceForm->setData($fieldInfo);
		$deviceForm->setFieldType($fieldType);
		echo $deviceForm->newPortForm($headings, $titles, $deviceKey, $types);
	}
	else
	{		//create a new client form
		$fieldType = array("","","drop_down", "drop_down", "", "text_area", "", "", "", "", "");
		$type = array($location, $deviceTypes);
		$deviceForm->setFieldType($fieldType);
		echo $deviceForm->newDeviceForm($headings, $titles, $deviceKey, $type);	
	}
}

//This function adds a new device to the existing devices
function addDevice($devices)
{
	//global the variables
	global $deviceKey, $deviceForm, $tool, $headings, $titles, $status;
					
	//create an empty temporary array to store all the new values given from the form
	$tempDeviceInfo=array();
	foreach($deviceKey as $index => $key)
	{
		$tempDeviceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempDeviceInfo[notes] = addslashes($tempDeviceInfo[notes]);
	$tempDeviceInfo[name] = addslashes($tempDeviceInfo[name]);
					
	//checks if the name is empty, if not set all the names and insert them
	if ($devices->set_name($tempDeviceInfo[name]))
		{
			//set all the values to the query
			$devices->set_device_fqdn($tempDeviceInfo[device_fqdn]);
			$devices->set_location_id($tempDeviceInfo[location]);
			$devices->set_device_type($tempDeviceInfo[device_type]);
			$devices->set_snmp_ro($tempDeviceInfo[snmp_ro]);
			$devices->set_device_oob($tempDeviceInfo[device_oob]);
			$devices->set_notes($tempDeviceInfo[notes]);
							
		//if the insert is sucessful reload the page with the new values
		if($this_ID=$devices->insert())
		{
			$status="success";
			$_SESSION['action'] = "Added new device: ".$tempDeviceInfo[name];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$this_ID&add=$status\">";
		}
		//or else show error
		else {
			$deviceForm->error("Warning: Failed to add device. Reason: ".$devices->get_error(), $_GET['ID']);
		}
	}
	//if no name, then output error
	else
	{								
		$deviceForm->error("Warning: Failed to add device. Reason: ".$devices->get_error(), $_GET['ID']);
	}										
}

//This function adds a new Port to the existing Ports
function addPort($id)
{
	//global the variables
	global $deviceKey, $deviceForm, $tool, $headings, $titles, $status;
	$control_port = new ControlPort(); 
	$pType = $_POST['pType'];	
	//create an empty temporary array to store all the new values given from the form
	$tempDeviceInfo=array();
	if(empty($pType)) {
		$deviceForm->error("Warning: Failed to add port. Reason: Unable to determine port typ pType");
		return false;
	}
	//give the right device key to the corresponding device (managed, control
	if($pType == "cport") {
		$deviceKey = array("description", "physicalPort", "portName", "portType", "group", "managedDevice");
	} elseif ($pType == "mport") {
		$deviceKey = array("description", "physicalPort", "portName", "portType", "group", "controlledDevice");
	} else {
		$deviceForm->error("Warning: Failed to add port. Reason: Unable to determine port type!");
	}
	
	foreach($deviceKey as $index => $key)
	{
		$tempDeviceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempDeviceInfo[description] = addslashes($tempDeviceInfo[description]);
	$tempDeviceInfo[portName] = addslashes($tempDeviceInfo[portName]);
	
	if (true)
	{
		$control_port->set_name($tempDeviceInfo[portName]);
		//set all the values to the query
		$control_port->set_port($tempDeviceInfo[physicalPort]);
		$control_port->set_group($tempDeviceInfo[group]);
		
		//set the correct info based on the type of device
		if ($pType == "cport")
		{
			$control_port->set_control_device_id($id);
			$control_port->set_managed_device_id($tempDeviceInfo[managedDevice]);
		}
		else 
		{
			$control_port->set_control_device_id($tempDeviceInfo[controlledDevice]);
			$control_port->set_managed_device_id($id);
		}
		
		//change the wording for power_control to power and console_control to console
		if ($tempDeviceInfo[portType]=="power_control")
		{$control_port->set_type("power");}
		else {$control_port->set_type("console");}
		$control_port->set_description($tempDeviceInfo[description]);
		
		//if the insert is sucessful reload the page with the new values
		if($this_ID=$control_port->insert())
		{
			$status="success";
			$_SESSION['action'] = "Added new device port: ".$tempDeviceInfo[portName];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]&add=$status&tab=2\">";
		}
		//or else show error
		else {
			$deviceForm->error("Warning: Failed to add port. Reason: ".$control_port->get_error(), $_GET['ID']);
		}
	}
	//if no name, then output error
	else
	{								
		$deviceForm->error("Warning: Failed to add port. Reason: ".$control_port->get_error(), $_GET['ID']);
	}										
}

// List defice types
function displayDeviceTypes() {
	global $deviceForm;
	switch (success)
	{
		case $_GET['update']:
		$deviceForm->success("Updated successfully");
		break;
		
		case $_GET['add']:
		$deviceForm->success("Added new data successfully");
		break;

		case $_GET['delete']:
		$deviceForm->success("Deleted and archived device successfully");
		break;
	}
		
	$deviceForm->setCols(4);
	$content = "<h1>Device Types</h1>";

	// Tools menu
	$tool = new EdittingTools();
	if ($_SESSION['access'] >= 50) {
		$toolNames = array("Add Device Type");
		$toolIcons = array("add");
		$toolHandlers = array("window.location.href='devices.php?action=add_device_type'");
		$content .= $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	$content .=" <div style=\"clear:both;\"></div><br> ";
	// end tools

	$device_types = Device_type::get_device_types();
	$heading = array( "Device Type", "Description", "Vendor","Class");
	$data = array();
	$handler = array();
	foreach ($device_types as $id => $name) {
		$devType = new Device_type($id);
		array_push ($data,$devType->get_name(),$devType->get_description(), $devType->get_vendor(), $devType->get_device_class());

		$url = "devices.php?action=show_device_type&devtype_id=$id";
		array_push($handler,"handleEvent('$url')");
	}
	
	$deviceForm->setSortable(true); // or false for not sortable
	$deviceForm->setHeadings($heading);
	$deviceForm->setEventHandler($handler);
	$deviceForm->setData($data);
	$content .= $deviceForm->showForm();
	#return $content;
	print $content;
}

function AddDeviceType() {
	global $deviceForm;
	$deviceForm->setCols(2);
	if (isset($_POST[updateInfo])) {
		$devType = new Device_type();
		// Ok Update
		$devType->set_name($_POST[Name]);
		$devType->set_description($_POST[Description]);
		$devType->set_vendor($_POST[Vendor]);
		$devType->set_device_class($_POST['Class']);
		if ($devType->insert()) {
			$_SESSION['action'] = $_POST[Name] ." Updated";
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=list_device_types&update=success\">";
			
		} else {
			$deviceForm->error("Warning: Failed to add device type. Reason: ".$devType->get_error()) ;
		}
		
	} else {
		// Render form
		$content = "<h1>Add Device Type</h1>";
		$form = new Form("auto",2);
		$handler = array();
		$values = array();
		$titles = array();
		$postkeys = array();
		$heading = array("Device Type Details");
		array_push ($postkeys,"Name", "Description","Vendor","Class");
		array_push ($titles,"Name.tip.Short descriptive name for device type", "Description.tip.Device Type description",
			"Vendor","Class.tip.This specifies the device type class and is used to group types by function<br>
			Two examples are console_server and power_control, these two will be availble as control devices");
		$deviceForm->setSortable(false);
		$deviceForm->setHeadings($heading);
		$deviceForm->setTitles($titles);
		$deviceForm->setDatabase($postkeys);
		$deviceForm->setData($values);
		$deviceForm->setTableWidth("60%");
		$deviceForm->setUpdateText("Add new Device Type");

		$content .= $deviceForm->editForm();
		#return $content;
		print $content;
	}
}
// Show defice type
function ArchiveDeviceType() {
	global $deviceForm;
	$deviceForm->setCols(2);
	$device_type_id = $_GET[devtype_id];
	if (empty($device_type_id)) {
		print "Sorry invalid device type<br>";
		return;
	}
	$devType = new Device_type($device_type_id);

	// Confimration part
	if ((! isset($_POST['confirm'])) || (($_POST['confirm'] !='No') && ($_POST['confirm'] !='Yes'))) {
		$form = new Form(auto,2);
		$msg = "<b>Are you sure your want to delete Device Type: ". $devType->get_name() ."</b>";
		print $form->confirm($msg);
		return;
	} elseif ($_POST['confirm'] =='No') {
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=show_device_type&devtype_id=$device_type_id\">";
	} else {
		if ($devType->delete()) {
			$_SESSION['action'] = $_POST[Name] ." Deleted";
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=list_device_types&delete=success\">";
			
		} else {
			$deviceForm->error("Warning: Failed to delete device type. Reason: ".$devType->get_error()) ;
		}
	}
}
// Show defice type
function EditDeviceType() {
	global $deviceForm;
	$deviceForm->setCols(2);
	$device_type_id = $_GET[devtype_id];
	if (empty($device_type_id)) {
		print "Sorry invalid device type<br>";
		return;
	}
	$devType = new Device_type($device_type_id);

	if (isset($_POST[updateInfo])) {
		// Ok Update
		$devType->set_name($_POST[Name]);
		$devType->set_description($_POST[Description]);
		$devType->set_vendor($_POST[Vendor]);
		$devType->set_device_class($_POST['Class']);
		if ($devType->update()) {
			$_SESSION['action'] = $_POST[Name] ." Updated";
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=list_device_types&update=success\">";
			
		} else {
			$deviceForm->error("Warning: Failed to add device type. Reason: ".$devType->get_error()) ;
		}
		
	} else {
		// Render form

		$content = "<h1>". $devType->get_name() ."</h1>";
		$form = new Form("auto",2);
		$handler = array();
		$values = array();
		$titles = array();
		$postkeys = array();
		$heading = array("Device Type Details");
		array_push ($postkeys,"Name", "Description","Vendor","Class");
		array_push ($titles,"Name.tip.Short descriptive name for device type", "Description.tip.Device Type description",
			"Vendor","Class.tip.This specifies the device type class and is used to group types by function<br>
			Two examples are console_server and power_control, these two will be availble as control devices");
		array_push ($values,$devType->get_name(),$devType->get_description(), $devType->get_vendor(), $devType->get_device_class());

		$deviceForm->setSortable(false);
		$deviceForm->setHeadings($heading);
		$deviceForm->setTitles($titles);
		$deviceForm->setDatabase($postkeys);
		$deviceForm->setData($values);
		$deviceForm->setTableWidth("60%");
		$content .= $deviceForm->editForm();
		#return $content;
		print $content;
	}
}


// Show defice type
function DisplayDeviceType() {
	global $deviceForm;
	$deviceForm->setCols(2);
	$device_type_id = $_GET[devtype_id];
	if (empty($device_type_id)) {
		print "Sorry invalid device type<br>";
		return;
	}
	$devType = new Device_type($device_type_id);
	$content = "<h1>". $devType->get_name() ."</h1>";

	// Tools menu
	$tool = new EdittingTools();
	if ($_SESSION['access'] >= 50) {
		$toolNames = array("Edit","Delete");
		$toolIcons = array("edit","delete");
		$toolHandlers = array(
			"window.location.href='devices.php?action=edit_device_type&devtype_id=$device_type_id'",
			"window.location.href='devices.php?action=archive_device_type&devtype_id=$device_type_id'"
		);
		$content .= $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	$content .=" <div style=\"clear:both;\"></div><br> ";


	$form = new Form("auto",2);
	$handler = array();
	$values = array();
	$titles = array();
	$postkeys = array();
	$heading = array("Device Type Details");
	array_push ($postkeys,"Name", "Description","Vendor","Class");
	array_push ($titles,"Name.tip.Short descriptive name for device type", "Description.tip.Device Type description",
			"Vendor","Class.tip.This specifies the device type class and is used to group types by function<br>
			Two examples are console_server and power_control, these two will be availble as control devices");
	array_push ($values,$devType->get_name(),$devType->get_description(), $devType->get_vendor(), $devType->get_device_class());

	$deviceForm->setSortable(false);
	$deviceForm->setHeadings($heading);
	$deviceForm->setTitles($titles);
	$deviceForm->setDatabase($postkeys);
	 $deviceForm->setData($values);
	$deviceForm->setTableWidth("60%");
	$content .= $deviceForm->showForm();
	#return $content;
	print $content;
}


//This function adds a new device type to the existing device types
/*
function addDeviceType($deviceType)
{
	//global the variables
	global $deviceKey, $deviceForm, $tool, $headings, $titles, $status;
	//create an empty temporary array to store all the new values given from the form
	$tempDeviceInfo=array();
	$deviceKey = array("name", "description", "vendor", "dClass", "notes");
	
	foreach($deviceKey as $index => $key)
	{
		$tempDeviceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempDeviceInfo[description] = addslashes($tempDeviceInfo[description]);
	$tempDeviceInfo[name] = addslashes($tempDeviceInfo[name]);
	
	if (true)
	{
		$deviceType->set_name($tempDeviceInfo[name]);
		//set all the values to the query
		$deviceType->set_description($tempDeviceInfo[description]);
		$deviceType->set_vendor($tempDeviceInfo[vendor]);
		$deviceType->set_device_class($tempDeviceInfo[dClass]);
		$deviceType->set_notes($tempDeviceInfo[notes]);
		
		//if the insert is sucessful reload the page with the new values
		if($this_ID=$deviceType->insert())
		{
			$status="success";
			$allType = Device_type::get_device_types();
			
			$_SESSION['action'] = "Added new device type: ".$tempDeviceInfo[name];
			$update = new Updates();
			$update->set_action($_SESSION['action']);
			$update->set_username($_SESSION['fullname']);
			if($update->insert_update())
			{$_SESSION['action']="";}
			
			foreach($allType as $id =>$type)
			{
				echo "<option id=$id name=$id>$type</option>";	
			}
		}
		//or else show error
		else {
			$deviceForm->error("Warning: Failed to add device type. Reason: ".$deviceType->get_error(), $_GET['ID']);
		}
	}
	//if no name, then output error
	else
	{								
		$deviceForm->error("Warning: Failed to add device type. Reason: ".$deviceType->get_error(), $_GET['ID']);
	}										
}
*/

//This function adds a new location to the existing locations
function addLocation($location)
{
	//global the variables
	global $deviceKey, $deviceForm, $tool, $headings, $titles, $status;
	//create an empty temporary array to store all the new values given from the form
	$tempDeviceInfo=array();
	$deviceKey = array("name", "city", "address", "postal", "phone", "email", "room". "notes", "addLocation");
	
	//add the values from the array
	foreach($deviceKey as $index => $key)
	{
		$tempDeviceInfo[$key] = addslashes(htmlspecialchars(trim($_POST[$key]),ENT_QUOTES));
	}
	
	//set the values for location
	if ($location->set_name($tempDeviceInfo[name]))
	{
		$location->set_city($tempDeviceInfo[city]);
		$location->set_address($tempDeviceInfo[address]);
		$location->set_postal_code($tempDeviceInfo[postal]);
		$location->set_email($tempDeviceInfo[email]);
		$location->set_phone($tempDeviceInfo[phone]);
		$location->set_room($tempDeviceInfo[room]);
		$location->set_notes($tempDeviceInfo[notes]);
		
		//if the insert is sucessful reload the page with the new values
		if($this_ID=$location->insert())
		{
			$status="success";
			$allLocation = Location::get_locations();
			
			$_SESSION['action'] = "Added new device location: ".$tempDeviceInfo[name];
			$update = new Updates();
			$update->set_action($_SESSION['action']);
			$update->set_username($_SESSION['fullname']);
			if($update->insert_update())
			
			{$_SESSION['action']="";}
			
			foreach($allLocation as $id =>$type)
			{
				echo "<option id=$id name=$id>$type</option>";	
			}
		}
		//or else show error
		else {
			$deviceForm->error("Warning: Failed to add location. Reason: ".$location->get_error(), $_GET['ID']);
		}
	}
	//if no name, then output error
	else
	{								
		$deviceForm->error("Warning: Failed to add location. Reason: ".$location->get_error(), $_GET['ID']);
	}										
}
			
//The function removes a device
function removeDevice($devices)
{
	//global the variables
	global $deviceKey, $deviceForm, $tool, $headings, $titles, $status;
					
	
					
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (true){
			if($devices->delete()){
				$status="success";
				$_SESSION['action'] = "Removed ".$devices->get_name();
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?delete=$status\">";
			}
			//or else show error
			else
			{
				$deviceForm->error("Warning: Failed to remove. Reason: ".$devices->get_error(), $_GET['ID']);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$deviceForm->prompt("Are you sure you want to delete?");
	}					
}

//The function removes a ports
function removePort($ports)
{
	//global the variables
	global $deviceKey, $deviceForm, $tool, $headings, $titles, $status;
					
	
					
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (true){
			if($ports->delete()){
				$status="success";
				$_SESSION['action'] = "Removed ".$ports->get_name();
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]&delete=$status&tab=2\">";
			}
			//or else show error
			else
			{
				$deviceForm->error("Warning: Failed to remove. Reason: ".$ports->get_error(), $_GET['ID']);
			}						
		}
	}
	//if the user does not confirm, then refresh to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]&tab=2\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$deviceForm->prompt("Are you sure you want to delete?");
	}					
}
?>

<script language="javascript">
$(function() {
		   $('#hideDNS').click(function(){
					if ($(".DNS").is(":visible") && $("#hideDNS").attr('checked'))
					{
						$(".DNS").hide();
					}
					else
					{
						$(".DNS").show();
						if($("#hideRFC").attr('checked'))
						{
							$(".RFC").hide();	
						}
					}
									})
		   $('#hideRFC').click(function(){
					if ($(".RFC").is(":visible") && $("#hideRFC").attr('checked'))
					{
						$(".RFC").hide();
					}
					else
					{
						$(".RFC").show();
						if($("#hideDNS").attr('checked'))
						{
							$(".DNS").hide();	
						}
					}
									})
		   
		   });
</script>

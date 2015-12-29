<?php
include_once("sessionCheck.php");
/*Database coding: this checks for multiple different actions made by users and responds accordingly.*/
include_once "classes/Service.php";
include_once "classes/Device.php";
include_once "classes/Contact.php";
include_once 'config/graph.conf';

if(!isset($_GET['mode']))
{include("controlBar.php");}
?>

<?
if(!isset($_GET['mode']))
{
?>
<meta http-equiv="refresh" content="60" />
<div id="main">
<h1 id="mainTitle">STATISTICS
<?

if(isset($_GET['ID']))
{
	echo "<div style='font-size:10px; font-weight:100px;'>";
	$link = $_SERVER['PHP_SELF'];
	echo "<a href='".$link."'>All Devices</a>";
	
	$link = $_SERVER['PHP_SELF']."?action=showGraph&ID=".$_GET['ID']."&pageNum=1&active=yes";
	$device = new Device($_GET['ID']);
	$deviceName = $device->get_name();
	
	if(isset($_GET['interID']))
	{
		echo " >> <a href='".$link."'>".$deviceName."</a>";
		
		$port = new Port($_GET['interID']);
		$portName = $port->get_name();
		
		if (preg_match("/\.[0-9]/", $portName))
		{
			$physPort = array();
			$physPort = explode(".", $portName, 2);
			$interID = $device->get_interface_id_by_name($physPort[0]);
			$link = $_SERVER['PHP_SELF']."?action=showGraphDetail&ID=".$_GET['ID']."&interID=".$interID."&active=up&type=".$_GET['type'];
			echo " >> <a href='".$link."'>".$physPort[0]."</a>";
		}
		if($_GET['action']=="zoomGraphDetail")
		{
			$link = $_SERVER['PHP_SELF']."?action=showGraphDetail&ID=".$_GET['ID']."&interID=".$_GET['interID']."&active=up&type=".$_GET['type'];
			echo " >> <a href='".$link."'>".$portName."</a>";
			
			if($_GET['action']=='zoomGraphDetail')
			{
				echo " >> zoom";
			}
		}
		else
		{
			$link = $_SERVER['PHP_SELF']."?action=showGraph&ID=".$_GET['ID']."&interID=".$_GET['interID']."&pageNum=1";
			echo " >> <a href='".$link."'>".$portName."</a>";
		}
	}
	else
	{
		echo " >> ".$deviceName;
	}
	echo "</div>";
}
?>
</h1>

<?
}
?>


<?
//A javascript minor glitch that's not fixable must be included here instead
include_once 'classes/EdittingTools.php';
include_once 'classes/DeviceForm.php';

//Make a new contact, a new tool bar, and a new form
$tool = new EdittingTools();
$deviceForm = new DeviceForm("auto", 4);
//get the new contact corresponding to the ID
$devices = new Device($_GET['ID']);
$status;
$keyword = $_POST['keyword'];
$options = array('THIS DEVICE', 'GLOBAL');

if($_GET['action'] == showGraph)
{
	$result = $_POST['list'];
	displayGraph($devices, $result);
}

else if($_GET['action'] == showGraphResult)
{
	//get the new contact corresponding to the ID
	displayGraphResult($keyword);
}

else if($_GET['action'] == showGraphList)
{
	if($_POST['options']=='GLOBAL')
	{displayGraphListResult($keyword);}
	else{
		//get the new contact corresponding to the ID
		displayGraphList($devices);
	}
}

else if($_GET['action'] == showGraphListResult)
{
	//get the new contact corresponding to the ID
	displayGraphListResult($keyword);
}

else if($_GET['action'] == showGraphDetail)
{
	//get the new contact corresponding to the ID
	displayGraphDetail($devices);
}

else if($_GET['action'] == showAggreGraph)
{
	$graphID=$_GET['aggreName'];
	//get the new contact corresponding to the ID
	aggregatedGraphs($graphID);
}

else if($_GET['action'] == zoomGraphDetail)
{
?><!--Cropper Files--->
<script src="js/cropper/lib/prototype.js" type="text/javascript"></script>      
<script src="js/cropper/lib/scriptaculous.js?load=builder,dragdrop" type="text/javascript"></script>
<script src="js/cropper/cropper.js" type="text/javascript"></script>
<script src="js/cropper/smokeping-zoom.js" type="text/javascript"></script>
<?
	//get the new contact corresponding to the ID
	zoomGraph($devices);
}

//if nothing else, display all the clients for the user to see
else
{
	displayAll($devices);
}

?>
</div>        
<?php 
if(!isset($_GET['mode']))
{include("footer.php");} ?>


<?
/*****************************************************FUNCTIONS************************************************/

//This function displays all the contacts
function displayAll($devices)
{
	//global the tool and make a tool bar for adding a client
	global $tool, $deviceForm, $aggregrated_graph_traffic, $nested_aggregrated_graph_traffic;
	
	$searchAction="statistics.php?action=showGraphListResult";
	echo $tool->createSearchBar($searchAction);
	
	$deviceForm->setCols(2);
	$deviceForm->setTableWidth('20%');
	$deviceForm->setTitleWidth('90%');
	$allDevices = $devices->get_devices();
	
	$heading = array("Devices");
	$title = array();
	$handlers = array();
	$info = array();
	
	foreach ($allDevices as $id => $value)
	{
		array_push($title, $value);
		array_push($handlers, "handleEvent('statistics.php?action=showGraph&ID=$id&pageNum=1&active=yes')");
	}
	
	echo $deviceForm->showAll($heading, $title, $info, $handlers);
	
	$heading2 = array("Aggregated Devices");
	$title2 = array();
	$handlers2 = array();
	$info2 = array();
	
	foreach ($aggregrated_graph_traffic as $id => $value)
	{
		$name = $id;
		array_push($title2, $id);
		$name = str_replace(" ", "%20", $name);
		array_push($handlers2, "handleEvent('statistics.php?action=showAggreGraph&aggreName=".$id."')");
	}
	
	foreach ($nested_aggregrated_graph_traffic as $id => $value)
	{
		$name = $id;
		array_push($title2, $id);
		$name = str_replace(" ", "%20", $name);
		array_push($handlers2, "handleEvent('statistics.php?action=showAggreGraph&aggreName=".$id."')");
	}

	$deviceForm->setFirst(false);
	echo $deviceForm->showAll($heading2, $title2, $info2, $handlers2);
}

#/~atoonk/test/rrd/graph.php?file=deviceid36_ifc1-(Slot:-1-Port:-1)&titel=ifc1 (Slot: 1 Port: 1)---Bits per Second&height=100&width=217&type=traffic errors unicastpkts nonunicastpkts
function displayGraph($devices, $results)
{
	//global the tool and make a tool bar for adding a client
	global $tool, $deviceForm, $options;
	
	$interfaceStorage = array();
	$pageNum = $_GET['pageNum'];
	
	$interfaces = $devices->get_interfaces();
	
	if (!isset($results))
	{
		if($_GET['mode'] == filter || $_GET['neglect'] == filter)
		{
			foreach ($interfaces as $id => $value)
			{	
				if ($_GET['active']!=yes)
				{
					if ($_GET['physical']==yes)
					{
						$name = $value->get_name();
						if(!preg_match("/.*\.\d+$/", $name))
						{
							array_push($interfaceStorage, $value);
						}
					}
					else {
						array_push($interfaceStorage, $value);
					}
				}
				else
				{
					if ($value->get_oper_status() == 'up')
					{
						if ($_GET['physical']==yes)
						{
							$name = $value->get_name();
							if(!preg_match("/.*\.\d+$/", $name))
							{
								array_push($interfaceStorage, $value);
							}
						}
						else {array_push($interfaceStorage, $value);}
					}
				}	
			}
		}
		else if (isset($_GET['interID']))
		{
			$port = new Port($_GET['interID']);
			array_push($interfaceStorage, $port);
		}
		else {
			foreach ($interfaces as $id => $value)
			{
				if ($value->get_oper_status() == 'up')
				{
					array_push($interfaceStorage, $value);
				}
			}
		}
	}
	else
	{
		foreach ($interfaces as $id => $value)
		{
			foreach($results as $rID => $rValue)
			{
				if ($rValue==$id)
				{
					array_push($interfaceStorage, $value);
				}
			}
		}
	}
	
	$numInterface = count($interfaceStorage);
	
	$itemPerPage = 10;
	$numPages = intval($numInterface/$itemPerPage);
	
	if(!isset($_GET['mode']))
	{
		$searchAction="statistics.php?action=showGraphList&ID=".$devices->get_device_id();
		echo $tool->createSearchBar($searchAction, $options);
		
		if (!isset($results))	
		{	
			echo "<form name='filterForm' method='POST' style='float:left;'>";
			
			if ($_GET['active']==yes) {
				echo "<input type='checkbox' checked name='activePort' onclick=\"return checkCheck(activePort, ".$pageNum.")\" >Active Port</input>";
			}
			else {
				echo "<input type='checkbox' name='activePort' onclick=\"return checkCheck(activePort, ".$pageNum.")\" >Active Port</input>";
			}
			
			if ($_GET['physical']==yes) {
				echo "<input type='checkbox' checked name='physicalPort' onclick=\"return checkCheck(physicalPort, ".$pageNum.")\" >Physical Port</input>";
			}
			else {
				echo "<input type='checkbox' name='physicalPort' onclick=\"return checkCheck(physicalPort, ".$pageNum.")\" >Physical Port</input>";
			}
			echo "</form>";
		}
	}
	
	#********************LINK*****************************echo "<a href='#' style='float:left; margin-left:10px;'>Statistics->".$device."</>";
	
	echo "<div id='filteredResult' style='width:1024px;'>";
	echo "<div style='float:right; margin-left:10px;'>
			<input type='button' enabled icon='graph' onclick=\"handleEvent('statistics.php?action=showGraph&ID=".$devices->get_device_id()."&pageNum=".$_GET['pageNum']."&active=".$_GET['active']."&physical=".$_GET['physical']."')\">
			<input type='button' icon='list' onclick=\"handleEvent('statistics.php?action=showGraphList&ID=".$devices->get_device_id()."&pageNum=".$_GET['pageNum']."&active=".$_GET['active']."&physical=".$_GET['physical']."')\">
		</div>";
	echo "<div id='navigationBar'>";
	echo $tool->createNavigation($pageNum, $numPages);
	echo "</div>";
	
	$firstItem = $pageNum * $itemPerPage - $itemPerPage;
	$lastItem = $pageNum * $itemPerPage - 1;
	if(isset($interfaceStorage[$firstItem]))
	{
		echo "<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%; clear:left;'>
		<thead><tr><th colspan='4'>Statistics for ".$devices->get_name()."</th></tr></thead>"; 
		#"<select name='interfaces' onchange=\"return LoadPage('statistics.php?action=showCurGraph&mode=graphTime'+this.value, 'statGraphs')\">";
	
		for($index=$firstItem;$index<=$lastItem; $index++) //($interfaces as $id => $value)
		{
			$allLinks = array();
			$linkString;
			
			if(!isset($interfaceStorage[$index]))
			{break;}
			
			$deviceID = $devices->get_device_id();
			$oriName = $interfaceStorage[$index]->get_name();
			$nameTitle = str_replace(" ", "%20", $oriName);
			$name = str_replace(" ", "-", $oriName);
			$name = str_replace("/", "-", $name);
			$height = 50;
			$width= 150;
			
			$graph = "Bits Per Second";
			$graph = str_replace(" ", "%20", $graph);
			$type = "traffic";
			$type = str_replace(" ", "%20", $type);
			$link="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=".$height."&width=".$width."&type=".$type."&legend=0";
			array_push($allLinks, $link);
			
			$graph = "Errors";
			$graph = str_replace(" ", "%20", $graph);
			$type = "errors";
			$type = str_replace(" ", "%20", $type);
			$link="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=".$height."&width=".$width."&type=".$type."&legend=0";
			array_push($allLinks, $link);
			
			$graph = "Unicast Packets";
			$graph = str_replace(" ", "%20", $graph);
			$type = "unicastpkts";
			$type = str_replace(" ", "%20", $type);
			$link="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=".$height."&width=".$width."&type=".$type."&legend=0";
			array_push($allLinks, $link);
			
			$graph = "Non Unicast Packets";
			$graph = str_replace(" ", "%20", $graph);
			$type = "nonunicastpkts";
			$type = str_replace(" ", "%20", $type);
			$link="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=".$height."&width=".$width."&type=".$type."&legend=0";
			array_push($allLinks, $link);
			
			foreach($allLinks as $id=>$value)
			{
				$linkString .= "&img".$id."=".$value;
			}
			
			$detail = "statistics.php?action=showGraphDetail&ID=".$deviceID."&interID=".$interfaceStorage[$index]->get_interface_id()."&active=".$interfaceStorage[$index]->get_oper_status()."&type=";
			echo "<tbody><tr class='form'>
				<td colspan='4' style='text-align:center' class='info'><h3>".$oriName." | ".$interfaceStorage[$index]->get_alias()." - ".$interfaceStorage[$index]->get_descr()."</h3></td></tr>
				<tr>
				<td><a href='#' onclick=\"handleEvent('".$detail."traffic')\"><img src=".$allLinks[0]."></a></td>
				<td><a href='#' onclick=\"handleEvent('".$detail."errors')\"><img src=".$allLinks[1]."></a></td>
				<td><a href='#' onclick=\"handleEvent('".$detail."unicastpkts')\"><img src=".$allLinks[2]."></a></td>
				<td><a href='#' onclick=\"handleEvent('".$detail."nonunicastpkts')\"><img src=".$allLinks[3]."></a></td>
				</tr></tbody>";
		#<option name='interfaceName' value='".$linkString."'>".$oriName."</option>";
		//http:grizzly.bc.net/~atoonk/test/rrd/graph.php?file=deviceid3_ge-0-0-0&titel=ge-0/0/0---Bits%20per%20Second&height=100&width=217&type=traffic
		//http:grizzly.bc.net/~atoonk/test/rrd/graph.php?file=deviceid3_ge-0/0/0&titel=ge-0/0/0---Bits%20Per%20Second&height=100&width=217&type=traffic
			#";
			
			$itemOnPage++;
			if ($itemPerPage <= $itemOnPage)
			{break;}
		}
		echo "</table>";
	}
	else {$deviceForm->warning("No interfaces found");}
	
	echo "<div style='float:right; margin-left:10px;'>
		<input type='button' enabled icon='graph' onclick=\"handleEvent('statistics.php?action=showGraph&ID=".$devices->get_device_id()."&pageNum=".$_GET['pageNum']."&active=".$_GET['active']."&physical=".$_GET['physical']."')\">
		<input type='button' icon='list' onclick=\"handleEvent('statistics.php?action=showGraphList&ID=".$devices->get_device_id()."&pageNum=".$_GET['pageNum']."&active=".$_GET['active']."&physical=".$_GET['physical']."')\">
	</div>";
	echo "<div id='navigationBar'>";
	echo $tool->createNavigation($pageNum, $numPages);
	echo "</div>
	</div>";
	 
	#</select>
	#<div id='statGraphs'></div>";
}

function displayGraphResult($keyword)
{
	//global the tool and make a tool bar for adding a client
	global $tool, $deviceForm;
	
	$interfaceStorage = array();
	$pageNum = $_GET['pageNum'];
	
	if(isset($_POST['list']))
	{
		$list = $_POST['list'];
		$allDeviceID = array();
		$interfaceID = array();
			
		foreach ($list as $id => $value)
		{
			$full = explode('//', $value, 2);
			array_push($interfaceID, $full[0]);
			array_push($allDeviceID, $full[1]);
		}

		foreach ($allDeviceID as $id => $value)
		{
			$devices = new Device($value);
			$interfaces = $devices->get_interfaces();
				
			foreach ($interfaces as $iID => $iValue)
			{
				foreach($interfaceID as $iiID => $iiValue)
				{ 
					if($iiValue == $iID)
					{
						if (!in_array($iValue, $interfaceStorage))
						{array_push($interfaceStorage, $iValue);}
					}
				}
			}		
		}
	}
	
	else if (isset($_GET['interID']))
	{
		$port = new Port($_GET['interID']);
		array_push($interfaceStorage, $port);
	}
	
	if(!isset($_GET['mode']))
	{	
		$searchAction="statistics.php?action=showGraphListResult";
		echo $tool->createSearchBar($searchAction);
	}
	
	echo "<div id='filteredResult' style='width:1024px;'>";
	
	if(isset($interfaceStorage))
	{
		echo "<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%; clear:left;'>
		<thead><tr><th colspan='5'>Statistics</th></tr></thead>"; 
		#"<select name='interfaces' onchange=\"return LoadPage('statistics.php?action=showCurGraph&mode=graphTime'+this.value, 'statGraphs')\">";
	
		foreach($interfaceStorage as $index => $value) //($interfaces as $id => $value)
		{
			$allLinks = array();
			$linkString;
			
			if(!isset($interfaceStorage[$index]))
			{break;}
			
			if(isset($allDeviceID))
			{
				$deviceID = $allDeviceID[$index];
			}
			else if(isset($_GET['ID']))
			{
				$deviceID=$_GET['ID'];
			}
			
			$oriName = $interfaceStorage[$index]->get_name();
			$nameTitle = str_replace(" ", "%20", $oriName);
			$name = str_replace(" ", "-", $oriName);
			$name = str_replace("/", "-", $name);
			$height = 50;
			$width= 150;
			
			$graph = "Bits Per Second";
			$graph = str_replace(" ", "%20", $graph);
			$type = "traffic";
			$type = str_replace(" ", "%20", $type);
			$link="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=".$height."&width=".$width."&type=".$type."&legend=0";
			array_push($allLinks, $link);
			
			$graph = "Errors";
			$graph = str_replace(" ", "%20", $graph);
			$type = "errors";
			$type = str_replace(" ", "%20", $type);
			$link="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=".$height."&width=".$width."&type=".$type."&legend=0";
			array_push($allLinks, $link);
			
			$graph = "Unicast Packets";
			$graph = str_replace(" ", "%20", $graph);
			$type = "unicastpkts";
			$type = str_replace(" ", "%20", $type);
			$link="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=".$height."&width=".$width."&type=".$type."&legend=0";
			array_push($allLinks, $link);
			
			$graph = "Non Unicast Packets";
			$graph = str_replace(" ", "%20", $graph);
			$type = "nonunicastpkts";
			$type = str_replace(" ", "%20", $type);
			$link="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=".$height."&width=".$width."&type=".$type."&legend=0";
			array_push($allLinks, $link);
			
			foreach($allLinks as $id=>$value)
			{
				$linkString .= "&img".$id."=".$value;
			}
			
			$detail = "statistics.php?action=showGraphDetail&ID=".$deviceID."&interID=".$interfaceStorage[$index]->get_interface_id()."&active=".$interfaceStorage[$index]->get_oper_status()."&type=";
			echo "<tbody><tr class='form'>
				<td colspan='4' style='text-align:center' class='info'><h3>".$oriName." | ".$interfaceStorage[$index]->get_alias()." - ".$interfaceStorage[$index]->get_descr()."</h3></td></tr>
				<tr>
				<td><a href='#' onclick=\"handleEvent('".$detail."traffic')\"><img src=".$allLinks[0]."></a></td>
				<td><a href='#' onclick=\"handleEvent('".$detail."errors')\"><img src=".$allLinks[1]."></a></td>
				<td><a href='#' onclick=\"handleEvent('".$detail."unicastpkts')\"><img src=".$allLinks[2]."></a></td>
				<td><a href='#' onclick=\"handleEvent('".$detail."nonunicastpkts')\"><img src=".$allLinks[3]."></a></td></tr></tbody>";
		#<option name='interfaceName' value='".$linkString."'>".$oriName."</option>";
		//http:grizzly.bc.net/~atoonk/test/rrd/graph.php?file=deviceid3_ge-0-0-0&titel=ge-0/0/0---Bits%20per%20Second&height=100&width=217&type=traffic
		//http:grizzly.bc.net/~atoonk/test/rrd/graph.php?file=deviceid3_ge-0/0/0&titel=ge-0/0/0---Bits%20Per%20Second&height=100&width=217&type=traffic
			#";
		}
		echo "</table>";
	}
	else {$deviceForm->warning("No interfaces found");}
		
	echo "</div>";
	 
	#</select>
	#<div id='statGraphs'></div>";
}

function displayGraphList($devices)
{
	//global the tool and make a tool bar for adding a client
	global $tool, $deviceForm, $options;
	
	$keyword = $_POST['keyword'];
	
	$interfaces = array();
	$interfaces = $devices->get_interfaces();
	
	$numInterface = count($interfaces);
	
	if(!isset($_GET['mode']))
	{
		$searchAction="statistics.php?action=showGraphList&ID=".$devices->get_device_id();
		echo $tool->createSearchBar($searchAction, $options);
	}
	
	echo "<div id='filteredResult' style='width:1024px; text-align:left;'>";
	echo "<div style='float:right; margin-left:10px;'>
			<input type='button' enabled icon='graph' onclick=\"handleEvent('statistics.php?action=showGraph&ID=".$devices->get_device_id()."&pageNum=".$_GET['pageNum']."&active=".$_GET['active']."&physical=".$_GET['physical']."')\">
			<input type='button' icon='list' onclick=\"handleEvent('statistics.php?action=showGraphList&ID=".$devices->get_device_id()."&pageNum=".$_GET['pageNum']."&active=".$_GET['active']."&physical=".$_GET['physical']."')\">
		</div>";
	echo "<form name='listForm' method='post' action='statistics.php?action=showGraph&ID=".$_GET['ID']."&pageNum=1'>
		<input type='submit' name='returnResults' value='Display Checked Graphs' style='float:right; margin-bottom:5px;'></input>
		<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%; clear:left;'>
		<thead><tr><th colspan='4'>
		<input type='checkbox' name='all' onclick=\"checkAll(document.listForm['list[]'], all)\">Select All</input> | Statistics for ".$devices->get_name()."
		</th></tr></thead>"; 
		#"<select name='interfaces' onchange=\"return LoadPage('statistics.php?action=showCurGraph&mode=graphTime'+this.value, 'statGraphs')\">";
	
	if (isset($interfaces))
	{
		foreach($interfaces as $id => $value) //($interfaces as $id => $value)
		{
			$deviceName = $devices->get_name();
			$link = "statistics.php?action=showGraphDetail&ID=".$devices->get_device_id()."&interID=".$value->get_interface_id()."&active=".$value->get_oper_status()."&type=";
			$all = "statistics.php?action=showGraph&ID=".$devices->get_device_id()."&interID=".$value->get_interface_id()."&pageNum=1&active=yes";
			
			if (isset($keyword))
			{
				$specialChars = array('/', '(', ')', '>', '<', '[', ']');
			$newKey = $keyword;
			foreach ($specialChars as $sid => $svalue)
			{
				$newKey = str_replace($svalue, "\\".$svalue, $newKey);
			}
				
				if (preg_match("/".$newKey."/i", $value->get_name()) || preg_match("/".$newKey."/i",$value->get_descr()) || preg_match("/".$newKey."/i",$value->get_alias()))
				{
					echo "<tr><td style='width:20%; border-bottom:solid thin;'><input type='checkbox' name='list[]' value='".$value->get_interface_id()."//".$value->get_device_id()."'>".$value->get_name()."</input></td>
					<td style='border-bottom:solid thin;'>".$value->get_descr()." | ". $value->get_alias()."</td>
					<td style='border-bottom:solid thin;'>".$deviceName."</td>
					<td style='border-bottom:solid thin; width:35%'>
					<input type='button' onclick=\"handleEvent('".$link."traffic')\" value='Traffic' />
					<input type='button' onclick=\"handleEvent('".$link."errors')\" value='Error' />
					<input type='button' onclick=\"handleEvent('".$link."unicastpkts')\" value='Unicast' />
					<input type='button' onclick=\"handleEvent('".$link."nonunicastpkts')\" value='Nonunicast' />
					<input type='button' onclick=\"handleEvent('".$all."')\" value='All' />
					</td></tr>";
				}
			}
			
			else
			{
				echo "<tr><td style='width:20%; border-bottom:solid thin;'><input type='checkbox' name='list[]' value='".$value->get_interface_id()."'>".$value->get_name()."</input></td>
					<td style='border-bottom:solid thin;'>".$value->get_descr()." | ". $value->get_alias()."</td>
					<td style='border-bottom:solid thin;'>".$deviceName."</td>
					<td style='border-bottom:solid thin; width:35%'>
					<input type='button' onclick=\"handleEvent('".$link."traffic')\" value='Traffic' />
					<input type='button' onclick=\"handleEvent('".$link."errors')\" value='Error' />
					<input type='button' onclick=\"handleEvent('".$link."unicastpkts')\" value='Unicast' />
					<input type='button' onclick=\"handleEvent('".$link."nonunicastpkts')\" value='Nonunicast' />
					<input type='button' onclick=\"handleEvent('".$all."')\" value='All' />
					</td></tr>";
			}
		}
	}
	
	else {$deviceForm->warning("No interfaces for this device");}
		
	echo "</table>";
	echo "<div style='float:right; margin-left:10px;'>
		<input type='button' enabled icon='graph' onclick=\"handleEvent('statistics.php?action=showGraph&ID=".$devices->get_device_id()."&pageNum=".$_GET['pageNum']."&active=".$_GET['active']."&physical=".$_GET['physical']."')\">
		<input type='button' icon='list' onclick=\"handleEvent('statistics.php?action=showGraphList&ID=".$devices->get_device_id()."&pageNum=".$_GET['pageNum']."&active=".$_GET['active']."&physical=".$_GET['physical']."')\">
		</div>";
		
	echo "<input type='submit' name='returnResults' value='Display Checked Graphs' style='float:right; margin-bottom:5px;'></input>
	</form>
	</div>";
}

function displayGraphListResult($keyword)
{
	//global the tool and make a tool bar for adding a client
	global $tool, $deviceForm;
	$interfaces = array();
	
	$allDevices = Device::get_devices();
	foreach ($allDevices as $id => $value)
	{
		$device = new Device($id);
		$interfaceStorage = $device->get_interfaces();
			
		foreach ($interfaceStorage as $iID => $iValue)
		{
			array_push($interfaces, $iValue);
		}
	}
	
	$numInterface = count($interfaces);
	
	if(!isset($_GET['mode']))
	{
		$searchAction="statistics.php?action=showGraphListResult";
		echo $tool->createSearchBar($searchAction);
	}
	
	echo "<div id='filteredResult' style='width:1024px; text-align:left;'>";
	echo "<form name='listForm' method='post' action='statistics.php?action=showGraphResult&ID=".$_GET['ID']."&pageNum=1'>
		<input type='submit' name='returnResults' value='Display Checked Graphs' style='float:right; margin-bottom:5px;'></input>
		<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%; clear:left;'>
		<thead><tr><th colspan='4'>
		<input type='checkbox' name='all' onclick=\"checkAll(document.listForm['list[]'], all)\">Select All</input> | Statistics
		</th></tr></thead>"; 
		#"<select name='interfaces' onchange=\"return LoadPage('statistics.php?action=showCurGraph&mode=graphTime'+this.value, 'statGraphs')\">";
	
	if (isset($interfaces))
	{
		foreach($interfaces as $id => $value) //($interfaces as $id => $value)
		{
			$device = new Device($value->get_device_id());
			$deviceName = $device->get_name();
			$link = "statistics.php?action=showGraphDetail&ID=".$value->get_device_id()."&interID=".$value->get_interface_id()."&active=".$value->get_oper_status()."&type=";
			$all = "statistics.php?action=showGraphResult&ID=".$value->get_device_id()."&interID=".$value->get_interface_id()."&pageNum=1&active=yes";
			
			$specialChars = array('/', '(', ')', '>', '<', '[', ']');
			$newKey = $keyword;
			foreach ($specialChars as $sid => $svalue)
			{
				$newKey = str_replace($svalue, "\\".$svalue, $newKey);
			}
			if (preg_match("/".$newKey."/i", $value->get_name()) || preg_match("/".$newKey."/i",$value->get_descr()) || preg_match("/".$newKey."/i",$value->get_alias()))
			{
				echo "<tr><td style='width:20%; border-bottom:solid thin;'><input type='checkbox' name='list[]' value='".$value->get_interface_id()."//".$value->get_device_id()."'>".$value->get_name()."</input></td>
				<td style='border-bottom:solid thin;'>".$value->get_descr()." | ". $value->get_alias()."</td>
				<td style='border-bottom:solid thin;'>".$deviceName."</td>
				<td style='border-bottom:solid thin; width:35%'>
				<input type='button' onclick=\"handleEvent('".$link."traffic')\" value='Traffic' />
				<input type='button' onclick=\"handleEvent('".$link."errors')\" value='Error' />
				<input type='button' onclick=\"handleEvent('".$link."unicastpkts')\" value='Unicast' />
				<input type='button' onclick=\"handleEvent('".$link."nonunicastpkts')\" value='Nonunicast' />
				<input type='button' onclick=\"handleEvent('".$all."')\" value='All' />
				</td></tr>";
			}
		}
	}
	else {$deviceForm->warning("No interfaces for this device");}
		
	echo "</table>
	<input type='submit' name='returnResults' value='Display Checked Graphs' style='float:right; margin-bottom:5px;'></input>
	</form>
	</div>";
}

function displayGraphDetail($devices)
{
	//global the tool and make a tool bar for adding a client
	global $tool, $deviceForm;
	
	$now = time();
	$day = time() - (24 * 60 * 60);
	$twoday = time() - (2 * 24 * 60 * 60);
	$week = time() - (7 * 24 * 60 * 60);
	$month = time() - (31 * 24 * 60 * 60);
	$year = time() - (365 * 24 * 60 * 60);
	
	$interfaces = $devices->get_interfaces();
	$interfaceID = $_GET['interID'];
	$port = new Port($interfaceID);
		
	echo "<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:1024px;'>";
	#"<select name='interfaces' onchange=\"return LoadPage('statistics.php?action=showCurGraph&mode=graphTime'+this.value, 'statGraphs')\">";

	$deviceID = $devices->get_device_id();
	$oriName = $port->get_name();
	$nameTitle = str_replace(" ", "%20", $oriName);
	$name = str_replace(" ", "-", $oriName);
	$name = str_replace("/", "-", $name);
	$type = $_GET['type'];
	$graph = $type;
	switch ($type)
	{
		case "traffic":
		$graph = 'Bits Per Second'; break;
				
		case "errors":
		$graph = 'Errors'; break;
				
		case "unicastpkts":
		$graph = 'Unicast Packets'; break;
				
		case "nonunicastpkts":
		$graph = 'Non Unicast Packets'; break;
	}
	$type = str_replace(" ", "%20", $type);
	$graph = str_replace(" ", "%20", $graph);
			
	$graphLink="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=150&width=900&type=".$type;
			
	$dayLink= $graphLink."&from=".$day."&to=-1s";
	$weekLink= $graphLink."&from=".$week."&to=-1s";
	$monthLink= $graphLink."&from=".$month."&to=-1s";
	$yearLink= $graphLink."&from=".$year."&to=-1s";
			
	$detail = "statistics.php?action=zoomGraphDetail&ID=".$deviceID."&interID=".$port->get_interface_id()."&active=".$port->get_oper_status()."&type=".$type;
	echo "<thead><tr><th colspan='5'>".$oriName." | ".$devices->get_name()." | ".$port->get_descr()." - ".$port->get_alias()."</th></tr></thead>
		<tbody>
		<tr>
		<tr class='form'><td class='info' style='text-align:center;'><h3>Last Day</h3></td></tr>
		<tr>
		<td><a href='#' onclick=\"handleEvent('".$detail."&from=day&to=now')\"><img src=".$dayLink."></a></td>
		</tr>
		<tr>
		<tr class='form'><td class='info' style='text-align:center;'><h3>Last Week</h3></td></tr>
		<tr>
		<td><a href='#' onclick=\"handleEvent('".$detail."&from=week&to=now')\"><img src=".$weekLink."></a></td>
		</tr>
		<tr>
		<tr class='form'><td class='info' style='text-align:center;'><h3>Last Month</h3></td></tr>
		<tr>
		<td><a href='#' onclick=\"handleEvent('".$detail."&from=month&to=now')\"><img src=".$monthLink."></a></td>
		</tr>
		<tr>
		<tr class='form'><td class='info' style='text-align:center;'><h3>Last Year</h3></td></tr>
		<tr><td><a href='#' onclick=\"handleEvent('".$detail."&from=year&to=now')\"><img src=".$yearLink."></a></td></tr>
		</tbody>";
	echo "</table>";
}

function zoomGraph($devices)
{
	//some time stuff
	$now = time();
	$day = time() - (24 * 60 * 60);
	$twoday = time() - (2 * 24 * 60 * 60);
	$week = time() - (7 * 24 * 60 * 60);
	$month = time() - (31 * 24 * 60 * 60);
	$year = time() - (365 * 24 * 60 * 60);

	$file = "deviceid".$deviceID."_".$name;
	switch ($_GET['from'])
	{
		case 'day':
		$from = $day;
		break;
				
		case 'week':
		$from = $week;
		break;
				
		case 'month':
		$from = $month;
		break;
				
		case 'year':
		$from = $year;
		break;
	}
			
	echo "<table id='dataTable' style='width:1024px;'>";
	
	if (isset($_GET['aggr_id']))
	{
		$type = $_GET['type'];
		$type = str_replace(" ", "%20", $type);
		$oriName = $_GET['aggr_id'];
		$name = str_replace(" ", "%20", $oriName);
		$graphLink="rrdgraph.php?type=aggr_traf&aggr_id=".$name."&from=".$from."&to=".$now."&title=".$name."&height=150&width=900";
				
		echo "<tr><th>".$oriName."<br></b></p></font></th></tr>";
		echo "<tr><td><img id='zoom' src='".$graphLink."'>
				<form method='GET' action='' enctype='multipart/form-data' id='range_form'>";
		echo '<input type="hidden" name="epoch_start" value="'.$from.'" id="epoch_start" />
				<input type="hidden" name="rrdfile" value="'.$name.'" id="rrdfile" />
				<input type="hidden" name="type" value="'.$type.'" id="type" />
				<input type="hidden" name="epoch_end" value="'.$now.'" id="epoch_end" />
				<input type="hidden" name="width" value="900" id="width" />
				<input type="hidden" name="height" value="150" id="height" />
				</form>
				</td>
				</tr>';
	}
	else
	{
		$deviceID = $_GET['ID'];
		$interfaces = $devices->get_interfaces();
		$interfaceID = $_GET['interID'];
		$port = new Port($interfaceID);

		$deviceID = $devices->get_device_id();
		$oriName = $port->get_name();
		$nameTitle = str_replace(" ", "%20", $oriName);
		$name = str_replace(" ", "-", $oriName);
		$name = str_replace("/", "-", $name);
		$type = $_GET['type'];
		$graph = $type;
		switch ($type)
		{
			case "traffic":
			$graph = 'Bits Per Second'; break;
							
			case "errors":
			$graph = 'Errors'; break;
							
			case "unicastpkts":
			$graph = 'Unicast Packets'; break;
							
			case "nonunicastpkts":
			$graph = 'Non Unicast Packets'; break;
		}
				
		$type = str_replace(" ", "%20", $type);
		$graph = str_replace(" ", "%20", $graph);
		
		$graphLink="rrdgraph.php?file=deviceid".$deviceID."_".$name.".rrd&title=".$nameTitle."---".$graph."&height=150&width=900&type=".$type;
		$file="deviceid".$deviceID."_".$name.".rrd&title=".$oriName;
				
		$graphLink .= "&from=".$from."&to".$now;
				
		echo "<tr><th>".$oriName." | ".$devices->get_name()." | ".$port->get_descr()." - ".$port->get_alias()."<br></b></p></font></th></tr>";
		echo "<tr><td><img id='zoom' src='".$graphLink."'>
				<form method='GET' action='' enctype='multipart/form-data' id='range_form'>";
		echo '<input type="hidden" name="epoch_start" value="'.$from.'" id="epoch_start" />
				<input type="hidden" name="rrdfile" value="'.$file.'" id="rrdfile" />
				<input type="hidden" name="type" value="'.$type.'" id="type" />
				<input type="hidden" name="epoch_end" value="'.$now.'" id="epoch_end" />
				<input type="hidden" name="width" value="900" id="width" />
				<input type="hidden" name="height" value="150" id="height" />
				</form>
				</td>
				</tr>';
	}
	echo "</table>";

}

function aggregatedGraphs($id){
	
	global $aggregrated_graph_traffic;
	global $nested_aggregrated_graph_traffic;
	
	//some time stuff
	$now = time();
	$day = time() - (24 * 60 * 60);
	$twoday = time() - (2 * 24 * 60 * 60);
	$week = time() - (7 * 24 * 60 * 60);
	$month = time() - (31 * 24 * 60 * 60);
	$year = time() - (365 * 24 * 60 * 60);

	$name = $id;
	$name = str_replace(" ", "%20", $name);
	$timeSlot = array($day, $week, $month, $year);
	$timeTitle = array("Last Day", "Last Week", "Last Month", "Last Year");
	$from = array("day", "week", "month", "year");
	
	echo "<table id='dataTable' style='width:1024px;'>
	<tr><th colspan='2'>".$id."</th></tr>";
	
	foreach ($timeSlot as $id => $value)
	{
		$detail = "statistics.php?action=zoomGraphDetail&type=aggr_traf&aggr_id=".$name."&from=".$value."&to=".$now."&height=150&width=900";
		echo "<tr><td>".$timeTitle[$id]."</td><td>
		<a href='#' onclick=\"handleEvent('".$detail."&from=".$from[$id]."&to=now')\">
		<img src='rrdgraph.php?type=aggr_traf&aggr_id=".$name."&from=".$value."&to=".$now."&title=".$name."&height=150&width=900'>
		</a>
		</td></tr>";
	}
	echo "</table>";
}

?>

<script language="javascript">

var actPort='yes';
var physPort='';

function checkCheck(layer, pageNum)
{
	if(layer.checked == 1)
	{
		if (layer.name=='activePort') {actPort='yes';}
		if (layer.name=='physicalPort') {physPort='yes';}
	}
	else if(layer.checked==0)
	{
		if (layer.name=='activePort') {actPort='';}
		if (layer.name=='physicalPort') {physPort='';}
	}
	
	var id = <? echo $_GET['ID']; ?>;
	
	document.filterForm.action = "javascript:LoadPage('statistics.php?action=showGraph&ID="+id+"&active="+actPort+"&physical="+physPort+"&mode=filter&pageNum="+pageNum+"', 'filteredResult');"
	document.filterForm.submit();	
}

function checkAll(field, origValue)
{
	var index;
	if (origValue.checked)
	{
		for (index=0; index<field.length; index++)
		{
			field[index].checked = true;
		}
	}
	else {
		for (index=0; index<field.length; index++)
		{
			field[index].checked = false;
		}
	}

}

</script>

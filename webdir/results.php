<?php 
include_once("sessionCheck.php");
if(!isset($_GET['mode']))
{include("controlBar.php");}
?>

<?
if(!isset($_GET['mode']))
{
?>
<div id="main">
<h1 id="mainTitle">Results Found</h1>
<style>
.first{
	clear:left;	
}

.resultBox {
	color:#666;
	padding: 10px; 
	margin-bottom: 10px;
	margin-right: 10px;
	float:left;
	width: 40%;
	height: auto;
}

.resultBox h3{
	padding-left:30px;
}

.resultBox .results{
	padding-left:30px;
	height:200px;
	overflow:auto;
}

.resultBox a:visited {
	color:#666;
}
.resultBox a:link {
	color:#666;
}
.resultBox a:hover {
	color:#000;
}

.resultBox a.archived {
	color:#C00;	
}
</style>
<?
}
?>

<script type='text/javascript' src='js/mouseClicks.js'></script>

<?
/*Database coding: this checks for multiple different actions made by users and responds accordingly.*/
include_once "classes/Search.php";
include_once "classes/Form.php";

$form = new Form('auto', 'auto');
$keyword = $_GET['keyword'];
if($keyword=='')
{echo $form->warning("Did not complete search: Please make sure your search keyword isn't empty");}
else {$form->success("Search completed for \"".$keyword."\"");}


$search = new Search();
$search->set_keyword($keyword);
$search->search_database();


echo "<p style='float:left; color:#666;'>Did you not find your result? Try searching in our archived section... <input type='checkbox' id='showArchived' value=''>Show archived items</input></p>";
echo "<div class='resultBox first'>";
echo "<h3 icon='contact'>Contacts<hr></h3>";
echo "<div class='results'>";
$contactResult = $search->get_contact_results();
if(empty($contactResult))
{
	echo "No results found...";
}
else
{
	foreach ($contactResult as $id => $value)
	{
		$notes ='';
		if(isset($value['group_id']))
		{
			foreach ($value as $vID => $vValue)
			{
				if (preg_match("/".$keyword."/", $vValue) && !is_numeric($vID))
				{
					$notes = $vID.": ".$vValue;
					break;
				}
			}
			
			if ($notes=='')
			{$notes = "No description for ".$value['group_name']." yet";}
			
			if($value['archived'] != 1)
			{
				echo "<a class='tooltip' title='".$notes."' href='contacts.php?action=showGroup&groupID=".$value['group_id']."'>".$value['group_name']."<br/></a>";	
			}
			else
			{
				echo "<a class='tooltip archived' title='".$notes."' href='contacts.php?action=showGroup&groupID=".$value['group_id']."'>".$value['group_name']."<br/></a>";	
			}
		}
		else if(isset($value['contact_id']))
		{
			foreach ($value as $vID => $vValue)
			{
				if (preg_match("/".$keyword."/", $vValue) && !is_numeric($vID))
				{
					$notes = $vID.": ".$vValue;
					break;
				}
			}
			
			if ($notes=='')
			{$notes = "No description for ".$value['name_first']." ".$value['name_middle']." ".$value['name_last']." yet";}
			
			if($value['archived'] != 1)
			{
				echo "<a class='tooltip' title='".$notes."' href='contacts.php?action=showPerson&contactID=".$value['contact_id']."'>".$value['name_first']." ".$value['name_middle']." ".$value['name_last']."<br/></a>";	
			}
			else
			{
				echo "<a class='tooltip archived' title='".$notes."' href='contacts.php?action=showPerson&contactID=".$value['contact_id']."'>".$value['name_first']." ".$value['name_middle']." ".$value['name_last']."<br/></a>";	
			}
		}
	}
}
echo "</div>";
echo "</div>";

echo "<div class='resultBox'>";
echo "<h3 icon='device'>Devices<hr></h3>";
$deviceResult = $search->get_device_results();
$interfaceResult = $search->get_interface_results();
echo "<div class='results'>";
if(empty($deviceResult) && empty($interfaceResult))
{
	echo "No results found...";
}
else
{
	foreach ($deviceResult as $id => $value)
	{
		$notes ='';
		foreach ($value as $vID => $vValue)
		{
			if (strpos($vValue, $keyword) !== false && !is_numeric($vID))
			{
				$notes = $vID.": ".$vValue;
				break;
			}
		}
		if ($notes=='')
		{$notes = "No description for ".$value['name']." yet";}
		if($value['archived'] != 1)
		{
			echo "<a class='tooltip' title='".$notes."' href='devices.php?action=showID&ID=".$value['device_id']."'>".$value['name']."<br/></a>";
		}
		else
		{
			echo "<a class='tooltip archived' title='".$notes."' href='devices.php?action=showID&ID=".$value['device_id']."'>".$value['name']."<br/></a>";
		}
	}
	echo "<h3 icon='device'>Interfaces</h3><hr>";
	foreach ($interfaceResult as $id => $value)
	{
		$notes = '';
		$notes = $value[interface_alias];
		/*
		foreach ($value as $vID => $vValue)
		{
			if (strpos($vValue, $keyword) !== false && !is_numeric($vID))
			{
				$notes = $vID.": ".$vValue;
				break;
			}
		}
		*/
		if ($notes=='')
		{$notes = "No description for ".$value['name']." yet";}
		if($value['archived'] != 1)
		{
			echo "<a class='tooltip' title='".$notes."' href='devices.php?action=showID&ID=".$value['device_id']."&tab=1'>".$value['interface_name']." from ".$value['name']."<br/></a>";
		}
		else
		{
			echo "<a class='tooltip archived' title='".$notes."' href='devices.php?action=showID&ID=".$value['device_id']."&tab=1'>".$value['interface_name']." from ".$value['name']."<br/></a>";
		}
	}
}
echo "</div>";
echo "</div>";

echo "<div class='resultBox'>";
echo "<h3 icon='service'>Services<hr></h3>";
$serviceResult = $search->get_service_results();
echo "<div class='results'>";
if(empty($serviceResult))
{
	echo "No results found...";
}
else
{
	//$allNames = array();
	foreach ($serviceResult as $id => $value)
	{
		$notes = '';
		$notes = $value[notes];
		/*
		foreach ($value as $vID => $vValue)
		{
			if (strpos($vValue, $keyword) !== false && !is_numeric($vID))
			{
				$notes = $vID.": ".$vValue;
				print "Notes is $notes<br>";
				break;
			}
		}
		*/
		if ($notes=='')
		{$notes = "No description for ".$value['name']." yet";}
		
		if($value['archived'] != 1)
		{
			//array_push($allNames, $value['name']);
			echo "<a class='tooltip' title='".$notes."' href='services.php?action=showID&ID=".$value['service_id']."'>".$value['name']."<br/></a>";	
		}
		else if ($value['archived'] == 1)
		{
			echo "<a class='tooltip archived' title='".$notes."' href='services.php?action=showID&ID=".$value['service_id']."'>".$value['name']."<br/></a>";	
		}
	}
}
echo "</div>";
echo "</div>";

echo "<div class='resultBox'>";
echo "<h3 icon='statistic'>Statistics<hr></h3>";
$interfaceResult = $search->get_interface_results();
echo "<div class='results'>";
if(empty($interfaceResult))
{
	echo "No results found...";
}
else
{
	foreach ($interfaceResult as $id => $value)
	{
		$portName = str_replace("/", "-", $value['interface_name']);
		$portName = str_replace(" ", "-", $portName);
		$link='rrdgraph.php?file=deviceid'.$value['device_id']."_".$portName.".rrd&title=".$value['device_name']."%20--%20".$portName;
	
		if($value['archived'] != 1)
		{
			echo "<a href='statistics.php?action=showGraphDetail&ID=".$value['device_id']."&interID=".$value['interface_id']."&active=up&type=traffic' class='screenshot' title='".$value['interface_descr']." - ".$value['interface_alias']."' style='padding-left:30px;' icon='graphPrev' rel='".$link."'>".$value['interface_name']." - from ".$value['name']."<br/></a>";
		}
		else
		{
			echo "<a href='statistics.php?action=showGraphDetail&ID=".$value['device_id']."&interID=".$value['interface_id']."&active=up&type=traffic' class='screenshot archived' title='".$value['interface_descr']." - ".$value['interface_alias']."' icon='graphPrev' style='padding-left:30px; width:100%' rel='".$link."'>".$value['interface_name']." - from ".$value['name']."<br/></a>";
		}
	}
}
echo "</div>";
echo "</div>";

echo "<div class='resultBox'>";
echo "<h3 icon='location'>Locations<hr></h3>";
$locationResult = $search->get_location_results();
echo "<div class='results'>";
if(empty($locationResult))
{
	echo "No results found...";
}
else
{
	foreach ($locationResult as $id => $value)
	{
		$notes ='';
		//echo "<pre>";print_r($value);echo "</pre>";
		if(isset($value['location_id']) && !isset($value['room_type']))
		{
			/*
			foreach ($value as $vID => $vValue)
			{
				if (strpos($vValue, $keyword) !== false && !is_numeric($vID))
				{
					$notes = $vID.": ".$vValue;
					break;
				}
			}
			*/
			$notes = $value[location_desc];
				
			if ($notes=='')
			{$notes = "No description for ".$value['location_name']." yet";}
				
			if($value['archived'] != 1)
			{
				echo "<a class='tooltip' title='".$notes."' href='location.php?action=showLocation&locationID=".$value['location_id']."'>".$value['location_name']."<br/></a>";	
			}
			else
			{
				echo "<a class='tooltip archived' title='".$notes."' href='location.php?action=showLocation&locationID=".$value['location_id']."'>".$value['location_name']."<br/></a>";	
			}
		}
		else
		{
			if(isset($value['room_id']))
			{
				/*
				foreach ($value as $vID => $vValue)
				{
					if (strpos($vValue, $keyword) !== false && !is_numeric($vID))
					{
						$notes = $vID.": ".$vValue;
						break;
					}
				}
				*/
				$notes = $value[room_desc];
			
					
				if ($notes=='')
				{$notes = "No description for ".$value['location_name']." yet";}
					
				if($value['archived'] != 1)
				{
					echo "<a class='tooltip' title='".$notes."' href='location.php?action=showRooms&roomID=".$value['room_id']."&locationID=".$value['location_id']."&roomTypeID=".$value['room_type']."'>".$value['room_name']."<br/></a>";	
				}
				else
				{
					echo "<a class='tooltip archived' title='".$notes."' href='location.php?action=showRooms&roomID=".$value['room_id']."&locationID=".$value['location_id']."&roomTypeID=".$value['room_type']."'>".$value['room_name']."<br/></a>";	
				}
			}
		}
	}
}
echo "</div>";
echo "</div>";
?>

</div>        
<?php 
if(!isset($_GET['mode']))
{include("footer.php");} ?>


<script language="javascript">
$(function() {
		   $('.archived').hide();
		   $('#showArchived').click(function(){
					$(".archived").toggle(400);
									})
		   
		   });
</script>

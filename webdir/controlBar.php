<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<link rel="stylesheet" type="text/css" href="bcnet.css" title="style" />

<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Network Database</title>
<script type='text/javascript' src='js/jquery-ui/js/jquery-1.5.1.min.js'></script>
<script type='text/javascript' src='js/jquery-ui/js/jquery-ui-1.8.11.custom.min.js'></script>
<script type='text/javascript' src='js/mouseClicks.js'></script>

<script type='text/javascript' src='js/Ajax.js'></script>
<script type='text/javascript'>
function checkAll(checkname, exby) {
       for (i = 0; i < checkname.length; i++)
	checkname[i].checked = exby.checked? true:false
} 
</script>
<?
include_once "classes/Plugins.php";

$allPlugins = Plugins::get_plugins();
echo "<style>";
foreach ($allPlugins as $id => $value)
{
	$curPlugin = new Plugins($id);
	$iconPath = $curPlugin->get_icon_path();
	$iconPath = str_replace(" ", "%20", $iconPath);
	
	echo "[icon^=\"plugIcon".$id."\"]{
	background:transparent url(".$iconPath.") no-repeat center left;
	}";
}
echo "</style>";
?>
</head>

<body>
<div id="container">

<div id="banner">
	<ul id="status">
    <?
		if ($access==100)
		{echo "<li><a href='configurations.php'><u>Configurations</u></a></li>";}
	?>
    <li><u>Help</u></li>
    <li><u>FAQs</u></li>
    <li><a href='login.php?action=logout'><u>logout</u></a></li>
    
<? 
	$name = $_SESSION['fullname'];
    echo "<li><a href='userSettings.php'>Welcome <b><u>".$name."</u></b></a></li>";
	
?>

</ul>
</div>

<div id="menu">
<div id="searchBox">
	<form method='get' action='results.php'>
  <input name='keyword' id="searchText" type="text"/>
  <input name='search' id="searchButton" type="submit" value="Search" icon="search"/>
  </form>
</div>
<?
$allTabs = array("index.php"=>"Home", 
				 "services.php"=>"Services", 
				 "devices.php"=>"Devices",
				 "location.php"=>"Locations",
				 "contacts.php"=>"Contacts", 
				 "statistics.php"=>"Statistics", 
				 "monitor.php"=>"Events", 
				 "plugins.php"=>"Plugins"
				 );

//After knowing their active status, put them on
echo "<div id='controlBar'>";

foreach ($allTabs as $link => $tName)
{
	echo "<ul>";
	echo "<a href='".$link."'>";
	if(preg_match("/".$link."/", $_SERVER['REQUEST_URI']))
	{echo "<li id='active'>";}
	else
	{echo "<li>";}
	
	echo "<font class='topMenu' icon='".strtolower($tName)."'>".$tName."</font>";
	
	$name = checkPlugin(strtolower($tName));
	if (!empty($name))
	{
		
		echo "<ul>";
		foreach ($name as $id => $value)
		{
			$curPlugin = new Plugins($id);
			$className = $curPlugin->get_class_name();
			echo "<a href='pluginTemplate.php?tab=".$link."&pluginID=".$id."&className=".$className."'><li class='topMenuItem'><font class='subTopMenu' icon='plugIcon".$id."'>".$value."</font></li></a>";
		}
		echo "</ul>";
	}
			  
				  
	echo "</li></a>
			  </ul>";
}

echo "</div>";

?>

</div>


<?

function checkPlugin($location)
{
	$allPlugins = Plugins::get_plugins();
	
	$location = strtolower($location);
	$pluginNames = array();
	$pluginHeadings = array("All Plugins");
	
	foreach ($allPlugins as $id => $value)
	{
		$curPlugin = new Plugins($id);
		$plugLocation = strtolower($curPlugin->get_location());
		if($plugLocation == $location && $curPlugin->get_enabled() == true)
		{
			$pluginNames[$id] = $curPlugin->get_name();
		}
	}
	
	return $pluginNames;
}
?>

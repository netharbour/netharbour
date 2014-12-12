<?php 
include_once("sessionCheck.php");
include_once("controlBar.php")?>

<style>
#pluginHolder
{
	float:left; 
	width:170px; 
	border-right:solid thin #666;
	margin-bottom: 10px;
}

#pluginViewer
{
	float:left;
	padding-left:20px;
	width:70%;
}

.plugIcon
{
	width:24px;
	height:24px;
	margin-right: 10px;
}
</style>
        
        
        <div id="main">
        
            <h1 id="mainTitle">PLUGINS</h1>
            
<?
include_once "classes/Plugins.php";
include_once "classes/PluginForm.php";

$pluginForm = new PluginForm(1, "auto");

echo "<div id='pluginHolder'>";
echo displayPlugins("default");
echo "</div>";

echo "<div id='pluginViewer'>";
if($_GET['pluginaction'] == 'showPlugin')
{
	echo displayPluginContent();
}
else
{
	echo "No plugins selected";
}

echo "</div>";
		
?>

<?php include("footer.php") ?>

<?

function displayPlugins($location)
{
	global $pluginForm;
	$allPlugins = Plugins::get_plugins();
	
	$pluginNames = array();
	$pluginHandler = array();
	$pluginHeadings = array("All Plugins");
	foreach ($allPlugins as $id => $value)
	{
		$curPlugin = new Plugins($id);
		
		if($curPlugin->get_location() == $location && $curPlugin->get_enabled() == true)
		{
			if ($curPlugin->get_icon_path() != "NONE")
			{
				$iconPath = str_replace(" ", "%20", $curPlugin->get_icon_path());
				array_push($pluginNames, "<img class='plugIcon' src='".$iconPath."'></img>".$curPlugin->get_name());
			}
			else
			{
				array_push($pluginNames, $curPlugin->get_name());
			}
			array_push($pluginHandler, "handleEvent('plugins.php?pluginaction=showPlugin&pluginID=".$id."&className=".$curPlugin->get_class_name()."')");
		}
	}
	
	$pluginForm->setHeadings($pluginHeadings);
	$pluginForm->setTableWidth("150px");
	$pluginForm->setEventHandler($pluginHandler);
	$pluginForm->setTitles($pluginNames);
	$pluginForm->setSortable(true);
	return $pluginForm->showAll();
}

function displayPluginContent()
{
	global $pluginForm;
	
	$curPlugin = new Plugins($_GET['pluginID']);
	
	if (file_exists($curPlugin->get_filename()))
	{
		include_once $curPlugin->get_filename();
	
		$className = $curPlugin->get_class_name();
		$pluginClass = new $className();
		
		return $pluginClass->get_content();
	}
	
	else {return "The file does not exist.";}
	
	/*$phpFile = $curPlugin->get_filename();
	$fh = fopen($phpFile, 'r');
	$data = fread($fh, filesize($phpFile));
	fclose($fh);
	
	if (!preg_match("/echo/i", $data) && !preg_match("/print/i", $data) )
	{
		if (class_exists($className))
		{
			$pluginClass = new $className();
		}
		else {return "Your class, \"".$className."\", taken from the database does not exist in your php file.";}
		
		if(method_exists($pluginClass, 'get_content')) {
		
			if($pluginClass->get_content() !='')
			{
				return $pluginClass->get_content();
			}
		}
		else{return "No Content to retrieve";}
	}
	else {return "You cannot echo or print your content out, you must return a value";}*/
}

?>

<?php 
include_once("sessionCheck.php");
include_once("controlBar.php")?>
<div id="main">
<?

if(preg_match("/index.php/", $_SERVER['REQUEST_URI']))
{$title ="HOME";}
else if(preg_match("/services.php/", $_SERVER['REQUEST_URI']))
{$title ="SERVICES";}
else if(preg_match("/devices.php/", $_SERVER['REQUEST_URI']))
{$title ="DEVICES";}
else if(preg_match("/clients.php/", $_SERVER['REQUEST_URI']))
{$title ="CLIENTS";}
else if(preg_match("/statistics.php/", $_SERVER['REQUEST_URI']))
{$title ="STATISTICS";}

echo "<h1 id='mainTitle'>".$title."</h1>";      
?>
<?
include_once "classes/Plugins.php";

echo "<div>";
echo displayPluginContent();
echo "</div>";

?>

<?php include("footer.php") ?>

<?


function displayPluginContent()
{
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

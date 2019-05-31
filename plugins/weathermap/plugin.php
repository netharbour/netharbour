<?php

include_once 'plugins/weathermap/view.php';
include_once 'plugins/weathermap/model.php';

class WeatherMap
{
	// only run when plugin is first enabled
	public function on_enable()
	{
		$model = new plugin_weathermap\Model();
		$view  = new plugin_weathermap\View();

		if (!$model->createWeathermapConfiguration()) {
			$view->errorMessage = "Oops, something went wrong with creating plugin_BHMon_devices DB table.";
			return $view->render('error.php');
		}
	}

	// renders the content
	function get_content()
	{
		return $this->renderWeathermap();
	}

	// renders the plugin configuration
	public function get_config($id='')
	{
		// TODO: have a button to dynamically add another field. May involve javascript?? or not?? refreshing box?? I dunno? (somewhere in this function anyway)
		// instantiate objects
		$view  = new plugin_weathermap\View();
		$model = new plugin_weathermap\Model();

		// initialize variables
		$tableData      = array();
		$header         = array(
			"Configuration File"
		);

		$form = $view->tableCreate("auto", 1, true, $header, "500px");

		$view->header = "Custom Weathermap Configuration Files";
		$view->value  = "Weathermap";
		$view->id     = $id;

		// get all configurations, return failure text on sql failure
		$configurations = $model->selectAllWeathermapConfiguration();
		if (!$configurations) {
			$view->errorMessage = "Unable to read plugin_Weathermap_configuration table. Try enabling the plugin first!";
			return $view->render('error.php');
		};

		// push right into fillable table rows
		while ($row = $model->fetchObject($configurations)) {
			array_push($tableData, $view->tableFillableForm("configuration[$row->id]", $row->configuration_file));
		}

		$view->tableSet($form, $tableData);
		$view->netharbourTable = $view->tableHTML($form);
		$view->buttonName      = "plugin_update";
		$view->buttonValue     = "Update configuration";

		return $view->render('deviceConfig.php');
	}

	// pull from config box and pushes to database, returns true or false
	public function update_config($id='')
	{
		$model = new plugin_weathermap\Model();

		$result = $model->deleteWeathermapConfiguration();

		if (!$result) {
			return false;
		}

		// TODO: get current fields in config box, then push to database

		return true;
	}

	// render UI functions

	// generate the weathermap page
	private function renderWeathermap()
	{
		$view = new plugin_weathermap\View();

		// TODO: refactor these guts...they need to dynamically set HTML files and stuff
		// Include the map config file
		// This files contains all the available weathermaps;
		include_once("plugins/weathermap/map_files.php");

		// Now build URL
		$url = $_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID'];
		$map_file = $_GET['map_file'];
		if (is_null($map_file)) {
			// if no map file is defined in the url, then use 1st one
			foreach ($map_files as $map_name => $map_file) {
				if( ($map_name != '') && (is_readable("plugins/weathermap/$map_file"))) {
					break;
				}
			}
		}
		$content = '
		<script type="text/javascript" src="plugins/weathermap/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
		<script language="javascript">
			function refreshDiv() {
				$("#refresher").load("plugins/weathermap/'.$map_file.'");
			}

			$(document).ready(function(){
					// Run our swapImages() function every 5secs
					setInterval(\'refreshDiv()\', 5000);
				});
		</script>';
		$content .= '<div style=" color: black;
			width: 200px; padding: 1px; padding-right:
			1px;  position: relative; float: left;
			margin-right: 5px;
			clear: both;
		" >';

		$content .= "<h3>Available Maps:</h3><ul>";
		foreach ($map_files as $map_name => $map_file_href) {
			if( ($map_name != '') && (is_readable("plugins/weathermap/$map_file_href"))) {
				$content .= "<li><a href='".$url."&map_file=$map_file_href'>$map_name</a></li>";
			}
		}
		$content .= "</ul></div>";

		$content .= "<div id='refresher'>" .
			file_get_contents("plugins/weathermap/$map_file") .
			"</div><br> <p></p><br> <p></p><br> <p></p><br> <p></p><br> <p></p><br> <p></p>
		<br> <p></p><br> <p></p><br> <p></p><br> <p></p>";

		return $content;

		return $view->render('pluginDisplay.php');
	}

}


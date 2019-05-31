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
		// instantiate objects
		$view  = new plugin_weathermap\View();
		$model = new plugin_weathermap\Model();

		// initialize variables
		$devices        = array();
		$tableData      = array();
		$header         = array(
			"",
			"Configuration File"
		);

		$form = $view->tableCreate("auto", 5, true, $header, "auto");

		$view->header = "Select devices to monitor blackholed routes";
		$view->value  = "BHMon";
		$view->id     = $id;

		// get all Blackhole devices, return failure text on sql failure
		$devicesToPoll = $model->selectAllBHMonDevices();
		if (!$devicesToPoll) {
			$view->errorMessage = "Unable to read plugin_BHMon_devices table. Try enabling the plugin first!";
			return $view->render('error.php');
		};

		// populate devices array, insert comma between elements only if more than 1 exist. e.g. aaa,bbb,ccc
		while ($row = $model->fetchObject($devicesToPoll)) {

			if ($devices[$row->device_id]) {
				$devices[$row->device_id] .= ',' . $row->logical_system;
			} else {
				$devices[$row->device_id] .= $row->logical_system;
			}

		}

		// get all devices, compare with enabled devices for polling. If a match, check the box.
		foreach ($model->getAllDevices() as $id => $name) {

			$deviceObject = $model->deviceObject($id);
			if ((array_key_exists($id, $devices))) {
				$deviceChecked = "checked='yes'";
				$logicalSystems = $devices[$id];
			} else {
				$deviceChecked  = "";
				$logicalSystems = "";
			}

			// push table row elements into array for devices
			array_push($tableData, $view->tableCheckBox("devices[]", $id, $deviceChecked));
			array_push($tableData, $name);
			array_push($tableData, $deviceObject->get_type_name());
			array_push($tableData, $deviceObject->get_location_name());
			array_push($tableData, $view->tableFillableForm("logical_system[$id]", $logicalSystems));

		}

		$view->tableSet($form, $tableData);
		$view->netharbourTable = $view->tableHTML($form);
		$view->buttonName      = "plugin_update";
		$view->buttonValue     = "Update configuration";

		return $view->render('deviceConfig.php');
	}

	// render UI functions

	// generate the weathermap page
	private function renderWeathermap()
	{
		$view = new plugin_weathermap\View();

		// TODO: refactor these guts...
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

//////////////////////////////////////////////////////////////



	// updates the configuration from the config box, returns true or false
	public function update_config($id='')
	{
		$model = new plugin_BHMon\Model();

		$result = $model->deleteBHMonDevices();

		if (!$result) {
			return false;
		}

		# pre-process input fields
		$lsFields = array();

		foreach ($_POST['logical_system'] as $lsKey => $lsValue) {
			if (trim($lsValue) === '') {
				// pass
				continue;
			} else {
				$lsFields[$lsKey] = trim($lsValue);
			}
		}

		// add all selected devices to DB with logical systems (each row is a [device_id] => [logical_system] key/value pair)
		foreach ($_POST['devices'] as $key => $deviceID) {

			$ls_array = explode(',', $lsFields[$deviceID]);
			foreach ($ls_array as $ls) {
				$result = $model->insertBHMonDevice($deviceID, $ls);
			}

			if (!$result) {
				return false;
			}
		}

		return true;
	}

	### UI render functions

	private function renderBlackholeRoutes()
	{
		// instantiate objects
		$view  = new plugin_BHMon\View();
		$model = new plugin_BHMon\Model();

		// instantiate variables
		$tableData = array();
		$handler   = array();
		$header    = array(
			"Route",
			"Protocol",
			"Peer AS",
			"Peer ID",
			"Route Age",
			"Time Since Seen",
			"Origin Device",
			"Logical System"
		);

		$form = $view->tableCreate("auto", 8, true, $header, "1024px");

		$result = $model->selectBHMonInfo();
		if (!$result) {
			$view->errorMessage = "Oops, something went wrong with getting the blackholed routes";
			return $view->render('error.php');
		}

		$view->header = "Blackholed Routes";

		// add table data
		while ($object = $model->fetchObject($result)) {
			array_push($tableData,
				$object->route_dest . "/" . $object->prefix_len,
				$object->route_protocol,
				$object->peer_as,
				$object->peer_id,
				$this->secondsToTime($object->route_age),
				$this->secondsToTime(($object->last_seen - $object->first_seen)),
				$object->device_fqdn,
				$object->logical_system
			);
		}

		$view->tableSet($form, $tableData);
		$view->netharbourTable = $view->tableHTML($form);

		return $view->render('pluginDisplay.php');
	}

	private function secondsToTime($inputSeconds)
	{
		$secondsInAMinute = 60;
		$secondsInAnHour = 60 * $secondsInAMinute;
		$secondsInADay = 24 * $secondsInAnHour;

		// Extract days
		$days = floor($inputSeconds / $secondsInADay);

		// Extract hours
		$hourSeconds = $inputSeconds % $secondsInADay;
		$hours = floor($hourSeconds / $secondsInAnHour);

		// Extract minutes
		$minuteSeconds = $hourSeconds % $secondsInAnHour;
		$minutes = floor($minuteSeconds / $secondsInAMinute);

		// Extract the remaining seconds
		$remainingSeconds = $minuteSeconds % $secondsInAMinute;
		$seconds = ceil($remainingSeconds);

		// Format and return
		$timeParts = array();
		$sections = array(
			'd' => (int)$days,
			'h' => (int)$hours,
			'm' => (int)$minutes,
			's' => (int)$seconds,
		);

		foreach ($sections as $name => $value){
			if ($value > 0){
				$timeParts[] = $value.$name;
			}
		}

		return implode(', ', $timeParts);
	}
}



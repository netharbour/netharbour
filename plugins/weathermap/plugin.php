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
			$view->errorMessage = "Oops, something went wrong with creating plugin_Weathermap_configuration DB table.";
			return $view->render('error.php');
		}
		
		$model->insertNullWeathermapConfiguration();
	}

	// renders the content
	function get_content()
	{
        $url = $_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."&className=".$_GET['className'];
        
        if ($_GET['map']) {
            return $this->clickedWeathermap($url, $_GET['map']);
        } else {
            return $this->renderWeathermap($url);
        }
    }

	// renders the plugin configuration
	public function get_config($id='')
	{
		// instantiate objects
		$view  = new plugin_weathermap\View();
		$model = new plugin_weathermap\Model();

		// initialize variables
		$tableData = array();
		$header    = array(
			"Configuration File (required)",
            "Custom Image Name (optional)"
		);

		$form = $view->tableCreate("auto", 2, true, $header, "500px");

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
			array_push($tableData, 
                $view->tableFillableForm("configuration[$row->id]", $row->configuration_file),
                $view->tableFillableForm("image_name[$row->id]", $row->custom_image_name)
            );
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
		
		// initialize variables
        $config_files = array();
        $image_names  = array();

		// delete config
		$result = $model->deleteWeathermapConfiguration();

        if (!$result) {
            return false;
        }
        
        // populate array's from POST
        foreach ($_POST['configuration'] as $configName) {
            array_push($config_files, $configName);
        }
        foreach ($_POST['image_name'] as $imageName) {
            array_push($image_names, $imageName);
        }
        
        // cleanup all empty elements using temp arrays
        $tmpArray1 = array();
        $tmpArray2 = array();
        for($i = 0; $i < count($config_files); $i++) {
            if ($config_files[$i] != "") {
                array_push($tmpArray1, $config_files[$i]);
                array_push($tmpArray2, $image_names[$i]);
            }
        }
        $config_files = $tmpArray1;
        $image_names  = $tmpArray2;
        
        // push configuration to DB
        for($i = 0; $i < count($config_files); $i++) {
            $result = $model->insertWeathermapConfiguration($config_files[$i], $image_names[$i]);
        }
        
        // add null row to end
        $model->insertNullWeathermapConfiguration();

		if (!$result) {
			return false;
		}

		return true;
	}

	// render UI functions

	// generate the weathermap page
	private function renderWeathermap($url)
	{
		$view = new plugin_weathermap\View();
        $model = new plugin_weathermap\Model();
        
        //// Sidebar table generation
        
        // initialize variables
        $tableData = array();
        $handler   = array();
        $header    = array(
            "Weathermaps"
        );
        $defaultMap = null;
        
        $form = $view->tableCreate("auto", 1, true, $header, "200px");

        // get all configurations, return failure text on sql failure
        $configurations = $model->selectAllWeathermapConfiguration();
        if (!$configurations) {
            $view->errorMessage = "Unable to read plugin_Weathermap_configuration table. Try enabling the plugin first!";
            return $view->render('error.php');
        };

        // push onto fillable table rows
        while ($row = $model->fetchObject($configurations)) {
            
            // if custom image is specified
            if ($row->custom_image_name) {
                
                // set the first map to be the one displayed
                if (!$defaultMap) {
                    $defaultMap = $row->custom_image_name;
                }

                array_push($tableData, $row->configuration_file);
                $url2 = $url . "&map=$row->custom_image_name";
                array_push($handler, "handleEvent('$url2')");
                
                continue;
            }

            // set the first map to be the one displayed
            if (!$defaultMap) {
                $defaultMap = $row->configuration_file;
            }
            
            // if row is NULL, skip
            if (!$row->configuration_file) {
                continue;
            }
            
            array_push($tableData, $row->configuration_file);
            $url2 = $url . "&map=$row->configuration_file";
            array_push($handler, "handleEvent('$url2')");
        }

        $view->tableHandler($form, $handler);
        $view->tableSet($form, $tableData);
        $view->netharbourTableSidebar = $view->tableHTML($form);
        
        //// Weathermap display
        
        // if no map
        if (!$defaultMap) {
            $view->netharbourImage = "No weathermap conf files are specified in the plugin configuration.";
        } else {
            $view->netharbourImage = $view->tableImage("plugins/weathermap/$defaultMap.png");
        }
        
        // render the page
        return $view->render('pluginDisplay.php');
	}
	
	private function clickedWeathermap($url, $map)
    {
        $view = new plugin_weathermap\View();
        $model = new plugin_weathermap\Model();

        //// Sidebar table generation

        // initialize variables
        $tableData = array();
        $handler   = array();
        $header    = array(
            "Weathermaps"
        );

        $form = $view->tableCreate("auto", 1, true, $header, "200px");

        // get all configurations, return failure text on sql failure
        $configurations = $model->selectAllWeathermapConfiguration();
        if (!$configurations) {
            $view->errorMessage = "Unable to read plugin_Weathermap_configuration table. Try enabling the plugin first!";
            return $view->render('error.php');
        };


        // push onto fillable table rows
        while ($row = $model->fetchObject($configurations)) {

            // if custom image is specified
            if ($row->custom_image_name) {

                array_push($tableData, $row->configuration_file);
                $url2 = $url . "&map=$row->custom_image_name";
                array_push($handler, "handleEvent('$url2')");

                continue;
            }

            // if row is NULL, skip
            if (!$row->configuration_file) {
                continue;
            }

            array_push($tableData, $row->configuration_file);
            $url2 = $url . "&map=$row->configuration_file";
            array_push($handler, "handleEvent('$url2')");
        }

        $view->tableHandler($form, $handler);
        $view->tableSet($form, $tableData);
        $view->netharbourTableSidebar = $view->tableHTML($form);

        //// Weathermap display
        
        // if no map
        if (!$map) {
            $view->netharbourImage = "No weathermap conf files are specified in the plugin configuration.";
        } else {
            $view->netharbourImage = $view->tableImage("plugins/weathermap/$map.png");
        }
        
        // render the page
        return $view->render('pluginDisplay.php');
    }

}


<?php

// We take the config for type from config: RRD, Graphite
// We will have a factory that depending on the type will return the specific class
// We will have a PUT and GET methods taking an array of arguments


class MetricsDB {
	protected $base_url;

	function __construct($base_url='') {
		// Initializing render default values from config file
		$ini_array = parse_ini_file("config/cmdb.conf");
		if ($base_url != '') {
			$this->base_url = $base_url;
		} elseif (array_key_exists("base_metrics_url", $ini_array)){
			$this->base_url = $ini_array['base_metrics_url'];
		} else {
			// Default base url for when using default metrics type RRD
			$this->base_url = "rrdgraph.php";
		}
	}

	function factory($plugin_type=''){
		// find the metric type first

		$ini_array = parse_ini_file("config/cmdb.conf");
		if (array_key_exists("metrics_type", $ini_array)) {
            $cmdb_default_metrics_type = $ini_array['metrics_type'];
        } else {
            $cmdb_default_metrics_type = 'rrd';
        }

        if ($plugin_type == '') {
            // not a plugin, an event
            $metrics_type = $cmdb_default_metrics_type;
		} elseif ($plugin_type == 'graphite'){
			$metrics_type = 'graphite';
		} else {
			# This is done manually
			$metrics_type = $plugin_type;

            # get metric_type from Plugin_plugin where plugin_type = $plugin_type
            
            # if db result:
                #get metrics_type
				#get metrics_base_url
            # else:
				#$metrics_type = $cmdb_default_metrics_type;
        }

		// instantiate the correct class depending on which metric type we got
		if ($metrics_type == 'rrd') {
			return new MetricsRRD($metrics_base_url);
		} elseif ($metrics_type == 'graphite') {
			return new MetricsGraphite($metrics_base_url);
		} else {

			$plugins_metrics_file = "/Users/chradell/github.com/netharbour/plugins/" . $metrics_type . "/plugin.php";
			include_once $plugins_metrics_file;
			$class_name = "Metrics" . $metrics_type;
			return new $class_name($metrics_base_url);

		}

	}
	public function get($args) {
		return "empty";
    }
}

class MetricsRRD extends MetricsDB {
	public function test() {
		print ("MetricsRRD\n");
	}
	
	public function get($args) {
	
		// Parameters:
		// $args is a dictionary with key and value
		// who called this get method?
		
	    return $this->buildLink($args);
    }
    
    private function buildLink($args) {
		$link = $this->base_url . "?";

		foreach($args as $args_key => $args_value){
			if ($args_key == 'file'){
				$args_value = "deviceid" . $args_value['device_id'] . "_" . $args_value['name'] . ".rrd";
			}
			$link .= "$args_key=$args_value&";
		}
		$link .= "legend=0";
		
		// This means that file and tile should be elaborated outside the function
		// "rrdgraph.php?
        // 	file=deviceid".$metrics_parameters['deviceID']."_".$metrics_parameters['name'].".rrd&
        //     title=".$metrics_parameters['nameTitle']."---".$metrics_parameters['graph'].
        //     "&height=".$metrics_parameters['height']."
        //     &width=".$metrics_parameters['width'].
        //     "&type=".$metrics_parameters['type']."
        //     &legend=0";
        
        return $link;
    }
}

class MetricsGraphite extends MetricsDB {
	public function test() {
		print ("MetricsGraphite\n");
	}

}

?>

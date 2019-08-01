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

	function factory($metrics_type=''){
		// find the metric type first
		$ini_array = parse_ini_file("config/cmdb.conf");
		if ($metrics_type != '') {
			; // already defined, let's use it
		} elseif (array_key_exists("metrics_type", $ini_array)){
			$metrics_type = $ini_array['metrics_type'];
		} else {
			$metrics_type = 'rrd';
		}
		// instantiate the correct class depending on which metric type we got
		if ($metrics_type == 'rrd') {
			return new MetricsRRD();
		} elseif ($metrics_type == 'graphite') {
			return new MetricsGraphite();
		} else {
			// if metrics_type is defined but is it not one of the supported ones, // return False
			return False;
		}

	}
}

class MetricsRRD extends MetricsDB {
	public function test() {
		print ("MetricsRRD\n");
	}
}

class MetricsGraphite extends MetricsDB {
	public function test() {
		print ("MetricsGraphite\n");
	}

}

?>
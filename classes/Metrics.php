<?php

// We take the config for type from config: RRD, Graphite
// We will have a factory that depending on the type will return the specific class
// We will have a PUT and GET methods taking an array of arguments


class MetricsDB {
	protected $base_url;

	function __construct($base_url='') {
		// Initializing render default values from config file
		$ini_array = parse_ini_file("/var/www/cmdb/config/cmdb.conf");
		if ($base_url != '') {
			$this->base_url = $base_url;
		} elseif (array_key_exists("base_metrics_url", $ini_array)){
			$base_url = $ini_array['base_metrics_url'];
		} else {
			// Default base url for when using default metrics type RRD
			$base_url = "rrdgraph.php";
		}
	}

	function factory(){
		$ini_array = parse_ini_file("/var/www/cmdb/config/cmdb.conf");
		if (array_key_exists("metrics_type", $ini_array)){
			// pass for now, this will be the case
		} else {
			return new MetricsRRD();
		}		
	}
}

class MetricsRRD extends MetricsDB {

}

?>


function __construct($base_url='') {
		// Initializing render default values from config file
		$ini_array = parse_ini_file("/var/www/cmdb/config/cmdb.conf");
		if ($base_url != '') {
			;
		} elseif (array_key_exists("base_url_statics", $ini_array)){
			$base_url = $ini_array['base_url_statics'];
		} else {
			$base_url = "https://graphite-vip2.sjc.opendns.com/render/";
		}
		parent::__construct($base_url);
	}

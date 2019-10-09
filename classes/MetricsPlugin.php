<?php

include_once "Metrics.php";

class MetricsPlugin extends MetricsDB {
	public function test() {
		print ("MetricsPlugin\n");
	}
    public function get($args){
        return "custom implemetnation";
    }
}

?>

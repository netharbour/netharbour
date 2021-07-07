<?php

include_once 'Metrics.php';

$rrd = MetricsDB::factory(); # default is rrd
$rrd->test();
$args = array(
	#"file" => "deviceid" . "some_device_id" . "_" . "some_name.". "rrd",
	"file" => array(
		"device_id" => "some_ID",
		"name" => "some_name"
	),
	"width" => "100",
);
		// "rrdgraph.php?
        // 	file=deviceid".$metrics_parameters['deviceID']."_".$metrics_parameters['name'].".rrd&
        //     title=".$metrics_parameters['nameTitle']."---".$metrics_parameters['graph'].
        //     "&height=".$metrics_parameters['height']."
        //     &width=".$metrics_parameters['width'].
        //     "&type=".$metrics_parameters['type']."
        //     &legend=0";
$res = $rrd->get($args);
print $res;
print "\n";

print "\n------------------\n";

$rrd = MetricsDB::factory("ChangeManager");
$rrd->test();
$res = $rrd->get($args);
print $res;

print "\n------------------\n";
$graphite = MetricsDB::factory('graphite');
$graphite->test();


?>

<?php

include_once 'Metrics.php';

$rrd = MetricsDB::factory(); # default is rrd
$rrd->test();

$graphite = MetricsDB::factory('graphite');
$graphite->test();


?>
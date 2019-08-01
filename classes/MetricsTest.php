<?php

include_once 'Metrics.php';

$rrd = MetricsDB::factory('rrd');
$rrd->test();

$graphite = MetricsDB::factory('graphite');
$graphite->test();


?>
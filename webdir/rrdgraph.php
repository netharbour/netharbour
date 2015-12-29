<?

include_once('config/opendb.php');
include_once('config/graph.conf');
include_once('classes/RRD.php');
include_once('classes/Property.php');

$property = new Property();
if ($rrdtool = $property->get_property("path_rrdtool")) {
} else {
	print $property->get_error();
	exit;
}
if ($rrd_dir = $property->get_property("path_rrddir")) {
} else {
	print $property->get_error();
	exit;
}

if ((!$rrdtool) || ($rrdtool == '')) {
	print "Could not find rrdtool";
	exit;
}
if ((!$rrd_dir) || ($rrd_dir == '')) {
	print "Could not find rrd_dir";
	exit;
}


// Do not edit below, unless you know what you are doing

// This is a hack, so that sou can access it as a webpage as well
// It will just request the same page, but will present it as html with
// the graph in img tag

if(isset($_GET['ctype'])) {
	$ctype = $_GET['ctype'];
	if ($ctype == 'html') {
	// Now check if loop is set or not, to prevent loops
	// If loop is set it will just continue generating the page
		if(isset($_GET['loop'])) {
			#continue;
		} else {
			print "<img src='" . $_SERVER[REQUEST_URI] . "&loop=1'>";
			exit;
		}
	} else {}
}
if(isset($_GET['width'])) {
	$width = $_GET['width'];
} else {
	$width = "550";
}

if(isset($_GET['height'])) {
	$height=$_GET['height'];
} else {
	$height = "150";
}

if(isset($_GET['type'])) {
	$type=$_GET['type'];
} else {
	$type = "traffic";
}

if(isset($_GET['file'])) {
	$file=$_GET['file'];
}

$title = "$file";
if(isset($_GET['title'])) {
	$title=$_GET['title'];
}

if(isset($_GET['from'])) {
	$from=$_GET['from'];
} else {
	// default to last day
	$from = "-24h";
}
	
if(isset($_GET['to'])) {
	$to=$_GET['to'];
} else {
	// default to now
	$to = "-1s";
}

if(isset($_GET['showtotal'])) {
        $total  =$_GET['showtotal'];
}
if ($total == "0") {
        $total = 0;
} else {
	$total = 1;
}
if(isset($_GET['legend'])) {
        $legend  =$_GET['legend'];
}
if ($legend == "0") {
        $legend = 0;
} else {
	$legend = 1;
}

	
if(isset($_GET['aggr_id'])) {
	$aggr_id=$_GET['aggr_id'];
} else {
	$aggr_id = "";
}

$exclude_ds = array();
if(isset($_GET['exclude_ds'])) {
	// build array of datasources to exclude
	foreach ($_GET['exclude_ds'] as $ds => $v) {
		if ($v == 'yes') {
			$exclude_ds[$ds] = 1;
		}
	}
}

$ds_colors = array();
if(isset($_GET['ds_colors'])) {
	// build array of datasources to and color code
	foreach ($_GET['ds_colors'] as $ds => $v) {
		$ds_colors[$ds] = $v;
	}
}



// Ok now we have all info.
// Lets create the graph we want.
$rrd = new RRD("$rrd_dir/$file",$rrdtool);
if ($rrd->get_error()) {
	//print $rrd->get_error();
	//exit;
	// This will render a nice img 
}
if ($type == "traffic") {
	$graph_params = array(
        	'type' => 'traffic', 
        	'title' => $title,
        	'legend' => $legend,
		'width' => $width,
		'height' => $height,
        	'start' => $from,
        	'end' => $to);
	$graphfile = $rrd->get_graph($graph_params);
} elseif ($type == "errors") {
	$graph_params = array(
        	'type' => 'errors', 
        	'title' => $title,
        	'legend' => $legend,
		'width' => $width,
		'height' => $height,
        	'start' => $from,
        	'end' => $to);
	$graphfile = $rrd->get_graph($graph_params);
} elseif ($type == "unicastpkts") {
	$graph_params = array(
        	'type' => 'unicastpkts', 
        	'title' => $title,
        	'legend' => $legend,
		'width' => $width,
		'height' => $height,
        	'start' => $from,
        	'end' => $to);
	$graphfile = $rrd->get_graph($graph_params);
} elseif ($type == "nonunicastpkts") {
	$graph_params = array(
        	'type' => 'nonunicastpkts', 
        	'title' => $title,
        	'legend' => $legend,
		'width' => $width,
		'height' => $height,
        	'start' => $from,
        	'end' => $to);
	$graphfile = $rrd->get_graph($graph_params);
/*
} elseif ($type == "aggr_traf") {
	// read config
	global $aggregrated_graph_traffic;
	// Create new object, with the array
	$rrd = false;
	$rrd = new RRD($aggregrated_graph_traffic[$aggr_id],$rrdtool);
	$graph_params = array(
        	'type' => 'aggr_traf', 
        	'title' => $title,
        	'legend' => $legend,
		'width' => $width,
		'height' => $height,
        	'start' => $from,
        	'end' => $to);
	$graphfile = $rrd->get_graph($graph_params);
*/
} elseif ($type == "aggr_traf") {
	// read config
	// Create new object, with the array
	$rrd = false;
	$rrdfiles = array();

	$archives = array();
	$colors = array();
	// First get all Round robin archives
	// Url should look like:
	// RRA[file1.rrd]=ISP1&RRA[file2.rrd]=cust1&color[file1.rrd]=red&color[file2.rrd]=
	error_reporting(0);

	// IF aggr_id is set, the read from config file
	if (isset($_GET['aggr_id'])) {
		global $aggregrated_graph_traffic;
		global $nested_aggregrated_graph_traffic;
		// Create new object, with the array
		if (array_key_exists($aggr_id, $nested_aggregrated_graph_traffic)) {
			foreach($nested_aggregrated_graph_traffic[$aggr_id] as $name => $array) {
				array_push($rrdfiles, $array);
				$archives[$array] = $name;
			}
		} else {
			foreach($aggregrated_graph_traffic[$aggr_id] as $name => $file) {
				array_push($rrdfiles, "$file");
				$archives[$file] = $name;
			}
		}
	} else {
	
		foreach($_GET['RRA'] as $file => $datasource) {
			$file = "$rrd_dir/$file";
			array_push($rrdfiles, "$file");
			$archives[$file] = $datasource;
		}
		if (isset($_GET['color'])) {
			foreach($_GET['color'] as $file => $color) {
				$file = "$rrd_dir/$file";
				if (array_key_exists($file, $archives)) {
					$colors[$file] = $color;
				}
			}
		}
	}
	//$rrd = new RRD($rrdfiles,$rrdtool);
	$rrd = new RRD($nested_aggregrated_graph_traffic[$aggr_id],$rrdtool);
	$graph_params = array(
        	'type' => 'aggr_traf', 
		'colors' => $colors,
		'archives' => $archives,
        	'title' => $title,
        	'legend' => $legend,
		'width' => $width,
		'height' => $height,
		'total' => $total,
        	'start' => $from,
        	'end' => $to);
	$graphfile = $rrd->get_graph($graph_params);

} elseif ($type == "check") {
	#print "in check file is $file<br>";
	// Now determin if there are any datasources we want to be ignore
	$graph_params = array(
		'type' => 'check',
		'title' => $title,
		'legend' => $legend,
		'width' => $width,
		'height' => $height,
		'start' => $from,
		'end' => $to,
		'exclude_ds' => $exclude_ds,
		'ds_colors' => $ds_colors);
	$graphfile = $rrd->get_graph($graph_params);
}


$rrd->print_graph();

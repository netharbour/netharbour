<?php

// 
// Class file for RRD
//
@ini_set('date.timezone', date_default_timezone_get());
//

class RRD {
	private $rrdtool;

	protected $error = false;
	protected $rrd_file = '';
	protected $graph_img = false;

	// Set some default colors:
	// Color codes
	private $colors=array("FF6600","87CEEB","7FFF00","9E7BFF","8B008B","4B0082","FA8072","4169E1","D2B9D3","B4CFEC",
		"eF6600","77CEEB","eFFF00","6FFF00","8E7BFF","7B008B","3B0082","eA8072","3169E1","c2B9D3","a4CFEC",
		"dF6600","67CEEB","dFFF00","5FFF00","7E7BFF","6B008B","2B0082","dA8072","2169E1","b2B9D3","94CFEC");


	function __construct($rrd_file = '',$rrdtool='') {

		// RRDfile can be an array or an string
		if (is_array($rrd_file)) {
			$this->rrd_file = $rrd_file;
			foreach($rrd_file as $key => $value) {
				if (file_exists($value)) {
					//$this->rrd_file = $value;
				} else {
					$this->error = "$value RRD Archive not found";
				}
			}
		} else {
		// Ok string, just one file
			if ($rrd_file != '') {
				if (file_exists($rrd_file)) {
					$this->rrd_file = $rrd_file;
				} else {
					$this->error = "RRD Archive not found";
				}
			}
		}
		if ($rrdtool != '') {
			$this->rrdtool = $rrdtool;
		}
	}
	
	public function get_file_name($file_info = array()) {
		if (isset($file_info{'device_id'})) {
			if (is_numeric($file_info{device_id})) {
				$device_id = $file_info{device_id};
			}
		}
		if (isset($file_info{'port_name'})) {
			if ($file_info{'port_name'} != '') {
				$port_name = $file_info{'port_name'};
			}
		}
		if ((isset($device_id)) && (isset($port_name))) {
			$rrdfile = "deviceid". $device_id ."_". $port_name;
			$rrdfile = preg_replace('/\s/','-',$rrdfile);
			$rrdfile = preg_replace('/(\/)/','-',$rrdfile);
			$rrdfile = "$rrdfile.rrd";
			return $rrdfile;
			#if (file_exists($rrdfile)) {
			#	return $rrdfile;
			#} else {
			#	return false;
			#}
		} else {
			return false;
		}
	}

	function get_data_sources() {
		$cli = "$this->rrdtool info $this->rrd_file";
		$data_sources = array();
		$e = escapeshellcmd($cli);
		$f =exec("$e 2>&1 ", $output, $return_var);
		$message = implode("\n",$output) ; 
		if ($return_var != 0) {
			$this->error = "Unable to determine data sources: ". $message;
			return false;
		}
		//search for ds[prefixes_rejected].type = "GAUGE"
		foreach ($output as $line) {
			$line = trim($line);
			$result = preg_match("/^ds\[(.+)\]\.type = \"(.+)\"$/",$line,$matches);
			if ((!empty($matches[1])) &&  (!empty($matches[2])) && (! array_key_exists($matches[1], $data_sources))) {
				$data_sources[$matches[1]] = $matches[2];
			}
		}
		ksort($data_sources,SORT_STRING);
		return $data_sources;
	}

	protected function get_data() {
		$database = str_replace(":", "\:", $this->rrd_file); /* escape colons */
		$cli = "$this->rrdtool xport --start -1w --end -1s \
        		DEF:inoctets=$database:INOCTETS:AVERAGE \
        		DEF:outoctets=$database:OUTOCTETS:AVERAGE \
        		DEF:maxinoctets=$database:INOCTETS:MAX \
        		DEF:maxoutoctets=$database:OUTOCTETS:MAX \
        		CDEF:octets=inoctets,outoctets,ADDNAN \
        		CDEF:doutoctets=outoctets,-1,* \
        		CDEF:dmaxoutoctets=maxoutoctets,-1,* \
        		CDEF:inbits=inoctets,8,* \
        		CDEF:maxinbits=maxinoctets,8,* \
        		CDEF:outbits=outoctets,8,* \
        		CDEF:maxoutbits=maxoutoctets,8,* \
        		CDEF:doutbits=doutoctets,8,* \
        		CDEF:dmaxoutbits=dmaxoutoctets,8,* \
        		XPORT:inbits:'Average in bits' \
        		XPORT:maxinbits:'max in bits' \
        		XPORT:outbits:'out bits' \
        		XPORT:maxoutbits:'max out bits' ";
        	$result = shell_exec($cli);
        	return $result;
	}

	function set_rrdtool($value) {
		$this->rrdtool = $value;
	}

	function get_error() {
		return $this->error;
	}
	function get_max_value() {
	}

	function get_avg_value() {
	}
	
	function export() {
		if (! $data = $this->get_data()) {
			$this->error = "Could not export data, possibly incorrect file or no permission";
			return false;
		}
		$xml = new SimpleXMLElement($data);
		$start = $xml->meta->start;
		$end = $xml->meta->end;
		$rows = $xml->meta->rows;
		$columns = $xml->meta->columns;
		$legend = $xml->meta->legend;
		$resul = '';

		//print_r($xml->meta);
		// print field names
		$result .= "timestamp,";
		foreach($xml->meta->legend->entry as  $value) {
        		$result .= "$value,";
		} 
		$result = rtrim($result,",");
		$result .= "\n";

		foreach($xml->data->row as $key => $value) {
			#print_r($xml->data->row);
			$line ='';
			$line .= $xml->data->row->t ."," ;
			foreach($xml->data->row->v as  $value) {
				$line .= sprintf("%.0f",$value) .",";
			} 
			$line = rtrim($line,",");
			$result .= "$line\n";
		}
		return $result;

	}
	
	function get_graph($params = array()) {
	/*
	This function returns a png file
	*/
		if ($params{'type'} == '') {
			$this->error = "Invalid graph type";
			return false;
		} else {
			$type = $params{'type'};
		}
		
		if ($params{'title'} != '') {
			$title = $params{'title'};
		} else {
			$title = 'title';
		}
		if ($params{'start'} != '') {
			$from = $params{'start'};
		} else {
			$from = '-24h';
		}
		if ($params{'end'} != '') {
			$to = $params{'end'};
		} else {
			$to = '-1s';
		}
		
		if ($params{'width'} != '') {
			$width = $params{'width'};
		} else {
			$width = '550';
		}
		
		if ($params{'height'} != '') {
			$height = $params{'height'};
		} else {
			$height = '150';
		}

		if ($params{'legend'} == '0') {
			$legend = 0;
		} else {
			$legend = 1;
		}

		if ($params{'total'} == '0') {
			$total = 0;
		} else {
			$total = 1;
		}


		
		if ($type == "traffic") {
			$graphfile = $this->trafgraph($title,$from,$to,$width,$height,$legend);
		} elseif ($type == "errors") {
        		$graphfile = $this->errorgraph($title,$from,$to,$width,$height,$legend);
		} elseif ($type == "unicastpkts") {
        		$graphfile = $this->unipktsgraph($title,$from,$to,$width,$height,$legend);
		} elseif ($type == "nonunicastpkts") {
        		$graphfile = $this->nonunipktsgraph($title,$from,$to,$width,$height,$legend);
		//} elseif ($type == "aggr_traf") {
        	//	$graphfile = $this->aggregate_traf_graph($title,$from,$to,$width,$height,$legend);
		} elseif ($type == "aggr_traf") {
        		$graphfile = $this->aggregate_traf_graph($title,$from,$to,$width,$height,$legend,$params{'archives'},$params{'colors'},$total);
		} elseif ($type == "check") {
			$graphfile = $this->checkgraph($title,$from,$to,$width,$height,$legend,$params{'exclude_ds'}, $params{'ds_colors'});
		}

		// now we have the image let's render that and done!
		if($graphfile) {
			$this->graph_img = $graphfile;
			return $true;
		} else {  
			$this->graph_img = false;
			return false;
		}
	}
	

	function get_summary($from,$to,$archives=array()) {
		
		$files = $archives;
		$result = array();
		
		if (empty($files)) {
			#print "No such group defined<br>";
			$this->error = "No files specified";
			return false;
		}
		$options = " --start '$from' --end '$to'  ";
		// This is a work around for a bug in rrdtool
		// at least in 1.2.19 
		// If you dont specify a width the From and to Date
		// Are incorrect
		// Width actually also specifies the number of max
		// Samples. So a width of 700 will limit you to 700 samples.
		// So we'll need to be sure we;re not aggregating/sumarizing
		// because of width that's to small.
		// Let's assume a sample rate of 300sec.
		// then determine max num of samples:
		if ((is_numeric($from)) && (is_numeric($to))) {
			$max_samples = round(($to - $from) / 300);
		} else {
			// set statically to 2 months
			$max_samples = 12 * 24 * 62;

		}
		$options .= "--width $max_samples --height 150 ";
		
		// Replace all white spaces in the key field with ''
		// Keep a copy for correct names in legend.
		// RRD tool doesn't like white spaces in CDEFS
		$orignames = array();
		foreach($files as $file => $value) {
			$name = $value;
			$files{$file} = str_replace(" ", "", $name);
			$orignames{str_replace(" ", "", $name)} = $name;
		}
		

		foreach($files as $file => $value) {
			
			$options .= "DEF:\"inoctets$value\"=\"$file\":INOCTETS:AVERAGE ";
			$options .= "DEF:\"outoctets$value\"=\"$file\":OUTOCTETS:AVERAGE ";
			$options .= "DEF:\"maxinoctets$value\"=\"$file\":INOCTETS:MAX ";
			$options .= "DEF:\"maxoutoctets$value\"=\"$file\":OUTOCTETS:MAX ";
		}


		foreach($files as $file => $value) {
			$options .= "CDEF:octets$value=inoctets$value,outoctets$value,ADDNAN ";
		}

		// this has to produce something like
		// "CDEF:octets=octets,octets2,+ \ ";

		$i=0;
		$options .= "CDEF:octets=";
		foreach($files as $file => $value) {
			$options .= "octets$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		foreach($files as $file => $value) {
			$options .= " CDEF:doutoctets$value=outoctets$value,1,* ";
			$options .= " CDEF:dmaxoutoctets$value=maxoutoctets$value,1,* ";
		}
		foreach($files as $file => $value) {
			$options .= " CDEF:inbits$value=inoctets$value,8,* ";
			$options .= " CDEF:maxinbits$value=maxinoctets$value,8,* ";
		}

		// CDEF:inbits=inbits1,inbits2,+ \
		$i=0;
		$options .= " CDEF:inbits=";
		foreach($files as $file => $value) {
			$options .= "inbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";
		// CDEF:maxinbits=inbits1,inbits2,+ \
		$i=0;
		$options .= " CDEF:maxinbits=";
		foreach($files as $file => $value) {
			$options .= "maxinbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";
        
		foreach($files as $file => $value) {
			$options .= "CDEF:outbits$value=outoctets$value,8,* ";
			$options .= "CDEF:maxoutbits$value=maxoutoctets$value,8,* ";
		}
		
		// CDEF:outbits=-outbits1,outbits2,+ \
		$i=0;
		$options .= "CDEF:outbits=";
		foreach($files as $file => $value) {
			$options .= "outbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		foreach($files as $file => $value) {
			$options .= "CDEF:doutbits$value=doutoctets$value,8,* ";
			$options .= "CDEF:dmaxoutbits$value=dmaxoutoctets$value,8,* ";
		}
		// CDEF:maxoutbits=maxoutbits1,maxoutbits2,+ \
		$i=0;
		$options .= "CDEF:maxoutbits=";
		foreach($files as $file => $value) {
			$options .= "maxoutbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

        	//  CDEF:doutbits=doutbits1,doutbits2,+ \
		$i=0;
		$options .= "CDEF:doutbits=";
		foreach($files as $file => $value) {
			$options .= "doutbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";
        
		//  CDEF:dmaxoutbits=doutbits1,doutbits2,+ \
		$i=0;
		$options .= "CDEF:dmaxoutbits=";
		foreach($files as $file => $value) {
			$options .= "dmaxoutbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		
		//CDEF:totinall=inoctets1,inoctets2,+ \
		$i=0;
		$options .= "CDEF:totinall=";
		foreach($files as $file => $value) {
			$options .= "inoctets$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		// CDEF:totoutall=doutoctets1,doutoctets2,+ \
		$i=0;
		$options .= "CDEF:totoutall=";
		foreach($files as $file => $value) {
			$options .= "doutoctets$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		$options .= "VDEF:totin=totinall,TOTAL ";
		$options .= "VDEF:totout=totoutall,TOTAL ";
		$options .= " VDEF:tot=octets,TOTAL ";
		$options .= " VDEF:95thin=inbits,95,PERCENTNAN ";
		$options .= " VDEF:95thout=outbits,95,PERCENTNAN ";
		$options .= " VDEF:d95thout=doutbits,5,PERCENTNAN ";
                $options .= " VDEF:FirstDate=totinall,FIRST ";
                $options .= " VDEF:LastDate=totinall,LAST ";
		$options .= "PRINT:inbits:LAST:\"CURRENTIN \"%6.2lf%s ";
		$options .= "PRINT:inbits:AVERAGE:\"AVERAGEIN \"%6.2lf%s ";
		$options .= "PRINT:maxinbits:MAX:\"MAXIN \"%6.2lf%s ";
		$options .= "PRINT:95thin:\"95IN \"%6.2lf%s ";
		$options .= "PRINT:outbits:LAST:\"CURRENTOUT \"%6.2lf%s ";
		$options .= "PRINT:outbits:AVERAGE:\"AVERAGEOUT \"%6.2lf%s ";
		$options .= "PRINT:maxoutbits:MAX:\"MAXOUT \"%6.2lf%s ";
		$options .= "PRINT:95thout:\"95OUT \"%6.2lf%s ";
		$options .= "PRINT:tot:\"TOTAL \"%6.2lf%s ";
		$options .= "PRINT:totin:\"TOTALIN \"%6.2lf%s ";
		$options .= "PRINT:totout:\"TOTALOUT \"%6.2lf%s ";
		$options .= "PRINT:FirstDate:\"FROM %d-%m-%Y %T:strftime\" ";
		$options .= "PRINT:LastDate:\"TO %d-%m-%Y %T:strftime\"";
		

		$cmd = "$this->rrdtool graph /dev/null -f '' $options";
		#print "$cmd<br>";
		#exit;
		$f = exec($cmd, $output);
		
		foreach($output as $value) {
			#print "$value<br>";
			$keywords = preg_split("/[\s]+/", $value);
			if ($keywords[0] != '') {
				$res ='';
				foreach ($keywords as $i => $val) {
					if ($i == 0) {
						// nothing
					}
					elseif ($i == 1) {
						$res = $val;
					}
					elseif ($i > 0)  {
						$res = "$res $val";
					}
				}
				$result{$keywords[0]} = $res;
			}

		}
		return $result;
		
	}

	
	
	
	function get_graph_img() {
	 	return $this->graph_img;
	}

	function print_graph() {
		header('Content-type: image/png');
		if ($this->graph_img)  {
			echo $this->graph_img;
		} else  {
			$this->print_graph_no_image();
		}
	}

	private function print_graph_no_image() {
		$fp = fopen("images/no_graph.png", "rb");
		while(!feof($fp)) {
			$buffer = fread($fp, 4096);
			$output = $output . $buffer;
			$bytesSent+=strlen($buf);    /* We know how many bytes were sent to the user */
		}
		echo $output;
	}


	private function checkgraph($title, $from, $to, $width, $height,$legend =1, $exclude_ds = array(), $ds_colors = array()) {

		$database = str_replace(":", "\:", $this->rrd_file); /* escape colons */
		$delim = "       ";	
		// Get all datasources and build command
		if ($datasources = $this->get_data_sources()) {

			// 1st determine longest ds name, so we can append the ds strings correctly
			// this is required for proper formatting of columns
			$max_lenghth = 0;
			foreach($datasources as $ds => $value) {
				// Check if this is not a datasource we want to ignore
				if (array_key_exists($ds,$exclude_ds)) {
					// loop to next;
					continue;
				}
				if (strlen($ds) > $max_lenghth) {
					$max_lenghth = strlen($ds);
				}
			}

			$def ="";
			$date1;
			$date2;
			$color_i = 0;
			foreach($datasources as $ds => $value) {

				// Check if this is not a datasource we want to ignore
				if (array_key_exists($ds,$exclude_ds)) {
					// loop to next;
					continue;
				}

				$database = str_replace(":", "\:", $this->rrd_file); /* escape colons */
				$def .= "DEF:". $ds ."=" . $database .":". $ds .":AVERAGE \\\n";
				$def .= "DEF:". $ds ."MAX=" . $database .":". $ds .":MAX \\\n";
				$ds_text = str_pad($ds,$max_lenghth);
				$date1 = " VDEF:FirstDate=$ds,FIRST \\\n";
				$date2 = " VDEF:LastDate=$ds,LAST \\\n";

				// determine color
				if ((array_key_exists($ds,$ds_colors)) && (preg_match('/^[a-f0-9]{6}$/i', $ds_colors[$ds])))  {
					$t_color = $ds_colors[$ds];
				} elseif($this->colors[$color_i]) {
					$t_color = $this->colors[$color_i];
				} else {
					$t_color = $this->rand_colorCode();
				}
				$color_i++;
				// end color

				if ($legend == 0) {
					$text .="LINE2:". $ds ."#" . $t_color .":";
				} else {
					$text .="LINE2:". $ds ."#" . $t_color .":'$ds_text' \\\n";
					$text .= "GPRINT:$ds:LAST:'%2.2lf %s' \\\n";
					$text .= "GPRINT:$ds:AVERAGE:'%2.2lf %s' \\\n";
					$text .= "GPRINT:$ds:MIN:'%2.2lf %s' \\\n";
					$text .= "GPRINT:".$ds."MAX:MAX:'%2.2lf %s\j' \\\n";
				}
			}
		}

		$period = $to - $from;
		$options = "--start $from --end $to --width $width --height $height --title=\"$title\" ";
		if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
		$options .= " --vertical-label=''  ";
		$text .= " COMMENT:\ \\\\n";
		$text .= " GPRINT:FirstDate:\"From\: %d-%m-%Y %T:strftime\"";
		$text .= " COMMENT:\ \\\\n";
		$text .= " GPRINT:LastDate:\"To\:   %d-%m-%Y %T:strftime\"";

		$title_line = "COMMENT:' ".str_pad(" ",$max_lenghth) ."'  \\\n";
		$title_line = "COMMENT:'".str_pad(" ",$max_lenghth) ." ' \\\n";
		$title_line .= "COMMENT:'Cur\: '  \\\n";
		$title_line .= "COMMENT:'Avg\: '  \\\n";
		$title_line .= "COMMENT:'Min\: '  \\\n";
		$title_line .= "COMMENT:'Max\: \j'\\\n";
		$today = date("F j, Y, g:i a");   
		$watwermark = "--watermark 'Image Generated on $today' \\\n";


		$cmd = "$this->rrdtool graph - $options $watwermark $title_line $def $date1 $date2 $text";
		//$result = exec($cmd, $response, $return_code);
		//print_r (array('result' => $result, 'response' => $response, 'return_code' => $return_code) );

		$handle = popen($cmd,"r");
		$output;
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$output = $output . $buffer;    
				$bytesSent+=strlen($buffer);    /* We know how many bytes were sent to the user */
			}
		}
		if ($bytesSent > 0 )  {
			return $output;
		} else {
			return  false;
		}
	}


	private function errorgraph($title, $from, $to, $width, $height,$legend =1) {
		$database = str_replace(":", "\:", $this->rrd_file); /* escape colons */
		$period = $to - $from;
		$options = " --start $from --end $to --width $width --height $height --title=\"$title\" ";
		if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
		$options .= " --vertical-label=\"Errors per second\"  ";
		$options .= " DEF:in=\"$database\":INERRORS:AVERAGE";
		$options .= " DEF:out=\"$database\":OUTERRORS:AVERAGE";
		$options .= " CDEF:dout=out,-1,*";
		$options .= " VDEF:FirstDate=in,FIRST ";
		$options .= " VDEF:LastDate=in,LAST ";
		$options .= " AREA:in#ff3300:";
		if ($legend == 0) {
			$options .= " LINE1.25:in#ff0000:";
			$options .= " AREA:dout#FF6633:";
			$options .= " AREA:dout#FF6633:";
			$options .= " LINE1.25:dout#cc3300:";
		} else {
			$options .= " COMMENT:Errors\ \ \ \ Current\ \ \ \ \ Average\ \ \ \ \ \ Maximum\\\\n";
			$options .= " LINE1.25:in#ff0000:In\ \ ";
			$options .= " GPRINT:in:LAST:%6.2lf%spps";
			$options .= " GPRINT:in:AVERAGE:%6.2lf%spps";
			$options .= " GPRINT:in:MAX:%6.2lf%spps\\\\n";
			$options .= " AREA:dout#FF6633:";
			$options .= " LINE1.25:dout#cc3300:Out\ ";
			$options .= " LINE1.25:0#000000: ";
			$options .= " GPRINT:out:LAST:%6.2lf%spps";
			$options .= " GPRINT:out:AVERAGE:%6.2lf%spps";
			$options .= " GPRINT:out:MAX:%6.2lf%spps\\\\n";
			$options .= " COMMENT:\ \\\\n";
			$options .= " GPRINT:FirstDate:\"From\: %d-%m-%Y %T:strftime\"";
			$options .= " COMMENT:\ \\\\n";
			$options .= " GPRINT:LastDate:\"To\:   %d-%m-%Y %T:strftime\"";
		}

		$cmd = "$this->rrdtool graph - $options";
		//$result = exec($cmd, $response, $return_code);
		//print_r (array('result' => $result, 'response' => $response, 'return_code' => $return_code) );

		$handle = popen($cmd,"r");
		$output;
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$output = $output . $buffer;    
				$bytesSent+=strlen($buffer);    /* We know how many bytes were sent to the user */
			}
		}
		if ($bytesSent > 0 )  {
			return $output;
		} else {
			return  false;
		}
	}


	private function trafgraph ($title, $from, $to, $width, $height,$legend =1) {
		$database = str_replace(":", "\:", $this->rrd_file); /* escape colons */
		$period = $to - $from;
		$options = " --start $from --end $to --width $width --height $height --title=\"$title\" ";
		if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
  		if($height < "33") { $options .= " --only-graph"; }
		$options .= " --vertical-label=\"bits per second\"  ";
		$options .= " DEF:inoctets=\"$database\":INOCTETS:AVERAGE";
		$options .= " DEF:outoctets=\"$database\":OUTOCTETS:AVERAGE";
		$options .= " DEF:maxinoctets=\"$database\":INOCTETS:MAX";
		$options .= " DEF:maxoutoctets=\"$database\":OUTOCTETS:MAX";
		$options .= " CDEF:octets=inoctets,outoctets,ADDNAN";
		$options .= " CDEF:doutoctets=outoctets,-1,*";
		$options .= " CDEF:dmaxoutoctets=maxoutoctets,-1,*";
		$options .= " CDEF:inbits=inoctets,8,*";
		$options .= " CDEF:maxinbits=maxinoctets,8,*";
		$options .= " CDEF:outbits=outoctets,8,*";
		$options .= " CDEF:maxoutbits=maxoutoctets,8,*";
		$options .= " CDEF:doutbits=doutoctets,8,*";
		$options .= " CDEF:dmaxoutbits=dmaxoutoctets,8,*";
		$options .= " VDEF:totin=inoctets,TOTAL";
		$options .= " VDEF:FirstDate=inoctets,FIRST ";
		$options .= " VDEF:LastDate=inoctets,LAST ";
		$options .= " VDEF:totout=outoctets,TOTAL";
		$options .= " VDEF:tot=octets,TOTAL";
		$options .= " VDEF:95thin=inbits,95,PERCENTNAN";
		$options .= " VDEF:95thout=outbits,95,PERCENTNAN";
		$options .= " VDEF:d95thout=doutbits,5,PERCENTNAN";
		$options .= " LINE1.25:0#000000: ";
		#$options .= " COMMENT:\ $period\\\\c";

		if ($legend == 0) {
			$options .= " AREA:inbits#CDEB8B:";
			$options .= " LINE1.25:inbits#006600: ";
			$options .= " LINE1:maxinbits#AF0A8D ";
			$options .= " AREA:doutbits#C3D9FF:";
			$options .= " LINE1.25:doutbits#000099";
			$options .= " LINE1:dmaxoutbits#AF0A8D:";
		} else {
			$options .= " COMMENT:\ \\\\n ";
			$options .= " COMMENT:\ \ \ \ \ \ \ Current\ \ \ Average\ \ \ \ \ \ \ Max\ \ \ \ \ \ 95th\ %\\\\n";
			$options .= " AREA:inbits#CDEB8B:";
			$options .= " LINE1.25:inbits#006600:In\ ";
			$options .= " AREA:inbits#CDEB8B:";
			$options .= " LINE1.25:inbits#006600: ";
			$options .= " LINE1:maxinbits#AF0A8D ";
			$options .= " AREA:doutbits#C3D9FF:";
			$options .= " LINE1.25:doutbits#000099";
			$options .= " LINE1:dmaxoutbits#AF0A8D:";
			$options .= " GPRINT:inbits:LAST:%6.2lf%s";
			$options .= " GPRINT:inbits:AVERAGE:%6.2lf%s";
			$options .= " LINE1:maxinbits#AF0A8D:\ ";
			$options .= " GPRINT:maxinbits:MAX:%6.2lf%s";
			$options .= " GPRINT:95thin:%6.2lf%s\\\\n";
			$options .= " AREA:doutbits#C3D9FF:";
			$options .= " LINE1.25:doutbits#000099:Out";
			$options .= " GPRINT:outbits:LAST:%6.2lf%s";
			$options .= " GPRINT:outbits:AVERAGE:%6.2lf%s";
			$options .= " LINE1:dmaxoutbits#AF0A8D:\ ";
			$options .= " GPRINT:maxoutbits:MAX:%6.2lf%s";
			$options .= " GPRINT:95thout:%6.2lf%s\\\\n";
			$options .= " GPRINT:tot:Total\ %6.2lf%s";
			$options .= " GPRINT:totin:\(In\ %6.2lf%s";
			$options .= " GPRINT:totout:Out\ %6.2lf%s\)\\\\l";
			$options .= " COMMENT:\ \\\\n";
			$options .= " GPRINT:FirstDate:\"From\: %d-%m-%Y %T:strftime\"";
			$options .= " COMMENT:\ \\\\n";
			$options .= " GPRINT:LastDate:\"To\:   %d-%m-%Y %T:strftime\"";
	
			
  		}
		$options .= " LINE1:95thin#aa0000";
		$options .= " LINE1:d95thout#aa0000";

		$cmd = "$this->rrdtool graph - $options";
		#print "$cmd";

		$handle = popen($cmd,"r");
		$output;
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$output = $output . $buffer;    
				$bytesSent+=strlen($buffer);    /* We know how many bytes were sent to the user */
			}
		}
		if ($bytesSent > 0 )  {
			return $output;
		} else {
			return  false;
		}
	}

	private function unipktsgraph($title,$from, $to, $width, $height, $legend=1) {
		$database = str_replace(":", "\:", $this->rrd_file); /* escape colons */
		$period = $to - $from;
		$options = " --start $from --end $to --width $width --height $height --title=\"$title\" ";
		if($height < "33") { $options .= " --only-graph"; }
		if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
		$options .= " --vertical-label=\" Unicast p/s\"  ";
		$options .= " DEF:in=\"$database\":INUCASTPKTS:AVERAGE";
		$options .= " DEF:out=\"$database\":OUTUCASTPKTS:AVERAGE";
		$options .= " CDEF:dout=out,-1,*";

		if ($legend == 0) {
			$options .= " AREA:in#aa66aa:";
			$options .= " LINE1.25:in#330033";
			$options .= " AREA:dout#FFDD88:";
			$options .= " LINE1.25:dout#FF6600:";
			$options .= " LINE1.25:0#000000: ";
		} else {
			$options .= " VDEF:FirstDate=in,FIRST ";
			$options .= " VDEF:LastDate=in,LAST ";
			$options .= " AREA:in#aa66aa:";
			$options .= " COMMENT:Packets\ \ \ \ Current\ \ \ \ \ Average\ \ \ \ \ \ Maximum\\\\n";
			$options .= " LINE1.25:in#330033:In\ \ ";
			$options .= " GPRINT:in:LAST:%6.2lf%spps";
			$options .= " GPRINT:in:AVERAGE:%6.2lf%spps";
			$options .= " GPRINT:in:MAX:%6.2lf%spps\\\\n";
			$options .= " AREA:dout#FFDD88:";
			$options .= " LINE1.25:dout#FF6600:Out\ ";
			$options .= " LINE1.25:0#000000: ";
			$options .= " GPRINT:out:LAST:%6.2lf%spps";
			$options .= " GPRINT:out:AVERAGE:%6.2lf%spps";
			$options .= " GPRINT:out:MAX:%6.2lf%spps\\\\n";  
			$options .= " COMMENT:\ \\\\n";
			$options .= " GPRINT:FirstDate:\"From\: %d-%m-%Y %T:strftime\"";
			$options .= " COMMENT:\ \\\\n";
			$options .= " GPRINT:LastDate:\"To\:   %d-%m-%Y %T:strftime\"";
		}

		$cmd = "$this->rrdtool graph - $options";

		$handle = popen($cmd,"r");
		$output;
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$output = $output . $buffer;    
				$bytesSent+=strlen($buffer);    /* We know how many bytes were sent to the user */
			}
		}
		if ($bytesSent > 0 )  {
			return $output;
		} else {
			return  false;
		}
	}


	private	function nonunipktsgraph($title,$from, $to, $width, $height, $legend=1) {
		$database = str_replace(":", "\:", $this->rrd_file); /* escape colons */
		$period = $to - $from;

		$options = " --start $from --end $to --width $width --height $height --title=\"$title\" ";
		if($height < "33") { $options .= " --only-graph"; }
		if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
		$options .= " --vertical-label=\"Non Unicast p/s\"  ";
		$options .= " DEF:in=\"$database\":INNUCASTPKTS:AVERAGE";
		$options .= " DEF:out=\"$database\":OUTNUCASTPKTS:AVERAGE";
		$options .= " CDEF:dout=out,-1,*";

		if ($legend == 0) {
			$options .= " AREA:in#aa66aa:";
			$options .= " LINE1.25:in#330033";
			$options .= " AREA:dout#FFDD88:";
			$options .= " LINE1.25:dout#FF6600:";
			$options .= " LINE1.25:0#000000: ";
		} else {
			$options .= " VDEF:FirstDate=in,FIRST ";
			$options .= " VDEF:LastDate=in,LAST ";
			$options .= " AREA:in#aa66aa:";
			$options .= " COMMENT:Packets\ \ \ \ Current\ \ \ \ \ Average\ \ \ \ \ \ Maximum\\\\n";
			$options .= " LINE1.25:in#330033:In\ \ ";
			$options .= " GPRINT:in:LAST:%6.2lf%spps";
			$options .= " GPRINT:in:AVERAGE:%6.2lf%spps";
			$options .= " GPRINT:in:MAX:%6.2lf%spps\\\\n";
			$options .= " AREA:dout#FFDD88:";
			$options .= " LINE1.25:dout#FF6600:Out\ ";
			$options .= " LINE1.25:0#000000: ";
			$options .= " GPRINT:out:LAST:%6.2lf%spps";
			$options .= " GPRINT:out:AVERAGE:%6.2lf%spps";
			$options .= " GPRINT:out:MAX:%6.2lf%spps\\\\n";  
		}

		$cmd = "$this->rrdtool graph - $options";

		$handle = popen($cmd,"r");
		$output;
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$output = $output . $buffer;    
				$bytesSent+=strlen($buffer);    /* We know how many bytes were sent to the user */
			}
		}
		if ($bytesSent > 0 )  {
			return $output;
		} else {
			return  false;
		}
	}
	
	/*
	private function aggregate_traf_graph($title,$from, $to, $width, $height,$legend=1) {

		// Check if this group is defined
		if (!is_array($this->rrd_file)) {
			#print "No such group defined<br>";
			$this->error = "rrd file parameter should be an array for aggregated graphs";
			return false;
		}
		$options = " --start $from --end $to --width $width --height $height --title=\"$title\" ";
		if($height < "33") { $options .= " --only-graph"; }
		if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
		$options .= " --vertical-label=\"Out <---> In\" ";
		// define cdefs


		// Replace all white spaces in the key field with ''
		// Keep a copy for correct names in legend.
		// RRD tool doesn't like white spaces in CDEFS
		$tmparray = array();
		$origarray = array();
		foreach($this->rrd_file as $key => $value) {
			$tmparray{str_replace(" ", "", $key)} = $value;
			$origarray{str_replace(" ", "", $key)} = $key;
			
		}
		$this->rrd_file = $tmparray;
		foreach($this->rrd_file as $key => $value) {
			$value  = str_replace(":", "\:", $value); 
			$options .= "DEF:\"inoctets$key\"=\"$value\":INOCTETS:AVERAGE ";
			$options .= "DEF:\"outoctets$key\"=\"$value\":OUTOCTETS:AVERAGE ";
			$options .= "DEF:\"maxinoctets$key\"=\"$value\":INOCTETS:MAX ";
			$options .= "DEF:\"maxoutoctets$key\"=\"$value\":OUTOCTETS:MAX ";
		}


		foreach($this->rrd_file as $key => $value) {
			$options .= "CDEF:octets$key=inoctets$key,outoctets$key,+ ";
		}

		// this has to produce something like
		// "CDEF:octets=octets,octets2,+ \ ";

		$i=0;
		$options .= "CDEF:octets=";
		foreach($this->rrd_file as $key => $value) {
			$options .= "octets$key,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "+";
			if ($i != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		foreach($this->rrd_file as $key => $value) {
			$options .= " CDEF:doutoctets$key=outoctets$key,-1,* ";
			$options .= " CDEF:dmaxoutoctets$key=maxoutoctets$key,-1,* ";
		}
		foreach($this->rrd_file as $key => $value) {
			$options .= " CDEF:inbits$key=inoctets$key,8,* ";
			$options .= " CDEF:maxinbits$key=maxinoctets$key,8,* ";
		}

		// CDEF:inbits=inbits1,inbits2,+ \
		$i=0;
		$options .= " CDEF:inbits=";
		foreach($this->rrd_file as $key => $value) {
			$options .= "inbits$key,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "+";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";
		// CDEF:maxinbits=inbits1,inbits2,+ \
		$i=0;
		$options .= " CDEF:maxinbits=";
		foreach($this->rrd_file as $key => $value) {
			$options .= "maxinbits$key,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "+";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";
        
		foreach($this->rrd_file as $key => $value) {
			$options .= "CDEF:outbits$key=outoctets$key,8,* ";
			$options .= "CDEF:maxoutbits$key=maxoutoctets$key,8,* ";
		}
		
		// CDEF:outbits=-outbits1,outbits2,+ \
		$i=0;
		$options .= "CDEF:outbits=";
		foreach($this->rrd_file as $key => $value) {
			$options .= "outbits$key,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "+";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		foreach($this->rrd_file as $key => $value) {
			$options .= "CDEF:doutbits$key=doutoctets$key,8,* ";
			$options .= "CDEF:dmaxoutbits$key=dmaxoutoctets$key,8,* ";
		}
		// CDEF:maxoutbits=maxoutbits1,maxoutbits2,+ \
		$i=0;
		$options .= "CDEF:maxoutbits=";
		foreach($this->rrd_file as $key => $value) {
			$options .= "maxoutbits$key,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "+";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

        	//  CDEF:doutbits=doutbits1,doutbits2,+ \
		$i=0;
		$options .= "CDEF:doutbits=";
		foreach($this->rrd_file as $key => $value) {
			$options .= "doutbits$key,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "+";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";
        
		//  CDEF:dmaxoutbits=doutbits1,doutbits2,+ \
		$i=0;
		$options .= "CDEF:dmaxoutbits=";
		foreach($this->rrd_file as $key => $value) {
			$options .= "dmaxoutbits$key,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "+";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		
		//CDEF:totinall=inoctets1,inoctets2,+ \
		$i=0;
		$options .= "CDEF:totinall=";
		foreach($this->rrd_file as $key => $value) {
			$options .= "inoctets$key,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "+";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		// CDEF:totoutall=doutoctets1,doutoctets2,+ \
		$i=0;
		$options .= "CDEF:totoutall=";
		foreach($this->rrd_file as $key => $value) {
			$options .= "doutoctets$key,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "+";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		$options .= "VDEF:totin=totinall,TOTAL ";
		$options .= "VDEF:totout=totoutall,TOTAL ";
		$options .= " VDEF:tot=octets,TOTAL ";
		$options .= " VDEF:95thin=inbits,95,PERCENTNAN ";
		$options .= " VDEF:95thout=outbits,95,PERCENTNAN ";
		$options .= " VDEF:d95thout=doutbits,5,PERCENTNAN ";
                $options .= " VDEF:FirstDate=totinall,FIRST ";
                $options .= " VDEF:LastDate=totinall,LAST ";


		$i =0;  
		foreach($this->rrd_file as $key => $value) {
			//AREA:inbits1#FF0000:In\ Telus\\\n\
			if ($this->colors[$i]) {
			} else {
				  $this->colors[$i] = $this->rand_colorCode();
			}
			$options .= "AREA:inbits$key#". $this->colors[$i] .":\"In\: ".$origarray{$key}."\\\\n\"";
			#$options .= "AREA:inbits$key#". $this->colors[$i] .":In\ $key\\\\n";
			if ($i > 0) {
				$options .= ":STACK ";
			} else { $options .= " ";}
			
			$options .= "GPRINT:inbits$key:LAST:\ \ \ \ \ Current\ %6.2lf%s  ";
			$options .= "GPRINT:inbits$key:AVERAGE:Average\ %6.2lf%s ";
			$options .= "GPRINT:maxinbits$key:MAX:Max\ %6.2lf%s\\\\n ";
			$i++;
		}
		#print "$options\n"; exit;
        
		$i =0;  
		foreach($this->rrd_file as $key => $value) {
			$options .= "AREA:doutbits$key#". $this->colors[$i] .":\"Out\: ".$origarray{$key}."\\\\n\"";
			if ($i > 0) {
				$options .= ":STACK ";
			} else { $options .= " ";}
			$options .= "GPRINT:outbits$key:LAST:\ \ \ \ \ Current\ %6.2lf%s  ";
			$options .= "GPRINT:outbits$key:AVERAGE:Average\ %6.2lf%s ";
			$options .= "GPRINT:maxoutbits$key:MAX:Max\ %6.2lf%s\\\\n ";
			$i++;
		}
		$options .= "COMMENT:\ \\\n ";
		$options .= "COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \\\\n ";
		$options .= "LINE1.25:0#000000: ";
		$options .= "LINE1.25:inbits#006600:Total\ In\\\\n ";
		$options .= "GPRINT:inbits:LAST:\ \ \ \ Current\ %6.2lf%s  ";
		$options .= "GPRINT:inbits:AVERAGE:Average\ %6.2lf%s ";
		$options .= "GPRINT:maxinbits:MAX:Max\ %6.2lf%s   ";
		$options .= "GPRINT:95thin:95th\ %6.2lf%s\\\\n ";
		$options .= "LINE1.25:doutbits#000099:Total\ Out\\\\n ";
		$options .= "GPRINT:outbits:LAST:\ \ \ \ Current\ %6.2lf%s ";
		$options .= "GPRINT:outbits:AVERAGE:Average\ %6.2lf%s ";
		$options .= "GPRINT:maxoutbits:MAX:Max\ %6.2lf%s ";
		$options .= "GPRINT:95thout:95th\ %6.2lf%s\\\\n ";
		$options .= "GPRINT:tot:Total\ %6.2lf%s ";
		$options .= "GPRINT:totin:\(In\ %6.2lf%s ";
		$options .= "GPRINT:totout:Out\ %6.2lf%s\)\\\\l ";
		$options .= "LINE1:95thin#aa0000 ";
		$options .= "LINE1:d95thout#aa0000 ";
		$options .= "LINE1:maxinbits#aa0000 ";
		$options .= "LINE1:dmaxoutbits#aa0000";
		$options .= " COMMENT:\ \\\\n";
		$options .= " GPRINT:FirstDate:\"From\: %d-%m-%Y %T:strftime\"";
		$options .= " COMMENT:\ \\\\n";
		$options .= " GPRINT:LastDate:\"To\:   %d-%m-%Y %T:strftime\"";

		$cmd = "$this->rrdtool graph - $options";
		#print "$cmd<br>"; exit;
		$result = exec($cmd, $response, $return_code);
		$handle = popen($cmd,"r");
		$output;
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$output = $output . $buffer;   
				$bytesSent+=strlen($buffer);    
	
			}
		}
		if ($bytesSent > 0 )  {
			return $output;
		} else {
			return  false;
		}

	}
	*/

	private function aggregate_traf_graph($title,$from,$to,$width,$height,$legend=1,$archives=array(),$colors=array(),$total=1) {
		
		$files = $archives;

		if (empty($files)) {
			#print "No such group defined<br>";
			$this->error = "No files specified";
			//return false;
		}

		// Check for nested graphs
		$mode = "nested";

		foreach ($files as $key => $value) {
			if (!is_array($value)) {
				$mode = "normal";
			}
		} 


		$options = " --start '$from' --end '$to' --width $width --height $height --title=\"$title\" ";
		if($height < "33") { $options .= " --only-graph"; }
		if($legend == 0)  { $options .= " --only-graph"; }


		if($width <= "300") { $options .= " --font LEGEND:7:".$config['mono_font']." --font AXIS:6:".$config['mono_font']." --font-render-mode normal "; }
		$options .= " --vertical-label=\"Out <---> In\" \\\n";
		// define cdefs
		
		// Replace all white spaces in the key field with ''
		// Keep a copy for correct names in legend.
		// RRD tool doesn't like white spaces in CDEFS
		$orignames = array();
		foreach($files as $file => $value) {
			$name = $value;
			$files{$file} = str_replace(" ", "", $name);
			$orignames{str_replace(" ", "", $name)} = $name;
		}
	
		$cdefdata = array();	
		if ($mode == "nested") {

			foreach($this->rrd_file as $title => $array) {
				$title = str_replace(" ", "", $title);
				$cdefdata["additions_title"] .= "ADDNAN,";
				$cdefdata["inbits"] .= "inbits$title,";
				$cdefdata["maxinbits"] .= "maxinbits$title,";
				$cdefdata["octets"] .= "octets$title,";
				$cdefdata["outbits"] .= "outbits$title,";
				$cdefdata["maxoutbits"] .= "maxoutbits$title,";
				$cdefdata["doutbits"] .= "doutbits$title,";
				$cdefdata["dmaxoutbits"] .= "dmaxoutbits$title,";
				//$cdefdata["totinall"] .= "inoctets$title,";
				//$cdefdata["totoutall"] .= "outoctets$title,";

				foreach ($array[0] as $sub_tiltle => $file) {
					$sub_tiltle = str_replace(" ", "", $sub_tiltle);

					$options .= "DEF:\"inoctets".$title."_".$sub_tiltle."\"=\"$file\":INOCTETS:AVERAGE \\\n";
					$options .= "DEF:\"outoctets".$title."_".$sub_tiltle."\"=\"$file\":OUTOCTETS:AVERAGE \\\n";
					$options .= "DEF:\"maxinoctets".$title."_".$sub_tiltle."\"=\"$file\":INOCTETS:MAX \\\n";
					$options .= "DEF:\"maxoutoctets".$title."_".$sub_tiltle."\"=\"$file\":OUTOCTETS:MAX \\\n";
					$cdefdata["additions_subtitle"] .= "ADDNAN,";
					$cdefdata["additions".$title] .= "ADDNAN,";
					$cdefdata["octets".$title] .= "inoctets".$title."_".$sub_tiltle.",outoctets".$title."_".$sub_tiltle.",";
					$cdefdata["inoctets".$title] .= "inoctets".$title."_".$sub_tiltle.",";
					$cdefdata["outoctets".$title] .= "outoctets".$title."_".$sub_tiltle.",";
				//	$cdefdata["doutoctets".$title] .= "outoctets".$title."_".$sub_tiltle.",";
				//	$cdefdata["dmaxoutoctets".$title] .= "maxoutoctets".$title."_".$sub_tiltle.",";
					$cdefdata["maxoutoctets".$title] .= "maxoutoctets".$title."_".$sub_tiltle.",";
					$cdefdata["maxinoctets".$title] .= "maxinoctets".$title."_".$sub_tiltle.",";
					$cdefdata["inbits".$title] .= "inoctets".$title."_".$sub_tiltle.",";
					$cdefdata["maxinbits".$title] .= "maxinoctets".$title."_".$sub_tiltle.",";
					$cdefdata["outbits".$title] .= "outoctets".$title."_".$sub_tiltle.",";
					$cdefdata["maxoutbits".$title] .= "maxoutoctets".$title."_".$sub_tiltle.",";
					$cdefdata["doutbits".$title] .= "doutoctets".$title."_".$sub_tiltle.",";
					$cdefdata["dmaxoutbits".$title] .= "dmaxoutoctets".$title."_".$sub_tiltle.",";
					$cdefdata["totinall"] .= "inoctets".$title."_".$sub_tiltle.",";
					$cdefdata["totoutall"] .= "outoctets".$title."_".$sub_tiltle.",";
				}
			}
			$cdefdata["additions_subtitle"] = rtrim($cdefdata["additions_subtitle"],",");
			$cdefdata["additions_subtitle"] = $this->rstrtrim($cdefdata["additions_subtitle"],"ADDNAN");
			$cdefdata["additions_subtitle"] = rtrim($cdefdata["additions_subtitle"],",");

			$cdefdata["additions_title"] = rtrim($cdefdata["additions_title"],",");
			$cdefdata["additions_title"] = $this->rstrtrim($cdefdata["additions_title"],"ADDNAN");
			$cdefdata["additions_title"] = rtrim($cdefdata["additions_title"],",");

			$i=0;
			foreach($this->rrd_file as $title => $array) {
				$orig_title = $title;
				$title = str_replace(" ", "", $title);
					
				$cdefdata["additions".$title] = rtrim($cdefdata["additions".$title],",");
				$cdefdata["additions".$title] = $this->rstrtrim($cdefdata["additions".$title],"ADDNAN");
				$cdefdata["additions".$title] = rtrim($cdefdata["additions".$title],",");
				$options .= "CDEF:octets".$title."=".$cdefdata["octets".$title] .$cdefdata["additions".$title].",".$cdefdata["additions".$title].",ADDNAN \\\n";
				$options .= "CDEF:maxoutoctets".$title."=".$cdefdata["maxoutoctets".$title] .$cdefdata["additions".$title]." \\\n";
				$options .= "CDEF:dmaxoutoctets".$title."=maxoutoctets".$title .",-1,* \\\n";
				$options .= "CDEF:outoctets".$title."=".$cdefdata["outoctets".$title] .$cdefdata["additions".$title]." \\\n";
				$options .= "CDEF:doutoctets".$title."=outoctets".$title .",-1,* \\\n";
				$options .= "CDEF:doutbits".$title."=doutoctets".$title.",8,* \\\n";
				$options .= "CDEF:outbits".$title."=outoctets".$title .",8,* \\\n";
				$options .= "CDEF:inoctets".$title."=".$cdefdata["inoctets".$title] .$cdefdata["additions".$title]." \\\n";
				$options .= "CDEF:maxinoctets".$title."=".$cdefdata["maxinoctets".$title] .$cdefdata["additions".$title]." \\\n";
				$options .= "CDEF:inbits".$title."=inoctets".$title .",8,* \\\n";
				$options .= "CDEF:maxinbits".$title."=maxinoctets".$title.",8,* \\\n";
				$options .= "CDEF:maxoutbits".$title."=maxoutoctets".$title.",8,* \\\n";
				$options .= "CDEF:dmaxoutbits".$title."=maxoutbits".$title.",-1,* \\\n";
				//$options .= "CDEF:totinall".$title."=".$cdefdata["totinall".$title] .",+ \\n";
				//$options .= "CDEF:totinall".$title."=".$cdefdata["totinall".$title] .",+ \\n";

				if ((isset($colors)) && (array_key_exists($file,$colors)) && ($colors{$file} != '')) {
				} elseif ($this->colors[$i]) {
					$colors{$title} = $this->colors[$i];
				} else {
					$colors{$title}  = $this->rand_colorCode();
				}

				//$colors{$title}  = $this->rand_colorCode();
				$options_in .= "AREA:inbits$title#". $colors{$title} .":\"In\: ".$orig_title."\\\\n\"";
				$options_out .= "AREA:doutbits$title#". $colors{$title} .":\"Out\: ".$orig_title."\\\\n\"";
				if ($i > 0) {
					$options_in .= ":STACK ";
					$options_out .= ":STACK ";
				} else { 
					$options_in .= " ";
					$options_out .= " ";
				}
				$options_in .= "GPRINT:inbits$title:LAST:\ \ \ \ \ Current\ %6.2lf%s ";
				$options_in .= "GPRINT:inbits$title:AVERAGE:Average\ %6.2lf%s ";
				$options_in .= "GPRINT:maxinbits$title:MAX:Max\ %6.2lf%s\\\\n ";

				$options_out .= "GPRINT:outbits$title:LAST:\ \ \ \ \ Current\ %6.2lf%s ";
				$options_out .= "GPRINT:outbits$title:AVERAGE:Average\ %6.2lf%s ";
				$options_out .= "GPRINT:maxoutbits$title:MAX:Max\ %6.2lf%s\\\\n ";
				$i++;
				
			}
			$options .= "CDEF:octets=".$cdefdata["octets"] .$cdefdata["additions_title"] ." \\\n";
			$options .= "CDEF:inbits=".$cdefdata["inbits"] .$cdefdata["additions_title"] ." \\\n";
			$options .= "CDEF:outbits=".$cdefdata["outbits"] .$cdefdata["additions_title"] ." \\\n";
			$options .= "CDEF:doutbits=".$cdefdata["doutbits"] .$cdefdata["additions_title"] ." \\\n";
			$options .= "CDEF:maxinbits=".$cdefdata["maxinbits"] .$cdefdata["additions_title"] ." \\\n";
			$options .= "CDEF:maxoutbits=".$cdefdata["maxoutbits"] .$cdefdata["additions_title"] ." \\\n";
			$options .= "CDEF:dmaxoutbits=".$cdefdata["dmaxoutbits"] .$cdefdata["additions_title"] ." \\\n";
			$options .= "CDEF:totinall=".$cdefdata["totinall"] .$cdefdata["additions_subtitle"] ." \\\n";
			$options .= "CDEF:totoutall=".$cdefdata["totoutall"] .$cdefdata["additions_subtitle"] ." \\\n";

			$options .= $options_in . $options_out ."";
	

		} else {

		foreach($files as $file => $value) {
			
			$options .= "DEF:\"inoctets$value\"=\"$file\":INOCTETS:AVERAGE ";
			$options .= "DEF:\"outoctets$value\"=\"$file\":OUTOCTETS:AVERAGE ";
			$options .= "DEF:\"maxinoctets$value\"=\"$file\":INOCTETS:MAX ";
			$options .= "DEF:\"maxoutoctets$value\"=\"$file\":OUTOCTETS:MAX ";
		}


		foreach($files as $file => $value) {
			$options .= "CDEF:octets$value=inoctets$value,outoctets$value,ADDNAN ";
		}

		// this has to produce something like
		// "CDEF:octets=octets,octets2,+ \ ";

		$i=0;
		$options .= "CDEF:octets=";
		foreach($files as $file => $value) {
			$options .= "octets$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		foreach($files as $file => $value) {
			$options .= " CDEF:doutoctets$value=outoctets$value,-1,* ";
			$options .= " CDEF:dmaxoutoctets$value=maxoutoctets$value,-1,* ";
		}
		foreach($files as $file => $value) {
			$options .= " CDEF:inbits$value=inoctets$value,8,* ";
			$options .= " CDEF:maxinbits$value=maxinoctets$value,8,* ";
		}

		// CDEF:inbits=inbits1,inbits2,+ \
		$i=0;
		$options .= " CDEF:inbits=";
		foreach($files as $file => $value) {
			$options .= "inbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";
		// CDEF:maxinbits=inbits1,inbits2,+ \
		$i=0;
		$options .= " CDEF:maxinbits=";
		foreach($files as $file => $value) {
			$options .= "maxinbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";
        
		foreach($files as $file => $value) {
			$options .= "CDEF:outbits$value=outoctets$value,8,* ";
			$options .= "CDEF:maxoutbits$value=maxoutoctets$value,8,* ";
		}
		
		// CDEF:outbits=-outbits1,outbits2,+ \
		$i=0;
		$options .= "CDEF:outbits=";
		foreach($files as $file => $value) {
			$options .= "outbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		foreach($files as $file => $value) {
			$options .= "CDEF:doutbits$value=doutoctets$value,8,* ";
			$options .= "CDEF:dmaxoutbits$value=dmaxoutoctets$value,8,* ";
		}
		// CDEF:maxoutbits=maxoutbits1,maxoutbits2,+ \
		$i=0;
		$options .= "CDEF:maxoutbits=";
		foreach($files as $file => $value) {
			$options .= "maxoutbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

        	//  CDEF:doutbits=doutbits1,doutbits2,+ \
		$i=0;
		$options .= "CDEF:doutbits=";
		foreach($files as $file => $value) {
			$options .= "doutbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";
        
		//  CDEF:dmaxoutbits=doutbits1,doutbits2,+ \
		$i=0;
		$options .= "CDEF:dmaxoutbits=";
		foreach($files as $file => $value) {
			$options .= "dmaxoutbits$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		
		//CDEF:totinall=inoctets1,inoctets2,+ \
		$i=0;
		$options .= "CDEF:totinall=";
		foreach($files as $file => $value) {
			$options .= "inoctets$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		$options .= " ";

		// CDEF:totoutall=doutoctets1,doutoctets2,+ \
		$i=0;
		$options .= "CDEF:totoutall=";
		foreach($files as $file => $value) {
			$options .= "doutoctets$value,";
			$i++;
		}
		for($j=1; $j<=$i-1; $j=$j+1) {
			$options .= "ADDNAN";
			if ($i-1 != $j) {
				$options .= ",";
			}
		}
		}


		$options .= " ";

		$options .= "VDEF:totin=totinall,TOTAL ";
		$options .= "VDEF:totout=totoutall,TOTAL ";
		$options .= " VDEF:tot=octets,TOTAL ";
		$options .= " VDEF:95thin=inbits,95,PERCENTNAN ";
		$options .= " VDEF:95thout=outbits,95,PERCENTNAN ";
		$options .= " VDEF:d95thout=doutbits,5,PERCENTNAN ";
                $options .= " VDEF:FirstDate=totinall,FIRST ";
                $options .= " VDEF:LastDate=totinall,LAST ";


		$i =0;  
		foreach($files as $file => $value) {
			//AREA:inbits1#FF0000:In\ Telus\\\n\
			if ((isset($colors)) && (array_key_exists($file,$colors)) && ($colors{$file} != '')) {
			} elseif ($this->colors[$i]) {
				$colors{$file} = $this->colors[$i];
			} else {
				  $colors{$file}  = $this->rand_colorCode();
			}
			$options .= "AREA:inbits$value#". $colors{$file} .":\"In\: ".$orignames{$value}."\\\\n\"";
			#$options .= "AREA:inbits$key#". $this->colors[$i] .":In\ $key\\\\n";
			if ($i > 0) {
				$options .= ":STACK ";
			} else { $options .= " ";}
			
			$options .= "GPRINT:inbits$value:LAST:\ \ \ \ \ Current\ %6.2lf%s  ";
			$options .= "GPRINT:inbits$value:AVERAGE:Average\ %6.2lf%s ";
			$options .= "GPRINT:maxinbits$value:MAX:Max\ %6.2lf%s\\\\n ";
			$i++;
		}
		#print "$options\n"; exit;
		$i =0;  
		foreach($files as $file => $value) {
			$options .= "AREA:doutbits$value#". $colors{$file} .":\"Out\: ".$orignames{$value}."\\\\n\"";
			if ($i > 0) {
				$options .= ":STACK ";
			} else { $options .= " ";}
			$options .= "GPRINT:outbits$value:LAST:\ \ \ \ \ Current\ %6.2lf%s  ";
			$options .= "GPRINT:outbits$value:AVERAGE:Average\ %6.2lf%s ";
			$options .= "GPRINT:maxoutbits$value:MAX:Max\ %6.2lf%s\\\\n ";
			$i++;
		}
		$options .= "COMMENT:\ \\\n ";
		if ($total >0 ) {
			$options .= "COMMENT:\ \ \ \ \ \ \ \ \ \ \ \ \\\\n ";
			$options .= "LINE1.25:0#000000: ";
			$options .= "LINE1.25:inbits#006600:Total\ In\\\\n ";
			$options .= "GPRINT:inbits:LAST:\ \ \ \ Current\ %6.2lf%s  ";
			$options .= "GPRINT:inbits:AVERAGE:Average\ %6.2lf%s ";
			$options .= "GPRINT:maxinbits:MAX:Max\ %6.2lf%s   ";
			$options .= "GPRINT:95thin:95th\ %6.2lf%s\\\\n ";
			$options .= "LINE1.25:doutbits#000099:Total\ Out\\\\n ";
			$options .= "GPRINT:outbits:LAST:\ \ \ \ Current\ %6.2lf%s ";
			$options .= "GPRINT:outbits:AVERAGE:Average\ %6.2lf%s ";
			$options .= "GPRINT:maxoutbits:MAX:Max\ %6.2lf%s ";
			$options .= "GPRINT:95thout:95th\ %6.2lf%s\\\\n ";
			$options .= "GPRINT:tot:Total\ %6.2lf%s ";
			$options .= "GPRINT:totin:\(In\ %6.2lf%s ";
			$options .= "GPRINT:totout:Out\ %6.2lf%s\)\\\\l ";
			$options .= "LINE1:95thin#aa0000 ";
			$options .= "LINE1:d95thout#aa0000 ";
			$options .= "LINE1:maxinbits#aa0000 ";
			$options .= "LINE1:dmaxoutbits#aa0000";
			$options .= " COMMENT:\ \\\\n";
			$options .= " GPRINT:FirstDate:\"From\: %d-%m-%Y %T:strftime\"";
			$options .= " COMMENT:\ \\\\n";
			$options .= " GPRINT:LastDate:\"To\:   %d-%m-%Y %T:strftime\"";
		} else {
			$options .= "LINE1.25:0#000000: ";
			$options .= "LINE1.25:inbits#006600 ";
			$options .= "LINE1.25:doutbits#000099 ";
		}
		
		$cmd = "$this->rrdtool graph - $options";
		#print "<pre>$cmd";exit;
		$result = exec($cmd, $response, $return_code);
		$handle = popen($cmd,"r");
		$output;
		if ($handle) {
			while (!feof($handle)) {
				$buffer = fgets($handle, 4096);
				$output = $output . $buffer;   
				$bytesSent+=strlen($buffer);    /* We know how many bytes were sent to the user */
	
			}
		}
		if ($bytesSent > 0 )  {
			return $output;
		} else {
			//print "$cmd";
			return  false;
		}

	}

	function rand_colorCode(){
		$r = (mt_rand(0,255)); // generate the red component
		$g = (mt_rand(0,255)); // generate the green component
		$b = (mt_rand(0,255)); // generate the blue component
		return sprintf('%02X%02X%02X', $r, $g, $b);
	}


	private function rstrtrim($str, $remove=null) {
		$str    = (string)$str;
		$remove = (string)$remove;   
  		 
		if(empty($remove)) {
			return rtrim($str);
		}
   
		$len = strlen($remove);
		$offset = strlen($str)-$len;
		while($offset > 0 && $offset == strpos($str, $remove, $offset)) {
			$str = substr($str, 0, $offset);
			$offset = strlen($str)-$len;
		}
   
		return rtrim($str);   
	}
   

}

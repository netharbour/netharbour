<?php
include_once 'classes/EdittingTools.php';
include_once 'classes/Form.php';
include_once 'classes/Contact.php';
include_once('classes/RRD.php');
include_once('classes/Device.php');


class SCUAccounting {

	// If you use href's you should use this as a base for 
	private $url = '';

	private function init() {
		$this->url = $_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']; 
		include_once 'classes/Property.php';

		$property = new Property();
		if ($this->rrdtool = $property->get_property("path_rrdtool")) {
		} else {
        		print $property->get_error();
        		exit;
		}
		if ($this->rrd_dir = $property->get_property("path_rrddir")) {
		} else {
        		print $property->get_error();
        		exit;
		}

		if ((!$this->rrdtool) || ($this->rrdtool == '')) {
        		print "Could not find rrdtool";
        		exit;
		}
		if ((!$this->rrd_dir) || ($this->rrd_dir == '')) {
        		print "Could not find rrd_dir";
        		exit;
		}
		return;
	}

        //renders the content
        function get_content() {

		// Firts do a init
		// To determin Url info
		$this->init();
			
		if ($_GET['action'] == 'device_detail') {
			return $this->render_device_detail($_GET['deviceid']);
		} elseif ($_GET['action'] == 'scudcu_detail') {
			return $this->render_profile_detail($_GET['profilename']);
		} elseif ($_GET['action'] == 'graph_profiles') {
			return $this->render_graph_profiles($_GET['graph_profile_id']);


		/* Accounting Profiles */
		} elseif ($_GET['action'] == 'list_accounting_profiles') {
			// List Accounting profiles;
			return $this->render_list_accounting_profiles();
		} elseif ($_GET['action'] == 'new_accounting_profile') {
			if (isset($_POST['addData'])) {
				return $this->insert_new_accounting_profile();
			} else {
				return $this->render_new_accounting_profile();
			}
		} elseif ($_GET['action'] == 'edit_accounting_profile') {
			if (isset($_POST['updateInfo'])) {
				return $this->update_accounting_profile();
			} else {
				return $this->render_edit_accounting_profile();
			}

		} elseif ($_GET['action'] == 'show_accounting_profile') {
			return $this->render_show_accounting_profile();
		} elseif ($_GET['action'] == 'del_accounting_profile') {
			return $this->delete_accounting_profile();
		} elseif ($_GET['action'] == 'show_add_rrd_to_profile') {
			return $this->render_add_rrd_to_profile();
		} elseif ($_GET['action'] == 'add_rrd_to_profile') {
			// Add SCU-DCU accounting RRD file to profile
			$this->add_rrd_to_profile();
			return $this->render_show_accounting_profile();
		} elseif ($_GET['action'] == 'add_device_interface_to_profile') {
			// Add Interface RRD file to profile
			return $this->add_interface_form();
		} elseif ($_GET['action'] == 'del_accounting_source_from_profile') {
			// Remove SCU-DCU accounting RRD file to profile
			$this->del_rrd_from_profile();
			return $this->render_show_accounting_profile();
		} elseif ($_GET['action'] == 'create_report') {
			// Create a report for an individual accounting profile
			return $this->render_create_report();
		} elseif ($_GET['action'] == 'render_multiple_reports') {
			// Render menu for multiple reports
			return $this->render_multiple_reports();
		} elseif ($_GET['action'] == 'render_report') {
			// Create a report for an individual accounting profile
			return $this->render_report();
		} elseif ($_GET['action'] == 'create_multiple_reports') {
			// Render menu for multiple reports
			return $this->create_multiple_reports();
		} elseif ($_GET['action'] == 'save_report') {
			return $this->save_report();
		} elseif ($_GET['action'] == 'show_reports') {
			// Shows reports for a certain profile id
			return $this->show_reports();
		} elseif ($_GET['action'] == 'show_uniq_reports') {
			// Shows all reports
			return $this->render_show_uniq_reports();
		} elseif ($_GET['action'] == 'show_reports_by_name') {
			// Shows all reports
			return $this->render_show_reports_by_name();
		} elseif ($_GET['action'] == 'del_report') {
			return $this->delete_report();
		} elseif ($_GET['action'] == 'delete_reports_by_name') {
			return $this->delete_reports_by_name();
		} elseif ($_GET['action'] == 'render_saved_report') {
			return $this->render_saved_report();
		

		/* End Accounting Profiles */
		
		} elseif ($_GET['action'] == 'graph') {
			return $this->render_graph($_GET['graphid']);
		} elseif ($_GET['action'] == 'render_device_list') {
			return  $this->render_device_list();
		} else {
			return  $this->render_list_accounting_profiles();
			//return  $this->render_device_list();
		}
		return 1;
	}
		
	function render_device_list() {
		// 1st get all the devices that have scu dcu info
		$query = "select distinct accounting_sources.device_id, Devices.name 
			FROM accounting_sources, Devices
			WHERE accounting_sources.device_id = Devices.device_id";
		$result =  mysql_query($query) ;
		if (!$result)  {
			return "<b>No Accounting information found</b>";
        	}

		$content = "<style type='text/css'>#cleantable tr:hover{background:none;}</style>";
		$content .= "<table id=cleantable width='777' valign=top><tr valign=top>";

		// Accounting profiles
		/*
		$content .= "<td witdh='33%'><p> Here you can find all accounting profiles and account reports <br>".
			 "<a href='".$this->url.
			 "&action=list_accounting_profiles'>".
                          "Accounting profiles</a></p></td>";	
		*/
		$content .= " <td witdh='33%'><p>View SCU-DCU statistics per device</p>
		<table><tr><th>Device</th></tr>";
                while ($obj = mysql_fetch_object($result)){
			$content .=  "<tr><td><a href='".$_SERVER['SCRIPT_NAME'].
				"?&action=device_detail&deviceid=$obj->device_id".
				"&tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."'>
				$obj->name</a></td></tr>";
                }

		$content .= "</table> </td><td witdh='33%'> <p>View SCU-DCU graphs</p>";

		// Now SCU profiles
		$query = "select distinct scu_profile 
			FROM accounting_sources";
		$result =  mysql_query($query) ;
		if (!$result)  {
			return "<b>No SCU-DCU profiles found information found</b>";
        	}

		
		$content .= " <table><tr><th>SCU-DCU profiles</th></tr>";
                while ($obj = mysql_fetch_object($result)){
			$content .=  "<tr><td><a href='".$_SERVER['SCRIPT_NAME'].
				"?&action=scudcu_detail&profilename=$obj->scu_profile".
				"&tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']."'>
				$obj->scu_profile</a></td></tr>";
                }

		$content .= " </table> </td></tr></table>";
		return $content;
        }
		
		
	function render_profile_detail($profile) {
		
		$content = "<h1>Accounting profiles for $profile </h1>";

		// Get all scu profiles for this device
		$query = "select title, id, file from accounting_sources
			WHERE scu_profile  = '$profile' 
			ORDER by title ";
		$result =  mysql_query($query) ;
                if (!$result)  {
                        return "<b>No information found for $profile</b>";
                }

		$content .= "<form method=get name='graph_profiles'>
			<INPUT TYPE=hidden NAME=tab VALUE='".$_GET['tab']."'>
			<INPUT TYPE=hidden NAME=pluginID VALUE='".$_GET['pluginID']."'>
			<INPUT TYPE=hidden NAME=action VALUE='graph_profiles'>
			<table><tr><th colspan=2>profiles</th></tr>";
		while ($obj = mysql_fetch_object($result)){
			$content .= "<tr>
				<td><input type=checkbox name=graph_profile_id[] value='$obj->id'></td>
				<td><a href='". $_SERVER['SCRIPT_NAME'].
				"?&action=graph&graphid=$obj->id".
				"&tab=".$_GET['tab']."&pluginID=".$_GET['pluginID'].
				"'> $obj->title</a></td></tr>";
                }
		$content .= " </table><br> ";
		$content .= "<input name='graph_submit' type='submit'
				id='graph_submit' value='Graph Selected' >
				</form>";
		return $content;
	}
	function render_device_detail($id) {
		include_once "classes/Device.php";
		$device = new Device($id);
		$device_name = $device->get_name();
		
		$content = "<h1>Accounting profiles for $device_name </h1>";

		// Get all scu profiles for this device
		$query = "select distinct title, id from accounting_sources
			WHERE device_id = '$id' 
			ORDER by scu_profile ";
		$result =  mysql_query($query) ;
                if (!$result)  {
                        return "<b>No Accounting information found for $device_name</b>";
                }

		$content .= " <table><tr><th>Graph Title</th></tr>";
		while ($obj = mysql_fetch_object($result)){
			$content .= "<tr><td><a href='". $_SERVER['SCRIPT_NAME'].
				"?&action=graph&graphid=$obj->id".
				"&tab=".$_GET['tab']."&pluginID=".$_GET['pluginID'].
				"'> $obj->title</a></td></tr>";
                }
		$content .= " </table> ";
		return $content;

	}
	
	
	function render_graph($graphid) {

		// Get all info for this graph
		$query = "select title, scu_profile, file from accounting_sources
			WHERE id = '$graphid' ";
		$result =  mysql_query($query) ;
                if (!$result)  {
                        return "<b>Graph  information not found </b>";
                }
		$obj = mysql_fetch_object($result);
		$file = $obj->file;
		$title = $obj->title;
		$content = "<h2>SCU DCU Accounting statistics for $title</h2>";
		$content .= "<p><img src='rrdgraph.php?file=accounting/$file&title=$title&height=150&width=900&type=traffic&from=-24h&to=-1s'></p>";
		$content .= "<p><img src='rrdgraph.php?file=accounting/$file&title=$title&height=150&width=900&type=traffic&from=-1w&to=-1s'></p>";
		$content .= "<p><img src='rrdgraph.php?file=accounting/$file&title=$title&height=150&width=900&type=traffic&from=-1m&to=-1s'></p>";
		$content .= "<p><img src='rrdgraph.php?file=accounting/$file&title=$title&height=150&width=900&type=traffic&from=-1y&to=-1s'></p>";
		return $content;
		
	}
	
	function render_graph_profiles($graph_ids) {
		$content = "";
		$url = '';
		$archives = '';
		$files = array();

		#$url = $_SERVER['SCRIPT_NAME'].  "?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID'];
		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			$pid ='';
			#return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		



		foreach ($graph_ids as $graphid) {
			$query = "select title, scu_profile, file from accounting_sources
				WHERE id = '$graphid' ";
			$result =  mysql_query($query) ;
                	if (!$result)  {
                       	#return "<b>Graph  information not found </b>";
                	}
			$obj = mysql_fetch_object($result);
			$file = $obj->file;
			$title = $obj->title;
			$url2 .= "&RRA[accounting/$file]=$title";
			array_push($files,"$this->rrd_dir/accounting/$file");
			$archives{"$this->rrd_dir/accounting/$file"} = $title;

		}

		$profile_name = $this->get_accounting_profile_name($pid);
		// Menu bar
		$content .= "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> <a href='$this->url&pid=$pid&action=show_accounting_profile'>$profile_name</a></div>";
		$content .= "<h2>Quick Graph</h2>";

		// Get summary
		$rrd = new RRD($files,$this->rrdtool);
		$last_day =   mktime() - 24*3600;
		$last_week =  mktime() - 7*24*3600;
		$last_month  = mktime() - 31*24*3600;
		$last_year =  mktime() - 365*24*3600;
		$arr = array(
			"Last Day" => $last_day,
			"Last Week" =>$last_week,
			"Last Month" =>$last_month,
			"Last Year" => $last_year);
		foreach ($arr as $title => $rrd_from) {
				
			$summary = $rrd->get_summary($rrd_from,'-1s',$archives);
			// Determine highest 95%
			$in_95 = $this->si_to_int($summary{'95IN'});
			$out_95 = $this->si_to_int($summary{'95OUT'});

			if ($in_95 > $out_95) {
				$billing_number = $summary{'95IN'};
			} elseif  ($out_95 >= $in_95) {
				$billing_number = $summary{'95OUT'};
			}
		
               		$content .= "<p><table><th colspan=2><h3>$title</h3></th><tr><td>";
                	$content .= "<img src='rrdgraph.php?type=aggr_traf".$url2."&title=$title&height=150&width=700&from=$rrd_from&to=$rrd_to&showtotal=0'>";
               		$content .= "</td><td valign='top'><b><center>Summary</b></center><br>";
                	$content .= "<table valign='top'><tr><th></th><th>In</th><th>Out</th></tr>";
                	$content .= "<tr><td>Average</td><td>".$summary{'AVERAGEIN'}."bs</td><td>".$summary{'AVERAGEOUT'}."bs</td></tr>";
                	$content .= "<tr><td>Max</td><td>".$summary{'MAXIN'}."bs</td><td>".$summary{'MAXOUT'}."bs</td></tr>";
                	$content .= "<tr><td>95th Percentile</td><td>".$summary{'95IN'}."bs</td><td>".$summary{'95OUT'}."bs</td></tr>";
                	$content .= "<tr><td>Total</td><td>".$summary{'TOTALIN'}."B</td><td>".$summary{'TOTALOUT'}."B</td></tr>";
                	$content .= "</table><hr><table>";
                	$content .= "<tr><td>First Measurement <br>sample</td><td>". $summary{'FROM'} ."</td></tr>";
                	$content .= "<tr><td>Last Measurement <br>sample</td><td>". $summary{'TO'} ."</td></tr>";
                	$content .= "<tr><td><p>Total Traffic for <br>this accounting period</td><td>  ".$summary{'TOTAL'} . "B</td></tr>";
                	$content .= "<tr><td>Billed 95th% for <br>for this accounting period</td><td>  ".$billing_number . "bs</td></tr></table>";
                	$content .= "</td></tr></table></p>";
			#$content .= "<p><img src='rrdgraph.php?type=aggr_traf".$url."&title=Aggregated%20Graph&height=150&width=900&from=-1y&to=-1s'></p>";
		}
		return $content;
	}

	function render_list_accounting_profiles() {
		// Include contact class
		$content = "<h1>Accounting Profiles</h1>";
		
		$query = "select profile_id, title, client_id FROM accounting_profiles where archived = '0'";
		$result =  mysql_query($query) ;
                if (!$result)  {
                 	return "<b>Oops something went wrong, unable to select accounting profiles </b>";
                }

		// Tools menu
	
		$tool = new EdittingTools();
		if ($_SESSION['access'] >= 50) {
			$toolNames = array("Add New Accounting Profile", "Create Reports","All Reports","SCU-DCU Graphs");
			$toolIcons = array("add","add","stat","graph");
			$toolHandlers = array("window.location.href='$this->url&action=new_accounting_profile'",
						"window.location.href='$this->url&action=render_multiple_reports'",
						"window.location.href='$this->url&action=show_uniq_reports'",
						"window.location.href='$this->url&action=render_device_list'");
			$content .= $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
		}
	
		$content .= $tool->createNewFilters();

		$content .=" <div style=\"clear:both;\"></div> ";

		$form = new Form("auto",2);
                $heading = array( "Profile name", "Client Name");
                $data = array();
                $handler = array();
                while ($obj = mysql_fetch_object($result)){
			$contact = new Contact($obj->client_id);
			$contact_name = $contact->get_name();
			array_push ($data,$obj->title,$contact_name);
			$url = $this->url ."&action=show_accounting_profile&pid=$obj->profile_id";
			array_push($handler,"handleEvent('$url')");
                }

		$form->setSortable(true); // or false for not sortable
		$form->setHeadings($heading);
		$form->setEventHandler($handler);
		$form->setData($data);
		$form->setTableWidth("678px");

                //set the table size
                #$form->setTableWidth("100%");
                $content .= $form->showForm();
		
		return $content;
	}

	function render_new_accounting_profile() {
		// Include contact class

		$content = "<h1>New Accounting Profiles</h1>";

		$form = new Form("auto", 2);
		$heading = array("New Accounting Profile");

		$titles = array("Name", "Client", "Traffic Cap.tip.Configured cap or Contracted rate.<br>Examples 100M, 2M, 1G, 155M, 800K","Notes");
		$post_keys = array("Name", "Client", "TrafficCap","Notes");

		$allGroups = Contact::get_groups();

		$form->setType($allGroups); // Drop down
		$fieldType = array("", "drop_down", "","text_area");
		$form->setFieldType($fieldType);

     		$form->setSortable(false);
     		$form->setHeadings($heading);
     		$form->setTitles($titles);
     		$form->setDatabase($post_keys);

     		//set the table size
     		$form->setTableWidth("1024px");
 
    		$form->setTitleWidth("20%");
    		$content .= $form->NewForm(1);

		return $content;
	}
	function render_edit_accounting_profile() {
		// Include contact class
		
		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		$allGroups = Contact::get_groups();

		$query = "Select title, client_id, notes, traffic_cap
			FROM accounting_profiles
			WHERE profile_id = '$pid'";

		$result =  mysql_query($query) ;
		if (!$result)  {
			return "<b>Sorry something went wrong</b>". mysql_error() . $query;
        	}
                $obj = mysql_fetch_object($result);
		$thisGroup = new Contact($obj->client_id);
		$groupName = $thisGroup->get_name();
		$values = array($obj->title, "$groupName", $this->int_to_si($obj->traffic_cap), $obj->notes);

		$content .=  "<h1>Accounting Profiles</h1>";

		$form = new Form("auto", 2);
		$heading = array("Edit Accounting Profile");
		$titles = array("Name", "Client", "Traffic Cap.tip.Configured cap or Contracted rate.<br>Examples 100M, 2M, 1G, 155M, 800K","Notes");
		$post_keys = array("Name", "Client", "TrafficCap","Notes");

		$form->setType($allGroups); // Drop down
		$fieldType = array(1=>"drop_down",3=>"text_area");
		$form->setFieldType($fieldType);


     		$form->setSortable(false);
     		$form->setHeadings($heading);
     		$form->setTitles($titles);
     		$form->setDatabase($post_keys);
		$form->setData($values);

     		//set the table size
     		$form->setTableWidth("1024px");
 
    		$form->setTitleWidth("20%");
    		$content .= $form->EditForm(1);
		return $content;
	}

	function del_rrd_from_profile() {
		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}

		$accounting_src = $_GET['asrc'];
		$query = "delete FROM accounting_profiles_files WHERE
				profile_id = '$pid' AND
				accounting_source  = '$accounting_src'";
		$result =  mysql_query($query) ;
                if (!$result)  {
			print "Unable to  delete $accounting_src from profile id $pid -> ". mysql_error() ."<br>";
                }
	}
		
	function add_rrd_to_profile() {

		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}

		$files = $_GET['scu_files'];
		foreach ($files as $id) {
			$query = "INSERT INTO accounting_profiles_files SET
				profile_id = '$pid',
				accounting_source   = '$id'";
			$result =  mysql_query($query) ;
                        if (!$result)  {
				print "Unable to add file $id to profile id $pid -> ". mysql_error() ."<br>";
                        }
		}
	}
		
	function render_add_rrd_to_profile() {

		include_once "classes/Device.php";
		$device_name_cache = array();

		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		
		$content = "";
		
		// Get all SCU-DCU files
		$query = "select id, device_id, title, file 
			FROM accounting_sources ";
		$result =  mysql_query($query) ;
		if (!$result)  {
			return "<b>No SCU-DCU profiles found information found</b>";
        	}
		$content .=  "<p>";
		$tool = new EdittingTools();
		$content .= $tool->createNewFilters();
		$content .=  "<div style=\"clear:both;\"></div> </p>";
		
                $content .= "<form method=get name='graph_profiles'>
			<INPUT TYPE=hidden NAME=tab VALUE='".$_GET['tab']."'>
			<INPUT TYPE=hidden NAME=pluginID VALUE='".$_GET['pluginID']."'>
			<INPUT TYPE=hidden NAME=pid VALUE='$pid'>
			<INPUT TYPE=hidden NAME=action VALUE='add_rrd_to_profile'>";

		$keyData=array();
                while ($obj = mysql_fetch_object($result)){
			if (! array_key_exists($obj->device_id,$device_name_cache)) {
				$device = new Device($obj->device_id);
				$device_name_cache{$obj->device_id} = $device->get_name();
			}
			//$content .= "<tr><td><input type=checkbox name=scu_files[] value='$obj->id'></td>";
			//$content .=  "<td>". $device_name_cache{$obj->device_id} ."</td><td>
			//	$obj->title</a></td></tr>";
			array_push($keyData,"<input type='checkbox' name='scu_files[]' value='$obj->id'>");
                        array_push($keyData,$device_name_cache{$obj->device_id});
                        array_push($keyData,$obj->title);

                }

		$headings = array(" ","Device","SCU-DCU profile");
		$form = new Form();
		$form->setCols(3);
		$form->setTableWidth("auto");
		$form->setData($keyData);
		$form->setHeadings($headings);
		$form->setSortable(true);
		$content .= $form->showForm();
		$content .= "<div style='clear:both'></div>";

		$content .= "<input name='add_rrd' type='submit'
			id='graph_submit' value='Add Selected Graphs to profile' >
			</form>";

		return $content;
	}

	function save_report() {
		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		if ((isset($_GET['from'])) && (is_numeric($_GET['from']))) {
			$from = $_GET['from'];
		} else {
			return "Invalid from date";
		}
		if ((isset($_GET['to'])) && (is_numeric($_GET['to']))) {
			$to = $_GET['to'];
		} else {
			return "Invalid to date";
		}
			
		if ((isset($_GET['report_name'])) && ($_GET['report_name'] != '')) {
			$name = $_GET['report_name'];
		} else { 
			return "Invalid name";
		}
		$report_id = $this->insert_report($pid,$from,$to,$name);
		if (!is_numeric($report_id)) {
			$content =  "<p><b>Failed to save report</b></p>";
			return $content;
		}
		$content = "<meta http-equiv=\"REFRESH\" content=\"0;url=$this->url&action=show_reports&pid=$pid\">";
		return $content;
	}

	function insert_report($pid,$from,$to,$name) {
		if ($name =='') {
			print "Invalid name";
			return false;
		}
		if (! is_numeric($pid)) {
			return false;
		}
			
		$rrd_from = $from;
		$rrd_to = $to;
		if (!is_numeric($rrd_from)) {
			print "Invalid from date";	
			return false;
		}
		if (!is_numeric($rrd_to)) {
			print "Invalid to date";	
			return false;
		}
		// Now render data
		$archives = '';
		$files = array();

		$query = "select accounting_profiles.traffic_cap, accounting_sources.title, accounting_sources.scu_profile, 
			accounting_sources.file 
			FROM accounting_sources, accounting_profiles_files, accounting_profiles
			WHERE accounting_sources.id = accounting_profiles_files.accounting_source 
			AND accounting_profiles.profile_id = '$pid'
			AND accounting_profiles_files.profile_id = '$pid' ";
		$result =  mysql_query($query) ;
                if (!$result)  {		
			print "failed to execute query $query<br>";
			return false;
                }
		while ($obj = mysql_fetch_object($result)){
			$file = $obj->file;
			$title = $obj->title;
			if ($obj->traffic_cap) {
				$sql_traffic_cap = "traffic_cap = $obj->traffic_cap";
			} else {
				$sql_traffic_cap = "traffic_cap = NULL";
			}
			$url2 .= "&RRA[accounting/$file]=$title";
			array_push($files,"$this->rrd_dir/accounting/$file");
			$archives{"$this->rrd_dir/accounting/$file"} = $title;

		}
		$rrd = new RRD($files,$this->rrdtool);
		$summary = $rrd->get_summary($rrd_from,$rrd_to,$archives);
		#print_r($summary);

		$avg_in = $this->si_to_int($summary{'AVERAGEIN'});
		$avg_out = $this->si_to_int($summary{'AVERAGEOUT'});
		$max_in = $this->si_to_int($summary{'MAXIN'});
		$max_out = $this->si_to_int($summary{'MAXOUT'});
		$in_95 = $this->si_to_int($summary{'95IN'});
		$out_95 = $this->si_to_int($summary{'95OUT'});
		$tot_in = $this->si_to_int($summary{'TOTALIN'});
		$tot_out = $this->si_to_int($summary{'TOTALOUT'});
		$sample_date1 = $summary{'FROM'};
		$sample_date2 = $summary{'TO'};
		
		// This is to get the timestamp of the 1st and last sample
		// They come in this format 14-04-2010 00:00:00
		$datetime1 = date_create($sample_date1);
		$timestamp1 = $datetime1->format('U');
		$datetime2 = date_create($sample_date2);
		$timestamp2 = $datetime2->format('U');

		/* Ok now generate graph */

		//https://nms.bc.net/cmdb/rrdgraph.php?type=aggr_traf&RRA[accounting/deviceid4_Akamai%20--%20EX%20208%20Telus%20Transit.rrd]=Akamai%20--%20EX%20208%20Telus%20Transit&RRA[accounting/deviceid4_Akamai%20--%20CU%20248%20Shaw%20Commodity%20Transit%20in%20Vancouver.rrd]=Akamai%20--%20CU%20248%20Shaw%20Commodity%20Transit%20in%20Vancouver&RRA[accounting/deviceid9_Akamai%20--%20CU%2090%20Peer1-Transit.rrd]=Akamai%20--%20CU%2090%20Peer1-Transit&title=Aggregated%20Graph&height=150&width=700&from=1271142000&to=1271314800

	        $graph_params = array(
                'type' => 'aggr_traf', 
                'archives' => $archives,
		'total' => 0,
                'title' => $name,
                'width' => "700",
                'height' => "150",
                'start' => $rrd_from,
                'end' => $rrd_to);
        	$rrd->get_graph($graph_params) ;
		#$rrd->print_graph();
		$img = $rrd->get_graph_img();
		#print "size is ".getimagesize($img);
		$img = base64_encode($rrd->get_graph_img());
		#$img=mysql_real_escape_string($img);

		$query = "Insert into accounting_reports SET
			profile_id = '$pid', 	report_name  = '$name',
			avg_in = '$avg_in', avg_out = '$avg_out',
			max_in = '$max_in', max_out = '$max_out',
			95_in = '$in_95', 95_out = '$out_95',
			tot_in = '$tot_in', tot_out = '$tot_out',
			date1 = FROM_UNIXTIME( '$from' ), date2 = FROM_UNIXTIME( '$to' ),
			sample_date1 = FROM_UNIXTIME( '$timestamp1' ), sample_date2 = FROM_UNIXTIME( '$timestamp2' ),
			`img_file` = '$img', $sql_traffic_cap
			";
		$result =  mysql_query($query) ;
		if (!$result)  {
			 print mysql_error() ;
			return false;
		}
		$new_report_id = mysql_insert_id();
			
		return $new_report_id;
	}
	
	function create_multiple_reports() {
		if ((isset($_GET['date1'])) ) {
			list($year1, $month1, $day1) = split("-", $_GET['date1']);
			//list($day1, $month1, $year1) = split("-", $_GET['date1']);
			$rrd_from = @mktime(0,0,0,$month1,$day1,$year1);
			if (! is_numeric($rrd_from)) {
				return "Unable to parse from date";
			}
		} else {
			return "Invalid from date";
		}
		if ((isset($_GET['date2'])) ) {
			//list($day2, $month2, $year2) = split("/", $_GET['date2']);
			list($year2, $month2, $day2) = split("-", $_GET['date2']);
			$rrd_to = @mktime(0,0,0,$month2,$day2,$year2);
			if (! is_numeric($rrd_to)) {
				return "Unable to parse To date";
			}
		} else {
			return "Invalid to date";
		}
			
		if ((isset($_GET['name'])) && ($_GET['name'] != '')) {
			$name = $_GET['name'];
		} else { 
			return "Invalid name ". $_GET['name'] ."<br>";
		}
		$warning = false;

		$content = "<table><tr><th>Accounting Profile</th><th>Report Name</th><th>Start date</th<th>End date</th>";
		$content .= "<th>Average In</th><th>Average out</th<th>Max In</th><th>Max out</th>";
		$content .= "<th>95% In</th><th>95% out</th<th>95% Billing</th><th>Total In</th><th>Total out</th>";
		$content .= "<th>First Sample date</th><th>Last Sample date</th><th>Graph</th></tr>";

		foreach($_GET['profile_id'] as $pid) {
			if (!is_numeric($pid)) {
				print "Invalid profile_id $pid<br>";
				continue;
			}
			$new_id = $this->insert_report($pid,$rrd_from,$rrd_to,$name);
			
			if ($new_id) {
			} else {
				$warning .= "Failed to inserted report for profile $pid<br>";
			}
		}
		//$content .= "</table>";
		if ($warning) {
			return "There were warnings: <br> $warning . <a href='$this->url&action=show_reports_by_name&report_name=$name'> See report results</a>";
		}
		else {
			return "<meta http-equiv=\"REFRESH\" content=\"0;url=$this->url&action=show_reports_by_name&report_name=$name\">";
		}
	}	

	function render_show_uniq_reports() {
		// Menu bar
		$form = new Form("auto",3);
		$heading = array("Report Name", "Number of reports", "Create Date");
		$data = array();
		$handler = array();


		$content = "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> 
			<a href='$this->url&action=show_uniq_reports'>Unique Reports</a> </div><br>";

		#$content .= "<table><tr><th>Report Name</th><th>Number of reports</th><th>Create Date</th></tr>";

		$query = "select report_name, count(id) as c1, create_date
			from accounting_reports 
			group by report_name
			order by create_date desc, date2 desc, date1 desc";
		$result =  mysql_query($query) ;
                if (!$result)  {	
			print "failed to execute query $query<br>";
                }
		while ($obj = mysql_fetch_object($result)){
			#$content .= "<tr onclick=\"handleEvent('$this->url&action=show_reports_by_name&report_name=".$obj->report_name."')\">";
			#$content .= "<td>". $obj->report_name ."</td> 
			#	<td>". $obj->c1 ."</td>
			#	<td>". $obj->create_date ."</td></tr>";
			#array_push ($title,$obj->report_name);
			array_push ($data,$obj->report_name,$obj->c1,$obj->create_date);
			array_push ($handler,"handleEvent('".$this->url."&action=show_reports_by_name&report_name=".$obj->report_name."')");
			#array_push($keyHandlers, "handleEvent('devices.php?action=showID&ID=$id')");

		}
		#$content .="</table>";
		$form->setSortable(true); // or false for not sortable
		$form->setHeadings($heading);
		$form->setEventHandler($handler);
		#$form->setTitles($titles);
		$form->setData($data);

		//set the table size
		$form->setTableWidth("1024px");
		$form->setTitleWidth("20%");
		$content .= $form->showForm();

		return $content;
	}

	function delete_reports_by_name() {
		// Menu bar
		$form = new Form();
		if ((isset($_GET['report_name'])) && ($_GET['report_name'] != '')) {
			$name = $_GET['report_name'];
		} else {
			return "<b>Sorry invalid report name ". $_GET['report_name'] ."</b>";
		}
		$content = "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> 
			<a href='$this->url&action=show_uniq_reports'>Unique Reports</a> 
			>> $name </div><p>";


		// Confimration part
		if(isset($_POST['deleteYes'])) {
			// Delete
			$query = "delete from accounting_reports where report_name = '$name'";
			$result =  mysql_query($query) ;
			if (!$result)  {
				print "failed to execute query $query<br>";
				return;
			}
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$this->url."&action=show_uniq_reports\">";
		}
		//if the user does not confirm, then refrest to the current ID
		else if(isset($_POST['deleteNo'])) {
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$this->url."&action=show_reports_by_name&report_name=$name\">";
		} else {
			// Count lines and confirm
			$query = "select id, profile_id, report_name, date1, date2, 
			avg_in, avg_out, max_in, max_out,95_in as in95 ,95_out as out95 ,tot_in,tot_out,
			sample_date1, sample_date2, traffic_cap
			from accounting_reports where report_name = '$name'";
			$result =  mysql_query($query) ;
			if (!$result)  {
				print "failed to execute query $query<br>";
				return;
			}
			$num_rows = mysql_num_rows($result);
			$form->prompt("Are you sure you want to delete <i>'$name'</i>? It contains $num_rows reports.");
		}
		
	}

	
	function render_show_reports_by_name() {
		// Menu bar
		if ((isset($_GET['report_name'])) && ($_GET['report_name'] != '')) {
			$name = $_GET['report_name'];
		} else {
			return "<b>Sorry invalid report name ". $_GET['report_name'] ."</b>";
		}
		$content = "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> 
			<a href='$this->url&action=show_uniq_reports'>Unique Reports</a> 
			>> $name </div> <p>";

	
		$tool2 = new EdittingTools();
		if ($_SESSION['access'] >= 50) {
			$toolNames = array("Export to CSV","Delete");
			$toolIcons = array("icons/checklist.png","delete");
			$toolHandlers = array(
				"window.location.href='plugins/SCU-Accounting/export_csv.php?report_name=$_GET[report_name]'",
				"window.location.href='$this->url&action=delete_reports_by_name&report_name=$_GET[report_name]&pid=$pid'"
			);
		} else {
			$toolNames = array("Export to CSV");
			$toolIcons = array("export");
			$toolHandlers = array(
				"window.location.href='plugins/SCU-Accounting/export_csv.php?report_name=$_GET[report_name]'",
			);
		}
		$content .=  $tool2->createNewTools($toolNames, $toolIcons, $toolHandlers);

	

		$query = "select id, profile_id, report_name, date1, date2, 
			avg_in, avg_out, max_in, max_out,95_in as in95 ,95_out as out95 ,tot_in,tot_out,
			sample_date1, sample_date2, traffic_cap
			from accounting_reports where report_name = '$name' order by ";
		$query = "select accounting_reports.id, accounting_reports.profile_id, accounting_reports.report_name, 
			accounting_reports.date1, accounting_reports.date2, 
			accounting_reports.avg_in, accounting_reports.avg_out, 
			accounting_reports.max_in, accounting_reports.max_out,95_in as in95,
			accounting_reports.95_out as out95 , accounting_reports.tot_in, accounting_reports.tot_out,
			accounting_reports.sample_date1,   accounting_reports.sample_date2, 
			accounting_reports.traffic_cap, accounting_profiles.client_id
			from 
			accounting_reports, accounting_profiles where report_name = '$name'
			AND accounting_profiles.profile_id =  accounting_reports.profile_id";

		$result =  mysql_query($query) ;
		if (!$result)  {
			print "failed to execute query $query<br>";
		}
		$form = new Form("auto",18);
		$heading = array( "Graph", "Client","Accounting Profile", "Report Name", "Start date", "End date", 
				"Average In", "Average out", "Max In", "Max out", "95% In",
				"95% out","95% Billing","Committed Traffic","Total In","Total out","First Sample date",
				"Last Sample date");
		$data = array();
		$handler = array();


		while ($obj = mysql_fetch_object($result)){
			$pid = $obj->profile_id;
			if ($obj->in95 > $obj->out95) {
				$bill95 = $obj->in95;
			} else {
				$bill95 = $obj->out95;
			}

			// get client name
			$contact_name = "N/A";
			$client_id = $obj->client_id;
			if (!is_null($client_id)) {
				$contact = new Contact($client_id);
				$contact_name = $contact->get_name();
				if ($contact_name != '') {
				} else {
					$contact_name = "N/A";
				}
			}

			$profile_name =  $this->get_accounting_profile_name($pid);
			$avg_in = $this->int_to_si($obj->avg_in)."bs";
			$avg_out = $this->int_to_si($obj->avg_out)."bs";
			$max_in = $this->int_to_si($obj->max_in)."bs";
			$max_out = $this->int_to_si($obj->max_out)."bs";
			$in95 = $this->int_to_si($obj->in95)."bs";
			$out95 = $this->int_to_si($obj->out95)."bs";
			$bill95_orig = $bill95;
			$bill95 = $this->int_to_si($bill95) ."bs";
			if ($obj->traffic_cap) {
				if ($obj->traffic_cap >= $bill95_orig) {
					$traffic_cap = "<font color=green>". $this->int_to_si($obj->traffic_cap) ."bs</font>";
				} else {
					$traffic_cap = "<font color=orange>". $this->int_to_si($obj->traffic_cap) ."bs</font>";
				}
			} else {
				$traffic_cap = 'N/A';
			}
			$tot_in = $this->int_to_si($obj->tot_in)."B";
			$tot_out = $this->int_to_si($obj->tot_out)."B";
			$imglink ="plugins/SCU-Accounting/sql_img.php?report_id=".$obj->id;
			array_push ($data,
				"<a class='screenshot' title='Statistics' rel='$imglink'><img src='$imglink' height=20 width=44></a>",
				$contact_name, $profile_name, $obj->report_name, $obj->date1, $obj->date2, $avg_in, $avg_out, $max_in, $max_out,
				$in95, $out95, $bill95, $traffic_cap, $tot_in, $tot_out,
				$obj->sample_date1, $obj->sample_date2
			);
			array_push($handler,"handleEvent('".$this->url."&action=render_saved_report&pid=$pid&report_id=".$obj->id."')");
	
		}
		#$content .="</table>";
		$form->setSortable(true); // or false for not sortable
		$form->setHeadings($heading);
		$form->setEventHandler($handler);
		$form->setData($data);

                //set the table size
		$form->setTableWidth("100%");
		$content .= $form->showForm();
	
		return $content;
	}


	function show_reports() {
		$content = '';
		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}

		$profile_name = $this->get_accounting_profile_name($pid);
		// Menu bar
		$content .= "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> <a href='$this->url&pid=$pid&action=show_accounting_profile'>$profile_name</a>
			>> Reports</div>";

		$query = "select  id, report_name, date1, date2, 
			avg_in, avg_out, max_in, max_out,95_in as in95 ,95_out as out95 ,tot_in,tot_out,
			sample_date1, 	sample_date2
			from accounting_reports where profile_id = '$pid'";
		$result =  mysql_query($query) ;
                if (!$result)  {		
			print "failed to execute query $query<br>";
			return false;
                }
		$content .= "<h2>$profile_name reports</h2>";

		$form = new Form("auto",16);
		$heading = array( "Delete", "Graph", "Report Name", "Start date", "End date",
                                "Average In", "Average out", "Max In", "Max out", "95% In",
                                "95% out","95% Billing","Total In","Total out","First Sample date",
                                "Last Sample date");
                $data = array();
                $handler = array();

		#$content .= "<p><div align=right><a href='$this->url&pid=$pid&action=create_report'>Create new report</a></div></p>";
		#$content .= "<table><tr><th>Delete</th><th>Report Name</th><th>Start date</th<th>End date</th>";
		#$content .= "<th>Average In</th><th>Average out</th<th>Max In</th><th>Max out</th>";
		#$content .= "<th>95% In</th><th>95% out</th<th>95% Billing</th><th>Total In</th><th>Total out</th>";
		#$content .= "<th>First Sample date</th><th>Last Sample date</th><th>Graph</th></tr>";
		while ($obj = mysql_fetch_object($result)){
			if ($obj->in95 > $obj->out95) {
				$bill95 = $obj->in95;
			} else {
				$bill95 = $obj->out95;
			}
			/*
			$content .= "<tr onclick=\"handleEvent('$this->url&action=render_saved_report&pid=$pid&report_id=".$obj->id."')\">
				<td><a href='$this->url&action=del_report&report_id=".$obj->id."&pid=$pid'>
				<img src='icons/Delete.png' height=17></a></td>";
			$content .= "<td>$obj->report_name</td><td>$obj->date1</td>";
			$content .= "<td>$obj->date2</td>";
			$content .= "<td>". $this->int_to_si($obj->avg_in)."bs</td>";
			$content .= "<td>". $this->int_to_si($obj->avg_out) ."bs</td>";
			$content .= "<td>". $this->int_to_si($obj->max_in) ."bs</td>";
			$content .= "<td>". $this->int_to_si($obj->max_out) ."bs</td>";
			$content .= "<td>". $this->int_to_si($obj->in95) ."bs</td>";
			$content .= "<td>". $this->int_to_si($obj->out95) ."bs</td>";
			$content .= "<td>". $this->int_to_si($bill95) ."bs</td>";
			$content .= "<td>". $this->int_to_si($obj->tot_in)."B</td>";
			$content .="<td>". $this->int_to_si($obj->tot_out) ."B</td>";
			$content .= "<td>$obj->sample_date1</td><td>$obj->sample_date2</td>";
			$content .= "<td><img src=plugins/SCU-Accounting/sql_img.php?report_id=".$obj->id."height=10 width=40></td>";
			$content .= "</tr>";
			*/
			$profile_name =  $this->get_accounting_profile_name($pid);
			$avg_in = $this->int_to_si($obj->avg_in)."bs";
			$avg_out = $this->int_to_si($obj->avg_out)."bs";
			$max_in = $this->int_to_si($obj->max_in)."bs";
			$max_out = $this->int_to_si($obj->max_out)."bs";
			$in95 = $this->int_to_si($obj->in95)."bs";
			$out95 = $this->int_to_si($obj->out95)."bs";
			$bill95 = $this->int_to_si($bill95) ."bs";
			$tot_in = $this->int_to_si($obj->tot_in)."B";
			$tot_out = $this->int_to_si($obj->tot_out)."B";
			$imglink ="plugins/SCU-Accounting/sql_img.php?report_id=".$obj->id;

			array_push ($data,
				"<a href='$this->url&action=del_report&report_id=".$obj->id."&pid=$pid'><img src='icons/Delete.png' height=17></a>",
				"<a class='screenshot' title='Statistics' rel='$imglink'><img src='$imglink' height=20 width=44></a>",
				$obj->report_name,
				$obj->date1, $obj->date2, $avg_in, $avg_out, $max_in, $max_out,
                                $in95, $out95, $bill95, $tot_in, $tot_out,
                                $obj->sample_date1, $obj->sample_date2
			);
		}
		$form->setSortable(true); // or false for not sortable
		$form->setHeadings($heading);
		$form->setEventHandler($handler);
		$form->setData($data);

                //set the table size
                $form->setTableWidth("100%");
                $content .= $form->showForm();
		return $content;

	}

	function delete_report() {
		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		if ((isset($_GET['report_id'])) && (is_numeric($_GET['report_id']))) {
			$report_id = $_GET['report_id'];
		} else {
			return "<b>Sorry invalid report_id ". $_GET['report_id'] ."</b>";
		}

		$profile_name = $this->get_accounting_profile_name($pid);
		// Menu bar
		$content .= "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> <a href='$this->url&pid=$pid&action=show_accounting_profile'>$profile_name</a></div>";
		$content .= '<h2>Accounting Report</h2>';
	
		// Confimration part
		if ((! isset($_GET['confirm'])) || (($_GET['confirm'] !='no') && ($_GET['confirm'] !='yes'))) {
			return "$content  <b>Are you sure your want to delete this report?</b><table><tr>
				<td><a href='$this->url&action=del_report&report_id=$report_id&pid=$pid&confirm=yes'> Yes </a></td>
				<td><a href='$this->url&action=del_report&report_id=$report_id&pid=$pid&confirm=no'> No </a></td>
				</tr></table>
			";
		} elseif ($_GET['confirm'] =='no') {
			// return
			 return "<meta http-equiv=\"REFRESH\" content=\"0;url=$this->url&action=show_reports&pid=$pid\">";
		}
		
		$query = "Delete from accounting_reports 
			WHERE profile_id = '$pid' AND
			id = '$report_id' limit 1";
		
		$result =  mysql_query($query) ;
                if (!$result)  {		
			print "failed to execute query $query<br>";
			return false;
                }
		return "<meta http-equiv=\"REFRESH\" content=\"0;url=$this->url&action=show_reports&pid=$pid\">";
	}

	function render_saved_report() {
		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		if ((isset($_GET['report_id'])) && (is_numeric($_GET['report_id']))) {
			$report_id = $_GET['report_id'];
		} else {
			return "<b>Sorry invalid report id ". $_GET['report_id'] ."</b>";
		}

		$profile_name = $this->get_accounting_profile_name($pid);
		// Menu bar
		$content .= "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> <a href='$this->url&pid=$pid&action=show_accounting_profile'>$profile_name</a>
			>> <a href='$this->url&pid=$pid&action=show_reports''> Reports</a> >> View report</div>";
		$content .= '<h2>Accounting Report</h2>';


		$query = "select report_name, avg_in, avg_out, max_in, max_out, 95_in as in95, 95_out as out95,
				tot_in, tot_out, date2, date1, sample_date1, sample_date2
			FROM accounting_reports
			WHERE profile_id = '$pid'  AND id = '$report_id'";
		$result =  mysql_query($query) ;
                if (!$result)  {
                	return "<b>Graph  information not found </b>";
		}
		if (mysql_num_rows($result) < 1) {
                	return "<b>Report  information not found </b>";
		}	

		$obj = mysql_fetch_object($result);

		if ($obj->in95 > $obj->out95) {
			$billing_number = $obj->in95;
		} else {
			$billing_number = $obj->out95;
		}
		$total_traffic = $obj->tot_out + $obj->tot_in;
		
		$content .= "<style type='text/css'>#cleantable tr:hover{background:none;}</style>";
		$content .= "<table id='cleantable'><th colspan=2><b><h2> $obj->report_name </h2>Reporting Period $obj->date1 - $obj->date2</b></th><tr><td>";
		$content .= "<img src=plugins/SCU-Accounting/sql_img.php?report_id=".$report_id."></td>";
		$content .= "</td><td valign='top'><b><center>Summary</b></center><br>";
		$content .= "<table valign='top'><tr><th></th><th>In</th><th>Out</th></tr>";
		$content .= "<tr><td>Average</td><td>".$this->int_to_si($obj->avg_in)."bs</td><td>".$this->int_to_si($obj->avg_out)."bs</td></tr>";
		$content .= "<tr><td>Max</td><td>".$this->int_to_si($obj->max_in)."bs</td><td>".$this->int_to_si($obj->max_out)."bs</td></tr>";
		$content .= "<tr><td>95th Percentile</td><td>".$this->int_to_si($obj->in95)."bs</td><td>".$this->int_to_si($obj->out95)."bs</td></tr>";
		$content .= "<tr><td>Total</td><td>".$this->int_to_si($obj->tot_in)."B</td><td>".$this->int_to_si($obj->tot_out)."B</td></tr>";
		$content .= "</table><hr><table>";
		$content .= "<tr><td>First Measurement <br>sample</td><td>". $obj->sample_date1 ."</td></tr>";
		$content .= "<tr><td>Last Measurement <br>sample</td><td>". $obj->sample_date2 ."</td></tr>";
		$content .= "<tr><td><p>Total Traffic for <br>this accounting period</td><td>  ".$this->int_to_si($total_traffic) . "B</td></tr>";
		$content .= "<tr><td>Billed 95th% for <br>for this accounting period</td><td>  ".$this->int_to_si($billing_number) . "bs</td></tr></table>";
		$content .= "</td></tr></table>";
		return $content;
	}

	function render_report() {
		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}

		$profile_name = $this->get_accounting_profile_name($pid);
		// Menu bar
		$content .= "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> <a href='$this->url&pid=$pid&action=show_accounting_profile'>$profile_name</a></div>";
		$content .= '<h2>Accounting Report</h2>';


		$from = $_GET[date1];
		$to = $_GET[date2];
		

			
		// Now render data
		$archives = '';
		$files = array();


		$query = "select accounting_sources.title, accounting_sources.scu_profile, 
			accounting_sources.file 
			FROM accounting_sources, accounting_profiles_files
			WHERE accounting_sources.id = accounting_profiles_files.accounting_source 
			AND accounting_profiles_files.profile_id = '$pid' ";
		$result =  mysql_query($query) ;
                if (!$result)  {
                	return "<b>Graph  information not found </b>";
                }
		while ($obj = mysql_fetch_object($result)){
			$file = $obj->file;
			$title = $obj->title;
			$url2 .= "&RRA[accounting/$file]=$title";
			array_push($files,"$this->rrd_dir/accounting/$file");
			$archives{"$this->rrd_dir/accounting/$file"} = $title;

		}
		// Get summary
		list($year1, $month1, $day1) = split("-", $from);
		list($year2, $month2, $day2) = split("-", $to);
		$rrd_from = @mktime(0,0,0,$month1,$day1,$year1);
		#$rrd_from = "12am $month1/$day1/$year1";
		$rrd_to = @mktime(0,0,0,$month2,$day2,$year2);
		// -1 sec, so it aligns perfect
		$rrd_to = $rrd_to -1;

		#print " $rrd_from  $rrd_to ->";
		#echo date("i:H M/d/Y",$rrd_from);
		if (!is_numeric($rrd_from)) {
			return "Invalid From date";
		}
		if (!is_numeric($rrd_to)) {
			return "Invalid To date";
		}
		$rrd_width = round(($rrd_to - $rrd_from) /300) + 10;
		$rrd = new RRD($files,$this->rrdtool);
		$summary = $rrd->get_summary($rrd_from,$rrd_to,$archives);

		// Determine highest 95% number => Billing
		$billing_number = "N/A";

		$in_95 = $this->si_to_int($summary{'95IN'});
		$out_95 = $this->si_to_int($summary{'95OUT'});

		if ($in_95 > $out_95) {
			$billing_number = $summary{'95IN'};
		} elseif  ($out_95 >= $in_95) {
			$billing_number = $summary{'95OUT'};
		}

		/* if ($summary{'95IN'} > $summary{'95OUT'}) {
			$billing_number = $summary{'95IN'};
		} elseif  ($summary{'95OUT'} > $summary{'95IN'}) {
			$billing_number = $summary{'95OUT'};
		} */
		
		$content .= "<p><form><b>Save this report as: </b><input type=hidden name=pid value=$pid>
				<input type=hidden name=pluginID value=".$_GET['pluginID'].">
				<input type=hidden name=tab value=".$_GET['tab'].">
				<input type=hidden name=from value=$rrd_from>
				<input type=hidden name=to value=$rrd_to>
				<input type=hidden name=action value=save_report>
				<input type=text name=report_name id=report_name size='40'>";
		$content .= " <input type='submit' name='mysubmit' value='Save Report'></form></p> <br>  ";
		#$content.= "<a href='$url&action=save_report&pid=$pid&from=$rrd_from&to=$rrd_to'><b>Save Report</b></a>";
		$content .= "<table><th colspan=2><b>Reporting Period $from - $to</b></th><tr><td>";
		$content .= "<img src='rrdgraph.php?type=aggr_traf".$url2."&title=Reporting Period $from - $to&height=150&width=700&from=$rrd_from&to=$rrd_to&showtotal=0'>";
		$content .= "</td><td valign='top'><b><center>Summary</b></center><br>";
		$content .= "<table valign='top'><tr><th></th><th>In</th><th>Out</th></tr>";
		$content .= "<tr><td>Average</td><td>".$summary{'AVERAGEIN'}."bs</td><td>".$summary{'AVERAGEOUT'}."bs</td></tr>";
		$content .= "<tr><td>Max</td><td>".$summary{'MAXIN'}."bs</td><td>".$summary{'MAXOUT'}."bs</td></tr>";
		$content .= "<tr><td>95th Percentile</td><td>".$summary{'95IN'}."bs</td><td>".$summary{'95OUT'}."bs</td></tr>";
		$content .= "<tr><td>Total</td><td>".$summary{'TOTALIN'}."B</td><td>".$summary{'TOTALOUT'}."B</td></tr>";
		$content .= "</table><hr><table>";
		$content .= "<tr><td>First Measurement <br>sample</td><td>". $summary{'FROM'} ."</td></tr>";
		$content .= "<tr><td>Last Measurement <br>sample</td><td>". $summary{'TO'} ."</td></tr>";
		$content .= "<tr><td><p>Total Traffic for <br>this accounting period</td><td>  ".$summary{'TOTAL'} . "B</td></tr>";
		$content .= "<tr><td>Billed 95th% for <br>for this accounting period</td><td>  ".$billing_number . "bs</td></tr></table>";
		$content .= "</td></tr></table>";
		return $content;
	}


	function render_multiple_reports() {
		// Menu bar
		$form1 = new Form();
		$content = "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> Create reports</div>";

		$content .= '<h2>Create Report</h2>';
		$form = new Form();
		if ($_POST[step] !="2") {

			$headings = array("Please provide reporting information ");
			$postKeys=array("action","step","name","date1","date2");
 			$keyTitle=array("action","step","Report Name","Start Date","End Date");
			$keyData=array("renderCheckReport","2","$profile_id","","");

        		$form->setCols(2);
        		$form->setData($keyData);
			$form->setTitles($keyTitle);
			$form->setDatabase($postKeys);
			$form->setTableWidth("auto");

			$fieldType[0]= "hidden";
			$fieldType[1]= "hidden";
			$fieldType[3]= "date_picker";
			$fieldType[4]= "date_picker";
			$form->setFieldType($fieldType);

			$form->setHeadings($headings);
			$form->setSortable(false);
			$form->setUpdateValue("") ;

			$form->setUpdateText("Continue to Select Profiles") ;
        		$content .= $form->editForm();
		} elseif ($_POST[step] == "2") {

			$filter .=  "<p>";
			$tool = new EdittingTools();
			$filter .= $tool->createNewFilters();
			$filter .=  "<div style=\"clear:both;\"></div> </p>";
			$content .= $filter;

			// Fil array
			$keyData = array();
        
			$query = "select profile_id, title, client_id FROM accounting_profiles where archived = '0'";
			$result =  mysql_query($query) ;
			if (!$result)  {
				return "<b>Oops something went wrong, unable to select accounting profiles </b>";
			}
			while ($obj = mysql_fetch_object($result)){
				if ($obj->client_id != '') {
					$contact = new Contact($obj->client_id);
					$contact_name = $contact->get_name();
				} else { $contact_name = 'n/a';}
		
				array_push($keyData,"<input type='checkbox' name='profile_id[]' value='$obj->profile_id'>");
				array_push($keyData,$obj->title,$contact_name);
			}


			$headings = array("Select All<input name='all' type='checkbox' value='Select All' onclick=\"checkAll(document.dataForm['profile_id[]'],this)\" ","Profile name","Client");
        
			$form->setCols(3);
			$form->setTableWidth("1024px");
			$form->setData($keyData);
			$form->setHeadings($headings);
			$form->setSortable(true);
       			 // manually create form
        		$content .= "<p><b>Select the profiles you'd like to add to this report.</b></p>";
			$content .= "<form action='' id='dataForm' method='GET' name='dataForm'>";
			$content .= $form->showForm();
			$content .= "<div style='clear:both'></div>
                			<INPUT TYPE=SUBMIT VALUE='Add Selected Checks' name='addChecksToProfile'>
					<INPUT TYPE=hidden NAME=action VALUE='create_multiple_reports'>
					<INPUT TYPE=hidden NAME=tab VALUE='".$_GET['tab']."'>
					<INPUT TYPE=hidden NAME=date1 VALUE='".$_POST['date1']."'>
					<INPUT TYPE=hidden NAME=date2 VALUE='".$_POST['date2']."'>
					<INPUT TYPE=hidden NAME=name VALUE='".$_POST['name']."'>
					<INPUT TYPE=hidden NAME=pluginID VALUE='".$_GET['pluginID']."'>
				</form>";


		}
		return $content;
	}

	function render_create_report() {
		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		$profile_name = $this->get_accounting_profile_name($pid);
		// Menu bar
		$content = "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> <a href='$this->url&pid=$pid&action=show_accounting_profile'>$profile_name</a>
			>> <a href='$this->url&pid=$pid&action=show_reports''> Reports</a> </div>";

		$content .= "<h2>Create Report</h2>";


		$headings = array("Please select reporting Period ");
		$postKeys=array("action","tab","pluginID","pid","date1","date2");
 		$keyTitle=array("action","tab","pluginID","pid","Start Date","End Date");
		$keyData=array("render_report",$_GET['tab'],$_GET['pluginID'],$pid,"","");
		
		$form = new Form();
        	$form->setCols(2);
        	$form->setData($keyData);
		$form->setTitles($keyTitle);
		$form->setDatabase($postKeys);
		$form->setTableWidth("400px");

		$fieldType[0]= "hidden";
		$fieldType[1]= "hidden";
		$fieldType[2]= "hidden";
		$fieldType[3]= "hidden";
		$fieldType[4]= "date_picker";
		$fieldType[5]= "date_picker";
		$form->setFieldType($fieldType);

		$form->setHeadings($headings);
		$form->setSortable(false);
		$form->setUpdateValue("gen_report") ;
		$form->setUpdateText("Generate Report") ;
		$form->setMethod("GET");
		$content .= $form->editForm();

		return $content;
	
	}

	function render_show_accounting_profile() {

		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		$content ="";
		$profile_name = $this->get_accounting_profile_name($pid);
		// Menu bar
		$content .=  "<div style='font-size:10px; font-weight:100px;'>
			<a href='$this->url'>Accounting</a> >> $profile_name</div><br>";
	
		/*
		 Get all SCU-DCU sources for this profile
		*/
		$query = "SELECT accounting_source from accounting_profiles_files
			WHERE profile_id = '$pid'";
		$result =  mysql_query($query) ;
		$sources ='';
		while ($obj = mysql_fetch_object($result)){
			$sources .= "graph_profile_id[]=" . $obj->accounting_source . "&";
		}
		// Remove last &
		$sources = substr($sources,0,-1);
		/*
		 End Get all SCU-DCU sources for this profile
		*/

		// Tools menu
	
		$tool = new EdittingTools();
		if ($_SESSION['access'] >= 50) {
			$toolNames = array("Edit","Delete","Saved Reports","New Report","Quick Report");
			$toolIcons = array("edit", "delete","stat","line","graph");
			$toolHandlers = array("window.location.href='$this->url&action=edit_accounting_profile&pid=$pid'", 
					"window.location.href='$this->url&action=del_accounting_profile&pid=$pid'",
					"window.location.href='$this->url&pid=$pid&action=show_reports'",
					"window.location.href='$this->url&action=create_report&pid=$pid'",
					"window.location.href='$this->url&action=graph_profiles&$sources&pid=$pid'",
			);
			$content .= $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
		}


		$content .=  "<p>";

		$query = "Select title, client_id, notes, traffic_cap
			FROM accounting_profiles
			WHERE profile_id = '$pid'";

		$result =  mysql_query($query) ;
		if (!$result)  {
			return "<b>Sorry something went wrong</b>". mysql_error() . $query;
        	}

		$obj = mysql_fetch_object($result);
		$contact = new Contact($obj->client_id);
		$contact_name = $contact->get_name();
		if ($contact_name == '') {
			$contact_name = "n/a";
		}
		$values = array($obj->title, $contact_name, $this->int_to_si($obj->traffic_cap), $obj->notes);

		$form = new Form("auto", 2);
		$heading = array("Edit Accounting Profile");

		$titles = array("Name", "Client", "Traffic Cap.tip.Configured cap or Contracted rate.", "Notes");

     		$form->setSortable(false);
     		$form->setHeadings($heading);
     		$form->setTitles($titles);
     		$form->setDatabase($titles);
		$form->setData($values);

     		//set the table size
     		$form->setTableWidth("1024px");
 
    		$form->setTitleWidth("20%");
    		$content .= $form->ShowForm(1);

		$content .=  "<div style=\"clear:both;\"></div> </p>";
		
		$content .=  "</p><p><h2>RRD files</h2>";
		$tool2 = new EdittingTools();
		if ($_SESSION['access'] >= 50) {
			$toolNames = array("Add RRD file");
			$toolIcons = array("add");
                        #$formType = array("newDialog");
                        #$tool2->createNewModal($toolNames, $toolIcons, $formType);
                        $toolHandlers = array("window.location.href='$this->url&action=show_add_rrd_to_profile&pid=$pid'");
                        $content .=  $tool2->createNewTools($toolNames, $toolIcons, $toolHandlers);

		}
			

		$form = new Form("auto",3);
		$heading = array( "Delete", "Device", "SCU-DCU source");
		$data = array();
		$title = array();
		$handler = array();
		#$content .=  "<table><tr><th>Delete</th><th>Device</th><th>SCU-DCU sources</th>";
		$query = "SELECT accounting_profiles_files.accounting_source,
				accounting_sources.title, accounting_sources.device_id,
				Devices.name as device_name
			FROM accounting_profiles_files, accounting_sources, Devices
			WHERE profile_id = '$pid'
			AND accounting_sources.device_id = Devices.device_id
			AND accounting_profiles_files.accounting_source = accounting_sources.id";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$content .=  "<b>No Files found</b>";
        	}
		
                while ($obj = mysql_fetch_object($result)){
			#$content .=  "<tr><td><a href = '$this->url&action=del_accounting_source_from_profile&pid=$pid&asrc=$obj->accounting_source'><img src='icons/Delete.png' height=20></a></td> <td>$obj->device_name</td><td>$obj->title</td></tr>";
			array_push ($title,
				"<a href='$this->url&action=del_accounting_source_from_profile&pid=$pid&asrc=$obj->accounting_source'><img src='icons/Delete.png' height=20></a>");
			array_push ($data, $obj->device_name,$obj->title);
		}
		#$content .=  "</table>";
		$form->setSortable(true); // or false for not sortable
		$form->setHeadings($heading);
		$form->setTitles($title);
		$form->setTitleWidth("50px") ;
		$form->setData($data);
		$form->setTableWidth("500px");

                $content .= $form->showForm();

		// This is to add normal Interface RRD files
		// AT, remove this link as it's not fully implemented
		//$content .= "<a href='$this->url&action=add_device_interface_to_profile&pid=$pid'>Add interface to accounting profile</a>";

		// Done
		return $content;

		
	}
	
	function add_interface_form() {

		// This function will be the wizard that allows the user
		// to add interfaces to an accounting profile

		
		// First check if Device ID is set, if not then render device menu
		if (isset ($_POST['device_id'])) {
			$device = new Device($_POST['device_id']);
			$all_interfaces = $device->get_interfaces();

			$data = array();
			$title = array();
			foreach($all_interfaces as $id => $value) {
                        	array_push($title, "<input type='checkbox' class='check_pref' name='if_add[]' value='".$id."' />");
                        	array_push($data, $value->get_name());
                        	array_push($data, $value->get_alias());
                        	array_push($data, $value->get_oper_status());
			}
			$form = new Form("auto",4);

			$heading = array( "Select All<input name='all' type='checkbox' value='Select All' onclick=\"checkAll(document.dataForm['if_add[]'],this)\"",
				"Interface name", "Description", "Status");
			$form->setSortable(true); // or false for not sortable
			$form->setHeadings($heading);
			$form->setTitles($title);
			$form->setTitleWidth("50px") ;
			$form->setData($data);
			$form->setTableWidth("500px");

			$content .= "<form action='' id='dataForm' method='POST' name='dataForm'>";
                        $content .= $form->showForm();
                        $content .= "<div style='clear:both'></div>
                                        <INPUT TYPE=SUBMIT VALUE='Add Selected Interfaces' name='addInterfacesToProfile'>
                                        <INPUT TYPE=hidden NAME=action VALUE='add_device_interface_to_profile'>
                                        <INPUT TYPE=hidden NAME=tab VALUE='".$_GET['tab']."'>
                                        <INPUT TYPE=hidden NAME=name VALUE='".$_POST['name']."'>
                                        <INPUT TYPE=hidden NAME=pluginID VALUE='".$_GET['pluginID']."'>
                                        <INPUT TYPE=hidden NAME=device_id VALUE='".$_POST['device_id']."'>
                                </form>";


                	$content .= $form->showForm();
			print_r($_POST);


			return $content;
		}
		elseif (isset($_POST['addInterfacesToProfile'])) {
			print "adding ifs!!<br>";
			print_r($_POST);
		}
		else {
			// Render device menu
	
			$heading = array("Select Device");
			$titles = array("Device");
			$keys = array("device_id");
			$all_devices = Device::get_devices();
			$myDropDownListener = "alert('Your gender is '+this.value)";
			$fieldType = array("drop_down");
 
			$form = new Form("auto", 2);
			$form->setHeadings($heading);
			$form->setTitles($titles);
			$form->setDatabase($keys) ;
			$form->setType($all_devices);
			$form->setFieldType($fieldType);
			$form->setUpdateText("Continue to select Interface") ;
			$form->setUpdateValue("select_if") ;
			//$form->setMethod("GET") ;
 
			echo $form->editForm();
		} 
		return;
		
	}

	function delete_accounting_profile() {
		// Include contact class

		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		
		$query = "UPDATE accounting_profiles SET
			 archived = '1' 
			WHERE profile_id = '$pid'";

		$result =  mysql_query($query) ;
		if (!$result)  {
			 print mysql_error() . $query;
			 $form->error("Unable to insert new data...");
		}
		return "<meta http-equiv=\"REFRESH\" content=\"0;url=".$this->url."&action=list_accounting_profiles\">";
	}

	function update_accounting_profile() {
		// Include contact class

		if ($_POST['Name'] == '') {
			print "Failed: <b>Name can not be empty</b>";
		} else {
			$name = $_POST['Name'];
		}

		if ($_POST['Client'] == '') {
			$client = "NULL";
		} else {
			$client = "'" . $_POST['Client'] ."'";
		}

		if ($_POST['TrafficCap'] == '') {
			$traffic_cap = "NULL";
		} else {
			$traffic_cap = strtoupper($_POST['TrafficCap']);
			$traffic_cap = $this->si_to_int($traffic_cap);
			$traffic_cap = "'$traffic_cap'";
		}
		
		
		$notes = $_POST['Notes'];
		

		if ((isset($_GET['pid'])) && (is_numeric($_GET['pid']))) {
			$pid = $_GET['pid'];
		} else {
			return "<b>Sorry invalid profile id ". $_GET['pid'] ."</b>";
		}
		
		$query = "UPDATE accounting_profiles SET
			 title = '$name', client_id = $client,
			notes = '$notes', traffic_cap = $traffic_cap
			WHERE profile_id = '$pid'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			 print mysql_error() . $query;
			 $form->error("Unable to insert new data...");
        	} else {
		}
		return "<meta http-equiv=\"REFRESH\" content=\"0;url=".$this->url."&action=show_accounting_profile&pid=$pid\">";
	}


	function insert_new_accounting_profile() {
		// Include contact class

		if ($_POST['Name'] == '') {
			print "Failed: <b>Name can not be empty</b>";
		} else {
			$name = $_POST['Name'];
		}

		if ($_POST['Client'] == '') {
			$client = "NULL";
		} else {
			$client = "'" . $_POST['Client'] ."'";
		}
		
		if ($_POST['TrafficCap'] == '') {
			$traffic_cap = "NULL";
		} else {
			$traffic_cap = strtoupper($_POST['TrafficCap']);
			$traffic_cap = $this->si_to_int($traffic_cap);
			$traffic_cap = "'$traffic_cap'";
		}
		
		$notes = $_POST['notes'];
		
		$query = "INSERT INTO accounting_profiles SET
			 title = '$name', client_id = $client,
			notes = '$notes', traffic_cap = $traffic_cap";

		$result =  mysql_query($query) ;
		if (!$result)  {
			 print mysql_error() . $query;
			 $form->error("Unable to insert new data...");
        	} else {
			 $id = mysql_insert_id();
		}
		return "<meta http-equiv=\"REFRESH\" content=\"0;url=".$this->url."&action=show_accounting_profile&pid=$id\">";

	}

	/*
        //renders the configuration
        function get_config($id) {
                // the name of the property must follow the conventions Plugin<Classname>_<propertyName>
                // have the form post and make sure the submit button is named plugin_update
                // make sure there is also a hidden value giving the name of this Class file
				
		// ADDED THESE $id, <input type='hidden' name='id' value=".$id."></input>, and Plugin_AjaxTermUrl
                return "<h1> Configure Ajax Term Url</h1>
                        Please configure the Ajax Term Url in the form below<br>

                        <form id='configForm' method='post'>
						<input type='hidden' name='id' value=".$id."></input>
                        <input type='hidden' name='class' value='AjaxTerm'></input>
                        AjaxTerm url:
                        <input type='text' name='Plugin_AjaxTermUrl'/>
                <input type='submit' class='submitBut' name='plugin_update' value='Update config'/>
                </form>";
        }

        //updates the configuration
        function update_config($values)
        {
			include_once("classes/Property.php");
			//calls on for the database class
			$property = new Property();    
			//sets the properties to store them, use a switch statement to store different description based on different properties
			
			//needs to return a true value to indicate that update has completed.
			if($property->set_property($values['name'],$values['value'], "Ajax Term URL"))
			{return true;}
        }
	*/
	function si_to_int($value){
		$number = false;
		$quantifier = false;
		#$keywords = preg_split("/^[\d]+\.[\d]+[a-zA-Z]$/", $value);
		$keywords =array();
		if (preg_match("/NaN/",$value, $keywords)) {
			return 0;
		}
		preg_match("/(\d+)\.(\d+)([a-zA-Z]?)/",$value, $keywords);
		if (is_numeric($keywords[1])) {
			$number = $keywords[1].".".$keywords[2];
			$quantifier = $keywords[3];
		} else {
			preg_match("/(\d+)([a-zA-Z]?)/",$value, $keywords);
			if (is_numeric($keywords[1])) {
				$number = $keywords[1];
				$quantifier = $keywords[2];
			} else {
				print "Warning could not determin absolute value for -$value- $keywords[1] <br>";
				return false;
			}
		}
		if (! $quantifier) {
			return $number;
		} elseif ($quantifier == "m") {
			return $number * pow(10, -3);
		} elseif (($quantifier == "k") || ($quantifier == "K"))  {
			return $number * pow(10, 3);
		} elseif ($quantifier == "M") {
			return $number * pow(10, 6);
		} elseif ($quantifier == "G") {
			return $number * pow(10, 9);
		} elseif ($quantifier == "T") {
			return $number * pow(10, 12);
		} elseif ($quantifier == "P") {
			return $number * pow(10, 15);
		} elseif ($quantifier == "E") {
			return $number * pow(10, 18);
		} elseif ($quantifier == "Z") {
			return $number * pow(10, 21);
		} elseif ($quantifier == "Y") {
			return $number * pow(10, 24);
		}
		return false;
	}

	function int_to_si($value){
		
		if ($value < pow(10, 3)) {
			return "$value";
		} elseif ($value < pow(10, 6)) {
			return $value/pow(10, 3)."K";
		} elseif ($value < pow(10, 9)) {
			return $value/pow(10, 6)."M";
		} elseif ($value < pow(10, 12)) {
			return $value/pow(10, 9)."G";
		} elseif ($value < pow(10, 15)) {
			return $value/pow(10, 12)."T";
		} elseif ($value < pow(10, 18)) {
			return $value/pow(10, 15)."P";
		} elseif ($value < pow(10, 21)) {
			return $value/pow(10, 18)."E";
		} elseif ($value < pow(10, 24)) {
			return $value/pow(10, 21)."Z";
		} elseif ($value >= pow(10, 24)) {
			return $value/pow(10, 24)."Y";
		}
		return false;
	}
	
	function get_accounting_profile_name($profile_id) {
		$query = "select title from accounting_profiles
			WHERE profile_id = '$profile_id'";
		                $result =  mysql_query($query) ;
                if (!$result)  {
                        print "failed to execute query $query<br>";
                        return false;
                }
		$obj = mysql_fetch_object($result);
		return $obj->title;
	}
}
?>

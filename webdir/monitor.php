<?php

// Special for export CSV, we don't want to send any data yet!
if ($_GET[action] == 'export_report') {
	export_csv();
	exit;
}


include_once("sessionCheck.php");
//control bar, $_GET['mode'] is used to determine if this site is in Ajax mode, if it is don't repeat functionalities that are already executed without AJAX
if(!isset($_GET['mode']))
{include("controlBar.php");}
?>

<div id="main">
<h1 id="mainTitle">Event Management</h1>
<script type="text/javascript" src="js/modal-message.js"></script>
<script type="text/javascript" src="js/ajax-dynamic-content.js"></script>

<?
include_once 'classes/EdittingTools.php';
include_once('classes/Property.php');
include_once 'classes/Form.php';
include_once 'classes/Event.php';
include_once 'classes/RRD.php';
include_once('classes/Property.php');
include_once('classes/Check.php');


//Make a new contact, a new tool bar, and a new form
$tool = new EdittingTools();
$form = new Form("auto", 3);

$status_array=array(
	0 => "Ok",
	1 => "Warning",
	2 => "Critical",
	3 => "Unknown",
);
$status_collors=array(
	0 => "Green",
	1 => "Orange",
	2 => "Red",
	3 => "Blue"
);

$report_types=array(
	summary => "Summary Report",
	one_up_all_up => "One Up all Up",
	one_down_all_down => "One Down all Down",
);

				
//checks the status of what's happening. If something was done successfully, it will be displayed
if(isset($_GET['update']) || isset($_GET['delete']) || isset($_GET['add']))
{
	switch (success)
	{
		case $_GET['update']:
		$form->success("Updated successfully");
		break;
		
		case $_GET['add']:
		$form->success("Added new data successfully");
		break;
		
		case $_GET['delete']:
		$form->success("Deleted successfully");
		break;
	}
}

// Templates
if  ($_GET['action'] == "list_templates") {
	displayAllTemplates();
}
elseif  ($_GET['action'] == "showTemplate") {
	displayTemplate();
}
elseif  ($_GET['action'] == "editTemplate") {
	if(isset($_POST['updateTemplate'])) {
		updateTemplate();
	} else {
		editTemplate();
	}
}
elseif  ($_GET['action'] == "addTemplate") {
	if(isset($_POST['insertTemplate'])) {
		insertTemplate();
	} else {
		addTemplate();
	}
}
elseif  ($_GET['action'] == "deleteTemplate") {
	deleteTemplate();
}


// Cghcks				
elseif  ($_GET['action'] == "list_checks") {
	displayAllChecks();
}
elseif  ($_GET['action'] == "showCheck") {
	displayCheck();
}
elseif  ($_GET['action'] == "editCheck") {
	if(isset($_POST['updateCheck'])) {
		updateCheck();
	} else {
		editCheck();
	}
}

elseif  ($_GET['action'] == "addCheck") {
	if(isset($_POST['insertCheck'])) {
		insertCheck();
	} else {
		addCheck();
	}
}
elseif  ($_GET['action'] == "deleteCheck") {
	deleteCheck();
}
elseif  ($_GET['action'] == "renderCheckReport") {
	renderCheckReport();
}
elseif  ($_GET['action'] == "renderCheckReportForm") {
	renderCheckReportForm();
}

// Report
elseif  ($_GET['action'] == "list_ReportProfiles") {
	displayAllReportProfiles();
}
elseif  ($_GET['action'] == "addReportProfile") {
	if(isset($_POST['insertProfile'])) {
		insertReportProfile();
	} else {
		addReportProfile();
	}
}
elseif  ($_GET['action'] == "editReportProfile") {
	if(isset($_POST['updateReportProfile'])) {
		updateReportProfile();
	} else {
		editReportProfile();
	}
}
elseif  ($_GET['action'] == "deleteReportProfile") {
	deleteReportProfile();
}
elseif  ($_GET['action'] == "showReportProfile") {
	displayReportProfile();
}
elseif  ($_GET['action'] == "add_check_to_profile") {
	if(isset($_POST['addChecksToProfile'])) {
		addChecksToProfile();
	} else {
		addCheckToProfileForm();
	}
}
elseif  ($_GET['action'] == "del_check_from_profile") {
	delCheckFromProfile();
}
elseif  ($_GET['action'] == "renderProfileReportForm") {
	renderProfileReportForm();
}
elseif  ($_GET['action'] == "create_multipe_reports") {
	 if(isset($_POST['insert_multiple_reports'])) {
		// This creates the reports once we get all info from step 1 +2
		insertMultipeReports();
	} else {
		// This renders the create form (step 1 + 2)
		renderCreateMultipeReports();
	}
}
elseif  ($_GET['action'] == "list_reports") {
	displayReports();
}
elseif  ($_GET['action'] == "display_reports_by_name") {
	displayReportsByName();
}
elseif  ($_GET['action'] == "display_report_id") {
	renderCheckReport();
}
elseif  ($_GET['action'] == "delete_reports") {
	deleteReports();
}
				
//if nothing else, display all the devices for the user to see
else
{
	displayAll($devices);
}
?>
</div>        
<?php 
//Footer
if(!isset($_GET['mode']))
{include("footer.php");} ?>

<?
/*****************************************************FUNCTIONS************************************************/


function displayCheck() {
	echo "<div style='position: relative; float:left; clear:both; width:;'>
		<h2>Check Detail</h2>".  displayCheckDetails() ."</div>";
	echo "<div style='position: relative; float:left; margin-left:25px; width:700px;'>";
	echo "<h2>Recent Events</h2>".displayCheckRecent() . "</div>";
	echo "<div style='position: relative; float:left; clear:both; width:;'>";

	if ($chart = displayCheckGraphs()) {
		print "<a name='showperfdata'></a>";
		displayGraphForm();

		echo "<div style='position: relative; float:left; clear:both; width:;'>";
		echo "<h2>Performance data last day</h2> $chart </div>";
		echo "<div style='position: relative; float:left; margin-left:25px; width:700px;'>";
		echo "<h2>Performance data last week</h2> ". displayCheckGraphs('week');
		echo "</div>";
		echo "<div style='position: relative; float:left; clear:both; width:;'></div>";

		echo "<div style='position: relative; float:left; clear:both; width:;'>";
		echo "<h2>Performance data last month</h2> ". displayCheckGraphs('month') ."</div>";
		echo "<div style='position: relative; float:left; margin-left:25px; width:700px;'>";
		echo "<h2>Performance data last year</h2> ". displayCheckGraphs('year');
		echo "</div>";
		echo "<div style='position: relative; float:left; clear:both; width:;'><br></div>";
	}  else {
		//print "no Grpags<br>";
	}
}

function displayGraphForm() {
	// Get data sources

	if (is_numeric($_GET[checkid])) {
		$check = new Check($_GET[checkid]);
	} else {
		return;
	}

	$property = new Property();
	if ($rrdtool = $property->get_property("path_rrdtool")) {
	} else {
		return;
        }
	if ($rrd_dir = $property->get_property("path_rrddir")) {
	} else {
		return;
        }

        $filename = "checks/checkid_". $check->get_check_id() .".rrd";
        $filepath = $rrd_dir."/".$filename;

        // Now check if there's are graphs for this check
        // Not all checks have these
	if (!file_exists($filepath)) {
		//$form->warning( "file $filename not there");
		return false;
        }
	
        $rrd = new RRD("$filepath",$rrdtool);
	$datasources = $rrd->get_data_sources();
	ksort($datasources,SORT_STRING);
	$form_data ="<br><FORM action='monitor.php' METHOD='GET' >";
	$form_url = "monitor.php?action=showCheck&checkid=". $check->get_check_id();

	foreach ($datasources as $ds => $value) {
		if ((isset($_GET[$ds])) && ($_GET[$ds] == 'no')) {
			$form_url .= "&".$ds ."=no";
		}
	}
	// We don't want more than 4 values per line, so after 4 include a break
	$i=0;
	foreach ($datasources as $ds => $value) {
		if (($i %4 ==0) && ($i >0)) {
			 $form_data .="<br>";
		} 
		$i++;
		$check = "checked";
		if ($_GET[$ds] == "no") {
			$check = "";
		}
		$form_data .= "<input type='checkbox' name='$ds' value='yes' $check " ;
		$form_data .= "onclick=\"if (this.checked) {window.location='$form_url&$ds='+this.value+'#showperfdata'}";
		$form_data .= "else {window.location='$form_url&$ds=no#showperfdata';}\"> $ds    ";
	}
	$form_data .= "</form>";		
	print $form_data;
	return;
}


function displayCheckRecent()
{
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form, $status_array, $status_collors;
	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	if (is_numeric($_GET[checkid])) {
		$check = new Check($_GET[checkid]);
	} else {
		$form->warning("Invalid check id" );     
		return;
	}

	// Get last events
	$recent_events = $check->get_last_events(5);
	foreach ($recent_events as $event_id => $status) {
		unset($event);
		$event = new Event($event_id);

                $status = 3;
                $status = $event->get_status();
                $status_name = $status_array[$status];
                array_push($keyData, "<font color=$status_collors[$status]> $status_name </font>");
                array_push($keyData, $event->get_hostname() );
                //array_push($keyData, $event->get_check_name() .".tip.". $event->get_key1() ." ". $event->get_key2());

                array_push($keyData, $event->get_insert_date() );
                array_push($keyData, $event->get_last_updated());
                $insert_time = (strtotime($event->get_insert_date()));
                $last_time = (strtotime($event->get_last_updated()));
                $diff = strTime($last_time - $insert_time);

                array_push($keyData, $diff);
                array_push($keyData, $event->get_info_msg() );
        }
        $headings = array("Status","Host",  "Insert Time", "Last Check", "Duration","Service Information" );

	$form->setCols(6);
	$form->setTableWidth("auto");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(true);
	return $form->showForm();
	
}

function displayCheckGraphs($interval = 'day') {

	global $tool, $form, $status_array, $status_collors;
	$my_colors=array("FF0000","0404B4","04B431","B45F04","F7FE2E","8B008B","4B0082","FA8072","4169E1","D2B9D3","B4CFEC",
                "eF6600","77CEEB","eFFF00","6FFF00","8E7BFF","7B008B","3B0082","eA8072","3169E1","c2B9D3","a4CFEC",
                "dF6600","67CEEB","dFFF00","5FFF00","7E7BFF","6B008B","2B0082","dA8072","2169E1","b2B9D3","94CFEC");


	if (is_numeric($_GET[checkid])) {
		$check = new Check($_GET[checkid]);
	} else {
		$form->warning("Invalid check id" );     
	}

	$property = new Property();
	if ($rrd_dir = $property->get_property("path_rrddir")) {
	} else {
        	$form->warning($property->get_error());
        	return false;
	}
	if ($rrdtool = $property->get_property("path_rrdtool")) {
	} else {
		return;
	}

	$filename = "checks/checkid_". $check->get_check_id() .".rrd";
	$filepath = $rrd_dir."/".$filename;

	// Now check if there's are graphs for this check
	// Not all checks have these
	if (!file_exists($filepath)) {
		//$form->warning( "file $filename not there");
		return false;
	}
	if ($interval == 'week') {
		$from = "-7d";
	} elseif ($interval == 'month') {
		$from = "-1m";
	} elseif ($interval == 'year') {
		$from = "-365d";
	}  else {
		$from = "-24h";
	}

	$rrd = new RRD("$filepath",$rrdtool);
	$datasources = $rrd->get_data_sources();
	ksort($datasources,SORT_STRING);
	$exclude_ds = array();
	$color_codes="";
	$exclude = "";	
	$i=0;
	foreach ($datasources as $ds => $value) {

		$color_code .= "&ds_colors[$ds]=$my_colors[$i]";
		$i++;
		
		if ((isset($_GET[$ds])) && ($_GET[$ds] == 'no')) {
			array_push($exclude_ds,$ds);
			$exclude .= "&exclude_ds[$ds]=yes";
		}
	}

	$rrd_url = "rrdgraph.php?file=". urlencode($filename) ."&type=check&title=". 
		urlencode($check->get_name())."&width=400&height=122&from=$from" ."$exclude".$color_code;
	return "<img src='$rrd_url'>";
}

function updateCheck() {
	global $tool, $form, $status_array, $status_collors;
	if (is_numeric($_GET[checkid])) {
		$check = new Check($_GET[checkid]);
	} else {
		$form->warning("Invalid check id" );     
		return;
	}
	$check->set_name(trim($_POST[check_name]));
	$check->set_device_id(trim($_POST[hostname]));
	$check->set_desc(trim($_POST[check_desc]));
	$check->set_interval(trim($_POST[check_interval]));
	$check->set_template_id(trim($_POST[check_template]));
	$check->set_arguments(trim($_POST[check_args]));
	$check->set_key1(trim($_POST[check_key1]));
	$check->set_key2(trim($_POST[check_key2]));
	$check->set_notes(trim($_POST[check_notes]));
	if ($check->update()) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showCheck&checkid=".$_GET[checkid]."&update=success\">";

	} else {
		$form->warning("Update failed " . $check->get_error() );     
		return;
	}
}
function editCheck() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form, $status_array, $status_collors;
	$allDevices = Device::get_devices();

	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	$postKeys = array();
	if (is_numeric($_GET[checkid])) {
		$check = new Check($_GET[checkid]);
	} else {
		$form->warning("Invalid check id" );     
		return;
	}
	
	$allTemplates = CheckTemplate::get_templates();

	$keyData=array($check->get_name(), $check->get_hostname(), $check->get_desc(),
			$check->get_interval(), $check->get_template_name(), $check->get_arguments(), 
			$check->get_key1(), $check->get_key2(),$check->get_notes()
	);
	$form->setType($allDevices); // Drop down
	$form->setType($allTemplates); // Drop down

	$key1 = "Key1<br><small>".$check->get_key1_name()."</small>";
	$key2 = "Key2<br><i><small>".$check->get_key2_name()."</small></i>";
	$keyTitle=array("Name.tip.descriptive name for this chesk","Hostname",
		"Description.tip.A usefull description","Interval.tip.Check interval in Minutes","Template","Arguments","$key1","$key2","Notes");
	$postKeys=array("check_name","hostname","check_desc","check_interval","check_template","check_args","check_key1","check_key2","check_notes");
	$headings = array("Check Details");
        
	$form->setCols(2);
	$form->setTableWidth("500px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
        $form->setDatabase($postKeys);

        $fieldType[1]= "drop_down";
        $fieldType[4]= "drop_down";
        $fieldType[8]= "text_area";
	$form->setFieldType($fieldType);

	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(false);
	$form->setUpdateValue("updateCheck") ;

	print $form->editForm();
}

function displayCheckDetails()
{
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form, $status_array, $status_collors;

	$toolNames = array("Edit Check","Delete Check","Create Report");
	$toolIcons = array("edit","delete","report");
	$toolHandlers = array(
		"handleEvent('".$_SERVER['PHP_SELF']."?action=editCheck&checkid=".$_GET[checkid]."')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=deleteCheck&checkid=".$_GET[checkid]."')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=renderCheckReportForm&checkid=".$_GET[checkid]."')"
	);
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);

	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	if (is_numeric($_GET[checkid])) {
		$check = new Check($_GET[checkid]);
	} else {
		$form->warning("Invalid check id" );     
		return;
	}
	
	$keyData=array($check->get_name(), $check->get_hostname(), $check->get_desc(),
			$check->get_interval() ." Minutes", $check->get_template_name(), $check->get_arguments(), 
			$check->get_key1(), $check->get_key2(),nl2br($check->get_notes())
	);
	$headings = array("Check Details");
	$key1 = "Key1<br><small>".$check->get_key1_name()."</small>";
	$key2 = "Key2<br><i><small>".$check->get_key2_name()."</small></i>";
	$keyTitle=array("Name.tip.descriptive name for this chesk","Hostname",
		"Description.tip.A usefull description","Interval.tip.Check interval in Minutes","Template","Arguments","$key1","$key2","Notes");
        
	$form->setCols(2);
	$form->setTableWidth("450px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(false);
	return $form->showForm();
	
}

function displayAllChecks() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form, $status_array, $status_collors;
	$toolNames = array("Add Check");
	$toolIcons = array("add");
	$toolHandlers = array(
		"handleEvent('".$_SERVER['PHP_SELF']."?action=addCheck')",
	);
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	foreach (Check::get_checks() as $id => $name)
	{
		$check = new Check($id);
		array_push($keyHandlers, "handleEvent('".$_SERVER['PHP_SELF']."?action=showCheck&checkid=".$check->get_check_id()."')");
		array_push($keyData, $check->get_name() );
		array_push($keyData, $check->get_hostname() );
		array_push($keyData, $check->get_desc() );
		array_push($keyData, $check->get_interval() );
		array_push($keyData, $check->get_template_name() );
	}
	$headings = array("Name","Host", "Description", "Interval","Template");
        
	$form->setCols(5);
	$form->setTableWidth("1024px");
	$form->setData($keyData);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(true);
	echo $form->showForm();
	
}
function addCheck() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form;

	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	
	$allDevices = Device::get_devices();

	$keyHandlers = array();
	$keyData = array();
	$keyData[3] = "5";
	$keyTitle = array();
	$postKeys = array();
	
	$allTemplates = CheckTemplate::get_templates();
	$check= new Check();

	$form->setType($allDevices); // Drop down
	$form->setType($allTemplates); // Drop down

	$key1 = "Key1<br><small><i>Depends on selected Template</i></small>";
	$key2 = "Key2<br><small><i>Depends on selected Template</i></small>";

	$headings = array("New Check ");
	$postKeys=array("check_name","hostname","check_desc","check_interval","check_template","check_args","check_key1","check_key2","check_notes");
	$keyTitle=array("Name.tip.descriptive name for this chesk","Hostname",
		"Description.tip.A usefull description","Interval.tip.Check interval in Minutes","Template","Arguments","$key1","$key2","Notes");
        
	$form->setCols(2);
	$form->setData($keyData);
	$form->setTitles($keyTitle);
        $form->setDatabase($postKeys);
	$form->setCols(2);
	$form->setTableWidth("500px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
        $form->setDatabase($postKeys);

        $fieldType[1]= "drop_down";
        $fieldType[4]= "drop_down";
        $fieldType[8]= "text_area";
	$form->setFieldType($fieldType);

	$form->setHeadings($headings);
	$form->setSortable(false);
	$form->setUpdateValue("insertCheck") ;
	$form->setUpdateText("Add new Check") ;
	echo $form->editForm();
	
}

function insertCheck() {
	global $tool, $form; 
	$check = new Check();
	$check->set_name($_POST[check_name]);
	$check->set_desc(trim($_POST[check_desc]));
	$check->set_key1(trim($_POST[check_key1]));
	$check->set_key2(trim($_POST[check_key2]));
	$check->set_notes(trim($_POST[check_notes]));
	$check->set_interval(trim($_POST[check_interval]));
	$check->set_template_id(trim($_POST[check_template]));
	$check->set_device_id(trim($_POST[hostname]));

	$check_id = false;
	$check_id = $check->insert();
	if ($check_id) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=editCheck&checkid=$check_id&add=success\">";

	} else {
		$form->warning("Insert failed " . $check->get_error() );     
		return;
	}
}
function deleteCheck() {
	global $tool, $form; 
	if (is_numeric($_GET[checkid])) {
		$check = new Check($_GET[checkid]);
	} else {
		$form->warning("Invalid Check id" );     
		return;
	}
	// Confimration part
	if(isset($_POST['deleteYes'])) {
		if ($check->delete()) {
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=list_checks&delete=success\">";
		} else {
			$form->warning("Could not  delete domain. ". $check->get_error() );
			return false;
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo'])) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showCheck&checkid=".$_GET[checkid]."\">";
	} else {
		$form->prompt("Are you sure you want to delete this Check (". $check->get_name() ." on " . $check->get_hostname() .")?");
	}

}

function renderCheckReportForm() {
	// This is to render a form for a per check report
	global $tool, $form, $report_types;
	$content = "<h2>Please fill in the information below</h2>";
	$check_id = $_GET[checkid];

	$headings = array("Report Details ");
	$postKeys=array("action","checks[]","report_type","from","to");
	$keyTitle=array("action","checks[]","Report Type","Start Date","End Date");
	$keyData=array("renderCheckReport",$check_id,"summary","","");
	
	$form->setCols(2);
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setDatabase($postKeys);
	$form->setTableWidth("auto");

	// Different report types
	// * one_down_all_down ie worst case
	// * one_up_all_up     ie best case
	// * summary

	$form->setType($report_types); // Drop down
	$fieldType[0]= "hidden";
	$fieldType[1]= "hidden";
	$fieldType[2]= "hidden";
	$fieldType[3]= "date_picker";
	$fieldType[4]= "date_picker";
	$form->setFieldType($fieldType);

	$form->setHeadings($headings);
	$form->setSortable(false);
	$form->setUpdateValue("renderCheckReport") ;
	$form->setUpdateText("Show Report") ;
	$form->setMethod("GET");
	echo $form->editForm();
}

function renderCheckReport() {
	global $tool, $form, $status_array, $status_collors, $report_types; 
	// Generating rerports can take a while
	// Increase time out
	$report_id = $_GET[report_id];
	$profile_id = $_GET[profileid];	

	if (isset($report_id)) {
		// This means a saved report
		$mode = "saved_report";
		$report = new Report($report_id);
		$profile_name = $report->get_name();
		$report_type = $report->get_report_type();
		$from = $report->get_start_time();
		$to = $report->get_end_time();

		$profile = new CheckReportProfile($report->get_profile_id());
		$profile_name = $profile->get_name();
		$report_checks=array();
		foreach ($profile->get_checks() as $report_id => $report_name) {
			array_push($report_checks,  $report_id);
		}
	} else {
		// else a live report

		$profile_name ='';
		if (is_numeric($profile_id)) {
			$mode = "report";
			$profile = new CheckReportProfile($_GET[profileid]);
			$report_type = $profile->get_report_type();
			$profile_name = $profile->get_name();
			$report_checks=array();
			foreach ($profile->get_checks() as $report_id => $report_name) {
				array_push($report_checks,  $report_id);
			}
		}  else {
			$report_checks = $_GET[checks];
			if (!is_array($report_checks)) {
				$form->warning("NO checks specified " );     
				return;
			} elseif (count($report_checks) <1) {
				$form->warning("NO checks specified " );     
				return;
			}
			$mode = "checks";
			$report_type = $_GET['report_type'];
		}
		$from = $_GET[from];
		$to = $_GET[to];
	}


	// Different report types
	// * one_down_all_down ie worst case
	// * one_up_all_up     ie best case
	// * summary
	if (($report_type != 'one_down_all_down') && ($report_type != 'one_up_all_up')) {
		$report_type = 'summary';
	}

	// Check From date
	if ((is_numeric($from))&&(date($from))) {
		$start_stamp = date($from);
	}
	// Check From date
	if ((is_numeric($from))&&(date($from))) {
		$start_stamp = date($from);
	}
	elseif (($from != '') && (strtotime($from))) {
		$start_stamp = strtotime($from);
	} else {
		$form->warning( "Invalid start date $from");
		return false;
	}

	// Check To date
	if ((is_numeric($to))&&(date($to))) {
		$end_stamp = date($to);
	}
	elseif (($to != '') && (strtotime($to))) {
		$end_stamp = strtotime($to);
	} else {
		$form->warning( "Invalid End date $to");
		return false;
	}
	if ($mode == 'saved_report') {
		$timers = array();
		$timers[ok] = $report->get_ok_secs();
		$timers[warning] = $report->get_warning_secs();
		$timers[critical] = $report->get_critical_secs();
		$timers[unknown] = $report->get_unknown_secs();
		//$timers[other] = $report->get_other_secs();
		$timers[no_data] = $report->get_no_data_secs();
	} else {	
		$timers = get_report_data($start_stamp,$end_stamp,$report_type,$report_checks);
	}
	print "<h2>Availability Report</h2>";

	if ($mode == 'saved_report') {
		print "<div style='background-color: orange; color: black;
		 width: 500px; padding: 1px; padding-right:
		1px; border: 2px black solid; position: relative; float: right; padding-top: 0.1em;
		padding-right: 0.1em;
		padding-bottom: 0.1em;
		padding-left: 0.1em;
		'>

		<b>Note:</b> This is a saved report; the time data is extracted from the report data however all other data
		such as the events are re-generated from the database. <br>This is based on which checks are currently in the
		Report profile and not at the time of report generation<br>
		</div><br><br>";
	}
	echo "<div style='position: relative; float:left; clear:both; width:;'>";
	echo "<br><b>Report for: $profile_name:</b><ul>";
	foreach($report_checks as $check_id) {
		$check = new Check($check_id);
		print "<li>". $check->get_name() ." (". $check->get_desc() .")</li>";
	}
	print "</ul></div>";
	echo "<div style='position: relative; float:left; margin-left:105px; width:;'>
		<br><b>Reporting Detaills:</b><ul>
			<li>".  date("F j, Y, H:i ",$start_stamp) . " -- ". date("F j, Y, H:i ",$end_stamp) ."</li>
			<li>Reporting Type: $report_types[$report_type]</li>
		</ul>
		</div>";
	
	$totalsec = $timers[ok] + $timers[warning] + $timers[critical] + $timers[unknown] + $timers[no_data];
	
	$data = array(
		"Ok",strTime($timers[ok]),($timers[ok]/$totalsec *100)."%",	
		"Warning",strTime($timers[warning]),($timers[warning]/$totalsec *100)."%",	
		"Critical",strTime($timers[critical]),($timers[critical]/$totalsec *100)."%",	
		"Unknown",strTime($timers[unknown]),($timers[unknown]/$totalsec *100)."%",	
		"No Measurement data",strTime($timers[no_data]),($timers[no_data]/$totalsec *100)."%",
	);
	$headings = array("Status","Time","Percentage" );
	$form->setCols(3);
	$form->setTableWidth("500px");
	$form->setHeadings($headings);
	$form->setSortable(true);
	$form->setData($data);
	echo "<div style='position: relative; float:left; clear:both; width:;'>";
	echo $form->showForm();
	echo "</div";
	
	echo "<div style='position: relative; float:left; margin-left:25px; width:;'>";
	print report_chart($timers);
	echo "</div>";
	$keyData = array();
	$keyData=array();
	$recent_events = Event::get_events_for_checks($report_checks,$start_stamp,$end_stamp);
	foreach ($recent_events as $event_id => $status) {
		unset($event);
		$event = new Event($event_id);

		$status = 3;
		$status = $event->get_status();
		$status_name = $status_array[$status];
	
		array_push($keyData, "<font color=$status_collors[$status]> $status_name </font>");
		array_push($keyData, $event->get_check_name() );
		array_push($keyData, $event->get_hostname() );
                //array_push($keyData, $event->get_check_name() .".tip.". $event->get_key1() ." ". $event->get_key2());

		array_push($keyData, $event->get_insert_date() );
		array_push($keyData, $event->get_last_updated());
		$insert_time = (strtotime($event->get_insert_date()));
		$last_time = (strtotime($event->get_last_updated()));
		
		$diff = strTime($last_time - $insert_time);
		array_push($keyData, $diff);
		array_push($keyData, $event->get_info_msg() );
	}
	$headings = array("Status","Check Name","Host",  "Insert Time", "Last Check", "Duration","Service Information" );
	$form = new Form("auto");
	$form->setCols(7);
	$form->setTableWidth("auto");
	$form->setData($keyData);
	$form->setTitles($keyTitle);

	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(true);
	print "<div style='clear:both'></div><h3>Events</h3>";
	print $form->showForm();
}

function strTime($s) {
	if ($s == 0) {
		return "0s";
	}

	$d = intval($s/86400);
	$s -= $d*86400;

	$h = intval($s/3600);
	$s -= $h*3600;

	$m = intval($s/60);
	$s -= $m*60;

	if ($d) $str = $d . 'd ';
	if ($h) $str .= $h . 'h ';
	if ($m) $str .= $m . 'm ';
	if ($s) $str .= $s . 's';

	return $str;
}

function get_report_data($from,$to,$report_type,$report_checks=array()) {
	global $tool, $form; 
	// Generating reports can take a while
	// Increase time out
	set_time_limit(1500);
	// We also need to increase the memory limit see below

	// Different report types
	// * one_down_all_down ie worst case
	// * one_up_all_up     ie best case
	// * summary
	if (($report_type != 'one_down_all_down') && ($report_type != 'one_up_all_up')) {
		$report_type = 'summary';
	}

	// Check From date
	if ((is_numeric($from))&&(date($from))) {
		$start_stamp = date($from);
	}
	elseif (($from != '') && (strtotime($from))) {
		$start_stamp = strtotime($from);
	} else {
		$form->warning( "Invalid start date $from");
		return false;
	}

	// Check To date
	if ((is_numeric($to))&&(date($to))) {
		$end_stamp = date($to);
	}
	elseif (($to != '') && (strtotime($to))) {
		$end_stamp = strtotime($to);
	} else {
		$form->warning( "Invalid End date $to");
		return false;
	}
	if ($start_stamp > $end_stamp) {
		$form->warning( "Error: End date is before Start date $start_stamp -- $end_stamp");
		return false;
	}

	// print "Generate report From date ". date("Y md",$start_stamp) ." To date ". date("Y md",$end_stamp)." Total secs = $total_secs<br>";
	// Get all events
	// Build time line
	$timers = array();
	$timers[ok] = 0;
	$timers[warning] = 0;
	$timers[critical] = 0;
	$timers[unknown] = 0;
	$timers[other] = 0;
	$aggr_timers = array();
	$aggr_timers[0] = 0;
	$aggr_timers[1] = 0;
	$aggr_timers[2] = 0;
	$aggr_timers[3] = 0;
	$aggr_timers[4] = 0;
	$work_time = $start_stamp;


	// Create a loop per 24 hours, otherwise too much memory usage
	$step = 24*60*60;
	$run_counter = $start_stamp;
	$run = 0;
	//print "will loop from $start_stamp to $end_stamp with increments of $step <br>";
	while ($run_counter < $end_stamp) {
		$run++;
		$run_start = $run_counter;
		$run_end = ($run_start + $step) -1 ;
		if ($run_end > $end_stamp) {
			//print "rewrote run end from $run_end to end time $end_stamp<br>";
			$run_end = $end_stamp;
		}
		$run_counter = $run_counter + $step;
		$all_checks = array();
		//print "<br>working on run $run counter is $run_counter  $run_start - $run_end<br>" ;
		

		// 1 create a hash of all time stamps and their status values for each check
		// this is stored in a 2 dimensional array called all_checks;
		foreach ($report_checks as $my_check_id) {
			unset($events);
			//$events = Event::get_events_for_checks($my_check_id,$start_stamp,$end_stamp);
			$events = Event::get_events_for_checks($my_check_id,$run_start,$run_end);
			foreach($events as $event_id => $status) {
				$event = new Event($event_id);
			
				// Check if event start is before our check time, if so set start to,
				// begin of check period. Otherwise we do unnecesary loops.. waste of resource
				$start_sec = strtotime($event->get_insert_date());
				if ($start_sec < $run_start) {
					$start_sec = $run_start;
				}
				// Same for end
				$end_sec = strtotime($event->get_last_updated());
				if ($end_sec > $run_end) {
					//print "set end from $end_sec to $end_stamp<br>";
					$end_sec = $run_end;
				}
				for ( $counter = $start_sec; $counter <= $end_sec; $counter += 1) {
					$all_checks[$my_check_id][$counter] = $event->get_status();
				}
				unset($counter);
			}
		}

	
		// Now that we have all status values for all seconds between start and end
		// we'll generate the report result;
		// comparison is done based on the report type
		// Loop though all secs and compare status values

		for ( $counter = $run_start; $counter <= $run_end; $counter += 1) {
			// If no value is found we set status to 4, ie no data.
			
			if ($report_type == 'one_down_all_down') {
				$status = 4;
				foreach ($report_checks as $my_check_id) {
					if (! isset($all_checks[$my_check_id][$counter])) {
						if (!($status >= 0 && $status < 4)) {
							$status = 4;
						}
					}
					elseif ($all_checks[$my_check_id][$counter] == 2) {
						$status =2;
					}
					elseif ($all_checks[$my_check_id][$counter] == 1) {
						// Wanrning over rules all but error (2)
						if (($status != 2 ))  {
							$status =1;
						}
					}
					elseif ($all_checks[$my_check_id][$counter] == 0) {
						// OK only over rules unknown and no data
						if (($status != 2) && ($status != 1))  {
							$status =0;
						}
					}
					elseif ($all_checks[$my_check_id][$counter] == 3) {
						// Only set to unknown (3) if all of them are unknown.
						if ( ($status != 0) && ($status != 1)  && ($status != 2) ) {
							$status =3;
						}
					} elseif (!($status >= 0 && $status < 4)) {
						// In case there's no data
						$status =4;
					}
				}
				$aggr_timers[$status] += 1;
			}

			elseif ($report_type == 'one_up_all_up') {
				$status = 4;
				foreach ($report_checks as $my_check_id) {
					if (! isset($all_checks[$my_check_id][$counter])) {
						if (!($status >= 0 && $status < 4)) {
							$status = 4;
						}
					} elseif ($all_checks[$my_check_id][$counter] == 0) {
						$status =0;
					}
					elseif ($all_checks[$my_check_id][$counter] == 1) {
						// Warning only overrules Critical and unknown
						if (($status != 0 ))  {
							$status =1;
						}
					}
					elseif ($all_checks[$my_check_id][$counter] == 2) {
						// critical only overrules unknown and no data
						if (($status != 0) && ($status != 1)) {
							$status =2;
						}
					}
					elseif ($all_checks[$my_check_id][$counter] == 3) {
						// Only set to unknown (3) if all of them are unknown.
						if ( ($status != 0) && ($status != 1)  && ($status != 2) ) {
							$status =3;
						}
					} elseif (!($status >= 0 && $status < 4)) {
						$status = 4;
					}
				}
				$aggr_timers[$status] += 1;
			}
			
			// ### Summary Report Type Calculation ###
			
			elseif ($report_type == 'summary') {
				// Summary uses all samples.
				$status = 4;
				foreach ($report_checks as $my_check_id) {
					if (!isset($all_checks[$my_check_id][$counter])) {
						if (!($status >= 0 && $status < 4)) {
							$status = 4;
						}
					}
					// If ok , set to ok
					elseif (($all_checks[$my_check_id][$counter] >= 0) && ($all_checks[$my_check_id][$counter] <= 3))  {
						$status = $all_checks[$my_check_id][$counter];						
					} else {
						$status = 4;
					}
					// Note that this is in the foreach loop
					// As we want to add all samples;
					$aggr_timers[$status] += 1;
				}
			}
			else {
				print "Invalid report type $report_type<br>";
				break;
			}
		}
		
		/** 
		 if ($report_type == 'summary') {
			// If it's a summary we'll need to divide the results by the number of checks
			// that are in	this report
			$num_checks = count($report_checks);
			foreach ($aggr_timers as $status => $secs) {
				// print "Number of Checks: $num_checks<br>";
				// print "Number of Seconds for Status $status: $secs<br>";
				$aggr_timers[$status] = $secs/$num_checks;
				// print "aggr_timers[status]: $aggr_timers[$status] <br>";
			}
		} */

	}
	
	 if ($report_type == 'summary') {
		// If it's a summary we'll need to divide the results by the number of checks
		// that are in	this report
		$num_checks = count($report_checks);
		foreach ($aggr_timers as $status => $secs) {
			// print "Number of Checks: $num_checks<br>";
			// print "Number of Seconds for Status $status: $secs<br>";
			$aggr_timers[$status] = $secs/$num_checks;
			// print "aggr_timers[status]: $aggr_timers[$status] <br>";
		}
	}
	
	$timers[ok] += $aggr_timers[0];
	$timers[warning] += $aggr_timers[1];
	$timers[critical] += $aggr_timers[2];
	$timers[unknown] += $aggr_timers[3];
	$timers[no_data] += $aggr_timers[4];
	return $timers;
}


function report_chart($data) {

        // Chart
        //
        // This is the MODEL section:
        //
        include 'open-flash-chart/php-ofc-library/open-flash-chart.php';

      
	$title = new title( "Availability Report " );

	$pie = new pie();
	$pie->set_alpha(0.9);
	$pie->radius(90);
	//$pie->start_angle(100);
	$pie->add_animation( new pie_fade() );
	$pie->set_tooltip( '#label#: #percent#<br>#val# of #total#<br>' );
    	$status_colors= array(
	        ok => '#77CC6D',    // (green)
		critical => '#FF0000',    // Red
		warning => '#FFD40F',    // spend (pink)
		unknown => '#6D86CC',    // profit (blue)
		no_data => '#848484',   //  Grey
	);
    	$status_name= array(
	        ok => 'Ok',    
		critical => 'Critical',    
		warning => 'Warning', 
		unknown => 'Unknown',
		no_data => 'No Data',  
	);
		

	$col = array();	
	$d = array();	
	foreach ($data as $name => $value) {
		if ($value > 0) {
			$d[] = new pie_value($value*1, "$status_name[$name]");
			array_push($col, $status_colors[$name]);
		}
	}
	$pie->set_values( $d );
	$pie->set_colours($col);

	$chart = new open_flash_chart();
	$chart->set_title( $title );
	$chart->add_element( $pie );
	$chart->x_axis = null;
	$chart->set_bg_colour('#202020');
        $title->set_style( "{font-size: 16px; font-family: Times New Roman; font-weight: bold; color: #000; text-align: center;}" );
        $chart->set_bg_colour( '#FFFFFF' );
	$chart->set_title( $title );


        // This is the VIEW section:
        // Should print this first.
        //
        $heading = "
        <script type='text/javascript' src='open-flash-chart/js/json/json2.js'></script>
        <script type='text/javascript' src='open-flash-chart/js/swfobject.js'></script>
        <script type='text/javascript'>
        swfobject.embedSWF('open-flash-chart/open-flash-chart.swf', 'my_chart', '300', '300', '9.0.0');
        </script>

        <script type='text/javascript'>

        function open_flash_chart_data() {
                return JSON.stringify(data);
        }

        function findSWF(movieName) {
                if (navigator.appName.indexOf('Microsoft')!= -1) {
                        return window[movieName];
                } else {
                        return document[movieName];
                }
        }
    
        var data = ". $chart->toPrettyString() ."

        </script>


        <script type=\"text/javascript\">
 
        OFC = {};
 
        OFC.jquery = {
        name: 'jQuery',
        version: function(src) { return $('#'+ src)[0].get_version() },
        rasterize: function (src, dst) { $('#'+ dst).replaceWith(OFC.jquery.image(src)) },
        image: function(src) { return \"<img src='data:image/png;base64,\" + $('#'+src)[0].get_img_binary() + \"' />\"},
        popup: function(src) {
        var img_win = window.open('', 'Charts: Export as Image')
        with(img_win.document) {
            write('<html><head><title>Charts: Export as Image<\/title><\/head><body>' + OFC.jquery.image(src) + '<\/body><\/html>') }
                // stop the 'loading...' message
                img_win.document.close();
        }
        }
 
        // Using_ an object as namespaces is JS Best Practice. I like the Control.XXX style.
        //if (!Control) {var Control = {}}
        //if (typeof(Control == \"undefined\")) {var Control = {}}
        if (typeof(Control == \"undefined\")) {var Control = {OFC: OFC.jquery}}
 
 
        // By default, right-clicking on OFC and choosing \"save image locally\" calls this function.
        // You are free to change the code in OFC and call my wrapper (Control.OFC.your_favorite_save_method)
        // function save_image() { alert(1); Control.OFC.popup('my_chart') }
        function save_image() { alert(\"Your image will be displayed in a new window\"); OFC.jquery.popup('my_chart') }
        </script>
        <div id='my_chart' style='float:left; margin-left:28px;'></div>
        ";
	return $heading;

}
function convert($size) {
	$unit=array('b','kb','mb','gb','tb','pb');
	return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
}

function displayAllTemplates()
{
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form, $status_array, $status_collors;
	$toolNames = array("Add Template");
	$toolIcons = array("add");
	$toolHandlers = array("handleEvent('".$_SERVER['PHP_SELF']."?action=addTemplate')");
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);

	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	foreach (CheckTemplate::get_templates() as $id => $name)
	{
		$template = new CheckTemplate($id);
		array_push($keyHandlers, "handleEvent('".$_SERVER['PHP_SELF']."?action=showTemplate&templateid=".$template->get_template_id()."')");
		array_push($keyData, $template->get_name() );
		array_push($keyData, $template->get_desc() );
		array_push($keyData, $template->get_script() );
	}
	$headings = array("Name","Description","Script" );
        
	$form->setCols(3);
	$form->setTableWidth("800px");
	$form->setData($keyData);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(true);
	echo $form->showForm();
	
}

function displayTemplate() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form, $status_array, $status_collors;

	$toolNames = array("Edit Template","Delete Template");
	$toolIcons = array("edit","delete");
	$toolHandlers = array(
			"handleEvent('".$_SERVER['PHP_SELF']."?action=editTemplate&templateid=".$_GET[templateid]."')",
			"handleEvent('".$_SERVER['PHP_SELF']."?action=deleteTemplate&templateid=".$_GET[templateid]."')"
	);
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);

	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	if (is_numeric($_GET[templateid])) {
		$template = new CheckTemplate($_GET[templateid]);
	} else {
		$form->warning("Invalid template id" );     
		return;
	}
	
	$keyData=array($template->get_name(), $template->get_desc(), $template->get_script(),
			$template->get_key1_name(), $template->get_key2_name(),nl2br($template->get_notes())
	);
	$headings = array("Check Template Details");
	$keyTitle=array("Name.tip.descriptive name for this template",
		"Description.tip.A usefull description","Script.tip.Script to be executed",
		"Key1 Description", "Key2 Description","Notes");
        
	$form->setCols(2);
	$form->setTableWidth("700px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(false);
	echo $form->showForm();
	
}

function editTemplate() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form, $status_array, $status_collors;

	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	if (is_numeric($_GET[templateid])) {
		$template = new CheckTemplate($_GET[templateid]);
	} else {
		$form->warning("Invalid template id" );     
		return;
	}
	
	$keyData=array($template->get_name(), $template->get_desc(), $template->get_script(),
			$template->get_key1_name(), $template->get_key2_name(),$template->get_notes()
	);
	$headings = array("Edit Check Template ");
	$keyTitle=array("Name.tip.descriptive name for this template",
		"Description.tip.A usefull description","Script.tip.Script to be executed",
		"Key1 Description", "Key2 Description","Notes");
        
	$postKeys=array("template_name","template_desc","template_script","template_key1_desc","template_key2_desc","template_notes");
	$form->setCols(2);
	$form->setTableWidth("700px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(false);
        $fieldType[5]= "text_area";
	$form->setFieldType($fieldType);
	$form->setUpdateValue("updateTemplate") ;
        $form->setDatabase($postKeys);
	echo $form->editForm();
	
}
function updateTemplate() {
	global $tool, $form; 
	if (is_numeric($_GET[templateid])) {
		$template = new CheckTemplate($_GET[templateid]);
	} else {
		$form->warning("Invalid Template id" );     
		return;
	}
	$template->set_name(trim($_POST[template_name]));
	$template->set_desc(trim($_POST[template_desc]));
	$template->set_script(trim($_POST[template_script]));
	$template->set_key1_name(trim($_POST[template_key1_desc]));
	$template->set_key2_name(trim($_POST[template_key2_desc]));
	$template->set_notes(trim($_POST[template_notes]));
	if ($template->update()) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showTemplate&templateid=".$_GET[templateid]."&update=success\">";

	} else {
		$form->warning("Update failed " . $template->get_error() );     
		return;
	}
}

function addTemplate() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form;

	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	
	$headings = array("New Template ");
	$keyTitle=array("Name.tip.descriptive name for this template",
		"Description.tip.A usefull description","Script.tip.Script to be executed",
		"Key1 Description", "Key2 Description","Notes");
        
	$postKeys=array("template_name","template_desc","template_script","template_key1_desc","template_key2_desc","template_notes");
	$form->setCols(2);
	$form->setTableWidth("700px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(false);
        $fieldType[5]= "text_area";
	$form->setFieldType($fieldType);
	$form->setUpdateValue("insertTemplate") ;
	$form->setUpdateText("Add new Template") ;
        $form->setDatabase($postKeys);
	echo $form->editForm();
	
}

function insertTemplate() {
	global $tool, $form; 
	$template = new CheckTemplate();
	$template->set_name($_POST[template_name]);
	$template->set_desc(trim($_POST[template_desc]));
	$template->set_script(trim($_POST[template_script]));
	$template->set_key1_name(trim($_POST[template_key1_desc]));
	$template->set_key2_name(trim($_POST[template_key2_desc]));
	$template->set_notes(trim($_POST[template_notes]));

	$template_id = false;
	$template_id = $template->insert();
	if ($template_id) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showTemplate&templateid=$template_id&update=success\">";

	} else {
		$form->warning("Insert failed " . $template->get_error() );     
		return;
	}
}
function deleteTemplate() {
	global $tool, $form; 
	if (is_numeric($_GET[templateid])) {
		$template = new CheckTemplate($_GET[templateid]);
	} else {
		$form->warning("Invalid Template id" );     
		return;
	}
	// Confimration part
	if(isset($_POST['deleteYes'])) {
		if ($template->delete()) {
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=list_templates&delete=success\">";
		} else {
			$form->warning("Could not  delete domain. ". $template->get_error() );
			return false;
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo'])) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showTemplate&templateid=".$_GET[templateid]."\">";
	} else {
		$form->prompt("Are you sure you want to delete this Template?");
	}

}


function displayAll()
{
        //global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
        global $tool, $form, $status_array, $status_collors;

	$toolNames = array("Configure Checks","Check Templates","Reports");
	$toolIcons = array("device","icons/checklist.png","stat");
	$toolHandlers = array(
		"handleEvent('".$_SERVER['PHP_SELF']."?action=list_checks')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=list_templates')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=list_ReportProfiles')",
	);
	echo "<h2>Events Dashboard</h2>";
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);


        $keyHandlers = array();
        $keyData = array();
        $keyTitle = array();
        foreach (Event::get_events() as $id => $name)
        {
                $event = new Event($id);
		$check_id = $event->get_check_id();
		$check = new Check($check_id);
		if (is_null($check_id)) {
                	array_push($keyHandlers, "");
		} else {
                	array_push($keyHandlers, "handleEvent('monitor.php?action=showCheck&checkid=$check_id')");
		}
                $status = 3;
                $status = $event->get_status();
                $status_name = $status_array[$status];
                array_push($keyData, "<font color=$status_collors[$status]> $status_name </font>");
                array_push($keyData, $event->get_hostname() );
                array_push($keyData, $event->get_check_name() .".tip.". $check->get_desc() ."<br> ". $event->get_key1() ."<br>".$event->get_key2());

                array_push($keyData, $event->get_insert_date() );
                array_push($keyData, $event->get_last_updated());
                $insert_time = (strtotime($event->get_insert_date()));
                $last_time = (strtotime($event->get_last_updated()));
                $diff = strTime($last_time - $insert_time);

                array_push($keyData, $diff);
                array_push($keyData, $event->get_info_msg() );
        }
        $headings = array("Status","Host", "Service", "Insert Time", "Last Check", "Duration","Service Information" );

        $form->setCols(7);
        $form->setTableWidth("90%");
        $form->setData($keyData);
        $form->setEventHandler($keyHandlers);
        $form->setHeadings($headings);
        $form->setSortable(true);

        echo $form->showForm();
	echo "<meta http-equiv=\"REFRESH\" content=\"300;url=".$_SERVER['PHP_SELF']."\">";

}

function addReportProfile() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form, $report_types;
	include_once 'classes/Contact.php';

	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	$postKeys = array();
	$allGroups = array_merge(array(""=>""),Contact::get_groups());
	
	$form->setType($allGroups); // Drop down
	$form->setType($report_types); // Drop down

	$headings = array("New Report Profile ");
	$postKeys=array("profile_name","profile_client","profile_report_type","profile_notes");
	$keyTitle=array("Name.tip.descriptive name for this profile","Client.tip.Optionally specify the contact client",
		"Report Type","Notes");

       	$keyData[2] ="Summary Report"; 
	$form->setCols(2);
	$form->setData($keyData);
	$form->setTitles($keyTitle);
        $form->setDatabase($postKeys);
	$form->setCols(2);
	$form->setTableWidth("500px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
        $form->setDatabase($postKeys);

        $fieldType[1]= "drop_down";
        $fieldType[2]= "drop_down";
        $fieldType[3]= "text_area";
	$form->setFieldType($fieldType);

	$form->setHeadings($headings);
	$form->setSortable(false);
	$form->setUpdateValue("insertProfile") ;
	$form->setUpdateText("Add new Profile") ;
	echo $form->editForm();
	
}

function insertReportProfile() {
	global $tool, $form; 
	$profile = new CheckReportProfile();
	$profile->set_name($_POST[profile_name]);
	$profile->set_contact_id(trim($_POST[profile_client]));
	$profile->set_notes(trim($_POST[profile_notes]));
	$profile->set_report_type(trim($_POST[profile_report_type]));

	$profile_id = false;
	$profile_id = $profile->insert();
	if ($profile_id) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showReportProfile&profileid=$profile_id&add=success\">";

	} else {
		$form->warning("Insert failed " . $profile->get_error() );     
		return;
	}
}


function displayReportProfile() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form,$report_types ;
	include_once 'classes/Contact.php';

	$toolNames = array("Edit Profile","Add Check","Delete Profile","Create Report");
	$toolIcons = array("edit","add","delete","report");
	$toolHandlers = array(
		"handleEvent('".$_SERVER['PHP_SELF']."?action=editReportProfile&profileid=".$_GET[profileid]."')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=add_check_to_profile&profileid=".$_GET[profileid]."')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=deleteReportProfile&profileid=".$_GET[profileid]."')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=renderProfileReportForm&profileid=".$_GET[profileid]."')"
	);
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);

	if (is_numeric($_GET[profileid])) {
		$profile = new CheckReportProfile($_GET[profileid]);
	} else {
		$form->warning("Invalid Profile id id" );     
		return;
	}

	// Create left div
	echo "<div style='position: relative; float:left; clear:both; width:;'>";

	$headings = array("Report Profile Details");
	$keyTitle=array("Name.tip.descriptive name for this profile","Client.tip.Optionally specify the contact client",
		"Report Type","Notes");


	$keyHandlers = array();
	$keyData = array();
	
	// Get contact name
	if ($profile->get_contact_id()) {
		$contact = new Contact($profile->get_contact_id());		
		$contact_name = $contact->get_name();
	} else {
		$contact_name  ="N/A";
	}
	
	$keyData=array($profile->get_name(), $contact_name, $report_types[$profile->get_report_type()],
			nl2br($profile->get_notes())
	);
	$form->setCols(2);
	$form->setTableWidth("450px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(false);
	echo $form->showForm();
	echo "</div>";

	// Right div
	echo "<div style='position: relative; float:left; margin-left:25px; width:'>";

	// Now we need to create a link to add checks
	// Here a table of checks
	
	$checks = $profile->get_checks();
	if ($profile->get_error()) {
		print "error ". $profile->get_error() ."<br>";
	}
	$headings = array("Delete", "Check Name", "Template","Device");

	$keyHandlers = array();
	$keyData = array();

	foreach ($checks as $check_id => $check_name) {
		$check = new Check($check_id);
		array_push($keyData,
			"<a href='".$_SERVER['PHP_SELF']."?action=del_check_from_profile&profileid=".$profile->get_profile_id()."&checkid=".$check_id."'>".
			"<img src='icons/Delete.png' height=20px></a>",$check->get_name(), $check->get_template_name(), $check->get_hostname());
	}
	$form2 = new Form("auto");
	$form2->setCols(4);
	$form2->setTableWidth("auto");
	$form2->setData($keyData);
	$form2->setEventHandler($keyHandlers);
	$form2->setHeadings($headings);
	$form2->setSortable(true);
	echo $form2->showForm();
	echo "</div>";
}

function editReportProfile() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form,$report_types ;
	include_once 'classes/Contact.php';

	$toolNames = array("Edit Profile","Delete Profile","Create Report");
	$toolIcons = array("edit","delete","report");
	$toolHandlers = array(
		"handleEvent('".$_SERVER['PHP_SELF']."?action=editReportProfile&profileid=".$_GET[profileid]."')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=deleteReportProfile&profileid=".$_GET[profileid]."')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=renderReportProfileReport&profileid=".$_GET[profileid]."')"
	);
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);

	if (is_numeric($_GET[profileid])) {
		$profile = new CheckReportProfile($_GET[profileid]);
	} else {
		$form->warning("Invalid Profile id id" );     
		return;
	}

	$allGroups[""] = "";
	foreach(Contact::get_groups() as $id => $name) {
		 $allGroups[$id] = $name;
	}
	$form->setType($allGroups); // Drop down
	$form->setType($report_types); // Drop down

	$headings = array("Edit Report Profile Details");
	$postKeys=array("profile_name","profile_client","profile_report_type","profile_notes");
	$keyTitle=array("Name.tip.descriptive name for this profile","Client.tip.Optionally specify the contact client",
		"Report Type","Notes");

	$keyHandlers = array();
	$keyData = array();
	if ($profile->get_contact_id()) {
		$contact = new Contact($profile->get_contact_id());		
		$contact_name = $contact->get_name();
	} else {
		$contact_name  ="";
	}
	$keyData=array($profile->get_name(), $contact_name, $report_types[$profile->get_report_type()],
			$profile->get_notes()
	);
	$postKeys=array("profile_name","profile_client","profile_report_type","profile_notes");

        $fieldType[1]= "drop_down";
        $fieldType[2]= "drop_down";
        $fieldType[3]= "text_area";
	$form->setFieldType($fieldType);

	$form->setCols(2);
	$form->setTableWidth("450px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setDatabase($postKeys);
	$form->setHeadings($headings);
	$form->setSortable(false);
	$form->setUpdateValue("updateReportProfile") ;
	echo $form->editForm();
}

function updateReportProfile() {
	global $tool, $form; 
	if (is_numeric($_GET[profileid])) {
		$profile = new CheckReportProfile($_GET[profileid]);
	} else {
		$form->warning("Invalid Profile id id" );     
		return;
	}
	$profile->set_name($_POST[profile_name]);
	$profile->set_contact_id(trim($_POST[profile_client]));
	$profile->set_notes(trim($_POST[profile_notes]));
	$profile->set_report_type(trim($_POST[profile_report_type]));

	if ($profile->update()) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showReportProfile&profileid=".$_GET[profileid]."&update=success\">";

	} else {
		$form->warning("Update failed " . $profile->get_error() );     
		return;
	}
}

function deleteReportProfile() {
	global $tool, $form; 
	if (is_numeric($_GET[profileid])) {
		$profile = new CheckReportProfile($_GET[profileid]);
	} else {
		$form->warning("Invalid Profile id id" );     
		return;
	}
	if(isset($_POST['deleteYes'])) {
		if ($profile->delete()) {
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=listReportProfiles&delete=success\">";
	
		} else {
			$form->warning("Update failed " . $profile->get_error() );     
			return;
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo'])) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showReportProfile&profileid=".$_GET[profileid]."\">";
	} else {
		$form->prompt("Are you sure you want to delete this profile ". $profile->get_name() ."?");
	}
}

function displayAllReportProfiles() {
	//global the tool and make a tool bar for adding a device and display all the archived device, and displaying the IP Report
	global $tool, $form, $status_array, $status_collors;
	$toolNames = array("Add Profile","Create Report","All Reports");
	$toolIcons = array("add","add","stat");
	$toolHandlers = array(
		"handleEvent('".$_SERVER['PHP_SELF']."?action=addReportProfile')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=create_multipe_reports')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=list_reports')",
	);
	echo "<h2>Report Profiles</h2>";
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	foreach (CheckReportProfile::get_profiles() as $id => $name)
	{
		$profile = new CheckReportProfile($id);
		array_push($keyHandlers, "handleEvent('".$_SERVER['PHP_SELF']."?action=showReportProfile&profileid=".$profile->get_profile_id()."')");
		array_push($keyData, $profile->get_name() );
	}
	$headings = array("Name");
        
	$form->setCols(1);
	$form->setTableWidth("500px");
	$form->setData($keyData);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(true);
	echo $form->showForm();
}

function addCheckToProfileForm() {
	global $tool, $form;
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	if (is_numeric($_GET[profileid])) {
		$profile = new CheckReportProfile($_GET[profileid]);
	} else {
		$form->warning("Invalid Profile id id" );     
		return;
	}
	echo "<h2>Add check to profile ". $profile->get_name()."</h2>";
	$filter .=  "<p>";
	$tool = new EdittingTools();
	$filter .= $tool->createNewFilters();
	$filter .=  "<div style=\"clear:both;\"></div> </p>";
	echo $filter;
	

	foreach (Check::get_checks() as $id => $name)
	{
		$check = new Check($id);
		array_push($keyData, "<input type=checkbox name=checks[] value='$id'" );
		array_push($keyData, $check->get_name() );
		array_push($keyData, $check->get_hostname() );
		array_push($keyData, $check->get_desc() );
		array_push($keyData, $check->get_template_name() );
	}
	$headings = array("Select","Name","Host", "Description", "Template");
        
	$form->setCols(5);
	$form->setTableWidth("1024px");
	$form->setData($keyData);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(true);
	// manually create form
	echo "<p><b>Select the checks you'd like to add to this profile.</b></p>";
	echo "<br><form action='' id='dataForm' method='POST' name='dataForm'>";
	echo $form->showForm();
	echo "<br><div style='clear:both;'><br></div>";
	echo "<INPUT TYPE='SUBMIT' VALUE='Add Selected Checks' name='addChecksToProfile'>";
	echo "</form>";
}

function addChecksToProfile() {
	global $form;
	if (is_numeric($_GET[profileid])) {
		$profile = new CheckReportProfile($_GET[profileid]);
		$profile_id = $_GET[profileid];
	} else {
		$form->warning("Invalid Profile id id" );     
		return;
	}
	$warning = '';;
	foreach ($_POST['checks'] as $check_id) {
		if ($profile->add_check($check_id)) {
		} else {
			$warning .= $profile->get_error() ."<br>";
		}
	}
	if ($warning != '') {
		$form->warning($warning);     
		return;
	} else {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showReportProfile&profileid=$profile_id&add=success\">";
	}
}

function delCheckFromProfile() {
	global $form;
	if (is_numeric($_GET[profileid])) {
		$profile = new CheckReportProfile($_GET[profileid]);
		$profile_id = $_GET[profileid];
	} else {
		$form->warning("Invalid Profile id id" );     
		return;
	}
	if (is_numeric($_GET[checkid])) {
		$check_id = $_GET[checkid];
	} else {
		$form->warning("Invalid Check id" );     
		return;
	}
	if ($profile->delete_check($check_id)) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showReportProfile&profileid=$profile_id&delete=success\">";
	} else{
		$form->warning($warning);     
		return;
	}
}
function renderProfileReportForm() {
	// This is to render a form for a per check report
	global $tool, $form, $report_types;
	echo "<h2>Please specify the reporting period below</h2>";
	if (is_numeric($_GET[profileid])) {
		$profile = new CheckReportProfile($_GET[profileid]);
		$profile_id = $_GET[profileid];
	} else {
		$form->warning("Invalid Profile id id" );     
		return;
	}
	foreach ($profile->get_checks() as $check_id => $check_name) {
		 "$check_id";
	}
	$headings = array("Reporting period ");
	$postKeys=array("action","profileid","from","to");
	$keyTitle=array("action","profileid","Start Date","End Date");
	$keyData=array("renderCheckReport","$profile_id","","");

	
	$form->setCols(2);
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setDatabase($postKeys);
	$form->setTableWidth("auto");

	$form->setType($report_types); // Drop down
	$fieldType[0]= "hidden";
	$fieldType[1]= "hidden";
	$fieldType[2]= "date_picker";
	$fieldType[3]= "date_picker";
	$form->setFieldType($fieldType);

	$form->setHeadings($headings);
	$form->setSortable(false);
	$form->setUpdateValue("") ;

	$form->setUpdateText("Show Report") ;
	$form->setMethod("GET");
	echo $form->editForm();
}

function renderCreateMultipeReports() {
	global $tool, $form, $report_types;
                // Menu bar
	$content .= '<h2>Create Report</h2>';
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
		
        
		foreach (CheckReportProfile::get_profiles() as $id => $name) {
			$profile = new CheckReportProfile($id);
			array_push($keyData, 
				"<input type='checkbox' name='profile_id[]' value='$id'>",
				$profile->get_name()
			);
		}

		$headings = array("Select All<input name='all' type='checkbox' value='Select All' onclick=\"checkAll(document.dataForm['profile_id[]'],this)\" ","Profile");

		$form->setCols(2);
		$form->setTableWidth("auto");
		$form->setData($keyData);
		$form->setHeadings($headings);
		$form->setSortable(true);
		// manually create form
		$content .= "<p><b>Select the profiles you'd like to add to this report.</b></p>";
		$content .= "<form action='' id='dataForm' method='POST' name='dataForm'>";
		$content .= $form->showForm();
		$content .= "<div style='clear:both'></div>
			<INPUT TYPE=SUBMIT VALUE='Add Selected Checks' name='insert_multiple_reports'>
			<INPUT TYPE=hidden NAME=action VALUE='insert_multiple_reports'>
			<INPUT TYPE=hidden NAME=date1 VALUE='".$_POST['date1']."'>
			<INPUT TYPE=hidden NAME=date2 VALUE='".$_POST['date2']."'>
			<INPUT TYPE=hidden NAME=name VALUE='".$_POST['name']."'>
		</form>";
	}
        echo $content;
}

function insertMultipeReports() {
	// This creates multiple reports and saves them
	global $tool, $form, $report_types;
	
	$from = $_POST['date1'];
	$to = $_POST['date2'];
	$name = $_POST['name'];
	
	if ($name == '') {
		$form->warning( "Invalid Name, can not be empty");
		return false;
	}

	// Check From date
	if ((is_numeric($from))&&(date($from))) {
		$start_stamp = date($from);
	}
	elseif (($from != '') && (strtotime($from))) {
		$start_stamp = strtotime($from);
	} else {
		$form->warning( "Invalid start date $from");
		return false;
	}

	// Check To date
	if ((is_numeric($to))&&(date($to))) {
		$end_stamp = date($to);
	}
	elseif (($to != '') && (strtotime($to))) {
		$end_stamp = strtotime($to);
	} else {
		$form->warning( "Invalid End date $to");
		return false;
	}
	flush();
	$i=1;
	$total = count($_POST[profile_id]);
	print "<p><b>Creating $total reports for $name, please be patient as this may take a few minutes<br></p></b>";
	flush();
	foreach ($_POST[profile_id] as $profile_id) {
		$profile = new CheckReportProfile($profile_id);
		print "creating report $i of $total: ". $profile->get_name()." Type (".$profile->get_report_type() .").<br>";
		flush();
		$report_checks = array();
		foreach ($profile->get_checks() as $check_id => $check_name) {
			array_push($report_checks, $check_id);
		}
		$report_type = $profile->get_report_type();
		$timers = get_report_data($start_stamp,$end_stamp,$report_type,$report_checks);

		$report = new Report();
		$report->set_name($name);
		$report->set_profile_id($profile_id);
		$report->set_report_type($profile->get_report_type());
		$report->set_start_time($from);
		$report->set_end_time($to);
		$report->set_ok_secs($timers[ok]);
		$report->set_warning_secs($timers[warning]);
		$report->set_critical_secs($timers[critical]);
		$report->set_unknown_secs($timers[unknown]);
		$report->set_other_secs($timers[other]);
		$report->set_no_data_secs($timers[no_data]);
		$new_id = $report->insert();
		if (is_numeric($new_id)) {
			//print "new is is $new_id<br>";
		} else {
			print "insert failed! reason: " . $report->get_error() ."<br>";
		}
		$i++;
	}
	print "<br><br>Done! You're reports are available here: <a href='".$_SERVER['PHP_SELF']."?action=display_reports_by_name&name=$name'> Availability Report $name</a><br>";
}

function displayReports() {
	// This creates multiple reports and saves them
	global $tool, $form, $report_types;
	$report_names = Report::get_report_names();
	if ((!$report_names) || (count($report_names) < 1)) {
		$form->warning( "Sorry no reports found");
		return false;
	}
	print "<h2>Available SLA reports</h2>";


	$headings = array("Report Name");
	$keyTitle=array();
	$keyHandlers = array();
	$keyData = array();
	
	foreach ($report_names as $name) {
		array_push($keyData, $name);
		array_push($keyHandlers, "handleEvent('".$_SERVER['PHP_SELF']."?action=display_reports_by_name&name=$name')");
	}
	$form->setCols(1);
	$form->setTableWidth("450px");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(true);
	echo $form->showForm();
}

function displayReportsByName() {
	// This creates multiple reports and saves them
	global $tool, $form, $report_types;
	$name = $_GET[name];
	if ((!$name) || ($name == '')) {
		$form->warning( "invalid name ");
		return false;
	}
	print "<h2>SLA report: $name</h2>";

	$toolNames = array("Export to CSV","Delete");
	$toolIcons = array("icons/checklist.png","delete");
	$toolHandlers = array(
		"handleEvent('".$_SERVER['PHP_SELF']."?action=export_report&name=$name')",
		"handleEvent('".$_SERVER['PHP_SELF']."?action=delete_reports&name=$name')",
	);
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);


	$headings = array("Name","Up Time","From","To","Ok","Warning","Critical","Unknown","No Data");
	$keyTitle=array();
	$keyHandlers = array();
	$keyData = array();
	$reports = Report::get_reports_by_name($name);
	
	foreach ($reports as $report_id => $profile_id) {
		$report = new Report($report_id);
		$profile = new CheckReportProfile($profile_id);
		array_push($keyData, $profile->get_name(), $report->get_up_percentage()."%",$report->get_start_time(),$report->get_end_time(),
			strTime($report->get_ok_secs()), strTime($report->get_warning_secs()),strTime($report->get_critical_secs()),
			strTime($report->get_unknown_secs()),strTime($report->get_no_data_secs()));
		array_push($keyHandlers, "handleEvent('".$_SERVER['PHP_SELF']."?action=display_report_id&report_id=$report_id')");
	}
	$form->setCols(9);
	$form->setTableWidth("auto");
	$form->setData($keyData);
	$form->setTitles($keyTitle);
	$form->setEventHandler($keyHandlers);
	$form->setHeadings($headings);
	$form->setSortable(true);
	echo $form->showForm();
}

function deleteReports() {
	global $tool, $form, $report_types;
	$name = $_GET[name];
	if ((!$name) || ($name == '')) {
		$form->warning( "invalid name ");
		return false;
	}
	$numreports =count(Report::get_reports_by_name($name));
	if ($numreports == 0) {
		$form->warning( "No reports found for $name ");
		return false;
	}
	// Confimration part
	if(isset($_POST['deleteYes'])) {
		$success = true;
		$msg = '';
		foreach (Report::get_reports_by_name($name) as $report_id => $profile_id) {
			$report = new Report ($report_id);
			if ($report->delete()) {
			} else {
				$success = false;
				$msg .= "<br>Could not delete report id $report_id. Reason: ". $report->get_error();
			}
		}
		if ($success) {
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=list_reports&delete=success\">";
		} else {
			$form->warning("Could not  delete reports:<br> $msg  ");
			return false;
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo'])) {
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=display_reports_by_name&name=$name\">";
	} else {
		$form->prompt("Are you sure you want to delete $numreports for $name?");
	}

}

function export_csv() {
	session_start();
	if (!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
        	header("Location:login.php");
	}

	$ini_array = parse_ini_file("config/cmdb.conf");
	$dbhost = $ini_array['db_host'];
	$dbport = $ini_array['db_port'];
	$dbuser = $ini_array['db_user'];
	$dbpass = $ini_array['db_pass'];
	$dbname = $ini_array['db_name'];

	$conn = mysql_connect("$dbhost:$dbport", $dbuser, $dbpass) or die  ('Error connecting to mysql');
	mysql_select_db($dbname);
	include_once 'classes/Check.php';


	$file_name = trim($_GET['name']) . ".csv";
	header("Content-type:text/octect-stream");
	header("Content-Disposition:attachment;filename=\"$file_name\"");

	$heading = array("Name","Uptime %","From","To","Ok","Warning","Critical","Unknown","No Data","Ok (secs)","Warning (secs)","Critical (secs)","Unknown (secs)","No Data (secs)");
	$content = implode(",", $heading);
	$content .= "\n";

	$reports = Report::get_reports_by_name($_GET[name]);
	foreach ($reports as $report_id => $profile_id) {
		$data=array();
		$report = new Report($report_id);
		$profile = new CheckReportProfile($profile_id);
		array_push($data, $profile->get_name(), $report->get_up_percentage(),$report->get_start_time(),$report->get_end_time(),
			strTime($report->get_ok_secs()), strTime($report->get_warning_secs()),strTime($report->get_critical_secs()),
			strTime($report->get_unknown_secs()),strTime($report->get_no_data_secs()),
			$report->get_ok_secs(), $report->get_warning_secs(),$report->get_critical_secs(),
			$report->get_unknown_secs(),$report->get_no_data_secs()
		);
		$content .= stripslashes(implode(",",$data));
		$content .= "\n";

	}
	print $content; ;

}


?>

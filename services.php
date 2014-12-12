<?php
include_once("sessionCheck.php");
//if it's not in ajax mode include the control bar
if(!isset($_GET['mode']))
{include("controlBar.php");}
?>

<?
if(!isset($_GET['mode']))
{
?>
<div id="main">
<h1 id="mainTitle">SERVICES</h1>

<?
}
//add in all the sripts
?>

<?
/*Database coding: this checks for multiple different actions made by users and responds accordingly.*/
include_once "classes/Service.php";
include_once "classes/Device.php";
include_once "classes/Contact.php";
include_once 'classes/ServiceForm.php';
include_once 'classes/EdittingTools.php';
include_once 'classes/PopLocations.php';

//Make a new service, a new tool bar, and a new form
$tool = new EdittingTools();
$serviceForm = new ServiceForm("auto", 5);
$services = new Service();
$status;

$keyHandlers = array();
$keyData = array();
$keyTitle = array();
//infoKey generates a set of key names to store the key values
$serviceKey = array();
				
//heading is the array of headlines in the table
$headings = array("Service ID", "Customer", "Service Name", "Service Type");

// Specify Service status
$status_array = array(
	'Testing' => 'Testing',
	'In Production' => 'In Production',
	'Out of Service' => 'Out of Service'
);
//titles are the subcategories for each headline. In the array "heading" means make a room there for the headline
$titles = array();

//display the status for success and failures
switch (success)
{
	case $_GET['update']:
	$serviceForm->success("Updated successfully");
	break;
	
	case $_GET['add']:
	$serviceForm->success("Added new data successfully");
	break;
	
	case $_GET['delete']:
	$serviceForm->success("Deleted data successfully");
	break;
}

//if the user is editting or viewing and ID, check if there is an update being made. If there are no updates then show them the service information form.
if(($_GET['action'] == edit && $_SESSION['access'] >= 50) || $_GET['action'] == showID)
{
	//get the new service corresponding to the ID
	$services = new Service($_GET['ID']);
	
	//checks to see if this is a valid ID or not
	if($services->get_service_id() =="")
	{
		$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
		$serviceForm->error("Warning: Failed to load. Reason: ".$services->get_error(), $link );
	}
	
	//if this is a service update then update the service
	if(isset($_POST['updateInfo']) && $_SESSION['access'] >= 50)
	{
		$layer = $services->get_service_layer();
		if ($layer == 2)
		{$services = new Layer2_service($_GET['ID']);}
		else {$services = new Layer3_service($_GET['ID']);}
		updateService($services);		
	}
	//if the update is an interface update, update that
	else if(isset($_POST['updateModal']) && $_SESSION['access'] >= 50)
	{
		$services =  new Layer2_service_port($_POST['interfaceID']);
		updateModal($services);
	}
	
	//if the port needs to be added
	else if(isset($_POST['addPort']) && $_SESSION['access'] >= 50)
	{
		$servicePort = new Layer2_service_port();
		addPort($servicePort, $services);
	}
	//or else display all services
	else
	{
		displayService($services);
	}
}
//if the user is editting or viewing and ID, check if there is an update being made. If there are no updates then show them the service information form.
else if(($_GET['action'] == editServiceType && $_SESSION['access'] >= 50) || $_GET['action'] == showServiceType)
{
	//get the new service type corresponding to the ID
	$serviceType = new ServiceType($_GET['ID']);
	if (isset($_POST['updateInfo']))
	{
		updateServiceType($serviceType);	
	}
	else
	{
		displayServiceType($serviceType);
	}
}
/// Reporting
else if($_GET['action'] == 'serviceReports')
{
	render_service_reports();
}
else if($_GET['action'] == 'detailedServiceReports')
{
	render_detailed_service_report();
}
//display the graph detail
else if($_GET['action'] == showGraphDetail)
{
	displayGraphDetail();
}

//if the user wants to zoom the graph he's looking at
else if($_GET['action'] == zoomGraphDetail)
{

?><!--Cropper Files--->
<script src="js/cropper/lib/prototype.js" type="text/javascript"></script>      
<script src="js/cropper/lib/scriptaculous.js?load=builder,dragdrop" type="text/javascript"></script>
<script src="js/cropper/cropper.js" type="text/javascript"></script>
<script src="js/cropper/smokeping-zoom.js" type="text/javascript"></script>
<?

	zoomGraphDetail();
}

//if the user prompts to add a new service
else if ($_GET['action'] == add && $_SESSION['access'] >= 50)
{
	//if the user is adding the service, then add it
	if(isset($_POST['addData']))
	{
		$curSerType = new ServiceType($_POST['serviceType']);
		
		$layer = $curSerType->get_service_layer();
		//add the service depending the layer
		if ($layer == 2)
		{$services = new Layer2_service($_GET['ID']);}
		else {$services = new Layer3_service($_GET['ID']);}
		
		addService($services, $layer);					
	}
	//or else display the form for the adding a new service
	else {
		addServiceForm($services);								
	}
}

//if the user prompts to add a new service
else if ($_GET['action'] == addServiceType && $_SESSION['access'] >= 50)
{
	$serviceType = new ServiceType();
	//if the user is adding the service, then add it
	if(isset($_POST['addData']))
	{
		addServiceType($serviceType);					
	}
	//or else display the form for the adding a new service
	else {
		addServiceTypeForm($services);								
	}
}
				
//if the user prompts to remove a service
else if($_GET['action'] == remove && $_SESSION['access'] >= 50)
{
	//remove a port
	if(isset($_GET['portID']))
	{
		$servicePort = new Layer2_service_port($_GET['portID']);
		removeService($servicePort);
	}
	else
	{
		//get the service ID and remove that
		$services = new Service($_GET['ID']);
		removeService($services);
	}
}

//if the user prompts to remove a service type
else if($_GET['action'] == removeServiceType && $_SESSION['access'] >= 50)
{
	$serviceType = new ServiceType($_GET['ID']);
	removeServiceType($serviceType);
}

//if nothing else, display all the archived service for the user to see
else if($_GET['action'] == showAllServiceTypes)
{
	displayAllServiceTypes();
}
//if nothing else, display all the archived service for the user to see
else if($_GET['action'] == showArchived)
{
	displayAllArchived($services);
}
				
//if nothing else, display all the services for the user to see
else
{
	displayAll($services);
}
?>
</div>        
<?php 
if(!isset($_GET['mode']))
{include("footer.php");} ?>


<?
/*****************************************************FUNCTIONS************************************************/

function render_detailed_service_report() {

	$start_date = $_GET[start_date];
	$end_date = $_GET[end_date];

	// Start filter
	$allServiceTypes = ServiceType::get_service_types();	
	$allServiceTypes = array('all' => 'all');
	$allServiceTypes = array_merge($allServiceTypes, ServiceType::get_service_types());
	$service_type = $_GET['service_type'];
	$service_filter = $_GET['service_type'];
	if (($service_type == '') || (!isset($service_type)) ||(!is_numeric($service_type))) {
		$service_type = 'all';
		$service_filter = '';
	}
	$filter = "
		<FORM>
		<DIV style=\" \">
		<SELECT name'=service_type_report'
			onChange=\"window.location='services.php?action=detailedServiceReports&start_date=$start_date&end_date=$end_date&service_type='+this.options[this.selectedIndex].value;\">";
	foreach($allServiceTypes as $id => $name) {
		if ($service_type == "$id") {
			$selected = "SELECTED";
		} else {
			$selected = '';
		}
		$filter .= "<OPTION value='$id' $selected>$name\n";
        }
        $filter .= " 
                </SELECT>
                </DIV>
                </FORM>
        ";
        // End filter

	$start_date = $_GET[start_date];
	$end_date = $_GET[end_date];
	$content = "<h1>Detailed Service Reports</h1>
		<b>Reporting Period: $start_date to $end_date</b>
	<br>	<b>Service type: $filter</b><br>";

	// Start with added services
	$in_prod = Service::get_inprod_services_diff_date ($start_date, $end_date, $service_filter);
	$form = new Form(auto, 4);
	$headings = Array("Service ID","Service Description","Service Type","Client");
	$data = Array();
	$handlers = Array();
	foreach ($in_prod as $sid) {
		unset($service);
		$service = new Service($sid);
		array_push($data, $sid, $service->get_name(), $service->get_service_type_name(),$service->get_contact_name());
		array_push ($handlers,"handleEvent('services.php?action=showID&ID=$sid')"); 
	}
	$form->setTableWidth("500px");
	$form->setData($data);
	$form->setHeadings($headings);
	$form->setSortable(true);
	$form->setEventHandler($handlers);
	// Add in a diff
        $content .= "<div style='float:left; clear:both;'>
		<h3>Services that went into production</h3>".
		$form->showForm()
		."</div>";

	// lost  services
	$out_prod = Service::get_outprod_services_diff_date ($start_date, $end_date, $service_filter);	
	unset($form);
	$form = new Form(auto, 4);
	$headings = Array("Service ID","Service Description","Service Type","Client");
	$data = Array();
	$handlers = Array();
	foreach ($out_prod as $sid) {
		unset($service);
		$service = new Service($sid);
		array_push($data, $sid, $service->get_name(), $service->get_service_type_name(),$service->get_contact_name());
		array_push ($handlers,"handleEvent('services.php?action=showID&ID=$sid')"); 
	}
	$form->setTableWidth("500px");
	$form->setData($data);
	$form->setHeadings($headings);
	$form->setSortable(true);
	$form->setEventHandler($handlers);
	// Add in a diff
        $content .= "<div style='float:left; margin-left:20px;'>
		<h3>Services that went out of production</h3>".
		$form->showForm()
		."</div>";
	print $content;
}

function render_service_reports() {
	$content = "<h1>Service Reports</h1>";

	// Start Filter for  service type
	$allServiceTypes = ServiceType::get_service_types();	
#	$allServiceTypes = array('all' => 'all');
	#$allServiceTypes = array_merge($allServiceTypes, ServiceType::get_service_types());
	$allServiceTypes['all'] = 'all';
	$service_type = $_GET['service_type'];
	$service_filter = $_GET['service_type'];
	if (($service_type == '') || (!isset($service_type)) ||(!is_numeric($service_type))) {
		$service_type = 'all';
		$service_filter = '';
	}
	$filter = "
		<FORM>
		<DIV style=\" \">
		<SELECT name'=service_type_report'
			onChange=\"window.location='services.php?&action=serviceReports&service_type='+this.options[this.selectedIndex].value;\">";
	foreach($allServiceTypes as $id => $name) {
		if ($service_type == $id) {
			$selected = "SELECTED";
		} else {
			$selected = '';
		}
		$filter .= "<OPTION value='$id' $selected>$name\n";
	}	
	$filter .= "
		</SELECT>
		</DIV>
		</FORM>
	";
	// End filter
	
	$max_date = strtotime("2009-01-01");
	$start_date = date("Y-m")."-01";
	// Get all months since start
	$workdate = strtotime($start_date);
	$now = strtotime("Now");
	$form = new Form(auto, 3);
	$headings = Array("Period","In production","Out of Production");
	$data = Array();
	$x_ax_data = Array();
	$y_ax_data = Array();
	$handlers=Array();

	
	while ($workdate > $max_date)  {
		$sql_enddate = date("Y-m-d", $workdate);
		$month_period = date("m-Y",$workdate);

		
		// This is for the chart
		$month_label = date("M\nY",$workdate);
		$graph_date = strtotime("-1 month", $workdate);
		$month_label = date("M\nY",$graph_date);
		$sql_startdate = date("Y-m-d", $workdate);
		array_push($x_ax_data, $month_label);
		array_push($y_ax_data, count(Service::get_inprod_services_at_date($sql_startdate,$service_filter)));

		// Add one month
		$workdate = strtotime("-1 month", $workdate);
		$sql_startdate = date("Y-m-d", $workdate);
		$out_of_prod = count(Service::get_outprod_services_diff_date($sql_startdate,$sql_enddate,$service_filter));
		$in_prod = count(Service::get_inprod_services_diff_date($sql_startdate,$sql_enddate,$service_filter));

		array_push($data, "$sql_startdate $sql_enddate");
		array_push($data, $in_prod);
		array_push($data, $out_of_prod);
		array_push ($handlers,"handleEvent('services.php?action=detailedServiceReports&start_date=$sql_startdate&end_date=$sql_enddate&service_type=$service_type')"); 
	}
	$form->setTableWidth("224px");
	$form->setData($data);
	$form->setEventHandler($handlers);
	$form->setHeadings($headings);
	$form->setSortable(true);
        $content .= "<div style=\"float: left; clear: both; margin-right:28px\">". $form->showForm() . "</div>";

	// Chart
	//
	// This is the MODEL section:
	//

	include 'open-flash-chart/php-ofc-library/open-flash-chart.php';

	// create an X Axis object
	//
	$y_ax_data = array_reverse($y_ax_data);
	$x_ax_data = array_reverse($x_ax_data);
	$x = new x_axis();
	$x->set_steps( 3 );
	$x->set_labels_from_array($x_ax_data);

	$max = max($y_ax_data);
	$y = new y_axis();
	$y->set_range( 0,$max ); 

	// Bar
	$bar = new bar();
	$bar->set_values( $y_ax_data );
	// Bar
	

	// ------- LINE 2 -----
	$line_2_default_dot = new dot();
	$line_2_default_dot->size(3)->halo_size(1)->colour('#3D5C56');

	$line_2 = new line();
	$line_2->set_default_dot_style($line_2_default_dot);
	$line_2->set_values( $y_ax_data );
	$line_2->set_width( 3 );
	$line_2->set_colour( '#3D5C56' );

	$chart = new open_flash_chart();
	$title = new title( "In Production Services over Time" );
	$title->set_style( "{font-size: 10px; font-family: Times New Roman; font-weight: bold; color: #000; text-align: center;}" );
	$chart->set_bg_colour( '#FFFFFF' );
	$chart->set_title( $title );
	$chart->add_element( $bar );
	//$chart->add_element( $line1 );
	$chart->add_element( $line_2 );
	$chart->set_x_axis( $x );
	$chart->set_y_axis( $y );
	//
	// This is the VIEW section:
	// Should print this first.
	//
	$heading = "
	<script type='text/javascript' src='open-flash-chart/js/json/json2.js'></script>
	<script type='text/javascript' src='open-flash-chart/js/swfobject.js'></script>
	<script type='text/javascript'>
	swfobject.embedSWF('open-flash-chart/open-flash-chart.swf', 'my_chart', '660', '350', '9.0.0');
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

	print " $content\n 
		<div style=''<b>Select Service Type:</b>$filter <br></div>\n
		$heading ";
}


//This function displays all the services
function displayAll($services)
{
	//global the tool and make a tool bar for adding a service
	global $tool, $serviceForm, $keyHandlers, $keyTitle, $keyData;
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	
	//add the tool if it's not in Ajax mode
	if(!isset($_GET['mode']))
	{
		if ($_SESSION['access'] >= 50)
		{
			$toolNames = array("Add Services", "All Archived Services", "All Service Types","Service Reports");
			$toolIcons = array("add", "service", "service","stat");
			$toolHandlers = array("handleEvent('services.php?action=add')", "handleEvent('services.php?action=showArchived')",
				"handleEvent('services.php?action=showAllServiceTypes')",
				"handleEvent('services.php?action=serviceReports&service_type=all')"
			);
		}
		else
		{
			$toolNames = array("All Archived Services","Service Reports");
			$toolIcons = array("service","stat");
			$toolHandlers = array("handleEvent('services.php?action=showArchived')","handleEvent('services.php?action=serviceReports&service_type=all')");
		}						
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
		
		$filters = array();
		$allServiceTypes = ServiceType::get_service_types();
		$allLocation = Location::get_locations();
		
		//print_r($allServiceTypes);
		//print_r($allLocation);
		$allFilters = array("Service Types"=>$allServiceTypes, "Locations"=>$allLocation);
		
		echo $tool->createNewFilters($allFilters);
	}
	
	//check if it's filtering options NO NEED
	/*if($_GET['action']==filter)
	{
		//store the data corresponding to the filtering options
		foreach (Service::get_services() as $id => $name)
		{
			$serviceInfo = new Service($id);
			$serviceID = $serviceInfo->get_service_id();
			$layer = $serviceInfo->get_service_layer();
			$serviceType = $serviceInfo->get_service_type_name();
			
			/*UNUSED SCRIPT STATIC SCRIPT TAKEN OUT
			if(isset($serviceID))
			{
				if($_GET['l2']==yes)
				{
					if ($layer == 2)
					{
						if ($_GET['iptransit']==yes) {pushData($serviceInfo, $serviceType, 'Transit');}							
						if ($_GET['oran']==yes) {pushData($serviceInfo, $serviceType, 'oran');}
						if ($_GET['ix']==yes) {pushData($serviceInfo, $serviceType, 'ix');}
						if ($_GET['layer2Vlan']==yes) {pushData($serviceInfo, $serviceType, 'l2_vlan');}
						if ($_GET['cu_all']==yes) {pushData($serviceInfo, $serviceType, 'CU_ALL');}
					}
				}			
				if ($_GET['l3']==yes)
				{
					if ($layer == 3)
					{
						if ($_GET['iptransit']==yes) {pushData($serviceInfo, $serviceType, 'Transit');}							
						if ($_GET['oran']==yes) {pushData($serviceInfo, $serviceType, 'oran');}
						if ($_GET['ix']==yes) {pushData($serviceInfo, $serviceType, 'ix');}
						if ($_GET['layer2Vlan']==yes) {pushData($serviceInfo, $serviceType, 'l2_vlan');}
						if ($_GET['cu_all']==yes) {pushData($serviceInfo, $serviceType, 'CU_ALL');}
					}
				}		
			}
		}		
	}
	//or else display all the service
	else
	{*/
	$allFilter = array();
	foreach (Service::get_services() as $id => $name)
	{
		$serviceInfo = new Service($id);
		$serviceID = $serviceInfo->get_service_id();
		
		$locClass = "";
		$locations = $serviceInfo->get_locations();
		
		foreach ($locations as $lId => $lValue)
		{
			$locClass .= $lValue." ";
		}
		
		$filterClass = $locClass.$serviceInfo->get_service_type_name();
		
		if(isset($serviceID))
		{
			array_push($allFilter, $filterClass);
			array_push($keyHandlers, "handleEvent('services.php?action=showID&ID=$id')");
			array_push($keyData, $serviceInfo->get_service_id());
			array_push($keyData, $serviceInfo->get_contact_name());
			array_push($keyData, $serviceInfo->get_name());
			array_push($keyData, $serviceInfo->get_service_type_name());	
			array_push($keyData, $serviceInfo->get_status());	
		}
		else{};
	}
	//}
	
	//display the services
	echo "<div id='filteredResult'>";
	$headings = array("Service ID", "Customer", "Service Name", "Service Type","Status");
	$serviceForm->setFilter($allFilter);
	echo $serviceForm->showAll($headings, $keyTitle, $keyData, $keyHandlers);
	echo "</div>";
}

//function to push the data into the ports ********TAKEN OUT For JQUERY*******
/*function pushData($serviceInfo, $serviceType, $instance)
{
	global $tool, $serviceForm, $keyHandlers, $keyTitle, $keyData;
	if($serviceType == $instance)
	{
		array_push($keyHandlers, "handleEvent('services.php?action=showID&ID=".$serviceInfo->get_service_id()."')");
		array_push($keyTitle, $serviceInfo->get_service_id());
		array_push($keyData, $serviceInfo->get_contact_name());
		array_push($keyData, $serviceInfo->get_name());
		array_push($keyData, $serviceInfo->get_service_type_name());
	}
}*/

//This function displays all the archived services
function displayAllArchived($services)
{
	//global the tool and make a tool bar for adding a service
	global $tool, $serviceForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Services", "All Services");
		$toolIcons = array("add", "service");
		$toolHandlers = array("handleEvent('services.php?action=add')", "handleEvent('services.php')");
	}
	else
	{
		$toolNames = array("All Services");
		$toolIcons = array("service");
		$toolHandlers = array("handleEvent('services.php')");
	}					
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	
	//store all the archived data
	foreach (Service::get_services(1) as $id => $name)
		{
			$serviceInfo = new Service($id);
			$serviceID = $serviceInfo->get_service_id();
			if(isset($serviceID))
			{
				array_push($keyHandlers, "handleEvent('services.php?action=showID&ID=$id')");
				array_push($keyTitle, $serviceInfo->get_service_id());
				array_push($keyData, $serviceInfo->get_contact_name());
				array_push($keyData, $serviceInfo->get_name());
				array_push($keyData, $serviceInfo->get_service_type_name());	
			}
			else{};
		}
	
	//get all the service and display them all in the 3 sections "Service ID", "Customer", "Service Name", "Service Type"
	echo "<div id='filteredResult'>";
	$headings = array("Service ID", "Customer", "Service Name", "Service Type");
	$serviceForm->setCols(4);
	echo $serviceForm->showAll($headings, $keyTitle, $keyData, $keyHandlers);
	echo "</div>";
}

function displayAllServiceTypes()
{
	//global the tool and make a tool bar for adding a service
	global $tool, $serviceForm;
	
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Service Type", "All Services");
		$toolIcons = array("add", "service");
		$toolHandlers = array("handleEvent('services.php?action=addServiceType')", "handleEvent('services.php')");
	}
	else
	{
		$toolNames = array("All Services");
		$toolIcons = array("service");
		$toolHandlers = array("handleEvent('services.php?action=addServiceType')", "handleEvent('services.php')");
	}
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	$headings = array("Service Type", "Description", "Layer");
	$title = array();
	$data = array();
	$handlers = array();
	$allServiceTypes = ServiceType::get_service_types();
	foreach ($allServiceTypes as $id => $value)
	{
		$curService = new ServiceType($id);
		array_push($data, $curService->get_name(), $curService->get_description(), $curService->get_service_layer());
		array_push($handlers, "handleEvent('services.php?action=showServiceType&ID=".$id."')");
	}
	
	$serviceForm->setCols(3);
	echo $serviceForm->showAll($headings, $title, $data, $handlers);
}

function displayServiceType($serviceType)
{
	//global the tool and make a tool bar for adding a service
	global $tool, $serviceForm;
	
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Edit Service Type", "Remove Service Type");
		$toolIcons = array("edit", "delete");
		$toolHandlers = array("handleEvent('services.php?action=editServiceType&ID=".$_GET['ID']."')", "handleEvent('services.php?action=removeServiceType&ID=".$_GET['ID']."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	$heading = array($serviceType->get_name());
	$title = array("Description", "Layer");
	$data = array($serviceType->get_description(), $serviceType->get_service_layer());
	
	$serviceForm->setCols(2);
	
	if($_GET['action']=='editServiceType')
	{
		$heading = array("Service Type Information");
		$title = array("Name", "Description", "Layer");
		$data = array($serviceType->get_name(), $serviceType->get_description(), $serviceType->get_service_layer());
		$key = array("name", "desc", "layer");
		$fieldType = array(2=>"static");
		//$type = array(2=>2, 3=>3);
		
		$serviceForm->setFieldType($fieldType);
		echo $serviceForm->editServiceForm($heading, $title, $data, $key);
	}
	else
	{
		echo $serviceForm->showServiceForm($heading, $title, $data);
	}
	
}

function updateServiceType($serviceType)
{
	$key = array("name", "desc", "layer");
	
	//global all variables
	global $serviceForm;
	
	//create an empty temporary array to store all the new values given from the form
	$tempServiceInfo=array();
	foreach($key as $index => $key)
	{
		$tempServiceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	
	if ($tempServiceInfo['name'] != '')
	{
		if ($serviceType->set_name($tempServiceInfo['name']))
		{
			$serviceType->set_desc($tempServiceInfo['desc']);
			$serviceType->set_service_layer($tempServiceInfo['layer']);
			
			if($serviceType->update())
			{
				$status="success";
				$_SESSION['action'] = "Updated service type: ".$tempServiceInfo['name'];
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showServiceType&ID=".$_GET['ID']."&update=$status\">";
			}
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
				$serviceForm->error("Warning: Failed to update. Reason: ".$serviceType->get_error(), $link);
			}
			
		}
	}
	else
	{
		$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
		$serviceForm->error("Warning: Failed to update. Reason: Name is blank", $link);
	}
}

function addServiceTypeForm()
{
	//global all variables
	global $serviceForm;
	
	$heading = array("Service Type Information");
	$title = array("Name", "Description", "Layer");
	$key = array("name", "desc", "layer");
	$fieldType = array(2=>"drop_down");
	$type = array(2=>2, 3=>3);
		
	$serviceForm->setFieldType($fieldType);
	$serviceForm->setCols(2);
	echo $serviceForm->newServiceForm($heading, $title, $key, $type);	
}

function addServiceType($serviceType)
{
	$key = array("name", "desc", "layer");
	
	//global all variables
	global $serviceForm;
	
	//create an empty temporary array to store all the new values given from the form
	$tempServiceInfo=array();
	foreach($key as $index => $key)
	{
		$tempServiceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	
	if ($tempServiceInfo['name'] != '' && $tempServiceInfo['layer'] != '')
	{
		if ($serviceType->set_name($tempServiceInfo['name']))
		{
			$serviceType->set_desc($tempServiceInfo['desc']);
			$serviceType->set_service_layer($tempServiceInfo['layer']);
			
			if($serviceType->insert())
			{
				$status="success";
				$_SESSION['action'] = "Added new service type: ".$tempServiceInfo['name'];
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showAllServiceTypes&add=$status\">";
			}
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
				$serviceForm->error("Warning: Failed to update. Reason: ".$serviceType->get_error(), $link);
			}
			
		}
	}
	else
	{
		$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
		$serviceForm->error("Warning: Failed to update. Reason: Name or layer is blank", $link);
	}
}

//Updating the service. This is where the service stores updated values
function updateService($services)
{
	//global all variables
	global $serviceKey, $serviceForm, $status, $serviceTypes, $location;
					
	//create an empty temporary array to store all the new values given from the form
	$tempServiceInfo=array();
	
	//checks what layer it is and give the corresponding key to the service
	if($services->get_service_layer() == 2)
	{
		$serviceKey = array("cusName", "cusID", "serviceType", "stats", "description", "notes","status","in_production","out_production",
							"vlanNum");
	}
	else {
		$serviceKey = array("cusName", "cusID", "serviceType", "stats", "description", "notes","status","in_production","out_production",
							"device", "interface", "interfaceMTU", "tagged", "vlanNum",
							"logiRout", "routType", "ASNum", "trafPolice",
							"ipv4Uni", "ipv4Multi", "pRoutAd4", "cRoutAd4", "pRoutAd4-length", "cRoutAd4-length", "prefix4",
							"ipv6Uni", "ipv6Multi", "pRoutAd6", "cRoutAd6", "pRoutAd6-length", "cRoutAd6-length", "prefix6");
	}
	
	foreach($serviceKey as $index => $key)
	{
		$tempServiceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempServiceInfo[notes] = addslashes($tempServiceInfo[notes]);
	$tempServiceInfo[description] = addslashes($tempServiceInfo[description]);
	//check if this ID is valid
	if (!(is_numeric($services->get_service_id())))
	{
		$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
		$serviceForm->error("Warning: Failed to update. Reason: ".$services->get_error(), $link);
 	}
	//if it works then set all the values into the service and update it
	else{
		//makes sure the service has a name
		/*$serviceKey = array("name", "device_fqdn", "location_name", "device_type_name", "device_oob" "notes", "snmp_ro", "snmp_rw", "snmp_version", "ro_user", "ro_password");*/
		if ($services->set_name($tempServiceInfo[description]))
		{
			$services->set_in_production_date($tempServiceInfo[in_production]);
			$services->set_out_production_date($tempServiceInfo[out_production]);
			$services->set_status($tempServiceInfo[status]);

			//set all the values to the query according to the service layer
			if($services->get_service_layer() == 2)
			{
				$services->set_portal_statistics($tempServiceInfo[stats]);
				$services->set_notes($tempServiceInfo[notes]);
				$services->set_vlan_id($tempServiceInfo[vlanNum]);
				$services->set_service_type($tempServiceInfo[serviceType]);
			}
			elseif($services->get_service_layer() == 3) {
				$services->set_portal_statistics($tempServiceInfo[stats]);
				$services->set_notes($tempServiceInfo[notes]);
				$services->set_service_type($tempServiceInfo[serviceType]);
				
				$services->set_pe_id($tempServiceInfo[device]);
				$services->set_port_name($tempServiceInfo['interface']);
				$services->set_mtu($tempServiceInfo[interfaceMTU]);
				$services->set_tagged($tempServiceInfo[tagged]);
				$services->set_vlan_id($tempServiceInfo[vlanNum]);
				
				$services->set_logical_router($tempServiceInfo[logiRout]);
				$services->set_routing_type($tempServiceInfo[routType]);
				$services->set_bgp_as($tempServiceInfo[ASNum]);
				$services->set_traffic_policing($tempServiceInfo[trafPolice]);
				
				$services->set_ipv4_unicast($tempServiceInfo[ipv4Uni]);
				$services->set_ipv4_multicast($tempServiceInfo[ipv4Multi]);
				$services->set_pe4_address($tempServiceInfo[pRoutAd4]);
				$services->set_ce4_address($tempServiceInfo[cRoutAd4]);
				$services->set_pe4_address_length($tempServiceInfo['pRoutAd4-length']);
				$services->set_ce4_address_length($tempServiceInfo['cRoutAd4-length']);
				$services->clear_ipv4_prefixes();
				$prefix4 = explode("\n", $tempServiceInfo[prefix4]);
				foreach($prefix4 as $id => $prefix)
				{
					//echo $prefix." <br />";
					$services->add_ipv4_prefixes($prefix);
				}
				
				$services->set_ipv6_unicast($tempServiceInfo[ipv6Uni]);
				$services->set_ipv6_multicast($tempServiceInfo[ipv6Multi]);
				$services->set_pe6_address($tempServiceInfo[pRoutAd6]);
				$services->set_ce6_address($tempServiceInfo[cRoutAd6]);
				$services->set_pe6_address_length($tempServiceInfo['pRoutAd6-length']);
				$services->set_ce6_address_length($tempServiceInfo['cRoutAd6-length']);
				$services->clear_ipv6_prefixes();
				$prefix6 = explode("\n", $tempServiceInfo[prefix6]);
				foreach($prefix6 as $id => $prefix)
				{
					//echo $prefix." <br />";
					$services->add_ipv6_prefixes($prefix);
				}
			}
								
			//if the update is sucessful go back to show the new updates or else show an error
			if($services->update())
			{
				$status="success";
				$_SESSION['action'] = "Updated service: ".$tempServiceInfo[description];
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]&update=$status\">";
			}
			else{
				$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
				$serviceForm->error("Warning: Failed to update. Reason: ".$services->get_error(), $link);
			}
		}
		//if there are no names then show error
		else
		{	
			$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
			$serviceForm->error("Warning: Failed to update. Reason: ".$services->get_error(), $link);
		}
	}
}

//Updating the interface ports. This is where the interface ports stores updated values
function updateModal($services)
{
	//global all variables
	global $serviceKey, $serviceForm, $status, $serviceTypes, $location;
	
	
	//create an empty temporary array to store all the new values given from the form
	$tempServiceInfo=array();
	$serviceKey = array("interfaceID", "deviceName", "portName", "tagged", "vlan", "mtu");
	
	
	foreach($serviceKey as $index => $key)
	{
		$tempServiceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempServiceInfo[portName] = addslashes($tempServiceInfo[portName]);
	$tempServiceInfo[deviceName] = addslashes($tempServiceInfo[deviceName]);
	
	//check if this ID is valid
	if (!(is_numeric($services->get_service_id())))
	{
		$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
		$serviceForm->error("Warning: Failed to update. Reason: ".$services->get_error(), $link);
 	}
	//if it works then set all the values into the interface ports and update it
	else{
		//makes sure the interface ports has a name
		/*$serviceKey = array("name", "device_fqdn", "location_name", "device_type_name", "device_oob" "notes", "snmp_ro", "snmp_rw", "snmp_version", "ro_user", "ro_password");*/
		if ($services->set_port_name($tempServiceInfo[portName]))
		{
			//set all the values to the query
			$services->set_device_id($tempServiceInfo[deviceName]);
			$services->set_tagged($tempServiceInfo[tagged]);
			$services->set_vlan_id($tempServiceInfo[vlan]);
			$services->set_mtu($tempServiceInfo[mtu]);
										
			//if the update is sucessful go back to show the new updates or else show an error
			if($services->update())
			{
				$status="success";
				$_SESSION['action'] = "Updated service port: ".$tempServiceInfo[portName];
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]&update=$status\">";
			}
			else{
				$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
				$serviceForm->error("Warning: Failed to update. Reason: ".$services->get_error(), $link);
			}
		}
		//if there are no names then show error
		else
		{
			$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
			$serviceForm->error("Warning: Failed to update. Reason: ".$services->get_error(), $link);
		}
	}
}
				
//display the current service information
function displayService($services)
{
	//global all variables
	global $serviceKey, $serviceForm, $tool, $headings, $titles, $serviceTypes, $location, $status_array;
	
	$serviceForm->setCols(2);
	
	//make the tool bar for this page
	if ($_SESSION['access']>=50)
	{
		$toolNames = array("Edit Service", "Delete Service");
		$toolIcons = array("edit", "delete");
		$toolHandlers = array("handleEvent('services.php?action=edit&ID=$_GET[ID]', 'devicePart');",
							"handleEvent('services.php?action=remove&ID=$_GET[ID]')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	//check the service layer and display according to the layer
	//if the layer is 3
	if($services->get_service_layer() == 3)
	{
		//store the values in the heading and title array
		$headings = array("Service Information", 
				  "*<break>*", "Port Specific Information",
				  "*<break>*", "Routing Specific Information",
				  "*<break>*", "IPv4 Information",
				  "*<break>*", "IPv6 Information");
		$titles = array("Customer name", "Customer ID", "Service type", "Service ID", 
							"Include statistics in portal?.tip.If YES is selected the user will be able to see the statistics for this particular service in the wiki portal. If this is not selected, traffic stats for this service will not be available in the wiki portal", 
							"Service description (name).tip.A useful description for this service", 
							"Notes.tip.Here you can add generic notes for this service, for example: This is a tempory backup connection.",
					"Status.tip.Specifies the production status of this Service", "In production date", "Out of production date",
					"*<break>*", "Device for Service", 
							"Interface.tip.Name of the physical interface, for example ge-2/0/1<br>Do not use subinterface format no ge-2/0/1.10", 
							"Interface MTU Size.tip.Default for ORAN and CU_ALL is 9000 bytes, the commodity and IX instance use 1500", 
							"Interface tagged", 
							"Vlan number.tip.Please enter a vlan number. If this is an untagged routed port and has no vlan, than please use 0. This means no vlan configuration",
					"*<break>*", "Logical router", "Routing type", "AS number", "Traffic Policing",
					"*<break>*", "IPv4 unicast", "IPv4 multicast", 
							"BCNET router address.tip.IPv4 address of the BCNET side of this link. Please include masklenght. Format: x.x.x.x/30", 
							"Customer router address.tip.IPv4 address of the Customer side of this link. Please include masklenght. Format: x.x.x.x/30", "IPv4 prefix",
					"*<break>*", "IPv6 unicast", "IPv6 multicast", "BCNET router address", "Customer router address", "IPv6 prefix");
		
		$layer3Service = new Layer3_service($services->get_service_id());
		
		//add all the prefixes for ipv4 and ipv6 together
		foreach(array_keys($layer3Service->get_prefixes(4)) as $prefix)
		{
			$address4Prefix .= $prefix . "<br />";
		}
		foreach(array_keys($layer3Service->get_prefixes(6)) as $prefix)
		{
			$address6Prefix .= $prefix . "<br />";
		}
		//switch the values into strings
		($layer3Service->get_portal_statistics()==1)?$stats='Yes':$stats='No';
		($layer3Service->get_tagged()==1)?$tagged='Tagged':$tagged='Untagged';
		($layer3Service->get_ipv4_unicast()==1) ?$uni4='True':$uni4='False';
		($layer3Service->get_ipv4_multicast()==1)?$multi4='True':$multi4='False';
		($layer3Service->get_ipv6_unicast()==1)?$uni6='True':$uni6='False';
		($layer3Service->get_ipv6_multicast()==1)?$multi6='True':$multi6='False';
		
		//store the values in the info
		$info = array($layer3Service->get_contact_name(), $layer3Service->get_contact_id(), $layer3Service->get_service_type_name(), $layer3Service->get_service_id(), $stats, $layer3Service->get_name(), $layer3Service->get_notes(),
			$layer3Service->get_status(), $layer3Service->get_in_production_date(), $layer3Service->get_out_production_date(),
			$layer3Service->get_pe_name(), $layer3Service->get_port_name(), $layer3Service->get_mtu(), $tagged, $layer3Service->get_vlan_id(),
			$layer3Service->get_logical_router(), $layer3Service->get_routing_type(), $layer3Service->get_bgp_as(), $layer3Service->get_traffic_policing(),
			$uni4, $multi4, $layer3Service->get_pe_address(4), $layer3Service->get_ce_address(4), $address4Prefix,
			$uni6, $multi6, $layer3Service->get_pe_address(6), $layer3Service->get_ce_address(6), $address6Prefix);
	}
	//if the layer is 2
	elseif($services->get_service_layer() == 2) {
		
		//store the values in the heading and title array
		$headings = array("Service Information", 
				  "*<break>*", "Layer 2 Specific Information");
		$titles = array("Customer name", "Customer ID", "Service type", "Service ID", "Include statistics in portal?", "Service description (name)", "Notes",
					"Status.tip.Specify the production status of this Service","In production date", "Out of production date",
					"*<break>*", "Vlan number");
			
		$layer2Service = new Layer2_service($services->get_service_id());
		
		//change the value into strings
		($layer2Service->get_portal_statistics()==1)?$stats='Yes':$stats='No';
		$info = array($layer2Service->get_contact_name(), $layer2Service->get_contact_id(), $layer2Service->get_service_type_name(), $layer2Service->get_service_id(), $stats, $layer2Service->get_name(), $layer2Service->get_notes(), $layer2Service->get_status(), $layer2Service->get_in_production_date(), $layer2Service->get_out_production_date(),$layer2Service->get_vlan_id());
					  
		$layer2Interfaces = $layer2Service->get_interfaces();
		
		//store the interface port into the array
		$titles2 = array();
		$info2 = array();
		$headings2 = array("Device Name", "Port name", "Tagged", "Vlan", "MTU", "Actions");
		$handlers = array();
		//~atoonk/test/rrd/graph.php?file=deviceid12_ge-0-0-3.653&titel=cr1.keltx1.bc.net -- ge-0/0/3.653
		
		//add the ports information together
		$key = array("interfaceID", "deviceName", "portName", "tagged", "vlan", "mtu");
		$titlePort = array("Interface ID", "Device Name", 
							"Port name.tip.Name of the physical interface, for example ge-2/0/1<br>Do not use subinterface format no ge-2/0/1.10", "Tagged", 
							"Vlan.tip.Please enter a vlan number. If this is an untagged routed port and has no vlan, than please use 0. This means no vlan configuration", 
							"MTU.tip.Default for ORAN and CU_ALL is 9000 bytes, the commodity and IX instance use 1500");
		$infoPort = array();
		$headingPort = array('Port Information');
		$fieldType = array("static", "drop_down", "", "radio", "", "");
		$types = array( Device::get_devices());
		//push all the interface port information
		foreach ($layer2Interfaces as $id => $value)
		{	
			$infoPort = array();
			//Array ( [service_interface_id] => 184 [device_id] => 11 [device_name] => cr1.victx1.bc.net [port_name] => ge-0/1/2 [tagged] => 1 [vlan_id] => 0 [mtu] => 1500 )
			$oriPortName = $value[port_name];	
			$portName = str_replace("/", "-", $value[port_name]);
			$portName = str_replace(" ", "-", $portName);

			// Determine port alias /descs
			//print_r(Port::get_device_interfaces($value[device_id]));
			
	
			if ($value[tagged] == 1)
			{
				// Nortel hack
				// Nortel BPS / baystack switches don't append the vlan id to the interface
				// So if it's a Nortel switch don't append
				// Nortel interfaces start with ifc24 (Slot: 1 Port: 24)
				if (! preg_match("/ifc\d+\s\(Slot:/", $oriPortName)) {
					$oriPortName = $oriPortName.".".$value[vlan];
					$portName = $portName.".".$value[vlan];
				} 
			}

			// Determine port alias /descs
			$device = new Device($value[device_id]);
			$port = new Port($device->get_interface_id_by_name($oriPortName));
			$port_alias = $port->get_alias();
			$port_alias = '';
			if ($port->get_alias() != '') {
				$port_alias = " <i> (".$port->get_alias() .")</i>";
			}
			// Done Determine port alias /descs

			$link='rrdgraph.php?file=deviceid'.$value[device_id]."_".$portName.".rrd&title=".$value[device_name]."%20--%20".$oriPortName;
			array_push($handlers, $link);
			
			array_push($titles2, $value['device_name']."//".$device->get_interface_id_by_name($oriPortName)."//".$value[device_id]);
			array_push($infoPort, $value['service_interface_id']);
			array_push($infoPort, $value['device_name']);
			foreach ($value as $subID => $subValue)
			{
				if($subID=="tagged"){
					if($subValue == 1){
						array_push($info2, 'Tagged');
						array_push($infoPort, 'Tagged');
					}
					else {
						array_push($info2, 'Untagged');
						array_push($infoPort, 'Untagged');
					}
				}
				else
				{
					if($subID != "service_interface_id" && $subID != "device_id" && $subID != "device_name"){
						// Append port desc
						if ($subID == 'port_name') {
							array_push($info2, $subValue.$port_alias);
							array_push($infoPort, $subValue);
						} else {
							array_push($info2, $subValue);
							array_push($infoPort, $subValue);
						}
					}
				}
			}
			if ($_SESSION['access'] >= 50)
			{array_push($info2, "<a name=modal href='#dialog".$id."'>Edit</a> | <a href='#' onclick=\"handleEvent('services.php?action=remove&ID=$_GET[ID]&portID=$id')\">Delete</a>");}
			else
			{array_push($info2, 'No Access');}
			//create the modal form for current values for the interface ports
			$serviceForm2=$serviceForm;
			$serviceForm2->setFieldType($fieldType);
			$ff .= $serviceForm2->modalForm($headingPort, $titlePort, $infoPort, $key, $types, "", "dialog".$id);
		}
		
		//For ports
		$newKey = array("deviceName", "portName", "tagged", "vlan", "mtu");
		$newTitlePort = array("Device Name", 
							"Port name.tip.Name of the physical interface, for example ge-2/0/1<br>Do not use subinterface format no ge-2/0/1.10", 
							"Tagged", 
							"Vlan.tip.Please enter a vlan number. If this is an untagged routed port and has no vlan, than please use 0. This means no vlan configuration", 
							"MTU.tip.Default for ORAN and CU_ALL is 9000 bytes, the commodity and IX instance use 1500");
		$newHeadingPort = array('Port Information');
		
		//create a new modal form for a new interface ports
		$fieldType = array("drop_down","","radio","","");
		$serviceForm->setFieldType($fieldType);
		$serviceForm->setNewModalID("newInterface");
		echo $serviceForm->newModalForm($newHeadingPort, $newTitlePort, $newKey, $types);
	}
			
	//if the user is editting this information, make it all editable
	if ($_GET['action'] == edit)
	{
		// Get all L3 & L2 service types
		$allServiceTypes = ServiceType::get_service_types();
		$lay3Types = array();
		$lay2Types = array();
		foreach ($allServiceTypes as $id => $value) {
			$curServiceType = new ServiceType($id);
			if ($curServiceType->get_service_layer() == 3 ) {
				$lay3Types[$id] = $curServiceType->get_name();
			 } elseif ($curServiceType->get_service_layer() == 2 ) {
				$lay2Types[$id] = $curServiceType->get_name();
			}
		}
		//if the layer is 3 use a different key
		if($services->get_service_layer() == 3)
		{
			// Get all L3 service types
			$allServiceTypes = ServiceType::get_service_types();
			$lay3Types = array();
			foreach ($allServiceTypes as $id => $value) {
				$curServiceType = new ServiceType($id);
				 if ($curServiceType->get_service_layer() == 3 )
				 	{$lay3Types[$id] = $curServiceType->get_name();}
			}
			// Now we have all L3 service types

			$serviceKey = array("cusName", "cusID", "serviceType", "serviceID", "stats", "description", "notes", 
								"status", "in_production","out_production",
								"device", "interface", "interfaceMTU", "tagged", "vlanNum",
								"logiRout", "routType", "ASNum", "trafPolice",
								"ipv4Uni", "ipv4Multi", "pRoutAd4", "cRoutAd4", "prefix4",
								"ipv6Uni", "ipv6Multi", "pRoutAd6", "cRoutAd6", "prefix6");
			
			$fieldType = array("static", "static", "drop_down", "static", "radio", "", "text_area",
							"drop_down", "date_picker", "date_picker",
							   "drop_down", "", "drop_down", "radio", "",
							   "", "drop_down", "", "",
							   "radio", "radio", "custom", "custom", "text_area.width:150px.height:100px",
							   "radio", "radio", "custom", "custom", "text_area.width:200px.height:100px");
			
			$allCustomData = array($layer3Service->get_pe_address(4), $layer3Service->get_ce_address(4), 
								  $layer3Service->get_pe_address(6), $layer3Service->get_ce_address(6));
			
			$customKeys = array("pRoutAd4", "cRoutAd4", "pRoutAd6", "cRoutAd6");
			$custom = array();
			
			foreach ($allCustomData as $id => $value)
			{
				$full = explode("/",  $value, 2);
				$address = $full[0];
				$length = $full[1];
				$custom[$id] = "<input name=\"".$customKeys[$id]."\" id=\"".$customKeys[$id]."\" value=\"".$address."\" type=\"text\" maxChar=\"250\" style='width: 30%;'> / <input name=\"".$customKeys[$id]."-length\" id=\"".$customKeys[$id]."-length\" value=\"".$length."\" type=\"text\" maxChar=\"2\" style='width: 2%;'>";
			}
			
			//store the values in the info
		$info = array($layer3Service->get_contact_name(), $layer3Service->get_contact_id(), $layer3Service->get_service_type_name(), $layer3Service->get_service_id(), $stats, $layer3Service->get_name(), $layer3Service->get_notes(),
					  $layer3Service->get_status(),$layer3Service->get_in_production_date(), $layer3Service->get_out_production_date(),
					  $layer3Service->get_pe_name(), $layer3Service->get_port_name(), $layer3Service->get_mtu(), $tagged, $layer3Service->get_vlan_id(),
					  $layer3Service->get_logical_router(), $layer3Service->get_routing_type(), $layer3Service->get_bgp_as(), $layer3Service->get_traffic_policing(),
					  $uni4, $multi4, $custom[0], $custom[1], $address4Prefix,
				  	  $uni6, $multi6, $custom[2], $custom[3], $address6Prefix);
			
			$serviceForm->setFieldType($fieldType);
			
			$MTU = array(1500=>'1500', 9000=>'9000');
			$routingType = array('BGP'=>'BGP', 'Static'=>'Static');
			$types = array( $lay3Types, $status_array,Device::get_devices(), $MTU, $routingType);
			
			$serviceForm->setData($fieldCustomInfo);
			echo $serviceForm->editServiceForm($headings, $titles, $info, $serviceKey, $types);
		}
		//if the layer is 2 use a different key
		if($services->get_service_layer() == 2)
		{
			$titles = array("Customer name", "Customer ID", "Service type", "Service ID",
							"Include statistics in portal?.tip.If YES is selected the user will be able to see the statistics for this particular service in the wiki portal. If this is not selected, traffic stats for this service will not be available in the wiki portal", 
							"Service description (name).tip.A useful description for this service", 
							"Notes.tip.Here you can add generic notes for this service, for example: This is a tempory backup connection.",
							"Status.tip.Specify the production status of this Service", "In production date", "Out of production date",
					"*<break>*", 
							"Vlan number.tip.Please enter a vlan number. If this is an untagged routed port and has no vlan, than please use 0. This means no vlan configuration");
			$serviceKey = array("cusName", "cusID", "serviceType", "serviceID", "stats", "description", "notes","status", 
					"in_production","out_production", "vlanNum");
			
			$fieldType = array("static", "static", "drop_down", "static", "radio", "", "text_area","drop_down","date_picker", "date_picker", "");
			global $status_array;
			$types = array($status_array);
			$serviceForm->setFieldType($fieldType);
			#print "<hr>2nd form<hr><pre>";print_r($serviceForm);print "</pre>end 2nd form<hr>";
			#echo $serviceForm->editServiceForm($headings, $titles, $info, $serviceKey,$types);
			$editForm = new Form();	
			$editForm->setCols(2);
			$editForm->setFieldType($fieldType);
			$editForm->setType($lay2Types);
			$editForm->setType($status_array);
			$editForm->setHeadings($headings);
			$editForm->setTitles($titles);
			$editForm->setDatabase($serviceKey);
			$editForm->setData($info);
			echo $editForm->EditForm(2);

		}
	}
						
	//if the user is showing the informating make it all uneditable
	else if($_GET['action'] == showID)
	{	
		//if the layer is 3 show the service form
		if($services->get_service_layer() == 3){
			$id = $layer3Service->get_service_id();
			$title = $layer3Service->get_name();
			$title = str_replace(" ", "%20", $title);
			$link = 'rrdgraph.php?file=service_id_'.$id.'.rrd&title='.$title;
			$directLink = "services.php?action=showGraphDetail&serviceID=".$id."&title=".$title."&type=traffic";
			echo "<div class='graph'>
					<a href='$directLink' class='screenshot' title='Statistics' rel='$link'><img src='$link'/></a>
					</div>";
			
			echo $serviceForm->showServiceForm($headings, $titles, $info);
		}
		//or else show the layer 2 service form along with the interface ports
		elseif($services->get_service_layer() == 2) {
			$titles = array("Customer name", "Customer ID", "Service type", "Service ID",
							"Include statistics in portal?.tip.If YES is selected the user will be able to see the statistics for this particular service in the wiki portal. If this is not selected, traffic stats for this service will not be available in the wiki portal", 
							"Service description (name).tip.A useful description for this service", 
							"Notes.tip.Here you can add generic notes for this service, for example: This is a tempory backup connection.",
							"Status.tip.Specifies the production status of this Service", "In production date", "Out of production date",
					"*<break>*", 
							"Vlan number.tip.Please enter a vlan number. If this is an untagged routed port and has no vlan, than please use 0. This means no vlan configuration");
			echo $serviceForm->showServiceForm($headings, $titles, $info);
			$serviceForm->setCols(6);
			
			if($_SESSION['access'] >= 50)
			{
				$toolNames = array("Add Interface");
				$toolIcons = array("add");
				$formType = array("newInterface");
				
				echo $tool->createNewModal($toolNames, $toolIcons, $formType);
			}
			echo $ff . $serviceForm->showAll($headings2, $titles2, $info2, $handlers, 3);
		}
	}
}

//This function displays the add service form
function addServiceForm($services)
{
	//global all variables and make the tool bar
	global $tool, $serviceForm, $headings, $titles, $serviceKey, $serviceTypes, $location, $status_array;
	
	//create the tools if this is not in Ajax mode
	/*Taken out for usability issues
	if (!isset($_GET['mode']))
	{
		$toolNames = array("All Services", "All Archived Services");
		$toolIcons = array("services", "services");
		$toolHandlers = array("handleEvent('services.php')", "handleEvent('services.php?action=showArchived')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}*/
		
	$serviceForm->setCols(2);
	
	$serviceType;
	$value = $_GET['mode'];
	/*switch ($value)
	{
		case 0: $serviceType = 'commodity'; break;
		case 1: $serviceType = 'oran'; break;
		case 2: $serviceType = 'ix'; break;
		case 3: $serviceType = 'undefined'; break;
		case 4: $serviceType = 'l2_vlan'; break;
		case 5: $serviceType = 'l2_transparant_p2p'; break;
		case 6: $serviceType = 'l2_tranparant_multipoint'; break;
		case 7: $serviceType = 'CU_ALL'; break;
	}*/
	
	//gives the headings titles and service keyes to the corresponding layer
	if($_GET['layer'] == 3)
	{
		$headings = array("Service Information", 
				  "*<break>*", "Port Specific Information",
				  "*<break>*", "Routing Specific Information",
				  "*<break>*", "IPv4 Information",
				  "*<break>*", "IPv6 Information");
		$titles = array("Customer name", "Service type", 
							"Include statistics in portal?.tip.If YES is selected the user will be able to see the statistics for this particular service in the wiki portal. If this is not selected, traffic stats for this service will not be available in the wiki portal", 
							"Service description (name).tip.A useful description for this service", 
							"Notes.tip.Here you can add generic notes for this service, for example: This is a tempory backup connection.",
					"Status.tip.Specify the production status of this Service", "In production date", "Out of production date",
					"*<break>*", "Device for Service", 
							"Interface.tip.Name of the physical interface, for example ge-2/0/1<br>Do not use subinterface format no ge-2/0/1.10", 
							"Interface MTU Size.tip.Default for ORAN and CU_ALL is 9000 bytes, the commodity and IX instance use 1500", 
							"Interface tagged", 
							"Vlan number.tip.Please enter a vlan number. If this is an untagged routed port and has no vlan, than please use 0. This means no vlan configuration",
					"*<break>*", "Logical router", "Routing type", "AS number", "Traffic Policing",
					"*<break>*", "IPv4 unicast", "IPv4 multicast", 
							"BCNET router address.tip.IPv4 address of the BCNET side of this link. Please include masklenght. Format: x.x.x.x/30", 
							"Customer router address.tip.IPv4 address of the Customer side of this link. Please include masklenght. Format: x.x.x.x/30", "IPv4 prefix",
					"*<break>*", "IPv6 unicast", "IPv6 multicast", "BCNET router address", "Customer router address", "IPv6 prefix");
					
		$serviceKey = array("cusName", "serviceType", "stats", "description", "notes", "status","in_production","out_production",
							"device", "interface", "interfaceMTU", "tagged", "vlanNum",
							"logiRout", "routType", "ASNum", "trafPolice",
							"ipv4Uni", "ipv4Multi", "pRoutAd4", "cRoutAd4", "prefix4",
							"ipv6Uni", "ipv6Multi", "pRoutAd6", "cRoutAd6", "prefix6");
		
		$layer3Service = new Layer3_service($services->get_service_id());
		$fieldType = array("drop_down", "drop_down", "radio", "", "text_area","drop_down","date_picker","date_picker",
						   "drop_down", "", "drop_down", "radio", "",
						   "", "drop_down", "", "",
						   "radio", "radio", "custom", "custom", "text_area.width:150px.height:100px",
						   "radio", "radio", "custom", "custom", "text_area.width:200px.height:100px");
			
		$customKeys = array("pRoutAd4", "cRoutAd4", "pRoutAd6", "cRoutAd6");
		$custom = array();
			
		foreach ($customKeys as $id => $value)
		{
			$custom[$id] = "<input name=\"".$customKeys[$id]."\" id=\"".$customKeys[$id]."\" value=\"\" type=\"text\" maxChar=\"250\" style='width: 30%;'> / <input name=\"".$customKeys[$id]."-length\" id=\"".$customKeys[$id]."-length\" value=\"\" type=\"text\" maxChar=\"2\" style='width: 2%;'>";
		}
		
		
		//store the values in the info
		$fieldInfo = array("", "", "", "", "","","","",
					  "", "", "", "", "", 
					  "", "", "", "",
					  "", "", $custom[0], $custom[1], "",
				  	  "", "", $custom[2], $custom[3], "");
		
		$contact = new Contact();
		$customers = $contact->get_groups();	
		$MTU = array(1500=>'1500', 9000=>'9000');
		$routingType = array('BGP'=>'BGP', 'Static'=>'Static');
		
		$allServiceTypes = ServiceType::get_service_types();
		$lay3Types = array();
		
		foreach ($allServiceTypes as $id => $value)
		{
			$curServiceType = new ServiceType($id);
			if ($curServiceType->get_service_layer() == 3)
			{$lay3Types[$id] = $curServiceType->get_name();}
		}
		$types = array($customers, $lay3Types, $status_array, Device::get_devices(), $MTU, $routingType);

		
		$serviceForm->setData($fieldInfo);
		$serviceForm->setFieldType($fieldType);
		
		//WEIRD BUG THAT I DUN GET BCNET ROUTER ADDRESS NOT WORKING
		
		//store all the contact information values into an array
		echo $serviceForm->newServiceForm($headings, $titles, $serviceKey, $types);
		
	}
	//for layer 2
	else if ($_GET['layer']==2)
	{
		$serviceKey = array("cusName", "serviceType", "stats", "description", "notes", "status","in_production","out_production","vlanNum");
		$headings = array("Service Information", 
				  "*<break>*", "Layer 2 Specific Information");
		$titles = array("Customer name", "Service type", 
							"Include statistics in portal?.tip.If YES is selected the user will be able to see the statistics for this particular service in the wiki portal. If this is not selected, traffic stats for this service will not be available in the wiki portal", 
							"Service description (name).tip.A useful description for this service", 
							"Notes.tip.Here you can add generic notes for this service, for example: This is a tempory backup connection.",
							"Status.tip.Specify the production status of this Service",
							"In production date", "Out of production date",
					"*<break>*",
							"Vlan number.tip.Please enter a vlan number. If this is an untagged routed port and has no vlan, than please use 0. This means no vlan configuration");
		$types="";
		
	
		$contact = new Contact();
		
		$allServiceTypes = ServiceType::get_service_types();
		$lay2Types = array();
		
		foreach ($allServiceTypes as $id => $value)
		{
			$curServiceType = new ServiceType($id);
			if ($curServiceType->get_service_layer() == 2)
			{$lay2Types[$id] = $curServiceType->get_name();}
		}
		
		$customers = $contact->get_groups();
		
		$types = array($customers, $lay2Types,$status_array);
		
		$fieldType = array("drop_down", "drop_down", "radio", "", "text_area", "drop_down","date_picker", "date_picker","");
		
		//$fieldInfo = array("", $serviceType, "", "", "", "");
		
		$serviceForm->setFieldType($fieldType);
		//$serviceForm->setData($fieldInfo);
		//store all the service information values into an array
		echo $serviceForm->newServiceForm($headings, $titles, $serviceKey, $types);
	}
	//to make all the handler buttons for the different forms
	else
	{
		$handler2 = "return LoadPage('services.php?action=add&layer=2&mode='+this.value, 'addLayerForm');";
		$handler3 = "return LoadPage('services.php?action=add&layer=3&mode='+this.value, 'addLayerForm');";
		// Change that... for some reason the above doesn't load the ajax scripts properly....
		// Specificaly the datepicker scripts.
		$handler2 = "window.location='services.php?action=add&layer=2';";
		$handler3 = "window.location='services.php?action=add&layer=3';";
		echo "
		<div style='margin-bottom:10px; clear:left;'>
		<form method='post'>
		<input type='radio' name='serviceType' value='2' onclick=\"$handler2\">Layer 2</input>
		<input type='radio' name='serviceType' value='3' onclick=\"$handler3\">Layer 3</input>
		</form></div>
		<div id='addLayerForm'></div>";
	}
}

//This function adds a new service to the existing services
function addService($services, $layer)
{
	//global the variables
	global $serviceKey, $serviceForm, $tool, $headings, $titles, $status;
	
					
	//create an empty temporary array to store all the new values given from the form
	$tempServiceInfo=array();
	
	//give the key corresponding to the layer
	if($layer == 2)
	{
		$serviceKey = array("cusName", "serviceType", "stats", "description", "notes","status","in_production","out_production", "vlanNum");
	}
	else {
		$serviceKey = array("cusName", "cusID", "serviceType", "stats", "description", "notes", "status","in_production","out_production",
							"device", "interface", "interfaceMTU", "tagged", "vlanNum",
							"logiRout", "routType", "ASNum", "trafPolice",
							"ipv4Uni", "ipv4Multi", "pRoutAd4", "cRoutAd4", "pRoutAd4-length", "cRoutAd4-length", "prefix4",
							"ipv6Uni", "ipv6Multi", "pRoutAd6", "cRoutAd6", "pRoutAd6-length", "cRoutAd6-length", "prefix6");
	}
			
	//create an empty temporary array to store all the new values given from the form
	$tempServiceInfo=array();
	foreach($serviceKey as $index => $key)
	{
		$tempServiceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempServiceInfo[notes] = addslashes($tempServiceInfo[notes]);
	$tempServiceInfo[description] = addslashes($tempServiceInfo[description]);
	
	//echo $tempServiceInfo[logiRout];
	//checks if the name is empty, if not set all the names and insert them
	
	/*$serviceType = $tempServiceInfo[serviceType];
	switch ($serviceType)
	{
		case 'commodity': $serviceType = 0; break;
		case 'oran': $serviceType = 1; break;
		case 'ix': $serviceType = 2; break;
		case 'undefined': $serviceType = 3; break;
		case 'l2_vlan': $serviceType = 4; break;
		case 'l2_transparant_p2p': $serviceType = 5; break;
		case 'l2_tranparant_multipoint': $serviceType = 6; break;
		case 'CU_ALL': $serviceType = 7; break;
	}
	$tempServiceInfo[serviceType] = $serviceType;*/
	
	if ($services->set_name($tempServiceInfo[description]))
	{
		$services->set_in_production_date($tempServiceInfo[in_production]);
		$services->set_out_production_date($tempServiceInfo[out_production]);
		$services->set_status($tempServiceInfo[status]);
		//set all the values to the query corresponding to the layer
		if($layer == 2)
		{
			$services->set_contact_id($tempServiceInfo[cusName]);
			$services->set_service_type($tempServiceInfo[serviceType]);
			$services->set_portal_statistics($tempServiceInfo[stats]);
			$services->set_notes($tempServiceInfo[notes]);
			$services->set_vlan_id($tempServiceInfo[vlanNum]);
		}
		else{
			$services->set_contact_id($tempServiceInfo[cusName]);
			$services->set_service_type($tempServiceInfo[serviceType]);
			$services->set_portal_statistics($tempServiceInfo[stats]);
			$services->set_notes($tempServiceInfo[notes]);
			
			$services->set_pe_id($tempServiceInfo[device]);
			$services->set_port_name($tempServiceInfo['interface']);
			$services->set_mtu($tempServiceInfo[interfaceMTU]);
			$services->set_tagged($tempServiceInfo[tagged]);
			$services->set_vlan_id($tempServiceInfo[vlanNum]);
				
			$services->set_logical_router($tempServiceInfo[logiRout]);
			$services->set_routing_type($tempServiceInfo[routType]);
			$services->set_bgp_as($tempServiceInfo[ASNum]);
			$services->set_traffic_policing($tempServiceInfo[trafPolice]);
				
			$services->set_ipv4_unicast($tempServiceInfo[ipv4Uni]);
			$services->set_ipv4_multicast($tempServiceInfo[ipv4Multi]);
			$services->set_pe4_address($tempServiceInfo[pRoutAd4]);
			$services->set_ce4_address($tempServiceInfo[cRoutAd4]);
			$services->set_pe4_address_length($tempServiceInfo['pRoutAd4-length']);
			$services->set_ce4_address_length($tempServiceInfo['cRoutAd4-length']);
			
			$services->clear_ipv4_prefixes();
			
			$prefix4 = explode("\n", $tempServiceInfo[prefix4]);
			foreach($prefix4 as $id => $prefix)
			{
				$services->add_ipv4_prefixes($prefix);
			}
				
			$services->set_ipv6_unicast($tempServiceInfo[ipv6Uni]);
			$services->set_ipv6_multicast($tempServiceInfo[ipv6Multi]);
			$services->set_pe6_address($tempServiceInfo[pRoutAd6]);
			$services->set_ce6_address($tempServiceInfo[cRoutAd6]);
			$services->set_pe6_address_length($tempServiceInfo['pRoutAd6-length']);
			$services->set_ce6_address_length($tempServiceInfo['cRoutAd6-length']);
			$services->clear_ipv6_prefixes();
			$prefix6 = explode("\n", $tempServiceInfo[prefix6]);
			foreach($prefix6 as $id => $prefix)
			{
				$services->add_ipv6_prefixes($prefix);
			}
		}
							
		//if the insert is sucessful reload the page with the new values
		if($this_ID=$services->insert())
		{
			$status="success";
			$_SESSION['action'] = "Added new service: ".$tempServiceInfo[description];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$this_ID&add=$status\">";
		}
		//or else show error
		else {
			$serviceForm->error("Warning: Failed to add service. Reason: ".$services->get_error());
		}
		
	}
	//if no name, then output error
	else
	{
		$serviceForm->error("Warning: Failed to add service. Reason: ".$services->get_error());
	}										
}

//This function adds a new interface port to the existing interface port
function addPort($servicePort, $services)
{
	//global all variables
	global $serviceKey, $serviceForm, $status, $serviceTypes, $location;
	
	//create an empty temporary array to store all the new values given from the form
	$tempServiceInfo=array();
	$serviceKey = array("deviceName", "portName", "tagged", "vlan", "mtu");
	
	foreach($serviceKey as $index => $key)
	{
		$tempServiceInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempServiceInfo[portName] = addslashes($tempServiceInfo[portName]);
	$tempServiceInfo[deviceName] = addslashes($tempServiceInfo[deviceName]);
		
	//makes sure the service port has a name
	/*$serviceKey = array("name", "device_fqdn", "location_name", "device_type_name", "device_oob" "notes", "snmp_ro", "snmp_rw", "snmp_version", "ro_user", "ro_password");*/
	if ($servicePort->set_port_name($tempServiceInfo[portName]))
	{
		//set all the values to the query
		$servicePort->set_device_id($tempServiceInfo[deviceName]);
		$servicePort->set_tagged($tempServiceInfo[tagged]);
		$servicePort->set_vlan_id($tempServiceInfo[vlan]);
		$servicePort->set_mtu($tempServiceInfo[mtu]);
		$servicePort->set_service_id($services->get_service_id());
									
		//if the update is sucessful go back to show the new updates or else show an error
		if($servicePort->insert())
		{
			$status="success";
			$_SESSION['action'] = "Added new service port: ".$tempServiceInfo[portName];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]&update=$status\">";
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
			$serviceForm->error("Warning: Failed to add. Reason: ".$servicePort->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{
		$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
		$serviceForm->error("Warning: Failed to add. Reason: ".$servicePort->get_error(), $link);
	}
}										

//display all the graph detail
function displayGraphDetail()
{
	//global the tool
	global $tool, $deviceForm;
	
	//create all the time frames
	$now = time();
	$day = time() - (24 * 60 * 60);
	$twoday = time() - (2 * 24 * 60 * 60);
	$week = time() - (7 * 24 * 60 * 60);
	$month = time() - (31 * 24 * 60 * 60);
	$year = time() - (365 * 24 * 60 * 60);
		
	echo "<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:1024px;'>";
	#"<select name='interfaces' onchange=\"return LoadPage('statistics.php?action=showCurGraph&mode=graphTime'+this.value, 'statGraphs')\">";

	//create all the variables id and links
	$serviceID = $_GET['serviceID'];
	$oriName= $_GET['title'];
	$name = str_replace(" ", "%20", $oriName);
	$link="rrdgraph.php?file=service_id_".$serviceID.".rrd&width=900&height=150&title=".$name;
			
	$dayLink= $link."&from=".$day."&to".$now;
	$weekLink= $link."&from=".$week."&to".$now;
	$monthLink= $link."&from=".$month."&to".$now;
	$yearLink= $link."&from=".$year."&to".$now;
			
	//display the graphs and the links for the graphs
	$detail = "services.php?action=zoomGraphDetail&serviceID=".$serviceID."&title=".$name;
	echo "<thead><tr><th colspan='5'>".$oriName."</th></tr></thead>
			<tr>
			<tr class='form'><td class='info' style='text-align:center;'><h1>Last Day</h1></td></tr>
			<tr>
			<td><a href='#' onclick=\"handleEvent('".$detail."&from=day&to=now')\"><img src=".$dayLink."></a></td>
			</tr>
			<tr>
			<tr class='form'><td class='info' style='text-align:center;'><h1>Last Week</h1></td></tr>
			<tr>
			<td><a href='#' onclick=\"handleEvent('".$detail."&from=week&to=now')\"><img src=".$weekLink."></a></td>
			</tr>
			<tr>
			<tr class='form'><td class='info' style='text-align:center;'><h1>Last Month</h1></td></tr>
			<tr>
			<td><a href='#' onclick=\"handleEvent('".$detail."&from=month&to=now')\"><img src=".$monthLink."></a></td>
			</tr>
			<tr>
			<tr class='form'><td class='info' style='text-align:center;'><h1>Last Year</h1></td></tr>
			<tr><td><a href='#' onclick=\"handleEvent('".$detail."&from=year&to=now')\"><img src=".$yearLink."></a></td></tr>
			</tbody>";
	echo "</table>";
}

//display the graph for zooming
function zoomGraphDetail()
{
	//create all the time frames
	$now = time();
	$day = time() - (24 * 60 * 60);
	$twoday = time() - (2 * 24 * 60 * 60);
	$week = time() - (7 * 24 * 60 * 60);
	$month = time() - (31 * 24 * 60 * 60);
	$year = time() - (365 * 24 * 60 * 60);
	
	//check if it's by day week month or year
	switch ($_GET['from'])
	{
		case 'day':
		$from = $day;
		break;
				
		case 'week':
		$from = $week;
		break;
				
		case 'month':
		$from = $month;
		break;
				
		case 'year':
		$from = $year;
		break;
	}
			
	echo "<table id='dataTable' style='width:1024px;'>";
	
	//create the links for the graph
	$serviceID = $_GET['serviceID'];
	$oriName = $_GET['title'];
	$name = str_replace(" ", "%20", $oriName);
	$type = 'inter';
	
	$link="rrdgraph.php?file=service_id_".$serviceID.".rrd&width=900&height=150&title=".$name;			
	$link .= "&from=".$from."&to".$now;
	$file="service_id_".$_GET['serviceID'].".rrd&title=".$name;
		
	//set the values for the new zoomed graph		
	echo "<tr><th>".$oriName."<br></b></p></font></th></tr>";
	echo "<tr><td><img id='zoom' src='".$link."'>
			<form method='GET' action='' enctype='multipart/form-data' id='range_form'>";
	echo '<input type="hidden" name="epoch_start" value="'.$from.'" id="epoch_start" />
			<input type="hidden" name="rrdfile" value="'.$file.'" id="rrdfile" />
			<input type="hidden" name="type" value="'.$type.'" id="type" />
			<input type="hidden" name="epoch_end" value="'.$now.'" id="epoch_end" />
			<input type="hidden" name="width" value="900" id="width" />
			<input type="hidden" name="height" value="150" id="height" />
			</form>
			</td>
			</tr>';
	echo "</table>";
}
			
//The function removes a service
function removeService($services)
{
	//global the variables
	global $serviceKey, $serviceForm, $tool, $headings, $titles, $status;
					
	//if the ID is invalid, show error
	if($services->get_service_id() =="" && get_class($services) != Layer2_service_port)
	{
		$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
		$serviceForm->error("Warning: Failed to load. Reason: ".$services->get_error(), $link);
		$services->set_service_type($tempServiceInfo[serviceType]);
	}
					
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['ID'])){
			
			if(get_class($services) == Layer2_service_port)
			{$portName = $services->get_port_name();}
			if($services->delete()){
				$status="success";
				
				if(get_class($services) == Layer2_service_port)
				{
					$_SESSION['action'] = "Removed port: ".$portName;
					echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID']."&delete=$status\">";
					
				}
				else {
					$_SESSION['action'] = "Removed service: ".$services->get_name();
					echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?delete=$status\">";
				}
			}
			//or else show error
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
				$serviceForm->error("Warning: Failed to remove service. Reason: ".$services->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showID&ID=$_GET[ID]\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		if(get_class($services)!= Layer2_service_port) {
			if ($services->get_status() == 'In Production') {
				$msg .= "<br><i><b>Note that the service status is " . $services->get_status() ."<br>Please update this!</b></i>";
			}
			if ($services->get_out_production_date() == '') {
				$msg .= "<br><i><b>Note that the Out of service date is empty<br>Please update this!</b></i>";
			}
		}
		$serviceForm->prompt("Are you sure you want to delete? <br>$msg");
	}
}

//The function removes a service
function removeServiceType($serviceType)
{
	//global the variables
	global $serviceForm;
					
	//if the ID is invalid, show error
	if($serviceType->get_service_type_id() == "")
	{
		$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
		$serviceForm->error("Warning: Failed to load. Reason: ".$serviceType->get_error(), $link);
	}
					
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['ID'])){
			
			if($serviceType->delete()){
				$status="success";
				$_SESSION['action'] = "Removed service type: ".$serviceType->get_name();
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showAllServiceTypes&delete=$status\">";
				}
			//or else show error
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showID&ID=".$_GET['ID'];
				$serviceForm->error("Warning: Failed to remove service. Reason: ".$serviceType->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showServiceType&ID=".$_GET['ID']."\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$serviceForm->prompt("Are you sure you want to delete?");
	}
}

	
	
	
	
	/**UNUSED SCRIPT
	<script language="javascript">
var lay2='yes';
var lay3='yes';
var vantx='yes';
var pgtx='yes';
var kamtx='yes';
var victx='yes';
var keltx='yes';
var iptransit='yes';
var oran='yes';
var ix='yes';
var cu_all='yes';
var layer2Vlan='yes';

function checkChecked(layer)
{
	if(layer.checked == 1)
	{
		if (layer.name=='layer2') {lay2='yes';}
		if (layer.name=='layer3') {lay3='yes';}
		if (layer.name=='vantx') {vantx='yes';}
		if (layer.name=='pgtx') {pgtx='yes';}
		if (layer.name=='kamtx') {kamtx='yes';}
		if (layer.name=='victx') {victx='yes';}
		if (layer.name=='keltx') {keltx='yes';}
		if (layer.name=='iptransit') {iptransit='yes';}
		if (layer.name=='oran') {oran='yes';}
		if (layer.name=='ix') {ix='yes';}
		if (layer.name=='cu_all') {cu_all='yes';}
		if (layer.name=='layer2Vlan') {layer2Vlan='yes';}
	}
	else if(layer.checked==0)
	{
		if (layer.name=='layer2') {lay2='';}
		if (layer.name=='layer3') {lay3='';}
		if (layer.name=='vantx') {vantx='';}
		if (layer.name=='pgtx') {pgtx='';}
		if (layer.name=='kamtx') {kamtx='';}
		if (layer.name=='victx') {victx='';}
		if (layer.name=='keltx') {keltx='';}
		if (layer.name=='iptransit') {iptransit='';}
		if (layer.name=='oran') {oran='';}
		if (layer.name=='ix') {ix='';}
		if (layer.name=='cu_all') {cu_all='';}
		if (layer.name=='layer2Vlan') {layer2Vlan='';}
	}
	
	document.filterForm.action = "javascript:LoadPage('services.php?action=filter&l2="+lay2+"&l3="+lay3+"&vantx="+vantx+"&pgtx="+pgtx+"&kamtx="+kamtx+"&victx="+victx+"&keltx="+keltx+"&iptransit="+iptransit+"&oran="+oran+"&ix="+ix+"&cu_all="+cu_all+"&layer2Vlan="+layer2Vlan+"&mode=filtering', 'filteredResult');"
	document.filterForm.submit();	
}
</script>*/
?>

<?php
include_once 'classes/EdittingTools.php';
include_once 'classes/Form.php';
include_once 'classes/Device.php';
include_once 'classes/AAA.php';

class ChangeManager 
{

	// If you use href's you should use this as a base for 
	private $url = '';
	private $timetable=array();

	// Please define dir name
	// used for calendir scripts below
	private $dir = 'ChangeManager';

	// Define an array for impact values
	private $impact_values=array(
		0 => "",
		1 => "No Impact",
		2 => "Single Client",
		3 => "Single Location",
		4 => "Network Wide",
	);

	private $status_values=array(
		0 => "",
		1 => "Request",
		2 => "Success",
		3 => "Rolled back",
		4 => "Failed",
		5 => "Cancelled",
	);



	private function init() {

		//Create Time table
		for ($i = 0; $i <= 23; $i++) {	
			$hour = sprintf("%02.0f", $i);	
			$time1 = "$hour:00";
			$time2 = "$hour:30";
			$this->timetable[$time1]=$time1;
			$this->timetable[$time2]=$time2;
		}

		// Base url
		$this->url = $_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID']; 

		// Special args if this runs from plugins.php
		if (isset($_GET['pluginaction'])) {
			$this->url .= "&pluginaction=".$_GET['pluginaction'];
		}
		if (isset($_GET['pluginID'])) {
			$this->url .= "&pluginID=".$_GET['pluginID'];
		}
		return;
	}

	//renders the content
	function get_content() {
		// First do a init
		// To determine Url info
		$this->init();

		if ($_GET['action'] == 'list_changes') {
			return $this->render_list_changes();
		} elseif ($_GET['action'] == 'search') {
			if (isset($_POST['SearchChange'])) {
				$searchParams = array();
				if($_POST['Device'] != ""){
					$searchParams['device_id'] = $_POST['Device'];
				}
				if($_POST['ChangedBy'] != ""){
					$searchParams['chgby_id'] = $_POST['ChangedBy'];
				}
				if($_POST['SearchText'] != ""){
					$searchParams['search_text'] = $_POST['SearchText'];
				}
				return $this->render_search_results_changes($searchParams);
			} else {
				return $this->render_search_results_changes();
			}
		} elseif ($_GET['action'] == 'report') {
			if (isset($_POST['ReportMonth'])) {
				$reportMonth = $_POST['ReportMonth'];
				return $this->render_report_changes($reportMonth);
			} else {
				return $this->render_report_changes();
			}
		} elseif ($_GET['action'] == 'new_change') {
			if (isset($_POST['InsertChange'])) {
				$new_cid =  $this->insert_change();
				print "<meta http-equiv=\"REFRESH\" content=\"0;url=".$this->url."&action=show_change&cid=$new_cid\">";
			} else {
				return $this->render_new_change();
			}

		} elseif ($_GET['action'] == 'show_change') {
			return $this->render_show_change();

		} elseif ($_GET['action'] == 'edit_change') {
			if (isset($_POST['updateInfo'])) {
				$content = $this->update_change();
				//return $content . $this->render_show_change();
				print "<meta http-equiv=\"REFRESH\" content=\"0;url=".$this->url."&action=show_change&cid=$_GET[cid]\">";
			} else {
				return $this->render_edit_change();
			}
		} elseif ($_GET['action'] == 'delete_device_component') {
			return $this->delete_device_component_from_change();

		} elseif ($_GET['action'] == 'delete_change') {
			return $this->delete_change();
		} elseif ($_GET['action'] == 'email_change') {
			return $this->email_change();
		} else {
			return $this->render_list_changes();
		}


	}

	function render_search_results_changes($inSearchParams = -1) {
		// This renders all the changes p

		$content = "";

		// Get all users 
		$allUsers_real = User::get_users_by_fullname();
		$allUsers = User::get_users_by_fullname();
		// Do not show Administrator account in changed by dropdown
		unset($allUsers[1]);
		asort($allUsers);
		
		$allDevices = Device::get_devices();
		
		if ($inSearchParams == -1)
		{
			$content .= "<h1>Change Manager - Search</h1>";
			/** Search Form
			 *  Only need to search by the following fields
			 *  By:
			 *  Device
			 * 	Changed By
			 *  Any Text
			 * */
			
			/*
				First Generic change info
			*/
			$values = array();
			$form = new Form("auto",2);
			// Need to count the number of sections in this form
			// Used by editForm($numHead)  later
	
			// Device
			$allDevices = Device::get_devices();
	
			$form->setType($allDevices); // Drop down
			$fieldType[0]= "drop_down";
			
	
			$form->setType($allUsers); // Drop down
			$fieldType[1]= "drop_down";
			
			array_push($values,"","","");
	
			$heading = array("Search Form");
			$titles = array("Device", "Contact/Changed By","Search Any Text");
			$postkeys = array("Device","ChangedBy","SearchText");
	
			array_push($postkeys,"Device","ChangedBy","SearchText");
	
			//set the table size
			$form->setFieldType($fieldType);
	
			$form->setSortable(false);
			$form->setHeadings($heading);
			$form->setTitles($titles);
			$form->setDatabase($postkeys);
			$form->setData($values);
	
			//set the table size
			$form->setTableWidth("94%");
			$form->setTableWidth("94%");
			$form->setTitleWidth("20%");
			$form->setUpdateValue("SearchChange");
			$form->setUpdateText("Search");
			$content .= $form->EditForm(1);
			return $content;
		}
		else
		{
			$content .= "<h1>Change Manager - Search Results</h1>";
			// Declare search term nice string vars
			$device_id = "";
			$chgby_id = "";
			$searchText = "";
			
			// Declare sub queries
			$queryDeviceID = "";
			$queryChgbyID = "";
			$querySearchText = "";
			
			/** Search parameters were given, now build query and results list */
			// Prepare query
			
			/** Process the fields and provide the SQL searchParams */
			// Post key names: "Device","ChangedBy","SearchText"
			// Search plugin_ChangeManager_Changes title, notes, 
			// Search plugin_ChangeManager_Components description
			
			if(isset($inSearchParams['device_id']) && $inSearchParams['device_id'] != "")
			{
				$device_id = mysql_real_escape_string($inSearchParams['device_id']);
				$queryDeviceID = " change_id IN (SELECT change_id FROM plugin_ChangeManager_Components 
					WHERE device_id = '$device_id')";
			}
			if(isset($inSearchParams['chgby_id']) && $inSearchParams['chgby_id'] != "")
			{
				$chgby_id = mysql_real_escape_string($inSearchParams['chgby_id']);
				$queryChangeBy = " (change_contact_1 = '$chgby_id' 
									OR change_contact_2 = '$chgby_id'
									OR change_id IN (SELECT change_id FROM plugin_ChangeManager_Components 
										WHERE change_id = '$chgby_id'))";
			}
			if(isset($inSearchParams['search_text']) && $inSearchParams['search_text'] != "")
			{
				$searchText = mysql_real_escape_string($inSearchParams['search_text']);
				$querySearchText = " title LIKE '%$searchText%' OR notes LIKE '%$searchText%'";
			}
			
			$query = "SELECT change_id, title, notes, change_date, planned_change_date, 
						change_contact_1, change_contact_2, status
						FROM plugin_ChangeManager_Changes";
			
			if($device_id !="" || $chgby_id != "" || $searchText != "")
			{
				$query .= " WHERE ";
			}
			
			if($device_id != "")
			{
				$query .= $queryDeviceID;
			}
						
			if($chgby_id != "")
			{
				if($device_id !="")
				{
					$query .= " AND ";
				}
				$query .= $queryChangeBy;
			}

			if($searchText != "")
			{
				if($device_id !="" || $chgby_id != "")
				{
					$query .= " AND ";
				}
				$query .= $querySearchText;
			}			
						
			$query .= "	ORDER BY change_id DESC";

			$result =  mysql_query($query);
			if (!$result)  {
				return "<b>Oops something went wrong, unable to select changes </b>";
			}
			$content .= "<h3>Search Parameters Given: </h3>
			<b>Device:</b> " . $allDevices[$device_id] . " <br>
			<b>Contact/Changed By:</b> " . $allUsers[$chgby_id] . "<br>
			<b>Search Text:</b> $searchText <br><br>
			<b>(".mysql_num_rows($result).")</b> Results
			<br><br>";
			 
			$form = new Form("auto",6);
			$heading = array( "Change ID", "Change","Planned Time","Contact","Status","Actions");
			$data = array();
			$handler = array();
			while ($obj = mysql_fetch_object($result)){
				$user = new User($obj->change_contact_1);
				$contact_name = $user->get_full_name();
	
				$actions = "<a href='$this->url&action=delete_change&cid=$obj->change_id&return=list_changes'>
	<img src='icons/Delete.png' height=18></a> ";
	
				if ($obj->status > 0) {
					$actions .= "<a href='$this->url&action=email_change&cid=$obj->change_id'>
	<img src='icons/Email.png' height=18></a> ";
				}
	
				array_push ($data,$obj->change_id,$obj->title,$obj->planned_change_date,$contact_name, $this->status_values[$obj->status],
				$actions
				);
				// Event handler
				$url = $this->url ."&action=show_change&cid=$obj->change_id";
				array_push($handler,"handleEvent('$url')");
	
			}
	
			$form->setSortable(true); // or false for not sortable
			$form->setHeadings($heading);
			$form->setEventHandler($handler);
			$form->setData($data);
			//set the table size
			#$form->setTableWidth("100%");
			$content .= $form->showForm();
			return $content;
		}
	}
	
	function render_report_changes($inMonth = -1) {
		// This renders all the changes p

		$content = "";

		// Get all users 
		$allUsers_real = User::get_users_by_fullname();
		$allUsers = User::get_users_by_fullname();
		// Do not show Administrator account in changed by dropdown
		unset($allUsers[1]);
		asort($allUsers);
		
		$allDevices = Device::get_devices();
		
		$thisYear = date("Y",mktime());
		$thisMonth = date("m",mktime());
		
		$DropDownMonths = array();
		$DropDownMonths[$thisMonth . " / " . $thisYear] = $thisMonth . " / " . $thisYear;
		
		for ($i = 0; $i <= 12; ++$i) {
		    $time = strtotime(sprintf('-%d months', $i));
		    $year = date('Y', $time);
		    $month = date('m', $time);
			$DropDownMonths[$month . " / " . $year] = $month . " / " . $year;
		}
		
		if ($inMonth == -1)
		{
			$content .= "<h1>Change Manager - Report</h1>";
			/** Report Form
			 *  Only need to select month.
			 * */

			$values = array();
			$form = new Form("auto",2);
			// Need to count the number of sections in this form
			// Used by editForm($numHead)  later
	
			// Months

	
			$form->setType($DropDownMonths); // Drop down
			$fieldType[0]= "drop_down";
					
			array_push($values,"");
	
			$heading = array("Choose Report");
			$titles = array("Report Period (Monthly)");
			$postkeys = array("ReportMonth");
	
			array_push($postkeys,"ReportMonth");
	
			//set the table size
			$form->setFieldType($fieldType);
	
			$form->setSortable(false);
			$form->setHeadings($heading);
			$form->setTitles($titles);
			$form->setDatabase($postkeys);
			$form->setData($values);
	
			//set the table size
			$form->setTableWidth("94%");
			$form->setTableWidth("94%");
			$form->setTitleWidth("20%");
			$form->setUpdateValue("SubmitReportMonth");
			$form->setUpdateText("Submit");
			$content .= $form->EditForm(1);
			return $content;
		}
		else
		{
			$content .= "<h1>Change Manager - Report Results</h1>";
			// Declare search term nice string vars
						
			/** Search parameters were given, now build query and results list */
			// Prepare query
			
			/** Process the fields and provide the SQL searchParams */
			// Post key names: "ReportMonth","ChangedBy","SearchText"
			// Search plugin_ChangeManager_Changes 
			
			if($inMonth != "")
			{
				$inMonth = mysql_real_escape_string($inMonth);
				$tempStrArr = explode("/",$inMonth);
				$selectedMonth = $tempStrArr[0];
				$selectedYear = $tempStrArr[1];
				$query = "SELECT change_id, title, notes, change_date, planned_change_date, 
						change_contact_1, change_contact_2, status
						FROM plugin_ChangeManager_Changes
						WHERE MONTH(change_date) = $selectedMonth AND YEAR(change_date) = $selectedYear
						ORDER BY change_date ASC";
			}
			else // List nothing
			{
				$query = "SELECT * FROM plugin_ChangeManager_Changes WHERE 0";
			}		

			$result =  mysql_query($query);
			if (!$result)  {
				return "<b>Oops something went wrong, unable to generate report. </b>";
			}
			
			$content .= "<h3>Report Selected</h3>
			<b>Month:</b> $selectedMonth / $selectedYear<br>
			<b>(".mysql_num_rows($result).")</b> Results
			<br><br>";
			 
			$form = new Form("auto",8);
			$heading = array( "Change ID", "Change","Planned Time","Completion Time","Contact","Status","Devices","Actions");
			$data = array();
			$handler = array();
			while ($obj = mysql_fetch_object($result)){
				$user = new User($obj->change_contact_1);
				$contact_name = $user->get_full_name();
				$numDevices = $this->get_numdevices_for_change($obj->change_id);
	
				$actions = "<a href='$this->url&action=delete_change&cid=$obj->change_id&return=list_changes'>
	<img src='icons/Delete.png' height=18></a> ";
	
				if ($obj->status > 0) {
					$actions .= "<a href='$this->url&action=email_change&cid=$obj->change_id'>
	<img src='icons/Email.png' height=18></a> ";
				}
	
				array_push ($data,$obj->change_id,$obj->title,$obj->planned_change_date,$obj->change_date,$contact_name, $this->status_values[$obj->status],$numDevices,
				$actions
				);
				// Event handler
				$url = $this->url ."&action=show_change&cid=$obj->change_id";
				array_push($handler,"handleEvent('$url')");
	
			}
	
			$form->setSortable(true); // or false for not sortable
			$form->setHeadings($heading);
			$form->setEventHandler($handler);
			$form->setData($data);
			//set the table size
			#$form->setTableWidth("100%");
			$content .= $form->showForm();
			return $content;
		}
	}

	function render_list_changes() {
		// This renders all the changes p

		$content = "<h1>Change Manager</h1>";

		// Tools menu
		$tool = new EdittingTools();
		if ($_SESSION['access'] >= 50) {
			$toolNames = array("New Change", "List Changes", "Search", "Report");
			$toolIcons = array("add","stat","search","report");
			$toolHandlers = array("window.location.href='$this->url&action=new_change'",
			"window.location.href='$this->url&action=render_report'",
			"window.location.href='$this->url&action=search'","window.location.href='$this->url&action=report'");
			$content .= $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
		}
		$content .= $tool->createNewFilters();
		$content .=" <div style=\"clear:both;\"></div><br> ";

		// Prepare query

		$query = "SELECT change_id, title, notes, change_date, planned_change_date, 
					change_contact_1, change_contact_2, status
					FROM plugin_ChangeManager_Changes
					ORDER BY change_id DESC";
		$result =  mysql_query($query) ;
		if (!$result)  {
			return "<b>Oops something went wrong, unable to select changes </b>";
		}

		$form = new Form("auto",6);
		$heading = array( "Change ID", "Change","Planned Time","Contact","Status","Actions");
		$data = array();
		$handler = array();
		while ($obj = mysql_fetch_object($result)){
			$user = new User($obj->change_contact_1);
			$contact_name = $user->get_full_name();

			$actions = "<a href='$this->url&action=delete_change&cid=$obj->change_id&return=list_changes'>
<img src='icons/Delete.png' height=18></a> ";

			if ($obj->status > 0) {
				$actions .= "<a href='$this->url&action=email_change&cid=$obj->change_id'>
<img src='icons/Email.png' height=18></a> ";
			}

			array_push ($data,$obj->change_id,$obj->title,$obj->planned_change_date,$contact_name, $this->status_values[$obj->status],
			$actions
			);
			// Event handler
			$url = $this->url ."&action=show_change&cid=$obj->change_id";
			array_push($handler,"handleEvent('$url')");

		}

		$form->setSortable(true); // or false for not sortable
		$form->setHeadings($heading);
		$form->setEventHandler($handler);
		$form->setData($data);
		//set the table size
		#$form->setTableWidth("100%");
		$content .= $form->showForm();
		return $content;
	}

	function email_change() 
	{
		if ($_GET['return'] == 'show_change') {
			$returnto = 'show_change';
		} else {
			$returnto = 'list_changes';
		}

		$cid = mysql_real_escape_string($_GET['cid']);
		if (!is_numeric($cid)) {
			return "Invalid Change id";
		}
		
		// Choose whether to send email report in HTML format.
		$html_email = 1;

		/*
		First Generic change info
		*/ 
		$query = "SELECT change_id, title, notes, record_date, UNIX_TIMESTAMP(planned_change_date) as planned_change_date, 
					change_contact_1, change_contact_2, impact, status
					FROM plugin_ChangeManager_Changes
					WHERE change_id = '$cid'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			return "<b>Oops something went wrong, unable to select changes </b>";
		}
		$values=array();
		while ($obj = mysql_fetch_object($result))
		{
			$title = $obj->title;
			$change_id = $obj->change_id;
			$user1 = new User($obj->change_contact_1);
			$contact_name1 = $user1->get_full_name();
			$user2 = new User($obj->change_contact_2);
			$contact_name2 = $user2->get_full_name();
			$impact_name = $this->impact_values{$obj->impact};
			$change_desc = $obj->notes;
			$status = $this->status_values{$obj->status};
			$my_date = date("Y-m-d",$obj->planned_change_date);
			$my_time = date("H:i",$obj->planned_change_date);

			// HTML
			$notes = nl2br($obj->notes);
			$user1 = new User($obj->change_contact_1);
			$contact_name1 = stripslashes(nl2br(htmlentities($user1->get_full_name())));
			$user2 = new User($obj->change_contact_2);
			$contact_name2 = stripslashes(nl2br(htmlentities($user2->get_full_name())));
			$impact_name = stripslashes(nl2br(htmlentities($this->impact_values{$obj->impact})));
			$status_name = stripslashes(nl2br(htmlentities($this->status_values{$obj->status})));
			array_push ($values,$obj->title, "$my_date $my_time",
			$contact_name1,$contact_name2,$status_name,$impact_name,$notes);
			//print_r($values); exit;
			// END HTML

		}
		$form = new Form("auto", 2);
		$heading = array("Change Details");
		$titles = array("Summary", "Change Date","Primary Contact", "Secondary Contact","Status","Impact","Change Description");
		$form->setSortable(false);
		$form->setHeadings($heading);
		$form->setTitles($titles);
		$form->setDatabase($titles);
		$form->setData($values);
		//set the table size
		$form->setTableWidth("94%");
		$form->setTitleWidth("20%");

		$css = file_get_contents('./plugins/ChangeManager/email.css', true);	
		$css .= file_get_contents('./bcnet.css', true);	
		$html_content = "<html xmlns='http://www.w3.org/1999/xhtml'><head>
						<STYLE type='text/css'>
						$css
						</STYLE>

						</head>";

		$html_content .= $form->ShowForm(1);

		$mail_from = "BCNET CMDB Account <scripts@bc.net>";
		$mail_to = "noc@bc.net";
		$mail_headers = "From: $mail_from\r\n" .
		"Reply-To: $mail_from\r\n" .
		"X-Mailer: Network Change Report Mailer\n";
		if ($html_email == 1) {
			$mail_headers .= "MIME-Version: 1.0' \r\n".
			"Content-type: text/html; charset=iso-8859-1' \r\n";
		}

		$mail_subject = "Change [$change_id] $my_date $title (". strtoupper($status) . ")";

		$mail_body = "";
		if ($html_email != 1) {
			$mail_body .= "########################################\n\n";
		}
		$mail_body .= "NETWORK CHANGE NUMBER:   $change_id\n\n";
		$mail_body .= "CHANGE SUMMARY:  $title\n\n";
		$mail_body .= "CHANGE DATE:  $my_date $my_time\n\n";
		$mail_body .= "PRIMARY CONTACT:  $contact_name1\n\n";
		$mail_body .= "SECONDARY CONTACT:  $contact_name2\n\n";
		$mail_body .= "STATUS:  $status\n\n";
		$mail_body .= "IMPACT:  $impact_name\n\n";
		$mail_body .= "CHANGE DESCRIPTION:  $change_desc\n\n";

		/*
			Device specific change info
		*/ 
		$query2 = "SELECT device_id, impact, effects, chgby_id, change_date, description,
					back_out, status, id
					FROM plugin_ChangeManager_Components
					WHERE change_id = '$cid'";
		$result2 =  mysql_query($query2) ;
		if (!$result2)
		{
			return "<b>Oops something went wrong, unable to select changes </b>";
		}

		while ($obj = mysql_fetch_object($result2))
		{

			if ($html_email != 1) {
				$mail_body .= "########################################\n\n";
			}
			// Determine device name
			$device = new Device($obj->device_id);
			$device_name = $device->get_name();

			// Determine device name
			$user = new User($obj->chgby_id);

			$fullname = $user->get_full_name();
			$effects = stripslashes(($obj->effects));
			$description = stripslashes(($obj->description));
			$back_out = stripslashes(($obj->back_out));

			$mail_body .= "\n--------  DEVICE CHANGE DETAILS: $device_name ------\n\n";
			$mail_body .= "CHANGED BY: $fullname\n\n";
			$mail_body .= "CHANGE DETAILS: $description\n\n";
			$mail_body .= "CHANGE EFFECTS: $effects\n\n";
			$mail_body .= "BACKOUT PROCEDURE: $back_out\n\n";

			if ($html_email == 1)
			{
				$dvalues = array();
				$user = new User($obj->chgby_id);
				$fullname = $user->get_full_name();
				$effects = stripslashes(nl2br($obj->effects));

				$description = stripslashes(nl2br(htmlentities($obj->description)));
				// For Juniper code we need to keep the indents in place. so replace white spaces as well
				$description = str_replace("  ", "&nbsp;&nbsp;", $description);
				$back_out = stripslashes(nl2br($obj->back_out));

				$heading = array("Device Details -- $device_name");
				$titles = array("Device", "Changed By","Change Details","Change Effects","Backout Procedure");
				array_push ($dvalues,$device_name,$fullname, $description,$effects,$back_out);

				$dform = new Form("auto", 2);
				$dform->setHeadings($heading);
				$dform->setFieldType($fieldType);
				$dform->setTitles($titles);
				$dform->setDatabase($titles);
				$dform->setData($dvalues);

				//set the table size
				$dform->setTableWidth("94%");
				$dform->setTitleWidth("20%");
				$html_content .= "<p><br>".$dform->ShowForm(1) ."</p>";
			}
		}
		if ( $html_email == 1) {
			$mail_body = $html_content;
			// make sure that lines don't get too long, otherwise email clients will
			// wrap the line and include an ! character
			$mail_body = wordwrap($mail_body,800, "\n"); // new statement with a linebreak every 800 characters
		}
		if (mail($mail_to, $mail_subject, $mail_body, $mail_headers))
		{
			print_r("<h4>Emailing Change Report Notification to: $mail_to</h4><br>...Please standby.  Browser will refresh.");
			return "<meta http-equiv=\"REFRESH\" content=\"1;url=$this->url&action=list_changes\">";
		} 
		else 
		{
			return "<b>Unable to deliver mail....<br></b>";
		}
	}

	function get_numdevices_for_change($inChangeID = -1) 
	{
		$numDevices = 0;
		if ($inChangeID == -1) 
		{
			return $numDevices;
		}
		else
		{
			$query = "SELECT * from plugin_ChangeManager_Components WHERE change_id = '$inChangeID'";
			$result =  mysql_query($query) ;
			if (!$result)
			{
				return "<b>Oops something went wrong, unable to perform query of:$query ".  mysql_error() ."</b>";
			}
			$numDevices = mysql_num_rows($result);
			return $numDevices;
		}
	}

	function delete_change() {
		if ($_GET['return'] == 'show_change') 
		{
			$returnto = 'show_change';
		} else
		{
			$returnto = 'list_changes';
		}

		$cid = $_GET['cid'];
		if (!is_numeric($cid))
		{
			return "Invalid Change id";
		}

		// Confirmation part
		if ((! isset($_GET['confirm'])) || (($_GET['confirm'] !='no') && ($_GET['confirm'] !='yes')))
		{
			return "$content  <b>Are you sure your want to remove this change report?</b>
					<table>
						<tr>
							<td><a href='$this->url&action=delete_change&cid=$cid&confirm=yes'> Yes </a></td>
							<td><a href='$this->url&action=$returnto&cid=$cid'> No </a></td>
						</tr>
					</table>
					";
		}

		$query = "DELETE from plugin_ChangeManager_Changes WHERE change_id = '$cid' LIMIT 1";
		$result =  mysql_query($query) ;

		if (!$result)
		{
			return "<b>Oops something went wrong, unable to update changes :$query ".  mysql_error() ."</b>";
		}
		return "<meta http-equiv=\"REFRESH\" content=\"0;url=$this->url&action=list_changes\">";
	}

	function delete_device_component_from_change() {
		if ($_GET['return'] == 'edit_change') 
		{
			$returnto = 'edit_change';
		} else
		{
			$returnto = 'show_change';
		}

		$cid = $_GET['cid'];
		$component_id = $_GET['component_id'];
		if (!is_numeric($cid)) {
			return "Invalid Change id";
		}
		if (!is_numeric($component_id)) {
			return "Invalid component id";
		}

		// Confirmation part
		if ((! isset($_GET['confirm'])) || (($_GET['confirm'] !='no') && ($_GET['confirm'] !='yes')))
		{
			return "$content  <b>Are you sure your want to remove this device change from your change report?</b>
					<table>
					<tr>
						<td><a href='$this->url&action=delete_device_component&return=$returnto&cid=$cid&component_id=$component_id&confirm=yes'> Yes </a></td>
						<td><a href='$this->url&action=$returnto&cid=$cid&confirm=no'> No </a></td>
					</tr>
					</table>
";
		}
		elseif ($_GET['confirm'] =='no')
		{
			// return
			return "<meta http-equiv=\"REFRESH\" content=\"0;url=$this->url&action=$returnto&cid=$cid\">";
		}


		$query = "DELETE from plugin_ChangeManager_Components WHERE change_id = '$cid' and id = '$component_id' LIMIT 1";
		$result =  mysql_query($query) ;
		if (!$result)
		{
			return "<b>Oops something went wrong, unable to update changes :$query ".  mysql_error() ."</b>";
		}
		return "<meta http-equiv=\"REFRESH\" content=\"0;url=$this->url&action=$returnto&cid=$cid\">";
	}

	function insert_change()
	{

		$content = '';

		$title = mysql_real_escape_string($_POST['Summary']);
		$planned_change_date = mysql_real_escape_string($_POST['PlannedChangeDate']);
		$plannedChangeTime = mysql_real_escape_string($_POST['PlannedChangeTime']);
		$change_date = mysql_real_escape_string($_POST['ChangeDate']);
		$ChangeTime = mysql_real_escape_string($_POST['ChangeTime']);
		$PrimaryContact = mysql_real_escape_string($_POST['PrimaryContact']);
		$SecondaryContact = mysql_real_escape_string($_POST['SecondaryContact']);
		$Impact = mysql_real_escape_string($_POST['Impact']);
		$Status = mysql_real_escape_string($_POST['Status']);
		$ChangeDescription = mysql_real_escape_string($_POST['ChangeDescription']);
		$sql_date_time = "$change_date $ChangeTime";

		// If no change persons are defined, set it to NULL
		if (is_numeric($PrimaryContact))
		{
			$sql_change_contact_1 = "change_contact_1 = '$PrimaryContact'";
		}
		else
		{
			$sql_change_contact_1 = "change_contact_1 = NULL";
		}
		if (is_numeric($SecondaryContact))
		{
			$sql_change_contact_2 = "change_contact_2 = '$SecondaryContact'";
		}
		else
		{
			$sql_change_contact_2 = "change_contact_2 = NULL";
		}

		if (($change_date == '') || ($ChangeTime == '')) 
		{
			$sql_change_dateTime = "change_date = NULL";
		}
		else
		{
			$sql_change_dateTime = "change_date = '$change_date $ChangeTime'";
		}

		if (($planned_change_date == '') || ($plannedChangeTime == '')) 
		{
			$sql_planned_change_dateTime = "planned_change_date = NULL";
		}
		else
		{
			$sql_planned_change_dateTime = "planned_change_date = '$planned_change_date $plannedChangeTime'";
		}

		if (is_numeric($Impact))
		{
			$sql_Impact = "impact = '$Impact'";
		}
		else
		{
			$sql_Impact = "impact = '0'";
		}
		
		if (is_numeric($Status))
		{
			$sql_status = "status = '$Status'";
		}
		else
		{
			$sql_status = "status = '0'";
		}
		
		$query = "INSERT into plugin_ChangeManager_Changes SET 
					title = '$title', 
					notes = '$ChangeDescription',
					$sql_change_dateTime,
					$sql_planned_change_dateTime,
					$sql_change_contact_1,
					$sql_change_contact_2,
					$sql_Impact,
					$sql_status";
		$result =  mysql_query($query) ;

		if (!$result)  {
			return "<b>Oops something went wrong, unable to update changes :$query ".  mysql_error() ."</b>";
		}
		
		$cid = mysql_insert_id();
		$device_array = $_POST['new_device_component'];
		
		$device_id = $device_array['Device'];
		$changed_by = $device_array['ChangedBy'];	
		$desc = mysql_real_escape_string(($device_array['Description']));
		$effects = mysql_real_escape_string($device_array['Effects']);
		$backout = mysql_real_escape_string($device_array['BackoutProcedure']);

		// If no change persons are defined, set it to NULL
		if (is_numeric($changed_by)) {
			$sql_changed_by = "chgby_id = '$changed_by'";
		} else {
			$sql_changed_by = "chgby_id = NULL";
		}

		if (!is_numeric($device_id)) 
		{
			// Nothing to do.
			// Probably no device info filled in
		} 
		else 
		{
			$dev_query = "INSERT INTO plugin_ChangeManager_Components SET
							device_id = '$device_id',
							$sql_changed_by,
							description = '$desc',
							effects = '$effects',
							back_out = '$backout',
							change_id = '$cid'";
			print_r($dev_query);
			$dev_result =  mysql_query($dev_query) ;
			if (!$dev_result)  
			{
				// Something went wrong
				// Use session checkl
			}
		}

		return  $cid;
	}
	function update_change() {

		// First determine change id
		if ((isset($_GET[cid])) && (is_numeric($_GET[cid])))
		{
			$cid = mysql_real_escape_string($_GET[cid]);
		} else
		{
			return "<b>Sorry change not found<br></b>";
		}
		$content = '';

		$title = mysql_real_escape_string($_POST['Summary']);
		$change_date = mysql_real_escape_string($_POST['ChangeDate']);
		$ChangeTime = mysql_real_escape_string($_POST['ChangeTime']);
		$planned_change_date = mysql_real_escape_string($_POST['PlannedChangeDate']);
		$plannedChangeTime = mysql_real_escape_string($_POST['PlannedChangeTime']);	
		$PrimaryContact = mysql_real_escape_string($_POST['PrimaryContact']);
		$SecondaryContact = mysql_real_escape_string($_POST['SecondaryContact']);
		$Impact = mysql_real_escape_string($_POST['Impact']);
		$status = mysql_real_escape_string($_POST['Status']);
		$ChangeDescription = mysql_real_escape_string($_POST['ChangeDescription']);
		//list($day, $month, $year) = split('[/.-]', $change_date);
		$sql_date_time = "$change_date $ChangeTime";

		// Need to check if user id exists
		
		// If no change persons are defined, set it to NULL

		// Get all users 
		$allUsers_real = User::get_users_by_fullname();
		
		if (is_numeric($PrimaryContact) && array_key_exists($PrimaryContact,$allUsers_real))
		{
			$sql_change_contact_1 = "change_contact_1 = '$PrimaryContact'";
		}
		else
		{
			$sql_change_contact_1 = "change_contact_1 = NULL";
		}

		if (is_numeric($SecondaryContact) && array_key_exists($SecondaryContact,$allUsers_real))
		{
			$sql_change_contact_2 = "change_contact_2 = '$SecondaryContact'";
		}
		else
		{
			$sql_change_contact_2 = "change_contact_2 = NULL";
		}

		if (($change_date == '') || ($ChangeTime == ''))
		{
			$sql_change_dateTime = "change_date = NULL";
		}
		else
		{
			$sql_change_dateTime = "change_date = '$change_date $ChangeTime'";
		}

		if (($planned_change_date == '') || ($plannedChangeTime == ''))
		{
			$sql_planned_change_dateTime = "planned_change_date = NULL";
		}
		else
		{
			$sql_planned_change_dateTime = "planned_change_date = '$planned_change_date $plannedChangeTime'";
		}

		if (is_numeric($status))
		{
			$sql_change_status = ", status = '$status'";
		}
		else
		{
			$sql_change_status = "";	
		}


		$query = "UPDATE plugin_ChangeManager_Changes
					SET  title = '$title', 
					notes = '$ChangeDescription',
					$sql_change_dateTime,
					$sql_planned_change_dateTime,
					$sql_change_contact_1,
					$sql_change_contact_2,
					impact = '$Impact'
					$sql_change_status 
					WHERE change_id = '$cid'";
		$result =  mysql_query($query) ;
		if (!$result)
		{
			print "<b>Oops: something went wrong, unable to update changes :$query ".  mysql_error() ."</b> ";
			exit;
		}

		// Now update devices only if exists
		if (isset($_POST['device_component']))
		{
			foreach($_POST['device_component'] as $component_id => $device_array) 
			{
				$device_id = $device_array['Device'];
				$changed_by = $device_array['ChangedBy'];	
				$desc = mysql_real_escape_string(($device_array['Description']));
				$effects = mysql_real_escape_string($device_array['Effects']);
				$backout = mysql_real_escape_string($device_array['BackoutProcedure']);

				if (!is_numeric($device_id)) 
				{
					$content .= "Sorry device id is not valid. Ignoring $device_id <br>";
					continue;
				}
				if (!is_numeric($component_id))
				{
					$content .= "Sorry component_id is not valid. Ignoring $component_id <br>";
					continue;
				}

				/* Allow for empty changed by field
				if (!is_numeric($changed_by)) {
				$content .= "Sorry changed_by is not valid. Ignoring $changed_by <br>";
				continue;
				}
				*/ 
				
				if (is_numeric($changed_by) && array_key_exists($changed_by,$allUsers_real))
				{
					$sql_changed_by = "chgby_id = '$changed_by'";
				}
				else
				{
					$sql_changed_by = "chgby_id = NULL";
				}
				
				$dev_query = "UPDATE plugin_ChangeManager_Components SET
								device_id = '$device_id',
								$sql_changed_by, 
								description = '$desc',
								effects = '$effects',
								back_out = '$backout'
								WHERE id = '$component_id'";
				$dev_result =  mysql_query($dev_query) ;
				if (!$dev_result)  {
					$content .= "<b>Oops something went wrong, unable to update changes :$dev_query ".  mysql_error() ."</b>";
					print $content;exit;
				}
			}
		}
		if ((isset($_POST['new_device_component'])) && (is_numeric($_POST['new_device_component']['Device']))) {
			$new_device = $_POST['new_device_component'];
			// This array contains new device
			$device_id = mysql_real_escape_string($new_device['Device']);	
			$changed_by = mysql_real_escape_string($new_device['ChangedBy']);	
			$desc = mysql_real_escape_string(($new_device['Description']));
			$effects = mysql_real_escape_string($new_device['Effects']);
			$backout = mysql_real_escape_string($new_device['BackoutProcedure']);

			if (!is_numeric($device_id)) {
				$content .= "Sorry device id is not valid. Ignoring $device_id <br>";
			}
			if (!is_numeric($changed_by)) {
				$content .= "Sorry changed_by is not valid. Ignoring $changed_by <br>";
			}
			$dev_query = "INSERT INTO plugin_ChangeManager_Components SET
							device_id = '$device_id',
							chgby_id = '$changed_by',
							description = '$desc',
							effects = '$effects',
							back_out = '$backout',
							change_id = '$cid'";
			$dev_result =  mysql_query($dev_query) ;
			if (!$dev_result)  {
				$content .= "<b>Oops something went wrong, unable to update changes :$dev_query ".  mysql_error() ."</b>";
			}
		}

		#$this->email_change();
		return $content;
	}


	function render_show_change() {

		// First determine change id
		// If not a;ready defined. then check GET

		if ((isset($_GET[cid])) && (is_numeric($_GET[cid]))) {
			$cid = mysql_real_escape_string($_GET[cid]);
		} else {
			return "<b>Sorry change not found<br></b>";
		}

		//$content = "<h1>Change Details (#$cid) </h1>";

		// Tools menu
		$tool = new EdittingTools();
		if ($_SESSION['access'] >= 50) {
			$toolNames = array("Edit","Add Device","Delete Change");
			$toolIcons = array("edit","add","delete");
			$toolHandlers = array(
			"window.location.href='$this->url&action=edit_change&cid=$cid'",
			"window.location.href='$this->url&action=edit_change&cid=$cid&add_device#new_device'",
			"window.location.href='$this->url&action=delete_change&cid=$cid&return=show_change'"
			);
			$contentH .= $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
		}
		$contentH .=" <div style=\"clear:both;\"></div><br> ";

		/*
			First Generic change info
		*/ 
		$query = "SELECT change_id, title, notes, record_date, planned_change_date,change_date,
					change_contact_1, change_contact_2, impact, status
					FROM plugin_ChangeManager_Changes
					WHERE change_id = '$cid'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			return "<b>Oops something went wrong, unable to select changes </b>";
		}
		$values = array();
		while ($obj = mysql_fetch_object($result)){
			$notes = nl2br($obj->notes);
			$user1 = new User($obj->change_contact_1);
			$contact_name1 = $user1->get_full_name();
			$user2 = new User($obj->change_contact_2);
			$contact_name2 = $user2->get_full_name();
			$impact_name = $this->impact_values{$obj->impact};
			$status_name = $this->status_values{$obj->status};

			if (is_null($obj->planned_change_date))
			{ 
				$planned_change_date = 'Not Specified';
			}
			else
			{ 
				$planned_change_date = $obj->planned_change_date;
			}
			if (is_null($obj->change_date))
			{
				$change_date = 'Not Specified';
			}
			else
			{ 
				$change_date = $obj->change_date;
			}
			array_push ($values,$obj->title,$planned_change_date,$change_date,
			$contact_name1,$contact_name2,$impact_name,$status_name, $notes);

			$title = $obj->title;

		}
		$content = "<hr align=\"left\" style=\"width:94%;border:1px solid #C0C0C0;\">
					<h2>CHANGE (#$cid): $title</h2>
					<hr align=\"left\" style=\"width:94%;border:1px solid #C0C0C0;\">"
					. $contentH;

		$form = new Form("auto", 2);
		$heading = array("Change Details");
		$titles = array("Summary", "Planned Date","Completion Date","Primary Contact", "Secondary Contact","Impact","Status","Change Description");

		#$fieldType = array(1=>"text_area");
		#$form->setFieldType($fieldType);

		$form->setSortable(false);
		$form->setHeadings($heading);
		$form->setTitles($titles);
		$form->setDatabase($titles);
		$form->setData($values);

		//set the table size
		$form->setTableWidth("94%");
		$form->setTitleWidth("20%");
		$content .= $form->ShowForm(1);


		/*
			Device specific change info
		*/ 
		$query2 = "SELECT device_id, impact, effects, chgby_id, change_date, description,
					back_out, status, id
					FROM plugin_ChangeManager_Components
					WHERE change_id = '$cid'";
		$result2 =  mysql_query($query2) ;
		if (!$result2)  {
			return "<b>Oops something went wrong, unable to select changes </b>";
		}

		$content .= "<table style='width:94%; clear:left;' id=\"sortDataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr></tr><td><h2>Device Changes</h2></td></tr></table>";
		while ($obj = mysql_fetch_object($result2)){
			$dvalues = array();

			// Determine device name
			$device = new Device($obj->device_id);
			$device_name = $device->get_name();

			// Determine device name
			$user = new User($obj->chgby_id);
			$fullname = $user->get_full_name();
			$effects = stripslashes(nl2br($obj->effects));

			$description = stripslashes(nl2br(htmlentities($obj->description)));
			// For Juniper code we need to keep the indents in place. so replace white spaces as well
			$description = str_replace("  ", "&nbsp;&nbsp;", $description);
			$back_out = stripslashes(nl2br($obj->back_out));

			array_push ($dvalues,$device_name,$fullname,
			$description,$effects,$back_out);

			$heading = array("Device Details: $device_name <div style='text-align: right;'> 
<a href='$this->url&action=delete_device_component&cid=$cid&component_id=$obj->id&return=show_change' style='color:#FFFFFF'>
Delete <img src='icons/Delete.png' height=20></a></div>");
			$titles = array("Device", "Changed By","Change Details","Change Effects","Backout Procedure");
			#$fieldType = array(4=>"text_area");
			#$fieldType = array(4=>"text_area.height:60px");
			$dform = new Form("auto", 2);
			$dform->setHeadings($heading);
			$dform->setFieldType($fieldType);
			$dform->setTitles($titles);
			$dform->setDatabase($titles);
			$dform->setData($dvalues);

			//set the table size
			$dform->setTableWidth("94%");
			$dform->setTitleWidth("20%");
			$content .= $dform->ShowForm(1);

		}
		return $content;
	}

	function render_new_change() {
	
		/**
		These are the fields for the change form:
			Summary
			PlannedChangeDate
			PlannedChangeTime
			ChangeDate
			ChangeTime
			PrimaryContact
			SecondaryContact
			Impact
			Status
			ChangeDescription
		*/
		
		$content = "<h1>New Change  </h1>";

		// Add Javascript input form validation function
		// Form.php form always renders forms with form name of "MyClassForm".
		$javascriptFormValidation = "
		<script language=\"javascript\">
		function validateForm()
		{
			// Parse Date and then check if change date is before planned date
			var PlannedChangeDateStr = document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value;
			var PlannedChangeDateStringArray = PlannedChangeDateStr.split(\"-\",3);			
			var PlannedYear = PlannedChangeDateStringArray[0];
			var PlannedMonth = PlannedChangeDateStringArray[1];
			var PlannedDay = PlannedChangeDateStringArray[2];						
			var PlannedChangeTimeStr = document.forms[\"MyClassForm\"][\"PlannedChangeTime\"].value;
			var PlannedChangeTimeStringArray = PlannedChangeTimeStr.split(\":\",2);			
			var PlannedHour = PlannedChangeTimeStringArray[0];
			var PlannedMinute = PlannedChangeTimeStringArray[1];
			
			var PlannedChangeDateTime = new Date(PlannedYear,PlannedMonth,PlannedDay,PlannedHour,PlannedMinute);
			
			// alert('Date: ' + PlannedChangeDateTime.getDate());
			// alert('Month: ' + PlannedChangeDateTime.getMonth());
			// alert('Year: ' + PlannedChangeDateTime.getFullYear());
			// alert('Hour: ' + PlannedChangeDateTime.getHours());
			// alert('Minute: ' + PlannedChangeDateTime.getMinutes());
			
			// alert('PlannedChangeDate ' + document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value + ' was chosen.');
			// alert('PlannedChangeTime ' + document.forms[\"MyClassForm\"][\"PlannedChangeTime\"].value + ' was chosen.');
			
			if (document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value == \"\")
			{
				alert('No Planned Change Date or Time entered!');
				return false;
			}
			
			var CompletionChangeDateStr = document.forms[\"MyClassForm\"][\"ChangeDate\"].value;
			var CompletionChangeTimeStr = document.forms[\"MyClassForm\"][\"ChangeTime\"].value;

			if (
				(
					(
						(CompletionChangeDateStr != \"\")
						 && 
						(CompletionChangeTimeStr == \"Pick an option\")
					)
				) 
				||		
				(
					(
						(CompletionChangeDateStr == \"\")
						&&
						(CompletionChangeTimeStr != \"Pick an option\")
					)
				)
			)
			{
				alert('Ensure both Completion Date and Completion Time entered!');
				return false;
			}
			
			if(CompletionChangeDateStr != \"\" && CompletionChangeTimeStr != \"Pick an option\")
			{			
				var CompletionChangeDateStringArray = CompletionChangeDateStr.split(\"-\",3);			
				var CompletionYear = CompletionChangeDateStringArray[0];
				var CompletionMonth = CompletionChangeDateStringArray[1];
				var CompletionDay = CompletionChangeDateStringArray[2];						

				var CompletionChangeTimeStringArray = CompletionChangeTimeStr.split(\":\",2);			
				var CompletionHour = CompletionChangeTimeStringArray[0];
				var CompletionMinute = CompletionChangeTimeStringArray[1];
				
				var CompletionChangeDateTime = new Date(CompletionYear,CompletionMonth,CompletionDay,CompletionHour,CompletionMinute);
				
				/* alert('Date: ' + CompletionChangeDateTime.getDate());
				alert('Month: ' + CompletionChangeDateTime.getMonth());
				alert('Year: ' + CompletionChangeDateTime.getFullYear());
				alert('Hour: ' + CompletionChangeDateTime.getHours());
				alert('Minute: ' + CompletionChangeDateTime.getMinutes()); */
				
				//alert('CompletionChangeDate ' + document.forms[\"MyClassForm\"][\"ChangeDate\"].value + ' was chosen.');
				//alert('CompletionChangeTime ' + document.forms[\"MyClassForm\"][\"ChangeTime\"].value + ' was chosen.');

				if(CompletionChangeDateTime < PlannedChangeDateTime)
				{
					alert(\"Completion Date: \" + document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value + \" \" + document.forms[\"MyClassForm\"][\"PlannedChangeTime\"].value + \" entered is before Planned Date: \" + document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value + \" \" + document.forms[\"MyClassForm\"][\"PlannedChangeTime\"].value + \"!\"); 
					return false;
				}	
			}
			
			if (document.forms[\"MyClassForm\"][\"Summary\"].value == \"\")
			{
				alert(\"Summary must be filled out!\");
				return false;
			}	
		}
		</script>";
		
		$content .= $javascriptFormValidation;
		
		// Get all users 
		$allUsers_real = User::get_users_by_fullname();
		$allUsers = User::get_users_by_fullname();
		// Do not show Administrator account in changed by dropdown
		unset($allUsers[1]);
		asort($allUsers);
		
		/*
			First Generic change info
		*/
		$values = array();
		$form = new Form("auto", 2);
		// Need to count the number of sections in this form
		// Used by editForm($numHead)  later

		// First Include the datescripts initialized with correct date
		$year = date("Y",mktime());
		$month = date("m",mktime());
		$day = date("d",mktime());
		$hour = date("H",mktime());
		$minute = date("i",mktime());
		$sql_PlannedTime = "$hour:00";
		$sql_ChangeTime = "$hour:30";


		$planned_date ="$year-$month-$day";
		$fieldType[1]= "date_picker";

		$fieldType[2]= "drop_down";
		$form->setType($this->timetable); // Drop down

		$change_date ="$year-$month-$day";
		$fieldType[3]= "date_picker";

		$fieldType[4]= "drop_down";
		$form->setType($this->timetable); // Drop down

		$fieldType[5]= "drop_down";
		$form->setType($allUsers); // Drop down
		$fieldType[6]= "drop_down";
		$form->setType($allUsers); // Drop down
		$fieldType[7]= "drop_down";
		$form->setType($this->impact_values); // Drop down
		$fieldType[8]= "drop_down";
		$form->setType($this->status_values); // Drop down

		array_push ($values,"",$planned_date,$sql_PlannedTime,"","",$_SESSION['fullname'],"","  ","  ","");

		$heading = array("Change Details");
		$titles = array("Summary", "Planned Date","Planned Time", "Completion Date","Completion Time", 
		"Primary Contact", "Secondary Contact","Impact","Status","Change Description");
		$postkeys = array("Summary","PlannedChangeDate","PlannedChangeTime", "ChangeDate","ChangeTime","PrimaryContact", "SecondaryContact","Impact","Status","ChangeDescription");

		$fieldType[9]="text_area";
		$i =9;

		/*
			Device specific change info
		*/ 

		array_push($heading,"*<break>*","Device Details - New Device");
		array_push($titles,"*<break>*","Device","Changed By","Change Details","Effects","Backout Procedure");
		$allDevices = Device::get_devices();
		$form->setType($allDevices); // Drop down
		$fieldType[$i+1]= "drop_down";
		$form->setType($allUsers); // Drop down
		$fieldType[$i+2]= "drop_down";

		array_push($postkeys,"new_device_component[Device]","new_device_component[ChangedBy]",
		"new_device_component[Description]","new_device_component[Effects]",
		"new_device_component[BackoutProcedure]");
		$fieldType[$i+3]="text_area";
		$fieldType[$i+4]="text_area";
		$fieldType[$i+5]="text_area.height:90px";
		array_push ($values,"",$_SESSION['fullname'],"","","");
		// Create Device index

		//set the table size
		$form->setFieldType($fieldType);

		$form->setSortable(false);
		$form->setHeadings($heading);
		$form->setTitles($titles);
		$form->setDatabase($postkeys);
		$form->setData($values);

		//set the table size
		$form->setTableWidth("94%");
		$form->setTableWidth("94%");
		$form->setTitleWidth("20%");
		$form->setUpdateValue("InsertChange");
		$form->setUpdateText("Create New Change");
		$content .= $form->EditForm(2);
		return $content;
	}

	function render_edit_change() {

		// First determine change id
		if ((isset($_GET[cid])) && (is_numeric($_GET[cid]))) {
			$cid = $_GET[cid];
		} else {
			return "<b>Sorry change not found<br></b>";
		}

		//$content = "<h1>Change Details (#$cid) </h1>";
		$contentH .=  "<div><p><a href='$this->url&action=edit_change&cid=$cid&add_device#new_device'>
<img src='icons/Add.png'>Add Device Component</a><br></p></div>";
		if (isset($_GET['add_device'])) {
			$add_device = 1;	
		} else { $add_device = 0;}

		// Get all users
		$allUsers_real = User::get_users_by_fullname();
		$allUsers = User::get_users_by_fullname();
		// Do not show Administrator account in changed by dropdown
		unset($allUsers[1]);
		array_push($allUsers, "  ");
		asort($allUsers);
		
		/*
			First Generic change info
		*/ 
		$query = "SELECT change_id, title, notes, record_date, 
					UNIX_TIMESTAMP(change_date) as change_date,
					UNIX_TIMESTAMP(planned_change_date) as planned_change_date,
					change_contact_1, change_contact_2, impact, status
					FROM plugin_ChangeManager_Changes
					WHERE change_id = '$cid'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			return "<b>Oops something went wrong, unable to select changes 'Error, query failed. '" . mysql_error() ."</b>";
		}
		$values = array();
		$form = new Form("auto", 2);
		// Need to count the number of sections in this form
		// Used by editForm($numHead)  later
		$form_sections = 1;

		while ($obj = mysql_fetch_object($result)){

			// First initialized with correct date
			if (is_null($obj->change_date)) { 
				$custom_date= '';
			} else {
				$year = date("Y",$obj->change_date);
				$month = date("m",$obj->change_date);
				$day = date("d",$obj->change_date);
				$hour = date("H",$obj->change_date);
				$minute = date("i",$obj->change_date);
				$sql_time = "$hour:$minute";
				$custom_date ="$year-$month-$day";
			}

			// First initialized with correct date
			if (is_null($obj->planned_change_date)) { 
				$custom_planned_date = '';
			} else {
				$planned_year = date("Y",$obj->planned_change_date);
				$planned_month = date("m",$obj->planned_change_date);
				$planned_day = date("d",$obj->planned_change_date);
				$planned_hour = date("H",$obj->planned_change_date);
				$planned_minute = date("i",$obj->planned_change_date);
				$planned_sql_time = "$planned_hour:$planned_minute";
				$custom_planned_date ="$planned_year-$planned_month-$planned_day";
			}

			// Create status array
			/**
			$status_arr= array();
			$status_arr = $this->status_values;
			if ($obj->status == 1) {
				$status_arr = array();
				//$status_arr[0] = $this->status_values[0];
				$status_arr[1] = $this->status_values[1];
				$status_arr[2] = $this->status_values[2];
			}
			elseif ($obj->status == 2) {
				//unset($status_arr[0]);
				unset($status_arr[1]);
			}
			else {
				unset($status_arr[0]);
				unset($status_arr[1]);
				unset($status_arr[2]);
			}
			*/
			$status_arr= array();
			$status_arr = $this->status_values;


			// date and time for planned change date	
			$fieldType[1]= "date_picker";
			$fieldType[2]= "drop_down";
			$form->setType($this->timetable); // Drop down

			// date and time for actual (completion)change date	
			$custom_date ="$year-$month-$day";
			$fieldType[3]= "date_picker";

			$fieldType[4]= "drop_down";
			$form->setType($this->timetable); // Drop down

			$notes = $obj->notes;
			$user1 = new User($obj->change_contact_1);
			$contact_name1 = $user1->get_full_name();
			$user2 = new User($obj->change_contact_2);
			$contact_name2 = $user2->get_full_name();
			$fieldType[5]= "drop_down";
			$form->setType($allUsers); // Drop down
			$fieldType[6]= "drop_down";
			$form->setType($allUsers); // Drop down

			$fieldType[7]= "drop_down";
			$impact_name = "";
			if($obj->impact == "0")
			{
				// Make it none of the existing drop down selected options if none were previously chosen
				$impact_name = "  ";
			}
			else
			{
				
				$impact_name = $this->impact_values[$obj->impact];
			}
			$form->setType($this->impact_values); // Drop down

			$fieldType[8]= "drop_down";
			$status_name = "";
			if($obj->status == "0")
			{
				// Make it none of the existing drop down selected options if none were previously chosen
				$status_name = "  ";
			}			
			else
			{
				$status_name = $this->status_values[$obj->status];
			}		
			$form->setType($status_arr); // Drop down

			// End Custom field
			$title = $obj->title;
			$fieldType[9]="text_area";
			array_push ($values,$obj->title,$custom_planned_date,$planned_sql_time,$custom_date,$sql_time,
			$contact_name1,$contact_name2,$impact_name,$status_name,$notes);
		}

		$content = "<hr align=\"left\" style=\"width:94%;border:1px solid #C0C0C0;\">
					<h2>CHANGE (#$cid): $title</h2>
					<hr align=\"left\" style=\"width:94%;border:1px solid #C0C0C0;\">"
					. $contentH;
					
		$javascriptFormValidation = "
		<script language=\"javascript\">
		function validateForm()
		{
			// Parse Date and then check if change date is before planned date
			var PlannedChangeDateStr = document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value;
			var PlannedChangeDateStringArray = PlannedChangeDateStr.split(\"-\",3);			
			var PlannedYear = PlannedChangeDateStringArray[0];
			var PlannedMonth = PlannedChangeDateStringArray[1];
			var PlannedDay = PlannedChangeDateStringArray[2];						
			var PlannedChangeTimeStr = document.forms[\"MyClassForm\"][\"PlannedChangeTime\"].value;
			var PlannedChangeTimeStringArray = PlannedChangeTimeStr.split(\":\",2);			
			var PlannedHour = PlannedChangeTimeStringArray[0];
			var PlannedMinute = PlannedChangeTimeStringArray[1];
			
			var PlannedChangeDateTime = new Date(PlannedYear,PlannedMonth,PlannedDay,PlannedHour,PlannedMinute);
			
			// alert('Date: ' + PlannedChangeDateTime.getDate());
			// alert('Month: ' + PlannedChangeDateTime.getMonth());
			// alert('Year: ' + PlannedChangeDateTime.getFullYear());
			// alert('Hour: ' + PlannedChangeDateTime.getHours());
			// alert('Minute: ' + PlannedChangeDateTime.getMinutes());
			
			// alert('PlannedChangeDate ' + document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value + ' was chosen.');
			// alert('PlannedChangeTime ' + document.forms[\"MyClassForm\"][\"PlannedChangeTime\"].value + ' was chosen.');
			
			if (document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value == \"\")
			{
				alert('No Planned Change Date or Time entered!');
				return false;
			}
			
			var CompletionChangeDateStr = document.forms[\"MyClassForm\"][\"ChangeDate\"].value;
			var CompletionChangeTimeStr = document.forms[\"MyClassForm\"][\"ChangeTime\"].value;

			if (
				(
					(
						(CompletionChangeDateStr != \"\")
						 && 
						(CompletionChangeTimeStr == \"Pick an option\")
					)
				) 
				||		
				(
					(
						(CompletionChangeDateStr == \"\")
						&&
						(CompletionChangeTimeStr != \"Pick an option\")
					)
				)
			)
			{
				alert('Ensure both Completion Date and Completion Time entered!');
				return false;
			}
			
			if(CompletionChangeDateStr != \"\" && CompletionChangeTimeStr != \"Pick an option\")
			{			
				var CompletionChangeDateStringArray = CompletionChangeDateStr.split(\"-\",3);			
				var CompletionYear = CompletionChangeDateStringArray[0];
				var CompletionMonth = CompletionChangeDateStringArray[1];
				var CompletionDay = CompletionChangeDateStringArray[2];						

				var CompletionChangeTimeStringArray = CompletionChangeTimeStr.split(\":\",2);			
				var CompletionHour = CompletionChangeTimeStringArray[0];
				var CompletionMinute = CompletionChangeTimeStringArray[1];
				
				var CompletionChangeDateTime = new Date(CompletionYear,CompletionMonth,CompletionDay,CompletionHour,CompletionMinute);
				
				/* alert('Date: ' + CompletionChangeDateTime.getDate());
				alert('Month: ' + CompletionChangeDateTime.getMonth());
				alert('Year: ' + CompletionChangeDateTime.getFullYear());
				alert('Hour: ' + CompletionChangeDateTime.getHours());
				alert('Minute: ' + CompletionChangeDateTime.getMinutes()); */
				
				//alert('CompletionChangeDate ' + document.forms[\"MyClassForm\"][\"ChangeDate\"].value + ' was chosen.');
				//alert('CompletionChangeTime ' + document.forms[\"MyClassForm\"][\"ChangeTime\"].value + ' was chosen.');

				if(CompletionChangeDateTime < PlannedChangeDateTime)
				{
					alert(\"Completion Date: \" + document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value + \" \" + document.forms[\"MyClassForm\"][\"PlannedChangeTime\"].value + \" entered is before Planned Date: \" + document.forms[\"MyClassForm\"][\"PlannedChangeDate\"].value + \" \" + document.forms[\"MyClassForm\"][\"PlannedChangeTime\"].value + \"!\"); 
					return false;
				}	
			}
			
			if (document.forms[\"MyClassForm\"][\"Summary\"].value == \"\")
			{
				alert(\"Summary must be filled out!\");
				return false;
			}	
		}
		</script>";
		
		$content .= $javascriptFormValidation;					

		$heading = array("Change Details");
		$titles = array("Summary", "Planned Date","Planned Time", "Completion Date","Completion Time","Primary Contact", "Secondary Contact","Impact","Status","Change Description");
		$postkeys = array("Summary", "PlannedChangeDate","PlannedChangeTime","ChangeDate","ChangeTime","PrimaryContact", "SecondaryContact","Impact","Status","ChangeDescription");


		/*
			Device specific change info
		*/ 
		$query2 = "SELECT id, device_id, impact, effects, chgby_id, change_date, description, back_out, status
					FROM plugin_ChangeManager_Components
					WHERE change_id = '$cid'";
		$result2 =  mysql_query($query2) ;
		if (!$result2)  {
			return "<b>Oops something went wrong, unable to select changes </b>";
		}

		// Get all devices
		$allDevices = Device::get_devices();

		// For Field type index:
		$i=9;
		while ($obj = mysql_fetch_object($result2)){
			$form_sections++;

			// Determine device name
			$device = new Device($obj->device_id);
			$device_name = $device->get_name();

			$form->setType($allDevices); // Drop down
			$fieldType[$i+1]= "drop_down";
			$form->setType($allUsers); // Drop down
			$fieldType[$i+2]= "drop_down";


			// Determine user changed by name
			$user = new User($obj->chgby_id);
			$fullname = $user->get_full_name();
			$effects = $obj->effects;

			array_push ($values,$device_name,$fullname,
			stripslashes($obj->description),stripslashes($effects),stripslashes($obj->back_out));


			array_push($heading,"*<break>*","Device Details:  $device_name  <div style='text-align: right;'> 
<a href='$this->url&action=delete_device_component&cid=$cid&component_id=$obj->id&return=edit_change' style='color:#FFFFFF'>
Delete <img src='icons/Delete.png' height=20></a></div>");
			array_push($titles,"*<break>*","Device","Changed By","Change Details","Effects","Backout Procedure");
			array_push($postkeys,"device_component[$obj->id][Device]","device_component[$obj->id][ChangedBy]",
			"device_component[$obj->id][Description]","device_component[$obj->id][Effects]",
			"device_component[$obj->id][BackoutProcedure]");
			$fieldType[$i+3]="text_area.height:230px";
			$fieldType[$i+4]="text_area.height:90px";
			$fieldType[$i+5]="text_area.height:90px";
			//Update with the number of rows/fields we just added
			$i=$i+5;

		}
		if ($add_device) {
			$form_sections++;
			array_push($heading,"*<break>*","Device Details - New Device <span id=new_device></span>");
			array_push($titles,"*<break>*","Device","Changed By","Change Details","Effects","Backout Procedure");
			$form->setType($allDevices); // Drop down
			$fieldType[$i+1]= "drop_down";
			$form->setType($allUsers); // Drop down
			$fieldType[$i+2]= "drop_down";

			array_push($postkeys,"new_device_component[Device]","new_device_component[ChangedBy]",
			"new_device_component[Description]","new_device_component[Effects]",
			"new_device_component[BackoutProcedure]");
			$fieldType[$i+3]="text_area.height:230px";
			$fieldType[$i+4]="text_area.height:90px";
			$fieldType[$i+5]="text_area.height:90px";
			array_push ($values,"",$_SESSION['fullname'],"","","");
		}
		// Create Device index

		//set the table size
		$form->setFieldType($fieldType);

		$form->setSortable(false);
		$form->setHeadings($heading);
		$form->setTitles($titles);
		$form->setDatabase($postkeys);
		$form->setData($values);

		//set the table size
		$form->setTableWidth("94%");
		$form->setTableWidth("94%");
		$form->setTitleWidth("20%");
		$content .= $form->EditForm($form_sections);
		return $content;
	}
}
?>

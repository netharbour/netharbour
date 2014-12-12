<?php
include_once("sessionCheck.php");
if(!isset($_GET['mode']))
{include("controlBar.php");}
?>

<?
if(!isset($_GET['mode']))
{
?>
<div id="main">
<h1 id="mainTitle">USER SETTINGS</h1>
<?
}
?>

<script type='text/javascript' src='js/table/common.js'></script>
<script type='text/javascript' src='js/table/css.js'></script>
<script type='text/javascript' src='js/table/standardista-table-sorting.js'></script>

<!--<script type='text/javascript' src='js/sorttable.js'></script>-->
<script type='text/javascript' src='js/mouseClicks.js'></script>

<?
/*Database coding: this checks for multiple different actions made by users and responds accordingly.*/
include_once 'classes/EdittingTools.php';
include_once 'classes/PropertyForm.php';
include_once 'classes/Dashboard.php';
include_once 'classes/Widgets.php';
include_once 'classes/AAA.php';
		
//Make a new contact, a new tool bar, and a new form
$tool = new edittingTools();
$keys = array();
$propertyForm = new PropertyForm('auto', 2);
if(!isset($_GET['mode'])){
	if($_GET['tab']==1 || isset($_POST['saveDashboard'])) {
		$name = array("Dashboard Widgets.first.", "Change password");
	}
	elseif($_GET['tab']==2 || isset($_POST['save_pass'])) {
		$name = array("Dashboard Widgets", "Change password.first.");
	} 
	else {
		$name = array("Dashboard Widgets.first.", "Change password");
	}

	$page = array(
		"userSettings.php?action=widgetsManagement&mode=edit",
		"userSettings.php?action=myPass&mode=edit"
	);
	echo $tool->createNewButtons($name, "settingsInfo", $page);
}
echo "<div id='settingsInfo'>";

switch (success)
{
	case $_GET['update']:
	$propertyForm->success("Updated successfully");
	break;
		
	case $_GET['add']:
	$propertyForm->success("Added new data successfully");
	break;
		
	case $_GET['delete']:
	$propertyForm->success("Deleted successfully");
	break;
}
// Now her all sub pages and actions

if (isset($_POST['changePass'])) {
		changePass();
}
if (isset($_POST['saveDashboard'])) {
		updateDashboard();
}

if($_GET['tab']==1 || $_GET['action'] == "widgetsManagement")
{

	displayDashboard();
}
elseif($_GET['tab']==2 || $_GET['action'] == "myPass")
{

	displayMyPass();
}
else {
	//if nothing else, display all the clients for the user to see
	displayDashboard();
}



echo "</div>";
?>
</div>        
<?php 
if(!isset($_GET['mode']))
{include("footer.php");} ?>

<?
#####################################################FUNCTIONS###################################################

function displayMyPass () {
	global $tool, $propertyForm;
	print  "<h2>Change password</h2>";
	
	// 1st check if local user
	$user_id = $_SESSION[userid];
	$user_name = $_SESSION[username];
	$user = new User($user_id);
	if (! $user->is_local_user($user_name,'local')) {
		print "Sorry this feature is only available for Local users.<br>
			You are not a local user, most likely an LDAP user. LDAP users have to
			Update their account at the LDAP server.<br>";
		return;
	}

	
	$form = new Form("auto", 2);
	$heading = array("<h3>Change Password</h3>");
	$titles = array("Current password:", "New password:","Confirm new password:");
	$data = array();
	$postkeys = array('oldpass','newpass1','newpass2');

	$custom1 = "<input name='oldpass' type='password' id='oldpass'>";
	$custom2 = "<input name='newpass1' type='password' id='newpass1'>";
	$custom3 = "<input name='newpass2' type='password' id='newpass2'>";
	$customData = array($custom1,$custom2,$custom3);
	$fieldType = array("custom", "custom","custom");
	$form->setFieldType($fieldType);
	$form->setData($customData);

	$form->setHeadings($heading);
	$form->setTitles($titles);
	$form->setDatabase($postkeys);
	$form->setTableWidth("500px");
	$form->setUpdateValue("changePass");
	$form->setUpdateText("Save Password");
	echo $form->editForm();

}


function changePass() {
	global $propertyForm;
	$oldpass = $_POST[oldpass];
	$newpass1 = $_POST[newpass1];
	$newpass2 = $_POST[newpass2];
	$status == false;
	$user_id = $_SESSION[userid];
	$user_name = $_SESSION[username];
	$user = new User($user_id);

	if (empty($oldpass)) {
		$error =  "Old password is empty<br>";	
	}elseif  (empty($newpass1)) {
		$error =  "new password is empty<br>";	
	}
	elseif (empty($newpass2)) {
		$error = "new password (confirm) is empty<br>";	
	}
	elseif ($newpass2 != $newpass1) {
		$error =  "new password are not the same<br>";	
	}

	// 1st check is user is local user
	elseif (! $user->is_local_user($user_name,'local')) {
		$error = "Sorry you're not a local user so can not change your password<br>
			You are probably an LDAP user. Please contact your admin<br>";	
	}
	// check old pass
	elseif (! $user->authenticate_user($user_name,$oldpass)) {
		$error = "Old password incorrect<br>";	
	}
	elseif (! $user->set_password($newpass1)) {
		$error = $user->get_error();
	}
	elseif (! $user->update()) {
		$error = $user->get_error();
		return false;
	} else {
		$status="success";
	}

	if ($status == "success") {
		echo "<script language='javascript'>LoadPage(\"userSettings.php?action=widgetsManagement&mode=edit&update=".$status."\", 'settingsInfo');</script>";
	}
	else
	{
		$propertyForm->warning("Warning: Failed to update password. Reason: ".$error);
	}

}


function displayDashboard () {
	global $tool, $propertyForm;
	
	echo "<form method='post' action='' style='width:1024px;'>";
	echo "<table id=\"sortDataTable\" class='sortable' cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%; clear:left;'>
		<thead>
		<tr><th style='text-align:left;'>Dashboard widgets</th>
			<th style='text-align:left;'>Description</th>
			<th style='text-align:left;'>Version</th></tr>
		</thead>
		<tbody>";
	
	$widgets = Widgets::get_widgets();
	$curUser = new DashboardUsers($_SESSION['userid']);
	$userWidgets = $curUser->get_users_widgets();

	foreach ($widgets as $id => $value)
	{	
		$curWidget = new Widgets($id);
		if($curWidget->get_enabled())
		{
			echo "<tr>";
			$enabled = false;
			foreach ($userWidgets as $widgetID => $userID)
			{
				if ($widgetID == $curWidget->get_id())
				{
					echo "<td><input type='checkbox' checked name='list[]' value='".$curWidget->get_id()."' />".$curWidget->get_name()."</td>";
					$enabled = true;
					break;
				}
			}
			
			if (!$enabled)
			{
				echo "<td><input type='checkbox' name='list[]' value='".$curWidget->get_id()."' />".$curWidget->get_name()."</td>";
			}
			echo "<td>".$curWidget->get_description()."</td>";
			echo "<td>".$curWidget->get_version()."</td>";
			echo "</tr>";
		}
		else
		{
			$curUser->set_widget_id($id);
			$curUser->remove_widget();
		}
	}

	echo "</tbody>
	</table>";
	echo "<input type='submit' name='saveDashboard' value='Save widget settings' style='float:left; clear:left; margin-bottom:5px;' />";
	echo "</form>";
}

function updateDashboard()
{
	global $tool, $propertyForm;
	
	$enabledWidgets = $_POST['list'];
	$curUser = new DashboardUsers($_SESSION['userid']);
	$userWidgets = $curUser->get_users_widgets();
	
	$update = true;
	
	//Remove all the non existing widgets
	foreach ($userWidgets as $widgetID => $userID)
	{
		$exists = false;
		foreach ($enabledWidgets as $id => $enabledID)
		{
			if ($widgetID == $enabledID)
			{
				$exists = true;
				break;
			}
		}
		
		if (!$exists)
		{
			$curUser->set_widget_id($widgetID);
			$curUser->remove_widget();
		}
	}
	
	//insert the enabled widgets
	foreach ($enabledWidgets as $postID => $enabledID)
	{
		$exists = false;
		
		foreach ($userWidgets as $widgetID => $userID)
		{
			if ($enabledID == $widgetID)
			{
				$exists = true;
				break;
			}
		}
		
		if (!$exists)
		{
			$freePos = false;
			$posX = 0;
			$posY = 0;
			
			
			if(!empty($userWidgets))
			{
				while ($freePos == false)
				{
					foreach ($userWidgets as $widgetID => $userID)
					{
						$widPosX = $curUser->get_position_x($widgetID);
						$widPosY = $curUser->get_position_y($widgetID);
						if (($widPosX == $posX) && ($widPosY == $posY))
						{
							$freePos = false;
							break;
						}
						else {$freePos = true;}
					}
					
					if(!$freePos)
					{
						$posX++;
					
						if ($posX == 3)
						{
							$posX=0;
							$posY++;
						}
						
						if ($posY > 999)
						{break;}
					}
				}
			}

			$curUser->set_position_x($posX);
			$curUser->set_position_y($posY);
			
			$curUser->set_widget_id($enabledID);
			if ($curUser->insert_widget())
			{$update = true;}
			else
			{
				$update = false;
				$error = $curUser->get_error();
			}
		}
	}
	
	//final check, Too glitchy for now
	/*foreach ($userWidgets as $widgetID=> $userID)
	{
		checkPosition($widgetID);
	}*/
	
	
	if($update)
	{
		$status="success";
		echo "<script language='javascript'>LoadPage(\"userSettings.php?action=widgetsManagement&mode=edit&update=".$status."\", 'settingsInfo');</script>";
	}
	else
	{
		$propertyForm->warning("Warning: Failed to enable widgets. Reason: ".$error);
	}
}

//TOO GLITCHY GIVE UP FOR LATER
function checkPosition($widgetID)
{
	$curUser = new DashboardUsers($_SESSION['userid']);
	$userWidgets = $curUser->get_users_widgets();
	
	$curWidPosX = $curUser->get_position_x($widgetID);
	$curWidPosY = $curUser->get_position_y($widgetID);
		
	foreach ($userWidgets as $widgetID2 => $userID2)
	{
		if ($widgetID != $widgetID2)
		{
			$compWidPosX = $curUser->get_position_x($widgetID2);
			$compWidPosY = $curUser->get_position_y($widgetID2);
				
			if (($curWidPosX == $compWidPosX) && ($curWidPosY == $compWidPosY))
			{
				$curUser->set_widget_id($widgetID2);
				
				$newX = $compWidPosX + 1;
				$newY = $compWidPosY;
				if ($newX >= 3)
				{
					$newX=0;
					$newY++;
				}
				
				$curUser->set_position_x($newX);
				$curUser->set_position_y($newY);
				$curUser->update_widget();
				
				checkPosition($widgetID2);
			}
		}
	}
}

?>

<?php

include_once("sessionCheck.php");
if(!isset($_GET['mode']))
{include_once("controlBar.php");}
?>

<?
if(!isset($_GET['mode']))
{
?>
<div id="main">
<h1 id="mainTitle">CONGFIGURATIONS</h1>
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
include_once 'classes/Property.php';
include_once 'classes/PropertyForm.php';
include_once 'classes/AAA.php';
include_once 'classes/Widgets.php';
include_once 'classes/Plugins.php';

// Report all PHP errors (see changelog)
//error_reporting(E_ALL);		
//Make a new contact, a new tool bar, and a new form
$tool = new EdittingTools();
$properties = Property::get_properties();
$keys = array();
$propertyForm = new PropertyForm('auto', 2);

foreach($properties as $id => $value)
{
	$id = str_replace(" ", "_", $id);
	array_push($keys, $id);
}
if(!isset($_GET['mode'])){

	if($_GET['tab']==1 || isset($_POST['addUser']) || isset($_POST['delUser']) || isset($_POST['updateUser']) || isset($_POST['userToGroup']))
	{
		$name = array("LDAP", "User Management.first.", "Group Management", "Dashboard Widgets", "Plugins", "Paths");
	}
	else if($_GET['tab']==2 || isset($_POST['addGroup']) || isset($_POST['delGroup']) || isset($_POST['updateGroup']) || isset($_POST['delUserFromGroup']) || isset($_POST['update_group_key']))
	{
		$name = array("LDAP", "User Management", "Group Management.first.", "Dashboard Widgets", "Plugins", "Paths");
	}
	else if($_GET['tab']==3 || isset($_POST['saveDashboard']))
	{
		$name = array("LDAP", "User Management", "Group Management", "Dashboard Widgets.first.", "Plugins", "Paths");
	}
	else if($_GET['tab']==4 || isset($_POST['savePlugins']))
	{
		$name = array("LDAP", "User Management", "Group Management", "Dashboard Widgets", "Plugins.first.", "Paths");
	}
	else if($_GET['tab']==5 || isset($_POST['savePaths']))
	{
		$name = array("LDAP", "User Management", "Group Management", "Dashboard Widgets", "Plugins", "Paths.first.");
	}
	else
	{
		$name = array("LDAP.first.", "User Management", "Group Management", "Dashboard Widgets", "Plugins","Paths");
	}
	$page = array("configurations.php?action=LDAP&mode=edit", "configurations.php?action=userManage&mode=edit", "configurations.php?action=groupManage&mode=edit", "configurations.php?action=dashWidgets&mode=edit", "configurations.php?action=plugins&mode=edit", "configurations.php?action=paths&mode=edit");
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

if($_GET['tab']==1 || $_GET['action'] == userManage)
{
	displayUserManagement();
}
else if($_GET['tab']==2 || $_GET['action'] == groupManage)
{
	displayGroupManagement();
}
else if($_GET['tab']==3 || $_GET['action'] == dashWidgets)
{
	displayDashboard();
}
else if($_GET['tab']==4 || $_GET['action'] == plugins)
{
	displayPlugins();
}
else if (($_GET['action'] == paths) && ($_GET['mode'] == autodetect))  
{
	autodetectPaths();
	displayPaths();
}
else if($_GET['tab']==5 || $_GET['action'] == paths)
{
	displayPaths();
}
else if(isset($_POST['saveDashboard']))
{
	updateDashboard();
}
else if(isset($_POST['savePlugins']))
{
	updatePlugins();
}
else if (isset($_POST['plugin_update']))
{
	updatePluginConfig();		
}
else if(isset($_POST['savePaths']))
{
	updatePaths();
}
else if(isset($_POST['addUser']))
{
	addUser();
}
else if(isset($_POST['userToGroup']))
{
	addUserToGroup();
}
else if(isset($_POST['delUserFromGroup']))
{
	delUserFromGroup();
}
else if(isset($_POST['delUser']))
{
	removeUser();
}
else if(isset($_POST['updateUser']))
{
	updateUser();
}
else if(isset($_POST['addGroup']))
{
	addGroup();
}
else if(isset($_POST['delGroup']))
{
	removeGroup();
}
else if(isset($_POST['updateGroup']))
{
	updateGroup();
}
else if(isset($_POST['update_group_key']))
{
	updateGroupKey();
}
//if the user prompts to add a new client
else if ($_GET['action'] == add)
{

}
				
//if the user prompts to remove a client
else if($_GET['action'] == remove)
{
	
}
				
//if nothing else, display all the clients for the user to see
else
{
	if (isset($_POST['updateProp']))
	{
		updateProperties($properties);
	}
	else {displayLDAP($properties);}
}

echo "</div>";
?>
</div>        
<?php 
if(!isset($_GET['mode']))
{include("footer.php");} ?>

<?
#####################################################FUNCTIONS###################################################

function displayLDAP($properties)
{
	$LDAPKeys = array('LDAP_enable', 'LDAP_server', 'LDAP_version', 'LDAP_base_dn', 'LDAP_dn', 'LDAP_group_base_dn', 'LDAP_group_search_filter');
	$description = array();
	foreach ($LDAPKeys as $id => $value)
	{
		$description[$id] = htmlspecialchars(Property::get_desc($value));
	}
	echo "<div id='ldap_info'>The CMDB has the possibility to authenticate users against a LDAP server. Once LDAP users are authenticated. Once a LDAP user is successfully authenticated the next step is to determine which LDAP groups this users belongs to.  These groups are then compared to the 'LDAP Group Name' in group management.  If there's a match the user will be put in the matched group. In case of multiple matches the user will become a member of the group with the highest access level. If no group is found the user will become a member of the default group.</div>";
	
	echo "<form method='post' style='width:1024px;'>
	<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%; clear:left;'>
		<tr><th colspan='2' style='text-align:left;'>Configurations</th></tr>";
	
	echo "<tr class='normal'><td class='info' style='width:20%'><h3>LDAP Authentication</h3>".$description[0]."</td>
		<td>
		<select name='".$LDAPKeys[0]."'>";
			
	if (Property::get_property($LDAPKeys[0]) == 1){
		echo "<option selected value='1'>Enabled</option>
			<option value='0'>Disabled</option>";
	}
	else {
		echo "<option value='1'>Enabled</option>
			<option selected value='0'>Disabled</option>";
	}
	echo "</select></td></tr>";
	
	echo "<tr class='normal'><td class='info' style='width:20%'><h3>LDAP Server</h3>".$description[1]."</td>
		<td><input type='text' name='".$LDAPKeys[1]."' value='".Property::get_property($LDAPKeys[1])."' /></td></tr>";
		
	echo "<tr class='normal'>
		<td class='info' style='width:20%'><h3>LDAP Version</h3>".$description[2]."</td>
		<td>
		<select name='".$LDAPKeys[2]."'>";
	if (Property::get_property($LDAPKeys[2]) == 2){
		echo "<option selected value='2'>Version 2</option>
			<option value='3'>Version 3</option>";
	}
	else {
		echo "<option value='2'>Version 2</option>
			<option selected value='3'>Version 3</option>";
	}	
	echo "</select></td></tr>";
	
	echo "<tr class='normal'><td class='info' style='width:20%'><h3>Search Base Distinguished Name (Base DN)</h3>".$description[3]."</td>
		<td><input type='text' name='".$LDAPKeys[3]."' value='".Property::get_property($LDAPKeys[3])."' /></td></tr>";
		
	echo "<tr class='normal'><td class='info' style='width:20%'><h3>LDAP Bind Distinguished Name (Bind DN)</h3>".$description[4]."</td>
		<td><input type='text' name='".$LDAPKeys[4]."' value='".Property::get_property($LDAPKeys[4])."' /></td></tr>";
		
	echo "<tr class='normal'><td class='info' style='width:20%'><h3>LDAP Group Base Distinguished Name</h3>".$description[5]."</td>
		<td><input type='text' name='".$LDAPKeys[5]."' value='".Property::get_property($LDAPKeys[5])."' /></td></tr>";
		
	echo "<tr class='normal'><td class='info' style='width:20%'><h3>LDAP Group Search Filter</h3>".$description[6]."</td>
		<td><input type='text' name='".$LDAPKeys[6]."' value='".Property::get_property($LDAPKeys[6])."' /></td></tr>";

	echo "</table>
	<input type='submit' name='updateProp' value='Update' />
	</form>";
}

function displayUserManagement()
{
	global $tool, $propertyForm;
	
	$users = User::get_users();
	
	echo "<style>";
	foreach ($users as $id => $value)
	{
		echo "#modalBox #dialog".$id;
		echo "{
			width:auto;
			max-width: 80%;
			min-width:40%;
			height:auto;
			padding:10px;
			padding-top:10px;
			overflow:auto;
		}";
	}
	echo "</style>";
	
	$toolNames = array("Add User");
	$toolIcons = array("add");
	$formType = array("newDialog");
	
	echo $tool->createNewModal($toolNames, $toolIcons, $formType);
	
	echo "<form method='post' action='' style='width:1024px;'>";
	echo "<table id=\"sortDataTable\" class='sortable' cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%; clear:left;'>
		<thead>
		<tr><th style='text-align:left;'>Full Name</th>
		<th style='text-align:left;'>User Name</th>
		<th style='text-align:left;'>Email</th>
		<th style='text-align:left;'>User Type</th>
		<th style='text-align:left;'>Group</th>
		<th style='text-align:left;'>Action</th>
		<th style='text-align:left;'>Last Login</th></tr>
		</thead>
		<tbody>";
		
	foreach ($users as $id => $value)
	{
		echo "<tr>";
		$curUser = new User($id);
		echo "<td><input type='checkbox' name='list[]' value='".$curUser->get_user_id()."' />".$curUser->get_full_name()."</td>
		<td>".$curUser->get_user_name()."</td>
		<td>".$curUser->get_email()."</td>
		<td>".$curUser->get_user_type()."</td>";
		
		$group = $curUser->get_groups();
		if(count($group) == 0)
		{
			$group = array("None");
		}
		
		echo "<td>";
		foreach ($group as $gid=>$value) {echo $value.", ";}
		echo "</td>
		<td><a name='modal' href='#dialog".$id."'>Edit</a></td>";
		echo "<td>".$curUser->get_last_login()." (".$curUser->get_last_ip() .")</td>";
		echo "</tr>";
	}

	echo "</tbody>
	</table>";
	echo "<input type='submit' name='delUser' value='Delete Checked Users' style='float:left; clear:left; margin-bottom:5px;' />";
	
	echo "<select name='groups' style='float:right; margin-bottom:5px;'>";
	$groups = Group::get_groups();
	foreach ($groups as $id=>$value)
	{
		$curGroup = new Group($id);
		echo "<option value=".$id.">".$curGroup->get_name()."</option>";
	}
	echo "</select>";
	echo "<input type='submit' name='userToGroup' value='Add Users to Group' style='float:right; margin-bottom:5px; margin-right: 5px;' />";
	
	echo "</form>";
	
	$heading = array("User Information");
	$title = array("Full Name", "User Name", "Password", "Email");
	$key = array("full", "user", "password", "email");
	
	foreach ($users as $id => $value)
	{
		$curUser = new User($id);
		$info = array($id, $curUser->get_full_name(), $curUser->get_user_name(), $curUser->get_email());
		$heading2 = array("User Information");
		$title2 = array("ID", "Full Name", "User Name", "Email");
		$key2 = array("id", "full", "user", "email");
		$fieldType = array("static");
		//create a new modal form for a new interface ports
		$propertyForm->setFieldType($fieldType);
		echo $propertyForm->editModalForm($heading2, $title2, $info, $key2, "dialog".$id, "updateUser");
	}
	//create a new modal form for a new interface ports
	$fieldType = array();
	$propertyForm->setFieldType($fieldType);
	echo $propertyForm->newModalForm($heading, $title, $key, "addUser");
}

function displayGroupManagement()
{
	global $tool, $propertyForm;
	
	$groups = Group::get_groups();
	
	echo "<style>";
	foreach ($groups as $id => $value)
	{
		echo "#modalBox #allUserDialog".$id;
		echo "{
			width:auto;
			max-width: 80%;
			min-width:40%;
			height:auto;
			padding:10px;
			padding-top:10px;
			overflow:auto;
		}";
	}
	echo "</style>";
	
	$toolNames = array("Add Group");
	$toolIcons = array("add");
	$formType = array("newDialog");
	
	echo $tool->createNewModal($toolNames, $toolIcons, $formType);

	foreach ($groups as $id => $value)
	{
		$curGroup = new Group($id);
		$groupID = $id;
		
		$access = $curGroup->get_access_level();
		switch ($access)
		{
			case 0:
			$access = "No Access";
			break;
								
			case 25:
			$access = "Read Only";
			break;
								
			case 50:
			$access = "Read Write Only";
			break;
																
			case 100:
			$access = "Admin";
			break;
		}
		
		echo "<form method='post' action='' style='width:1024px;'><input type='hidden' name='groupID' value='".$curGroup->get_group_id()."' />
		<table id=\"sortDataTable\" class='sortable' cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%; clear:left;'>
		<thead>
		<tr>
		<th style='text-align:left;'>".$curGroup->get_name()."</th>
		<th colspan='10'><a name='modal' href='#dialog".$id."' style='float:right; margin-bottom:5px; margin-right: 5px;'>Edit</a></th>
		</tr>
		</thead>
		<tbody>";
		
		if ($curGroup->get_group_pass() == true) {
			$myModalID = "modalPass". $curGroup->get_group_id();
			// Check if it already has a password or not.
			if ($curGroup->has_password() == 1) {
				// Already has a password
				// this is to update existing pass
				
				// Create custom input field for password
				// as type password is not defined in class...
				$group_pass = "Enabled<br> <a name='modal' href='#".$myModalID."'>Update Group Password</a>";
				$heading = array("Update Group Password");
				$title = array("Old Password", "New Password", "New Password","group_id","action");
				$keys = array("old_pass", "new_pass1", "new_pass2","group_id","update_group_key");
				$data = array("", "", "",$curGroup->get_group_id(),"update_group_key");
				$fieldType = array(0=>"password",1=>"password",2=>"password",3=>"hidden",4=>"hidden");
			} 
			elseif ($curGroup->has_password() == 0) {
	
				// Group does not yet have a pass
				// Inital password will be set below
				$group_pass = "Enabled (no password set)<br> <a name='modal' href='#".$myModalID."'>Set Group Password</a>";
				$heading = array("Configure Group Password");
				$title = array("Old Password", "New Password", "New Password","group_id","action");
				$keys = array("old_pass", "new_pass1", "new_pass2","group_id","update_group_key");
				$data = array("dummy", "", "",$curGroup->get_group_id(),"update_group_key");
				$fieldType = array(0=>'hidden',1=>'password',2=>'password',3=>"hidden",4=>"hidden");
			} 
		

			// Modal for password update

			$form = new Form("auto", 2);
			$form->setHeadings($heading);
			$form->setTitles($title);
			$form->setData($data);
			$form->setDatabase($keys);
			$form->setFieldType($fieldType);

			$form->setModalID($myModalID);

			//set the table size
			$form->setTableWidth("1024px");
			$form->setTitleWidth("20%");

			$modal_group_pass .= $form->modalForm();
			// End Modal for group pass

		} else {
			$group_pass = "Disabled";
		}
		echo "
		<tr class='form'><td style='text-align:left; width:200px;'><h3>Description</h3>".$curGroup->get_description()."</td>
		<td style='text-align:left; width:200px;'><h3>LDAP Group Name</h3>".$curGroup->get_ldap_group_name()."</td>
		<td style='text-align:left; width:100px;'><h3>Access Level</h3>".$access."</td>
		<td style='text-align:left; width:100px;'><h3>Access To Private Data</h3>".$group_pass."</td></tr>";
	

	
		echo "<tr class='form'><td colspan='4'>";
		$groupUsers = $curGroup->get_users();
		foreach ($groupUsers as $id => $value)
		{
			echo "<input type='checkbox' name='userList[]' value='".$id."'>".$value." | ";
		}
		echo "<a name=modal href='#allUserDialog".$groupID."'>Add User...</a>
		</td></tr>";
		
		echo "</tbody>
		</table>";
		echo "<input type='submit' name='delUserFromGroup' value='Delete Users From Group' style='float:right; margin-bottom:5px; margin-right: 5px;' />";
		echo "<input type='submit' name='delGroup' value='Delete Group' style='float:left; clear:left; margin-bottom:20px;' />
		</form>";
		
		echo "<div id='modalBox'>";
	
		$users = User::get_users();
		echo "<div id='allUserDialog".$groupID."' class='window'>
			 <a href='#'class='close' /><img src='icons/close.png'></a>
			 <form method='post' action=''>
			 <input type='hidden' name='groups2' value='".$curGroup->get_group_id()."' />";
			 
		foreach ($users as $id=>$value)
		{
			$isIn = false;
			foreach ($groupUsers as $gid => $gvalue)
			{	
				if($value==$gvalue){$isIn = true;}
			}
			if(!$isIn)
			{
				echo "<input type='checkbox' name='list[]' value='".$id."'>".$value." | "; 
			}
		}
		echo "<input type='submit' name='userToGroup' value='Add Users to Group' />";
		echo "</form>
			 </div>";
			 
		echo "<div id='mask'></div>
			 </div>";
	}
	
	$heading = array("Group Information");
	$title = array("Group Name", "Group Description", "Access Level");
	$key = array("name", "desc", "access");
	
	foreach ($groups as $id => $value)
	{
		$curGroup = new Group($id);
		
		switch ($curGroup->get_access_level())				
		{
			case 0:
			$value = "No Access";
			break;
								
			case 25:
			$value = "Read Only";
			break;
								
			case 50:
			$value = "Read Write Only";
			break;
								
			case 100:
			$value = "Admin";
			break;
		}
		if ($curGroup->get_group_pass() == true) {
			$group_pass = "Enabled";
		} else {
			$group_pass = "Disabled";
		}
		$info = array($id, $curGroup->get_name(), $curGroup->get_description(), $value, $curGroup->get_ldap_group_name(),$group_pass);
		// Only render group pass if it's 
		$heading2 = array("Group Information");
		$title2 = array("ID","Group Name", "Group Description", "Access Level", "LDAP Group","Enable Private Data access");
		$key2 = array("id", "name", "desc", "access", "ldap", "group_pass");
		//create a new modal form for a new interface ports
		$fieldType = array(0=>"static", 3=>"drop_down",5=>"drop_down");
		$propertyForm->setFieldType($fieldType);
		$accessLevel = array(0=>"No Access", 25=>"Read Only", 50=>"Read Write Only", 100=>"Admin");
		$group_pass = array(0=>"Disabled", 1=>"Enabled");
		$propertyForm->setType($accessLevel);
		$propertyForm->setType($group_pass);
		echo $propertyForm->editModalForm($heading2, $title2, $info, $key2, "dialog".$id, "updateGroup");
	}
	//create a new modal form for a new interface ports
	$fieldType = array(2=>"drop_down");
	$propertyForm->setFieldType($fieldType);
	$accessLevel = array(0=>"No Access", 25=>"Read Only", 50=>"Read Write Only", 100=>"Admin");
	$propertyForm->setType($accessLevel);
	echo $propertyForm->newModalForm($heading, $title, $key, "addGroup");
	echo $modal_group_pass;
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
	
	if($_GET['mode'] == refreshWidgets)
	{
		$dir = "widgets/";
		$status = checkDirForWidget($dir);
		if ($status != true)
		{
			$propertyForm->warning("Failed to refresh. Reason: ". $status) ;
		}
	}
	
	$widgets = Widgets::get_widgets();
	
	foreach ($widgets as $id => $value)
	{
		echo "<tr>";
		
		$curWidget = new Widgets($id);
		
		$fileExists = fopen($curWidget->get_filename(), 'r');
		
		if ($fileExists)
		{
			if($curWidget->get_enabled())
			{
				echo "<td><input type='checkbox' checked name='list[]' value='".$curWidget->get_id()."' />".$curWidget->get_name()."</td>";
			}
			else {
				echo "<td><input type='checkbox' name='list[]' value='".$curWidget->get_id()."' />".$curWidget->get_name()."</td>";
			}
			echo "<td>".$curWidget->get_description()."</td>";
			echo "<td>".$curWidget->get_version()."</td>";
			echo "</tr>";
		}
		else
		{
			if (!$curWidget->remove_widget())
			{
				$propertyForm->warning("You widget does not exist, but we failed to remove it from the database. Reason: ". $curWidget->get_error());	
			}
		}
	}

	echo "</tbody>
	</table>";
	echo "<input type='submit' name='saveDashboard' value='Enable checked widgets for users' style='float:left; clear:left; margin-bottom:5px;' />";
	echo "<input type='button' name='refresh' value='Refresh widgets' onclick=\"return LoadPage('configurations.php?action=dashWidgets&mode=refreshWidgets', 'settingsInfo')\" style='float:right; margin-bottom:5px;' />";
	echo "</form>";	
}


function autodetectPaths() {
	global $tool, $propertyForm;
	$my_paths = array(
		"path_snmpwalk" => "snmpwalk",
		"path_snmpget" => "snmpget",
		"path_rrdupdate" => "rrdupdate",
		"path_rrdtool" => "rrdtool",
	);
	$found = 0;
	foreach ($my_paths as $id => $path) {
		unset($output);
		if ($path == "snmpwalk") {
			$path = "snmpbulkwalk";
		}
		$cmd = "which $path";
		exec($cmd, $output, $return_var);
		if ($return_var != 0) {
			$propertyForm->error("Warning: Failed to execute 'which' (<i>$cmd</i>)");
			return;
		} else {
			if (is_readable($output[0])) {
				$property = new Property();
				$property->set_property($id,$output[0]);
				$found++;
			}
		}
	}
	print "<b>Found and Updated $found paths<b>";
}
function updatePaths() {
	global $tool, $propertyForm;
	$my_paths = array(
		"path_snmpwalk" => "snmpwalk",
		"path_snmpget" => "snmpget",
		"path_rrdupdate" => "rrdupdate",
		"path_rrdtool" => "rrdtool",
		"path_rrddir" => "RRD Directory",
	);
	$update = true;
	$error ="";
	foreach ($my_paths as $id => $path) {
		$property = new Property();
		if ($property->set_property($id,$_POST[$id])) {
		} else {
			$update = false;
			$error .= "<br>$id -> ". $property->get_error();
		}
	}
	if($update === true)
	{
		$status="success";
		echo "<script language='javascript'>LoadPage(\"configurations.php?action=paths&mode=edit&update=".$status."\", 'settingsInfo');</script>";
	}
	else
	{
		$propertyForm->error("Warning: Failed to enable widgets. Reason: ".$error);
	}
}
function displayPaths() {
	global $tool, $propertyForm;
	$my_paths = array(
		"path_snmpwalk" => "snmpwalk",
		"path_snmpget" => "snmpget",
		"path_rrdupdate" => "rrdupdate",
		"path_rrdtool" => "rrdtool",
		"path_rrddir" => "RRD Directory",
	);
	$content = "";
	$content .=  "<h1>System Paths</h1>";
		
	$content .= "<div style='padding-left: 600px;'>
			<a class='tooltip' title='This will try to detect the tools below in the current \$path'><img src='icons/Info.png' height='19'></a>
			<a href='javascript:LoadPage(\"configurations.php?action=paths&mode=autodetect\",\"settingsInfo\")'>
			click here to Auto Discover paths</a></div>";

	$form = new Form("auto",2);
	$keyHandlers = array();
	$keyData = array();
	$keyTitle = array();
	$postKeys = array();
		
	foreach ($my_paths as $id => $path) {
		$property = new Property();
		$value = $property->get_property($id);
		if ( $value === false) {
			$value = "WARNING: Property '$id' Not Found in Property table, Contact your admininistrator". $property->get_error();	
		}
		$desc = $property->get_desc($id);
		if (is_readable($value)) {
			$check = "<font size='' color='green'>Found!</font>";

			if ($id == 'path_rrdtool') {
				$cmd = "$value -v| awk '{print $2}'|head -1";
				exec($cmd, $output, $return_var);
				if ($output[0] < "1.4") {
					$check = "<font size='' color='Orange'>Found version $output[0]! You need at least RRDtool version 1.4.0 otherwise some graphs won't display correctlty</font>";
				}
			}  

		} else {
			$check = "<font size='' color='orange'>Not Found!</font>";
		}
		array_push($postKeys, "$id");
		array_push($keyData, "$value");
		array_push($keyTitle, "<font size='2'>$path</font><br><i>$desc</i><br>$check");
                        #$content .= "<tr><td><input type=checkbox name=devices[] value='$id' $checked ></td>";
                        #$content .= "<td>$name</td><td>". $deviceInfo->get_type_name() ."</td>";
                        #$content .= "<td>". $deviceInfo->get_location_name() ."</td></tr>";
	}
                #$content .= "</table> <br>";
                //get all the device and display them all in the 3 sections "Device Name", "Device Type", "Location".
	$heading = array("Program","Path");
        $form->setSortable(true); // or false for not sortable
        $form->setHeadings($heading);
        $form->setEventHandler($handler);
        $form->setData($keyData);
        $form->setTitles($keyTitle);
        $form->setTableWidth("800px");
	$form->setDatabase($postKeys);
	$form->setUpdateValue("savePaths") ;
	$form->setUpdateText("Save Program Paths") ;
        $content .= $form->editForm();
	print $content;
}

function displayPlugins() {
	global $tool, $propertyForm;

    if($_GET['mode'] == refreshPlugins)
    {
        $dir = "plugins/";
        $status = checkDirForPlugins($dir);
        if ($status != true)
        {
            $propertyForm->warning("Failed to refresh. Reason: ". $status) ;
        }
    }

	$plugins = Plugins::get_plugins();
	
	echo "<style>";
	foreach ($plugins as $id => $value)
	{
		echo "#modalBox #dialog".$id;
		echo "{
			width:auto;
			max-width: 80%;
			min-width:40%;
			height:auto;
			padding:10px;
			padding-top:10px;
			overflow:auto;
		}";
	}
	echo "</style>";
	
	echo "</div><form method='post' action='' style='width:1024px;'>";
	echo "<table id=\"sortDataTable\" class='sortable' cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:100%; clear:left;'>
		<thead>
		<tr><th style='text-align:left;'>Plugins</th>
			<th style='text-align:left;'>Description</th>
			<th style='text-align:left;'>Version</th>
			<th style='text-align:left;'>Poller Script</th>
			<th style='text-align:left;'>Poller Interval</th>
			<th style='text-align:left;'>Location</th>
			<th style='text-align:left;'>Action</th></tr>
			
		</thead>
		<tbody>";
	
	foreach ($plugins as $id => $value)
	{
		echo "<tr>";
		
		$curPlugin = new Plugins($id);
		if (file_exists($curPlugin->get_conf_path())) {
		// If this passes the plugin still exists
			$fileExists = fopen($curPlugin->get_filename(), 'r');
			if($curPlugin->get_enabled())
			{
				echo "<td><input type='checkbox' checked name='list[]' value='".$curPlugin->get_id()."' />".$curPlugin->get_name()."</td>";
			}
			else {
				echo "<td><input type='checkbox' name='list[]' value='".$curPlugin->get_id()."' />".$curPlugin->get_name()."</td>";
			}
			echo "<td>".$curPlugin->get_description()."</td>";
			echo "<td>".$curPlugin->get_version()."</td>";
			$poller_string = "N/A";
			$poller_interval_string = "";
			if ($curPlugin->get_poller() ) {
				$poller_string = $curPlugin->get_poller_script();
				$poller_interval_string = $curPlugin->get_poller_interval() ."min";
			}
			echo "<td>$poller_string</td>";
			echo "<td>$poller_interval_string</td>";

			if ($curPlugin->get_location() != '') {
				print "<td>". $curPlugin->get_location() ."</td>";
			} else {
				print "<td> <a class='tooltip' title='This should only be the case for poller plugins.<br> 
					All other plugins should have content.<br> Location is defined in the config.xml file<br> 
					Example: &lt; location&gt;statistics&lt;&#8260;location&gt;'><img src='icons/Info.png' height='16' width='16'>
					No Content</a></td>";
			}
			//print "file is -" . $curPlugin->get_filename() ."-<br>";
			if ($fileExists) {
				if (include_once $curPlugin->get_filename()) {
					$className = $curPlugin->get_class_name();
					if (($className) && ($className != '')) {
						$pluginClass = new $className();
				
						if(method_exists($pluginClass, 'get_config')) {
							echo "<td><a name='modal' href='#dialog".$id."'>Configure</a></td>";
						} else {
						echo "<td> </td>";
						}	
					} else {
						echo "<td> </td>";
					}
				}  else {
					echo "<td> </td>";
				}
			}
			echo "</tr>";
		}
		else
		{
			if (!$curPlugin->remove_plugin())
			{
				$propertyForm->warning("You widget does not exist, but we failed to remove it from the database. Reason: ". $curPlugin->get_error());	
			}
		}
	}

	echo "</tbody>
	</table>";
	echo "<input type='submit' name='savePlugins' value='Enable checked plugins for users' style='float:left; clear:left; margin-bottom:5px;' />";
	echo "<input type='button' name='refresh' value='Refresh Plugins' onclick=\"return LoadPage('configurations.php?action=plugins&mode=refreshPlugins', 'settingsInfo')\" style='float:right; margin-bottom:5px;' />";
	echo "</form>";
	
	foreach ($plugins as $id => $value)
	{
		$curPlugin = new Plugins($id);
		include_once $curPlugin->get_filename();
		
		$className = $curPlugin->get_class_name();
		if (($className) && ($className != '')) {
			$pluginClass = new $className();
		
			if(method_exists($pluginClass, 'get_config')) {
				echo "<div id='modalBox'>
					<div id='dialog".$id."' class='window'>
					<div style='clear:both;'></div>";
				echo "<a href='#'class='close' /><img src='icons/close.png'></a>";
				echo $pluginClass->get_config($id);
				echo "</div>
					<div id='mask'></div>
					</div>";
			}
		}
	}
	
}

function checkDirForWidget($dir)
{
	$process = true;
	$error;
	if (is_dir($dir))
	{
		if ($dh = opendir($dir)) {
			$index =0;
			while (($file = readdir($dh)) !== false) {
				if (!preg_match("/\./", $file))
				{
					//checkDirForWidget($dir.$file."/");
					$filename = $dir.$file."/widget.php";
					
					$configPath = $dir.$file."/config.xml";
					
					$xml = simplexml_load_file($configPath);
					
					$className = $xml->className;
					$name = $xml->name;
					$description = $xml->description;
					$version = $xml->version;
					$title = $xml->title;
					
					
					if (isset($className) && $className != "")
					{
						$allWidgets = Widgets::get_widgets();
						
						$exists = false;
						foreach ($allWidgets as $id => $value)
						{
							$tempWidget = new Widgets($id);
							if ($tempWidget->get_filename() == $filename)
							{
								$exists = true;
								$widget = new Widgets($id);
							}
						}
						
						if (!$exists)
						{$widget = new Widgets();}
						
						$widget->set_name($name);
						$widget->set_description($description);
						$widget->set_filename($filename);
						$widget->set_class_name($className);
						$widget->set_conf_path($configPath);
						$widget->set_version($version);
						
						if (!$exists)
						{
							if($widget->insert_widget())
							{$process = true;}
							else
							{
								$error = $widget->get_error();
								$process = false;
								break;
							}
						}
						else
						{
							if($widget->update_widget())
							{$process = true;}
							else
							{
								$error = $widget->get_error();
								$process = false;
								break;
							}
						}
					}
				}	
				
				if ($index > 9999)
				{break;}
			}
			closedir($dh);
		}
	}
	
	if($process)
	{return true;}
	else
	{return $error;}
}

function checkDirForPlugins($dir)
{
	$process = true;
	$error;
	if (is_dir($dir))
	{
		if ($dh = opendir($dir)) {
			$index =0;
			while (($file = readdir($dh)) !== false) {
				if (!preg_match("/\./", $file))
				{
					//checkDirForWidget($dir.$file."/");
					$filename = $dir.$file."/plugin.php";
					
					$configPath = $dir.$file."/config.xml";
					
					$iconPath = $dir.$file."/icon.png";
					
					if (!file_exists($iconPath))
					{
						$iconPath = $dir.$file."/icon.gif";
					}
					
					if (!file_exists($iconPath))
					{
						$iconPath = "NONE";
					}
					
					$xml = simplexml_load_file($configPath);
					
					$className = $xml->className;
					$name = $xml->name;
					$description = $xml->description;
					$version = $xml->version;
					$title = $xml->title;
					$location = $xml->location;
					$subLocation = $xml->subLocation;
					$plugin_order = $xml->order;
					$plugin_poller = $xml->poller;
					$plugin_poller_script = $xml->pollerScript;
					$plugin_poller_interval = $xml->pollerInterval;
					$plugin = new Plugins();
					
					$exists = false;
					if (isset($name) && $name != "")
					{
						$allPlugins = Plugins::get_plugins();
						
						foreach ($allPlugins as $id => $value)
						{
							$tempPlugin = new Plugins($id);
							if ($tempPlugin->get_filename() == $filename)
							{
								$exists = true;
								$plugin = new Plugins($id);
							}
						}
						if (!$exists) {
							$plugin = new Plugins();
						}
						
						$plugin->set_name($name);
						$plugin->set_description($description);
						$plugin->set_filename($filename);
						$plugin->set_class_name($className);
						$plugin->set_conf_path($configPath);
						$plugin->set_version($version);
						$plugin->set_location($location);
						$plugin->set_sub_location($subLocation);
						$plugin->set_plugin_order($plugin_order);
						$plugin->set_icon_path($iconPath);
					}  else {
						#print "No Class name<br>";
					}
					if ($plugin_poller > 0) {
						$plugin->set_poller("1");
						$plugin->set_poller_script($dir.$file."/".$plugin_poller_script);
						$plugin->set_poller_interval($plugin_poller_interval);
					}
					//echo "<pre>".print_r($plugin);echo "</pre>";
					
						
					if (!$exists) {
						if($plugin->insert_plugin()) {
							$process = true;
						}
						else {
							$error = $plugin->get_error();
							$process = false;
							break;
						}
					} else {
						if($plugin->update_plugin()) {
							#echo "<pre>".print_r($plugin);echo "</pre>";
							$process = true;
						} else {
							$error = $plugin->get_error();
							$process = false;
							break;
						}
					}
				}	
				
				if ($index > 9999)
				{break;}
			}
			closedir($dh);
		}
	}
	
	if($process)
	{return true;
	echo"yes";}
	else
	{return $error;}
}

function updateDashboard()
{
	global $tool, $propertyForm;
	
	$enabledWidgets = $_POST['list'];
	//print_r($enabledWidgets);
	
	$curUser= new DashboardUsers($_SESSION['userid']);
	$widgets = Widgets::get_widgets();
	
	$update = true;
	foreach ($widgets as $id => $value)
	{
		$isEnabled = false;
		$curWidget = new Widgets($id);
		foreach ($enabledWidgets as $eID => $eValue)
		{
			if ($id == $eValue)
			{
				$curWidget->set_enabled(true);
				$isEnabled = true;
			}
		}
		if (!$isEnabled)
		{
			$curWidget->set_enabled(false);
			$curUser->set_widget_id($id);
			$curUser->remove_widget();
		}
		
		if ($curWidget->update_widget())
		{$update = true;}
		else
		{
			$update = false;
			$error =  $curWidget->get_error();
			break;
		}
	}
	
	if($update)
	{
		$status="success";
		echo "<script language='javascript'>LoadPage(\"configurations.php?action=dashWidgets&mode=edit&update=".$status."\", 'settingsInfo');</script>";
	}
	else
	{
		$propertyForm->error("Warning: Failed to enable widgets. Reason: ".$error);
	}
}

function updatePlugins()
{
	global $tool, $propertyForm;

	$enabledPlugins = $_POST['list'];
	$plugins = Plugins::get_plugins();

	$update = true;
	foreach ($plugins as $id => $value)
	{
		$isEnabled = false;
		$curPlugin = new Plugins($id);

		$previously_enabled = $curPlugin->get_enabled();

		foreach ($enabledPlugins as $eID => $eValue)
		{
			if ($id == $eValue)
			{
				if (!$previously_enabled) // Run on_enable function of plugin (if exists), if plugin not previously enabled
				{
					include_once $curPlugin->get_filename();
					$className = $curPlugin->get_class_name();
					$pluginClass = new $className();

					if (method_exists($pluginClass, "on_enable")) {
						$pluginClass->on_enable();
					}
				}
				$curPlugin->set_enabled(true);
				$isEnabled = true;
			}
		}

		if (!$isEnabled)
		{
			$curPlugin->set_enabled(false);
		}

		if ($curPlugin->update_plugin())
		{$update = true;}
		else
		{
			$update = false;
			$error =  $curPlugin->get_error();
			break;
		}
	}

	if($update)
	{
		$status="success";
		echo "<script language='javascript'>LoadPage(\"configurations.php?action=plugins&mode=edit&update=".$status."\", 'settingsInfo');</script>";
	}
	else
	{
		$propertyForm->error("Warning: Failed to enable plugins. Reason: ".$error);
	}
}

function updatePluginConfig()
{
	global $tool, $propertyForm;
	
	if (!isset($_POST['id']))
	{
		echo "The configuration must contain the hidden input type, 'id' with values $id. <input type='hidden' name='id' value=\".$id.\"></input>";
		return false;
	}
	else
	{
		$curPlugin = new Plugins($_POST['id']);
		include_once $curPlugin->get_filename();
		
		$className = $curPlugin->get_class_name();
		$pluginClass = new $className();
		
		$postValues = $_POST;
		$values = array();
		foreach ($postValues as $id => $value)
		{
			$pos = strpos($id, "Plugin_");
			if ($pos === 0)
			{
				$values = $postValues;
			}
		}
		
		if ($pluginClass->update_config($values))
		{
			$status="success";
			echo "<script language='javascript'>LoadPage(\"configurations.php?action=plugins&mode=edit&update=".$status."\", 'settingsInfo');</script>";
			
		}
		else
		{
			$propertyForm->error("Warning: Failed to update plugins. Reason: plugin update is not returning a true value");
		}
	}
}

function addUser()
{
	global $tool, $propertyForm;
	$newUser = new User();
	$tempInfo=array();
	
	$infoKey = array("full", "user", "password", "email");
	foreach($infoKey as $index => $key)
	{
		$tempInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempInfo[user] = addslashes($tempInfo[user]);
	$tempInfo[full] = addslashes($tempInfo[full]);
					
	//checks if the name is empty, if not set all the names and insert them
	if ($newUser->set_user_name($tempInfo[user]))
	{
		//set all the values to the query
		$newUser->set_full_name($tempInfo[full]);
		$newUser->set_password($tempInfo[password]);
		$newUser->set_email($tempInfo[email]);
		$newUser->set_user_type('local');
							
		//if the insert is sucessful reload the page with the new values
		if($newUser->insert())
		{
			$status="success";
			echo "<script language='javascript'>LoadPage(\"configurations.php?action=userManage&mode=edit&add=".$status."\", 'settingsInfo');</script>";
			//echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=userManage&add=$status\">";
		}
		//or else show error
		else {
			$propertyForm->error("Warning: Failed to add user. Reason: ".$newUser->get_error(), $_GET['ID']);
		}
	}
	//if no name, then output error
	else
	{
		$propertyForm->error("Warning: Failed to add user. Reason: ".$newUser->get_error(), $_GET['ID']);
	}
}

function addUserToGroup()
{
	global $tool, $propertyForm;
	$users = $_POST['list'];
	if (isset($_POST['groups'])){$groupID = $_POST['groups'];}
	else if (isset($_POST['groups2'])){$groupID = $_POST['groups2'];}
	$addSuccess;
	
	if(isset($users))
	{
		foreach($users as $id=>$value)
		{
			$curUser = new User($value);
			if($curUser->add_to_group($groupID))
			{
				$addSuccess = true;
			}
			else {
				$addSuccess = false;
				$error = $curUser->get_error();
				break;
			}
		}
	
		if ($addSuccess)
		{
			$status="success";
			if (isset($_POST['groups'])){echo "<script language='javascript'>LoadPage(\"configurations.php?action=userManage&mode=edit&add=".$status."\", 'settingsInfo');</script>";}
		else if (isset($_POST['groups2'])){echo "<script language='javascript'>LoadPage(\"configurations.php?action=groupManage&mode=edit&add=".$status."\", 'settingsInfo');</script>";}
		}
		else {
			$propertyForm->error("Warning: Failed to add user to group ".$groupID.". Reason: ".$error, $_GET['ID']);
		}
	}
	else
	{
		$propertyForm->error("Warning: You did not pick any users. Reason: ".$error, $_GET['ID']);
	}
}


function removeUser()
{
	global $tool, $propertyForm;
	$users = $_POST['list'];
	$delSuccess;
	
	foreach($users as $id=>$value)
	{
		$curUser = new User($value);
		if($curUser->delete())
		{
			$delSuccess = true;
		}
		else {
			$delSuccess = false;
			$error = $curUser->get_error();
			break;
		}
	}
	
	if ($delSuccess)
	{
		$status="success";
		echo "<script language='javascript'>LoadPage(\"configurations.php?action=userManage&mode=edit&delete=".$status."\", 'settingsInfo');</script>";
	}
	else {
		$propertyForm->error("Warning: Failed to remove user. Reason: ".$error, $_GET['ID']);
	}
}

function updateUser()
{
	global $tool, $propertyForm;
	
	$tempInfo=array();
	
	$infoKey = array("id", "full", "user", "email");
	foreach($infoKey as $index => $key)
	{
		$tempInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempInfo[user] = addslashes($tempInfo[user]);
	$tempInfo[full] = addslashes($tempInfo[full]);
	$newUser = new User($tempInfo[id]);
					
	//checks if the name is empty, if not set all the names and insert them
	if ($newUser->set_user_name($tempInfo[user]))
	{
		//set all the values to the query
		$newUser->set_full_name($tempInfo[full]);
		$newUser->set_email($tempInfo[email]);
							
		//if the insert is sucessful reload the page with the new values
		if($newUser->update())
		{
			$status="success";
			echo "<script language='javascript'>LoadPage(\"configurations.php?action=userManage&mode=edit&update=".$status."\", 'settingsInfo');</script>";
			//echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=userManage&add=$status\">";
		}
		//or else show error
		else {
			$propertyForm->error("Warning: Failed to update user. Reason: ".$newUser->get_error(), $_GET['ID']);
		}
	}
	//if no name, then output error
	else
	{
		$propertyForm->error("Warning: Failed to update user. Reason: ".$newUser->get_error(), $_GET['ID']);
	}
}

function addGroup()
{
	global $tool, $propertyForm;
	$newGroup = new Group();
	$tempInfo=array();
	
	$key = array("name", "desc", "access");
	foreach($key as $index => $key)
	{
		$tempInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempInfo[name] = addslashes($tempInfo[name]);
	$tempInfo[desc] = addslashes($tempInfo[desc]);
		
	//checks if the name is empty, if not set all the names and insert them
	if ($newGroup->set_name($tempInfo[name]))
	{
		//set all the values to the query
		$newGroup->set_description($tempInfo[desc]);
		$newGroup->set_access_level($tempInfo[access]);
		$newGroup->set_ldap_group_name($tempInfo[ldap]);
							
		//if the insert is sucessful reload the page with the new values
		if($newGroup->insert())
		{
			$status="success";
			echo "<script language='javascript'>LoadPage(\"configurations.php?action=groupManage&mode=edit&add=".$status."\", 'settingsInfo');</script>";
			//echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=userManage&add=$status\">";
		}
		//or else show error
		else {
			$propertyForm->error("Warning: Failed to add group. Reason: ".$newGroup->get_error(), $_GET['ID']);
		}
	}
	//if no name, then output error
	else
	{
		$propertyForm->error("Warning: Failed to add group. Reason: ".$newGroup->get_error(), $_GET['ID']);
	}
}


function delUserFromGroup()
{
	global $tool, $propertyForm;
	$users = $_POST['userList'];
	$groupID = $_POST['groupID'];
	$delSuccess;
	
	echo $groupID;
	foreach($users as $id=>$value)
	{
		$curUser = new User($value);
		if($curUser->delete_from_group($groupID))
		{
			$delSuccess = true;
		}
		else {
			$delSuccess = false;
			$error = $curUser->get_error();
			break;
		}
	}
	
	if ($delSuccess)
	{
		$status="success";
		echo "<script language='javascript'>LoadPage(\"configurations.php?action=groupManage&mode=edit&delete=".$status."\", 'settingsInfo');</script>";
	}
	else {
		$propertyForm->error("Warning: Failed to delete user from group ".$groupID.". Reason: ".$error, $_GET['ID']);
	}
}

function removeGroup()
{
	global $tool, $propertyForm;
	$groupID = $_POST['groupID'];
	$delSuccess;
	$curGroup = new Group($groupID);
	if($curGroup->delete())
	{
		$delSuccess = true;
	}
	else {
		$delSuccess = false;
		$error = $curGroupr->get_error();
		break;
	}
	
	if ($delSuccess)
	{
		$status="success";
		echo "<script language='javascript'>LoadPage(\"configurations.php?action=groupManage&mode=edit&delete=".$status."\", 'settingsInfo');</script>";
	}
	else {
		$propertyForm->error("Warning: Failed to remove group. Reason: ".$error, $_GET['ID']);
	}
}

function updateGroupKey()
{
	// Function to update the group password, i.e. shared secret for password management
	global $tool, $propertyForm;
	if (! is_numeric($_POST['group_id'])) {
		// Error
		$propertyForm->error("Warning: invalid group_id");
		return;
	}
	$group = new Group($_POST['group_id']);
	$old_pass = $_POST['old_pass'];
	$new_pass1 = $_POST['new_pass1'];
	$new_pass2 = $_POST['new_pass2'];
	if ($old_pass == '') {
		// Old pass is empty
		$propertyForm->error("Warning: Old password field is empty");
		return;
	}
	if ($new_pass1 == '') {
		// new pass is empty
		$propertyForm->error("Warning: New password field is empty");
		return;
	} 
	if ($new_pass1 != $new_pass2) {
		// new passwords do not match 
		$propertyForm->error("Warning: New passwords do not match ");
		return;
	}
	
	if ($group->set_password($new_pass1,$old_pass)) {
		$status="success";
		echo "<script language='javascript'>LoadPage(\"configurations.php?action=groupManage&mode=edit&update=".$status."\", 'settingsInfo');</script>";
	} else {
		$propertyForm->error("Warning: Failed to update group Password Reason: ".$group->get_error());
	}
}
function updateGroup()
{
	global $tool, $propertyForm;
	$newGroup = new Group($_POST['id']);
	$tempInfo=array();
	print_r($_POST);	
	$key = array("name", "desc", "access", "ldap","group_pass");
	foreach($key as $index => $key)
	{
		$tempInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	//add slashes to these 2 to make sure it does not display wrongly
	$tempInfo[name] = addslashes($tempInfo[name]);
	$tempInfo[desc] = addslashes($tempInfo[desc]);
		
	//checks if the name is empty, if not set all the names and insert them
	if ($newGroup->set_name($tempInfo[name]))
	{
		//set all the values to the query
		$newGroup->set_description($tempInfo[desc]);
		$newGroup->set_access_level($tempInfo[access]);
		$newGroup->set_ldap_group_name($tempInfo[ldap]);
		$newGroup->set_group_pass($tempInfo[group_pass]);
							
		//if the insert is sucessful reload the page with the new values
		if($newGroup->update())
		{
			$status="success";
			echo "<script language='javascript'>LoadPage(\"configurations.php?action=groupManage&mode=edit&update=".$status."\", 'settingsInfo');</script>";
			//echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=userManage&add=$status\">";
		}
		//or else show error
		else {
			$propertyForm->error("Warning: Failed to update group. Reason: ".$newGroup->get_error(), $_GET['ID']);
		}
	}
	//if no name, then output error
	else
	{
		$propertyForm->error("Warning: Failed to update group. Reason: ".$newGroup->get_error(), $_GET['ID']);
	}
}


function updateProperties($properties)
{
	//global all variables
	global $keys, $propertyForm;
					
	//create an empty temporary array to store all the new values given from the form
	$tempArray=array();
	foreach($keys as $index => $key)
	{
		$key = str_replace(" ", "_", $key);
		$tempArray[$key] = trim($_POST[$key]);
	}
	
	print_r($tempArray);
	$index = 0;
	foreach ($properties as $id => $value)
	{
		$thisProperty = new Property;
		//error check
		$thisProperty->set_property($id, $tempArray[$keys[$index]]);
		$index++;
	}
	$status="success";
	echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?update=".$status."\">";
}

?>

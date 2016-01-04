<?php

include_once("sessionCheck.php");
if(!isset($_GET['user']))
{
include_once("controlBar.php")?>
<div id="main">
<h1 id="mainTitle">LOCATIONS
<?
include_once "classes/PopLocations.php";

if(isset($_GET['locationID']))
{
	echo "<div style='font-size:10px; font-weight:100px;'>";
	$link = $_SERVER['PHP_SELF'];
	echo "<a href='".$link."'>All Locations</a>";
	
	$location = new Location($_GET['locationID']);
	$locationName = $location->get_name();
	$link = $_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID'];
	
	if(isset($_GET['roomID']))
	{
		echo " >> <a href='".$link."'>".$locationName."</a>";
		
		$room = new Room($_GET['roomID']);
		
		echo " >> ".$room->get_name();
	}
	else
	{
		echo " >> ".$locationName;
	}
	echo "</div>";
}

if($_GET['action'] == 'showLocationTypes' || $_GET['action'] == 'showLocationType')
{
	echo "<div style='font-size:10px; font-weight:100px;'>";
	$link = $_SERVER['PHP_SELF'];
	echo "<a href='".$link."'>All Locations</a>";
	
	if(isset($_GET['locationTypeID']))
	{
		$link = $_SERVER['PHP_SELF']."?action=showLocationTypes";
		echo " >> <a href='".$link."'> All Location Types </a>";
		
		$locationType = new LocationType($_GET['locationTypeID']);
		
		echo " >> ".$locationType->get_name();
	}
	else
	{
		echo " >> All Location Types";
	}
	echo "</div>";
}

if($_GET['action'] == 'showRoomTypes' || $_GET['action'] == 'showRoomType')
{
	echo "<div style='font-size:10px; font-weight:100px;'>";
	$link = $_SERVER['PHP_SELF'];
	echo "<a href='".$link."'>All Locations</a>";
	
	if(isset($_GET['roomTypeID']))
	{
		$link = $_SERVER['PHP_SELF']."?action=showRoomTypes";
		echo " >> <a href='".$link."'> All Room Types </a>";
		
		$roomType = new RoomType($_GET['roomTypeID']);
		
		echo " >> ".$roomType->get_name();
	}
	else
	{
		echo " >> All Room Types";
	}
	echo "</div>";
}
?>
</h1>

<?
}
include_once "classes/PopLocations.php";
include_once "classes/Contact.php";
/*Database coding: this checks for multiple different actions made by users and responds accordingly.*/
include_once 'classes/LocationForm.php';
include_once 'classes/EdittingTools.php';
		
//Make a new room, a new tool bar, and a new form
//$rooms = new Room();
$tool = new EdittingTools();
$locationForm = new LocationForm("auto", 2);
		
/*//infoKey generates a set of key names to store the key values
$infoKey = array("name", "wiki_id", "client_location", "notes", "T_room_name", "T_room_email", "T_room_phone", "S_room_name", "S_room_email", "S_room_phone", "E_room_name", "E_room_email", "E_room_phone", "B_room_name", "B_room_email", "B_room_phone");
				
//heading is the array of headlines in the table
$headings = array("Location Details", 
				  "*<break>*", "Primary Technical Room", 
				  "*<break>*", "Primary Emergency Room", 
				  "*<break>*", "Primary Service Room", 
				  "*<break>*", "Primary Billing Room");
							
//titles are the subcategories for each headline. In the array "heading" means make a room there for the headline
$titles = array("Location Name", "Location ID", "Location Location", "Notes", 
				"*<break>*", 
				"Name", "Email", "Phone Number", 
				"*<break>*", 
				"Name", "Email", "Phone Number", 
				"*<break>*", 
				"Name", "Email", "Phone Number", 
				"*<break>*", 
				"Name", "Email", "Phone Number");*/
				
//if the user is editting or viewing and ID, check if there is an update being made. If there are no updates then show them the client information form.

switch (success)
{
	case $_GET['update']:
	$locationForm->success("Updated successfully");
	break;
	
	case $_GET['add']:
	$locationForm->success("Added new item successfully");
	break;
	
	case $_GET['delete']:
	$locationForm->success("Deleted item successfully");
	break;
}

if(($_GET['action'] == editLocation && $_SESSION['access'] >= 50) || $_GET['action'] == showLocation)
{
	//get the new location type corresponding to the ID
	$location = new Location($_GET['locationID']);
	
	//if this is an update then update the room
	if(isset($_POST['updateLocation']))
	{
		updateLocation($location);
	}
	//or else display the room information
	else
	{
		displayLocations($location);
	}					
}

else if(($_GET['action'] == editLocationType && $_SESSION['access'] >= 50) || $_GET['action'] == showLocationType)
{
	//get the new location type corresponding to the ID
	$locationType = new LocationType($_GET['locationTypeID']);
	
	//if this is an update then update the room
	if(isset($_POST['updateLocationType']))
	{
		updateLocationType($locationType);
	}
	//or else display the room information
	else
	{
		displayLocationType($locationType);
	}
}

else if(($_GET['action'] == editRoomType && $_SESSION['access'] >= 50) || $_GET['action'] == showRoomType)
{
	//get the new location type corresponding to the ID
	$roomType = new RoomType($_GET['roomTypeID']);
	
	//if this is an update then update the room
	if(isset($_POST['updateRoomType']))
	{
		updateRoomType($roomType);
	}
	//or else display the room information
	else
	{
		displayRoomType($roomType);
	}
}

else if(($_GET['action'] == editRooms && $_SESSION['access'] >= 50) || $_GET['action'] == showRooms)
{
	//get the new location type corresponding to the ID
	$room = new Room($_GET['roomID']);
	
	//if this is an update then update the room
	if(isset($_POST['updateRoom']))
	{
		updateRoom($room);
	}
	/*else if(isset($_POST['updateRoomType']))
	{
		$roomType = new RoomType($_GET['roomTypeID']);
		updateRoomType($roomType);
	}*/
	//or else display the room information
	else
	{
		displayRooms($room);
	}					
}

//if the user prompts to add a new client
else if ($_GET['action'] == addLocationType && $_SESSION['access'] >= 50)
{	
	$locationType = new LocationType();
	//if the user is adding the room, then add it
	if(isset($_POST['addLocationType']))
	{
		addLocationType($locationType);						
	}					
	//or else display the form for the adding a new client
	else {
		addLocationTypeForm();								
	}
}
//if the user prompts to add a new client
else if ($_GET['action'] == addLocation && $_SESSION['access'] >= 50)
{	
	$location = new Location();
	//if the user is adding the room, then add it
	if(isset($_POST['addLocation']))
	{
		addLocation($location);						
	}					
	//or else display the form for the adding a new client
	else {
		addLocationForm();								
	}
}
//if the user prompts to add a new client
else if ($_GET['action'] == addRoomType && $_SESSION['access'] >= 50)
{	
	//if the user is adding the room, then add it
	if(isset($_POST['addRoomType']))
	{
		$roomType = new RoomType();
		addRoomType($roomType);						
	}	
	/*else if(isset($_POST['addRoomTypeExist']))
	{
		addRoomTypeExist();
	}*/
	//or else display the form for the adding a new client
	else {
		addRoomTypeForm();								
	}
}
//if the user prompts to add a new client
else if ($_GET['action'] == addRoom && $_SESSION['access'] >= 50)
{	
	$room = new Room();
	//if the user is adding the room, then add it
	if(isset($_POST['addRoom']))
	{
		addRoom($room);						
	}
	//or else display the form for the adding a new client
	else {
		addRoomForm();								
	}
}

//if the user prompts to remove a client
else if($_GET['action'] == removeRoomType && $_SESSION['access'] >= 50)
{
	//get the client ID and remove that
	$roomType = new RoomType($_GET['roomTypeID']);
	removeRoomType($roomType);
}
				
//if the user prompts to remove a client
else if($_GET['action'] == removeRoomLocation && $_SESSION['access'] >= 50)
{
	//get the client ID and remove that
	$location = new Location($_GET['locationID']);
	removeRoomLocation($location);
}

//if the user prompts to remove a client
else if($_GET['action'] == removeRoom && $_SESSION['access'] >= 50)
{
	//get the client ID and remove that
	$room = new Room($_GET['roomID']);
	removeRoom($room);
}

//if the user prompts to remove a client
else if($_GET['action'] == removeLocation && $_SESSION['access'] >= 50)
{
	//get the client ID and remove that
	$location = new Location($_GET['locationID']);
	removeLocation($location);
}

//if the user prompts to remove a client
else if($_GET['action'] == removeLocationType && $_SESSION['access'] >= 50)
{
	//get the client ID and remove that
	$locationType = new LocationType($_GET['locationTypeID']);
	removeLocationType($locationType);
}
else if($_GET['action'] == 'showRoomTypes')
{
	displayAllRoomTypes();
}
else if($_GET['action'] == 'showLocationTypes')
{
	displayAllLocationTypes();
}
//if the user prompts to show archived clients
else if($_GET['action'] == 'showArchivedLocation')
{
	displayAllArchived();
}
//if nothing else, display all the clients for the user to see
else
{
	displayAll();
}

if(!isset($_GET['user']))
{
?>
</div>        
<?php 
include("footer.php");
}
?>


<?
/*****************************************************FUNCTIONS************************************************/

//This function displays all the location types
function displayAll()
{
	//global the tool and make a tool bar for adding a client
	global $tool, $locationForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Location", "All Archived Locations", "Location Types", "Room Types");
		$toolIcons = array("add", "location", "industry", "room");
		$toolHandlers = array("handleEvent('location.php?action=addLocation')", "handleEvent('location.php?action=showArchivedLocation')", "handleEvent('location.php?action=showLocationTypes')", "handleEvent('location.php?action=showRoomTypes')");
	}
	else {
		$toolNames = array("All Archived Locations", "Location Types", "Room Types");
		$toolIcons = array("location", "industry", "room");
		$toolHandlers = array("handleEvent('location.php?action=showArchivedLocation')", "handleEvent('location.php?action=showLocationTypes')", "handleEvent('location.php?action=showRoomTypes')");
	}
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	echo $tool->createNewFilters();
	
	//get all the client and display them all in the 2 sections: "Location Name" and "Location ID".
	//$allLocationTypes = $locationTypes->get_location_types();
	$allLocations = Location::get_locations(); 
	
	$keyHandlers = array();
	$keyTitle = array();
	$keyData = array();
	
	if(isset($allLocations))
	{
		foreach ($allLocations as $id => $value)
		{
			$curLocation = new Location($id);
			
			array_push($keyHandlers, "handleEvent('location.php?action=showLocation&locationID=$id')");
			//array_push($keyTitle, $curLocation->get_name());
			$locationType = new LocationType($curLocation->get_location_type());
			array_push($keyData, $curLocation->get_name(), $locationType->get_name(), $curLocation->get_country(), $curLocation->get_province(), $curLocation->get_city());
			//$curLocationType = new LocationType($id);
			//array_push($keyData, $curLocationType->get_desc());
		}
	}
	else {
		$locationForm->warning("There are NO location types available");	
	}
	
	$headings = array("Locations", "Location Type", "Country", "Province", "City");
	
	$locationForm->setCols(5);
	$locationForm->setTableWidth("1024px");
	//$locationForm->setTitles($keyTitle);
	$locationForm->setData($keyData);
	$locationForm->setEventHandler($keyHandlers);
	$locationForm->setHeadings($headings);
	$locationForm->setSortable(true);
	
	echo $locationForm->showAll();
	
}

//This function displays all the rooms
function displayAllArchived($locationTypes)
{
	//global the tool and make a tool bar for adding a client
	global $tool, $locationForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Location", "All Locations", "Location Types", "Room Types");
		$toolIcons = array("add", "location", "industry", "room");
		$toolHandlers = array("handleEvent('location.php?action=addLocation')", "handleEvent('location.php')", "handleEvent('location.php?action=showLocationTypes')", "handleEvent('location.php?action=showRoomTypes')");
		
	}
	else {
		$toolNames = array("All Locations", "Location Types", "Room Types");
		$toolIcons = array("location", "industry", "room");
		$toolHandlers = array("handleEvent('location.php')", "handleEvent('location.php?action=showLocationTypes')", "handleEvent('location.php?action=showRoomTypes')");
	}
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	echo $tool->createNewFilters();
	
	//get all the client and display them all in the 2 sections: "Location Name" and "Location ID".
	//$allLocationTypes = $locationTypes->get_location_types(1);
	$allLocations = Location::get_locations(1); 
	
	$keyHandlers = array();
	$keyTitle = array();
	$keyData = array();
	
	if(isset($allLocations))
	{
		foreach ($allLocations as $id => $value)
		{
			$curLocation = new Location($id);
			
			array_push($keyHandlers, "handleEvent('location.php?action=showLocation&locationID=$id')");
			array_push($keyTitle, $curLocation->get_name());
			$locationType = new LocationType($curLocation->get_location_type());
			array_push($keyData, $locationType->get_name(), $curLocation->get_country(), $curLocation->get_province(), $curLocation->get_city());
			//$curLocationType = new LocationType($id);
			//array_push($keyData, $curLocationType->get_desc());
		}
	}
	else {
		$locationForm->warning("There are NO location types available");	
	}
	
	$headings = array("Locations", "Location Type", "Country", "Province", "City");
	
	$locationForm->setCols(5);
	$locationForm->setTableWidth("1024px");
	$locationForm->setTitles($keyTitle);
	$locationForm->setData($keyData);
	$locationForm->setEventHandler($keyHandlers);
	$locationForm->setHeadings($headings);
	$locationForm->setSortable(true);
	
	echo $locationForm->showAll();
}

//This function displays all the rooms
function displayAllLocationTypes()
{
	//global the tool and make a tool bar for adding a client
	global $tool, $locationForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Location Type");
		$toolIcons = array("add");
		$toolHandlers = array("handleEvent('location.php?action=addLocationType')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	echo $tool->createNewFilters();
	
	//get all the client and display them all in the 2 sections: "Location Name" and "Location ID".
	//$allLocationTypes = $locationTypes->get_location_types(1);
	$allLocationTypes = LocationType::get_location_types(); 
	
	$keyHandlers = array();
	$keyTitle = array();
	$keyData = array();
	
	if(isset($allLocationTypes))
	{
		foreach ($allLocationTypes as $id => $value)
		{
			$curLocationType = new LocationType($id);
			
			array_push($keyHandlers, "handleEvent('location.php?action=showLocationType&locationTypeID=$id')");
			array_push($keyTitle, $curLocationType->get_name());
			array_push($keyData, $curLocationType->get_desc());
			//$curLocationType = new LocationType($id);
			//array_push($keyData, $curLocationType->get_desc());
		}
	}
	else {
		$locationForm->warning("There are NO location types available");	
	}
	
	$headings = array("Location Types", "Description");
	
	$locationForm->setTableWidth("1024px");
	$locationForm->setTitles($keyTitle);
	$locationForm->setData($keyData);
	$locationForm->setEventHandler($keyHandlers);
	$locationForm->setHeadings($headings);
	$locationForm->setSortable(true);
	
	echo $locationForm->showAll();
}

function displayLocationType($locationType)
{
	//global the tool and make a tool bar for adding a client
	global $tool, $locationForm;
	
	//make the tool bar for this page
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Edit Location Type", "Remove Location Type");
		$toolIcons = array("edit", "delete");
		$toolHandlers = array("handleEvent('location.php?action=editLocationType&locationTypeID=".$_GET["locationTypeID"]."')",
							  "handleEvent('location.php?action=removeLocationType&locationTypeID=".$_GET["locationTypeID"]."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	if ($_GET['action'] == editLocationType)
	{
		$locTypeHeader = array("Location Type Info");
		$locTypeTitle = array("Name", "Description");
		$locTypeKey = array("name", "desc");
		$locTypeInfo = array($locationType->get_name(), $locationType->get_desc());
		
		$locationForm->setTableWidth("1024px");
		$locationForm->setTitles($locTypeTitle);
		$locationForm->setData($locTypeInfo);
		$locationForm->setHeadings($locTypeHeader);
		$locationForm->setUpdateValue("updateLocationType");
		$locationForm->setDatabase($locTypeKey);
		
		echo $locationForm->editLocationForm();
	}
	else
	{
		$locTypeHeader = array($locationType->get_name());
		$locTypeTitle = array("Description");
		$locTypeInfo = array($locationType->get_desc());
		
		$locationForm->setTableWidth("1024px");
		$locationForm->setTitles($locTypeTitle);
		$locationForm->setData($locTypeInfo);
		$locationForm->setHeadings($locTypeHeader);
		
		echo $locationForm->showAll();
	}
}

//This function displays all the rooms
function displayAllRoomTypes()
{
	//global the tool and make a tool bar for adding a client
	global $tool, $locationForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Room Type");
		$toolIcons = array("add");
		$toolHandlers = array("handleEvent('location.php?action=addRoomType')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	echo $tool->createNewFilters();
	
	//get all the client and display them all in the 2 sections: "Location Name" and "Location ID".
	//$allLocationTypes = $locationTypes->get_location_types(1);
	$allRoomTypes = RoomType::get_room_types(); 
	
	$keyHandlers = array();
	$keyTitle = array();
	$keyData = array();
	
	if(isset($allRoomTypes))
	{
		foreach ($allRoomTypes as $id => $value)
		{
			$curRoomType = new RoomType($id);
			
			array_push($keyHandlers, "handleEvent('location.php?action=showRoomType&roomTypeID=$id')");
			array_push($keyTitle, $curRoomType->get_name());
			array_push($keyData, $curRoomType->get_desc());
			//$curLocationType = new LocationType($id);
			//array_push($keyData, $curLocationType->get_desc());
		}
	}
	else {
		$locationForm->warning("There are NO location types available");	
	}
	
	$headings = array("Room Types", "Description");
	
	$locationForm->setTableWidth("1024px");
	$locationForm->setTitles($keyTitle);
	$locationForm->setData($keyData);
	$locationForm->setEventHandler($keyHandlers);
	$locationForm->setHeadings($headings);
	$locationForm->setSortable(true);
	
	echo $locationForm->showAll();
}

function displayRoomType($roomType)
{
	//global the tool and make a tool bar for adding a client
	global $tool, $locationForm;
	
	//make the tool bar for this page
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Edit Room Type", "Remove Room Type");
		$toolIcons = array("edit", "delete");
		$toolHandlers = array("handleEvent('location.php?action=editRoomType&roomTypeID=".$_GET["roomTypeID"]."')",
							  "handleEvent('location.php?action=removeRoomType&roomTypeID=".$_GET["roomTypeID"]."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	if ($_GET['action'] == editRoomType)
	{
		$roomTypeHeader = array("Room Type Info");
		$roomTypeTitle = array("Name", "Description");
		$roomTypeKey = array("name", "desc");
		$roomTypeInfo = array($roomType->get_name(), $roomType->get_desc());
		
		$locationForm->setTableWidth("1024px");
		$locationForm->setTitles($roomTypeTitle);
		$locationForm->setData($roomTypeInfo);
		$locationForm->setHeadings($roomTypeHeader);
		$locationForm->setUpdateValue("updateRoomType");
		$locationForm->setDatabase($roomTypeKey);
		
		echo $locationForm->editLocationForm();
	}
	else
	{
		$roomTypeHeader = array($roomType->get_name());
		$roomTypeTitle = array("Description");
		$roomTypeInfo = array($roomType->get_desc());
		
		$locationForm->setTableWidth("1024px");
		$locationForm->setTitles($roomTypeTitle);
		$locationForm->setData($roomTypeInfo);
		$locationForm->setHeadings($roomTypeHeader);
		
		echo $locationForm->showAll();
	}
}

//display the current room information
function displayLocations($location)
{
	//global all variables
	global $locationForm, $tool;
		
	//make the tool bar for this page
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Edit Location", "Add New Room", "Remove Location");
		$toolIcons = array("edit", "add", "delete");
		$toolHandlers = array("handleEvent('location.php?action=editLocation&locationID=".$_GET["locationID"]."')",
							  "handleEvent('location.php?action=addRoom&locationID=".$_GET["locationID"]."')",
							  "handleEvent('location.php?action=removeLocation&locationID=".$_GET["locationID"]."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	$contactGroup = $location->get_contact_group_id();
	$thisGroup = new Contact($contactGroup);
	$groupName = $thisGroup->get_name();
	$groupTypeName = $thisGroup->get_group_type_name();
	if (isset($groupName) && isset($groupTypeName))
	{
		$groupLoc = "<a href='contacts.php?action=showGroup&groupID=".$contactGroup."'>".$groupName."</a>";
	}
	else
	{
		$groupLoc = "N/A";
	}
	
	$locationType = new LocationType($location->get_location_type());
	
	$locationHeader = array($location->get_name());
	$locationTitle = array("Description", "Country", "Province", "City", "Address 1", "Address 2", "Zip Code", "Contacts", "Notes", "Location Type");
	$locationInfo = array($location->get_desc(), $location->get_country(), $location->get_province(), $location->get_city(), $location->get_addr_line1(), $location->get_addr_line2(), $location->get_zip_code(), $groupLoc, $location->get_notes(), $locationType->get_name());
	
	$allRooms = $location->get_rooms();
	
	$roomTypes = array();
	
	foreach ($allRooms as $id => $value)
	{
		$curRoom = new Room($id);
		if (!in_array($curRoom->get_room_type(), $roomTypes) && $_GET['locationID'] == $curRoom->get_location_id())
		{
			array_push($roomTypes, $curRoom->get_room_type());	
		}
	}
			
	//if the user is editting this information, make it all editable
	if ($_GET['action'] == editLocation)
	{
		$locationInfoKey = array("name", "desc", "country", "province", "city", "address1", "address2", "zip", "group", "notes", "type");
		$fieldType = array(8=>"drop_down", 9=>"text_area", 10=>"drop_down");
		$locationHeader= array("Location Information");
		
		$locationTitle = array("Name", "Description", "Country", "Province", "City", "Address 1", "Address 2", "Zip Code", "Contact", "Notes", "Location Type");
		$locationInfo = array($location->get_name(), $location->get_desc(), $location->get_country(), $location->get_province(), $location->get_city(), $location->get_addr_line1(), $location->get_addr_line2(), $location->get_zip_code(), $thisGroup->get_name(), $location->get_notes(), $locationType->get_name());
		
		$allLocationType = LocationType::get_location_types();
		$allGroups = Contact::get_groups();
		
		$locationForm->setHeadings($locationHeader);
		$locationForm->setTitles($locationTitle);
		$locationForm->setData($locationInfo);
		$locationForm->setSortable(false);
		$locationForm->setDatabase($locationInfoKey);
		$locationForm->setUpdateValue("updateLocation");
		$locationForm->setType($allGroups);
		$locationForm->setType($allLocationType);
		$locationForm->setFieldType($fieldType);
		
		echo $locationForm->editLocationForm();
	}
					
	//if the user is showing the informating make it all uneditable
	else if($_GET['action'] == showLocation)
	{
		$locationForm->setTableWidth("1024px");
		$locationForm->setTitles($locationTitle);
		$locationForm->setData($locationInfo);
		$locationForm->setHeadings($locationHeader);
		$locationForm->setSortable(false);
		echo $locationForm->showAll();
		
		echo "<div style='clear:left; width:1024px;'>";
		foreach ($roomTypes as $id => $value)
		{
			$curRoomType = new RoomType($value);
			
			$roomHeader=array($curRoomType->get_name());
			
			$roomTitle = array();
			$roomInfo = array();
			$roomHandler = array();
			
			foreach ($allRooms as $cID => $cValue)
			{
				$curRoom = new Room($cID);
				if($curRoom->get_room_type() == $value)
				{
					array_push($roomTitle, $curRoom->get_name());
					array_push($roomInfo, $curRoom->get_desc(), "<a href='location.php?action=removeRoom&locationID=".$_GET['locationID']."&roomTypeID=".$value."&roomID=".$cID."'>Remove</a>");
					array_push($roomHandler, "handleEvent('location.php?action=showRooms&locationID=".$_GET['locationID']."&roomTypeID=".$value."&roomID=".$cID."')");
				}
			}
			
			//make the tool bar for this page
			/*if ($_SESSION['access'] >= 50)
			{
				$toolNames = array("Add New Room", "Edit Room Type", "Remove Room Type");
				$toolIcons = array("add", "edit", "delete");
				$toolHandlers = array("handleEvent('location.php?action=addRoom&locationID=".$_GET['locationID']."&roomTypeID=".$value."')",
									"handleEvent('location.php?action=editRoomType&locationID=".$_GET['locationID']."&roomTypeID=".$value."')",
									"handleEvent('location.php?action=removeRoomType&locationID=".$_GET['locationID']."&roomTypeID=".$value."')",);
				echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
			}*/
	
			$locationForm->setTableWidth("300px");
			$locationForm->setCols(3);
			$locationForm->setTitles($roomTitle);
			$locationForm->setData($roomInfo);
			$locationForm->setEventHandler($roomHandler);
			$locationForm->setHeadings($roomHeader);
			$locationForm->setSortable(true);
			$locationForm->setFirst(false);
			echo $locationForm->showAll();
			
		}
		echo "</div>";
	}
}

//display the current room information
function addLocationForm()
{
	//global all variables
	global $locationForm, $tool;
	
	$locationInfoKey = array("name", "desc", "country", "province", "city", "address1", "address2", "zip", "group", "notes", "type");
	$fieldType = array(8=>"drop_down", 9=>"text_area", 10=>"drop_down");
	$locationHeader= array("Location Information");
		
	$locationTitle = array("Name", "Description", "Country", "Province", "City", "Address 1", "Address 2", "Zip Code", "Contact", "Notes", "Location Type");
		
	$allLocationType = LocationType::get_location_types();
	$allGroups = Contact::get_groups();
		
	$locationForm->setHeadings($locationHeader);
	$locationForm->setTitles($locationTitle);
	$locationForm->setData($locationInfo);
	$locationForm->setSortable(false);
	$locationForm->setDatabase($locationInfoKey);
	$locationForm->setAddValue("addLocation");
	$locationForm->setType($allGroups);
	$locationForm->setType($allLocationType);
	$locationForm->setFieldType($fieldType);
	
	echo $locationForm->newLocationForm();
}

//display the current room information
function displayRooms($curRoom)
{
	//global all variables
	global $locationForm, $tool;
		
	//make the tool bar for this page
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array( "Edit Room", "Remove Room");
		$toolIcons = array("edit", "delete", "delete");
		$toolHandlers = array("handleEvent('location.php?action=editRooms&roomID=".$_GET['roomID']."&locationID=".$_GET['locationID']."&roomTypeID=".$_GET['roomTypeID']."')",
							  "handleEvent('location.php?action=removeRoom&roomID=".$_GET['roomID']."&locationID=".$_GET['locationID']."&roomTypeID=".$_GET['roomTypeID']."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	$roomHeader = array("Room Information");
	$roomTitle = array("Name", "Description", "Notes", "Room Number", "Location");
	$fieldType = array(2=>"text_are");
	$roomInfo = array($curRoom->get_name(), $curRoom->get_desc(), $curRoom->get_notes(), $curRoom->get_room_no(), $curRoom->get_location_name());
	
	if ($_GET['action'] == editRooms)
	{
		//THE CLIENT INFO
		$roomKey = array("name", "desc", "notes", "num");
		$fieldType = array(2=>"text_area",4=>"static");
		
		$roomHeader = array("Room Info");
		
		$locationForm->setTitles($roomTitle);
		$locationForm->setData($roomInfo);
		$locationForm->setHeadings($roomHeader);
		$locationForm->setDatabase($roomKey);
		$locationForm->setUpdateValue("updateRoom");
		$locationForm->setFieldType($fieldType);
		echo $locationForm->editLocationForm();
	}
	
	else if($_GET['action'] == editRoomType)
	{
		$roomType = new RoomType($_GET['roomTypeID']);
		
		$roomTypeHeader = array("Room Type Info");
		$roomTypeKey = array("name", "desc");
		$roomTypeTitle = array("Name", "Description");
		$roomTypeInfo = array($roomType->get_name(), $roomType->get_desc());
		
		$locationForm->setTitles($roomTypeTitle);
		$locationForm->setData($roomTypeInfo);
		$locationForm->setHeadings($roomTypeHeader);
		$locationForm->setDatabase($roomTypeKey);
		$locationForm->setUpdateValue("updateRoomType");
		echo $locationForm->editLocationForm();
	}
	//if the user is showing the informating make it all uneditable
	else if($_GET['action'] == showRooms)
	{
		//THE CLIENT INFO
		$locationForm->setTableWidth("1024px");
		$locationForm->setTitleWidth("50px");
		$locationForm->setTitles($roomTitle);
		$locationForm->setData($roomInfo);
		$locationForm->setHeadings($roomHeader);
		$locationForm->setSortable(false);
		$locationForm->setFirst(true);
		echo $locationForm->showInfoForm();
		
	}
}

//Updating the room. This is where the rooms stores updated values
function updateLocation($location)
{
	//global all variables
	global $locationForm;
	
	$locationInfoKey = array("name", "desc", "country", "province", "city", "address1", "address2", "zip", "group", "notes", "type");
	//create an empty temporary array to store all the new values given from the form
	$tempLocationInfo=array();
	foreach($locationInfoKey as $index => $key)
	{
		$tempLocationInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	addslashes($tempLocationInfo['notes']);
	
	echo $tempLocationInfo["group"];
	//makes sure the room has a name
	if ($location->set_name($tempLocationInfo["name"]))
	{
		$location->set_desc($tempLocationInfo["desc"]);
		$location->set_country($tempLocationInfo["country"]);
		$location->set_province($tempLocationInfo["province"]);
		$location->set_city($tempLocationInfo["city"]);
		$location->set_addr_line1($tempLocationInfo["address1"]);
		$location->set_addr_line2($tempLocationInfo["address2"]);
		$location->set_zip_code($tempLocationInfo["zip"]);
		$location->set_contact_group_id($tempLocationInfo["group"]);
		$location->set_notes($tempLocationInfo["notes"]);
		$location->set_location_type($tempLocationInfo["type"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($location->update())
		{
			$status="success";
			$_SESSION['action'] = "Updated Location: ".$tempLocationInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID']."&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID'];
			$locationForm->error("Warning: Failed to update. Reason: ".$location->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{
		$link = $_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID'];
		$locationForm->error("Warning: Failed to update. Reason: ".$location->get_error(), $link);
	}
}

//Updating the room. This is where the rooms stores updated values
function addLocation($location)
{
	//global all variables
	global $locationForm;
	
	$locationInfoKey = array("name", "desc", "country", "province", "city", "address1", "address2", "zip", "group", "notes", "type");
	//create an empty temporary array to store all the new values given from the form
	$tempLocationInfo=array();
	foreach($locationInfoKey as $index => $key)
	{
		$tempLocationInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	addslashes($tempLocationInfo['notes']);
					
	//makes sure the room has a name
	if ($location->set_name($tempLocationInfo["name"]))
	{
		$location->set_desc($tempLocationInfo["desc"]);
		$location->set_country($tempLocationInfo["country"]);
		$location->set_province($tempLocationInfo["province"]);
		$location->set_city($tempLocationInfo["city"]);
		$location->set_addr_line1($tempLocationInfo["address1"]);
		$location->set_addr_line2($tempLocationInfo["address2"]);
		$location->set_zip_code($tempLocationInfo["zip"]);
		$location->set_contact_group_id($tempLocationInfo["group"]);
		$location->set_notes($tempLocationInfo["notes"]);
		$location->set_location_type($tempLocationInfo["type"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($location->insert())
		{
			$status="success";
			$_SESSION['action'] = "Added Location: ".$tempLocationInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$locationForm->error("Warning: Failed to add. Reason: ".$location->get_error());
		}
	}
	//if there are no names then show error
	else
	{								
		$locationForm->error("Warning: Failed to add. Reason: ".$location->get_error());
	}
}

//Updating the room. This is where the rooms stores updated values
function updateLocationType($locationType)
{
	//global all variables
	global $locationForm;
	
	$locationInfoKey = array("name", "desc");
	//create an empty temporary array to store all the new values given from the form
	$tempLocationInfo=array();
	foreach($locationInfoKey as $index => $key)
	{
		$tempLocationInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	
	//makes sure the room has a name
	if ($locationType->set_name($tempLocationInfo["name"]))
	{
		$locationType->set_desc($tempLocationInfo["desc"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($locationType->update())
		{
			$status="success";
			$_SESSION['action'] = "Updated Location Type: ".$tempLocationInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showLocationType&locationTypeID=".$_GET['locationTypeID']."&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showLocationType&locationTypeID=".$_GET['locationTypeID'];
			$locationForm->error("Warning: Failed to update. Reason: ".$locationType->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{
		$link = $_SERVER['PHP_SELF']."?action=showLocationType&locationTypeID=".$_GET['locationTypeID'];
		$locationForm->error("Warning: Failed to update. Reason: ".$locationType->get_error(), $link);
	}
}

function addLocationTypeForm($locationType)
{
	//global all variables
	global $locationForm, $tool;
	
	$locTypeKey = array("name", "desc");
	$locTypeHeader= array("Location Type Information");
	$locTypeTitle = array("Name", "Description");
		
	$locationForm->setHeadings($locTypeHeader);
	$locationForm->setTitles($locTypeTitle);
	$locationForm->setSortable(false);
	$locationForm->setDatabase($locTypeKey);
	$locationForm->setAddValue("addLocationType");
	
	echo $locationForm->newLocationForm();
}

//Updating the room. This is where the rooms stores updated values
function addLocationType($locationType)
{
	//global all variables
	global $locationForm;
	
	$locationInfoKey = array("name", "desc");
	//create an empty temporary array to store all the new values given from the form
	$tempLocationInfo=array();
	foreach($locationInfoKey as $index => $key)
	{
		$tempLocationInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	
	//makes sure the room has a name
	if ($locationType->set_name($tempLocationInfo["name"]))
	{
		$locationType->set_desc($tempLocationInfo["desc"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($locationType->insert())
		{
			$status="success";
			$_SESSION['action'] = "Updated Location Type: ".$tempLocationInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showLocationTypes&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showLocationTypes";
			$locationForm->error("Warning: Failed to update. Reason: ".$locationType->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{								
		$link = $_SERVER['PHP_SELF']."?action=showLocationTypes";
		$locationForm->error("Warning: Failed to update. Reason: ".$locationType->get_error(), $link);
	}
}

//Updating the room. This is where the rooms stores updated values
function updateRoomType($roomType)
{
	//global all variables
	global $locationForm;
	
	$roomTypeInfoKey = array("name", "desc");
	//create an empty temporary array to store all the new values given from the form
	$tempLocationInfo=array();
	foreach($roomTypeInfoKey as $index => $key)
	{
		$tempLocationInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
					
	//makes sure the room has a name
	if ($roomType->set_name($tempLocationInfo["name"]))
	{
		$roomType->set_desc($tempLocationInfo["desc"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($roomType->update())
		{
			$status="success";
			$_SESSION['action'] = "Updated Room Type: ".$tempLocationInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showRoomType&roomTypeID=".$_GET['roomTypeID']."&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showRoomType&roomTypeID=".$_GET['roomTypeID'];
			$locationForm->error("Warning: Failed to update. Reason: ".$roomType->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{	
		$link = $_SERVER['PHP_SELF']."?action=showRoomType&roomTypeID=".$_GET['roomTypeID'];
		$locationForm->error("Warning: Failed to update. Reason: ".$roomType->get_error(), $link);
	}
}

function addRoomTypeForm()
{		
	//global all variables
	global $locationForm;
	
	$roomTypeHeader = array("Room Type Info");
	$roomTypeKey = array("name", "desc");
	$roomTypeTitle = array("Name", "Description");

	$locationForm->setTitles($roomTypeTitle);
	$locationForm->setHeadings($roomTypeHeader);
	$locationForm->setDatabase($roomTypeKey);
	$locationForm->setAddValue("addRoomType");
	echo $locationForm->newLocationForm();
}

//Updating the room. This is where the rooms stores updated values
function addRoomType($roomType)
{
	//global all variables
	global $locationForm;
	
	$roomTypeInfoKey = array("name", "desc");
	//create an empty temporary array to store all the new values given from the form
	$tempLocationInfo=array();
	foreach($roomTypeInfoKey as $index => $key)
	{
		$tempLocationInfo[$key] = addslashes(htmlspecialchars(trim($_POST[$key]),ENT_QUOTES));
	}
					
	//makes sure the room has a name
	if ($roomType->set_name($tempLocationInfo["name"]))
	{
		$roomType->set_desc($tempLocationInfo["desc"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($roomType->insert())
		{
			$status="success";

			$_SESSION['action'] = "Added new Room Type: ".$tempLocationInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showRoomTypes&add=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showRoomTypes";
			$locationForm->error("Warning: Failed to update. Reason: ".$roomType->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{							
		$link = $_SERVER['PHP_SELF']."?action=showLocationTypes";
		$locationForm->error("Warning: Failed to update. Reason: ".$roomType->get_error(), $_GET['ID'], $link);
	}
}

//Updating the room. This is where the rooms stores updated values
function updateRoom($room)
{
	//global all variables
	global $locationForm;
	
	$roomKey = array("name", "desc", "notes", "num", "loc");
	//create an empty temporary array to store all the new values given from the form
	$tempLocationInfo=array();
	foreach($roomKey as $index => $key)
	{
		$tempLocationInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}

	//makes sure the room has a name
	if ($room->set_name($tempLocationInfo["name"]))
	{
		$room->set_desc($tempLocationInfo["desc"]);
		$room->set_notes($tempLocationInfo["notes"]);
		$room->set_room_no($tempLocationInfo["num"]);
		
		//if the update is sucessful go back to show the new updates or else show an error
		if($room->update())
		{
			$status="success";
			$_SESSION['action'] = "Updated Room: ".$tempLocationInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showRooms&locationID=".$_GET['locationID']."&roomID=".$_GET['roomID']."&roomTypeID=".$_GET['roomTypeID']."&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showRooms&roomID=".$_GET['roomID'];
			$locationForm->error("Warning: Failed to update. Reason: ".$room->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{
		$link = $_SERVER['PHP_SELF']."?action=showRooms&roomID=".$_GET['roomID'];
		$locationForm->error("Warning: Failed to update. Reason: ".$room->get_error(), $link);
	}
}

//This function displays the add room form
function addRoomForm()
{
	//global all variables
	global $locationForm, $tool;
	
	$allRoom = Room::get_rooms();
	
	$roomHeader = array("Room Information");
	$roomTitle = array("Name", "Description", "Notes", "Room Number", "Room Type");
		
	//THE CLIENT INFO
	$roomKey = array("name", "desc", "notes", "num", "type");
	$fieldType = array(2=>"text_area", 4=>"drop_down");
		
	$roomHeader = array("New Room Info");
		
	$allRoomTypes = RoomType::get_room_types();
		
	$locationForm->setTitles($roomTitle);
	$locationForm->setHeadings($roomHeader);
	$locationForm->setDatabase($roomKey);
	$locationForm->setAddValue("addRoom");
	$locationForm->setType($allRoomTypes);
	$locationForm->setFieldType($fieldType);
	
	echo $locationForm->newLocationForm();
}

//Updating the room. This is where the rooms stores updated values
function addRoom($room)
{
	//global all variables
	global $locationForm;
	
	$roomKey = array("name", "desc", "notes", "num", "type");
	//create an empty temporary array to store all the new values given from the form
	$tempLocationInfo=array();
	foreach($roomKey as $index => $key)
	{
		$tempLocationInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	
	//makes sure the room has a name
	if ($room->set_name($tempLocationInfo["name"]))
	{
		$room->set_desc($tempLocationInfo["desc"]);
		$room->set_notes($tempLocationInfo["notes"]);
		$room->set_room_no($tempLocationInfo["num"]);
		$room->set_room_type($tempLocationInfo["type"]);
		$room->set_location_id($_GET['locationID']);
		
		//if the update is sucessful go back to show the new updates or else show an error
		if($room->insert())
		{
			$status="success";
			$_SESSION['action'] = "Added New Room: ".$tempLocationInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID']."&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID'];
			$locationForm->error("Warning: Failed to update. Reason: ".$room->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{
		$link = $_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID'];
		$locationForm->error("Warning: Failed to update. Reason: ".$room->get_error(), $link);
	}
}
				
//The function removes a room
function removeRoom($room)
{
	//global the variables
	global $locationForm;
	
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['roomID'])){
			$name = $room->get_name();
			if($room->delete()){
				$status="success";
				$_SESSION['action'] = "Removed: ".$name;
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID']."&delete=$status\">";
			}
			//or else show error
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showRoom&roomID=".$_GET['roomID'];
				$locationForm->error("Warning: Failed to remove customer. Reason: ".$room->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID']."\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$locationForm->prompt("Are you sure you want to delete?");
	}					
}

//The function removes a room
function removeLocation($location)
{
	//global the variables
	global $locationForm;
	
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['locationID'])){
			$name = $location->get_name();
			if($location->delete()){
				$status="success";
				$_SESSION['action'] = "Removed: ".$name;
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?delete=$status\">";
			}
			//or else show error
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID'];
				$locationForm->error("Warning: Failed to remove customer. Reason: ".$location->get_error());
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showLocation&locationID=".$_GET['locationID']."\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$locationForm->prompt("Are you sure you want to delete?");
	}					
}

//The function removes a room
function removeLocationType($locationType)
{
	//global the variables
	global $locationForm;
	
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['locationTypeID'])){
			$name = $locationType->get_name();
			if($locationType->delete()){
				$status="success";
				$_SESSION['action'] = "Removed: ".$name;
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showLocationTypes&delete=$status\">";
			}
			//or else show error
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showLocationType&locationTypeID=".$_GET['locationTypeID'];
				$locationForm->error("Warning: Failed to remove customer. Reason: ".$location->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showLocationTypes&locationTypeID=".$_GET['locationTypeID']."\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$locationForm->prompt("Are you sure you want to delete?");
	}					
}

//The function removes a contact
function removeRoomType($roomType)
{
	//global the variables
	global $locationForm;
	
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['roomTypeID'])){
			$name = $roomType->get_name();
			if($roomType->delete()){
				$status="success";
				$_SESSION['action'] = "Removed: ".$name;
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showRoomTypes&delete=$status\">";
			}
			//or else show error
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showRoomType&roomTypeID=".$_GET['roomTypeID'];
				$locationForm->error("Warning: Failed to remove room Type. Reason: ".$roomType->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showRoomType&roomTypeID=".$_GET['roomTypeID']."\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$locationForm->prompt("Are you sure you want to delete?");
	}					
}
?>

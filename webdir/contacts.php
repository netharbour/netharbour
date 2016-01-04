<?php

include_once("sessionCheck.php");
if(!isset($_GET['user']))
{
include_once("controlBar.php")?>
<div id="main">
<h1 id="mainTitle">CONTACTS
<?
include_once "classes/Contact.php";

if(isset($_GET['groupID']))
{
	echo "<div style='font-size:10px; font-weight:100px;'>";
	$link = $_SERVER['PHP_SELF'];
	echo "<a href='".$link."'>Contacts</a>";
	
	$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
	$group = new Contact($_GET['groupID']);
	$groupName = $group->get_name();
	
	if(isset($_GET['contactID']))
	{
		echo " >> <a href='".$link."'>".$groupName."</a>";
		
		$link = $_SERVER['PHP_SELF']."?action=showContacts&groupID=".$_GET['groupID']."&contactTypeID=".$_GET['contactTypeID']."&contactID=".$_GET['contactID'];
		$contact = new Person($_GET['contactID']);
		$contactName = $contact->get_first_name(). " " . $contact->get_last_name();
	
		echo " >> ".$contactName;
	}
	else
	{
		echo " >> <a href='".$link."'>".$groupName."</a>";
	}
}

if ($_GET['action'] == 'showGroupTypes' || isset($_GET['groupTypeID']) || $_GET['action'] == 'addGroupType')
{
	echo "<div style='font-size:10px; font-weight:100px;'>";
	$link = $_SERVER['PHP_SELF'];
	echo "<a href='".$link."'>Contacts</a>";
	
	$link = $_SERVER['PHP_SELF']."?action=showGroupTypes";
	if(isset($_GET['groupTypeID']))
	{
		echo " >> <a href='".$link."'>Contact Types</a>";
		$groupType = new ContactType($_GET['groupTypeID']);
		
		echo " >> ".$groupType->get_name();
	}
	else
	{
		echo " >> <a href='".$link."'>Contact Types</a>";
	}
}

if ($_GET['action'] == 'showContactTypes' || $_GET['action'] == 'showContactType' || $_GET['action'] == 'addContactType' )
{
	echo "<div style='font-size:10px; font-weight:100px;'>";
	$link = $_SERVER['PHP_SELF'];
	echo "<a href='".$link."'>Contacts</a>";
	
	$link = $_SERVER['PHP_SELF']."?action=showContactTypes";
	if(isset($_GET['contactTypeID']))
	{
		echo " >> <a href='".$link."'>Person Types</a>";
		$contactType = new PersonType($_GET['contactTypeID']);
		
		echo " >> ".$contactType->get_name();
	}
	else
	{
		echo " >> <a href='".$link."'>Person Types</a>";
	}
}

if ($_GET['action'] == 'showPeople' || $_GET['action'] == 'showPerson' || $_GET['action'] == 'editPerson' || $_GET['action']=='addPerson' )
{
	echo "<div style='font-size:10px; font-weight:100px;'>";
	$link = $_SERVER['PHP_SELF'];
	echo "<a href='".$link."'>Contacts</a>";
	
	$link = $_SERVER['PHP_SELF']."?action=showPeople";
	if(isset($_GET['contactID']))
	{
		echo " >> <a href='".$link."'>People</a>";
		$contact = new Person($_GET['contactID']);
		
		echo " >> ".$contact->get_first_name()." ".$contact->get_middle_name()." ".$contact->get_last_name();
	}
	else
	{
		echo " >> <a href='".$link."'>People</a>";
	}
}
?>
</h1>

<?
}
include_once "classes/Contact.php";
/*Database coding: this checks for multiple different actions made by users and responds accordingly.*/
include_once 'classes/ContactForm.php';
include_once 'classes/EdittingTools.php';
		
//Make a new contact, a new tool bar, and a new form
//$contacts = new Contact();
$tool = new EdittingTools();
$contactForm = new ContactForm("auto", 2);
$status;

switch (success)
{
	case $_GET['update']:
	$contactForm->success("Updated successfully");
	break;
	
	case $_GET['add']:
	$contactForm->success("Added new item successfully");
	break;
	
	case $_GET['delete']:
	$contactForm->success("Deleted item successfully");
	break;
}

if(($_GET['action'] == editGroup && $_SESSION['access'] >= 50) || $_GET['action'] == showGroup)
{
	//get the new group type corresponding to the ID
	$group = new Contact($_GET['groupID']);
	
	//if this is an update then update the contact
	if(isset($_POST['updateGroup']))
	{
		updateGroup($group);
	}
	//or else display the contact information
	else
	{
		displayGroups($group);
	}					
}

else if(($_GET['action'] == editContacts && $_SESSION['access'] >= 50) || $_GET['action'] == showContacts)
{
	//get the new group type corresponding to the ID
	$contact = new Person($_GET['contactID']);
	
	//if this is an update then update the contact
	if(isset($_POST['updateContact']))
	{
		updateContact($contact);
	}
	else if(isset($_POST['updateContactType']))
	{
		$contactType = new PersonType($_GET['contactTypeID']);
		updateContactType($contactType);
	}
	//or else display the contact information
	else
	{
		displayContacts($contact);
	}					
}

else if(($_GET['action'] == editPerson && $_SESSION['access'] >= 50) || $_GET['action'] == showPerson)
{
	//get the new group type corresponding to the ID
	$contact = new Person($_GET['contactID']);
	
	//if this is an update then update the contact
	if(isset($_POST['updateContact']))
	{
		updateContact($contact);
	}
	//or else display the contact information
	else
	{
		displayContacts($contact);
	}					
}

else if(($_GET['action'] == editGroupType && $_SESSION['access'] >= 50) || $_GET['action'] == showGroupType)
{
	//get the new group type corresponding to the ID
	$groupType = new ContactType($_GET['groupTypeID']);
	
	//if this is an update then update the contact
	if(isset($_POST['updateGroupType']))
	{
		updateGroupType($groupType);
	}
	//or else display the contact information
	else
	{
		displayGroupType($groupType);
	}					
}

else if(($_GET['action'] == editContactType && $_SESSION['access'] >= 50) || $_GET['action'] == showContactType)
{
	//get the new contact type corresponding to the ID
	$contactType = new PersonType($_GET['contactTypeID']);
	
	//if this is an update then update the contact
	if(isset($_POST['updateContactType']))
	{
		updateContactType($contactType);
	}
	//or else display the contact information
	else
	{
		displayContactType($contactType);
	}					
}

//if the user prompts to add a new contact
else if ($_GET['action'] == addGroupType && $_SESSION['access'] >= 50)
{	
	//if the user is adding the contact, then add it
	if(isset($_POST['addGroupType']))
	{
		$groupType = new ContactType();
		addGroupType($groupType);						
	}					
	//or else display the form for the adding a new contact
	else {
		addGroupTypeForm();								
	}
}
//if the user prompts to add a new contact
else if ($_GET['action'] == addGroup && $_SESSION['access'] >= 50)
{	
	$group = new Contact();
	//if the user is adding the contact, then add it
	if(isset($_POST['addGroup']))
	{
		addGroup($group);						
	}					
	//or else display the form for the adding a new contact
	else {
		addGroupForm();								
	}
}
//if the user prompts to add a new contact
else if ($_GET['action'] == addContactType && $_SESSION['access'] >= 50)
{	
	//if the user is adding the contact, then add it
	if(isset($_POST['addContactType']))
	{
		$contactType = new PersonType();
		addContactType($contactType);						
	}	
	//or else display the form for the adding a new contact
	else {
		addContactTypeForm();								
	}
}
//if the user prompts to add a new contact
else if (($_GET['action'] == addContact && $_SESSION['access'] >= 50) || ($_GET['action'] == addPerson && $_SESSION['access'] >= 50))
{	
	$contact = new Person();
	//if the user is adding the contact, then add it
	if(isset($_POST['addContact']))
	{
		addContact($contact);						
	}
	else if(isset($_POST['addContactExist']))
	{
		addContactExist();
	}
	//or else display the form for the adding a new contact
	else if($_GET['action']==addContact){
		addContactForm();								
	}
	else
	{
		addPersonForm();	
	}
}
	
//if the user prompts to remove a contact
else if($_GET['action'] == removeGroupType && $_SESSION['access'] >= 50)
{
	//get the contact ID and remove that
	$groupType = new ContactType($_GET['groupTypeID']);
	removeGroupType($groupType);
}

//if the user prompts to remove a contact
else if($_GET['action'] == removeContactGroup && $_SESSION['access'] >= 50)
{
	//get the contact ID and remove that
	$group = new Contact($_GET['groupID']);
	removeContactGroup($group);
}

//if the user prompts to remove a contact
else if($_GET['action'] == removeContactType && $_SESSION['access'] >= 50)
{
	//get the contact ID and remove that
	$contactType = new Persontype($_GET['contactTypeID']);
	removeContactType($contactType);
}

//if the user prompts to remove a contact
else if(($_GET['action'] == removeContactDB && $_SESSION['access'] >= 50) || ($_GET['action'] == removePersonDB && $_SESSION['access'] >= 50))
{
	//get the contact ID and remove that
	$contact = new Person($_GET['contactID']);
	removeContactDB($contact);
}

//if the user prompts to remove a contact
else if($_GET['action'] == removeGroup && $_SESSION['access'] >= 50)
{
	//get the contact ID and remove that
	$group = new Contact($_GET['groupID']);
	removeGroup($group);
}
//if the user prompts to show archived contacts
else if($_GET['action'] == 'showGroupTypes')
{
	displayAllGroupTypes();
}

//if the user prompts to show archived contacts
else if($_GET['action'] == 'showContactTypes')
{
	displayAllContactTypes();
}

//if the user prompts to show archived contacts
else if($_GET['action'] == 'showPeople')
{
	displayAllPeople();
}

//if the user prompts to show archived contacts
else if($_GET['action'] == 'showArchivedGroup')
{
	$groupTypes = new ContactType();
	displayAllArchived($groupTypes);
}
				
//if nothing else, display all the contacts for the user to see
else
{
	$groupTypes = new ContactType();
	displayAll($groupTypes);
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

//This function displays all the group types
function displayAll($groupTypes)
{
	//global the tool and make a tool bar for adding a contact
	global $tool, $contactForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Contact", "Archived Contacts", "Contact Types", "Person Types", "All People");
		$toolIcons = array("add", "contact", "client", "person", "people");
		$toolHandlers = array("handleEvent('contacts.php?action=addGroup')", "handleEvent('contacts.php?action=showArchivedGroup')", "handleEvent('contacts.php?action=showGroupTypes')", "handleEvent('contacts.php?action=showContactTypes')", "handleEvent('contacts.php?action=showPeople')");
	}
	else
	{
		$toolNames = array("Archived Contacts", "Contact Types", "Person Types", "All People");
		$toolIcons = array("contact", "client", "person", "people");
		$toolHandlers = array("handleEvent('contacts.php?action=showArchivedGroup')", "handleEvent('contacts.php?action=showGroupTypes')", "handleEvent('contacts.php?action=showContactTypes')", "handleEvent('contacts.php?action=showPeople')");
	}
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	echo $tool->createNewFilters();
	
	//get all the contact and display them all in the 2 sections: "Contact Name" and "Contact ID".
	//$allGroupTypes = $groupTypes->get_group_types();
	$allGroups = Contact::get_groups(); 
	
	$keyHandlers = array();
	$keyTitle = array();
	$keyData = array();
	
	if(isset($allGroups))
	{
		foreach ($allGroups as $id => $value)
		{
			$curGroup = new Contact($id);
			
			array_push($keyHandlers, "handleEvent('contacts.php?action=showGroup&groupID=$id')");
			array_push($keyTitle, $curGroup->get_name());
			array_push($keyData, $curGroup->get_group_type_name());
			//$curGroupType = new GroupType($id);
			//array_push($keyData, $curGroupType->get_desc());
		}
	}
	else {
		$contactForm->warning("There are NO group types available");	
	}
	
	$headings = array("Contact", "Contact Type");
	
	$contactForm->setTableWidth("1024px");
	$contactForm->setTitles($keyTitle);
	$contactForm->setData($keyData);
	$contactForm->setEventHandler($keyHandlers);
	$contactForm->setHeadings($headings);
	$contactForm->setSortable(true);
	
	echo $contactForm->showAll();
	
}

//This function displays all the contacts
function displayAllArchived($groupTypes)
{
	//global the tool and make a tool bar for adding a contact
	global $tool, $contactForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Contacts", "Contacts", "Contact Types", "Person Types");
		$toolIcons = array("add", "contact", "contact", "contact");
		$toolHandlers = array("handleEvent('contacts.php?action=addGroup')", "handleEvent('contacts.php')", "handleEvent('contacts.php?action=showGroupTypes')", "handleEvent('contacts.php?action=showArchivedGroup')");
		
	}
	else
	{
		$toolNames = array("Contacts", "Contact Types", "Person Types");
		$toolIcons = array("contact", "contact", "contact");
		$toolHandlers = array("handleEvent('contacts.php')", "handleEvent('contacts.php?action=showGroupTypes')", "handleEvent('contacts.php?action=showArchivedGroup')");
	}
	echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	
	echo $tool->createNewFilters();
	
	//get all the contact and display them all in the 2 sections: "Contact Name" and "Contact ID".
	//$allGroupTypes = $groupTypes->get_group_types(1);
	$allGroups = Contact::get_groups(1); 
	
	$keyHandlers = array();
	$keyTitle = array();
	$keyData = array();
	
	if(isset($allGroups))
	{
		foreach ($allGroups as $id => $value)
		{
			$curGroup = new Contact($id);
			
			array_push($keyHandlers, "handleEvent('contacts.php?action=showGroup&groupID=$id')");
			array_push($keyTitle, $curGroup->get_name());
			array_push($keyData, $curGroup->get_group_type_name());
			//$curGroupType = new GroupType($id);
			//array_push($keyData, $curGroupType->get_desc());
		}
	}
	else {
		$contactForm->warning("There are NO group types available");	
	}
	
	$headings = array("Contacts", "Contact Type");
	
	$contactForm->setTableWidth("1024px");
	$contactForm->setTitles($keyTitle);
	$contactForm->setData($keyData);
	$contactForm->setEventHandler($keyHandlers);
	$contactForm->setHeadings($headings);
	$contactForm->setSortable(true);
	
	echo $contactForm->showAll();
}

//This function displays all the contacts
function displayAllGroupTypes()
{
	//global the tool and make a tool bar for adding a contact
	global $tool, $contactForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Contact Type");
		$toolIcons = array("add", "contact", "contact", "contact");
		$toolHandlers = array("handleEvent('contacts.php?action=addGroupType')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	echo $tool->createNewFilters();
	
	//get all the contact and display them all in the 2 sections: "Contact Name" and "Contact ID".
	//$allGroupTypes = $groupTypes->get_group_types(1);
	$allGroupType = ContactType::get_group_types(); 
	$keyHandlers = array();
	$keyTitle = array();
	$keyData = array();
	
	if(isset($allGroupType))
	{
		foreach ($allGroupType as $id => $value)
		{
			$curGroupType = new ContactType($id);
			
			array_push($keyHandlers, "handleEvent('contacts.php?action=showGroupType&groupTypeID=$id')");
			array_push($keyTitle, $curGroupType->get_name());
			array_push($keyData, $curGroupType->get_desc());
			//$curGroupType = new GroupType($id);
			//array_push($keyData, $curGroupType->get_desc());
		}
	}
	else {
		$contactForm->warning("There are NO group types available");	
	}
	
	$headings = array("Contact Types");
	
	$contactForm->setTableWidth("1024px");
	$contactForm->setTitles($keyTitle);
	$contactForm->setData($keyData);
	$contactForm->setEventHandler($keyHandlers);
	$contactForm->setHeadings($headings);
	$contactForm->setSortable(true);
	
	echo $contactForm->showAll();
}

//This function displays all the contacts
function displayAllContactTypes()
{
	//global the tool and make a tool bar for adding a contact
	global $tool, $contactForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Person Type");
		$toolIcons = array("add");
		$toolHandlers = array("handleEvent('contacts.php?action=addContactType')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	echo $tool->createNewFilters();
	
	//get all the contact and display them all in the 2 sections: "Contact Name" and "Contact ID".
	//$allGroupTypes = $groupTypes->get_group_types(1);
	$allContactType = PersonType::get_contact_types(); 
	$keyHandlers = array();
	$keyTitle = array();
	$keyData = array();
	
	if(isset($allContactType))
	{
		foreach ($allContactType as $id => $value)
		{
			$curContactType = new PersonType($id);
			
			array_push($keyHandlers, "handleEvent('contacts.php?action=showContactType&contactTypeID=$id')");
			array_push($keyTitle, $curContactType->get_name());
			array_push($keyData, $curContactType->get_desc());
			//$curGroupType = new GroupType($id);
			//array_push($keyData, $curGroupType->get_desc());
		}
	}
	else {
		$contactForm->warning("There are NO group types available");	
	}
	
	$headings = array("Person Types");
	
	$contactForm->setTableWidth("1024px");
	$contactForm->setTitles($keyTitle);
	$contactForm->setData($keyData);
	$contactForm->setEventHandler($keyHandlers);
	$contactForm->setHeadings($headings);
	$contactForm->setSortable(true);
	
	echo $contactForm->showAll();
}

//This function displays all the contacts
function displayAllPeople()
{
	//global the tool and make a tool bar for adding a contact
	global $tool, $contactForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Add New Person");
		$toolIcons = array("add");
		$toolHandlers = array("handleEvent('contacts.php?action=addPerson')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	echo $tool->createNewFilters();
	
	//get all the contact and display them all in the 2 sections: "Contact Name" and "Contact ID".
	//$allGroupTypes = $groupTypes->get_group_types(1);
	$allPeople = Person::get_contacts(); 
	$keyHandlers = array();
	$keyTitle = array();
	$keyData = array();
	
	if(isset($allPeople))
	{
		foreach ($allPeople as $id => $value)
		{
			$curPerson = new Person($id);
			
			array_push($keyHandlers, "handleEvent('contacts.php?action=showPerson&contactID=$id')");
			array_push($keyTitle, $curPerson->get_first_name()." ".$curPerson->get_middle_name()." ".$curPerson->get_last_name());
			array_push($keyData, $curPerson->get_groups());
			
			//$curGroupType = new GroupType($id);
			//array_push($keyData, $curGroupType->get_desc());
		}
	}
	else {
		$contactForm->warning("There are NO group types available");	
	}
	
	$headings = array("People", "Contacts person is in");
	
	$contactForm->setTableWidth("1024px");
	$contactForm->setTitles($keyTitle);
	$contactForm->setData($keyData);
	$contactForm->setEventHandler($keyHandlers);
	$contactForm->setHeadings($headings);
	$contactForm->setSortable(true);
	
	echo $contactForm->showAll();
}

function displayGroupType($groupType)
{
	global $tool, $contactForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Edit Contact Type", "Remove Contact Type");
		$toolIcons = array("edit", "delete");
		$toolHandlers = array("handleEvent('contacts.php?action=editGroupType&groupTypeID=".$_GET["groupTypeID"]."')",
							  "handleEvent('contacts.php?action=removeGroupType&groupTypeID=".$_GET["groupTypeID"]."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	
	if($_GET['action'] == editGroupType)
	{
		$groupTypeKey = array("name", "desc");
		$groupTypeHeader = array("Contact Type Info");
		$groupTypeTitle = array("Name", "Description");
		$groupTypeInfo = array($groupType->get_name(), $groupType->get_desc());
		
		$contactForm->setTableWidth("1024px");
		$contactForm->setTitles($groupTypeTitle);
		$contactForm->setData($groupTypeInfo);
		$contactForm->setDatabase($groupTypeKey);
		$contactForm->setHeadings($groupTypeHeader);
		$contactForm->setUpdateValue("updateGroupType");
		
		echo $contactForm->editContactForm();
	}
	else
	{
		
		$groupTypeHeader = array($groupType->get_name());
		$groupTypeTitle = array("Description");
		$groupTypeInfo = array($groupType->get_desc());
		
		$contactForm->setTableWidth("1024px");
		$contactForm->setTitles($groupTypeTitle);
		$contactForm->setData($groupTypeInfo);
		$contactForm->setHeadings($groupTypeHeader);
		
		echo $contactForm->showAll();
	}
}

//display the current contact information
function addGroupTypeForm()
{
	//global all variables
	global $contactForm, $tool;
	
	$groupTypeKey = array("name", "desc");
	$groupTypeHeader = array("Contact Type Info");
	$groupTypeTitle = array("Name", "Description");
		
	$contactForm->setTableWidth("1024px");
	$contactForm->setTitles($groupTypeTitle);
	$contactForm->setDatabase($groupTypeKey);
	$contactForm->setHeadings($groupTypeHeader);
	$contactForm->setAddValue("addGroupType");
	
	echo $contactForm->newContactForm();
}

function displayContactType($contactType)
{
	global $tool, $contactForm;
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Edit Person Type", "Remove Person Type");
		$toolIcons = array("edit", "delete");
		$toolHandlers = array("handleEvent('contacts.php?action=editContactType&contactTypeID=".$_GET["contactTypeID"]."')",
							  "handleEvent('contacts.php?action=removeContactType&contactTypeID=".$_GET["contactTypeID"]."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	
	if($_GET['action'] == editContactType)
	{
		$contactTypeKey = array("name", "desc");
		$contactTypeHeader = array("Person Type Info");
		$contactTypeTitle = array("Name", "Description");
		$contactTypeInfo = array($contactType->get_name(), $contactType->get_desc());
		
		$contactForm->setTableWidth("1024px");
		$contactForm->setTitles($contactTypeTitle);
		$contactForm->setData($contactTypeInfo);
		$contactForm->setDatabase($contactTypeKey);
		$contactForm->setHeadings($contactTypeHeader);
		$contactForm->setUpdateValue("updateContactType");
		
		echo $contactForm->editContactForm();
	}
	else
	{
		
		$contactTypeHeader = array($contactType->get_name());
		$contactTypeTitle = array("Description");
		$contactTypeInfo = array($contactType->get_desc());
		
		$contactForm->setTableWidth("1024px");
		$contactForm->setTitles($contactTypeTitle);
		$contactForm->setData($contactTypeInfo);
		$contactForm->setHeadings($contactTypeHeader);
		
		echo $contactForm->showAll();
	}
}

//display the current contact information
function addContactTypeForm()
{
	//global all variables
	global $contactForm, $tool;
	
	$contactTypeKey = array("name", "desc");
	$contactTypeHeader = array("Person Type Info");
	$contactTypeTitle = array("Name", "Description");
		
	$contactForm->setTableWidth("1024px");
	$contactForm->setTitles($contactTypeTitle);
	$contactForm->setDatabase($contactTypeKey);
	$contactForm->setHeadings($contactTypeHeader);
	$contactForm->setAddValue("addContactType");
	
	echo $contactForm->newContactForm();
}

//display the current contact information
function displayGroups($group)
{
	//global all variables
	global $contactForm, $tool;
		
	//make the tool bar for this page
	if ($_SESSION['access'] >= 50)
	{
		$toolNames = array("Edit Contact", "Add Person", "Remove Contact");
		$toolIcons = array("edit", "add", "delete");
		$toolHandlers = array("handleEvent('contacts.php?action=editGroup&groupID=".$_GET["groupID"]."')",
							  "handleEvent('contacts.php?action=addContact&groupID=".$_GET["groupID"]."')",
							  "handleEvent('contacts.php?action=removeGroup&groupID=".$_GET["groupID"]."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	$groupHeader = array($group->get_name());
	$groupTitle = array("Description", "Contact Type", "Custom Client ID", "Custom Group ID", "Notes");
	$groupInfo = array($group->get_desc(), $group->get_group_type_name(), $group->get_custom_client_id(), $group->get_custom_group_id(), $group->get_notes());
	
	$allContacts = $group->get_contacts();
	$contactTypes = array();
	
	foreach ($allContacts as $id => $value)
	{
		if (!in_array($value["contact_type_id"], $contactTypes))
		{
			array_push($contactTypes, $value["contact_type_id"]);	
		}
	}
			
	//if the user is editting this information, make it all editable
	if ($_GET['action'] == editGroup)
	{
		$groupInfoKey = array("name", "type", "cusCli", "cusGro", "desc", "notes");
		$groupHeader= array("Contact Information");
		
		$groupTitle = array("Name", "Contact Type", "Custom Client ID", "Custom Group ID", "Description", "Notes");
		$fieldType = array("", "drop_down", "", "", "", "text_area");
		$groupInfo = array($group->get_name(), $group->get_group_type_name(), $group->get_custom_client_id(), $group->get_custom_group_id(), $group->get_desc(), $group->get_notes());
		$allGroupType = ContactType::get_group_types();
		
		$contactForm->setHeadings($groupHeader);
		$contactForm->setTitles($groupTitle);
		$contactForm->setData($groupInfo);
		$contactForm->setSortable(false);
		$contactForm->setDatabase($groupInfoKey);
		$contactForm->setUpdateValue("updateGroup");
		$contactForm->setType($allGroupType);
		$contactForm->setFieldType($fieldType);
		
		echo $contactForm->editContactForm();
	}
					
	//if the user is showing the informating make it all uneditable
	else if($_GET['action'] == showGroup)
	{
		$contactForm->setTableWidth("1024px");
		$contactForm->setTitles($groupTitle);
		$contactForm->setData($groupInfo);
		$contactForm->setHeadings($groupHeader);
		$contactForm->setSortable(false);
		echo $contactForm->showAll();
		
		echo "<div style='clear:left; width:1024px;'>";
		foreach ($contactTypes as $id => $value)
		{
			$curConType = new PersonType($value);
			
			$conHeader=array($curConType->get_name());
			
			$conTitle = array();
			$conInfo = array();
			$conHandler = array();
			
			foreach ($allContacts as $cID => $cValue)
			{
				$curContact = new Person($cValue['contact_id']);
				if($cValue['contact_type_id'] == $value)
				{
					array_push($conTitle, $curContact->get_first_name()." ".$curContact->get_last_name());
					if($_SESSION['access'] >= 50)
					{
						array_push($conInfo, $curContact->get_phone1(), "<a href='contacts.php?action=removeContactGroup&contactID=".$cValue['contact_id']."&groupID=".$_GET['groupID']."&contactTypeID=".$value."'> Remove</a>");
					}
					else
					{
						array_push($conInfo, $curContact->get_phone1(), "No Access");
					}
					array_push($conHandler, "handleEvent('contacts.php?action=showContacts&groupID=".$_GET['groupID']."&contactTypeID=".$value."&contactID=".$cValue['contact_id']."')");
				}
			}
			
			
			//make the tool bar for this page
			/*if ($_SESSION['access'] >= 50)
			{
				$toolNames = array("Add New Contact", "Edit Contact Type", "Remove Contact Type");
				$toolIcons = array("add", "edit", "delete");
				$toolHandlers = array("handleEvent('contacts2.php?action=addContact&groupID=".$_GET['groupID']."&contactTypeID=".$value."')",
									"handleEvent('contacts2.php?action=editContactType&groupID=".$_GET['groupID']."&contactTypeID=".$value."')",
									"handleEvent('contacts2.php?action=removeContactType&groupID=".$_GET['groupID']."&contactTypeID=".$value."')",);
				echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
			}*/
			
			$contactForm->setCols(3);
			$contactForm->setTableWidth("335px");
			$contactForm->setTitleWidth("100px");
			$contactForm->setTitles($conTitle);
			$contactForm->setData($conInfo);
			$contactForm->setEventHandler($conHandler);
			$contactForm->setHeadings($conHeader);
			$contactForm->setSortable(true);
			$contactForm->setFirst(false);
			echo $contactForm->showAll();
		}
		echo "</div>";
	}
}

//display the current contact information
function addGroupForm()
{
	//global all variables
	global $contactForm, $tool;
	
	$groupInfoKey = array("name", "type", "cusCli", "cusGro", "desc", "notes");
	$groupHeader= array("Group Information");
		
	$groupTitle = array("Name", "Contact Type", "Custom Client ID", "Custom Group ID", "Description", "Notes", );
	$fieldType = array("", "drop_down", "", "", "", "text_area");
	$allGroupType = ContactType::get_group_types();
	
	$contactForm->setHeadings($groupHeader);
	$contactForm->setTitles($groupTitle);
	$contactForm->setSortable(false);
	$contactForm->setDatabase($groupInfoKey);
	$contactForm->setAddValue("addGroup");
	$contactForm->setType($allGroupType);
	
	$contactForm->setFieldType($fieldType);
	echo $contactForm->newContactForm();
}

//display the current contact information
function displayContacts($curContact)
{
	//global all variables
	global $contactForm, $tool;
		
	//make the tool bar for this page
	if ($_GET['action'] == 'showContacts' && $_SESSION['access'] >= 50)
	{
		$toolNames = array( "Edit Contact", "Remove Person From Group", "Remove Person From Database");
		$toolIcons = array("edit", "delete", "delete");
		$toolHandlers = array("handleEvent('contacts.php?action=editContacts&groupID=".$_GET['groupID']."&contactID=".$_GET['contactID']."')",
							  "handleEvent('contacts.php?action=removeContactGroup&contactID=".$_GET['contactID']."&groupID=".$_GET['groupID']."&contactTypeID=".$_GET['contactTypeID']."')",
							  "handleEvent('contacts.php?action=removeContactDB&contactID=".$_GET['contactID']."&groupID=".$_GET['groupID']."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	else if($_GET['action'] == 'showPerson' && $_SESSION['access'] >= 50)
	{
		$toolNames = array( "Edit Contact", "Remove Person From Database");
		$toolIcons = array("edit", "delete");
		$toolHandlers = array("handleEvent('contacts.php?action=editPerson&&contactID=".$_GET['contactID']."')",
							  "handleEvent('contacts.php?action=removePersonDB&contactID=".$_GET['contactID']."')");
		echo $tool->createNewTools($toolNames, $toolIcons, $toolHandlers);
	}
	
	$nameHeader = array("Contact Name Info");
	$nameTitle = array("First", "Middle", "Last");
	$nameInfo = array($curContact->get_first_name(), $curContact->get_middle_name(), $curContact->get_last_name());
			
	$placeHeader = array("Contact Location Info");
	$placeTitle = array("Address 1", "Address 2", "City", "Province", "Country", "Postal Code");
	$placeInfo = array($curContact->get_addr_line1(), $curContact->get_addr_line2(), $curContact->get_city(), $curContact->get_province(), $curContact->get_country(), $curContact->get_postal_code());
			
	$comHeader = array("Contact Communication Info");
	$comTitle = array("Phone 1.tip." .$curContact->get_phone1_comment(), "Phone 2.tip." .$curContact->get_phone2_comment(), "Cellphone.tip." .$curContact->get_phone_cell_comment(), "Pager.tip." .$curContact->get_phone_pager_comment(), "Fax", "Email");
	$comInfo = array($curContact->get_phone1() , $curContact->get_phone2(), $curContact->get_phone_cell(), $curContact->get_phone_pager(), $curContact->get_phone_fax(), $curContact->get_email());
			
	//For the editting part
	$comAllInfo = array($curContact->get_phone1(), $curContact->get_phone1_comment(), $curContact->get_phone2(), $curContact->get_phone2_comment(), $curContact->get_phone_cell(), $curContact->get_phone_cell_comment(), $curContact->get_phone_pager(), $curContact->get_phone_pager_comment(), $curContact->get_phone_fax(), $curContact->get_email());
	
	$contactGroups = array();
	foreach ($curContact->get_groups() as $id => $value)
	{
		array_push($contactGroups, "<a href='".$_SERVER['PHP_SELF']."?action=showGroup&groupID=".$id."'>".$value."</a>");
	}
	
	$otherHeader = array("Contact Other Information");
	$otherTitle = array("Notes", "External ID 1", "External ID 2", "External ID 3", "Contact this person is in");
	$otherInfo = array($curContact->get_notes(), $curContact->get_external_id1(), $curContact->get_external_id2(), $curContact->get_external_id3(), $contactGroups);
	
	if ($_GET['action'] == editContacts || $_GET['action'] == editPerson)
	{
		//THE CLIENT INFO
		$contactKey = array("first", "middle", "last", "address1", "address2", "city", "province", "country", "postalCode", "phone1", "phone1Com", "phone2", "phone2Com", "cellphone", "cellphoneCom", "pager", "pagerCom", "fax", "email", "notes", "external1", "external2", "external3");
		$fieldType = array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "text_area", "", "", "", "static");
		
		$contactHeader = array("Contact Info");
		
		//array_unshift($nameTitle, "Contact Type");
		$contactTitle = array_merge($nameTitle, $placeTitle);
		$comTitle = array("Phone 1", "Phone 1 Comment", "Phone 2", "Phone 2 Comment", "Cellphone", "Cellphone Comment", "Pager", "Pager Comment", "Fax", "Email");
		$contactTitle = array_merge($contactTitle, $comTitle);
		$contactTitle = array_merge($contactTitle, $otherTitle);
		
		//array_unshift($nameInfo, $
		$contactInfo = array_merge($nameInfo, $placeInfo);
		$contactInfo = array_merge($contactInfo, $comAllInfo);
		$contactInfo = array_merge($contactInfo, $otherInfo);
		
		$contactForm->setTitles($contactTitle);
		$contactForm->setData($contactInfo);
		$contactForm->setHeadings($contactHeader);
		$contactForm->setDatabase($contactKey);
		$contactForm->setUpdateValue("updateContact");
		$contactForm->setFieldType($fieldType);
		echo $contactForm->editContactForm();
	}
	//if the user is showing the informating make it all uneditable
	else if($_GET['action'] == showContacts || $_GET['action'] == showPerson)
	{
		//THE CLIENT INFO
		$contactForm->setTableWidth("200px");
		$contactForm->setTitleWidth("50px");
		$contactForm->setTitles($nameTitle);
		$contactForm->setData($nameInfo);
		$contactForm->setHeadings($nameHeader);
		$contactForm->setSortable(false);
		$contactForm->setFirst(true);
		echo $contactForm->showInfoForm();
		
		$contactForm->setTableWidth("300px");
		$contactForm->setTitleWidth("100px");
		$contactForm->setTitles($placeTitle);
		$contactForm->setData($placeInfo);
		$contactForm->setHeadings($placeHeader);
		$contactForm->setFirst(false);
		echo $contactForm->showInfoForm();
			
		$contactForm->setTableWidth("300px");
		$contactForm->setTitleWidth("100px");
		$contactForm->setTitles($comTitle);
		$contactForm->setData($comInfo);
		$contactForm->setHeadings($comHeader);
		$contactForm->setFirst(false);
		echo $contactForm->showInfoForm();
		
		$contactForm->setTableWidth("200px");
		$contactForm->setTitleWidth("50px");
		$contactForm->setTitles($otherTitle);
		$contactForm->setData($otherInfo);
		$contactForm->setHeadings($otherHeader);
		$contactForm->setFirst(false);
		echo $contactForm->showInfoForm();
	}
}

function updateGroupType($groupType)
{
	//global all variables
	global $contactForm;
	
	$groupTypeKey = array("name", "desc");
	//create an empty temporary array to store all the new values given from the form
	$tempCustomerInfo=array();
	foreach($groupTypeKey as $index => $key)
	{
		$tempCustomerInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
					
	//makes sure the contact has a name
	if ($groupType->set_name($tempCustomerInfo["name"]))
	{
		$groupType->set_desc($tempCustomerInfo["desc"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($groupType->update())
		{
			$status="success";
			$_SESSION['action'] = "Updated Contact Type: ".$tempCustomerInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroupType&groupTypeID=".$_GET['groupTypeID']."&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showGroupType&groupTypeID=".$_GET['groupTypeID'];
			$contactForm->error("Warning: Failed to update. Reason: ".$groupType->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{		
		$link = $_SERVER['PHP_SELF']."?action=showGroupType&groupTypeID=".$_GET['groupTypeID'];
		$contactForm->error("Warning: Failed to update. Reason: ".$groupType->get_error(), $link);
	}
}

//Updating the contact. This is where the contacts stores updated values
function updateGroup($group)
{
	//global all variables
	global $contactForm;
	
	$groupInfoKey = array("name", "type", "cusCli", "cusGro", "desc", "notes");
	//create an empty temporary array to store all the new values given from the form
	$tempCustomerInfo=array();
	foreach($groupInfoKey as $index => $key)
	{
		$tempCustomerInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	
	if (!is_numeric($tempCustomerInfo["type"]))
	{
		$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
		$contactForm->error("Warning: Failed to add. Reason: Contact type not valid, please specify a valid contact type", $link);
		return;
	}
	
	//makes sure the contact has a name
	if ($group->set_name($tempCustomerInfo["name"]))
	{
		$group->set_custom_client_id($tempCustomerInfo["cusCli"]);
		$group->set_custom_group_id($tempCustomerInfo["cusGro"]);
		$group->set_desc($tempCustomerInfo["desc"]);
		$group->set_notes($tempCustomerInfo["notes"]);
		$group->set_group_type($tempCustomerInfo["type"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($group->update())
		{
			$status="success";
			$_SESSION['action'] = "Updated Group: ".$tempCustomerInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID']."&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
			$contactForm->error("Warning: Failed to update. Reason: ".$group->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{			
		$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
		$contactForm->error("Warning: Failed to update. Reason: ".$group->get_error(), $link);
	}
}

function addGroupType($groupType)
{
	//global all variables
	global $contactForm;
	
	$groupTypeKey = array("name", "desc");
	//create an empty temporary array to store all the new values given from the form
	$tempCustomerInfo=array();
	foreach($groupTypeKey as $index => $key)
	{
		$tempCustomerInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
					
	//makes sure the contact has a name
	if ($groupType->set_name($tempCustomerInfo["name"]))
	{
		$groupType->set_desc($tempCustomerInfo["desc"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($groupType->insert())
		{
			$status="success";
			$_SESSION['action'] = "Added new Contact Type: ".$tempCustomerInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroupTypes&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showGroupTypes";
			$contactForm->error("Warning: Failed to update. Reason: ".$groupType->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{	
		$link = $_SERVER['PHP_SELF']."?action=showGroupTypes";
		$contactForm->error("Warning: Failed to update. Reason: ".$groupType->get_error(), $showGroupTypes);
	}
}

//Updating the contact. This is where the contacts stores updated values
function addGroup($group)
{
	//global all variables
	global $contactForm;
	
	$groupInfoKey = array("name", "type", "cusCli", "cusGro", "desc", "notes");
	//create an empty temporary array to store all the new values given from the form
	$tempCustomerInfo=array();
	foreach($groupInfoKey as $index => $key)
	{
		$tempCustomerInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	
	if (!is_numeric($tempCustomerInfo["type"]))
	{
		$contactForm->error("Warning: Failed to add. Reason: Contact type not valid, please specify a valid contact type");
		return;
	}
					
	//makes sure the contact has a name
	if ($group->set_name($tempCustomerInfo["name"]))
	{
		$group->set_custom_client_id($tempCustomerInfo["cusCli"]);
		$group->set_custom_group_id($tempCustomerInfo["cusGro"]);
		$group->set_desc($tempCustomerInfo["desc"]);
		$group->set_notes($tempCustomerInfo["notes"]);
		$group->set_group_type($tempCustomerInfo["type"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($group->insert())
		{
			$status="success";
			$_SESSION['action'] = "Added Group: ".$tempCustomerInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?add=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$contactForm->error("Warning: Failed to add. Reason: ".$group->get_error());
		}
	}
	//if there are no names then show error
	else
	{								
		$contactForm->error("Warning: Failed to add. Reason: ".$group->get_error());
	}
}


//Updating the contact. This is where the contacts stores updated values
function updateContactType($contactType)
{
	//global all variables
	global $contactForm;
	
	$contactTypeInfoKey = array("name", "desc");
	//create an empty temporary array to store all the new values given from the form
	$tempCustomerInfo=array();
	foreach($contactTypeInfoKey as $index => $key)
	{
		$tempCustomerInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
		
	//makes sure the contact has a name
	if ($contactType->set_name($tempCustomerInfo["name"]))
	{
		$contactType->set_desc($tempCustomerInfo["desc"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($contactType->update())
		{
			$status="success";
			$_SESSION['action'] = "Updated Person Type: ".$tempCustomerInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showContactType&contactTypeID=".$_GET['contactTypeID']."&update=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showContactType&contactTypeID=".$_GET['contactTypeID'];
			$contactForm->error("Warning: Failed to update. Reason: ".$contactType->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{								
		$link = $_SERVER['PHP_SELF']."?action=showContactType&contactTypeID=".$_GET['contactTypeID'];
		$contactForm->error("Warning: Failed to update. Reason: ".$contactType->get_error(), $link);
	}
}

//Updating the contact. This is where the contacts stores updated values
function addContactType($contactType)
{
	//global all variables
	global $contactForm;
	
	$contactTypeInfoKey = array("name", "desc");
	//create an empty temporary array to store all the new values given from the form
	$tempCustomerInfo=array();
	foreach($contactTypeInfoKey as $index => $key)
	{
		$tempCustomerInfo[$key] = addslashes(htmlspecialchars(trim($_POST[$key]),ENT_QUOTES));
	}
					
	//makes sure the contact has a name
	if ($contactType->set_name($tempCustomerInfo["name"]))
	{
		$contactType->set_desc($tempCustomerInfo["desc"]);
		//if the update is sucessful go back to show the new updates or else show an error
		if($contactType->insert())
		{
			$status="success";
			$_SESSION['action'] = "Added new Person Type: ".$tempCustomerInfo["name"];
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showContactTypes&add=".$status."\">";
			//2010-01-04 08:56:34
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showContactTypes";
			$contactForm->error("Warning: Failed to update. Reason: ".$contactType->get_error());
		}
	}
	//if there are no names then show error
	else
	{								
		$link = $_SERVER['PHP_SELF']."?action=showContactTypes";
		$contactForm->error("Warning: Failed to update. Reason: ".$contactType->get_error());
	}
}

//Updating the contact. This is where the contacts stores updated values
function updateContact($contact)
{
	//global all variables
	global $contactForm;
	
	$contactInfoKey = array("first", "middle", "last", "address1", "address2", "city", "province", "country", "postalCode", "phone1", "phone1Com", "phone2", "phone2Com", "cellphone", "cellphoneCom", "pager", "pagerCom", "fax", "email", "notes", "external1", "external2", "external3");
	//create an empty temporary array to store all the new values given from the form
	$tempCustomerInfo=array();
	foreach($contactInfoKey as $index => $key)
	{
		$tempCustomerInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	
	//makes sure the contact has a name
	if ($contact->set_first_name($tempCustomerInfo["first"]))
	{
		$contact->set_middle_name($tempCustomerInfo["middle"]);
		$contact->set_last_name($tempCustomerInfo["last"]);
		$contact->set_addr_line1($tempCustomerInfo["address1"]);
		$contact->set_addr_line2($tempCustomerInfo["address2"]);
		$contact->set_city($tempCustomerInfo["city"]);
		$contact->set_province($tempCustomerInfo["province"]);
		$contact->set_country($tempCustomerInfo["country"]);
		$contact->set_postal_code($tempCustomerInfo["postalCode"]);
		$contact->set_phone1($tempCustomerInfo["phone1"]);
		$contact->set_phone1_comment($tempCustomerInfo["phone1Com"]);
		$contact->set_phone2($tempCustomerInfo["phone2"]);
		$contact->set_phone2_comment($tempCustomerInfo["phone2Com"]);
		$contact->set_phone_cell($tempCustomerInfo["cellphone"]);
		$contact->set_phone_cell_comment($tempCustomerInfo["cellphoneCom"]);
		$contact->set_phone_pager($tempCustomerInfo["pager"]);
		$contact->set_phone_pager_comment($tempCustomerInfo["pagerCom"]);
		$contact->set_phone_fax($tempCustomerInfo["fax"]);
		$contact->set_email($tempCustomerInfo["email"]);
		$contact->set_notes($tempCustomerInfo["notes"]);
		$contact->set_external_id1($tempCustomerInfo["external1"]);
		$contact->set_external_id2($tempCustomerInfo["external2"]);
		$contact->set_external_id3($tempCustomerInfo["external3"]);
		
		//if the update is sucessful go back to show the new updates or else show an error
		if($contact->update())
		{
			$status="success";
			$_SESSION['action'] = "Updated Contact: ".$tempCustomerInfo["first"]." ".$tempCustomerInfo["last"];
			if ($_GET['action'] == 'editContacts')
			{
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showContacts&contactID=".$_GET['contactID']."&groupID=".$_GET['groupID']."&contactTypeID=".$_GET['contactTypeID']."&update=".$status."\">";
			}
			else if ($_GET['action'] == 'editPerson')
			{
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showPerson&contactID=".$_GET['contactID']."&update=".$status."\">";
			}
			//2010-01-04 08:56:34
		}
		else{
			if ($_GET['action'] == 'editContacts')
			{
				$link = $_SERVER['PHP_SELF']."?action=showContacts&contactID=".$_GET['contactID'];
			}
			else if ($_GET['action'] == 'editPerson')
			{
				$link = $_SERVER['PHP_SELF']."?action=showPerson&contactID=".$_GET['contactID'];
			}
			$contactForm->error("Warning: Failed to update. Reason: ".$contact->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{
		if ($_GET['action'] == 'editContacts')
		{
			$link = $_SERVER['PHP_SELF']."?action=showContacts&contactID=".$_GET['contactID'];
		}
		else if ($_GET['action'] == 'editPerson')
		{
			$link = $_SERVER['PHP_SELF']."?action=showPerson&contactID=".$_GET['contactID'];
		}
		$contactForm->error("Warning: Failed to update. Reason: ".$contact->get_error(), $link);
	}
}

//This function displays the add contact form
function addContactForm()
{
	//global all variables
	global $contactForm, $tool;
	
	$allContact = Person::get_contacts();
	if(!isset($_GET['user']))
	{
		echo "<select style='margin-bottom:10px;' onchange=\"return LoadPage('contacts.php?action=addContact&user='+this.value, 'newContact');\">
				<option value='0'>New Person</option>
				<option value='1'>Existing Persons</option>";
		
		
		/*foreach ($allContact as $id =>$value)
		{
			echo "<option value='".$id."'>".$value."</option>";	
		}*/
		echo "</select><br/>";
	}
	
	echo "<div id='newContact'>";
	
	$nameHeader = array("Contact Name Info");
	$nameTitle = array("First", "Middle", "Last");
		
	$placeHeader = array("Contact Location Info");
	$placeTitle = array("Address 1", "Address 2", "City", "Province", "Country", "Postal Code");
	
				
	$comHeader = array("Contact Communication Info");
						
	$otherHeader = array("Contact Other Information");
	$otherTitle = array("Notes", "External ID 1", "External ID 2", "External ID 3");
		
	//THE CLIENT INFO
	$contactKey = array("first", "middle", "last", "address1", "address2", "city", "province", "country", "postalCode", "phone1", "phone1Com", "phone2", "phone2Com", "cellphone", "cellphoneCom", "pager", "pagerCom", "fax", "email", "notes", "external1", "external2", "external3", "type");
	$fieldType = array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "text_area", "", "", "", "drop_down");
		
	if($_GET['user'] == "0" || !isset($_GET['user']))
	{		
		$contactHeader = array("New Contact Info");
			
		//array_unshift($nameTitle, "Contact Type");
		$contactTitle = array_merge($nameTitle, $placeTitle);
		$comTitle = array("Phone 1", "Phone 1 Comment", "Phone 2", "Phone 2 Comment", "Cellphone", "Cellphone Comment", "Pager", "Pager Comment", "Fax", "Email");
		$contactTitle = array_merge($contactTitle, $comTitle);
		$contactTitle = array_merge($contactTitle, $otherTitle);
		array_push($contactTitle, "Person Type");
		
		$allContactType = PersonType::get_contact_types();
		
		$contactForm->setType($allContactType);
		$contactForm->setTitles($contactTitle);
		$contactForm->setHeadings($contactHeader);
		$contactForm->setDatabase($contactKey);
		$contactForm->setAddValue("addContact");
		
		$contactForm->setFieldType($fieldType);
		echo $contactForm->newContactForm();
	}
	else {
		
		echo $tool->createNewFilters();
		echo "<form method='post' style='clear:left; width:1024px;'>";
		
		echo "<table id=\"dataTable\" cellspacing=\"0\" cellpadding=\"0\" border=\"1\" style='width:1024px; clear:left;'>";
		echo "<thead><tr>
				<th style='text-align:left;'>Existing Contacts</th>
				<th style='text-align:left;'>Phone</th>
				<th style='text-align:left;'>Country</th>
				<th style='text-align:left;'>Email</th>
			</tr></thead>";
			
		$allContactType = PersonType::get_contact_types();
		foreach($allContact as $id => $value)
		{
			$curContact = new Person($id);
			
			echo "<tr>
			<td><input type='checkbox' name='user[]' value='".$id."'>".$value."</input></td>
			<td>".$curContact->get_phone1() ."</td>
			<td>".$curContact->get_country() ."</td>
			<td>".$curContact->get_email() ."</td>
			</tr>";	
		}
		
		echo "</table>
		<select name='type' style='width:20%;'>";
			foreach ($allContactType as $ctID => $ctValue)
			{
				echo "<option value='".$ctID."'>".$ctValue."</option>";
			}
		echo "</select>

		<input type='submit' name='addContactExist' value='Add this contact' style='clear:left;'></input>
		</form>";
		
	}
	
	echo "</div>";
}

//This function displays the add contact form
function addPersonForm()
{
	//global all variables
	global $contactForm, $tool;
	
	$nameHeader = array("Contact Name Info");
	$nameTitle = array("First", "Middle", "Last");
		
	$placeHeader = array("Contact Location Info");
	$placeTitle = array("Address 1", "Address 2", "City", "Province", "Country", "Postal Code");
	
				
	$comHeader = array("Contact Communication Info");
						
	$otherHeader = array("Contact Other Information");
	$otherTitle = array("Notes", "External ID 1", "External ID 2", "External ID 3");
		
	//THE CLIENT INFO
	$contactKey = array("first", "middle", "last", "address1", "address2", "city", "province", "country", "postalCode", "phone1", "phone1Com", "phone2", "phone2Com", "cellphone", "cellphoneCom", "pager", "pagerCom", "fax", "email", "notes", "external1", "external2", "external3", "type");
	$fieldType = array("", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "", "text_area", "", "", "", "drop_down");
	
	$contactHeader = array("New Contact Info");
			
	//array_unshift($nameTitle, "Contact Type");
	$contactTitle = array_merge($nameTitle, $placeTitle);
	$comTitle = array("Phone 1", "Phone 1 Comment", "Phone 2", "Phone 2 Comment", "Cellphone", "Cellphone Comment", "Pager", "Pager Comment", "Fax", "Email");
	$contactTitle = array_merge($contactTitle, $comTitle);
	$contactTitle = array_merge($contactTitle, $otherTitle);
	array_push($contactTitle, "Person Type");
		
	$allContactType = PersonType::get_contact_types();
	
	$contactForm->setType($allContactType);
	$contactForm->setTitles($contactTitle);
	$contactForm->setHeadings($contactHeader);
	$contactForm->setDatabase($contactKey);
	$contactForm->setAddValue("addContact");
		
	$contactForm->setFieldType($fieldType);
	echo $contactForm->newContactForm();
}

//Updating the contact. This is where the contacts stores updated values
function addContact($contact)
{
	//global all variables
	global $contactForm;
	
	$contactType = new PersonType($_GET['contactTypeID']);
	$contactInfoKey = array("first", "middle", "last", "address1", "address2", "city", "province", "country", "postalCode", "phone1", "phone1Com", "phone2", "phone2Com", "cellphone", "cellphoneCom", "pager", "pagerCom", "fax", "email", "notes", "external1", "external2", "external3", "type");
	//create an empty temporary array to store all the new values given from the form
	$tempCustomerInfo=array();
	foreach($contactInfoKey as $index => $key)
	{
		$tempCustomerInfo[$key] = htmlspecialchars(trim($_POST[$key]),ENT_QUOTES);
	}
	
	//makes sure the contact has a name
	if ($contact->set_first_name($tempCustomerInfo["first"]))
	{
		$contact->set_middle_name($tempCustomerInfo["middle"]);
		$contact->set_last_name($tempCustomerInfo["last"]);
		$contact->set_addr_line1($tempCustomerInfo["address1"]);
		$contact->set_addr_line2($tempCustomerInfo["address2"]);
		$contact->set_city($tempCustomerInfo["city"]);
		$contact->set_province($tempCustomerInfo["province"]);
		$contact->set_country($tempCustomerInfo["country"]);
		$contact->set_postal_code($tempCustomerInfo["postalCode"]);
		$contact->set_phone1($tempCustomerInfo["phone1"]);
		$contact->set_phone1_comment($tempCustomerInfo["phone1Com"]);
		$contact->set_phone2($tempCustomerInfo["phone2"]);
		$contact->set_phone2_comment($tempCustomerInfo["phone2Com"]);
		$contact->set_phone_cell($tempCustomerInfo["cellphone"]);
		$contact->set_phone_cell_comment($tempCustomerInfo["cellphoneCom"]);
		$contact->set_phone_pager($tempCustomerInfo["pager"]);
		$contact->set_phone_pager_comment($tempCustomerInfo["pagerCom"]);
		$contact->set_phone_fax($tempCustomerInfo["fax"]);
		$contact->set_email($tempCustomerInfo["email"]);
		$contact->set_notes($tempCustomerInfo["notes"]);
		$contact->set_external_id1($tempCustomerInfo["external1"]);
		$contact->set_external_id2($tempCustomerInfo["external2"]);
		$contact->set_external_id3($tempCustomerInfo["external3"]);
		
		//if the update is sucessful go back to show the new updates or else show an error
		if($contact->insert())
		{
			$status="success";
			$allContact = Person::get_contacts();
			//$lastAdded = $allContact[count($allContact)-1];
			//NEED TO PUT SOME HANDLER HERE INCASE THE LAST ISN'T THE LAST ADDED
			
			$lastID=0;
			foreach ($allContact as $id => $value)
			{
				if($lastID<$id)
				{$lastID = $id;}
			}
			
			foreach ($allContact as $id => $value)
			{
				if (preg_match("/".$tempCustomerInfo["first"]."/", $value) && preg_match("/".$tempCustomerInfo["last"]."/", $value) && $id == $lastID)
				{
					$group = new Contact($_GET['groupID']);
					$group->add_contact($lastID, $tempCustomerInfo['type'], $tempCustomerInfo["first"]." ".$tempCustomerInfo["last"]." is added to this group");
				}
			}
			$_SESSION['action'] = "Added New Contact: ".$tempCustomerInfo["first"]." ".$tempCustomerInfo["last"]." to ".$contactType->get_name();
			if ($_GET['action'] == 'addPerson')
			{
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showPeople&add=".$status."\">";
			}
			else
			{
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID']."&add=".$status."\">";
				//2010-01-04 08:56:34
			}
		}
		else{
			
			if ($_GET['action'] == 'addPerson')
			{
				$link = $_SERVER['PHP_SELF']."?action=showPeople";
			}
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
			}
			$contactForm->error("Warning: Failed to update. Reason: ".$contact->get_error(), $link);
		}
	}
	//if there are no names then show error
	else
	{
		if ($_GET['action'] == 'addPerson')
		{
			$link = $_SERVER['PHP_SELF']."?action=showPeople";
		}
		else
		{
			$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
		}
		$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
		$contactForm->error("Warning: Failed to update. Reason: ".$contact->get_error(), $link);
	}
}

//Updating the contact. This is where the contacts stores updated values
function addContactExist()
{
	//global all variables
	global $contactForm;
	
	$allUser = $_POST['user'];
	$contactType = new PersonType($_POST['type']);
	$group = new Contact($_GET['groupID']);
	
	$allnames = array();
	foreach ($allUser as $id => $value)
	{
		$contact = new Person($value);
		
		if($group->add_contact($contact->get_contact_id(), $contactType->get_contact_type_id(), $contact->get_first_name()." ".$contact->get_last_name()." is added to this group"))
		{
			$finish = true;
			array_push($allnames, $contact->get_first_name()." ".$contact->get_last_name(). " to ".$contactType->get_name());
		}
		else{
			$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
			$contactForm->error("Warning: Failed to update. Reason: ".$contact->get_error(), $link);
			$finish = false;
			break;
		}
	}
	
	if ($finish)
	{
		$status="success";
		$string = "";
		foreach ($allnames as $id => $value)
		{
			$string .= $value.". ";
		}
		$_SESSION['action'] = "Added Contact: ".$string;
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID']."&add=".$status."\">";
		//2010-01-04 08:56:34
	}
	
}




//The function removes a contact
function removeGroupType($groupType)
{
	//global the variables
	global $contactForm;
	
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['groupTypeID'])){
			$name = $groupType->get_name();
			if($groupType->delete()){
				echo "HERE";
				$status="success";
				$_SESSION['action'] = "Removed: ".$name;
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroupTypes&delete=$status\">";
			}
			//or else show error
			else
			{
				$link =$_SERVER['PHP_SELF']."?action=showGroupTypes&groupTypeID=".$_GET['groupTypeID'];
				$contactForm->error("Warning: Failed to remove Person Type. Reason: ".$groupType->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroupType&groupTypeID=".$_GET['groupTypeID']."\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$contactForm->prompt("Are you sure you want to delete?");
	}					
}
				
//The function removes a contact
function removeContactGroup($group)
{
	//global the variables
	global $contactForm;
					
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['contactID']) && is_numeric($_GET['contactTypeID'])){
			
			$contact = new Person($_GET['contactID']);
			$contactType = new PersonType($_GET['contactTypeID']);
			if($group->remove_contact($_GET['contactID'], $_GET['contactTypeID'])){
				$status="success";
				$_SESSION['action'] = "Removed from ".$contactType->get_name().": ".$contact->get_first_name()." ".$contact->get_last_name();
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID']."&delete=$status\">";
			}
			//or else show error
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
				$contactForm->error("Warning: Failed to remove customer. Reason: ".$group->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID']."\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$contactForm->prompt("Are you sure you want to delete?");
	}					
}

//The function removes a contact
function removeContactDB($contact)
{
	//global the variables
	global $contactForm;
	
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['contactID'])){
			$name = $contact->get_first_name()." ".$contact->get_last_name();
			if($contact->delete()){
				$status="success";
				$_SESSION['action'] = "Removed: ".$name;
				if ($_GET['action'] == 'removeContactDB')
				{
					echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID']."&delete=$status\">";
				}
				else if ($_GET['action'] == 'removePersonDB')
				{
					echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showPeople&delete=$status\">";
				}
			}
			//or else show error
			else
			{
				if ($_GET['action'] == 'removeContactDB')
				{
					$link = $_SERVER['PHP_SELF']."?action=showContacts&contactID=".$_GET['contactID'];
				}
				else if ($_GET['action'] == 'removePersonDB')
				{
					$link = $_SERVER['PHP_SELF']."?action=showPerson&contactID=".$_GET['contactID'];
				}
				$contactForm->error("Warning: Failed to remove customer. Reason: ".$contact->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		if ($_GET['action'] == 'removeContactDB')
		{
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showContacts&contactID=".$_GET['contactID']."&groupID=".$_GET['groupID']."&contactTypeID=".$_GET['contactTypeID']."\">";
		}
		else
		{
			echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showPerson&contactID=".$_GET['contactID']."\">";
		}
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$contactForm->prompt("Are you sure you want to delete?");
	}					
}

//The function removes a contact
function removeGroup($group)
{
	//global the variables
	global $contactForm;
	
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['groupID'])){
			$name = $group->get_name();
			if($group->delete()){
				$status="success";
				$_SESSION['action'] = "Removed: ".$name;
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?delete=$status\">";
			}
			//or else show error
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID'];
				$contactForm->error("Warning: Failed to remove customer. Reason: ".$group->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showGroup&groupID=".$_GET['groupID']."\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$contactForm->prompt("Are you sure you want to delete?");
	}					
}

//The function removes a contact
function removeContactType($contactType)
{
	//global the variables
	global $contactForm;
	
	//if the user confirms the delete then delete the id
	if(isset($_POST['deleteYes']))
	{
		//if the id is valid delete
		if (is_numeric($_GET['contactTypeID'])){
			$name = $contactType->get_name();
			if($contactType->delete()){
				$status="success";
				$_SESSION['action'] = "Removed: ".$name;
				echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showContactTypes&delete=$status\">";
			}
			//or else show error
			else
			{
				$link = $_SERVER['PHP_SELF']."?action=showContactType&contactTypeID=".$_GET['contactTypeID'];
				$contactForm->error("Warning: Failed to remove Person Type. Reason: ".$contactType->get_error(), $link);
			}						
		}
	}
	//if the user does not confirm, then refrest to the current ID
	else if(isset($_POST['deleteNo']))
	{
		echo "<meta http-equiv=\"REFRESH\" content=\"0;url=".$_SERVER['PHP_SELF']."?action=showContactType&contactTypeID=".$_GET['contactTypeID']."\">";
	}
	//if the user has not been prompted yet, prompt the user for a delete
	else {						
		$contactForm->prompt("Are you sure you want to delete?");
	}					
}
?>

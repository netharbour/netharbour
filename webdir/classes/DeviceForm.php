<?
/*****************FORM CREATION**************************************************************
Originally I want to extend this to another class called form. But because there are so little forms right now, it has not been done yet. For future reference, this class should be extended to another class called "Form" and the class name should be called "ClientForm'
*/
include_once 'Form.php';
class DeviceForm extends Form
{
	//show the client form
	function showDeviceForm($headingName, $titleName, $infoArray)
	{
		parent::setHeadings($headingName);
		parent::setTitles($titleName);
		parent::setData($infoArray);
		parent::setSortable(false);
		return parent::showForm(2);
	}

	//the function to create an layout for the form editing
	function editDeviceForm($headingName, $titleName, $infoArray, $infoKey, $type='')
	{
		parent::setHeadings($headingName);
		parent::setTitles($titleName);
		parent::setData($infoArray);
		parent::setSortable(false);
		parent::setDatabase($infoKey);
		
		foreach	($type as $id =>$value)
		{
			if (is_array($value))
			{
				parent::setType($value);
			}	
			else
			{
				parent::setType($type);
				break;
			}
		}
		
		return parent::editForm(2);
	}
	
	//this function creates a new client form with no values
	function newDeviceForm($headingName, $titleName, $infoKey, $type='')
	{
		parent::setHeadings($headingName);
		parent::setTitles($titleName);
		parent::setSortable(false);
		parent::setDatabase($infoKey);
		
		foreach	($type as $id =>$value)
		{
			if (is_array($value))
			{
				parent::setType($value);
			}	
			else
			{
				parent::setType($type);
				break;
			}
		}
		
		return parent::newForm(2);			
	}
	
	function editPortForm($headingName, $titleName, $infoArray, $infoKey, $ports='')
	{
		parent::setHeadings($headingName);
		parent::setTitles($titleName);
		parent::setData($infoArray);
		parent::setSortable(false);
		parent::setDatabase($infoKey);
		parent::setType($ports);
		parent::setUpdateValue("updatePort");
		return parent::editForm(1);
	}
	
		//this function creates a new client form with no values
	function newPortForm($headingName, $titleName, $infoKey, $type='')
	{
		parent::setHeadings($headingName);
		parent::setTitles($titleName);
		parent::setSortable(false);
		parent::setDatabase($infoKey);
		
		foreach	($type as $id =>$value)
		{
			if (is_array($value))
			{
				parent::setType($value);
			}	
			else
			{
				parent::setType($type);
				break;
			}
		}
		
		parent::setAddValue("addPort");
		return parent::newForm(2);			
	}
	
	//This function creates a layout that displays all users
	function showAll($headingName, $titles, $data, $handlers = array(), $mouseType =0)
	{
		parent::setTitles($titles);
		parent::setData($data);
		parent::setEventHandler($handlers);
		parent::setHeadings($headingName);
		parent::setSortable(true);
		parent::setMouseHandlerType($mouseType);
		return parent::showForm(90);
	}
	
	//Function fo the error message layout
	function error($msg, $id)
	{
		parent::error($msg);
		//if there are no customer id, then the error will go back to the original page
		if($id =="")
		{
			echo "<input type='button' value='Back' onclick=\"handleEvent('devices.php')\">";
		}
		//or else go back to the original user page
		else{
			echo "<input type='button' value='Back' onclick=\"handleEvent('devices.php?action=edit&ID=$id')\">";
		}
	}
}
?>

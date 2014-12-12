<?
/*****************FORM CREATION**************************************************************
Originally I want to extend this to another class called form. But because there are so little forms right now, it has not been done yet. For future reference, this class should be extended to another class called "Form" and the class name should be called "ClientForm'
*/
include_once 'Form.php';
class PropertyForm extends Form
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
	
	//this function creates a new client form with no values
	function newModalForm($headingName, $titleName, $infoKey, $value, $type='')
	{
		parent::setHeadings($headingName);
		parent::setTitles($titleName);
		parent::setSortable(false);
		parent::setDatabase($infoKey);
		parent::setType($type);
		parent::setAddValue($value);
		return parent::newModalForm(10);	
	}
	
	function editModalForm($headingName, $titleName, $infoArray, $infoKey, $modalID, $value)
	{
		parent::setHeadings($headingName);
		parent::setTitles($titleName);
		parent::setData($infoArray);
		parent::setSortable(false);
		parent::setDatabase($infoKey);
		parent::setModalID($modalID);
		parent::setUpdateValue($value);
		return parent::modalForm(10);
	}

	//Function fo the error message layout
	function error($msg, $id='')
	{
		parent::error($msg);
		//if there are no customer id, then the error will go back to the original page
		if($id =="")
		{
			echo "<input type='button' value='Back' onclick=\"handleEvent('configurations.php')\">";
		}
		//or else go back to the original user page
		else{
			echo "<input type='button' value='Back' onclick=\"handleEvent('configurations.php?action=edit&ID=$id')\">";
		}
	}
}
?>

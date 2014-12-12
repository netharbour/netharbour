<?
/*****************FORM CREATION**************************************************************
Originally I want to extend this to another class called form. But because there are so little forms right now, it has not been done yet. For future reference, this class should be extended to another class called "Form" and the class name should be called "PluginForm'
*/
include_once 'Form.php';
class PluginForm extends Form
{
	//show the Plugin form
	function showInfoForm()
	{
		return parent::showForm(5);
	}
	
	//This function creates a layout that displays all users
	function showAll()
	{
		return parent::showForm();
	}
	
	//Function fo the error message layout
	function error($msg, $cusID)
	{
		parent::error($msg);
		//if there are no customer id, then the error will go back to the original page
		if($cusID =="")
		{
			echo "<input type='button' value='Back' onclick=\"handleEvent('plugins.php')\">";
		}
		//or else go back to the original user page
		else{
			echo "<input type='button' value='Back' onclick=\"handleEvent('plugins.php?action=edit&ID=$cusID')\">";
		}
	}
}
?>

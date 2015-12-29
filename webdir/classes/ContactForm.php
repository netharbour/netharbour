<?
/*****************FORM CREATION**************************************************************
Originally I want to extend this to another class called form. But because there are so little forms right now, it has not been done yet. For future reference, this class should be extended to another class called "Form" and the class name should be called "ContactForm'
*/
include_once 'Form.php';
class ContactForm extends Form
{
	//show the Contact form
	function showInfoForm()
	{
		return parent::showForm(5);
	}

	//the function to create an layout for the form editing
	function editContactForm()
	{
		return parent::editForm(5);
	}
	
	//this function creates a new Contact form with no values
	function newContactForm()
	{
		return parent::newForm(5);
			
	}
	
	//This function creates a layout that displays all users
	function showAll()
	{
		return parent::showForm();
	}
	
	//Function fo the error message layout
	function error($msg, $url='')
	{
		parent::error($msg);
		//if there are no customer id, then the error will go back to the original page
		if($url == '')
		{
			echo "<input type='button' value='Back' onclick=\"handleEvent('contacts.php')\">";
		}
		//or else go back to the original user page
		else{
			echo "<input type='button' value='Back' onclick=\"handleEvent('".$url."')\">";
		}
	}
}
?>

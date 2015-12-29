<?
include_once ("../../sessionCheck.php");
include_once("../../classes/Property.php");

$property = new Property_users();
$value = $_POST['greetings'];

if(isset($_POST['greetings']))
{
	$value = $_POST['greetings'];
	$property->set_property("Widget_HelloWorld__greetings", $value, "Goodbye or Hello?");
}
?>
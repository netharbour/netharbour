<?
class HelloWorld
{
	//default content
	private $content = "<h1>HELLO WORLD!</h1>";

	// initialization function run only when plugin is first enabled
	function on_enable()
	{
		// Run any one-time start-up code (e.g. CREATE TABLE IF NOT EXISTS)
	}
	
	//renders the content
	function get_content()
	{
		//use the property class to get content from db
		include_once("classes/Property.php");
		//name of the property
		$name = "Plugin_HelloWorld_greetings";
		
		//create the property to get information
		$property = new Property($name);
		
		//if this property gets the information, show the information out
		if($propertyInfo = $property->get_property($name))
		{return "<h1>".addslashes($propertyInfo)."</h1>";}
		//if not give a default content
		else {return $this->content;}
	}
	
	//renders the configuration
	function get_config($id='')
	{
		// MUST HAVE<input type='hidden' name='id' value=".$id."></input>
		// the name of the property must follow the conventions Widget_<Classname>_<propertyName>
		// have the form post and make sure the submit button is named widget_update
		// make sure there is also a hidden value giving the name of this Class file
		return "<form id='configForm' method='post'>
			<input type='hidden' name='class' value='HelloWorld'></input>
			<input type='hidden' name='id' value=".$id."></input>
			<select name='Plugin_HelloWorld_greetings'>
				<option value='Hello World!'>Hello World!</option>
				<option value='Goodbye World!'>Goodbye World!</option>
			</select>
		
		<input type='submit' class='submitBut' name='plugin_update' value='Update config'/>
		</form>";
	}
	
	//updates the configuration, needs to return a true or false value.
	function update_config($values='')
	{
		//calls on for the database class
		include_once("classes/Property.php");
		$property = new Property();
		
		//sets the properties to store them, use a switch statement to store different description based on different properties
		if($property->set_property("Plugin_HelloWorld_greetings", $_POST['Plugin_HelloWorld_greetings'], "Hello World description"))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}
?>

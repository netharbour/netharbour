<?
class HelloWorld {
	
	//default content
	private $content = "<h1>HELLO WORLD!</h1>";
	
	//renders the content
	function get_content()
	{
		//use the property class to get content from db
		include_once("classes/Property.php");
		//name of the property
		$name = "Widget_HelloWorld_greetings";
		
		//create the property to get information
		$property = new Property_users($name);
		
		//if this property gets the information, show the information out
		if($propertyInfo = $property->get_property($name))
		{return "<h1>".addslashes($propertyInfo)."</h1>";}
		//if not give a default content
		else {return $this->content;}
	}
	
	//renders the configuration
	function get_config()
	{
		// the name of the property must follow the conventions Widget_<Classname>_<propertyName>
		// have the form post and make sure the submit button is named widget_update
		// make sure there is also a hidden value giving the name of this Class file
		return "<form id='configForm' method='post'>
			<input type='hidden' name='class' value='HelloWorld'></input>
			<select name='Widget_HelloWorld_greetings'>
				<option value='Hello World!'>Hello World!</option>
				<option value='Goodbye World!'>Goodbye World!</option>
			</select>
		
		<input type='submit' class='submitBut' name='widget_update' value='Update config'/>
		</form>";
	}
	
	//updates the configuration
	function update_config()
	{
		//calls on for the database class
		include_once("classes/Property.php");
		$property = new Property_users();
		
		//sets the properties to store them, use a switch statement to store different description based on different properties
		$property->set_property("Widget_HelloWorld_greetings", $_POST['Widget_HelloWorld_greetings'], "The Hellow world property description");
	}

}
?>

<!--<script language="javascript">
$(function() {
		   $('.submitBut').click(function(){
	$.post("widgets/Demo-HelloWorld/updateMessage.php", $("#configForm").serialize() , function(data){location.reload(true)});
	
							   });
		   });
</script>-->
<?

class Demo
{
	function get_content(){
		
		$form = "<form method='post'>
				What is your name? <input name='name' type='text'/>
				<input type='submit' value='Submit'/>
				</form>";
				
		if(!isset($_POST['name']))
		{
			return $form;	
		}
		else
		{
			return "Hi, ".$_POST['name'].", This is my demo";
		}
	}
}

?>
<?
class CMDBUpdates
{	
	function get_content()
	{
		include_once 'classes/Dashboard.php';

		$updates0 = Updates::get_updates(0);
		$updates1 = Updates::get_updates(1);
		foreach ($updates0 as $id => $value)
		{$updates[$id] = $value;}
		foreach ($updates1 as $id => $value)
		{$updates[$id] = $value;}
		
		$index = 1;
		$updateStr ='';
		
		$updateStr .= "<ul>";
		
		foreach ($updates as $id => $action)
		{
			$curUpdate = new Updates($id);
			
			
			if($index < 10)
			{$updateStr .= '<li>'.$curUpdate->get_date().' by '.$curUpdate->get_username().' - '.$curUpdate->get_action().'</li>';}
			else
			{$updateStr .= "<li class=\"hidden\">".$curUpdate->get_date()." by ".$curUpdate->get_username()." - ".$curUpdate->get_action()."</li>";}
			
			if($index==9)
			{
				$updateStr .= "<li style='float:right; list-style:none;'><input type='checkbox' id='showArchived'>Show archived updates</input></li><hr>";	
			}
			$index++;
			
		}
					
		$updateStr .= "</ul>";
		return $updateStr;
	}
}

?>

<script language="javascript">
$(function() {
		   $('.hidden').hide();
		   $('#showArchived').click(function(){
					$(".hidden").toggle(400);
									})
		   
		   });
</script>
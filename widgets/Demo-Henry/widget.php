<script type="text/javascript">

$(function() {
var index = 0;

$("#awesome").click(
	function() 
	{
		$.post("widgets/Demo-Henry/buttonResponse.php", "inc="+index, 
			   function(theResponse)
			   {
					$("#henrystat").html(theResponse);
				}
			);
		index++;
	}
	);

});
		
</script>

<?
//TESTS javascript and posting methods in this widget.
class HenryAwesome {

	function get_content()
	{
		return "How awesome can CMDB get?<br/><input id='awesome' type='button' value=\"increase CMDB's awesomeness\" ></input>
		<div id='henrystat'></div>";
	}
}
?>
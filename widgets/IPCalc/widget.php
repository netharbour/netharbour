<?
/////////////////////////////////////////////////////
// IP calculator
// Andree Toonk, May 2011
/////////////////////////////////////////////////////
?>

<script type="text/javascript">

$(function() {

//changed name incase of conflict with cmdb awesome
$("#calcIP").click(
        function() 
        {
		//Instead of using form which needs a form to be created, I just gave each an id
		$("#henrystat").html("<img height='45' src='icons/loading.gif'>");
		$.post("widgets/IPCalc/buttonResponse.php", {length: $("#length").val(), net: $("#net").val()},
                           function(theResponse)
                           {
                                        $("#henrystat").html(theResponse);
                                }
                        );
        }
        );

});
</script>

<?
include_once 'plugins/IP Manager/Netblock.php';

class IPCalcWidget {
	
	function get_content() {
		$content = "";
		// determine inet by address
		$ip =  $_SERVER['REMOTE_ADDR'];
		if (preg_match ("/[0-9A-Fa-f]{1,4}:/i", "$ip")){
			$inet= 6;
			$length = 48;
		} else {
			$inet =4;
			$length = 24;
		}

		return "
        		Network 
        		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	
        		Length<br>
        		<input type='text' id='net' name='net' size='26' value='$ip'/>  /
        		<input type='text' id='length' name='length' size='3' value='$length' />
        		&nbsp;&nbsp;&nbsp;&nbsp;
       			<input id='calcIP' type='button' value='Show Result' ></input>
               		<div id='henrystat'></div>";
	}

}

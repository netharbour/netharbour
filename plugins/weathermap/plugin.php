<?
class WeatherMap {

        //renders the content
        function get_content($post='', $get='') {

		// Include the map config file
		// This files contains all the available weathermaps;
		include_once("plugins/weathermap/map_files.php");
		
		// Now build URL
		$url = $_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID'];
		$map_file = $_GET['map_file'];
		if (is_null($map_file)) {
			// if no map file is defined in the url, then use 1st one
			foreach ($map_files as $map_name => $map_file) {
				if( ($map_name != '') && (is_readable("plugins/weathermap/$map_file"))) {
					break;
				}
			}
		}
		$content = '
			<script type="text/javascript" src="plugins/weathermap/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
			<script language="javascript">
				function refreshDiv() {
					$("#refresher").load("plugins/weathermap/'.$map_file.'");
				}

				$(document).ready(function(){
      					// Run our swapImages() function every 5secs
      					setInterval(\'refreshDiv()\', 5000);
    				});
			</script>';
		$content .= '<div style=" color: black;
 				width: 200px; padding: 1px; padding-right:
				1px;  position: relative; float: left;
				margin-right: 5px;
				clear: both;
			" >';
	
		$content .= "<h3>Available Maps:</h3><ul>";	
		foreach ($map_files as $map_name => $map_file_href) {
			if( ($map_name != '') && (is_readable("plugins/weathermap/$map_file_href"))) {
				$content .= "<li><a href='".$url."&map_file=$map_file_href'>$map_name</a></li>";
			}
		}
		$content .= "</ul></div>";

			$content .= "<div id='refresher'>" .
			file_get_contents("plugins/weathermap/$map_file") .
			"</div><br> <p></p><br> <p></p><br> <p></p><br> <p></p><br> <p></p><br> <p></p>
			<br> <p></p><br> <p></p><br> <p></p><br> <p></p>";
			
			return $content;
        }
}
?>

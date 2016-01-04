<script type="text/javascript" src="js/Ajax.js"></script>
<?

/////////////////////////////////////////////////////
// PHP soap client demonstrating the BGPmon SOAP API
// Andree Toonk, Jan 2010
// 
// Config in config.php
/////////////////////////////////////////////////////

include 'config.php';

class BgpMon {

	var $alert_name = array(
			10 => "Hijack",
    			11 => "Hijack",
    			12 => "Hijack",
    			21 => "BGP MITM",
    			22 => "More Specific",
    			23 => "More Specific",
    			31 => "New Upstream",
    			41 => "Regex Mismatch",
    			60 => "New Prefix",
    			97 => "Withdraws",
    			99 => "Other"
	);

	var $alert_desc = array(
    		"10" => "Origin AS and Prefix changed (more specific) Or Origin AS changed and no valid route object found for this announcement",
    		"11" => "Origin AS and Prefix changed, more specific, Or Origin AS changed.  Valid route object",
    		"12" => "Transit AS and Prefix changed (more specific)",
    		"21" => "Possible MITM BGP attack",
    		"22" => "More specific ",
    		"23" => "Withdraw of More specific detected",
    		"31" => "Transit AS changed (transit AS was not found in list you entered)",
    		"41" => "ASpath Regex did not match ",
    		"60" => "New prefix for your AS ",
    		"97" => "Withdraws detected for your prefix, indicates instability. ",
    		"99" => "Undefined "
	);


	var $asnames = array();
	function get_content() {

 		global $email;
		global $passw;
 		global $wsdl_url;
 		global $proxy;
 		global $days;
 		global $maxcode;
 		global $active;
		global $max_no_alerts;
			
		$asnames = array();

 		// Now we make the call, to read the WSDL file
		//libxml_disable_entity_loader(false);
		//try {
 		$client = new SoapClient(
        		$wsdl_url,
        		$proxy
 		);
		/**
		}  catch (Exception $e) {
			echo 'Error Caught';
		}*/

 		try {
			$content = false;
			$i = 0;
			foreach ($client->getAlerts($email,$passw,$days) as $key => $value) {
				// Cache ASnames
				if (! array_key_exists($value->monitored_AS, $this->asnames)) {
					 $this->asnames[$value->monitored_AS] = $client->getASName($email,$passw,$value->monitored_AS);	
				}
				if ($value->alert_code != 97) {
					if (! array_key_exists($value->origin_AS, $this->asnames)) {
						 $this->asnames[$value->origin_AS] = $client->getASName($email,$passw,$value->origin_AS);
					}
				}


				$url = "https://portal.bgpmon.net/alerts.php?details&amp;alert_id=$value->alert_id";
				if ($value->alert_code != 97) {
					$ttip2 = "Detected prefix: $value->announced_prefix AS$value->origin_AS (".  $this->asnames[$value->origin_AS] . ")<br>";
				} else {
					$ttip2 = "AS$value->monitored_AS (".   $this->asnames[$value->monitored_AS] . ")<br>";
				}
				$ttip1 = $this->alert_desc[$value->alert_code];

				if ($value->cleared == true) {
					$cleared = "Yes";
				} else {
					$cleared = "No";
				}

				$content .= "<tr onclick=\"DoNav('$url)\">";
				$content .= "<td <a class='tooltip' title='$ttip1'>" .
					htmlentities($this->alert_name[$value->alert_code]) ."</a></td>";
				$content .= "<td><a class='tooltip' title='$ttip2'>". 
					htmlentities("$value->monitored_network (AS$value->monitored_AS)") ."</a></td>";
				$content .= "<td>" .htmlentities("$value->date") . "</td>";
				$content .= "<td>" .htmlentities("$cleared") . "</td>";
				$content .= "</tr>";
				
				// Check max number of alerts
				$i++;
				if ($i >= $max_no_alerts) {
					break;
				}
			}
		if ($content) {
			return  "<table class='' id='' >
				<tr><th>Alert Type</th><th>Prefix</th><th>Date</th><th>Cleared</th></tr>
				$content
				</table> ";
		} else {
			return "<b>No Alerts found</b>";
		}
 
 		} catch (SoapFault $exception) {
     			return "<b>SOAP Fault</b><br><pre> $exception</pre>";
 		} 

	}

}
?>

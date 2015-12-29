<?
include_once "classes/Device.php";
include_once 'classes/Form.php';


class SNMPPoller
{
	
	//renders the content
	//function get_content() { }
	
	//renders the configuration
	function get_config($id='')
	{

		// MUST HAVE<input type='hidden' name='id' value=".$id."></input>
		// the name of the property must follow the conventions plugin_<Classname>_<propertyName>
		// have the form post and make sure the submit button is named widget_update
		// make sure there is also a hidden value giving the name of this Class file

		// Get defaults
		$subquery = "select device_id, enabled FROM plugin_SNMPPoller_devices";
		$result2 =  mysql_query($subquery) ;
                if (!$result2)  {
                        return "<b>Oops something went wrong, unable to read plugin_SNMPPoller_devices SQL table </b>";
                }
		$devices = array();
                while ($obj = mysql_fetch_object($result2)){
			$devices[$obj->device_id] = $obj->enabled;
		}
		// Now we have the defaults in $devices;
			
	
		$content .=  "<h1>Please select the Devices you would like to Monitor with the SNMP poller</h1>";

		$content .= "<form id='configForm' method='post' name='edit_devices'>
			<input type='hidden' name='class' value='SNMPPoller'></input>
			<input type='hidden' name='id' value=".$id."></input> ";

		$select_all ="<input name='all' type='checkbox' value='Select All' onclick=\"checkAll(document.edit_devices['devices[]'],this)\"";
		#$content .= "<table border=1><tr><th>$select_all</th><th>Device</th><th>Device Type</th><th>Location</th></tr>";
		$form = new Form("auto",4);
		$keyHandlers = array();
		$keyData = array();
		$keyTitle = array();
		foreach (Device::get_devices() as $id => $name) {
			if ((array_key_exists($id, $devices)) && ($devices[$id] == 1)) {
				$checked = "checked='yes'";
			} else { $checked = "";}
                	$deviceInfo = new Device($id);
			array_push($keyData, "<input type=checkbox name=devices[] value='$id' $checked >");
			array_push($keyData, $name);
			array_push($keyData, $deviceInfo->get_type_name());
			array_push($keyData, $deviceInfo->get_location_name());
			#$content .= "<tr><td><input type=checkbox name=devices[] value='$id' $checked ></td>";
			#$content .= "<td>$name</td><td>". $deviceInfo->get_type_name() ."</td>";
			#$content .= "<td>". $deviceInfo->get_location_name() ."</td></tr>";
		}
		#$content .= "</table> <br>";
        	//get all the device and display them all in the 3 sections "Device Name", "Device Type", "Location".
		            $heading = array($select_all,"Device Name", "Device Type", "Location ");
                $form->setSortable(true); // or false for not sortable
                $form->setHeadings($heading);
                $form->setEventHandler($handler);
                $form->setData($keyData);
                $form->setTableWidth("auto");
                $content .= $form->showForm();

		
		$content .= "<div style='clear:both;'></div><input type='submit' class='submitBut' name='plugin_update' value='Update configuration'/>
			</form> ";
                return "$content";

	}
	
	//updates the configuration, needs to return a true or false value.
	function update_config($values='')
	{
		// 1st delete all
		$subquery = "delete from plugin_SNMPPoller_devices";
		$result2 =  mysql_query($subquery) ;
                if (!$result2)  {
			return false;
                        return "<b>Oops something went wrong, unable to read plugin_SNMPPoller_devices SQL table </b>";
                }
		// Now add all the selected once
		foreach($_POST['devices'] as $key => $devid) {
			// This is a new device, insert
			$update_q = "INSERT INTO  plugin_SNMPPoller_devices 
				SET enabled = '1', device_id = '$devid'";

			$result3 =  mysql_query($update_q) ;
       		         if (!$result3)  {
				return false;
       		               return "<b>Oops something went wrong, unable to update plugin_SNMPPoller_devices SQL table </b>";
			}
		}
		return true;
	}
}
?>

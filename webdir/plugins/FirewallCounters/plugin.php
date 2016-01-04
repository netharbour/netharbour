<?
include_once 'classes/Form.php';
include_once 'classes/Device.php';
include_once 'classes/RRD.php';
include_once('classes/Property.php');


class  FirewallCounters
{
	function get_content() {
		$output = $this->renderFirewallCounterForm(); 
		if(isset($_GET['deviceID'])) { 
			$device_id = $_GET['deviceID'];
			$output .= $this->get_files_for_device($device_id);
		}
		return $output;
	} 

	private function get_files_for_device($device_id) { if (!is_numeric($device_id)) {
			return;
		}
		$selectedDevice = new Device($device_id);

		$output = "<h2>Displaying Counters for: <b>" . $selectedDevice->get_name() . "</b></h2><br>";
		//print $selectedDevice->get_name() . "<br>";
		$property = new Property();
		if ($rrdtool = $property->get_property("path_rrdtool")) {
		} else {
			return;
		}
		if ($rrd_dir = $property->get_property("path_rrddir")) {
		} else {
			return;
		}
		$pattern = "$rrd_dir/fwcounters/fwcounter_deviceid" . $device_id ."_*.rrd";
		$files = glob($pattern);
		foreach ($files as  $v) {
			$path_parts = pathinfo($v);
			$fullPath= "fwcounters/".$path_parts['basename'];
			$fileName = $path_parts['filename'];
			//(\d+)_(.+)$
			$searchPattern = '/fwcounter_deviceid(\d+)_(.+)$/';
			$replacement = $selectedDevice->get_name().' $2';
			$counterName = preg_replace($searchPattern,$replacement, $fileName);

			// If this is an interface-specific counter then show more info about the interface
			$outputPortInfo = "";
			// print strtolower($counterName);
			//ge-0-2-5.0
			$arrPortTypes = array();
			$arrPortTypes[] = "fe"; 
			$arrPortTypes[] = "ge"; 
			$arrPortTypes[] = "xe"; 
			$arrPortTypes[] = "et"; 
		
			$interfaceName = false;

			foreach($arrPortTypes as $k => $v)
			{	
				$interfaceName = strstr($counterName, $v."-");
				if ($interfaceName != false) {
					$interfaceName = strtr($interfaceName,array ('-' => '/'));
					$interfaceName = str_replace($v."/",$v."-",$interfaceName);
					break;
				}
			}
			
			if($interfaceName != false){
				$thisDevice = new Device ($device_id);
				$interfaceID = $thisDevice->get_interface_id_by_name($interfaceName);
				if($interfaceID) {
					$thisPort = new Port($interfaceID);
					$outputPortInfo = "<br>Port description: " . $thisPort->get_alias();
				}
			}
			
			$output .= "<table>
					<tr>
						<td colspan='2'><h3>RRD File: $fileName $outputPortInfo </h3></td>
					</tr>
					<tr>
						<td>";
                        $height = 150;
                        $width= 550;
			$from="-1d";
			if(isset($_GET['From'])) {
				$from=$_GET['From'];
			}

			$graph = "Bits Per Second";
                        $graph = str_replace(" ", "%20", $graph);
                        $type = "traffic";
                        $type = str_replace(" ", "%20", $type);
                        $link ="rrdgraph.php?file=$fullPath&title=".$fileName." --- ".$graph."&height=".$height."&width=".$width."&type=".$type;
			$output .= "<a href='#'><img src='rrdgraph.php?file=$fullPath&title=". $counterName ." --- ".$graph."&from=$from&height=$height&width=$width&type=$type'></a><br><br>"; 
			$output .= "</td><td>";
	
			$graph = "Unicast Packets Per Second";
                        $graph = str_replace(" ", "%20", $graph);
                        $type = "unicastpkts";
                        $type = str_replace(" ", "%20", $type);
                        $link ="rrdgraph.php?file=$fullPath&title=".$fileName." --- ".$graph."&height=".$height."&width=".$width."&type=".$type;
			$output .= "<a href='#'><img src='rrdgraph.php?file=$fullPath&title=". $counterName ." --- ".$graph."&from=$from&height=$height&width=$width&type=$type'></a><br>";

			$output .= "</td></tr><br><hr>"; 
		}
		return $output;
	}

	private function renderFirewallCounterForm() {

                $content = '<h2>Firewall Counters</h2>';

                $allDevices = Device::get_devices();

                //$allDevices = array();
                asort($allDevices);
		$getdeviceID = "";

		if (isset($_GET['deviceID'])) { 
			$getdeviceID = $_GET['deviceID'];
		}

               $getFrom= "-1d";

                if (isset($_GET['From'])) {
                        $getFrom = $_GET['From'];
                }

                $form = new Form("auto",2);
                $values = array();
                $handler = array();
                $titles = array("Device","Time Frame","tab","pluginID" );
                $postKeys = array("deviceID","From","tab","pluginID" );
                array_push ($values,$getdeviceID,$getFrom,$_GET['tab'],$_GET['pluginID']);
                $heading = array("Select location");
                $fieldType[0]= "drop_down";
                $fieldType[1]= "drop_down";
                $fieldType[2]= "hidden";
                $fieldType[3]= "hidden";
                $form->setType($allDevices);
		$allFrom = array();
		$allFrom["-1h"] = "Past 1 Hour";
		$allFrom["-1d"] = "Past 1 Day";
		$allFrom["-1w"] = "Past 1 Week";
		$allFrom["-1y"] = "Past 1 Year";
                $form->setType($allFrom);
                $form->setFieldType($fieldType);
                $form->setSortable(false);
                $form->setHeadings($heading);
                $form->setTitles($titles);
                $form->setData($values);
                $form->setDatabase($postKeys);

                //set the table size
                $form->setTableWidth("100");
                $form->setTitleWidth("20%");
                $form->setUpdateValue("GetCounters");
                $form->setUpdateText("Get Counters");
                $form->setMethod("GET");

                $content .= $form->EditForm(1);
                $content .="<div style=\"clear:both;\"></div> </p>";
                return $content;
        }
}

?>

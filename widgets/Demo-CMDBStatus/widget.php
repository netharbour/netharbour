<?
class CMDBStatus {
	
	function get_content()
	{
		include_once "classes/Contact.php";
		include_once "classes/Device.php";
		include_once "classes/Service.php";
		
		$clients = new Contact();
		$allClients = $clients->get_groups();
		$numClients = count($allClients);
		
		$devices = Device::get_devices();
		$numDevices = count($devices);
		
		$services = Service::get_services(0);
		$numLay2=0;
		$numOran=0;
		$numComm=0;
		$numCU=0;
		$numIx=0;
		foreach($services as $id => $value)
		{
			$curService = new Service($id);
			switch ($curService->get_service_type())
			{
				case 0:
				$numComm++;
				break;
				
				case 1:
				$numOran++;
				break;
				
				case 2:
				$numIx++;
				break;
				
				case 4:
				$numLay2++;
				break;
				
				case 7:
				$numCU++;
				break;
			
			}
		}
	
		$content = "<ul>";
        $content .= "<li>".$numClients." clients</li>".
		  		"<li>".$numDevices." devices</li>".
				"<li>".$numCU." customer logical router (CU_ALL) Service</li>".
				"<li>".$numComm." internet IP Transit (commodity) Service</li>".
				"<li>".$numLay2." layer 2 vlan service (l2_vlan) Service</li>".
				"<li>".$numOran." oran IP service (oran) Service</li>".
				"<li>".$numIx." transit Exchange Peering (ix) Service</li>";
        $content .= "</ul>";
		return $content;
	}
}
?>

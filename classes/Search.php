<?
//Search through database
//by Henry v1.0

class Search{
	
	private $keyword;
	private $serResults = array();
	private $devResults = array();
	private $intResults = array();
	private $conResults = array();
	private $staResults = array();
	private $locResults = array();
	
	private $error;
	
	function __construct() {
	}
	
	function set_keyword($keyword){$this->keyword = $keyword;}
	
	function get_keyword(){return $this->keyword;}
	
	
	function get_service_results(){return $this->serResults;}
	
	function get_device_results(){return $this->devResults;}
	
	function get_interface_results(){return $this->intResults;}
	
	function get_contact_results(){return $this->conResults;}
	
	function get_statistic_results(){return $this->staResults;}
	
	function get_location_results(){return $this->locResults;}
	
	function get_error(){return $this->error;}
	
	function search_database()
	{
		if ($this->keyword == '')
		{return false;}
		$keyword = rtrim($this->keyword);
		$keyword = ltrim($keyword);
		//$keyArray = explode(" ", $keyword);
		
		$keyword = "%".$keyword."%";
		$query = "SELECT Devices.device_id, Devices.archived, Devices.name, Devices.notes FROM Devices WHERE (Devices.name LIKE '".$keyword."' || Devices.notes LIKE '".$keyword."')";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_array($result)){
			array_push($this->devResults, $obj);
		}
		
		$query = "SELECT Services.service_id, Services.archived, Services.name, Services.notes, Services.cust_id, 
				contact_groups.group_name as username FROM Services, contact_groups 
				WHERE 
				Services.cust_id = contact_groups.group_id
				AND (Services.name LIKE '%".$keyword."%' || Services.notes LIKE '%".$keyword."%')";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_array($result)){
			array_push($this->serResults, $obj);
		}

		$query = "SELECT DISTINCT Services.service_id, Services.archived, Services.name, Services.notes, 
			contact_groups.group_name as username, L3_service_details.IPv4_prefixes, L3_service_details.IPv6_prefixes, 
			L3_service_details.BCNETrouterAddress4, L3_service_details.CustrouterAddress4, 
			L3_service_details.BCNETrouterAddress6, L3_service_details.CustrouterAddress6, 
			L3_service_details.bgp_as FROM Services, contact_groups, L3_service_details, Services_Interfaces
			WHERE 
			(L3_service_details.l3_service_id = Services.l3_service_id) 
			AND Services.cust_id = contact_groups.group_id
			AND Services.service_id = Services_Interfaces.service_id
			AND (L3_service_details.IPv4_prefixes LIKE '".$keyword."' || L3_service_details.IPv6_prefixes LIKE '".$keyword."' || L3_service_details.BCNETrouterAddress4 LIKE '".$keyword."' || L3_service_details.CustrouterAddress4 LIKE '".$keyword."' || L3_service_details.BCNETrouterAddress6 LIKE '".$keyword."' || L3_service_details.CustrouterAddress6 LIKE '".$keyword."' || L3_service_details.bgp_as LIKE '".$keyword."' || Services_Interfaces.interface_name LIKE '".$keyword."')";

		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_array($result)){
			array_push($this->serResults, $obj);
		}
		
		$query = "SELECT DISTINCT L2_service_details.vlan, Services.archived, Services.service_id, Services.name, Services.notes, Services.cust_id, contact_groups.group_name as username FROM Services, contact_groups, L2_service_details, Services_Interfaces WHERE (L2_service_details.l2_service_id = Services.l2_service_id) AND Services.service_id = Services_Interfaces.service_id AND Services.cust_id = contact_groups.group_id AND (L2_service_details.vlan LIKE '".$keyword."' || Services_Interfaces.interface_name LIKE '".$keyword."')";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_array($result)){
			array_push($this->serResults, $obj);
		}
		
		/*FOR INTERFACES*/
		$query = "SELECT Devices.device_id, Devices.archived, Devices.name, interface_device, interface_id, interface_name, interface_descr, interface_alias FROM interfaces, Devices WHERE Devices.device_id = interfaces.interface_device AND interfaces.active = '1' AND (interface_name LIKE '".$keyword."' || interface_descr LIKE '".$keyword."' || interface_alias LIKE '".$keyword."') ORDER BY interface_name";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		
 		while ($obj = mysql_fetch_array($result)){
			array_push($this->intResults, $obj);
		}
		
		$query = "SELECT * FROM interface_IPaddresses, interfaces, Devices WHERE interface_IPaddresses.device_id = Devices.device_id AND Devices.device_id = interfaces.interface_device AND interface_IPaddresses.if_index = interfaces.disc_interface_index AND interfaces.active = '1' AND interface_IPaddresses.inet_address LIKE '".$keyword."' ORDER BY interface_name";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		
		while ($obj = mysql_fetch_array($result)){
			array_push($this->intResults, $obj);
		}
		
		$query = "SELECT * FROM contact_groups WHERE (group_name LIKE '".$keyword."' || group_desc LIKE '".$keyword."' || custom_client_id LIKE '".$keyword."' || custom_client_group_id LIKE '".$keyword."') ORDER BY group_name";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_array($result)){
			array_push($this->conResults, $obj);
		}
		
		$query = "SELECT * FROM contacts WHERE (name_first LIKE '".$keyword."' || name_middle LIKE '".$keyword."' || name_last LIKE '".$keyword."' || country LIKE '".$keyword."' || province LIKE '".$keyword."' || city LIKE '".$keyword."' || addr_line1 LIKE '".$keyword."' || addr_line2 LIKE '".$keyword."' || zipcode LIKE '".$keyword."' || phone1 LIKE '".$keyword."' || phone2 LIKE '".$keyword."' || phone_cell LIKE '".$keyword."' || phone_pager LIKE '".$keyword."' || phone_fax LIKE '".$keyword."' || email LIKE '".$keyword."' || notes LIKE '".$keyword."' || external_id1 LIKE '".$keyword."' || external_id2 LIKE '".$keyword."' || external_id3 LIKE '".$keyword."') ORDER BY name_last";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_array($result)){
			array_push($this->conResults, $obj);
		}
		
		$query = "SELECT * FROM pop_locations WHERE (location_name LIKE '".$keyword."' || location_desc LIKE '".$keyword."' || location_country LIKE '".$keyword."' || location_province LIKE '".$keyword."' || location_city LIKE '".$keyword."' || location_addr_line1 LIKE '".$keyword."' || location_addr_line2 LIKE '".$keyword."' || location_zip_code LIKE '".$keyword."' || location_type LIKE '".$keyword."') ORDER BY location_name";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_array($result)){
			array_push($this->locResults, $obj);
		}
		
		$query = "SELECT * FROM pop_rooms WHERE (room_name LIKE '".$keyword."' || room_desc LIKE '".$keyword."' || room_no LIKE '".$keyword."') ORDER BY room_name";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_array($result)){
			array_push($this->locResults, $obj);
		}
	}
}

?>

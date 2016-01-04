<?
include_once 'Netblock.php';

class IP_Database
{
	//create the variables
	private $error = false;
	private $netblock_id;
	private $address_int;
	private $address_ip;
	private $subnet_size;
	private $description;
	private $title;
	private $family;
	private $is_stub = 0;
	private $parent_id;
	private $status = "FREE";
	private $owner=NULL;
	private $assigned_to = NULL;
	private $class_id = NULL;
	private $location = NULL;
	private $tags = array();
	
	private $host_ip;
	
	//constructor
	function __construct($netblock_id = "")
	{
		if (is_numeric($netblock_id)) {
			$this->get_netblock_info($netblock_id);
			if ($this->netblock_id == '') {
				$this->error = "Netblock ID not found";
				return false;
			}
		}
	}
	
	//gets the netblock information
	private function get_netblock_info($netblock_id) {
		
		$query = "SELECT * FROM ipmanager_netblocks WHERE netblock_id = '".$netblock_id."'";
		// execute the query 
		
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		
		while ($obj = mysql_fetch_object($result)){
			$this->netblock_id =  $obj->netblock_id;
			$this->address_int =  $obj->base_addr;
			$this->subnet_size =  $obj->subnet_size;
			$this->description =  $obj->description;
			$this->title = $obj->title;
			$this->family =  $obj->family;
			$this->is_stub = $obj->stub;
			$this->parent_id =  $obj->parent;
			$this->status =  $obj->status;
			$this->owner =  $obj->owner;
			$this->assigned_to =  $obj->assigned_to;
			$this->class_id =  $obj->class;
			$this->location =  $obj->location;
		//	$this->tags =  $obj->tags;
		}
		
		$query_t = "SELECT tag_name FROM ipmanager_tags_netblock WHERE netblock_id = '".$this->netblock_id."'";
		$result_t = mysql_query($query_t) or die('Error, query failed. ' . mysql_error());
		
		while ($obj = mysql_fetch_object($result_t)){
			array_push($this->tags, $obj->tag_name);
		}
		
		return true;
	}
	
	//----------------------------------SET METHODS-----------------------------------------------------//
	function set_netblock_id($id){$this->netblock_id = $id;}
	
	function set_address_int($str){$this->address_int = $str;}
	
	function set_subnet_size($size){$this->subnet_size = $size;}
	
	function set_description($desc){$this->description = $desc;}
	
	function set_title($title){$this->title = $title;}
	
	function set_family($fam){$this->family = $fam;}
	
	function set_parent_id($id){$this->parent_id = $id;}
	
	function set_status($stat){$this->status = $stat;}
	
	function set_owner_id($id){$this->owner = $id;}
	
	function set_assigned_to_id($id){$this->assigned_to = $id;}
	
	function set_location_id($id){$this->location = $id;}
	
	function set_class_id($id){$this->class_id = $id;}
	
	function set_stub($id){$this->is_stub = $id;}
	
	function set_tags($tags){
		if(is_array($tags))
		{
			$this->tags = $tags;
		}
		else {
			$this->tags = explode(",", $tags);
		}
	}
	
	//----------------------------------GET METHODS-----------------------------------------------------//
	function get_netblock_id(){return $this->netblock_id;}
	
	function get_address_int(){return $this->address_int;}
	
	function get_address_ip(){
		$netblock = new Netblock();
		$netblock->set_IP($this->address_int."/".$this->subnet_size, $this->family);
		return $netblock->get_IP();	
	}
	
	function get_subnet_size(){return $this->subnet_size;}
	
	function get_description(){return $this->description;}
	
	function get_title(){return $this->title;}
	
	function get_family(){return $this->family;}
	
	function get_parent_id(){return $this->parent_id;}
	
	function get_status(){return $this->status;}
	
	function is_stub(){return $this->is_stub;}
	
	function get_owner_id(){return $this->owner;}
	
	function get_owner_name(){
		if ($this->owner === NULL)
		{
			return "";	
		}
		$owner = new Contact($this->owner);
		return $owner->get_name();	
	}
	
	function get_assigned_to_id(){return $this->assigned_to;}
	
	function get_assigned_to_name(){
		if ($this->assigned_to === NULL)
		{
			return "";	
		}
		$assigned_to = new Contact($this->assigned_to);
		return $assigned_to->get_name();	
	}
	
	function get_location_id(){return $this->location;}
	
	function get_location_name(){
		if ($this->location === NULL)
		{
			return "";	
		}
		$location = new Location($this->location);
		return $location->get_name();
	}
	
	function get_class_id(){return $this->class_id;}
	
	function get_tags(){return $this->tags;}
	
	function get_error(){return $this->error;}
	
	//insert a new ip netblock into the database
	function insert($type="split") {
		
		// Test mandatory fields
		if ($this->netblock_id != '') {
			$this->error = "This is an insert, netblock_id should be empty";
			return false;
		}
		
		if ($this->address_int == '') {
			$this->error = "address can not be empty";
			return false;
		}
		
		if ($this->family == '')
		{
			$this->error = "family can not be empty";
			return false;
		}
		
		if ($this->subnet_size == '') {
			if($this->family == 4){$this->subnet_size=32;}
			else if($this->family == 6){$this->subnet_size=128;}
		}
		
		if ($this->parent_id == '')
		{
			$query = "INSERT INTO ipmanager_netblocks SET 
			base_addr = '".$this->address_int."', 
			subnet_size = '".$this->subnet_size."', 
			description = '".$this->description."', 
			family = '".$this->family."', 
			title = '".$this->title."', ";
			
			if ($this->location != "")
			{
				$query .= "location = '".$this->location."', ";
			}
			
			if ($this->owner != "")
			{
				$query .= "owner = '".$this->owner."', ";
			}
			
			if ($this->assigned_to != "")
			{
				$query .= "assigned_to = '".$this->assigned_to."', ";
			}
			
			if ($this->status != "")
			{
				$query .= "status = '".$this->status."', ";
			}
		// Now execute query
		}
		else if(is_numeric($this->parent_id))
		{
			
			//depending on type different netblocks insertion methods will execute
			if($type == "host")
			{
				$stub = 1;
			
				$query = "UPDATE ipmanager_netblocks SET stub = '".$stub."' WHERE netblock_id ='".$this->parent_id."'";
				$results = mysql_query($query) or die(mysql_error());
			}
			
			$query = "INSERT INTO ipmanager_netblocks SET 
			base_addr = '".$this->address_int."', 
			subnet_size = '".$this->subnet_size."', 
			description = '".$this->description."', 
			family = '".$this->family."', 
			title = '".$this->title."', 
			parent = '".$this->parent_id."', ";
			
			if ($this->location != "")
			{
				$query .= "location = '".$this->location."', ";
			}
			
			if ($this->owner != "")
			{
				$query .= "owner = '".$this->owner."', ";
			}
			
			if ($this->assigned_to != "")
			{
				$query .= "assigned_to = '".$this->assigned_to."', ";
			}
			
			if ($this->status != "")
			{
				$query .= "status = '".$this->status."', ";
			}
			// Now execute query
		}
		else
		{
			$this->error = "Invalid parent ID";
			return false;	
		}
		
		$query = rtrim($query, ", ");
		
		if (! $result = mysql_query($query)) {
			$this->error = "Could not add new user $query ... query failed : " . mysql_error();
			return false;
		} else {
			$new_id = mysql_insert_id();
			$this->netblock_id =  $new_id;
			
			//manages the tags for inserting and deleting netblokcs
			if (!empty($this->tags))
			{
				
				$queryFIND = "DELETE FROM ipmanager_tags_netblock WHERE netblock_id = '".$this->netblock_id."'";
				$resultFIND = mysql_query($queryFIND);
				
				foreach ($this->tags as $t_id => $t_name)
				{
					$t_name = trim($t_name, " ");
					
					if($t_name == "")
					{
						continue;	
					}
					
					$queryINSERT = "INSERT INTO ipmanager_tags_netblock SET
					tag_name = '".$t_name."', 
					netblock_id = '".$this->netblock_id."'";
						
					$resultTAG = mysql_query($queryINSERT);
					if (!$resultTAG) {
						$this->error = "Could not add new group $query ... query failed : " . mysql_error();
						return false;
					}
				}
			}
			return $this->netblock_id;
		}
	}
	
	//update the netblock
	function update() {
		
		//test mandatory fields
		if (!is_numeric($this->netblock_id)) {
			$this->error = "Invalid group id";
			return false;
		}
		
		//filters informationfor the query  based on what is given
		$query = "UPDATE ipmanager_netblocks SET ";
		
		if (isset($this->description))
		{
			$query .= "description = '".$this->description."', ";
		}
		
		if (isset($this->title))
		{
			$query .= "title = '".$this->title."', ";
		}
		
		if (isset($this->location) && $this->location != "NULL") {
			$query .= "location = '".$this->location."', ";
		}
		else if ($this->location =="NULL")
		{
			$query .= "location = NULL, ";
		}
		
		if (isset($this->owner) && $this->owner != "NULL") {
			$query .= "owner = '".$this->owner."', ";
		}
		else if ($this->owner =="NULL")
		{
			$query .= "owner = NULL, ";
		}
		
		if (isset($this->assigned_to) && $this->assigned_to != "NULL") {
			$query .= "assigned_to = '".$this->assigned_to."', ";
		}
		else if ($this->assigned_to == "NULL")
		{
			$query .= "assigned_to = NULL, ";
		}
		
		if (isset($this->status))
		{
			$query .= "status = '".$this->status."', ";
		}
		
		if (isset($this->is_stub))
		{
			$query .= "stub = '".$this->is_stub."', ";
		}
		
		if (!empty($this->tags))
		{
			
			$queryFIND = "DELETE FROM ipmanager_tags_netblock WHERE netblock_id = '".$this->netblock_id."'";
			$resultFIND = mysql_query($queryFIND);
			
			foreach ($this->tags as $t_id => $t_name)
			{
				$t_name = trim($t_name, " ");
				$t_name = str_replace("<", "", $t_name);
				$t_name = str_replace(">", "", $t_name);
				$t_name = str_replace("\"", "", $t_name);
				$t_name = str_replace("\'", "", $t_name);
				
				
				if($t_name == "")
				{
					continue;	
				}
				
				$queryINSERT = "INSERT INTO ipmanager_tags_netblock SET
				tag_name = '".$t_name."', 
				netblock_id = '".$this->netblock_id."'";
					
				$resultTAG = mysql_query($queryINSERT);
				if (!$resultTAG) {
					$this->error = "Could not add new group $query ... query failed : " . mysql_error();
					return false;
				}
			}
		}
		
		$query = rtrim($query, ", ");

		$query .= " WHERE netblock_id ='".$this->netblock_id."'";
		
		$result = mysql_query($query);
			
		if (!$result) {
			$this->error = "Could not add new group $query ... query failed : " . mysql_error();
			return false;
		} else {
			return $result;
		}
	}
	
	//functions taken out
	/*function add_ip_to_db($long_ip, $subnet, $desc, $family)
	{
		if (isset($long_ip) && isset($subnet) && isset($family) && isset($desc))
		{
			$query = "INSERT INTO ipmanager_netblocks (base_addr, subnet_size, description, family) VALUES ('".$long_ip."', '".$subnet."', '".$desc."', '".$family."')";
			mysql_query($query) or die(mysql_error());
		}
		else
		{
			print "NO IP or subnet size or family";	
		}
	}
	
	function add_split_to_db($long_ip, $subnet, $desc, $family, $parent)
	{
		if (isset($long_ip) && isset($subnet) && isset($family) && isset($desc) && isset($parent))
		{
			$query = "INSERT INTO ipmanager_netblocks (base_addr, subnet_size, description, family, parent) VALUES ('".$long_ip."', '".$subnet."', '".$desc."', '".$family."', '".$parent."')";
			mysql_query($query) or die(mysql_error());
		}
		else
		{
			print "NO IP or subnet size or family";	
		}
	}*/
	
	//returns all the ip netblocks and sorts them based on parent netblock or family
	function get_all_ip($parent = NULL, $family = NULL)
	{
		$all_ip = array();
		
		if ($parent === NULL)
		{
			$parent_query = "parent IS NULL";
		}
		else if (is_numeric($parent))
		{
			$parent_query = "parent = '".$parent."'";
		}
		else if ($parent == "ALL")
		{
			$parent_query = "1";
		}
		else
		{
			$error;	
			return false;
		}
		
		$family_query = "1";
		
		if($family == 4)
		{
			$family_query = "family = '4'";
		}
		else if ($family == 6)	
		{
			$family_query = "family = '6'";
		}
		
		$query = "SELECT netblock_id, base_addr, subnet_size FROM ipmanager_netblocks WHERE ".$parent_query." AND ".$family_query."";
		
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			#$this->error = mysql_error();
			return false;
		}
		
		$n_result = array();
		
		if (mysql_num_rows($result)==0)
		{
			return $n_result;
		}
		
		$index=array();
		
		$t_ip_arr = array();
		
		//substring the netblock into 4 different 32 bits and multi sort all 4 of these
 		while ($obj = mysql_fetch_object($result)){
			//array_push($t_ip_arr, array("netblock"=>$obj->netblock_id, "addr"=>$obj->base_addr, "subnet"=>$obj->subnet_size));
			$addr = str_pad($obj->base_addr, 40, 0, STR_PAD_LEFT);
			$str1 = substr($addr, 0, 10);
			$str2 = substr($addr, 9, 10);
			$str3 = substr($addr, 19, 10);
			$str4 = substr($addr, 29, 10);
			$str5 = substr($addr, 39, 10);
			
			//give the argument for sorting
			$all_ip[$obj->netblock_id] = array("real_addr"=>$obj->base_addr, "addr1"=>$str1, "addr2"=>$str2, "addr3"=>$str3, "addr4"=>$str4, "addr5"=>$str5, "subnet"=>$obj->subnet_size);
			$index[$obj->base_addr."/".$obj->subnet_size] = $obj->netblock_id;
		}
		
		//rearrange the result and put them in again based on the sorting argument
		$s_result = IP_Database::multisort($all_ip, array("addr1", "addr2", "addr3", "addr4", "addr5", "subnet"));
		
		foreach ($s_result as $id=>$content)
		{
			//$addr = explode(".", bcmul($content["addr"],100000));
			$ip_id = $index[$content["real_addr"]."/".$content["subnet"]];
			$n_result[$ip_id] = $content["real_addr"]."/".$content["subnet"];
		}
		
		//print_r($s_result);
		//print_r($n_result);
		return $n_result;
	}
	
	//gets all the tags
	function get_all_tags()
	{
		$query = "SELECT * FROM `ipmanager_tags_netblock`";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		
		$tags=array();
		
 		while ($obj = mysql_fetch_object($result)){
			array_push($tags, $obj->tag_name);
		}
		
		return $tags;
	}
	
	//dropped function, only used for testing
	function get_ip_by_ip($ip)
	{
		if (!isset($ip))
		{
			print "NO ip";
			exit;
		}
		
		$query = "SELECT * FROM ipmanager_netblocks WHERE base_addr = '".$ip."'";
		
		$results = mysql_query($query) or die(mysql_error());
		
		$ip_result = mysql_fetch_array( $results );
		return $ip_result;
	}
	
	//dropped function, only used for testing
	function get_ip_by_id($id)
	{
		if (!isset($id))
		{
			print "NO id";
			exit;
		}
		
		$query = "SELECT * FROM ipmanager_netblocks WHERE netblock_id = '".$id."'";
		
		$results = mysql_query($query) or die(mysql_error());
		
		$ip_result = mysql_fetch_array( $results );
		return $ip_result;
	}
	
	//the function to multisort
	function multisort($array, $sort_by) {
		foreach ($array as $key => $value) {
			$evalstring = '';
			foreach ($sort_by as $sort_field) {
				$tmp[$sort_field][$key] = $value[$sort_field];
				$evalstring .= '$tmp[\'' . $sort_field . '\'], ';
			}
		}
		$evalstring .= '$array';
		$evalstring = 'array_multisort(' . $evalstring . ');';
		eval($evalstring);
	
		return $array;
	}
	
	//checks if the ip is a parent of another ip
	function is_parent($id)
	{
		$parent = false;
		if (!is_numeric($id))
		{
			print "NO id";
			exit;
		}
		
		$query = "SELECT parent FROM ipmanager_netblocks WHERE parent = '".$id."'";
		
		$results = mysql_query($query) or die(mysql_error());
		
		if (mysql_num_rows($results)!=0)
		{
			$parent = true;
		}
		return $parent;
	}
	
	//gets the parent ip id
	function get_parent($id)
	{
		if (!is_numeric($id))
		{
			print "NO id";
			exit;
		}
		
		$parent_id = false;
		$query = "SELECT parent FROM ipmanager_netblocks WHERE netblock_id = '".$id."'";
		
		$results = mysql_query($query) or die(mysql_error());
		
		while ($obj = mysql_fetch_object($results))
		{
			$parent_id = $obj->parent;
		}
		return $parent_id;
	}
	
	//gets the master ip id
	function get_master($id)
	{
		if (!is_numeric($id))
		{
			print "NO id";
			exit;
		}
		$c_id = 0;
		$query = "SELECT netblock_id, parent FROM ipmanager_netblocks WHERE netblock_id = '".$id."'";
		$results = mysql_query($query) or die(mysql_error());
		while ($obj = mysql_fetch_object($results))
		{
			$c_id = $obj->netblock_id;
			$n_id = $obj->parent;
		}
		
		
		while ($n_id != NULL)
		{
			$query = "SELECT netblock_id, parent FROM ipmanager_netblocks WHERE netblock_id = '".$n_id."'";
			$results = mysql_query($query) or die(mysql_error());
			while ($obj = mysql_fetch_object($results))
			{
				$c_id = $obj->netblock_id;
				$n_id = $obj->parent;
			}
		}
		
		return $c_id;
		
	}
	
	//removes a netblock
	function remove($force = false)
	{	
		//test mandatory fields
		if (!is_numeric($this->netblock_id))
		{
			print "NO id";
			exit;
		}
		
		if ($force == true)
		{
			$query = "DELETE FROM ipmanager_netblocks WHERE netblock_id = '".$this->netblock_id."'";
			$results = mysql_query($query) or die(mysql_error());
			return true;
		}
		
		//if the ip is a parent then change the netblock into free instead of removing it
		if (IP_Database::is_parent($this->netblock_id))
		{
			
			$query = "SELECT * FROM ipmanager_netblocks WHERE parent = '".$this->netblock_id."'";
			$results = mysql_query($query) or die(mysql_error());
			
			//checks if all the childs are free
			if($results)
			{
				while ($obj = mysql_fetch_object($results)){
					$ip_calc = new Netblock();
					$ip_calc->set_IP($obj->base_addr."/".$obj->subnet_size, $obj->family);
					if (IP_Database::is_parent($obj->netblock_id))
					{
						$this->error = "Please delete the child within first. Please look at ". $ip_calc->get_IP();
						return false;
					}
					if($obj->status != "FREE")
					{
						$this->error = "All children of the network needs to be FREE. Please look at ". $ip_calc->get_IP();
						return false;
					}
				}
			}
			
			//delete the netblocks childs
			$query = "DELETE FROM ipmanager_netblocks WHERE parent = '".$this->netblock_id."'";
			$results = mysql_query($query) or die(mysql_error());
			
			if ($this->parent_id === NULL)
			{
				$query = "DELETE FROM ipmanager_netblocks WHERE netblock_id = '".$this->netblock_id."'";
				$results = mysql_query($query) or die(mysql_error());
			}
			else
			{
				$query = "UPDATE ipmanager_netblocks SET title= '', description='', owner = NULL, location = NULL, assigned_to = NULL,  status = 'FREE', stub = '0' WHERE netblock_id = '".$this->netblock_id."'";
				$results = mysql_query($query) or die(mysql_error());
			}
		}
		//if its not a parent then do this
		else if (!IP_Database::is_parent($this->netblock_id))
		{
			//if it doesn't have any parent as well delete it
			if ($this->parent_id === NULL)
			{
				$query = "DELETE FROM ipmanager_netblocks WHERE netblock_id = '".$this->netblock_id."'";
			}
			//else just make it a free netblock
			else
			{
				$query = "UPDATE ipmanager_netblocks SET title= '', description='', owner = NULL, location = NULL, assigned_to = NULL, status = 'FREE', stub = '0' WHERE netblock_id = '".$this->netblock_id."'";
			}
			$results = mysql_query($query) or die(mysql_error());
		}
		
		return true;
	}
	
	//dropped function for testing only
	/*function assign_ip_by_id($id, $status)
	{
		if (!isset($id))
		{
			print "NO id";
			exit;
		}
		$query = "UPDATE ipmanager_netblocks SET status = '".$status."' WHERE netblock_id ='".$id."'";
		$results = mysql_query($query) or die(mysql_error());
	}*/
	
	//searches the databse for a netblock with same arguments
	function search($name="", $tags = array(), $location ="", $owner="", $assigned_to="", $status="", $family="")
	{
		//Name = string
		//tags = array()
		//location = id 
		// owner = id
		// assigned_to = id, 
		// status = string
		//HAVE TO CHANGE THIS
		
		$t_query = "SELECT * FROM ipmanager_tags_netblock";
		$t_results = mysql_query($t_query);
		
		if(mysql_num_rows($t_results)!=0)
		{
			$query = "SELECT DISTINCT ipmanager_netblocks.netblock_id  FROM ipmanager_netblocks, ipmanager_tags_netblock WHERE ";
		}
		else
		{
			$query = "SELECT DISTINCT ipmanager_netblocks.netblock_id  FROM ipmanager_netblocks WHERE ";
		}
		
		
		$w_query = "";
		if ($name != "")
		{
			$w_query .= "ipmanager_netblocks.title LIKE '%".$name."%' AND ";
		}
		
		if (!empty($tags))
		{
			$w_query .="(";
			foreach ($tags as $t_id => $t_name)
			{
				$w_query.= "ipmanager_tags_netblock.tag_name = '".$t_name."' OR ";
			}
			$w_query = rtrim($w_query, " OR ");
			$w_query .=")";
			$w_query .= " AND ipmanager_tags_netblock.netblock_id = ipmanager_netblocks.netblock_id AND ";
		}
		
		if ($location != "" && is_numeric($location))
		{
			$w_query .= "ipmanager_netblocks.location = '".$location."' AND ";
		}
		
		if ($owner != "" && is_numeric($owner))
		{
			$w_query .= "ipmanager_netblocks.owner = '".$owner."' AND ";
		}
		
		if ($assigned_to != "" && is_numeric($assigned_to))
		{
			$w_query .= "ipmanager_netblocks.assigned_to = '".$assigned_to."' AND ";
		}
		
		if ($status != "")
		{
			$w_query .= "ipmanager_netblocks.status = '".$status."' AND ";
		}
		
		if ($family != "")
		{
			$w_query .= "ipmanager_netblocks.family = '".$family."' AND ";
		}
		
		$w_query = rtrim($w_query, " AND ");
		
		if( $w_query =="")
		{
			$w_query .= " '1'";
		}
		
		$query = $query.$w_query;
		$results = mysql_query($query);
		if (!$results)
		{
			$this->error = "QUERY FAILED ".$query." ".mysql_error();	
			return false;
		}
		
		$n_id = array();
		while ($obj = mysql_fetch_object($results))
		{
			$n_id[$obj->netblock_id] = $obj->title;
		}
		
		return $n_id;
	}
}
?>
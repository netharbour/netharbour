<?
class Vlan_database
{
	private $error = false;
	private $id = NULL;
	private $vlan_id = false;
	private $name;
	private $notes;
	private $status;
	private $assigned_to = NULL;
	private $class_id = NULL;
	private $location = NULL;
	private $vlan_distinguisher = "";
	private $tags = array();
	
	function __construct($id = "") {
		if (is_numeric($id)) {
			return $this->get_vlan_info($id);
			if ($this->vlan_id === false) {
				return false;
			}
		}
	}
	
	private function get_vlan_info($id) {
		
		$query = "SELECT * FROM ipmanager_vlans WHERE id = '$id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() ."   -- query: $query ";
			return false;
		}
		if (mysql_numrows($result) < 1 ) {
			$this->error = "Vlan not found";
			return false;
		}

		
		while ($obj = mysql_fetch_object($result)){
			$this->id = $obj->id;
			$this->vlan_id =  $obj->vlan_id;
			$this->name =  $obj->name;
			$this->notes =  $obj->notes;
			$this->status =  $obj->status;
			$this->assigned_to = $obj->assigned_to;
			$this->class_id =  $obj->class_id;
			$this->location =  $obj->location_id;
			$this->vlan_distinguisher = $obj->vlan_distinguisher;

		}

		// Now that we have all the base info, let's retrieve the tags
		$query = "SELECT tag FROM ipmanager_vlans_tags WHERE id = '$id'";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() ."   -- query: $query ";
			return false;
		}

		while ($obj = mysql_fetch_object($result)){
			array_push($this->tags, $obj->tag);
		}
		return true;
	}
	function set_vlan_id($id){$this->vlan_id = $id;}
	
	function set_name($str){$this->name = $str;}
	
	function set_notes($notes){$this->notes = $notes;}
	
	function set_status($stat){$this->status = $stat;}
	
	function set_assigned_to($id){$this->assigned_to = $id;}
	
	function set_location($id){$this->location = $id;}
	
	function set_class($id){$this->class_id = $id;}
	
	function set_tags($tags=array()){$this->tags = $tags;}
	
	function set_vlan_distinguisher($vlan_distinguisher){$this->vlan_distinguisher = $vlan_distinguisher;}

	
	//----------------------------------GET METHODS-----------------------------------------------------//
	function get_id(){return $this->id;}
	
	function get_vlan_id(){return $this->vlan_id;}
	
	function get_name(){return $this->name;}
	
	function get_notes(){return $this->notes;}
	
	function get_status(){return $this->status;}
	
	function get_assigned_to(){return $this->assigned_to;}
	
	function get_assigned_to_name(){
		if ($this->assigned_to === NULL)
		{
			return "";	
		}
		$assigned_to = new Contact($this->assigned_to);
		return $assigned_to->get_name();	
	}
	
	function get_location(){return $this->location;}
	
	function get_location_name(){
		if ($this->location === NULL)
		{
			return "";	
		}
		$location = new Location($this->location);
		return $location->get_name();
	}
	
	function get_class(){return $this->class_id;}
	
	function get_tags(){return $this->tags;}
	
	function get_vlan_distinguisher(){return $this->vlan_distinguisher;}
	
	function get_error(){return $this->error;}
	
	function get_all_vlans() {
		$all_vlans = array();
		
		$query = "SELECT id, vlan_id FROM ipmanager_vlans ";
		
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			$this->error = "Unable to retrieve vlans. $query ". mysql_error();
			return false;
		}
		
 		while ($obj = mysql_fetch_object($result)){
			$all_vlans[$obj->id] = $obj->vlan_id;
		}
		
		return $all_vlans;
	}
	function get_all_tags() {
		$all_tags = array();
		$query = "SELECT distinct tag FROM ipmanager_vlans_tags ";
		
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			$this->error = "Unable to retrieve vlans. $query ". mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			array_push($all_tags, $obj->tag);
		}
		
		return $all_tags;
	}
	
	function update() {
		
		if (!is_numeric($this->id)) {
			$this->error = "Invalid id";
			return false;
		}
		
		if (!is_numeric($this->vlan_id)) {
			$this->error = "Invalid vlan id";
			return false;
		}
		
		$query = "UPDATE ipmanager_vlans SET ";
		
		if (isset($this->name))
		{
			$query .= "name = '".$this->name."', ";
		}
		
		if (isset($this->notes))
		{
			$query .= "notes = '".$this->notes."', ";
		}
		
		if (isset($this->location) && $this->location != "NULL") {
			$query .= "location_id = '".$this->location."', ";
		}
		else if ($this->location =="NULL")
		{
			$query .= "location = NULL, ";
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
		
		if (isset($this->vlan_distinguisher))
		{
			$query .= "vlan_distinguisher = '".$this->vlan_distinguisher."', ";
		}
		
		
		$query = rtrim($query, ", ");

		$query .= " WHERE id ='".$this->id."'";
		
		$result = mysql_query($query);
			
		if (!$result) {
			$this->error = "Could not update vlan database: $query ... query failed : " . mysql_error();
			return false;
		} else {
			
			// Now add tags
			// delete old tags and then add new tags for this vlan
			$query = "delete from ipmanager_vlans_tags where id = '$this->id'";
			$result = mysql_query($query);
			if (!$result) {
				$this->error = "Could not add remove old tags for vlan $query ... query failed : " . mysql_error();
				return false;
			}
			// now add new tags
			foreach ($this->tags as $tag_name) {
				$query = "insert into ipmanager_vlans_tags SET
						id = '$this->id',
						tag = '$tag_name'";
				$result = mysql_query($query);
				if (!$result) {
					$this->error = "Could not add remove old tags for vlan $query ... query failed : " . mysql_error();
					return false;
				}
			}

		}
		return true;
	}

	function delete()
	{	
		if (!is_numeric($this->id))
		{
			$this->error= "invalid id";
			return false;
		}
		
		$query = "DELETE FROM ipmanager_vlans 
			WHERE id = '".$this->id."'";

		$result = mysql_query($query);
		if (!$result)  {
			$this->error = "Unable to delete vlan. $query ".  mysql_error();
			return false;
		}
		
		return true;
	}

	function insert()
	{	
		if (!is_numeric($this->vlan_id)) {
			$this->error = "Invalid vlan id";
			return false;
		}
		
		$query = "INSERT INTO ipmanager_vlans SET ";
		
		if (isset($this->vlan_id))
		{
			$query .= "vlan_id = '".$this->vlan_id."', ";
		}
		
		if (isset($this->name))
		{
			$query .= "name = '".$this->name."', ";
		}
		
		if (isset($this->notes))
		{
			$query .= "notes = '".$this->notes."', ";
		}
		
		if (isset($this->location) && $this->location != "NULL") {
			$query .= "location_id = '".$this->location."', ";
		}
		else if ($this->location =="NULL")
		{
			$query .= "location = NULL, ";
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
		
		if (isset($this->vlan_distinguisher))
		{
			$query .= "vlan_distinguisher = '".$this->vlan_distinguisher."', ";
		}
		
		$query = rtrim($query, ", ");
		
		$result = mysql_query($query);
		
			
		if (!$result) {
			$this->error = "Could not update vlan database: $query ... query failed : " . mysql_error();
			return false;
		} else {
			$new_id = mysql_insert_id();
			// now add new tags
			foreach ($this->tags as $tag_name) {
				$query = "insert into ipmanager_vlans_tags SET
						id = '$new_id',
						tag = '$tag_name'";
				$result = mysql_query($query);
				if (!$result) {
					$this->error = "Could not add remove old tags for vlan $query ... query failed : " . mysql_error();
					return false;
				}
			}
			return $new_id;
		}
	}
	
	function search($vlan_id="", $name = "", $status ="", $location="", $assigned_to="", $vlan_distinguisher="", $notes="")
	{	
		$query = "SELECT DISTINCT ipmanager_vlans.id  FROM ipmanager_vlans WHERE ";
		$w_query = "";
		
		if ($vlan_id != "")
		{
			$w_query .= "ipmanager_vlans.vlan_id LIKE '%".$vlan_id."%' AND ";
		}
		
		if ($name != "")
		{
			$w_query .= "ipmanager_vlans.name LIKE '%".$name."%' AND ";
		}
		
		if ($status != "")
		{
			$w_query .= "ipmanager_vlans.status LIKE '%".$status."%' AND ";
		}
		
		if ($location != "" && is_numeric($location))
		{
			$w_query .= "ipmanager_vlans.location_id = '".$location."' AND ";
		}
		
		if ($assigned_to != "" && is_numeric($assigned_to))
		{
			$w_query .= "ipmanager_vlans.assigned_to = '".$assigned_to."' AND ";
		}
		
		if ($vlan_distinguisher != "")
		{
			$w_query .= "ipmanager_vlan.vlan_distinguisher = '".$vlan_distinguisher."' AND ";
		}
		
		if ($notes != "")
		{
			$w_query .= "ipmanager_netblocks.notes = '".$notes."' AND ";
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
			$n_id[$obj->id] = $obj->name;
		}
		
		return $n_id;
	}
	/*function reset() {
		// Will reset the datebase and re-create all vlans in the range from
		// 1-4096
		$query = "DELETE FROM ipmanager_vlans";
		$result = mysql_query($query);
		if (!$result)  {
			$this->error = "Unable to delete vlans. $query ".  mysql_error();
			return false;
		}

		$query = "DELETE FROM ipmanager_vlans_tags";
		$result = mysql_query($query);
		if (!$result)  {
			$this->error = "Unable to delete vlan tags. $query ".  mysql_error();
			return false;
		}
		$vlan_id = 1;	
		while ( $vlan_id <= 4096 ) {
			$query = "insert into ipmanager_vlans SET
				vlan_id = '$vlan_id',
				status = 'FREE'
			";
			$result = mysql_query($query);
			if (!$result)  {
				$this->error = "Unable to add vlan tag. $query ".  mysql_error();
				return false;
			}
			print "added vlan $vlan_id\n";
			$vlan_id++;
		}
		return $true;

	}*/
	
}
?>

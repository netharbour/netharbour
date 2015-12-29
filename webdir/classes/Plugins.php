<?

class Plugins {
	
	private $id;
	private $name;
	private $icon;
	private $description;
	private $enabled;
	private $poller;
	private $poller_script;
	private $poller_interval;
	private $version;
	private $filename;
	private $class_name;
	private $config_filename;
	private $location;
	private $sub_location;
	private $plugin_order;
	
	private $error;

	function get_name(){return $this->name;}
	
	function get_icon_path(){return $this->icon;}
	
	function get_id(){return $this->id;}
	
	function get_description(){return $this->description;}
	
	function get_enabled(){return $this->enabled;}
	
	function get_version(){return $this->version;}

	function get_poller(){return $this->poller;}

	function get_poller_script(){return $this->poller_script;}

	function get_poller_interval(){return $this->poller_interval;}
	
	function get_filename(){return $this->filename;}
	
	function get_class_name(){return $this->class_name;}
	
	function get_conf_path(){return $this->config_filename;}
	
	function get_location(){
		return $this->location;}
	
	function get_sub_location(){
		return $this->sub_location;}
	
	function get_plugin_order(){return $this->plugin_order;}
	
	function get_error() {
		return $this->error;
	}
	
	function set_name($name){
		$this->name = $name;
	}
	
	function set_icon_path($icon){$this->icon = $icon;}
	
	function set_id($id){
		if(is_numeric($id))
		{$this->id = $id;}	
	}
	
	function set_poller($poller){
		if ($poller > 0 ) {
			$this->poller = 1;
			return $this->poller;
		}
		elseif ($poller == 0) {
			$this->poller = $poller;
			return $this->poller;
		}
		else {
			$this->error = "Invalid poller $poller";
			return false;
		}
	}

	function set_poller_script($poller_script){
		$this->poller_script = $poller_script;
	}

	function set_poller_interval($poller_interval){
		if (! is_numeric($poller_interval)) {
			$this->poller_interval = $poller_interval;
			return true;
		}
		$this->error = "Invalid poller interval";
		return false;
	}

	function set_description($description){$this->description = $description;}
	
	function set_enabled($enabled){
		if($enabled == 0)
		{$this->enabled = false;}
		else if ($enabled == 1)
		{$this->enabled = true;}
		else {$this->enabled = false;}
	}
	
	function set_version($version){$this->version = $version;}
	
	function set_filename($filename){$this->filename = $filename;}
	
	function set_class_name($class_name){$this->class_name = $class_name;}
	
	function set_conf_path($config_filename){$this->config_filename = $config_filename;}
	
	function set_location($location){
		$this->location = $location;}
	
	function set_sub_location($sub_location){
		$this->sub_location = $sub_location;}
	
	function set_plugin_order($plugin_order){$this->plugin_order = $plugin_order;}
	
	function __construct($id = '') {
		if (is_numeric($id)) {
			$this->get_plugin_info($id);
			if ($this->id == '') {
				$this->error = "Plugin not found";
				return false;
			}
		}
	}
	
	private function get_plugin_info($id)
	{
		$query = "SELECT Plugins_plugin.id, Plugins_plugin.name, Plugins_plugin.description, 
				Plugins_plugin.enabled, Plugins_plugin.version, Plugins_plugin.filename,
				Plugins_plugin.class_name, Plugins_plugin.config_filename, Plugins_plugin.location,
				Plugins_plugin.sub_location, Plugins_plugin.plugin_order, Plugins_plugin.icon,
				Plugins_plugin.poller, Plugins_plugin.poller_script, Plugins_plugin.poller_interval
			FROM  Plugins_plugin 
			WHERE id = '$id'";    

		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries

 		while ($obj = mysql_fetch_object($result)){
			$this->id = $obj->id;
			$this->name = $obj->name;
			$this->description = $obj->description;
			$this->enabled = $obj->enabled;
			$this->version = $obj->version;
			$this->filename = $obj->filename;
			$this->class_name = $obj->class_name;
			$this->config_filename = $obj->config_filename;
			$this->location = $obj->location;
			$this->sub_location = $obj->sub_location;
			$this->plugin_order = $obj->plugin_order;
			$this->icon = $obj->icon;
			$this->poller = $obj->poller;
			$this->poller_script = $obj->poller_script;
			$this->poller_interval = $obj->poller_interval;
		}
	}
	
	public function get_plugins()
	{
		//Return an array of devices names + id
		$all_plugins = array();

		$query = "SELECT id, name ".
			"FROM  Plugins_plugin ORDER BY name ";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			#$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$all_plugins[$obj->id] = $obj->name;
		}
		return $all_plugins;
	}
	
	function insert_plugin() {
		
		/*
		Commented out june14 2010
		No hard coded stuff...
	
		$allLocations = array('home', 'services', 'devices', 'clients', 'statistics');
		
		if (!in_array(strtolower($this->location), $allLocations))
		{
			$this->location="default";
		}
		
		if(!in_array(strtolower($this->sub_location), $allLocations))
		{
			$this->sub_location='default';
		}
		if ($this->location =='') {
			$this->location="default";
		}
		if ($this->sub_location =='') {
			$this->location="default";
		}
		*/
		
		$name = addslashes($this->name);
		$desc = addslashes($this->description);
		$query = "INSERT INTO Plugins_plugin 
				(Plugins_plugin.name, Plugins_plugin.description, Plugins_plugin.version, 
				Plugins_plugin.filename, Plugins_plugin.class_name, Plugins_plugin.config_filename,
				Plugins_plugin.location, Plugins_plugin.sub_location, Plugins_plugin.plugin_order,
				Plugins_plugin.icon, Plugins_plugin.poller, Plugins_plugin.poller_script, Plugins_plugin.poller_interval) 
			Values ('$name', '$desc', '$this->version', '$this->filename', 
				'$this->class_name', '$this->config_filename', '$this->location', 
				'$this->sub_location', '$this->plugin_order', '$this->icon', 
				'$this->poller', '$this->poller_script', '$this->poller_interval')";
		// execute the query 
		$result =  mysql_query($query) ;
		
		if (!$result)  {
			$this->error = mysql_error();
			echo mysql_error();
				return false;
			}
		return true;
	}
	
	function update_plugin()
	{
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
		$this->error = "Name can not be empty";
			return false;
		}
		
		/*
		Commented out june14 2010
		No hard coded stuff...
	
		$allLocations = array('home', 'services', 'devices', 'clients', 'statistics');
		
		if (!in_array(strtolower($this->location), $allLocations))
		{
			$this->location="default";
		}
		
		if(!in_array(strtolower($this->sub_location), $allLocations))
		{
			$this->sub_location='default';
		}
		if ($this->location =='') {
			print "setting location to default<br>";
			$this->location="default";
		}
		if ($this->sub_location =='') {
			$this->location="default";
		}

		*/
		if (!(is_numeric($this->id))) {
			$this->error = "Invalid plugin id";
			return false;
		}
		
		if (!(is_numeric($this->plugin_order))) {
			$this->plugin_order = 50;
		}
		
		if ($this->enabled === false)
		{$this->enabled =0;}
		$name = addslashes($this->name);
		$desc = addslashes($this->description);
		$query = "UPDATE Plugins_plugin 
			SET name = '$name', description = '$desc',   enabled = '$this->enabled', 
				version = '$this->version', filename = '$this->filename', class_name = '$this->class_name',
				config_filename = '$this->config_filename', location = '$this->location', sub_location = '$this->sub_location', 
				plugin_order = '$this->order', icon = '$this->icon', poller = '$this->poller',
 				poller_script = '$this->poller_script', poller_interval = '$this->poller_interval'
			 WHERE id = '$this->id'";
		// execute the query 
		$result =  mysql_query($query) ;
		
		if (!$result)  {
			$this->error = mysql_error() . " " . $query;
			return false;
		}
		return $result;
	}
	
	function remove_plugin()
	{
		if (!(is_numeric($this->id))) {
			$this->error = "Invalid plugin id";
			return false;
		}
		$query = "DELETE FROM Plugins_plugin WHERE id = '".$this->id."'";
		// execute the query 
		$result =  mysql_query($query) ;
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return $result;
	}
}

?>

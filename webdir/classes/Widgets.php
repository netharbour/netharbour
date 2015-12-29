<?
//a class for widgets
class Widgets {
	
	private $id;
	private $name;
	private $description;
	private $enabled;
	private $version;
	private $filename;
	private $class_name;
	private $config_filename;
	
	private $error;

	function get_name(){return $this->name;}
	
	function get_id(){return $this->id;}
	
	function get_description(){return $this->description;}
	
	function get_enabled(){return $this->enabled;}
	
	function get_version(){return $this->version;}
	
	function get_filename(){return $this->filename;}
	
	function get_class_name(){return $this->class_name;}
	
	function get_conf_path(){return $this->config_filename;}
	
	function get_error() {
		return $this->error;
	}
	
	function set_name($name){$this->name = $name;}
	
	function set_id($id){
		if(is_numeric($id))
		{$this->id = $id;}	
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
	
	function __construct($id = '') {
		if (is_numeric($id)) {
			$this->get_widget_info($id);
			if ($this->id == '') {
				$this->error = "Widget not found";
				return false;
			}
		}
	}
	
	private function get_widget_info($id)
	{
		$query = "SELECT Dashboard_widgets.id, Dashboard_widgets.name, Dashboard_widgets.description, Dashboard_widgets.enabled, Dashboard_widgets.version, Dashboard_widgets.filename, Dashboard_widgets.class_name, Dashboard_widgets.config_filename ".
			"FROM  Dashboard_widgets where id = '".$id."'";    
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
		}
	}
	
	public function get_widgets()
	{
		//Return an array of devices names + id
		$all_widgets = array();

		$query = "SELECT id, name ".
			"FROM  Dashboard_widgets ORDER BY name";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			#$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$all_widgets[$obj->id] = $obj->name;
		}
		return $all_widgets;
	}
	
	function insert_widget() {
		
		$query = "INSERT INTO Dashboard_widgets (Dashboard_widgets.name, Dashboard_widgets.description, Dashboard_widgets.version, Dashboard_widgets.filename, Dashboard_widgets.class_name, Dashboard_widgets.config_filename) Values ('$this->name', '$this->description', '$this->version', '$this->filename', '$this->class_name', '$this->config_filename')";
		// execute the query 
		$result =  mysql_query($query) ;
		
		if (!$result)  {
			$this->error = mysql_error();
			echo mysql_error();
				return false;
			}
		return true;
	}
	
	function update_widget()
	{
		// Update the info in the database
		// Test mandatory fields
		if ($this->name == '') {
		$this->error = "Name can not be empty";
			return false;
		}

		if (!(is_numeric($this->id))) {
			$this->error = "Invalid widget id";
			return false;
		}
		
		if ($this->enabled === false)
		{$this->enabled =0;}
		
		$query = "UPDATE Dashboard_widgets SET name = '$this->name', description = '$this->description',  " .
			" enabled = '$this->enabled', version = '$this->version', filename = '$this->filename', class_name = '$this->class_name', config_filename = '$this->config_filename' ".
			" WHERE id = '$this->id'";
		// execute the query 
		$result =  mysql_query($query) ;
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return $result;
	}
	
	function remove_widget()
	{
		if (!(is_numeric($this->id))) {
			$this->error = "Invalid widget id";
			return false;
		}
		$query = "DELETE FROM Dashboard_widgets WHERE id = '".$this->id."'";
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

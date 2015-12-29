<?
class Dashboard {
	private $updates;
	
	public function get_updates($archived = 0)
	{
		//Return an array of devices names + id
		if ($archived != 0) {
			$archived == 1;
		}
		$all_updates = array();

		$query = "SELECT id, action ".
			"FROM  Updates where archived = '".$archived."' ORDER BY curDate DESC";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			#$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$all_updates[$obj->id] = $obj->action;
		}
		return $all_updates;
	}
	
	function get_error() {
		return $this->error;
	}
}

class Updates extends Dashboard {

	private $id;
	private $action;
	private $curDate;
	private $username;
	private $archived;
	
	private $error;
	
	function __construct($id = '') {
		if (is_numeric($id)) {
			$this->get_update_info($id);
			if ($this->id == '') {
				$this->error = "Update not found";
				return false;
			}
		}
	}
	
	
	function get_error() {
		return $this->error;
	}
	
	public function get_updates($archived = 0)
	{
		//Return an array of devices names + id
		if ($archived != 0) {
			$archived == 1;
		}
		$all_updates = array();

		$query = "SELECT id, action ".
			"FROM  Updates where archived = '".$archived."' ORDER BY curDate DESC";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			#$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$all_updates[$obj->id] = $obj->action;
		}
		return $all_updates;
	}
	
	private function get_update_info($id)
	{
		$query = "SELECT Updates.id, Updates.action, Updates.curDate, Updates.username ".
			"FROM  Updates where id = '".$id."'";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries

 		while ($obj = mysql_fetch_object($result)){
			$this->id = $obj->id;
			$this->action = $obj->action;
			$this->curDate = $obj->curDate;
			$this->username = $obj->username;
		}
	}
	
	function insert_update() {
		
		$query = "INSERT INTO Updates (Updates.action, Updates.username, Updates.curDate, Updates.archived) Values ('$this->action', '$this->username', NOW(), '$this->archived')";
		// execute the query 
		$result =  mysql_query($query) ;
		
		if (!$result)  {
			$this->error = mysql_error();
			echo $this->error;
				return false;
			}
		return true;
	}
	
	function update()
	{
		// Update the info in the database
		// Test mandatory fields
		if (!(is_numeric($this->id))) {
			$this->error = "Invalid update id";
			return false;
		}
		$query = "UPDATE Updates SET archived = '$this->archived' WHERE id = '$this->id'";
		// execute the query 
		$result =  mysql_query($query) ;
		
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return $result;
	}
	
	function get_id()
	{return $this->id;}
	
	function get_action()
	{return $this->action;}
	
	function get_date()
	{return $this->curDate;}
	
	function get_username()
	{return $this->username;}
	
	function get_archived()
	{return $this->archived;}
	
	
	function set_id($id)
	{$this->id = $id;}
	
	function set_action($action)
	{$this->action = $action;}
	
	function set_date($date)
	{$this->curDate = $date;}
	
	function set_username($username)
	{$this->username = $username;}
	
	function set_archived($archived)
	{$this->archived = $archived;}
}

class DashboardUsers extends Dashboard{
	
	private $user_id;
	private $widget_id;
	private $position_x;
	private $position_y;
	
	private $error;

	function get_user_id(){return $this->id;}
	
	function get_widget_id(){return $this->widget_id;}
	
	function get_position_x($widget_id)
	{
		if (!(is_numeric($this->user_id))) {
			$this->error = "Unknown user, declare a user before getting their widgets";
			return false;
		}
		
		$query = "SELECT Dashboard_users.position_x ".
			"FROM  Dashboard_users WHERE widget_id = '".$widget_id."' AND user_id = '".$this->user_id."'";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			$this->position_x = $obj->position_x;
		}
		return $this->position_x;
	}
	
	function get_position_y($widget_id)
	{
		if (!(is_numeric($this->user_id))) {
			$this->error = "Unknown user, declare a user before getting their widgets";
			return false;
		}
		
		$query = "SELECT Dashboard_users.position_y ".
			"FROM  Dashboard_users WHERE widget_id = '".$widget_id."' AND user_id = '".$this->user_id."'";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			$this->position_y = $obj->position_y;
		}
		return $this->position_y;
	}
	
	function get_error() {
		return $this->error;
	}
	
	function set_user_id($user_id){
		if(is_numeric($user_id))
		{$this->id = $user_id;}	
	}
	
	function set_widget_id($widget_id){
		if(is_numeric($widget_id))
		{$this->widget_id = $widget_id;}	
	}
	
	function set_position_x($position_x){$this->position_x = $position_x;}
	
	function set_position_y($position_y){$this->position_y = $position_y;}
	
	function __construct($user_id = '') {
		if (is_numeric($user_id)) {
			$this->get_user_dash_info($user_id);
			if ($this->user_id == '') {
				$this->error = "User widgets not found";
				return false;
			}
		}
	}
	
	//Will try a different method
	private function get_user_dash_info($user_id)
	{
		$query = "SELECT Dashboard_users.user_id, Dashboard_users.widget_id, Dashboard_users.position_x, Dashboard_users.position_y ".
			"FROM  Dashboard_users where user_id = '".$user_id."'";    
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			
			$this->user_id = $obj->user_id;
			$this->widget_id = $obj->widget_id;
			$this->position_x = $obj->position_x;
			$this->position_y = $obj->position_y;
		}
		if ($this->user_id == '')
		{
			$this->user_id = $user_id;
		}
	}
	
	public function get_users_widgets()
	{
		if (!(is_numeric($this->user_id))) {
			$this->error = "Unknown user, declare a user before getting their widgets";
			return false;
		}
		//Return an array of devices names + id
		$all_users = array();
		
		$query = "SELECT user_id, widget_id ".
				"FROM  Dashboard_users WHERE user_id = '".$this->user_id."' ORDER BY position_y, position_x"; 
		// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
 		while ($obj = mysql_fetch_object($result)){
			$all_users[$obj->widget_id] = $obj->user_id;
		}
		return $all_users;
	}
	
	function insert_widget() {
		
		$query = "INSERT INTO Dashboard_users (Dashboard_users.user_id, Dashboard_users.widget_id, Dashboard_users.position_x, Dashboard_users.position_y) Values ('$this->user_id', '$this->widget_id', '$this->position_x', '$this->position_y')";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
				return false;
			}
		return true;
	}
	
	function update_widget()
	{
		// Update the info in the database
		// Test mandatory fields
		if (!(is_numeric($this->user_id))) {
			$this->error = "Invalid user id";
			return false;
		}

		$query = "UPDATE Dashboard_users SET position_x = '$this->position_x', position_y = '$this->position_y' WHERE widget_id = '$this->widget_id'";
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
		if (!(is_numeric($this->user_id))) {
			$this->error = "Invalid user id";
			return false;
		}
		$query = "DELETE FROM Dashboard_users WHERE widget_id = '".$this->widget_id."' AND user_id ='".$this->user_id."'" ;
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
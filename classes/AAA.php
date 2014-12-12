<?php

// 
// Class file for AAA 
//

// include the database configuration and
// open connection to database

include_once 'classes/Property.php';
include_once 'classes/PrivateData.php';

class User {
	private $error = false;
	private $user_id = '';
	private $full_name = '';
	private $user_name ='';
	private $user_email ='';
	private $user_pass = '';
	private $last_login = '';
	private $last_ip = '';

	// A flag to keep track if password is changed using set_user_password();
	// Need that in update()
	// Otherwise i'll encrypt the md5 version in the database
	private $user_pass_changed = false;

	// type is either ldap or local
	private $user_type;


	// Constructor 
	function __construct($user_id = '') {
		if (is_numeric($user_id)) {
			$this->get_user_info($user_id);
			if ($this->user_id == '') {
				$this->error = "Contact ID not found";
				return false;
			}
		}
	} 

	private function get_user_info($user_id) {
		$query = "Select user_id, user_name, full_name, user_email, user_type, user_pwd,
			last_login, last_ip
			from AAA_users 
			WHERE
			user_id = '$user_id'";
			// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		while ($obj = mysql_fetch_object($result)){
			$this->user_id =  $obj->user_id;
			$this->user_name =  $obj->user_name;
			$this->user_email =  $obj->user_email;
			$this->full_name =  $obj->full_name;
			$this->user_type =  $obj->user_type;
			$this->user_pass =  $obj->user_pwd;
			$this->last_login =  $obj->last_login;
			$this->last_ip =  $obj->last_ip;
		}
		return true;
	}

	
	function authenticate_user($user_name,$user_pass) {
		// First determine if this is a local or ldap user
        	if ($this->is_local_user($user_name,'local'))  {
			return $this->authenticate_local_user($user_name,$user_pass);
		} else {
			if (! $user_info = $this->authenticate_ldap_user($user_name,$user_pass)) {
				// Auth failed
				return false;
			}
			// Userinfo is an array which hold email and full name

			// Ok user is success fully authenticated
			// create user object and update / insert
			if (! $userid = $this->is_local_user($user_name,'ldap'))  {
				$ldap_user = new User();
				$ldap_user->set_full_name($user_info["fullname"]);
				$ldap_user->set_email($user_info["email"]);
				$ldap_user->set_user_name($user_name);
				$ldap_user->set_user_type('ldap');
				// New user insert in local user
				if (! $userid = $ldap_user->insert()) {
					// Unable to update local user cache
					$this->error = $ldap_user->get_error();
					return false;
				}

			// existing  user update in local user cache
			} else  {	
				$ldap_user = new User($userid);
				$ldap_user->set_full_name($user_info["fullname"]);
				$ldap_user->set_email($user_info["email"]);
				$ldap_user->set_user_name($user_name);
				$ldap_user->set_user_type('ldap');
				if (!$ldap_user->update()) {
					// Unable to update local user cache
					$this->error = $ldap_user->get_error();
					return false;
				}
			}
			// get groups
			if (! $ldap_groups = $this->get_ldap_groups($user_name,$user_pass)) {
				return false;
			}
			if (! $this->update_ldap_groups($userid,$ldap_groups)) {
				// Unable to update local group cache
				return false;
			}
			
			return true;
		}
	}

	public function get_users_by_fullname($archived = 0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$users = array();
		$query = "Select user_id, full_name 
			from AAA_users 
			WHERE
			archived = '$archived'";
			// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		while ($obj = mysql_fetch_object($result)){
			$users[$obj->user_id] = $obj->full_name;
		}
		return $users;
	}

	public function get_users($archived = 0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$users = array();
		$query = "Select user_id, user_name 
			from AAA_users 
			WHERE
			archived = '$archived'";
			// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		while ($obj = mysql_fetch_object($result)){
			$users[$obj->user_id] = $obj->user_name;
		}
		return $users;
	}

	function get_error() {
		return $this->error;
	}
	
	function get_user_name() {
		return $this->user_name;
	}
	function get_full_name() {
		return $this->full_name;
	}
	function get_email() {
		return $this->user_email;
	}
	function get_user_type() {
		return $this->user_type;
	}
	function get_user_id() {
		return $this->user_id;
	}
	function get_last_login() {
		return $this->last_login;
	}
	function get_last_ip() {
		return $this->last_ip;
	}

	function set_user_name($value) {
		if ($value != '') {
			$this->user_name = $value;
			return true;
		} else {
			$this->error = "userName Can not be empty";
			return false;
		}
	}
	
	function set_full_name($value) {
		$this->full_name = $value;
	}
	function set_email($value) {
		$this->user_email = $value;
	}
	function set_password($value) {
		if ($value == '') {
			$this->error = "password can not be empty";
			return false;
		} else {
			$this->user_pass = $value;		
			$this->user_pass_changed = true;
			return true;
		}
	}
	function set_user_type($value) {
		if (($value != 'local') && ($value != 'ldap')  ) {
			$this->error = "Invalid user type, should be either ldap or local";
			return false;
		} else {
			$this->user_type = $value;
		}
	}


	function get_user_id_by_user_name($user_name) {
		$user_id = false;
		$query = "select user_id from AAA_users where user_name = '$user_name' AND archived = '0'";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			$user_id = $obj->user_id;
		}
		return $user_id;
	}
	
	function get_groups() {
		if (!(is_numeric($this->user_id))) {
			$this->error = "Invalid user id";
			return false;
		}
		$groups = array();

		$query = "select AAA_users_groups.group_id, AAA_groups.group_name  
			FROM AAA_users_groups, AAA_groups
			WHERE AAA_users_groups.user_id = '$this->user_id'
			AND AAA_users_groups.group_id = AAA_groups.group_id";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			$groups[$obj->group_id] = $obj->group_name;
		}
		return $groups;
	}

	function add_to_group($group_id) {
		if (!(is_numeric($this->user_id))) {
			$this->error = "Invalid user id";
			return false;
		}
		$group = New group($group_id);
		if (! is_numeric($group->get_group_id())) {
			$this->error = "Group id $group_id does not exist";
			return false;
		}

		$query = "insert into AAA_users_groups SET 
			user_id = '$this->user_id',
			group_id = '$group_id'";

		$result = mysql_query($query);
		if (! $result) {
			$this->error = "Error, query failed. " . mysql_error() ;
			return false;
		} else {
			return true;
		}
	}

	function delete_from_group($group_id) {
		if (!(is_numeric($this->user_id))) {
			$this->error = "Invalid user id";
			return false;
		}
		$group = New group($group_id);
		if (! is_numeric($group->get_group_id())) {
			$this->error = "Group id $group_id does not exist";
			return false;
		}

		$query = "delete from AAA_users_groups WHERE
			user_id = '$this->user_id' AND
			group_id = '$group_id'";

		$result = mysql_query($query);
		if (! $result) {
			$this->error = "Error, query failed. " . mysql_error() ;
			return false;
		} else {
			return true;
		}
	}
	
	function get_access_level() {
		// This function will return the highest access level
		// Access levels are bases on the group of this user
		$level = 0;
		$query = "select max(access_level) as level from AAA_groups, AAA_users, AAA_users_groups 
			WHERE  AAA_users_groups.user_id = '$this->user_id' AND 
			 AAA_users_groups.group_id = AAA_groups.group_id";
		$result = mysql_query($query);
		if (! $result) {
			$this->error = "Error, query failed. " . mysql_error() ;
			return false;
		} else {
			while ($obj = mysql_fetch_object($result)){
				if (is_numeric($obj->level)) {
					$level =  $obj->level;
				}
			}
			return $level;
		}
	}


	function update() {
		// Test mandatory fields
		if (!(is_numeric($this->user_id))) {
			$this->error = "Invalid user id";
			return false;
		} 
		if ($this->user_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (($this->user_type != 'local') && ($this->user_type != 'ldap')  ) {
			$this->error = "Invalid user type, should be either ldap or local";
			return false;
		}
		if (($this->user_type == 'local') && ($this->user_pass_changed) && ($this->user_pass == '')) {
			$this->error = "password can not be empty";
			return false;
		}
		if ($this->is_local_user($user_name,'ldap')) {
			$this->error = "Username $user_name already exists";
			return false;
		}

		if ($this->user_type == 'local') {
			$md5pass = md5($this->user_pass);
		} else {
			$md5pass = '';
		}
		$query = "UPDATE AAA_users SET
			user_name = '$this->user_name', ";
		// If password was changed, update in db
		// Otherwise don't touch
		if ($this->user_pass_changed) {
			 $query .= "user_pwd = '$md5pass', ";
		}
		$sql_fullname = mysql_real_escape_string($this->full_name);

		$query .= "user_type = '$this->user_type',
			full_name = '$sql_fullname',
			user_email = '$this->user_email' 
			WHERE user_id = '$this->user_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return $result;
	}

	function update_last_login($ip='') {
		$query = "UPDATE AAA_users 
			SET last_login = NOW(), last_ip = '$ip'
			WHERE user_id = '$this->user_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return $result;
	}

	function insert() {
		
		// first test if username is already in user:
		if ($this->get_user_id_by_user_name($this->user_name)) {
			$this->error =  "username $this->user_name  already exists";
			return false;
		}
		// Test mandatory fields
		if ($this->user_id != '') {
			$this->error = "This is an insert, contact_id should be empty";
			return false;
		} 
		if ($this->user_name == '') {
			$this->error = "Name can not be empty";
			return false;
		}
		if (($this->user_type != 'local') && ($this->user_type != 'ldap')  ) {
			$this->error = "Invalid user type, should be either ldap or local";
			return false;
		}
		if (($this->user_type == 'local') && ( $this->user_pass == '')) {
			$this->error = "password can not be empty";
			return false;
		}
		$md5pass = '';
		if ($this->user_type == 'local') {
			$md5pass = md5($this->user_pass);
			if ($md5pass == '') {
				$this->error = "Could not encrypt password";
				return false;
			}
		} else {
			$md5pass = '';
		}
		$sql_fullname = mysql_real_escape_string($this->full_name);
		$query = "Insert Into AAA_users SET
			user_name = '$this->user_name',
			user_pwd = '$md5pass',
			user_type = '$this->user_type',
			full_name = '$sql_fullname',
			user_email = '$this->user_email' ";
		// Now execute query

		if (! $result = mysql_query($query)) {
			$this->error = "Could not add new user $query ... query failed : " . mysql_error();
			return false;
		} else {
			$new_id = mysql_insert_id();
			$this->user_id =  $new_id;
			return $this->user_id;
		}
	}

	function delete() {
		if (!(is_numeric($this->user_id))) {
			$this->error = "Invalid user id";
			return false;
		}

		$query = "update AAA_users SET archived = '1'  
			WHERE user_id = '$this->user_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

	function is_local_user($user_name,$type='local') {
		$query = "Select user_id from AAA_users WHERE
			user_name = '$user_name' and user_type = '$type'";
			// execute the query 
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
		while ($obj = mysql_fetch_object($result)){
			return $obj->user_id;
		}
		return false;
	}

	private function authenticate_local_user($user_name,$user_pass) {

		$md5pass = md5($user_pass);
		$sql = "SELECT user_id FROM AAA_users WHERE 
            		user_name = '$user_name' AND 
            		user_pwd = '$md5pass'"; 
                        
		$result = mysql_query($sql) or die (mysql_error()); 
		$num = mysql_num_rows($result);
		if ( $num > 0 ) { 
			return true;
		} else {
			$this->error = "Invalid credentials";
			return false;
		}
	}

	private function authenticate_ldap_user($username,$user_pass) {

		$property = new Property;
		if(! $ldap_server = $property->get_property("LDAP_server")) {
			$this->error= "No ldap server specified. Please configure an LDAP server address";
			return false;
		}
		if(! $ldap_version = $property->get_property("LDAP_version")) {
			$this->error= "No ldap version specified. Please configure an LDAP protocol version ";
			return false;
		}
		if(! $dn = $property->get_property("LDAP_DN")) {
			$this->error= "No ldap Distinguished Name specified. Please configure a Distinguished Name  ";
			return false;
		}
		if(! $basedn = $property->get_property("LDAP_base_dn")) {
			$this->error= "No ldap BASE DN  Name specified ";
			return false;
		}
		if(! $group_base_dn = $property->get_property("LDAP_group_base_dn")) {
			$this->error= "No ldap group base DN specified. Please configure a group base DN ";
			return false;
		}
		if(! $group_search = $property->get_property("LDAP_group_search_filter")) {
			$this->error= "No ldap group search filter specified. ";
			return false;
		}
		// These should be retrieved using get_properties
		#$ldap_version = 3;
		#$ldap_server = 'ldap.bc.net';
		#$dn = "cn=<username>,ou=people,dc=bc,dc=net";

       		 /* strip bad chars from username - prevent altering filter from username */
        	$username = str_replace("&", "", $username);
        	$username = str_replace("|", "", $username);
        	$username = str_replace("(", "", $username);
        	$username = str_replace(")", "", $username);
        	$username = str_replace("*", "", $username);
        	$username = str_replace(">", "", $username);
        	$username = str_replace("<", "", $username);
        	$username = str_replace("!", "", $username);
        	$username = str_replace("=", "", $username);

		$dn = str_replace("<username>",$username,$dn);


		// Set result to false;
		$result = false;
		 if (!($connect = ldap_connect($ldap_server))) {
                        $this->error =  "Could not connect to LDAP server: $ldap_server";
			return false;
                }
                if (! ldap_set_option($connect,LDAP_OPT_PROTOCOL_VERSION,$ldap_version)) {
                        $this->error =  "Failed to set version to protocol $ldap_version";
			return false;
                }

		// verify binding
                if ($bind = @ldap_bind($connect,$dn, $user_pass) ){
			$result = true;
			#print "auth ok<br>";
			// Get full name
			$sr = ldap_search($connect, $basedn,"cn=$username");
			$info = ldap_get_entries($connect, $sr);
			$givenname=$info[0]["givenname"][0];
			$sn=$info[0]["sn"][0];
			$mail =  $info[0]["mail"][0];
			$result = array("fullname" => "$givenname $sn", "email" => $mail);

			ldap_unbind($connect);
		} else {
			// unable to bind 
			$ldap_error = ldap_errno($connect);
			$this->error =  ldap_error($connect);
			$result = false;
			#print "auth false: $this->error<br>";
		}

		
		return $result;
	}

	// This is to retrieve all ldap groups this user is in
	// We can use that to update local cache
	private function get_ldap_groups($user_name,$user_pass) {


		$property = new Property;
		if(! $ldap_server = $property->get_property("LDAP_server")) {
			$this->error= "No ldap server specified. Please configure an LDAP server address";
			return false;
		}
		if(! $ldap_version = $property->get_property("LDAP_version")) {
			$this->error= "No ldap version specified. Please configure an LDAP protocol version ";
			return false;
		}
		if(! $dn = $property->get_property("LDAP_DN")) {
			$this->error= "No ldap Distinguished Name specified. Please configure a Distinguished Name  ";
			return false;
		}
		if(! $group_base_dn = $property->get_property("LDAP_group_base_dn")) {
			$this->error= "No ldap group base DN specified. Please configure a group base DN ";
			return false;
		}
		if(! $group_search = $property->get_property("LDAP_group_search_filter")) {
			$this->error= "No ldap group search filter specified. ";
			return false;
		}

		// These should be retrieved using get_properties
		#$ldap_version = 3;
		#$ldap_server = 'ldap.bc.net';
		#$dn = "cn=<username>,ou=people,dc=bc,dc=net";
		#$group_base_dn = "ou=groups,dc=bc,dc=net";
                #$group_search = "uniqueMember=cn=<username>,ou=people,dc=bc,dc=net"   ;

       		 /* strip bad chars from username - prevent altering filter from username */
        	$user_name = str_replace("&", "", $user_name);
        	$user_name = str_replace("|", "", $user_name);
        	$user_name = str_replace("(", "", $user_name);
        	$user_name = str_replace(")", "", $user_name);
        	$user_name = str_replace("*", "", $user_name);
        	$user_name = str_replace(">", "", $user_name);
        	$user_name = str_replace("<", "", $user_name);
        	$user_name = str_replace("!", "", $user_name);
        	$user_name = str_replace("=", "", $user_name);

		$dn = str_replace("<username>",$user_name,$dn);
                $group_search = str_replace("<username>",$user_name,$group_search);

		// Set result to false;
		$result = false;

		 if (!($connect = ldap_connect($ldap_server))) {
                        $this->error =  "Could not connect to LDAP server: $ldap_server";
			return false;
                }
                if (! ldap_set_option($connect,LDAP_OPT_PROTOCOL_VERSION,$ldap_version)) {
                        $this->error =  "Failed to set version to protocol $ldap_version";
			return false;
                }

		// verify binding
                if ($bind = @ldap_bind($connect,$dn, $user_pass) ){
		} else {
			// unable to bind 
			$ldap_error = ldap_errno($connect);
			$this->error =  ldap_error($connect);
			$result = false;
		}

		// Get all groups
		$member_groups = array();
		$sr= ldap_search($connect, $group_base_dn,$group_search);
		$info = ldap_get_entries($connect, $sr);
		for ($i=0; $i<$info["count"]; $i++) {
			array_push($member_groups,$info[$i]["cn"][0]);
		}
		return $member_groups;
	}

	private function update_ldap_groups($user_id,$groups) {

		// This function is to update the local groups table
		// based on ldap groups they are in

		// If it's not in a ldap group access should be denied

		// First get User id
		if (!is_numeric($user_id)) {
			$this->error = "invalid user id";
			return false;
		}

		// First flush all existing onces.
		$query = "delete from AAA_users_groups WHERE
				user_id = '$user_id'";
		if (! $result = mysql_query($query)) {
			$this->error = "Could not flush AAA_users_groups for user id $user_id $query ... query failed : " . mysql_error();
		}
		
		if (sizeof($groups) <= 0 ) {
			// User is in no groups
			// Access denied
			 $this->error = "Can't find any LDAP groups for this user";
			return false;
		}
		
		$found_valid_group = 0;
		foreach ($groups as $group) {
			$query = "Select group_id from AAA_groups WHERE
				group_name = '$group' OR
				ldap_group_name = '$group'";
			 $result = mysql_query($query) or die('Error, query failed. ' . mysql_error());

			// get all entries
 			while ($obj = mysql_fetch_object($result)){
				// Means there's a valid group mapping;
				$found_valid_group++;

				// let's update db
				$query2 = "INSERT INTO AAA_users_groups SET
					user_id = '$user_id',
					group_id = '$obj->group_id'";
				if (! $result2 = mysql_query($query2)) {
					$this->error = "Could not update users_groups $query2 ... query failed : " . mysql_error();
					return false;
				}
			}
		}

		if ($found_valid_group < 1 )  {
			// No valid group mapping found
			$this->error = "Access denied: No valid group mapping found. Looks like you are not in a ldap group that has access";
			return false;
		} else {
			return true;
		}
	}
}

class Group {
	private $error = false;
	private $group_id = '';
	private $group_name;
	private $group_desc;
	private $ldap_group_name;
	private $access_level = 0;

	// Used to check if this group uses a group pass
	// for secret data
	private $group_pass = false;

	function __construct($group_id = '') {
		if (is_numeric($group_id)) {
			$this->get_group_info($group_id);
		}
	}

        private function get_group_info($group_id) {
		if (! is_numeric($group_id)) {
			$this->error = "Invalid group id";
			return false;
		}
                $query = "Select group_id, group_name, group_desc, ldap_group_name, access_level, group_pass
                        from AAA_groups 
                        WHERE
                        group_id = '$group_id'";
                        // execute the query 
                $result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
                // get all entries
                while ($obj = mysql_fetch_object($result)){
                        $this->group_id =  $obj->group_id;
                        $this->group_name =  $obj->group_name;
                        $this->group_desc =  $obj->group_desc;
                        $this->ldap_group_name =  $obj->ldap_group_name;
                        $this->access_level =  $obj->access_level;
			if ($obj->group_pass == 1) {
				$this->group_pass = true;
			} else {
				$this->group_pass = false;
			}
                }
                return true;
        }

	
	public function get_groups($archived =0) {
		if ($archived != 0) {
			$archived = 1;
		}
		$groups = array();
		$query = "select group_id, group_name from AAA_groups where archived = '$archived'";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			$groups[$obj->group_id] = $obj->group_name;
		}
		return $groups;
	}
	
	function get_group_id_by_group_name($group_name) {
		$group_id = false;
		$query = "select group_id from AAA_groups where group_name = '$group_name'";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			$group_id = $obj->group_id;
		}
		return $group_id;
	}

	function get_error() {
		return $this->error;
	}

	function get_name() {
		return $this->group_name;
	}
	function get_description() {
		return $this->group_desc;
	}
	function get_ldap_group_name() {
		return $this->ldap_group_name;
	}
	function get_group_id() {
		return $this->group_id;
	}
	function get_access_level() {
		return $this->access_level;
	}
	function get_group_pass() {
		return $this->group_pass;
	}

	function set_name($value) {
		if ($value != '') {
			$this->group_name = $value;
			return true;
		} else {
			$this->error = "group name Can not be empty";
			return false;
		}
	}

	function set_description($value) {
		$this->group_desc = $value;
	}
	function set_ldap_group_name($value) {
		$this->ldap_group_name = $value;
	}
	function set_group_pass($value) {
		$this->group_pass = $value;
	}

	function set_access_level($value) {
		if (($value < 0) || ($value > 100))   {
			$this->error = "Invalid access level, should be between 0 and 100";
			return false;
		} else {
			$this->access_level = $value;
			return true;
		}
	}
	
	
	function insert() {
		// First check if groups is already defined
		$all_groups = $this->get_groups();
		if (array_search($this->group_name,$all_groups) ) {
			$this->error = "Group $this->group_name already exists";
			return false;
		}
		
		if ($this->group_name == '') {
			$this->error = "Group name can not be empty";
			return false;
		}

		if ($this->group_pass) {
			$group_pass = 1;
		} else {
			$group_pass = 0;
		}
		// Continue adding
		$query = "insert INTO AAA_groups SET
				group_name = '$this->group_name',
				group_desc = '$this->group_desc',
				access_level = '$this->access_level',
				group_pass = '$group_pass',
				ldap_group_name = '$this->ldap_group_name'";
		if (! $result = mysql_query($query)) {
			$this->error = "Could not add new group $query ... query failed : " . mysql_error();
			return false;
		} else {
			$this->group_id =  mysql_insert_id();
			return $this->group_id;
		}
	}


	function update() {
		if (! is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}
		if ($this->group_name == '') {
			$this->error = "Group name can not be empty";
			return false;
		}
		if ($this->group_pass) {
			$group_pass = 1;
		} else {
			$group_pass = 0;
		}

		// Continue adding
		$query = "update AAA_groups SET
				group_name = '$this->group_name',
				group_desc = '$this->group_desc',
				ldap_group_name = '$this->ldap_group_name',
				group_pass = '$group_pass',
				access_level = '$this->access_level'
				WHERE group_id = '$this->group_id'";
		if (! $result = mysql_query($query)) {
			$this->error = "Could not add new group $query ... query failed : " . mysql_error();
			return false;
		} else {
			return $result;
		}
	}

	function delete() {
		if (!(is_numeric($this->group_id))) {
			$this->error = "Invalid group id";
			return false;
		}

		$query = "update AAA_groups SET archived = '1'  
			WHERE group_id = '$this->group_id'";
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		}
		return true;
	}

	function has_password() {
		// check if this is group already has a password, or if this 
		// is a new password (i.e. it had no password before).
		// If it did not have a password before than the verification_string_encr
		// field in the database is set to NULL
		// Returns 0 (if password is not yet set)
		// returns 1 (if a password is set).

		if (! is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}

		$query = "select verification_string_encr FROM AAA_groups	
			WHERE group_id = '$this->group_id' AND 
			verification_string_encr is NULL";
		$result =  mysql_query($query) or die("Mysql query failed: $query  Error: ". mysql_error()) ;
		if (mysql_num_rows($result) > 0) {
			// New password
			return 0;
		} else {
			return 1;
		}
	}

	function set_password($new_pass ='', $old_pass = false) {
		if (! is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}
		if ($new_pass == '') {
			$this->error = "password can not be empty";
			return false;
		}

		// 1st check if this is group already has a password, or if this is a new pass
		// if it already has a pass we need to check 1st of the old password is correct.

		if ($this->has_password() === false) {
			return false;
		} elseif ($this->has_password() == 0) {
			// New password, do nothing

		} else {
			// Change of existing pass, check old pass 1st
			$priv_data_obj = new PrivateData();
			if (! $priv_data_obj->verify_group_key($this->group_id, $old_pass) ) {
				$this->error = $priv_data_obj->get_error();
				return false;
			}
		}
		
		// We need to check if there is already data encrypted with this password.
		// Or if this is this the first password for this group.
		// If there is already data encrypted with this password it means we
		// need to find these data entried, decrypt them with the old pass
		// and then encrypt all of them with the new pass

		// So let's start by checking if there are secret data entried for this
		// group or not.
		
		$query = "select secret_data_id FROM secret_data_groups	
			WHERE aaa_groups_id = '$this->group_id'";
		$result =  mysql_query($query) ;

		// Now Count number of rows that are encrypted for this group
		// If more than 0, we need to update and need old pass (check for that)
		// let's keep a record of the data_id's we need to update
		$data_ids = array();
		if (mysql_num_rows($result) > 0) {
			
			// Check old pass
			if ( $old_pass == false ) {
				$this->error = "No old password provided, This group already has encrypted data";
				return false;
			}
	
			while ($obj = mysql_fetch_object($result)){	
				$data_ids[$obj->secret_data_id] = $obj->secret_data_id;
			}
		} else {
			// No passwords yet
		}

		// now we have list of old encrypted entries.
		// Let's start updating.
		// we need a transaction for this.

		// We need to update multiple tables, so we'll use a transaction with commit
		mysql_query("BEGIN") or die("Error, start of transaction failed " . mysql_error());
		$commit_ok = true;
		$commit_log ='';

		
		foreach ($data_ids as $data_id => $old_secret_data) {
			if ($commit_ok == false) {
				break;
			}
			unset($secret);
			$secret =  new PrivateData($data_id);
			if ($secret->update_private_data($new_pass,$old_pass)) {
				// Good
			} else {
				$commit_ok = false;
				$commit_log = $secret->get_error();
			}
		}

		$verifcation_string = false;	
		$verifcation_string = PrivateData::VERIFICATION_STRING;
		if ($verifcation_string == false) {
			$commit_log = "Unable to retrieve verification string";
			$commit_ok = false;
		}
		
		if ($commit_ok) {	
			$query = "Update AAA_groups 
				SET verification_string_encr = aes_encrypt('$verifcation_string','$new_pass')
				WHERE group_id = '$this->group_id' ";
			$result =  mysql_query($query);
			if (! $result) {
				$commit_ok = false;
				$commit_log = mysql_error() ."query was $query";
			}
		}
		
		// Now of all went ok we commit
		if ( $commit_ok == true ) {
			$result = mysql_query("COMMIT");
			if ($result) {
				// Good
				return true;
			} else {
				$this->error = "Failed to Commit: ". mysql_error();
				return false;
			}
		} else {
			mysql_query("ROLLBACK") or die("Error, Rollback failed " . mysql_error());
			$this->error = "Failed, doing rollback. $commit_log";
			return false;
                }

	}


	function get_users() {
		if (!(is_numeric($this->group_id))) {
			$this->error = "Invalid group id";
			return false;
		}
		$users = array();

		$query = "select AAA_users_groups.user_id, AAA_users.user_name  
			FROM AAA_users_groups, AAA_users
			WHERE AAA_users_groups.group_id = '$this->group_id'
			AND AAA_users_groups.user_id = AAA_users.user_id";
		$result = mysql_query($query) or die('Error, query failed. ' . mysql_error());
		// get all entries
 		while ($obj = mysql_fetch_object($result)){
			$users[$obj->user_id] = $obj->user_name;
		}
		return $users;
	}

	function add_member($user_id) {
		if (!(is_numeric($this->group_id))) {
			$this->error = "Invalid group id";
			return false;
		}
		$user = New user($user_id);
		if (! is_numeric($user->get_user_id())) {
			$this->error = "User id $user_id does not exist";
			return false;
		}

		$query = "insert into AAA_users_groups SET 
			user_id = '$user_id',
			group_id = '$this->group_id'";

		$result = mysql_query($query);
		if (! $result) {
			$this->error = "Error, query failed. " . mysql_error() ;
			return false;
		} else {
			return true;
		}
	}

	function delete_member($user_id) {
		if (!(is_numeric($this->group_id))) {
			$this->error = "Invalid group id";
			return false;
		}
		$user = New user($user_id);
		if (! is_numeric($user->get_user_id())) {
			$this->error = "User id $user_id does not exist";
			return false;
		}

		$query = "delete from AAA_users_groups WHERE
			user_id = '$user_id' AND
			group_id = '$this->group_id'";

		$result = mysql_query($query);
		if (! $result) {
			$this->error = "Error, query failed. " . mysql_error() ;
			return false;
		} else {
			return true;
		}
	}

}

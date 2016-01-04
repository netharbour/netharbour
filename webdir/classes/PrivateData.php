<?

/*
This class file provides access to secret data.
Secret data contains username and passwords. These passwords are AES encrypted
stored in MySQL.
*/


class PrivateData {

	// Static verifcation string
	// This verification string is encrypted/decrypted with 
	// the group password, to check if the provided password was valid
	// DO NOT CHANGE!!
	const VERIFICATION_STRING = "verifcation_string";


	private $data_id = '';
	private $name;
	private $type_id = false;
	private $type_name;
	private $type_desc;
	private $encr_data = false;
	private $plain_data = false;
	private $notes;
	private $group_id = false;
	private $device_id = false;
	private $group_name;

	private $error = false;
        
	function __construct($data_id = '') {
		if (is_numeric($data_id)) {
			$this->get_private_data_info($data_id);
		} 
	}


	private function get_private_data_info($data_id){
		$query = "SELECT 
			secret_data.id, secret_data.name,
			secret_data.device_id,
			secret_data.type_id, secret_data.encr_data, secret_data.notes,
			secret_data_types.type_name, secret_data_types.type_desc,
			secret_data_groups.aaa_groups_id,
			AAA_groups.group_name
		FROM 
			secret_data, secret_data_types, secret_data_groups, AAA_groups
		WHERE 
			secret_data.id = '$data_id' 
			AND secret_data_types.type_id = secret_data.type_id
			AND secret_data_groups.secret_data_id = secret_data.id
			AND AAA_groups.group_id = aaa_groups_id
		";
				
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() ."   -- query: $query ";
			return false;
		}
		if (mysql_numrows($result) < 1 ) {
			$this->error = "No data found for this Group";
			return false;
		}
		while ($obj = mysql_fetch_object($result)){
			$this->data_id = $obj->id;
			$this->name = $obj->name;
			$this->type_id = $obj->type_id;
			$this->type_name = $obj->type_name;
			$this->type_desc = $obj->type_desc;
			$this->type_notes = $obj->type_notes;
			//$this->notes = $obj->notes;
			$this->encr_data = $obj->encr_data;
			$this->group_id = $obj->aaa_groups_id;
			$this->group_name = $obj->group_name;
			$this->device_id = $obj->device_id;
		}
		return true;
	}

	// List of secret data entries

	public function get_private_data_by_device($device_id = false, $group_id = false) {
		if (!is_numeric($device_id)) {
			//$this->error = "Invalid device id";
			return false;
		}	

		// Optionally you can also specify the group
		if (is_numeric($group_id)) {
			$sql_group = "secret_data_groups.group_id = '$group_id' AND secret_data_groups.data_id = secret_data.id'";
		} else {
			$sql_group = "1";
		}
		$password = array();
		$query = "SELECT 
			secret_data.id,
			secret_data.type_id, secret_data.encr_data,
			secret_data_types.type_name, secret_data_types.type_desc,
			secret_data_groups.aaa_groups_id
		FROM 
			secret_data, secret_data_types, secret_data_groups
		WHERE
			secret_data_types.type_id = secret_data.type_id
			AND secret_data.device_id = '$device_id'
			AND $sql_group
		";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			die(mysql_error() ."   -- query: $query ");
			return false;
		}
		while ($obj = mysql_fetch_object($result)){
			$passwords[$obj->id] = $obj->aaa_groups_id;
		}
		return $passwords;
	}


	public function get_private_data_by_group($group_id = false) {
		if (!is_numeric($group_id)) {
			$this->error = "Invalid Group id";
			return false;
		}	
		$password = array();
		$query = "SELECT 
			secret_data.id, secret_data.name,
			secret_data.type_id, secret_data.encr_data,
			secret_data_types.type_name, secret_data_types.type_desc
		FROM 
			secret_data, secret_data_types, secret_data_groups
		WHERE
			secret_data_types.type_id = secret_data.type_id
			AND secret_data_groups.aaa_groups_id = '$group_id'
			AND secret_data_groups.secret_data_id = secret_data.id
		";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			$this->error = mysql_error() ."   -- query: $query ";
			return false;
		}
		while ($obj = mysql_fetch_object($result)){
			$passwords[$obj->id] = $obj->name;
		}
		return $passwords;
	}


	/*
	Decrypt the password identified by $data_id
	The key is the shared key for that group
	*/

	function decrypt($key ='') {
		$data['private_data'] = false;
		$data['notes'] = false;

		if (!is_numeric($this->data_id)) {
			$this->error = "Invalid data id";
			return false;
		}

		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group_id ";
			return false;
		}
		// Verify if provided group key is correct.
		if (! $this->verify_group_key($this->group_id, $key)) {
			return false;
		}
		$query = "SELECT 
			aes_decrypt(encr_data, '$key') as decrypted_string,
			aes_decrypt(notes, '$key') as notes_plain
		FROM 
			secret_data, secret_data_groups
		WHERE
			secret_data.id = '$this->data_id'
			AND secret_data_groups.aaa_groups_id = '$this->group_id'
			AND secret_data_groups.secret_data_id = secret_data.id
		";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result) {
			$this->error = mysql_error() ."   -- query: $query ";
			return false;
		}
		while ($obj = mysql_fetch_object($result)){
			$data['private_data'] = $obj->decrypted_string;
			$data['notes'] = $obj->notes_plain;
		}
		if (is_null($data['private_data'])) {
			$this->error = "Unable to decrypt, Invalid password";
			return false;
		}
		return $data;
	}

	/*
	Encrypt the password identified by $data_id
	The key is the shared key for that group
	*/

	function encrypt($key ='') {
		$password = false;
		if (!is_numeric($this->data_id)) {
			$this->error = "Invalid data id";
			return false;
		}
		if ($this->plain_data == false) {
			$this->error = "no password specified";
			return false;
		}
		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group_id ";
			return false;
		}
		// Verify if provided group key is correct.
		if (! $this->verify_group_key($this->group_id, $key)) {
			return false;
		}

		$query = "UPDATE secret_data, secret_data_groups SET 
			secret_data.encr_data =  aes_encrypt('$this->plain_data','$key')
			WHERE
			secret_data.id = '$this->data_id'
			AND secret_data_groups.aaa_groups_id = '$this->group_id'
			AND secret_data_groups.secret_data_id = secret_data.id
		";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			$this->error = mysql_error() ."   -- query: $query ";
			return false;
		} else {
			return true;
		}
	}



	function verify_group_key($group_id, $key) {

	/*
	This function is to make sure things to not get encrypted with the wrong password.
	Each group has a verification_string in encrypted format. 
	This is verification string $verifcation_string is encrypted with the group password.
	
	Be decrypting the encrypted verification string with the key (password) and then 
	compare this to the plain text version (should be equal) we know if the provided key was valid
	*/	
		$plain = self::VERIFICATION_STRING;
		$decrypt = 'xxx';	

		$query = "SELECT 
			aes_decrypt(verification_string_encr, '$key') as decrypt
		FROM 
			AAA_groups
		WHERE
			group_id = '$group_id'
		";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			$this->error = mysql_error() ."   -- query: $query ";
			return false;
		}
		if (mysql_numrows($result) < 1 ) {
			$this->error = "No Group Key set: Group not configured for private data authenticaton. Group id $group_id";
			return false;
		}
		while ($obj = mysql_fetch_object($result)){
			$decrypt = $obj->decrypt;
		}
		if (is_null($decrypt)) {
			$this->error = "Invalid Group password";
			return false;
		}
		if ($decrypt == '') {
			$this->error = "Unable to retrieve verification string for  Group id $group_id";
			return false;
		}
		elseif ($plain == $decrypt) {
			return true;
		}  
		else {
			$this->error = "Provided Key is Invalid";
			return false;
		}
	}

	/*
	Below the SET and GET functions for this object
	*/

	// Get Functions
	function get_history($key) {
		if ($key == '') {
			$this->error = "Invalid key password, can not be empty";
			return false;
		}
		if (!is_numeric($this->data_id)) {
			$this->error = "Invalid data id";
			return false;
		} 
		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}

		// First check if the provided shared secret is ok
		if (! $this->verify_group_key($this->group_id, $key)) {
			return false;
		}
		
		$query = "Select 
			aes_decrypt(secret_data_history.encr_data, '$key') as decrypted_string, 
			secret_data_history.change_time 
		FROM 
			secret_data_history, secret_data, secret_data_groups
		WHERE
			secret_data.id = '$this->data_id'
			AND secret_data_history.secret_data_id  = secret_data.id
			AND secret_data_groups.aaa_groups_id = '$this->group_id'
			AND secret_data_groups.secret_data_id = secret_data.id
		Order by change_time desc
		";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			$this->error = mysql_error() ."   -- query: $query ";
			return false;
		}
		$history = array();
		while ($obj = mysql_fetch_object($result)){
			$history[$obj->change_time] = $obj->decrypted_string;
		}
		return $history;
	}


	function get_error() {
		return $this->error;
	}

	function get_type_id() {
		return $this->type_id;
	}

	function get_type_name() {
		return $this->type_name;
	}

	function get_type_desc() {
		return $this->type_desc;
	}

	function get_private_data($key) {
		$data = $this->decrypt($key);
		return $data['private_data'];
	}

	function get_name() {
		return $this->name;
	}
	function get_notes($key) {
		$data = $this->decrypt($key);
		return $data['notes'];
	}

	function get_group_id() {
		return $this->group_id;
	}
	function get_group_name() {
		return $this->group_name;
	}
	function get_device_id() {
		return $this->device_id;
	}

	// Set functions
        function set_group_id($value) {
                if (is_numeric($value)) {
                        $this->group_id = $value;
                        return true;
                } else {
                        $this->error = "Invalid Group id";
                        return false;
                }
	}

        function set_type_id($value) {
                if (is_numeric($value)) {
                        $this->type_id = $value;
                        return true;
                } else {
                        $this->error = "Invalid Type id";
                        return false;
                }
        }
        function set_device_id($value) {
                if (is_numeric($value)) {
                        $this->device_id = $value;
                        return true;
                } else {
                        $this->error = "Invalid Device id";
                        return false;
                }
        }

        function set_notes($value) {
                $this->notes_plain = $value;
        }
        function set_name($value) {
                $this->name = $value;
        }

        function set_private_data($value = '') {
                $this->plain_data = $value;
        }
	
	function insert($key ='') {
		// Test mandatory fields
		if ($key == '') {
			$this->error = "Invalid key password, can not be empty";
			return false;
		}
		if ($this->data_id != '') {
			$this->error = "This is an insert, data_id should be empty";
			return false;
		} 
		if (!is_numeric($this->type_id)) {
			$this->error = "Invalid data Type id";
			return false;
		} 
		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}
		if (is_numeric($this->device_id)) {
			$sql_device_id=  "device_id = '$this->device_id'";
		} else {
			$sql_device_id = "device_id = null";
		}


		// We need to update multiple tables, so we'll use a transaction with commit
		mysql_query("BEGIN") or die("Error, start of transaction failed " . mysql_error());
		$commit_ok = true;

		// First the secret_data part
		$query = " Insert into secret_data SET 
			type_id = '$this->type_id', 
			notes = aes_encrypt('$this->notes_plain','$key'),
			name = '$this->name',
			$sql_device_id
		";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$commit_log = mysql_error() . "  ---query:   $query";
			$commit_ok = false;
		}
		$this->data_id = mysql_insert_id();

		if ($commit_ok == true) {	
			// Now the group part 
			$query = " Insert into secret_data_groups SET 
				secret_data_id = '$this->data_id', 
				aaa_groups_id = '$this->group_id' 
			";
			// execute the query 
			$result =  mysql_query($query) ;
			if (!$result)  {
				$commit_log .= mysql_error() . "  ---query:   $query";
				$commit_ok = false;
			}
		}

		if ($commit_ok == true) {	
			// Now we just need to add the encrypted data
			if (! $this->encrypt($key)) {
				$commit_log .= "<br>" . $this->error;
				$commit_ok = false;
			}
		}

		// Now of all went ok we commit
		if ( $commit_ok == true ) {
			$result = mysql_query("COMMIT") or die("Error, Commit failed " . mysql_error());
			if ($result) {
				return $this->data_id;
			} else {
				$this->data_id = false;
				return false;
			}
		} else {
			$this->data_id = false;
			mysql_query("ROLLBACK") or die("Error, Rollback failed " . mysql_error());
			$this->error = "Failed, doing rollback. $commit_log";
			return false;
		}
	}

	function delete($key ='') {
		if ($key == '') {
			$this->error = "Invalid key password, can not be empty";
			return false;
		}
		if (!is_numeric($this->data_id)) {
			$this->error = "Invalid data id";
			return false;
		} 
		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}
		// Check password
		if (! $this->verify_group_key($this->group_id, $key)) {
			return false;
		}

		// First the secret_data part
		$query = " Delete from secret_data 
			WHERE id = '$this->data_id'";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error();
			return false;
		} else {
			return true;
		}
	}

	function update($key ='') {
		// Test mandatory fields
		if ($key == '') {
			$this->error = "Invalid key password, can not be empty";
			return false;
		}
		if (!is_numeric($this->data_id)) {
			$this->error = "Invalid data id";
			return false;
		} 
		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}
		if (is_numeric($this->device_id)) {
			$sql_device_id = "device_id = '$this->device_id'";
		} else {
			$sql_device_id  ="device_id = null";
		}



		// First check if the provided shared secret is ok
		if (! $this->verify_group_key($this->group_id, $key)) {
			return false;
		}

		// We need a copy of the old password, so we can update the history table
		$old_secret_data = false;
		$old_secret_data = $this->get_private_data($key);
		if ($old_secret_data === false) {
			$this->error = "Unable to determine old password used shared key: $key";
			return false;
		} 


		// We need to update multiple tables, so we'll use a transaction with commit
		mysql_query("BEGIN") or die("Error, start of transaction failed " . mysql_error());
		$commit_ok = true;

		// First the secret_data part
		$query = " Update secret_data SET 
			type_id = '$this->type_id', 
			name = '$this->name',
			notes = aes_encrypt('$this->notes_plain','$key'),
			$sql_device_id  
			WHERE id = '$this->data_id'
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$commit_log = mysql_error() . "  ---query:   $query";
			$commit_ok = false;
		}


		if ($commit_ok == true) {	

			// Only encrypt if they called set_secret_data();
			// If that wasn't called it will be set to false
			if (($this->plain_data != false)) {
				// Now we just need to add the encrypted data
				if (! $this->encrypt($key)) {
					$commit_log .= "<br>" . $this->error;
					$commit_ok = false;
				} else {
					
					// Only thing left is to update the secret_data_history  table
					// Only if the passwords are not the same
					if ($old_secret_data != $this->plain_data) {
						
						$sql_old_secret_data = mysql_real_escape_string($old_secret_data);

						$query = "INSERT INTO secret_data_history SET 
						secret_data_id = '$this->data_id',
						change_time = NOW(),
						encr_data = aes_encrypt('$sql_old_secret_data','$key')
						";
						$result =  mysql_query($query) ;
						// execute the query 
						if (!$result)  {
							$commit_log = mysql_error() . "  ---query:   $query";
							$commit_ok = false;
						}
					}
				}
			}
		}

		// Now of all went ok we commit
		if ( $commit_ok == true ) {
			$result = mysql_query("COMMIT") or die("Error, Commit failed " . mysql_error());
			if ($result) {
				return true;
			} else {
				$this->data_id = false;
				return false;
			}
		} else {
			$this->data_id = false;
			mysql_query("ROLLBACK") or die("Error, Rollback failed " . mysql_error());
			$this->error = "Failed, doing rollback. $commit_log";
			return false;
		}
	}

	function update_private_data($new_pass ='', $old_pass ='') {
		
		// This function is called when changing the shared secret.
		// You provide new and old password, secret will be decrypted with old password
		// and encrypted with new one.
		
		if ($new_pass == $old_pass) {
			$this->error = "Old and New group password are the same";
			return false;
		}

		// Test mandatory fields
		if ($new_pass == '') {
			$this->error = "Invalid (empty) new password";
			return false;
		}
		if (!is_numeric($this->data_id)) {
			$this->error = "Invalid data id";
			return false;
		} 
		if (!is_numeric($this->group_id)) {
			$this->error = "Invalid group id";
			return false;
		}


		// First check if the provided shared secret is ok
		if (! $this->verify_group_key($this->group_id, $old_pass)) {
			return false;
		}

		// Then update secret data
		$query = "UPDATE secret_data SET 
				encr_data = aes_encrypt(aes_decrypt(encr_data,'$old_pass'),'$new_pass')
			WHERE
				id = '$this->data_id'
		";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			$this->error =  mysql_error() . "  ---query:   $query";
			return false;
		}

		// Only thing left is to update the secret_data_history  table

		$query = "UPDATE secret_data_history  SET 
				encr_data = aes_encrypt(aes_decrypt(encr_data,'$old_pass'),'$new_pass')
			WHERE
				secret_data_id = '$this->data_id'
		";
		$result =  mysql_query($query) ;
		// execute the query 
		if (!$result)  {
			$this->error =  mysql_error() . "  ---query:   $query";
			return false;
		}
		return true;
	}

}
class PrivateDataType {

	private $type_id = '';
	private $name;
	private $desc = false;

	private $error = false;
        
	function __construct($type_id = '') {
		if (is_numeric($type_id)) {
			$this->get_private_data_type_info($type_id);
		} 
	}


	private function get_private_data_type_info($type_id){
		$query = "SELECT 
			type_id, type_name, type_desc
		FROM 
			secret_data_types 
		WHERE 
			type_id = '$type_id' 
		";
				
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() ."   -- query: $query ";
			return false;
		}
		if (mysql_numrows($result) < 1 ) {
			$this->error = "No data found for this Type";
			return false;
		}
		while ($obj = mysql_fetch_object($result)){
			$this->type_id = $obj->type_id;
			$this->name = $obj->type_name;
			$this->desc = $obj->type_desc;
		}
		return true;
	}


	public function get_private_data_types() {
		$types = array();
		$query = "SELECT 
			type_id, 
			type_name
		FROM 
			secret_data_types
		";
		$result =  mysql_query($query) ;
		if (!$result)  {
			return false;
		}
		while ($obj = mysql_fetch_object($result)){
			$types[$obj->type_id] = $obj->type_name;
		}
		return $types;
	}

	function get_error() {
		return $this->error;
	}

	function get_id() {
		return $this->type_id;
	}

	function get_name() {
		return $this->name;
	}

	function get_desc() {
		return $this->desc;
	}

        function set_name($value) {
                $this->name = $value;
        }
        function set_desc($value) {
                $this->desc = $value;
        }

	function insert() {
		// Test mandatory fields
		if ($this->type_id != '') {
			$this->error = "This is an insert, type_id should be empty";
			return false;
		} 

		if ($this->name == '') {
			$this->error = "Name cannot be empty";
			return false;
		} 
		// First the secret_data part
		$query = " Insert into secret_data_types SET 
			type_name = '$this->name',
			type_desc = '$this->desc'
		";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
		}
		$this->type_id = mysql_insert_id();
		return $this->type_id;
	}

	function update() {
		// Test mandatory fields
		if (!is_numeric($this->type_id)) {
			$this->error = "Invalid type id";
			return false;
		} 

		if ($this->name == '') {
			$this->error = "Name cannot be empty";
			return false;
		} 
		// First the secret_data part
		$query = " Update secret_data_types SET 
			type_name = '$this->name',
			type_desc = '$this->desc'
			WHERE type_id = '$this->type_id'
		";
		// execute the query 
		$id = false;
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
		}
		return true;
	}

	function delete() {
		// Test mandatory fields
		if (!is_numeric($this->type_id)) {
			$this->error = "Invalid type id";
			return false;
		} 

		// First Check if the type is not referenced somewhere
		$query = "Select secret_data.id FROM
				secret_data, secret_data_types
			WHERE
			secret_data_types.type_id = '$this->type_id'
			AND secret_data_types.type_id = secret_data.type_id
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		if (mysql_numrows($result) > 0 ) {
			$this->error = "Can't remove Private Data Type, there are still " . mysql_numrows($result) ." references";
			return false;
		}
		$query = "delete FROM
				secret_data_types
			WHERE
			secret_data_types.type_id = '$this->type_id'
		";
		// execute the query 
		$result =  mysql_query($query) ;
		if (!$result)  {
			$this->error = mysql_error() . "  ---query:   $query";
			return false;
		}
		return true;
	}
}

?>

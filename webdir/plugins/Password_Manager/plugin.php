<?
include_once 'classes/Form.php';
include_once 'classes/EdittingTools.php';
include_once 'classes/PrivateData.php';
include_once 'classes/AAA.php';


class PasswordManager {

        //renders the content
        function get_content($post='', $get='') {
		$content = '<h2>Password Management</h2>';

		// Check modification actions
		$content .= $this->check_actions();
		
		$content .= $this->list_secret_data($group);

		return $content;
        }

        //renders the configuration
	/*
        function get_config($id='') {

                // MUST HAVE<input type='hidden' name='id' value=".$id."></input>
                // the name of the property must follow the conventions plugin_<Classname>_<propertyName>
                // have the form post and make sure the submit button is named widget_update
                // make sure there is also a hidden value giving the name of this Class file


        }
        
        //updates the configuration, needs to return a true or false value.
        function update_config($values='')
        {
                return true;
        }
	*/


	private function list_secret_data() {
		$content = '';	
		$modal_forms;

		$tool = new EdittingTools();
		$content .= $tool->createNewFilters();
		$content .= "<a name='modal' href='#Add_privatedata_modal'><img src='icons/Add.png' height=18>Add Private Data</a><br><br>";

		// We need to know all groups this user is in:
		$user = new User($_SESSION['userid']);
		$user_groups = $user->get_groups();
		$data = array();


		// Create modal for adding a new Private data entry
		// This modal should ask for which group to add it as and the password

		// We need to know all groups this user is in:
		$user = new User($_SESSION['userid']);
		$user_groups = $user->get_groups();
		if ((sizeof($user_groups)) == 1) {
			foreach ($user_groups as $gid => $gname) {
				$group_data = $gname;
			}
		} else {
			$group_data = "";
		}
		$modalForm = new Form("auto", 2);       
		$modalForm->setHeadings(array("For which group would you like to add private" ));
		$modalForm->setTitles(array("Group","Group Password.tip.This is the shared secret for the group you selected above.",
			"Fill in Private Data Details below:","Description",
			"Private Data <br><small><i>Stored encrypted</i></small>.tip.This data will be AES encrypted",
			"Type <br><small><a name='modal' href='#add_pdtype_modal'>Add Private data type</a></small>",
			"Notes <br><small><i>Stored encrypted</i></small>.tip.This data will be AES encrypted","device_id"));
		$modalForm->setData(array("$group_data","","","","","","",$_GET['ID']));
		$modalForm->setDatabase(array("group_id","group_pass","dummy","private_data_desc","private_data_password",
			"private_data_type","private_data_notes","device_id"));
		$modalForm->setFieldType(array(0=>'drop_down',1=>'password_autocomplete_off',
			2=>'static',5=>'drop_down',6=>'text_area',7=>'hidden'));
		// Drop down
		// We need to know all groups this user is in:
		$modalForm->setType($user_groups);
		$dataTypes = PrivateDataType::get_private_data_types();
		$modalForm->setType($dataTypes);
		//End Dropdown
		// Change button text
		$modalForm->setUpdateValue("add_private_data_for_group");
		$modalForm->setUpdateValue("add_private_data_for_group");
		$modalForm->setUpdateText("Add");
		$modalForm->setModalID("Add_privatedata_modal");
		$modal_forms .= $modalForm->modalForm();
		unset($modalForm);
		// End modal


		// Create modal forms
		// Add Modal for adding Private data types
		$modalForm = new Form("auto", 2);       
		$modalForm->setHeadings(array("<br><br>Add Private Data Type"));
		$modalForm->setTitles(array("Name.tip.Descriptive String for this type","Description"));
		$modalForm->setData(array("",""));
		$modalForm->setDatabase(array("pdtype_name","pdtype_desc"));
		// Change button text
		$modalForm->setUpdateValue("add_private_data_type");
		$modalForm->setUpdateText("Add Private Data Type");
		$modalForm->setModalID("add_pdtype_modal");
		$modal_forms .= $modalForm->modalForm();
		unset($modalForm);
		// End Modal for adding Private data types


		foreach ($user_groups as $gid => $gname) {

			// Create a modal per group, that asks for the group password
			// We only need one per group, as passwords are unqiue per group
			$modalForm = new Form("auto", 2);       
			$modalForm->setHeadings(array("Please provide group password for $gname"));
			$modalForm->setTitles(array("Password","group_id"));
			$modalForm->setData(array("",$gid));
			$modalForm->setDatabase(array('group_pass','group_id'));
			$modalForm->setFieldType(array(0=>'password_autocomplete_off',1=>'hidden'));
			$myModalID = "modal_group_pass_". $gid;
			// Change button text
			$modalForm->setUpdateValue("Decrypt_Private_Data");
			$modalForm->setUpdateText("Submit");
			$modalForm->setModalID($myModalID);
			$modal_forms .= $modalForm->modalForm();
			unset($modalForm);
			// End modal

			$group_private_data = PrivateData::get_private_data_by_group($gid);
			if ($group_private_data) {
			foreach ($group_private_data as $id => $pdname) {
				$privDataObj = new PrivateData($id);

				if (is_numeric($privDataObj->get_device_id()))  {
					// Means device assocication
					continue;
				}

				// Here we check if the user submitted a group password
				// Only for the group for which the pasword has been provided

				$password = "*********";
				$actions = "<a name='modal' href='#modal_group_pass_". $gid."'>Unlock Private Data</a>";
				if ((isset($_POST['group_pass'])) && ($_POST['group_pass'] != '') && ($privDataObj->get_group_id() == $_POST['group_id'])) {
					// now get private data (password)
					$password = $privDataObj->get_private_data($_POST['group_pass']);
					if ($password != false) {
					// Decrypted successful!

						// Get historical data, and create modal
						$modalForm = new Form("auto", 2);       
						$modalForm->setHeadings(array("Changed (exipred) at:","Private Data"));
			
						// Loop through old data and fill arrays for form
						$Htitles=array();
						$Hdata=array();
						$HfieldType=array();
						$historical_passwords =  $privDataObj->get_history($_POST['group_pass']);
						if ($historical_passwords) {
							foreach ($historical_passwords as $old_date =>$old_data) {
								array_push($Htitles, $old_date);
								array_push($Hdata, $old_data);
								array_push($HfieldType, "static");
							}

						}
						$modalForm->setTitles($Htitles);
						$modalForm->setData($Hdata);
						$modalForm->setFieldType($HfieldType);
						unset($Htitles);
						unset($Hdata);
						unset($HfieldType);
						$modalForm->setTitleWidth("40%");
						$modalForm->setDatabase(array('date','old_data'));
						$myHistoryModalID = "modal_old_pass_". $id;
						// Change button text
						$modalForm->setUpdateValue("close");
						$modalForm->setUpdateText("Press cancel");
						$modalForm->setModalID($myHistoryModalID);
						$modal_forms .= $modalForm->modalForm();
						unset($modalForm);
						// End modal

						if ($privDataObj->get_notes($_POST['group_pass']) != '') {
							$name_tooltip = ".tip.<b>Notes:</b><br>".nl2br($privDataObj->get_notes($_POST['group_pass']));
						}


						// Now create a modal that allows us to update the private data object
						// Start Update Modal

						$PdataModal = new Form("auto", 2);      
						$PdataModal->setHeadings(array("Update Private Data"));
						$PdataModal->setTitles(array("Description","Private Data <br><small><i>Stored encrypted</i></small>.tip.This data will be AES encrypted",
							"Type <br><small><a name='modal' href='#add_pdtype_modal'>
							Add Private data type</a></small>","Notes<br><small><i>Stored encrypted</i></small>.tip.This data will be AES encrypted",
							"PDid","",""));
						$PdataModal->setData(array( $privDataObj->get_name(),$password,$privDataObj->get_type_name(),
							$privDataObj->get_notes($_POST['group_pass'] ),$id,
							$_POST['group_id'],$_POST['group_pass']));
						$PdataModal->setDatabase(array('private_data_desc','private_data_password',
							'private_data_type','private_data_notes','private_data_id',
							'group_id','group_pass'));
						$PdataModal->setFieldType(array(2=>'drop_down',3=>'text_area',4=>'hidden',
							5=>'hidden',6=>'hidden'));
						// Creat dropdown
						$dataTypes = PrivateDataType::get_private_data_types();
						$PdataModal->setType($dataTypes);
						$PdataModal->setUpdateValue('update_private_data');
						$PdataModalID = "modal_private_data_id". $id;
						// Change button text
						$PdataModal->setModalID($PdataModalID);
						$modalForms .= $PdataModal->modalForm();
						// End Update modal


						// Now a Modal to Delete an Entry
						// We'll ask for the password again.
						$modalFormDelete = new Form("auto", 2); 
						$modalFormDelete->setHeadings(array("Delete ". $privDataObj->get_name() ."<br>Please provide group password for ". $privDataObj->get_group_name()));
						$modalFormDelete->setTitles(array("Password","group_id",""));
						$modalFormDelete->setData(array("",$privDataObj->get_group_id(),$id));
						$modalFormDelete->setDatabase(array('group_pass','group_id','private_data_id'));
						$modalFormDelete->setFieldType(array(0=>'password_autocomplete_off',1=>'hidden',2=>'hidden'));
						$myDeleteModalID = "modal_delete_pass_". $id;
						// Change button text
						$modalFormDelete->setUpdateValue("delete_private_data");
						$modalFormDelete->setUpdateText("Delete");

						$modalFormDelete->setModalID($myDeleteModalID);
						$modalForms .= $modalFormDelete->modalForm();
						// End Delete modal


						if (count($historical_passwords) > 0) {
							$history_string = "<a name='modal' href='#".$myHistoryModalID."'>History</a>";
						} else {
							$history_string = "<i>No History</i>";
						}

						$actions = "<a name='modal' href='#".$PdataModalID."'>Edit</a> &nbsp&nbsp&nbsp &nbsp&nbsp&nbsp
                                              		<a name='modal' href='#".$myDeleteModalID."'>Delete</a> &nbsp&nbsp&nbsp &nbsp&nbsp&nbsp
							$history_string";
				
					} else {
						$form = new Form();
						$content .= $form->error("Warning: ".$privDataObj->get_error()) ;
					}
				}




				if (count($historical_passwords) > 0) {
					$history_string = "<a name='modal' href='#".$myHistoryModalID."'>History</a>";
				} else {
					$history_string = "<i>No History</i>";
				}
				array_push($data, $privDataObj->get_type_desc().$type_tooltip,
					$privDataObj->get_name().$name_tooltip, $password,
					$privDataObj->get_group_name(), $actions);

			}
			}
		}
		$heading = array("Type","Description","Private Data","Group","Actions");
		$pdata_form = new Form("auto", 5);
                $pdata_form->setSortable(true);
                $pdata_form->setHeadings($heading);
                $pdata_form->setData($data);
                $pdata_form->setTableWidth("800px");
                $content .= $pdata_form->showForm();
                $content .= $modalForms;
		return "$content $modal_forms $private_data_type_modal";;
	}

	private function check_actions() {
		// Here we check if we just deleted a private data entry
                if (isset($_POST['delete_private_data'])) {
			// Delete entry
			return $this->delete_entry();
		}
		if (isset($_POST['add_private_data_type'])) {
			// add private data type entry
			return $this->add_type_entry();
		}
		if (isset($_POST['add_private_data_for_group'])) {
			// Add private data	
			 return $this->add_entry();
		}
		if (isset($_POST['update_private_data'])) {
			return $this->update_entry();
		}
	}

	private function delete_entry() {
		if (isset($_POST['delete_private_data'])) {
			$form = new Form();
			$privDataObj = new PrivateData($_POST['private_data_id']);
			$name = $privDataObj->get_name();
			if ($privDataObj->delete($_POST['group_pass'])) {
				$_SESSION['action'] = "Removed private data for: $name";
				return $form->success("Private entry Deleted") ;
			} else {
				return $form->error("Warning: Failed to delete Private data Reason: ".$privDataObj->get_error(), $_GET['ID']) ;
			}
		}
	}
	
	private function add_type_entry() {
		// Check if we just added a private data Type
		$content = '';
		if (isset($_POST['add_private_data_type'])) {
			$form = new Form();
			$no_error = true;
			// Check mandotry fields
			if (($_POST['pdtype_name'] == '') ) {
				$content .= $form->error("Error: Private DataType name is empty") ;
				$no_error = false;
			}
			elseif (($_POST['pdtype_desc'] == '') ) {
				$content .= $form->error("Error: Private DataType Description is empty") ;
				$no_error = false;
			}
			if ($no_error) {        
				$privDataTypeObj = new PrivateDataType();
				$privDataTypeObj->set_name($_POST['pdtype_name']);
				$privDataTypeObj->set_desc($_POST['pdtype_desc']);
				if ($privDataTypeObj->insert()) {
					$content .= $form->success("Private data type '". $_POST['pdtype_name'] ."' Added") ;
					$_SESSION['action'] = "Added private data Type";
				} else {
					$content .= $form->error("Warning: Failed to Add Private data Reason: ".$privDataTypeObj->get_error());
				}
			}
		}
		return $content;
	}

	private function add_entry() {
		$content ='';
		if (isset($_POST['add_private_data_for_group'])) {
			$form = new Form();
			$no_error = true;
			// Check mandotry fields
			if ((!is_numeric($_POST['group_id']))  ) {
				$content .= $form->error("Error: No Group Specified") ;
				$no_error = false;
			}
			elseif ((!is_numeric($_POST['private_data_type'])) ) {
				$content .= $form->error("Error: No Private Data type specified") ;
				$no_error = false;
			}
			elseif (($_POST['private_data_password'] == '') ) {
				$content .= $form->error("Warning: Private Data string was empty") ;
				//$no_error = false;
			}
			if ($no_error) {        
				$privDataObj = new PrivateData();
				$privDataObj->set_group_id($_POST['group_id']);
				$privDataObj->set_type_id($_POST['private_data_type']);
				$privDataObj->set_notes($_POST['private_data_notes']);
				$privDataObj->set_name($_POST['private_data_desc']);
				$privDataObj->set_private_data($_POST['private_data_password']);
                                        
				if ($privDataObj->insert($_POST['group_pass'])) {
					$content .= $form->success("Private data entry Added") ; 
					$_SESSION['action'] = "Added private data: ".$_POST['private_data_desc'];
				} else {
					$content .= $form->error("Warning: Failed to Add Private data Reason: ".$privDataObj->get_error(), $_GET['ID']) ;
				}
			}
		}
		return $content;
	}
	


	private function update_entry() {	
		$content ='';
		if (isset($_POST['update_private_data'])) {
			// Yes update
			$tmpform = new Form();
			$privDataObj = new PrivateData($_POST['private_data_id']);
			$privDataObj->set_name($_POST['private_data_desc']);
			$privDataObj->set_notes($_POST['private_data_notes']);
			$privDataObj->set_type_id($_POST['private_data_type']);
			$privDataObj->set_private_data($_POST['private_data_password']);
			if ($privDataObj->update($_POST['group_pass'])) {
				$content .= $tmpform->success("Private data updated Succesfully") ;
				$_SESSION['action'] = "Updated private data for: ".$_POST['private_data_desc'];
			} else {
				$content .= $tmpform->error("Warning: Failed to Update Private data Reason: ".$privDataObj->get_error(), $_GET['ID']) ;
			}
		}
		return $content;
	}
                                
}

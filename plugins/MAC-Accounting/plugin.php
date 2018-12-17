<?
include_once "classes/Device.php";
include_once 'classes/Form.php';

class MACAccounting
{
    // init() is triggered when plugin is enabled.
    function init()
    {
        $create_table_query =
            "CREATE TABLE IF NOT EXISTS `plugin_MACAccounting_devices`
              (
                `device_id` int(11)    NOT NULL COMMENT 'Points to devices',
                `enabled`   tinyint(2) NOT NULL COMMENT 'enabled (1) or not (0)',
                PRIMARY KEY (`device_id`),
                CONSTRAINT `plugin_MACAccounting_devices_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='MAC Accounting';";
        $create_result = mysql_query($create_table_query);
        if (!$create_result) {
            return false;
        }
        return true;
    }

    // renders the content on it's own plugin page
    // function get_content() { }

    // renders the config box
    function get_config($id='')
    {

        $select_query  = "SELECT device_id, enabled FROM plugin_MACAccounting_devices";
        $select_result = mysql_query($select_query);
        if (!$select_result) {
            return "<b>Oops something went wrong, unable to read plugin_MACAccounting_devices SQL table. Maybe it doesn't exist. Try enabling the plugin first!</b>";
        }
        $devices = array();
        while ($obj = mysql_fetch_object($select_result)) {
            $devices[$obj->device_id] = $obj->enabled;
        }

        $content = "<h1>Please select the Devices you would like to gather MAC accounting for</h1>";

        $content .= "<form id='configForm' method='post' name='edit_devices'>
                     <input type='hidden' name='class' value='MACAccounting'></input>
                     <input type='hidden' name='id' value=".$id."></input>";

        $select_all ="<input name='all' type='checkbox' value='Select All' onclick=\"checkAll(document.edit_devices['devices[]'],this)\"";

        $form = new Form("auto",4);
        $keyData = array();

        foreach (Device::get_devices() as $id => $name) {
            if ((array_key_exists($id, $devices)) && ($devices[$id] == 1)) {
                $checked = "checked='yes'";
            } else { $checked = "";}

            $deviceInfo = new Device($id);
            array_push($keyData, "<input type=checkbox name=devices[] value='$id' $checked >");
            array_push($keyData, $name);
            array_push($keyData, $deviceInfo->get_type_name());
            array_push($keyData, $deviceInfo->get_location_name());
        }

        // get all the device and display them all in the 3 sections "Device Name", "Device Type", "Location".
        $heading = array($select_all, "Device Name", "Device Type", "Location ");
        $form->setSortable(true); // or false for not sortable
        $form->setHeadings($heading);
        $form->setData($keyData);
        $form->setTableWidth("auto");
        $content .= $form->showForm();

        $content .= "<div style='clear:both;'></div><input type='submit' class='submitBut' name='plugin_update' value='Update configuration'/>
			         </form> ";
        return "$content";

    }

    // updates the configuration from config box, needs to return true or false.
    function update_config($values='')
    {

        // delete all values in table
        $delete_query = "DELETE FROM plugin_MACAccounting_devices";
        $delete_result =  mysql_query($delete_query) ;
        if (!$delete_result)  {
            return false;
        }

        // add all selected devices
        foreach($_POST['devices'] as $key => $dev_id) {
            // This is a new device, insert
            $insert_query = "INSERT INTO plugin_MACAccounting_devices SET enabled = '1', device_id = '$dev_id'";
            $insert_result =  mysql_query($insert_query) ;
            if (!$insert_result)  {
                return false;
            }
        }
        return true;

    }

}

?>
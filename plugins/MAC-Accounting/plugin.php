<?php

include_once 'plugins/MAC-Accounting/model.php';
include_once 'plugins/MAC-Accounting/template.php';

class MACAccounting
{

    // TODO move create table code to model class
    // initialization function run only when plugin is first enabled
    public function on_enable()
    {
        $create_device_table =
            "CREATE TABLE IF NOT EXISTS `plugin_MACAccounting_devices`
              (
                `device_id` int(11)    NOT NULL COMMENT 'Points to devices',
                `enabled`   tinyint(2) NOT NULL COMMENT 'enabled (1) or not (0)',
                PRIMARY KEY (`device_id`),
                CONSTRAINT `plugin_MACAccounting_devices_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE
              ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='MAC Accounting devices being polled'";
        $create_result = mysql_query($create_device_table);
        if (!$create_result) {
            return false;
        }
        return true;

        $create_info_table =
            "CREATE TABLE IF NOT EXISTS `plugin_MACAccounting_info`
            (
              `device_id`       int(11) NOT NULL COMMENT 'Points to MACAccounting device table',
              `device_name`     varchar(100) NOT NULL COMMENT 'FQDN of Device',
              `interface_name`  varchar(50) NOT NULL COMMENT 'interface name, e.g. xe-0/0/1',
              `interface_descr` varchar(200) NOT NULL COMMENT 'human defined interface description',
              `ip_address`      varchar(40) NOT NULL COMMENT 'ip address',
              `mac_address`     varchar(20) NOT NULL COMMENT 'mac address',
              PRIMARY KEY (`device_id`, `ip_address`, `mac_address`),
              CONSTRAINT `plugin_MACAccounting_info_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `plugin_MACAccounting_devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE 
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='mac and ip details for MAC Accounting'";
        $create_result = mysql_query($create_info_table);
        if (!$create_result) {
            return falsee;
        }
        return true;
    }

    // renders the content on it's own plugin page
    public function get_content()
    {
        $url = $_SERVER['SCRIPT_NAME']."?tab=".$_GET['tab']."&pluginID=".$_GET['pluginID'];

        if ($_GET['action'] == 'show_macaccounting_info') {
            return $this->renderDeviceAccounting($url, $_GET['device_id']);
        } elseif ($_GET['action'] == 'show_macaccounting_detail') {
            return $this->renderDetailAccounting($url, $_GET['device_name'], $_GET['ip']);
        } else {
            return $this->renderDeviceList($url);
        }
    }

    // renders the config box
    public function get_config($id='')
    {
        // instantiate objects
        $view  = new Template();
        $model = new Model();

        // initialize variables
        $devices   = array();
        $tableData = array();
        $header    = array(
            "",
            "Device Name",
            "Device Type",
            "Location"
        );

        $form = $view->tableCreate("auto", 4, true, $header, "auto");

        $view->header = "Select the Devices to poll for MAC Accounting";
        $view->value = "MACAccounting";
        $view->id = $id;

        // get all MAC Accounting devices, return failure text on sql failure
        $result = $model->selMACAcctDevs();
        if (!$result) {
            $view->errorMessage = "Unable to read plugin_MACAccounting_devices table. Try enabling the plugin first!";
            return $view->render('error.php');
        };

        while ($obj = $model->fetchObject($result)) {
            $devices[$obj->device_id] = $obj->enabled;
        }

        // get all devices, compare with enabled devices for polling. If a match, check the box.
        foreach ($model->getAllDevices() as $id => $name) {

            $devObj = $model->deviceObject($id);

            if ((array_key_exists($id, $devices)) && ($devices[$id] == 1)) {
                $checked = "checked='yes'";
            } else {
                $checked = "";
            }

            // push table row elements into array
            array_push($tableData, $view->tableCheckBox($id, $checked));
            array_push($tableData, $name);
            array_push($tableData, $devObj->get_type_name());
            array_push($tableData, $devObj->get_location_name());
        }

        $view->tableSet($form, $tableData);

        $view->netharbourTable = $view->tableHTML($form);
        $view->buttonName      = "plugin_update";
        $view->buttonValue     = "Update configuration";

        return $view->render('deviceConfig.php');
    }

    // updates the configuration from config box, needs to return true or false.
    public function update_config($values='')
    {
        $model = new Model();

        $result = $model->delMACAcctDevs();
        if (!$result) {
            return false;
        }

        // add all selected devices
        foreach($_POST['devices'] as $key => $id) {
            $result = $model->insertDevEnabled($id);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    ##### UI render functions #####

    private function renderDeviceList($url)
    {
        // instantiate objects
        $view  = new Template();
        $model = new Model();

        // initialize variables
        $tableData = array();
        $handler   = array();
        $header    = array("Devices");

        $form = $view->tableCreate("auto", 1, true, $header, "678px");

        $result = $model->selMACAcctDevName();
        if (!$result) {
            $view->errorMessage = "Oops, something went wrong with getting MAC Accounting device list";
            return $view->render('error.php');
        }

        $view->header = "MAC Accounting";

        // add table data and generate device links
        while ($obj = $model->fetchObject($result)) {
            array_push($tableData, $obj->name);
            $url2 = $url . "&action=show_macaccounting_info&device_id=$obj->device_id";
            array_push($handler, "handleEvent('$url2')");
        }

        $view->tableHandler($form, $handler);
        $view->tableSet($form, $tableData);
        $view->netharbourTable = $view->tableHTML($form);

        return $view->render('deviceList.php');
    }

    private function renderDeviceAccounting($url, $id)
    {
        // instantiate objects
        $view   = new Template();
        $model  = new Model();
        $device = $model->deviceObject($id);

        // initialize variables
        $tableData = array();
        $handler   = array();
        $header    = array("IP Address", "MAC Address");

        $form = $view->tableCreate("auto", 2, true, $header, "678px");

        $result = $model->selMACAcctIPMAC($id);
        if (!$result) {
            $view->errorMessage = "Oops, something went wrong with getting MAC Accounting device info";
            return $view->render('error.php');
        }

        $view->header = "MAC Accounting";

        // add table data and generate device links
        while ($obj = $model->fetchObject($result)) {
            array_push ($tableData, $obj->ip_address, $obj->mac_address);
            $url2 = $url . "&action=show_macaccounting_detail&device_name=" . $device->get_name() . "&ip=$obj->ip_address";
            array_push($handler, "handleEvent('$url2')");
        }

        $view->tableHandler($form, $handler);
        $view->tableSet($form, $tableData);
        $view->netharbourTable = $view->tableHTML($form);

        return $view->render('deviceAcct.php');
    }


    // renders the graph for specific MAC/IP accounting
    private function renderDetailAccounting($url, $device_name, $ip) {

        // instantiate objects
        $model    = new Model();
        $view     = new Template();
        $property = $model->propertyObject();

        // initialize variables
        $tableData  = array();
        $handler    = array();
        $header     = array($device_name);
        $now        = "-1s";
        $graphTimes = array(
            "Last Day"   => "-24h",
            "Last Week"  => "-7d",
            "Last Month" => "-1m",
            "Last Year"  => "-1y"
        );
        $graphHeight = 150;
        $graphWidth  = 900;
        $graphUnit   = "Bits Per Second";
        $graphType   = "traffic";
        $graphUnit   = str_replace(" ", "%20", $graphUnit);
        $graphType   = str_replace(" ", "%20", $graphType);

        // TODO handle file not found
        $rrd_dir  = $property->get_property("path_rrddir");
        $rrdFile  = "$rrd_dir" . "/MAC-ACCT_" . "$ip" . "_device_" . "$device_name" . ".rrd";

        $form = $view->tableCreate("auto", 1, true, $header, "900px");
        $view->header = "MAC Accounting";

        // TODO maybe embed this loop into graph.php? Then graph.php can be re-used easily???
        foreach ($graphTimes as $timeMnemonic => $RRDtime) {
            $pathParts = pathinfo($rrdFile);
            $filename  = $pathParts['filename'] . ".rrd";

            $graphLink =
                "rrdgraph.php?" .
                "file="   . $filename .
                "&title=" . $ip .
                "---" . $graphUnit .
                "&height=" . $graphHeight .
                "&width=" . $graphWidth .
                "&type=" . $graphType .
                "&from=" . $RRDtime .
                "&to=" . $now;

            $view->timeMnemonic = $timeMnemonic;
            $view->graphLink    = $graphLink;
            array_push ($tableData, $view->render('graph.php'));
        }

        $view->tableHandler($form, $handler);
        $view->tableSet($form, $tableData);
        $view->netharbourTable = $view->tableHTML($form);

        return $view->render('deviceAcctDetail.php');
    }
}

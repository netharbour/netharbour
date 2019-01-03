<?php

include_once 'plugins/MAC-Accounting/model.php';
include_once 'plugins/MAC-Accounting/template.php';

class MACAccounting
{
    // TODO test on_enable initialization (specifically how the error handling works)
    // initialization function run only when plugin is first enabled
    public function on_enable()
    {
        $model = new Model();
        $view  = new Template();

        if (!$model->createMACAcctDevs()) {
            $view->errorMessage = "Oops, something went wrong with creating plugin_MACAccounting_devices DB table.";
            return $view->render('error.php');
        }

        if (!$model->createMACAcctInfo()) {
            $view->errorMessage = "Oops, something went wrong with creating plugin_MACAccounting_info DB table.";
            return $view->render('error.php');
        }

    }

    // renders the content on it's own plugin page, handles user input
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
        $devices      = array();
        $tableData1   = array();
        $tableData2   = array();
        $header1      = array(
            "",
            "Plugin Setting",
            "Description",
            "Notes"
        );
        $header2      = array(
            "",
            "Device Name",
            "Device Type",
            "Location"
        );

        $form1 = $view->tableCreate("auto", 4, true, $header1, "auto");
        $form2 = $view->tableCreate("auto", 4, true, $header2, "auto");

        $view->header = "Select the Devices to poll for MAC Accounting";
        $view->value = "MACAccounting";
        $view->id = $id;

        // get all MAC Accounting devices, return failure text on sql failure
        $result = $model->selMACAcctDevs();
        if (!$result) {
            $view->errorMessage = "Unable to read plugin_MACAccounting_devices table. Try enabling the plugin first!";
            return $view->render('error.php');
        };

        // ensure ip_resolve and whois_lookup is checked if enabled. also populate $devices array with enable state
        while ($obj = $model->fetchObject($result)) {
            $devices[$obj->device_id] = $obj->enabled;

            if ($obj->ip_resolve == 1) {
                $ipResolveChecked = "checked='yes'";
            } else {
                $ipResolveChecked = "";
            }

            if ($obj->whois_lookup == 1) {
                $whoisLookupChecked = "checked='yes'";
            } else {
                $whoisLookupChecked = "";
            }
        }

        // push table row elements into array for configuration settings
        array_push($tableData1,
            $view->tableCheckBox("ip_resolve", 1, $ipResolveChecked)
        );
        array_push($tableData1,
            "Reverse DNS Lookup",
            "Resolve IP to it's FQDN (only if PTR records exist)",
            "**Can slow down MAC Accounting polling if PTR records don't exist and the reverse lookup is timing out**"
        );
        array_push($tableData1,
            $view->tableCheckBox("whois_lookup", 1, $whoisLookupChecked)
        );
        array_push($tableData1,
            "ASN Whois Lookup",
            "Whois lookup based on ASN number in hostname (gathered from the reverse DNS lookup)",
            "**The only numbers in the hostname have to be the ASN (e.g. asXXXX.sub.domain)**"
        );

        // get all devices, compare with enabled devices for polling. If a match, check the box.
        foreach ($model->getAllDevices() as $id => $name) {

            $devObj = $model->deviceObject($id);
            if ((array_key_exists($id, $devices)) && ($devices[$id] == 1)) {
                $deviceChecked = "checked='yes'";
            } else {
                $deviceChecked = "";
            }

            // push table row elements into array for devices
            array_push($tableData2, $view->tableCheckBox("devices[]", $id, $deviceChecked));
            array_push($tableData2, $name);
            array_push($tableData2, $devObj->get_type_name());
            array_push($tableData2, $devObj->get_location_name());
        }

        $view->tableSet($form1, $tableData1);
        $view->tableSet($form2, $tableData2);

        $view->netharbourTable1 = $view->tableHTML($form1);
        $view->netharbourTable2 = $view->tableHTML($form2);
        $view->buttonName       = "plugin_update";
        $view->buttonValue      = "Update configuration";

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
            if ($_POST['ip_resolve'] == 1) {
                $ip_resolve = 1;
            } else {
                $ip_resolve = 0;
            }

            if ($_POST['whois_lookup'] == 1) {
                $whois_lookup = 1;
            } else {
                $whois_lookup = 0;
            }

            $result = $model->insertEnabledDev($id, $ip_resolve, $whois_lookup);
            if (!$result) {
                return false;
            }
        }

        return true;
    }

    ##### UI render functions #####

    // renders the devices that are being polled
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

        return $view->render('pluginDisplay.php');
    }

    // renders the MAC and IP details
    private function renderDeviceAccounting($url, $id)
    {
        // instantiate objects
        $view   = new Template();
        $model  = new Model();
        $device = $model->deviceObject($id);

        // initialize variables
        $tableData = array();
        $handler   = array();
        $header    = array(
            "MAC Address",
            "IP Address",
            "Reverse DNS Lookup",
            "ASN lookup"
        );

        $form = $view->tableCreate("auto", 4, true, $header, "1024px");

        $result = $model->selMACAcctInfo($id);
        if (!$result) {
            $view->errorMessage = "Oops, something went wrong with getting MAC Accounting device info";
            return $view->render('error.php');
        }

        $view->header = "MAC Accounting";

        // add table data and generate device links
        while ($obj = $model->fetchObject($result)) {
            array_push ($tableData, $obj->mac_address, $obj->ip_address, $obj->resolved_ip, $obj->org_name);
            $url2 = $url . "&action=show_macaccounting_detail&device_name=" . $device->get_device_fqdn() . "&ip=$obj->ip_address";
            array_push($handler, "handleEvent('$url2')");
        }

        $view->tableHandler($form, $handler);
        $view->tableSet($form, $tableData);
        $view->netharbourTable = $view->tableHTML($form);

        return $view->render('pluginDisplay.php');
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

        return $view->render('pluginDisplay.php');
    }
}

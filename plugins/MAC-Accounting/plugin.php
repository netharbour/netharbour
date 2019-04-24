<?php

include_once 'plugins/MAC-Accounting/model.php';
include_once 'plugins/MAC-Accounting/template.php';

class MACAccounting
{
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
            return $this->renderDetailAccounting($url, $_GET['device_id'], $_GET['ip']);
        } elseif ($_GET['action'] == 'show_macaccounting_zoom') {
            return $this->renderZoomGraph($url, $_GET['device_id'], $_GET['ip'], $_GET['from'], $_GET['to']);
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

        // ensure ip_resolve and asn_resolve is checked if enabled. also populate $devices array with enable state
        while ($obj = $model->fetchObject($result)) {
            $devices[$obj->device_id] = $obj->enabled;

            if ($obj->ip_resolve == 1) {
                $ipResolveChecked = "checked='yes'";
            } else {
                $ipResolveChecked = "";
            }

            if ($obj->asn_resolve == 1) {
                $asnResolveChecked = "checked='yes'";
            } else {
                $asnResolveChecked = "";
            }
        }

        // push table row elements into array for configuration settings
        array_push($tableData1,
            $view->tableCheckBox("ip_resolve", 1, $ipResolveChecked)
        );
        array_push($tableData1,
            "Reverse DNS Lookup",
            "Resolve IP to it's FQDN (only if PTR records exist)",
            "**Can slow down MAC Accounting polling if PTR records don't exist**"
        );
        array_push($tableData1,
            $view->tableCheckBox("asn_resolve", 1, $asnResolveChecked)
        );
        array_push($tableData1,
            "OrgName Lookup",
            "OrgName lookup based on ASN, derived from IP address",
            "**Uses peeringdb API. If OrgName is blank, it's because peeringdb has incomplete peer info. Use manual override in config file.**"
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

            if ($_POST['asn_resolve'] == 1) {
                $asn_resolve = 1;
            } else {
                $asn_resolve = 0;
            }

            $result = $model->insertEnabledDev($id, $ip_resolve, $asn_resolve);
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
        $model  = new Model();
        $view   = new Template();

        // initialize variables
        $tableData = array();
        $handler   = array();
        $header    = array(
            "MAC Address",
            "IP Address",
            "FQDN",
            "Org Name"
        );

        $form = $view->tableCreate("auto", 4, true, $header, "900px");

        $result = $model->selMACAcctInfo($id);
        if (!$result) {
            $view->errorMessage = "Oops, something went wrong with getting MAC Accounting device info";
            return $view->render('error.php');
        }

        $view->header = "MAC Accounting";

        // add table data and generate device links
        while ($obj = $model->fetchObject($result)) {
            array_push ($tableData, $obj->mac_address, $obj->ip_address, $obj->resolved_ip, $obj->org_name);
            $url2 = $url . "&action=show_macaccounting_detail&device_id=" . $id . "&ip=$obj->ip_address";
            array_push($handler, "handleEvent('$url2')");
        }

        $view->tableHandler($form, $handler);
        $view->tableSet($form, $tableData);
        $view->netharbourTable = $view->tableHTML($form);

        return $view->render('pluginDisplay.php');
    }

    // renders the graph for specific MAC/IP accounting
    private function renderDetailAccounting($url, $id, $ip)
    {
        // instantiate objects
        $model    = new Model();
        $view     = new Template();
        $property = $model->propertyObject();
        $device   = $model->deviceObject($id);

        // initialize variables
        $tableData  = array();
        $handler    = array();
        $header     = array($device->get_device_fqdn());
        $now        = time();
        $graphTimes = array(
            "Last Day"   => (time() - (24 * 60 * 60)),
            "Last Week"  => (time() - (7 * 24 * 60 * 60)),
            "Last Month" => (time() - (31 * 24 * 60 * 60)),
            "Last Year"  => (time() - (365 * 24 * 60 * 60))
        );
        $graphHeight = 150;
        $graphWidth  = 900;
        $graphUnit   = "Bits Per Second";
        $graphType   = "traffic";
        $graphUnit   = str_replace(" ", "%20", $graphUnit);
        $graphType   = str_replace(" ", "%20", $graphType);

        $rrd_dir  = $property->get_property("path_rrddir");
        $rrdFile  = "$rrd_dir" . "/MAC-ACCT_" . "$ip" . "_device_" . $device->get_device_fqdn() . ".rrd";

        $form = $view->tableCreate("auto", 1, true, $header, "900px");
        $view->header = "MAC Accounting";

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
            $url2 = $url . "&action=show_macaccounting_zoom&device_id=$id&ip=$ip&from=$RRDtime&to=$now";
            array_push($handler, "handleEvent('$url2')");
        }

        $view->tableHandler($form, $handler);
        $view->tableSet($form, $tableData);
        $view->netharbourTable = $view->tableHTML($form);

        return $view->render('pluginDisplay.php');
    }

    private function renderZoomGraph($url, $id, $ip, $from, $to)
    {
        // instantiate objects
        $model    = new Model();
        $view     = new Template();
        $property = $model->propertyObject();
        $device   = $model->deviceObject($id);

        // initialize variables
        $tableData   = array();
        $handler     = array();
        $header      = array($device->get_device_fqdn());
        $graphHeight = 150;
        $graphWidth  = 900;
        $graphUnit   = "Bits Per Second";
        $graphType   = "traffic";
        $graphUnit   = str_replace(" ", "%20", $graphUnit);
        $graphType   = str_replace(" ", "%20", $graphType);

        $rrd_dir  = $property->get_property("path_rrddir");
        $rrdFile  = "$rrd_dir" . "/MAC-ACCT_" . "$ip" . "_device_" . $device->get_device_fqdn() . ".rrd";
        $rrdFileName = "MAC-ACCT_" . "$ip" . "_device_" . $device->get_device_fqdn() . ".rrd";

        $form = $view->tableCreate("auto", 1, true, $header, "900px");
        $view->header = "MAC Accounting";

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
            "&from=" . $from .
            "&to=" . $to;

        $view->graphLink     = $graphLink;
        $view->from          = $from;
        $view->rrdFileName   = $rrdFileName;
        $view->graphType     = $graphType;
        $view->to            = $to;
        array_push ($tableData, $view->render('zoomGraph.php'));

        $view->tableHandler($form, $handler);
        $view->tableSet($form, $tableData);
        $view->netharbourTable = $view->tableHTML($form);

        return $view->render('pluginDisplay.php');
    }
}

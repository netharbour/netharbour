<?php

namespace plugin_MACAccounting;

include_once "classes/Device.php";
include_once 'classes/Property.php';

use Device;
use Property;

# Note: the old MySQL API is used to access the DB. This keeps the plugin consistent with the rest of the codebase.
# A decision on refactoring the DB code has not been made currently. When the DB connection code is refactored, then
# this plugin will be addressed at the same time.

class Model
{
    public function __construct()
    {
        // pass
    }

    public function fetchObject($queryResult)
    {
        return mysql_fetch_object($queryResult);
    }

    public function createMACAcctDevs()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS `plugin_MACAccounting_devices`
            (
                `device_id` int(11)    NOT NULL COMMENT 'Points to devices',
                `enabled`   tinyint(2) NOT NULL COMMENT 'enabled (1) or not (0)',
                `ip_resolve` tinyint(2) NOT NULL COMMENT 'enabled (1) or not (0)',
                `asn_resolve` tinyint(2) NOT NULL COMMENT 'enabled (1) or not (0)',
                PRIMARY KEY (`device_id`),
                CONSTRAINT `plugin_MACAccounting_devices_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='MAC Accounting devices being polled'
        ";

        $result = mysql_query($query);
        return $result;
    }

    public function createMACAcctInfo()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS `plugin_MACAccounting_info`
            (
                `device_id`       int(11) NOT NULL COMMENT 'Points to MACAccounting device table',
                `device_name`     varchar(255) NOT NULL COMMENT 'FQDN of Device',
                `ip_address`      varchar(50) NOT NULL COMMENT 'ip address',
                `mac_address`     varchar(20) NOT NULL COMMENT 'mac address',
                `resolved_ip`     varchar(255) NOT NULL COMMENT 'DNS name of harvested ip',
                `org_name`        varchar(200) NOT NULL COMMENT 'Org name derived from asn',
                PRIMARY KEY (`device_id`, `ip_address`, `mac_address`),
                CONSTRAINT `plugin_MACAccounting_info_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `plugin_MACAccounting_devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='mac and ip details for MAC Accounting'
        ";

        $result = mysql_query($query);
        return $result;
    }

    public function selMACAcctDevs()
    {
        $query = "
            SELECT device_id, enabled, ip_resolve, asn_resolve
            FROM plugin_MACAccounting_devices
        ";

        $result = mysql_query($query);
        return $result;
    }

    public function delMACAcctDevs()
    {
        $query = "
            DELETE FROM plugin_MACAccounting_devices
        ";
        $result =  mysql_query($query) ;
        return $result;
    }

    public function insertEnabledDev($id, $ip_resolve, $asn_resolve)
    {
        $query = "
            INSERT INTO plugin_MACAccounting_devices 
            SET
                device_id = '$id',
                enabled = '1',
                ip_resolve = '$ip_resolve',
                asn_resolve = '$asn_resolve'
        ";
        $result =  mysql_query($query) ;
        return $result;
    }

    public function selMACAcctDevName()
    {
        $query = "
            SELECT plugin_MACAccounting_devices.device_id, Devices.name 
            FROM plugin_MACAccounting_devices, Devices 
            WHERE plugin_MACAccounting_devices.device_id = Devices.device_id 
                AND plugin_MACAccounting_devices.enabled = '1'
        ";
        $result = mysql_query($query);
        return $result;
    }

    public function selMACAcctInfo($id)
    {
        $query = "
            SELECT 
                   plugin_MACAccounting_info.ip_address, 
                   plugin_MACAccounting_info.mac_address, 
                   plugin_MACAccounting_info.resolved_ip,
                   plugin_MACAccounting_info.org_name
            FROM plugin_MACAccounting_info
            WHERE plugin_MACAccounting_info.device_id = '$id'";
        $result = mysql_query($query);
        return $result;
    }

    ##### access to netharbour's Device() class

    public function deviceObject($id)
    {
        return new Device($id);
    }

    public function getAllDevices()
    {
        return Device::get_devices();
    }

    ##### access to netharbour's Property() class

    public function propertyObject()
    {
        return new Property();
    }
}

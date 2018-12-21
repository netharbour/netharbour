<?php

include_once "classes/Device.php";
include_once 'classes/Property.php';

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

    public function selMACAcctDevs()
    {
        $query = "
            SELECT device_id, enabled 
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

    public function insertDevEnabled($id)
    {
        $query = "
            INSERT INTO plugin_MACAccounting_devices 
            SET enabled = '1', device_id = '$id'
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

    public function selMACAcctIPMAC($id)
    {
        $query = "
            SELECT plugin_MACAccounting_info.ip_address, plugin_MACAccounting_info.mac_address, plugin_MACAccounting_info.resolved_ip
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

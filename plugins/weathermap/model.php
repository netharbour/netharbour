<?php

namespace plugin_weathermap;

include_once "classes/Device.php";
include_once 'classes/Property.php';

use Device;
use Property;

# Note: the old MySQL API is used to access the DB. This keeps the plugin consistent with the rest of the codebase.

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

    public function createWeathermapConfiguration()
    {
        $query = "
            CREATE TABLE IF NOT EXISTS `plugin_Weathermap_configuration`
            (
                `id` int NOT NULL AUTO_INCREMENT,
                `configuration_file` varchar(100) NULL DEFAULT NULL COMMENT 'a weathermap configuration file',
                `custom_image_name`  varchar(100) NULL DEFAULT NULL COMMENT 'a custom weathermap image name',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Weathermap plugin configuration files that are fed into weathermap'
        ";
        $result = mysql_query($query);
        return $result;
    }

    public function selectAllWeathermapConfiguration()
    {
        $query = "
            SELECT id, configuration_file, custom_image_name
            FROM plugin_Weathermap_configuration
            WHERE configuration_file IS NOT NULL
        ";

        $result = mysql_query($query);
        return $result;
    }

    public function deleteWeathermapConfiguration()
    {
        $query = "
            DELETE 
            FROM plugin_Weathermap_configuration
        ";

        $result = mysql_query($query);
        return $result;
    }

    public function insertWeathermapConfiguration($configuration, $image_name)
    {
        $query = "
            INSERT INTO plugin_Weathermap_configuration
            SET
                configuration_file = '$configuration',
                custom_image_name = '$image_name'
        ";

        $result = mysql_query($query);
        return $result;
    }
    
    public function insertNullWeathermapConfiguration()
    {
        $query = "
            INSERT INTO plugin_Weathermap_configuration
            SET
                configuration_file = NULL,
                custom_image_name = NULL
        ";

        $result = mysql_query($query);
        return $result;
    }

    ### Access to netharbour's Device() class

    public function deviceObject($id)
    {
        return new Device($id);
    }

    public function getAllDevices()
    {
        return Device::get_devices();
    }

    ### Access to netharbour's Property() class

    public function propertyObject()
    {
        return new Property();
    }

}

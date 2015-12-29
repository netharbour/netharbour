USE `netharbour`;
SET FOREIGN_KEY_CHECKS = 0;


-- MySQL dump 10.13  Distrib 5.1.73, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: netharbour
-- ------------------------------------------------------
-- Server version	5.1.73

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `AAA_groups`
--

DROP TABLE IF EXISTS `AAA_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AAA_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_name` varchar(100) NOT NULL,
  `group_desc` varchar(100) NOT NULL,
  `ldap_group_name` varchar(100) NOT NULL COMMENT 'ldap group name',
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  `access_level` int(11) NOT NULL DEFAULT '25' COMMENT 'access level (value between 0 (no access)- 100 (admin access) ',
  `verification_string_encr` blob COMMENT 'contains the encrypted string for password verification',
  `group_pass` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'is group using a group_pass, for secret data',
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `AAA_users`
--

DROP TABLE IF EXISTS `AAA_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AAA_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_pwd` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_type` varchar(100) NOT NULL COMMENT 'local or ldap users',
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  `last_login` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_ip` varchar(40) NOT NULL COMMENT 'last ip address login',
  PRIMARY KEY (`user_id`),
  KEY `user_name` (`user_name`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=latin1 COMMENT='local users';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `AAA_users_groups`
--

DROP TABLE IF EXISTS `AAA_users_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `AAA_users_groups` (
  `group_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `group_id` (`group_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `AAA_users_groups_ibfk_4` FOREIGN KEY (`user_id`) REFERENCES `AAA_users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `AAA_users_groups_ibfk_5` FOREIGN KEY (`group_id`) REFERENCES `AAA_groups` (`group_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='mapping users to groups';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Dashboard_users`
--

DROP TABLE IF EXISTS `Dashboard_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Dashboard_users` (
  `user_id` int(11) NOT NULL COMMENT 'the user id',
  `widget_id` int(11) NOT NULL COMMENT 'the enabled widgets ID',
  `position_x` int(11) NOT NULL DEFAULT '0' COMMENT 'column of the widget',
  `position_y` int(11) NOT NULL DEFAULT '0' COMMENT 'rows of the widget',
  KEY `user_id` (`user_id`),
  KEY `widget_id` (`widget_id`),
  CONSTRAINT `Dashboard_users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `AAA_users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `Dashboard_users_ibfk_2` FOREIGN KEY (`widget_id`) REFERENCES `Dashboard_widgets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='This table is for the user settings';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Dashboard_widgets`
--

DROP TABLE IF EXISTS `Dashboard_widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Dashboard_widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'the id of the widget',
  `name` varchar(250) NOT NULL COMMENT 'the name of the widget',
  `class_name` varchar(250) NOT NULL COMMENT 'classname for the widget to instantiate',
  `description` varchar(500) NOT NULL COMMENT 'the description of the widget',
  `enabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'is the widget enabled',
  `version` varchar(11) NOT NULL COMMENT 'the version of the widget',
  `filename` varchar(250) NOT NULL COMMENT 'the filename of the file',
  `config_filename` varchar(250) NOT NULL COMMENT 'read the config file for info',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Device_types`
--

DROP TABLE IF EXISTS `Device_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Device_types` (
  `device_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'device type name, used top refer to this device type ',
  `description` varchar(500) DEFAULT NULL COMMENT 'a short description of this device type ',
  `vendor` varchar(50) DEFAULT NULL COMMENT 'Device vendor (just a string, maybe later a reference to a new vendor table don''t see a real use for that now as we keep vendor info on the wiki) ',
  `type` varchar(50) DEFAULT NULL COMMENT 'Device type, describes the type of device, for example, optical, switch, router, router-switch, console server, etc ',
  `notes` varchar(500) DEFAULT NULL COMMENT 'Free text field for any other notes ',
  `archived` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`device_type_id`,`name`) USING BTREE,
  KEY `ID` (`device_type_id`),
  KEY `ID_2` (`device_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=79 DEFAULT CHARSET=utf8 COMMENT='Configuration table for devices';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Devices`
--

DROP TABLE IF EXISTS `Devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Devices` (
  `device_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'device id and primary key ',
  `name` varchar(100) NOT NULL COMMENT 'FQDN of Device ',
  `location` int(11) NOT NULL COMMENT 'is a foreign key to location_id in Locations. ',
  `type` int(11) NOT NULL DEFAULT '1' COMMENT 'Device type, describes the type of device, for example, optical, switch, router, router-switch, console server, etc is a foreign key to DeviceTypes_id',
  `snmp_ro` varchar(100) DEFAULT NULL COMMENT 'read only snmp string ',
  `snmp_rw` varchar(100) DEFAULT NULL COMMENT 'read write snmp string ',
  `snmp_version` varchar(10) DEFAULT NULL COMMENT 'Snmp version to use for this device ',
  `ro_user` varchar(100) DEFAULT NULL COMMENT 'read only user, can be used for scripts ',
  `ro_password` varchar(100) DEFAULT NULL COMMENT 'read only password, again for scripts ',
  `notes` varchar(500) DEFAULT NULL COMMENT 'Free text field for any other notes ',
  `device_fqdn` varchar(100) NOT NULL COMMENT 'Ip or fqdn of device',
  `device_oob` varchar(100) NOT NULL COMMENT 'out of band access (phone)',
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'is device trashed/archived?',
  PRIMARY KEY (`device_id`,`name`) USING BTREE,
  KEY `location` (`location`),
  KEY `type` (`type`),
  CONSTRAINT `Devices_ibfk_2` FOREIGN KEY (`type`) REFERENCES `Device_types` (`device_type_id`) ON UPDATE CASCADE,
  CONSTRAINT `Devices_ibfk_3` FOREIGN KEY (`location`) REFERENCES `pop_locations` (`location_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=224 DEFAULT CHARSET=utf8 COMMENT='Configuartion table for Devices';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `L2_service_details`
--

DROP TABLE IF EXISTS `L2_service_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `L2_service_details` (
  `l2_service_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'service id of this layer2 service. PK ',
  `vlan` int(10) DEFAULT NULL COMMENT 'vlan used for this L2 service',
  `notes` text,
  PRIMARY KEY (`l2_service_id`)
) ENGINE=InnoDB AUTO_INCREMENT=232 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='L2 services describes the configuration for the different la';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `L3_service_details`
--

DROP TABLE IF EXISTS `L3_service_details`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `L3_service_details` (
  `l3_service_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'layer3 service id (PK) ',
  `name` varchar(100) DEFAULT NULL COMMENT 'useful name for this service ',
  `cust_id` int(11) DEFAULT NULL COMMENT 'owner of this circuit. Typically the one who pays and/or is using this service. FK to users table ',
  `routing_type` varchar(10) NOT NULL COMMENT 'Routing type can either be, static or bgp ',
  `service_type` int(11) NOT NULL COMMENT 'Describes the kind of service all service type are defined in table service_definitions this is FK (FK > Service_types)',
  `logical_router` varchar(100) NOT NULL COMMENT 'Which logical router is this configured in ',
  `IPv4_unicast` tinyint(3) NOT NULL COMMENT 'Boolean, 1 if IPv4 unicast is configured for this service, 0 if not ',
  `IPv4_multicast` tinyint(3) NOT NULL COMMENT 'Boolean, 1 if IPv4 mcast is configured for this service, 0 if not ',
  `IPv6_unicast` tinyint(3) NOT NULL COMMENT 'Boolean, 1 if IPv6 unicast is configured for this service, 0 if not ',
  `IPv6_multicast` tinyint(3) NOT NULL COMMENT 'Boolean, 1 if IPv6 mcast is configured for this service, 0 if not ',
  `IPv4_prefixes` text COMMENT 'list of ipv4 prefixes, for now a text field maybe later make a reference to ip manager ',
  `IPv6_prefixes` text COMMENT 'list of ipv6 prefixes, for now a text field maybe later make a reference to ip manager ',
  `BCNETrouterAddress4` varchar(50) DEFAULT NULL COMMENT 'IPv4 address of BCNET router for this service connection ',
  `CustrouterAddress4` varchar(50) DEFAULT NULL COMMENT 'IPv4 address of BCNET customer for this service connection ',
  `BCNETrouterAddress6` varchar(50) DEFAULT NULL COMMENT 'IPv6 address of BCNET router for this service connection ',
  `CustrouterAddress6` varchar(50) DEFAULT NULL COMMENT 'IPv6 address of BCNET customer for this service connection ',
  `mtu` int(10) DEFAULT NULL COMMENT 'MTU (in  bytes)  for this service. In this case L3 mtu size. probably different than the one on the physical interface referenced above ',
  `bgp_as` int(10) DEFAULT NULL COMMENT 'AS number of client, only used in case of bgp ',
  `bgp_pass` varchar(100) DEFAULT '' COMMENT 'BGP password only used in case of bgp and password ',
  `traffic_policing` int(20) NOT NULL DEFAULT '0' COMMENT 'policy limit in bps. if no limit leave 0 ',
  `router` int(11) NOT NULL COMMENT 'which router is this service configured on',
  `notes` text NOT NULL COMMENT 'some addtitional notes',
  PRIMARY KEY (`l3_service_id`),
  UNIQUE KEY `name` (`name`,`cust_id`),
  KEY `service_type` (`service_type`),
  KEY `cust_id` (`cust_id`),
  KEY `service_type_2` (`service_type`),
  KEY `router` (`router`)
) ENGINE=InnoDB AUTO_INCREMENT=444 DEFAULT CHARSET=latin1 COMMENT='L3 service definition';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Locations`
--

DROP TABLE IF EXISTS `Locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'location id as well as primary key ',
  `name` varchar(100) NOT NULL COMMENT 'fqdn of location, for example: vantx1.bc.net ',
  `city` varchar(100) DEFAULT NULL COMMENT 'City of location ',
  `address` varchar(300) DEFAULT NULL COMMENT 'Street address ',
  `zip_code` varchar(10) DEFAULT NULL COMMENT 'postal code of location ',
  `email` varchar(100) DEFAULT NULL COMMENT 'contact email for this location ',
  `phone` varchar(30) DEFAULT NULL COMMENT 'contact phone number for this location ',
  `room` varchar(20) DEFAULT NULL COMMENT 'room number of pop in building. Normally if we have more then one pop in a bulding that would be a different pop name. vantx1, vantx2 ',
  `notes` varchar(500) DEFAULT NULL COMMENT 'Free text field for any other notes ',
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='which service locations do we have?';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Plugin_checks`
--

DROP TABLE IF EXISTS `Plugin_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Plugin_checks` (
  `check_id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `plugin_id` int(11) NOT NULL COMMENT 'plugin ',
  `service_id` int(11) DEFAULT NULL COMMENT 'service id',
  PRIMARY KEY (`check_id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `device_id` (`device_id`),
  KEY `service_id` (`service_id`),
  CONSTRAINT `Plugin_checks_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE,
  CONSTRAINT `Plugin_checks_ibfk_2` FOREIGN KEY (`plugin_id`) REFERENCES `Plugins` (`plugin_id`) ON DELETE CASCADE,
  CONSTRAINT `Plugin_checks_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `Services` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT COMMENT='Table which defines which check will be don on which device';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Plugins`
--

DROP TABLE IF EXISTS `Plugins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Plugins` (
  `plugin_id` int(11) NOT NULL AUTO_INCREMENT,
  `plugin_name` varchar(100) NOT NULL,
  `plugin_location` varchar(100) NOT NULL,
  PRIMARY KEY (`plugin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Plugins_plugin`
--

DROP TABLE IF EXISTS `Plugins_plugin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Plugins_plugin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'plugin id',
  `name` varchar(150) NOT NULL COMMENT 'plugin name',
  `icon` varchar(150) NOT NULL COMMENT 'icon for plugin',
  `filename` varchar(150) NOT NULL COMMENT 'the path of the file',
  `config_filename` varchar(150) NOT NULL COMMENT 'path of the config file',
  `description` varchar(500) NOT NULL COMMENT 'description for the plugin',
  `version` varchar(32) NOT NULL COMMENT 'the version this plugin is in',
  `class_name` varchar(100) NOT NULL COMMENT 'the name of the class',
  `location` varchar(100) NOT NULL DEFAULT 'default' COMMENT 'where the plugin is located',
  `sub_location` varchar(32) NOT NULL COMMENT 'the sub location of location',
  `plugin_order` int(11) NOT NULL DEFAULT '50' COMMENT 'order that the plugins comes in',
  `enabled` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'is the plugin enabled?',
  `poller` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Wheter there''''s a poller (1) or not (0)',
  `poller_script` varchar(400) NOT NULL COMMENT 'poller script location',
  `poller_interval` int(11) NOT NULL COMMENT 'interval in minutes',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Service_types`
--

DROP TABLE IF EXISTS `Service_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Service_types` (
  `service_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(500) NOT NULL,
  `service_layer` varchar(10) NOT NULL COMMENT 'layer by which services can this type be used (layer1,layer2,layer3)',
  `archived` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`service_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Services`
--

DROP TABLE IF EXISTS `Services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'service id',
  `name` varchar(100) NOT NULL COMMENT 'name of service',
  `cust_id` int(11) NOT NULL COMMENT 'cust id, who uses thi service',
  `service_type` int(11) NOT NULL COMMENT 'pointer to serviceTypes',
  `l2_service_id` int(11) DEFAULT NULL COMMENT 'if l2, l2service_id',
  `l3_service_id` int(11) DEFAULT NULL COMMENT 'if l3, l3 service id',
  `notes` text,
  `portal_statistics` tinyint(3) NOT NULL DEFAULT '1' COMMENT 'Show statistics in portal?',
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  `date_in_production` timestamp NULL DEFAULT NULL,
  `date_out_production` timestamp NULL DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`service_id`),
  UNIQUE KEY `name` (`name`,`cust_id`),
  KEY `cust_id` (`cust_id`),
  CONSTRAINT `Services_ibfk_1` FOREIGN KEY (`cust_id`) REFERENCES `contact_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=630 DEFAULT CHARSET=latin1 COMMENT='this table holds all services.';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Services_Interfaces`
--

DROP TABLE IF EXISTS `Services_Interfaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Services_Interfaces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `interface_name` varchar(100) NOT NULL COMMENT 'name of interface, i.e ge-0/2/2 will be used as the key',
  `service_id` int(11) NOT NULL,
  `tagged` tinyint(3) NOT NULL COMMENT '1 if tagged interface',
  `vlan` int(10) NOT NULL COMMENT 'vlan for interface',
  `device` int(11) NOT NULL COMMENT 'Interface device',
  `speed` bigint(20) DEFAULT NULL,
  `mtu` int(10) DEFAULT NULL,
  `duplex` varchar(10) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `subnet_mask` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `interface_name` (`interface_name`,`service_id`,`vlan`,`device`),
  KEY `interface_id` (`interface_name`),
  KEY `service_id` (`service_id`),
  KEY `device` (`device`),
  CONSTRAINT `Services_Interfaces_ibfk_2` FOREIGN KEY (`device`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE,
  CONSTRAINT `Services_Interfaces_ibfk_3` FOREIGN KEY (`service_id`) REFERENCES `Services` (`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1192 DEFAULT CHARSET=latin1 ROW_FORMAT=COMPACT;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Updates`
--

DROP TABLE IF EXISTS `Updates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Updates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `action` varchar(300) NOT NULL COMMENT 'the actions',
  `username` varchar(250) NOT NULL COMMENT 'the user that does this action',
  `curDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'the date when the action was taken',
  `archived` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'archived updates',
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4547 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `Users`
--

DROP TABLE IF EXISTS `Users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'user id and primary key ',
  `name` varchar(50) NOT NULL COMMENT 'Full name of the customer',
  `wiki_id` varchar(50) DEFAULT NULL COMMENT 'if customer they probably have a wiki id. This can be used to generate customer specific information on their customer portal ',
  `group_id` varchar(100) DEFAULT NULL COMMENT 'wiki group name',
  `phone` varchar(50) DEFAULT NULL COMMENT 'Phone number',
  `email` varchar(50) DEFAULT NULL COMMENT 'email',
  `notes` text,
  `T_contact_name` varchar(100) NOT NULL COMMENT 'technical contact name',
  `T_contact_phone` varchar(50) NOT NULL,
  `T_contact_email` varchar(50) NOT NULL,
  `S_contact_name` varchar(100) NOT NULL COMMENT 'Prime Service Contact:',
  `S_contact_phone` varchar(50) NOT NULL,
  `S_contact_email` varchar(50) NOT NULL,
  `E_contact_name` varchar(100) NOT NULL COMMENT 'Emergency 24 Hour Contact',
  `E_contact_phone` varchar(50) NOT NULL COMMENT 'Emergency 24 Hour Contact',
  `E_contact_email` varchar(50) NOT NULL COMMENT 'Emergency 24 Hour Contact',
  `B_contact_name` varchar(100) NOT NULL COMMENT 'Prime Billing Contact',
  `B_contact_phone` varchar(50) NOT NULL COMMENT 'Prime Billing Contact:',
  `B_contact_email` varchar(50) NOT NULL COMMENT 'Prime Billing Contact:',
  `archived` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=136 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounting_profiles`
--

DROP TABLE IF EXISTS `accounting_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_profiles` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `notes` text NOT NULL,
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  `traffic_cap` bigint(20) DEFAULT NULL COMMENT 'commited rate or configured cap',
  PRIMARY KEY (`profile_id`),
  UNIQUE KEY `title` (`title`),
  KEY `client_id` (`client_id`),
  CONSTRAINT `accounting_profiles_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `contact_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=latin1 COMMENT='Table for accounting profiles';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounting_profiles_files`
--

DROP TABLE IF EXISTS `accounting_profiles_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_profiles_files` (
  `profile_id` int(11) NOT NULL,
  `accounting_source` int(11) NOT NULL COMMENT 'link to scu-dcu accounting source',
  `traffic_cap` int(11) DEFAULT NULL COMMENT 'commited rate or configured cap at the time the report was generated',
  PRIMARY KEY (`profile_id`,`accounting_source`),
  KEY `accounting_source` (`accounting_source`),
  CONSTRAINT `accounting_profiles_files_ibfk_1` FOREIGN KEY (`accounting_source`) REFERENCES `accounting_sources` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='relationship table for profile ids to files';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounting_reports`
--

DROP TABLE IF EXISTS `accounting_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(11) NOT NULL,
  `report_name` varchar(500) NOT NULL,
  `avg_in` bigint(20) NOT NULL,
  `avg_out` bigint(20) NOT NULL,
  `max_in` bigint(20) NOT NULL,
  `max_out` bigint(20) NOT NULL,
  `95_in` bigint(20) NOT NULL,
  `95_out` bigint(20) NOT NULL,
  `tot_in` bigint(20) NOT NULL,
  `tot_out` bigint(11) NOT NULL,
  `date2` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `date1` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sample_date1` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'firts measurement sample date',
  `sample_date2` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'last measurement sample date',
  `img_file` mediumblob NOT NULL COMMENT 'this is the rrd_img file',
  `create_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `traffic_cap` bigint(11) DEFAULT NULL COMMENT 'commited rate or configured cap at the time the report was generated',
  PRIMARY KEY (`id`),
  UNIQUE KEY `profile_id_2` (`profile_id`,`report_name`),
  KEY `profile_id` (`profile_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2824 DEFAULT CHARSET=latin1 COMMENT='Table where we store reports';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounting_sources`
--

DROP TABLE IF EXISTS `accounting_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `accounting_sources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `scu_profile` varchar(200) NOT NULL COMMENT 'name of scu profile',
  `destination` varchar(200) NOT NULL COMMENT 'outgoing interface',
  `file` varchar(200) NOT NULL,
  `last_update` timestamp NULL DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `file` (`file`),
  KEY `device_id` (`device_id`),
  CONSTRAINT `accounting_sources_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1002 DEFAULT CHARSET=latin1 COMMENT='Table for accounting / scu';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_group_types`
--

DROP TABLE IF EXISTS `contact_group_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_group_types` (
  `group_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_type_name` varchar(100) NOT NULL,
  `group_type_desc` varchar(200) NOT NULL,
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`group_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1 COMMENT='This table holds the different group types. Examples are: cl';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_groups`
--

DROP TABLE IF EXISTS `contact_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_groups` (
  `group_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_type` int(11) NOT NULL,
  `group_name` varchar(100) NOT NULL,
  `group_desc` varchar(200) NOT NULL,
  `group_notes` text NOT NULL,
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  `custom_client_id` varchar(200) DEFAULT NULL COMMENT 'custom client id to link to local system',
  `custom_client_group_id` varchar(200) DEFAULT NULL COMMENT 'custom client group id to link to local system',
  PRIMARY KEY (`group_id`),
  UNIQUE KEY `group_name` (`group_name`),
  KEY `group_type` (`group_type`),
  CONSTRAINT `contact_groups_ibfk_1` FOREIGN KEY (`group_type`) REFERENCES `contact_group_types` (`group_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=244 DEFAULT CHARSET=latin1 COMMENT='groups of contacts, group can be a vendor or client, etc';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact_types`
--

DROP TABLE IF EXISTS `contact_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact_types` (
  `contact_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `contact_type_name` varchar(100) NOT NULL,
  `contact_type_desc` varchar(300) NOT NULL,
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`contact_type_id`),
  UNIQUE KEY `contact_type_name` (`contact_type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1 COMMENT='examples are  technical_contact emergency_contact billing_co';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contacts` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `name_first` varchar(50) NOT NULL,
  `name_middle` varchar(50) NOT NULL,
  `name_last` varchar(50) NOT NULL,
  `country` varchar(100) NOT NULL,
  `province` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `addr_line1` varchar(100) NOT NULL,
  `addr_line2` varchar(100) NOT NULL,
  `zipcode` varchar(20) NOT NULL,
  `phone1` varchar(100) NOT NULL,
  `phone1_comment` varchar(100) NOT NULL,
  `phone2` varchar(100) NOT NULL,
  `phone2_comment` varchar(100) NOT NULL,
  `phone_cell` varchar(100) NOT NULL,
  `phone_cell_comment` varchar(100) NOT NULL,
  `phone_pager` varchar(100) NOT NULL,
  `phone_pager_comment` varchar(100) NOT NULL,
  `phone_fax` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `notes` text NOT NULL,
  `external_id1` varchar(100) NOT NULL COMMENT 'to link to external system id''s',
  `external_id2` varchar(100) NOT NULL COMMENT 'to link to external system id''s',
  `external_id3` varchar(100) NOT NULL COMMENT 'to link to external system id''s',
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`contact_id`),
  UNIQUE KEY `name_first` (`name_first`,`name_last`,`name_middle`)
) ENGINE=InnoDB AUTO_INCREMENT=383 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `control_ports`
--

DROP TABLE IF EXISTS `control_ports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `control_ports` (
  `control_port_id` int(11) NOT NULL AUTO_INCREMENT,
  `managed_device_id` int(11) DEFAULT NULL COMMENT 'Which managed device belongs this control port to',
  `control_port_description` varchar(100) NOT NULL COMMENT 'useful descriptive  name of control port name',
  `control_device_id` int(11) NOT NULL COMMENT 'Device id of console/power management device',
  `control_port_name` varchar(100) NOT NULL COMMENT 'control port name',
  `control_group` varchar(100) NOT NULL COMMENT 'control group name',
  `control_port_type` varchar(100) NOT NULL COMMENT 'control port type (poe | power | console)',
  `control_port` varchar(100) NOT NULL,
  PRIMARY KEY (`control_port_id`),
  UNIQUE KEY `managed_device_id` (`managed_device_id`,`control_device_id`,`control_port_name`,`control_port_type`),
  KEY `device_id` (`managed_device_id`,`control_device_id`),
  KEY `control_device_id` (`control_device_id`),
  CONSTRAINT `control_ports_ibfk_2` FOREIGN KEY (`control_device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `control_ports_ibfk_3` FOREIGN KEY (`managed_device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=latin1 COMMENT='Table for control ports';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `events` (
  `event_id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(50) NOT NULL,
  `info_msg` varchar(800) NOT NULL,
  `insert_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `active` tinyint(4) NOT NULL DEFAULT '0',
  `check_id` int(11) DEFAULT NULL COMMENT 'refers to service_checks',
  `host_name` varchar(200) NOT NULL COMMENT 'hostname',
  `check_name` varchar(200) NOT NULL COMMENT 'check name',
  `key1` varchar(200) NOT NULL COMMENT 'unique identifier per check (example = interface_name)',
  `key2` varchar(200) NOT NULL COMMENT '2nd key argument',
  `script` varchar(400) NOT NULL COMMENT 'executed script ',
  `notify_state` varchar(20) NOT NULL,
  PRIMARY KEY (`event_id`),
  KEY `check_id` (`check_id`),
  KEY `host_name` (`host_name`),
  KEY `check_name` (`check_name`),
  KEY `key1` (`key1`),
  KEY `key2` (`key2`)
) ENGINE=InnoDB AUTO_INCREMENT=17665 DEFAULT CHARSET=latin1 COMMENT='events table';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `groups_contacts`
--

DROP TABLE IF EXISTS `groups_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `groups_contacts` (
  `group_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `contact_type` int(11) NOT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`group_id`,`contact_type`,`contact_id`),
  KEY `contact_id` (`contact_id`),
  KEY `contact_type` (`contact_type`),
  CONSTRAINT `groups_contacts_ibfk_3` FOREIGN KEY (`contact_type`) REFERENCES `contact_types` (`contact_type_id`),
  CONSTRAINT `groups_contacts_ibfk_4` FOREIGN KEY (`group_id`) REFERENCES `contact_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `groups_contacts_ibfk_5` FOREIGN KEY (`contact_id`) REFERENCES `contacts` (`contact_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='to map contacts to groups';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interface_IPaddresses`
--

DROP TABLE IF EXISTS `interface_IPaddresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interface_IPaddresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `if_index` int(11) NOT NULL,
  `inet_address` varchar(100) NOT NULL,
  `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `inet_address_length` int(11) NOT NULL COMMENT 'prefix lenght',
  `inet` int(2) NOT NULL DEFAULT '4' COMMENT 'ipv4 or ipv6',
  PRIMARY KEY (`id`),
  UNIQUE KEY `Unique` (`device_id`,`if_index`,`inet_address`,`inet_address_length`,`inet`),
  KEY `device_id` (`device_id`),
  KEY `if_index` (`if_index`),
  CONSTRAINT `interface_IPaddresses_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE,
  CONSTRAINT `interface_IPaddresses_ibfk_2` FOREIGN KEY (`if_index`) REFERENCES `interfaces` (`disc_interface_index`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21085 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `interfaces`
--

DROP TABLE IF EXISTS `interfaces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `interfaces` (
  `interface_id` int(10) NOT NULL AUTO_INCREMENT,
  `ifOperStatus` varchar(15) DEFAULT NULL,
  `interface_name` varchar(50) NOT NULL,
  `interface_descr` varchar(200) NOT NULL,
  `interface_alias` varchar(100) DEFAULT NULL,
  `interface_device` int(11) NOT NULL,
  `interface_speed` bigint(15) DEFAULT NULL COMMENT 'in bps',
  `interface_mtu` int(11) DEFAULT NULL,
  `interface_duplex` varchar(10) DEFAULT '' COMMENT 'half or full',
  `interface_type` varchar(10) NOT NULL DEFAULT '' COMMENT 'TX,SX,LX,ZX,CWDM',
  `interface_tagged` tinyint(3) NOT NULL,
  `interface_vlan` int(10) NOT NULL,
  `disc_interface_speed` bigint(15) DEFAULT NULL COMMENT 'bps',
  `disc_interface_mtu` bigint(11) DEFAULT NULL,
  `disc_interface_index` int(5) DEFAULT NULL,
  `active` tinyint(3) NOT NULL,
  `insert_time` datetime NOT NULL,
  `last_seen` datetime NOT NULL,
  `disc_interface_type` varchar(20) DEFAULT NULL COMMENT 'ethernetCsmacd, l2vlan',
  `inbits` bigint(11) NOT NULL COMMENT 'in bits per second last 5min',
  `outbits` bigint(11) NOT NULL COMMENT 'out bits per second last 5min',
  `inerrors` int(11) NOT NULL COMMENT 'in errors per second last 5 min',
  `outerrors` int(11) NOT NULL COMMENT 'out errors per second last 5 min',
  `inunicastpackets` int(11) NOT NULL COMMENT 'in unicast packets per second last 5 min',
  `outunicastpackets` int(11) NOT NULL COMMENT 'out unicast packets per second last 5min',
  `innonunicastpackets` int(11) NOT NULL COMMENT 'in non unicast packets per second last 5 min',
  `outnonunicastpackets` int(11) NOT NULL COMMENT 'out non unicast packets per second last 5 min',
  `last_threshold_alert` timestamp NULL DEFAULT NULL COMMENT 'when was last threshold alert send',
  PRIMARY KEY (`interface_id`),
  KEY `interface_device` (`interface_device`),
  KEY `disc_interface_index` (`disc_interface_index`),
  CONSTRAINT `interfaces_ibfk_1` FOREIGN KEY (`interface_device`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18997 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ipmanager_class`
--

DROP TABLE IF EXISTS `ipmanager_class`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipmanager_class` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the class',
  `class_name` varchar(50) NOT NULL COMMENT 'name of the class',
  `class_desc` text NOT NULL COMMENT 'description of the class',
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table for ipmanager\r\nclasses/types';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ipmanager_netblocks`
--

DROP TABLE IF EXISTS `ipmanager_netblocks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipmanager_netblocks` (
  `netblock_id` int(11) NOT NULL AUTO_INCREMENT,
  `base_addr` varchar(40) NOT NULL COMMENT 'ip address',
  `subnet_size` int(11) NOT NULL COMMENT 'the netmask',
  `description` text NOT NULL COMMENT 'everything else, the details\r\n(hostmin/max etc.)',
  `title` varchar(100) NOT NULL COMMENT 'title for the ip',
  `family` int(11) NOT NULL COMMENT 'ipv4/ipv6',
  `parent` int(11) DEFAULT NULL COMMENT 'parent of the prefix',
  `stub` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'is stub network',
  `type` varchar(20) NOT NULL DEFAULT 'NETWORK' COMMENT 'network or host',
  `status` varchar(30) NOT NULL DEFAULT 'FREE' COMMENT 'status of the network',
  `owner` int(11) DEFAULT NULL COMMENT 'Owner of the block from AAA_groups',
  `assigned_to` int(11) DEFAULT NULL COMMENT 'Block user - AAA_groups',
  `class` int(11) DEFAULT NULL COMMENT 'the class types',
  `location` int(11) DEFAULT NULL COMMENT 'location of the ip',
  `comments` text NOT NULL COMMENT 'for notes',
  PRIMARY KEY (`netblock_id`),
  UNIQUE KEY `base_addr` (`base_addr`,`subnet_size`),
  KEY `parent` (`parent`),
  KEY `owner` (`owner`),
  KEY `location` (`location`),
  KEY `assigned_to` (`assigned_to`),
  CONSTRAINT `ipmanager_netblocks_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `ipmanager_netblocks` (`netblock_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ipmanager_netblocks_ibfk_2` FOREIGN KEY (`owner`) REFERENCES `contact_groups` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ipmanager_netblocks_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `contact_groups` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `ipmanager_netblocks_ibfk_4` FOREIGN KEY (`location`) REFERENCES `pop_locations` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13859 DEFAULT CHARSET=latin1 COMMENT='All the available netblocks';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ipmanager_tags`
--

DROP TABLE IF EXISTS `ipmanager_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipmanager_tags` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the tag',
  `tag_name` varchar(50) NOT NULL COMMENT 'name of the tag',
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Table for ipmanager\r\nclasses/types';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ipmanager_tags_netblock`
--

DROP TABLE IF EXISTS `ipmanager_tags_netblock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipmanager_tags_netblock` (
  `tag_name` varchar(50) NOT NULL COMMENT 'name of the tags',
  `netblock_id` int(11) NOT NULL COMMENT 'refers to netblock ids',
  PRIMARY KEY (`tag_name`,`netblock_id`),
  KEY `netblock_id` (`netblock_id`),
  CONSTRAINT `ipmanager_tags_netblock_ibfk_2` FOREIGN KEY (`netblock_id`) REFERENCES `ipmanager_netblocks` (`netblock_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='relationship table for\r\ntags and netblocks';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ipmanager_vlans`
--

DROP TABLE IF EXISTS `ipmanager_vlans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipmanager_vlans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `vlan_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `notes` text NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL,
  `status` varchar(30) NOT NULL,
  `class_id` int(11) DEFAULT NULL,
  `vlan_distinguisher` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `vlan_id` (`vlan_id`,`vlan_distinguisher`),
  KEY `class_id` (`class_id`),
  KEY `location` (`location_id`),
  KEY `assigned_to` (`assigned_to`)
) ENGINE=InnoDB AUTO_INCREMENT=337 DEFAULT CHARSET=latin1 COMMENT='vlan manager';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `ipmanager_vlans_tags`
--

DROP TABLE IF EXISTS `ipmanager_vlans_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ipmanager_vlans_tags` (
  `id` int(11) NOT NULL,
  `tag` varchar(100) NOT NULL,
  PRIMARY KEY (`id`,`tag`),
  CONSTRAINT `ipmanager_vlans_tags_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ipmanager_vlans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='vlan tags relationshiptable';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugin_ChangeManager_Changes`
--

DROP TABLE IF EXISTS `plugin_ChangeManager_Changes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_ChangeManager_Changes` (
  `change_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(500) NOT NULL,
  `notes` text NOT NULL,
  `record_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `change_date` timestamp NULL DEFAULT NULL,
  `planned_change_date` timestamp NULL DEFAULT NULL COMMENT 'planned change time',
  `change_contact_1` int(11) DEFAULT NULL COMMENT 'reference to AAA_Users',
  `change_contact_2` int(11) DEFAULT NULL COMMENT '2dn changecontact',
  `impact` int(11) DEFAULT NULL COMMENT 'Impact',
  `status` int(2) NOT NULL,
  PRIMARY KEY (`change_id`),
  KEY `created_by` (`change_contact_1`),
  KEY `change_contact_2` (`change_contact_2`),
  CONSTRAINT `plugin_ChangeManager_Changes_ibfk_1` FOREIGN KEY (`change_contact_1`) REFERENCES `AAA_users` (`user_id`) ON UPDATE CASCADE,
  CONSTRAINT `plugin_ChangeManager_Changes_ibfk_2` FOREIGN KEY (`change_contact_2`) REFERENCES `AAA_users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=170 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugin_ChangeManager_Components`
--

DROP TABLE IF EXISTS `plugin_ChangeManager_Components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_ChangeManager_Components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `change_id` int(11) NOT NULL COMMENT 'reference to change',
  `device_id` int(11) NOT NULL COMMENT 'reference to device',
  `impact` int(11) NOT NULL,
  `effects` text NOT NULL,
  `chgby_id` int(11) DEFAULT NULL COMMENT 'reference to AAA_users',
  `change_date` timestamp NULL DEFAULT NULL COMMENT 'time of change',
  `description` text NOT NULL,
  `back_out` text NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `change_id` (`change_id`),
  CONSTRAINT `plugin_ChangeManager_Components_ibfk_1` FOREIGN KEY (`change_id`) REFERENCES `plugin_ChangeManager_Changes` (`change_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=315 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugin_SNMPPoller_devices`
--

DROP TABLE IF EXISTS `plugin_SNMPPoller_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_SNMPPoller_devices` (
  `device_id` int(11) NOT NULL COMMENT 'Points to devices',
  `enabled` tinyint(2) NOT NULL COMMENT 'enabled(1) or not (0)',
  PRIMARY KEY (`device_id`),
  CONSTRAINT `plugin_SNMPPoller_devices_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='SNMP poller';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plugin_threshold`
--

DROP TABLE IF EXISTS `plugin_threshold`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plugin_threshold` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `device_id` int(11) NOT NULL,
  `port_name` varchar(100) NOT NULL,
  `in_max_threshold` int(11) DEFAULT NULL,
  `in_min_threshold` int(11) DEFAULT NULL,
  `out_max_threshold` int(11) DEFAULT NULL,
  `out_min_threshold` int(11) DEFAULT NULL,
  `threshold_data_type` varchar(100) NOT NULL COMMENT 'bps, unicast, nonunicast, err',
  `threshold_value_type` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'percentage (1) or absolute (0)',
  `description` varchar(200) NOT NULL COMMENT 'description',
  PRIMARY KEY (`id`),
  KEY `device_id` (`device_id`),
  KEY `port_name` (`port_name`),
  CONSTRAINT `plugin_threshold_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COMMENT='data for thresholds';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pop_location_types`
--

DROP TABLE IF EXISTS `pop_location_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pop_location_types` (
  `location_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `location_type_name` varchar(100) NOT NULL,
  `location_type_desc` varchar(200) NOT NULL,
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`location_type_id`),
  UNIQUE KEY `location_type_name_2` (`location_type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pop_locations`
--

DROP TABLE IF EXISTS `pop_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pop_locations` (
  `location_id` int(11) NOT NULL AUTO_INCREMENT,
  `location_name` varchar(100) NOT NULL,
  `location_desc` varchar(500) NOT NULL,
  `location_country` varchar(100) NOT NULL,
  `location_province` varchar(100) NOT NULL,
  `location_city` varchar(100) NOT NULL,
  `location_addr_line1` varchar(200) NOT NULL,
  `location_addr_line2` varchar(200) NOT NULL,
  `location_zip_code` varchar(50) NOT NULL,
  `location_type` int(11) NOT NULL,
  `location_notes` text NOT NULL,
  `location_contact_group` int(11) DEFAULT NULL,
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`location_id`),
  UNIQUE KEY `location_name` (`location_name`),
  KEY `location_type` (`location_type`),
  KEY `location_contact_group` (`location_contact_group`),
  CONSTRAINT `pop_locations_ibfk_1` FOREIGN KEY (`location_type`) REFERENCES `pop_location_types` (`location_type_id`),
  CONSTRAINT `pop_locations_ibfk_2` FOREIGN KEY (`location_contact_group`) REFERENCES `contact_groups` (`group_id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=latin1 COMMENT='Table for locations';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pop_room_types`
--

DROP TABLE IF EXISTS `pop_room_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pop_room_types` (
  `room_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_type_name` varchar(100) NOT NULL,
  `room_type_desc` varchar(200) NOT NULL,
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`room_type_id`),
  UNIQUE KEY `room_type_name` (`room_type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pop_rooms`
--

DROP TABLE IF EXISTS `pop_rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pop_rooms` (
  `room_id` int(11) NOT NULL AUTO_INCREMENT,
  `room_name` varchar(100) NOT NULL,
  `room_desc` varchar(200) NOT NULL,
  `room_notes` text NOT NULL,
  `room_type` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `room_no` varchar(100) NOT NULL,
  `archived` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`room_id`),
  UNIQUE KEY `room_name` (`room_name`),
  KEY `room_type` (`room_type`,`location_id`),
  KEY `location_id` (`location_id`),
  CONSTRAINT `pop_rooms_ibfk_1` FOREIGN KEY (`room_type`) REFERENCES `pop_room_types` (`room_type_id`),
  CONSTRAINT `pop_rooms_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `pop_locations` (`location_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=latin1 COMMENT='pop rooms';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `properties`
--

DROP TABLE IF EXISTS `properties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `properties` (
  `name` varchar(100) NOT NULL COMMENT 'property name',
  `value` varchar(1000) NOT NULL COMMENT 'property value',
  `description` varchar(1000) NOT NULL COMMENT 'property description',
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Table for properties';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `properties_user`
--

DROP TABLE IF EXISTS `properties_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `properties_user` (
  `name` varchar(50) NOT NULL COMMENT 'the name of the user properties',
  `friendly_name` varchar(50) NOT NULL COMMENT 'easier name for the user to see',
  `user_id` int(11) NOT NULL COMMENT 'id of the user with the property',
  `value` varchar(50) NOT NULL COMMENT 'value for the property',
  `description` varchar(250) NOT NULL COMMENT 'description for the property',
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `secret_data`
--

DROP TABLE IF EXISTS `secret_data`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secret_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_id` int(11) NOT NULL,
  `encr_data` blob,
  `notes` text NOT NULL,
  `name` varchar(100) NOT NULL,
  `device_id` int(11) DEFAULT NULL COMMENT 'refers to devices',
  PRIMARY KEY (`id`),
  KEY `type_id` (`type_id`),
  KEY `device_id` (`device_id`),
  CONSTRAINT `secret_data_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `secret_data_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `secret_data_types` (`type_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=187 DEFAULT CHARSET=latin1 COMMENT='Table for encrypted passwords';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `secret_data_groups`
--

DROP TABLE IF EXISTS `secret_data_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secret_data_groups` (
  `secret_data_id` int(11) NOT NULL,
  `aaa_groups_id` int(11) NOT NULL,
  PRIMARY KEY (`secret_data_id`,`aaa_groups_id`),
  KEY `secret_data_id` (`secret_data_id`),
  KEY `aaa_groups_id` (`aaa_groups_id`),
  CONSTRAINT `secret_data_groups_ibfk_1` FOREIGN KEY (`secret_data_id`) REFERENCES `secret_data` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `secret_data_groups_ibfk_2` FOREIGN KEY (`aaa_groups_id`) REFERENCES `AAA_groups` (`group_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='relation table between secret data and groups';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `secret_data_history`
--

DROP TABLE IF EXISTS `secret_data_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secret_data_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `secret_data_id` int(11) NOT NULL COMMENT 'points to secure_data',
  `change_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'when the password was overuled',
  `encr_data` blob COMMENT 'old password, encrypted using aes',
  PRIMARY KEY (`id`),
  KEY `secure_data_id` (`secret_data_id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `secret_data_types`
--

DROP TABLE IF EXISTS `secret_data_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `secret_data_types` (
  `type_id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(100) NOT NULL,
  `type_desc` varchar(1000) NOT NULL,
  PRIMARY KEY (`type_id`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=latin1 COMMENT='secret data types ';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_checks`
--

DROP TABLE IF EXISTS `service_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_checks` (
  `check_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `last_event_id` int(11) DEFAULT NULL COMMENT 'refers to events',
  `last_check` timestamp NULL DEFAULT NULL,
  `arguments` varchar(400) NOT NULL,
  `check_script` varchar(100) NOT NULL COMMENT 'tmp for development. should point to check_scripts table somepoint',
  `check_interval` int(11) NOT NULL DEFAULT '5' COMMENT 'interval in minutes',
  `key1` varchar(150) NOT NULL COMMENT 'Key1',
  `key2` varchar(150) NOT NULL COMMENT 'key2',
  `description` varchar(500) NOT NULL COMMENT 'Description for this check',
  `notes` text NOT NULL,
  `archived` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Archived (1) or not (0)',
  PRIMARY KEY (`check_id`),
  KEY `device_id` (`device_id`,`template_id`),
  KEY `last_event_id` (`last_event_id`),
  KEY `plugin_check` (`template_id`),
  CONSTRAINT `service_checks_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `Devices` (`device_id`) ON DELETE CASCADE,
  CONSTRAINT `service_checks_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `service_checks_template` (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_checks_profiles`
--

DROP TABLE IF EXISTS `service_checks_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_checks_profiles` (
  `profile_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(400) NOT NULL,
  `contact_id` int(11) DEFAULT NULL,
  `notes` text NOT NULL,
  `report_type` varchar(100) NOT NULL,
  PRIMARY KEY (`profile_id`),
  KEY `client_id` (`contact_id`),
  CONSTRAINT `service_checks_profiles_ibfk_1` FOREIGN KEY (`contact_id`) REFERENCES `contact_groups` (`group_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COMMENT='service check profiles';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_checks_profiles_checks`
--

DROP TABLE IF EXISTS `service_checks_profiles_checks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_checks_profiles_checks` (
  `service_check_id` int(11) NOT NULL COMMENT 'refers to check id',
  `report_id` int(11) NOT NULL COMMENT 'refers to report',
  PRIMARY KEY (`service_check_id`,`report_id`),
  KEY `report_id` (`report_id`),
  CONSTRAINT `service_checks_profiles_checks_ibfk_1` FOREIGN KEY (`service_check_id`) REFERENCES `service_checks` (`check_id`) ON DELETE CASCADE,
  CONSTRAINT `service_checks_profiles_checks_ibfk_2` FOREIGN KEY (`report_id`) REFERENCES `service_checks_profiles` (`profile_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_checks_reports`
--

DROP TABLE IF EXISTS `service_checks_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_checks_reports` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
  `report_name` varchar(400) NOT NULL,
  `profile_id` int(11) NOT NULL,
  `ok` bigint(20) NOT NULL COMMENT 'in seconds',
  `warning` bigint(20) NOT NULL COMMENT 'in seconds',
  `critical` bigint(20) NOT NULL COMMENT 'in seconds',
  `unknown` bigint(20) NOT NULL COMMENT 'in seconds',
  `other` bigint(20) NOT NULL COMMENT 'in seconds',
  `no_data` bigint(20) NOT NULL COMMENT 'in seconds',
  `start_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `report_type` varchar(100) NOT NULL,
  `create_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`report_id`),
  UNIQUE KEY `report_name` (`report_name`,`profile_id`),
  KEY `profile_id` (`profile_id`)
) ENGINE=InnoDB AUTO_INCREMENT=594 DEFAULT CHARSET=latin1 COMMENT='sla reports';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_checks_template`
--

DROP TABLE IF EXISTS `service_checks_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_checks_template` (
  `template_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL COMMENT 'check_name',
  `description` varchar(1000) NOT NULL COMMENT 'short description',
  `notes` text NOT NULL COMMENT 'help field / notes',
  `script` varchar(1000) NOT NULL COMMENT 'script location',
  `key1_name` varchar(200) DEFAULT NULL COMMENT 'Name of of key1 (example interface name)',
  `key2_name` varchar(200) DEFAULT NULL COMMENT 'Name of of key2 (example interface name)',
  PRIMARY KEY (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_aes`
--

DROP TABLE IF EXISTS `user_aes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_aes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `password` varchar(1000) DEFAULT NULL,
  `notes` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2014-11-28 16:10:48


SET FOREIGN_KEY_CHECKS = 1;

INSERT INTO `AAA_groups` (`group_id`, `group_name`, `group_desc`, `ldap_group_name`, `archived`, `access_level`) VALUES
(1, 'admin', 'Administrators Group', '', 0, 100),
(2, 'Read Only', 'Read Only Group', '', 0, 25),
(3, 'Read Write', 'Read Write Group', '', 0, 51);


--
-- Default admin pass is admin
--
INSERT INTO `AAA_users` (`user_id`, `full_name`, `user_name`, `user_pwd`, `user_email`, `user_type`, `archived`, `last_login`, `last_ip`) VALUES
(1, 'Administrator', 'admin', '21232f297a57a5a743894a0e4a801fc3', '', 'local', 0, '0000-00-00 00:00:00', '');

INSERT INTO `AAA_users_groups` (`group_id`, `user_id`) VALUES
(1, 1);

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`name`, `value`, `description`) VALUES
('LDAP_server', 'ldap.bc.net', 'The DNS hostname or IP\naddress of the LDAP server. (property name = LDAP_server  )'),('LDAP_version', '3', 'Protocol Version that the\nserver supports.'),
('LDAP_base_dn', 'ou=people,dc=bc,dc=net', 'Search base for\nsearching the LDAP directory, such as "dc=win2kdomain,dc=local" or\n"ou=people,dc= people,dc=net".'),
('LDAP_enable', '0', 'Determines if LDAP\nauthentication is enabled'),
('LDAP_group_search_filter', 'uniqueMember=cn=<username>,ou=people,dc=bc,dc=net', 'Filter used to\ndetermine which groups ldap users are in. Example:\nuniqueMember=cn=<username>,ou=people,dc=bc,dc=net <
username> will automatically\nbe replaced by the username'),
('LDAP_dn', 'cn=<username>,ou=people,dc=bc,dc=net', 'Distinguished Name syntax, such\nas for windows: "<username>@win2kdomain.local" or for OpenLDAP:\n"uid=<username>,ou=people,dc=domain,dc=local".  Or\
n"cn=<username>,ou=people,dc=bc,dc=net". "<username>" will automatically be\nreplaced with the username that was supplied at the login prompt.  This DN is\nused for binding / authenticating against the 
ldap server.'),
('LDAP_group_base_dn', 'ou=groups,dc=bc,dc=net', 'Base DN for groups.\nExample: ou=groups,dc=bc,dc=net'),
('path_snmpwalk', '/usr/bin/snmpbulkwalk', ''),('path_snmpget', '/usr/bin/snmpget', ''),
('path_rrdupdate', '/usr/bin/rrdupdate', ''),('path_rrdtool', '/usr/bin/rrdtool', ''),
('path_rrddir', '/var/www/netharbour/rrd-files/', '');

-- 
-- Add generic device
--

INSERT INTO `Device_types` ( `device_type_id` , `name` , `description` , `vendor` , `type` , `notes` , `archived`) VALUES 
( NULL , 'Generic Device', 'This is a Generic Device', NULL , NULL , NULL , '0');


-- 
-- Add generic pop location
--
INSERT INTO `pop_location_types` (`location_type_id`, `location_type_name`, `location_type_desc`, `archived`) VALUES
(3, 'Pop Location', '', 0);


-- 
-- Add demo pop location
--

INSERT INTO `pop_locations` (`location_id`, `location_name`, `location_desc`, `location_country`, `location_province`, `location_city`, `location_addr_line1`, `location_addr_line2`, `location_zip_code`, `location_type`, `location_notes`, `location_contact_group`, `archived`) VALUES
(10, 'Demo location', 'Demo Pop Location Please update after installation', '', '', '', '', '', '', 3, 'Demo Pop Location Please update after installation', NULL, 0);


-- 
-- Add data for some default templates
-- 

INSERT INTO `service_checks_template` ( `name`, `description`, `notes`, `script`, `key1_name`, `key2_name`) VALUES
('check_ifoperstatus', 'Check Interface status', 'This script check the operational status of the specified interface', 'cmdb_check_ifstatus.pl -H $HOSTADDRESS$ -C $SNMP_RO$ -d $KEY1$', 'interface', ''),
('BGP Check', 'BGP status check for Oran', 'Example:\r\nplugins-scripts/check_bgp.pl -H cr1.mgmt.vantx1.bc.net -C XXX -p 134.87.2.233', 'check_bgp.pl -H $HOSTADDRESS$ -C $SNMP_RO$ -p $KEY1$', 'Peer IP address', ''),
('OSPF Check', 'Checks the OSPF status of a peer', '', 'check_ospf.0.1.pl -H $HOSTADDRESS$ -C $SNMP_RO$ -p $KEY1$', 'neighbor ip address', '');




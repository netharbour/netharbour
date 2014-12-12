--
-- Table structure for table `plugin_ChangeManager_Changes`
--

DROP TABLE IF EXISTS `plugin_ChangeManager_Changes`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `plugin_ChangeManager_Changes` (
  `change_id` int(11) NOT NULL auto_increment,
  `title` varchar(500) NOT NULL,
  `notes` text NOT NULL,
  `record_date` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `change_date` timestamp NULL default NULL,
  `planned_change_date` timestamp NULL default NULL COMMENT 'planned change time',
  `change_contact_1` int(11) default NULL COMMENT 'reference to AAA_Users',
  `change_contact_2` int(11) default NULL COMMENT '2dn changecontact',
  `impact` int(11) NOT NULL COMMENT 'Impact',
  `status` int(2) NOT NULL,
  PRIMARY KEY  (`change_id`),
  KEY `created_by` (`change_contact_1`),
  KEY `change_contact_2` (`change_contact_2`),
  CONSTRAINT `plugin_ChangeManager_Changes_ibfk_1` FOREIGN KEY (`change_contact_1`) REFERENCES `AAA_users` (`user_id`) ON UPDATE CASCADE,
  CONSTRAINT `plugin_ChangeManager_Changes_ibfk_2` FOREIGN KEY (`change_contact_2`) REFERENCES `AAA_users` (`user_id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=latin1 COMMENT='Table for changes';
SET character_set_client = @saved_cs_client;


--
-- Table structure for table `plugin_ChangeManager_Components`
--

DROP TABLE IF EXISTS `plugin_ChangeManager_Components`;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
CREATE TABLE `plugin_ChangeManager_Components` (
  `id` int(11) NOT NULL auto_increment,
  `change_id` int(11) NOT NULL COMMENT 'reference to change',
  `device_id` int(11) NOT NULL COMMENT 'reference to device',
  `impact` int(11) NOT NULL,
  `effects` text NOT NULL,
  `chgby_id` int(11) default NULL COMMENT 'reference to AAA_users',
  `change_date` timestamp NULL default NULL COMMENT 'time of change',
  `description` text NOT NULL,
  `back_out` text NOT NULL,
  `status` varchar(100) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `change_id` (`change_id`),
  CONSTRAINT `plugin_ChangeManager_Components_ibfk_1` FOREIGN KEY (`change_id`) REFERENCES `plugin_ChangeManager_Changes` (`change_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=latin1;
SET character_set_client = @saved_cs_client;
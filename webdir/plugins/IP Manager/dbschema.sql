-- Host: localhost
-- Generation Time: Apr 28, 2011 at 11:31 AM
-- Server version: 5.5.9
-- PHP Version: 5.3.5


SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET foreign_key_checks = 0;

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `BCNET`
--

-- --------------------------------------------------------

--
-- Table structure for table `ipmanager_class`
--

CREATE TABLE `ipmanager_class` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the class',
  `class_name` varchar(50) NOT NULL COMMENT 'name of the class',
  `class_desc` text NOT NULL COMMENT 'description of the class',
  PRIMARY KEY (`class_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Table for ipmanager\r\nclasses/types' AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `ipmanager_netblocks`
--

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
  KEY `assigned_to` (`assigned_to`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='All the available netblocks' AUTO_INCREMENT=4856 ;

-- --------------------------------------------------------

--
-- Table structure for table `ipmanager_tags`
--

CREATE TABLE `ipmanager_tags` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id of the tag',
  `tag_name` varchar(50) NOT NULL COMMENT 'name of the tag',
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='Table for ipmanager\r\nclasses/types' AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Table structure for table `ipmanager_tags_netblock`
--

CREATE TABLE `ipmanager_tags_netblock` (
  `tag_name` varchar(50) NOT NULL COMMENT 'name of the tags',
  `netblock_id` int(11) NOT NULL COMMENT 'refers to netblock ids',
  PRIMARY KEY (`tag_name`,`netblock_id`),
  KEY `netblock_id` (`netblock_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='relationship table for\r\ntags and netblocks';

-- --------------------------------------------------------

--
-- Table structure for table `ipmanager_vlans`
--

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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COMMENT='vlan manager' AUTO_INCREMENT=4 ;

-- --------------------------------------------------------

--
-- Table structure for table `ipmanager_vlans_tags`
--

CREATE TABLE `ipmanager_vlans_tags` (
  `id` int(11) NOT NULL,
  `tag` varchar(100) NOT NULL,
  PRIMARY KEY (`id`,`tag`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='vlan tags relationshiptable';

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ipmanager_netblocks`
--
ALTER TABLE `ipmanager_netblocks`
  ADD CONSTRAINT `ipmanager_netblocks_ibfk_1` FOREIGN KEY (`parent`) REFERENCES `ipmanager_netblocks` (`netblock_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ipmanager_netblocks_ibfk_2` FOREIGN KEY (`owner`) REFERENCES `contact_groups` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ipmanager_netblocks_ibfk_3` FOREIGN KEY (`assigned_to`) REFERENCES `contact_groups` (`group_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `ipmanager_netblocks_ibfk_4` FOREIGN KEY (`location`) REFERENCES `pop_locations` (`location_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `ipmanager_tags_netblock`
--
ALTER TABLE `ipmanager_tags_netblock`
  ADD CONSTRAINT `ipmanager_tags_netblock_ibfk_2` FOREIGN KEY (`netblock_id`) REFERENCES `ipmanager_netblocks` (`netblock_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `ipmanager_vlans_tags`
--
ALTER TABLE `ipmanager_vlans_tags`
  ADD CONSTRAINT `ipmanager_vlans_tags_ibfk_1` FOREIGN KEY (`id`) REFERENCES `ipmanager_vlans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

SET foreign_key_checks = 1;

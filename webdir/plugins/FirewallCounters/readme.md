This plugin is called FirewallCounters. 

Implementation details:
--------------------------------
There are two perl files within the plugin folder:

  * dispatch_fwcounters.pl: This is typically set to run every 5 minutes from the plugins configuration. It forks process fwcounters.pl script for each device ID. This file was adapted from dispatch_snmp_pollers.pl. This script is hardcoded designed to only search and query devices of vendor "Juniper" and type "firewall" and "router".

  * fwcounters.pl : This is the actual script that will collect the list of firewall counters configured for a device ID specified and then record all the packet and byte counts into RRD files within a new /var/www/cmdb/rrd-files/fwcounters subdirectory. Collection of the counter information is based on relating 3 different SNMP OID results for each device.

There is one plugin.php file for the plugin's GUI. The Firewall Counters GUI is available from the Statistics menu. Navigating to the Firewall Counters page displays a drop-down list to view all the counter RRD files for a particular chosen device. Once a deviceID is chosen, it displays all graphs by simple enumeration of all RRD files within the /var/www/cmdb/rrd-files/fwcounters directory with the particular deviceID. The display will also show the description/aliases for each interface for that device.


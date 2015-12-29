The official NetHarbour repo.
===

see INSTALL for installation help
To make changes, fork the repo to your personal github, then send a pull request. This is production code.

For More info: http://netharbour.net/

Features
----
* Service management
* Device management
* IP address planner (IPAM) IPv4, IPv6 and VLAN management
* Out of the box Cacti like graphs
* Secure password manager
* Nagios like monitoring
* Flexible reporting capabilities
* Plugin framework
* Dashboard widgets
* Location management
* Contact management


Version
----
0.1

Dependencies
-----------
* MySQL
* PHP
* Apache
* rrdtool
* snmp


## Docker Usage
------------
* `cd netharbour`
* `docker-compose build`
* `docker-compose up -d`

### Getting container IP address:
* `docker inspect --format '{{ .NetworkSettings.IPAddress }}' netharbour_web_1`

### Viewing Web Interface
* Visit http://(docker_ip)/netharbour user: admin / pass: admin


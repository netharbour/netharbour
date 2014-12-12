#----------------- Upgrading from a previous version-----------------------------

This version contain a number of new mysql tables as well as changes to existing tables.
The fisrt step will be to create a backup of the old cmdb directory as well as the old database.
Then we'll upgrade and make the database changes

!! NOTE !!!
You should only update one version at a time, so from 1.1 to 1.2, to 1.3, etc
This is because of the database changes.

#----------------- Create backups--------------------------------

Create a copy of the existing CMDB backup:
	cp -R cmdb cmdb-old
	cd cmdb-old

then create a mysqldump, in the example below mysqluser is the mysql username
mysqlpass is mysql password and cmdb is the database name.
The file is written to CMDB.sql.
	
	mysqldump -u mysqluser -pmysqlpass cmdb > CMDB.sql

Now we should have a full backup of all files as well as the database in the cmdb-old directory

#----------------- Install new version --------------------------------

We'll install the new version in a directory called cmdb1.6
make sure you are in the root directory of your htdocs

First untar the file and move it to cmdb1.6:
	tar -xvf cmdb1.6.tar
	mv release-1.6/ cmdb1.6

Copy old config files to new directory:
	cp cmdb-old/config/cmdb.conf cmdb1.6/config/
	cp cmdb-old/config/graph.conf cmdb1.6/config/

Copy old rrd files to new directory:
	cp -r cmdb-old/rrd-files/* cmdb1.6/rrd-files/


Now you should already be able to log in. However because the database is still running the old version
Some features and functionality might not work correctly. 

#----------------- Upgrade database schema --------------------------------

The directory  cmdb1.6 should contain a file called upgrade.sql
We need to feed that into mysql so that it will be updated. 

Note that you can only upgrade from a version that's one lower then the newer one.
for version 1.6 you can upgrade directly from 1.3

in this example mysqluser is the mysql username
mysqlpass is mysql password and cmdb is the database name.

	cat upgrade.sql | mysql -u mysqluser -pmysqlpass cmdb	


#----------------- Plugins --------------------------------
Some plugins have local congfiguration files.
For example the weathermap plugin. Make sure to copy the configuration files
from the old version to the new versions.

#----------------- Clean up --------------------------------
I like to run the current version in the directory called cmdb.
We can use links to link this to the current versions.
We are asumming you now have cmdb-old and cmdb1.6

first delete the current cmdb directory. That's ok because if all is correct
you should have an exact copy of this directory in cmdb-old

Now create a link called cmdb that links to cmdb1.6
	
	ln -s  cmdb1.6 cmdb 

in the future when we upgrade you can just change the link so that it points to the latest version.


#----------------- Rollback --------------------------------

If something goes wrong we can always do a rollback.
you have all the files in cmdb-old
that same directory has a file called CMDB.sql, this can be imported into mysql
You probably want to drop the cmdb database first and then import the old data


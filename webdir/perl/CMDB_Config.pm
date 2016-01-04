##########################################################################
# Andree Toonk
# June 16, 2010
# This class is to provide easy access to properties defined in the 
# local config file 'cmdb.conf' and the database
#
# It wil lfirst read the local config file and add all the found config
# elements to %config. This should include the mysql connection info.
# Next it will connect to the database and add all values from the 
# properties table and add these to %config
#
# It's not object oriented... (yet), not sure if that's needed.
# For now you should just use CMDB_Config::get_config which returns
# a hash with as key the properties and the values as values.
##########################################################################


package CMDB_Config;
use warnings;

use DBI;
use Config::Simple;

my %config;

sub get_config {
	my $config_file = shift;
	read_file($config_file);
	read_sql_properties();
	return %config;
}

sub read_file {
	my $config_file = shift;
	my %config_values;
	Config::Simple->import_from($config_file, \%config_values) or die Config::Simple->error();
	# After reading it prepends 'default.' 
	# example: default.db_user => cmdb_user
	while ( my ($key, $value) = each(%config_values) ) {
		if ($key =~ /default\.(.*)/) {
			$config{$1} = $value;
		}
	}
}


sub read_sql_properties {
	my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
	my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass}) or warn("Could not connect to Mysql!");
	my $query = "select name, value from properties " ;
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	while (my @data = $sth->fetchrow_array()) {
		$config{$data[0]} = $data[1];
	}
	$sth->finish();
}
1;

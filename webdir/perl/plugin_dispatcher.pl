#!/usr/bin/env perl


####################################################################
# Andree Toonk
# May 26 2009
# Vancouver, BC, Canada
#
# This script will start all the other plugins
# This is the parent that will fork childs, so it's nice and fast
#
#The script should run from crontab every minute
#Based on the configured interval it will call the correct plugins
#Maximum interval is 1440, i.e. once per day
#
# If a script runs to long it will be killed, this is to make sure
# we don't halt the system. The time out can be counfigured in seconds
# by changing the $timeout variable
#
# $config_file specifies the config file
#####################################################################

my $config_file = "config/cmdb.conf";
my $timeout = 300;

use strict;
use warnings;
use DBI;

###################### Get config ###########################
# This specifies where CMDB_Config.pm is
use lib "perl/";
use CMDB_Config;
my %config = CMDB_Config::get_config($config_file);
###################### Get config ###########################


###################### Connect To MySQL ########################
my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass}) 
	or die("Could not connect to Mysql!");


############################################################
# Determine current time
# Based on the current minute we can determin which 
# plugins to run.
# Now determine number of minutes past midnight
# This allows us the run plugins with an interval greater
# than 60, upto 24*60 = 1440 min = 24h
############################################################
my $minutes_after_midnight = undef;
my @timeData = localtime(time);
my $min = $timeData[1];
my $hour = $timeData[2] *60;
$minutes_after_midnight = $min + $hour;
############################################################

my @childs;
# This is for time out, to make sure the scripts don't hang
eval {
	local $SIG{ALRM} = sub {
		print "Warning collector script terminated because of timeout ($timeout seconds)\n";
		#kill 'INT', $pid;
		foreach (@childs) {
			kill 'INT', $_;
		}
		die 'alarm';
	};
	alarm $timeout;

	my %plugins = get_plugins($minutes_after_midnight);	
	while ( my ($plugin_name, $plugin_script) = each(%plugins) ) {
		my $pid = fork();
		if ($pid) {
			# parent
			#print "pid is $pid, parent $$\n";
			push(@childs, $pid);
		} elsif ($pid == 0) {
       			# child
			print scalar localtime();
			print " Starting $plugin_name $plugin_script\n";
			my @out = `$plugin_script `;
			if ($#out > 0) {
				print "--------------------- $plugin_name ----------------\n"; 
				print @out;
				print "-------------------------------------------------\n";
			}
			#print "Executing: $plugin_name -> $plugin_location $device_name $snmp_ro\n";
			print scalar localtime();
			print " Done with  $plugin_name\n";
			exit(0);
		} else {
			die "couldnt fork: $!\n";
		}

		# Need a way to pass arguments as well, now always sent hostname and snmp_ro
		#print "Executing: $plugin_name -> $plugin_location $device_name $snmp_ro\n";

	}	

	foreach (@childs) {
    		my $tmp = waitpid($_, 0);
	}
	alarm 0;
};
die "Timeout Exit\n" if $@ and  $@ =~ /alarm/;

sub get_plugins {
	my $minutes_after_midnight = shift;
	my %plugins;
	if ((!defined($minutes_after_midnight)) || ($minutes_after_midnight > 1440))  {
		warn "Invalid number for minutes after midnight $minutes_after_midnight\n";
		return %plugins;
	}
	#print "$minutes_after_midnight\n";
	my $query = "select name, poller_script, poller_interval  
		FROM Plugins_plugin 
		WHERE enabled > 0 AND poller > 0";
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;

	while (my @data = $sth->fetchrow_array()) {
		my $plugin_name = $data[0];
		my $plugin_location = $data[1];
		my $interval = $data[2];
		if ($minutes_after_midnight % $interval eq 0) {
			$plugins{$plugin_name} = $plugin_location;
			print "Adding $plugin_name = $plugin_location inteval $interval $minutes_after_midnight\n";
		} else {
			print "NOT Adding $plugin_name = $plugin_location inteval $interval $minutes_after_midnight\n";
		}
	}
	return %plugins;
}

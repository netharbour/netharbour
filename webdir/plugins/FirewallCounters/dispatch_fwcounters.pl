#!/usr/bin/env perl

use strict;
use warnings;

#Config part
my $config_file = "config/cmdb.conf";
my $max_process = 30;
my $verbose = 3;

### Import libs
use DBI;


#----------------- Get config ----------------------------- 
# This specifies where CMDB_Config.pm is
use lib "perl/";
use CMDB_Config;
my %config = CMDB_Config::get_config($config_file);
#use Data::Dumper; print Dumper( \%config );

#------------------ Connect To MySQL -------------------------------
my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass}) 
        or die("Could not connect to Mysql!");

#------------------ Determine If we need to check thresholds ------------------
my $do_thresholds = undef;
if ((defined($config{threshold_check})) && ($config{threshold_check} > 0)) {
	$do_thresholds = 1;
}


#------------------ Determine location of script ------------------------------
use Cwd qw(abs_path);
my $cur_dir = get_current_dir();

#------------------ Get Devices to check --------------------------------------
my %devices_todo = get_devices_to_poll();

#------------------ Start SNMP service collection script ----------------------


#------------------------------------------------------------------------------
# Now fork proces for each device, 
# The number of simultaneous processes is limited by $max_proces
my $counter = 0;
for my $device_id ( sort {$a <=> $b} (keys %devices_todo) ) {
	my $device_name = $devices_todo{$device_id};
	wait unless $counter <= $max_process;
	#die "Fork failed: $!\n" unless defined (my $pid = fork);
	my $pid = fork();
	if ($pid) {
        	# parent
        	#print "pid is $pid, parent $$\n";
	} elsif ($pid == 0) {
		# child

		#------------------ Start SNMP pollers ------------------- ----------------------
		print scalar localtime() . " $0 -- starting proces #$counter: => $device_name\n" unless $verbose < 2;
		my $start_time = time;
		&exec_fwcounters_poller($device_id,$device_name)  unless $pid;
		my $end_time = time;
		my $proc_time = $end_time - $start_time;
		print scalar localtime() . " $0 -- finished proces #$counter: => $device_name ($proc_time sec)\n" unless $verbose < 2;
		exit 0;
	} else {
		warn "couldnt fork: $!\n";
	}

	$counter++;
}

# Waitl till all process are finished
1 until -1 == wait;

#------------------ Sub routines ------------------------------

sub exec_fwcounters_poller {
	my $device_id = shift;
	my $device_name = shift;
	
	my $cli = "$cur_dir/fwcounters.pl -d $device_id 2>&1";
        my @results = `$cli`;
        foreach my $line (@results) {
		print scalar localtime() . " fwcounters.pl -- $device_name -> $line" unless $verbose < 1;
	}
}

sub get_devices_to_poll {
	my %device_list;
        # In the future we can add an array of types of devices to query as a parameter
	my $query = "SELECT device_id,name
	FROM Devices
	WHERE type IN (SELECT device_type_id FROM Device_types 
		WHERE vendor LIKE \"%Juniper%\"
		AND (type LIKE '%router%' OR type LIKE '%firewall%' ))
	AND archived = \"0\"";
#       my $query = "select plugin_SNMPPoller_devices.device_id, Devices.name
#                FROM plugin_SNMPPoller_devices, Devices
#                WHERE plugin_SNMPPoller_devices.enabled = '1'
#                AND plugin_SNMPPoller_devices.device_id = Devices.device_id
#                order by plugin_SNMPPoller_devices.device_id ";

	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	while (my @data = $sth->fetchrow_array()) {
 		$device_list{$data[0]} = $data[1];
	}
	$sth->finish();
	return %device_list;
}

sub get_current_dir {
	my $path = abs_path($0);
	my $full_dir = "";
	my @dirs = split('/', $path);
	for (my $i=0; $i<$#dirs ; $i++)  {
		if ($dirs[$i] ne '') {
			$full_dir .= "/$dirs[$i]";
		}
	}
	return $full_dir;
}

#!/usr/bin/env perl

use strict;
use warnings;

#Config part
my $config_file = "config/cmdb.conf";
my $max_process = 30;
my $verbose = 2;

### Import libs
use DBI;


#----------------- Get config -----------------------------
# This specifies where CMDB_Config.pm is
use lib "perl/";
use CMDB_Config;
my %config = CMDB_Config::get_config($config_file);

#------------------ Connect To MySQL -------------------------------
my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass})
    or die("Could not connect to Mysql!");

#------------------ Determine location of script ------------------------------
use Cwd qw(abs_path);
my $cur_dir = get_current_dir();

#------------------ Get Devices to check --------------------------------------
my %devices_todo = get_devices_to_check();

#------------------ Start MAC Accounting collection script ----------------------
# Now fork process for each device,
# The number of simultaneous processes is limited by $max_process
my $counter = 0;
for my $device_id ( sort {$a <=> $b} (keys %devices_todo) ) {
    my $device_name = $devices_todo{$device_id};
    wait unless $counter <= $max_process;
    my $pid = fork();
    if ($pid) {
        # parent
    } elsif ($pid == 0) {
        # child

        #------------------ Start MAC acct ------------------------------------------
        print scalar localtime() . " $0 -- starting process #$counter: => $device_name\n" unless $verbose < 2;
        my $start_time = time;
        &exec_mac_acct($device_id, $device_name)  unless $pid;
        my $end_time = time;
        my $proc_time = $end_time - $start_time;
        print scalar localtime() . " $0 -- finished process #$counter: => $device_name ($proc_time sec)\n" unless $verbose < 2;
        exit 0;
    } else {
        warn "could not fork: $!\n";
    }

    $counter++;
}

# Wait till all processes are finished
1 until -1 == wait;

#------------------ Sub routines ------------------------------

sub exec_mac_acct {
    my $device_id = shift;
    my $device_name = shift;

    my $cli = "$cur_dir/mac_acct.pl -d $device_id 2>&1";
    my @results = `$cli`;
    foreach my $line (@results) {
        print scalar localtime() . " mac_acct.pl -- $device_name -> $line" unless $verbose < 1;
    }
}

sub get_devices_to_check {
    my %device_list;
    my $query = "
        SELECT plugin_MACAccounting_devices.device_id, Devices.name
		FROM plugin_MACAccounting_devices, Devices
		WHERE plugin_MACAccounting_devices.enabled = '1'
		AND plugin_MACAccounting_devices.device_id = Devices.device_id
		ORDER BY plugin_MACAccounting_devices.device_id
    ";

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


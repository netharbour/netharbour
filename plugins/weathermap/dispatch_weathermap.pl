#!/usr/bin/env perl

# Author: Craig Tomkow
# Date: June 7, 2019

use strict;
use warnings;
# TODO: get this working to dispatch ./weathermap --config configs/mahWeather.conf
# core imports
use Cwd qw(abs_path);

# 3rd part imports
use DBI;

# netharbour imports
use lib "perl/";
use CMDB_Config;

# config file
my %config = CMDB_Config::get_config("config/cmdb.conf");

# script config variables
my $pid_count   = 0;
my $max_process = 5;
my @pids        = ();
my $verbose     = 2;

# database connection
my $conn_info = "DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh       = DBI->connect($conn_info, $config{db_user}, $config{db_pass}) or die("Could not connect to database!");

my $current_directory = get_current_directory();
my $weathermap_array_ref = get_devices_to_check();

########## main loop, fork for each device/LS pair limited by $max_process ##########

foreach my $conf_array_ref (@$weathermap_array_ref) {

    my $configuration = @$conf_array_ref[0];

    # if max_processes reached, wait for children to finish
    if (@pids >= $max_process) {
        1 until -1 == wait; # wait for child processes and reap zombies
        @pids = ();
        $pid_count = 0;
    }

    $pid_count++;
    my $pid = fork();

    if ($pid) {
        # parent
        push(@pids, $pid);
    } elsif ($pid == 0) {
        # child

        # dispatch script
        print(scalar localtime() . " $0 -- starting proc num $pid_count: configuration: $configuration\n") unless $verbose < 2;
        my $start_time = time;

        weathermap($configuration);

        my $end_time  = time;
        my $proc_time = $end_time - $start_time;
        print(scalar localtime() . " $0 -- finished proc num $pid_count: configuration: $configuration ($proc_time sec)\n") unless $verbose < 2;
        exit(0);
    } else {
        warn "could not fork: $!\n";
    }
}

#  wait for child processes and reap zombies
1 until -1 == wait;

########## Subroutines ##########

sub weathermap {
    my ($configuration) = @_;

    my $cli = "$current_directory/weathermap --config configs/$configuration 2>&1";
    my @results = `$cli`;
    foreach my $line (@results) {
        print(scalar localtime() . " weathermap -- $configuration -> $line") unless $verbose < 1;
    }
}

sub get_devices_to_check {
    my $data;
    my $query = "
        SELECT
            configuration_file
		FROM
		    plugin_Weathermap_configuration
    ";

    my $sth = $dbh->prepare($query);
    $sth->execute() or die "Couldn't execute statement: " . $sth->errstr;

    $data = $sth->fetchall_arrayref();

    $sth->finish();
    return $data;
}

sub get_current_directory {
    my $path = abs_path($0);
    my $full_dir = "";
    my @dirs = split('/', $path);
    for (my $i=0; $i<$#dirs; $i++)  {
        if ($dirs[$i] ne '') {
            $full_dir .= "/$dirs[$i]";
        }
    }
    return $full_dir;
}

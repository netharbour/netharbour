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
my $check_time_out = 30;
my $max_process = 10; # How many devices at the same time
my $max_process_per_device = 3; # how many threads per device
my $verbose = 2;


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

my %checks = get_events($minutes_after_midnight);	

#------------------------------------------------------------------------------
# Now fork proces for each device, 
# The number of simultaneous processes is limited by $max_proces
my $counter =1;
for my $device_id ( sort {$a <=> $b} (keys %checks) ) {
	my @check_ids = @{$checks{$device_id}};
        wait unless $counter <= $max_process;
        #die "Fork failed: $!\n" unless defined (my $pid = fork);
        #exit &exec_poller($device_id)  unless $pid;
        my $pid = fork();
        if ($pid) {
                # parent
                #print "pid is $pid, parent $$\n";
        } elsif ($pid == 0) {
                # child
		# Start thread per device
		
		#my $cli = "./monpol.php -i $check_id";
                #&exec_poller($cli)  unless $pid;
		print "------------ ". scalar localtime() . " Starting Thread for for device $device_id ----------------\n";

		&afork(\@check_ids,$max_process_per_device,\&exec_poller);
		print "------------ ".scalar localtime() . " Finished Thread for for device $device_id ----------------\n";
                exit 0;
        } else {
                warn "couldnt fork: $!\n";
        }

        $counter++;
}

# Waitl till all process are finished
1 until -1 == wait;



#------------------ Sub routines ------------------------------

sub afork (\@$&) {
        my ($data, $max, $code) = @_;
        my $c = 0;
	my $out;
	my @childs;


        foreach my $data (@$data) {
                wait unless $c <= $max;
        	my $pid = fork();
		if ($pid) {
			#parent
			push(@childs, $pid);
		}
		elsif ($pid == 0) {
			$out .= $code -> ($data) unless $pid;
			print $out;
			exit 0;
		} else {
                	warn "couldnt fork $pid: $!\n";
		}
                #die "Fork failed: $!\n" unless defined (my $pid = fork);
                #exit $code -> ($data) unless $pid;
		#$out .= $code -> ($data) unless $pid;
		#exit 0;
		$c++;
        }
        1 until -1 == wait;
	return $out;
}

sub exec_poller {
        my $check_id = shift;
	my $cli = "./monpol.php -i $check_id";

	# Just in case of problems, let's not hang Nagios
	$SIG{'ALRM'} = sub {
		print ("ERROR: $cli (alarm timeout $check_time_out sec)\n");
		exit ;
	};
	alarm($check_time_out);

	# End timeout

        my @results = `$cli 2>&1`;
	my $res = scalar localtime() . " $cli => ";
	$res .= join("\n",@results);
	#print "$res\n"  unless $verbose < 1;
	return $res;
	alarm(0);

	#die "Timeout Exit\n" if $@ and  $@ =~ /alarm/;

}


sub get_events {
	my $minutes_after_midnight = shift;
	my %checks;
	if ((!defined($minutes_after_midnight)) || ($minutes_after_midnight > 1440))  {
		warn "Invalid number for minutes after midnight $minutes_after_midnight\n";
		return %checks;
	}
	#print "$minutes_after_midnight\n";
	my $query = "select check_id, device_id, template_id, check_interval
		FROM service_checks 
		WHERE archived = '0'";
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;

	while (my @data = $sth->fetchrow_array()) {
		my $template_id = "N/A";
		if (defined($data[2])) {
			$template_id = $data[2];
		}
		my $name = "device_id $data[1] template_id $template_id";
		my $id = $data[0];
		my $device_id = $data[1];
		my $interval = $data[3];
		if ($minutes_after_midnight % $interval eq 0) {
			#$checks{$id} = $name;
			#$checks{$device_id} = $name;
			push @{ $checks{$device_id} },$id
			#print "Adding $plugin_name = $plugin_location inteval $interval $minutes_after_midnight\n";
		} else {
			#print "NOT Adding $plugin_name = $plugin_location inteval $interval $minutes_after_midnight\n";
		}
	}
	return %checks;
}

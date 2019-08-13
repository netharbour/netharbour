#!/usr/bin/env perl

use strict;
use warnings;
use lib "check-scripts";
use Getopt::Std;

my $max_sessions = 3000;
my $max_failed_sessions = 20;
my $max_sessions_in_use = $max_sessions;

my $hostname;

my $opt_string = 'H:';
my %opt;
getopts( "$opt_string", \%opt );


if (!defined $opt{H}) {
	print "Error: No host defined\n";               
	exit 4;
	} else {
		$hostname = $opt{H};
}

my @lines;
my %result;

@lines = `ssh -l cmdb $hostname "show security flow session summary"`;

foreach(@lines) {
	my $line = $_;	
	chomp $line;
	if ($line =~ /(\s+)?(.+):\s?(\d+)/) {
		#print "key-$2- val-$3-\n";
		$result{$2} = $3;
	}
}

@lines = `ssh -l cmdb $hostname "show security flow statistics"`;
foreach(@lines) {
	my $line = $_;	
	chomp $line;
	if ($line =~ /(\s+)?(.+):\s?(\d+)/) {
		#print "key-$2- val-$3-\n";
		$result{$2} = $3;
	}
}

my $info ="";
my $perfdata ="";
my $exit_status = 3;

#key-Unicast-sessions- val-2321-
#key-Multicast-sessions- val-0-
#key-Failed-sessions- val-0-
#key-Sessions-in-use- val-2575-
#key-Valid sessions- val-2346-
#key-Pending sessions- val-0-
#key-Invalidated sessions- val-229-
##key-Sessions in other states- val-0-
#key-Maximum-sessions- val-204800-
##key-Current sessions- val-2377-
#key-Packets forwarded- val-588845953-
#key-Packets dropped- val-675196236-
#key-Fragment packets- val-11137281-

while ( my ($key, $value) = each(%result) ) {

	if ($key eq "Current sessions") {
		$exit_status  = 0;
		$info .= "$key: $value  ";
		if ($value > $max_sessions) {
			$exit_status  = 2;
		}
		$perfdata .= "current_sessions=$value, "
	}
	elsif ($key eq "Sessions-in-use") {
		$info .= "$key: $value ";
		$exit_status  = 0;
		if ($value > $max_sessions_in_use) {
			$exit_status  = 2;
		}
		$perfdata .= "Sessions-in-use=$value, "
	}
	elsif ($key eq "Failed-sessions") {
		$info .= "$key: $value ";
		$exit_status  = 0;
		if ($value > $max_failed_sessions) {
			$exit_status  = 2;
		}
		$perfdata .= "Failed-sessions=$value, "
	} 
	elsif ($key eq "Unicast-sessions") {
		$perfdata .= "$key=$value, "
	}
	elsif ($key eq "Multicast-sessions") {
		$perfdata .= "$key=$value, "
	}
	elsif ($key eq "Valid sessions") {
		$perfdata .= "Valid-sessions=$value, "
	}
	elsif ($key eq "Invalidated sessions") {
		$perfdata .= "Invalidated-sessions=$value, "
	}
	elsif ($key eq "Pending sessions") {
		$perfdata .= "Pending-sessions=$value, "
	}
	elsif ($key eq "Packets dropped") {
		$perfdata .= "PacketsDropped=".$value ."c, "
	}
}
print "$info Maximum-sessions: $result{'Maximum-sessions'} | $perfdata\n";
exit $exit_status ;

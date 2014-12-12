#!/usr/bin/perl

use strict;
use warnings;
use lib "check-scripts";
use Getopt::Std;

#-------------------------------------------------------------------------------
# Some defaults
#-------------------------------------------------------------------------------

my $default_loss_warning = 0;
my $default_loss_critical = 5 ;
my $default_rtt_warning = 230;
my $default_rtt_critical = 250;
my $default_packet_count = 10;
my $default_packet_size = 512;


#-------------------------------------------------------------------------------
# Check the usage
#-------------------------------------------------------------------------------

my ($hostname, $loss_warning,$loss_critical,$rtt_warning,$rtt_critical,$losscheck,$packet_count, $packet_size);

check_usage();
sub check_usage {
	my $opt_string = 'H:r:R:l:L:c:h' ;
	my %opt;
	getopts( "$opt_string", \%opt );

	if (defined $opt{h}) {
		&print_help();
		exit 4;
	}
	if (!defined $opt{H}) {
		print "Error: No host defined\n";		
		&print_help();
		exit 4;
	} else {
		$hostname = $opt{H};
	}

	if (defined $opt{c} && $opt{c} >= 0) {
		$packet_count = $opt{c};
	} else {
		$packet_count = $default_packet_count;
	}
	if (defined $opt{s} && $opt{s} >= 0) {
		$packet_size = $opt{s};
	} else {
		$packet_size = $default_packet_size;
	}
	if (defined $opt{l} && $opt{l} >= 0) {
		$loss_warning = $opt{l};
	} else {
		$loss_warning = $default_loss_warning;
	}

	if (defined $opt{L} && $opt{L} >= 0) {
		$loss_critical = $opt{L};
	} else {
		$loss_critical = $default_loss_critical;
	}

	if (defined $opt{r} && $opt{r} >= 0) {
		$rtt_warning = $opt{r};
	} else {
		$rtt_warning = $default_rtt_warning;
	}

	if (defined $opt{R} && $opt{R} >= 0) {
		$rtt_critical = $opt{R};
	} else {
		$rtt_critical = $default_rtt_critical;
	}
}



#===============================================================================
#      Initialization
# 	Set defaults
#===============================================================================


# start with loss
my $loss_status_str = "";
my $loss_perfdata = "";
my $loss_status_exit = 3;
my $rtt_status_str = "";
my $rtt_perf_data = "";
my $rtt_status_exit = 3;
my $lossp = undef;
my $rttms = undef;

#my @ping_result = `/bin/ping -c $packet_count $hostname`;
my @ping_result = `/bin/ping -c 30 -i 0.2 -f -s $packet_size $hostname`;

foreach (@ping_result) {
        my $line = $_;
        chomp $line;
        if ($line =~ /(\d+)\% packet loss/) {
                $lossp = $1;
        }
        if ($line =~ /mdev = (\d+\.\d+)\/(\d+\.\d+)\/(\d+\.\d+)\/(\d+\.\d+) ms/) {
                $rttms =  $2;
        }
}


#10 packets transmitted, 10 received, 0% packet loss, time 9071ms
# 10 packets transmitted, 0 received, +10 errors, 100% packet loss, time 9060ms
#rtt min/avg/max/mdev = 145.948/146.081/146.255/0.429 ms
#
analyze_loss($lossp);
analyze_rtt($rttms);

# Build performance data string
my $perfdata = "";
if (($rtt_perf_data ne '') &&  ($loss_perfdata ne '') ) {
	$perfdata = " | $rtt_perf_data , $loss_perfdata";
}

# Now determine exit status

if (($loss_status_exit == 2) || ($rtt_status_exit == 2)) {
	print "Critical $hostname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 2;
}
elsif (($loss_status_exit == 1) || ($rtt_status_exit == 1)) {
	print "Warning $hostname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 1;
}
elsif (($loss_status_exit == 3) || ($rtt_status_exit == 3)) {
	print "Unknown $hostname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 3;
} elsif (($loss_status_exit == 0) && ($rtt_status_exit == 0)) {
	print "Ok $hostname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 0;
} 
else {
	print "Unknown status $hostname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 3;
}


#-------------------------------------------------------
# Functions
#-------------------------------------------------------
sub analyze_rtt {
	my $rtt = shift;

	# Now analyze
	if (!defined($rtt)) {
        if ($losscheck == 100) {
            $rtt_status_str = " ";
            $rtt_perf_data = "RTT = 0ms";
		    $rtt_status_exit =  3;
            return;
        } else {
		    $rtt_status_str = "Unknown - ping failed\n";
		    $rtt_status_exit =  3;
        }
		return;
	}

	# check if is ms or us
	my $rtt_l = "ms";
	my $rtt_v = $rtt;
	my $rtt_ms = $rtt;
		$rtt_v = sprintf "%.0f",($rtt );
		$rtt_l = "ms";
		$rtt_ms = $rtt_v;
	# Now evaluate results
	$rtt_status_str = "Avg rtt $rtt_v $rtt_l";
	$rtt_perf_data = "RTT = ".$rtt_v ."$rtt_l";
	if ($rtt_ms > $rtt_critical ) {
		 $rtt_status_exit =  2;
	} elsif ($rtt_ms > $rtt_warning) {
		 $rtt_status_exit =  1;
	} elsif (($rtt_ms <= $rtt_critical) && ($rtt_ms <= $rtt_warning)) {
		 $rtt_status_exit =  0;
	} else {
		$rtt_status_str = "Unknown: Something went wrong....\n";
		 $rtt_status_exit =  3;
	}
}

sub analyze_loss {
	my $loss = shift;
	$losscheck = $loss;

	# Now evaluate results
	$loss_status_str = "$loss% packet loss";
	$loss_perfdata = "packet_loss = $loss%";


	if ($loss > $loss_critical ) {
		 $loss_status_exit =  2;
	} elsif ($loss > $loss_warning) {
		 $loss_status_exit =  1;
	} elsif (($loss <= $loss_critical) && ($loss <= $loss_warning)) {
		 $loss_status_exit =  0;
	} else {
		$loss_status_str = "Unknown: Something went wrong....\n";
		 $loss_status_exit =  3;
	}
}


sub print_help() {

	print "My super ping nagios plugin
	usage:   $0 -H hostname  [-r <rtt_warning>] [-R <rtt_critical>] [-l <packet_loss_warning>] [-L <packet_loss_critical>]
	example: $0 -H cr1.vantx1 -r 10 -R 20

	[-h]    : Print this message

	[-H]   	:  FQDN or IP address of host to poll

	[-s]   	:  Packet size
		:  default is $default_packet_size;

	[-r]  	:  rtt threshold for warnings in ms
		:  default is $default_rtt_warning;

	[-R]  	:  rtt threshold for critical in ms
		:  default is $default_rtt_critical;

	[-l]  	:  packet loss threshold for warnings in percentage (0 .. 100)
		:  default is $default_loss_warning;

	[-L]  	:  packet loss threshold for critical in percentage (0 .. 100)
		:  default is $default_loss_critical;

	Andree Toonk: andree\@opendns.com  \n\n";
}

sub build_rtt_oid {
	my $t_owner = shift;
	my $t_test = shift;

	##################################################
	# Build the OID for min rrt number
	##################################################
	# base is 1.3.6.1.4.1.2636.3.50.1.3.1.3 for min RTT
	# base is 1.3.6.1.4.1.2636.3.50.1.3.1.5 for avg RTT
	# followed by number of chars in owner 
	# followed by owner in decimal (ascii) 
	# followed by number of chars in testname 
	# followed by testname in decimal (ascii)
	# followed by 2.1
	#
	# for rtt use
	#1.3.6.1.4.1.2636.3.50.1.3.1.5.3.110.109.99.12.118.97.110.116.120.50.45.112.103.116.120.49.2.1
	##################################################

	my @owner_dec = string_to_dec($t_owner);
	my @test_dec = string_to_dec($t_test);
	my $owner_count = @owner_dec;
	my $test_count = @test_dec;
	#my $oid = "1.3.6.1.4.1.2636.3.50.1.3.1.3";
	my $oid = "1.3.6.1.4.1.2636.3.50.1.3.1.5";

	$oid = $oid .".$owner_count";
	foreach (@owner_dec) {
		$oid = $oid .".". $_;
	}
	$oid = $oid .".$test_count";
	foreach (@test_dec) {
		$oid = $oid .".". $_;
	}
	# add .2 as we want results of last test
	$oid = $oid .".2.1";
	# Oid is build
	##################################################
	#push (@snmpoids,$oid);
	return $oid;
}


##################################################
# Just in case of problems, let's not hang Nagios
sub build_loss_oid {
	my $t_owner = shift;
	my $t_test = shift;

	##################################################
	# Build the OID for packetloss number
	##################################################
	# base is 1.3.6.1.4.1.2636.3.50.1.2.1.4.3
	# followed by owner in decimal (ascii) 
	# followed by number of chars in testname 
	# followed by testname in decimal (ascii)
	# followed by 2 (last char / end) = last test
	# ending in:
	#. 1 is current test
	# .2 is last test
	# .4 all test
	#
	# for rtt use
	#1.3.6.1.4.1.2636.3.50.1.3.1.5.3.110.109.99.12.118.97.110.116.120.50.45.112.103.116.120.49.2.1
	##################################################

	my @owner_dec = string_to_dec($t_owner);
	my @test_dec = string_to_dec($t_test);
	my $owner_count = @owner_dec;
	my $test_count = @test_dec;
	my $oid = "1.3.6.1.4.1.2636.3.50.1.2.1.4";

	$oid = $oid .".$owner_count";
	foreach (@owner_dec) {
		$oid = $oid .".". $_;
	}
	$oid = $oid .".$test_count";
	foreach (@test_dec) {
		$oid = $oid .".". $_;
	}
	# add .2 as we want results of last test
	$oid = $oid .".2";
	# Oid is build
	##################################################
	#push (@snmpoids,$oid);
	return $oid;
}


##################################################
# Just in case of problems, let's not hang Nagios
##################################################
my $TIMEOUT = 3;
$SIG{'ALRM'} = sub {
	print ("ERROR: Plugin took too long to complete (alarm)\n");
	exit 3;
};
alarm($TIMEOUT);
##################################################

sub get_snmp_result {
	my $hostname = shift;
	my $community = shift;
	my $oid = shift;

	##################################################
	# Build SNMP client
	##################################################
	my ($snmp, $snmperror);
	($snmp, $snmperror) = Net::SNMP->session(
                -hostname =>$hostname,
                -version => 'snmpv2c',
                -community => $community
	);
	# Send request

	#my $result = $snmp->get_request(
	#	-varbindlist => \@snmpoids
	#);
	my $result = $snmp->get_request($oid);

	if (!defined($result)) {
		my $answer = $snmp->error;
		$snmp->close;
		print ("UNKNOWN: SNMP error: $answer\n");
		exit 3;
	}
	return $result;
}


#	if ($result->{"$bgpPeerState.$bgppeer"} ne "noSuchInstance") {
#		$output = "$bgppeer (AS".
#			$result->{"$bgpPeerRemoteAs.$bgppeer"}.
#			") state is ".
#			$bgpPeerStates{$result->{"$bgpPeerState.$bgppeer"}};


######################################################
# Function to translate ascii into decimal notiation
######################################################
sub string_to_dec {
	my $string = shift;
	my @array = split //,$string;
	# converts the elements of the array into their
	# equivalent ASCII codes
	@array = map(ord, @array);
	return @array;
}



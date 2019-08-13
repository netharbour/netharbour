#!/usr/bin/env perl

use strict;
use warnings;
use lib "check-scripts";
use Net::SNMP;
use Getopt::Std;
use Net::Statsd;
use lib "perl/";
use DBI;
use CMDB_Config;


#-------------------------------------------------------------------------------
# Some defaults
#-------------------------------------------------------------------------------

my $statsd_server = undef;
my $statsd_port = undef;

my $default_loss_warning = 7;
my $default_loss_critical = 11;
my $default_rtt_warning = 450;
my $default_rtt_critical = 460;

my $config_file = "config/cmdb.conf";

### Import libs

###################### Get config ###########################
# This specifies where CMDB_Config.pm is

my %config = CMDB_Config::get_config($config_file);
#use Data::Dumper; print Dumper( \%config );
###################### Get config ###########################

###################### Connect To MySQL ########################
my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass}) 
        or die("Could not connect to Mysql!");
###################### Connect To MySQL ########################


#-------------------------------------------------------------------------------
# Check the usage
#-------------------------------------------------------------------------------

my ($hostname,$community,$owner,$testname, $loss_warning,$loss_critical,$rtt_warning,$rtt_critical, $losscheck);

check_usage();
sub check_usage {
	my $opt_string = 'H:C:o:r:R:l:L:t:h' ;
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

	if (!defined $opt{C}) {
		print "Error: No Community defined\n";		
		&print_help();
		exit 4;
	} else {
		$community = $opt{C};
	}

	if (!defined $opt{o}) {
		print "Error: No Owner defined\n";		
		&print_help();
		exit 4;
	} else {
		$owner = $opt{o};
	}

	if (!defined $opt{t}) {
		print "Error: No Test name defined\n";		
		&print_help();
		exit 4;
	} else {
		$testname = $opt{t};
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
        #$rtt_critical = $opt{R};
        #  RTT should never result in critical
		$rtt_critical = $default_rtt_critical;
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
my $loss_oid = build_loss_oid($owner,$testname);
my $snmp_result_loss = get_snmp_result($hostname,$community,$loss_oid);
analyze_loss($snmp_result_loss);

# Now RTT
my $rtt_status_str = "";
my $rtt_perf_data = "";
my $rtt_status_exit = 3;
my $rtt_oid = build_rtt_oid($owner,$testname);
my $snmp_result_rtt = get_snmp_result($hostname,$community,$rtt_oid);
analyze_rtt($snmp_result_rtt);

# Build performance data string
my $perfdata = "";
if (($rtt_perf_data ne '') &&  ($loss_perfdata ne '') ) {
	$perfdata = " | $rtt_perf_data , $loss_perfdata";
}

# Now determine exit status

if (($loss_status_exit == 2) || ($rtt_status_exit == 2)) {
	print "Critical $testname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 2;
}
elsif (($loss_status_exit == 1) || ($rtt_status_exit == 1)) {
	print "Warning $testname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 1;
}
elsif (($loss_status_exit == 3) || ($rtt_status_exit == 3)) {
	print "Unknown $testname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 3;
} elsif (($loss_status_exit == 0) && ($rtt_status_exit == 0)) {
	print "Ok $testname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 0;
} 
else {
	print "Unknown status $testname: $loss_status_str -- $rtt_status_str". $perfdata ."\n";
	exit 3;
}


#-------------------------------------------------------
# Functions
#-------------------------------------------------------
sub analyze_rtt {
	my $snmp_result = shift;

	# Now analyze
	if ($snmp_result->{$rtt_oid} eq "noSuchInstance") {
        if ($losscheck == 100) {
            $rtt_status_str = " ";
             $rtt_perf_data = "RTT = 0ms";
		    $rtt_status_exit =  3;
            return;
        } else {
		    $rtt_status_str = "Unknown rtt Test $testname not found, $rtt_oid\n";
		    $rtt_status_exit =  3;
        }
		return;
	}

	my $rtt = $snmp_result->{$rtt_oid};

	# check if is ms or us
	my $rtt_l = "ms";
	my $rtt_v = $rtt;
	my $rtt_ms = $rtt;
	if ($rtt < 1000) {
		$rtt_v = sprintf "%.0f",($rtt);
		$rtt_l = "us";
		$rtt_ms = $rtt_v / 1000;
	} else {
		$rtt_v = sprintf "%.0f",($rtt /1000);
		$rtt_l = "ms";
		$rtt_ms = $rtt_v;
	}

    # Hack
    send_statsd('rtt',$testname,$rtt_ms);
    update_transit_manager_metrics('rtt',$testname,$rtt_ms);
	
	# Now evaluate results
	$rtt_status_str = "Avg rtt $rtt_v $rtt_l";
	$rtt_perf_data = "RTT = ".$rtt_v ."$rtt_l";
	if ($rtt_ms >= $rtt_critical ) {
		 $rtt_status_exit =  2;
	} elsif ($rtt_ms >= $rtt_warning) {
		 $rtt_status_exit =  1;
	} elsif (($rtt_ms < $rtt_critical) && ($rtt_ms < $rtt_warning)) {
		 $rtt_status_exit =  0;
	} else {
		$rtt_status_str = "Unknown: Something went wrong....\n";
		 $rtt_status_exit =  3;
	}
}

sub analyze_loss {
	my $snmp_result = shift;

	# Now analyze
	if ($snmp_result->{$loss_oid} eq "noSuchInstance") {
		$loss_status_str = "Unknown loss Test $testname not found, $loss_oid\n";
		$loss_status_exit =  3;
		return;
	}

	my $loss = $snmp_result->{$loss_oid};
	$losscheck = $loss;
    
    # Hack
    send_statsd('loss',$testname,$loss);
    update_transit_manager_metrics('loss',$testname,$loss);

	# Now evaluate results
	$loss_status_str = "$loss% packet loss";
	$loss_perfdata = "packet_loss = $loss%";


	if ($loss >= $loss_critical ) {
		 $loss_status_exit =  2;
	} elsif ($loss >= $loss_warning) {
		 $loss_status_exit =  1;
	} elsif (($loss < $loss_critical) && ($loss < $loss_warning)) {
		 $loss_status_exit =  0;
	} else {
		$loss_status_str = "Unknown: Something went wrong....\n";
		 $loss_status_exit =  3;
	}
}


sub print_help() {

	print "Juniper RPM probe nagios plugin
	usage:   $0 -H hostname -C community -t testname -o owner [-r <rtt_warning>] [-R <rtt_critical>] [-l <packet_loss_warning>] [-L <packet_loss_critical>] [-s <statsD_server>] [-p <statsD_port>]
	example: $0 -H cr1.vantx1 -C secret -t vantx1-pgtx1 -o nmc -r 10 -R 20

	[-h]    : Print this message

	[-H]   	:  FQDN or IP address of host to poll

	[-C] 	:  SNMP community.

	[-t] 	:  test-name.

	[-o] 	:  owner-name.

	[-r]  	:  rtt threshold for warnings in ms
		:  default is $default_rtt_warning;

	[-R]  	:  rtt threshold for critical in ms
		:  default is $default_rtt_critical;

	[-l]  	:  packet loss threshold for warnings in percentage (0 .. 100)
		:  default is $default_loss_warning;

	[-L]  	:  packet loss threshold for critical in percentage (0 .. 100)
		:  default is $default_loss_critical;

	Andree Toonk: andree.toonk\@bc.net  \n\n";
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


sub update_transit_manager_metrics {
    my $type = shift; # rtt, loss, bw, etc
    my $testname = shift;
    my $value = shift;

    my $query = "INSERT INTO TransitManager_metrics SET 
			timestamp = CURRENT_TIMESTAMP(),
			metric = '$type',
			value = '$value',
			test_name = '$testname'";

    my $sth = $dbh->prepare($query);

    if (!$sth->execute()) {
	print "Unable to update database: $query"
    }
    $sth->finish();
    
}

sub send_statsd {
    my $type = shift;
    my $testname = shift;
    my $value = shift;
    if ((defined($statsd_server)) && (defined($statsd_port))) {
    	$Net::Statsd::HOST = $statsd_server;    # Default
    	$Net::Statsd::PORT = $statsd_port;          # Default
    	Net::Statsd::gauge("network.$testname.$type" => $value);
   }
}

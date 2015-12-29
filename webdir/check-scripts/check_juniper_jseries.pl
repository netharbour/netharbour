#!/usr/bin/perl

use strict;
use warnings;
use lib "check-scripts";
use Net::SNMP;
use Getopt::Std;

#-------------------------------------------------------------------------------
# Some defaults
#-------------------------------------------------------------------------------

my $default_temp_warning = 45;
my $default_temp_critical = 55;
my $default_mem_warning = 80;
my $default_mem_critical = 90;
my $default_cpu_warning = 80;
my $default_cpu_critical = 90;


#-------------------------------------------------------------------------------
# Check the usage
#-------------------------------------------------------------------------------

my ($hostname,$community,$owner, $temp_warning,$temp_critical,$mem_warning,$mem_critical,$cpu_warning,$cpu_critical);

check_usage();
sub check_usage {
	my $opt_string = 'H:C:t:T:m:M:h' ;
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

	if (defined $opt{t} && $opt{t} >= 0) {
		$temp_warning = $opt{t};
	} else {
		$temp_warning = $default_temp_warning;
	}

	if (defined $opt{T} && $opt{T} >= 0) {
		$temp_critical = $opt{T};
	} else {
		$temp_critical = $default_temp_critical;
	}

	if (defined $opt{m} && $opt{m} >= 0) {
		$mem_warning = $opt{m};
	} else {
		$mem_warning = $default_mem_warning;
	}

	if (defined $opt{M} && $opt{M} >= 0) {
		$mem_critical = $opt{M};
	} else {
		$mem_critical = $default_mem_critical;
	}

	if (defined $opt{l} && $opt{l} >= 0) {
		$cpu_warning = $opt{l};
	} else {
		$cpu_warning = $default_cpu_warning;
	}

	if (defined $opt{L} && $opt{L} >= 0) {
		$cpu_critical = $opt{L};
	} else {
		$cpu_critical = $default_cpu_critical;
	}
}



#===============================================================================
#      Initialization
# 	Set defaults
#===============================================================================

my $mem_oid_re0 = "1.3.6.1.4.1.2636.3.1.13.1.11.9.1.0.0";

my $temp_oid_re0 = "1.3.6.1.4.1.2636.3.1.13.1.7.9.1.0.0";

my $cpu_oid_re0 = "1.3.6.1.4.1.2636.3.1.13.1.8.9.1.0.0";

# start with memory
my $mem_status_str = "";
my $mem_perf_data = "";
my $mem_status_exit = 3;
my @mem_oids = ($mem_oid_re0);
my $snmp_result_mem = get_snmp_result($hostname,$community,\@mem_oids);
analyze_memory_usage($snmp_result_mem);

# Now Temperature
my $temp_status_str = "";
my $temp_perf_data = "";
my $temp_status_exit = 3;
my @temp_oid = ($temp_oid_re0);
my $snmp_result_temp = get_snmp_result($hostname,$community,\@temp_oid);
analyze_temperature($snmp_result_temp);

# Now CPU
my $cpu_status_str = "";
my $cpu_perf_data = "";
my $cpu_status_exit = 3;
my @cpu_oid = ($cpu_oid_re0);
my $snmp_result_cpu = get_snmp_result($hostname,$community,\@cpu_oid);
analyze_cpu($snmp_result_cpu);

# Build performance data string
my $perfdata = "| $temp_perf_data, $mem_perf_data, $cpu_perf_data";

# Now determine exit status

if (($temp_status_exit == 3) || ($mem_status_exit == 3) || ($cpu_status_exit == 3)) {
	print "Unknown: $cpu_status_str $mem_status_str $temp_status_str". $perfdata ."\n";
	exit 3;
}
elsif (($temp_status_exit == 2) || ($mem_status_exit == 2) || ($cpu_status_exit == 2)) {
	print "Critical: $cpu_status_str $mem_status_str $temp_status_str". $perfdata ."\n";
	exit 2;
}
elsif (($temp_status_exit == 1) || ($mem_status_exit == 1) || ($cpu_status_exit == 1)) {
	print "Warning: $cpu_status_str $mem_status_str $temp_status_str". $perfdata ."\n";
	exit 1;
}
elsif (($temp_status_exit == 0) && ($mem_status_exit == 0) && ($cpu_status_exit == 0)) {
	print "Ok: $cpu_status_str $mem_status_str $temp_status_str". $perfdata ."\n";
	exit 0;
} 
else {
	print "Unknown status $cpu_status_str $mem_status_str $temp_status_str". $perfdata ."\n";
	exit 3;
}


#-------------------------------------------------------
# Functions
#-------------------------------------------------------
sub analyze_temperature {
	my $snmp_result = shift;
	my $temp_status_str_0 ="";
	my $temp_status_exit_0 =3;
	my $temp_re0 = 0;

	# Now analyze
	if ((!defined($snmp_result->{$temp_oid_re0})) || ($snmp_result->{$temp_oid_re0} eq "noSuchInstance")) {
		$temp_status_str_0 = "Could not determine temperature usage for re0, $temp_oid_re0";
		$temp_status_exit_0 =  1;
	} else {
		$temp_re0 = $snmp_result->{$temp_oid_re0};

		if ($temp_re0 >= $temp_critical ) {
		 	$temp_status_exit_0 =  2;
		} elsif ($temp_re0 >= $temp_warning) {
		 	$temp_status_exit_0 =  1;
		} elsif (($temp_re0 < $temp_critical) && ($temp_re0 < $temp_warning)) {
		 	$temp_status_exit_0 =  0;
		} else {
			$temp_status_str_0 = "Unknown: Something went wrong....\n";
		 	$temp_status_exit_0 =  3;
		}
	}


	# Now evaluate results
	$temp_status_str = "temperature RE0:".$temp_re0 ."c".$temp_status_str_0;
	$temp_perf_data = "temperature_re0 = $temp_re0";
    $temp_status_exit =  $temp_status_exit_0;	

}

sub analyze_memory_usage {
	my $snmp_result = shift;
	my $mem_status_str_0 ="";
	my $mem_status_exit_0 =3;
	my $mem_re0 = 0;

	if ((!defined($snmp_result->{$mem_oid_re0})) || ($snmp_result->{$mem_oid_re0} eq "noSuchInstance")) {
		$mem_status_str_0 = " Could not determine memory usage for re0, $mem_oid_re0 ";
		$mem_status_exit_0 =  1;
	} else {
		$mem_re0 = $snmp_result->{$mem_oid_re0};

		if ($mem_re0 >= $mem_critical ) {
		 	$mem_status_exit_0 =  2;
		} elsif ($mem_re0 >= $mem_warning) {
		 	$mem_status_exit_0 =  1;
		} elsif (($mem_re0 < $mem_critical) && ($mem_re0 < $mem_warning)) {
		 	$mem_status_exit_0 =  0;
		} else {
			$mem_status_str_0 = " Unknown: Something went wrong.... ";
		 	$mem_status_exit_0 =  3;
		}
	}


	# Now evaluate results
	$mem_status_str = "Memory usage RE0:".$mem_re0 ."% ". $mem_status_str_0 ;
	$mem_perf_data = "memory_re0 = $mem_re0%";

    $mem_status_exit =  $mem_status_exit_0;
}

sub analyze_cpu {
	my $snmp_result = shift;
	my $cpu_status_str_0 ="";
	my $cpu_status_exit_0 =3;
	my $cpu_re0 = 0;

	if ((!defined($snmp_result->{$cpu_oid_re0})) || ($snmp_result->{$cpu_oid_re0} eq "noSuchInstance")) {
		$cpu_status_str_0 = " Could not determine cpu usage for re0, $cpu_oid_re0 ";
		$cpu_status_exit_0 =  1;
	} else {
		$cpu_re0 = $snmp_result->{$cpu_oid_re0};
		$cpu_status_exit_0 =  0;


                if ($cpu_re0 >= $cpu_critical ) {
                        $cpu_status_exit_0 =  2;
			$cpu_status_str_0 .= "Criticaly high load on RE0. ";
                } elsif ($cpu_re0 >= $cpu_warning) {
                        $cpu_status_exit_0 =  1;
			$cpu_status_str_0 .= "Warning high load on RE0. ";
                }
	}


	# Now evaluate results
	$cpu_status_str = "CPU usage RE0:".$cpu_re0 ."% ". $cpu_status_str_0;
	$cpu_perf_data = "cpu_re0 = $cpu_re0%";
    $cpu_status_exit =  $cpu_status_exit_0;	
}


sub print_help() {

	print "Juniper MX480 routing engine probe nagios plugin
	usage:   $0 -H hostname -C community [-m <memory_usage_warning>] [-M <memory_usage_critical>] [-t <temperature_warning>] [-T <temperature_critical>]  [-l <cpu_load_warning>] [-L <cpu_load_critical>]
	example: $0 -H cr1.vantx1 -C secret -m 75 -M 85 -t 50 -T 55 -l 75 -L 90

	[-h]    : Print this message

	[-H]   	:  FQDN or IP address of host to poll

	[-C] 	:  SNMP community.

	[-t]  	:  temperature threshold for warnings in celcius
		:  default is $default_temp_warning;

	[-T]  	:  temperature threshold for critical in celcius
		:  default is $default_temp_critical;

	[-m]  	:  memory utilization threshold for warnings in percentage (0 .. 100)
		:  default is $default_mem_warning;

	[-M]  	:  memory utilization threshold for critical in percentage (0 .. 100)
		:  default is $default_mem_critical;

	[-l]  	:  cpu load threshold for warnings in percentage (0 .. 100)
		:  default is $default_cpu_warning;

	[-L]  	:  cpu load threshold for critical in percentage (0 .. 100)
		:  default is $default_cpu_critical;


	Andree Toonk: andree.toonk\@bc.net  \n\n";
}

sub build_rtt_oid {
	my $t_owner = shift;
	my $t_test = shift;

	##################################################
	# Build the OID for min rrt number
	##################################################
	# base is 1.3.6.1.4.1.2636.3.50.1.3.1.3
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
	my $oid = "1.3.6.1.4.1.2636.3.50.1.3.1.3";

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
	my @snmpoids = @{(shift)};

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

	my $result = $snmp->get_request(
		-varbindlist => \@snmpoids
	);
	#my $result = $snmp->get_request($oid);

	if (!defined($result)) {
		my $answer = $snmp->error;
		$snmp->close;
		print ("UNKNOWN: SNMP error: $answer\n");
		exit 3;
	}
	return $result;
}




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



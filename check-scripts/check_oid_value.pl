#!/usr/bin/env perl

# Author: Craig Tomkow
# Date: March 13, 2019

use strict;
use warnings;

# core imports
use Getopt::Std;

# netharbour imports
use lib "utils.pm";
use lib "check-scripts";
use utils qw(%ERRORS);
use lib "perl/";
use CMDB_Config;

# config file
my %config = CMDB_Config::get_config("config/cmdb.conf");

# cli usage and options
$Getopt::Std::STANDARD_HELP_VERSION = 1;
my %opts;

getopts('H:C:O:V:', \%opts);

if (!defined $opts{H}) {
    print("Missing -H flag\n");
    exit($ERRORS{"UNKNOWN"});
} elsif (!defined $opts{C}) {
    print("Missing -C flag\n");
    exit($ERRORS{"UNKNOWN"});
} elsif (!defined $opts{O}) {
    print("Missing -O flag\n");
    exit($ERRORS{"UNKNOWN"});
} elsif (!defined $opts{V}) {
    print("Missing -V flag\n");
    exit($ERRORS{"UNKNOWN"});
}

# vars
my $host_addr      = $opts{H};
my $community      = $opts{C};
my $oid            = $opts{O};
my $expected_value = $opts{V};

my $snmpget = $config{'path_snmpget'};

# snmp get
my $raw_result = snmp_get($snmpget, $host_addr, $community, $oid);

# snmp error handling
if (index($raw_result, "No Such Object") != -1) {
    print("ERROR: $raw_result");
    exit($ERRORS{"UNKNOWN"});
} elsif (index($raw_result, "Timeout") != -1) {
    print("ERROR: $raw_result");
    exit($ERRORS{"UNKNOWN"});
}

my ($oid_and_datatype, $value) = split(/:/, $raw_result);

$value =~ s/^\s+|\s+$//g; # strip left and right whitespace

# check if value is equal to the defined truth
if ($value eq $expected_value) {
    print("OK: Value is '$value'. This is expected.\n");
    exit($ERRORS{"OK"});
} else {
    print("CRITICAL: Value is '$value'. This is different than what is expected.\n");
    exit($ERRORS{"CRITICAL"});
}

##### Subroutines

sub HELP_MESSAGE {
    my $usage = <<"    EOF";
    usage:   $0 -H <host> -C <community> -O <oid> -V 0
    example: $0 -H dev.domain -C public -O .1.3.6.1.4.1.15255.1.2.1.1.1.2.3 -V 0

    Required Flags
    [-H]          : Host address to check
    [-C]          : Host community string
    [-O]          : OID
    [-V]          : The value to match against

    Optional Flags
    [--help]      : Print this message
    [--version]   : Print script version

    Craig Tomkow: craig.tomkow\@bc.net
    EOF
    print($usage);
}

sub VERSION_MESSAGE {
    print("OID Value Check Script: 1.0\n");
}

sub snmp_get {
    my ($get, $device, $snmp_string, $OID) = @_;
    my $cli = "$get -v 2c -On -c $snmp_string $device $OID 2>&1"; # ensure to redirect stderr to stdout
    my $result = `$cli`;
    if (!defined $result) {
        print("ERROR: $get failed. Check SNMP path.");
        exit($ERRORS{"UNKNOWN"});
    } else {
        return $result;
    }
}

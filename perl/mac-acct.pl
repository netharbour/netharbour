#!/usr/bin/perl

#use strict;
#use warnings;
use DBI;
use Config::Simple;

my $config_file = "config/cmdb.conf";

###################### Get config ###########################
## This specifies where CMDB_Config.pm i
use lib "perl/";
use CMDB_Config;
my %config = CMDB_Config::get_config($config_file);
####################### Get config ###########################
#
#
####################### Connect To MySQL ########################
my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass}) 
        or die("Could not connect to Mysql!");


my $snmpwalk = $config{'path_snmpwalk'};
my $snmpget = $config{'path_snmpget'};
my $rrdupdate = $config{'path_rrdupdate'};
my $rrdtool = $config{'path_rrdtool'};
my $rrddir = $config{'path_rrddir'};



my $device = $ARGV[0];    # name
my $community = "xxxx";    # community

my $line;
my $cli;
my @results;
my %mactoip;
my %jnxMacHCInOctets;
my %jnxMacHCInFrames;
my %jnxMacHCOutOctets;
my %jnxMacHCOutFrames;
my %allmacs;

#fisrt get ARP table
$cli = "$snmpwalk -v 2c -Onq -c $community $device .1.3.6.1.2.1.4.22.1.2 ";
@results = `$cli`;
foreach $line (@results) {
        chomp $line;
	#.1.3.6.1.2.1.4.22.1.2.707.206.81.80.39 0:b0:c2:84:ab:0
	# .1.3.6.1.2.1.4.22.1.2.707.206.81.80.30 0:b:5f:6b:74:c0

        if ( $line =~ /\.1\.3\.6\.1\.2\.1\.4\.22\.1\.2\.(\d+)\.((\d+)\.(\d+)\.(\d+)\.(\d+))\s(\S+)/) {
		my $ip = $2;
		my $mac ='';
		my @mac_short = split(/:/, $7);
		foreach(@mac_short) {
			if ($_ =~ /^.$/) {
				$mac = $mac . "0$_:";
			} else {
				$mac = $mac . "$_:";
			}
		}
		chop($mac);	
		$mactoip{$mac} = $ip;
	} else {
		#print "no match for $_\n";
	}
}

# collect stats
$cli = "$snmpwalk -v 2c -On -c $community $device 1.3.6.1.4.1.2636.3.23 ";
#exit;
@results = `$cli`;
foreach $line (@results) {
        chomp $line;
     	#.1.3.6.1.4.1.2636.3.23.1.1.1.3.180.0.0.28.88.155.193.136 = Counter64: 11072

        if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.23\.1\.1\.1\.(\d+)\.(\d+)\.(\d+)\.(.+) = Counter64: (\d+)/) {
		my $type = $1;
		my $value = $5;
		my $mac = '';
		my @mac_decimal = split(/\./, $4);
		foreach(@mac_decimal) {
			$mac = $mac . sprintf("%02x", $_) .":";
		}
		# remove last :
		chop($mac);
		$allmacs{$mac} = $4;

		if ($type == 3) {
			$jnxMacHCInOctets{$mac} = $value;
		}
		elsif ($type == 4) {
			$jnxMacHCInFrames{$mac} = $value;
		}
		elsif ($type == 5) {
			$jnxMacHCOutOctets{$mac} = $value;
		}
		elsif ($type == 6) {
			$jnxMacHCOutFrames{$mac} = $value;
		}

		#print "type $type mac is $mac\n";
	}
}


while ( my $mac = each(%allmacs) ) {
	## RRD Stuff
        #first check if rrd file exist, if not create
	my $ip = $mactoip{$mac};
	#print "$mac == $ip\n";
	if (! $ip) {
		#print "could not find IP for mac $mac\n";
		next;
	}
	my $rrd_file = "MAC-ACCT_". $ip ."_device_$device.rrd";
        $rrd_file =~ s/([\$\#\@\\\/\s])/-/g;
        create_rrd_archive($rrd_file) if ! -e "$rrddir/$rrd_file";
	my $update = "N:$jnxMacHCInOctets{$mac}:$jnxMacHCOutOctets{$mac}:$jnxMacHCInFrames{$mac}:$jnxMacHCOutFrames{$mac}";
        $cli = "$rrdupdate \"$rrddir/$rrd_file\" $update";
        system($cli);
}


sub create_rrd_archive {
        # search and replace special chars
        my $interface_file_name = shift;
	my $variable_64bit = "18446744073709551616";
	my $max = $variable_64bit;
        #special chars replaced by a -, unix doesnt like / in filename
        #64 bits max is 18446744073709551616
        my $cli = " $rrdtool create \'$rrddir/$interface_file_name\' \\
      DS:INOCTETS:COUNTER:600:0:2.5000000000e+08 \\
      DS:OUTOCTETS:COUNTER:600:0:2.5000000000e+08 \\
      DS:INUCASTPKTS:COUNTER:600:0:U \\
      DS:OUTUCASTPKTS:COUNTER:600:0:U \\
      RRA:AVERAGE:0.5:1:600 \\
      RRA:AVERAGE:0.5:6:700 \\
      RRA:AVERAGE:0.5:24:775 \\
      RRA:AVERAGE:0.5:288:797 \\
      RRA:MAX:0.5:1:600 \\
      RRA:MAX:0.5:6:700 \\
      RRA:MAX:0.5:24:775 \\
      RRA:MAX:0.5:288:797 ";
      system `$cli`;
     # print "$cli\n";

}



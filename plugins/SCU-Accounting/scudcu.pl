#!/usr/bin/env perl

use strict;
use warnings;

# Import libs
use DBI;
use Getopt::Std;
use lib "perl/";
use CMDB_Config;

#------------------ Get config -------------------------------------------------
my $config_file = "config/cmdb.conf";
my %config = CMDB_Config::get_config($config_file);

#------------------ Connect to database ----------------------------------------
my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass})
    or die("Could not connect to Mysql!");

#------------------- Usage message ---------------------------------------------
my $usage = <<"EOF";
SCU DCU Collector script
usage:   $0 -d <device_id>
example: $0 -d 23

[-h]          : Print this message
[-d] 	      : device_id

Andree Toonk: andree.toonk\@bc.net
Craig Tomkow: craig.tomkow\@bc.net

EOF

#------------------- Check the usage -------------------------------------------
my $opt_string = 'h:d:';
my %opt;
getopts( "$opt_string", \%opt ) or die $usage;
die $usage if (defined $opt{h});
die $usage if (!defined $opt{d});
my $device_id = $opt{d},

#------------------- Set variables ---------------------------------------------
my $snmpwalk  = $config{'path_snmpwalk'};
my $snmpget   = $config{'path_snmpget'};
my $rrdupdate = $config{'path_rrdupdate'};
my $rrdtool   = $config{'path_rrdtool'};
my $rrddir    = $config{'path_rrddir'} . "/accounting";

my %device_info = get_device_info($device_id);
my $fqdn           = $device_info{device_fqdn};
my $community      = $device_info{snmp_ro};
my $device         = $device_info{name};

my $line;
my @results;
my %isp;  
my $cli;  
my %customer_names;
my %scu_profile;  
my %dest_name;
my %jnxScuStatsBytes;  
my %jnxDcuStatsBytes;

# If accounting directory doesn't exist, create
if (!-d $rrddir) {
    mkdir $rrddir or die "Error creating $rrddir directory for RRD files"
}

#------------------- Stat collection ------------------------------------------

# collect customer name  via jnxScuStatsClName
$cli = "$snmpwalk -v 2c -On -c $community $fqdn  .1.3.6.1.4.1.2636.3.16.1.1.1.6";
@results = `$cli`;
print "jnxScuStatsClName: collect customer name (.1.3.6.1.4.1.2636.3.16.1.1.1.6)\n";
foreach $line (@results) {
    chomp $line;

	#.1.3.6.1.4.1.2636.3.16.1.1.1.6.82.1.8.84.101.108.111.115.69.110.103 = STRING: "TelosEng"
    if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.16\.1\.1\.1\.6\.(\d+)\.(\d+)\.(\d+)\.(\d+)\.(.+) = STRING: \"(.+)\"/) {
        $customer_names{$5} = $6;
		$isp{$1} ='';
		#print "jnxScuStatsClName: $5 == $6\n";
    } else {
        #print "jnxScuStatsClName: no match for $line\n";
    }
}

# Now collect customer name via jnxDcuStatsClName
$cli = "$snmpwalk -v 2c -On -c $community $fqdn .1.3.6.1.4.1.2636.3.6.2.1.6";
@results = `$cli`;
print "jnxDcuStatsClName: collect customer name (.1.3.6.1.4.1.2636.3.6.2.1.6)\n";
foreach $line (@results) {
    chomp $line;

	#.1.3.6.1.4.1.2636.3.6.2.1.6.155.1.9.69.109.105.108.121.67.97.114.114 = STRING: "EmilyCarr"
    if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.6\.2\.1\.6\.(\d+)\.(\d+)\.(\d+)\.(.+) = STRING: \"(.+)\"/) {
        $isp{$1} ='';
        $customer_names{$4} = $5;
		#print "jnxDcuStatsClName: $4 == $5\n";
    } else {
        #print "jnxDcuStatsClName: no match for $line\n";
    }
}

#  collect ISP name by if index
print "ifAlias: collect ISP name by if index (.1.3.6.1.2.1.31.1.1.1.18.<ifIndex>)\n";
while (my ($isp_ifindex, $isp_name) = each(%isp) ) {
	$cli = "$snmpwalk -v 2c -Onqv -c $community $fqdn  .1.3.6.1.2.1.31.1.1.1.18.$isp_ifindex";
	my @result = `$cli`;
	$isp_name =  $result[0];
	chomp $isp_name;
    #print "ifAlias: $isp_name\n";
	$isp{$isp_ifindex}= $isp_name;
}

#jnxScuStatsBytes
#Example:
#.1.3.6.1.4.1.2636.3.16.1.1.1.5.155.1.5.66.67.78.69.84 = Counter64: 251064025499
#IFINDEX = 155
#1.
#3. Number of chars
#66.67.78.69.84 = BCNET
#The name is in ASCII. The other way to get that is using:
#.1.3.6.1.4.1.2636.3.16.1.1.1.6.IFINDEX.1.NUMBER_OF_CHARS."ASCII"
#.1.3.6.1.4.1.2636.3.16.1.1.1.6.155.1.5.66.67.78.69.84 = STRING: "BCNET"

print "jnxScuStatsBytes: collect scu stats (.1.3.6.1.4.1.2636.3.16.1.1.1.5)\n";
$cli = "$snmpwalk -v 2c -On -c $community $fqdn .1.3.6.1.4.1.2636.3.16.1.1.1.5";
@results = `$cli`;
foreach $line (@results) {
    chomp $line;

	#.1.3.6.1.4.1.2636.3.16.1.1.1.5.155.1.5.66.67.78.69.84 = Counter64: 251064025499
    if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.16\.1\.1\.1\.5\.(\d+)\.1\.(\d+)\.(.+) = Counter64:\s(\d+)/) {
		my $title = $customer_names{$3} . " -- " . $isp{$1};
		$scu_profile{$title} = $customer_names{$3};
		$jnxScuStatsBytes{$title} = $4; 
		$dest_name{$title} = $isp{$1};
		#print "jnxScuStatsBytes: $3 == $4\n";
    } else {
        #print "jnxScuStatsBytes: no match for $line\n";
    }
}

#jnxDcuStatsBytes
#Example:.1.3.6.1.4.1.2636.3.6.2.1.5.155.1.4.66.67.73.84 = Counter64: 51574082779709
#IFINDEX = 155
#1.
#3. Number of chars
#66.67.73.84 = BCIT

print "jnxDcuStatsBytes: collect dcu stats (.1.3.6.1.4.1.2636.3.6.2.1.5)\n";
$cli = "$snmpwalk -v 2c -On -c $community $fqdn .1.3.6.1.4.1.2636.3.6.2.1.5";
@results = `$cli`;
foreach $line (@results) {
    chomp $line;
    if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.6\.2\.1\.5\.(\d+)\.1\.(\d+)\.(.+) = Counter64:\s(\d+)/) {
        my $title = $customer_names{$3} . " -- " . $isp{$1};
		$dest_name{$title} = $isp{$1};
		$scu_profile{$title} = $customer_names{$3};
		$jnxDcuStatsBytes{$title} = $4; 
		#print "jnxDcuStatsBytes: $3 == $4\n";
    } else {
        #print "jnxDcuStatsBytes: no match for $line\n";
    }
}

print "Received all stats\n";
print "Now updating RRD files\n";

#------------------- RRD update ----------------------------------------------

while (my ($title, $value) = each(%jnxScuStatsBytes) ) {
	my $file_name = "deviceid".$device_id ."_"."$title.rrd";
	if (!-e "$rrddir/$file_name") {
		create_rrd_archive($file_name) if ! -e "$rrddir/$file_name";
		insert_db($scu_profile{$title},$dest_name{$title},$title,$file_name,$device_id);
	}

	if ((defined($value)) && (defined($jnxDcuStatsBytes{$title}))) {
        my $update = "N:$value:$jnxDcuStatsBytes{$title}";
		my $cli = "$rrdupdate \"$rrddir/$file_name\" $update";
        system($cli);
		update_db($file_name);
	}

	#print "SCU RRD update: $title = $value\n";
	#print "DCU RRD update: $title = $jnxDcuStatsBytes{$title}\n";
}

#------------------- Sub routines ---------------------------------------------

sub create_rrd_archive {
    # search and replace special chars
    my $file_name = shift;
    #special chars replaced by a -, unix doesnt like / in filename
    #64 bits max is 18446744073709551616
    #  105120 samples of 5 minutes  (365 days = 12(1hour) * 24(1day) *365(1year) )
    #  2920 samples of 6 hour ( 2 years of 1 hour samples. 4 * 365 * 2 = 2920)
    # 12500000000 = 100gbs=  12,5GBs

    my $cli = " $rrdtool create \'$rrddir/$file_name\' \\
    DS:INOCTETS:COUNTER:600:0:12500000000 \\
    DS:OUTOCTETS:COUNTER:600:0:12500000000 \\
    RRA:AVERAGE:0.5:1:105120 \\
    RRA:AVERAGE:0.5:72:2920 \\
    RRA:MAX:0.5:1:105120 \\
    RRA:MAX:0.5:72:2920 ";

    system `$cli`;
    #print "$cli\n";
}

sub get_device_info {
    my $dev_id = shift;
    my %info;
    my $query = "
        SELECT name, snmp_ro, device_fqdn
        FROM Devices where device_id = '$dev_id'
        ";
    my $sth = $dbh->prepare($query);
    $sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
    while (my @data = $sth->fetchrow_array()) {
        $info{name} = $data[0];
        $info{snmp_ro} = $data[1];
        $info{device_fqdn} = $data[2];
    }
    $sth->finish();
    return %info;
}

sub insert_db {
	my $scu_profile = shift;
	my $dest = shift;
	my $title = shift;
	my $file_name = shift;
	my $dev_id = shift;
        my $query = "
        INSERT INTO accounting_sources
        SET
		    device_id = '$dev_id',
		    title = '$title',
		    scu_profile = '$scu_profile',
		    destination = '$dest',
		    file = '$file_name',
		    last_update = NOW(),
		    created = NOW()";
        my $sth = $dbh->prepare($query);
        $sth->execute() or warn "Couldn't execute statement: " . $sth->errstr;
        $sth->finish();
}

sub update_db {
	my $file_name = shift;
        my $query = "
        UPDATE accounting_sources
        SET
		    last_update = NOW()
		WHERE
		    file = '$file_name'";
        my $sth = $dbh->prepare($query);
        $sth->execute() or warn "Couldn't execute statement: " . $sth->errstr;
        $sth->finish();
}

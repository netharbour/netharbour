#!/usr/bin/perl

use strict;
use warnings;
use DBI;

## Get config
use lib "./config";
use lib "./perl";
use Getopt::Std;
use File::Path qw(make_path);

# ------------------------------------------------
# Initialize NetHarbour environment variables 
#-------------------------------------------------
use CMDB_Config;
my %config = CMDB_Config::get_config("./config/cmdb.conf");


my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass}) or die("Could not connect to Mysql!");

my $snmpwalk = $config{'path_snmpwalk'};
my $snmpget = $config{'path_snmpget'};
my $rrdupdate = $config{'path_rrdupdate'};
my $rrdtool = $config{'path_rrdtool'};
my $rrddir = $config{'path_rrddir'};

# Eenable or disable debug print output
my $show_debug = 0;

#-------------------------------------------------------------------------------
#    Usage message
#-------------------------------------------------------------------------------

my $usage = <<"EOF";
=================================================
NetHarbour Juniper Firewall Counter RRD script
=================================================

usage:   $0 -d <device_id>
example: $0 -d 23

[-h]          : Print this message

[-d]          : device_id

[-v]          : Verbose output

Written Dec 2014, by Andree Toonk, Alvin Wong 
EOF

#-------------------------------------------------------------------------------
# Check the usage
#-------------------------------------------------------------------------------
my $opt_string = 'h:d:v';
my %opt;
getopts( "$opt_string", \%opt ) or die $usage;
die $usage if (defined $opt{h});
die $usage if (!defined $opt{d});

if (defined $opt{v}) {
	$show_debug = 1;
}

#-------------------------------------------------------------------------------
# Initialize variables and obtain values for this device 
#-------------------------------------------------------------------------------
my $device_id = $opt{d};

my $community = undef;
my $fqdn = undef;
my $device = undef;

my %device_info = get_device_info($device_id);

$fqdn = $device_info{device_fqdn};
$community = $device_info{snmp_ro};
$device = $device_info{name};

# Specify the sub-folder to put Firewall Counter RRD files to
my $fwcountersdir = 'fwcounters';
my $line;
my @results;
my %isp;  
my $cli;  
my %counternames;  
my %jnxFWBytes;  
my %jnxFWCounterPacketCount;  
my %fw_counters;  
my %in;  
my %out;  
my %inPacketCount;  
my %outPacketCount;  

print "SNMP querying deviceID $device_id : $fqdn...\n"; 

#-------------------------------------------------------------------------------
# Collect SNMP OIDs to counter name via jnxScuStatsClName
#-------------------------------------------------------------------------------

#$cli = "$snmpwalk -v 2c -On -c $community $device 1.3.6.1.4.1.2636.3.5.1.1.2 ";
#$cli = "$snmpwalk -v 2c -On -c $community $device 1.3.6.1.4.1.2636.3.5.2.1.7 ";
$cli = "$snmpwalk -v 2c -On -c $community $fqdn 1.3.6.1.4.1.2636.3.5.1.1.2 ";
$cli = "$snmpwalk -v 2c -On -c $community $fqdn 1.3.6.1.4.1.2636.3.5.2.1.7 ";

print "...Collecting SNMP OIDs to Counter Names...\n";
print "$cli\n" if $show_debug == 1;
@results = `$cli`;
foreach $line (@results) {
        chomp $line;
	print "$line\n" if $show_debug == 1;
#.1.3.6.1.4.1.2636.3.5.1.1.2.83.73.88.45.65.67.67.84.45.73.78.0.0.0.0.0.0.0.0.0.0.0.0.0.10.65.83.51.54.53.54.49.45.105.110.0.0.0.0.0.0.0.0.0.0.0.0.0 = STRING: "AS36561-in"


        #if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.5\.1\.1\.2\.(.+) = STRING: \"(.+)\"/) {
        if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.5\.2\.1\.7\.(.+) = STRING: \"(.+)\"/) {
               $counternames{$1} = $2; 
		print "Counter name $1 == $2\n" if $show_debug == 1;
        }
        else {
                print "no match for $line\n" if $show_debug == 1;
        }
}

#-------------------------------------------------------------------------------
# Now collect byte counts to counter SNMP OIDs via jnxFWBytes 
#-------------------------------------------------------------------------------

#$cli = "$snmpwalk -v 2c -On -c $community $device 1.3.6.1.4.1.2636.3.5.1.1.5 ";
#$cli = "$snmpwalk -v 2c -On -c $community $device 1.3.6.1.4.1.2636.3.5.2.1.5 ";
$cli = "$snmpwalk -v 2c -On -c $community $fqdn 1.3.6.1.4.1.2636.3.5.2.1.5 ";
print "...Collecting byte counts for every counter SNMP OID...\n";
print "$cli\n" if $show_debug == 1;
@results = `$cli`;
foreach $line (@results) {
        chomp $line;
	#print "$line\n";
	#.1.3.6.1.4.1.2636.3.5.1.1.5.83.73.88.45.65.67.67.84.45.73.78.0.0.0.0.0.0.0.0.0.0.0.0.0.10.65.83.51.54.53.54.49.45.105.110.0.0.0.0.0.0.0.0.0.0.0.0.0 = Counter64: 54023895
 
        #if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.5\.1\.1\.5\.(.+) = Counter64: (\d+)/) {
        if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.5\.2\.1\.5\.(.+) = Counter64: (\d+)/) {
               $jnxFWBytes{$1} = $2; 
		print "Counter name $1 has byte count == $2\n" if $show_debug == 1;
        }
        else {
                print "no match for $line\n" if $show_debug == 1;
        }
}

#-------------------------------------------------------------------------------
# Now collect byte counts to counter SNMP OIDs via jnxFWCounterPacketCount 
#-------------------------------------------------------------------------------

#$cli = "$snmpwalk -v 2c -On -c $community $device 1.3.6.1.4.1.2636.3.5.2.1.4 ";
$cli = "$snmpwalk -v 2c -On -c $community $fqdn 1.3.6.1.4.1.2636.3.5.2.1.4 ";
print "...Collecting packet counts for every counter SNMP OID...\n";
print "$cli\n" if $show_debug == 1;
@results = `$cli`;
foreach $line (@results) {
        chomp $line;
        #print "$line\n";
        #.1.3.6.1.4.1.2636.3.5.1.1.5.83.73.88.45.65.67.67.84.45.73.78.0.0.0.0.0.0.0.0.0.0.0.0.0.10.65.83.51.54.53.54.49.45.105.110.0.0.0.0.0.0.0.0.0.0.0.0.0 = Counter64: 54023895

        #if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.5\.1\.1\.5\.(.+) = Counter64: (\d+)/) {
        if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.5\.2\.1\.4\.(.+) = Counter64: (\d+)/) {
		$jnxFWCounterPacketCount{$1} = $2;
                print "Counter name $1 has packet count == $2\n" if $show_debug == 1;
        }
        else {
                print "no match for $line\n" if $show_debug == 1;
        }
}

#-------------------------------------------------------------------------------
# Debug print out all the firewall counter names to byte and packet count values
#-------------------------------------------------------------------------------

while ( my ($key, $value) = each(%counternames) ) {
	# $value is the name of the counter
	# $key is the OID of the counter
	# $jnxFWBytes{$key} are the byte values
	# $jnxFWCounterPacketCount{$key} are the packet count values

	print "Name $key = $value\n" if $show_debug == 1;

	print "Byte Value $key = $jnxFWBytes{$key}\n" if $show_debug == 1;
	print "$value === $jnxFWBytes{$key}\n" if $show_debug == 1;
        
	print "Packet Count Value $key = $jnxFWCounterPacketCount{$key}\n" if $show_debug == 1;
	print "$value === $jnxFWCounterPacketCount{$key}\n" if $show_debug == 1;

	if (!(exists $jnxFWBytes{$key})){
                print "Could not find counter for  $value\n" if $show_debug == 1;
                next;
        }

	# Pick which filter names to view
	my $specificCounter = undef;
	if ($value =~ /(.+)-([io])$/)  {
                #Record in and out counters here
                #ANYCAST-DNS-V6-xe-1/2/0.309-i
                $specificCounter = $1;
                my $dir = lc($2);
                $fw_counters{$specificCounter} = $specificCounter;
                if ($dir eq "i") {
                        $in{$specificCounter} = $key;
			$inPacketCount{$specificCounter} = $key;
                } elsif ($dir eq "o") {
                        $out{$specificCounter} = $key;
                        $outPacketCount{$specificCounter} = $key;
                }
        } else { 
		# Record non-interface specific counters (all else)
		#Record in and out counters here
		#ANYCAST-DISTR-WEB for example
		$specificCounter = $value;
                $fw_counters{$specificCounter} = $specificCounter;
		# Record counters to in direction by default as the counter found may be not direction specific.
                $in{$specificCounter} = $key;
                $inPacketCount{$specificCounter} = $key;
	}
}

# Now you have a collection of all the specific counters with the byte values

# ==================================================================
# Record data into RRD files
# ==================================================================
print "...Recording to RRD files...\n";

while ( my ($specificCounter, $value) = each(%fw_counters) ) {
	## RRD Stuff
        
	#Check if $rrddir/$fwcountersdir rrd folder exist, if not create
	if (!-d "$rrddir/$fwcountersdir") {
    	     	# directory called doesn't exists
		print "######## Directory $rrddir/$fwcountersdir doesn't create so creating it ###### \n" if $show_debug == 1;
		make_path("$rrddir/$fwcountersdir");
	}

	my $rrd_file = "fwcounter_deviceid" . $device_id . "_" . $specificCounter .".rrd";
        $rrd_file =~ s/([\$\#\@\\\/\s])/-/g;
	print "rrd_file $rrd_file\n" if $show_debug == 1;

	# If file doesn't exist we will create one first
	if (!-e "$rrddir/$fwcountersdir/$rrd_file") {
        	print "####### ATTEMPTING TO CREATE RRD FILE ###### \n" if $show_debug == 1; 
		create_rrd_archive($rrd_file,"1000000000");
	}

	# The "U" values referenced below for RRD means "no data"
        # Prep in direction counters and bytes values in variables
        my $inref;
        my $inbytes = "U";
        my $inpackets= "U";
        if (defined($in{$specificCounter})) {
                 $inref = $in{$specificCounter};
                 $inbytes = $jnxFWBytes{$inref} || "U";
		 $inpackets = $jnxFWCounterPacketCount{$inref} || "U";
        }

        # Prep out direction counters and bytes values in variables
        my $outref;
        my $outbytes = "U";
        my $outpackets = "U";
        if (defined($out{$specificCounter})) {
                 $outref = $out{$specificCounter};
                 $outbytes = $jnxFWBytes{$outref} || "U";
		 $outpackets = $jnxFWCounterPacketCount{$outref} || "U";
        }

        # Build up a CLI command for RRDupdate and send command to shell
        my $update = "N:".$inbytes.":".$outbytes.":::".$inpackets.":".$outpackets."::";
	$cli = "$rrdupdate \"$rrddir/$fwcountersdir/$rrd_file\" $update";
	print "$cli\n" if $show_debug == 1;
	system($cli);
}
print "Done!\n";

# ------------------------------------------------
# Subroutine to create an RRD File
#------------------------------------------------

sub create_rrd_archive {
        # search and replace special chars
        my $rrd_file_name = shift;
        my $ifspeed = shift;
        my $max;
        if ($ifspeed == 0) {
                $max = "U";
        } else {
                $max = ($ifspeed / 8);
        }
        #special chars replaced by a -, unix doesnt like / in filename
        #64 bits max is 18446744073709551616
        my $cli = " $rrdtool create \'$rrddir/$fwcountersdir/$rrd_file_name\' \\
	DS:INOCTETS:COUNTER:600:0:$max \\
	DS:OUTOCTETS:COUNTER:600:0:$max \\
	DS:INERRORS:COUNTER:600:0:$max \\
	DS:OUTERRORS:COUNTER:600:0:$max \\
	DS:INUCASTPKTS:COUNTER:600:0:$max \\
	DS:OUTUCASTPKTS:COUNTER:600:0:$max \\
	DS:INNUCASTPKTS:COUNTER:600:0:$max \\
	DS:OUTNUCASTPKTS:COUNTER:600:0:$max \\
	RRA:AVERAGE:0.5:1:600 \\
	RRA:AVERAGE:0.5:6:700 \\
	RRA:AVERAGE:0.5:24:775 \\
	RRA:AVERAGE:0.5:288:797 \\
	RRA:MAX:0.5:1:600 \\
	RRA:MAX:0.5:6:700 \\
	RRA:MAX:0.5:24:775 \\
	RRA:MAX:0.5:288:797 ";
        system `$cli`;
        print "$cli\n" if $show_debug == 1;
}

# ------------------------------------------------
# Subroutine to obtain device name, SNMP and FQDN 
#------------------------------------------------

sub get_device_info {
        my $device_id = shift;
        my %info;
        my $query = "select name, snmp_ro, device_fqdn from Devices where device_id = '$device_id' " ;
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

#!/usr/bin/env perl

use strict;
use warnings;

my $config_file = "config/cmdb.conf";

### Import libs
use DBI;
use Config::Simple;
use Getopt::Std;
use Socket;

###################### Get config ###########################
## This specifies where CMDB_Config.pm i
use lib "perl/";

use CMDB_Config;
my %config = CMDB_Config::get_config($config_file);
####################### Get config ###########################


####################### Connect To MySQL ########################
my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass})
    or die("Could not connect to Mysql!");


my $snmpwalk = $config{'path_snmpwalk'};
my $snmpget = $config{'path_snmpget'};
my $rrdupdate = $config{'path_rrdupdate'};
my $rrdtool = $config{'path_rrdtool'};
my $rrddir = $config{'path_rrddir'};


####################################################

#-------------------------------------------------------------------------------
#    Usage message
#-------------------------------------------------------------------------------

my $usage = <<"EOF";
MAC Accounting Collector script
usage:   $0 -d <device_id>
example: $0 -d 23

[-h]          : Print this message

[-d] 	      : device_id


Andree Toonk: andree.toonk\@bc.net
Craig Tomkow: craig.tomkow\@bc.net

EOF

#-------------------------------------------------------------------------------
# Check the usage
#-------------------------------------------------------------------------------
my $opt_string = 'h:d:';
my %opt;
getopts( "$opt_string", \%opt ) or die $usage;
die $usage if (defined $opt{h});
die $usage if (!defined $opt{d});

my $device_id = $opt{d};

my $community = undef;
my $fqdn = undef;
my $device = undef;

my %device_info = get_device_info($device_id);

$fqdn = $device_info{device_fqdn};
$community = $device_info{snmp_ro};
$device = $device_info{name};

my $line;
my $cli;
my @results;
my %mactoip;
my %jnxMacHCInOctets;
my %jnxMacHCInFrames;
my %jnxMacHCOutOctets;
my %jnxMacHCOutFrames;
my %allmacs;

#first get ARP table
$cli = "$snmpwalk -v 2c -Onq -c $community $fqdn .1.3.6.1.2.1.4.22.1.2";
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
$cli = "$snmpwalk -v 2c -On -c $community $fqdn 1.3.6.1.4.1.2636.3.23";
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
    my $rrd_file = "MAC-ACCT_". $ip ."_device_$fqdn.rrd";
    $rrd_file =~ s/([\$\#\@\\\/\s])/-/g;
    create_rrd_archive($rrd_file) if ! -e "$rrddir/$rrd_file";
    my $update = "N:$jnxMacHCInOctets{$mac}:$jnxMacHCOutOctets{$mac}:$jnxMacHCInFrames{$mac}:$jnxMacHCOutFrames{$mac}";
    $cli = "$rrdupdate \"$rrddir/$rrd_file\" $update";
    system($cli);

    # DB update
    my $record_exists = record_exists($device_id, $ip, $mac);
    my $resolved_ip = resolve_ip($ip);
    store_MACAccounting_info($record_exists, $device_id, $device, $ip, $mac, $resolved_ip);
}

############################ Below are all the sub routines #########################

sub create_rrd_archive {
    # search and replace special chars
    my $interface_file_name = shift;
    my $variable_64bit = "18446744073709551616";
    my $max = $variable_64bit;
    #special chars replaced by a -, unix doesnt like / in filename
    #64 bits max is 18446744073709551616
    my $cmd = " $rrdtool create \'$rrddir/$interface_file_name\' \\
      DS:INOCTETS:COUNTER:600:0:1.25000000000e+09 \\
      DS:OUTOCTETS:COUNTER:600:0:1.25000000000e+09 \\
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
    system `$cmd`;
    # print "$cli\n";

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

sub store_MACAccounting_info {
    my $record_exists = shift;
    my $dev_id        = shift;
    my $device_name   = shift;
    my $ip_address    = shift;
    my $mac_address   = shift;
    my $resolved_ip   = shift;
    my $query = "";
    if ($record_exists) {
        $query = "
            UPDATE plugin_MACAccounting_info
            SET
                device_name = '$device_name',
                ip_address = '$ip_address',
                mac_address = '$mac_address',
                resolved_ip = '$resolved_ip'
            WHERE device_id = '$dev_id'
                AND ip_address = '$ip_address'
                AND mac_address = '$mac_address'
        ";
    } else {
        $query = "
            INSERT INTO plugin_MACAccounting_info ( device_id, device_name, ip_address, mac_address, resolved_ip )
            VALUES ('$dev_id', '$device_name', '$ip_address', '$mac_address', '$resolved_ip')
        ";
    }
    my $sth = $dbh->prepare($query);
    $sth->execute();
    $sth->finish();
}

# returns 0 if entry cannot be found, and a 1 if found
sub record_exists {
    my $dev_id = shift;
    my $ip_address = shift;
    my $mac_address = shift;
    my $result = 0;
    my $query = "";
    $query = "
        SELECT device_id, ip_address, mac_address
        FROM plugin_MACAccounting_info
        WHERE device_id = '$dev_id'
            AND ip_address = '$ip_address'
            AND mac_address = '$mac_address'
    ";
    my $sth = $dbh->prepare($query);
    $sth->execute();
    if ($sth->rows == 0) {
        $result = 0;
    }
    else {
        $result = 1;
    }
    $sth->finish();
    return $result;
}

# TODO handle IPv6 addresses. Ensure backwards compatibility with perl Socket code/version
sub resolve_ip {
    my $ip_address = shift;
    my $name = gethostbyaddr(inet_aton($ip_address), AF_INET) or die "Can't resolve address";
    return $name;
}

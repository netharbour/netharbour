#!/usr/bin/env perl

use strict;
use warnings;

my $config_file = "config/cmdb.conf";

### Import libs
use DBI;
use Getopt::Std;
use Socket;
use LWP::Simple;
use XML::LibXML;

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
my $resolved_ip;
my $resolved_asn;

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

    # check plugin configuration settings
    my ($ip_resolve_ref, $whois_lookup_ref) = config_status($device_id);

    # resolve ip
    if ($ip_resolve_ref->{$device_id} == 1) {
        $resolved_ip = resolve_ip($ip);
    } else {
        $resolved_ip = "";
    }

    # asn whois lookup
    if ($whois_lookup_ref->{$device_id} == 1) {
        $resolved_asn = asn_whois_lookup(parse_fqdn_for_asn($resolved_ip));
    } else {
        $resolved_asn = "";
    }

    # DB update
    my $record_existence = record_exists($device_id, $ip, $mac);

    store_MACAccounting_info($record_existence, $device_id, $device, $ip, $mac, $resolved_ip, $resolved_asn);
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

# get the configuration options of the plugin
sub config_status {
    my $dev_id = shift;
    my %dev_ip_resolve;
    my %dev_whois_lookup;

    my $query = "
        SELECT
            plugin_MACAccounting_devices.ip_resolve,
            plugin_MACAccounting_devices.whois_lookup
        FROM plugin_MACAccounting_devices
        WHERE plugin_MACAccounting_devices.enabled = '1'
        AND plugin_MACAccounting_devices.device_id = '$dev_id'
        ORDER BY plugin_MACAccounting_devices.device_id
    ";

    my $sth = $dbh->prepare($query);
    $sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
    while (my @data = $sth->fetchrow_array()) {
        $dev_ip_resolve{$dev_id} = $data[0];
        $dev_whois_lookup{$dev_id} = $data[1];
    }
    $sth->finish();

    # return references to both hashes
    return (\%dev_ip_resolve, \%dev_whois_lookup);
}

sub store_MACAccounting_info {
    my $record_exists = shift;
    my $dev_id        = shift;
    my $device_name   = shift;
    my $ip_address    = shift;
    my $mac_address   = shift;
    my $resolved_ip   = shift;
    my $resolved_asn  = shift;
    my $query = "";
    if ($record_exists) {
        $query = "
            UPDATE plugin_MACAccounting_info
            SET
                device_name = '$device_name',
                ip_address = '$ip_address',
                mac_address = '$mac_address',
                resolved_ip = '$resolved_ip',
                org_name = '$resolved_asn'
            WHERE device_id = '$dev_id'
                AND ip_address = '$ip_address'
                AND mac_address = '$mac_address'
        ";
    } else {
        $query = "
            INSERT INTO plugin_MACAccounting_info ( device_id, device_name, ip_address, mac_address, resolved_ip, $resolved_asn )
            VALUES ('$dev_id', '$device_name', '$ip_address', '$mac_address', '$resolved_ip', '$resolved_asn')
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
# TODO fail quickly if lookup fails
# TODO use newer function than the legacy gethostbyaddr()
sub resolve_ip {
    my $ip_address = shift;
    my $name = gethostbyaddr(inet_aton($ip_address), AF_INET) or die "Can't resolve address";
    return $name;
}

# TODO fail quickly if HTTP lookup fails
sub asn_whois_lookup {
    my $asn = shift;
    my $xml_string = get("http://whois.arin.net/rest/asn/$asn");
    my $dom = XML::LibXML->load_xml(
        string => $xml_string,
    );
    my $dom_asn   = $dom->documentElement;
    my ($org_ref) = $dom_asn->getChildrenByTagName("orgRef");
    return($org_ref->getAttribute("name"));
}

sub parse_fqdn_for_asn {
    my $fqdn = shift;
    # split up fqdn by dot (.)
    my @names = split(/\./, $fqdn);
    # grab only numbers from first name
    my ($asn) = (shift @names) =~ /(\d+)/;
    return $asn;
}

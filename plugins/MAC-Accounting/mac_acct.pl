#!/usr/bin/env perl

use strict;
use warnings;

my $config_file = "config/cmdb.conf";
my $plugin_conf = "plugins/MAC-Accounting/config/MACAcct.conf";

### Import libs
use DBI;
use Getopt::Std;
# Socket module part of perl 5.10.1 (centos/rhel 6) does not have support for inet_ntop, inet_pton
# If on centos/rhel 6, upgrade Socket from CPAN
#     cpan ExtUtils::Constant
#     cpan Socket
# Otherwise use centos/rhel 7+ for perl 5.16
use Socket qw(:DEFAULT inet_ntop inet_pton getnameinfo);
use LWP::UserAgent;
use JSON;

############## Get config #################################

## This specifies where CMDB_Config.pm is
use lib "perl/";

use CMDB_Config;
my %config      = CMDB_Config::get_config($config_file);
my %plugin_conf = CMDB_Config::get_config($plugin_conf);

############### Get config ################################

############### Connect To MySQL ##########################

my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass})
    or die("Could not connect to Mysql!");

my $snmpwalk = $config{'path_snmpwalk'};
my $snmpget = $config{'path_snmpget'};
my $rrdupdate = $config{'path_rrdupdate'};
my $rrdtool = $config{'path_rrdtool'};
my $rrddir = $config{'path_rrddir'};

###########################################################

############### Set plugin config variables ###############

my $proxy_support     = $plugin_conf{'proxy_support'};
my $proxy_address     = $plugin_conf{'proxy_address'};
my $org_name_override = $plugin_conf{'org_name_override'};

###########################################################


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
my $hostname;
my $orgname;

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
    my ($ip_resolve_ref, $asn_resolve_ref) = config_status($device_id);

    # resolve ip
    if ($ip_resolve_ref->{$device_id} == 1) {
        $hostname = ip_resolve($ip);
    } else {
        $hostname = "";
    }

    # API call to peeringDB
    my $ip_details_ref = get_ip_details($ip, $proxy_support, $proxy_address);

    # pull ASN from JSON, then resolve to orgname
    if ($asn_resolve_ref->{$device_id} == 1) {
        $orgname = get_orgname_from_asn(get_asn_from_json($ip_details_ref), $proxy_support, $proxy_address, $org_name_override, $ip, \%plugin_conf);
    } else {
        $orgname = "";
    }

    # pull ipv6_address from JSON
    my $ipv6_address = get_ipv6_from_json($ip_details_ref);

    # DB update
    my $record_state = record_exists($device_id, $ip, $mac);
    store_MACAccounting_info($record_state, $device_id, $device, $ip, $mac, $hostname, $orgname, $ipv6_address);
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
    my %dev_asn_resolve;

    my $query = "
        SELECT
            plugin_MACAccounting_devices.ip_resolve,
            plugin_MACAccounting_devices.asn_resolve
        FROM plugin_MACAccounting_devices
        WHERE plugin_MACAccounting_devices.enabled = '1'
        AND plugin_MACAccounting_devices.device_id = '$dev_id'
        ORDER BY plugin_MACAccounting_devices.device_id
    ";

    my $sth = $dbh->prepare($query);
    $sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
    while (my @data = $sth->fetchrow_array()) {
        $dev_ip_resolve{$dev_id} = $data[0];
        $dev_asn_resolve{$dev_id} = $data[1];
    }
    $sth->finish();

    # return references to both hashes
    return (\%dev_ip_resolve, \%dev_asn_resolve);
}

sub store_MACAccounting_info {
    my $record_exists = shift;
    my $dev_id        = shift;
    my $device_name   = shift;
    my $ip_address    = shift;
    my $mac_address   = shift;
    my $resolved_ip   = shift;
    my $resolved_asn  = shift;
    my $ipv6_address  = shift // "";
    my $query = "";

    if ($record_exists) {
        $query = "
            UPDATE plugin_MACAccounting_info
            SET
                device_name = '$device_name',
                ip_address = '$ip_address',
                mac_address = '$mac_address',
                resolved_ip = '$resolved_ip',
                org_name = '$resolved_asn',
                ipv6_address = '$ipv6_address',
                last_seen = NOW(),
                active = 1
            WHERE device_id = '$dev_id'
                AND ip_address = '$ip_address'
                AND mac_address = '$mac_address'
        ";
    } else {
        $query = "
            INSERT INTO plugin_MACAccounting_info ( device_id, device_name, ip_address, mac_address, resolved_ip, org_name, ipv6_address )
            VALUES ('$dev_id', '$device_name', '$ip_address', '$mac_address', '$resolved_ip', '$resolved_asn', '$ipv6_address')
        ";
    }

    my $sth = $dbh->prepare($query);
    $sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
    $sth->finish();
}

# returns 0 if entry cannot be found, and a 1 if found
sub record_exists {
    my $dev_id      = shift;
    my $ip_address  = shift;
    my $mac_address = shift;
    my $result      = 0;
    my $query       = "";
    $query = "
        SELECT device_id, ip_address, mac_address
        FROM plugin_MACAccounting_info
        WHERE device_id = '$dev_id'
            AND ip_address = '$ip_address'
            AND mac_address = '$mac_address'
    ";
    my $sth = $dbh->prepare($query);
    $sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
    if ($sth->rows == 0) {
        $result = 0;
    }
    else {
        $result = 1;
    }
    $sth->finish();
    return $result;
}

sub ip_resolve {
    my $ip_addr = shift;
    my $sock_addr;

    # if ipv6
    if ($ip_addr =~ /:/) {
        $sock_addr = sockaddr_in6(0, inet_pton(AF_INET6, $ip_addr));
    } else {
        $sock_addr = sockaddr_in(0, inet_pton(AF_INET, $ip_addr));
    }

    my ($err, $hostname, $servicename) = getnameinfo($sock_addr);

    if ($err) {
        return "";
    } else {
        return $hostname;
    }
}

# make API call and return reference to JSON details
sub get_ip_details {
    my $ip_addr      = shift // "";
    my $enable_proxy = shift // 0;
    my $proxy_dest   = shift // "";

    my $ua = LWP::UserAgent->new;

    if ($enable_proxy) {
        $ua->proxy(['http', 'https'], $proxy_dest);
    }

    my $response;

    # ipv6
    if ($ip_addr =~ /:/) {
        $response = $ua->get("https://peeringdb.com/api/netixlan?ipaddr6__in=$ip_addr");
    } else {
        $response = $ua->get("https://peeringdb.com/api/netixlan?ipaddr4__in=$ip_addr");
    }

    if (!$response->is_success) {
        return "";
    }

    my $content = $response->content;
    my $content_hash_ref = from_json($content);
    return $content_hash_ref;
}

sub get_orgname_from_asn {
    my $asn               = shift // 0;
    my $enable_proxy      = shift // 0;
    my $proxy_dest        = shift // "";

    my $org_name_override  = shift // 0;
    my $ip                = shift // "";
    my $plugin_conf_ref   = shift // ();

    my $ua = LWP::UserAgent->new;

    if ($enable_proxy) {
        $ua->proxy(['http', 'https'], $proxy_dest);
    }

    # description override from MACAcct.conf
    if ($org_name_override && $plugin_conf_ref->{$ip}) {
        return($plugin_conf_ref->{$ip})
    }

    my $response = $ua->get("https://peeringdb.com/api/net?asn=$asn");

    if (!$response->is_success) {
        return "";
    };

    my $content = $response->content;
    my $content_hash_ref =from_json($content);

    return($content_hash_ref->{'data'}[0]{'name'});
}

sub get_asn_from_json {
    my $content_hash_ref = shift;
    return($content_hash_ref->{'data'}[0]{'asn'});
}

sub get_ipv6_from_json {
    my $content_hash_ref = shift;
    my $addr = $content_hash_ref->{'data'}[0]{'ipaddr6'};

    if (!$addr) {
        return("None");
    } else {
        return($addr);
    }
}

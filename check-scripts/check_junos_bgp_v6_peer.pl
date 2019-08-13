#!/usr/bin/env perl

use strict;
use warnings;
use lib "check-scripts";

use Net::IP;
use Net::SNMP;
use NetAddr::IP::Util;
use Data::Validate::Domain;
use Data::Validate::IP;
use lib "/usr/lib/nagios/plugins";
use utils qw($TIMEOUT %ERRORS);
use Getopt::Long;

my $hostname;

my $community = 'public';

my $ip_local;

my $ip_peer;

my $domain;

my $base_junos_bgp_oid = '1.3.6.1.4.1.2636.5.1.1.2.1.1.1.2.0.2.';

my $query_oid;

my $bgp_status = -1;

my $exit_status = 'UNKNOWN';

my $output_string;

sub IPv6_To_OID
	{
	my $ip_str = $_[0];
	my $ip_oid;
	my $i;
	foreach my $c (split(':',$ip_str))
		{
		$ip_oid .= sprintf("%s.", hex(substr($c, 0, 2)));
		$i++;
		if($i<15)
			{
			$ip_oid .= sprintf("%s.", hex(substr($c, 2, 2)));
			$i++;
			}
		else
			{
			$ip_oid .= sprintf("%s", hex(substr($c, 2, 2)));
			}
		}
	return $ip_oid;
	}

sub usage() {
        print "Usage:\n";
        print "		$0 -H <HOSTNAME> [-C <community>] -l <local_ip> -p <peer_ip>\n";
}

use Getopt::Long;
&Getopt::Long::config('bundling');

GetOptions(
        "C=s" => \$community,
        "H=s" => \$hostname,
        "l=s" => \$ip_local,
        "p=s" => \$ip_peer,
);
if (!is_domain($hostname)&&!is_ipv4($hostname)&&!is_ipv6($hostname))
	{
	usage();
	exit $ERRORS{'UNKNOWN'};
	}
if (!defined($ip_local))
	{
	usage();
	exit $ERRORS{'UNKNOWN'};
	}
if (!defined($ip_peer)) 
	{
	usage();
	exit $ERRORS{'UNKNOWN'};
	}


# Dont hang Nagios
$SIG{'ALRM'} = sub {
        print ("UNKNOWN: No snmp response from $hostname\n");
        exit $ERRORS{"UNKNOWN"};
};
alarm($TIMEOUT);

$query_oid = $base_junos_bgp_oid;
$query_oid .= IPv6_To_OID(Net::IP::ip_expand_address($ip_local, 6));
$query_oid .= ".2.";
$query_oid .= IPv6_To_OID(Net::IP::ip_expand_address($ip_peer, 6));

if(is_ipv4($hostname))
	{
	$domain = 'udp4';
	}
if(is_ipv6($hostname))
	{
	$domain = 'udp6';
	}
if(is_domain($hostname))
	{
	# need to test if resolved IP is v4 or v6!
	$domain = 'udp4';
	}

my ($session, $error) = Net::SNMP->session(
			-hostname      => shift || $hostname,
			-community	=> shift || $community,
			-domain		=> shift || $domain,
);



if (!defined $session) {
      printf "ERROR: %s.\n", $error;
      exit $ERRORS{'UNKNOWN'};
   }

   my $result = $session->get_request(-varbindlist => [ $query_oid ],);

   if (!defined $result) {
      printf "ERROR: %s.\n", $session->error();
      $session->close();
      exit $ERRORS{'UNKNOWN'};
   }

$bgp_status = $result->{$query_oid};
$session->close();

my %jnxBgpM2PeerStatus = 
	(
	-1 => 'unknown(-1)',
	1 => 'idle(1)',
	2 => 'connect(2)',
	3 => 'active(3)',
	4 => 'opensent(4)',
	5 => 'openconfirm(5)',
	6 => 'established(6)'
	);

if ($bgp_status == 6)
	{
	$exit_status = 'OK';
	}
else
	{
	$exit_status = 'CRITICAL';
	}

$output_string = "Local $ip_local to remote peer $ip_peer state is $jnxBgpM2PeerStatus{$bgp_status}";

print "$exit_status - $output_string\n";

exit $ERRORS{$exit_status};


#!/usr/bin/env perl
#
#  check_if_threshold.pl
#  This script is used to check if interfaces are close to reaching threshold values
#  Alvin Wong, Feb 2014
#
#  E.g. To check for more than 500Mbps on cr2.vncv1 xe-4/1/0 interface
#  check-scripts/check_if_threshold.pl -H cr2.vncv1 -i xe-4/1/0 -t 500000000
#
#  E.g. To check for more than 3Gbps on cr2.vncv1 xe-4/1/0 interface
#  check-scripts/check_if_threshold.pl -H cr2.vncv1 -i xe-4/1/0 -t 3000000000
#
#  check_if_threshold.pl -H $HOSTADDRESS$ -i $KEY1$ -t $KEY2$

#############################################################
### Import libraries
#############################################################

use strict;
use warnings;
use lib "check-scripts";
use DBI;
use Getopt::Std;
use utils qw($TIMEOUT %ERRORS);
use Getopt::Long;
&Getopt::Long::config('bundling');

#############################################################
###  Declare variables
#############################################################

# Alvin Debug Print output
my $debugPrint = "Processing check_if_threshold.pl";
my $config_file = "config/cmdb.conf";

my $param_hostname;
my $param_if_name;
my $param_threshold_value;

# Boolean whether thresholds met
my $blThresholdMet = 0;

my $state = 'UNKNOWN';
my $content = "";
my $answer = "";

#############################################################
###  Obtain arguments passed
#############################################################

sub usage() {
        print "Usage:\n";
        print "		$0 -H <param_hostname> -i <param_if_name> -t <param_threshold_value in bps>\n";
}

# Needs hostname, Interface, and threshold value in bps
GetOptions(
        "H=s" => \$param_hostname,
        "i=s" => \$param_if_name,
        "t=s" => \$param_threshold_value,
);
if (!defined($param_hostname))
{
	usage();
	exit $ERRORS{$state};
}
if (!defined($param_if_name))
{
	usage();
	exit $ERRORS{$state};
}
if (!defined($param_threshold_value)) 
{
	usage();
	exit $ERRORS{$state};
}

my $reportContent = "CMDB Script: check_if_threshold.pl.  Checking for traffic over ". format_number($param_threshold_value,2) . "bps.\n\n";

###################### Get config ###########################
# This specifies where CMDB_Config.pm is
use lib "perl/";

use CMDB_Config;
my %config = CMDB_Config::get_config($config_file);
###################### Get config ###########################

###################### Connect To MySQL ########################
my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass}) 
        or die("Could not connect to Mysql!");
###################### Connect To MySQL ########################

###################### Get Email info ########################

# Temporarily use my own email to check if this script works.
#my $email_to = 'alvin.wong@bc.net';
#my $email_to = 'nmc@bc.net';
my $email_to = $config{email_to};
my $email_from = $config{email_from};

if ((!defined($email_to)) || ($email_to eq '')) {
	print "ERROR: Could not determine email address to send to!\n";
	print "ERROR: Please define email_to in config/cmdb.conf\n";
	print "ERROR: Script terminated\n";
	exit $ERRORS{$state};
}

if ((!defined($email_from)) || ($email_from eq '')) {
	print "ERROR: Could not determin from email address!\n";
	print "ERROR: Please define email_from in config/cmdb.conf\n";
	print "ERROR: Script terminated\n";
	exit $ERRORS{$state};
}

################################################################################
#   MAIN
################################################################################

# This is where the email content will go
my $email_content = "";

###################### CHECK BPS THRESHOLD #####################

# Check if the interface inbits and outbits meet threshold and 
# if the device is in list of devices to poll
my $query = "SELECT inbits, outbits, inerrors, outerrors, disc_interface_speed, interface_name, interface_descr, interface_alias, interface_id,interface_device,last_threshold_alert,last_seen
			FROM interfaces
			WHERE interface_name = '$param_if_name'
			AND interface_device IN
				(SELECT device_id from Devices 
					WHERE name ='$param_hostname' 
						OR device_fqdn ='$param_hostname' 
						AND device_id IN (SELECT device_id FROM plugin_SNMPPoller_devices WHERE enabled = '1'))
			AND (disc_interface_speed != 0)
			AND ((inbits >= $param_threshold_value) 
				OR (outbits >= $param_threshold_value))";

my $sth = $dbh->prepare($query);
$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;

# If true, it is over the threshold
if ($sth->rows > 0)
{
	$blThresholdMet = 1;
	
	$reportContent .= 	"The following thresholds were exceeded:\n\n" ;
	$reportContent .= "------------------------------------------------------------\n";
}

while (my @data = $sth->fetchrow_array()) {
	my %interface;
	$interface{'inbits'} = $data[0];
	$interface{'outbits'} = $data[1];
	$interface{'inerrors'} = $data[2];
	$interface{'outerrors'} = $data[3];
	$interface{'disc_interface_speed'} = $data[4];
	$interface{'interface_name'} = $data[5];
	$interface{'interface_descr'} = $data[6];
	$interface{'interface_alias'} = $data[7];
	$interface{'interface_id'} = $data[8];
	$interface{'interface_device'} = $data[9];
	$interface{'last_threshold_alert'} = $data[10];
	$interface{'last_seen'} = $data[11];

	# RECORD all the interfaces found for later email report and screen report
	
	# SCREEN REPORT
	my %device_info = get_device_info($interface{'interface_device'});

	$reportContent .= "$device_info{name} -- $device_info{device_fqdn} \n\n";	
	$reportContent .= "$interface{'interface_name'} -- Capacity: ".format_number($interface{'disc_interface_speed'})."b/s  -- $interface{'interface_alias'} \n";
	$reportContent .= "\tbps In:  ". format_number($interface{'inbits'},2)."bps\n";
	$reportContent .= "\tbps Out: ".format_number($interface{'outbits'},2)."bps\n\n";

	if(check_send_alert($interface{'interface_id'}) == 1)
	{
		send_mail($reportContent);
		# Update last alert timestamp in database
		update_last_threshold_alert($interface{'interface_id'});
	}
}

if ($blThresholdMet == 1)
{
	$state = "WARNING";
}
# Content is empty, that means threshold not met so we are ok
else
{
	$state = "OK";
}

print $state;
# SHOW What is happening
# Alvin Debug
#print " - " . $reportContent;

exit $ERRORS{$state};

################################################################################
#    Sub Routines 
################################################################################

sub update_last_threshold_alert {
	my $interface_id = shift;
	my $query = "UPDATE interfaces 
		SET last_threshold_alert = NOW() 
		WHERE interface_id = '$interface_id'" ;
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
}

sub check_send_alert {
	# Check to see if last_seen is within 10 minutes ago
	# Check to see if last threshold alert is beyond 2 hours ago or never sent
	# If either is true, then return true -- send email alert
	# else return false - don't send email alert
	
	my $interface_id = shift;
	my $blSendAlert = 0;
	# Check to see if last_seen is within 10 minutes ago
	# Check to see if last threshold alert is beyond 2 hours ago or never sent
	my $query = "SELECT interface_name,last_threshold_alert,last_seen
					FROM interfaces
					WHERE (last_seen  >= NOW() - INTERVAL 10 MINUTE)
					AND ((last_threshold_alert  <= NOW() - INTERVAL 120 MINUTE) OR (last_threshold_alert IS NULL)) 
					AND interface_id = '$interface_id'" ;
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	# Found
	if($sth->rows > 0)
	{
		$blSendAlert =1;
	}
	return $blSendAlert;
}

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

sub send_mail {
        my $mailcontent = shift;
        if ($mailcontent ne '') {
                open (MAIL, "|/usr/sbin/sendmail -oi -t");
                print MAIL "From: $email_from\n"; ## don't forget to escape the @
                print MAIL "To: $email_to\n";
                print MAIL "Subject: Thresholds exceeded\n";
                print MAIL "\n";
                print MAIL "$mailcontent\n\n\n" ;
                close (MAIL);
        
        }
}

sub format_number {
        my ($number,$digits) = @_;
	if (!defined($digits)) {
		$digits = 0;
	}
        my ($num, $divedby,$legend);
        if ($number >= 1 * 10 ** 12) {
                $legend = "T";
                $divedby = 10 ** 12;
        } elsif ($number >= 1 * 10 ** 9) {
                $legend = "G";
                $divedby = 10 ** 9;
        } elsif ($number >= 1 * 10 ** 6) {
                $divedby = 10 ** 6;
                $legend = "M";
        } elsif ($number >= 1 * 10 ** 3) {
                $divedby = 10 ** 3;
                $legend =  "K";
        } else {
                $legend = "";
                $divedby = 10 ** 0;
        }
	my $format_num = "OOPS";
	$format_num = sprintf("%.".$digits."f",($number / $divedby));
        #my @result = ($format_num,$legend);
        #return @result;
	return $format_num . "$legend";
}
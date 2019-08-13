#!/usr/bin/env perl

use strict;
use warnings;

# Defined threshold levels
# Send mail if more than xx errors were detected:
my $error_threshold_level = "10";

# Send mail if more than xx percent (%) of interface capacity was utilized
my $bps_threshold_level = "90";


my $config_file = "config/cmdb.conf";

### Import libs
use DBI;
use Getopt::Std;

###################### Get config ###########################
# This specifies where CMDB_Config.pm is
use lib "perl/";

use CMDB_Config;
my %config = CMDB_Config::get_config($config_file);
#use Data::Dumper; print Dumper( \%config );
###################### Get config ###########################


###################### Connect To MySQL ########################
my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass}) 
        or die("Could not connect to Mysql!");
###################### Connect To MySQL ########################

###################### Get Email info ########################
my $email_to = $config{email_to};
my $email_from = $config{email_from};

if ((!defined($email_to)) || ($email_to eq '')) {
	print "ERROR: Could not determin to email address!\n";
	print "ERROR: Please define email_to in config/cmdb.conf\n";
	print "ERROR: Script terminated\n";
	exit 1;
}

if ((!defined($email_from)) || ($email_from eq '')) {
	print "ERROR: Could not determin from email address!\n";
	print "ERROR: Please define email_from in config/cmdb.conf\n";
	print "ERROR: Script terminated\n";
	exit 1;
}

###################### Get Email info ########################

# This is where the email content will go
my $email_content = "";

# First get list of all devices to check
my %devices_to_check = get_threshold_devices();

# Loop through
while ( my ($key, $value) = each(%devices_to_check) ) {
	#print "Checking $key => $value\n";
	my $content = check_thresholds_for_device($key);
	if ($content ne '') {
		my %device_info = get_device_info($key);
		$email_content .= "------------------------------------------------------------\n";
		$email_content .= "$device_info{name} -- $device_info{device_fqdn} \n\n";
		$email_content .= $content ."\n";
	}
}

if ($email_content ne '') {
	$email_content = "The following thresholds were exceeded:\n\n" . $email_content;
	send_mail($email_content);
}


#-------------------------------------------------------------------------------
#    Functions 
#-------------------------------------------------------------------------------

sub check_thresholds_for_device {
	my $device_id = shift;
	my $content = "";
	my $query = "select inbits, outbits, 
		inerrors, outerrors, 
		inunicastpackets, outunicastpackets,
		innonunicastpackets, outnonunicastpackets,
		disc_interface_speed, interface_name, interface_descr, 
		interface_alias, interface_id
	FROM interfaces
	WHERE interface_device = '$device_id'
	AND last_seen  >= NOW() - INTERVAL 10 MINUTE 
	AND (last_threshold_alert  <= NOW() - INTERVAL 120 MINUTE OR last_threshold_alert IS NULL)
	ORDER by interface_name ";
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	while (my @data = $sth->fetchrow_array()) {
		my %interface;
		$interface{'inbits'} = $data[0];
		$interface{'outbits'} = $data[1];
		$interface{'inerrors'} = $data[2];
		$interface{'outerrors'} = $data[3];
		$interface{'inunicastpackets'} = $data[4];
		$interface{'outunicastpackets'} = $data[5];
		$interface{'innonunicastpackets'} = $data[6];
		$interface{'outnonunicastpackets'} = $data[7];
		$interface{'disc_interface_speed'} = $data[8];
		$interface{'interface_name'} = $data[9];
		$interface{'interface_alias'} = $data[11];
		$interface{'interface_id'} = $data[12];
		
		# Now that we have all info for this port check it exceed threshold
		# Check bps threshold
		my $tmp_content = '';
        if ($interface{'interface_name'} eq "vlan.114") {
            next;
        }
		$tmp_content .= check_bps_threshold(\%interface);
		$tmp_content .=  check_error_threshold(\%interface);
		if ($tmp_content ne '') {
			# Update database
			update_last_threshold_alert($interface{'interface_id'});

			$content .= "$interface{'interface_name'} -- Capacity: ".format_number($interface{'disc_interface_speed'})."b/s  -- $interface{'interface_alias'} \n";
			$content .= $tmp_content;
		}
		
	}
	return $content;
}

sub update_last_threshold_alert {
	my $interface_id = shift;
	my $query = "UPDATE interfaces 
		SET last_threshold_alert = NOW() 
		WHERE interface_id = '$interface_id'" ;
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
}

sub check_error_threshold {
	my ($interface) = @_;
	my $threshold_level = $error_threshold_level;
	my $content = "";
	my $match = undef;

	#calculate threshold

	my $in_threshold =  $$interface{'inerrors'};
	my $out_threshold =  $$interface{'outerrors'};
	if ($in_threshold >= $threshold_level) {
		$match = 1;
	}
	if ($out_threshold >= $threshold_level) {
		$match = 1;
	}
	if (defined($match)) {
		$content .= "\tErrors In:  ". format_number($$interface{'inerrors'},2)."/sec \n";
		$content .= "\tErrors Out: ".format_number($$interface{'outerrors'},2)."/sec \n";
	}
	return $content;
}


sub check_bps_threshold {
	my ($interface) = @_;
	my $threshold_level = $bps_threshold_level;
	my $content = "";
	my $match = undef;

	#calculate threshold
	if ($$interface{'disc_interface_speed'} eq 0) {
		return $content;
	}

	# Hack for Juniper logical interfaces. They can actually be higher the ifspeed
	# Set it to 1.5 times for now.
	my $ifspeed =  $$interface{'disc_interface_speed'};
	if ($$interface{'interface_name'} =~ /lt-\d+\/\d+\/\d+$/) {
		$ifspeed = $ifspeed * 1.5;
	}

	my $in_threshold =  sprintf("%.0f",($$interface{'inbits'} / $ifspeed) * 100);
	my $out_threshold =  sprintf("%.0f",($$interface{'outbits'} / $ifspeed) * 100);
	if ($in_threshold >= $threshold_level) {
		$match = 1;
	}
	if ($out_threshold >= $threshold_level) {
		$match = 1;
	}
	if (defined($match)) {
		$content .= "\tbps In:  ". format_number($$interface{'inbits'},2)." ($in_threshold%)\n";
		$content .= "\tbps Out: ".format_number($$interface{'outbits'},2)." ($out_threshold%)\n";
	}
	return $content;
}


sub get_threshold_devices {
	my %devices;
	my $query = "select distinct device_id from plugin_SNMPPoller_devices
		WHERE enabled = '1'";
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	while (my @data = $sth->fetchrow_array()) {
		$devices{$data[0]} = $data[0];
	}
	return %devices;
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

sub get_port_speeds {
        my $device_id = shift;
        my %ports;
        my $query = "select interface_id, disc_interface_speed from interfaces where interface_device = '$device_id' " ;
        my $sth = $dbh->prepare($query);
        $sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
        while (my @data = $sth->fetchrow_array()) {
                $ports{$data[0]} = $data[1];
        }
        $sth->finish();
        return %ports;
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


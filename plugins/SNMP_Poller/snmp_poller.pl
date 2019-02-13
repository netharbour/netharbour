#!/usr/bin/env perl

use strict;
use warnings;

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


my $snmpwalk = $config{'path_snmpwalk'};
my $snmpget = $config{'path_snmpget'};
my $rrdupdate = $config{'path_rrdupdate'};
my $rrdtool = $config{'path_rrdtool'};
my $rrddir = $config{'path_rrddir'};
my $mailcontent ='';


####################################################
# Jus a simple hash for netmask to cidr format:
my %netmask = (
	'0.0.0.0' => '0', '128.0.0.0' => '1', '192.0.0.0' => '2', '224.0.0.0' => '3',	
	'240.0.0.0' => '4', '248.0.0.0' => '5',	'252.0.0.0' => '6', '254.0.0.0' => '7',	
	'255.0.0.0' => '8', '255.128.0.0' => '9', '255.192.0.0' => '10', '255.224.0.0' => '11',	
	'255.240.0.0' => '12', '255.248.0.0' => '13', '255.252.0.0' => '14', '255.254.0.0' => '15',	
	'255.255.0.0' => '16', '255.255.128.0' => '17', '255.255.192.0' => '18', '255.255.224.0' => '19',	
	'255.255.240.0' => '20', '255.255.248.0' => '21', '255.255.252.0' => '22', '255.255.254.0' => '23',	
	'255.255.255.0' => '24', '255.255.255.128' => '25', '255.255.255.192' => '26', 
	'255.255.255.224' => '27', '255.255.255.240' => '28', '255.255.255.248' => '29', 
	'255.255.255.252' => '30', '255.255.255.254' => '31', '255.255.255.255' => '32',
);

#-------------------------------------------------------------------------------
#    Usage message
#-------------------------------------------------------------------------------

my $usage = <<"EOF";
SNMP Collector script
usage:   $0 -d <device_id> 
example: $0 -d 23

[-h]          : Print this message

[-d] 	      : device_id


Andree Toonk: andree.toonk\@bc.net  
 
EOF

#-------------------------------------------------------------------------------
# Check the usage
#-------------------------------------------------------------------------------
my $opt_string = 'h:d:';
my %opt;
getopts( "$opt_string", \%opt ) or die $usage;
die $usage if (defined $opt{h});
die $usage if (!defined $opt{d});
my $device_id = $opt{d},     

my $community = undef;
my $fqdn = undef;
my $device = undef;

my %device_info = get_device_info($device_id);
#This is a hash with port ids and speed
my %port_speeds = get_port_speeds($device_id);

$fqdn = $device_info{device_fqdn};
$community = $device_info{snmp_ro};
$device = $device_info{name};

#--------------------------------------------------------------------
# Check if we have all the required properties
#--------------------------------------------------------------------

unless ((defined($snmpget)) && ($snmpget ne '')) {
	die "Error: could not find snmpget\n";
}
unless ((defined($snmpwalk)) && ($snmpwalk ne '')) {
	die "Error: could not find snmpwalk\n";
}
unless ((defined($rrdtool)) && ($rrdtool ne '')) {
	die "Error: could not find rrdtool\n";
}
unless ((defined($rrdupdate)) && ($rrdtool ne '')) {
	die "Error: could not find rrdupdate\n";
}
unless ((defined($rrddir)) && ($rrddir ne '')){
	die "Error: could not find rrd directory\n";
}
unless ((defined($device)) && ($device ne '')) {
	die "Error: Error: Unable to find FQDN for device ID $device_id, aborting device SNMP collection\n";
}
unless ((defined($fqdn)) && ($fqdn ne '')) {
	die "Error: Error: Unable to find FQDN for $device, aborting device SNMP collection\n";
}
if ((!defined($community)) && ($community ne '')) {
	die "Error: Unable to find community for $device, aborting device SNMP collection\n";
}
############################################
############################################
# Now let's check if the device is alive
############################################
if (!defined(device_alive())) {
	warn "Error: Unable connect to $device, not alive! aborting device SNMP collection\n";
	exit;
}

############################################
# Get all the information
############################################
my %index = get_ifIndex();
my %types = get_ifType();
my %names = get_ifNames();
my %alias = get_ifAlias();
my %ifDescr = get_ifDesc();
my %speed = get_ifSpeed();
my %mtu = get_ifMtu();
my %OperStatus = get_ifStatus();
my %ipv4 = get_ifIpv4();
my %v4netmasks = get_ifIpv4_mask();
my %ipv6 = get_ifIpv6();

###### 64 bit counters ############
my %ifHCInOctets = get_ifHCInOctets();
my %ifHCOutOctets =  get_ifHCOutOctets();

my %ifHCInUcastPkts = get_ifHCInUcastPkts();
my %ifHCOutUcastPkts = get_ifHCOutUcastPkts();


###### 32 bit counters ############
my %ifInErrors =  get_ifInErrors();
my %ifOutErrors = get_ifOutErrors();

my %ifInNUcastPkts =  get_ifInNUcastPkts();
my %ifOutNUcastPkts = get_ifOutNUcastPkts();
	

# loop through index names and sort
for my $int (sort { $index{$a} <=> $index{$b} }keys %index ) {
	my $type = $types{$int};
	my $name = $names{$int};
	my $ifalias = $alias{$int};
	my $ifdescr = $ifDescr{$int};
	my $ifspeed = $speed{$int};
	my $inet4;
	my $ifmtu;
	if(defined($mtu{$int}))   {
		$ifmtu = $mtu{$int};
	} else {
		$ifmtu = ''
	};

	my $ifOperStatus = $OperStatus{$int};

	# defining 32 bit counter hashes as empty for this interface, if required they will be populated just in time
	my %ifInOctets = ();
	my %ifOutOctets = ();
	my %ifInUcastPkts = ();
	my %ifOutUcastPkts = ();

	## RRD Stuff
	#first check if rrd file exist, if not create
	my $interface_file_name = $name;
	$interface_file_name =~ s/([\$\#\@\\\/\s])/-/g;
	#print "$interface_file_name\n";
	$interface_file_name = "deviceid".$device_id ."_" . $interface_file_name.".rrd";

	#now we know the file name, and it exists. let's update
	#my $update = "N:$ifHCInOctets{$int}:$ifHCOutOctets{$int}:$ifInErrors{$int}:$ifOutErrors{$int}:$ifInUcastPkts{$int}:$ifOutUcastPkts{$int}:$ifInNUcastPkts{$int}:$ifOutNUcastPkts{$int}";
	my $update;
	my $ifinErrors;
	my $ifoutErrors;
	if ((defined($ifHCInOctets{$int})) && (defined($ifHCOutOctets{$int}))) {
		### This means we're using 64bit counters
		$update = "N:$ifHCInOctets{$int}:$ifHCOutOctets{$int}";
		if (!defined($ifInErrors{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifInErrors{$int};
		}
		if (!defined($ifOutErrors{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifOutErrors{$int};
		}
		if (!defined($ifHCInUcastPkts{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifHCInUcastPkts{$int};
		}
		if (!defined($ifHCOutUcastPkts{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifHCOutUcastPkts{$int};
		}
		if (!defined($ifInNUcastPkts{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifInNUcastPkts{$int};
		}
		if (!defined($ifOutNUcastPkts{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifOutNUcastPkts{$int};
		}
			
	} else {
		# just in time, collect 32 bit counters for this interface because 64 bit don't exist
		%ifInOctets = get_ifInOctets($int);
		%ifOutOctets =  get_ifOutOctets($int);
		%ifInUcastPkts = get_ifInUcastPkts($int);
		%ifOutUcastPkts = get_ifOutUcastPkts($int);
	}

	if ((defined($ifInOctets{$int})) && (defined($ifOutOctets{$int}))) {
		##### This means we're using 32bit counters
		$update = "N:$ifInOctets{$int}:$ifOutOctets{$int}";
		if (!defined($ifInErrors{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifInErrors{$int};
		}
		if (!defined($ifOutErrors{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifOutErrors{$int};
		}
		if (!defined($ifInUcastPkts{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifInUcastPkts{$int};
		}
		if (!defined($ifOutUcastPkts{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifOutUcastPkts{$int};
		}
		if (!defined($ifInNUcastPkts{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifInNUcastPkts{$int};
		}
		if (!defined($ifOutNUcastPkts{$int})) {
			$update .= ':';
		} else {
			$update .= ":" . $ifOutNUcastPkts{$int};
		}
	} else {
		$name = $name || '';
		$ifalias = $ifalias || '';
		$int = $int || '';

		print "Ignoring $name, $ifalias , $int , No statistics found for this IF\n";
		next;
	}

	# Error threshold test	
	#if ((($ifInErrors{$int} > 0) || ($ifOutErrors{$int} > 0 )) && ($ifOperStatus eq "up")) {
	#	$mailcontent .= "$device $name\t in Errors: $ifInErrors{$int} \t Out errors: $ifOutErrors{$int}\t($ifalias -- $ifdescr\n";
	#}
		

	create_rrd_archive($interface_file_name,($ifspeed/8)) if ! -e "$rrddir/$interface_file_name";
	my $cli = "$rrdupdate \"$rrddir/$interface_file_name\" $update";
	system($cli);

	# Now get latest value
	my %latest_rrd_values = get_latest_rrd_values("$rrddir/$interface_file_name");
	# End RRD

	#for now only ethernet interfaces
	#if (($type eq "ethernetCsmacd") || ($type eq "l2vlan") ||($name =~ /(irb.(\d+))/)|| ($type eq "propVirtual")) { 
	if ( $type ne "other") {
		my $query = undef;
		if (my $ifid = check_entry($name,$type,$device_id) ) {

			#Update max DS value for RRD, just in case the interface speed has chancged
			if ($port_speeds{$ifid} != $ifspeed) {
				tune_rrd_archive($interface_file_name,($ifspeed/8));
				print "Updating update_rrd_archive new old speed ".$port_speeds{$ifid}, "new $ifspeed\n";
			}
			#print "entry bestaat, update $name,$deviceid\n";
			$ifalias = $dbh->quote( $ifalias );
			$ifdescr = $dbh->quote( $ifdescr );

			$query = "UPDATE interfaces set disc_interface_speed = '$ifspeed', disc_interface_mtu = " .
			"'$ifmtu', disc_interface_index = '$int', interface_alias = $ifalias, ifOperStatus = '$ifOperStatus', " .
			"disc_interface_type = '$type', last_seen = NOW(), interface_descr = $ifdescr,  ".
			"inbits = '$latest_rrd_values{inbits}' , outbits = '$latest_rrd_values{outbits}', ".
			"inerrors = '$latest_rrd_values{inerrors}', outerrors = '$latest_rrd_values{outerrors}',  ".
			"inunicastpackets = '$latest_rrd_values{inunicastpackets}', outunicastpackets = " .
			"'$latest_rrd_values{outunicastpackets}',  innonunicastpackets = '$latest_rrd_values{innonunicastpackets}', ".
			" outnonunicastpackets = '$latest_rrd_values{outnonunicastpackets}' where interface_id = $ifid";
		} else {
			#print "entry bestaat niet $name,$deviceid\n";

			$ifalias = $dbh->quote( $ifalias );
			$ifdescr = $dbh->quote( $ifdescr );
			$name = $dbh->quote( $name );

			$query = "INSERT INTO interfaces ( disc_interface_speed, disc_interface_mtu, disc_interface_index, " .
			"interface_name, interface_alias, interface_device, active, ifOperStatus, last_seen, insert_time, disc_interface_type, interface_descr ) VALUES " .
			"('$ifspeed', '$ifmtu', '$int', $name, $ifalias ,'$device_id', '1', '$ifOperStatus', NOW(), NOW(), '$type', $ifdescr )";
		}
		my $sth = $dbh->prepare($query);
        	$sth->execute();
        	$sth->finish();
	} 
	else {
		print "DEBUG: ignoring $name type is $type\n";
	}
}

# Now we do the same for IPv4 addresses
for my $int ( keys %ipv4 ) {
	# Int is snmp index number
	# In some stange cases we see an IP address for a interface (snmp interface index number)
	# that does not seem to exist. The database will complain because of foreign key. For now we ignore those
	if (!exists($index{$int})) {
		#print "Ignoring ipv4 IF snmp index $int on $device. I have an IP but the interface does not seem to exist\n";
		next;
	}
	my @tmparray = @{$ipv4{$int}};
	foreach (@tmparray) {
		my $query = undef;

		if (my $ifid = check_entry_interface_ip($device_id,$int,$_,$v4netmasks{$_},'4') ) {
			$query = "UPDATE interface_IPaddresses set last_seen = NOW() where id = $ifid " ;
		} else {
			$query = "INSERT INTO interface_IPaddresses ( device_id, if_index, inet_address, " .
			"inet_address_length, last_seen, inet) VALUES " .
			"('$device_id', '$int', '$_', '$v4netmasks{$_}', NOW(), '4' )";
		}
		my $sth = $dbh->prepare($query);
        	if (!$sth->execute()) {
			print "$device ip address is $_ snmpindex $int query failed: $query\n";
		}
        	$sth->finish();
	}
}

# Now we do the same for IPv6 addresses
for my $int ( keys %ipv6 ) {
	# Int is snmp index number
	# In some stange cases we see an IP address for a interface (snmp interface index number)
	# that does not seem to exist. The database will complain because of foreign key. For now we ignore those
	if (!exists($index{$int})) {
		#print "Ignoring ipv4 IF snmp index $int on $device. I have an IP but the interface does not seem to exist\n";
		next;
	}
	my @tmparray = @{$ipv6{$int}};
	foreach (@tmparray) {
		my $query = undef;
		my @inet6 = split(/\//,$_);
		my $v6address = $inet6[0];
		my $v6address_length = $inet6[1];
		if (my $ifid = check_entry_interface_ip($device_id,$int,$v6address,$v6address_length,'6') ) {
			$query = "UPDATE interface_IPaddresses set last_seen = NOW() where id = $ifid " ;
		} else {
			$query = "INSERT INTO interface_IPaddresses ( device_id, if_index, inet_address, inet_address_length, last_seen, inet) VALUES " .
			"('$device_id', '$int', '$v6address','$v6address_length', NOW() , '6')";
		}
		my $sth = $dbh->prepare($query);
        	if (!$sth->execute()) {
			print "$device ip address is $_ snmpindex $int query failed: $query\n";
		}
        	$sth->finish();
	}
}

### Now clean up database, to makes sure that old interfaces don't stick around to long
update_all($device_id);

# Disconnect from MySQL
$dbh->disconnect;

############################ Below are all the sub routines #########################

sub send_mail {
	my $mailcontent = shift;
	if ($mailcontent ne '') {
                open (MAIL, "|/usr/sbin/sendmail -oi -t");
                print MAIL "From: NetHarbour <xx\@xx.xx>\n"; ## don't forget to escape the @
                print MAIL "To: xx\@xx.xx\n";
                print MAIL "Subject: Interface errors detected on $device\n";
                print MAIL "\n";
                print MAIL "There were Interface errors detected, see detail below:\n\n";
                print MAIL "$mailcontent\n\n\n" ;
                close (MAIL);
	
	}
}
sub update_entry {
        my ($interface_name,$interface_device) = @_;
        my $query = "UPDATE cmdb SET last_seen = NOW(), " .
                "active = 1 WHERE interface_name = '$interface_name'  AND " .
                "interface_device = '$interface_device'  AND active = '1'";
        my $sth = $dbh->prepare($query);
        $sth->execute();
        $sth->finish();
}
sub  check_entry_interface_ip {
        my ($deviceid,$snmpifindex,$ip,$iplength,$inet) = @_;
        my $result = 0;
        my $query = "select id from interface_IPaddresses where device_id = '$deviceid'  AND " .
		"if_index = '$snmpifindex'  AND inet_address = '$ip' and inet_address_length = '$iplength'" .
		" And inet = '$inet'";
        my $sth = $dbh->prepare($query);
        $sth->execute();
        if ($sth->rows == 0) {
                $result = 0;
        } else {
		while (my @data = $sth->fetchrow_array()) {
			$result = $data[0];
		}
        }
        $sth->finish();
        return $result;
}
sub check_entry {
	my ($interface_name,$type,$interface_device) = @_;
        my $result = 0;
        my $query = "select interface_id from interfaces where interface_name = '$interface_name'  AND " .
				"disc_interface_type  = '$type' AND interface_device = '$interface_device'  AND active = '1'";
        my $sth = $dbh->prepare($query);
        $sth->execute();
        if ($sth->rows == 0) {
                $result = 0;
        } else {
		while (my @data = $sth->fetchrow_array()) {
			$result = $data[0];
		}
        }
        $sth->finish();
        return $result;
}
sub update_all {
        my ($interface_device) = @_;
	my $query = "UPDATE interfaces SET active = 0 where last_seen < CURRENT_TIMESTAMP  - interval 2 day and interface_device = '$interface_device'";
        my $sth = $dbh->prepare($query);
        $sth->execute();
        $sth->finish();

	# Do the same for IP addresses
	# Here we just delete them
	$query = "delete from interface_IPaddresses where last_seen < CURRENT_TIMESTAMP  - interval 300 second and device_id = '$interface_device'";
        $sth = $dbh->prepare($query);
        $sth->execute();
        $sth->finish();
}

sub tune_rrd_archive {
	my $interface_file_name = shift;
	my $max_ds = shift;

	$max_ds = "U" if ($max_ds < 1);
	$max_ds = "U" unless (defined($max_ds));  

	# Setting the max to the ifspeed
	# will result in gaps. So we should give it some buffer
	# for now we just double it.

	if ($max_ds ne "U") {
		$max_ds = $max_ds * 2;
	}



	my $cli = " $rrdtool tune \'$rrddir/$interface_file_name\' \\
      		--maximum INOCTETS:$max_ds \\
      		--maximum OUTOCTETS:$max_ds \\
      		--maximum INERRORS:$max_ds \\
      		--maximum OUTERRORS:$max_ds \\
      		--maximum INUCASTPKTS:$max_ds \\
      		--maximum OUTUCASTPKTS:$max_ds \\
      		--maximum INNUCASTPKTS:$max_ds \\
      		--maximum OUTNUCASTPKTS:$max_ds ";
	system `$cli`;
	print "$cli\n";
}

sub create_rrd_archive {
	# search and replace special chars
	my $interface_file_name = shift;
	my $max_ds = shift;

	$max_ds = "U" if ($max_ds < 1);
	$max_ds = "U" unless (defined($max_ds));  

	if ($max_ds ne "U") {
		$max_ds = $max_ds * 2;
	}

	#special chars replaced by a -, unix doesnt like / in filename
	
	# Based on http://oss.oetiker.ch/rrdtool/tut/rrdtutorial.en.html
	#  600 samples of 5 minutes  (2 days and 2 hours)
	#  700 samples of 30 minutes (2 days and 2 hours, plus 12.5 days)
 	#  775 samples of 2 hours    (above + 50 days)
 	#  797 samples of 1 day      (above + 732 days, rounded up to 797)

	my $cli = " $rrdtool create \'$rrddir/$interface_file_name\' \\
      DS:INOCTETS:COUNTER:600:0:$max_ds \\
      DS:OUTOCTETS:COUNTER:600:0:$max_ds \\
      DS:INERRORS:COUNTER:600:0:$max_ds \\
      DS:OUTERRORS:COUNTER:600:0:$max_ds \\
      DS:INUCASTPKTS:COUNTER:600:0:$max_ds \\
      DS:OUTUCASTPKTS:COUNTER:600:0:$max_ds \\
      DS:INNUCASTPKTS:COUNTER:600:0:$max_ds \\
      DS:OUTNUCASTPKTS:COUNTER:600:0:$max_ds \\
      RRA:AVERAGE:0.5:1:600 \\
      RRA:AVERAGE:0.5:6:700 \\
      RRA:AVERAGE:0.5:24:775 \\
      RRA:AVERAGE:0.5:288:797 \\
      RRA:MAX:0.5:1:600 \\
      RRA:MAX:0.5:6:700 \\
      RRA:MAX:0.5:24:775 \\
      RRA:MAX:0.5:288:797 ";
	system `$cli`;
	print "$cli\n";
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

sub get_latest_rrd_values {
	my $interface_file_name = shift;
	my $cli = "$rrdtool fetch \'$interface_file_name\' AVERAGE -s -15m ";
	my @results = `$cli`;
	my %rrdvalues = (
		inbits => 0,
		outbits => 0,
		inerrors => 0,
		outerrors => 0,
		inunicastpackets => 0,
		outunicastpackets => 0,
		innonunicastpackets => 0,
		outnonunicastpackets => 0
	);
	for (my $count=@results-1; $count >= 0; $count--) {
		chomp($results[$count]);
		#if ( $results[$count] =~ /NaN NaN NaN NaN NaN NaN NaN NaN/) {
		#	next;
		#}
		#if ( $results[$count] =~ /-NaN -NaN -NaN -NaN -NaN -NaN -NaN -NaN/) {
		#	next;
		#}
		if ( $results[$count] =~ /^\d+:\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)\s+(\S+)$/) {
			# translate from scientific notation and round and translate to bps
			my $INBITS = round($1)*8;
			my $OUTBITS = round($2)*8;
			my $INERRORS = round($3);
			my $OUTERRORS = round($4);
			my $INUNIPCTS = round($5);
			my $OUTUNIPCTS = round($6);
			my $INNUNIPCTS = round($7);
			my $OUTNUNIPCTS = round($8);

			# Check if this is a valid number
			unless ($INBITS =~ /\d+/) {
				next;
			}

			$rrdvalues{'inbits'} = $INBITS;
			$rrdvalues{'outbits'} = $OUTBITS;
			$rrdvalues{'inerrors'} = $INERRORS;
			$rrdvalues{'outerrors'} = $OUTERRORS;
			$rrdvalues{'inunicastpackets'} = $OUTUNIPCTS;
			$rrdvalues{'outunicastpackets'} = $OUTUNIPCTS,
			$rrdvalues{'innonunicastpackets'} = $INNUNIPCTS;
			$rrdvalues{'outnonunicastpackets'} = $OUTNUNIPCTS;
			return %rrdvalues;
		}
	}
	return %rrdvalues;
}

sub round {
	my($number) = shift;
	return int($number + .5);
}

sub get_devices {
	my $query = "select device_id plugin_SNMPPoller_devices 
		FROM plugin_SNMPPoller_devices";
	my @devices;
	my $sth = $dbh->prepare($query);
	$sth->execute();
	if ($sth->rows == 0) {
        } else {
		while (my @data = $sth->fetchrow_array()) {
			push (@devices, $data[0]);
		}
	}
        $sth->finish();
	return @devices;
}

sub device_alive {
	# First test if the Device is alive
	my $result = undef;
	#my $cli_alive = "$snmpget -t2  -r1 -c $community -v2c  $fqdn  system.sysDescr.0 ";
	my $cli_alive = "$snmpget -t2  -r1 -c $community -v2c  $fqdn 1.3.6.1.2.1.1.5.0 ";
	#print "$cli_alive\n";
	my @alive_results = `$cli_alive`;
	if (!@alive_results) {
		warn "$device - $fqdn unreachable\n";
		$result = undef;
	} else {
		$result = 1;
	}
	return $result;
}

sub get_ifIndex {
	# ifindex
	my $cli = "$snmpwalk -v 2c -c $community $fqdn .1.3.6.1.2.1.2.2.1.1";
	my @results = `$cli`;
	my %snmpdata;  
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifIndex.92 = INTEGER: 92
		if ( $line =~ /^IF-MIB::ifIndex.(\d+).+INTEGER:\s(\d+)$/) {
			#print "match ifindex $1-- index $1 \n";
			$snmpdata{$1} = $2; 
		}
		else {
			#print "no match for $line\n";
		}
	}
	return %snmpdata;
}

sub get_ifStatus {
	# ifindex and if status
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn .1.3.6.1.2.1.2.2.1.8 ";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifOperStatus.136 = INTEGER: up(1)
		#print "$line\n";
		if ( $line =~ /^IF-MIB::ifOperStatus.(\d+).+INTEGER:\s(\w+)/) {
			$snmpdata{$1} = $2; 
		}
		else {
			#print "no match for $line\n";
		}
	}
	return %snmpdata;
}

sub get_ifNames {
	# ifindex and if names
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn .1.3.6.1.2.1.31.1.1.1.1";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifName.105 = STRING: xe-1/0/0.739
		#print "$line\n";
		if ( $line =~ /^IF-MIB::ifName.(\d+).+STRING:\s(.+)$/) {
			#print "match ifindex $1-- name $2\n";
			$snmpdata{$1} = $2; 
		}
		else {
			#print "no match for $line\n";
		}
	}
	return %snmpdata;
}

sub get_ifSpeed {
	# ifindex and ifspeed
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn .1.3.6.1.2.1.31.1.1.1.15";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifSpeed.99 = Gauge32: 4294967295
		if ( $line =~ /^IF-MIB::ifHighSpeed.(\d+).+Gauge\d+:\s(\d+)$/) {
			my $index = $1;
			#higspeed is necesary for 10g port, otherwise you'd get 4294967295
			#highspeed is mb/s so we need to multiply with 1,000,000
			my $ifspeed = 1000000 * $2;
			#print "$index $2, $ifspeed\n";
			$snmpdata{$index} = $ifspeed; 
		}
		else {
			#print "no match for $line\n";
		}
	}
	return %snmpdata;
}

sub get_ifType {
	# ifindex and iftype
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn .1.3.6.1.2.1.2.2.1.3";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifType.93 = INTEGER: l2vlan(135)
		#IF-MIB::ifType.126 = INTEGER: propVirtual(53)
		if ( $line =~ /^IF-MIB::ifType.(\d+).+INTEGER:\s(.+)\(\d+\)/) {
			#print "match ifindex $1-- type $2 \n";
			$snmpdata{$1} = $2; 
		}
		else { 	
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifDesc {
	# ifindex and ifDescr
	my %snmpdata;
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifDescr";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		my $descr;
        	#IF-MIB::ifAlias.97 = STRING: ORAN-Test
        	if ( $line =~ /^IF-MIB::ifDescr.(\d+).+STRING:\s(.*)?/) {
			my $index = $1;
			if (defined($2)) {
				$descr = $2;
			} else {
				$descr = '';
                	}
                	#print "match ifindex $1-- alias $2 \n";
                	$snmpdata{$index} = $descr;
        	}
        	else {
			warn "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifIpv6 {
	# ifindex and IPv6 address
	# %ipv6addr holds an array for each snmp index 
	# Because an interface can have mutiple addresses.
	my %snmpdata;  
	my $cli = "$snmpwalk -Onq -v 2c -c $community $fqdn 1.3.6.1.2.1.55.1.8.1.2 ";
	my @results = `$cli`;
	foreach my $line (@results) {
		my $v6addr_length = '';
		my $v6addr ='';
		chomp $line;
		#.1.3.6.1.2.1.55.1.8.1.2.175.32.1.4.16.16.1.0.4.0.0.0.0.0.0.0.2 64
		#.1.3.6.1.2.1.55.1.8.1.2.243.254.128.0.0.0.0.0.0.2.160.165.255.254.97.184.85 128

		if ( $line =~ /^\.1\.3\.6\.1\.2\.1\.55\.1\.8\.1\.2\.(\d+)\.((\d+\.){15}(\d+))\s(\d+)/) {
			my $index = $1;
			my @tmp_array;
			if (exists($snmpdata{$index})) {
				@tmp_array = @{$snmpdata{$index}};
			} 
			if (defined($2)) {
				$v6addr = $2;
				$v6addr_length = $5;
				my @v6hex = split(/\./, $v6addr);
				my $v6string = '';
				my $i=0;
				foreach (@v6hex) {
					$v6string .= sprintf("%02x", $_);
					if (($i % 2 && $i < 14)) {
						$v6string .= ":";
					}
					$i++;
				}
				push(@tmp_array, "$v6string/$v6addr_length");
				@{$snmpdata{$index}} = @tmp_array; 
			}
		} else { 
			#warn "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifIpv4_mask {

	############################################################################
	# Now retrieve subnetmask and translate to cidr
	############################################################################

	my %snmpdata;  
	my $cli = "$snmpwalk -Onq -v 2c -c $community $fqdn 1.3.6.1.2.1.4.20.1.3";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#.1.3.6.1.2.1.4.20.1.3.10.0.0.4 255.0.0.0
		if ( $line =~ /^\.1\.3\.6\.1\.2\.1\.4\.20\.1\.3\.(\d+\.\d+\.\d+\.\d+)\s(\d+\.\d+\.\d+\.\d+)/) {
			my $v4address = $1;
			my $v4netmask = $2;
			my $cidrmask = $netmask{$v4netmask};
			$snmpdata{$v4address} = $cidrmask;
		}
		else { 
			warn "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifIpv4 {

	# ifindex and IPv4 address
	# %ipv4addr holds an array for each snmp index 
	# Because an interface can have mutiple addresses.
	my %snmpdata;  
	my $cli = "$snmpwalk -Onq -v 2c -c $community $fqdn .1.3.6.1.2.1.4.20.1.2";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		if ( $line =~ /^\.1\.3\.6\.1\.2\.1\.4\.20\.1\.2\.(\d+\.\d+\.\d+\.\d+)\s(\d+)/) {
			my $v4addr;
			my $index = $2;
			my @tmp_array;
			if (exists($snmpdata{$index})) {
				@tmp_array = @{$snmpdata{$index}};
			} 
			if (defined($2)) {
				$v4addr = $1;
				push(@tmp_array, $v4addr);
			} else {
				$v4addr = '';
			}
			@{$snmpdata{$index}} = @tmp_array; 
			#print "if index $index : $v4addr\n";
		}
		else { 
			warn "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifAlias {

	# ifindex and alias
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn .1.3.6.1.2.1.31.1.1.1.18";
	my @results = `$cli`;
	my $name;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifAlias.97 = STRING: ORAN-Test
		if ( $line =~ /^IF-MIB::ifAlias.(\d+).+STRING:\s(.+)?/) {
			my $index = $1;
			if (defined($2)) {
				$name = $2;
			} else {
				$name = '';
			}
			$snmpdata{$index} = $name; 
		}
		else { 
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifHCOutOctets {

	# ifindex and ifHCOutOctets 64 bits
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifHCOutOctets";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifHCOutOctets.194 = Counter64: 0

		if ( $line =~ /^IF-MIB::ifHCOutOctets.(\d+).+Counter64:\s(\d+)/) {
			$snmpdata{$1} = $2; 
		}
		else { 
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifOutOctets {
	# ifindex and ifHCOutOctets 32 bits
	my $int = shift;
	my %snmpdata;
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifOutOctets.$int";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;

		if ( $line =~ /^IF-MIB::ifOutOctets.(\d+).+Counter32:\s(\d+)/) {
			$snmpdata{$1} = $2;
		}
		else {
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifInNUcastPkts {
	# ifindex and ifInNUcastPkts 32 bits
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifInNUcastPkts";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifInNUcastPkts.40 = Counter32: 18341497

		if ( $line =~ /^IF-MIB::ifInNUcastPkts.(\d+).+Counter32:\s(\d+)/) {
			$snmpdata{$1} = $2; 
		}
		else { 
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifOutNUcastPkts {
	# ifindex and ifOutNUcastPkts 64 bits
	my %snmpdata;
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifOutNUcastPkts";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifOutNUcastPkts.39 = Counter32: 1064569
		if ( $line =~ /^IF-MIB::ifOutNUcastPkts.(\d+).+Counter32:\s(\d+)/) {
			$snmpdata{$1} = $2;
		}
		else {
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifHCInUcastPkts {
	# ifindex and ifHCInUcastPkts 64 bits
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifHCInUcastPkts";
	my @results = `$cli`;
	my %snmpdata;  
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifHCInUcastPkts.51 = Counter64: 103302252
		if ( $line =~ /^IF-MIB::ifHCInUcastPkts.(\d+).+Counter64:\s(\d+)/) {
			$snmpdata{$1} = $2; 
		}
		else { 
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifHCOutUcastPkts {
	# ifindex and ifHCOutUcastPkts 64 bits
	my %snmpdata;
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifHCOutUcastPkts";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifHCOutUcastPkts.51 = Counter64: 103302252
		if ( $line =~ /^IF-MIB::ifHCOutUcastPkts.(\d+).+Counter64:\s(\d+)/) {
			$snmpdata{$1} = $2;
		}
		else {
			#print "no match for $line\n"; 
		}
	}
	return  %snmpdata;
}

sub get_ifInUcastPkts {
	# ifindex and ifHCInUcastPkts 32 bits
	my $int = shift;
	my %snmpdata;
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifInUcastPkts.$int";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		if ( $line =~ /^IF-MIB::ifInUcastPkts.(\d+).+Counter32:\s(\d+)/) {
			$snmpdata{$1} = $2;
		}
		else {
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifOutUcastPkts {
	# ifindex and ifOutUcastPkts 32 bits
	my $int = shift;
	my %snmpdata;
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifOutUcastPkts.$int";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		if ( $line =~ /^IF-MIB::ifOutUcastPkts.(\d+).+Counter32:\s(\d+)/) {
			$snmpdata{$1} = $2;
        	}
		else {
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifOutErrors {
	# ifindex and ifOutErrors 32 bits
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifOutErrors";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifOutErrors.163 = Counter32: 0
		if ( $line =~ /^IF-MIB::ifOutErrors.(\d+).+Counter32:\s(\d+)/) {
			$snmpdata{$1} = $2; 
		}
		else { 
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata
}

sub get_ifInErrors {
	# ifindex and ifInErrors 32 bits
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifInErrors";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifInErrors.163 = Counter32: 0
		if ( $line =~ /^IF-MIB::ifInErrors.(\d+).+Counter32:\s(\d+)/) {
			$snmpdata{$1} = $2; 
		}
		else { 
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifHCInOctets {

	# ifindex and inoctets 64 bits
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifHCInOctets";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifHCInOctets.194 = Counter64: 0
	
		if ( $line =~ /^IF-MIB::ifHCInOctets.(\d+).+Counter64:\s(\d+)/) {
			$snmpdata{$1} = $2; 
		}
		else { 
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}

sub get_ifInOctets {
	# ifindex and inoctets 64 bits
	my $int = shift;
	my %snmpdata;
	my $cli = "$snmpwalk -v 2c -c $community $fqdn IF-MIB::ifInOctets.$int";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;

		if ( $line =~ /^IF-MIB::ifInOctets.(\d+).+Counter32:\s(\d+)/) {
			$snmpdata{$1} = $2;
		}
		else {
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}


sub get_ifMtu {
	# ifindex and mtu
	my %snmpdata;  
	my $cli = "$snmpwalk -v 2c -c $community $fqdn .1.3.6.1.2.1.2.2.1.4";
	my @results = `$cli`;
	foreach my $line (@results) {
		chomp $line;
		#IF-MIB::ifMtu.55 = INTEGER: 1514
		if ( $line =~ /^IF-MIB::ifMtu.(\d+).+INTEGER:\s(\d+)/) {
			#print "match ifindex $1-- mtu $2 \n";
			$snmpdata{$1} = $2; 
		}
		else { 
			#print "no match for $line\n"; 
		}
	}
	return %snmpdata;
}


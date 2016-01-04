#!/usr/bin/env perl

use strict;
use warnings;
use DBI;

my $config_file = "config/cmdb.conf";


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

my $service_id;
my @childs;
my %host_list;
my %host_alive;

my $query = "SELECT distinct Services.service_id  
		FROM  Services, Service_types 
		WHERE 
		Service_types.service_type_id = Services.service_type and service_layer = '3' 
		AND Services.archived = '0'	
		" ;
my $sth = $dbh->prepare($query);
$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
while (my @data = $sth->fetchrow_array()) {
	$service_id = $data[0];
	#now we have service id, next step is to retrieve device and if index we want
	# let's do that in a function:
	my ($ifindex, $interface_speed) = get_interface_info_for_service($service_id);
	my @snmpinfo = get_snmp_info_for_service($service_id);
	my $device = $snmpinfo[1];
	my $snmp_community = $snmpinfo[2];
	my $device_id = $snmpinfo[3];
	# element [0] is device address, element [1] is snmp_ro community

	#---------------- Check if user wants this device to be polled with SNMP ------#
	unless (do_snmp($device_id)) {
		next;
	}
	if (!defined($ifindex)) {
		next;
	}
        if (($device eq '') || ($snmp_community eq '') || ($ifindex eq '')) {
                print "didnt get all parameters: device = $device, snmp_community = $snmp_community, ifindex = $ifindex\n";
		next;
        }
        my $cli = "$snmpget -v 2c  -Oqv -c $snmp_community $device IF-MIB::ifHCInOctets.$ifindex IF-MIB::ifHCOutOctets.$ifindex";
        if(!exists($host_list{$device})) {
                #create array
                my @tmparray;
                @{$host_list{$device}} = @tmparray;
 	}
	my @array = @{$host_list{$device}};
	push(@array, [$snmp_community, $device, $ifindex, $interface_speed, $service_id]);
	@{$host_list{$device}} = @array;
}
$sth->finish();
$dbh->disconnect;

#now we have snmp info and ifindex, so let's fire of the snmpget

for my $host_key ( keys %host_list ) {
	my @cli_array = @{$host_list{$host_key}};
	my $pid = fork();
	if ($pid) {
		# parent
		#print "pid is $pid, parent $$\n";
		push(@childs, $pid);
	} elsif ($pid == 0) {
		#print "Starting PID $$ for $host_key \n";
		foreach (@cli_array) {
			my @this = @$_;
			my $snmp_community = $this[0];
			my $device = $this[1];
			my $ifindex = $this[2];
			my $interface_speed_sql = $this[3];
			my $service_id = $this[4];
			unless (is_alive($device,$snmp_community)) {
				next;
			}

			my @ifstats = get_ifstats($device,$snmp_community,$ifindex);
			my $ifspeed = get_ifspeed($device,$snmp_community,$ifindex);
			my $filename = "service_id_" . $service_id . ".rrd";
			
			#print "$filename,$ifstats[0],$ifstats[1],$ifspeed\n";
		
			unless (defined($ifspeed)) {
				next;
			}	
			#Create RRD file if does not exist
        		$filename =~ s/([\$\#\@\\\/\s])/-/g;
        		create_rrd_archive($filename,($ifspeed/8)) if ! -e "$rrddir/$filename";

			my $max_ds = $ifspeed/8;
			if ((defined($max_ds)) && ($max_ds > 1)) {
				tune_rrd_archive($filename, $max_ds);
			}

			#Now update rrd file with latest counter values
			unless (update_rrd($filename,$ifstats[0],$ifstats[1])) {
				print "failed to update service_id $service_id => inoctets / outoctets not defined\n";
			}
		}
		exit(0);
	} else {
		die "couldnt fork: $!\n";
	}
}
	

foreach (@childs) {
	my $tmp = waitpid($_, 0);
}


#exit;

sub tune_rrd_archive {
	my $interface_file_name = shift;
	my $max_ds = shift;
	$max_ds = "U" if ($max_ds < 1);
	$max_ds = "U" unless (defined($max_ds));
	#print "tuning $interface_file_name to $max_ds\n";
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
	#print "$cli\n";
}

sub update_rrd {
	my $rrd_file = shift;
	my $inoctets = shift;
	my $outoctets = shift;
	if ((!defined($inoctets)) || (!defined($outoctets))) {
		#print "inoctets ($inoctets) / outoctets not defined ($outoctets)\n";
		return 0;
	}

	## RRD Stuff
	#first check if rrd file exist, if not create
        #$rrd_file =~ s/([\$\#\@\\\/\s])/-/g;
        #print "$rrd_file\n";
	#my $ifindex = get_ifindex_for_service($service_id);
	

        #now we know the file name, and it exists. let's update
        #$update = "N:$ifHCInOctets{$int}:$ifHCOutOctets{$int}:$ifInErrors{$int}:$ifOutErrors{$int}:$ifInUcastPkts{$int}:$ifOutUcastPkts{$int}:$ifInNUcastPkts{$int}:$ifOutNUcastPkts{$int}";
        my $update = "N:$inoctets:$outoctets:0:0:0:0:0:0";

        my $cli = "$rrdupdate \"$rrddir/$rrd_file\" $update";
        system($cli);
	return 1;

        # End RRD
}

sub create_host_list {
	my $device = shift;
	my $snmp_community = shift;
	my $ifindex = shift;
	my @result = undef;
	my %hosts;
	if (($device eq '') || ($snmp_community eq '') || ($ifindex eq '')) {
		print "didnt get all parameters: device = $device, snmp_community = $snmp_community, ifindex = $ifindex\n";
		#return undef;
	}
	my $cli = "$snmpget -v 2c  -Oqv -c $snmp_community $device IF-MIB::ifHCInOctets.$ifindex IF-MIB::ifHCOutOctets.$ifindex";
	if(!exists($hosts{$device})) {
		print "Added $device\n";
		#create array
		my @tmparray = ();
		$hosts{$device} = @tmparray;
	} else {
		print "Existing $device\n";
		my @array = $hosts{$device};
		push(@array, $cli);
		$hosts{$device} = @array;
	}
	return  %hosts;
}
		
sub get_ifstats {
	# ifindex and ifHCOutOctets 64 bits
	my $device = shift;
	my $snmp_community = shift;
	my $ifindex = shift;
	my $cli = "$snmpget -v 2c  -Oqv -c $snmp_community $device IF-MIB::ifHCInOctets.$ifindex IF-MIB::ifHCOutOctets.$ifindex";
	my @result;
	chomp (my @results = `$cli`);
	my $ifHCInOctets = $results[0];
	my $ifHCOutOctets = $results[1];
	#print "$ifHCInOctets $ifHCOutOctets\n";
	#$ifHCInOctets = chomp($ifHCInOctets);
	if (($ifHCInOctets !~ /\d+/) || ($ifHCOutOctets !~ /\d+/)) {
		#print "Invalid result for $cli\n";
		#print "result is ifHCInOctets: $ifHCInOctets ifHCOutOctets is $ifHCOutOctets\n";
	} 
	else {
		@result = ($ifHCInOctets,$ifHCOutOctets);
	}
	return @result;
}

sub get_ifspeed {
	# ifindex and ifHCOutOctets 64 bits
	my $device = shift;
	my $snmp_community = shift;
	my $ifindex = shift;
	my $cli = "$snmpget -v 2c  -Oqv -c $snmp_community $device .1.3.6.1.2.1.31.1.1.1.15.$ifindex";
	my @result;
	chomp (my @results = `$cli`);
	my $speed = $results[0];
	#$ifHCInOctets = chomp($ifHCInOctets);
	if (($speed !~ /\d+/) ) {
		#print "Invalid result for $cli\n";
		#print "result is ifHCInOctets: $ifHCInOctets ifHCOutOctets is $ifHCOutOctets\n";
	} 
	else {
		return $speed * 1000000;
	}
	return undef;
}




sub create_rrd_archive {
	# search and replace special chars
        my $interface_file_name = shift;
	my $max_ds = shift;

	$max_ds = "U" if ($max_ds < 1);
	$max_ds = "U" unless (defined($max_ds));

        #special chars replaced by a -, unix doesnt like / in filename
        #64 bits max is 18446744073709551616
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

sub get_snmp_info_for_service {
	my $service_id = shift;
	my @snmp_info;
	my $query = "select Services_Interfaces.device, Devices.name, Devices.snmp_ro, Devices.device_fqdn, Devices.device_id
			FROM  Services_Interfaces, Devices  
			WHERE service_id = '$service_id' 
			AND Devices.device_id = Services_Interfaces.device";
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: $query\n " . $sth->errstr;
	if ($sth->rows == 0) {
		print "something went wrong, no snmp info for this service $service_id\n";
	} else {
                while (my @data = $sth->fetchrow_array()) {
                        my $device_name = $data[1];
                        my $device_fqdn = $data[3];
                        my $snmp_ro = $data[2];
                        my $device_id = $data[4];
			@snmp_info = ($device_name,$device_fqdn,$snmp_ro,$device_id);
		}
	}
        $sth->finish();
	return @snmp_info;
}


sub get_interface_info_for_service {
	my $service_id = shift;
	my $interface_index = undef;
	my $interface_speed = undef;
	my $query = "select Services_Interfaces.interface_name, tagged, vlan, device from Services_Interfaces where service_id = '$service_id'";
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: $query\n " . $sth->errstr;
	if ($sth->rows == 0) {
		#print "something went wrong, no interface for this service $service_id\n";
	} else {
                while (my @data = $sth->fetchrow_array()) {
                        my $interface_name = $data[0];
                        my $tagged = $data[1];
                        my $vlan = $data[2];
                        my $device = $data[3];
			if (($vlan == 0) && ($tagged == 0)) {
				# then there's no sub interface and we just monitor the physical interface
				$interface_name = $interface_name;
			} else {
				$interface_name = "$interface_name.$vlan";
			}
			# Ok, now we have the correct interface, let's retrieve if index and device name
			my $ifquery = "select disc_interface_index, disc_interface_speed from interfaces where interface_device = '$device' and interface_name = '$interface_name' and active = '1'";
			my $sth2 = $dbh->prepare($ifquery);
			$sth2->execute();
			if ($sth2->rows == 0) {
				print "something went wrong, no if index for device $device interface '$interface_name  service $service_id\n";
			} else {
                		while (my @data2 = $sth2->fetchrow_array()) {
                        		$interface_index = $data2[0];
                        		$interface_speed = $data2[1];
				}
			}
        		$sth2->finish();
		}
				
        }
        $sth->finish();
	return ($interface_index, $interface_speed);
}

sub is_alive {
	my $host = shift;
	my $community = shift;

	# First check cached info
	if (!defined($host_alive{$host})) {
		my $cli_alive = "$snmpget -t2  -r1 -c $community -v2c  $host  system.sysDescr.0 ";
		my @alive_results = `$cli_alive`;
		if (!@alive_results) {
			$host_alive{$host} = 0;
			print "host $host not responding to snmp: $cli_alive\n";
		} else {
			$host_alive{$host} = 1;
		}
	}
	
	return $host_alive{$host};
}

sub do_snmp {
	my $device_id = shift;
	my $query ="select device_id from plugin_SNMPPoller_devices
		WHERE  device_id = '$device_id' AND enabled > '0'";
	my $sth = $dbh->prepare($query);
        $sth->execute() or die "Couldn't execute statement: $query\n " . $sth->errstr;
        if ($sth->rows == 0) {
		return undef;
        } else {
		return 1;
	}
}

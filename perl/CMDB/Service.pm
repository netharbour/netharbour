##########################################################################
# Andree Toonk
# July 16, 2010
##########################################################################
package Service;
use warnings;
use strict;

#----------------- constructor ------------------#
sub new {
	my $invocant = shift;
	my $class    = ref($invocant) || $invocant;
	my $self = {
		db_handler => undef,
		service_id => undef,
		@_,
	};
	bless($self, $class);
	unless (defined($self->{'service_id'})) {
		return $self;
	}

	if (defined($self->{'db_handler'})) {
		$self->get_service_info();
		if ($self->{'service_layer'} == 3) {
			$self->get_L3_service_info();
		}
	} else {
		warn "WARNING: Invalid DB handler\n";
	}
	return $self;
}


#----------------- Get all Services ------------------#
sub getServices {
	my $self = shift;
	my %result;
	my $query = "SELECT service_id, name FROM Services";
	my $dbh = $self->{'db_handler'};
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	while (my @data = $sth->fetchrow_array()) {
		$result{$data[0]} = $data[1];
	} 
	return %result;
};

#----------------- Get Functions ------------------#
sub name {
	my $self = shift;
	return $self->{name};
}
sub type {
	my $self = shift;
	return $self->{service_type};
}
sub typeName {
	my $self = shift;
	return $self->{service_type_name};
}
sub serviceLayer {
	my $self = shift;
	return $self->{service_layer};
}

sub contact {
	my $self = shift;
	return $self->{contact_id};
}
sub contactName {
	my $self = shift;
	return $self->{contact_name};
}
sub status {
	my $self = shift;
	return $self->{status};
}
sub peId {
	my $self = shift;
	return $self->{pe_id};
}
sub peName {
	my $self = shift;
	return $self->{pe_name};
}

sub interfaceFull {
	# Contsruct full name incl vlan etc
	my $self = shift;
	if (($self->{tagged} > 0) && ($self->{vlan_id} ne '')){
		return $self->{interface_name} . "." . $self->{vlan_id};
	} else {
		return $self->{interface_name};
	}
}
sub interface {
	my $self = shift;
	return $self->{interface_name};
}
sub mtu {
	my $self = shift;
	return $self->{mtu};
}
sub tagged {
	my $self = shift;
	return $self->{tagged};
}
sub vlanId {
	my $self = shift;
	return $self->{vlan_id};
}

sub routingType {
	my $self = shift;
	return $self->{routing_type};
}
sub bgpAS {
	my $self = shift;
	return $self->{bgp_as};
}
sub bgpPass {
	my $self = shift;
	return $self->{bgp_pass};
}
sub trafficPolicing {
	my $self = shift;
	return $self->{traffic_policing};
}

sub pe4Address {
	my $self = shift;
	return $self->{pe4_address};
}
sub pe6Address {
	my $self = shift;
	return $self->{pe6_address};
}

sub ce4Address {
	my $self = shift;
	return $self->{ce4_address};
}
sub ce6Address {
	my $self = shift;
	return $self->{ce6_address};
}
sub ipv4Unicast {
	my $self = shift;
	return $self->{ipv4_unicast};
}
sub ipv6Unicast {
	my $self = shift;
	return $self->{ipv6_unicast};
}
sub ipv4Multicast {
	my $self = shift;
	return $self->{ipv4_multicast};
}
sub ipv6Multicast {
	my $self = shift;
	return $self->{ipv6_multicast};
}
#----------------- Private Functions ------------------#
sub get_service_info {
	my $self = shift;
	my $service_id = $self->{'service_id'};

	my $query = "SELECT Services.service_id, Services.name, Services.cust_id
		as contact_id, Services.service_type,
		Services.l2_service_id, Services.l3_service_id, Services.notes,
		Services.portal_statistics, contact_groups.group_name as client_name,
		Service_types.name as service_type_name, Service_types.service_layer,
		DATE_FORMAT(Services.date_in_production,'%Y-%m-%d') as date_in_production,
		DATE_FORMAT(Services.date_out_production,'%Y-%m-%d') as date_out_production, 
		Services.last_updated, Services.status
		FROM Services, contact_groups, Service_types  WHERE Services.cust_id =
		contact_groups.group_id AND Service_types.service_type_id =
		Services.service_type AND 
		Services.service_id = '$service_id'
	";
	my $dbh1 = $self->{'db_handler'};
	my $sth = $dbh1->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	while (my @data = $sth->fetchrow_array()) {
		$self->{service_id} = $data[0];
		$self->{name} = $data[1];
		$self->{contact_id} = $data[2];
		$self->{service_type} = $data[3];
		$self->{l2_service_id} = $data[4];
		$self->{l3_service_id} = $data[5];
		$self->{notes} = $data[6];
		$self->{portal_statistics} = $data[7];
		$self->{contact_name} = $data[8];
		$self->{service_type_name} = $data[9];
		$self->{service_layer} = $data[10];
		$self->{in_production} = $data[11];
		$self->{out_production} = $data[12];
		$self->{last_updated} = $data[13];
		$self->{status} = $data[14];
	}
	$sth->finish();
}


sub get_L3_service_info {
	my $self = shift;
	my $l3_service_id = $self->{'l3_service_id'};
	my $service_id = $self->{'service_id'};

	my $query = "SELECT L3_service_details.routing_type, 
		L3_service_details.logical_router, L3_service_details.IPv4_unicast, 
		L3_service_details.IPv4_multicast, L3_service_details.IPv6_unicast, 
		L3_service_details.IPv6_multicast, L3_service_details.IPv4_prefixes, 
		L3_service_details.IPv6_prefixes, L3_service_details.BCNETrouterAddress4, 
		L3_service_details.CustrouterAddress4, L3_service_details.BCNETrouterAddress6, 
		L3_service_details.CustrouterAddress6,
		L3_service_details.bgp_as, L3_service_details.bgp_pass, L3_service_details.traffic_policing, 
		L3_service_details.router, Devices.name as router_name 
		FROM L3_service_details, Devices
		WHERE l3_service_id = '$l3_service_id' AND Devices.device_id = L3_service_details.router
	" ;
	my $dbh = $self->{'db_handler'};
	my $sth = $dbh->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	while (my @data = $sth->fetchrow_array()) {
		$self->{routing_type} = $data[0];
		$self->{logical_router} = $data[1];
		$self->{ipv4_unicast} = $data[2];
		$self->{ipv4_multicast} = $data[3];
		$self->{ipv6_unicast} = $data[4];
		$self->{ipv6_multicast} = $data[5];
		$self->{ipv4_prefix} = $data[6];
		$self->{ipv6_prefix} = $data[7];
		$self->{pe4_address} = $data[8];
		$self->{ce4_address} = $data[9];
		$self->{pe6_address} = $data[10];
		$self->{ce6_address} = $data[11];
		$self->{bgp_as} = $data[20];
		$self->{bgp_pass} = $data[13];
		$self->{traffic_policing} = $data[14];
		$self->{pe_id} = $data[15];
		$self->{pe_name} = $data[16];
	}

	#Now get Interfaces for this service
        #L3 so only 1 interface
        $query = "SELECT Services_Interfaces.interface_name,
		Services_Interfaces.device, Services_Interfaces.tagged,
		Services_Interfaces.vlan, Services_Interfaces.mtu, 
		Devices.name as router_name
		FROM Services_Interfaces, Devices 
		WHERE service_id = '$service_id' 
		AND Devices.device_id = Services_Interfaces.device";

	my $sth2 = $dbh->prepare($query);
	$sth2->execute() or die "Couldn't execute statement: " . $sth2->errstr;
	while (my @data = $sth2->fetchrow_array()) {
		$self->{interface_name} = $data[0];
		$self->{pe_id} = $data[1];
		$self->{tagged} = $data[2];
		$self->{vlan_id} = $data[3];
		$self->{mtu} = $data[4];
		$self->{pe_name} = $data[5];
	}
	
}

1;

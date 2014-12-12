##########################################################################
# Andree Toonk
# July 16, 2010
##########################################################################
package Device;
use CMDB::Interface;
use warnings;
use strict;

#----------------- constructor ------------------#
sub new {
	my $invocant = shift;
	my $class    = ref($invocant) || $invocant;
	my $self = {
		db_handler => undef,
		device_id => undef,
		interfaces => {},
		@_,
	};
	bless($self, $class);
	if (defined($self->{'db_handler'})) {
		$self->get_device_info($self->{'device_id'});
		$self->get_interface_info($self->{'device_id'});
	} else {
		warn "WARNING: Invalid DB handler\n";
	}
	return $self;
}


#----------------- Get Functions ------------------#
sub getName {
	my $self = shift;
	return $self->{name};
}
sub getAddress {
	my $self = shift;
	return $self->{device_fqdn};
}
sub getSnmpRead {
	my $self = shift;
	return $self->{snmp_ro};
}
sub getReadUser {
	my $self = shift;
	return $self->{ro_user};
}
sub getReadPass {
	my $self = shift;
	return $self->{ro_password};
}
sub getLocationId {
	my $self = shift;
	return $self->{location_id};
}
sub getDeviceTypeId {
	my $self = shift;
	return $self->{type};
}

sub getArchived {
	my $self = shift;
	return $self->{archived};
}

sub getInterfaces {
	my ($self) = @_;
	return %{$self->{interfaces}};
}
#----------------- Private Functions ------------------#
sub get_device_info {
	my $self = shift;
	my $device_id = $self->{'device_id'};

	my $query = "SELECT name, location, type, snmp_ro, 
		ro_user, ro_password, notes, device_fqdn,
		device_oob, archived FROM Devices
		WHERE device_id = '$device_id'
	";
	my $dbh1 = $self->{'db_handler'};
	my $sth = $dbh1->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	while (my @data = $sth->fetchrow_array()) {
		$self->{name} = $data[0];
		$self->{location_id} = $data[1];
		$self->{type} = $data[2];
		$self->{snmp_ro} = $data[3];
		$self->{ro_user} = $data[4];
		$self->{ro_password} = $data[5];
		$self->{notes} = $data[6];
		$self->{device_fqdn} = $data[7];
		$self->{device_oob} = $data[8];
		$self->{archived} = $data[9];
	}
	$sth->finish();
}

sub get_interface_info {
	my $self = shift;
	my $device_id = $self->{'device_id'};

	my $query = "SELECT   	interface_id, ifOperStatus, interface_name,
		interface_descr, interface_alias, disc_interface_speed,
		disc_interface_mtu, disc_interface_index, inbits,
		outbits, inerrors, outerrors
		FROM interfaces
		WHERE interface_device = '$device_id' and active = '1'
	";
	my $dbh1 = $self->{'db_handler'};
	my $sth = $dbh1->prepare($query);
	$sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
	while (my @data = $sth->fetchrow_array()) {
		my $if = Interface->new (
			interface_id => $data[0],
			ifOperStatus => $data[1],
			interface_name => $data[2],
			interface_descr => $data[3],
			interface_alias => $data[4],
			disc_interface_speed => $data[5],
			disc_interface_mtu => $data[6],
			disc_interface_index => $data[7],
			inbits => $data[8],
			outbits => $data[9],
			inerrors => $data[10],
			outerrors => $data[11],
		);
		my $interface = $data[2];
		bless $if, "Interface";
		$self->{interfaces}{$interface} = $if;
	}

	$sth->finish();
}
1;

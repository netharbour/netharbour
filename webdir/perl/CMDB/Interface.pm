##########################################################################
# Andree Toonk
# July 16, 2010
##########################################################################
package Interface;

use warnings;
use strict;

#----------------- constructor ------------------#
sub new {
	my $invocant = shift;
	my $class    = ref($invocant) || $invocant;
	my $self = {
		interface_id => undef,
		@_,
	};
	bless($self, $class);
	return $self;
}


#----------------- Get Functions ------------------#
sub name {
	my $self = shift;
	if (@_) {
		$self->{interface_name} = shift;
	}
	print "In name \n";
	return $self->{name};
}
sub id {
	my $self = shift;
	if (@_) {
		$self->{interface_id} = shift;
	}
	return $self->{interface_id};
}
sub operStatus {
	my $self = shift;
	if (@_) {
		$self->{ifOperStatus} = shift;
	}
	return $self->{ifOperStatus};
}

sub descr {
	my $self = shift;
	if (@_) {
		$self->{interface_descr} = shift;
	}
	return  $self->{interface_descr};
}

sub alias {
	my $self = shift;
	if (@_) {
		$self->{interface_alias} = shift;
	}
	return $self->{interface_alias} ;
}

sub speed {
	my $self = shift;
	if (@_) {
		$self->{disc_interface_speed} = shift;
	}
	return $self->{disc_interface_speed};
}
sub mtu {
	my $self = shift;
	if (@_) {
		$self->{disc_interface_mtu} = shift;
	}
	return $self->{disc_interface_mtu};
}

sub duplex {
	my $self = shift;
	if (@_) {
		$self->{disc_interface_mtu} = shift;
	}
	return $self->{disc_interface_mtu};
}
sub type  {
	my $self = shift;
	if (@_) {
		$self->{disc_interface_type} = shift;
	}
	return $self->{disc_interface_type};
}

sub ifIndex  {
	my $self = shift;
	if (@_) {
		$self->{disc_interface_index} = shift;
	}
	return $self->{disc_interface_index};
}
sub inBits  {
	my $self = shift;
	if (@_) {
		$self->{inbits} = shift;
	}
	return $self->{inbits};
}
sub outBits  {
	my $self = shift;
	if (@_) {
		$self->{outbits} = shift;
	}
	return $self->{outbits};
}
sub inErrors  {
	my $self = shift;
	if (@_) {
		$self->{inerrors} = shift;
	}
	return $self->{inerrors};
}
sub outErrors  {
	my $self = shift;
	if (@_) {
		$self->{outerrors} = shift;
	}
	return $self->{outerrors};
}
1;

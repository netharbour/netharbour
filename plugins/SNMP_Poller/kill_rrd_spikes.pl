#!/usr/bin/env perl

# This is a  modified version of removespikes.pl found at 
# http://oss.oetiker.ch/rrdtool/pub/contrib/
# original: matapicos v2.2 - Vins Vilaplana <vins at terra dot es)
#
# This version looks at absolute numbers, instead of percentages
# Andree Toonk, March 2011
#

use strict;
use warnings;
use Getopt::Std;

my (@dump,%exp,@cols,@dbak,%tot,%por);
my ($linea,$linbak,$lino,$cdo,$tresto,$tstamp,$a,$b,$cont);


#-------------------------------------------------------------------------------
#    Global var initializations
#-------------------------------------------------------------------------------

my $usage = <<"EOF";
kill_rrd_spikes.pl
usage:   $0 -m <max> -f <file> [-v]
example: $0 -m 10G - f input.rrd

[-h]          : Print this message

[-m] <max>    : maximum allowed value. All values higer than the supplied value will be chopped
              : Mandatory argument. Examples to chop at 1G:
	      : -m 1G or -m 1000M, -M 1000000K
	      : script understands the following units: k, m, g, t, p (case insensitive)

[-f] <file>   : rrd file to chop
              : Mandatory argument.

[-v] 	      : Verbose mode

Andree Toonk: andree.toonk\@bc.net  
March 2011

EOF


#-------------------------------------------------------------------------------
# Check the usage
#-------------------------------------------------------------------------------
my $opt_string = 'h:m:f:';
my %opt;
getopts( "$opt_string", \%opt ) or die $usage;
die $usage if (defined $opt{h});
die $usage if (!defined $opt{m});
die $usage if (!defined $opt{f});

my $max = get_max($opt{m});
if (!defined($max)) {
	die "Unable to parse -m $max\n";
}

my $rrdfile = $opt{f};

# temporary filename:
# safer this way, so many users can run this script simultaneusly
my $tempfile="/tmp/matapicos.dump.$$"; 

###########################################################################
# Dump the rrd database to the temporary file (as XML)
`rrdtool dump '$rrdfile' > $tempfile`;


###########################################################################
# Open the XML dump, and create a new one removing the spikes:
# If the found value is higher then specified cap, we use the previous 
# value as the new value.

open(FICH,"<$tempfile") || 
   die "$0: Cannot open $tempfile for reading: $!-$@";
open(FSAL,">$tempfile.xml")  || 
   die "$0: Cannot open $tempfile.xml for writing: $!-$@";

$linbak='';
$cont=0;
while (<FICH>) {
	chomp;
	$linea=$_;
	$cdo=0;
	my $verbose_string ="";

	if ($linea=~/^(.*)<row>/) { $tstamp=$1; }     # Grab timestamp
	if ($linea=~/(<row>.*)$/) { $tresto=$1; }     # grab rest-of-line :-)
	if (/<v>\s?\d\.\d+e.(\d+)\s?<\/v>/) {           # are there DS's?
	@dump=split(/<\/v>/, $tresto);              # split them
	if ($linbak ne '') {
		for ($lino=0;$lino<=$#dump-1;$lino++) {   # for each DS:
			if ($dump[$lino]=~/(\d\.\d+)e.(\d+)\s?/) { # grab number (and not a NaN)
				$a=$2*1;                              # and exponent
				my $num=$1*1;                              # and exponent
				my $exp=$2*1;                              # and exponent
				$b=substr("0$lino",-2).":$2";         # calculate the max percentage of this DS
				my $speed = ($num * 8) * 10 ** $exp;
				if ((defined($speed)) && ($speed > $max)) {
					$linea=$tstamp.$linbak;             # we dump it.
					$cdo=1;
					$tresto=$linbak;
					$speed = ($num * 8) * 10 ** $exp;
					$speed = format_number($speed,2);
					
					$verbose_string =  $speed."bps";

				}
			}
		}
	}
	$linbak=$tresto;
	if ($cdo==1) { 
		print "Chopping peak $verbose_string at $tstamp\n" ;
		$cont++; }
	}
  
	print FSAL "$linea\n";
}
close FICH;
close FSAL;

###########################################################################
# Cleanup and move new file to the place of original one
# and original one gets backed up.
if ($cont == 0) { print "No peaks found.!\n"; }
else {
  rename("$rrdfile","$rrdfile.old");
  $lino="rrdtool restore $tempfile.xml '$rrdfile'";
  system($lino);
  die "$0: Unable to execute the rrdtool restore on $rrdfile - $! - $@\n" if $? != 0;
}

# cleans up the files created
unlink("$tempfile");
unlink("$tempfile.xml");

sub format_number {
        my ($number,$digits) = @_;
        if (!defined($digits)) {
                $digits = 0;
        }
        my ($num, $divedby,$legend);
        if ($number >= 1 * 10 ** 15) {
                $legend = "P";
                $divedby = 10 ** 15;
        }elsif ($number >= 1 * 10 ** 12) {
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

sub get_max {
	my $max_string = shift;
	my $num = undef;
	my $multiplier = undef;
	if ($max_string =~ /^(\d+)(\w)?$/) {
		$num = $1;
		$multiplier = $2;
	} elsif ($max_string =~ /^(\d+\.\d+)(\w+)?$/) {
		$num = $1;
		$multiplier = $2;
	}
	if (!defined($num)) {
		return undef;
	}
	if (!defined($multiplier)) {
		return $num;
	}
	if (lc($multiplier) eq "k") {
		return $num * (10 ** 3);
	}
	if (lc($multiplier) eq "m") {
		return $num * (10 ** 6);
	}
	if (lc($multiplier) eq "g") {
		return $num * (10 ** 9);
	}
	if (lc($multiplier) eq "t") {
		return $num * (10 ** 12);
	}
	if (lc($multiplier) eq "p") {
		return $num * (10 ** 15);
	}
	return undef;
}


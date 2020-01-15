#!/usr/bin/env perl

use strict;
use warnings;
use DBI;

## Get config
use lib "/../../library";
use CMDB_Config;
my %config = CMDB_Config::get_config();


my $connectionInfo="DBI:mysql:database=$config{db_name};$config{db_host}:$config{db_port}";
my $dbh = DBI->connect($connectionInfo,$config{db_user},$config{db_pass}) or die("Could not connect to Mysql!");

my $snmpwalk = $config{'path_snmpwalk'};
my $snmpget = $config{'path_snmpget'};
my $rrdupdate = $config{'path_rrdupdate'};
my $rrdtool = $config{'path_rrdtool'};
my $rrddir = $config{'path_rrddir'};

my $device = $ARGV[0];    # name
my $community = "<snip>";    # community

my $line;
my @results;
my %isp;  
my $cli;  
my %counternames;  
my %jnxFWBytes;  
my %ASns;  
my %in;  
my %out;  



# collect counter name  via jnxScuStatsClName
$cli = "$snmpwalk -v 2c -On -c $community $device 1.3.6.1.4.1.2636.3.5.1.1.2 ";
@results = `$cli`;
foreach $line (@results) {
        chomp $line;
#.1.3.6.1.4.1.2636.3.5.1.1.2.83.73.88.45.65.67.67.84.45.73.78.0.0.0.0.0.0.0.0.0.0.0.0.0.10.65.83.51.54.53.54.49.45.105.110.0.0.0.0.0.0.0.0.0.0.0.0.0 = STRING: "AS36561-in"


        if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.5\.1\.1\.2\.(.+) = STRING: \"(.+)\"/) {
               $counternames{$1} = $2; 
		#print "Customer name $1 == $2\n";
        }
        else {
                print "no match for $line\n";
        }
}


# Now collect customer name via jnxFWBytes 
$cli = "$snmpwalk -v 2c -On -c $community $device 1.3.6.1.4.1.2636.3.5.1.1.5 ";
@results = `$cli`;
foreach $line (@results) {
        chomp $line;
	#.1.3.6.1.4.1.2636.3.5.1.1.5.83.73.88.45.65.67.67.84.45.73.78.0.0.0.0.0.0.0.0.0.0.0.0.0.10.65.83.51.54.53.54.49.45.105.110.0.0.0.0.0.0.0.0.0.0.0.0.0 = Counter64: 54023895
 
        if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.5\.1\.1\.5\.(.+) = Counter64: (\d+)/) {
               $jnxFWBytes{$1} = $2; 
		#print "Customer name $4 == $5\n";
        }
        else {
                print "no match for $line\n";
        }
}

while ( my ($key, $value) = each(%counternames) ) {
	#print "Name $key = $value\n";
	#print "Value $key = $jnxFWBytes{$key}\n";
	#print "$value === $jnxFWBytes{$key}\n";
	my $originAS = undef;
	if ($value  =~ /(AS(\d+))-(.+)/)  {
		$originAS = $1;
		my $dir = lc($3);
		$ASns{$originAS} = $originAS;
		if ($dir eq "in") {
			$in{$originAS} = $key;
		} elsif ($dir eq "out") {
			$out{$originAS} = $key;
		}
	}
	if ($value  =~ /(AS-(\S+))-(.+)/)  {
		$originAS = $1;
		my $dir = lc($3);
		$ASns{$originAS} = $originAS;
		if ($dir eq "in") {
			$in{$originAS} = $key;
		} elsif ($dir eq "out") {
			$out{$originAS} = $key;
		}
	}
}

# now you have a has ASns, with all ASNs you have filters for

while ( my ($originAS, $value) = each(%ASns) ) {
	## RRD Stuff
        #first check if rrd file exist, if not create
	my $rrd_file = $originAS ."_". $device .".rrd";
        $rrd_file =~ s/([\$\#\@\\\/\s])/-/g;
        create_rrd_archive($rrd_file,"1000000000") if ! -e "$rrddir/$rrd_file";
	my $inref = $in{$originAS};
	my $outref = $out{$originAS};
	my $update = "N:$jnxFWBytes{$inref}:$jnxFWBytes{$outref}::::::";
        $cli = "$rrdupdate \"$rrddir/$rrd_file\" $update";
        #print "$cli\n";
        system($cli);
}


sub create_rrd_archive {
        # search and replace special chars
        my $interface_file_name = shift;
        my $ifspeed = shift;
        my $max;
        if ($ifspeed == 0) {
                $max = "U";
        } else {
                $max = ($ifspeed / 8);
        }
        #special chars replaced by a -, unix doesnt like / in filename
        #64 bits max is 18446744073709551616
        my $cli = " $rrdtool create \'$rrddir/$interface_file_name\' \\
      DS:INOCTETS:COUNTER:600:0:$max \\
      DS:OUTOCTETS:COUNTER:600:0:$max \\
      DS:INERRORS:COUNTER:600:0:$max \\
      DS:OUTERRORS:COUNTER:600:0:$max \\
      DS:INUCASTPKTS:COUNTER:600:0:$max \\
      DS:OUTUCASTPKTS:COUNTER:600:0:$max \\
      DS:INNUCASTPKTS:COUNTER:600:0:$max \\
      DS:OUTNUCASTPKTS:COUNTER:600:0:$max \\
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



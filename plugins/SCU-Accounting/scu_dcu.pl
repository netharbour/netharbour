#!/usr/bin/perl

use strict;
use warnings;
use DBI;

use Config::Simple;

my $cfg = new Config::Simple('../etc/perl_config.pl');

my $db_host =  $cfg->param('db_host');
my $db_port =  $cfg->param('db_port');
my $db_name =  $cfg->param('db_name');
my $db_user =  $cfg->param('db_user');
my $db_pass =  $cfg->param("db_pass");

my $connectionInfo="DBI:mysql:database=$db_name;$db_host:$db_port";
my $dbh = DBI->connect($connectionInfo,$db_user,$db_pass) or die("Could not connect to Mysql!");

my $snmpwalk = $cfg->param('snmpwalk');
my $snmpget = $cfg->param('snmpget');
my $rrdupdate = $cfg->param('rrdupdate');
my $rrdtool = $cfg->param('rrdtool');

#my $rrddir = $cfg->param('rrddir') . "/accounting";
my $rrddir = "/var/www/html/cmdb/rrd-files/accounting";

if ($#ARGV != 1) {
      print "Please provide router name and community as command line argument\n";
      exit;
};


my $deviceid = $ARGV[0];    # name
my $community = $ARGV[1];    # community

my $line;
my @results;
my %isp;
my $cli;
my %custumernames;
my %scu_profile;
my %dest_name;
my %jnxScuStatsBytes;
my %jnxDcuStatsBytes;

#my $deviceid = get_device_id($device);
my $fqdn = get_device_fqdn($deviceid);


# collect customer name  via jnxScuStatsClName
$cli = "$snmpwalk -v 2c -On -c $community $fqdn  .1.3.6.1.4.1.2636.3.16.1.1.1.6";
print "$cli\n";
@results = `$cli`;
print "collect customer name  via jnxScuStatsClName\n";
foreach $line (@results) {
      chomp $line;
      #.1.3.6.1.4.1.2636.3.16.1.1.1.6.82.1.8.84.101.108.111.115.69.110.103 = STRING: "TelosEng"

      if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.16\.1\.1\.1\.6\.(\d+)\.(\d+)\.(\d+)\.(\d+)\.(.+) = STRING: \"(.+)\"/) {
            $custumernames{$5} = $6;
            $isp{$1} ='';
            print "Customer name $5 == $6\n";
      }
      else {
            #print "no match for $line\n";
      }
}


# Now collect customer name via jnxDcuStatsClName
$cli = "$snmpwalk -v 2c -On -c $community $fqdn .1.3.6.1.4.1.2636.3.6.2.1.6 ";
@results = `$cli`;
print "collect customer name  via jnxDcuStatsClName\n";
foreach $line (@results) {
      chomp $line;
      #.1.3.6.1.4.1.2636.3.6.2.1.6.155.1.9.69.109.105.108.121.67.97.114.114 = STRING: "EmilyCarr"

      if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.6\.2\.1\.6\.(\d+)\.(\d+)\.(\d+)\.(.+) = STRING: \"(.+)\"/) {
            $isp{$1} ='';
            $custumernames{$4} = $5;
            print "Customer name $4 == $5\n";
      }
      else {
            #print "no match for $line\n";
      }
}

#  collect ISP name by if index
print " collect ISP name by if index\n";
while ( my ($isp_ifindex, $isp_name) = each(%isp) ) {
      $cli = "$snmpwalk -v 2c -Onqv -c $community $fqdn  .1.3.6.1.2.1.31.1.1.1.18.$isp_ifindex";
      my @result = `$cli`;
      $isp_name =  $result[0];
      chomp $isp_name;
      $isp{$isp_ifindex}= $isp_name;
}

# collect customer name  via jnxScuStatsClName
$cli = "$snmpwalk -v 2c -On -c $community $fqdn  .1.3.6.1.4.1.2636.3.16.1.1.1.6";
#jnxScuStatsBytes
#jnxScuStatsBytes
#Example:
#.1.3.6.1.4.1.2636.3.16.1.1.1.5.155.1.5.66.67.78.69.84 = Counter64: 251064025499
#IFINDEX = 155
#1.
#3. Number of chars
#66.67.78.69.84 = BCNET
#The name is in ASCII. The other way to get that is using:
#.1.3.6.1.4.1.2636.3.16.1.1.1.6.IFINDEX.1.NUMBER_OF_CHARS."ASCII"
#.1.3.6.1.4.1.2636.3.16.1.1.1.6.155.1.5.66.67.78.69.84 = STRING: "BCNET"

$cli = "$snmpwalk -v 2c -On -c $community $fqdn   .1.3.6.1.4.1.2636.3.16.1.1.1.5";
@results = `$cli`;

foreach $line (@results) {
      chomp $line;
      #.1.3.6.1.4.1.2636.3.16.1.1.1.5.155.1.5.66.67.78.69.84 = Counter64: 251064025499


      if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.16\.1\.1\.1\.5\.(\d+)\.1\.(\d+)\.(.+) = Counter64:\s(\d+)/) {
            #print "Custname $3: is $custumernames{$3} and ispname is  $isp{$1}\n";
            my $title = $custumernames{$3} . " -- " . $isp{$1};
            $scu_profile{$title} = $custumernames{$3};
            $jnxScuStatsBytes{$title} = $4;
            $dest_name{$title} = $isp{$1};
            #print "jnxScuStatsBytes $3 == $4\n";
      }
      else {
            #print "no match for $line\n";
      }
}

#jnxDcuStatsBytes
#Example:.1.3.6.1.4.1.2636.3.6.2.1.5.155.1.4.66.67.73.84 = Counter64: 51574082779709
#IFINDEX = 155
#1.
#3. Number of chars
#66.67.73.84 = BCIT

$cli = "$snmpwalk -v 2c -On -c $community $fqdn   1.3.6.1.4.1.2636.3.6.2.1.5";
@results = `$cli`;

foreach $line (@results) {
      chomp $line;
      if ( $line =~ /\.1\.3\.6\.1\.4\.1\.2636\.3\.6\.2\.1\.5\.(\d+)\.1\.(\d+)\.(.+) = Counter64:\s(\d+)/) {
            my $title = $custumernames{$3} . " -- " . $isp{$1};
            $dest_name{$title} = $isp{$1};
            $scu_profile{$title} = $custumernames{$3};
            $jnxDcuStatsBytes{$title} = $4;
            #print "jnxDcuStatsBytes $3 == $4\n";
      }
      else {
            #print "no match for $line\n";
      }
}

print "Received all stats\nNow updating RRD files";

while ( my ($title, $value) = each(%jnxScuStatsBytes) ) {
      my $file_name = "deviceid".$deviceid ."_"."$title.rrd";
      if (! -e "$rrddir/$file_name") {
            create_rrd_archive($file_name) if ! -e "$rrddir/$file_name";
            insert_db($scu_profile{$title},$dest_name{$title},$title,$file_name,$deviceid);
      }

      if ((defined($value)) && (defined($jnxDcuStatsBytes{$title}))) {
            my $update = "N:$value:$jnxDcuStatsBytes{$title}";

            my $cli = "$rrdupdate \"$rrddir/$file_name\" $update";
            system($cli);
            update_db($file_name);
      }

      print " SCU $title = $value\n";
      print " DCU $title = $jnxDcuStatsBytes{$title}\n";
}


sub create_rrd_archive {
      # search and replace special chars
      my $file_name = shift;
      #special chars replaced by a -, unix doesnt like / in filename
      #64 bits max is 18446744073709551616

      #  105120 samples of 5 minutes  (365 days = 12(1hour) * 24(1day) *365(1year) )
      #  2920 samples of 6 hour ( 2 years of 1 hour samples. 4 * 365 * 2 = 2920)
      # 12500000000 = 100gbs=  12,5GBs

      my $cli = " $rrdtool create \'$rrddir/$file_name\' \\
      	DS:INOCTETS:COUNTER:600:0:12500000000 \\
      	DS:OUTOCTETS:COUNTER:600:0:12500000000 \\
      	RRA:AVERAGE:0.5:1:105120 \\
      	RRA:AVERAGE:0.5:72:2920 \\
      	RRA:MAX:0.5:1:105120 \\
      	RRA:MAX:0.5:72:2920 ";

      system `$cli`;
      print "$cli\n";

}


sub get_device_id {
      my $device = shift;
      my $deviceid = undef;;
      my $query = "select device_id from Devices where name = '$device' " ;
      my $sth = $dbh->prepare($query);
      $sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
      while (my @data = $sth->fetchrow_array()) {
            $deviceid = $data[0];
      }
      $sth->finish();
      return $deviceid;

}

sub get_device_fqdn {
      my $device_id = shift;
      my $fqdn = undef;;
      my $query = "select device_fqdn from Devices where device_id = '$device_id' " ;
      print "$query\n";
      my $sth = $dbh->prepare($query);
      $sth->execute() or die "Couldn't execute statement: " . $sth->errstr;
      while (my @data = $sth->fetchrow_array()) {
            $fqdn = $data[0];
      }
      $sth->finish();
      return $fqdn;
}

sub insert_db {
      my $scu_profile = shift;
      my $dest = shift;
      my $title = shift;
      my $file_name = shift;
      my $device_id = shift;
      my $query = "INSERT INTO accounting_sources SET
		device_id = '$device_id',
		title = '$title',
		scu_profile = '$scu_profile',
		destination = '$dest',
		file = '$file_name',
		last_update = NOW(),
		created = NOW()";
      my $sth = $dbh->prepare($query);
      $sth->execute() or warn "Couldn't execute statement: " . $sth->errstr;
      $sth->finish();
}

sub update_db {
      my $file_name = shift;
      my $query = "update accounting_sources SET
		last_update = NOW()
		WHERE
		file = '$file_name'";
      my $sth = $dbh->prepare($query);
      $sth->execute() or warn "Couldn't execute statement: " . $sth->errstr;
      $sth->finish();
}
FROM ubuntu:trusty
RUN echo 'Acquire::GzipIndexes "true";\
          Acquire::CompressionTypes::Order:: "gz";' >> /etc/apt/apt.conf.d/02compress-indexes
RUN echo ' Dir::Cache {\
   srcpkgcache "";\
   pkgcache "";\
 }' >> /etc/apt/apt.conf.d/02nocache

RUN apt-get update \
    && apt-get upgrade -y -o Dpkg::Options::="--force-confold" \
    && apt-get install -y --no-install-recommends \
            build-essential \
            rrdtool \
            snmp \
            rancid \
            apache2 \
            php5 \
            php5-mysql \
	    php5-ldap \
	    php5-cli \
            libmysqlclient-dev \
	    mysql-client-5.6 \
            software-properties-common
RUN apt-add-repository "deb http://archive.ubuntu.com/ubuntu $(lsb_release -sc) multiverse"
RUN apt-get update \
    && apt-get install -y --no-install-recommends snmp-mibs-downloader \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*
RUN cpan -i DBI Config::Simple DBD::mysql

RUN echo "#Empty file to enable MIBs" > /etc/snmp/snmp.conf

ADD docker_cmdb/000-default.conf /etc/apache2/sites-enabled/000-default.conf
ADD docker_cmdb/php.ini /etc/php5/apache2/php.ini
ADD webdir /var/www/netharbour
ADD docker_cmdb/cmdb.conf /var/www/netharbour/config/cmdb.conf
RUN mkdir -p /var/www/netharbour/rrd-files/
RUN chown -R www-data /var/www/netharbour && chmod -R a+rx /var/www/netharbour
ADD docker_cmdb/crontab /etc/cron.d/crontab

#RUN echo "America/Denver" > /etc/timezone && dpkg-reconfigure --frontend noninteractive tzdata
EXPOSE 80
ADD docker_cmdb/start.sh /start.sh
CMD /start.sh

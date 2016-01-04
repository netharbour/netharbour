#!/bin/sh
nohup /usr/sbin/cron -f &
nohup /usr/bin/crontab /etc/cron.d/crontab & 
apachectl -DFOREGROUND

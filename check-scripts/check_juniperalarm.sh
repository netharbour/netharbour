#!/bin/bash
#
#   File name	 : check_juniperalarm.sh
#
#   Created by	 : R.W. Felix - Netherlands ( for any questions contact me on info@rubenfelix.nl )
#   Created date : 28-05-2013
#   Version	 : 1.0
#
#   Information  : Check via SNMP the Yellow/Red status of the SNMP HOST
#
####################################################################

## SNMP hostname
## SNMP C
## SNMP V

SNMP_HOST=${1}
SNMP_COMM=${2}
SNMP_VER=${3}

## Error if no ARG is given

if [[ $# -eq 0 ]]
then
	echo "UNKNOWN - No ARG = hostname (1), SNMP COMM (2), SNMP VER (3) is given"
        exit 3
else

	YELLOWALARM=$(snmpget -c ${SNMP_COMM} -v ${SNMP_VER} ${SNMP_HOST} 1.3.6.1.4.1.2636.3.4.2.2.2.0 | awk '{ print $4 }')
	REDALARM=$(snmpget -c ${SNMP_COMM} -v ${SNMP_VER} ${SNMP_HOST} 1.3.6.1.4.1.2636.3.4.2.3.2.0 | awk '{ print $4 }')

	## Check if yellow- / redalarm is 0

	if [[ ${YELLOWALARM} == 0 && ${REDALARM} == 0 ]]
	then
		echo "OK - Everything is fine ( Y: ${YELLOWALARM} / R: ${REDALARM} )"
		exit 0
	else

		if [[ ${YELLOWALARM} != 0 && ${REDALARM} != 0 ]]
		then
			echo "CRITICAL -  Yellow-/Redalarm problems ( Y: ${YELLOWALARM} / R: ${REDALARM} )"
			exit 2

		elif [[ ${YELLOWALARM} != 0 ]]
		then
			echo "WARNING - Yellow Alarm Count: ${YELLOWALARM}"
			exit 1

		elif [[ ${REDALARM} != 0 ]]
		then
			echo "CRITICAL - Red Alarm Count: ${REDALARM}"
			exit 2

		else
			echo "UNKNOWN - Difficult output to understand ;-)"
			exit 3

		fi
	fi
fi

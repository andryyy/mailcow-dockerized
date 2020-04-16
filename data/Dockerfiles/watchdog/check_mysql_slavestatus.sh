#!/bin/bash
#########################################################################
# Script:       check_mysql_slavestatus.sh                              #
# Author:       Claudio Kuenzler www.claudiokuenzler.com                #
# Purpose:      Monitor MySQL Replication status with Nagios            #
# Description:  Connects to given MySQL hosts and checks for running    #
#               SLAVE state and delivers additional info                #
# Original:     This script is a modified version of                    #
#               check mysql slave sql running written by dhirajt        #
# Thanks to:    Victor Balada Diaz for his ideas added on 20080930      #
#               Soren Klintrup for stuff added on 20081015              #
#               Marc Feret for Slave_IO_Running check 20111227          #
#               Peter Lecki for his mods added on 20120803              #
#               Serge Victor for his mods added on 20131223             #
#               Omri Bahumi for his fix added on 20131230               #
#               Marc Falzon for his option mods added on 20190822       #
#               Andreas Pfeiffer for adding socket option on 20190822   #
# History:                                                              #
# 2008041700 Original Script modified                                   #
# 2008041701 Added additional info if status OK                         #
# 2008041702 Added usage of script with params -H -u -p                 #
# 2008041703 Added bindir variable for multiple platforms               #
# 2008041704 Added help because mankind needs help                      #
# 2008093000 Using /bin/sh instead of /bin/bash                         #
# 2008093001 Added port for MySQL server                                #
# 2008093002 Added mysqldir if mysql binary is elsewhere                #
# 2008101501 Changed bindir/mysqldir to use PATH                        #
# 2008101501 Use $() instead of `` to avoid forks                       #
# 2008101501 Use ${} for variables to prevent problems                  #
# 2008101501 Check if required commands exist                           #
# 2008101501 Check if mysql connection works                            #
# 2008101501 Exit with unknown status at script end                     #
# 2008101501 Also display help if no option is given                    #
# 2008101501 Add warning/critical check to delay                        #
# 2011062200 Add perfdata                                               #
# 2011122700 Checking Slave_IO_Running                                  #
# 2012080300 Changed to use only one mysql query                        #
# 2012080301 Added warn and crit delay as optional args                 #
# 2012080302 Added standard -h option for syntax help                   #
# 2012080303 Added check for mandatory options passed in                #
# 2012080304 Added error output from mysql                              #
# 2012080305 Changed from 'cut' to 'awk' (eliminate ws)                 #
# 2012111600 Do not show password in error output                       #
# 2013042800 Changed PATH to use existing PATH, too                     #
# 2013050800 Bugfix in PATH export                                      #
# 2013092700 Bugfix in PATH export                                      #
# 2013092701 Bugfix in getopts                                          #
# 2013101600 Rewrite of threshold logic and handling                    #
# 2013101601 Optical clean up                                           #
# 2013101602 Rewrite help output                                        #
# 2013101700 Handle Slave IO in 'Connecting' state                      #
# 2013101701 Minor changes in output, handling UNKWNON situations now   #
# 2013101702 Exit CRITICAL when Slave IO in Connecting state            #
# 2013123000 Slave_SQL_Running also matched Slave_SQL_Running_State     #
# 2015011600 Added 'moving' check to catch possible connection issues   #
# 2015011900 Use its own threshold for replication moving check         #
# 2019082200 Add support for mysql option file                          #
# 2019082201 Improve password security (remove from mysql cli)          #
# 2019082202 Added socket parameter (-S)                                #
# 2019082203 Use default port 3306, makes -P optional                   #
# 2019082204 Fix moving subcheck, improve documentation                 #
#########################################################################
# Usage: ./check_mysql_slavestatus.sh (-o file|(-H dbhost [-P port]|-S socket) -u dbuser -p dbpass) [-s connection] [-w integer] [-c integer] [-m integer]
#########################################################################
help="\ncheck_mysql_slavestatus.sh (c) 2008-2019 GNU GPLv2 licence
Usage: $0 (-o file|(-H dbhost [-P port]|-S socket) -u username -p password) [-s connection] [-w integer] [-c integer] [-m]\n
Options:\n-o Path to option file containing connection settings (e.g. /home/nagios/.my.cnf). Note: If this option is used, -H, -u, -p parameters will become optional\n-H Hostname or IP of slave server\n-P MySQL Port of slave server (optional, defaults to 3306)\n-u Username of DB-user\n-p Password of DB-user\n-S database socket\n-s Connection name (optional, with multi-source replication)\n-w Replication delay in seconds for Warning status (optional)\n-c Replication delay in seconds for Critical status (optional)\n-m Threshold in seconds since when replication did not move (compares the slaves log position)\n
Attention: The DB-user you type in must have CLIENT REPLICATION rights on the DB-server. Example:\n\tGRANT REPLICATION CLIENT on *.* TO 'nagios'@'%' IDENTIFIED BY 'secret';"

STATE_OK=0              # define the exit code if status is OK
STATE_WARNING=1         # define the exit code if status is Warning (not really used)
STATE_CRITICAL=2        # define the exit code if status is Critical
STATE_UNKNOWN=3         # define the exit code if status is Unknown
export PATH=$PATH:/usr/local/bin:/usr/bin:/bin # Set path
crit="No"               # what is the answer of MySQL Slave_SQL_Running for a Critical status?
ok="Yes"                # what is the answer of MySQL Slave_SQL_Running for an OK status?
port="-P 3306"          # on which tcp port is the target MySQL slave listening?

for cmd in mysql awk grep expr [
do
 if ! `which ${cmd} &>/dev/null`
 then
  echo "UNKNOWN: This script requires the command '${cmd}' but it does not exist; please check if command exists and PATH is correct"
  exit ${STATE_UNKNOWN}
 fi
done

# Check for people who need help
#########################################################################
if [ "${1}" = "--help" -o "${#}" = "0" ];
        then
        echo -e "${help}";
        exit 1;
fi

# Important given variables for the DB-Connect
#########################################################################
while getopts "H:P:u:p:S:s:w:c:o:m:h" Input;
do
        case ${Input} in
        H)      host="-h ${OPTARG}";slavetarget=${OPTARG};;
        P)      port="-P ${OPTARG}";;
        u)      user="-u ${OPTARG}";;
        p)      password="${OPTARG}"; export MYSQL_PWD="${OPTARG}";;
        S)      socket="-S ${OPTARG}";;
        s)      connection=\"${OPTARG}\";;
        w)      warn_delay=${OPTARG};;
        c)      crit_delay=${OPTARG};;
        o)      optfile="--defaults-extra-file=${OPTARG}";;
        m)      moving=${OPTARG};;
        h)      echo -e "${help}"; exit 1;;
        \?)     echo "Wrong option given. Check help (-h, --help) for usage."
                exit 1
                ;;
        esac
done

# Check if we can write to tmp
#########################################################################
test -w /tmp && tmpfile="/tmp/mysql_slave_${slavetarget}_pos.txt"

# Connect to the DB server and check for informations
#########################################################################
# Check whether all required arguments were passed in (either option file or full connection settings)
if [[ -z "${optfile}" && -z "${host}" && -z "${socket}" ]]; then
  echo -e "Missing required parameter(s)"; exit ${STATE_UNKNOWN}
elif [[ -n "${host}" && (-z "${user}" || -z "${password}") ]]; then
  echo -e "Missing required parameter(s)"; exit ${STATE_UNKNOWN}
elif [[ -n "${socket}" && (-z "${user}" || -z "${password}") ]]; then
  echo -e "Missing required parameter(s)"; exit ${STATE_UNKNOWN}
fi

# Connect to the DB server and store output in vars
if [[ -n $socket ]]; then 
  ConnectionResult=$(mysql ${optfile} ${socket} ${user} -e "show slave ${connection} status\G" 2>&1)
else
  ConnectionResult=$(mysql ${optfile} ${host} ${port} ${user} -e "show slave ${connection} status\G" 2>&1)
fi

if [ -z "`echo "${ConnectionResult}" |grep Slave_IO_State`" ]; then
        echo -e "CRITICAL: Unable to connect to server"
        exit ${STATE_CRITICAL}
fi
check=`echo "${ConnectionResult}" |grep Slave_SQL_Running: | awk '{print $2}'`
checkio=`echo "${ConnectionResult}" |grep Slave_IO_Running: | awk '{print $2}'`
masterinfo=`echo "${ConnectionResult}" |grep  Master_Host: | awk '{print $2}'`
delayinfo=`echo "${ConnectionResult}" |grep Seconds_Behind_Master: | awk '{print $2}'`
readpos=`echo "${ConnectionResult}" |grep Read_Master_Log_Pos: | awk '{print $2}'`
execpos=`echo "${ConnectionResult}" |grep Exec_Master_Log_Pos: | awk '{print $2}'`

# Output of different exit states
#########################################################################
if [ ${check} = "NULL" ]; then
echo "CRITICAL: Slave_SQL_Running is answering NULL"; exit ${STATE_CRITICAL};
fi

if [ ${check} = ${crit} ]; then
echo "CRITICAL: ${host}:${port} Slave_SQL_Running: ${check}"; exit ${STATE_CRITICAL};
fi

if [ ${checkio} = ${crit} ]; then
echo "CRITICAL: ${host} Slave_IO_Running: ${checkio}"; exit ${STATE_CRITICAL};
fi

if [ ${checkio} = "Connecting" ]; then
echo "CRITICAL: ${host} Slave_IO_Running: ${checkio}"; exit ${STATE_CRITICAL};
fi

if [ ${check} = ${ok} ] && [ ${checkio} = ${ok} ]; then
 # Delay thresholds are set
 if [[ -n ${warn_delay} ]] && [[ -n ${crit_delay} ]]; then
  if ! [[ ${warn_delay} -gt 0 ]]; then echo "Warning threshold must be a valid integer greater than 0"; exit $STATE_UNKNOWN; fi
  if ! [[ ${crit_delay} -gt 0 ]]; then echo "Warning threshold must be a valid integer greater than 0"; exit $STATE_UNKNOWN; fi
  if [[ -z ${warn_delay} ]] || [[ -z ${crit_delay} ]]; then echo "Both warning and critical thresholds must be set"; exit $STATE_UNKNOWN; fi
  if [[ ${warn_delay} -gt ${crit_delay} ]]; then echo "Warning threshold cannot be greater than critical"; exit $STATE_UNKNOWN; fi

  if [[ ${delayinfo} -ge ${crit_delay} ]]
  then echo "CRITICAL: Slave is ${delayinfo} seconds behind Master | delay=${delayinfo}s"; exit ${STATE_CRITICAL}
  elif [[ ${delayinfo} -ge ${warn_delay} ]]
  then echo "WARNING: Slave is ${delayinfo} seconds behind Master | delay=${delayinfo}s"; exit ${STATE_WARNING}
  else 
    # Everything looks OK here but now let us check if the replication is moving
    if [[ -n ${moving} ]] && [[ -n ${tmpfile} ]] && [[ $readpos -eq $execpos ]]
    then  
      #echo "Debug: Read pos is $readpos - Exec pos is $execpos" 
      # Check if tmp file exists
      curtime=`date +%s`
      if [[ -w $tmpfile ]] 
      then 
        tmpfiletime=`date +%s -r $tmpfile`
        if [[ `expr $curtime - $tmpfiletime` -gt ${moving} ]]
        then
          exectmp=`cat $tmpfile`
          #echo "Debug: Exec pos in tmpfile is $exectmp"
          if [[ $exectmp -eq $execpos ]]
          then 
            # The value read from the tmp file and from db are the same. Replication hasnt moved!
            echo "WARNING: Slave replication has not moved in ${moving} seconds. Manual check required."; exit ${STATE_WARNING}
          else 
            # Replication has moved since the tmp file was written. Delete tmp file and output OK.
            rm $tmpfile
            echo "OK: Slave SQL running: ${check} Slave IO running: ${checkio} / master: ${masterinfo} / slave is ${delayinfo} seconds behind master | delay=${delayinfo}s"; exit ${STATE_OK};
          fi
        else 
          echo "OK: Slave SQL running: ${check} Slave IO running: ${checkio} / master: ${masterinfo} / slave is ${delayinfo} seconds behind master | delay=${delayinfo}s"; exit ${STATE_OK};
        fi
      else 
        echo "$execpos" > $tmpfile
        echo "OK: Slave SQL running: ${check} Slave IO running: ${checkio} / master: ${masterinfo} / slave is ${delayinfo} seconds behind master | delay=${delayinfo}s"; exit ${STATE_OK};
      fi
    else # Everything OK (no additional moving check)
      echo "OK: Slave SQL running: ${check} Slave IO running: ${checkio} / master: ${masterinfo} / slave is ${delayinfo} seconds behind master | delay=${delayinfo}s"; exit ${STATE_OK};
    fi
  fi
 else
 # Without delay thresholds
 echo "OK: Slave SQL running: ${check} Slave IO running: ${checkio} / master: ${masterinfo} / slave is ${delayinfo} seconds behind master | delay=${delayinfo}s"
 exit ${STATE_OK};
 fi
fi

echo "UNKNOWN: should never reach this part (Slave_SQL_Running is ${check}, Slave_IO_Running is ${checkio})"
exit ${STATE_UNKNOWN}

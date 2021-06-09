#!/bin/bash

trap "exit" INT TERM
trap "kill 0" EXIT

# Prepare
BACKGROUND_TASKS=()
echo "Waiting for containers to settle..."
sleep 30

if [[ "${USE_WATCHDOG}" =~ ^([nN][oO]|[nN])+$ ]]; then
  echo -e "$(date) - USE_WATCHDOG=n, skipping watchdog..."
  sleep 365d
  exec $(readlink -f "$0")
fi

# Checks pipe their corresponding container name in this pipe
if [[ ! -p /tmp/com_pipe ]]; then
  mkfifo /tmp/com_pipe
fi

# Wait for containers
while ! mysqladmin status --socket=/var/run/mysqld/mysqld.sock -u${DBUSER} -p${DBPASS} --silent; do
  echo "Waiting for SQL..."
  sleep 2
done

# Do not attempt to write to slave
if [[ ! -z ${REDIS_SLAVEOF_IP} ]]; then
  REDIS_CMDLINE="redis-cli -h ${REDIS_SLAVEOF_IP} -p ${REDIS_SLAVEOF_PORT}"
else
  REDIS_CMDLINE="redis-cli -h redis -p 6379"
fi

until [[ $(${REDIS_CMDLINE} PING) == "PONG" ]]; do
  echo "Waiting for Redis..."
  sleep 2
done

${REDIS_CMDLINE} DEL F2B_RES > /dev/null

# Common functions
get_ipv6(){
  local IPV6=
  local IPV6_SRCS=
  local TRY=
  IPV6_SRCS[0]="ip6.mailcow.email"
  IPV6_SRCS[1]="ip6.nevondo.com"
  until [[ ! -z ${IPV6} ]] || [[ ${TRY} -ge 10 ]]; do
    IPV6=$(curl --connect-timeout 3 -m 10 -L6s ${IPV6_SRCS[$RANDOM % ${#IPV6_SRCS[@]} ]} | grep "^\([0-9a-fA-F]\{0,4\}:\)\{1,7\}[0-9a-fA-F]\{0,4\}$")
    [[ ! -z ${TRY} ]] && sleep 1
    TRY=$((TRY+1))
  done
  echo ${IPV6}
}

array_diff() {
  # https://stackoverflow.com/questions/2312762, Alex Offshore
  eval local ARR1=\(\"\${$2[@]}\"\)
  eval local ARR2=\(\"\${$3[@]}\"\)
  local IFS=$'\n'
  mapfile -t $1 < <(comm -23 <(echo "${ARR1[*]}" | sort) <(echo "${ARR2[*]}" | sort))
}

progress() {
  SERVICE=${1}
  TOTAL=${2}
  CURRENT=${3}
  DIFF=${4}
  [[ -z ${DIFF} ]] && DIFF=0
  [[ -z ${TOTAL} || -z ${CURRENT} ]] && return
  [[ ${CURRENT} -gt ${TOTAL} ]] && return
  [[ ${CURRENT} -lt 0 ]] && CURRENT=0
  PERCENT=$(( 200 * ${CURRENT} / ${TOTAL} % 2 + 100 * ${CURRENT} / ${TOTAL} ))
  ${REDIS_CMDLINE} LPUSH WATCHDOG_LOG "{\"time\":\"$(date +%s)\",\"service\":\"${SERVICE}\",\"lvl\":\"${PERCENT}\",\"hpnow\":\"${CURRENT}\",\"hptotal\":\"${TOTAL}\",\"hpdiff\":\"${DIFF}\"}" > /dev/null
  log_msg "${SERVICE} health level: ${PERCENT}% (${CURRENT}/${TOTAL}), health trend: ${DIFF}" no_redis
  # Return 10 to indicate a dead service
  [ ${CURRENT} -le 0 ] && return 10
}

log_msg() {
  if [[ ${2} != "no_redis" ]]; then
    ${REDIS_CMDLINE} LPUSH WATCHDOG_LOG "{\"time\":\"$(date +%s)\",\"message\":\"$(printf '%s' "${1}" | \
      tr '\r\n%&;$"_[]{}-' ' ')\"}" > /dev/null
  fi
  echo $(date) $(printf '%s\n' "${1}")
}

function mail_error() {
  THROTTLE=
  [[ -z ${1} ]] && return 1
  # If exists, body will be the content of "/tmp/${1}", even if ${2} is set
  [[ -z ${2} ]] && BODY="Service was restarted on $(date), please check your mailcow installation." || BODY="$(date) - ${2}"
  # If exists, mail will be throttled by argument in seconds
  [[ ! -z ${3} ]] && THROTTLE=${3}
  if [[ ! -z ${THROTTLE} ]]; then
    TTL_LEFT="$(${REDIS_CMDLINE} TTL THROTTLE_${1} 2> /dev/null)"
    if [[ "${TTL_LEFT}" == "-2" ]]; then
      # Delay key not found, setting a delay key now
      ${REDIS_CMDLINE} SET THROTTLE_${1} 1 EX ${THROTTLE}
    else
      log_msg "Not sending notification email now, blocked for ${TTL_LEFT} seconds..."
      return 1
    fi
  fi
  WATCHDOG_NOTIFY_EMAIL=$(echo "${WATCHDOG_NOTIFY_EMAIL}" | sed 's/"//;s|"$||')
  # Some exceptions for subject and body formats
  if [[ ${1} == "fail2ban" ]]; then
    SUBJECT="${BODY}"
    BODY="Please see netfilter-mailcow for more details and triggered rules."
  else
    SUBJECT="${WATCHDOG_SUBJECT}: ${1}"
  fi
  IFS=',' read -r -a MAIL_RCPTS <<< "${WATCHDOG_NOTIFY_EMAIL}"
  for rcpt in "${MAIL_RCPTS[@]}"; do
    RCPT_DOMAIN=
    #RCPT_MX=
    RCPT_DOMAIN=$(echo ${rcpt} | awk -F @ {'print $NF'})
    # Latest smtp-cli looks up mx via dns
    #RCPT_MX=$(dig +short ${RCPT_DOMAIN} mx | sort -n | awk '{print $2; exit}')
    #if [[ -z ${RCPT_MX} ]]; then
    #  log_msg "Cannot determine MX for ${rcpt}, skipping email notification..."
    #  return 1
    #fi
    [ -f "/tmp/${1}" ] && BODY="/tmp/${1}"
    timeout 10s ./smtp-cli --missing-modules-ok \
      --charset=UTF-8 \
      --subject="${SUBJECT}" \
      --body-plain="${BODY}" \
      --add-header="X-Priority: 1" \
      --to=${rcpt} \
      --from="watchdog@${MAILCOW_HOSTNAME}" \
      --hello-host=${MAILCOW_HOSTNAME} \
      --ipv4
      #--server="${RCPT_MX}"
    log_msg "Sent notification email to ${rcpt}"
  done
}

get_container_ip() {
  # ${1} is container
  CONTAINER_ID=()
  CONTAINER_IPS=()
  CONTAINER_IP=
  LOOP_C=1
  until [[ ${CONTAINER_IP} =~ ^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$ ]] || [[ ${LOOP_C} -gt 5 ]]; do
    if [ ${IP_BY_DOCKER_API} -eq 0 ]; then
      CONTAINER_IP=$(dig a "${1}" +short)
    else
      sleep 0.5
      # get long container id for exact match
      CONTAINER_ID=($(curl --silent --insecure https://dockerapi/containers/json | jq -r ".[] | {name: .Config.Labels[\"com.docker.compose.service\"], project: .Config.Labels[\"com.docker.compose.project\"], id: .Id}" | jq -rc "select( .name | tostring == \"${1}\") | select( .project | tostring | contains(\"${COMPOSE_PROJECT_NAME,,}\")) | .id"))
      # returned id can have multiple elements (if scaled), shuffle for random test
      CONTAINER_ID=($(printf "%s\n" "${CONTAINER_ID[@]}" | shuf))
      if [[ ! -z ${CONTAINER_ID} ]]; then
        for matched_container in "${CONTAINER_ID[@]}"; do
          CONTAINER_IPS=($(curl --silent --insecure https://dockerapi/containers/${matched_container}/json | jq -r '.NetworkSettings.Networks[].IPAddress')) 
          for ip_match in "${CONTAINER_IPS[@]}"; do
            # grep will do nothing if one of these vars is empty
            [[ -z ${ip_match} ]] && continue
            [[ -z ${IPV4_NETWORK} ]] && continue
            # only return ips that are part of our network
            if ! grep -q ${IPV4_NETWORK} <(echo ${ip_match}); then
              continue
            else
              CONTAINER_IP=${ip_match}
              break
            fi
          done
          [[ ! -z ${CONTAINER_IP} ]] && break
        done
      fi
    fi
    LOOP_C=$((LOOP_C + 1))
  done
  [[ ${LOOP_C} -gt 5 ]] && echo 240.0.0.0 || echo ${CONTAINER_IP}
}

# One-time check
if grep -qi "$(echo ${IPV6_NETWORK} | cut -d: -f1-3)" <<< "$(ip a s)"; then
  if [[ -z "$(get_ipv6)" ]]; then
    mail_error "ipv6-config" "enable_ipv6 is true in docker-compose.yml, but an IPv6 link could not be established. Please verify your IPv6 connection."
  fi
fi

external_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${EXTERNAL_CHECKS_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  GUID=$(mysql -u${DBUSER} -p${DBPASS} ${DBNAME} -e "SELECT version FROM versions WHERE application = 'GUID'" -BN)
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    err_c_cur=${err_count}
    CHECK_REPONSE="$(curl --connect-timeout 3 -m 10 -4 -s https://checks.mailcow.email -X POST -dguid=${GUID} 2> /dev/null)"
    if [[ ! -z "${CHECK_REPONSE}" ]] && [[ "$(echo ${CHECK_REPONSE} | jq -r .response)" == "critical" ]]; then
      echo ${CHECK_REPONSE} | jq -r .out > /tmp/external_checks
      err_count=$(( ${err_count} + 1 ))
    fi
    CHECK_REPONSE6="$(curl --connect-timeout 3 -m 10 -6 -s https://checks.mailcow.email -X POST -dguid=${GUID} 2> /dev/null)"
    if [[ ! -z "${CHECK_REPONSE6}" ]] && [[ "$(echo ${CHECK_REPONSE6} | jq -r .response)" == "critical" ]]; then
      echo ${CHECK_REPONSE} | jq -r .out > /tmp/external_checks
      err_count=$(( ${err_count} + 1 ))
    fi
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "External checks" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 60
    else
      diff_c=0
      sleep $(( ( RANDOM % 20 ) + 1800 ))
    fi
  done
  return 1
}

nginx_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${NGINX_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/nginx-mailcow; echo "$(tail -50 /tmp/nginx-mailcow)" > /tmp/nginx-mailcow
    host_ip=$(get_container_ip nginx-mailcow)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_http -4 -H ${host_ip} -u / -p 8081 2>> /tmp/nginx-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Nginx" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

unbound_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${UNBOUND_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/unbound-mailcow; echo "$(tail -50 /tmp/unbound-mailcow)" > /tmp/unbound-mailcow
    host_ip=$(get_container_ip unbound-mailcow)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_dns -s ${host_ip} -H stackoverflow.com 2>> /tmp/unbound-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    DNSSEC=$(dig com +dnssec | egrep 'flags:.+ad')
    if [[ -z ${DNSSEC} ]]; then
      echo "DNSSEC failure" 2>> /tmp/unbound-mailcow 1>&2
      err_count=$(( ${err_count} + 1))
    else
      echo "DNSSEC check succeeded" 2>> /tmp/unbound-mailcow 1>&2
    fi
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Unbound" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

redis_checks() {
  # A check for the local redis container
  err_count=0
  diff_c=0
  THRESHOLD=${REDIS_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/redis-mailcow; echo "$(tail -50 /tmp/redis-mailcow)" > /tmp/redis-mailcow
    host_ip=$(get_container_ip redis-mailcow)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_tcp -4 -H redis-mailcow -p 6379 -E -s "PING\n" -q "QUIT" -e "PONG" 2>> /tmp/redis-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Redis" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

mysql_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${MYSQL_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/mysql-mailcow; echo "$(tail -50 /tmp/mysql-mailcow)" > /tmp/mysql-mailcow
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_mysql -s /var/run/mysqld/mysqld.sock -u ${DBUSER} -p ${DBPASS} -d ${DBNAME} 2>> /tmp/mysql-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    /usr/lib/nagios/plugins/check_mysql_query -s /var/run/mysqld/mysqld.sock -u ${DBUSER} -p ${DBPASS} -d ${DBNAME} -q "SELECT COUNT(*) FROM information_schema.tables" 2>> /tmp/mysql-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "MySQL/MariaDB" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

mysql_repl_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${MYSQL_REPLICATION_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/mysql_repl_checks; echo "$(tail -50 /tmp/mysql_repl_checks)" > /tmp/mysql_repl_checks
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_mysql_slavestatus.sh -S /var/run/mysqld/mysqld.sock -u root -p ${DBROOT} 2>> /tmp/mysql_repl_checks 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "MySQL/MariaDB replication" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 60
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

sogo_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${SOGO_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/sogo-mailcow; echo "$(tail -50 /tmp/sogo-mailcow)" > /tmp/sogo-mailcow
    host_ip=$(get_container_ip sogo-mailcow)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_http -4 -H ${host_ip} -u /SOGo.index/ -p 20000 -R "SOGo\.MainUI" 2>> /tmp/sogo-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "SOGo" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

postfix_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${POSTFIX_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/postfix-mailcow; echo "$(tail -50 /tmp/postfix-mailcow)" > /tmp/postfix-mailcow
    host_ip=$(get_container_ip postfix-mailcow)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_smtp -4 -H ${host_ip} -p 589 -f "watchdog@invalid" -C "RCPT TO:watchdog@localhost" -C DATA -C . -R 250 2>> /tmp/postfix-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    /usr/lib/nagios/plugins/check_smtp -4 -H ${host_ip} -p 589 -S 2>> /tmp/postfix-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Postfix" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

clamd_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${CLAMD_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/clamd-mailcow; echo "$(tail -50 /tmp/clamd-mailcow)" > /tmp/clamd-mailcow
    host_ip=$(get_container_ip clamd-mailcow)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_clamd -4 -H ${host_ip} 2>> /tmp/clamd-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Clamd" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 120 ) + 20 ))
    fi
  done
  return 1
}

dovecot_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${DOVECOT_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/dovecot-mailcow; echo "$(tail -50 /tmp/dovecot-mailcow)" > /tmp/dovecot-mailcow
    host_ip=$(get_container_ip dovecot-mailcow)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_smtp -4 -H ${host_ip} -p 24 -f "watchdog@invalid" -C "RCPT TO:<watchdog@invalid>" -L -R "User doesn't exist" 2>> /tmp/dovecot-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    /usr/lib/nagios/plugins/check_imap -4 -H ${host_ip} -p 993 -S -e "OK " 2>> /tmp/dovecot-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    /usr/lib/nagios/plugins/check_imap -4 -H ${host_ip} -p 143 -e "OK " 2>> /tmp/dovecot-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    /usr/lib/nagios/plugins/check_tcp -4 -H ${host_ip} -p 10001 -e "VERSION" 2>> /tmp/dovecot-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    /usr/lib/nagios/plugins/check_tcp -4 -H ${host_ip} -p 4190 -e "Dovecot ready" 2>> /tmp/dovecot-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Dovecot" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

dovecot_repl_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${DOVECOT_REPL_THRESHOLD}
  D_REPL_STATUS=$(redis-cli -h redis -r GET DOVECOT_REPL_HEALTH)
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    err_c_cur=${err_count}
    D_REPL_STATUS=$(redis-cli --raw -h redis GET DOVECOT_REPL_HEALTH)
    if [[ "${D_REPL_STATUS}" != "1" ]]; then
      err_count=$(( ${err_count} + 1 ))
    fi
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Dovecot replication" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 60
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

cert_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=7
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/certcheck; echo "$(tail -50 /tmp/certcheck)" > /tmp/certcheck
    host_ip_postfix=$(get_container_ip postfix)
    host_ip_dovecot=$(get_container_ip dovecot)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_smtp -H ${host_ip_postfix} -p 589 -4 -S -D 7 2>> /tmp/certcheck 1>&2; err_count=$(( ${err_count} + $? ))
    /usr/lib/nagios/plugins/check_imap -H ${host_ip_dovecot} -p 993 -4 -S -D 7 2>> /tmp/certcheck 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Primary certificate expiry check" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    # Always sleep 5 minutes, mail notifications are limited
    sleep 300
  done
  return 1
}

phpfpm_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${PHPFPM_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/php-fpm-mailcow; echo "$(tail -50 /tmp/php-fpm-mailcow)" > /tmp/php-fpm-mailcow
    host_ip=$(get_container_ip php-fpm-mailcow)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_tcp -H ${host_ip} -p 9001 2>> /tmp/php-fpm-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    /usr/lib/nagios/plugins/check_tcp -H ${host_ip} -p 9002 2>> /tmp/php-fpm-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "PHP-FPM" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

ratelimit_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${RATELIMIT_THRESHOLD}
  RL_LOG_STATUS=$(redis-cli -h redis LRANGE RL_LOG 0 0 | jq .qid)
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    err_c_cur=${err_count}
    RL_LOG_STATUS_PREV=${RL_LOG_STATUS}
    RL_LOG_STATUS=$(redis-cli -h redis LRANGE RL_LOG 0 0 | jq .qid)
    if [[ ${RL_LOG_STATUS_PREV} != ${RL_LOG_STATUS} ]]; then
      err_count=$(( ${err_count} + 1 ))
      echo 'Last 10 applied ratelimits (may overlap with previous reports).' > /tmp/ratelimit
      echo 'Full ratelimit buckets can be emptied by deleting the ratelimit hash from within mailcow UI (see /debug -> Protocols -> Ratelimit):' >> /tmp/ratelimit
      echo >> /tmp/ratelimit
      redis-cli --raw -h redis LRANGE RL_LOG 0 10 | jq . >> /tmp/ratelimit
    fi
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Ratelimit" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

mailq_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${MAILQ_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/mail_queue_status; echo "$(tail -50 /tmp/mail_queue_status)" > /tmp/mail_queue_status
    MAILQ_LOG_STATUS=$(find /var/spool/postfix/deferred -type f | wc -l)
    echo "Mail queue contains ${MAILQ_LOG_STATUS} items (critical limit is ${MAILQ_CRIT}) at $(date)" >> /tmp/mail_queue_status
    err_c_cur=${err_count}
    if [ ${MAILQ_LOG_STATUS} -ge ${MAILQ_CRIT} ]; then
      err_count=$(( ${err_count} + 1 ))
      echo "Mail queue contains ${MAILQ_LOG_STATUS} items (critical limit is ${MAILQ_CRIT}) at $(date)" >> /tmp/mail_queue_status
    fi
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Mail queue" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 60
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

fail2ban_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${FAIL2BAN_THRESHOLD}
  F2B_LOG_STATUS=($(${REDIS_CMDLINE} --raw HKEYS F2B_ACTIVE_BANS))
  F2B_RES=
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    err_c_cur=${err_count}
    F2B_LOG_STATUS_PREV=(${F2B_LOG_STATUS[@]})
    F2B_LOG_STATUS=($(${REDIS_CMDLINE} --raw HKEYS F2B_ACTIVE_BANS))
    array_diff F2B_RES F2B_LOG_STATUS F2B_LOG_STATUS_PREV
    if [[ ! -z "${F2B_RES}" ]]; then
      err_count=$(( ${err_count} + 1 ))
      echo -n "${F2B_RES[@]}" | tr -cd "[a-fA-F0-9.:/] " | timeout 3s ${REDIS_CMDLINE} -x SET F2B_RES > /dev/null
      if [ $? -ne 0 ]; then
         ${REDIS_CMDLINE} -x DEL F2B_RES
      fi
    fi
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Fail2ban" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

acme_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${ACME_THRESHOLD}
  ACME_LOG_STATUS=$(redis-cli -h redis GET ACME_FAIL_TIME)
  if [[ -z "${ACME_LOG_STATUS}" ]]; then
    ${REDIS_CMDLINE} SET ACME_FAIL_TIME 0
    ACME_LOG_STATUS=0
  fi
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    err_c_cur=${err_count}
    ACME_LOG_STATUS_PREV=${ACME_LOG_STATUS}
    ACME_LC=0
    until [[ ! -z ${ACME_LOG_STATUS} ]] || [ ${ACME_LC} -ge 3 ]; do
      ACME_LOG_STATUS=$(redis-cli -h redis GET ACME_FAIL_TIME 2> /dev/null)
      sleep 3
      ACME_LC=$((ACME_LC+1))
    done
    if [[ ${ACME_LOG_STATUS_PREV} != ${ACME_LOG_STATUS} ]]; then
      err_count=$(( ${err_count} + 1 ))
    fi
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "ACME" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

ipv6nat_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${IPV6NAT_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    err_c_cur=${err_count}
    CONTAINERS=$(curl --silent --insecure https://dockerapi/containers/json)
    IPV6NAT_CONTAINER_ID=$(echo ${CONTAINERS} | jq -r ".[] | {name: .Config.Labels[\"com.docker.compose.service\"], project: .Config.Labels[\"com.docker.compose.project\"], id: .Id}" | jq -rc "select( .name | tostring | contains(\"ipv6nat-mailcow\")) | select( .project | tostring | contains(\"${COMPOSE_PROJECT_NAME,,}\")) | .id")
    if [[ ! -z ${IPV6NAT_CONTAINER_ID} ]]; then
      LATEST_STARTED="$(echo ${CONTAINERS} | jq -r ".[] | {name: .Config.Labels[\"com.docker.compose.service\"], project: .Config.Labels[\"com.docker.compose.project\"], StartedAt: .State.StartedAt}" | jq -rc "select( .project | tostring | contains(\"${COMPOSE_PROJECT_NAME,,}\")) | select( .name | tostring | contains(\"ipv6nat-mailcow\") | not)" | jq -rc .StartedAt | xargs -n1 date +%s -d | sort | tail -n1)"
      LATEST_IPV6NAT="$(echo ${CONTAINERS} | jq -r ".[] | {name: .Config.Labels[\"com.docker.compose.service\"], project: .Config.Labels[\"com.docker.compose.project\"], StartedAt: .State.StartedAt}" | jq -rc "select( .project | tostring | contains(\"${COMPOSE_PROJECT_NAME,,}\")) | select( .name | tostring | contains(\"ipv6nat-mailcow\"))" | jq -rc .StartedAt | xargs -n1 date +%s -d | sort | tail -n1)"
      DIFFERENCE_START_TIME=$(expr ${LATEST_IPV6NAT} - ${LATEST_STARTED} 2>/dev/null)
      if [[ "${DIFFERENCE_START_TIME}" -lt 30 ]]; then
        err_count=$(( ${err_count} + 1 ))
      fi
    fi
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "IPv6 NAT" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 30
    else
      diff_c=0
      sleep 300
    fi
  done
  return 1
}


rspamd_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${RSPAMD_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/rspamd-mailcow; echo "$(tail -50 /tmp/rspamd-mailcow)" > /tmp/rspamd-mailcow
    host_ip=$(get_container_ip rspamd-mailcow)
    err_c_cur=${err_count}
    SCORE=$(echo 'To: null@localhost
From: watchdog@localhost

Empty
' | usr/bin/curl --max-time 10 -s --data-binary @- --unix-socket /var/lib/rspamd/rspamd.sock http://rspamd/scan | jq -rc .default.required_score)
    if [[ ${SCORE} != "9999" ]]; then
      echo "Rspamd settings check failed, score returned: ${SCORE}" 2>> /tmp/rspamd-mailcow 1>&2
      err_count=$(( ${err_count} + 1))
    else
      echo "Rspamd settings check succeeded, score returned: ${SCORE}" 2>> /tmp/rspamd-mailcow 1>&2
    fi
    # A dirty hack until a PING PONG event is implemented to worker proxy
    # We expect an empty response, not a timeout
    if [ "$(curl -s --max-time 10 ${host_ip}:9900 2> /dev/null ; echo $?)" == "28" ]; then
      echo "Milter check failed" 2>> /tmp/rspamd-mailcow 1>&2; err_count=$(( ${err_count} + 1 ));
    else
      echo "Milter check succeeded" 2>> /tmp/rspamd-mailcow 1>&2
    fi
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Rspamd" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

olefy_checks() {
  err_count=0
  diff_c=0
  THRESHOLD=${OLEFY_THRESHOLD}
  # Reduce error count by 2 after restarting an unhealthy container
  trap "[ ${err_count} -gt 1 ] && err_count=$(( ${err_count} - 2 ))" USR1
  while [ ${err_count} -lt ${THRESHOLD} ]; do
    touch /tmp/olefy-mailcow; echo "$(tail -50 /tmp/olefy-mailcow)" > /tmp/olefy-mailcow
    host_ip=$(get_container_ip olefy-mailcow)
    err_c_cur=${err_count}
    /usr/lib/nagios/plugins/check_tcp -4 -H ${host_ip} -p 10055 -s "PING\n" 2>> /tmp/olefy-mailcow 1>&2; err_count=$(( ${err_count} + $? ))
    [ ${err_c_cur} -eq ${err_count} ] && [ ! $((${err_count} - 1)) -lt 0 ] && err_count=$((${err_count} - 1)) diff_c=1
    [ ${err_c_cur} -ne ${err_count} ] && diff_c=$(( ${err_c_cur} - ${err_count} ))
    progress "Olefy" ${THRESHOLD} $(( ${THRESHOLD} - ${err_count} )) ${diff_c}
    if [[ $? == 10 ]]; then
      diff_c=0
      sleep 1
    else
      diff_c=0
      sleep $(( ( RANDOM % 60 ) + 20 ))
    fi
  done
  return 1
}

# Notify about start
if [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]]; then
  mail_error "watchdog-mailcow" "Watchdog started monitoring mailcow."
fi

# Create watchdog agents

(
while true; do
  if ! nginx_checks; then
    log_msg "Nginx hit error limit"
    echo nginx-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned nginx_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

if [[ ${WATCHDOG_EXTERNAL_CHECKS} =~ ^([yY][eE][sS]|[yY])+$ ]]; then
(
while true; do
  if ! external_checks; then
    log_msg "External checks hit error limit"
    echo external_checks > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned external_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})
fi

if [[ ${WATCHDOG_MYSQL_REPLICATION_CHECKS} =~ ^([yY][eE][sS]|[yY])+$ ]]; then
(
while true; do
  if ! mysql_repl_checks; then
    log_msg "MySQL replication check hit error limit"
    echo mysql_repl_checks > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned mysql_repl_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})
fi

(
while true; do
  if ! mysql_checks; then
    log_msg "MySQL hit error limit"
    echo mysql-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned mysql_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! redis_checks; then
    log_msg "Local Redis hit error limit"
    echo redis-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned redis_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! phpfpm_checks; then
    log_msg "PHP-FPM hit error limit"
    echo php-fpm-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned phpfpm_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

if [[ "${SKIP_SOGO}" =~ ^([nN][oO]|[nN])+$ ]]; then
(
while true; do
  if ! sogo_checks; then
    log_msg "SOGo hit error limit"
    echo sogo-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned sogo_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})
fi

if [ ${CHECK_UNBOUND} -eq 1 ]; then
(
while true; do
  if ! unbound_checks; then
    log_msg "Unbound hit error limit"
    echo unbound-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned unbound_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})
fi

if [[ "${SKIP_CLAMD}" =~ ^([nN][oO]|[nN])+$ ]]; then
(
while true; do
  if ! clamd_checks; then
    log_msg "Clamd hit error limit"
    echo clamd-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned clamd_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})
fi

(
while true; do
  if ! postfix_checks; then
    log_msg "Postfix hit error limit"
    echo postfix-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned postfix_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! mailq_checks; then
    log_msg "Mail queue hit error limit"
    echo mail_queue_status > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned mailq_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! dovecot_checks; then
    log_msg "Dovecot hit error limit"
    echo dovecot-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned dovecot_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! dovecot_repl_checks; then
    log_msg "Dovecot hit error limit"
    echo dovecot_repl_checks > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned dovecot_repl_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! rspamd_checks; then
    log_msg "Rspamd hit error limit"
    echo rspamd-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned rspamd_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! ratelimit_checks; then
    log_msg "Ratelimit hit error limit"
    echo ratelimit > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned ratelimit_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! fail2ban_checks; then
    log_msg "Fail2ban hit error limit"
    echo fail2ban > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned fail2ban_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! cert_checks; then
    log_msg "Cert check hit error limit"
    echo certcheck > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned cert_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! olefy_checks; then
    log_msg "Olefy hit error limit"
    echo olefy-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned olefy_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! acme_checks; then
    log_msg "ACME client hit error limit"
    echo acme-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned acme_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

(
while true; do
  if ! ipv6nat_checks; then
    log_msg "IPv6 NAT warning: ipv6nat-mailcow container was not started at least 30s after siblings (not an error)"
    echo ipv6nat-mailcow > /tmp/com_pipe
  fi
done
) &
PID=$!
echo "Spawned ipv6nat_checks with PID ${PID}"
BACKGROUND_TASKS+=(${PID})

# Monitor watchdog agents, stop script when agents fails and wait for respawn by Docker (restart:always:n)
(
while true; do
  for bg_task in ${BACKGROUND_TASKS[*]}; do
    if ! kill -0 ${bg_task} 1>&2; then
      log_msg "Worker ${bg_task} died, stopping watchdog and waiting for respawn..."
      kill -TERM 1
    fi
    sleep 10
  done
done
) &

# Monitor dockerapi
(
while true; do
  while nc -z dockerapi 443; do
    sleep 3
  done
  log_msg "Cannot find dockerapi-mailcow, waiting to recover..."
  kill -STOP ${BACKGROUND_TASKS[*]}
  until nc -z dockerapi 443; do
    sleep 3
  done
  kill -CONT ${BACKGROUND_TASKS[*]}
  kill -USR1 ${BACKGROUND_TASKS[*]}
done
) &

# Actions when threshold limit is reached
while true; do
  CONTAINER_ID=
  HAS_INITDB=
  read com_pipe_answer </tmp/com_pipe
  if [ -s "/tmp/${com_pipe_answer}" ]; then
    cat "/tmp/${com_pipe_answer}"
  fi
  if [[ ${com_pipe_answer} == "ratelimit" ]]; then
    log_msg "At least one ratelimit was applied"
    [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]] && mail_error "${com_pipe_answer}"
  elif [[ ${com_pipe_answer} == "mail_queue_status" ]]; then
    log_msg "Mail queue status is critical"
    [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]] && mail_error "${com_pipe_answer}"
  elif [[ ${com_pipe_answer} == "external_checks" ]]; then
    log_msg "Your mailcow is an open relay!"
    # Define $2 to override message text, else print service was restarted at ...
    [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]] && mail_error "${com_pipe_answer}" "Please stop mailcow now and check your network configuration!"
  elif [[ ${com_pipe_answer} == "mysql_repl_checks" ]]; then
    log_msg "MySQL replication is not working properly"
    # Define $2 to override message text, else print service was restarted at ...
    # Once mail per 10 minutes
    [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]] && mail_error "${com_pipe_answer}" "Please check the SQL replication status" 600
  elif [[ ${com_pipe_answer} == "dovecot_repl_checks" ]]; then
    log_msg "Dovecot replication is not working properly"
    # Define $2 to override message text, else print service was restarted at ...
    # Once mail per 10 minutes
    [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]] && mail_error "${com_pipe_answer}" "Please check the Dovecot replicator status" 600
  elif [[ ${com_pipe_answer} == "certcheck" ]]; then
    log_msg "Certificates are about to expire"
    # Define $2 to override message text, else print service was restarted at ...
    # Only mail once a day
    [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]] && mail_error "${com_pipe_answer}" "Please renew your certificate" 86400
  elif [[ ${com_pipe_answer} == "acme-mailcow" ]]; then
    log_msg "acme-mailcow did not complete successfully"
    # Define $2 to override message text, else print service was restarted at ...
    [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]] && mail_error "${com_pipe_answer}" "Please check acme-mailcow for further information."
  elif [[ ${com_pipe_answer} == "fail2ban" ]]; then
    F2B_RES=($(timeout 4s ${REDIS_CMDLINE} --raw GET F2B_RES 2> /dev/null))
    if [[ ! -z "${F2B_RES}" ]]; then
      ${REDIS_CMDLINE} DEL F2B_RES > /dev/null
      host=
      for host in "${F2B_RES[@]}"; do
        log_msg "Banned ${host}"
        rm /tmp/fail2ban 2> /dev/null
        timeout 2s whois "${host}" > /tmp/fail2ban
        [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]] && [[ ${WATCHDOG_NOTIFY_BAN} =~ ^([yY][eE][sS]|[yY])+$ ]] && mail_error "${com_pipe_answer}" "IP ban: ${host}"
      done
    fi
  elif [[ ${com_pipe_answer} =~ .+-mailcow ]]; then
    kill -STOP ${BACKGROUND_TASKS[*]}
    sleep 10
    CONTAINER_ID=$(curl --silent --insecure https://dockerapi/containers/json | jq -r ".[] | {name: .Config.Labels[\"com.docker.compose.service\"], project: .Config.Labels[\"com.docker.compose.project\"], id: .Id}" | jq -rc "select( .name | tostring | contains(\"${com_pipe_answer}\")) | select( .project | tostring | contains(\"${COMPOSE_PROJECT_NAME,,}\")) | .id")
    if [[ ! -z ${CONTAINER_ID} ]]; then
      if [[ "${com_pipe_answer}" == "php-fpm-mailcow" ]]; then
        HAS_INITDB=$(curl --silent --insecure -XPOST https://dockerapi/containers/${CONTAINER_ID}/top | jq '.msg.Processes[] | contains(["php -c /usr/local/etc/php -f /web/inc/init_db.inc.php"])' | grep true)
      fi
      S_RUNNING=$(($(date +%s) - $(curl --silent --insecure https://dockerapi/containers/${CONTAINER_ID}/json | jq .State.StartedAt | xargs -n1 date +%s -d)))
      if [ ${S_RUNNING} -lt 360 ]; then
        log_msg "Container is running for less than 360 seconds, skipping action..."
      elif [[ ! -z ${HAS_INITDB} ]]; then
        log_msg "Database is being initialized by php-fpm-mailcow, not restarting but delaying checks for a minute..."
        sleep 60
      else
        log_msg "Sending restart command to ${CONTAINER_ID}..."
        curl --silent --insecure -XPOST https://dockerapi/containers/${CONTAINER_ID}/restart
        if [[ ${com_pipe_answer} != "ipv6nat-mailcow" ]]; then
          [[ ! -z ${WATCHDOG_NOTIFY_EMAIL} ]] && mail_error "${com_pipe_answer}"
        fi
        log_msg "Wait for restarted container to settle and continue watching..."
        sleep 35
      fi
    fi
    kill -CONT ${BACKGROUND_TASKS[*]}
    sleep 1
    kill -USR1 ${BACKGROUND_TASKS[*]}
  fi
done

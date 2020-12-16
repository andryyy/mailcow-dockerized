#!/bin/bash

REDIS_SLAVEOF_IP=__REDIS_SLAVEOF_IP__

# Do not attempt to write to slave
if [[ ! -z ${REDIS_SLAVEOF_IP} ]]; then
  REDIS_CMDLINE="redis-cli -h ${REDIS_SLAVEOF_IP} -p ${REDIS_SLAVEOF_PORT}"
else
  REDIS_CMDLINE="redis-cli -h redis -p 6379"
fi

while read QUERY; do
  QUERY=($QUERY)
  # If nothing matched, end here - Postfix last line will be empty
  if [[ -z "$(echo ${QUERY[0]} | tr -d '\040\011\012\015')" ]]; then
    echo -ne "action=dunno\n\n"
  # We found a username, log and return
  elif [[ "${QUERY[0]}" =~ sasl_username ]]; then
    MUSER=$(printf "%q" ${QUERY[0]#sasl_username=})
    ${REDIS_CMDLINE} SET "last-login/smtp/$MUSER" "$(date +%s)"
    echo -ne "action=dunno\n\n"
  fi
done

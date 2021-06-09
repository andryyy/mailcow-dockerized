#!/bin/bash
catch_non_zero() {
  CMD=${1}
  ${CMD} > /dev/null
  EC=$?
  if [ ${EC} -ne 0 ]; then
    echo "Command ${CMD} failed to execute, exit code was ${EC}"
  fi
}
source /source_env.sh
# Do not attempt to write to slave
if [[ ! -z ${REDIS_SLAVEOF_IP} ]]; then
  REDIS_CMDLINE="redis-cli -h ${REDIS_SLAVEOF_IP} -p ${REDIS_SLAVEOF_PORT}"
else
  REDIS_CMDLINE="redis-cli -h redis -p 6379"
fi
catch_non_zero "${REDIS_CMDLINE} LTRIM ACME_LOG 0 ${LOG_LINES}"
catch_non_zero "${REDIS_CMDLINE} LTRIM POSTFIX_MAILLOG 0 ${LOG_LINES}"
catch_non_zero "${REDIS_CMDLINE} LTRIM DOVECOT_MAILLOG 0 ${LOG_LINES}"
catch_non_zero "${REDIS_CMDLINE} LTRIM SOGO_LOG 0 ${LOG_LINES}"
catch_non_zero "${REDIS_CMDLINE} LTRIM NETFILTER_LOG 0 ${LOG_LINES}"
catch_non_zero "${REDIS_CMDLINE} LTRIM AUTODISCOVER_LOG 0 ${LOG_LINES}"
catch_non_zero "${REDIS_CMDLINE} LTRIM API_LOG 0 ${LOG_LINES}"
catch_non_zero "${REDIS_CMDLINE} LTRIM RL_LOG 0 ${LOG_LINES}"
catch_non_zero "${REDIS_CMDLINE} LTRIM WATCHDOG_LOG 0 ${LOG_LINES}"

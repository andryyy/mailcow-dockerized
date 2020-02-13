#!/bin/bash

if [[ ! -z ${REDIS_SLAVEOF_IP} ]]; then
  cp /etc/syslog-ng/syslog-ng-redis_slave.conf /etc/syslog-ng/syslog-ng.conf
fi

exec "$@"

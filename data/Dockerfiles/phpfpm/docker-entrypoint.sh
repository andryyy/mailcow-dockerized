#!/bin/bash

function array_by_comma { local IFS=","; echo "$*"; }

# Wait for containers
while ! mysqladmin status --socket=/var/run/mysqld/mysqld.sock -u${DBUSER} -p${DBPASS} --silent; do
  echo "Waiting for SQL..."
  sleep 2
done

until [[ $(redis-cli -h redis-mailcow PING) == "PONG" ]]; do
  echo "Waiting for Redis..."
  sleep 2
done

# Set a default release format

if [[ -z $(redis-cli --raw -h redis-mailcow GET Q_RELEASE_FORMAT) ]]; then
  redis-cli --raw -h redis-mailcow SET Q_RELEASE_FORMAT raw
fi

# Set max age of q items - if unset

if [[ -z $(redis-cli --raw -h redis-mailcow GET Q_MAX_AGE) ]]; then
  redis-cli --raw -h redis-mailcow SET Q_MAX_AGE 365
fi

# Check of mysql_upgrade

CONTAINER_ID=
until [[ ! -z "${CONTAINER_ID}" ]] && [[ "${CONTAINER_ID}" =~ ^[[:alnum:]]*$ ]]; do
  CONTAINER_ID=$(curl --silent --insecure https://dockerapi/containers/json | jq -r ".[] | {name: .Config.Labels[\"com.docker.compose.service\"], id: .Id}" 2> /dev/null | jq -rc "select( .name | tostring | contains(\"mysql-mailcow\")) | .id" 2> /dev/null)
done
echo "MySQL @ ${CONTAINER_ID}"
SQL_LOOP_C=0
SQL_CHANGED=0
until [[ ${SQL_UPGRADE_STATUS} == 'success' ]]; do
  if [ ${SQL_LOOP_C} -gt 4 ]; then
    echo "Tried to upgrade MySQL and failed, giving up after ${SQL_LOOP_C} retries and starting container (oops, not good)"
    break
  fi
  SQL_FULL_UPGRADE_RETURN=$(curl --silent --insecure -XPOST https://dockerapi/containers/${CONTAINER_ID}/exec -d '{"cmd":"system", "task":"mysql_upgrade"}' --silent -H 'Content-type: application/json')
  SQL_UPGRADE_STATUS=$(echo ${SQL_FULL_UPGRADE_RETURN} | jq -r .type)
  SQL_LOOP_C=$((SQL_LOOP_C+1))
  echo "SQL upgrade iteration #${SQL_LOOP_C}"
  if [[ ${SQL_UPGRADE_STATUS} == 'warning' ]]; then
    SQL_CHANGED=1
    echo "MySQL applied an upgrade, debug output:"
    echo ${SQL_FULL_UPGRADE_RETURN}
    sleep 3
    while ! mysqladmin status --socket=/var/run/mysqld/mysqld.sock -u${DBUSER} -p${DBPASS} --silent; do
      echo "Waiting for SQL to return, please wait"
      sleep 2
    done
    continue
  elif [[ ${SQL_UPGRADE_STATUS} == 'success' ]]; then
    echo "MySQL is up-to-date - debug output:"
    echo ${SQL_FULL_UPGRADE_RETURN}
  else
    echo "No valid reponse for mysql_upgrade was received, debug output:"
    echo ${SQL_FULL_UPGRADE_RETURN}
  fi
done

# doing post-installation stuff, if SQL was upgraded
if [ ${SQL_CHANGED} -eq 1 ]; then
  POSTFIX=($(curl --silent --insecure https://dockerapi/containers/json | jq -r '.[] | {name: .Config.Labels["com.docker.compose.service"], id: .Id}' | jq -rc 'select( .name | tostring | contains("postfix-mailcow")) | .id' | tr "\n" " "))
  if [[ -z ${POSTFIX} ]]; then
    echo "Could not determine Postfix container ID, skipping Postfix restart."
  else
    echo "Restarting Postfix"
    curl -X POST --silent --insecure https://dockerapi/containers/${POSTFIX}/restart | jq -r '.msg'
    echo "Sleeping 5 seconds..."
    sleep 5
  fi
fi

# Trigger db init
echo "Running DB init..."
php -c /usr/local/etc/php -f /web/inc/init_db.inc.php

# Recreating domain map
echo "Rebuilding domain map in Redis..."
declare -a DOMAIN_ARR
  redis-cli -h redis-mailcow DEL DOMAIN_MAP > /dev/null
while read line
do
  DOMAIN_ARR+=("$line")
done < <(mysql --socket=/var/run/mysqld/mysqld.sock -u ${DBUSER} -p${DBPASS} ${DBNAME} -e "SELECT domain FROM domain" -Bs)
while read line
do
  DOMAIN_ARR+=("$line")
done < <(mysql --socket=/var/run/mysqld/mysqld.sock -u ${DBUSER} -p${DBPASS} ${DBNAME} -e "SELECT alias_domain FROM alias_domain" -Bs)

if [[ ! -z ${DOMAIN_ARR} ]]; then
for domain in "${DOMAIN_ARR[@]}"; do
  redis-cli -h redis-mailcow HSET DOMAIN_MAP ${domain} 1 > /dev/null
done
fi

# Set API options if env vars are not empty

if [[ ${API_ALLOW_FROM} != "invalid" ]] && \
  [[ ${API_KEY} != "invalid" ]] && \
  [[ ! -z ${API_KEY} ]] && \
  [[ ! -z ${API_ALLOW_FROM} ]]; then
  IFS=',' read -r -a API_ALLOW_FROM_ARR <<< "${API_ALLOW_FROM}"
  declare -a VALIDATED_API_ALLOW_FROM_ARR
  REGEX_IP6='^([0-9a-fA-F]{0,4}:){1,7}[0-9a-fA-F]{0,4}$'
  REGEX_IP4='^[0-9]+\.[0-9]+\.[0-9]+\.[0-9]+$'

  for IP in "${API_ALLOW_FROM_ARR[@]}"; do
    if [[ ${IP} =~ ${REGEX_IP6} ]] || [[ ${IP} =~ ${REGEX_IP4} ]]; then
      VALIDATED_API_ALLOW_FROM_ARR+=("${IP}")
    fi
  done
  VALIDATED_IPS=$(array_by_comma ${VALIDATED_API_ALLOW_FROM_ARR[*]})
  if [[ ! -z ${VALIDATED_IPS} ]]; then
    mysql --socket=/var/run/mysqld/mysqld.sock -u ${DBUSER} -p${DBPASS} ${DBNAME} << EOF
DELETE FROM api;
INSERT INTO api (api_key, active, allow_from) VALUES ("${API_KEY}", "1", "${VALIDATED_IPS}");
EOF
  fi
fi

# Run hooks
for file in /hooks/*; do
  if [ -x "${file}" ]; then
    echo "Running hook ${file}"
    "${file}"
  fi
done

exec "$@"

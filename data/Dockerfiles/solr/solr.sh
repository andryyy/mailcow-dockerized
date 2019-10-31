#!/bin/bash

if [[ "${SKIP_SOLR}" =~ ^([yY][eE][sS]|[yY])+$ ]]; then
  echo "SKIP_SOLR=y, skipping Solr..."
  sleep 365d
  exit 0
fi

MEM_TOTAL=$(awk '/MemTotal/ {print $2}' /proc/meminfo)

if [[ "${1}" != "--bootstrap" ]]; then
  if [ ${MEM_TOTAL} -lt "2097152" ]; then
    echo "System memory less than 2 GB, skipping Solr..."
    sleep 365d
    exit 0
  fi
fi

set -e

# run the optional initdb
. /opt/docker-solr/scripts/run-initdb

# fixing volume permission
[[ -d /opt/solr/server/solr/dovecot-fts/data ]] && chown -R solr:solr /opt/solr/server/solr/dovecot-fts/data
if [[ "${1}" != "--bootstrap" ]]; then
  sed -i '/SOLR_HEAP=/c\SOLR_HEAP="'${SOLR_HEAP:-1024}'m"' /opt/solr/bin/solr.in.sh
else
  sed -i '/SOLR_HEAP=/c\SOLR_HEAP="256m"' /opt/solr/bin/solr.in.sh
fi

if [[ "${1}" == "--bootstrap" ]]; then
  echo "Creating initial configuration"
  echo "Modifying default config set"
  cp /solr-config-7.7.0.xml /opt/solr/server/solr/configsets/_default/conf/solrconfig.xml
  cp /solr-schema-7.7.0.xml /opt/solr/server/solr/configsets/_default/conf/schema.xml
  rm /opt/solr/server/solr/configsets/_default/conf/managed-schema

  echo "Starting local Solr instance to setup configuration"
  gosu solr start-local-solr

  echo "Creating core \"dovecot-fts\""
  gosu solr /opt/solr/bin/solr create -c "dovecot-fts"

  # See https://github.com/docker-solr/docker-solr/issues/27
  echo "Checking core"
  while ! wget -O - 'http://localhost:8983/solr/admin/cores?action=STATUS' | grep -q instanceDir; do
    echo "Could not find any cores, waiting..."
    sleep 3
  done

  echo "Created core \"dovecot-fts\""

  echo "Stopping local Solr"
  gosu solr stop-local-solr

  exit 0
fi

exec gosu solr solr-foreground


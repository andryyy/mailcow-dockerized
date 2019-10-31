#!/usr/bin/env bash

SCRIPT_DIR="$( cd "$( dirname "$0" )" && pwd )"
WORKING_DIR=${SCRIPT_DIR}/postwhite_tmp
SPFTOOLS_DIR=${WORKING_DIR}/spf-tools
POSTWHITE_DIR=${WORKING_DIR}/postwhite
POSTWHITE_CONF=${POSTWHITE_DIR}/postwhite.conf

COSTOM_HOSTS="web.de gmx.net mail.de freenet.de arcor.de unity-mail.de"
STATIC_HOSTS=(
    "194.25.134.0/24 permit # t-online.de"
)

mkdir ${SCRIPT_DIR}/postwhite_tmp
git clone https://github.com/spf-tools/spf-tools.git ${SPFTOOLS_DIR}
git clone https://github.com/stevejenkins/postwhite.git ${POSTWHITE_DIR}

function set_config() {
    sudo sed -i "s@^\($1\s*=\s*\).*\$@\1$2@" ${POSTWHITE_CONF}
}

set_config custom_hosts ${COSTOM_HOSTS}
set_config reload_postfix no
set_config postfixpath /.
set_config spftoolspath ${WORKING_DIR}/spf-tools
set_config whitelist .${SCRIPT_DIR}/../data/conf/postfix/postscreen_access.cidr
set_config yahoo_static_hosts ${POSTWHITE_DIR}/yahoo_static_hosts.txt

cd ${POSTWHITE_DIR}
./postwhite ${POSTWHITE_CONF}

( IFS=$'\n'; echo "${STATIC_HOSTS[*]}" >> "${SCRIPT_DIR}/../data/conf/postfix/postscreen_access.cidr")

rm -r ${WORKING_DIR}

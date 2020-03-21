#!/bin/bash

# Return values / exit codes
# 0 = cert created successfully
# 1 = cert renewed successfully
# 2 = cert not due for renewal
# * = errors


source /srv/functions.sh

CERT_DOMAINS=(${DOMAINS[@]})
CERT_DOMAIN=${CERT_DOMAINS[0]}
ACME_BASE=/var/lib/acme

TYPE=${1}
PREFIX=""
if [[ "${TYPE}" == "ecdsa" ]]; then
  PREFIX="ecdsa-"
elif [[ "${TYPE}" != "rsa" ]]; then
  log_f "Unknown certificate type '${TYPE}' requested"
  exit 5
fi
DOMAINS_FILE=${ACME_BASE}/${CERT_DOMAIN}/domains
CERT=${ACME_BASE}/${CERT_DOMAIN}/${PREFIX}cert.pem
SHARED_KEY=${ACME_BASE}/acme/${PREFIX}key.pem  # must already exist
KEY=${ACME_BASE}/${CERT_DOMAIN}/${PREFIX}key.pem
CSR=${ACME_BASE}/${CERT_DOMAIN}/${PREFIX}acme.csr

if [[ -z ${CERT_DOMAINS[*]} ]]; then
  log_f "Missing CERT_DOMAINS to obtain a certificate"
  exit 3
fi

if [[ "${LE_STAGING}" =~ ^([yY][eE][sS]|[yY])+$ ]]; then
  log_f "Using Let's Encrypt staging servers"
  STAGING_PARAMETER='--directory-url https://acme-staging-v02.api.letsencrypt.org/directory'
else
  STAGING_PARAMETER=
fi

if [[ -f ${DOMAINS_FILE} && "$(cat ${DOMAINS_FILE})" ==  "${CERT_DOMAINS[*]}" ]]; then
  if [[ ! -f ${CERT} || ! -f "${KEY}" || -f "${ACME_BASE}/force_renew" ]]; then
    log_f "Certificate ${CERT} doesn't exist yet or forced renewal - start obtaining"
  # Certificate exists and did not change but could be due for renewal (30 days)
  elif ! openssl x509 -checkend 2592000 -noout -in ${CERT} > /dev/null; then
    log_f "Certificate ${CERT} is due for renewal (< 30 days) - start renewing"
  else
    log_f "Certificate ${CERT} validation done, neither changed nor due for renewal."
    exit 2
  fi
else
  log_f "Certificate ${CERT} missing or changed domains '${CERT_DOMAINS[*]}' - start obtaining"
fi


# Make backup
if [[ -f ${CERT} ]]; then
  DATE=$(date +%Y-%m-%d_%H_%M_%S)
  BACKUP_DIR=${ACME_BASE}/backups/${CERT_DOMAIN}/${PREFIX}${DATE}
  log_f "Creating backups in ${BACKUP_DIR} ..."
  mkdir -p ${BACKUP_DIR}/
  [[ -f ${DOMAINS_FILE} ]] && cp ${DOMAINS_FILE} ${BACKUP_DIR}/
  [[ -f ${CERT} ]] && cp ${CERT} ${BACKUP_DIR}/
  [[ -f ${KEY} ]] && cp ${KEY} ${BACKUP_DIR}/
  [[ -f ${CSR} ]] && cp ${CSR} ${BACKUP_DIR}/
fi

mkdir -p ${ACME_BASE}/${CERT_DOMAIN}
if [[ ! -f ${KEY} ]]; then
  log_f "Copying shared private key for this certificate..."
  cp ${SHARED_KEY} ${KEY}
  chmod 600 ${KEY}
fi

# Generating CSR
printf "[SAN]\nsubjectAltName=" > /tmp/_SAN
printf "DNS:%s," "${CERT_DOMAINS[@]}" >> /tmp/_SAN
sed -i '$s/,$//' /tmp/_SAN
openssl req -new -sha256 -key ${KEY} -subj "/" -reqexts SAN -config <(cat /etc/ssl/openssl.cnf /tmp/_SAN) > ${CSR}

# acme-tiny writes info to stderr and ceritifcate to stdout
# The redirects will do the following:
# - redirect stdout to temp certificate file
# - redirect acme-tiny stderr to stdout (logs to variable ACME_RESPONSE)
# - tee stderr to get live output and log to dockerd

ACME_RESPONSE=$(acme-tiny ${STAGING_PARAMETER} \
  --account-key ${ACME_BASE}/acme/account.pem \
  --disable-check \
  --csr ${CSR} \
  --acme-dir /var/www/acme/ 2>&1 > /tmp/_cert.pem | tee /dev/fd/5; exit ${PIPESTATUS[0]})
SUCCESS="$?"
ACME_RESPONSE_B64=$(echo "${ACME_RESPONSE}" | openssl enc -e -A -base64)
log_f "${ACME_RESPONSE_B64}" redis_only b64
case "$SUCCESS" in
  0) # cert requested
    log_f "Deploying certificate ${CERT}..."
    # Deploy the new certificate and key
    # Moving temp cert to {domain} folder
    if verify_hash_match /tmp/_cert.pem ${KEY}; then
      RETURN=0  # certificate created
      if [[ -f ${CERT} ]]; then
        RETURN=1  # certificate renewed
      fi
      mv -f /tmp/_cert.pem ${CERT}
      echo -n ${CERT_DOMAINS[*]} > ${DOMAINS_FILE}
      rm /var/www/acme/* 2> /dev/null
      log_f "Certificate successfully obtained"
      exit ${RETURN}
    else
      log_f "Certificate was successfully requested, but key and certificate have non-matching hashes, ignoring certificate"
      exit 4
    fi
    ;;
  *) # non-zero is non-fun
    log_f "Failed to obtain certificate ${CERT} for domains '${CERT_DOMAINS[*]}'"
    redis-cli -h redis SET ACME_FAIL_TIME "$(date +%s)"
    exit 100${SUCCESS}
    ;;
esac

#!/bin/bash

set -o pipefail

if grep --help 2>&1 | grep -q -i "busybox"; then
  echo "BusybBox grep detected, please install gnu grep, \"apk add --no-cache --upgrade grep\""
  exit 1
fi
if cp --help 2>&1 | grep -q -i "busybox"; then
  echo "BusybBox cp detected, please install coreutils, \"apk add --no-cache --upgrade coreutils\""
  exit 1
fi

if [[ -f mailcow.conf ]]; then
  read -r -p "A config file exists and will be overwritten, are you sure you want to continue? [y/N] " response
  case $response in
    [yY][eE][sS]|[yY])
      mv mailcow.conf mailcow.conf_backup
      ;;
    *)
      exit 1
    ;;
  esac
fi

if [ -z "$MAILCOW_HOSTNAME" ]; then
  read -p "Hostname (FQDN - example.org is not a valid FQDN): " -ei "mx.example.org" MAILCOW_HOSTNAME
fi

if [[ -a /etc/timezone ]]; then
  TZ=$(cat /etc/timezone)
elif  [[ -a /etc/localtime ]]; then
  TZ=$(readlink /etc/localtime|sed -n 's|^.*zoneinfo/||p')
fi

if [ -z "$TZ" ]; then
  read -p "Timezone: " -ei "Europe/Berlin" TZ
else
  read -p "Timezone: " -ei ${TZ} TZ
fi

MEM_TOTAL=$(awk '/MemTotal/ {print $2}' /proc/meminfo)

if [ ${MEM_TOTAL} -le "1572864" ]; then
  echo "Installed memory is less than 1.5 GiB. It is recommended to disable ClamAV to prevent out-of-memory situations."
  read -r -p  "Do you want to disable ClamAV now? ClamAV can be re-enabled by setting SKIP_CLAMD=n in mailcow.conf. [Y/n] " response
  case $response in
    [nN][oO]|[nN])
      SKIP_CLAMD=n
      ;;
    *)
      SKIP_CLAMD=y
    ;;
  esac
else
 SKIP_CLAMD=n
fi

if [ ${MEM_TOTAL} -le "6815744" ]; then
  echo "Installed memory is less than 6.5 GiB. It is highly recommended to disable Solr to prevent out-of-memory situations."
  echo "Solr is a prone to run OOM and should be monitored. The default Solr heap size is 3072 MiB and should be set according to your expected load in mailcow.conf."
  read -r -p  "Do you want to disable Solr now (recommended)? Solr can be re-enabled by setting SKIP_SOLR=n in mailcow.conf. [Y/n] " response
  case $response in
    [nN][oO]|[nN])
      SKIP_SOLR=n
      ;;
    *)
      SKIP_SOLR=y
    ;;
  esac
else
 SKIP_SOLR=n
fi

[[ ! -f ./data/conf/rspamd/override.d/worker-controller-password.inc ]] && echo '# Placeholder' > ./data/conf/rspamd/override.d/worker-controller-password.inc

DEFAULTPASS=$(</dev/urandom tr -dc A-Za-z0-9 | head -c 15)

cat << EOF > mailcow.conf
# ------------------------------
# mailcow web ui configuration
# ------------------------------
# example.org is _not_ a valid hostname, use a fqdn here.
# Default admin user is "admin"
# Default password is "${DEFAULTPASS}"
MAILCOW_HOSTNAME=${MAILCOW_HOSTNAME}

# ------------------------------
# SQL database configuration
# ------------------------------
DBNAME=mailcow
DBUSER=mailcow

# ------------------------------
# Web Admin Password
# DEFAULTPASS defines the default admin password to be injected when no superadmin was found.
# This parameters value has no effect after the first database initialization.
# You are able to change your administrators credentials using the web interface.
# ------------------------------
DEFAULTPASS=${DEFAULTPASS}

# Please use long, random alphanumeric strings (A-Za-z0-9)
DBPASS=$(</dev/urandom tr -dc A-Za-z0-9 | head -c 28)
DBROOT=$(</dev/urandom tr -dc A-Za-z0-9 | head -c 28)

# ------------------------------
# HTTP/S Bindings
# ------------------------------

# You should use HTTPS, but in case of SSL offloaded reverse proxies:
HTTP_PORT=80
HTTP_BIND=0.0.0.0

HTTPS_PORT=443
HTTPS_BIND=0.0.0.0

# ------------------------------
# Other bindings
# ------------------------------
# You should leave that alone
# Format: 11.22.33.44:25 or 0.0.0.0:465 etc.
# Do _not_ use IP:PORT in HTTP(S)_BIND or HTTP(S)_PORT

SMTP_PORT=25
SMTPS_PORT=465
SUBMISSION_PORT=587
IMAP_PORT=143
IMAPS_PORT=993
POP_PORT=110
POPS_PORT=995
SIEVE_PORT=4190
DOVEADM_PORT=127.0.0.1:19991
SQL_PORT=127.0.0.1:13306

# Your timezone
TZ=${TZ}

# Fixed project name
COMPOSE_PROJECT_NAME=mailcow-dockerized

# Additional SAN for the certificate
ADDITIONAL_SAN=

# Skip running ACME (acme-mailcow, Let's Encrypt certs) - y/n
SKIP_LETS_ENCRYPT=n

# Skip IPv4 check in ACME container - y/n
SKIP_IP_CHECK=n

# Skip ClamAV (clamd-mailcow) anti-virus (Rspamd will auto-detect a missing ClamAV container) - y/n
SKIP_CLAMD=$(echo ${SKIP_CLAMD})

# Skip Solr - y/n
SKIP_SOLR=$(echo ${SKIP_SOLR})

# Enable watchdog (watchdog-mailcow) to restart unhealthy containers (experimental)
USE_WATCHDOG=n
# Send notifications by mail (no DKIM signature, sent from watchdog@MAILCOW_HOSTNAME)
#WATCHDOG_NOTIFY_EMAIL=

# Max log lines per service to keep in Redis logs
LOG_LINES=9999

# Internal IPv4 /24 subnet, format n.n.n. (expands to n.n.n.0/24)
IPV4_NETWORK=172.22.1

# Internal IPv6 subnet in fc00::/7
IPV6_NETWORK=fd4d:6169:6c63:6f77::/64

# Use this IP for outgoing connections (SNAT)
#SNAT_TO_SOURCE=

# Disable IPv6
# mailcow-network will still be created as IPv6 enabled, all containers will be created
# without IPv6 support.
# Use 1 for disabled, 0 for enabled
SYSCTL_IPV6_DISABLED=0

EOF

mkdir -p data/assets/ssl

# copy but don't overwrite existing certificate
cp -n data/assets/ssl-example/*.pem data/assets/ssl/

echo
echo
echo "Initial credentials"
echo "---------------------------------"
echo "Username: admin"
echo "Password: ${DEFAULTPASS}"
echo

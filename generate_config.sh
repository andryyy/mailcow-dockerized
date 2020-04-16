#!/usr/bin/env bash

set -o pipefail

if [[ "$(uname -r)" =~ ^4\.15\.0-60 ]]; then
  echo "DO NOT RUN mailcow ON THIS UBUNTU KERNEL!";
  echo "Please update to 5.x or use another distribution."
  exit 1
fi

if [[ "$(uname -r)" =~ ^4\.4\. ]]; then
  if grep -q Ubuntu <<< $(uname -a); then
    echo "DO NOT RUN mailcow ON THIS UBUNTU KERNEL!";
    echo "Please update to linux-generic-hwe-16.04 by running \"apt-get install --install-recommends linux-generic-hwe-16.04\""
  fi
  exit 1
fi

if grep --help 2>&1 | grep -q -i "busybox"; then
  echo "BusybBox grep detected, please install gnu grep, \"apk add --no-cache --upgrade grep\""
  exit 1
fi
if cp --help 2>&1 | grep -q -i "busybox"; then
  echo "BusybBox cp detected, please install coreutils, \"apk add --no-cache --upgrade coreutils\""
  exit 1
fi

if [ -f mailcow.conf ]; then
  read -r -p "A config file exists and will be overwritten, are you sure you want to contine? [y/N] " response
  case $response in
    [yY][eE][sS]|[yY])
      mv mailcow.conf mailcow.conf_backup
      chmod 600 mailcow.conf_backup
      ;;
    *)
      exit 1
    ;;
  esac
fi

echo "Press enter to confirm the detected value '[value]' where applicable or enter a custom value."
while [ -z "${MAILCOW_HOSTNAME}" ]; do
  read -p "Mail server hostname (FQDN) - this is not your mail domain, but your mail servers hostname: " -e MAILCOW_HOSTNAME
  DOTS=${MAILCOW_HOSTNAME//[^.]};
  if [ ${#DOTS} -lt 2 ] && [ ! -z ${MAILCOW_HOSTNAME} ]; then
    echo "${MAILCOW_HOSTNAME} is not a FQDN"
    MAILCOW_HOSTNAME=
  fi
done

if [ -a /etc/timezone ]; then
  DETECTED_TZ=$(cat /etc/timezone)
elif [ -a /etc/localtime ]; then
  DETECTED_TZ=$(readlink /etc/localtime|sed -n 's|^.*zoneinfo/||p')
fi

while [ -z "${MAILCOW_TZ}" ]; do
  if [ -z "${DETECTED_TZ}" ]; then
    read -p "Timezone: " -e MAILCOW_TZ
  else
    read -p "Timezone [${DETECTED_TZ}]: " -e MAILCOW_TZ
    [ -z "${MAILCOW_TZ}" ] && MAILCOW_TZ=${DETECTED_TZ}
  fi
done

MEM_TOTAL=$(awk '/MemTotal/ {print $2}' /proc/meminfo)

if [ ${MEM_TOTAL} -le "2621440" ]; then
  echo "Installed memory is <= 2.5 GiB. It is recommended to disable ClamAV to prevent out-of-memory situations."
  echo "ClamAV can be re-enabled by setting SKIP_CLAMD=n in mailcow.conf."
  read -r -p  "Do you want to disable ClamAV now? [Y/n] " response
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

if [ ${MEM_TOTAL} -le "2097152" ]; then
  echo "Disabling Solr on low-memory system."
  SKIP_SOLR=y
elif [ ${MEM_TOTAL} -le "3670016" ]; then
  echo "Installed memory is <= 3.5 GiB. It is recommended to disable Solr to prevent out-of-memory situations."
  echo "Solr is a prone to run OOM and should be monitored. The default Solr heap size is 1024 MiB and should be set in mailcow.conf according to your expected load."
  echo "Solr can be re-enabled by setting SKIP_SOLR=n in mailcow.conf but will refuse to start with less than 2 GB total memory."
  read -r -p  "Do you want to disable Solr now? [Y/n] " response
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

[ ! -f ./data/conf/rspamd/override.d/worker-controller-password.inc ] && echo '# Placeholder' > ./data/conf/rspamd/override.d/worker-controller-password.inc

cat << EOF > mailcow.conf
# ------------------------------
# mailcow web ui configuration
# ------------------------------
# example.org is _not_ a valid hostname, use a fqdn here.
# Default admin user is "admin"
# Default password is "moohoo"

MAILCOW_HOSTNAME=${MAILCOW_HOSTNAME}

# ------------------------------
# SQL database configuration
# ------------------------------

DBNAME=mailcow
DBUSER=mailcow

# Please use long, random alphanumeric strings (A-Za-z0-9)

DBPASS=$(LC_ALL=C </dev/urandom tr -dc A-Za-z0-9 | head -c 28)
DBROOT=$(LC_ALL=C </dev/urandom tr -dc A-Za-z0-9 | head -c 28)

# ------------------------------
# HTTP/S Bindings
# ------------------------------

# You should use HTTPS, but in case of SSL offloaded reverse proxies:
# Might be important: This will also change the binding within the container.
# If you use a proxy within Docker, point it to the ports you set below.
# IMPORTANT: Do not use port 8081, 9081 or 65510!

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
SOLR_PORT=127.0.0.1:18983
REDIS_PORT=127.0.0.1:7654

# Your timezone

TZ=${MAILCOW_TZ}

# Fixed project name

COMPOSE_PROJECT_NAME=mailcowdockerized

# Set this to "allow" to enable the anyone pseudo user. Disabled by default.
# When enabled, ACL can be created, that apply to "All authenticated users"
# This should probably only be activated on mail hosts, that are used exclusivly by one organisation.
# Otherwise a user might share data with too many other users.
ACL_ANYONE=disallow

# Garbage collector cleanup
# Deleted domains and mailboxes are moved to /var/vmail/_garbage/timestamp_sanitizedstring
# How long should objects remain in the garbage until they are being deleted? (value in minutes)
# Check interval is hourly

MAILDIR_GC_TIME=1440

# Additional SAN for the certificate
#
# You can use wildcard records to create specific names for every domain you add to mailcow.
# Example: Add domains "example.com" and "example.net" to mailcow, change ADDITIONAL_SAN to a value like:
#ADDITIONAL_SAN=imap.*,smtp.*
# This will expand the certificate to "imap.example.com", "smtp.example.com", "imap.example.net", "imap.example.net"
# plus every domain you add in the future.
#
# You can also just add static names...
#ADDITIONAL_SAN=srv1.example.net
# ...or combine wildcard and static names:
#ADDITIONAL_SAN=imap.*,srv1.example.com
#

ADDITIONAL_SAN=

# Skip running ACME (acme-mailcow, Let's Encrypt certs) - y/n

SKIP_LETS_ENCRYPT=n

# Create seperate certificates for all domains - y/n
# this will allow adding more than 100 domains, but some email clients will not be able to connect with alternative hostnames
# see https://wiki.dovecot.org/SSL/SNIClientSupport
ENABLE_SSL_SNI=n

# Skip issuing Let's Encrypt ECDSA certificates - y/n

SKIP_ECDSA_CERT=n

# Skip IPv4 check in ACME container - y/n

SKIP_IP_CHECK=n

# Skip HTTP verification in ACME container - y/n

SKIP_HTTP_VERIFICATION=n

# Skip ClamAV (clamd-mailcow) anti-virus (Rspamd will auto-detect a missing ClamAV container) - y/n

SKIP_CLAMD=${SKIP_CLAMD}

# Skip Solr on low-memory systems or if you do not want to store a readable index of your mails in solr-vol-1.

SKIP_SOLR=${SKIP_SOLR}

# Solr heap size in MB, there is no recommendation, please see Solr docs.
# Solr is a prone to run OOM and should be monitored. Unmonitored Solr setups are not recommended.

SOLR_HEAP=1024

# Enable watchdog (watchdog-mailcow) to restart unhealthy containers (experimental)

USE_WATCHDOG=n

# Allow admins to log into SOGo as email user (without any password)

ALLOW_ADMIN_EMAIL_LOGIN=n

# Send notifications by mail (sent from watchdog@MAILCOW_HOSTNAME)
# CAUTION:
# 1. You should use external recipients
# 2. Mails are sent unsigned (no DKIM)
# 3. If you use DMARC, create a separate DMARC policy ("v=DMARC1; p=none;" in _dmarc.MAILCOW_HOSTNAME)
# Multiple rcpts allowed, NO quotation marks, NO spaces

#WATCHDOG_NOTIFY_EMAIL=a@example.com,b@example.com,c@example.com
#WATCHDOG_NOTIFY_EMAIL=

# Notify about banned IP (includes whois lookup)
WATCHDOG_NOTIFY_BAN=y

# Checks if mailcow is an open relay. Requires a SAL. More checks will follow.
# https://www.servercow.de/mailcow?lang=en
# https://www.servercow.de/mailcow?lang=de
# No data is collected. Opt-in and anonymous.
# Will only work with unmodified mailcow setups.
WATCHDOG_EXTERNAL_CHECKS=n

# Max log lines per service to keep in Redis logs

LOG_LINES=9999

# Internal IPv4 /24 subnet, format n.n.n (expands to n.n.n.0/24)

IPV4_NETWORK=172.22.1

# Internal IPv6 subnet in fc00::/7

IPV6_NETWORK=fd4d:6169:6c63:6f77::/64

# Use this IPv4 for outgoing connections (SNAT)

#SNAT_TO_SOURCE=

# Use this IPv6 for outgoing connections (SNAT)

#SNAT6_TO_SOURCE=

# Create or override an API key for the web UI
# You _must_ define API_ALLOW_FROM, which is a comma separated list of IPs
# An API key defined as API_KEY has read-write access
# An API key defined as API_KEY_READ_ONLY has read-only access
# Allowed chars for API_KEY and API_KEY_READ_ONLY: a-z, A-Z, 0-9, -
# You can define API_KEY and/or API_KEY_READ_ONLY

#API_KEY=
#API_KEY_READ_ONLY=
#API_ALLOW_FROM=172.22.1.1,127.0.0.1

# mail_home is ~/Maildir
MAILDIR_SUB=Maildir

# SOGo session timeout in minutes
SOGO_EXPIRE_SESSION=480

EOF

mkdir -p data/assets/ssl

chmod 600 mailcow.conf

# copy but don't overwrite existing certificate
cp -n -d data/assets/ssl-example/*.pem data/assets/ssl/

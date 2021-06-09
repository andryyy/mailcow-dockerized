#!/bin/bash

log_f() {
  if [[ ${2} == "no_nl" ]]; then
    echo -n "$(date) - ${1}"
  elif [[ ${2} == "no_date" ]]; then
    echo "${1}"
  elif [[ ${2} != "redis_only" ]]; then
    echo "$(date) - ${1}"
  fi
  if [[ ${3} == "b64" ]]; then
    ${REDIS_CMDLINE} LPUSH ACME_LOG "{\"time\":\"$(date +%s)\",\"message\":\"base64,$(printf '%s' "${MAILCOW_HOSTNAME} - ${1}")\"}" > /dev/null
  else
    ${REDIS_CMDLINE} LPUSH ACME_LOG "{\"time\":\"$(date +%s)\",\"message\":\"$(printf '%s' "${MAILCOW_HOSTNAME} - ${1}" | \
      tr '%&;$"[]{}-\r\n' ' ')\"}" > /dev/null
  fi
}

verify_email(){
  regex="^(([A-Za-z0-9]+((\.|\-|\_|\+)?[A-Za-z0-9]?)*[A-Za-z0-9]+)|[A-Za-z0-9]+)@(([A-Za-z0-9]+)+((\.|\-|\_)?([A-Za-z0-9]+)+)*)+\.([A-Za-z]{2,})+$"
  if [[ $1 =~ ${regex} ]]; then
    return 0
  else
    return 1
  fi
}

verify_hash_match(){
  CERT_HASH=$(openssl x509 -in "${1}" -noout -pubkey | openssl md5)
  KEY_HASH=$(openssl pkey -in "${2}" -pubout | openssl md5)
  if [[ ${CERT_HASH} != ${KEY_HASH} ]]; then
    log_f "Certificate and key hashes do not match!"
    return 1
  else
    log_f "Verified hashes."
    return 0
  fi
}

get_ipv4(){
  local IPV4=
  local IPV4_SRCS=
  local TRY=
  IPV4_SRCS[0]="ip4.mailcow.email"
  IPV4_SRCS[1]="ip4.nevondo.com"
  until [[ ! -z ${IPV4} ]] || [[ ${TRY} -ge 10 ]]; do
    IPV4=$(curl --connect-timeout 3 -m 10 -L4s ${IPV4_SRCS[$RANDOM % ${#IPV4_SRCS[@]} ]} | grep -E "^((25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$")
    [[ ! -z ${TRY} ]] && sleep 1
    TRY=$((TRY+1))
  done
  echo ${IPV4}
}

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

check_domain(){
    DOMAIN=$1
    A_DOMAIN=$(dig A ${DOMAIN} +short | tail -n 1)
    AAAA_DOMAIN=$(dig AAAA ${DOMAIN} +short | tail -n 1)
    # Hard-fail on CAA errors for MAILCOW_HOSTNAME
    PARENT_DOMAIN=$(echo ${DOMAIN} | cut -d. -f2-)
    CAAS=( $(dig CAA ${PARENT_DOMAIN} +short | sed -n 's/\d issue "\(.*\)"/\1/p') )
    if [[ ! -z ${CAAS} ]]; then
      if [[ ${CAAS[@]} =~ "letsencrypt.org" ]]; then
        log_f "Validated CAA for parent domain ${PARENT_DOMAIN}"
      else
        log_f "Lets Encrypt disallowed for ${PARENT_DOMAIN} by CAA record"
        return 1
      fi
    fi
    # Check if CNAME without v6 enabled target
    if [[ ! -z ${AAAA_DOMAIN} ]] && [[ -z $(echo ${AAAA_DOMAIN} | grep "^\([0-9a-fA-F]\{0,4\}:\)\{1,7\}[0-9a-fA-F]\{0,4\}$") ]]; then
      AAAA_DOMAIN=
    fi
    if [[ ! -z ${AAAA_DOMAIN} ]]; then
      log_f "Found AAAA record for ${DOMAIN}: ${AAAA_DOMAIN} - skipping A record check"
      if [[ $(expand ${IPV6:-"0000:0000:0000:0000:0000:0000:0000:0000"}) == $(expand ${AAAA_DOMAIN}) ]] || [[ ${SKIP_IP_CHECK} == "y" ]] || [[ ${SNAT6_TO_SOURCE} != "n" ]]; then
        if verify_challenge_path "${DOMAIN}" 6; then
          log_f "Confirmed AAAA record with IP $(expand ${AAAA_DOMAIN})"
          return 0
        else
          log_f "Confirmed AAAA record with IP $(expand ${AAAA_DOMAIN}), but HTTP validation failed"
        fi
      else
        log_f "Cannot match your IP $(expand ${IPV6:-"0000:0000:0000:0000:0000:0000:0000:0000"}) against hostname ${DOMAIN} (DNS returned $(expand ${AAAA_DOMAIN}))"
      fi
    elif [[ ! -z ${A_DOMAIN} ]]; then
      log_f "Found A record for ${DOMAIN}: ${A_DOMAIN}"
      if [[ ${IPV4:-ERR} == ${A_DOMAIN} ]] || [[ ${SKIP_IP_CHECK} == "y" ]] || [[ ${SNAT_TO_SOURCE} != "n" ]]; then
        if verify_challenge_path "${DOMAIN}" 4; then
          log_f "Confirmed A record ${A_DOMAIN}"
          return 0
        else
          log_f "Confirmed A record with IP ${A_DOMAIN}, but HTTP validation failed"
        fi
      else
        log_f "Cannot match your IP ${IPV4} against hostname ${DOMAIN} (DNS returned ${A_DOMAIN})"
      fi
    else
      log_f "No A or AAAA record found for hostname ${DOMAIN}"
    fi
    return 1
}

verify_challenge_path(){
  if [[ ${SKIP_HTTP_VERIFICATION} == "y" ]]; then
    echo '(skipping check, returning 0)'
    return 0
  fi
  # verify_challenge_path URL 4|6
  RANDOM_N=${RANDOM}${RANDOM}${RANDOM}
  echo ${RANDOM_N} > /var/www/acme/${RANDOM_N}
  if [[ "$(curl --insecure -${2} -L http://${1}/.well-known/acme-challenge/${RANDOM_N} --silent)" == "${RANDOM_N}"  ]]; then
    rm /var/www/acme/${RANDOM_N}
    return 0
  else
    rm /var/www/acme/${RANDOM_N}
    return 1
  fi
}

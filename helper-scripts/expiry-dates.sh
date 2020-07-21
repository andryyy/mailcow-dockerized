#!/usr/bin/env bash

[[ -f mailcow.conf ]] && source mailcow.conf
[[ -f ../mailcow.conf ]] && source ../mailcow.conf

POSTFIX=$(echo | openssl s_client -connect ${MAILCOW_HOSTNAME}:25 -starttls smtp 2>/dev/null | openssl x509 -inform pem -noout -enddate | cut -d "=" -f 2)
DOVECOT=$(echo | openssl s_client -connect ${MAILCOW_HOSTNAME}:143 -starttls imap 2>/dev/null | openssl x509 -inform pem -noout -enddate | cut -d "=" -f 2)
NGINX=$(echo | openssl s_client -connect ${MAILCOW_HOSTNAME}:443 2>/dev/null | openssl x509 -inform pem -noout -enddate | cut -d "=" -f 2)
echo TLS expiry dates:
echo Postfix: ${POSTFIX}
echo Dovecot: ${DOVECOT}
echo Nginx: ${NGINX}

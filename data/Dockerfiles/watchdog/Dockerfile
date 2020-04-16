FROM alpine:3.10
LABEL maintainer "André Peters <andre.peters@servercow.de>"

# Installation
RUN apk add --update \
  && apk add --no-cache nagios-plugins-smtp \
  nagios-plugins-tcp \
  nagios-plugins-http \
  nagios-plugins-ping \
  mariadb-client \
  curl \
  bash \
  coreutils \
  jq \
  fcgi \
  openssl \
  nagios-plugins-mysql \
  nagios-plugins-dns \
  nagios-plugins-disk \
  bind-tools \
  redis \
  perl \
  perl-net-dns \
  perl-io-socket-ssl \
  perl-io-socket-inet6 \
  perl-socket \
  perl-socket6 \
  perl-mime-lite \
  perl-term-readkey \
  tini \
  tzdata \
  whois \
  && curl https://raw.githubusercontent.com/mludvig/smtp-cli/v3.10/smtp-cli -o /smtp-cli \
  && chmod +x smtp-cli

COPY watchdog.sh /watchdog.sh
COPY check_mysql_slavestatus.sh /usr/lib/nagios/plugins/check_mysql_slavestatus.sh

CMD /watchdog.sh 2> /dev/null

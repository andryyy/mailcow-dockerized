FROM debian:buster-slim
LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

ARG DEBIAN_FRONTEND=noninteractive
ENV LC_ALL C

# Add groups and users before installing Dovecot to not break compatibility
RUN groupadd -g 5000 vmail \
  && groupadd -g 401 dovecot \
  && groupadd -g 402 dovenull \
  && useradd -g vmail -u 5000 vmail -d /var/vmail \
  && useradd -c "Dovecot unprivileged user" -d /dev/null -u 401 -g dovecot -s /bin/false dovecot \
  && useradd -c "Dovecot login user" -d /dev/null -u 402 -g dovenull -s /bin/false dovenull \
  && touch /etc/default/locale \
  && apt-get update \
  && apt-get -y --no-install-recommends install \
  apt-transport-https \
  ca-certificates \
  cpanminus \
  cron \
  curl \
  dnsutils \
  dirmngr \
  gettext \
  gnupg2 \
  jq \
  libauthen-ntlm-perl \
  libcgi-pm-perl \
  libcrypt-openssl-rsa-perl \
  libcrypt-ssleay-perl \
  libdata-uniqid-perl \
  libdbd-mysql-perl \
  libdbi-perl \
  libdigest-hmac-perl \
  libdist-checkconflicts-perl \
  libfile-copy-recursive-perl \
  libfile-tail-perl \
  libhtml-parser-perl \
  libio-compress-perl \
  libio-socket-inet6-perl \
  libio-socket-ssl-perl \
  libio-tee-perl \
  libipc-run-perl \
  libjson-webtoken-perl \
  liblockfile-simple-perl \
  libmail-imapclient-perl \
  libmodule-implementation-perl \
  libmodule-scandeps-perl \
  libnet-ssleay-perl \
  libpackage-stash-perl \
  libpackage-stash-xs-perl \
  libpar-packer-perl \
  libparse-recdescent-perl \
  libproc-processtable-perl \
  libreadonly-perl \
  libregexp-common-perl \
  libsys-meminfo-perl \
  libterm-readkey-perl \
  libtest-deep-perl \
  libtest-fatal-perl \
  libtest-mock-guard-perl \
  libtest-mockobject-perl \
  libtest-nowarnings-perl \
  libtest-pod-perl \
  libtest-requires-perl \
  libtest-simple-perl \
  libtest-warn-perl \
  libtry-tiny-perl \
  libunicode-string-perl \
  liburi-perl \
  libwww-perl \
  mariadb-client \
  procps \
  python3-pip \
  redis-server \
  supervisor \
  syslog-ng \
  syslog-ng-core \
  syslog-ng-mod-redis \
  && apt-key adv --fetch-keys https://repo.dovecot.org/DOVECOT-REPO-GPG \
  && echo 'deb https://repo.dovecot.org/ce-2.3-latest/debian/buster buster main' > /etc/apt/sources.list.d/dovecot.list \
  && apt-get update \
  && apt-get -y --no-install-recommends install \
  dovecot-lua \
  dovecot-managesieved \
  dovecot-sieve \
  dovecot-lmtpd \
  dovecot-ldap \
  dovecot-mysql \
  dovecot-core \
  dovecot-pop3d \
  dovecot-imapd \
  dovecot-solr \
  && pip3 install mysql-connector-python html2text jinja2 redis \
  && apt-get autoremove --purge -y \
  && apt-get autoclean \
  && rm -rf /var/lib/apt/lists/* \
  && rm -rf /tmp/* /var/tmp/* /etc/cron.daily/* /root/.cache/

COPY trim_logs.sh /usr/local/bin/trim_logs.sh
COPY clean_q_aged.sh /usr/local/bin/clean_q_aged.sh
COPY syslog-ng.conf /etc/syslog-ng/syslog-ng.conf
COPY imapsync /usr/local/bin/imapsync
COPY postlogin.sh /usr/local/bin/postlogin.sh
COPY imapsync_cron.pl /usr/local/bin/imapsync_cron.pl
COPY report-spam.sieve /usr/lib/dovecot/sieve/report-spam.sieve
COPY report-ham.sieve /usr/lib/dovecot/sieve/report-ham.sieve
COPY rspamd-pipe-ham /usr/lib/dovecot/sieve/rspamd-pipe-ham
COPY rspamd-pipe-spam /usr/lib/dovecot/sieve/rspamd-pipe-spam
COPY sa-rules.sh /usr/local/bin/sa-rules.sh
COPY maildir_gc.sh /usr/local/bin/maildir_gc.sh
COPY docker-entrypoint.sh /
COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY stop-supervisor.sh /usr/local/sbin/stop-supervisor.sh
COPY quarantine_notify.py /usr/local/bin/quarantine_notify.py
COPY quota_notify.py /usr/local/bin/quota_notify.py

ENTRYPOINT ["/docker-entrypoint.sh"]
CMD exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf

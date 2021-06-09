FROM debian:buster-slim
LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

ARG DEBIAN_FRONTEND=noninteractive
ENV LC_ALL C

RUN dpkg-divert --local --rename --add /sbin/initctl \
	&& ln -sf /bin/true /sbin/initctl \
	&& dpkg-divert --local --rename --add /usr/bin/ischroot \
	&& ln -sf /bin/true /usr/bin/ischroot

# Add groups and users before installing Postfix to not break compatibility
RUN groupadd -g 102 postfix \
  && groupadd -g 103 postdrop \
  && useradd -g postfix -u 101 -d /var/spool/postfix -s /usr/sbin/nologin postfix \
  && apt-get update && apt-get install -y --no-install-recommends \
	ca-certificates \
	curl \
	dirmngr \
  dnsutils \
	gnupg \
	libsasl2-modules \
  mariadb-client \
	perl \
	postfix \
	postfix-mysql \
	postfix-pcre \
	redis-tools \
	sasl2-bin \
	sudo \
	supervisor \
	syslog-ng \
	syslog-ng-core \
	syslog-ng-mod-redis \
  tzdata \
	&& rm -rf /var/lib/apt/lists/* \
	&& touch /etc/default/locale \
  && printf '#!/bin/bash\n/usr/sbin/postconf -c /opt/postfix/conf "$@"' > /usr/local/sbin/postconf \
  && chmod +x /usr/local/sbin/postconf

COPY supervisord.conf /etc/supervisor/supervisord.conf
COPY syslog-ng.conf /etc/syslog-ng/syslog-ng.conf
COPY syslog-ng-redis_slave.conf /etc/syslog-ng/syslog-ng-redis_slave.conf
COPY postfix.sh /opt/postfix.sh
COPY rspamd-pipe-ham /usr/local/bin/rspamd-pipe-ham
COPY rspamd-pipe-spam /usr/local/bin/rspamd-pipe-spam
COPY whitelist_forwardinghosts.sh /usr/local/bin/whitelist_forwardinghosts.sh
COPY stop-supervisor.sh /usr/local/sbin/stop-supervisor.sh
COPY docker-entrypoint.sh /docker-entrypoint.sh

RUN chmod +x /opt/postfix.sh \
  /usr/local/bin/rspamd-pipe-ham \
  /usr/local/bin/rspamd-pipe-spam \
  /usr/local/bin/whitelist_forwardinghosts.sh \
  /usr/local/sbin/stop-supervisor.sh
RUN rm -rf /tmp/* /var/tmp/*

EXPOSE 588

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf

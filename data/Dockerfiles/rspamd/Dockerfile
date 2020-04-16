FROM debian:buster-slim
LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

ARG DEBIAN_FRONTEND=noninteractive
ARG CODENAME=buster
ENV LC_ALL C

RUN apt-get update && apt-get install -y \
  tzdata \
  ca-certificates \
  gnupg2 \
  apt-transport-https \
  dnsutils \
  netcat \
  && apt-key adv --fetch-keys https://rspamd.com/apt-stable/gpg.key \
  && echo "deb [arch=amd64] https://rspamd.com/apt-stable/ $CODENAME main" > /etc/apt/sources.list.d/rspamd.list \
  && apt-get update \
  && apt-get --no-install-recommends -y install rspamd redis-tools \
  && rm -rf /var/lib/apt/lists/* \
  && apt-get autoremove --purge \
  && apt-get clean \
  && mkdir -p /run/rspamd \
  && chown _rspamd:_rspamd /run/rspamd

COPY settings.conf /etc/rspamd/settings.conf
COPY docker-entrypoint.sh /docker-entrypoint.sh

ENTRYPOINT ["/docker-entrypoint.sh"]

STOPSIGNAL SIGTERM

CMD ["/usr/bin/rspamd", "-f", "-u", "_rspamd", "-g", "_rspamd"]

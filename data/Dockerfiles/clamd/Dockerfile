FROM debian:buster-slim

LABEL maintainer "André Peters <andre.peters@servercow.de>"

ARG CLAMAV=0.103.2

RUN apt-get update && apt-get install -y --no-install-recommends \
  ca-certificates \
  zlib1g-dev \
  libcurl4-openssl-dev \
  libncurses5-dev \
  libzip-dev \
  libpcre2-dev \
  libxml2-dev \
  libssl-dev \
  build-essential \
  libjson-c-dev \
  curl \
  bash \
  wget \
  tzdata \
  dnsutils \
  rsync \
  dos2unix \
  netcat \
  && rm -rf /var/lib/apt/lists/* \
  && wget -O - https://www.clamav.net/downloads/production/clamav-${CLAMAV}.tar.gz | tar xfvz - \
  && cd clamav-${CLAMAV} \
  && ./configure \
  --prefix=/usr \
  --libdir=/usr/lib \
  --sysconfdir=/etc/clamav \
  --mandir=/usr/share/man \
  --infodir=/usr/share/info \
  --disable-llvm \
  --with-user=clamav \
  --with-group=clamav \
  --with-dbdir=/var/lib/clamav \
  --enable-clamdtop \
  --enable-bigstack \
  --with-pcre \
  && make -j4 \
  && make install \
  && make clean \
  && cd .. && rm -rf clamav-${CLAMAV} \
  && apt-get -y --auto-remove purge build-essential \
  && apt-get -y purge zlib1g-dev \
  libncurses5-dev \
  libzip-dev \
  libpcre2-dev \
  libxml2-dev \
  libssl-dev \
  libjson-c-dev \
  && addgroup --system --gid 700 clamav \
  && adduser --system --no-create-home --home /var/lib/clamav --uid 700 --gid 700 --disabled-login clamav \
  && rm -rf /tmp/* /var/tmp/*

COPY clamd.sh ./
COPY tini /sbin/tini

CMD ["/sbin/tini", "-g", "--", "/clamd.sh"]

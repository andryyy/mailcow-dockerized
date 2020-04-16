FROM php:7.3-fpm-alpine3.10
LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

ENV APCU_PECL 5.1.18
ENV IMAGICK_PECL 3.4.4
# Mailparse is pulled from master branch
#ENV MAILPARSE_PECL 3.0.2
ENV MEMCACHED_PECL 3.1.5
ENV REDIS_PECL 5.2.1

RUN apk add -U --no-cache autoconf \
  bash \
  c-client \
  cyrus-sasl-dev \
  freetype \
  freetype-dev \
  g++ \
  git \
  gettext-dev \
  gnupg \
  icu-dev \
  icu-libs \
  imagemagick \
  imagemagick-dev \
  imap-dev \
  jq \
  libjpeg-turbo \
  libjpeg-turbo-dev \
  libmemcached-dev \
  libpng \
  libpng-dev \
  libressl \
  libressl-dev \
  librsvg \
  libtool \
  libwebp-dev \
  libxml2-dev \
  libxpm-dev \
  libzip-dev \
  make \
  mysql-client \
  openldap-dev \
  pcre-dev \
  re2c \
  redis \
  samba-client \
  zlib-dev \
  tzdata \
  && git clone https://github.com/php/pecl-mail-mailparse \
  && cd pecl-mail-mailparse \
  && pecl install package.xml \
  && cd .. \
  && rm -r pecl-mail-mailparse \
  && pecl install redis-${REDIS_PECL} memcached-${MEMCACHED_PECL} APCu-${APCU_PECL} imagick-${IMAGICK_PECL} \
  && docker-php-ext-enable apcu imagick memcached mailparse redis \
  && pecl clear-cache \
  && docker-php-ext-configure intl \
  && docker-php-ext-configure exif \
  && docker-php-ext-configure gd \
    --with-gd \
    --with-freetype-dir=/usr/include/ \
    --with-png-dir=/usr/include/ \
    --with-jpeg-dir=/usr/include/ \
  && docker-php-ext-install -j 4 exif gd gettext intl ldap opcache pcntl pdo pdo_mysql soap sockets xmlrpc zip \
  && docker-php-ext-configure imap --with-imap --with-imap-ssl \
  && docker-php-ext-install -j 4 imap \
  && curl --silent --show-error https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && chmod +x /usr/local/bin/composer \
  && apk del --purge autoconf \
    cyrus-sasl-dev \
    freetype-dev \
    g++ \
    icu-dev \
    imagemagick-dev \
    imap-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libressl-dev \
    libwebp-dev \
    libxml2-dev \
    make \
    pcre-dev \
    zlib-dev

COPY ./docker-entrypoint.sh /

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["php-fpm"]

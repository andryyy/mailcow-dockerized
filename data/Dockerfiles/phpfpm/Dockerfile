FROM php:7.4-fpm-alpine3.13
LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

ENV APCU_PECL 5.1.19
ENV IMAGICK_PECL 3.4.4
# Mailparse is pulled from master branch
#ENV MAILPARSE_PECL 3.0.2
ENV MEMCACHED_PECL 3.1.5
ENV REDIS_PECL 5.3.3

RUN apk add -U --no-cache autoconf \
  aspell-dev \
  aspell-libs \
  bash \
  c-client \
  cyrus-sasl-dev \
  freetype \
  freetype-dev \
  g++ \
  git \
  gettext-dev \
  gmp-dev \
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
  && docker-php-ext-configure gd --with-freetype=/usr/include/ \  
    --with-jpeg=/usr/include/ \
  && docker-php-ext-install -j 4 exif gd gettext intl ldap opcache pcntl pdo pdo_mysql pspell soap sockets xmlrpc zip bcmath gmp \
  && docker-php-ext-configure imap --with-imap --with-imap-ssl \
  && docker-php-ext-install -j 4 imap \
  && curl --silent --show-error https://getcomposer.org/installer | php \
  && mv composer.phar /usr/local/bin/composer \
  && chmod +x /usr/local/bin/composer \
  && apk del --purge autoconf \
    aspell-dev \
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

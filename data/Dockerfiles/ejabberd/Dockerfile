FROM ejabberd/ecs:21.01

LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

ENV GOSU_VERSION 1.11

# We need to copy cert files, dropping rights at a later point
USER root

RUN apk add --update --no-cache su-exec \
  bash \
  tini \
  jq \
  mariadb-client \
  redis \
  tzdata \
  curl \
  openssl \
  bind-tools \
  composer \
  php7-pdo \
  php7-pdo_mysql \
  php7-ctype

RUN mkdir -p /var/www/authentication && \
  cd /var/www/authentication && \
  composer require leesherwood/ejabberd-php-auth monolog/monolog

COPY docker-entrypoint.sh /docker-entrypoint.sh
COPY authenticator /var/www/authentication/authenticator
COPY mailcowCommandExecutor.php /var/www/authentication/vendor/leesherwood/ejabberd-php-auth/src/CommandExecutors/mailcowCommandExecutor.php

ENTRYPOINT ["/docker-entrypoint.sh"]

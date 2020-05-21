FROM alpine:3.11

LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

RUN apk add --update --no-cache \
	curl \
	unbound \
	bash \
	openssl \
	drill \
	tzdata \
	&& curl -o /etc/unbound/root.hints https://www.internic.net/domain/named.cache \
	&& chown root:unbound /etc/unbound \
  && adduser unbound tty \
	&& chmod 775 /etc/unbound

EXPOSE 53/udp 53/tcp

COPY docker-entrypoint.sh /docker-entrypoint.sh

ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["/usr/sbin/unbound"]

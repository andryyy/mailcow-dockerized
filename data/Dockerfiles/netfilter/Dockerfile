FROM alpine:3.11
LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

ENV XTABLES_LIBDIR /usr/lib/xtables
ENV PYTHON_IPTABLES_XTABLES_VERSION 12
ENV IPTABLES_LIBDIR /usr/lib

RUN apk add --virtual .build-deps gcc python3-dev libffi-dev openssl-dev \
  && apk add -U python3 iptables ip6tables tzdata musl-dev \
  && pip3 install --upgrade pip python-iptables redis ipaddress dnspython \
#  && pip3 install --upgrade pip python-iptables==0.13.0 redis ipaddress dnspython \
  && apk del .build-deps

COPY server.py /
CMD ["python3", "-u", "/server.py"]

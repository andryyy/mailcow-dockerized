FROM alpine:3.13

LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

WORKDIR /app

RUN apk add --update --no-cache python3 \
  py3-pip \
  openssl \
  tzdata \
&& pip3 install --upgrade pip \
  docker \
  flask \
  flask-restful

COPY dockerapi.py /app/

CMD ["python3", "-u", "/app/dockerapi.py"]

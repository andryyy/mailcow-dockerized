FROM alpine:3.13
LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

WORKDIR /app

#RUN addgroup -S olefy && adduser -S olefy -G olefy \
RUN apk add --virtual .build-deps gcc musl-dev python3-dev libffi-dev openssl-dev cargo \
  && apk add --update --no-cache python3 py3-pip openssl tzdata libmagic \
  && pip3 install --upgrade pip \
  && pip3 install --upgrade asyncio python-magic \
  && pip3 install --upgrade https://github.com/HeinleinSupport/oletools/archive/master.zip \
  && apk del .build-deps
#  && sed -i 's/decompress_stream(bytearray(compressed_code))/bytes2str(decompress_stream(bytearray(compressed_code)))/g' /usr/lib/python3.8/site-packages/oletools/olevba.py

ADD https://raw.githubusercontent.com/HeinleinSupport/olefy/master/olefy.py /app/

RUN chown -R nobody:nobody /app /tmp

USER nobody

CMD ["python3", "-u", "/app/olefy.py"]

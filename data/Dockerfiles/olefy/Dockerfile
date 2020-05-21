FROM alpine:3.11
LABEL maintainer "Andre Peters <andre.peters@servercow.de>"

WORKDIR /app

#RUN addgroup -S olefy && adduser -S olefy -G olefy \
RUN apk add --virtual .build-deps gcc python3-dev musl-dev libffi-dev openssl-dev \
  && apk add --update --no-cache python3 openssl tzdata libmagic \
  && pip3 install --upgrade pip \
  && pip3 install --upgrade oletools asyncio python-magic \
  && apk del .build-deps

ADD https://raw.githubusercontent.com/HeinleinSupport/olefy/master/olefy.py /app/

RUN chown -R nobody:nobody /app /tmp

USER nobody

CMD ["python3", "-u", "/app/olefy.py"]

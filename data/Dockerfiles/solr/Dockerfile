FROM solr:7.7-slim

USER root

ENV GOSU_VERSION 1.11

COPY solr.sh /
COPY solr-config-7.7.0.xml /
COPY solr-schema-7.7.0.xml /

RUN dpkgArch="$(dpkg --print-architecture | awk -F- '{ print $NF }')" \
  && wget -O /usr/local/bin/gosu "https://github.com/tianon/gosu/releases/download/$GOSU_VERSION/gosu-$dpkgArch" \
  && chmod +x /usr/local/bin/gosu \
  && gosu nobody true \
  && apt-get update && apt-get install -y --no-install-recommends \
  tzdata \
  curl \
  bash \
  && apt-get autoclean \
  && rm -rf /var/lib/apt/lists/* \
  && chmod +x /solr.sh \
  && sync \
  && bash /solr.sh --bootstrap

CMD ["/solr.sh"]

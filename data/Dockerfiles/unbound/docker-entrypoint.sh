#!/bin/bash

echo "Setting console permissions..."
chown root:tty /dev/console
chmod g+rw /dev/console
echo "Receiving anchor key..."
/usr/sbin/unbound-anchor -a /etc/unbound/trusted-key.key
echo "Receiving root hints..."
curl -#o /etc/unbound/root.hints https://www.internic.net/domain/named.cache
/usr/sbin/unbound-control-setup

# Run hooks
for file in /hooks/*; do
  if [ -x "${file}" ]; then
    echo "Running hook ${file}"
    "${file}"
  fi
done

exec "$@"

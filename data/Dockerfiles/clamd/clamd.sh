#!/bin/bash

if [[ "${SKIP_CLAMD}" =~ ^([yY][eE][sS]|[yY])+$ ]]; then
  echo "SKIP_CLAMD=y, skipping ClamAV..."
  sleep 365d
  exit 0
fi

# Cleaning up garbage
echo "Cleaning up tmp files..."
rm -rf /var/lib/clamav/clamav-*.tmp

# Prepare whitelist

mkdir -p /run/clamav /var/lib/clamav

if [[ -s /etc/clamav/whitelist.ign2 ]]; then
  echo "Copying non-empty whitelist.ign2 to /var/lib/clamav/whitelist.ign2"
  cp /etc/clamav/whitelist.ign2 /var/lib/clamav/whitelist.ign2
fi
if [[ ! -f /var/lib/clamav/whitelist.ign2 ]]; then
  echo "Creating /var/lib/clamav/whitelist.ign2"
  echo "Example-Signature.Ignore-1" > /var/lib/clamav/whitelist.ign2
fi

chown clamav:clamav -R /var/lib/clamav /run/clamav

chmod 755 /var/lib/clamav
chmod 644 -R /var/lib/clamav/*
chmod 750 /run/clamav

echo "Stating whitelist.ign2"
stat /var/lib/clamav/whitelist.ign2

dos2unix /var/lib/clamav/whitelist.ign2

sed -i '/^\s*$/d' /var/lib/clamav/whitelist.ign2

BACKGROUND_TASKS=()

(
while true; do
  sleep 1m
  freshclam
  sleep 1h
done
) &
BACKGROUND_TASKS+=($!)

(
while true; do
  sleep 2m
  SANE_MIRRORS="$(dig +ignore +short rsync.sanesecurity.net)"
  for sane_mirror in ${SANE_MIRRORS}; do
    CE=
    rsync -avp --chown=clamav:clamav --chmod=Du=rwx,Dgo=rx,Fu=rw,Fog=r --timeout=5 rsync://${sane_mirror}/sanesecurity/ \
      --include 'blurl.ndb' \
      --include 'junk.ndb' \
      --include 'jurlbl.ndb' \
      --include 'jurbla.ndb' \
      --include 'phishtank.ndb' \
      --include 'phish.ndb' \
      --include 'spamimg.hdb' \
      --include 'scam.ndb' \
      --include 'rogue.hdb' \
      --include 'sanesecurity.ftm' \
      --include 'sigwhitelist.ign2' \
      --exclude='*' /var/lib/clamav/
    CE=$?
    chmod 755 /var/lib/clamav/
    if [ ${CE} -eq 0 ]; then
      echo RELOAD | nc localhost 3310
      break
    fi
  done
  sleep 30h
done
) &
BACKGROUND_TASKS+=($!)

nice -n10 clamd &
BACKGROUND_TASKS+=($!)

while true; do
  for bg_task in ${BACKGROUND_TASKS[*]}; do
    if ! kill -0 ${bg_task} 1>&2; then
      echo "Worker ${bg_task} died, stopping container waiting for respawn..."
      kill -TERM 1
    fi
    sleep 10
  done
done

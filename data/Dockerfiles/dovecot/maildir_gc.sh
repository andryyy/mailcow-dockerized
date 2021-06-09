#!/bin/bash
[ -d /var/vmail/_garbage/ ] && /usr/bin/find /var/vmail/_garbage/ -mindepth 1 -maxdepth 1 -type d -cmin +${MAILDIR_GC_TIME} -exec rm -r {} \;

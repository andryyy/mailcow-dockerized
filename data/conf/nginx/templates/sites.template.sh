echo '
server {
  listen 127.0.0.1:65510;
  include /etc/nginx/conf.d/listen_plain.active;
  include /etc/nginx/conf.d/listen_ssl.active;

  ssl_certificate /etc/ssl/mail/cert.pem;
  ssl_certificate_key /etc/ssl/mail/key.pem;
  ssl_certificate /etc/ssl/mail/ecdsa-cert.pem;
  ssl_certificate_key /etc/ssl/mail/ecdsa-key.pem;

  include /etc/nginx/conf.d/server_name.active;

  include /etc/nginx/conf.d/includes/site-defaults.conf;
}
';
for cert_dir in /etc/ssl/mail/*/ ; do
  if [[ ! -f ${cert_dir}domains ]] || [[ ! -f ${cert_dir}cert.pem ]] || [[ ! -f ${cert_dir}key.pem ]]; then
    continue
  fi
  # do not create vhost for default-certificate. the cert is already in the default server listen
  domains="$(cat ${cert_dir}domains | sed -e 's/^[[:space:]]*//')"
  case "${domains}" in
    "") continue;;
    "${MAILCOW_HOSTNAME}"*) continue;;
  esac
  echo -n '
server {
  include /etc/nginx/conf.d/listen_ssl.active;

  ssl_certificate '${cert_dir}'cert.pem;
  ssl_certificate_key '${cert_dir}'key.pem;
';
  if [[ -f ${cert_dir}ecdsa-cert.pem && -f ${cert_dir}ecdsa-key.pem ]]; then
    echo -n '
  ssl_certificate '${cert_dir}'ecdsa-cert.pem;
  ssl_certificate_key '${cert_dir}'ecdsa-key.pem;
';
  fi
  echo -n '
  server_name '${domains}';

  include /etc/nginx/conf.d/includes/site-defaults.conf;
}
';
done

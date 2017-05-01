## SSL (and: How to use Let's Encrypt)

mailcow dockerized comes with a snakeoil CA "mailcow" and a server certificate in `data/assets/ssl`. Please use your own trusted certificates.

mailcow uses 3 domain names that should be covered by your new certificate:

- ${MAILCOW_HOSTNAME}
- autodiscover.**example.org**
- autoconfig.**example.org**

### Obtain multi-SAN certificate by Let's Encrypt

This is just an example of how to obtain certificates with certbot. There are several methods!

1\. Get the certbot client:
``` bash
wget https://dl.eff.org/certbot-auto -O /usr/local/sbin/certbot && chmod +x /usr/local/sbin/certbot
```

2\. Make sure you set `HTTP_BIND=0.0.0.0` and `HTTP_PORT=80` in `mailcow.conf` or setup a reverse proxy to enable connections to port 80. If you changed HTTP_BIND, then rebuild Nginx:
``` bash
docker-compose up -d
```

3\. Request the certificate with the webroot method:
``` bash
cd /path/to/git/clone/mailcow-dockerized
source mailcow.conf
certbot certonly \
    --webroot \
    -w ${PWD}/data/web \
    -d ${MAILCOW_HOSTNAME} \
    -d autodiscover.example.org \
    -d autoconfig.example.org \
    --email you@example.org \
    --agree-tos
```

**Remember to replace the example.org domain with your own domain, this command will not work if you dont.**
    
4\. Create hard links to the full path of the new certificates. Assuming you are still in the mailcow root folder:
``` bash
mv data/assets/ssl/cert.{pem,pem.backup}
mv data/assets/ssl/key.{pem,pem.backup}
ln $(readlink -f /etc/letsencrypt/live/${MAILCOW_HOSTNAME}/fullchain.pem) data/assets/ssl/cert.pem
ln $(readlink -f /etc/letsencrypt/live/${MAILCOW_HOSTNAME}/privkey.pem) data/assets/ssl/key.pem
```

5\. Restart affected containers:
```
docker-compose restart postfix-mailcow dovecot-mailcow nginx-mailcow
```

When renewing certificates, run the last two steps (link + restart) as post-hook in a script.

## Rspamd Web UI
At first you may want to setup Rspamds web interface which provides some useful features and information.

1\. Generate a Rspamd controller password hash:
```
docker-compose exec rspamd-mailcow rspamadm pw
```

2\. Replace the default hash in `data/conf/rspamd/override.d/worker-controller.inc` by your newly generated:
```
enable_password = "myhash";
```

You can use `password = "myhash";` instead of `enable_password` to disable write-access in the web UI.

3\. Restart rspamd:
```
docker-compose restart rspamd-mailcow
```

Open https://${MAILCOW_HOSTNAME}/rspamd in a browser and login!

## Optional: Reverse proxy

You don't need to change the Nginx site that comes with mailcow: dockerized.
mailcow: dockerized trusts the default gateway IP 172.22.1.1 as proxy. This is very important to control access to Rspamd's web UI.

1\. Make sure you change HTTP_BIND and HTTPS_BIND in `mailcow.conf` to a local address and set the ports accordingly, for example:
``` bash
HTTP_BIND=127.0.0.1
HTTP_PORT=8080
HTTPS_PORT=127.0.0.1
HTTPS_PORT=8443
```
** IMPORTANT: Do not use port 8081 **

Recreate affected containers by running `docker-compose up -d`.

2\. Configure your local webserver as reverse proxy:

### Apache 2.4
``` apache
<VirtualHost *:443>
    ServerName mail.example.org
    ServerAlias autodiscover.example.org
    ServerAlias autoconfig.example.org

    [...]
    # You should proxy to a plain HTTP session to offload SSL processing
    ProxyPass / http://127.0.0.1:8080/
    ProxyPreserveHost Off
    ProxyAddHeaders Off
    RewriteEngine on
    RewriteRule ^(.*) - [E=HOST_HEADER:%{HTTP_HOST},E=CLIENT_IP:%{REMOTE_ADDR},E=PORT_NUMBER:%{SERVER_PORT},L]
    RequestHeader append X-Forwarded-For "%{CLIENT_IP}e"
    RequestHeader set X-Forwarded-Host "%{HOST_HEADER}e"
    RequestHeader set X-Forwarded-Proto "https" env=HTTPS
    RequestHeader set X-Forwarded-Proto "http" env=!HTTPS
    RequestHeader set X-Forwarded-Port "%{PORT_NUMBER}e"
    your-ssl-configuration-here
    [...]

    # If you plan to proxy to a HTTPS host:
    #SSLProxyEngine On
    
    # If you plan to proxy to an untrusted HTTPS host:
    #SSLProxyVerify none
    #SSLProxyCheckPeerCN off
    #SSLProxyCheckPeerName off
    #SSLProxyCheckPeerExpire off
</VirtualHost>
```

### Nginx
```
server {
    listen 443;
    server_name mail.example.org autodiscover.example.org autoconfig.example.org;

    [...]
    your-ssl-configuration-here
    location / {
        proxy_pass http://127.0.0.1:8080/;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Host $host:$server_port;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Port $server_port;
    }
    [...]
}
```

### HAProxy
```
frontend https-in
  bind :::443 v4v6 ssl crt mailcow.pem
  default_backend mailcow

backend mailcow
  option forwardfor
  http-request set-header X-Forwarded-Host %[req.hdr(Host)]
  http-request set-header X-Forwarded-Proto https if { ssl_fc }
  http-request set-header X-Forwarded-Proto http if !{ ssl_fc }
  http-request set-header X-Forwarded-Port %[dst_port]
  server mailcow 127.0.0.1:8080 check
```

## Optional: Setup a relayhost

Insert these lines to `data/conf/postfix/main.cf`. "relayhost" does already exist (empty), just change its value.
```
relayhost = [your-relayhost]:587
smtp_sasl_password_maps = hash:/opt/postfix/conf/smarthost_passwd
smtp_sasl_auth_enable = yes
```

Create the credentials file:
```
echo "your-relayhost username:password" > data/conf/postfix/smarthost_passwd
```

Run:
```
docker-compose exec postfix-mailcow postmap /opt/postfix/conf/smarthost_passwd
docker-compose exec postfix-mailcow chown root:postfix /opt/postfix/conf/smarthost_passwd /opt/postfix/conf/smarthost_passwd.db
docker-compose exec postfix-mailcow chmod 660 /opt/postfix/conf/smarthost_passwd /opt/postfix/conf/smarthost_passwd.db
docker-compose exec postfix-mailcow postfix reload
```

### Helper script

There is a helper script `mailcow-setup-relayhost.sh` you can run to setup a relayhost.

``` bash
Usage:

Setup a relayhost:
./mailcow-setup-relayhost.sh relayhost port (username) (password)
Username and password are optional parameters.

Reset to defaults:
./mailcow-setup-relayhost.sh reset
```

## Optional: Log to Syslog

Enable Rsyslog to receive logs on 524/tcp:

```
# This setting depends on your Rsyslog version and configuration format.
# For most Debian derivates it will work like this...
$ModLoad imtcp
$TCPServerAddress 127.0.0.1
$InputTCPServerRun 524

# ...while for Ubuntu 16.04 it looks like this:
module(load="imtcp")
input(type="imtcp" address="127.0.0.1" port="524")

# No matter your Rsyslog version, you should set this option to off
# if you plan to use Fail2ban
$RepeatedMsgReduction off
```

Restart rsyslog after enabling the TCP listener.

Now setup Docker daemon to start with the syslog driver.
This enables the syslog driver for all containers!

Debian users can change the startup configuration in `/etc/default/docker` while CentOS users find it in `/etc/sysconfig/docker`:
```
...
DOCKER_OPTS="--log-driver=syslog --log-opt syslog-address=tcp://127.0.0.1:524"
...
```

**Caution:** For some reason Ubuntu 16.04 and some, but not all, systemd based distros do not read the defaults file parameters.

Just run `systemctl edit docker.service` and add the following content to fix it.

**Note:** If "systemctl edit" is not available, just copy the content to `/etc/systemd/system/docker.service.d/override.conf`.

The first empty ExecStart parameter is not a mistake.

```
[Service]
EnvironmentFile=/etc/default/docker
ExecStart=
ExecStart=/usr/bin/docker daemon -H fd:// $DOCKER_OPTS
```

Restart the Docker daemon and run `docker-compose down && docker-compose up -d` to recreate the containers.

### Use Fail2ban

**This is a subsection of "Log to Syslog", which is required for Fail2ban to work.**

Open `/etc/fail2ban/filter.d/common.conf` and search for the prefix_line parameter, change it to ".*":

```
__prefix_line = .*
```

Create `/etc/fail2ban/jail.d/dovecot.conf`...
```
[dovecot]
enabled = true
filter  = dovecot
logpath = /var/log/syslog
chain = FORWARD
```

and `jail.d/postfix-sasl.conf`:
```
[postfix-sasl]
enabled = true
filter  = postfix-sasl
logpath = /var/log/syslog
chain = FORWARD
```

Restart Fail2ban.

## Install a local MTA

The easiest option would be to disable the listener on port 25/tcp.

**Postfix** users disable the listener by commenting the following line (starting with `smtp` or `25`) in `/etc/postfix/master.cf`:
```
#smtp      inet  n       -       -       -       -       smtpd
```
Restart Postfix after applying your changes.

## Sender and receiver model

When a mailbox is created, a user is allowed to send mail from and receive mail for his own mailbox address.

    Mailbox me@example.org is created. example.org is a primary domain. 
    Note: a mailbox cannot be created in an alias domain.

    me@example.org is only known as me@example.org.
    me@example.org is allowed to send as me@example.org.

We can add an alias domain for example.org:

    Alias domain alias.com is added and assigned to primary domain example.org.
    me@example.org is now known as me@example.org and me@alias.com.
    me@example.org is now allowed to send as me@example.org and me@alias.com.

We can add aliases for a mailbox to receive mail for and to send from this new address.

It is important to know, that you are not able to receive mail for `my-alias@my-alias-domain.tld`. You would need to create this particular alias.

    me@example.org is assigned the alias alias@example.org
    me@example.org is now known as alias@example.org, me@alias.com, alias@example.org

    me@example.org is NOT known as alias@alias.com.

Administrators and domain administrators can edit mailboxes to allow specific users to send as other mailbox users ("delegate" them).

You can choose between mailbox users or completely disable the sender check for domains.

### SOGo "mail from" addresses

Mailbox users can, obviously, select their own mailbox address, as well as all alias addresses and aliases that exist through alias domains.

If you want to select another _existing_ mailbox user as your "mail from" address, this user has to delegate you access through SOGo (see SOGo documentation). Moreover a mailcow (domain) administrator
needs to grant you access as described above.

bind_socket = "*:11334";
count = 1;
secure_ip = "127.0.0.1";
secure_ip = "::1";
bind_socket = "/var/lib/rspamd/rspamd.sock mode=0666 owner=nobody";
.include(try=true; priority=10) "$CONFDIR/override.d/worker-controller-password.inc"
.include(try=true; priority=30) "$CONFDIR/override.d/worker-controller.custom.inc" 

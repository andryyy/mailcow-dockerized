bind_socket = "rspamd:9900";
milter = true;
upstream "local" {
  name = "localhost";
  default = true;
  hosts = "rspamd:11333"
}
reject_message = "This message does not meet our delivery requirements";
.include(try=true; priority=30) "$CONFDIR/override.d/worker-proxy.custom.inc"

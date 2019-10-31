type = "console";
systemd = false;
.include "$CONFDIR/logging.inc"
.include(try=true; priority=20) "$CONFDIR/override.d/logging.custom.inc"

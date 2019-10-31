# Socket to listen on (UDP and TCP from rspamd 1.3)
bind_socket = "*:11445";
allow_update = ["127.0.0.1", "::1"];
# Number of processes to serve this storage (useful for read scaling)
count = 1;
# Backend ("sqlite" or "redis" - default "sqlite")
backend = "redis";
# Hashes storage time (3 months)
expire = 90d;
# Synchronize updates to the storage each minute
sync = 1min;


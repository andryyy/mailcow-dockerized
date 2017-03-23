## Install mailcow

You need Docker and Docker Compose.

1\. Learn how to install [Docker](https://docs.docker.com/engine/installation/linux/) and [Docker Compose](https://docs.docker.com/compose/install/).

Quick installation for most operation systems:

- Docker
```
curl -sSL https://get.docker.com/ | sh
``` 

- Docker-Compose
```
curl -L https://github.com/docker/compose/releases/download/$(curl -Ls https://www.servercow.de/docker-compose/latest.php)/docker-compose-$(uname -s)-$(uname -m) > /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
```

Please use the latest Docker engine available and do not use the engine that ships with your distros repository.

2\. Clone the master branch of the repository
```
git clone https://github.com/andryyy/mailcow-dockerized && cd mailcow-dockerized
```

3\. Generate a configuration file. Use a FQDN (`host.domain.tld`) as hostname when asked.
```
./generate_config.sh
```

4\. Change configuration if you want or need to.
```
nano mailcow.conf
```
If you plan to use a reverse proxy, you can, for example, bind HTTPS to 127.0.0.1 on port 8443 and HTTP to 127.0.0.1 on port 8080.

5\. Pull the images and run the composer file. The paramter `-d` will start mailcow: dockerized detached:
```
docker-compose pull
docker-compose up -d
```

Done!

You can now access **https://${MAILCOW_HOSTNAME}** with the default credentials `admin` + password `moohoo`.

The database will be initialized right after a connection to MySQL can be established.

## Update mailcow

There is no update routine. You need to refresh your pulled repository clone and apply your local changes (if any). Actually there are many ways to merge local changes. Here is one to
stash all local changes, pull changes from the remote master branch and apply your stash on top of it. You will most likely see warnings about non-commited changes; you can ignore them:

```
# Stash local changes
git stash
# Re-pull master
git pull
# Apply stash and remove it
git stash pop
```

Pull new images (if any) and recreate changed containers:

```
docker-compose pull
docker-compose up -d --remove-orphans
```
In case this procedure does not work (for many reason like you're unable to fix the CONFLICTS or any other reasons) you can simply 

copy mailcow.conf somewhere outside the mailcow-dockerized
stop docker: docker-compose down 
delete the cloned directory 
clone again the repo (git clone https://github.com/andryyy/mailcow-dockerized && cd mailcow-dockerized)
copy back your previous mailcow.conf into mailcow-dockerizd folder (pay attention to this step - the folder must have the same name of the previous one)
docker-compose pull
docker-compose up -d


*If you forgot to stop Docker before deleting the cloned directoy you can use these following command
docker stop $(docker ps -a -q)
docker rm $(docker ps -a -q)
docker rmi $(docker images -a -q)

Clean-up dangling (unused) images and volumes:

```
docker rmi -f $(docker images -f "dangling=true" -q)
docker volume rm $(docker volume ls -qf dangling=true)
```

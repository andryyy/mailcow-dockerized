#!/usr/bin/env bash

if [[ ! -z ${MAILCOW_BACKUP_LOCATION} ]]; then
  BACKUP_LOCATION="${MAILCOW_BACKUP_LOCATION}"
fi

if [[ ! ${1} =~ (backup|restore) ]]; then
  echo "First parameter needs to be 'backup' or 'restore'"
  exit 1
fi

if [[ ${1} == "backup" && ! ${2} =~ (crypt|vmail|redis|rspamd|postfix|mysql|all|--delete-days) ]]; then
  echo "Second parameter needs to be 'vmail', 'crypt', 'redis', 'rspamd', 'postfix', 'mysql', 'all' or '--delete-days'"
  exit 1
fi

if [[ -z ${BACKUP_LOCATION} ]]; then
  while [[ -z ${BACKUP_LOCATION} ]]; do
    read -ep "Backup location (absolute path, starting with /): " BACKUP_LOCATION
  done
fi

if [[ ! ${BACKUP_LOCATION} =~ ^/ ]]; then
  echo "Backup directory needs to be given as absolute path (starting with /)."
  exit 1
fi

if [[ -f ${BACKUP_LOCATION} ]]; then
  echo "${BACKUP_LOCATION} is a file!"
  exit 1
fi

if [[ ! -d ${BACKUP_LOCATION} ]]; then
  echo "${BACKUP_LOCATION} is not a directory"
  read -p "Create it now? [y|N] " CREATE_BACKUP_LOCATION
  if [[ ! ${CREATE_BACKUP_LOCATION,,} =~ ^(yes|y)$ ]]; then
    exit 1
  else
    mkdir -p ${BACKUP_LOCATION}
    chmod 755 ${BACKUP_LOCATION}
  fi
else
  if [[ ${1} == "backup" ]] && [[ -z $(echo $(stat -Lc %a ${BACKUP_LOCATION}) | grep -oE '[0-9][0-9][5-7]') ]]; then
    echo "${BACKUP_LOCATION} is not write-able for others, that's required for a backup."
    exit 1
  fi
fi
BACKUP_LOCATION=$(echo ${BACKUP_LOCATION} | sed 's#/$##')
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
COMPOSE_FILE=${SCRIPT_DIR}/../docker-compose.yml
echo "Using ${BACKUP_LOCATION} as backup/restore location."
echo
source ${SCRIPT_DIR}/../mailcow.conf
CMPS_PRJ=$(echo $COMPOSE_PROJECT_NAME | tr -cd "[A-Za-z-_]")

function backup() {
  DATE=$(date +"%Y-%m-%d-%H-%M-%S")
  mkdir -p "${BACKUP_LOCATION}/mailcow-${DATE}"
  chmod 755 "${BACKUP_LOCATION}/mailcow-${DATE}"
  cp "${SCRIPT_DIR}/../mailcow.conf" "${BACKUP_LOCATION}/mailcow-${DATE}"
  while (( "$#" )); do
    case "$1" in
    vmail|all)
      docker run --rm \
        -v ${BACKUP_LOCATION}/mailcow-${DATE}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_vmail-vol-1):/vmail:ro \
        debian:stretch-slim /bin/tar --warning='no-file-ignored' --use-compress-program="gzip --rsyncable --best" -Pcvpf /backup/backup_vmail.tar.gz /vmail
      ;;&
    crypt|all)
      docker run --rm \
        -v ${BACKUP_LOCATION}/mailcow-${DATE}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_crypt-vol-1):/crypt:ro \
        debian:stretch-slim /bin/tar --warning='no-file-ignored' --use-compress-program="gzip --rsyncable --best" -Pcvpf /backup/backup_crypt.tar.gz /crypt
      ;;&
    redis|all)
      docker exec $(docker ps -qf name=redis-mailcow) redis-cli save
      docker run --rm \
        -v ${BACKUP_LOCATION}/mailcow-${DATE}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_redis-vol-1):/redis:ro \
        debian:stretch-slim /bin/tar --warning='no-file-ignored' --use-compress-program="gzip --rsyncable --best" -Pcvpf /backup/backup_redis.tar.gz /redis
      ;;&
    rspamd|all)
      docker run --rm \
        -v ${BACKUP_LOCATION}/mailcow-${DATE}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_rspamd-vol-1):/rspamd:ro \
        debian:stretch-slim /bin/tar --warning='no-file-ignored' --use-compress-program="gzip --rsyncable --best" -Pcvpf /backup/backup_rspamd.tar.gz /rspamd
      ;;&
    postfix|all)
      docker run --rm \
        -v ${BACKUP_LOCATION}/mailcow-${DATE}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_postfix-vol-1):/postfix:ro \
        debian:stretch-slim /bin/tar --warning='no-file-ignored' --use-compress-program="gzip --rsyncable --best" -Pcvpf /backup/backup_postfix.tar.gz /postfix
      ;;&
    mysql|all)
      SQLIMAGE=$(grep -iEo '(mysql|mariadb)\:.+' ${COMPOSE_FILE})
      docker run --rm \
        --network $(docker network ls -qf name=${CMPS_PRJ}_) \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_mysql-vol-1):/var/lib/mysql/:ro \
        --entrypoint= \
        -v ${BACKUP_LOCATION}/mailcow-${DATE}:/backup \
        ${SQLIMAGE} /bin/sh -c "mysqldump -hmysql -uroot -p${DBROOT} --all-databases | gzip > /backup/backup_mysql.gz"
      ;;&
    --delete-days)
      shift
      if [[ "${1}" =~ ^[0-9]+$ ]]; then
        find ${BACKUP_LOCATION}/* -maxdepth 0 -mmin +$((${1}*60*24)) -exec rm -rvf {} \;
      else
        echo "Parameter of --delete-days is not a number."
      fi
      ;;
    esac
    shift
  done
}

function restore() {
  docker stop $(docker ps -qf name=watchdog-mailcow)
  RESTORE_LOCATION="${1}"
  shift
  while (( "$#" )); do
    case "$1" in
    vmail)
      docker stop $(docker ps -qf name=dovecot-mailcow)
      docker run -it --rm \
        -v ${RESTORE_LOCATION}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_vmail-vol-1):/vmail \
        debian:stretch-slim /bin/tar -Pxvzf /backup/backup_vmail.tar.gz
      docker start $(docker ps -aqf name=dovecot-mailcow)
      echo
      echo "In most cases it is not required to run a full resync, you can run the command printed below at any time after testing wether the restore process broke a mailbox:"
      echo
      echo "docker exec $(docker ps -qf name=dovecot-mailcow) doveadm force-resync -A '*'"
      echo
      read -p "Force a resync now? [y|N] " FORCE_RESYNC
      if [[ ${FORCE_RESYNC,,} =~ ^(yes|y)$ ]]; then
        docker exec $(docker ps -qf name=dovecot-mailcow) doveadm force-resync -A '*'
      else
        echo "OK, skipped."
      fi
      ;;
    redis)
      docker stop $(docker ps -qf name=redis-mailcow)
      docker run -it --rm \
        -v ${RESTORE_LOCATION}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_redis-vol-1):/redis \
        debian:stretch-slim /bin/tar -Pxvzf /backup/backup_redis.tar.gz
      docker start $(docker ps -aqf name=redis-mailcow)
      ;;
    crypt)
      docker stop $(docker ps -qf name=dovecot-mailcow)
      docker run -it --rm \
        -v ${RESTORE_LOCATION}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_crypt-vol-1):/crypt \
        debian:stretch-slim /bin/tar -Pxvzf /backup/backup_crypt.tar.gz
      docker start $(docker ps -aqf name=dovecot-mailcow)
      ;;
    rspamd)
      docker stop $(docker ps -qf name=rspamd-mailcow)
      docker run -it --rm \
        -v ${RESTORE_LOCATION}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_rspamd-vol-1):/rspamd \
        debian:stretch-slim /bin/tar -Pxvzf /backup/backup_rspamd.tar.gz
      docker start $(docker ps -aqf name=rspamd-mailcow)
      ;;
    postfix)
      docker stop $(docker ps -qf name=postfix-mailcow)
      docker run -it --rm \
        -v ${RESTORE_LOCATION}:/backup \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_postfix-vol-1):/postfix \
        debian:stretch-slim /bin/tar -Pxvzf /backup/backup_postfix.tar.gz
      docker start $(docker ps -aqf name=postfix-mailcow)
      ;;
    mysql)
      SQLIMAGE=$(grep -iEo '(mysql|mariadb)\:.+' ${COMPOSE_FILE})
      docker stop $(docker ps -qf name=mysql-mailcow)
      docker run \
        -it --rm \
        -v $(docker volume ls -qf name=${CMPS_PRJ}_mysql-vol-1):/var/lib/mysql/ \
        --entrypoint= \
        -u mysql \
        -v ${RESTORE_LOCATION}:/backup \
        ${SQLIMAGE} /bin/sh -c "mysqld --skip-grant-tables & \
        until mysqladmin ping; do sleep 3; done && \
        echo Restoring... && \
        gunzip < backup/backup_mysql.gz | mysql -uroot && \
        mysql -uroot -e SHUTDOWN;"
      docker start $(docker ps -aqf name=mysql-mailcow)
      ;;
    esac
    shift
  done
  docker start $(docker ps -aqf name=watchdog-mailcow)
}

if [[ ${1} == "backup" ]]; then
  backup ${@,,}
elif [[ ${1} == "restore" ]]; then
  i=1
  declare -A FOLDER_SELECTION
  if [[ $(find ${BACKUP_LOCATION}/mailcow-* -maxdepth 1 -type d 2> /dev/null| wc -l) -lt 1 ]]; then
    echo "Selected backup location has no subfolders"
    exit 1
  fi
  for folder in $(ls -d ${BACKUP_LOCATION}/mailcow-*/); do
    echo "[ ${i} ] - ${folder}"
    FOLDER_SELECTION[${i}]="${folder}"
    ((i++))
  done
  echo
  input_sel=0
  while [[ ${input_sel} -lt 1 ||  ${input_sel} -gt ${i} ]]; do
    read -p "Select a restore point: " input_sel
  done
  i=1
  echo
  declare -A FILE_SELECTION
  RESTORE_POINT="${FOLDER_SELECTION[${input_sel}]}"
  if [[ -z $(find "${FOLDER_SELECTION[${input_sel}]}" -maxdepth 1 -type f -regex ".*\(redis\|rspamd\|mysql\|crypt\|vmail\|postfix\).*") ]]; then
    echo "No datasets found"
    exit 1
  fi

  echo "[ 0 ] all"
  # find all files in folder with .tar.gz extension, print their base names, remove backup_, remove .tar, remove .gz
  FILE_SELECTION[0]=$(find "${FOLDER_SELECTION[${input_sel}]}" -type f -name "*.tar.gz" -printf "%f\n" | sed 's/backup_*//' | sed 's/\.[^.]*$//' | sed 's/\.[^.]*$//')
  for file in $(ls -f "${FOLDER_SELECTION[${input_sel}]}"); do
    if [[ ${file} =~ vmail ]]; then
      echo "[ ${i} ] - Mail directory (/var/vmail)"
      FILE_SELECTION[${i}]="vmail"
      ((i++))
    elif [[ ${file} =~ crypt ]]; then
      echo "[ ${i} ] - Crypt data"
      FILE_SELECTION[${i}]="crypt"
      ((i++))
    elif [[ ${file} =~ redis ]]; then
      echo "[ ${i} ] - Redis DB"
      FILE_SELECTION[${i}]="redis"
      ((i++))
    elif [[ ${file} =~ rspamd ]]; then
      echo "[ ${i} ] - Rspamd data"
      FILE_SELECTION[${i}]="rspamd"
      ((i++))
    elif [[ ${file} =~ postfix ]]; then
      echo "[ ${i} ] - Postfix data"
      FILE_SELECTION[${i}]="postfix"
      ((i++))
    elif [[ ${file} =~ mysql ]]; then
      echo "[ ${i} ] - SQL DB"
      FILE_SELECTION[${i}]="mysql"
      ((i++))
    fi
  done
  echo
  input_sel=-1
  while [[ ${input_sel} -lt 0 ||  ${input_sel} -gt ${i} ]]; do
    read -p "Select a dataset to restore: " input_sel
  done
  echo "Restoring ${FILE_SELECTION[${input_sel}]} from ${RESTORE_POINT}..."
  restore "${RESTORE_POINT}" ${FILE_SELECTION[${input_sel}]}
fi

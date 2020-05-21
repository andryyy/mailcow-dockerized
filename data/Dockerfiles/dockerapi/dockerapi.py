#!/usr/bin/env python3

from flask import Flask
from flask_restful import Resource, Api
from flask import jsonify
from flask import Response
from flask import request
from threading import Thread
import docker
import uuid
import signal
import time
import os
import re
import sys
import ssl
import socket
import subprocess
import traceback

docker_client = docker.DockerClient(base_url='unix://var/run/docker.sock', version='auto')
app = Flask(__name__)
api = Api(app)

class containers_get(Resource):
  def get(self):
    containers = {}
    try:
      for container in docker_client.containers.list(all=True):
        containers.update({container.attrs['Id']: container.attrs})
      return containers
    except Exception as e:
      return jsonify(type='danger', msg=str(e))

class container_get(Resource):
  def get(self, container_id):
    if container_id and container_id.isalnum():
      try:
        for container in docker_client.containers.list(all=True, filters={"id": container_id}):
          return container.attrs
      except Exception as e:
          return jsonify(type='danger', msg=str(e))
    else:
      return jsonify(type='danger', msg='no or invalid id defined')

class container_post(Resource):
  def post(self, container_id, post_action):
    if container_id and container_id.isalnum() and post_action:
      try:
        """Dispatch container_post api call"""
        if post_action == 'exec':
          if not request.json or not 'cmd' in request.json:
            return jsonify(type='danger', msg='cmd is missing')
          if not request.json or not 'task' in request.json:
            return jsonify(type='danger', msg='task is missing')

          api_call_method_name = '__'.join(['container_post', str(post_action), str(request.json['cmd']), str(request.json['task']) ])
        else:
          api_call_method_name = '__'.join(['container_post', str(post_action) ])

        api_call_method = getattr(self, api_call_method_name, lambda container_id: jsonify(type='danger', msg='container_post - unknown api call'))


        print("api call: %s, container_id: %s" % (api_call_method_name, container_id))
        return api_call_method(container_id)
      except Exception as e:
        print("error - container_post: %s" % str(e))
        return jsonify(type='danger', msg=str(e))

    else:
      return jsonify(type='danger', msg='invalid container id or missing action')


  # api call: container_post - post_action: stop
  def container_post__stop(self, container_id):
    for container in docker_client.containers.list(all=True, filters={"id": container_id}):
      container.stop()
    return jsonify(type='success', msg='command completed successfully')


  # api call: container_post - post_action: start
  def container_post__start(self, container_id):
    for container in docker_client.containers.list(all=True, filters={"id": container_id}):
      container.start()
    return jsonify(type='success', msg='command completed successfully')


  # api call: container_post - post_action: restart
  def container_post__restart(self, container_id):
    for container in docker_client.containers.list(all=True, filters={"id": container_id}):
      container.restart()
    return jsonify(type='success', msg='command completed successfully')


  # api call: container_post - post_action: top
  def container_post__top(self, container_id):
    for container in docker_client.containers.list(all=True, filters={"id": container_id}):
      return jsonify(type='success', msg=container.top())


  # api call: container_post - post_action: stats
  def container_post__stats(self, container_id):
    for container in docker_client.containers.list(all=True, filters={"id": container_id}):
      for stat in container.stats(decode=True, stream=True):
        return jsonify(type='success', msg=stat )


  # api call: container_post - post_action: exec - cmd: mailq - task: delete
  def container_post__exec__mailq__delete(self, container_id):
    if 'items' in request.json:
      r = re.compile("^[0-9a-fA-F]+$")
      filtered_qids = filter(r.match, request.json['items'])
      if filtered_qids:
        flagged_qids = ['-d %s' % i for i in filtered_qids]
        sanitized_string = str(' '.join(flagged_qids));

        for container in docker_client.containers.list(filters={"id": container_id}):
          postsuper_r = container.exec_run(["/bin/bash", "-c", "/usr/sbin/postsuper " + sanitized_string])
          return exec_run_handler('generic', postsuper_r)

  # api call: container_post - post_action: exec - cmd: mailq - task: hold
  def container_post__exec__mailq__hold(self, container_id):
    if 'items' in request.json:
      r = re.compile("^[0-9a-fA-F]+$")
      filtered_qids = filter(r.match, request.json['items'])
      if filtered_qids:
        flagged_qids = ['-h %s' % i for i in filtered_qids]
        sanitized_string = str(' '.join(flagged_qids));

        for container in docker_client.containers.list(filters={"id": container_id}):
          postsuper_r = container.exec_run(["/bin/bash", "-c", "/usr/sbin/postsuper " + sanitized_string])
          return exec_run_handler('generic', postsuper_r)

  # api call: container_post - post_action: exec - cmd: mailq - task: cat
  def container_post__exec__mailq__cat(self, container_id):
    if 'items' in request.json:
      r = re.compile("^[0-9a-fA-F]+$")
      filtered_qids = filter(r.match, request.json['items'])
      if filtered_qids:
        sanitized_string = str(' '.join(filtered_qids));

        for container in docker_client.containers.list(filters={"id": container_id}):
          postcat_return = container.exec_run(["/bin/bash", "-c", "/usr/sbin/postcat -q " + sanitized_string], user='postfix')
        if not postcat_return:
          postcat_return = 'err: invalid'
        return exec_run_handler('utf8_text_only', postcat_return)

   # api call: container_post - post_action: exec - cmd: mailq - task: unhold
  def container_post__exec__mailq__unhold(self, container_id):
    if 'items' in request.json:
      r = re.compile("^[0-9a-fA-F]+$")
      filtered_qids = filter(r.match, request.json['items'])
      if filtered_qids:
        flagged_qids = ['-H %s' % i for i in filtered_qids]
        sanitized_string = str(' '.join(flagged_qids));

        for container in docker_client.containers.list(filters={"id": container_id}):
          postsuper_r = container.exec_run(["/bin/bash", "-c", "/usr/sbin/postsuper " + sanitized_string])
          return exec_run_handler('generic', postsuper_r)


  # api call: container_post - post_action: exec - cmd: mailq - task: deliver
  def container_post__exec__mailq__deliver(self, container_id):
    if 'items' in request.json:
      r = re.compile("^[0-9a-fA-F]+$")
      filtered_qids = filter(r.match, request.json['items'])
      if filtered_qids:
        flagged_qids = ['-i %s' % i for i in filtered_qids]

        for container in docker_client.containers.list(filters={"id": container_id}):
          for i in flagged_qids:
            postqueue_r = container.exec_run(["/bin/bash", "-c", "/usr/sbin/postqueue " + i], user='postfix')
            # todo: check each exit code
          return jsonify(type='success', msg=str("Scheduled immediate delivery"))


  # api call: container_post - post_action: exec - cmd: mailq - task: list
  def container_post__exec__mailq__list(self, container_id):
    for container in docker_client.containers.list(filters={"id": container_id}):
      mailq_return = container.exec_run(["/usr/sbin/postqueue", "-j"], user='postfix')
      return exec_run_handler('utf8_text_only', mailq_return)


  # api call: container_post - post_action: exec - cmd: mailq - task: flush
  def container_post__exec__mailq__flush(self, container_id):
    for container in docker_client.containers.list(filters={"id": container_id}):
      postqueue_r = container.exec_run(["/usr/sbin/postqueue", "-f"], user='postfix')
      return exec_run_handler('generic', postqueue_r)


  # api call: container_post - post_action: exec - cmd: mailq - task: super_delete
  def container_post__exec__mailq__super_delete(self, container_id):
    for container in docker_client.containers.list(filters={"id": container_id}):
      postsuper_r = container.exec_run(["/usr/sbin/postsuper", "-d", "ALL"])
      return exec_run_handler('generic', postsuper_r)


  # api call: container_post - post_action: exec - cmd: system - task: fts_rescan
  def container_post__exec__system__fts_rescan(self, container_id):
    if 'username' in request.json:
      for container in docker_client.containers.list(filters={"id": container_id}):
        rescan_return = container.exec_run(["/bin/bash", "-c", "/usr/bin/doveadm fts rescan -u '" + request.json['username'].replace("'", "'\\''") + "'"], user='vmail')
        if rescan_return.exit_code == 0:
          return jsonify(type='success', msg='fts_rescan: rescan triggered')
        else:
          return jsonify(type='warning', msg='fts_rescan error')

    if 'all' in request.json:
      for container in docker_client.containers.list(filters={"id": container_id}):
        rescan_return = container.exec_run(["/bin/bash", "-c", "/usr/bin/doveadm fts rescan -A"], user='vmail')
        if rescan_return.exit_code == 0:
          return jsonify(type='success', msg='fts_rescan: rescan triggered')
        else:
          return jsonify(type='warning', msg='fts_rescan error')


  # api call: container_post - post_action: exec - cmd: system - task: df
  def container_post__exec__system__df(self, container_id):
    if 'dir' in request.json:
      for container in docker_client.containers.list(filters={"id": container_id}):
        df_return = container.exec_run(["/bin/bash", "-c", "/bin/df -H '" + request.json['dir'].replace("'", "'\\''") + "' | /usr/bin/tail -n1 | /usr/bin/tr -s [:blank:] | /usr/bin/tr ' ' ','"], user='nobody')
        if df_return.exit_code == 0:
          return df_return.output.decode('utf-8').rstrip()
        else:
          return "0,0,0,0,0,0"


  # api call: container_post - post_action: exec - cmd: system - task: mysql_upgrade
  def container_post__exec__system__mysql_upgrade(self, container_id):
    for container in docker_client.containers.list(filters={"id": container_id}):
      sql_return = container.exec_run(["/bin/bash", "-c", "/usr/bin/mysql_upgrade -uroot -p'" + os.environ['DBROOT'].replace("'", "'\\''") + "'\n"], user='mysql')
      if sql_return.exit_code == 0:
        matched = False
        for line in sql_return.output.decode('utf-8').split("\n"):
          if 'is already upgraded to' in line:
            matched = True
        if matched:
          return jsonify(type='success', msg='mysql_upgrade: already upgraded', text=sql_return.output.decode('utf-8'))
        else:
          container.restart()
          return jsonify(type='warning', msg='mysql_upgrade: upgrade was applied', text=sql_return.output.decode('utf-8'))
      else:
        return jsonify(type='error', msg='mysql_upgrade: error running command', text=sql_return.output.decode('utf-8'))

  # api call: container_post - post_action: exec - cmd: system - task: mysql_tzinfo_to_sql
  def container_post__exec__system__mysql_tzinfo_to_sql(self, container_id):
    for container in docker_client.containers.list(filters={"id": container_id}):
      sql_return = container.exec_run(["/bin/bash", "-c", "/usr/bin/mysql_tzinfo_to_sql /usr/share/zoneinfo | /bin/sed 's/Local time zone must be set--see zic manual page/FCTY/' | /usr/bin/mysql -uroot -p'" + os.environ['DBROOT'].replace("'", "'\\''") + "' mysql \n"], user='mysql')
      if sql_return.exit_code == 0:
        return jsonify(type='info', msg='mysql_tzinfo_to_sql: command completed successfully', text=sql_return.output.decode('utf-8'))
      else:
        return jsonify(type='error', msg='mysql_tzinfo_to_sql: error running command', text=sql_return.output.decode('utf-8'))

  # api call: container_post - post_action: exec - cmd: reload - task: dovecot
  def container_post__exec__reload__dovecot(self, container_id):
    for container in docker_client.containers.list(filters={"id": container_id}):
      reload_return = container.exec_run(["/bin/bash", "-c", "/usr/sbin/dovecot reload"])
      return exec_run_handler('generic', reload_return)


  # api call: container_post - post_action: exec - cmd: reload - task: postfix
  def container_post__exec__reload__postfix(self, container_id):
    for container in docker_client.containers.list(filters={"id": container_id}):
      reload_return = container.exec_run(["/bin/bash", "-c", "/usr/sbin/postfix reload"])
      return exec_run_handler('generic', reload_return)


  # api call: container_post - post_action: exec - cmd: reload - task: nginx
  def container_post__exec__reload__nginx(self, container_id):
    for container in docker_client.containers.list(filters={"id": container_id}):
      reload_return = container.exec_run(["/bin/sh", "-c", "/usr/sbin/nginx -s reload"])
      return exec_run_handler('generic', reload_return)


  # api call: container_post - post_action: exec - cmd: sieve - task: list
  def container_post__exec__sieve__list(self, container_id):
    if 'username' in request.json:
      for container in docker_client.containers.list(filters={"id": container_id}):
        sieve_return = container.exec_run(["/bin/bash", "-c", "/usr/bin/doveadm sieve list -u '" + request.json['username'].replace("'", "'\\''") + "'"])
        return exec_run_handler('utf8_text_only', sieve_return)


  # api call: container_post - post_action: exec - cmd: sieve - task: print
  def container_post__exec__sieve__print(self, container_id):
    if 'username' in request.json and 'script_name' in request.json:
      for container in docker_client.containers.list(filters={"id": container_id}):
        cmd = ["/bin/bash", "-c", "/usr/bin/doveadm sieve get -u '" + request.json['username'].replace("'", "'\\''") + "' '" + request.json['script_name'].replace("'", "'\\''") + "'"]  
        sieve_return = container.exec_run(cmd)
        return exec_run_handler('utf8_text_only', sieve_return)


  # api call: container_post - post_action: exec - cmd: maildir - task: cleanup
  def container_post__exec__maildir__cleanup(self, container_id):
    if 'maildir' in request.json:
      for container in docker_client.containers.list(filters={"id": container_id}):
        sane_name = re.sub(r'\W+', '', request.json['maildir'])
        cmd = ["/bin/bash", "-c", "if [[ -d '/var/vmail/" + request.json['maildir'].replace("'", "'\\''") + "' ]]; then /bin/mv '/var/vmail/" + request.json['maildir'].replace("'", "'\\''") + "' '/var/vmail/_garbage/" + str(int(time.time())) + "_" + sane_name + "'; fi"]
        maildir_cleanup = container.exec_run(cmd, user='vmail')
        return exec_run_handler('generic', maildir_cleanup)



  # api call: container_post - post_action: exec - cmd: rspamd - task: worker_password
  def container_post__exec__rspamd__worker_password(self, container_id):
    if 'raw' in request.json:
      for container in docker_client.containers.list(filters={"id": container_id}):
        cmd = "/usr/bin/rspamadm pw -e -p '" + request.json['raw'].replace("'", "'\\''") + "' 2> /dev/null"
        cmd_response = exec_cmd_container(container, cmd, user="_rspamd")
        matched = False
        for line in cmd_response.split("\n"):
          if '$2$' in line:
            hash = line.strip()
            hash_out = re.search('\$2\$.+$', hash).group(0)
            rspamd_passphrase_hash = re.sub('[^0-9a-zA-Z\$]+', '', hash_out.rstrip())

            rspamd_password_filename = "/etc/rspamd/override.d/worker-controller-password.inc"
            cmd = '''/bin/echo 'enable_password = "%s";' > %s && cat %s''' % (rspamd_passphrase_hash, rspamd_password_filename, rspamd_password_filename)
            cmd_response = exec_cmd_container(container, cmd, user="_rspamd")

            if rspamd_passphrase_hash.startswith("$2$") and rspamd_passphrase_hash in cmd_response:
              container.restart()
              matched = True

        if matched:
            return jsonify(type='success', msg='command completed successfully')
        else:
            return jsonify(type='danger', msg='command did not complete')

def exec_cmd_container(container, cmd, user, timeout=2, shell_cmd="/bin/bash"):

  def recv_socket_data(c_socket, timeout):
    c_socket.setblocking(0)
    total_data=[];
    data='';
    begin=time.time()
    while True:
      if total_data and time.time()-begin > timeout:
        break
      elif time.time()-begin > timeout*2:
        break
      try:
        data = c_socket.recv(8192)
        if data:
          total_data.append(data.decode('utf-8'))
          #change the beginning time for measurement
          begin=time.time()
        else:
          #sleep for sometime to indicate a gap
          time.sleep(0.1)
          break
      except:
        pass
    return ''.join(total_data)

  try :
    socket = container.exec_run([shell_cmd], stdin=True, socket=True, user=user).output._sock
    if not cmd.endswith("\n"):
      cmd = cmd + "\n"
    socket.send(cmd.encode('utf-8'))
    data = recv_socket_data(socket, timeout)
    socket.close()
    return data

  except Exception as e:
    print("error - exec_cmd_container: %s" % str(e))
    traceback.print_exc(file=sys.stdout)

def exec_run_handler(type, output):
  if type == 'generic':
    if output.exit_code == 0:
      return jsonify(type='success', msg='command completed successfully')
    else:
      return jsonify(type='danger', msg='command failed: ' + output.output.decode('utf-8'))
  if type == 'utf8_text_only':
    r = Response(response=output.output.decode('utf-8'), status=200, mimetype="text/plain")
    r.headers["Content-Type"] = "text/plain; charset=utf-8"
    return r

class GracefulKiller:
  kill_now = False
  def __init__(self):
    signal.signal(signal.SIGINT, self.exit_gracefully)
    signal.signal(signal.SIGTERM, self.exit_gracefully)

  def exit_gracefully(self, signum, frame):
    self.kill_now = True

def create_self_signed_cert():
    process = subprocess.Popen(
      "openssl req -x509 -newkey rsa:4096 -sha256 -days 3650 -nodes -keyout /app/dockerapi_key.pem -out /app/dockerapi_cert.pem -subj /CN=dockerapi/O=mailcow -addext subjectAltName=DNS:dockerapi".split(),
      stdout = subprocess.PIPE, stderr = subprocess.PIPE, shell=False
    )
    process.wait()

def startFlaskAPI():
  create_self_signed_cert()
  try:
    ctx = ssl.create_default_context(ssl.Purpose.CLIENT_AUTH)
    ctx.check_hostname = False
    ctx.load_cert_chain(certfile='/app/dockerapi_cert.pem', keyfile='/app/dockerapi_key.pem')
  except:
    print ("Cannot initialize TLS, retrying in 5s...")
    time.sleep(5)
  app.run(debug=False, host='0.0.0.0', port=443, threaded=True, ssl_context=ctx)

api.add_resource(containers_get, '/containers/json')
api.add_resource(container_get,  '/containers/<string:container_id>/json')
api.add_resource(container_post, '/containers/<string:container_id>/<string:post_action>')

if __name__ == '__main__':
  api_thread = Thread(target=startFlaskAPI)
  api_thread.daemon = True
  api_thread.start()
  killer = GracefulKiller()
  while True:
    time.sleep(1)
    if killer.kill_now:
      break
  print ("Stopping dockerapi-mailcow")

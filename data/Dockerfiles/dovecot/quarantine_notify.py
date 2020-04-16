#!/usr/bin/python3

import smtplib
import os
import mysql.connector
from email.mime.multipart import MIMEMultipart
from email.mime.text import MIMEText
from email.utils import COMMASPACE, formatdate
import cgi
import jinja2
from jinja2 import Template
import json
import redis
import time
import html2text
import socket

while True:
  try:
    r = redis.StrictRedis(host='redis', decode_responses=True, port=6379, db=0)
    r.ping()
  except Exception as ex:
    print('%s - trying again...'  % (ex))
    time.sleep(3)
  else:
    break

time_now = int(time.time())

def query_mysql(query, headers = True, update = False):
  while True:
    try:
      cnx = mysql.connector.connect(unix_socket = '/var/run/mysqld/mysqld.sock', user='__DBUSER__', passwd='__DBPASS__', database='__DBNAME__', charset="utf8")
    except Exception as ex:
      print('%s - trying again...'  % (ex))
      time.sleep(3)
    else:
      break
  cur = cnx.cursor()
  cur.execute(query)
  if not update:
    result = []
    columns = tuple( [d[0] for d in cur.description] )
    for row in cur:
      if headers:
        result.append(dict(list(zip(columns, row))))
      else:
        result.append(row)
    cur.close()
    cnx.close()
    return result
  else:
    cnx.commit()
    cur.close()
    cnx.close()

def notify_rcpt(rcpt, msg_count, quarantine_acl):
  meta_query = query_mysql('SELECT SHA2(CONCAT(id, qid), 256) AS qhash, id, subject, score, sender, created FROM quarantine WHERE notified = 0 AND rcpt = "%s"' % (rcpt))
  if r.get('Q_HTML'):
    try:
      template = Template(r.get('Q_HTML'))
    except:
      print("Error: Cannot parse quarantine template, falling back to default template.")
      with open('/templates/quarantine.tpl') as file_:
        template = Template(file_.read())
  else:
    with open('/templates/quarantine.tpl') as file_:
      template = Template(file_.read())
  html = template.render(meta=meta_query, counter=msg_count, hostname=socket.gethostname(), quarantine_acl=quarantine_acl)
  text = html2text.html2text(html)
  count = 0
  while count < 15:
    try:
      server = smtplib.SMTP('postfix', 590, 'quarantine')
      server.ehlo()
      msg = MIMEMultipart('alternative')
      msg_from = r.get('Q_SENDER') or "quarantine@localhost"
      # Remove non-ascii chars from field
      msg['From'] = ''.join([i if ord(i) < 128 else '' for i in msg_from])
      msg['Subject'] = r.get('Q_SUBJ') or "Spam Quarantine Notification"
      msg['Date'] = formatdate(localtime = True)
      text_part = MIMEText(text, 'plain', 'utf-8')
      html_part = MIMEText(html, 'html', 'utf-8')
      msg.attach(text_part)
      msg.attach(html_part)
      msg['To'] = str(rcpt)
      bcc = r.get('Q_BCC') or ""
      text = msg.as_string()
      server.sendmail(msg['From'], [str(rcpt)] + [str(bcc)], text)
      server.quit()
      for res in meta_query:
        query_mysql('UPDATE quarantine SET notified = 1 WHERE id = "%d"' % (res['id']), update = True)
      r.hset('Q_LAST_NOTIFIED', record['rcpt'], time_now)
      break
    except Exception as ex:
      server.quit()
      print('%s'  % (ex))
      time.sleep(3)

records = query_mysql("""
SELECT IFNULL(user_acl.quarantine, 0) AS quarantine_acl, count(id) AS counter, rcpt, sender FROM quarantine
LEFT OUTER JOIN user_acl ON user_acl.username = rcpt
WHERE notified = 0 AND rcpt in (SELECT username FROM mailbox)
# dont send notifications for blacklisted senders
AND (SELECT prefid FROM filterconf WHERE option = "blacklist_from" AND (object = rcpt OR object = SUBSTRING(rcpt, LOCATE("@", rcpt) + 1)) AND sender REGEXP(REPLACE(value, '*', '.+'))) IS NULL GROUP BY rcpt
""")

for record in records:
  attrs = ''
  attrs_json = ''
  try:
    last_notification = int(r.hget('Q_LAST_NOTIFIED', record['rcpt']))
    if last_notification > time_now:
      print('Last notification is > time now, assuming never')
      last_notification = 0
  except Exception as ex:
    print('Could not determine last notification for %s, assuming never' % (record['rcpt']))
    last_notification = 0
  attrs_json = query_mysql('SELECT attributes FROM mailbox WHERE username = "%s"' % (record['rcpt']))
  attrs = attrs_json[0]['attributes']
  if isinstance(attrs, str):
    # if attr is str then just load it
    attrs = json.loads(attrs)
  else:
    # if it's bytes then decode and load it
    attrs = json.loads(attrs.decode('utf-8'))
  if attrs['quarantine_notification'] not in ('hourly', 'daily', 'weekly', 'never'):
    print('Abnormal quarantine_notification value')
    continue
  if attrs['quarantine_notification'] == 'hourly':
    if last_notification == 0 or (last_notification + 3600) < time_now:
      print("Notifying %s about %d new items in quarantine" % (record['rcpt'], record['counter']))
      notify_rcpt(record['rcpt'], record['counter'], record['quarantine_acl'])
  elif attrs['quarantine_notification'] == 'daily':
    if last_notification == 0 or (last_notification + 86400) < time_now:
      print("Notifying %s about %d new items in quarantine" % (record['rcpt'], record['counter']))
      notify_rcpt(record['rcpt'], record['counter'], record['quarantine_acl'])
  elif attrs['quarantine_notification'] == 'weekly':
    if last_notification == 0 or (last_notification + 604800) < time_now:
      print("Notifying %s about %d new items in quarantine" % (record['rcpt'], record['counter']))
      notify_rcpt(record['rcpt'], record['counter'], record['quarantine_acl'])

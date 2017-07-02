#!/usr/bin/env python2

import re
import os
import time
import atexit
import signal
import ipaddress
import subprocess
from threading import Thread
import docker
import redis
import time
import json

yes_regex = re.compile(r'([yY][eE][sS]|[yY])+$')
if re.search(yes_regex, os.getenv('SKIP_FAIL2BAN', 0)):
	print "Skipping Fail2ban container..."
	raise SystemExit

r = redis.StrictRedis(host='172.22.1.249', decode_responses=True, port=6379, db=0)
client = docker.from_env()

for container in client.containers.list():
	if "postfix-mailcow" in container.name:
		postfix_container = container.name
	elif "dovecot-mailcow" in container.name:
		dovecot_container = container.name
	elif "sogo-mailcow" in container.name:
		sogo_container = container.name
	elif "php-fpm-mailcow" in container.name:
		php_fpm_container = container.name

RULES = {}

RULES[postfix_container] = {}
RULES[dovecot_container] = {}
RULES[sogo_container] = {}
RULES[php_fpm_container] = {}

RULES[postfix_container][1] = 'warning: .*\[([0-9a-f\.:]+)\]: SASL .* authentication failed'
RULES[dovecot_container][1] = '-login: Disconnected \(auth failed, .*\): user=.*, method=.*, rip=([0-9a-f\.:]+),'
RULES[dovecot_container][2] = '-login: Disconnected \(no auth .+\): user=.+, rip=([0-9a-f\.:]+), lip.+'
RULES[dovecot_container][3] = '-login: Aborted login \(no auth .+\): user=.+, rip=([0-9a-f\.:]+), lip.+'
RULES[dovecot_container][4] = '-login: Aborted login \(tried to use disallowed .+\): user=.+, rip=([0-9a-f\.:]+), lip.+'
RULES[sogo_container][1] = 'SOGo.* Login from \'([0-9a-f\.:]+)\' for user .* might not have worked'
RULES[php_fpm_container][1] = 'mailcow UI: Invalid password for .* by ([0-9a-f\.:]+)'


r.setnx("F2B_BAN_TIME", "1800")
r.setnx("F2B_MAX_ATTEMPTS", "10")
r.setnx("F2B_RETRY_WINDOW", "600")

bans = {}
log = {}
quit_now = False

def ban(address):
	BAN_TIME = int(r.get("F2B_BAN_TIME"))
	MAX_ATTEMPTS = int(r.get("F2B_MAX_ATTEMPTS"))
	RETRY_WINDOW = int(r.get("F2B_RETRY_WINDOW"))
	WHITELIST = r.hgetall("F2B_WHITELIST")

	ip = ipaddress.ip_address(address.decode('ascii'))
	if type(ip) is ipaddress.IPv6Address and ip.ipv4_mapped:
		ip = ip.ipv4_mapped
		address = str(ip)
	if ip.is_private or ip.is_loopback:
		return

	self_network = ipaddress.ip_network(address.decode('ascii'))
	if WHITELIST:
		for wl_key in WHITELIST:
			wl_net = ipaddress.ip_network(wl_key.decode('ascii'), False)
			if wl_net.overlaps(self_network):
				log['time'] = int(round(time.time()))
				log['priority'] = "info"
				log['message'] = "Address %s is whitelisted by rule %s" % (self_network, wl_net)
				r.lpush("F2B_LOG", json.dumps(log, ensure_ascii=False))
				print "Address %s is whitelisted by rule %s" % (self_network, wl_net)
				return

	net = ipaddress.ip_network((address + ('/24' if type(ip) is ipaddress.IPv4Address else '/64')).decode('ascii'), strict=False)
	net = str(net)

	if not net in bans or time.time() - bans[net]['last_attempt'] > RETRY_WINDOW:
		bans[net] = { 'attempts': 0 }
		active_window = RETRY_WINDOW
	else:
		active_window = time.time() - bans[net]['last_attempt']

	bans[net]['attempts'] += 1
	bans[net]['last_attempt'] = time.time()

	active_window = time.time() - bans[net]['last_attempt']

	if bans[net]['attempts'] >= MAX_ATTEMPTS:
		log['time'] = int(round(time.time()))
		log['priority'] = "crit"
		log['message'] = "Banning %s" % net
		r.lpush("F2B_LOG", json.dumps(log, ensure_ascii=False))
		print "Banning %s for %d minutes" % (net, BAN_TIME / 60)
		if type(ip) is ipaddress.IPv4Address:
			subprocess.call(["iptables", "-I", "INPUT", "-s", net, "-j", "REJECT"])
			subprocess.call(["iptables", "-I", "FORWARD", "-s", net, "-j", "REJECT"])
		else:
			subprocess.call(["ip6tables", "-I", "INPUT", "-s", net, "-j", "REJECT"])
			subprocess.call(["ip6tables", "-I", "FORWARD", "-s", net, "-j", "REJECT"])
		r.hset("F2B_ACTIVE_BANS", "%s" % net, log['time'] + BAN_TIME)
	else:
		log['time'] = int(round(time.time()))
		log['priority'] = "warn"
		log['message'] = "%d more attempts in the next %d seconds until %s is banned" % (MAX_ATTEMPTS - bans[net]['attempts'], RETRY_WINDOW, net)
		r.lpush("F2B_LOG", json.dumps(log, ensure_ascii=False))
		print "%d more attempts in the next %d seconds until %s is banned" % (MAX_ATTEMPTS - bans[net]['attempts'], RETRY_WINDOW, net)

def unban(net):
	log['time'] = int(round(time.time()))
	log['priority'] = "info"
	r.lpush("F2B_LOG", json.dumps(log, ensure_ascii=False))
	if not net in bans:
		log['message'] = "%s is not banned, skipping unban and deleting from queue (if any)" % net
		r.lpush("F2B_LOG", json.dumps(log, ensure_ascii=False))
		print "%s is not banned, skipping unban and deleting from queue (if any)" % net
		r.hdel("F2B_QUEUE_UNBAN", "%s" % net)
		return
	log['message'] = "Unbanning %s" % net
	r.lpush("F2B_LOG", json.dumps(log, ensure_ascii=False))
	print "Unbanning %s" % net
	if type(ipaddress.ip_network(net.decode('ascii'))) is ipaddress.IPv4Network:
		subprocess.call(["iptables", "-D", "INPUT", "-s", net, "-j", "REJECT"])
		subprocess.call(["iptables", "-D", "FORWARD", "-s", net, "-j", "REJECT"])
	else:
		subprocess.call(["ip6tables", "-D", "INPUT", "-s", net, "-j", "REJECT"])
		subprocess.call(["ip6tables", "-D", "FORWARD", "-s", net, "-j", "REJECT"])
	r.hdel("F2B_ACTIVE_BANS", "%s" % net)
	r.hdel("F2B_QUEUE_UNBAN", "%s" % net)
	del bans[net]

def quit(signum, frame):
	global quit_now
	quit_now = True

def clear():
	log['time'] = int(round(time.time()))
	log['priority'] = "info"
	log['message'] = "Clearing all bans"
	r.lpush("F2B_LOG", json.dumps(log, ensure_ascii=False))
	print "Clearing all bans"
	for net in bans.copy():
		unban(net)

def watch(container):
	log['time'] = int(round(time.time()))
	log['priority'] = "info"
	log['message'] = "Watching %s" % container
	r.lpush("F2B_LOG", json.dumps(log, ensure_ascii=False))
	print "Watching", container
	for msg in client.containers.get(container).attach(stream=True, logs=False):
		for rule_id, rule_regex in RULES[container].iteritems():
			result = re.search(rule_regex, msg)
			if result:
				addr = result.group(1)
				print "%s matched rule id %d in %s" % (addr, rule_id, container)
				log['time'] = int(round(time.time()))
				log['priority'] = "warn"
				log['message'] = "%s matched rule id %d in %s" % (addr, rule_id, container)
				r.lpush("F2B_LOG", json.dumps(log, ensure_ascii=False))
				ban(addr)

def autopurge():
	while not quit_now:
		BAN_TIME = int(r.get("F2B_BAN_TIME"))
		MAX_ATTEMPTS = int(r.get("F2B_MAX_ATTEMPTS"))
		QUEUE_UNBAN = r.hgetall("F2B_QUEUE_UNBAN")
		if QUEUE_UNBAN:
			for net in QUEUE_UNBAN:
				unban(str(net))
		for net in bans.copy():
			if bans[net]['attempts'] >= MAX_ATTEMPTS:
				if time.time() - bans[net]['last_attempt'] > BAN_TIME:
					unban(net)
		time.sleep(30)

if __name__ == '__main__':
	threads = []
	for container in RULES:
		threads.append(Thread(target=watch, args=(container,)))
		threads[-1].daemon = True
		threads[-1].start()

	autopurge_thread = Thread(target=autopurge)
	autopurge_thread.daemon = True
	autopurge_thread.start()

	signal.signal(signal.SIGTERM, quit)
	atexit.register(clear)

	while not quit_now:
		for thread in threads:
			if not thread.isAlive():
				break
		time.sleep(0.1)
	
	clear()

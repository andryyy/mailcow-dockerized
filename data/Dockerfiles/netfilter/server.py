#!/usr/bin/env python3

import re
import os
import sys
import time
import atexit
import signal
import ipaddress
from collections import Counter
from random import randint
from threading import Thread
from threading import Lock
import redis
import json
import iptc
import dns.resolver
import dns.exception

while True:
  try:
    redis_slaveof_ip = os.getenv('REDIS_SLAVEOF_IP', '')
    redis_slaveof_port = os.getenv('REDIS_SLAVEOF_PORT', '')
    if "".__eq__(redis_slaveof_ip):
      r = redis.StrictRedis(host=os.getenv('IPV4_NETWORK', '172.22.1') + '.249', decode_responses=True, port=6379, db=0)
    else:
      r = redis.StrictRedis(host=redis_slaveof_ip, decode_responses=True, port=redis_slaveof_port, db=0)
    r.ping()
  except Exception as ex:
    print('%s - trying again in 3 seconds'  % (ex))
    time.sleep(3)
  else:
    break

pubsub = r.pubsub()

WHITELIST = []
BLACKLIST= []

bans = {}

quit_now = False
exit_code = 0
lock = Lock()

def log(priority, message):
  tolog = {}
  tolog['time'] = int(round(time.time()))
  tolog['priority'] = priority
  tolog['message'] = message
  r.lpush('NETFILTER_LOG', json.dumps(tolog, ensure_ascii=False))
  print(message)

def logWarn(message):
  log('warn', message)

def logCrit(message):
  log('crit', message)

def logInfo(message):
  log('info', message)

def refreshF2boptions():
  global f2boptions
  global quit_now
  global exit_code
  if not r.get('F2B_OPTIONS'):
    f2boptions = {}
    f2boptions['ban_time'] = int
    f2boptions['max_attempts'] = int
    f2boptions['retry_window'] = int
    f2boptions['netban_ipv4'] = int
    f2boptions['netban_ipv6'] = int
    f2boptions['ban_time'] = r.get('F2B_BAN_TIME') or 1800
    f2boptions['max_attempts'] = r.get('F2B_MAX_ATTEMPTS') or 10
    f2boptions['retry_window'] = r.get('F2B_RETRY_WINDOW') or 600
    f2boptions['netban_ipv4'] = r.get('F2B_NETBAN_IPV4') or 32
    f2boptions['netban_ipv6'] = r.get('F2B_NETBAN_IPV6') or 128
    r.set('F2B_OPTIONS', json.dumps(f2boptions, ensure_ascii=False))
  else:
    try:
      f2boptions = {}
      f2boptions = json.loads(r.get('F2B_OPTIONS'))
    except ValueError:
      print('Error loading F2B options: F2B_OPTIONS is not json')
      quit_now = True
      exit_code = 2

def refreshF2bregex():
  global f2bregex
  global quit_now
  global exit_code
  if not r.get('F2B_REGEX'):
    f2bregex = {}
    f2bregex[1] = 'warning: .*\[([0-9a-f\.:]+)\]: SASL .+ authentication failed'
    f2bregex[2] = '-login: Disconnected \(auth failed, .+\): user=.*, method=.+, rip=([0-9a-f\.:]+),'
    f2bregex[3] = '-login: Aborted login \(tried to use disallowed .+\): user=.+, rip=([0-9a-f\.:]+), lip.+'
    f2bregex[4] = 'SOGo.+ Login from \'([0-9a-f\.:]+)\' for user .+ might not have worked'
    f2bregex[5] = 'mailcow UI: Invalid password for .+ by ([0-9a-f\.:]+)'
    f2bregex[6] = '([0-9a-f\.:]+) \"GET \/SOGo\/.* HTTP.+\" 403 .+'
    f2bregex[7] = 'Rspamd UI: Invalid password by ([0-9a-f\.:]+)'
    f2bregex[8] = '-login: Aborted login \(auth failed .+\): user=.+, rip=([0-9a-f\.:]+), lip.+'
    f2bregex[9] = 'NOQUEUE: reject: RCPT from \[([0-9a-f\.:]+)].+Protocol error.+'
    r.set('F2B_REGEX', json.dumps(f2bregex, ensure_ascii=False))
  else:
    try:
      f2bregex = {}
      f2bregex = json.loads(r.get('F2B_REGEX'))
    except ValueError:
      print('Error loading F2B options: F2B_REGEX is not json')
      quit_now = True
      exit_code = 2

if r.exists('F2B_LOG'):
  r.rename('F2B_LOG', 'NETFILTER_LOG')

def mailcowChainOrder():
  global lock
  global quit_now
  global exit_code
  while not quit_now:
    time.sleep(10)
    with lock:
      filter4_table = iptc.Table(iptc.Table.FILTER)
      filter6_table = iptc.Table6(iptc.Table6.FILTER)
      filter4_table.refresh()
      filter6_table.refresh()
      for f in [filter4_table, filter6_table]:
        forward_chain = iptc.Chain(f, 'FORWARD')
        input_chain = iptc.Chain(f, 'INPUT')
        for chain in [forward_chain, input_chain]:
          target_found = False
          for position, item in enumerate(chain.rules):
            if item.target.name == 'MAILCOW':
              target_found = True
              if position > 2:
                logCrit('Error in %s chain order: MAILCOW on position %d, restarting container' % (chain.name, position))
                quit_now = True
                exit_code = 2
          if not target_found:
            logCrit('Error in %s chain: MAILCOW target not found, restarting container' % (chain.name))
            quit_now = True
            exit_code = 2

def ban(address):
  global lock
  refreshF2boptions()
  BAN_TIME = int(f2boptions['ban_time'])
  MAX_ATTEMPTS = int(f2boptions['max_attempts'])
  RETRY_WINDOW = int(f2boptions['retry_window'])
  NETBAN_IPV4 = '/' + str(f2boptions['netban_ipv4'])
  NETBAN_IPV6 = '/' + str(f2boptions['netban_ipv6'])

  ip = ipaddress.ip_address(address)
  if type(ip) is ipaddress.IPv6Address and ip.ipv4_mapped:
    ip = ip.ipv4_mapped
    address = str(ip)
  if ip.is_private or ip.is_loopback:
    return

  self_network = ipaddress.ip_network(address)

  with lock:
    temp_whitelist = set(WHITELIST)

  if temp_whitelist:
    for wl_key in temp_whitelist:
      wl_net = ipaddress.ip_network(wl_key, False)
      if wl_net.overlaps(self_network):
        logInfo('Address %s is whitelisted by rule %s' % (self_network, wl_net))
        return

  net = ipaddress.ip_network((address + (NETBAN_IPV4 if type(ip) is ipaddress.IPv4Address else NETBAN_IPV6)), strict=False)
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
    cur_time = int(round(time.time()))
    logCrit('Banning %s for %d minutes' % (net, BAN_TIME / 60))
    if type(ip) is ipaddress.IPv4Address:
      with lock:
        chain = iptc.Chain(iptc.Table(iptc.Table.FILTER), 'MAILCOW')
        rule = iptc.Rule()
        rule.src = net
        target = iptc.Target(rule, "REJECT")
        rule.target = target
        if rule not in chain.rules:
          chain.insert_rule(rule)
    else:
      with lock:
        chain = iptc.Chain(iptc.Table6(iptc.Table6.FILTER), 'MAILCOW')
        rule = iptc.Rule6()
        rule.src = net
        target = iptc.Target(rule, "REJECT")
        rule.target = target
        if rule not in chain.rules:
          chain.insert_rule(rule)
    r.hset('F2B_ACTIVE_BANS', '%s' % net, cur_time + BAN_TIME)
  else:
    logWarn('%d more attempts in the next %d seconds until %s is banned' % (MAX_ATTEMPTS - bans[net]['attempts'], RETRY_WINDOW, net))

def unban(net):
  global lock
  if not net in bans:
   logInfo('%s is not banned, skipping unban and deleting from queue (if any)' % net)
   r.hdel('F2B_QUEUE_UNBAN', '%s' % net)
   return
  logInfo('Unbanning %s' % net)
  if type(ipaddress.ip_network(net)) is ipaddress.IPv4Network:
    with lock:
      chain = iptc.Chain(iptc.Table(iptc.Table.FILTER), 'MAILCOW')
      rule = iptc.Rule()
      rule.src = net
      target = iptc.Target(rule, "REJECT")
      rule.target = target
      if rule in chain.rules:
        chain.delete_rule(rule)
  else:
    with lock:
      chain = iptc.Chain(iptc.Table6(iptc.Table6.FILTER), 'MAILCOW')
      rule = iptc.Rule6()
      rule.src = net
      target = iptc.Target(rule, "REJECT")
      rule.target = target
      if rule in chain.rules:
        chain.delete_rule(rule)
  r.hdel('F2B_ACTIVE_BANS', '%s' % net)
  r.hdel('F2B_QUEUE_UNBAN', '%s' % net)
  if net in bans:
    del bans[net]

def permBan(net, unban=False):
  global lock
  if type(ipaddress.ip_network(net, strict=False)) is ipaddress.IPv4Network:
    with lock:
      chain = iptc.Chain(iptc.Table(iptc.Table.FILTER), 'MAILCOW')
      rule = iptc.Rule()
      rule.src = net
      target = iptc.Target(rule, "REJECT")
      rule.target = target
      if rule not in chain.rules and not unban:
        logCrit('Add host/network %s to blacklist' % net)
        chain.insert_rule(rule)
        r.hset('F2B_PERM_BANS', '%s' % net, int(round(time.time()))) 
      elif rule in chain.rules and unban:
        logCrit('Remove host/network %s from blacklist' % net)
        chain.delete_rule(rule)
        r.hdel('F2B_PERM_BANS', '%s' % net)
  else:
    with lock:
      chain = iptc.Chain(iptc.Table6(iptc.Table6.FILTER), 'MAILCOW')
      rule = iptc.Rule6()
      rule.src = net
      target = iptc.Target(rule, "REJECT")
      rule.target = target
      if rule not in chain.rules and not unban:
        logCrit('Add host/network %s to blacklist' % net)
        chain.insert_rule(rule)
        r.hset('F2B_PERM_BANS', '%s' % net, int(round(time.time()))) 
      elif rule in chain.rules and unban:
        logCrit('Remove host/network %s from blacklist' % net)
        chain.delete_rule(rule)
        r.hdel('F2B_PERM_BANS', '%s' % net)

def quit(signum, frame):
  global quit_now
  quit_now = True

def clear():
  global lock
  logInfo('Clearing all bans')
  for net in bans.copy():
    unban(net)
  with lock:
    filter4_table = iptc.Table(iptc.Table.FILTER)
    filter6_table = iptc.Table6(iptc.Table6.FILTER)
    for filter_table in [filter4_table, filter6_table]:
      filter_table.autocommit = False
      forward_chain = iptc.Chain(filter_table, "FORWARD")
      input_chain = iptc.Chain(filter_table, "INPUT")
      mailcow_chain = iptc.Chain(filter_table, "MAILCOW")
      if mailcow_chain in filter_table.chains:
        for rule in mailcow_chain.rules:
          mailcow_chain.delete_rule(rule)
        for rule in forward_chain.rules:
          if rule.target.name == 'MAILCOW':
            forward_chain.delete_rule(rule)
        for rule in input_chain.rules:
          if rule.target.name == 'MAILCOW':
            input_chain.delete_rule(rule)
        filter_table.delete_chain("MAILCOW")
      filter_table.commit()
      filter_table.refresh()
      filter_table.autocommit = True
    r.delete('F2B_ACTIVE_BANS')
    r.delete('F2B_PERM_BANS')
    pubsub.unsubscribe()

def watch():
  logInfo('Watching Redis channel F2B_CHANNEL')
  pubsub.subscribe('F2B_CHANNEL')

  global quit_now
  global exit_code

  while not quit_now:
    try:
      for item in pubsub.listen():
        refreshF2bregex()
        for rule_id, rule_regex in f2bregex.items():
          if item['data'] and item['type'] == 'message':
            try:
              result = re.search(rule_regex, item['data'])
            except re.error:
              result = False
            if result:
              addr = result.group(1)
              ip = ipaddress.ip_address(addr)
              if ip.is_private or ip.is_loopback:
                continue
              logWarn('%s matched rule id %s (%s)' % (addr, rule_id, item['data']))
              ban(addr)
    except Exception as ex:
      logWarn('Error reading log line from pubsub')
      quit_now = True
      exit_code = 2

def snat4(snat_target):
  global lock
  global quit_now

  def get_snat4_rule():
    rule = iptc.Rule()
    rule.src = os.getenv('IPV4_NETWORK', '172.22.1') + '.0/24'
    rule.dst = '!' + rule.src
    target = rule.create_target("SNAT")
    target.to_source = snat_target
    return rule

  while not quit_now:
    time.sleep(10)
    with lock:
      try:
        table = iptc.Table('nat')
        table.refresh()
        chain = iptc.Chain(table, 'POSTROUTING')
        table.autocommit = False
        if get_snat4_rule() not in chain.rules:
          logCrit('Added POSTROUTING rule for source network %s to SNAT target %s' % (get_snat4_rule().src, snat_target))
          chain.insert_rule(get_snat4_rule())
          table.commit()
        else:
          for position, item in enumerate(chain.rules):
            if item == get_snat4_rule():
              if position != 0:
                chain.delete_rule(get_snat4_rule())
          table.commit()
        table.autocommit = True
      except:
        print('Error running SNAT4, retrying...') 

def snat6(snat_target):
  global lock
  global quit_now

  def get_snat6_rule():
    rule = iptc.Rule6()
    rule.src = os.getenv('IPV6_NETWORK', 'fd4d:6169:6c63:6f77::/64')
    rule.dst = '!' + rule.src
    target = rule.create_target("SNAT")
    target.to_source = snat_target
    return rule

  while not quit_now:
    time.sleep(10)
    with lock:
      try:
        table = iptc.Table6('nat')
        table.refresh()
        chain = iptc.Chain(table, 'POSTROUTING')
        table.autocommit = False
        if get_snat6_rule() not in chain.rules:
          logInfo('Added POSTROUTING rule for source network %s to SNAT target %s' % (get_snat6_rule().src, snat_target))
          chain.insert_rule(get_snat6_rule())
          table.commit()
        else:
          for position, item in enumerate(chain.rules):
            if item == get_snat6_rule():
              if position != 0:
                chain.delete_rule(get_snat6_rule())
          table.commit()
        table.autocommit = True
      except:
        print('Error running SNAT6, retrying...') 

def autopurge():
  while not quit_now:
    time.sleep(10)
    refreshF2boptions()
    BAN_TIME = int(f2boptions['ban_time'])
    MAX_ATTEMPTS = int(f2boptions['max_attempts'])
    QUEUE_UNBAN = r.hgetall('F2B_QUEUE_UNBAN')
    if QUEUE_UNBAN:
      for net in QUEUE_UNBAN:
        unban(str(net))
    for net in bans.copy():
      if bans[net]['attempts'] >= MAX_ATTEMPTS:
        if time.time() - bans[net]['last_attempt'] > BAN_TIME:
          unban(net)

def isIpNetwork(address):
  try:
    ipaddress.ip_network(address, False)
  except ValueError:
    return False
  return True


def genNetworkList(list):
  resolver = dns.resolver.Resolver()
  hostnames = []
  networks = []
  for key in list:
    if isIpNetwork(key):
      networks.append(key)
    else:
      hostnames.append(key)
  for hostname in hostnames:
    hostname_ips = []
    for rdtype in ['A', 'AAAA']:
      try:
        answer = resolver.resolve(qname=hostname, rdtype=rdtype, lifetime=3)
      except dns.exception.Timeout:
        logInfo('Hostname %s timedout on resolve' % hostname)
        break
      except (dns.resolver.NXDOMAIN, dns.resolver.NoAnswer):
        continue
      except dns.exception.DNSException as dnsexception:
        logInfo('%s' % dnsexception)
        continue
      for rdata in answer:
        hostname_ips.append(rdata.to_text())
    networks.extend(hostname_ips)
  return set(networks)

def whitelistUpdate():
  global lock
  global quit_now
  global WHITELIST
  while not quit_now:
    start_time = time.time()
    list = r.hgetall('F2B_WHITELIST')
    new_whitelist = []
    if list:
      new_whitelist = genNetworkList(list)
    with lock:
      if Counter(new_whitelist) != Counter(WHITELIST):
        WHITELIST = new_whitelist
        logInfo('Whitelist was changed, it has %s entries' % len(WHITELIST))
    time.sleep(60.0 - ((time.time() - start_time) % 60.0)) 

def blacklistUpdate():
  global quit_now
  global BLACKLIST
  while not quit_now:
    start_time = time.time()
    list = r.hgetall('F2B_BLACKLIST')
    new_blacklist = []
    if list:
      new_blacklist = genNetworkList(list)
    if Counter(new_blacklist) != Counter(BLACKLIST): 
      addban = set(new_blacklist).difference(BLACKLIST)
      delban = set(BLACKLIST).difference(new_blacklist)
      BLACKLIST = new_blacklist
      logInfo('Blacklist was changed, it has %s entries' % len(BLACKLIST))
      if addban:
        for net in addban:
          permBan(net=net)
      if delban:
        for net in delban:
          permBan(net=net, unban=True)
    time.sleep(60.0 - ((time.time() - start_time) % 60.0)) 

def initChain():
  # Is called before threads start, no locking
  print("Initializing mailcow netfilter chain")
  # IPv4
  if not iptc.Chain(iptc.Table(iptc.Table.FILTER), "MAILCOW") in iptc.Table(iptc.Table.FILTER).chains:
    iptc.Table(iptc.Table.FILTER).create_chain("MAILCOW")
  for c in ['FORWARD', 'INPUT']:
    chain = iptc.Chain(iptc.Table(iptc.Table.FILTER), c)
    rule = iptc.Rule()
    rule.src = '0.0.0.0/0'
    rule.dst = '0.0.0.0/0'
    target = iptc.Target(rule, "MAILCOW")
    rule.target = target
    if rule not in chain.rules:
      chain.insert_rule(rule)
  # IPv6
  if not iptc.Chain(iptc.Table6(iptc.Table6.FILTER), "MAILCOW") in iptc.Table6(iptc.Table6.FILTER).chains:
    iptc.Table6(iptc.Table6.FILTER).create_chain("MAILCOW")
  for c in ['FORWARD', 'INPUT']:
    chain = iptc.Chain(iptc.Table6(iptc.Table6.FILTER), c)
    rule = iptc.Rule6()
    rule.src = '::/0'
    rule.dst = '::/0'
    target = iptc.Target(rule, "MAILCOW")
    rule.target = target
    if rule not in chain.rules:
      chain.insert_rule(rule)

if __name__ == '__main__':

  # In case a previous session was killed without cleanup
  clear()
  # Reinit MAILCOW chain
  initChain()

  watch_thread = Thread(target=watch)
  watch_thread.daemon = True
  watch_thread.start()

  if os.getenv('SNAT_TO_SOURCE') and os.getenv('SNAT_TO_SOURCE') != 'n':
    try:
      snat_ip = os.getenv('SNAT_TO_SOURCE')
      snat_ipo = ipaddress.ip_address(snat_ip)
      if type(snat_ipo) is ipaddress.IPv4Address:
        snat4_thread = Thread(target=snat4,args=(snat_ip,))
        snat4_thread.daemon = True
        snat4_thread.start()
    except ValueError:
      print(os.getenv('SNAT_TO_SOURCE') + ' is not a valid IPv4 address')

  if os.getenv('SNAT6_TO_SOURCE') and os.getenv('SNAT6_TO_SOURCE') != 'n':
    try:
      snat_ip = os.getenv('SNAT6_TO_SOURCE')
      snat_ipo = ipaddress.ip_address(snat_ip)
      if type(snat_ipo) is ipaddress.IPv6Address:
        snat6_thread = Thread(target=snat6,args=(snat_ip,))
        snat6_thread.daemon = True
        snat6_thread.start()
    except ValueError:
      print(os.getenv('SNAT6_TO_SOURCE') + ' is not a valid IPv6 address')

  autopurge_thread = Thread(target=autopurge)
  autopurge_thread.daemon = True
  autopurge_thread.start()

  mailcowchainwatch_thread = Thread(target=mailcowChainOrder)
  mailcowchainwatch_thread.daemon = True
  mailcowchainwatch_thread.start()

  blacklistupdate_thread = Thread(target=blacklistUpdate)
  blacklistupdate_thread.daemon = True
  blacklistupdate_thread.start()

  whitelistupdate_thread = Thread(target=whitelistUpdate)
  whitelistupdate_thread.daemon = True
  whitelistupdate_thread.start()

  signal.signal(signal.SIGTERM, quit)
  atexit.register(clear)

  while not quit_now:
    time.sleep(0.5)

  sys.exit(exit_code)

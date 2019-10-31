<?php
function valid_network($network) {
  $cidr = explode('/', $network);
  if (filter_var($cidr[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) && (!isset($cidr[1]) || ($cidr[1] >= 0 && $cidr[1] <= 32))) {
    return true;
  }
  elseif (filter_var($cidr[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) && (!isset($cidr[1]) || ($cidr[1] >= 0 && $cidr[1] <= 128))) {
    return true;
  }
  return false;
}

function valid_hostname($hostname) {
    return filter_var($hostname, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);
}

function fail2ban($_action, $_data = null) {
  global $redis;
  global $lang;
  $_data_log = $_data;
  switch ($_action) {
    case 'get':
      $f2b_options = array();
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        return false;
      }
      try {
        $f2b_options = json_decode($redis->Get('F2B_OPTIONS'), true);
        $wl = $redis->hGetAll('F2B_WHITELIST');
        if (is_array($wl)) {
          foreach ($wl as $key => $value) {
            $tmp_wl_data[] = $key;
          }
          if (isset($tmp_wl_data)) {
            natsort($tmp_wl_data);
            $f2b_options['whitelist'] = implode(PHP_EOL, $tmp_wl_data);
          }
          else {
            $f2b_options['whitelist'] = "";
          }
        }
        else {
          $f2b_options['whitelist'] = "";
        }
        $bl = $redis->hGetAll('F2B_BLACKLIST');
        if (is_array($bl)) {
          foreach ($bl as $key => $value) {
            $tmp_bl_data[] = $key;
          }
          if (isset($tmp_bl_data)) {
            natsort($tmp_bl_data);
            $f2b_options['blacklist'] = implode(PHP_EOL, $tmp_bl_data);
          }
          else {
            $f2b_options['blacklist'] = "";
          }
        }
        else {
          $f2b_options['blacklist'] = "";
        }
        $pb = $redis->hGetAll('F2B_PERM_BANS');
        if (is_array($pb)) {
          foreach ($pb as $key => $value) {
            $f2b_options['perm_bans'][] = $key;
          }
        }
        else {
          $f2b_options['perm_bans'] = "";
        }
        $active_bans = $redis->hGetAll('F2B_ACTIVE_BANS');
        $queue_unban = $redis->hGetAll('F2B_QUEUE_UNBAN');
        if (is_array($active_bans)) {
          foreach ($active_bans as $network => $banned_until) {
            $queued_for_unban = (isset($queue_unban[$network]) && $queue_unban[$network] == 1) ? 1 : 0;
            $difference = $banned_until - time();
            $f2b_options['active_bans'][] = array(
              'queued_for_unban' => $queued_for_unban,
              'network' => $network,
              'banned_until' => sprintf('%02dh %02dm %02ds', ($difference/3600), ($difference/60%60), $difference%60)
            );
          }
        }
        else {
          $f2b_options['active_bans'] = "";
        }
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
      return $f2b_options;
    break;
    case 'edit':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      if (isset($_data['action']) && !empty($_data['network'])) {
        $networks = (array) $_data['network'];
        foreach ($networks as $network) {
          try {
            if ($_data['action'] == "unban") {
              if (valid_network($network)) {
                $redis->hSet('F2B_QUEUE_UNBAN', $network, 1);
              }
            }
            elseif ($_data['action'] == "whitelist") {
              if (valid_network($network)) {
                $redis->hSet('F2B_WHITELIST', $network, 1);
                $redis->hDel('F2B_BLACKLIST', $network, 1);
                $redis->hSet('F2B_QUEUE_UNBAN', $network, 1);
              }
              else  {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_data_log),
                  'msg' => array('network_host_invalid', $network)
                );
                continue;
              }
            }
            elseif ($_data['action'] == "blacklist") {
              if (valid_network($network)) {
                $redis->hSet('F2B_BLACKLIST', $network, 1);
                $redis->hDel('F2B_WHITELIST', $network, 1);
                //$response = docker('post', 'netfilter-mailcow', 'restart');
              }
              else  {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_data_log),
                  'msg' => array('network_host_invalid', $network)
                );
                continue;
              }
            }
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data_log),
              'msg' => array('redis_error', $e)
            );
            continue;
          }
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('object_modified', htmlspecialchars($network))
          );
        }
        return true;
      }
      $is_now = fail2ban('get');
      if (!empty($is_now)) {
        $ban_time = intval((isset($_data['ban_time'])) ? $_data['ban_time'] : $is_now['ban_time']);
        $max_attempts = intval((isset($_data['max_attempts'])) ? $_data['max_attempts'] : $is_now['active_int']);
        $retry_window = intval((isset($_data['retry_window'])) ? $_data['retry_window'] : $is_now['retry_window']);
        $netban_ipv4 = intval((isset($_data['netban_ipv4'])) ? $_data['netban_ipv4'] : $is_now['netban_ipv4']);
        $netban_ipv6 = intval((isset($_data['netban_ipv6'])) ? $_data['netban_ipv6'] : $is_now['netban_ipv6']);
        $wl = (isset($_data['whitelist'])) ? $_data['whitelist'] : $is_now['whitelist'];
        $bl = (isset($_data['blacklist'])) ? $_data['blacklist'] : $is_now['blacklist'];
      }
      else {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      $f2b_options = array();
      $f2b_options['ban_time'] = ($ban_time < 60) ? 60 : $ban_time;
      $f2b_options['netban_ipv4'] = ($netban_ipv4 < 8) ? 8 : $netban_ipv4;
      $f2b_options['netban_ipv6'] = ($netban_ipv6 < 8) ? 8 : $netban_ipv6;
      $f2b_options['netban_ipv4'] = ($netban_ipv4 > 32) ? 32 : $netban_ipv4;
      $f2b_options['netban_ipv6'] = ($netban_ipv6 > 128) ? 128 : $netban_ipv6;
      $f2b_options['max_attempts'] = ($max_attempts < 1) ? 1 : $max_attempts;
      $f2b_options['retry_window'] = ($retry_window < 1) ? 1 : $retry_window;
      try {
        $redis->Set('F2B_OPTIONS', json_encode($f2b_options));
        $redis->Del('F2B_WHITELIST');
        $redis->Del('F2B_BLACKLIST');
        if(!empty($wl)) {
          $wl_array = array_map('trim', preg_split( "/( |,|;|\n)/", $wl));
          if (is_array($wl_array)) {
            foreach ($wl_array as $wl_item) {
              if (valid_network($wl_item) || valid_hostname($wl_item)) {
                $redis->hSet('F2B_WHITELIST', $wl_item, 1);
              }
            }
          }
        }
        if(!empty($bl)) {
          $bl_array = array_map('trim', preg_split( "/( |,|;|\n)/", $bl));
          if (is_array($bl_array)) {
            foreach ($bl_array as $bl_item) {
              if (valid_network($bl_item) || valid_hostname($bl_item)) {
                $redis->hSet('F2B_BLACKLIST', $bl_item, 1);
              }
            }
          }
        }
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => 'f2b_modified'
      );
    break;
  }
}

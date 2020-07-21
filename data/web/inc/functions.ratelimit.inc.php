<?php
function ratelimit($_action, $_scope, $_data = null) {
  global $redis;
  $_data_log = $_data;
  switch ($_action) {
    case 'edit':
      if (!isset($_SESSION['acl']['ratelimit']) || $_SESSION['acl']['ratelimit'] != "1" ) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
          'msg' => 'access_denied'
        );
        return false;
      }
      switch ($_scope) {
        case 'domain':
          if (!is_array($_data['object'])) {
            $objects = array();
            $objects[] = $_data['object'];
          }
          else {
            $objects = $_data['object'];
          }
          foreach ($objects as $object) {
            $rl_value = intval($_data['rl_value']);
            $rl_frame = $_data['rl_frame'];
            if (!in_array($rl_frame, array('s', 'm', 'h', 'd'))) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'rl_timeframe'
              );
              continue;
            }
            if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $object)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (empty($rl_value)) {
              try {
                $redis->hDel('RL_VALUE', $object);
              }
              catch (RedisException $e) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                  'msg' => array('redis_error', $e)
                );
                continue;
              }
            }
            else {
              try {
                $redis->hSet('RL_VALUE', $object, $rl_value . ' / 1' . $rl_frame);
              }
              catch (RedisException $e) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                  'msg' => array('redis_error', $e)
                );
                continue;
              }
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => array('rl_saved', $object)
            );
          }
        break;
        case 'mailbox':
          if (!is_array($_data['object'])) {
            $objects = array();
            $objects[] = $_data['object'];
          }
          else {
            $objects = $_data['object'];
          }
          foreach ($objects as $object) {
            $rl_value = intval($_data['rl_value']);
            $rl_frame = $_data['rl_frame'];
            if (!in_array($rl_frame, array('s', 'm', 'h', 'd'))) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'rl_timeframe'
              );
              continue;
            }
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $object)
              || ($_SESSION['mailcow_cc_role'] != 'admin' && $_SESSION['mailcow_cc_role'] != 'domainadmin')) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (empty($rl_value)) {
              try {
                $redis->hDel('RL_VALUE', $object);
              }
              catch (RedisException $e) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                  'msg' => array('redis_error', $e)
                );
                continue;
              }
            }
            else {
              try {
                $redis->hSet('RL_VALUE', $object, $rl_value . ' / 1' . $rl_frame);
              }
              catch (RedisException $e) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                  'msg' => array('redis_error', $e)
                );
                continue;
              }
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => array('rl_saved', $object)
            );
          }
        break;
      }
    break;
    case 'get':
      switch ($_scope) {
        case 'domain':
          if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            return false;
          }
          try {
            if ($rl_value = $redis->hGet('RL_VALUE', $_data)) {
              $rl = explode(' / 1', $rl_value);
              $data['value'] = $rl[0];
              $data['frame'] = $rl[1];
              return $data;
            }
            else {
              return false;
            }
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
          return false;
        break;
        case 'mailbox':
          if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)
            || ($_SESSION['mailcow_cc_role'] != 'admin' && $_SESSION['mailcow_cc_role'] != 'domainadmin')) {
            return false;
          }
          try {
            if ($rl_value = $redis->hGet('RL_VALUE', $_data)) {
              $rl = explode(' / 1', $rl_value);
              $data['value'] = $rl[0];
              $data['frame'] = $rl[1];
              return $data;
            }
            else {
              return false;
            }
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
          return false;
        break;
      }
    break;
    case 'delete':
      $data['hash'] = $_data;
      if ($_SESSION['mailcow_cc_role'] != 'admin' || !preg_match('/^RL[0-9A-Za-z=]+$/i', trim($data['hash']))) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
          'msg' => 'access_denied'
        );
        return false;
      }
      try {
        $data_rllog = $redis->lRange('RL_LOG', 0, -1);
        if ($data_rllog) {
          foreach ($data_rllog as $json_line) {
            if (preg_match('/' . $data['hash'] . '/i', $json_line)) {
              $redis->lRem('RL_LOG', $json_line, 0);
            }
          }
        }
        if ($redis->type($data['hash']) == Redis::REDIS_HASH) {
          $redis->delete($data['hash']);
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
            'msg' => 'hash_deleted'
          );
          return true;
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'warning',
            'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
            'msg' => 'hash_not_found'
          );
          return false;
        }
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
      return false;
    break;
  }
}
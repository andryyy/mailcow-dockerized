<?php
function relayhost($_action, $_data = null) {
	global $pdo;
	global $lang;
  $_data_log = $_data;
  switch ($_action) {
    case 'add':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      $hostname = trim($_data['hostname']);
      $username = str_replace(':', '\:', trim($_data['username']));
      $password = str_replace(':', '\:', trim($_data['password']));
      if (empty($hostname)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('invalid_host', htmlspecialchars($host))
        );
        return false;
      }
      try {
        $stmt = $pdo->prepare("INSERT INTO `relayhosts` (`hostname`, `username` ,`password`, `active`)
          VALUES (:hostname, :username, :password, :active)");
        $stmt->execute(array(
          ':hostname' => $hostname,
          ':username' => $username,
          ':password' => str_replace(':', '\:', $password),
          ':active' => '1'
        ));
      }
      catch (PDOException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('mysql_error', $e)
        );
        return false;
      }
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => array('relayhost_added', htmlspecialchars(implode(', ', $hosts)))
      );
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
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        $is_now = relayhost('details', $id);
        if (!empty($is_now)) {
          $hostname = (!empty($_data['hostname'])) ? trim($_data['hostname']) : $is_now['hostname'];
          $username = (isset($_data['username'])) ? trim($_data['username']) : $is_now['username'];
          $password = (isset($_data['password'])) ? trim($_data['password']) : $is_now['password'];
          $active   = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active'];
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('relayhost_invalid', $id)
          );
          continue;
        }
        try {
          $stmt = $pdo->prepare("UPDATE `relayhosts` SET
            `hostname` = :hostname,
            `username` = :username,
            `password` = :password,
            `active` = :active
              WHERE `id` = :id");
          $stmt->execute(array(
            ':id' => $id,
            ':hostname' => $hostname,
            ':username' => $username,
            ':password' => $password,
            ':active' => $active
          ));
        }
        catch (PDOException $e) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('mysql_error', $e)
          );
          continue;
        }
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('object_modified', htmlspecialchars(implode(', ', $hostnames)))
        );
      }
    break;
    case 'delete':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        try {
          $stmt = $pdo->prepare("DELETE FROM `relayhosts` WHERE `id`= :id");
          $stmt->execute(array(':id' => $id));
          $stmt = $pdo->prepare("UPDATE `domain` SET `relayhost` = '0' WHERE `relayhost`= :id");
          $stmt->execute(array(':id' => $id));
        }
        catch (PDOException $e) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('mysql_error', $e)
          );
          continue;
        }
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('relayhost_removed', htmlspecialchars($id))
        );
      }
    break;
    case 'get':
      if ($_SESSION['mailcow_cc_role'] != "admin" && $_SESSION['mailcow_cc_role'] != "domainadmin") {
        return false;
      }
      $relayhosts = array();
      $stmt = $pdo->query("SELECT `id`, `hostname`, `username`, `active` FROM `relayhosts`");
      $relayhosts = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $relayhosts;
    break;
    case 'details':
      if ($_SESSION['mailcow_cc_role'] != "admin" || !isset($_data)) {
        return false;
      }
      $relayhostdata = array();
      $stmt = $pdo->prepare("SELECT `id`,
        `hostname`,
        `username`,
        `password`,
        `active`,
        CONCAT(LEFT(`password`, 3), '...') AS `password_short`
          FROM `relayhosts`
            WHERE `id` = :id");
      $stmt->execute(array(':id' => $_data));
      $relayhostdata = $stmt->fetch(PDO::FETCH_ASSOC);
      if (!empty($relayhostdata)) {
        $stmt = $pdo->prepare("SELECT GROUP_CONCAT(`domain` SEPARATOR ', ') AS `used_by_domains` FROM `domain` WHERE `relayhost` = :id");
        $stmt->execute(array(':id' => $_data));
        $used_by_domains = $stmt->fetch(PDO::FETCH_ASSOC)['used_by_domains'];
        $used_by_domains = (empty($used_by_domains)) ? '' : $used_by_domains;
        $relayhostdata['used_by_domains'] = $used_by_domains;
        $stmt = $pdo->prepare("SELECT GROUP_CONCAT(`username` SEPARATOR ', ') AS `used_by_mailboxes` FROM `mailbox` WHERE JSON_VALUE(`attributes`, '$.relayhost') = :id");
        $stmt->execute(array(':id' => $_data));
        $used_by_mailboxes = $stmt->fetch(PDO::FETCH_ASSOC)['used_by_mailboxes'];
        $used_by_mailboxes = (empty($used_by_mailboxes)) ? '' : $used_by_mailboxes;
        $relayhostdata['used_by_mailboxes'] = $used_by_mailboxes;
      }
      return $relayhostdata;
    break;
  }
}
function transport($_action, $_data = null) {
	global $pdo;
	global $lang;
  $_data_log = $_data;
  switch ($_action) {
    case 'add':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      $destinations  = array_map('trim', preg_split( "/( |,|;|\n)/", $_data['destination']));
      $active = intval($_data['active']);
      $is_mx_based = intval($_data['is_mx_based']);
      $nexthop = trim($_data['nexthop']);
      if (filter_var($nexthop, FILTER_VALIDATE_IP)) {
        $nexthop = '[' . $nexthop . ']';
      }
      preg_match('/\[(.+)\].*/', $nexthop, $next_hop_matches);
      $next_hop_clean = (isset($next_hop_matches[1])) ? $next_hop_matches[1] : $nexthop;
      $username = str_replace(':', '\:', trim($_data['username']));
      $password = str_replace(':', '\:', trim($_data['password']));
      if (empty($nexthop)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('invalid_nexthop')
        );
        return false;
      }
      $transports = transport('get');
      if (!empty($transports)) {
        foreach ($transports as $transport) {
          $transport_data = transport('details', $transport['id']);
          $existing_nh[] = $transport_data['nexthop'];
          preg_match('/\[(.+)\].*/', $transport_data['nexthop'], $existing_clean_nh[]);
          if (($transport_data['nexthop'] == $nexthop || $transport_data['nexthop'] == $next_hop_clean) && $transport_data['username'] != $username) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data_log),
              'msg' => 'invalid_nexthop_authenticated'
            );
            return false;
          }
          foreach ($destinations as $d_ix => &$dest) {
            if (empty($dest)) {
              unset($destinations[$d_ix]);
              continue;
            }
            if ($transport_data['destination'] == $dest) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_data_log),
                'msg' => array('transport_dest_exists', $dest)
              );
              unset($destinations[$d_ix]);
              continue;
            }
            // ".domain" is a valid destination, "..domain" is not
            if ($is_mx_based == 0 && (empty($dest) || (is_valid_domain_name(preg_replace('/^' . preg_quote('.', '/') . '/', '', $dest)) === false && $dest != '*' && filter_var($dest, FILTER_VALIDATE_EMAIL) === false))) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_data_log),
                'msg' => array('invalid_destination', $dest)
              );
              unset($destinations[$d_ix]);
              continue;
            }
            if ($is_mx_based == 1 && (empty($dest) || @preg_match('/' . $dest . '/', null) === false)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_data_log),
                'msg' => array('invalid_destination', $dest)
              );
              unset($destinations[$d_ix]);
              continue;
            }
          }
        }
      }
      $destinations = array_filter(array_values(array_unique($destinations)));
      if (empty($destinations)) { return false; }
      if (isset($next_hop_matches[1])) {
        if (in_array($next_hop_clean, $existing_nh)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('next_hop_interferes', $next_hop_clean, $nexthop)
          );
          return false;
        }
      }
      else {
        foreach ($existing_clean_nh as $existing_clean_nh_each) {
          if ($existing_clean_nh_each[1] == $nexthop) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data_log),
              'msg' => array('next_hop_interferes_any', $nexthop)
            );
            return false;
          }
        }
      }
      foreach ($destinations as $insert_dest) {
        $stmt = $pdo->prepare("INSERT INTO `transports` (`nexthop`, `destination`, `is_mx_based`, `username` , `password`,  `active`)
          VALUES (:nexthop, :destination, :is_mx_based, :username, :password, :active)");
        $stmt->execute(array(
          ':nexthop' => $nexthop,
          ':destination' => $insert_dest,
          ':is_mx_based' => $is_mx_based,
          ':username' => $username,
          ':password' => str_replace(':', '\:', $password),
          ':active' => $active
        ));
      }
      $stmt = $pdo->prepare("UPDATE `transports` SET
        `username` = :username,
        `password` = :password
          WHERE `nexthop` = :nexthop");
      $stmt->execute(array(
        ':nexthop' => $nexthop,
        ':username' => $username,
        ':password' => $password
      ));
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => array('relayhost_added', htmlspecialchars(implode(', ', $hosts)))
      );
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
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        $is_now = transport('details', $id);
        if (!empty($is_now)) {
          $destination = (!empty($_data['destination'])) ? trim($_data['destination']) : $is_now['destination'];
          $nexthop = (!empty($_data['nexthop'])) ? trim($_data['nexthop']) : $is_now['nexthop'];
          $username = (isset($_data['username'])) ? trim($_data['username']) : $is_now['username'];
          $password = (isset($_data['password'])) ? trim($_data['password']) : $is_now['password'];
          $is_mx_based = (isset($_data['is_mx_based']) && $_data['is_mx_based'] != '') ? intval($_data['is_mx_based']) : $is_now['is_mx_based'];
          $active   = (isset($_data['active']) && $_data['active'] != '') ? intval($_data['active']) : $is_now['active'];
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('relayhost_invalid', $id)
          );
          continue;
        }
        preg_match('/\[(.+)\].*/', $nexthop, $next_hop_matches);
        if (filter_var($nexthop, FILTER_VALIDATE_IP)) {
          $nexthop = '[' . $nexthop . ']';
        }
        $next_hop_clean = (isset($next_hop_matches[1])) ? $next_hop_matches[1] : $nexthop;
        $transports = transport('get');
        if (!empty($transports)) {
          foreach ($transports as $transport) {
            $transport_data = transport('details', $transport['id']);
            if ($transport['id'] == $id) {
              continue;
            }
            $existing_nh[] = $transport_data['nexthop'];
            preg_match('/\[(.+)\].*/', $transport_data['nexthop'], $existing_clean_nh[]);
            if ($transport_data['destination'] == $destination) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_data_log),
                'msg' => 'transport_dest_exists'
              );
              return false;
            }
          }
        }
        if ($is_mx_based == 0 && (empty($destination) || (is_valid_domain_name(preg_replace('/^' . preg_quote('.', '/') . '/', '', $destination)) === false && $destination != '*' && filter_var($destination, FILTER_VALIDATE_EMAIL) === false))) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('invalid_destination', $destination)
          );
          return false;
        }
        if ($is_mx_based == 1 && (empty($destination) || @preg_match('/' . $destination . '/', null) === false)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('invalid_destination', $destination)
          );
          return false;
        }
        if (isset($next_hop_matches[1])) {
          if (in_array($next_hop_clean, $existing_nh)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data_log),
              'msg' => array('next_hop_interferes', $next_hop_clean, $nexthop)
            );
            return false;
          }
        }
        else {
          foreach ($existing_clean_nh as $existing_clean_nh_each) {
            if ($existing_clean_nh_each[1] == $nexthop) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_data_log),
                'msg' => array('next_hop_interferes_any', $nexthop)
              );
              return false;
            }
          }
        }
        if (empty($username)) {
          $password = '';
        }
        try {
          $stmt = $pdo->prepare("UPDATE `transports` SET
            `destination` = :destination,
            `is_mx_based` = :is_mx_based,
            `nexthop` = :nexthop,
            `username` = :username,
            `password` = :password,
            `active` = :active
              WHERE `id` = :id");
          $stmt->execute(array(
            ':id' => $id,
            ':destination' => $destination,
            ':is_mx_based' => $is_mx_based,
            ':nexthop' => $nexthop,
            ':username' => $username,
            ':password' => $password,
            ':active' => $active
          ));
          $stmt = $pdo->prepare("UPDATE `transports` SET
            `username` = :username,
            `password` = :password
              WHERE `nexthop` = :nexthop");
          $stmt->execute(array(
            ':nexthop' => $nexthop,
            ':username' => $username,
            ':password' => $password
          ));
        }
        catch (PDOException $e) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('mysql_error', $e)
          );
          continue;
        }
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('object_modified', htmlspecialchars(implode(', ', $hostnames)))
        );
      }
    break;
    case 'delete':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        try {
          $stmt = $pdo->prepare("DELETE FROM `transports` WHERE `id`= :id");
          $stmt->execute(array(':id' => $id));
        }
        catch (PDOException $e) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('mysql_error', $e)
          );
          continue;
        }
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('relayhost_removed', htmlspecialchars($id))
        );
      }
    break;
    case 'get':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        return false;
      }
      $transports = array();
      $stmt = $pdo->query("SELECT `id`, `is_mx_based`, `destination`, `nexthop`, `username` FROM `transports`");
      $transports = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $transports;
    break;
    case 'details':
      if ($_SESSION['mailcow_cc_role'] != "admin" || !isset($_data)) {
        return false;
      }
      $transportdata = array();
      $stmt = $pdo->prepare("SELECT `id`,
        `is_mx_based`,
        `destination`,
        `nexthop`,
        `username`,
        `password`,
        `active`,
        CONCAT(LEFT(`password`, 3), '...') AS `password_short`
          FROM `transports`
            WHERE `id` = :id");
      $stmt->execute(array(':id' => $_data));
      $transportdata = $stmt->fetch(PDO::FETCH_ASSOC);
      return $transportdata;
    break;
  }
}
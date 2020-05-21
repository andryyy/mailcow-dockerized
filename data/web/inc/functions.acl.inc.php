<?php
function acl($_action, $_scope = null, $_data = null) {
  global $pdo;
  global $lang;
  $_data_log = $_data;
  switch ($_action) {
    case 'edit':
      switch ($_scope) {
        case 'user':
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          foreach ($usernames as $username) {
            // Cast to array for single selections
            $acls = (array)$_data['user_acl'];
            // Create associative array from index array
            // All set items are given 1 as value
            foreach ($acls as $acl_key => $acl_val) {
              $acl_post[$acl_val] = 1;
            }
            // Users cannot change their own ACL
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $username)
              || ($_SESSION['mailcow_cc_role'] != 'admin' && $_SESSION['mailcow_cc_role'] != 'domainadmin')) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'access_denied'
              );
              continue;
            }
            // Read all available acl options by calling acl(get)
            // Set all available acl options we cannot find in the post data to 0, else 1
            $is_now = acl('get', 'user', $username);
            if (!empty($is_now)) {
              foreach ($is_now as $acl_now_name => $acl_now_val) {
                $set_acls[$acl_now_name] = (isset($acl_post[$acl_now_name])) ? 1 : 0;
              }
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'Cannot determine current ACL'
              );
              continue;
            }
            foreach ($set_acls as $set_acl_key => $set_acl_val) {
              $stmt = $pdo->prepare("UPDATE `user_acl` SET " . $set_acl_key . " = " . intval($set_acl_val) . "
                WHERE `username` = :username");
              $stmt->execute(array(
                ':username' => $username,
              ));
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => array('acl_saved', $username)
            );
          }
        break;
        case 'domainadmin':
          if ($_SESSION['mailcow_cc_role'] != 'admin') {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => 'access_denied'
            );
            return false;
          }
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          foreach ($usernames as $username) {
            // Cast to array for single selections
            $acls = (array)$_data['da_acl'];
            // Create associative array from index array
            // All set items are given 1 as value
            foreach ($acls as $acl_key => $acl_val) {
              $acl_post[$acl_val] = 1;
            }
            // Users cannot change their own ACL
            if ($_SESSION['mailcow_cc_role'] != 'admin') {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'access_denied'
              );
              continue;
            }
            // Read all available acl options by calling acl(get)
            // Set all available acl options we cannot find in the post data to 0, else 1
            $is_now = acl('get', 'domainadmin', $username);
            if (!empty($is_now)) {
              foreach ($is_now as $acl_now_name => $acl_now_val) {
                $set_acls[$acl_now_name] = (isset($acl_post[$acl_now_name])) ? 1 : 0;
              }
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'Cannot determine current ACL'
              );
              continue;
            }
            foreach ($set_acls as $set_acl_key => $set_acl_val) {
              $stmt = $pdo->prepare("UPDATE `da_acl` SET " . $set_acl_key . " = " . intval($set_acl_val) . "
                WHERE `username` = :username");
              $stmt->execute(array(
                ':username' => $username,
              ));
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => array('acl_saved', $username)
            );
          }
        break;
      }
    break;
    case 'get':
      switch ($_scope) {
        case 'user':
          if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            return false;
          }
          $stmt = $pdo->prepare("SELECT * FROM `user_acl` WHERE `username` = :username");
          $stmt->execute(array(':username' => $_data));
          $data = $stmt->fetch(PDO::FETCH_ASSOC);
          if ($_SESSION['mailcow_cc_role'] == 'domainadmin') {
            // Domain admins cannot see, add or remove user ACLs they don't have access to by themselves
            // Editing a user will use acl("get", "user") to determine granted ACLs and therefore block unallowed access escalation via form editing
            $self_da_acl = acl('get', 'domainadmin', $_SESSION['mailcow_cc_username']);
            foreach ($self_da_acl as $self_da_acl_key => $self_da_acl_val) {
              if ($self_da_acl_val == 0) {
                unset($data[$self_da_acl_key]);
              }
            }
          }
          if (!empty($data)) {
            unset($data['username']);
            return $data;
          }
          else {
            return false;
          }
        break;
        case 'domainadmin':
          if ($_SESSION['mailcow_cc_role'] != 'admin' && $_SESSION['mailcow_cc_role'] != 'domainadmin') {
            return false;
          }
          if ($_SESSION['mailcow_cc_role'] == 'domainadmin' && $_SESSION['mailcow_cc_username'] != $_data) {
            return false;
          }
          $stmt = $pdo->prepare("SELECT * FROM `da_acl` WHERE `username` = :username");
          $stmt->execute(array(':username' => $_data));
          $data = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!empty($data)) {
            unset($data['username']);
            return $data;
          }
          else {
            return false;
          }
        break;
      }
    break;
    case 'to_session':
      if (!isset($_SESSION['mailcow_cc_role'])) {
        return false;
      }
      unset($_SESSION['acl']);
      $username = strtolower(trim($_SESSION['mailcow_cc_username']));
      // Admins get access to all modules
      if ($_SESSION['mailcow_cc_role'] == 'admin' ||
        (isset($_SESSION["dual-login"]["role"]) && $_SESSION["dual-login"]["role"] == 'admin')) {
        $stmt = $pdo->query("SHOW COLUMNS FROM `user_acl` WHERE `Field` != 'username';");
        $acl_all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        while ($row = array_shift($acl_all)) {
          $acl['acl'][$row['Field']] = 1;
        }
        $stmt = $pdo->query("SHOW COLUMNS FROM `da_acl` WHERE `Field` != 'username';");
        $acl_all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        while ($row = array_shift($acl_all)) {
          $acl['acl'][$row['Field']] = 1;
        }
      }
      elseif ($_SESSION['mailcow_cc_role'] == 'domainadmin' ||
        (isset($_SESSION["dual-login"]["role"]) && $_SESSION["dual-login"]["role"] == 'domainadmin')) {
        // Read all exting user_acl modules and set to 1
        $stmt = $pdo->query("SHOW COLUMNS FROM `user_acl` WHERE `Field` != 'username';");
        $acl_all = $stmt->fetchAll(PDO::FETCH_ASSOC);
        while ($row = array_shift($acl_all)) {
          $acl['acl'][$row['Field']] = 1;
        }
        // Read da_acl rules for current user, OVERWRITE overlapping modules
        // This prevents access to a users sync jobs, when a domain admins was not given access to sync jobs
        $stmt = $pdo->prepare("SELECT * FROM `da_acl` WHERE `username` = :username");
        $stmt->execute(array(':username' => (isset($_SESSION["dual-login"]["username"])) ? $_SESSION["dual-login"]["username"] : $username));
        $acl_user = $stmt->fetch(PDO::FETCH_ASSOC);
        foreach ($acl_user as $acl_user_key => $acl_user_val) {
          $acl['acl'][$acl_user_key] = $acl_user_val;
        }
        unset($acl['acl']['username']);
      }
      elseif ($_SESSION['mailcow_cc_role'] == 'user') {
        $stmt = $pdo->prepare("SELECT * FROM `user_acl` WHERE `username` = :username");
        $stmt->execute(array(':username' => $username));
        $acl['acl'] = $stmt->fetch(PDO::FETCH_ASSOC);
        unset($acl['acl']['username']);
      }
      if (!empty($acl)) {
        $_SESSION = array_merge($_SESSION, $acl);
      }
    break;
  }
}
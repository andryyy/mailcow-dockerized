<?php
function app_passwd($_action, $_data = null) {
	global $pdo;
	global $lang;
  $_data_log = $_data;
  !isset($_data_log['app_passwd']) ?: $_data_log['app_passwd'] = '*';
  !isset($_data_log['app_passwd2']) ?: $_data_log['app_passwd2'] = '*';
  if (isset($_data['username']) && filter_var($_data['username'], FILTER_VALIDATE_EMAIL)) {
    if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data['username'])) {
      $_SESSION['return'][] = array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => 'access_denied'
      );
      return false;
    }
    else {
      $username = $_data['username'];
    }
  }
  else {
    $username = $_SESSION['mailcow_cc_username'];
  }
  switch ($_action) {
    case 'add':
      $app_name = htmlspecialchars(trim($_data['app_name']));
      $password = $_data['app_passwd'];
      $password2 = $_data['app_passwd2'];
      $active = intval($_data['active']);
      $domain = mailbox('get', 'mailbox_details', $username)['domain'];
      if (empty($domain)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      if (!preg_match('/' . $GLOBALS['PASSWD_REGEP'] . '/', $password)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'password_complexity'
        );
        return false;
      }
      if ($password != $password2) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'password_mismatch'
        );
        return false;
      }
      $password_hashed = hash_password($password);
      if (empty($app_name)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'app_name_empty'
        );
        return false;
      }
      $stmt = $pdo->prepare("INSERT INTO `app_passwd` (`name`, `mailbox`, `domain`, `password`, `active`)
        VALUES (:app_name, :mailbox, :domain, :password, :active)");
      $stmt->execute(array(
        ':app_name' => $app_name,
        ':mailbox' => $username,
        ':domain' => $domain,
        ':password' => $password_hashed,
        ':active' => $active
      ));
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => 'app_passwd_added'
      );
    break;
    case 'edit':
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        $is_now = app_passwd('details', $id);
        if (!empty($is_now)) {
          $app_name = (!empty($_data['app_name'])) ? $_data['app_name'] : $is_now['name'];
          $password = (!empty($_data['password'])) ? $_data['password'] : null;
          $password2 = (!empty($_data['password2'])) ? $_data['password2'] : null;
          $active = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active'];
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('app_passwd_id_invalid', $id)
          );
          continue;
        }
        $app_name = htmlspecialchars(trim($app_name));
        if (!empty($password) && !empty($password2)) {
          if (!preg_match('/' . $GLOBALS['PASSWD_REGEP'] . '/', $password)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'password_complexity'
            );
            continue;
          }
          if ($password != $password2) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'password_mismatch'
            );
            continue;
          }
          $password_hashed = hash_password($password);
          $stmt = $pdo->prepare("UPDATE `app_passwd` SET
              `password` = :password_hashed
                WHERE `mailbox` = :username AND `id` = :id");
          $stmt->execute(array(
            ':password_hashed' => $password_hashed,
            ':username' => $username,
            ':id' => $id
          ));
        }
        $stmt = $pdo->prepare("UPDATE `app_passwd` SET
          `name` = :app_name,
          `mailbox` = :username,
          `active` = :active
            WHERE `id` = :id");
        $stmt->execute(array(
          ':app_name' => $app_name,
          ':username' => $username,
          ':active' => $active,
          ':id' => $id
        ));
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('object_modified', htmlspecialchars($ids))
        );
      }
    break;
    case 'delete':
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        $stmt = $pdo->prepare("SELECT `mailbox` FROM `app_passwd` WHERE `id` = :id");
        $stmt->execute(array(':id' => $id));
        $mailbox = $stmt->fetch(PDO::FETCH_ASSOC)['mailbox'];
        if (empty($mailbox)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => 'app_passwd_id_invalid'
          );
          return false;
        }
        if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $mailbox)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => 'access_denied'
          );
          return false;
        }
        $stmt = $pdo->prepare("DELETE FROM `app_passwd` WHERE `id`= :id");
        $stmt->execute(array(':id' => $id));
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('app_passwd_removed', htmlspecialchars($id))
        );
      }
    break;
    case 'get':
      $app_passwds = array();
      $stmt = $pdo->prepare("SELECT `id`, `name` FROM `app_passwd` WHERE `mailbox` = :username");
      $stmt->execute(array(':username' => $username));
      $app_passwds = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $app_passwds;
    break;
    case 'details':
      $app_passwd_data = array();
      $stmt = $pdo->prepare("SELECT `id`,
        `name`,
        `mailbox`,
        `domain`,
        `created`,
        `modified`,
        `active`
          FROM `app_passwd`
            WHERE `id` = :id");
      $stmt->execute(array(':id' => $_data['id']));
      $app_passwd_data = $stmt->fetch(PDO::FETCH_ASSOC);
      if (empty($app_passwd_data)) {
        return false;
      }
      if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $app_passwd_data['mailbox'])) {
        $app_passwd_data = array();
        return false;
      }
      $app_passwd_data['name'] = htmlspecialchars(trim($app_passwd_data['name']));
      return $app_passwd_data;
    break;
  }
}
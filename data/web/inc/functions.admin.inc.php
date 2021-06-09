<?php
function admin($_action, $_data = null) {
  if ($_SESSION['mailcow_cc_role'] != "admin") {
    $_SESSION['return'][] = array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $_action, $_data_log),
      'msg' => 'access_denied'
    );
    return false;
  }
  global $pdo;
  global $lang;
  $_data_log = $_data;
  !isset($_data_log['password']) ?: $_data_log['password'] = '*';
  !isset($_data_log['password2']) ?: $_data_log['password2'] = '*';
  switch ($_action) {
    case 'add':
      $username		= strtolower(trim($_data['username']));
      $password		= $_data['password'];
      $password2  = $_data['password2'];
      $active     = intval($_data['active']);
      if (!ctype_alnum(str_replace(array('_', '.', '-'), '', $username)) || empty ($username) || $username == 'API') {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('username_invalid', $username)
        );
        return false;
      }

      $stmt = $pdo->prepare("SELECT `username` FROM `admin`
        WHERE `username` = :username");
      $stmt->execute(array(':username' => $username));
      $num_results[] = count($stmt->fetchAll(PDO::FETCH_ASSOC));

      $stmt = $pdo->prepare("SELECT `username` FROM `domain_admins`
        WHERE `username` = :username");
      $stmt->execute(array(':username' => $username));
      $num_results[] = count($stmt->fetchAll(PDO::FETCH_ASSOC));

      foreach ($num_results as $num_results_each) {
        if ($num_results_each != 0) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('object_exists', htmlspecialchars($username))
          );
          return false;
        }
      }
      if (password_check($password, $password2) !== true) {
        return false;
      }
      // support pre hashed passwords
      if (preg_match('/^{(ARGON2I|ARGON2ID|BLF-CRYPT|CLEAR|CLEARTEXT|CRYPT|DES-CRYPT|LDAP-MD5|MD5|MD5-CRYPT|PBKDF2|PLAIN|PLAIN-MD4|PLAIN-MD5|PLAIN-TRUNC|PLAIN-TRUNC|SHA|SHA1|SHA256|SHA256-CRYPT|SHA512|SHA512-CRYPT|SMD5|SSHA|SSHA256|SSHA512)}/i', $password)) {
        $password_hashed = $password_new;
      }
      else {
        $password_hashed = hash_password($password_new);
      }
      $stmt = $pdo->prepare("INSERT INTO `admin` (`username`, `password`, `superadmin`, `active`)
        VALUES (:username, :password_hashed, '1', :active)");
      $stmt->execute(array(
        ':username' => $username,
        ':password_hashed' => $password_hashed,
        ':active' => $active
      ));
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => array('admin_added', htmlspecialchars($username))
      );
    break;
    case 'edit':
      if (!is_array($_data['username'])) {
        $usernames = array();
        $usernames[] = $_data['username'];
      }
      else {
        $usernames = $_data['username'];
      }
      foreach ($usernames as $username) {
        $is_now = admin('details', $username);
        if (!empty($is_now)) {
          $active = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active'];
          $username_new = (!empty($_data['username_new'])) ? $_data['username_new'] : $is_now['username'];
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => 'access_denied'
          );
          continue;
        }
        $password     = $_data['password'];
        $password2    = $_data['password2'];
        if ($active == 0) {
          $left_active = 0;
          foreach (admin('get') as $admin) {
            $left_active = $left_active + admin('details', $admin)['active'];
          }
          if ($left_active == 1) {
            $_SESSION['return'][] = array(
              'type' => 'warning',
              'log' => array(__FUNCTION__, $_action, $_data_log),
              'msg' => 'no_active_admin'
            );
            continue;
          }
        }
        if (!ctype_alnum(str_replace(array('_', '.', '-'), '', $username_new))) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('username_invalid', $username_new)
          );
          continue;
        }
        if ($username_new != $username) {
          if (!empty(admin('details', $username_new)['username'])) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data_log),
              'msg' => array('username_invalid', $username_new)
            );
            continue;
          }
        }
        if (!empty($password)) {
          if (password_check($password, $password2) !== true) {
            return false;
          }
          // support pre hashed passwords
          if (preg_match('/^{(ARGON2I|ARGON2ID|BLF-CRYPT|CLEAR|CLEARTEXT|CRYPT|DES-CRYPT|LDAP-MD5|MD5|MD5-CRYPT|PBKDF2|PLAIN|PLAIN-MD4|PLAIN-MD5|PLAIN-TRUNC|PLAIN-TRUNC|SHA|SHA1|SHA256|SHA256-CRYPT|SHA512|SHA512-CRYPT|SMD5|SSHA|SSHA256|SSHA512)}/i', $password)) {
            $password_hashed = $password;
          }
          else {
            $password_hashed = hash_password($password);
          }
          $stmt = $pdo->prepare("UPDATE `admin` SET `username` = :username_new, `active` = :active, `password` = :password_hashed WHERE `username` = :username");
          $stmt->execute(array(
            ':password_hashed' => $password_hashed,
            ':username_new' => $username_new,
            ':username' => $username,
            ':active' => $active
          ));
          if (isset($_data['disable_tfa'])) {
            $stmt = $pdo->prepare("UPDATE `tfa` SET `active` = '0' WHERE `username` = :username");
            $stmt->execute(array(':username' => $username));
          }
          else {
            $stmt = $pdo->prepare("UPDATE `tfa` SET `username` = :username_new WHERE `username` = :username");
            $stmt->execute(array(':username_new' => $username_new, ':username' => $username));
          }
        }
        else {
          $stmt = $pdo->prepare("UPDATE `admin` SET `username` = :username_new, `active` = :active WHERE `username` = :username");
          $stmt->execute(array(
            ':username_new' => $username_new,
            ':username' => $username,
            ':active' => $active
          ));
          if (isset($_data['disable_tfa'])) {
            $stmt = $pdo->prepare("UPDATE `tfa` SET `active` = '0' WHERE `username` = :username");
            $stmt->execute(array(':username' => $username));
          }
          else {
            $stmt = $pdo->prepare("UPDATE `tfa` SET `username` = :username_new WHERE `username` = :username");
            $stmt->execute(array(':username_new' => $username_new, ':username' => $username));
          }
        }
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('admin_modified', htmlspecialchars($username))
        );
      }
      return true;
    break;
    case 'delete':
      $usernames = (array)$_data['username'];
      foreach ($usernames as $username) {
        if ($_SESSION['mailcow_cc_username'] == $username) {
          $_SESSION['return'][] = array(
            'type' => 'warning',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => 'cannot_delete_self'
          );
          continue;
        }
        if (empty(admin('details', $username))) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('username_invalid', $username)
          );
          continue;
        }
        $stmt = $pdo->prepare("DELETE FROM `admin` WHERE `username` = :username");
        $stmt->execute(array(
          ':username' => $username,
        ));
        $stmt = $pdo->prepare("DELETE FROM `domain_admins` WHERE `username` = :username");
        $stmt->execute(array(
          ':username' => $username,
        ));
        $stmt = $pdo->prepare("DELETE FROM `tfa` WHERE `username` = :username");
        $stmt->execute(array(
          ':username' => $username,
        ));
        $stmt = $pdo->prepare("DELETE FROM `fido2` WHERE `username` = :username");
        $stmt->execute(array(
          ':username' => $username,
        ));
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('admin_removed', htmlspecialchars($username))
        );
      }
    break;
    case 'get':
      $admins = array();
      $stmt = $pdo->query("SELECT `username` FROM `admin` WHERE `superadmin` = '1'");
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      while ($row = array_shift($rows)) {
        $admins[] = $row['username'];
      }
      return $admins;
    break;
    case 'details':
      $admindata = array();
      $stmt = $pdo->prepare("SELECT
        `tfa`.`active` AS `tfa_active`,
        `admin`.`username`,
        `admin`.`created`,
        `admin`.`active` AS `active`
          FROM `admin`
          LEFT OUTER JOIN `tfa` ON `tfa`.`username`=`admin`.`username`
            WHERE `admin`.`username`= :admin AND `superadmin` = '1'");
      $stmt->execute(array(
        ':admin' => $_data
      ));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if (empty($row)) {
        return false;
      }
      $admindata['username'] = $row['username'];
      $admindata['tfa_active'] = (is_null($row['tfa_active'])) ? 0 : $row['tfa_active'];
      $admindata['tfa_active_int'] = (is_null($row['tfa_active'])) ? 0 : $row['tfa_active'];
      $admindata['active'] = $row['active'];
      $admindata['active_int'] = $row['active'];
      $admindata['created'] = $row['created'];
      return $admindata;
    break;
  }
}

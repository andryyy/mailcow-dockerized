<?php
function policy($_action, $_scope, $_data = null) {
	global $pdo;
	global $redis;
	global $lang;
	$_data_log = $_data;
  switch ($_action) {
    case 'add':
      if (!isset($_SESSION['acl']['spam_policy']) || $_SESSION['acl']['spam_policy'] != "1" ) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      switch ($_scope) {
        case 'domain':
          $object = $_data['domain'];
          if (is_valid_domain_name($object)) {
            if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $object)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'access_denied'
              );
              return false;
            }
            $object = idn_to_ascii(strtolower(trim($object)), 0, INTL_IDNA_VARIANT_UTS46);
          }
          else {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => 'access_denied'
            );
            return false;
          }
          if ($_data['object_list'] == "bl") {
            $object_list = "blacklist_from";
          }
          elseif ($_data['object_list'] == "wl") {
            $object_list = "whitelist_from";
          }
          $object_from = trim(strtolower($_data['object_from']));
          if (!ctype_alnum(str_replace(array('@', '_', '.', '-', '*'), '', $object_from))) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => 'policy_list_from_invalid'
            );
            return false;
          }
          if ($object_list != "blacklist_from" && $object_list != "whitelist_from") {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => 'access_denied'
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `object` FROM `filterconf`
            WHERE (`option` = 'whitelist_from'  OR `option` = 'blacklist_from')
              AND `object` = :object
              AND `value` = :object_from");
          $stmt->execute(array(':object' => $object, ':object_from' => $object_from));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => 'policy_list_from_exists'
            );
            return false;
          }

          $stmt = $pdo->prepare("INSERT INTO `filterconf` (`object`, `option` ,`value`)
            VALUES (:object, :object_list, :object_from)");
          $stmt->execute(array(
            ':object' => $object,
            ':object_list' => $object_list,
            ':object_from' => $object_from
          ));

          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
            'msg' => array('domain_modified', $object)
          );
        break;
        case 'mailbox':
          $object = $_data['username'];
          if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $object)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => 'access_denied'
            );
            return false;
          }
          if ($_data['object_list'] == "bl") {
            $object_list = "blacklist_from";
          }
          elseif ($_data['object_list'] == "wl") {
            $object_list = "whitelist_from";
          }
          $object_from = trim(strtolower($_data['object_from']));
          if (!ctype_alnum(str_replace(array('@', '_', '.', '-', '*'), '', $object_from))) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => 'policy_list_from_invalid'
            );
            return false;
          }
          if ($object_list != "blacklist_from" && $object_list != "whitelist_from") {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => 'access_denied'
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `object` FROM `filterconf`
            WHERE (`option` = 'whitelist_from'  OR `option` = 'blacklist_from')
              AND `object` = :object
              AND `value` = :object_from");
          $stmt->execute(array(':object' => $object, ':object_from' => $object_from));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => 'policy_list_from_exists'
            );
            return false;
          }
          $stmt = $pdo->prepare("INSERT INTO `filterconf` (`object`, `option` ,`value`)
            VALUES (:object, :object_list, :object_from)");
          $stmt->execute(array(
            ':object' => $object,
            ':object_list' => $object_list,
            ':object_from' => $object_from
          ));
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
            'msg' => array('mailbox_modified', $object)
          );
        break;
      }
    break;
    case 'delete':
      if (!isset($_SESSION['acl']['spam_policy']) || $_SESSION['acl']['spam_policy'] != "1" ) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      switch ($_scope) {
        case 'domain':
          (array)$prefids = $_data['prefid'];
          foreach ($prefids as $prefid) {
            if (!is_numeric($prefid)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("SELECT `object` FROM `filterconf` WHERE `prefid` = :prefid");
            $stmt->execute(array(':prefid' => $prefid));
            $object = $stmt->fetch(PDO::FETCH_ASSOC)['object'];
            if (is_valid_domain_name($object)) {
              if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $object)) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                  'msg' => 'access_denied'
                );
                continue;
              }
              $object = idn_to_ascii(strtolower(trim($object)), 0, INTL_IDNA_VARIANT_UTS46);
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'access_denied'
              );
              continue;
            }
            try {
              $stmt = $pdo->prepare("DELETE FROM `filterconf` WHERE `object` = :object AND `prefid` = :prefid");
              $stmt->execute(array(
                ':object' => $object,
                ':prefid' => $prefid
              ));
            }
            catch (PDOException $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => array('mysql_error', $e)
              );
              continue;
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => array('item_deleted',$prefid)
            );
          }
        break;
        case 'mailbox':
          if (!is_array($_data['prefid'])) {
            $prefids = array();
            $prefids[] = $_data['prefid'];
          }
          else {
            $prefids = $_data['prefid'];
          }
          foreach ($prefids as $prefid) {
            if (!is_numeric($prefid)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("SELECT `object` FROM `filterconf` WHERE `prefid` = :prefid");
            $stmt->execute(array(':prefid' => $prefid));
            $object = $stmt->fetch(PDO::FETCH_ASSOC)['object'];
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $object)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => 'access_denied'
              );
              continue;
            }
            try {
              $stmt = $pdo->prepare("DELETE FROM `filterconf` WHERE `object` = :object AND `prefid` = :prefid");
              $stmt->execute(array(
                ':object' => $object,
                ':prefid' => $prefid
              ));
            }
            catch (PDOException $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
                'msg' => array('mysql_error', $e)
              );
              continue;
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_scope, $_data_log),
              'msg' => array('items_deleted', implode(', ', $prefids))
            );
          }
        break;
      }
    break;
    case 'get':
      switch ($_scope) {
        case 'domain':
          if (!is_valid_domain_name($_data)) {
            return false;
          }
          else {
            if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
            $_data = idn_to_ascii(strtolower(trim($_data)), 0, INTL_IDNA_VARIANT_UTS46);
          }

          // WHITELIST
          $stmt = $pdo->prepare("SELECT `object`, `value`, `prefid` FROM `filterconf` WHERE `option`='whitelist_from' AND (`object` LIKE :object_mail OR `object` = :object_domain)");
          $stmt->execute(array(':object_mail' => '%@' . $_data, ':object_domain' => $_data));
          $rows['whitelist'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
          // BLACKLIST
          $stmt = $pdo->prepare("SELECT `object`, `value`, `prefid` FROM `filterconf` WHERE `option`='blacklist_from' AND (`object` LIKE :object_mail OR `object` = :object_domain)");
          $stmt->execute(array(':object_mail' => '%@' . $_data, ':object_domain' => $_data));
          $rows['blacklist'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

          return $rows;
        break;
        case 'mailbox':
          if (isset($_data) && filter_var($_data, FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
          }
          else {
            $_data = $_SESSION['mailcow_cc_username'];
          }
          $domain = mailbox('get', 'mailbox_details', $_data)['domain'];
          if (empty($domain)) {
            return false;
          }
          // WHITELIST
          $stmt = $pdo->prepare("SELECT `object`, `value`, `prefid` FROM `filterconf` WHERE `option`='whitelist_from' AND (`object` = :username OR `object` = :domain)");
          $stmt->execute(array(':username' => $_data, ':domain' => $domain));
          $rows['whitelist'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
          // BLACKLIST
          $stmt = $pdo->prepare("SELECT `object`, `value`, `prefid` FROM `filterconf` WHERE `option`='blacklist_from' AND (`object` = :username OR `object` = :domain)");
          $stmt->execute(array(':username' => $_data, ':domain' => $domain));
          $rows['blacklist'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
          return $rows;
        break;
      }
    break;
  }
}
<?php
function bcc($_action, $_data = null, $attr = null) {
	global $pdo;
	global $lang;
  if ($_SESSION['mailcow_cc_role'] != "admin" && $_SESSION['mailcow_cc_role'] != "domainadmin") {
    return false;
  }
  switch ($_action) {
    case 'add':
      if (!isset($_SESSION['acl']['bcc_maps']) || $_SESSION['acl']['bcc_maps'] != "1" ) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => 'access_denied'
        );
        return false;
      }
      $local_dest = strtolower(trim($_data['local_dest']));
      $bcc_dest = $_data['bcc_dest'];
      $active = intval($_data['active']);
      $type = $_data['type'];
      if ($type != 'sender' && $type != 'rcpt') {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => 'invalid_bcc_map_type'
        );
        return false;
      }
      if (empty($bcc_dest)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => 'bcc_empty'
        );
        return false;
      }
      if (is_valid_domain_name($local_dest)) {
        if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $local_dest)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => 'access_denied'
          );
          return false;
        }
        $domain = idn_to_ascii($local_dest, 0, INTL_IDNA_VARIANT_UTS46);
        $local_dest_sane = '@' . idn_to_ascii($local_dest, 0, INTL_IDNA_VARIANT_UTS46);
      }
      elseif (filter_var($local_dest, FILTER_VALIDATE_EMAIL)) {
        $mailbox = mailbox('get', 'mailbox_details', $local_dest);
        if ($mailbox === false && array_key_exists($local_dest, array_merge($direct_aliases, $shared_aliases)) === false) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => 'access_denied'
          );
          return false;
        }
        if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $local_dest) &&
          !hasAliasObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $local_dest)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data, $_attr),
              'msg' => 'access_denied'
            );
            return false;
        }
        $domain = idn_to_ascii(substr(strstr($local_dest, '@'), 1), 0, INTL_IDNA_VARIANT_UTS46);
        $local_dest_sane = $local_dest;
      }
      else {
        return false;
      }
      if (!filter_var($bcc_dest, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('bcc_must_be_email', htmlspecialchars($bcc_dest))
        );
        return false;
      }

      $stmt = $pdo->prepare("SELECT `id` FROM `bcc_maps`
        WHERE `local_dest` = :local_dest AND `type` = :type");
      $stmt->execute(array(':local_dest' => $local_dest_sane, ':type' => $type));
      $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));

      if ($num_results != 0) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('bcc_exists', htmlspecialchars($local_dest_sane), $type)
        );
        return false;
      }
      $stmt = $pdo->prepare("INSERT INTO `bcc_maps` (`local_dest`, `bcc_dest`, `domain`, `active`, `type`) VALUES
        (:local_dest, :bcc_dest, :domain, :active, :type)");
      $stmt->execute(array(
        ':local_dest' => $local_dest_sane,
        ':bcc_dest' => $bcc_dest,
        ':domain' => $domain,
        ':active' => $active,
        ':type' => $type
      ));
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data, $_attr),
        'msg' => 'bcc_saved'
      );
    break;
    case 'edit':
      if (!isset($_SESSION['acl']['bcc_maps']) || $_SESSION['acl']['bcc_maps'] != "1" ) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => 'access_denied'
        );
        return false;
      }
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        $is_now = bcc('details', $id);
        if (!empty($is_now)) {
          $active = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active'];
          $bcc_dest = (!empty($_data['bcc_dest'])) ? $_data['bcc_dest'] : $is_now['bcc_dest'];
          $local_dest = $is_now['local_dest'];
          $type = (!empty($_data['type'])) ? $_data['type'] : $is_now['type'];
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => 'access_denied'
          );
          continue;
        }
        $active = intval($_data['active']);
        if (!filter_var($bcc_dest, FILTER_VALIDATE_EMAIL)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => array('bcc_must_be_email', $bcc_dest)
          );
          continue;
        }
        if (empty($bcc_dest)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => array('bcc_must_be_email', $bcc_dest)
          );
          continue;
        }
        $stmt = $pdo->prepare("SELECT `id` FROM `bcc_maps`
          WHERE `local_dest` = :local_dest AND `type` = :type");
        $stmt->execute(array(':local_dest' => $local_dest, ':type' => $type));
        $id_now = $stmt->fetch(PDO::FETCH_ASSOC)['id'];

        if (isset($id_now) && $id_now != $id) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => array('bcc_exists', htmlspecialchars($local_dest), $type)
          );
          continue;
        }

        $stmt = $pdo->prepare("UPDATE `bcc_maps` SET `bcc_dest` = :bcc_dest, `active` = :active, `type` = :type WHERE `id`= :id");
        $stmt->execute(array(
          ':bcc_dest' => $bcc_dest,
          ':active' => $active,
          ':type' => $type,
          ':id' => $id
        ));
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('bcc_edited', $bcc_dest)
        );
      }
    break;
    case 'details':
      $bccdata = array();
      $id = intval($_data);

      $stmt = $pdo->prepare("SELECT `id`,
        `local_dest`,
        `bcc_dest`,
        `active`,
        `type`,
        `created`,
        `domain`,
        `modified` FROM `bcc_maps`
          WHERE `id` = :id");
      $stmt->execute(array(':id' => $id));
      $bccdata = $stmt->fetch(PDO::FETCH_ASSOC);

      if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $bccdata['domain'])) {
        $bccdata = null;
        return false;
      }
      return $bccdata;
    break;
    case 'get':
      $bccdata = array();
      $all_items = array();
      $id = intval($_data);

      $stmt = $pdo->query("SELECT `id`, `domain` FROM `bcc_maps`");
      $all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($all_items as $i) {
        if (hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $i['domain'])) {
          $bccdata[] = $i['id'];
        }
      }
      $all_items = null;
      return $bccdata;
    break;
    case 'delete':
      if (!isset($_SESSION['acl']['bcc_maps']) || $_SESSION['acl']['bcc_maps'] != "1" ) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => 'access_denied'
        );
        return false;
      }
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        if (!is_numeric($id)) {
          return false;
        }
        $stmt = $pdo->prepare("SELECT `domain` FROM `bcc_maps` WHERE id = :id");
        $stmt->execute(array(':id' => $id));
        $domain = $stmt->fetch(PDO::FETCH_ASSOC)['domain'];
        if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => 'access_denied'
          );
          continue;
        }
        $stmt = $pdo->prepare("DELETE FROM `bcc_maps` WHERE `id`= :id");
        $stmt->execute(array(':id' => $id));

        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('bcc_deleted', $id)
        );
      }
    break;
  }
}

function recipient_map($_action, $_data = null, $attr = null) {
	global $pdo;
	global $lang;
  if ($_SESSION['mailcow_cc_role'] != "admin") {
    return false;
  }
  switch ($_action) {
    case 'add':
      $old_dest = strtolower(trim($_data['recipient_map_old']));
      if (substr($old_dest, 0, 1) == '@') {
        $old_dest = substr($old_dest, 1);
      }
      $new_dest = strtolower(trim($_data['recipient_map_new']));
      $active = intval($_data['active']);
      if (is_valid_domain_name($old_dest)) {
        $old_dest_sane = '@' . idn_to_ascii($old_dest, 0, INTL_IDNA_VARIANT_UTS46);
      }
      elseif (filter_var($old_dest, FILTER_VALIDATE_EMAIL)) {
        $old_dest_sane = $old_dest;
      }
      else {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('invalid_recipient_map_old', htmlspecialchars($old_dest))
        );
        return false;
      }
      if (!filter_var($new_dest, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('invalid_recipient_map_new', htmlspecialchars($new_dest))
        );
        return false;
      }
      $rmaps = recipient_map('get');
      foreach ($rmaps as $rmap) {
        if (recipient_map('details', $rmap)['recipient_map_old'] == $old_dest_sane) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => array('recipient_map_entry_exists', htmlspecialchars($old_dest_sane))
          );
          return false;
        }
      }
      $stmt = $pdo->prepare("INSERT INTO `recipient_maps` (`old_dest`, `new_dest`, `active`) VALUES
        (:old_dest, :new_dest, :active)");
      $stmt->execute(array(
        ':old_dest' => $old_dest_sane,
        ':new_dest' => $new_dest,
        ':active' => $active
      ));
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data, $_attr),
        'msg' => array('recipient_map_entry_saved', htmlspecialchars($old_dest_sane))
      );
    break;
    case 'edit':
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        $is_now = recipient_map('details', $id);
        if (!empty($is_now)) {
          $active = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active'];
          $new_dest = (!empty($_data['recipient_map_new'])) ? $_data['recipient_map_new'] : $is_now['recipient_map_new'];
          $old_dest = (!empty($_data['recipient_map_old'])) ? $_data['recipient_map_old'] : $is_now['recipient_map_old'];
          if (substr($old_dest, 0, 1) == '@') {
            $old_dest = substr($old_dest, 1);
          }
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => 'access_denied'
          );
          continue;
        }
        if (is_valid_domain_name($old_dest)) {
          $old_dest_sane = '@' . idn_to_ascii($old_dest, 0, INTL_IDNA_VARIANT_UTS46);
        }
        elseif (filter_var($old_dest, FILTER_VALIDATE_EMAIL)) {
          $old_dest_sane = $old_dest;
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => array('invalid_recipient_map_old', htmlspecialchars($old_dest))
          );
          continue;
        }
        if (!filter_var($new_dest, FILTER_VALIDATE_EMAIL)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => array('invalid_recipient_map_new', htmlspecialchars($new_dest))
          );
          continue;
        }
        $rmaps = recipient_map('get');
        foreach ($rmaps as $rmap) {
          if ($rmap == $id) { continue; }
          if (recipient_map('details', $rmap)['recipient_map_old'] == $old_dest_sane) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data, $_attr),
              'msg' => array('recipient_map_entry_exists', htmlspecialchars($old_dest_sane))
            );
            return false;
          }
        }
        $stmt = $pdo->prepare("UPDATE `recipient_maps` SET
          `old_dest` = :old_dest,
          `new_dest` = :new_dest,
          `active` = :active
            WHERE `id`= :id");
        $stmt->execute(array(
          ':old_dest' => $old_dest_sane,
          ':new_dest' => $new_dest,
          ':active' => $active,
          ':id' => $id
        ));
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('recipient_map_entry_saved', htmlspecialchars($old_dest_sane))
        );
      }
    break;
    case 'details':
      $mapdata = array();
      $id = intval($_data);

      $stmt = $pdo->prepare("SELECT `id`,
        `old_dest` AS `recipient_map_old`,
        `new_dest` AS `recipient_map_new`,
        `active`,
        `created`,
        `modified` FROM `recipient_maps`
          WHERE `id` = :id");
      $stmt->execute(array(':id' => $id));
      $mapdata = $stmt->fetch(PDO::FETCH_ASSOC);

      return $mapdata;
    break;
    case 'get':
      $mapdata = array();
      $all_items = array();
      $id = intval($_data);

      $stmt = $pdo->query("SELECT `id` FROM `recipient_maps`");
      $all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($all_items as $i) {
        $mapdata[] = $i['id'];
      }
      $all_items = null;
      return $mapdata;
    break;
    case 'delete':
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        if (!is_numeric($id)) {
          return false;
        }
        $stmt = $pdo->prepare("DELETE FROM `recipient_maps` WHERE `id`= :id");
        $stmt->execute(array(':id' => $id));
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('recipient_map_entry_deleted', htmlspecialchars($id))
        );
      }
    break;
  }
}

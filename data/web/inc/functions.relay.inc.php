<?php
function relay($_action, $_data = null, $attr = null) {
	global $pdo;
	global $lang;
  if ($_SESSION['mailcow_cc_role'] != "admin" && $_SESSION['mailcow_cc_role'] != "domainadmin") {
    return false;
  }
  switch ($_action) {
    case 'add':
      $domain = strtolower(trim($_data['domain']));
      $nexthop = strtolower(trim($_data['nexthop']));
      if (empty($domain)) {
        $_SESSION['return'] = array(
          'type' => 'danger',
          'msg' => 'Domain cannot be empty'
        );
        return false;
      } elseif (empty($nexthop)) {
        $_SESSION['return'] = array(
          'type' => 'danger',
          'msg' => 'Nexthop cannot be empty'
        );
        return false;
      }
      try {
        $stmt = $pdo->prepare("INSERT INTO `transport_maps` (`domain`, `nexthop`) VALUES
          (:domain, :nexthop)");
        $stmt->execute(array(
          ':domain' => $domain,
          ':nexthop' => $nexthop
        ));
      }
      catch (PDOException $e) {
        $_SESSION['return'] = array(
          'type' => 'danger',
          'msg' => 'MySQL: '.$e
        );
        return false;
      }
      $_SESSION['return'] = array(
        'type' => 'success',
        'msg' => 'Relay domain entry saved'
      );
    break;
    case 'edit':
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        $is_now = relay('details', $id);
        if (!empty($is_now)) {
          $id = $is_now['id'];
          $domain = $is_now['domain'];
          $nexthop = $is_now['nexthop'];
        }
        else {
          $_SESSION['return'] = array(
            'type' => 'danger',
            'msg' => sprintf($lang['danger']['access_denied'])
          );
          return false;
        }
        try {
          $stmt = $pdo->prepare("UPDATE `transport_maps` SET `domain` = :domain, `nexthop` = :nexthop WHERE `id`= :id");
          $stmt->execute(array(
            ':domain' => $domain,
            ':nexthop' => $nexthop,
            ':id' => $id
          ));
        }
        catch (PDOException $e) {
          $_SESSION['return'] = array(
            'type' => 'danger',
            'msg' => 'MySQL: '.$e
          );
          return false;
        }
      }
      $_SESSION['return'] = array(
        'type' => 'success',
        'msg' => 'Transport map entry edited'
      );
    break;
    case 'details':
      $relaydata = array();
      $id = intval($_data);
      try {
        $stmt = $pdo->prepare("SELECT `id`, `domain`, `nexthop` FROM `transport_maps` WHERE `id` = :id");
        $stmt->execute(array(':id' => $id));
        $relaydata = $stmt->fetch(PDO::FETCH_ASSOC);
      }
      catch(PDOException $e) {
        $_SESSION['return'] = array(
          'type' => 'danger',
          'msg' => 'MySQL: '.$e
        );
        return false;
      }
      if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $relaydata['domain'])) {
        $relaydata = null;
        return false;
      }
      return $relaydata;
    break;
    case 'get':
      $relaydata = array();
      $all_items = array();
      $id = intval($_data);
      try {
        $stmt = $pdo->query("SELECT `id`, `domain`, `nexthop` FROM `transport_maps`");
        $all_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
      }
      catch(PDOException $e) {
        $_SESSION['return'] = array(
          'type' => 'danger',
          'msg' => 'MySQL: '.$e
        );
        return false;
      }
      foreach ($all_items as $i) {
        if (hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $i['domain'])) {
          $relaydata[] = $i['id'];
        }
      }
      $all_items = null;
      return $relaydata;
    break;
    case 'delete':
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        if (!is_numeric($id)) {
          return false;
        }
        try {
          $stmt = $pdo->prepare("SELECT `domain` FROM `transport_maps` WHERE id = :id");
          $stmt->execute(array(':id' => $id));
          $domain = $stmt->fetch(PDO::FETCH_ASSOC)['domain'];
          if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
            $_SESSION['return'] = array(
              'type' => 'danger',
              'msg' => sprintf($lang['danger']['access_denied'])
            );
            return false;
          }
          $stmt = $pdo->prepare("DELETE FROM `transport_maps` WHERE `id`= :id");
          $stmt->execute(array(':id' => $id));
        }
        catch (PDOException $e) {
          $_SESSION['return'] = array(
            'type' => 'danger',
            'msg' => 'MySQL: '.$e
          );
          return false;
        }
      }
      $_SESSION['return'] = array(
        'type' => 'success',
        'msg' => 'Deleted transport map id/s ' . implode(', ', $ids)
      );
      return true;
    break;
  }
}
<?php
function tls_policy_maps($_action, $_data = null, $attr = null) {
	global $pdo;
	global $lang;
  if ($_SESSION['mailcow_cc_role'] != "admin") {
    return false;
  }
  switch ($_action) {
    case 'add':
      $dest = idn_to_ascii(trim($_data['dest']), 0, INTL_IDNA_VARIANT_UTS46);
      $policy = strtolower(trim($_data['policy']));
      $parameters = (isset($_data['parameters']) && !empty($_data['parameters'])) ? $_data['parameters'] : '';
      if (empty($dest) || in_array($dest, array('.', '*', '@'))) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => 'tls_policy_map_dest_invalid'
        );
        return false;
      }
      if (!empty($parameters)) {
        foreach (explode(' ', $parameters) as $parameter) {
          if (!preg_match('/(.+)\=(.+)/i', $parameter)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data, $_attr),
              'msg' => 'tls_policy_map_parameter_invalid'
            );
            return false;
          }
        }
      }
      $active = intval($_data['active']);
      $tls_policy_maps = tls_policy_maps('get');
      foreach ($tls_policy_maps as $tls_policy_map) {
        if (tls_policy_maps('details', $tls_policy_map)['dest'] == $dest) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => array('tls_policy_map_entry_exists', htmlspecialchars($dest))
          );
          return false;
        }
      }
      $stmt = $pdo->prepare("INSERT INTO `tls_policy_override` (`dest`, `policy`, `parameters`, `active`) VALUES
        (:dest, :policy, :parameters, :active)");
      $stmt->execute(array(
        ':dest' => $dest,
        ':policy' => $policy,
        ':parameters' => $parameters,
        ':active' => $active
      ));
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data, $_attr),
        'msg' => array('tls_policy_map_entry_saved', htmlspecialchars($dest))
      );
    break;
    case 'edit':
      $ids = (array)$_data['id'];
      foreach ($ids as $id) {
        $is_now = tls_policy_maps('details', $id);
        if (!empty($is_now)) {
          $active = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active'];
          $dest = (!empty($_data['dest'])) ? $_data['dest'] : $is_now['dest'];
          $policy = (!empty($_data['policy'])) ? $_data['policy'] : $is_now['policy'];
          $parameters = (isset($_data['parameters'])) ? $_data['parameters'] : $is_now['parameters'];
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => 'access_denied'
          );
          continue;
        }
        if (empty($dest) || in_array($dest, array('.', '*', '@'))) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data, $_attr),
            'msg' => 'tls_policy_map_dest_invalid'
          );
          return false;
        }
        if (!empty($parameters)) {
          foreach (explode(' ', $parameters) as $parameter) {
            if (!preg_match('/(.+)\=(.+)/i', $parameter)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_data, $_attr),
                'msg' => 'tls_policy_map_parameter_invalid'
              );
              return false;
            }
          }
        }
        $tls_policy_maps = tls_policy_maps('get');
        foreach ($tls_policy_maps as $tls_policy_map) {
          if ($tls_policy_map == $id) { continue; }
          if (tls_policy_maps('details', $tls_policy_map)['dest'] == $dest) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data, $_attr),
              'msg' => array('recipient_map_entry_exists', htmlspecialchars($dest))
            );
            return false;
          }
        }
        $stmt = $pdo->prepare("UPDATE `tls_policy_override` SET
          `dest` = :dest,
          `policy` = :policy,
          `parameters` = :parameters,
          `active` = :active
            WHERE `id`= :id");
        $stmt->execute(array(
          ':dest' => $dest,
          ':policy' => $policy,
          ':parameters' => $parameters,
          ':active' => $active,
          ':id' => $id
        ));
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('tls_policy_map_entry_saved', htmlspecialchars($dest))
        );
      }
    break;
    case 'details':
      $mapdata = array();
      $id = intval($_data);
      $stmt = $pdo->prepare("SELECT `id`,
        `dest`,
        `policy`,
        `parameters`,
        `active` AS `active`,
        `created`,
        `modified` FROM `tls_policy_override`
          WHERE `id` = :id");
      $stmt->execute(array(':id' => $id));
      $mapdata = $stmt->fetch(PDO::FETCH_ASSOC);
      return $mapdata;
    break;
    case 'get':
      $mapdata = array();
      $all_items = array();
      $id = intval($_data);
      $stmt = $pdo->query("SELECT `id` FROM `tls_policy_override`");
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
        $stmt = $pdo->prepare("DELETE FROM `tls_policy_override` WHERE `id`= :id");
        $stmt->execute(array(':id' => $id));
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data, $_attr),
          'msg' => array('tls_policy_map_entry_deleted', htmlspecialchars($id))
        );
      }
    break;
  }
}

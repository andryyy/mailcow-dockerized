<?php
function rsettings($_action, $_data = null) {
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
      $content = $_data['content'];
      $desc = $_data['desc'];
      $active = intval($_data['active']);
      if (empty($content)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'map_content_empty'
        );
        return false;
      }
      $stmt = $pdo->prepare("INSERT INTO `settingsmap` (`content`, `desc`, `active`)
        VALUES (:content, :desc, :active)");
      $stmt->execute(array(
        ':content' => $content,
        ':desc' => $desc,
        ':active' => $active
      ));
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => 'settings_map_added'
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
        $is_now = rsettings('details', $id);
        if (!empty($is_now)) {
          $content = (!empty($_data['content'])) ? $_data['content'] : $is_now['content'];
          $desc = (!empty($_data['desc'])) ? $_data['desc'] : $is_now['desc'];
          $active = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active'];
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => array('settings_map_invalid', $id)
          );
          continue;
        }
        $content = trim($content);
        $stmt = $pdo->prepare("UPDATE `settingsmap` SET
          `content` = :content,
          `desc` = :desc,
          `active` = :active
            WHERE `id` = :id");
        $stmt->execute(array(
          ':content' => $content,
          ':desc' => $desc,
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
        $stmt = $pdo->prepare("DELETE FROM `settingsmap` WHERE `id`= :id");
        $stmt->execute(array(':id' => $id));
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('settings_map_removed', htmlspecialchars($id))
        );
      }
    break;
    case 'get':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        return false;
      }
      $settingsmaps = array();
      $stmt = $pdo->query("SELECT `id`, `desc`, `active` FROM `settingsmap`");
      $settingsmaps = $stmt->fetchAll(PDO::FETCH_ASSOC);
      return $settingsmaps;
    break;
    case 'details':
      if ($_SESSION['mailcow_cc_role'] != "admin" || !isset($_data)) {
        return false;
      }
      $settingsmapdata = array();
      $stmt = $pdo->prepare("SELECT `id`,
        `desc`,
        `content`,
        `active`
          FROM `settingsmap`
            WHERE `id` = :id");
      $stmt->execute(array(':id' => $_data));
      $settingsmapdata = $stmt->fetch(PDO::FETCH_ASSOC);
      return $settingsmapdata;
    break;
  }
}
function rspamd_maps($_action, $_data = null) {
	global $pdo;
	global $lang;
	global $RSPAMD_MAPS;
  $_data_log = $_data;
  switch ($_action) {
    case 'edit':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, '-'),
          'msg' => 'access_denied'
        );
        return false;
      }
      $maps = (array)$_data['map'];
      foreach ($maps as $map) {
        foreach ($RSPAMD_MAPS as $rspamd_map_type) {
          if (!in_array($map, $rspamd_map_type)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, '-'),
              'msg' => array('global_map_invalid', $map)
            );
            continue;
          }
        }
        try {
          if (file_exists('/rspamd_custom_maps/' . $map)) {
            $map_content = trim($_data['rspamd_map_data']);
            $map_handle = fopen('/rspamd_custom_maps/' . $map, 'w');
            if (!$map_handle) {
              throw new Exception($lang['danger']['file_open_error']);
            }
            fwrite($map_handle, $map_content . PHP_EOL);
            fclose($map_handle);
            sleep(1.5);
            touch('/rspamd_custom_maps/' . $map);
          }
        }
        catch (Exception $e) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, '-'),
            'msg' => array('global_map_write_error', htmlspecialchars($map), htmlspecialchars($e->getMessage()))
          );
          continue;
        }
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, '-'),
          'msg' => array('object_modified', htmlspecialchars($map))
        );
      }
    break;
  }
}
function rspamd_actions() {
  if (isset($_SESSION["mailcow_cc_role"]) && $_SESSION["mailcow_cc_role"] == "admin") {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_UNIX_SOCKET_PATH, '/var/lib/rspamd/rspamd.sock');
    curl_setopt($curl, CURLOPT_URL,"http://rspamd/stat");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($curl);
    if ($data) {
      $return = array();
      $stats_array = json_decode($data, true)['actions'];
      $stats_array['soft reject'] = $stats_array['soft reject'] + $stats_array['greylist'];
      unset($stats_array['greylist']);
      foreach ($stats_array as $action => $count) {
        $return[] = array($action, $count);
      }
      return $return;
    }
    else {
      return false;
    }
  }
  else {
    return false;
  }
}

<?php
function customize($_action, $_item, $_data = null) {
	global $redis;
	global $lang;
  switch ($_action) {
    case 'add':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_item, $_data),
          'msg' => 'access_denied'
        );
        return false;
      }
      switch ($_item) {
        case 'main_logo':
          if (in_array($_data['main_logo']['type'], array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/x-png', 'image/png', 'image/svg+xml'))) {
            try {
              if (file_exists($_data['main_logo']['tmp_name']) !== true) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_item, $_data),
                  'msg' => 'img_tmp_missing'
                );
                return false;
              }
              $image = new Imagick($_data['main_logo']['tmp_name']);
              if ($image->valid() !== true) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_item, $_data),
                  'msg' => 'img_invalid'
                );
                return false;
              }
              $image->destroy();
            }
            catch (ImagickException $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_item, $_data),
                'msg' => 'img_invalid'
              );
              return false;
            }
          }
          else {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_item, $_data),
              'msg' => 'invalid_mime_type'
            );
            return false;
          }
          try {
            $redis->Set('MAIN_LOGO', 'data:' . $_data['main_logo']['type'] . ';base64,' . base64_encode(file_get_contents($_data['main_logo']['tmp_name'])));
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_item, $_data),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_item, $_data),
            'msg' => 'upload_success'
          );
        break;
      }
    break;
    case 'edit':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_item, $_data),
          'msg' => 'access_denied'
        );
        return false;
      }
      switch ($_item) {
        case 'app_links':
          $apps = (array)$_data['app'];
          $links = (array)$_data['href'];
          $out = array();
          if (count($apps) == count($links)) {
            for ($i = 0; $i < count($apps); $i++) {
              $out[] = array($apps[$i] => $links[$i]);
            }
            try {
              $redis->set('APP_LINKS', json_encode($out));
            }
            catch (RedisException $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_item, $_data),
                'msg' => array('redis_error', $e)
              );
              return false;
            }
          }
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_item, $_data),
            'msg' => 'app_links'
          );
        break;
        case 'ui_texts':
          $title_name = $_data['title_name'];
          $main_name = $_data['main_name'];
          $apps_name = $_data['apps_name'];
          $help_text = $_data['help_text'];
          $ui_footer = $_data['ui_footer'];
          try {
            $redis->set('TITLE_NAME', htmlspecialchars($title_name));
            $redis->set('MAIN_NAME', htmlspecialchars($main_name));
            $redis->set('APPS_NAME', htmlspecialchars($apps_name));
            $redis->set('HELP_TEXT', $help_text);
            $redis->set('UI_FOOTER', $ui_footer);
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_item, $_data),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_item, $_data),
            'msg' => 'ui_texts'
          );
        break;
      }
    break;
    case 'delete':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_item, $_data),
          'msg' => 'access_denied'
        );
        return false;
      }
      switch ($_item) {
        case 'main_logo':
          try {
            if ($redis->del('MAIN_LOGO')) {
              $_SESSION['return'][] = array(
                'type' => 'success',
                'log' => array(__FUNCTION__, $_action, $_item, $_data),
                'msg' => 'reset_main_logo'
              );
              return true;
            }
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_item, $_data),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
        break;
      }
    break;
    case 'get':
      switch ($_item) {
        case 'app_links':
          try {
            $app_links = json_decode($redis->get('APP_LINKS'), true);
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_item, $_data),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
          return ($app_links) ? $app_links : false;
        break;
        case 'main_logo':
          try {
            return $redis->get('MAIN_LOGO');
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_item, $_data),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
        break;
        case 'ui_texts':
          try {
            $data['title_name'] = ($title_name = $redis->get('TITLE_NAME')) ? $title_name : 'mailcow UI';
            $data['main_name'] = ($main_name = $redis->get('MAIN_NAME')) ? $main_name : 'mailcow UI';
            $data['apps_name'] = ($apps_name = $redis->get('APPS_NAME')) ? $apps_name : 'mailcow Apps';
            $data['help_text'] = ($help_text = $redis->get('HELP_TEXT')) ? $help_text : false;
            if (!empty($redis->get('UI_IMPRESS'))) {
              $redis->set('UI_FOOTER', $redis->get('UI_IMPRESS'));
              $redis->del('UI_IMPRESS');
            }
            $data['ui_footer'] = ($ui_footer = $redis->get('UI_FOOTER')) ? $ui_footer : false;
            return $data;
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_item, $_data),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
        break;
        case 'main_logo_specs':
          try {
            $image = new Imagick();
            $img_data = explode('base64,', customize('get', 'main_logo'));
            if ($img_data[1]) {
              $image->readImageBlob(base64_decode($img_data[1]));
            }
            return $image->identifyImage();
          }
          catch (ImagickException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_item, $_data),
              'msg' => 'imagick_exception'
            );
            return false;
          }
        break;
      }
    break;
  }
}
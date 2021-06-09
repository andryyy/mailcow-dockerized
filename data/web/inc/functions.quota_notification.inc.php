<?php
function quota_notification($_action, $_data = null) {
	global $redis;
	$_data_log = $_data;
  if ($_SESSION['mailcow_cc_role'] != "admin") {
    $_SESSION['return'][] = array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $_action, $_data_log),
      'msg' => 'access_denied'
    );
    return false;
  }
  switch ($_action) {
    case 'edit':
      $retention_size = $_data['retention_size'];
      if ($_data['release_format'] == 'attachment' || $_data['release_format'] == 'raw') {
        $release_format = $_data['release_format'];
      }
      else {
        $release_format = 'raw';
      }
      $subject = $_data['subject'];
      $sender = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $_data['sender']);
      if (filter_var($sender, FILTER_VALIDATE_EMAIL) === false) {
        $sender = '';
      }
      $html = $_data['html_tmpl'];
      try {
        $redis->Set('QW_SENDER', $sender);
        $redis->Set('QW_SUBJ', $subject);
        $redis->Set('QW_HTML', $html);
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => 'saved_settings'
      );
    break;
    case 'get':
      try {
        $settings['subject'] = $redis->Get('QW_SUBJ');
        $settings['sender'] = $redis->Get('QW_SENDER');
        $settings['html_tmpl'] = htmlspecialchars($redis->Get('QW_HTML'));
        if (empty($settings['html_tmpl'])) {
          $settings['html_tmpl'] = htmlspecialchars(file_get_contents("/tpls/quota.tpl"));
        }
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
      return $settings;
    break;
  }
}
function quota_notification_bcc($_action, $_data = null) {
	global $redis;
	$_data_log = $_data;
  if ($_SESSION['mailcow_cc_role'] != "admin" && $_SESSION['mailcow_cc_role'] != "domainadmin") {
    $_SESSION['return'][] = array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $_action, $_data_log),
      'msg' => 'access_denied'
    );
    return false;
  }
  switch ($_action) {
    case 'edit':
      $domain = $_data['domain'];
      if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      $active = intval($_data['active']);
      $bcc_rcpts = array_map('trim', preg_split( "/( |,|;|\n)/", $_data['bcc_rcpt']));
      foreach ($bcc_rcpts as $i => &$rcpt) {
        $rcpt = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $rcpt);
          if (!empty($rcpt) && filter_var($rcpt, FILTER_VALIDATE_EMAIL) === false) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data_log),
              'msg' => array('goto_invalid', htmlspecialchars($rcpt))
            );
            unset($bcc_rcpts[$i]);
            continue;
          }
      }
      $bcc_rcpts = array_unique($bcc_rcpts);
      $bcc_rcpts = array_filter($bcc_rcpts);
      if (empty($bcc_rcpts)) {
        $active = 0;
        
      }
      try {
        $redis->hSet('QW_BCC', $domain, json_encode(array('bcc_rcpts' => $bcc_rcpts, 'active' => $active)));
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => 'saved_settings'
      );
    break;
    case 'get':
      $domain = $_data;
      if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      try {
        return json_decode($redis->hGet('QW_BCC', $domain), true);
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
    break;
  }
}

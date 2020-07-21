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

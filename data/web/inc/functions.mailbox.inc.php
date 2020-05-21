<?php
function mailbox($_action, $_type, $_data = null, $_extra = null) {
  global $pdo;
  global $redis;
  global $lang;
  global $MAILBOX_DEFAULT_ATTRIBUTES;
  $_data_log = $_data;
  !isset($_data_log['password']) ?: $_data_log['password'] = '*';
  !isset($_data_log['password2']) ?: $_data_log['password2'] = '*';
  switch ($_action) {
    case 'add':
      switch ($_type) {
        case 'time_limited_alias':
          if (!isset($_SESSION['acl']['spam_alias']) || $_SESSION['acl']['spam_alias'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          if (isset($_data['username']) && filter_var($_data['username'], FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data['username'])) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
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
          if (!is_numeric($_data["validity"]) || $_data["validity"] > 672) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'validity_missing'
            );
            return false;
          }
          $domain = mailbox('get', 'mailbox_details', $username)['domain'];
          if (!is_valid_domain_name($domain)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'domain_invalid'
            );
            return false;
          }
          $validity = strtotime("+".$_data["validity"]." hour");
          $letters = 'abcefghijklmnopqrstuvwxyz1234567890';
          $random_name = substr(str_shuffle($letters), 0, 24);
          $stmt = $pdo->prepare("INSERT INTO `spamalias` (`address`, `goto`, `validity`) VALUES
            (:address, :goto, :validity)");
          $stmt->execute(array(
            ':address' => $random_name . '@' . $domain,
            ':goto' => $username,
            ':validity' => $validity
          ));
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
            'msg' => array('mailbox_modified', $username)
          );
        break;
        case 'global_filter':
          if ($_SESSION['mailcow_cc_role'] != "admin") {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          $sieve = new Sieve\SieveParser();
          $script_data = $_data['script_data'];
          $script_data = str_replace("\r\n", "\n", $script_data); // windows -> unix
          $script_data = str_replace("\r", "\n", $script_data);   // remaining -> unix
          $filter_type = $_data['filter_type'];
          try {
            $sieve->parse($script_data);
          }
          catch (Exception $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('sieve_error', $e->getMessage())
            );
            return false;
          }
          if ($filter_type == 'prefilter') {
            try {
              if (file_exists('/global_sieve/before')) {
                $filter_handle = fopen('/global_sieve/before', 'w');
                if (!$filter_handle) {
                  throw new Exception($lang['danger']['file_open_error']);
                }
                fwrite($filter_handle, $script_data);
                fclose($filter_handle);
              }
              $restart_response = json_decode(docker('post', 'dovecot-mailcow', 'restart'), true);
              if ($restart_response['type'] == "success") {
                $_SESSION['return'][] = array(
                  'type' => 'success',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'dovecot_restart_success'
                );
              }
              else {
                $_SESSION['return'][] = array(
                  'type' => 'warning',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'dovecot_restart_failed'
                );
              }
            }
            catch (Exception $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_data_log),
                'msg' => array('global_filter_write_error', htmlspecialchars($e->getMessage()))
              );
              return false;
            }
          }
          elseif ($filter_type == 'postfilter') {
            try {
              if (file_exists('/global_sieve/after')) {
                $filter_handle = fopen('/global_sieve/after', 'w');
                if (!$filter_handle) {
                  throw new Exception($lang['danger']['file_open_error']);
                }
                fwrite($filter_handle, $script_data);
                fclose($filter_handle);
              }
              $restart_response = json_decode(docker('post', 'dovecot-mailcow', 'restart'), true);
              if ($restart_response['type'] == "success") {
                $_SESSION['return'][] = array(
                  'type' => 'success',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'dovecot_restart_success'
                );
              }
              else {
                $_SESSION['return'][] = array(
                  'type' => 'warning',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'dovecot_restart_failed'
                );
              }
            }
            catch (Exception $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_data_log),
                'msg' => array('global_filter_write_error', htmlspecialchars($e->getMessage()))
              );
              return false;
            }
          }
          else {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'invalid_filter_type'
            );
            return false;
          }
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
            'msg' => 'global_filter_written'
          );
          return true;
        case 'filter':
          $sieve = new Sieve\SieveParser();
          if (!isset($_SESSION['acl']['filters']) || $_SESSION['acl']['filters'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          if (isset($_data['username']) && filter_var($_data['username'], FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data['username'])) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              return false;
            }
            else {
              $username = $_data['username'];
            }
          }
          elseif ($_SESSION['mailcow_cc_role'] == "user") {
            $username = $_SESSION['mailcow_cc_username'];
          }
          else {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'no_user_defined'
            );
            return false;
          }
          $active     = intval($_data['active']);
          $script_data = $_data['script_data'];
          $script_desc = $_data['script_desc'];
          $filter_type = $_data['filter_type'];
          if (empty($script_data)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'script_empty'
            );
            return false;
          }
          try {
            $sieve->parse($script_data);
          }
          catch (Exception $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('sieve_error', $e->getMessage())
            );
            return false;
          }
          if (empty($script_data) || empty($script_desc)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'value_missing'
            );
            return false;
          }
          if ($filter_type != 'postfilter' && $filter_type != 'prefilter') {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'filter_type'
            );
            return false;
          }
          if (!empty($active)) {
            $script_name = 'active';
            $stmt = $pdo->prepare("UPDATE `sieve_filters` SET `script_name` = 'inactive' WHERE `username` = :username AND `filter_type` = :filter_type");
            $stmt->execute(array(
              ':username' => $username,
              ':filter_type' => $filter_type
            ));
          }
          else {
            $script_name = 'inactive';
          }
          $stmt = $pdo->prepare("INSERT INTO `sieve_filters` (`username`, `script_data`, `script_desc`, `script_name`, `filter_type`)
            VALUES (:username, :script_data, :script_desc, :script_name, :filter_type)");
          $stmt->execute(array(
            ':username' => $username,
            ':script_data' => $script_data,
            ':script_desc' => $script_desc,
            ':script_name' => $script_name,
            ':filter_type' => $filter_type
          ));
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
            'msg' => array('mailbox_modified', $username)
          );
        break;
        case 'syncjob':
          if (!isset($_SESSION['acl']['syncjobs']) || $_SESSION['acl']['syncjobs'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          if (isset($_data['username']) && filter_var($_data['username'], FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data['username'])) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              return false;
            }
            else {
              $username = $_data['username'];
            }
          }
          elseif ($_SESSION['mailcow_cc_role'] == "user") {
            $username = $_SESSION['mailcow_cc_username'];
          }
          else {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'no_user_defined'
            );
            return false;
          }
          $active               = intval($_data['active']);
          $subscribeall         = intval($_data['subscribeall']);
          $delete2duplicates    = intval($_data['delete2duplicates']);
          $delete1              = intval($_data['delete1']);
          $delete2              = intval($_data['delete2']);
          $timeout1             = intval($_data['timeout1']);
          $timeout2             = intval($_data['timeout2']);
          $skipcrossduplicates  = intval($_data['skipcrossduplicates']);
          $automap              = intval($_data['automap']);
          $port1                = $_data['port1'];
          $host1                = strtolower($_data['host1']);
          $password1            = $_data['password1'];
          $exclude              = $_data['exclude'];
          $maxage               = $_data['maxage'];
          $maxbytespersecond    = $_data['maxbytespersecond'];
          $subfolder2           = $_data['subfolder2'];
          $user1                = $_data['user1'];
          $mins_interval        = $_data['mins_interval'];
          $enc1                 = $_data['enc1'];
          $custom_params        = (empty(trim($_data['custom_params']))) ? '' : trim($_data['custom_params']);
          if (empty($subfolder2)) {
            $subfolder2 = "";
          }
          if (!isset($maxage) || !filter_var($maxage, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32000)))) {
            $maxage = "0";
          }
          if (!isset($timeout1) || !filter_var($timeout1, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32000)))) {
            $timeout1 = "600";
          }
          if (!isset($timeout2) || !filter_var($timeout2, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32000)))) {
            $timeout2 = "600";
          }
          if (!isset($maxbytespersecond) || !filter_var($maxbytespersecond, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 125000000)))) {
            $maxbytespersecond = "0";
          }
          if (!filter_var($port1, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 65535)))) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          if (!filter_var($mins_interval, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 3600)))) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          // if (!is_valid_domain_name($host1)) {
            // $_SESSION['return'][] = array(
              // 'type' => 'danger',
              // 'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              // 'msg' => 'access_denied'
            // );
            // return false;
          // }
          if ($enc1 != "TLS" && $enc1 != "SSL" && $enc1 != "PLAIN") {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          if (@preg_match("/" . $exclude . "/", null) === false) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT '1' FROM `imapsync`
            WHERE `user2` = :user2 AND `user1` = :user1 AND `host1` = :host1");
          $stmt->execute(array(':user1' => $user1, ':user2' => $username, ':host1' => $host1));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('object_exists', htmlspecialchars($host1 . ' / ' . $user1))
            );
            return false;
          }
          $stmt = $pdo->prepare("INSERT INTO `imapsync` (`user2`, `exclude`, `delete1`, `delete2`, `timeout1`, `timeout2`, `automap`, `skipcrossduplicates`, `maxbytespersecond`, `subscribeall`, `maxage`, `subfolder2`, `host1`, `authmech1`, `user1`, `password1`, `mins_interval`, `port1`, `enc1`, `delete2duplicates`, `custom_params`, `active`)
            VALUES (:user2, :exclude, :delete1, :delete2, :timeout1, :timeout2, :automap, :skipcrossduplicates, :maxbytespersecond, :subscribeall, :maxage, :subfolder2, :host1, :authmech1, :user1, :password1, :mins_interval, :port1, :enc1, :delete2duplicates, :custom_params, :active)");
          $stmt->execute(array(
            ':user2' => $username,
            ':custom_params' => $custom_params,
            ':exclude' => $exclude,
            ':maxage' => $maxage,
            ':delete1' => $delete1,
            ':delete2' => $delete2,
            ':timeout1' => $timeout1,
            ':timeout2' => $timeout2,
            ':automap' => $automap,
            ':skipcrossduplicates' => $skipcrossduplicates,
            ':maxbytespersecond' => $maxbytespersecond,
            ':subscribeall' => $subscribeall,
            ':subfolder2' => $subfolder2,
            ':host1' => $host1,
            ':authmech1' => 'PLAIN',
            ':user1' => $user1,
            ':password1' => $password1,
            ':mins_interval' => $mins_interval,
            ':port1' => $port1,
            ':enc1' => $enc1,
            ':delete2duplicates' => $delete2duplicates,
            ':active' => $active,
          ));
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
            'msg' => array('mailbox_modified', $username)
          );
        break;
        case 'domain':
          if ($_SESSION['mailcow_cc_role'] != "admin") {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          $domain				= idn_to_ascii(strtolower(trim($_data['domain'])), 0, INTL_IDNA_VARIANT_UTS46);
          $description  = $_data['description'];
          if (empty($description)) {
            $description = $domain;
          }
          $aliases			= $_data['aliases'];
          $mailboxes    = $_data['mailboxes'];
          $defquota			= $_data['defquota'];
          $maxquota			= $_data['maxquota'];
          $restart_sogo = $_data['restart_sogo'];
          $quota				= $_data['quota'];
          if ($defquota > $maxquota) {
            $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'mailbox_defquota_exceeds_mailbox_maxquota'
            );
            return false;
          }
          if ($maxquota > $quota) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'mailbox_quota_exceeds_domain_quota'
            );
            return false;
          }
          if ($defquota == "0" || empty($defquota)) {
            $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'defquota_empty'
            );
            return false;
          }
          if ($maxquota == "0" || empty($maxquota)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'maxquota_empty'
            );
            return false;
          }
          $active = intval($_data['active']);
          $relay_all_recipients = intval($_data['relay_all_recipients']);
          $relay_unknown_only = intval($_data['relay_unknown_only']);
          $backupmx = intval($_data['backupmx']);
          $gal = intval($_data['gal']);
          if ($relay_all_recipients == 1) {
            $backupmx = '1';
          }
          if ($relay_unknown_only == 1) {
            $backupmx = 1;
            $relay_all_recipients = 1;
          }
          if (!is_valid_domain_name($domain)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'domain_invalid'
            );
            return false;
          }
          foreach (array($quota, $maxquota, $mailboxes, $aliases) as $data) {
            if (!is_numeric($data)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('object_is_not_numeric', htmlspecialchars($data))
              );
              return false;
            }
          }
          $stmt = $pdo->prepare("SELECT `domain` FROM `domain`
            WHERE `domain` = :domain");
          $stmt->execute(array(':domain' => $domain));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          $stmt = $pdo->prepare("SELECT `alias_domain` FROM `alias_domain`
            WHERE `alias_domain` = :domain");
          $stmt->execute(array(':domain' => $domain));
          $num_results = $num_results + count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('domain_exists', htmlspecialchars($domain))
            );
            return false;
          }
          if ($domain == getenv('MAILCOW_HOSTNAME')) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'domain_cannot_match_hostname'
            );
            return false;
          }
          $stmt = $pdo->prepare("DELETE FROM `sender_acl` WHERE `external` = 1 AND `send_as` LIKE :domain");
          $stmt->execute(array(
            ':domain' => '%@' . $domain
          ));
          $stmt = $pdo->prepare("INSERT INTO `domain` (`domain`, `description`, `aliases`, `mailboxes`, `defquota`, `maxquota`, `quota`, `backupmx`, `gal`, `active`, `relay_unknown_only`, `relay_all_recipients`)
            VALUES (:domain, :description, :aliases, :mailboxes, :defquota, :maxquota, :quota, :backupmx, :gal, :active, :relay_unknown_only, :relay_all_recipients)");
          $stmt->execute(array(
            ':domain' => $domain,
            ':description' => $description,
            ':aliases' => $aliases,
            ':mailboxes' => $mailboxes,
            ':defquota' => $defquota,
            ':maxquota' => $maxquota,
            ':quota' => $quota,
            ':backupmx' => $backupmx,
            ':gal' => $gal,
            ':active' => $active,
            ':relay_unknown_only' => $relay_unknown_only,
            ':relay_all_recipients' => $relay_all_recipients
          ));
          try {
            $redis->hSet('DOMAIN_MAP', $domain, 1);
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
          if (!empty(intval($_data['rl_value']))) {
            ratelimit('edit', 'domain', array('rl_value' => $_data['rl_value'], 'rl_frame' => $_data['rl_frame'], 'object' => $domain));
          }
          if (!empty($restart_sogo)) {
            $restart_response = json_decode(docker('post', 'sogo-mailcow', 'restart'), true);
            if ($restart_response['type'] == "success") {
              $_SESSION['return'][] = array(
                'type' => 'success',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('domain_added', htmlspecialchars($domain))
              );
              return true;
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'warning',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'domain_added_sogo_failed'
              );
              return false;
            }
          }
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
            'msg' => array('domain_added', htmlspecialchars($domain))
          );
          return true;
        break;
        case 'alias':
          $addresses  = array_map('trim', preg_split( "/( |,|;|\n)/", $_data['address']));
          $gotos      = array_map('trim', preg_split( "/( |,|;|\n)/", $_data['goto']));
          $active = intval($_data['active']);
          $sogo_visible = intval($_data['sogo_visible']);
          $goto_null = intval($_data['goto_null']);
          $goto_spam = intval($_data['goto_spam']);
          $goto_ham = intval($_data['goto_ham']);
          $private_comment = $_data['private_comment'];
          $public_comment = $_data['public_comment'];
          if (strlen($private_comment) > 160 | strlen($public_comment) > 160){
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'comment_too_long'
            );
            return false;
          }
          if (empty($addresses[0])) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'alias_empty'
            );
            return false;
          }
          if (empty($gotos[0]) && ($goto_null + $goto_spam + $goto_ham == 0)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'goto_empty'
            );
            return false;
          }
          if ($goto_null == "1") {
            $goto = "null@localhost";
          }
          elseif ($goto_spam == "1") {
            $goto = "spam@localhost";
          }
          elseif ($goto_ham == "1") {
            $goto = "ham@localhost";
          }
          else {
            foreach ($gotos as $i => &$goto) {
              if (empty($goto)) {
                continue;
              }
              $goto_domain = idn_to_ascii(substr(strstr($goto, '@'), 1), 0, INTL_IDNA_VARIANT_UTS46);
              $goto_local_part = strstr($goto, '@', true);
              $goto = $goto_local_part.'@'.$goto_domain;
              $stmt = $pdo->prepare("SELECT `username` FROM `mailbox`
                WHERE `kind` REGEXP 'location|thing|group'
                  AND `username`= :goto");
              $stmt->execute(array(':goto' => $goto));
              $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
              if ($num_results != 0) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('goto_invalid', htmlspecialchars($goto))
                );
                unset($gotos[$i]);
                continue;
              }
              if (!filter_var($goto, FILTER_VALIDATE_EMAIL) === true) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('goto_invalid', htmlspecialchars($goto))
                );
                unset($gotos[$i]);
                continue;
              }
            }
            $gotos = array_filter($gotos);
            if (empty($gotos)) { return false; }
            $goto = implode(",", $gotos);
          }
          foreach ($addresses as $address) {
            if (empty($address)) {
              continue;
            }
            if (in_array($address, $gotos)) {
              continue;
            }
            $domain       = idn_to_ascii(substr(strstr($address, '@'), 1), 0, INTL_IDNA_VARIANT_UTS46);
            $local_part   = strstr($address, '@', true);
            $address      = $local_part.'@'.$domain;
            $domaindata = mailbox('get', 'domain_details', $domain);
            if (is_array($domaindata) && $domaindata['aliases_left'] == "0") {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'max_alias_exceeded'
              );
              return false;
            }
            $stmt = $pdo->prepare("SELECT `address` FROM `alias`
              WHERE `address`= :address OR `address` IN (
                SELECT `username` FROM `mailbox`, `alias_domain`
                  WHERE (
                    `alias_domain`.`alias_domain` = :address_d
                      AND `mailbox`.`username` = CONCAT(:address_l, '@', alias_domain.target_domain)))");
            $stmt->execute(array(
              ':address' => $address,
              ':address_l' => $local_part,
              ':address_d' => $domain
            ));
            $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
            if ($num_results != 0) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('is_alias_or_mailbox', htmlspecialchars($address))
              );
              continue;
            }
            $stmt = $pdo->prepare("SELECT `domain` FROM `domain`
              WHERE `domain`= :domain1 OR `domain` = (SELECT `target_domain` FROM `alias_domain` WHERE `alias_domain` = :domain2)");
            $stmt->execute(array(':domain1' => $domain, ':domain2' => $domain));
            $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
            if ($num_results == 0) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('domain_not_found', htmlspecialchars($domain))
              );
              continue;
            }
            $stmt = $pdo->prepare("SELECT `address` FROM `spamalias`
              WHERE `address`= :address");
            $stmt->execute(array(':address' => $address));
            $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
            if ($num_results != 0) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('is_spam_alias', htmlspecialchars($address))
              );
              continue;
            }
            if ((!filter_var($address, FILTER_VALIDATE_EMAIL) === true) && !empty($local_part)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('alias_invalid', $address)
              );
              continue;
            }
            if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("INSERT INTO `alias` (`address`, `public_comment`, `private_comment`, `goto`, `domain`, `sogo_visible`, `active`)
              VALUES (:address, :public_comment, :private_comment, :goto, :domain, :sogo_visible, :active)");
            if (!filter_var($address, FILTER_VALIDATE_EMAIL) === true) {
              $stmt->execute(array(
                ':address' => '@'.$domain,
                ':public_comment' => $public_comment,
                ':private_comment' => $private_comment,
                ':address' => '@'.$domain,
                ':goto' => $goto,
                ':domain' => $domain,
                ':sogo_visible' => $sogo_visible,
                ':active' => $active
              ));
            }
            else {
              $stmt->execute(array(
                ':address' => $address,
                ':public_comment' => $public_comment,
                ':private_comment' => $private_comment,
                ':goto' => $goto,
                ':domain' => $domain,
                ':sogo_visible' => $sogo_visible,
                ':active' => $active
              ));
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('alias_added', $address)
            );
          }
        break;
        case 'alias_domain':
          $active = intval($_data['active']);
          $alias_domains  = array_map('trim', preg_split( "/( |,|;|\n)/", $_data['alias_domain']));
          $alias_domains = array_filter($alias_domains);
          $target_domain = idn_to_ascii(strtolower(trim($_data['target_domain'])), 0, INTL_IDNA_VARIANT_UTS46);
          if (!isset($_SESSION['acl']['alias_domains']) || $_SESSION['acl']['alias_domains'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          if (!is_valid_domain_name($target_domain)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'target_domain_invalid'
            );
            return false;
          }
          if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $target_domain)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($alias_domains as $i => $alias_domain) {
            $alias_domain = idn_to_ascii(strtolower(trim($alias_domain)), 0, INTL_IDNA_VARIANT_UTS46);
            if (!is_valid_domain_name($alias_domain)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('alias_domain_invalid', htmlspecialchars(alias_domain))
              );
              continue;
            }
            if ($alias_domain == $target_domain) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('aliasd_targetd_identical', htmlspecialchars($target_domain))
              );
              continue;
            }
            $stmt = $pdo->prepare("SELECT `domain` FROM `domain`
              WHERE `domain`= :target_domain");
            $stmt->execute(array(':target_domain' => $target_domain));
            $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
            if ($num_results == 0) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('targetd_not_found', htmlspecialchars($target_domain))
              );
              continue;
            }
            $stmt = $pdo->prepare("SELECT `domain` FROM `domain`
              WHERE `domain`= :target_domain AND `backupmx` = '1'");
            $stmt->execute(array(':target_domain' => $target_domain));
            $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
            if ($num_results == 1) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('targetd_relay_domain', htmlspecialchars($target_domain))
              );
              continue;
            }
            $stmt = $pdo->prepare("SELECT `alias_domain` FROM `alias_domain` WHERE `alias_domain`= :alias_domain
              UNION
              SELECT `domain` FROM `domain` WHERE `domain`= :alias_domain_in_domain");
            $stmt->execute(array(':alias_domain' => $alias_domain, ':alias_domain_in_domain' => $alias_domain));
            $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
            if ($num_results != 0) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('alias_domain_invalid', $alias_domain)
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `sender_acl` WHERE `external` = 1 AND `send_as` LIKE :domain");
            $stmt->execute(array(
              ':domain' => '%@' . $domain
            ));
            $stmt = $pdo->prepare("INSERT INTO `alias_domain` (`alias_domain`, `target_domain`, `active`)
              VALUES (:alias_domain, :target_domain, :active)");
            $stmt->execute(array(
              ':alias_domain' => $alias_domain,
              ':target_domain' => $target_domain,
              ':active' => $active
            ));
            try {
              $redis->hSet('DOMAIN_MAP', $alias_domain, 1);
            }
            catch (RedisException $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('redis_error', $e)
              );
              return false;
            }
            if (!empty(intval($_data['rl_value']))) {
              ratelimit('edit', 'domain', array('rl_value' => $_data['rl_value'], 'rl_frame' => $_data['rl_frame'], 'object' => $alias_domain));
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('aliasd_added', htmlspecialchars($alias_domain))
            );
          }
        break;
        case 'mailbox':
          $local_part   = strtolower(trim($_data['local_part']));
          $domain       = idn_to_ascii(strtolower(trim($_data['domain'])), 0, INTL_IDNA_VARIANT_UTS46);
          $username     = $local_part . '@' . $domain;
          if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'mailbox_invalid'
            );
            return false;
          }
          if (empty($_data['local_part'])) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'mailbox_invalid'
            );
            return false;
          }
          $password     = $_data['password'];
          $password2    = $_data['password2'];
          $name         = ltrim(rtrim($_data['name'], '>'), '<');
          $quota_m			= intval($_data['quota']);
          if ((!isset($_SESSION['acl']['unlimited_quota']) || $_SESSION['acl']['unlimited_quota'] != "1") && $quota_m === 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'unlimited_quota_acl'
            );
            return false;
          }
          if (empty($name)) {
            $name = $local_part;
          }
          $active = intval($_data['active']);
          $quota_b		= ($quota_m * 1048576);
          $mailbox_attrs = json_encode(
            array(
              'force_pw_update' => strval(intval($MAILBOX_DEFAULT_ATTRIBUTES['force_pw_update'])),
              'tls_enforce_in' => strval(intval($MAILBOX_DEFAULT_ATTRIBUTES['tls_enforce_in'])),
              'tls_enforce_out' => strval(intval($MAILBOX_DEFAULT_ATTRIBUTES['tls_enforce_out'])),
              'sogo_access' => (!isset($_SESSION['acl']['sogo_access']) || $_SESSION['acl']['sogo_access'] != "1") ? 0 : strval(intval($MAILBOX_DEFAULT_ATTRIBUTES['sogo_access'])),
              'mailbox_format' => strval($MAILBOX_DEFAULT_ATTRIBUTES['mailbox_format']),
              'quarantine_notification' => strval($MAILBOX_DEFAULT_ATTRIBUTES['quarantine_notification'])
            )
          );
          if (!is_valid_domain_name($domain)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'domain_invalid'
            );
            return false;
          }
          if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `mailboxes`, `maxquota`, `quota` FROM `domain`
            WHERE `domain` = :domain");
          $stmt->execute(array(':domain' => $domain));
          $DomainData = $stmt->fetch(PDO::FETCH_ASSOC);
          $stmt = $pdo->prepare("SELECT
            COUNT(*) as count,
            COALESCE(ROUND(SUM(`quota`)/1048576), 0) as `quota`
              FROM `mailbox`
                WHERE `kind` NOT REGEXP 'location|thing|group'
                  AND `domain` = :domain");
          $stmt->execute(array(':domain' => $domain));
          $MailboxData = $stmt->fetch(PDO::FETCH_ASSOC);
          $stmt = $pdo->prepare("SELECT `local_part` FROM `mailbox` WHERE `local_part` = :local_part and `domain`= :domain");
          $stmt->execute(array(':local_part' => $local_part, ':domain' => $domain));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('object_exists', htmlspecialchars($username))
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `address` FROM `alias` WHERE address= :username");
          $stmt->execute(array(':username' => $username));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('is_alias', htmlspecialchars($username))
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `address` FROM `spamalias` WHERE `address`= :username");
          $stmt->execute(array(':username' => $username));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('is_spam_alias', htmlspecialchars($username))
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `domain` FROM `domain` WHERE `domain`= :domain");
          $stmt->execute(array(':domain' => $domain));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results == 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('domain_not_found', htmlspecialchars($domain))
            );
            return false;
          }
          if (!empty($password) && !empty($password2)) {
            if (!preg_match('/' . $GLOBALS['PASSWD_REGEP'] . '/', $password)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'password_complexity'
              );
              return false;
            }
            if ($password != $password2) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'password_mismatch'
              );
              return false;
            }
            $password_hashed = hash_password($password);
          }
          else {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'password_empty'
            );
            return false;
          }
          if ($MailboxData['count'] >= $DomainData['mailboxes']) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('max_mailbox_exceeded', $MailboxData['count'], $DomainData['mailboxes'])
            );
            return false;
          }
          if ($quota_m > $DomainData['maxquota']) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_quota_exceeded', $DomainData['maxquota'])
            );
            return false;
          }
          if (($MailboxData['quota'] + $quota_m) > $DomainData['quota']) {
            $quota_left_m = ($DomainData['quota'] - $MailboxData['quota']);
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_quota_left_exceeded', $quota_left_m)
            );
            return false;
          }
          $stmt = $pdo->prepare("INSERT INTO `mailbox` (`username`, `password`, `name`, `quota`, `local_part`, `domain`, `attributes`, `active`)
            VALUES (:username, :password_hashed, :name, :quota_b, :local_part, :domain, :mailbox_attrs, :active)");
          $stmt->execute(array(
            ':username' => $username,
            ':password_hashed' => $password_hashed,
            ':name' => $name,
            ':quota_b' => $quota_b,
            ':local_part' => $local_part,
            ':domain' => $domain,
            ':mailbox_attrs' => $mailbox_attrs,
            ':active' => $active
          ));
          $stmt = $pdo->prepare("INSERT INTO `quota2` (`username`, `bytes`, `messages`)
            VALUES (:username, '0', '0') ON DUPLICATE KEY UPDATE `bytes` = '0', `messages` = '0';");
          $stmt->execute(array(':username' => $username));
          $stmt = $pdo->prepare("INSERT INTO `quota2replica` (`username`, `bytes`, `messages`)
            VALUES (:username, '0', '0') ON DUPLICATE KEY UPDATE `bytes` = '0', `messages` = '0';");
          $stmt->execute(array(':username' => $username));
          $stmt = $pdo->prepare("INSERT INTO `alias` (`address`, `goto`, `domain`, `active`)
            VALUES (:username1, :username2, :domain, :active)");
          $stmt->execute(array(
            ':username1' => $username,
            ':username2' => $username,
            ':domain' => $domain,
            ':active' => $active
          ));
          $stmt = $pdo->prepare("INSERT INTO `user_acl` (`username`) VALUES (:username)");
          $stmt->execute(array(
            ':username' => $username
          ));
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
            'msg' => array('mailbox_added', htmlspecialchars($username))
          );
        break;
        case 'resource':
          $domain             = idn_to_ascii(strtolower(trim($_data['domain'])), 0, INTL_IDNA_VARIANT_UTS46);
          $description        = $_data['description'];
          $local_part         = preg_replace('/[^\da-z]/i', '', preg_quote($description, '/'));
          $name               = $local_part . '@' . $domain;
          $kind               = $_data['kind'];
          $multiple_bookings  = intval($_data['multiple_bookings']);
          $active = intval($_data['active']);
          if (!filter_var($name, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'resource_invalid'
            );
            return false;
          }
          if (empty($description)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'description_invalid'
            );
            return false;
          }
          if (!isset($multiple_bookings) || $multiple_bookings < -1) {
            $multiple_bookings = -1;
          }
          if ($kind != 'location' && $kind != 'group' && $kind != 'thing') {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'resource_invalid'
            );
            return false;
          }
          if (!is_valid_domain_name($domain)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'domain_invalid'
            );
            return false;
          }
          if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `username` FROM `mailbox` WHERE `username` = :name");
          $stmt->execute(array(':name' => $name));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('object_exists', htmlspecialchars($name))
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `address` FROM `alias` WHERE address= :name");
          $stmt->execute(array(':name' => $name));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('is_alias', htmlspecialchars($name))
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `address` FROM `spamalias` WHERE `address`= :name");
          $stmt->execute(array(':name' => $name));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results != 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('is_spam_alias', htmlspecialchars($name))
            );
            return false;
          }
          $stmt = $pdo->prepare("SELECT `domain` FROM `domain` WHERE `domain`= :domain");
          $stmt->execute(array(':domain' => $domain));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if ($num_results == 0) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('domain_not_found', htmlspecialchars($domain))
            );
            return false;
          }
          $stmt = $pdo->prepare("INSERT INTO `mailbox` (`username`, `password`, `name`, `quota`, `local_part`, `domain`, `active`, `multiple_bookings`, `kind`)
            VALUES (:name, 'RESOURCE', :description, 0, :local_part, :domain, :active, :multiple_bookings, :kind)");
          $stmt->execute(array(
            ':name' => $name,
            ':description' => $description,
            ':local_part' => $local_part,
            ':domain' => $domain,
            ':active' => $active,
            ':kind' => $kind,
            ':multiple_bookings' => $multiple_bookings
          ));
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
            'msg' => array('resource_added', htmlspecialchars($name))
          );
        break;
      }
    break;
    case 'edit':
      switch ($_type) {
        case 'alias_domain':
          $alias_domains = (array)$_data['alias_domain'];
          foreach ($alias_domains as $alias_domain) {
            $alias_domain = idn_to_ascii(strtolower(trim($alias_domain)), 0, INTL_IDNA_VARIANT_UTS46);
            $is_now = mailbox('get', 'alias_domain_details', $alias_domain);
            if (!empty($is_now)) {
              $active         = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active_int'];
              $target_domain  = (!empty($_data['target_domain'])) ? idn_to_ascii(strtolower(trim($_data['target_domain'])), 0, INTL_IDNA_VARIANT_UTS46) : $is_now['target_domain'];
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('alias_domain_invalid', htmlspecialchars($alias_domain))
              );
              continue;
            }
            if (!is_valid_domain_name($target_domain)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('target_domain_invalid', htmlspecialchars($target_domain))
              );
              continue;
            }
            if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $target_domain)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (empty(mailbox('get', 'domain_details', $target_domain)) || !empty(mailbox('get', 'alias_domain_details', $target_domain))) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('target_domain_invalid', htmlspecialchars($target_domain))
              );
              continue;
            }
            $stmt = $pdo->prepare("UPDATE `alias_domain` SET
              `target_domain` = :target_domain,
              `active` = :active
                WHERE `alias_domain` = :alias_domain");
            $stmt->execute(array(
              ':alias_domain' => $alias_domain,
              ':target_domain' => $target_domain,
              ':active' => $active
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('aliasd_modified', htmlspecialchars($alias_domain))
            );
          }
        break;
        case 'tls_policy':
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          if (!isset($_SESSION['acl']['tls_policy']) || $_SESSION['acl']['tls_policy'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($usernames as $username) {
            if (!filter_var($username, FILTER_VALIDATE_EMAIL) || !hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $username)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $is_now = mailbox('get', 'tls_policy', $username);
            if (!empty($is_now)) {
              $tls_enforce_in = (isset($_data['tls_enforce_in'])) ? intval($_data['tls_enforce_in']) : $is_now['tls_enforce_in'];
              $tls_enforce_out = (isset($_data['tls_enforce_out'])) ? intval($_data['tls_enforce_out']) : $is_now['tls_enforce_out'];
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("UPDATE `mailbox`
              SET `attributes` = JSON_SET(`attributes`, '$.tls_enforce_out', :tls_out),
                  `attributes` = JSON_SET(`attributes`, '$.tls_enforce_in', :tls_in)
                    WHERE `username` = :username");
            $stmt->execute(array(
              ':tls_out' => intval($tls_enforce_out),
              ':tls_in' => intval($tls_enforce_in),
              ':username' => $username
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_modified', $username)
            );
          }
        break;
        case 'quarantine_notification':
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          if (!isset($_SESSION['acl']['quarantine_notification']) || $_SESSION['acl']['quarantine_notification'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($usernames as $username) {
            if (!filter_var($username, FILTER_VALIDATE_EMAIL) || !hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $username)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $is_now = mailbox('get', 'quarantine_notification', $username);
            if (!empty($is_now)) {
              $quarantine_notification = (isset($_data['quarantine_notification'])) ? $_data['quarantine_notification'] : $is_now['quarantine_notification'];
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (!in_array($quarantine_notification, array('never', 'hourly', 'daily', 'weekly'))) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("UPDATE `mailbox`
              SET `attributes` = JSON_SET(`attributes`, '$.quarantine_notification', :quarantine_notification)
                WHERE `username` = :username");
            $stmt->execute(array(
              ':quarantine_notification' => $quarantine_notification,
              ':username' => $username
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_modified', $username)
            );
          }
        break;
        case 'spam_score':
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          if (!isset($_SESSION['acl']['spam_score']) || $_SESSION['acl']['spam_score'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($usernames as $username) {
            if ($_data['spam_score'] == "default") {
              $stmt = $pdo->prepare("DELETE FROM `filterconf` WHERE `object` = :username
                AND (`option` = 'lowspamlevel' OR `option` = 'highspamlevel')");
              $stmt->execute(array(
                ':username' => $username
              ));
              $_SESSION['return'][] = array(
                'type' => 'success',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('mailbox_modified', $username)
              );
              continue;
            }
            $lowspamlevel	= explode(',', $_data['spam_score'])[0];
            $highspamlevel	= explode(',', $_data['spam_score'])[1];
            if (!is_numeric($lowspamlevel) || !is_numeric($highspamlevel)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'Invalid spam score, format must be "1,2" where first is low and second is high spam value.'
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `filterconf` WHERE `object` = :username
              AND (`option` = 'lowspamlevel' OR `option` = 'highspamlevel')");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("INSERT INTO `filterconf` (`object`, `option`, `value`)
              VALUES (:username, 'highspamlevel', :highspamlevel)");
            $stmt->execute(array(
              ':username' => $username,
              ':highspamlevel' => $highspamlevel
            ));
            $stmt = $pdo->prepare("INSERT INTO `filterconf` (`object`, `option`, `value`)
              VALUES (:username, 'lowspamlevel', :lowspamlevel)");
            $stmt->execute(array(
              ':username' => $username,
              ':lowspamlevel' => $lowspamlevel
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_modified', $username)
            );
          }
        break;
        case 'time_limited_alias':
          if (!isset($_SESSION['acl']['spam_alias']) || $_SESSION['acl']['spam_alias'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          if (!is_array($_data['address'])) {
            $addresses = array();
            $addresses[] = $_data['address'];
          }
          else {
            $addresses = $_data['address'];
          }
          foreach ($addresses as $address) {
            $stmt = $pdo->prepare("SELECT `goto` FROM `spamalias` WHERE `address` = :address");
            $stmt->execute(array(':address' => $address));
            $goto = $stmt->fetch(PDO::FETCH_ASSOC)['goto'];
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $goto)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (empty($_data['validity'])) {
              continue;
            }
            $validity = round((int)time() + ($_data['validity'] * 3600));
            $stmt = $pdo->prepare("UPDATE `spamalias` SET `validity` = :validity WHERE
              `address` = :address");
            $stmt->execute(array(
              ':address' => $address,
              ':validity' => $validity
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_modified', htmlspecialchars(implode(', ', $usernames)))
            );
          }
        break;
        case 'delimiter_action':
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          if (!isset($_SESSION['acl']['delimiter_action']) || $_SESSION['acl']['delimiter_action'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($usernames as $username) {
            if (!filter_var($username, FILTER_VALIDATE_EMAIL) || !hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $username)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (isset($_data['tagged_mail_handler']) && $_data['tagged_mail_handler'] == "subject") {
              try {
                $redis->hSet('RCPT_WANTS_SUBJECT_TAG', $username, 1);
                $redis->hDel('RCPT_WANTS_SUBFOLDER_TAG', $username);
              }
              catch (RedisException $e) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('redis_error', $e)
                );
                continue;
              }
            }
            else if (isset($_data['tagged_mail_handler']) && $_data['tagged_mail_handler'] == "subfolder") {
              try {
                $redis->hSet('RCPT_WANTS_SUBFOLDER_TAG', $username, 1);
                $redis->hDel('RCPT_WANTS_SUBJECT_TAG', $username);
              }
              catch (RedisException $e) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('redis_error', $e)
                );
                continue;
              }
            }
            else {
              try {
                $redis->hDel('RCPT_WANTS_SUBJECT_TAG', $username);
                $redis->hDel('RCPT_WANTS_SUBFOLDER_TAG', $username);
              }
              catch (RedisException $e) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('redis_error', $e)
                );
                continue;
              }
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_modified', $username)
            );
          }
        break;
        case 'syncjob':
          if (!is_array($_data['id'])) {
            $ids = array();
            $ids[] = $_data['id'];
          }
          else {
            $ids = $_data['id'];
          }
          if (!isset($_SESSION['acl']['syncjobs']) || $_SESSION['acl']['syncjobs'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($ids as $id) {
            $is_now = mailbox('get', 'syncjob_details', $id, array('with_password'));
            if (!empty($is_now)) {
              $username = $is_now['user2'];
              $user1 = (!empty($_data['user1'])) ? $_data['user1'] : $is_now['user1'];
              $active = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active_int'];
              $last_run = (isset($_data['last_run'])) ? NULL : $is_now['last_run'];
              $delete2duplicates = (isset($_data['delete2duplicates'])) ? intval($_data['delete2duplicates']) : $is_now['delete2duplicates'];
              $subscribeall = (isset($_data['subscribeall'])) ? intval($_data['subscribeall']) : $is_now['subscribeall'];
              $delete1 = (isset($_data['delete1'])) ? intval($_data['delete1']) : $is_now['delete1'];
              $delete2 = (isset($_data['delete2'])) ? intval($_data['delete2']) : $is_now['delete2'];
              $automap = (isset($_data['automap'])) ? intval($_data['automap']) : $is_now['automap'];
              $skipcrossduplicates = (isset($_data['skipcrossduplicates'])) ? intval($_data['skipcrossduplicates']) : $is_now['skipcrossduplicates'];
              $port1 = (!empty($_data['port1'])) ? $_data['port1'] : $is_now['port1'];
              $password1 = (!empty($_data['password1'])) ? $_data['password1'] : $is_now['password1'];
              $host1 = (!empty($_data['host1'])) ? $_data['host1'] : $is_now['host1'];
              $subfolder2 = (isset($_data['subfolder2'])) ? $_data['subfolder2'] : $is_now['subfolder2'];
              $enc1 = (!empty($_data['enc1'])) ? $_data['enc1'] : $is_now['enc1'];
              $mins_interval = (!empty($_data['mins_interval'])) ? $_data['mins_interval'] : $is_now['mins_interval'];
              $exclude = (isset($_data['exclude'])) ? $_data['exclude'] : $is_now['exclude'];
              $custom_params = (isset($_data['custom_params'])) ? $_data['custom_params'] : $is_now['custom_params'];
              $maxage = (isset($_data['maxage']) && $_data['maxage'] != "") ? intval($_data['maxage']) : $is_now['maxage'];
              $maxbytespersecond = (isset($_data['maxbytespersecond']) && $_data['maxbytespersecond'] != "") ? intval($_data['maxbytespersecond']) : $is_now['maxbytespersecond'];
              $timeout1 = (isset($_data['timeout1']) && $_data['timeout1'] != "") ? intval($_data['timeout1']) : $is_now['timeout1'];
              $timeout2 = (isset($_data['timeout2']) && $_data['timeout2'] != "") ? intval($_data['timeout2']) : $is_now['timeout2'];
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (empty($subfolder2)) {
              $subfolder2 = "";
            }
            if (!isset($maxage) || !filter_var($maxage, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32000)))) {
              $maxage = "0";
            }
            if (!isset($timeout1) || !filter_var($timeout1, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32000)))) {
              $timeout1 = "600";
            }
            if (!isset($timeout2) || !filter_var($timeout2, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 32000)))) {
              $timeout2 = "600";
            }
            if (!isset($maxbytespersecond) || !filter_var($maxbytespersecond, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 125000000)))) {
              $maxbytespersecond = "0";
            }
            if (!filter_var($port1, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 65535)))) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (!filter_var($mins_interval, FILTER_VALIDATE_INT, array('options' => array('min_range' => 1, 'max_range' => 3600)))) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (!is_valid_domain_name($host1)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if ($enc1 != "TLS" && $enc1 != "SSL" && $enc1 != "PLAIN") {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (@preg_match("/" . $exclude . "/", null) === false) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("UPDATE `imapsync` SET `delete1` = :delete1,
              `delete2` = :delete2,
              `automap` = :automap,
              `skipcrossduplicates` = :skipcrossduplicates,
              `maxage` = :maxage,
              `maxbytespersecond` = :maxbytespersecond,
              `subfolder2` = :subfolder2,
              `exclude` = :exclude,
              `host1` = :host1,
              `last_run` = :last_run,
              `user1` = :user1,
              `password1` = :password1,
              `mins_interval` = :mins_interval,
              `port1` = :port1,
              `enc1` = :enc1,
              `delete2duplicates` = :delete2duplicates,
              `custom_params` = :custom_params,
              `timeout1` = :timeout1,
              `timeout2` = :timeout2,
              `subscribeall` = :subscribeall,
              `active` = :active
                WHERE `id` = :id");
            $stmt->execute(array(
              ':delete1' => $delete1,
              ':delete2' => $delete2,
              ':automap' => $automap,
              ':skipcrossduplicates' => $skipcrossduplicates,
              ':id' => $id,
              ':exclude' => $exclude,
              ':maxage' => $maxage,
              ':maxbytespersecond' => $maxbytespersecond,
              ':subfolder2' => $subfolder2,
              ':host1' => $host1,
              ':user1' => $user1,
              ':password1' => $password1,
              ':last_run' => $last_run,
              ':mins_interval' => $mins_interval,
              ':port1' => $port1,
              ':enc1' => $enc1,
              ':delete2duplicates' => $delete2duplicates,
              ':custom_params' => $custom_params,
              ':timeout1' => $timeout1,
              ':timeout2' => $timeout2,
              ':subscribeall' => $subscribeall,
              ':active' => $active,
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_modified', $username)
            );
          }
        break;
        case 'filter':
          if (!isset($_SESSION['acl']['filters']) || $_SESSION['acl']['filters'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          $sieve = new Sieve\SieveParser();
          if (!is_array($_data['id'])) {
            $ids = array();
            $ids[] = $_data['id'];
          }
          else {
            $ids = $_data['id'];
          }
          foreach ($ids as $id) {
            $is_now = mailbox('get', 'filter_details', $id);
            if (!empty($is_now)) {
              $username = $is_now['username'];
              $active = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active_int'];
              $script_desc = (!empty($_data['script_desc'])) ? $_data['script_desc'] : $is_now['script_desc'];
              $script_data = (!empty($_data['script_data'])) ? $_data['script_data'] : $is_now['script_data'];
              $filter_type = (!empty($_data['filter_type'])) ? $_data['filter_type'] : $is_now['filter_type'];
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            try {
              $sieve->parse($script_data);
            }
            catch (Exception $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('sieve_error', $e->getMessage())
              );
              continue;
            }
            if ($filter_type != 'postfilter' && $filter_type != 'prefilter') {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'filter_type'
              );
              continue;
            }
            if ($active == '1') {
              $script_name = 'active';
              $stmt = $pdo->prepare("UPDATE `sieve_filters`
                SET `script_name` = 'inactive'
                  WHERE `username` = :username
                    AND `filter_type` = :filter_type");
              $stmt->execute(array(
                ':username' => $username,
                ':filter_type' => $filter_type
              ));
            }
            else {
              $script_name = 'inactive';
            }
            $stmt = $pdo->prepare("UPDATE `sieve_filters` SET `script_desc` = :script_desc, `script_data` = :script_data, `script_name` = :script_name, `filter_type` = :filter_type
              WHERE `id` = :id");
            $stmt->execute(array(
              ':script_desc' => $script_desc,
              ':script_data' => $script_data,
              ':script_name' => $script_name,
              ':filter_type' => $filter_type,
              ':id' => $id
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_modified', $username)
            );
          }
        break;
        case 'alias':
          if (!is_array($_data['id'])) {
            $ids = array();
            $ids[] = $_data['id'];
          }
          else {
            $ids = $_data['id'];
          }
          foreach ($ids as $id) {
            $is_now = mailbox('get', 'alias_details', $id);
            if (!empty($is_now)) {
              $active = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active_int'];
              $sogo_visible = (isset($_data['sogo_visible'])) ? intval($_data['sogo_visible']) : $is_now['sogo_visible_int'];
              $goto_null = (isset($_data['goto_null'])) ? intval($_data['goto_null']) : 0;
              $goto_spam = (isset($_data['goto_spam'])) ? intval($_data['goto_spam']) : 0;
              $goto_ham = (isset($_data['goto_ham'])) ? intval($_data['goto_ham']) : 0;
              $public_comment = (isset($_data['public_comment'])) ? $_data['public_comment'] : $is_now['public_comment'];
              $private_comment = (isset($_data['private_comment'])) ? $_data['private_comment'] : $is_now['private_comment'];
              $goto = (!empty($_data['goto'])) ? $_data['goto'] : $is_now['goto'];
              $address = (!empty($_data['address'])) ? $_data['address'] : $is_now['address'];
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('alias_invalid', $address)
              );
              continue;
            }
            $domain = idn_to_ascii(substr(strstr($address, '@'), 1), 0, INTL_IDNA_VARIANT_UTS46);
            if ($is_now['address'] != $address) {
              $local_part = strstr($address, '@', true);
              $address      = $local_part.'@'.$domain;
              if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'access_denied'
                );
                continue;
              }
              if ((!filter_var($address, FILTER_VALIDATE_EMAIL) === true) && !empty($local_part)) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('alias_invalid', $address)
                );
                continue;
              }
              if (strtolower($is_now['address']) != strtolower($address)) {
                $stmt = $pdo->prepare("SELECT `address` FROM `alias`
                  WHERE `address`= :address OR `address` IN (
                    SELECT `username` FROM `mailbox`, `alias_domain`
                      WHERE (
                        `alias_domain`.`alias_domain` = :address_d
                          AND `mailbox`.`username` = CONCAT(:address_l, '@', alias_domain.target_domain)))");
                $stmt->execute(array(
                  ':address' => $address,
                  ':address_l' => $local_part,
                  ':address_d' => $domain
                ));
                $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
                if ($num_results != 0) {
                  $_SESSION['return'][] = array(
                    'type' => 'danger',
                    'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                    'msg' => array('is_alias_or_mailbox', htmlspecialchars($address))
                  );
                  continue;
                }
              }
              $stmt = $pdo->prepare("SELECT `domain` FROM `domain`
                WHERE `domain`= :domain1 OR `domain` = (SELECT `target_domain` FROM `alias_domain` WHERE `alias_domain` = :domain2)");
              $stmt->execute(array(':domain1' => $domain, ':domain2' => $domain));
              $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
              if ($num_results == 0) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('domain_not_found', htmlspecialchars($domain))
                );
                continue;
              }
              $stmt = $pdo->prepare("SELECT `address` FROM `spamalias`
                WHERE `address`= :address");
              $stmt->execute(array(':address' => $address));
              $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
              if ($num_results != 0) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('is_spam_alias', htmlspecialchars($address))
                );
                continue;
              }
            }
            if ($goto_null == "1") {
              $goto = "null@localhost";
            }
            elseif ($goto_spam == "1") {
              $goto = "spam@localhost";
            }
            elseif ($goto_ham == "1") {
              $goto = "ham@localhost";
            }
            else {
              $gotos = array_map('trim', preg_split( "/( |,|;|\n)/", $goto));
              foreach ($gotos as $i => &$goto) {
                if (empty($goto)) {
                  continue;
                }
                if (!filter_var($goto, FILTER_VALIDATE_EMAIL)) {
                  $_SESSION['return'][] = array(
                    'type' => 'danger',
                    'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                    'msg' => array('goto_invalid', $goto)
                  );
                  unset($gotos[$i]);
                  continue;
                }
                if ($goto == $address) {
                  $_SESSION['return'][] = array(
                    'type' => 'danger',
                    'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                    'msg' => 'alias_goto_identical'
                  );
                  unset($gotos[$i]);
                  continue;
                }
                // Delete from sender_acl to prevent duplicates
                $stmt = $pdo->prepare("DELETE FROM `sender_acl` WHERE
                  `logged_in_as` = :goto AND
                  `send_as` = :address");
                $stmt->execute(array(
                  ':goto' => $goto,
                  ':address' => $address
                ));
              }
              $gotos = array_filter($gotos);
              $goto = implode(",", $gotos);
            }
            if (!empty($goto)) {
              $stmt = $pdo->prepare("UPDATE `alias` SET
                `address` = :address,
                `public_comment` = :public_comment,
                `private_comment` = :private_comment,
                `domain` = :domain,
                `goto` = :goto,
                `sogo_visible`= :sogo_visible,
                `active`= :active
                  WHERE `id` = :id");
              $stmt->execute(array(
                ':address' => $address,
                ':public_comment' => $public_comment,
                ':private_comment' => $private_comment,
                ':domain' => $domain,
                ':goto' => $goto,
                ':sogo_visible' => $sogo_visible,
                ':active' => $active,
                ':id' => $id
              ));
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('alias_modified', htmlspecialchars($address))
            );
          }
        break;
        case 'domain':
          if (!is_array($_data['domain'])) {
            $domains = array();
            $domains[] = $_data['domain'];
          }
          else {
            $domains = $_data['domain'];
          }
          foreach ($domains as $domain) {
            $domain = idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46);
            if (!is_valid_domain_name($domain)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'domain_invalid'
              );
              continue;
            }
            if ($_SESSION['mailcow_cc_role'] == "domainadmin" &&
            hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
              $is_now = mailbox('get', 'domain_details', $domain);
              if (!empty($is_now)) {
                $gal                  = (isset($_data['gal'])) ? intval($_data['gal']) : $is_now['gal_int'];
                $description          = (!empty($_data['description'])) ? $_data['description'] : $is_now['description'];
              }
              else {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'domain_invalid'
                );
                continue;
              }
              $stmt = $pdo->prepare("UPDATE `domain` SET
              `description` = :description,
              `gal` = :gal
                WHERE `domain` = :domain");
              $stmt->execute(array(
                ':description' => $description,
                ':gal' => $gal,
                ':domain' => $domain
              ));
              $_SESSION['return'][] = array(
                'type' => 'success',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('domain_modified', htmlspecialchars($domain))
              );
            }
            elseif ($_SESSION['mailcow_cc_role'] == "admin") {
              $is_now = mailbox('get', 'domain_details', $domain);
              if (!empty($is_now)) {
                $active               = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active_int'];
                $backupmx             = (isset($_data['backupmx'])) ? intval($_data['backupmx']) : $is_now['backupmx_int'];
                $gal                  = (isset($_data['gal'])) ? intval($_data['gal']) : $is_now['gal_int'];
                $relay_all_recipients = (isset($_data['relay_all_recipients'])) ? intval($_data['relay_all_recipients']) : $is_now['relay_all_recipients_int'];
                $relay_unknown_only   = (isset($_data['relay_unknown_only'])) ? intval($_data['relay_unknown_only']) : $is_now['relay_unknown_only_int'];
                $relayhost            = (isset($_data['relayhost'])) ? intval($_data['relayhost']) : $is_now['relayhost'];
                $aliases              = (!empty($_data['aliases'])) ? $_data['aliases'] : $is_now['max_num_aliases_for_domain'];
                $mailboxes            = (isset($_data['mailboxes']) && $_data['mailboxes'] != '') ? intval($_data['mailboxes']) : $is_now['max_num_mboxes_for_domain'];
                $defquota             = (isset($_data['defquota']) && $_data['defquota'] != '') ? intval($_data['defquota']) : ($is_now['def_quota_for_mbox'] / 1048576);
                $maxquota             = (!empty($_data['maxquota'])) ? $_data['maxquota'] : ($is_now['max_quota_for_mbox'] / 1048576);
                $quota                = (!empty($_data['quota'])) ? $_data['quota'] : ($is_now['max_quota_for_domain'] / 1048576);
                $description          = (!empty($_data['description'])) ? $_data['description'] : $is_now['description'];
                if ($relay_all_recipients == '1') {
                  $backupmx = '1';
                }
                if ($relay_unknown_only == '1') {
                  $backupmx = '1';
                  $relay_all_recipients = '1';
                }
              }
              else {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'domain_invalid'
                );
                continue;
              }
              // todo: should be using api here
              $stmt = $pdo->prepare("SELECT
                  COUNT(*) AS count,
                  MAX(COALESCE(ROUND(`quota`/1048576), 0)) AS `biggest_mailbox`,
                  COALESCE(ROUND(SUM(`quota`)/1048576), 0) AS `quota_all`
                    FROM `mailbox`
                      WHERE `kind` NOT REGEXP 'location|thing|group'
                        AND domain = :domain");
              $stmt->execute(array(':domain' => $domain));
              $MailboxData = $stmt->fetch(PDO::FETCH_ASSOC);
              // todo: should be using api here
              $stmt = $pdo->prepare("SELECT COUNT(*) AS `count` FROM `alias`
                  WHERE domain = :domain
                  AND address NOT IN (
                    SELECT `username` FROM `mailbox`
                  )");
              $stmt->execute(array(':domain' => $domain));
              $AliasData = $stmt->fetch(PDO::FETCH_ASSOC);
              if ($defquota > $maxquota) {
                $_SESSION['return'][] = array(
                    'type' => 'danger',
                    'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                    'msg' => 'mailbox_defquota_exceeds_mailbox_maxquota'
                );
                continue;
              }
              if ($defquota == "0" || empty($defquota)) {
                $_SESSION['return'][] = array(
                    'type' => 'danger',
                    'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                    'msg' => 'defquota_empty'
                );
                continue;
              }
              if ($maxquota > $quota) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'mailbox_quota_exceeds_domain_quota'
                );
                continue;
              }
              if ($maxquota == "0" || empty($maxquota)) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'maxquota_empty'
                );
                continue;
              }
              if ($MailboxData['biggest_mailbox'] > $maxquota) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('max_quota_in_use', $MailboxData['biggest_mailbox'])
                );
                continue;
              }
              if ($MailboxData['quota_all'] > $quota) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('domain_quota_m_in_use', $MailboxData['quota_all'])
                );
                continue;
              }
              if ($MailboxData['count'] > $mailboxes) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('mailboxes_in_use', $MailboxData['count'])
                );
                continue;
              }
              if ($AliasData['count'] > $aliases) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => array('aliases_in_use', $AliasData['count'])
                );
                continue;
              }
              $stmt = $pdo->prepare("UPDATE `domain` SET
              `relay_all_recipients` = :relay_all_recipients,
              `relay_unknown_only` = :relay_unknown_only,
              `backupmx` = :backupmx,
              `gal` = :gal,
              `active` = :active,
              `quota` = :quota,
              `defquota` = :defquota,
              `maxquota` = :maxquota,
              `relayhost` = :relayhost,
              `mailboxes` = :mailboxes,
              `aliases` = :aliases,
              `description` = :description
                WHERE `domain` = :domain");
              $stmt->execute(array(
                ':relay_all_recipients' => $relay_all_recipients,
                ':relay_unknown_only' => $relay_unknown_only,
                ':backupmx' => $backupmx,
                ':gal' => $gal,
                ':active' => $active,
                ':quota' => $quota,
                ':defquota' => $defquota,
                ':maxquota' => $maxquota,
                ':relayhost' => $relayhost,
                ':mailboxes' => $mailboxes,
                ':aliases' => $aliases,
                ':description' => $description,
                ':domain' => $domain
              ));
              $_SESSION['return'][] = array(
                'type' => 'success',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('domain_modified', htmlspecialchars($domain))
              );
            }
          }
        break;
        case 'mailbox':
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          foreach ($usernames as $username) {
            if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('username_invalid', $username)
              );
              continue;
            }
            $is_now = mailbox('get', 'mailbox_details', $username);
            if (!empty($is_now)) {
              $active     = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active_int'];
              (int)$force_pw_update = (isset($_data['force_pw_update'])) ? intval($_data['force_pw_update']) : intval($is_now['attributes']['force_pw_update']);
              (int)$sogo_access = (isset($_data['sogo_access']) && isset($_SESSION['acl']['sogo_access']) && $_SESSION['acl']['sogo_access'] == "1") ? intval($_data['sogo_access']) : intval($is_now['attributes']['sogo_access']);
              (int)$quota_m = (isset_has_content($_data['quota'])) ? intval($_data['quota']) : ($is_now['quota'] / 1048576);
              $name       = (!empty($_data['name'])) ? ltrim(rtrim($_data['name'], '>'), '<') : $is_now['name'];
              $domain     = $is_now['domain'];
              $quota_b    = $quota_m * 1048576;
              $password   = (!empty($_data['password'])) ? $_data['password'] : null;
              $password2  = (!empty($_data['password2'])) ? $_data['password2'] : null;
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            // if already 0 == ok
            if ((!isset($_SESSION['acl']['unlimited_quota']) || $_SESSION['acl']['unlimited_quota'] != "1") && ($quota_m == 0 && $is_now['quota'] != 0)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'unlimited_quota_acl'
              );
              return false;
            }
            $stmt = $pdo->prepare("SELECT `quota`, `maxquota`
              FROM `domain`
                WHERE `domain` = :domain");
            $stmt->execute(array(':domain' => $domain));
            $DomainData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if ($quota_m > $DomainData['maxquota']) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('mailbox_quota_exceeded', $DomainData['maxquota'])
              );
              continue;
            }
            if (((($is_now['quota_used'] / 1048576) - $quota_m) + $quota_m) > $DomainData['quota']) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('mailbox_quota_left_exceeded', ($is_now['max_new_quota'] / 1048576))
              );
              continue;
            }
            $extra_acls = array();
            if (isset($_data['extended_sender_acl'])) {
              if (!isset($_SESSION['acl']['extend_sender_acl']) || $_SESSION['acl']['extend_sender_acl'] != "1" ) {
                $_SESSION['return'][] = array(
                  'type' => 'danger',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'access_denied'
                );
                return false;
              }
              $extra_acls = array_map('trim', preg_split( "/( |,|;|\n)/", $_data['extended_sender_acl']));
              foreach ($extra_acls as $i => &$extra_acl) {
                if (empty($extra_acl)) {
                  continue;
                }
                if (substr($extra_acl, 0, 1) === "@") {
                  $extra_acl = ltrim($extra_acl, '@');
                }
                if (!filter_var($extra_acl, FILTER_VALIDATE_EMAIL) && !is_valid_domain_name($extra_acl)) {
                  $_SESSION['return'][] = array(
                    'type' => 'danger',
                    'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                    'msg' => array('extra_acl_invalid', htmlspecialchars($extra_acl))
                  );
                  unset($extra_acls[$i]);
                  continue;
                }
                $domains = array_merge(mailbox('get', 'domains'), mailbox('get', 'alias_domains'));
                if (filter_var($extra_acl, FILTER_VALIDATE_EMAIL)) {
                  $extra_acl_domain = idn_to_ascii(substr(strstr($extra_acl, '@'), 1), 0, INTL_IDNA_VARIANT_UTS46);
                  if (in_array($extra_acl_domain, $domains)) {
                    $_SESSION['return'][] = array(
                      'type' => 'danger',
                      'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                      'msg' => array('extra_acl_invalid_domain', $extra_acl_domain)
                    );
                    unset($extra_acls[$i]);
                    continue;
                  }
                }
                else {
                  if (in_array($extra_acl, $domains)) {
                    $_SESSION['return'][] = array(
                      'type' => 'danger',
                      'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                      'msg' => array('extra_acl_invalid_domain', $extra_acl_domain)
                    );
                    unset($extra_acls[$i]);
                    continue;
                  }
                  $extra_acl = '@' . $extra_acl;
                }
              }
              $extra_acls = array_filter($extra_acls);
              $extra_acls = array_values($extra_acls);
              $extra_acls = array_unique($extra_acls);
              $stmt = $pdo->prepare("DELETE FROM `sender_acl` WHERE `external` = 1 AND `logged_in_as` = :username");
              $stmt->execute(array(
                ':username' => $username
              ));
              foreach ($extra_acls as $sender_acl_external) {
                $stmt = $pdo->prepare("INSERT INTO `sender_acl` (`send_as`, `logged_in_as`, `external`)
                  VALUES (:sender_acl, :username, 1)");
                $stmt->execute(array(
                  ':sender_acl' => $sender_acl_external,
                  ':username' => $username
                ));
              }
            }
            if (isset($_data['sender_acl'])) {
              // Get sender_acl items set by admin
              $sender_acl_admin = array_merge(
                mailbox('get', 'sender_acl_handles', $username)['sender_acl_domains']['ro'],
                mailbox('get', 'sender_acl_handles', $username)['sender_acl_addresses']['ro']
              );
              // Get sender_acl items from POST array
              // Set sender_acl_domain_admin to empty array if sender_acl contains "default" to trigger a reset
              // Delete records from sender_acl if sender_acl contains "*" and set to array("*")
              $_data['sender_acl'] = (array)$_data['sender_acl'];
              if (in_array("*", $_data['sender_acl'])) {
                $sender_acl_domain_admin = array('*');
              }
              elseif (array("default") === $_data['sender_acl']) {
                $sender_acl_domain_admin = array();
              }
              else {
                if (array_search('default', $_data['sender_acl']) !== false){
                  unset($_data['sender_acl'][array_search('default', $_data['sender_acl'])]);
                }
                $sender_acl_domain_admin = $_data['sender_acl'];
              }
              if (!empty($sender_acl_domain_admin) || !empty($sender_acl_admin)) {
                // Check items in POST array and skip invalid
                foreach ($sender_acl_domain_admin as $key => $val) {
                  // Check for invalid domain or email format or not *
                  if (!filter_var($val, FILTER_VALIDATE_EMAIL) && !is_valid_domain_name(ltrim($val, '@')) && $val != '*') {
                    $_SESSION['return'][] = array(
                      'type' => 'danger',
                      'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                      'msg' => array('sender_acl_invalid', $sender_acl_domain_admin[$key])
                    );
                    unset($sender_acl_domain_admin[$key]);
                    continue;
                  }
                  // Check if user has domain access (if object is domain)
                  $domain = ltrim($sender_acl_domain_admin[$key], '@');
                  if (is_valid_domain_name($domain)) {
                    // Check for- and skip non-mailcow domains
                    $domains = array_merge(mailbox('get', 'domains'), mailbox('get', 'alias_domains'));
                    if (!empty($domains)) {
                      if (!in_array($domain, $domains)) {
                        $_SESSION['return'][] = array(
                          'type' => 'danger',
                          'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                          'msg' => array('sender_acl_invalid', $sender_acl_domain_admin[$key])
                        );
                        unset($sender_acl_domain_admin[$key]);
                        continue;
                      }
                    }
                    if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain)) {
                      $_SESSION['return'][] = array(
                        'type' => 'danger',
                        'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                        'msg' => array('sender_acl_invalid', $sender_acl_domain_admin[$key])
                      );
                      unset($sender_acl_domain_admin[$key]);
                      continue;
                    }
                  }
                  // Wildcard can only be used if role == admin
                  if ($val == '*' && $_SESSION['mailcow_cc_role'] != 'admin') {
                    $_SESSION['return'][] = array(
                      'type' => 'danger',
                      'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                      'msg' => array('sender_acl_invalid', $sender_acl_domain_admin[$key])
                    );
                    unset($sender_acl_domain_admin[$key]);
                    continue;
                  }
                  // Check if user has alias access (if object is email)
                  if (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                    if (!hasAliasObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $val)) {
                      $_SESSION['return'][] = array(
                        'type' => 'danger',
                        'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                        'msg' => array('sender_acl_invalid', $sender_acl_domain_admin[$key])
                      );
                      unset($sender_acl_domain_admin[$key]);
                      continue;
                    }
                  }
                }
                // Merge both arrays
                $sender_acl_merged = array_merge($sender_acl_domain_admin, $sender_acl_admin);
                // If merged array still contains "*", set it as only value
                !in_array('*', $sender_acl_merged) ?: $sender_acl_merged = array('*');
                $stmt = $pdo->prepare("DELETE FROM `sender_acl` WHERE `external` = 0 AND `logged_in_as` = :username");
                $stmt->execute(array(
                  ':username' => $username
                ));
                $fixed_sender_aliases = mailbox('get', 'sender_acl_handles', $username)['fixed_sender_aliases'];
                foreach ($sender_acl_merged as $sender_acl) {
                  $domain = ltrim($sender_acl, '@');
                  if (is_valid_domain_name($domain)) {
                    $sender_acl = '@' . $domain;
                  }
                  // Don't add if allowed by alias
                  if (in_array($sender_acl, $fixed_sender_aliases)) {
                    continue;
                  }
                  $stmt = $pdo->prepare("INSERT INTO `sender_acl` (`send_as`, `logged_in_as`)
                    VALUES (:sender_acl, :username)");
                  $stmt->execute(array(
                    ':sender_acl' => $sender_acl,
                    ':username' => $username
                  ));
                }
              }
              else {
                $stmt = $pdo->prepare("DELETE FROM `sender_acl` WHERE `external` = 0 AND `logged_in_as` = :username");
                $stmt->execute(array(
                  ':username' => $username
                ));
              }
            }
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
              $stmt = $pdo->prepare("UPDATE `mailbox` SET
                  `password` = :password_hashed
                    WHERE `username` = :username");
              $stmt->execute(array(
                ':password_hashed' => $password_hashed,
                ':username' => $username
              ));
            }
            $stmt = $pdo->prepare("UPDATE `alias` SET
                `active` = :active
                  WHERE `address` = :address");
            $stmt->execute(array(
              ':address' => $username,
              ':active' => $active
            ));
            $stmt = $pdo->prepare("UPDATE `mailbox` SET
                `active` = :active,
                `name`= :name,
                `quota` = :quota_b,
                `attributes` = JSON_SET(`attributes`, '$.force_pw_update', :force_pw_update),
                `attributes` = JSON_SET(`attributes`, '$.sogo_access', :sogo_access)
                  WHERE `username` = :username");
            $stmt->execute(array(
              ':active' => $active,
              ':name' => $name,
              ':quota_b' => $quota_b,
              ':force_pw_update' => $force_pw_update,
              ':sogo_access' => $sogo_access,
              ':username' => $username
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_modified', $username)
            );
          }
        break;
        case 'resource':
          if (!is_array($_data['name'])) {
            $names = array();
            $names[] = $_data['name'];
          }
          else {
            $names = $_data['name'];
          }
          foreach ($names as $name) {
            $is_now = mailbox('get', 'resource_details', $name);
            if (!empty($is_now)) {
              $active             = (isset($_data['active'])) ? intval($_data['active']) : $is_now['active_int'];
              $multiple_bookings  = (isset($_data['multiple_bookings'])) ? intval($_data['multiple_bookings']) : $is_now['multiple_bookings'];
              $description        = (!empty($_data['description'])) ? $_data['description'] : $is_now['description'];
              $kind               = (!empty($_data['kind'])) ? $_data['kind'] : $is_now['kind'];
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('resource_invalid', htmlspecialchars($name))
              );
              continue;
            }
            if (!filter_var($name, FILTER_VALIDATE_EMAIL)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('resource_invalid', htmlspecialchars($name))
              );
              continue;
            }
            if (!isset($multiple_bookings) || $multiple_bookings < -1) {
              $multiple_bookings = -1;
            }
            if (empty($description)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('description_invalid', htmlspecialchars($name))
              );
              continue;
            }
            if ($kind != 'location' && $kind != 'group' && $kind != 'thing') {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('resource_invalid', htmlspecialchars($name))
              );
              continue;
            }
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $name)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("UPDATE `mailbox` SET
                `active` = :active,
                `name`= :description,
                `kind`= :kind,
                `multiple_bookings`= :multiple_bookings
                  WHERE `username` = :name");
            $stmt->execute(array(
              ':active' => $active,
              ':description' => $description,
              ':multiple_bookings' => $multiple_bookings,
              ':kind' => $kind,
              ':name' => $name
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('resource_modified', htmlspecialchars($name))
            );
          }
        break;
      }
    break;
    case 'get':
      switch ($_type) {
        case 'sender_acl_handles':
          if ($_SESSION['mailcow_cc_role'] != "admin" && $_SESSION['mailcow_cc_role'] != "domainadmin") {
            return false;
          }
          $data['sender_acl_domains']['ro']               = array();
          $data['sender_acl_domains']['rw']               = array();
          $data['sender_acl_domains']['selectable']       = array();
          $data['sender_acl_addresses']['ro']             = array();
          $data['sender_acl_addresses']['rw']             = array();
          $data['sender_acl_addresses']['selectable']     = array();
          $data['fixed_sender_aliases']                   = array();
          $data['external_sender_aliases']                = array();
          // Fixed addresses
          $stmt = $pdo->prepare("SELECT `address` FROM `alias` WHERE `goto` REGEXP :goto AND `address` NOT LIKE '@%'");
          $stmt->execute(array(':goto' => '(^|,)'.$_data.'($|,)'));
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while ($row = array_shift($rows)) {
            $data['fixed_sender_aliases'][] = $row['address'];
          }
          $stmt = $pdo->prepare("SELECT CONCAT(`local_part`, '@', `alias_domain`.`alias_domain`) AS `alias_domain_alias` FROM `mailbox`, `alias_domain`
            WHERE `alias_domain`.`target_domain` = `mailbox`.`domain`
            AND `mailbox`.`username` = :username");
          $stmt->execute(array(':username' => $_data));
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while ($row = array_shift($rows)) {
            if (!empty($row['alias_domain_alias'])) {
              $data['fixed_sender_aliases'][] = $row['alias_domain_alias'];
            }
          }
          // External addresses
          $stmt = $pdo->prepare("SELECT `send_as` as `send_as_external` FROM `sender_acl` WHERE `logged_in_as` = :logged_in_as AND `external` = '1'");
          $stmt->execute(array(':logged_in_as' => $_data));
          $exernal_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while ($row = array_shift($exernal_rows)) {
            if (!empty($row['send_as_external'])) {
              $data['external_sender_aliases'][] = $row['send_as_external'];
            }
          }
          // Return array $data['sender_acl_domains/addresses']['ro'] with read-only objects
          // Return array $data['sender_acl_domains/addresses']['rw'] with read-write objects (can be deleted)
          $stmt = $pdo->prepare("SELECT REPLACE(`send_as`, '@', '') AS `send_as` FROM `sender_acl` WHERE `logged_in_as` = :logged_in_as AND `external` = '0' AND (`send_as` LIKE '@%' OR `send_as` = '*')");
          $stmt->execute(array(':logged_in_as' => $_data));
          $domain_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while ($domain_row = array_shift($domain_rows)) {
            if (is_valid_domain_name($domain_row['send_as']) && !hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain_row['send_as'])) {
              $data['sender_acl_domains']['ro'][] = $domain_row['send_as'];
              continue;
            }
            if (is_valid_domain_name($domain_row['send_as']) && hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $domain_row['send_as'])) {
              $data['sender_acl_domains']['rw'][] = $domain_row['send_as'];
              continue;
            }
            if ($domain_row['send_as'] == '*' && $_SESSION['mailcow_cc_role'] != 'admin') {
              $data['sender_acl_domains']['ro'][] = $domain_row['send_as'];
            }
            if ($domain_row['send_as'] == '*' && $_SESSION['mailcow_cc_role'] == 'admin') {
              $data['sender_acl_domains']['rw'][] = $domain_row['send_as'];
            }
          }
          $stmt = $pdo->prepare("SELECT `send_as` FROM `sender_acl` WHERE `logged_in_as` = :logged_in_as AND `external` = '0' AND (`send_as` NOT LIKE '@%' AND `send_as` != '*')");
          $stmt->execute(array(':logged_in_as' => $_data));
          $address_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while ($address_row = array_shift($address_rows)) {
            if (filter_var($address_row['send_as'], FILTER_VALIDATE_EMAIL) && !hasAliasObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $address_row['send_as'])) {
              $data['sender_acl_addresses']['ro'][] = $address_row['send_as'];
              continue;
            }
            if (filter_var($address_row['send_as'], FILTER_VALIDATE_EMAIL) && hasAliasObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $address_row['send_as'])) {
              $data['sender_acl_addresses']['rw'][] = $address_row['send_as'];
              continue;
            }
          }
          $stmt = $pdo->prepare("SELECT `domain` FROM `domain`
            WHERE `domain` NOT IN (
              SELECT REPLACE(`send_as`, '@', '') FROM `sender_acl`
                WHERE `logged_in_as` = :logged_in_as1
                  AND `external` = '0'
                  AND `send_as` LIKE '@%')
            UNION
            SELECT '*' FROM `domain`
              WHERE '*' NOT IN (
                SELECT `send_as` FROM `sender_acl`
                  WHERE `logged_in_as` = :logged_in_as2
                    AND `external` = '0'
              )");
          $stmt->execute(array(
            ':logged_in_as1' => $_data,
            ':logged_in_as2' => $_data
          ));
          $rows_domain = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while ($row_domain = array_shift($rows_domain)) {
            if (is_valid_domain_name($row_domain['domain']) && hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $row_domain['domain'])) {
              $data['sender_acl_domains']['selectable'][] = $row_domain['domain'];
              continue;
            }
            if ($row_domain['domain'] == '*' && $_SESSION['mailcow_cc_role'] == 'admin') {
              $data['sender_acl_domains']['selectable'][] = $row_domain['domain'];
              continue;
            }
          }
          $stmt = $pdo->prepare("SELECT `address` FROM `alias`
            WHERE `goto` != :goto
              AND `address` NOT IN (
                SELECT `send_as` FROM `sender_acl`
                  WHERE `logged_in_as` = :logged_in_as
                    AND `external` = '0'
                    AND `send_as` NOT LIKE '@%')");
          $stmt->execute(array(
            ':logged_in_as' => $_data,
            ':goto' => $_data
          ));
          $rows_mbox = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while ($row = array_shift($rows_mbox)) {
            // Aliases are not selectable
            if (in_array($row['address'], $data['fixed_sender_aliases'])) {
              continue;
            }
            if (filter_var($row['address'], FILTER_VALIDATE_EMAIL) && hasAliasObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $row['address'])) {
              $data['sender_acl_addresses']['selectable'][] = $row['address'];
            }
          }
          return $data;
        break;
        case 'mailboxes':
          $mailboxes = array();
          if (isset($_data) && !hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            return false;
          }
          elseif (isset($_data) && hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            $stmt = $pdo->prepare("SELECT `username` FROM `mailbox` WHERE `kind` NOT REGEXP 'location|thing|group' AND `domain` = :domain");
            $stmt->execute(array(
              ':domain' => $_data,
            ));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while($row = array_shift($rows)) {
              $mailboxes[] = $row['username'];
            }
          }
          else {
            $stmt = $pdo->prepare("SELECT `username` FROM `mailbox` WHERE `kind` NOT REGEXP 'location|thing|group' AND (`domain` IN (SELECT `domain` FROM `domain_admins` WHERE `active` = '1' AND `username` = :username) OR 'admin' = :role)");
            $stmt->execute(array(
              ':username' => $_SESSION['mailcow_cc_username'],
              ':role' => $_SESSION['mailcow_cc_role'],
            ));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while($row = array_shift($rows)) {
              $mailboxes[] = $row['username'];
            }
          }
          return $mailboxes;
        break;
        case 'tls_policy':
          $attrs = array();
          if (isset($_data) && filter_var($_data, FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
          }
          else {
            $_data = $_SESSION['mailcow_cc_username'];
          }
          $stmt = $pdo->prepare("SELECT `attributes` FROM `mailbox` WHERE `username` = :username");
          $stmt->execute(array(':username' => $_data));
          $attrs = $stmt->fetch(PDO::FETCH_ASSOC);
          $attrs = json_decode($attrs['attributes'], true);
          return array(
            'tls_enforce_in' => $attrs['tls_enforce_in'],
            'tls_enforce_out' => $attrs['tls_enforce_out']
          );
        break;
        case 'quarantine_notification':
          $attrs = array();
          if (isset($_data) && filter_var($_data, FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
          }
          else {
            $_data = $_SESSION['mailcow_cc_username'];
          }
          $stmt = $pdo->prepare("SELECT `attributes` FROM `mailbox` WHERE `username` = :username");
          $stmt->execute(array(':username' => $_data));
          $attrs = $stmt->fetch(PDO::FETCH_ASSOC);
          $attrs = json_decode($attrs['attributes'], true);
          return $attrs['quarantine_notification'];
        break;
        case 'filters':
          $filters = array();
          if (isset($_data) && filter_var($_data, FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
          }
          else {
            $_data = $_SESSION['mailcow_cc_username'];
          }
          $stmt = $pdo->prepare("SELECT `id` FROM `sieve_filters` WHERE `username` = :username");
          $stmt->execute(array(':username' => $_data));
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while($row = array_shift($rows)) {
            $filters[] = $row['id'];
          }
          return $filters;
        break;
        case 'global_filter_details':
          $global_filters = array();
          if ($_SESSION['mailcow_cc_role'] != "admin") {
            return false;
          }
          $global_filters['prefilter'] = file_get_contents('/global_sieve/before');
          $global_filters['postfilter'] = file_get_contents('/global_sieve/after');
          return $global_filters;
        break;
        case 'filter_details':
          $filter_details = array();
          if (!is_numeric($_data)) {
            return false;
          }
          $stmt = $pdo->prepare("SELECT CASE `script_name` WHEN 'active' THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`,
            CASE `script_name` WHEN 'active' THEN 1 ELSE 0 END AS `active_int`,
            id,
            username,
            filter_type,
            script_data,
            script_desc
            FROM `sieve_filters`
              WHERE `id` = :id");
          $stmt->execute(array(':id' => $_data));
          $filter_details = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $filter_details['username'])) {
            return false;
          }
          return $filter_details;
        break;
        case 'active_user_sieve':
          $filter_details = array();
          if (isset($_data) && filter_var($_data, FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
          }
          else {
            $_data = $_SESSION['mailcow_cc_username'];
          }
          $exec_fields = array(
            'cmd' => 'sieve',
            'task' => 'list',
            'username' => $_data
          );
          $filters = docker('post', 'dovecot-mailcow', 'exec', $exec_fields);
          $filters = array_filter(preg_split("/(\r\n|\n|\r)/",$filters));
          foreach ($filters as $filter) {
            if (preg_match('/.+ ACTIVE/i', $filter)) {
              $exec_fields = array(
                'cmd' => 'sieve',
                'task' => 'print',
                'script_name' => substr($filter, 0, -7),
                'username' => $_data
              );
              $script = docker('post', 'dovecot-mailcow', 'exec', $exec_fields);
              // Remove first line
              return preg_replace('/^.+\n/', '', $script);
            }
          }
          return false;
        break;
        case 'syncjob_details':
          $syncjobdetails = array();
          if (!is_numeric($_data)) {
            return false;
          }
          if (isset($_extra) && in_array('no_log', $_extra)) {
            $field_query = $pdo->query('SHOW FIELDS FROM `imapsync` WHERE FIELD NOT IN ("returned_text", "password1")');
            $fields = $field_query->fetchAll(PDO::FETCH_ASSOC);
            while($field = array_shift($fields)) {
              $shown_fields[] = $field['Field'];
            }
            $stmt = $pdo->prepare("SELECT " . implode(',', $shown_fields) . ",
              `active` AS `active_int`,
              CASE `active` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`
                FROM `imapsync` WHERE id = :id");
          }
          elseif (isset($_extra) && in_array('with_password', $_extra)) {
            $stmt = $pdo->prepare("SELECT *,
              `active` AS `active_int`,
              CASE `active` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`
                FROM `imapsync` WHERE id = :id");
          }
          else {
            $field_query = $pdo->query('SHOW FIELDS FROM `imapsync` WHERE FIELD NOT IN ("password1")');
            $fields = $field_query->fetchAll(PDO::FETCH_ASSOC);
            while($field = array_shift($fields)) {
              $shown_fields[] = $field['Field'];
            }
            $stmt = $pdo->prepare("SELECT " . implode(',', $shown_fields) . ",
              `active` AS `active_int`,
              CASE `active` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`
                FROM `imapsync` WHERE id = :id");
          }
          $stmt->execute(array(':id' => $_data));
          $syncjobdetails = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!empty($syncjobdetails['returned_text'])) {
            $syncjobdetails['log'] = $syncjobdetails['returned_text'];
          }
          else {
            $syncjobdetails['log'] = '';
          }
          unset($syncjobdetails['returned_text']);
          if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $syncjobdetails['user2'])) {
            return false;
          }
          return $syncjobdetails;
        break;
        case 'syncjobs':
          $syncjobdata = array();
          if (isset($_data) && filter_var($_data, FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
          }
          else {
            $_data = $_SESSION['mailcow_cc_username'];
          }
          $stmt = $pdo->prepare("SELECT `id` FROM `imapsync` WHERE `user2` = :username");
          $stmt->execute(array(':username' => $_data));
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while($row = array_shift($rows)) {
            $syncjobdata[] = $row['id'];
          }
          return $syncjobdata;
        break;
        case 'spam_score':
          $curl = curl_init();
          curl_setopt($curl, CURLOPT_UNIX_SOCKET_PATH, '/var/lib/rspamd/rspamd.sock');
          curl_setopt($curl, CURLOPT_URL,"http://rspamd/actions");
          curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
          $default_actions = curl_exec($curl);
          if (!curl_errno($curl)) {
            $data_array = json_decode($default_actions, true);
            curl_close($curl);
            foreach ($data_array as $data) {
              if ($data['action'] == 'reject') {
                $reject = $data['value'];
                continue;
              }
              elseif ($data['action'] == 'add header') {
                $add_header = $data['value'];
                continue;
              }
            }
            if (empty($add_header) || empty($reject)) {
              // Assume default, set warning
              $default = "5, 15";
              $_SESSION['return'][] = array(
                'type' => 'warning',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'Could not determine servers default spam score, assuming default'
              );
            }
            else {
              $default = $add_header . ', ' . $reject;
            }
          }
          else {
            // Assume default, set warning
            $default = "5, 15";
            $_SESSION['return'][] = array(
              'type' => 'warning',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'Could not determine servers default spam score, assuming default'
            );
          }
          curl_close($curl);
          $policydata = array();
          if (isset($_data) && filter_var($_data, FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
          }
          else {
            $_data = $_SESSION['mailcow_cc_username'];
          }
          $stmt = $pdo->prepare("SELECT `value` FROM `filterconf` WHERE `object` = :username AND
            (`option` = 'lowspamlevel' OR `option` = 'highspamlevel')");
          $stmt->execute(array(':username' => $_data));
          $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
          if (empty($num_results)) {
            return $default;
          }
          else {
            $stmt = $pdo->prepare("SELECT `value` FROM `filterconf` WHERE `option` = 'highspamlevel' AND `object` = :username");
            $stmt->execute(array(':username' => $_data));
            $highspamlevel = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt = $pdo->prepare("SELECT `value` FROM `filterconf` WHERE `option` = 'lowspamlevel' AND `object` = :username");
            $stmt->execute(array(':username' => $_data));
            $lowspamlevel = $stmt->fetch(PDO::FETCH_ASSOC);
            return $lowspamlevel['value'].', '.$highspamlevel['value'];
          }
        break;
        case 'time_limited_aliases':
          $tladata = array();
          if (isset($_data) && filter_var($_data, FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
          }
          else {
            $_data = $_SESSION['mailcow_cc_username'];
          }
          $stmt = $pdo->prepare("SELECT `address`,
            `goto`,
            `validity`
              FROM `spamalias`
                WHERE `goto` = :username
                  AND `validity` >= :unixnow");
          $stmt->execute(array(':username' => $_data, ':unixnow' => time()));
          $tladata = $stmt->fetchAll(PDO::FETCH_ASSOC);
          return $tladata;
        break;
        case 'delimiter_action':
          $policydata = array();
          if (isset($_data) && filter_var($_data, FILTER_VALIDATE_EMAIL)) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
              return false;
            }
          }
          else {
            $_data = $_SESSION['mailcow_cc_username'];
          }
          try {
            if ($redis->hGet('RCPT_WANTS_SUBJECT_TAG', $_data)) {
              return "subject";
            }
            elseif ($redis->hGet('RCPT_WANTS_SUBFOLDER_TAG', $_data)) {
              return "subfolder";
            }
            else {
              return "none";
            }
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('redis_error', $e)
            );
            return false;
          }
        break;
        case 'resources':
          $resources = array();
          if (isset($_data) && !hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            return false;
          }
          elseif (isset($_data) && hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            $stmt = $pdo->prepare("SELECT `username` FROM `mailbox` WHERE `kind` REGEXP 'location|thing|group' AND `domain` = :domain");
            $stmt->execute(array(
              ':domain' => $_data,
            ));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while($row = array_shift($rows)) {
              $resources[] = $row['username'];
            }
          }
          else {
            $stmt = $pdo->prepare("SELECT `username` FROM `mailbox` WHERE `kind` REGEXP 'location|thing|group' AND `domain` IN (SELECT `domain` FROM `domain_admins` WHERE `active` = '1' AND `username` = :username) OR 'admin' = :role");
            $stmt->execute(array(
              ':username' => $_SESSION['mailcow_cc_username'],
              ':role' => $_SESSION['mailcow_cc_role'],
            ));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while($row = array_shift($rows)) {
              $resources[] = $row['username'];
            }
          }
          return $resources;
        break;
        case 'alias_domains':
          $aliasdomains = array();
          if (isset($_data) && !hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            return false;
          }
          elseif (isset($_data) && hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            $stmt = $pdo->prepare("SELECT `alias_domain` FROM `alias_domain` WHERE `target_domain` = :domain");
            $stmt->execute(array(
              ':domain' => $_data,
            ));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while($row = array_shift($rows)) {
              $aliasdomains[] = $row['alias_domain'];
            }
          }
          else {
            $stmt = $pdo->prepare("SELECT `alias_domain` FROM `alias_domain` WHERE `target_domain` IN (SELECT `domain` FROM `domain_admins` WHERE `active` = '1' AND `username` = :username) OR 'admin' = :role");
            $stmt->execute(array(
              ':username' => $_SESSION['mailcow_cc_username'],
              ':role' => $_SESSION['mailcow_cc_role'],
            ));
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            while($row = array_shift($rows)) {
              $aliasdomains[] = $row['alias_domain'];
            }
          }
          return $aliasdomains;
        break;
        case 'aliases':
          $aliases = array();
          if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            return false;
          }
          $stmt = $pdo->prepare("SELECT `id` FROM `alias` WHERE `address` != `goto` AND `domain` = :domain");
          $stmt->execute(array(
            ':domain' => $_data,
          ));
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while($row = array_shift($rows)) {
            $aliases[] = $row['id'];
          }
          return $aliases;
        break;
        case 'alias_details':
          $aliasdata = array();
          $stmt = $pdo->prepare("SELECT
            `id`,
            `domain`,
            `goto`,
            `address`,
            `public_comment`,
            `private_comment`,
            `active` as `active_int`,
            `sogo_visible` as `sogo_visible_int`,
            CASE `active` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`,
            CASE `sogo_visible` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `sogo_visible`,
            `created`,
            `modified`
              FROM `alias`
                  WHERE `id` = :id AND `address` != `goto`");
          $stmt->execute(array(
            ':id' => intval($_data),
          ));
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          $stmt = $pdo->prepare("SELECT `target_domain` FROM `alias_domain` WHERE `alias_domain` = :domain");
          $stmt->execute(array(
            ':domain' => $row['domain'],
          ));
          $row_alias_domain = $stmt->fetch(PDO::FETCH_ASSOC);
          if (isset($row_alias_domain['target_domain']) && !empty($row_alias_domain['target_domain'])) {
            $aliasdata['in_primary_domain'] = $row_alias_domain['target_domain'];
          }
          else {
            $aliasdata['in_primary_domain'] = "";
          }
          $aliasdata['id'] = $row['id'];
          $aliasdata['domain'] = $row['domain'];
          $aliasdata['public_comment'] = $row['public_comment'];
          $aliasdata['private_comment'] = $row['private_comment'];
          $aliasdata['domain'] = $row['domain'];
          $aliasdata['goto'] = $row['goto'];
          $aliasdata['address'] = $row['address'];
          (!filter_var($aliasdata['address'], FILTER_VALIDATE_EMAIL)) ? $aliasdata['is_catch_all'] = 1 : $aliasdata['is_catch_all'] = 0;
          $aliasdata['active'] = $row['active'];
          $aliasdata['sogo_visible'] = $row['sogo_visible'];
          $aliasdata['active_int'] = $row['active_int'];
          $aliasdata['sogo_visible_int'] = $row['sogo_visible_int'];
          $aliasdata['created'] = $row['created'];
          $aliasdata['modified'] = $row['modified'];
          if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $aliasdata['domain'])) {
            return false;
          }
          return $aliasdata;
        break;
        case 'alias_domain_details':
          $aliasdomaindata = array();
          $rl = ratelimit('get', 'domain', $_data);
          $stmt = $pdo->prepare("SELECT
            `alias_domain`,
            `target_domain`,
            `active` AS `active_int`,
            CASE `active` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`,
            `created`,
            `modified`
              FROM `alias_domain`
                  WHERE `alias_domain` = :aliasdomain");
          $stmt->execute(array(
            ':aliasdomain' => $_data,
          ));
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          $stmt = $pdo->prepare("SELECT `backupmx` FROM `domain` WHERE `domain` = :target_domain");
          $stmt->execute(array(
            ':target_domain' => $row['target_domain']
          ));
          $row_parent = $stmt->fetch(PDO::FETCH_ASSOC);
          $aliasdomaindata['alias_domain'] = $row['alias_domain'];
          $aliasdomaindata['parent_is_backupmx'] = $row_parent['backupmx'];
          $aliasdomaindata['target_domain'] = $row['target_domain'];
          $aliasdomaindata['active'] = $row['active'];
          $aliasdomaindata['rl'] = $rl;
          $aliasdomaindata['active_int'] = $row['active_int'];
          $aliasdomaindata['created'] = $row['created'];
          $aliasdomaindata['modified'] = $row['modified'];
          if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $aliasdomaindata['target_domain'])) {
            return false;
          }
          return $aliasdomaindata;
        break;
        case 'domains':
          $domains = array();
          if ($_SESSION['mailcow_cc_role'] != "admin" && $_SESSION['mailcow_cc_role'] != "domainadmin") {
            return false;
          }
          $stmt = $pdo->prepare("SELECT `domain` FROM `domain`
            WHERE (`domain` IN (
              SELECT `domain` from `domain_admins`
                WHERE (`active`='1' AND `username` = :username))
              )
              OR 'admin'= :role");
          $stmt->execute(array(
            ':username' => $_SESSION['mailcow_cc_username'],
            ':role' => $_SESSION['mailcow_cc_role'],
          ));
          $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
          while($row = array_shift($rows)) {
            $domains[] = $row['domain'];
          }
          return $domains;
        break;
        case 'domain_details':
          $domaindata = array();
          $_data = idn_to_ascii(strtolower(trim($_data)), 0, INTL_IDNA_VARIANT_UTS46);
          if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            return false;
          }
          $stmt = $pdo->prepare("SELECT `target_domain` FROM `alias_domain` WHERE `alias_domain` =  :domain");
          $stmt->execute(array(
            ':domain' => $_data
          ));
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!empty($row)) {
            $_data = $row['target_domain'];
          }
          $stmt = $pdo->prepare("SELECT
              `domain`,
              `description`,
              `aliases`,
              `mailboxes`,
              `defquota`,
              `maxquota`,
              `quota`,
              `relayhost`,
              `relay_all_recipients` as `relay_all_recipients_int`,
              `relay_unknown_only` as `relay_unknown_only_int`,
              `backupmx` as `backupmx_int`,
              `gal` as `gal_int`,
              `active` as `active_int`,
              CASE `relay_all_recipients` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `relay_all_recipients`,
              CASE `relay_unknown_only` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `relay_unknown_only`,
              CASE `backupmx` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `backupmx`,
              CASE `gal` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `gal`,
              CASE `active` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`
                FROM `domain` WHERE `domain`= :domain");
          $stmt->execute(array(
            ':domain' => $_data
          ));
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          if (empty($row)) {
            return false;
          }
          $stmt = $pdo->prepare("SELECT COUNT(*) AS `count`,
            COALESCE(SUM(`quota`), 0) AS `in_use`
              FROM `mailbox`
                WHERE `kind` NOT REGEXP 'location|thing|group'
                  AND `domain` = :domain");
          $stmt->execute(array(':domain' => $row['domain']));
          $MailboxDataDomain = $stmt->fetch(PDO::FETCH_ASSOC);
          $stmt = $pdo->prepare("SELECT SUM(bytes) AS `bytes_total`, SUM(messages) AS `msgs_total` FROM `quota2`
            WHERE `username` IN (
              SELECT `username` FROM `mailbox`
                WHERE `domain` = :domain
            );");
          $stmt->execute(array(':domain' => $row['domain']));
          $SumQuotaInUse = $stmt->fetch(PDO::FETCH_ASSOC);
          $rl = ratelimit('get', 'domain', $_data);
          $domaindata['max_new_mailbox_quota']	= ($row['quota'] * 1048576) - $MailboxDataDomain['in_use'];
          if ($domaindata['max_new_mailbox_quota'] > ($row['maxquota'] * 1048576)) {
            $domaindata['max_new_mailbox_quota'] = ($row['maxquota'] * 1048576);
          }
          $domaindata['def_new_mailbox_quota'] = $domaindata['max_new_mailbox_quota'];
          if ($domaindata['def_new_mailbox_quota'] > ($row['defquota'] * 1048576)) {
            $domaindata['def_new_mailbox_quota'] = ($row['defquota'] * 1048576);
          }
          $domaindata['quota_used_in_domain'] = $MailboxDataDomain['in_use'];
          if (!empty($SumQuotaInUse['bytes_total'])) {
            $domaindata['bytes_total'] = $SumQuotaInUse['bytes_total'];
          }
          else {
            $domaindata['bytes_total'] = 0;
          }
          if (!empty($SumQuotaInUse['msgs_total'])) {
            $domaindata['msgs_total'] = $SumQuotaInUse['msgs_total'];
          }
          else {
            $domaindata['msgs_total'] = 0;
          }
          $domaindata['mboxes_in_domain'] = $MailboxDataDomain['count'];
          $domaindata['mboxes_left'] = $row['mailboxes']	- $MailboxDataDomain['count'];
          $domaindata['domain_name'] = $row['domain'];
          $domaindata['description'] = $row['description'];
          $domaindata['max_num_aliases_for_domain'] = $row['aliases'];
          $domaindata['max_num_mboxes_for_domain'] = $row['mailboxes'];
          $domaindata['def_quota_for_mbox'] = $row['defquota'] * 1048576;
          $domaindata['max_quota_for_mbox'] = $row['maxquota'] * 1048576;
          $domaindata['max_quota_for_domain'] = $row['quota'] * 1048576;
          $domaindata['relayhost'] = $row['relayhost'];
          $domaindata['backupmx'] = $row['backupmx'];
          $domaindata['gal'] = $row['gal'];
          $domaindata['backupmx_int'] = $row['backupmx_int'];
          $domaindata['gal_int'] = $row['gal_int'];
          $domaindata['rl'] = $rl;
          $domaindata['active'] = $row['active'];
          $domaindata['active_int'] = $row['active_int'];
          $domaindata['relay_all_recipients'] = $row['relay_all_recipients'];
          $domaindata['relay_unknown_only'] = $row['relay_unknown_only'];
          $domaindata['relay_all_recipients_int'] = $row['relay_all_recipients_int'];
          $domaindata['relay_unknown_only_int'] = $row['relay_unknown_only_int'];
          $stmt = $pdo->prepare("SELECT COUNT(*) AS `alias_count` FROM `alias`
            WHERE (`domain`= :domain OR `domain` IN (SELECT `alias_domain` FROM `alias_domain` WHERE `target_domain` = :domain2))
              AND `address` NOT IN (
                SELECT `username` FROM `mailbox`
              )");
          $stmt->execute(array(
            ':domain' => $_data,
            ':domain2' => $_data
          ));
          $AliasDataDomain = $stmt->fetch(PDO::FETCH_ASSOC);
          (isset($AliasDataDomain['alias_count'])) ? $domaindata['aliases_in_domain'] = $AliasDataDomain['alias_count'] : $domaindata['aliases_in_domain'] = "0";
          $domaindata['aliases_left'] = $row['aliases']	- $AliasDataDomain['alias_count'];
          if ($_SESSION['mailcow_cc_role'] == "admin")
          {
              $stmt = $pdo->prepare("SELECT GROUP_CONCAT(`username` SEPARATOR ', ') AS domain_admins FROM `domain_admins` WHERE `domain` = :domain");
              $stmt->execute(array(
                ':domain' => $_data
              ));
              $domain_admins = $stmt->fetch(PDO::FETCH_ASSOC);
              (isset($domain_admins['domain_admins'])) ? $domaindata['domain_admins'] = $domain_admins['domain_admins'] : $domaindata['domain_admins'] = "-";
          }
          return $domaindata;
        break;
        case 'mailbox_details':
          if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            return false;
          }
          $mailboxdata = array();
          $rl = ratelimit('get', 'mailbox', $_data);
          $last_imap_login = $redis->Get('last-login/imap/' . $_data);
          $last_pop3_login = $redis->Get('last-login/pop3/' . $_data);
          if ($last_imap_login === false || $GLOBALS['SHOW_LAST_LOGIN'] === false) {
            $last_imap_login = '0';
          }
          if ($last_pop3_login === false || $GLOBALS['SHOW_LAST_LOGIN'] === false) {
            $last_pop3_login = '0';
          }
          if (preg_match('/y|yes/i', getenv('MASTER'))) {
            $stmt = $pdo->prepare("SELECT
              `domain`.`backupmx`,
              `mailbox`.`username`,
              `mailbox`.`name`,
              `mailbox`.`active` AS `active_int`,
              CASE `mailbox`.`active` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`,
              `mailbox`.`domain`,
              `mailbox`.`local_part`,
              `mailbox`.`quota`,
              `quota2`.`bytes`,
              `attributes`,
              `quota2`.`messages`
                FROM `mailbox`, `quota2`, `domain`
                  WHERE `mailbox`.`kind` NOT REGEXP 'location|thing|group' AND `mailbox`.`username` = `quota2`.`username` AND `domain`.`domain` = `mailbox`.`domain` AND `mailbox`.`username` = :mailbox");
          }
          else {
            $stmt = $pdo->prepare("SELECT
              `domain`.`backupmx`,
              `mailbox`.`username`,
              `mailbox`.`name`,
              `mailbox`.`active` AS `active_int`,
              CASE `mailbox`.`active` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`,
              `mailbox`.`domain`,
              `mailbox`.`local_part`,
              `mailbox`.`quota`,
              `quota2replica`.`bytes`,
              `attributes`,
              `quota2replica`.`messages`
                FROM `mailbox`, `quota2replica`, `domain`
                  WHERE `mailbox`.`kind` NOT REGEXP 'location|thing|group' AND `mailbox`.`username` = `quota2replica`.`username` AND `domain`.`domain` = `mailbox`.`domain` AND `mailbox`.`username` = :mailbox");
          }
          $stmt->execute(array(
            ':mailbox' => $_data,
          ));
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          $stmt = $pdo->prepare("SELECT `maxquota`, `quota` FROM  `domain` WHERE `domain` = :domain");
          $stmt->execute(array(':domain' => $row['domain']));
          $DomainQuota  = $stmt->fetch(PDO::FETCH_ASSOC);
          $stmt = $pdo->prepare("SELECT IFNULL(COUNT(`active`), 0) AS `pushover_active` FROM `pushover` WHERE `username` = :username AND `active` = 1");
          $stmt->execute(array(':username' => $_data));
          $PushoverActive  = $stmt->fetch(PDO::FETCH_ASSOC);
          $stmt = $pdo->prepare("SELECT COALESCE(SUM(`quota`), 0) as `in_use` FROM `mailbox` WHERE `kind` NOT REGEXP 'location|thing|group' AND `domain` = :domain AND `username` != :username");
          $stmt->execute(array(':domain' => $row['domain'], ':username' => $_data));
          $MailboxUsage	= $stmt->fetch(PDO::FETCH_ASSOC);
          $stmt = $pdo->prepare("SELECT IFNULL(COUNT(`address`), 0) AS `sa_count` FROM `spamalias` WHERE `goto` = :address AND `validity` >= :unixnow");
          $stmt->execute(array(':address' => $_data, ':unixnow' => time()));
          $SpamaliasUsage	= $stmt->fetch(PDO::FETCH_ASSOC);
          $mailboxdata['max_new_quota'] = ($DomainQuota['quota'] * 1048576) - $MailboxUsage['in_use'];
          if ($mailboxdata['max_new_quota'] > ($DomainQuota['maxquota'] * 1048576)) {
            $mailboxdata['max_new_quota'] = ($DomainQuota['maxquota'] * 1048576);
          }
          $mailboxdata['username'] = $row['username'];
          if (!empty($rl)) {
            $mailboxdata['rl'] = $rl;
            $mailboxdata['rl_scope'] = 'mailbox';
          }
          else {
            $mailboxdata['rl'] = ratelimit('get', 'domain', $row['domain']);
            $mailboxdata['rl_scope'] = 'domain';
          }
          $mailboxdata['is_relayed'] = $row['backupmx'];
          $mailboxdata['name'] = $row['name'];
          $mailboxdata['last_imap_login'] = $last_imap_login;
          $mailboxdata['last_pop3_login'] = $last_pop3_login;
          $mailboxdata['active'] = $row['active'];
          $mailboxdata['active_int'] = $row['active_int'];
          $mailboxdata['domain'] = $row['domain'];
          $mailboxdata['local_part'] = $row['local_part'];
          $mailboxdata['quota'] = $row['quota'];
          $mailboxdata['attributes'] = json_decode($row['attributes'], true);
          $mailboxdata['quota_used'] = intval($row['bytes']);
          $mailboxdata['percent_in_use'] = ($row['quota'] == 0) ? '- ' : round((intval($row['bytes']) / intval($row['quota'])) * 100);
          $mailboxdata['messages'] = $row['messages'];
          $mailboxdata['spam_aliases'] = $SpamaliasUsage['sa_count'];
          $mailboxdata['pushover_active'] = ($PushoverActive['pushover_active'] == 1) ? $lang['mailbox']['yes'] : $lang['mailbox']['no'];
          if ($mailboxdata['percent_in_use'] === '- ') {
            $mailboxdata['percent_class'] = "info";
          }
          elseif ($mailboxdata['percent_in_use'] >= 90) {
            $mailboxdata['percent_class'] = "danger";
          }
          elseif ($mailboxdata['percent_in_use'] >= 75) {
            $mailboxdata['percent_class'] = "warning";
          }
          else {
            $mailboxdata['percent_class'] = "success";
          }
          return $mailboxdata;
        break;
        case 'resource_details':
          $resourcedata = array();
          if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data)) {
            return false;
          }
          $stmt = $pdo->prepare("SELECT
              `username`,
              `name`,
              `kind`,
              `multiple_bookings`,
              `local_part`,
              `active` AS `active_int`,
              CASE `active` WHEN 1 THEN '".$lang['mailbox']['yes']."' ELSE '".$lang['mailbox']['no']."' END AS `active`,
              `domain`
                FROM `mailbox` WHERE `kind` REGEXP 'location|thing|group' AND `username` = :resource");
          $stmt->execute(array(
            ':resource' => $_data,
          ));
          $row = $stmt->fetch(PDO::FETCH_ASSOC);
          $resourcedata['name'] = $row['username'];
          $resourcedata['kind'] = $row['kind'];
          $resourcedata['multiple_bookings'] = $row['multiple_bookings'];
          $resourcedata['description'] = $row['name'];
          $resourcedata['active'] = $row['active'];
          $resourcedata['active_int'] = $row['active_int'];
          $resourcedata['domain'] = $row['domain'];
          $resourcedata['local_part'] = $row['local_part'];
          if (!isset($resourcedata['domain']) ||
            (isset($resourcedata['domain']) && !hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $resourcedata['domain']))) {
            return false;
          }
          return $resourcedata;
        break;
      }
    break;
    case 'delete':
      switch ($_type) {
        case 'syncjob':
          if (!is_array($_data['id'])) {
            $ids = array();
            $ids[] = $_data['id'];
          }
          else {
            $ids = $_data['id'];
          }
          if (!isset($_SESSION['acl']['syncjobs']) || $_SESSION['acl']['syncjobs'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($ids as $id) {
            if (!is_numeric($id)) {
              return false;
            }
            $stmt = $pdo->prepare("SELECT `user2` FROM `imapsync` WHERE id = :id");
            $stmt->execute(array(':id' => $id));
            $user2 = $stmt->fetch(PDO::FETCH_ASSOC)['user2'];
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $user2)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `imapsync` WHERE `id`= :id");
            $stmt->execute(array(':id' => $id));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('deleted_syncjob', $id)
            );
          }
        break;
        case 'filter':
          if (!is_array($_data['id'])) {
            $ids = array();
            $ids[] = $_data['id'];
          }
          else {
            $ids = $_data['id'];
          }
          if (!isset($_SESSION['acl']['filters']) || $_SESSION['acl']['filters'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($ids as $id) {
            if (!is_numeric($id)) {
              continue;
            }
            $stmt = $pdo->prepare("SELECT `username` FROM `sieve_filters` WHERE id = :id");
            $stmt->execute(array(':id' => $id));
            $usr = $stmt->fetch(PDO::FETCH_ASSOC)['username'];
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $usr)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `sieve_filters` WHERE `id`= :id");
            $stmt->execute(array(':id' => $id));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('delete_filter', $id)
            );
          }
        break;
        case 'time_limited_alias':
          if (!is_array($_data['address'])) {
            $addresses = array();
            $addresses[] = $_data['address'];
          }
          else {
            $addresses = $_data['address'];
          }
          if (!isset($_SESSION['acl']['spam_alias']) || $_SESSION['acl']['spam_alias'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($addresses as $address) {
            $stmt = $pdo->prepare("SELECT `goto` FROM `spamalias` WHERE `address` = :address");
            $stmt->execute(array(':address' => $address));
            $goto = $stmt->fetch(PDO::FETCH_ASSOC)['goto'];
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $goto)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `spamalias` WHERE `goto` = :username AND `address` = :item");
            $stmt->execute(array(
              ':username' => $goto,
              ':item' => $address
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_modified', htmlspecialchars($goto))
            );
          }
        break;
        case 'eas_cache':
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          if (!isset($_SESSION['acl']['eas_reset']) || $_SESSION['acl']['eas_reset'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($usernames as $username) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $username)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `sogo_cache_folder` WHERE `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('eas_reset', htmlspecialchars($username))
            );
          }
        break;
        case 'sogo_profile':
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          if (!isset($_SESSION['acl']['sogo_profile_reset']) || $_SESSION['acl']['sogo_profile_reset'] != "1" ) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($usernames as $username) {
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $username)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `sogo_user_profile` WHERE `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_cache_folder` WHERE `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_acl` WHERE `c_object` LIKE '%/" . $username . "/%' OR `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_store` WHERE `c_folder_id` IN (SELECT `c_folder_id` FROM `sogo_folder_info` WHERE `c_path2` = :username)");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_quick_contact` WHERE `c_folder_id` IN (SELECT `c_folder_id` FROM `sogo_folder_info` WHERE `c_path2` = :username)");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_quick_appointment` WHERE `c_folder_id` IN (SELECT `c_folder_id` FROM `sogo_folder_info` WHERE `c_path2` = :username)");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_folder_info` WHERE `c_path2` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('sogo_profile_reset', htmlspecialchars($username))
            );
          }
        break;
        case 'domain':
          if (!is_array($_data['domain'])) {
            $domains = array();
            $domains[] = $_data['domain'];
          }
          else {
            $domains = $_data['domain'];
          }
          if ($_SESSION['mailcow_cc_role'] != "admin") {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => 'access_denied'
            );
            return false;
          }
          foreach ($domains as $domain) {
            if (!is_valid_domain_name($domain)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'domain_invalid'
              );
              continue;
            }
            $domain	= idn_to_ascii(strtolower(trim($domain)), 0, INTL_IDNA_VARIANT_UTS46);
            $stmt = $pdo->prepare("SELECT `username` FROM `mailbox`
              WHERE `domain` = :domain");
            $stmt->execute(array(':domain' => $domain));
            $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
            if ($num_results != 0 || !empty($num_results)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('domain_not_empty', $domain)
              );
              continue;
            }
            $exec_fields = array('cmd' => 'maildir', 'task' => 'cleanup', 'maildir' => $domain);
            $maildir_gc = json_decode(docker('post', 'dovecot-mailcow', 'exec', $exec_fields), true);
            if ($maildir_gc['type'] != 'success') {
              $_SESSION['return'][] = array(
                'type' => 'warning',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'Could not move mail storage to garbage collector: ' . $maildir_gc['msg']
              );
            }
            $stmt = $pdo->prepare("DELETE FROM `domain` WHERE `domain` = :domain");
            $stmt->execute(array(
              ':domain' => $domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `domain_admins` WHERE `domain` = :domain");
            $stmt->execute(array(
              ':domain' => $domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `alias` WHERE `domain` = :domain");
            $stmt->execute(array(
              ':domain' => $domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `alias_domain` WHERE `target_domain` = :domain");
            $stmt->execute(array(
              ':domain' => $domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `mailbox` WHERE `domain` = :domain");
            $stmt->execute(array(
              ':domain' => $domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `sender_acl` WHERE `logged_in_as` LIKE :domain");
            $stmt->execute(array(
              ':domain' => '%@'.$domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `quota2` WHERE `username` LIKE :domain");
            $stmt->execute(array(
              ':domain' => '%@'.$domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `pushover` WHERE `username` LIKE :domain");
            $stmt->execute(array(
              ':domain' => '%@'.$domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `quota2replica` WHERE `username` LIKE :domain");
            $stmt->execute(array(
              ':domain' => '%@'.$domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `spamalias` WHERE `address` LIKE :domain");
            $stmt->execute(array(
              ':domain' => '%@'.$domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `filterconf` WHERE `object` = :domain");
            $stmt->execute(array(
              ':domain' => $domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `bcc_maps` WHERE `local_dest` = :domain");
            $stmt->execute(array(
              ':domain' => $domain,
            ));
            $stmt = $pdo->query("DELETE FROM `admin` WHERE `superadmin` = 0 AND `username` NOT IN (SELECT `username`FROM `domain_admins`);");
            $stmt = $pdo->query("DELETE FROM `da_acl` WHERE `username` NOT IN (SELECT `username`FROM `domain_admins`);");
            try {
              $redis->hDel('DOMAIN_MAP', $domain);
              $redis->hDel('RL_VALUE', $domain);
            }
            catch (RedisException $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('redis_error', $e)
              );
              continue;
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('domain_removed', htmlspecialchars($domain))
            );
          }
        break;
        case 'alias':
          if (!is_array($_data['id'])) {
            $ids = array();
            $ids[] = $_data['id'];
          }
          else {
            $ids = $_data['id'];
          }
          foreach ($ids as $id) {
            $alias_data = mailbox('get', 'alias_details', $id);
            if (empty($alias_data)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `alias` WHERE `id` = :id");
            $stmt->execute(array(
              ':id' => $id
            ));
            $stmt = $pdo->prepare("DELETE FROM `sender_acl` WHERE `send_as` = :alias_address");
            $stmt->execute(array(
              ':alias_address' => $alias_data['address']
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('alias_removed', htmlspecialchars($alias_data['address']))
            );
          }
        break;
        case 'alias_domain':
          if (!is_array($_data['alias_domain'])) {
            $alias_domains = array();
            $alias_domains[] = $_data['alias_domain'];
          }
          else {
            $alias_domains = $_data['alias_domain'];
          }
          foreach ($alias_domains as $alias_domain) {
            if (!is_valid_domain_name($alias_domain)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'domain_invalid'
              );
              continue;
            }
            $stmt = $pdo->prepare("SELECT `target_domain` FROM `alias_domain`
              WHERE `alias_domain`= :alias_domain");
            $stmt->execute(array(':alias_domain' => $alias_domain));
            $DomainData = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $DomainData['target_domain'])) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `alias_domain` WHERE `alias_domain` = :alias_domain");
            $stmt->execute(array(
              ':alias_domain' => $alias_domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `alias` WHERE `domain` = :alias_domain");
            $stmt->execute(array(
              ':alias_domain' => $alias_domain,
            ));
            $stmt = $pdo->prepare("DELETE FROM `bcc_maps` WHERE `local_dest` = :alias_domain");
            $stmt->execute(array(
              ':alias_domain' => $alias_domain,
            ));
            try {
              $redis->hDel('DOMAIN_MAP', $alias_domain);
              $redis->hDel('RL_VALUE', $domain);
            }
            catch (RedisException $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('redis_error', $e)
              );
              continue;
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('alias_domain_removed', htmlspecialchars($alias_domain))
            );
          }
        break;
        case 'mailbox':
          if (!is_array($_data['username'])) {
            $usernames = array();
            $usernames[] = $_data['username'];
          }
          else {
            $usernames = $_data['username'];
          }
          foreach ($usernames as $username) {
            if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $username)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $mailbox_details = mailbox('get', 'mailbox_details', $username);
            if (!empty($mailbox_details['domain']) && !empty($mailbox_details['local_part'])) {
              $maildir = $mailbox_details['domain'] . '/' . $mailbox_details['local_part'];
              $exec_fields = array('cmd' => 'maildir', 'task' => 'cleanup', 'maildir' => $maildir);
              $maildir_gc = json_decode(docker('post', 'dovecot-mailcow', 'exec', $exec_fields), true);
              if ($maildir_gc['type'] != 'success') {
                $_SESSION['return'][] = array(
                  'type' => 'warning',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'Could not move maildir to garbage collector: ' . $maildir_gc['msg']
                );
              }
            }
            else {
              $_SESSION['return'][] = array(
                'type' => 'warning',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'Could not move maildir to garbage collector: variables local_part and/or domain empty'
              );
            }
            if (strtolower(getenv('SKIP_SOLR')) == 'n') {
              $curl = curl_init();
              curl_setopt($curl, CURLOPT_URL, 'http://solr:8983/solr/dovecot-fts/update?commit=true');
              curl_setopt($curl, CURLOPT_HTTPHEADER,array('Content-Type: text/xml'));
              curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($curl, CURLOPT_POST, 1);
              curl_setopt($curl, CURLOPT_POSTFIELDS, '<delete><query>user:' . $username . '</query></delete>');
              curl_setopt($curl, CURLOPT_TIMEOUT, 30);
              $response = curl_exec($curl);
              if ($response === false) {
                $err = curl_error($curl);
                $_SESSION['return'][] = array(
                  'type' => 'warning',
                  'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                  'msg' => 'Could not remove Solr index: ' . print_r($err, true)
                );
              }
              curl_close($curl);
            }
            $stmt = $pdo->prepare("DELETE FROM `alias` WHERE `goto` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `pushover` WHERE `username` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `quarantine` WHERE `rcpt` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `quota2` WHERE `username` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `quota2replica` WHERE `username` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `mailbox` WHERE `username` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sender_acl` WHERE `logged_in_as` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            // fk, better safe than sorry
            $stmt = $pdo->prepare("DELETE FROM `user_acl` WHERE `username` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `spamalias` WHERE `goto` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `imapsync` WHERE `user2` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `filterconf` WHERE `object` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_user_profile` WHERE `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_cache_folder` WHERE `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_acl` WHERE `c_object` LIKE '%/" . str_replace('%', '\%', $username) . "/%' OR `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_store` WHERE `c_folder_id` IN (SELECT `c_folder_id` FROM `sogo_folder_info` WHERE `c_path2` = :username)");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_quick_contact` WHERE `c_folder_id` IN (SELECT `c_folder_id` FROM `sogo_folder_info` WHERE `c_path2` = :username)");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_quick_appointment` WHERE `c_folder_id` IN (SELECT `c_folder_id` FROM `sogo_folder_info` WHERE `c_path2` = :username)");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_folder_info` WHERE `c_path2` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `bcc_maps` WHERE `local_dest` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `oauth_access_tokens` WHERE `user_id` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `oauth_refresh_tokens` WHERE `user_id` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("DELETE FROM `oauth_authorization_codes` WHERE `user_id` = :username");
            $stmt->execute(array(
              ':username' => $username
            ));
            $stmt = $pdo->prepare("SELECT `address`, `goto` FROM `alias`
                WHERE `goto` REGEXP :username");
            $stmt->execute(array(':username' => '(^|,)'.$username.'($|,)'));
            $GotoData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($GotoData as $gotos) {
              $goto_exploded = explode(',', $gotos['goto']);
              if (($key = array_search($username, $goto_exploded)) !== false) {
                unset($goto_exploded[$key]);
              }
              $gotos_rebuild = implode(',', $goto_exploded);
              $stmt = $pdo->prepare("UPDATE `alias` SET
                `goto` = :goto
                  WHERE `address` = :address");
              $stmt->execute(array(
                ':goto' => $gotos_rebuild,
                ':address' => $gotos['address']
              ));
            }
            try {
              $redis->hDel('RL_VALUE', $username);
            }
            catch (RedisException $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => array('redis_error', $e)
              );
              continue;
            }
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('mailbox_removed', htmlspecialchars($username))
            );
          }
        break;
        case 'resource':
          if (!is_array($_data['name'])) {
            $names = array();
            $names[] = $_data['name'];
          }
          else {
            $names = $_data['name'];
          }
          foreach ($names as $name) {
            if (!filter_var($name, FILTER_VALIDATE_EMAIL)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            if (!hasMailboxObjectAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $name)) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
                'msg' => 'access_denied'
              );
              continue;
            }
            $stmt = $pdo->prepare("DELETE FROM `mailbox` WHERE `username` = :username");
            $stmt->execute(array(
              ':username' => $name
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_user_profile` WHERE `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $name
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_cache_folder` WHERE `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $name
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_acl` WHERE `c_object` LIKE '%/" . $name . "/%' OR `c_uid` = :username");
            $stmt->execute(array(
              ':username' => $name
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_store` WHERE `c_folder_id` IN (SELECT `c_folder_id` FROM `sogo_folder_info` WHERE `c_path2` = :username)");
            $stmt->execute(array(
              ':username' => $name
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_quick_contact` WHERE `c_folder_id` IN (SELECT `c_folder_id` FROM `sogo_folder_info` WHERE `c_path2` = :username)");
            $stmt->execute(array(
              ':username' => $name
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_quick_appointment` WHERE `c_folder_id` IN (SELECT `c_folder_id` FROM `sogo_folder_info` WHERE `c_path2` = :username)");
            $stmt->execute(array(
              ':username' => $name
            ));
            $stmt = $pdo->prepare("DELETE FROM `sogo_folder_info` WHERE `c_path2` = :username");
            $stmt->execute(array(
              ':username' => $name
            ));
            $_SESSION['return'][] = array(
              'type' => 'success',
              'log' => array(__FUNCTION__, $_action, $_type, $_data_log, $_attr),
              'msg' => array('resource_removed', htmlspecialchars($name))
            );
          }
        break;
      }
    break;
  }
  if ($_action != 'get' && in_array($_type, array('domain', 'alias', 'alias_domain', 'mailbox', 'resource'))) {
    update_sogo_static_view();
  }
}

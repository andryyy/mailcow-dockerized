<?php
function isset_has_content($var) {
  if (isset($var) && $var != "") {
    return true;
  }
  else {
    return false;
  }
}
function hash_password($password) {
	$salt_str = bin2hex(openssl_random_pseudo_bytes(8));
	return "{SSHA256}".base64_encode(hash('sha256', $password . $salt_str, true) . $salt_str);
}
function last_login($user) {
  global $pdo;
  $stmt = $pdo->prepare('SELECT `remote`, `time` FROM `logs`
    WHERE JSON_EXTRACT(`call`, "$[0]") = "check_login"
      AND JSON_EXTRACT(`call`, "$[1]") = :user
      AND `type` = "success" ORDER BY `time` DESC LIMIT 1');
  $stmt->execute(array(':user' => $user));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!empty($row)) {
    return $row;
  }
  else {
    return false;
  }
}
function flush_memcached() {
  try {
    $m = new Memcached();
    $m->addServer('memcached', 11211);
    $m->flush();
  }
  catch ( Exception $e ) {
    // Dunno
  }
}
function sys_mail($_data) {
  if ($_SESSION['mailcow_cc_role'] != "admin") {
		$_SESSION['return'][] =  array(
			'type' => 'danger',
      'log' => array(__FUNCTION__),
			'msg' => 'access_denied'
		);
		return false;
	}
  $excludes = $_data['mass_exclude'];
  $includes = $_data['mass_include'];
  $mailboxes = array();
  $mass_from = $_data['mass_from'];
  $mass_text = $_data['mass_text'];
  $mass_subject = $_data['mass_subject'];
  if (!filter_var($mass_from, FILTER_VALIDATE_EMAIL)) {
		$_SESSION['return'][] =  array(
			'type' => 'danger',
      'log' => array(__FUNCTION__),
			'msg' => 'from_invalid'
		);
		return false;
  }
  if (empty($mass_subject)) {
		$_SESSION['return'][] =  array(
			'type' => 'danger',
      'log' => array(__FUNCTION__),
			'msg' => 'subject_empty'
		);
		return false;
  }
  if (empty($mass_text)) {
		$_SESSION['return'][] =  array(
			'type' => 'danger',
      'log' => array(__FUNCTION__),
			'msg' => 'text_empty'
		);
		return false;
  }
  $domains = array_merge(mailbox('get', 'domains'), mailbox('get', 'alias_domains'));
  foreach ($domains as $domain) {
    foreach (mailbox('get', 'mailboxes', $domain) as $mailbox) {
      $mailboxes[] = $mailbox;
    }
  }
  if (!empty($includes)) {
    $rcpts = array_intersect($mailboxes, $includes);
  }
  elseif (!empty($excludes)) {
    $rcpts = array_diff($mailboxes, $excludes);
  }
  else {
    $rcpts = $mailboxes;
  }
  if (!empty($rcpts)) {
    ini_set('max_execution_time', 0);
    ini_set('max_input_time', 0);
    $mail = new PHPMailer;
    $mail->Timeout = 10;
    $mail->SMTPOptions = array(
      'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      )
    );
    $mail->isSMTP();
    $mail->Host = 'dovecot-mailcow';
    $mail->SMTPAuth = false;
    $mail->Port = 24;
    $mail->setFrom($mass_from);
    $mail->Subject = $mass_subject;
    $mail->CharSet ="UTF-8";
    $mail->Body = $mass_text;
    $mail->XMailer = 'MooMassMail';
    foreach ($rcpts as $rcpt) {
      $mail->AddAddress($rcpt);
      if (!$mail->send()) {
        $_SESSION['return'][] =  array(
          'type' => 'warning',
          'log' => array(__FUNCTION__),
          'msg' => 'Mailer error (RCPT "' . htmlspecialchars($rcpt) . '"): ' . str_replace('https://github.com/PHPMailer/PHPMailer/wiki/Troubleshooting', '', $mail->ErrorInfo)
        );
      }
      $mail->ClearAllRecipients();
    }
  }
  $_SESSION['return'][] =  array(
    'type' => 'success',
    'log' => array(__FUNCTION__),
    'msg' => 'Mass mail job completed, sent ' . count($rcpts) . ' mails'
  );
}
function logger($_data = false) {
  /*
  logger() will be called as last function
  To manually log a message, logger needs to be called like below.

  logger(array(
    'return' => array(
      array(
        'type' => 'danger',
        'log' => array(__FUNCTION__),
        'msg' => $err
      )
    )
  ));

  These messages will not be printed as alert box.
  To do so, push them to $_SESSION['return'] and do not call logger as they will be included automatically:

  $_SESSION['return'][] =  array(
    'type' => 'danger',
    'log' => array(__FUNCTION__, $user, '*'),
    'msg' => $err
  );
  */
  global $pdo;
  if (!$_data) {
    $_data = $_SESSION;
  }
  if (!empty($_data['return'])) {
    $task = substr(strtoupper(md5(uniqid(rand(), true))), 0, 6);
    foreach ($_data['return'] as $return) {
      $type = $return['type'];
      $msg = json_encode($return['msg'], JSON_UNESCAPED_UNICODE);
      $call = json_encode($return['log'], JSON_UNESCAPED_UNICODE);
      if (!empty($_SESSION["dual-login"]["username"])) {
        $user = $_SESSION["dual-login"]["username"] . ' => ' . $_SESSION['mailcow_cc_username'];
        $role = $_SESSION["dual-login"]["role"] . ' => ' . $_SESSION['mailcow_cc_role'];
      }
      elseif (!empty($_SESSION['mailcow_cc_username'])) {
        $user = $_SESSION['mailcow_cc_username'];
        $role = $_SESSION['mailcow_cc_role'];
      }
      else {
        $user = 'unauthenticated';
        $role = 'unauthenticated';
      }
      // We cannot log when logs is missing...
      try {
        $stmt = $pdo->prepare("INSERT INTO `logs` (`type`, `task`, `msg`, `call`, `user`, `role`, `remote`, `time`) VALUES
          (:type, :task, :msg, :call, :user, :role, :remote, UNIX_TIMESTAMP())");
        $stmt->execute(array(
          ':type' => $type,
          ':task' => $task,
          ':call' => $call,
          ':msg' => $msg,
          ':user' => $user,
          ':role' => $role,
          ':remote' => get_remote_ip()
        ));
      }
      catch (Exception $e) {
        // Do nothing
      }
    }
  }
  else {
    return true;
  }
}
function hasDomainAccess($username, $role, $domain) {
	global $pdo;
	if (!filter_var($username, FILTER_VALIDATE_EMAIL) && !ctype_alnum(str_replace(array('_', '.', '-'), '', $username))) {
		return false;
	}
	if (empty($domain) || !is_valid_domain_name($domain)) {
		return false;
	}
	if ($role != 'admin' && $role != 'domainadmin') {
		return false;
	}
  if ($role == 'admin') {
    $stmt = $pdo->prepare("SELECT `domain` FROM `domain`
      WHERE `domain` = :domain");
    $stmt->execute(array(':domain' => $domain));
    $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
    $stmt = $pdo->prepare("SELECT `alias_domain` FROM `alias_domain`
      WHERE `alias_domain` = :domain");
    $stmt->execute(array(':domain' => $domain));
    $num_results = $num_results + count($stmt->fetchAll(PDO::FETCH_ASSOC));
    if ($num_results != 0) {
      return true;
    }
  }
  elseif ($role == 'domainadmin') {
    $stmt = $pdo->prepare("SELECT `domain` FROM `domain_admins`
    WHERE (
      `active`='1'
      AND `username` = :username
      AND (`domain` = :domain1 OR `domain` = (SELECT `target_domain` FROM `alias_domain` WHERE `alias_domain` = :domain2))
    )");
    $stmt->execute(array(':username' => $username, ':domain1' => $domain, ':domain2' => $domain));
    $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
    if (!empty($num_results)) {
      return true;
    }
  }
	return false;
}
function hasMailboxObjectAccess($username, $role, $object) {
	global $pdo;
	if (!filter_var(html_entity_decode(rawurldecode($username)), FILTER_VALIDATE_EMAIL) && !ctype_alnum(str_replace(array('_', '.', '-'), '', $username))) {
		return false;
	}
	if ($role != 'admin' && $role != 'domainadmin' && $role != 'user') {
		return false;
	}
	if ($username == $object) {
		return true;
	}
  $stmt = $pdo->prepare("SELECT `domain` FROM `mailbox` WHERE `username` = :object");
  $stmt->execute(array(':object' => $object));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (isset($row['domain']) && hasDomainAccess($username, $role, $row['domain'])) {
    return true;
  }
	return false;
}
function hasAliasObjectAccess($username, $role, $object) {
	global $pdo;
	if (!filter_var(html_entity_decode(rawurldecode($username)), FILTER_VALIDATE_EMAIL) && !ctype_alnum(str_replace(array('_', '.', '-'), '', $username))) {
		return false;
	}
	if ($role != 'admin' && $role != 'domainadmin' && $role != 'user') {
		return false;
	}
	if ($username == $object) {
		return true;
	}
  $stmt = $pdo->prepare("SELECT `domain` FROM `alias` WHERE `address` = :object");
  $stmt->execute(array(':object' => $object));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (isset($row['domain']) && hasDomainAccess($username, $role, $row['domain'])) {
    return true;
  }
	return false;
}
function pem_to_der($pem_key) {
  // Need to remove BEGIN/END PUBLIC KEY
  $lines = explode("\n", trim($pem_key));
  unset($lines[count($lines)-1]);
  unset($lines[0]);
  return base64_decode(implode('', $lines));
}
function generate_tlsa_digest($hostname, $port, $starttls = null, $ciphers = 'DEFAULT') {
  if (!is_valid_domain_name($hostname)) {
    return "Not a valid hostname";
  }
  if (empty($starttls)) {
    $context = stream_context_create(array("ssl" => array("ciphers" => $ciphers, "capture_peer_cert" => true, 'verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)));
    $stream = stream_socket_client('ssl://' . $hostname . ':' . $port, $error_nr, $error_msg, 5, STREAM_CLIENT_CONNECT, $context);
    if (!$stream) {
      if($error_nr === 0) return false; // probably negotiation failed
      $error_msg = isset($error_msg) ? $error_msg : '-';
      return $error_nr . ': ' . $error_msg;
    }
  }
  else {
    $stream = stream_socket_client('tcp://' . $hostname . ':' . $port, $error_nr, $error_msg, 5);
    if (!$stream) {
      return $error_nr . ': ' . $error_msg;
    }
    $banner = fread($stream, 512 );
    if (preg_match("/^220/i", $banner)) { // SMTP
      fwrite($stream,"HELO tlsa.generator.local\r\n");
      fread($stream, 512);
      fwrite($stream,"STARTTLS\r\n");
      fread($stream, 512);
    }
    elseif (preg_match("/imap.+starttls/i", $banner)) { // IMAP
      fwrite($stream,"A1 STARTTLS\r\n");
      fread($stream, 512);
    }
    elseif (preg_match("/^\+OK/", $banner)) { // POP3
      fwrite($stream,"STLS\r\n");
      fread($stream, 512);
    }
    elseif (preg_match("/^OK/m", $banner)) { // Sieve
      fwrite($stream,"STARTTLS\r\n");
      fread($stream, 512);
    }
    else {
      return 'Unknown banner: "' . htmlspecialchars(trim($banner)) . '"';
    }
    // Upgrade connection
    stream_set_blocking($stream, true);
    stream_context_set_option($stream, 'ssl', 'capture_peer_cert', true);
    stream_context_set_option($stream, 'ssl', 'verify_peer', false);
    stream_context_set_option($stream, 'ssl', 'verify_peer_name', false);
    stream_context_set_option($stream, 'ssl', 'allow_self_signed', true);
    stream_context_set_option($stream, 'ssl', 'ciphers', $ciphers);
    if(stream_socket_enable_crypto($stream, true, STREAM_CRYPTO_METHOD_ANY_CLIENT) === false) {
      return false; // negotiation failed
    }
    stream_set_blocking($stream, false);
  }
  $params = stream_context_get_params($stream);
  if (!empty($params['options']['ssl']['peer_certificate'])) {
    $key_resource = openssl_pkey_get_public($params['options']['ssl']['peer_certificate']);
    // We cannot get ['rsa']['n'], the binary data would contain BEGIN/END PUBLIC KEY
    $key_data = openssl_pkey_get_details($key_resource)['key'];
    return '3 1 1 ' . openssl_digest(pem_to_der($key_data), 'sha256');
  }
  else {
    return 'Error: Cannot read peer certificate';
  }
}
function alertbox_log_parser($_data){
  global $lang;
  if (isset($_data['return'])) {
    foreach ($_data['return'] as $return) {
      // Get type
      $type = $return['type'];
      // If a lang[type][msg] string exists, use it as message
      if (is_string($lang[$return['type']][$return['msg']])) {
        $msg = $lang[$return['type']][$return['msg']];
      }
      // If msg is an array, use first element as language string and run printf on it with remaining array elements
      elseif (is_array($return['msg'])) {
        $msg = array_shift($return['msg']);
        $msg = vsprintf(
          $lang[$return['type']][$msg],
          $return['msg']
        );
      }
      // If none applies, use msg as returned message
      else {
        $msg = $return['msg'];
      }
      $log_array[] = array('msg' => json_encode($msg), 'type' => json_encode($type));
    }
    if (!empty($log_array)) {
      return $log_array;
    }
  }
  return false;
}
function verify_hash($hash, $password) {
  if (preg_match('/^{SSHA256}/i', $hash)) {
    // Remove tag if any
    $hash = preg_replace('/^{SSHA256}/i', '', $hash);
    // Decode hash
    $dhash = base64_decode($hash);
    // Get first 32 bytes of binary which equals a SHA256 hash
    $ohash = substr($dhash, 0, 32);
    // Remove SHA256 hash from decoded hash to get original salt string
    $osalt = str_replace($ohash, '', $dhash);
    // Check single salted SHA256 hash against extracted hash
    if (hash_equals(hash('sha256', $password . $osalt, true), $ohash)) {
      return true;
    }
  }
  elseif (preg_match('/^{PLAIN-MD5}/i', $hash)) {
    $hash = preg_replace('/^{PLAIN-MD5}/i', '', $hash);
    if (md5($password) == $hash) {
      return true;
    }
  }
  elseif (preg_match('/^{SHA512-CRYPT}/i', $hash)) {
    // Remove tag if any
    $hash = preg_replace('/^{SHA512-CRYPT}/i', '', $hash);
    // Decode hash
    preg_match('/\\$6\\$(.*)\\$(.*)/i', $hash, $hash_array);
    $osalt = $hash_array[1];
    $ohash = $hash_array[2];
    if (hash_equals(crypt($password, '$6$' . $osalt . '$'), $hash)) {
      return true;
    }
  }
  elseif (preg_match('/^{SSHA512}/i', $hash)) {
    $hash = preg_replace('/^{SSHA512}/i', '', $hash);
    // Decode hash
    $dhash = base64_decode($hash);
    // Get first 64 bytes of binary which equals a SHA512 hash
    $ohash = substr($dhash, 0, 64);
    // Remove SHA512 hash from decoded hash to get original salt string
    $osalt = str_replace($ohash, '', $dhash);
    // Check single salted SHA512 hash against extracted hash
    if (hash_equals(hash('sha512', $password . $osalt, true), $ohash)) {
      return true;
    }
  }
  elseif (preg_match('/^{MD5-CRYPT}/i', $hash)) {
    $hash = preg_replace('/^{MD5-CRYPT}/i', '', $hash);
    if (password_verify($password, $hash)) {
      return true;
    }
  }
  return false;
}
function check_login($user, $pass) {
	global $pdo;
	global $redis;
	global $imap_server;
	if (!filter_var($user, FILTER_VALIDATE_EMAIL) && !ctype_alnum(str_replace(array('_', '.', '-'), '', $user))) {
    $_SESSION['return'][] =  array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $user, '*'),
      'msg' => 'malformed_username'
    );
		return false;
	}
	$user = strtolower(trim($user));
	$stmt = $pdo->prepare("SELECT `password` FROM `admin`
			WHERE `superadmin` = '1'
			AND `active` = '1'
			AND `username` = :user");
	$stmt->execute(array(':user' => $user));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		if (verify_hash($row['password'], $pass)) {
      if (get_tfa($user)['name'] != "none") {
        $_SESSION['pending_mailcow_cc_username'] = $user;
        $_SESSION['pending_mailcow_cc_role'] = "admin";
        $_SESSION['pending_tfa_method'] = get_tfa($user)['name'];
        unset($_SESSION['ldelay']);
        $_SESSION['return'][] =  array(
          'type' => 'info',
          'log' => array(__FUNCTION__, $user, '*'),
          'msg' => 'awaiting_tfa_confirmation'
        );
        return "pending";
      }
      else {
        unset($_SESSION['ldelay']);
        // Reactivate TFA if it was set to "deactivate TFA for next login"
        $stmt = $pdo->prepare("UPDATE `tfa` SET `active`='1' WHERE `username` = :user");
        $stmt->execute(array(':user' => $user));
        $_SESSION['return'][] =  array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $user, '*'),
          'msg' => array('logged_in_as', $user)
        );
        return "admin";
      }
		}
	}
	$stmt = $pdo->prepare("SELECT `password` FROM `admin`
			WHERE `superadmin` = '0'
			AND `active`='1'
			AND `username` = :user");
	$stmt->execute(array(':user' => $user));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		if (verify_hash($row['password'], $pass) !== false) {
      if (get_tfa($user)['name'] != "none") {
        $_SESSION['pending_mailcow_cc_username'] = $user;
        $_SESSION['pending_mailcow_cc_role'] = "domainadmin";
        $_SESSION['pending_tfa_method'] = get_tfa($user)['name'];
        unset($_SESSION['ldelay']);
        $_SESSION['return'][] =  array(
          'type' => 'info',
          'log' => array(__FUNCTION__, $user, '*'),
          'msg' => 'awaiting_tfa_confirmation'
        );
        return "pending";
      }
      else {
        unset($_SESSION['ldelay']);
        // Reactivate TFA if it was set to "deactivate TFA for next login"
        $stmt = $pdo->prepare("UPDATE `tfa` SET `active`='1' WHERE `username` = :user");
        $stmt->execute(array(':user' => $user));
        $_SESSION['return'][] =  array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $user, '*'),
          'msg' => array('logged_in_as', $user)
        );
        return "domainadmin";
      }
		}
	}
	$stmt = $pdo->prepare("SELECT `password` FROM `mailbox`
			WHERE `kind` NOT REGEXP 'location|thing|group'
        AND `active`='1'
        AND `username` = :user");
	$stmt->execute(array(':user' => $user));
	$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($rows as $row) {
		if (verify_hash($row['password'], $pass) !== false) {
			unset($_SESSION['ldelay']);
      $_SESSION['return'][] =  array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $user, '*'),
        'msg' => array('logged_in_as', $user)
      );
			return "user";
		}
	}
	if (!isset($_SESSION['ldelay'])) {
		$_SESSION['ldelay'] = "0";
    $redis->publish("F2B_CHANNEL", "mailcow UI: Invalid password for " . $user . " by " . $_SERVER['REMOTE_ADDR']);
    error_log("mailcow UI: Invalid password for " . $user . " by " . $_SERVER['REMOTE_ADDR']);
	}
	elseif (!isset($_SESSION['mailcow_cc_username'])) {
		$_SESSION['ldelay'] = $_SESSION['ldelay']+0.5;
    $redis->publish("F2B_CHANNEL", "mailcow UI: Invalid password for " . $user . " by " . $_SERVER['REMOTE_ADDR']);
		error_log("mailcow UI: Invalid password for " . $user . " by " . $_SERVER['REMOTE_ADDR']);
	}
  $_SESSION['return'][] =  array(
    'type' => 'danger',
    'log' => array(__FUNCTION__, $user, '*'),
    'msg' => 'login_failed'
  );
	sleep($_SESSION['ldelay']);
  return false;
}
function formatBytes($size, $precision = 2) {
	if(!is_numeric($size)) {
		return "0";
	}
	$base = log($size, 1024);
	$suffixes = array(' Byte', ' KiB', ' MiB', ' GiB', ' TiB');
	if ($size == "0") {
		return "0";
	}
	return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
}
function update_sogo_static_view() {
  global $pdo;
  global $lang;
  $stmt = $pdo->query("SELECT 'OK' FROM INFORMATION_SCHEMA.TABLES
    WHERE TABLE_NAME = 'sogo_view'");
  $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
  if ($num_results != 0) {
    $stmt = $pdo->query("REPLACE INTO _sogo_static_view (`c_uid`, `domain`, `c_name`, `c_password`, `c_cn`, `mail`, `aliases`, `ad_aliases`, `ext_acl`, `kind`, `multiple_bookings`)
      SELECT `c_uid`, `domain`, `c_name`, `c_password`, `c_cn`, `mail`, `aliases`, `ad_aliases`, `ext_acl`, `kind`, `multiple_bookings` from sogo_view");
    $stmt = $pdo->query("DELETE FROM _sogo_static_view WHERE `c_uid` NOT IN (SELECT `username` FROM `mailbox` WHERE `active` = '1');");
  }
  flush_memcached();
}
function edit_user_account($_data) {
	global $lang;
	global $pdo;
  $_data_log = $_data;
  !isset($_data_log['user_new_pass']) ?: $_data_log['user_new_pass'] = '*';
  !isset($_data_log['user_new_pass2']) ?: $_data_log['user_new_pass2'] = '*';
  !isset($_data_log['user_old_pass']) ?: $_data_log['user_old_pass'] = '*';
  $username = $_SESSION['mailcow_cc_username'];
  $role = $_SESSION['mailcow_cc_role'];
	$password_old = $_data['user_old_pass'];
  if (filter_var($username, FILTER_VALIDATE_EMAIL === false) || $role != 'user') {
    $_SESSION['return'][] =  array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $_data_log),
      'msg' => 'access_denied'
    );
    return false;
  }
	if (isset($_data['user_new_pass']) && isset($_data['user_new_pass2'])) {
		$password_new	= $_data['user_new_pass'];
		$password_new2	= $_data['user_new_pass2'];
	}
	$stmt = $pdo->prepare("SELECT `password` FROM `mailbox`
			WHERE `kind` NOT REGEXP 'location|thing|group'
        AND `username` = :user");
	$stmt->execute(array(':user' => $username));
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!verify_hash($row['password'], $password_old)) {
    $_SESSION['return'][] =  array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $_data_log),
      'msg' => 'access_denied'
    );
    return false;
  }
	if (isset($password_new) && isset($password_new2)) {
		if (!empty($password_new2) && !empty($password_new)) {
			if ($password_new2 != $password_new) {
				$_SESSION['return'][] =  array(
					'type' => 'danger',
          'log' => array(__FUNCTION__, $_data_log),
					'msg' => 'password_mismatch'
				);
				return false;
			}
			if (!preg_match('/' . $GLOBALS['PASSWD_REGEP'] . '/', $password_new)) {
					$_SESSION['return'][] =  array(
						'type' => 'danger',
            'log' => array(__FUNCTION__, $_data_log),
						'msg' => 'password_complexity'
					);
					return false;
			}
			$password_hashed = hash_password($password_new);
			try {
				$stmt = $pdo->prepare("UPDATE `mailbox` SET `password` = :password_hashed, `attributes` = JSON_SET(`attributes`, '$.force_pw_update', '0') WHERE `username` = :username");
				$stmt->execute(array(
					':password_hashed' => $password_hashed,
					':username' => $username
				));
			}
			catch (PDOException $e) {
				$_SESSION['return'][] =  array(
					'type' => 'danger',
          'log' => array(__FUNCTION__, $_data_log),
					'msg' => array('mysql_error', $e)
				);
				return false;
			}
		}
	}
  update_sogo_static_view();
	$_SESSION['return'][] =  array(
		'type' => 'success',
    'log' => array(__FUNCTION__, $_data_log),
		'msg' => array('mailbox_modified', htmlspecialchars($username))
	);
}
function user_get_alias_details($username) {
	global $lang;
	global $pdo;
  $data['direct_aliases'] = false;
  $data['shared_aliases'] = false;
  if ($_SESSION['mailcow_cc_role'] == "user") {
    $username	= $_SESSION['mailcow_cc_username'];
  }
  if (!filter_var($username, FILTER_VALIDATE_EMAIL)) {
    return false;
  }
  $data['address'] = $username;
  $stmt = $pdo->prepare("SELECT `address` AS `shared_aliases`, `public_comment` FROM `alias`
    WHERE `goto` REGEXP :username_goto
    AND `address` NOT LIKE '@%'
    AND `goto` != :username_goto2
    AND `address` != :username_address");
  $stmt->execute(array(
    ':username_goto' => '(^|,)'.$username.'($|,)',
    ':username_goto2' => $username,
    ':username_address' => $username
    ));
  $run = $stmt->fetchAll(PDO::FETCH_ASSOC);
  while ($row = array_shift($run)) {
    $data['shared_aliases'][$row['shared_aliases']]['public_comment'] = htmlspecialchars($row['public_comment']);

    //$data['shared_aliases'][] = $row['shared_aliases'];
  }

  $stmt = $pdo->prepare("SELECT `address` AS `direct_aliases`, `public_comment` FROM `alias`
    WHERE `goto` = :username_goto
    AND `address` NOT LIKE '@%'
    AND `address` != :username_address");
  $stmt->execute(
    array(
    ':username_goto' => $username,
    ':username_address' => $username
  ));
  $run = $stmt->fetchAll(PDO::FETCH_ASSOC);
  while ($row = array_shift($run)) {
    $data['direct_aliases'][$row['direct_aliases']]['public_comment'] = htmlspecialchars($row['public_comment']);
  }
  $stmt = $pdo->prepare("SELECT CONCAT(local_part, '@', alias_domain) AS `ad_alias`, `alias_domain` FROM `mailbox`
    LEFT OUTER JOIN `alias_domain` on `target_domain` = `domain`
      WHERE `username` = :username ;");
  $stmt->execute(array(':username' => $username));
  $run = $stmt->fetchAll(PDO::FETCH_ASSOC);
  while ($row = array_shift($run)) {
    if (empty($row['ad_alias'])) {
      continue;
    }
    $data['direct_aliases'][$row['ad_alias']]['public_comment'] = '↪ ' . $row['alias_domain'];
  }
  $stmt = $pdo->prepare("SELECT IFNULL(GROUP_CONCAT(`send_as` SEPARATOR ', '), '&#10008;') AS `send_as` FROM `sender_acl` WHERE `logged_in_as` = :username AND `send_as` NOT LIKE '@%';");
  $stmt->execute(array(':username' => $username));
  $run = $stmt->fetchAll(PDO::FETCH_ASSOC);
  while ($row = array_shift($run)) {
    $data['aliases_also_send_as'] = $row['send_as'];
  }
  $stmt = $pdo->prepare("SELECT CONCAT_WS(', ', IFNULL(GROUP_CONCAT(DISTINCT `send_as` SEPARATOR ', '), '&#10008;'), GROUP_CONCAT(DISTINCT CONCAT('@',`alias_domain`) SEPARATOR ', ')) AS `send_as` FROM `sender_acl` LEFT JOIN `alias_domain` ON `alias_domain`.`target_domain` =  TRIM(LEADING '@' FROM `send_as`) WHERE `logged_in_as` = :username AND `send_as` LIKE '@%';");
  $stmt->execute(array(':username' => $username));
  $run = $stmt->fetchAll(PDO::FETCH_ASSOC);
  while ($row = array_shift($run)) {
    $data['aliases_send_as_all'] = $row['send_as'];
  }
  $stmt = $pdo->prepare("SELECT IFNULL(GROUP_CONCAT(`address` SEPARATOR ', '), '&#10008;') as `address` FROM `alias` WHERE `goto` REGEXP :username AND `address` LIKE '@%';");
  $stmt->execute(array(':username' => '(^|,)'.$username.'($|,)'));
  $run = $stmt->fetchAll(PDO::FETCH_ASSOC);
  while ($row = array_shift($run)) {
    $data['is_catch_all'] = $row['address'];
  }
  return $data;
}
function is_valid_domain_name($domain_name) {
	if (empty($domain_name)) {
		return false;
	}
	$domain_name = idn_to_ascii($domain_name, 0, INTL_IDNA_VARIANT_UTS46);
	return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name)
		   && preg_match("/^.{1,253}$/", $domain_name)
		   && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name));
}
function set_tfa($_data) {
	global $lang;
	global $pdo;
	global $yubi;
	global $u2f;
	global $tfa;
  $_data_log = $_data;
  !isset($_data_log['confirm_password']) ?: $_data_log['confirm_password'] = '*';
  $username = $_SESSION['mailcow_cc_username'];
  if ($_SESSION['mailcow_cc_role'] != "domainadmin" &&
    $_SESSION['mailcow_cc_role'] != "admin") {
      $_SESSION['return'][] =  array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $_data_log),
        'msg' => 'access_denied'
      );
      return false;
  }
  $stmt = $pdo->prepare("SELECT `password` FROM `admin`
      WHERE `username` = :user");
  $stmt->execute(array(':user' => $username));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!verify_hash($row['password'], $_data["confirm_password"])) {
    $_SESSION['return'][] =  array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $_data_log),
      'msg' => 'access_denied'
    );
    return false;
  }

	switch ($_data["tfa_method"]) {
		case "yubi_otp":
      $key_id = (!isset($_data["key_id"])) ? 'unidentified' : $_data["key_id"];
      $yubico_id = $_data['yubico_id'];
      $yubico_key = $_data['yubico_key'];
      $yubi = new Auth_Yubico($yubico_id, $yubico_key);
      if (!$yubi) {
        $_SESSION['return'][] =  array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
			if (!ctype_alnum($_data["otp_token"]) || strlen($_data["otp_token"]) != 44) {
				$_SESSION['return'][] =  array(
					'type' => 'danger',
          'log' => array(__FUNCTION__, $_data_log),
					'msg' => 'tfa_token_invalid'
				);
				return false;
			}
      $yauth = $yubi->verify($_data["otp_token"]);
      if (PEAR::isError($yauth)) {
				$_SESSION['return'][] =  array(
					'type' => 'danger',
          'log' => array(__FUNCTION__, $_data_log),
          'msg' => array('yotp_verification_failed', $yauth->getMessage())
				);
				return false;
      }
			try {
        // We could also do a modhex translation here
        $yubico_modhex_id = substr($_data["otp_token"], 0, 12);
        $stmt = $pdo->prepare("DELETE FROM `tfa` 
          WHERE `username` = :username
            AND (`authmech` != 'yubi_otp')
            OR (`authmech` = 'yubi_otp' AND `secret` LIKE :modhex)");
				$stmt->execute(array(':username' => $username, ':modhex' => '%' . $yubico_modhex_id));
				$stmt = $pdo->prepare("INSERT INTO `tfa` (`key_id`, `username`, `authmech`, `active`, `secret`) VALUES
					(:key_id, :username, 'yubi_otp', '1', :secret)");
				$stmt->execute(array(':key_id' => $key_id, ':username' => $username, ':secret' => $yubico_id . ':' . $yubico_key . ':' . $yubico_modhex_id));
			}
			catch (PDOException $e) {
				$_SESSION['return'][] =  array(
					'type' => 'danger',
          'log' => array(__FUNCTION__, $_data_log),
					'msg' => array('mysql_error', $e)
				);
				return false;
			}
			$_SESSION['return'][] =  array(
				'type' => 'success',
        'log' => array(__FUNCTION__, $_data_log),
				'msg' => array('object_modified', htmlspecialchars($username))
			);
		break;
		case "u2f":
      $key_id = (!isset($_data["key_id"])) ? 'unidentified' : $_data["key_id"];
      try {
        $reg = $u2f->doRegister(json_decode($_SESSION['regReq']), json_decode($_data['token']));
        $stmt = $pdo->prepare("DELETE FROM `tfa` WHERE `username` = :username AND `authmech` != 'u2f'");
				$stmt->execute(array(':username' => $username));
        $stmt = $pdo->prepare("INSERT INTO `tfa` (`username`, `key_id`, `authmech`, `keyHandle`, `publicKey`, `certificate`, `counter`, `active`) VALUES (?, ?, 'u2f', ?, ?, ?, ?, '1')");
        $stmt->execute(array($username, $key_id, $reg->keyHandle, $reg->publicKey, $reg->certificate, $reg->counter));
        $_SESSION['return'][] =  array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_data_log),
          'msg' => array('object_modified', $username)
        );
        $_SESSION['regReq'] = null;
      }
      catch (Exception $e) {
        $_SESSION['return'][] =  array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_data_log),
          'msg' => array('u2f_verification_failed', $e->getMessage())
        );
        $_SESSION['regReq'] = null;
        return false;
      }
		break;
		case "totp":
      $key_id = (!isset($_data["key_id"])) ? 'unidentified' : $_data["key_id"];
      if ($tfa->verifyCode($_POST['totp_secret'], $_POST['totp_confirm_token']) === true) {
        try {
        $stmt = $pdo->prepare("DELETE FROM `tfa` WHERE `username` = :username");
        $stmt->execute(array(':username' => $username));
        $stmt = $pdo->prepare("INSERT INTO `tfa` (`username`, `key_id`, `authmech`, `secret`, `active`) VALUES (?, ?, 'totp', ?, '1')");
        $stmt->execute(array($username, $key_id, $_POST['totp_secret']));
        }
        catch (PDOException $e) {
          $_SESSION['return'][] =  array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_data_log),
            'msg' => array('mysql_error', $e)
          );
          return false;
        }
        $_SESSION['return'][] =  array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_data_log),
          'msg' => array('object_modified', $username)
        );
      }
      else {
        $_SESSION['return'][] =  array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_data_log),
          'msg' => 'totp_verification_failed'
        );
      }
		break;
		case "none":
			try {
				$stmt = $pdo->prepare("DELETE FROM `tfa` WHERE `username` = :username");
				$stmt->execute(array(':username' => $username));
			}
			catch (PDOException $e) {
				$_SESSION['return'][] =  array(
					'type' => 'danger',
          'log' => array(__FUNCTION__, $_data_log),
					'msg' => array('mysql_error', $e)
				);
				return false;
			}
			$_SESSION['return'][] =  array(
				'type' => 'success',
        'log' => array(__FUNCTION__, $_data_log),
				'msg' => array('object_modified', htmlspecialchars($username))
			);
		break;
	}
}
function unset_tfa_key($_data) {
  // Can only unset own keys
  // Needs at least one key left
  global $pdo;
  global $lang;
  $_data_log = $_data;
  $id = intval($_data['unset_tfa_key']);
  $username = $_SESSION['mailcow_cc_username'];
  if ($_SESSION['mailcow_cc_role'] != "domainadmin" &&
    $_SESSION['mailcow_cc_role'] != "admin") {
      $_SESSION['return'][] =  array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $_data_log),
        'msg' => 'access_denied'
      );
      return false;
  }
  try {
    if (!is_numeric($id)) {
      $_SESSION['return'][] =  array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $_data_log),
        'msg' => 'access_denied'
      );
      return false;
    }
    $stmt = $pdo->prepare("SELECT COUNT(*) AS `keys` FROM `tfa`
      WHERE `username` = :username AND `active` = '1'");
    $stmt->execute(array(':username' => $username));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['keys'] == "1") {
      $_SESSION['return'][] =  array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $_data_log),
        'msg' => 'last_key'
      );
      return false;
    }
    $stmt = $pdo->prepare("DELETE FROM `tfa` WHERE `username` = :username AND `id` = :id");
    $stmt->execute(array(':username' => $username, ':id' => $id));
    $_SESSION['return'][] =  array(
      'type' => 'success',
      'log' => array(__FUNCTION__, $_data_log),
      'msg' => array('object_modified', $username)
    );
  }
  catch (PDOException $e) {
    $_SESSION['return'][] =  array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $_data_log),
      'msg' => array('mysql_error', $e)
    );
    return false;
  }
}
function get_tfa($username = null) {
	global $pdo;
  if (isset($_SESSION['mailcow_cc_username'])) {
    $username = $_SESSION['mailcow_cc_username'];
  }
  elseif (empty($username)) {
    return false;
  }
  $stmt = $pdo->prepare("SELECT * FROM `tfa`
      WHERE `username` = :username AND `active` = '1'");
  $stmt->execute(array(':username' => $username));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

	switch ($row["authmech"]) {
		case "yubi_otp":
      $data['name'] = "yubi_otp";
      $data['pretty'] = "Yubico OTP";
      $stmt = $pdo->prepare("SELECT `id`, `key_id`, RIGHT(`secret`, 12) AS 'modhex' FROM `tfa` WHERE `authmech` = 'yubi_otp' AND `username` = :username");
      $stmt->execute(array(
        ':username' => $username,
      ));
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      while($row = array_shift($rows)) {
        $data['additional'][] = $row;
      }
      return $data;
    break;
		case "u2f":
      $data['name'] = "u2f";
      $data['pretty'] = "Fido U2F";
      $stmt = $pdo->prepare("SELECT `id`, `key_id` FROM `tfa` WHERE `authmech` = 'u2f' AND `username` = :username");
      $stmt->execute(array(
        ':username' => $username,
      ));
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      while($row = array_shift($rows)) {
        $data['additional'][] = $row;
      }
      return $data;
    break;
		case "hotp":
      $data['name'] = "hotp";
      $data['pretty'] = "HMAC-based OTP";
      return $data;
		break;
 		case "totp":
      $data['name'] = "totp";
      $data['pretty'] = "Time-based OTP";
      $stmt = $pdo->prepare("SELECT `id`, `key_id`, `secret` FROM `tfa` WHERE `authmech` = 'totp' AND `username` = :username");
      $stmt->execute(array(
        ':username' => $username,
      ));
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      while($row = array_shift($rows)) {
        $data['additional'][] = $row;
      }
      return $data;
      break;
    default:
      $data['name'] = 'none';
      $data['pretty'] = "-";
      return $data;
    break;
	}
}
function verify_tfa_login($username, $token) {
	global $pdo;
	global $lang;
	global $yubi;
	global $u2f;
	global $tfa;
  $stmt = $pdo->prepare("SELECT `authmech` FROM `tfa`
      WHERE `username` = :username AND `active` = '1'");
  $stmt->execute(array(':username' => $username));
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

	switch ($row["authmech"]) {
		case "yubi_otp":
			if (!ctype_alnum($token) || strlen($token) != 44) {
				$_SESSION['return'][] =  array(
					'type' => 'danger',
          'log' => array(__FUNCTION__, $username, '*'),
					'msg' => array('yotp_verification_failed', 'token length error')
				);
        return false;
      }
      $yubico_modhex_id = substr($token, 0, 12);
      $stmt = $pdo->prepare("SELECT `id`, `secret` FROM `tfa`
          WHERE `username` = :username
          AND `authmech` = 'yubi_otp'
          AND `active`='1'
          AND `secret` LIKE :modhex");
      $stmt->execute(array(':username' => $username, ':modhex' => '%' . $yubico_modhex_id));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      $yubico_auth = explode(':', $row['secret']);
      $yubi = new Auth_Yubico($yubico_auth[0], $yubico_auth[1]);
      $yauth = $yubi->verify($token);
      if (PEAR::isError($yauth)) {
				$_SESSION['return'][] =  array(
					'type' => 'danger',
          'log' => array(__FUNCTION__, $username, '*'),
					'msg' => array('yotp_verification_failed', $yauth->getMessage())
				);
				return false;
      }
      else {
        $_SESSION['tfa_id'] = $row['id'];
				$_SESSION['return'][] =  array(
					'type' => 'success',
          'log' => array(__FUNCTION__, $username, '*'),
					'msg' => 'verified_yotp_login'
				);
        return true;
      }
      $_SESSION['return'][] =  array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $username, '*'),
        'msg' => array('yotp_verification_failed', 'unknown')
      );
    return false;
  break;
  case "u2f":
    try {
      $reg = $u2f->doAuthenticate(json_decode($_SESSION['authReq']), get_u2f_registrations($username), json_decode($token));
      $stmt = $pdo->prepare("SELECT `id` FROM `tfa` WHERE `keyHandle` = ?");
      $stmt->execute(array($reg->keyHandle));
      $row_key_id = $stmt->fetch(PDO::FETCH_ASSOC);
      $_SESSION['tfa_id'] = $row_key_id['id'];
      $_SESSION['authReq'] = null;
      $_SESSION['return'][] =  array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $username, '*'),
        'msg' => 'verified_u2f_login'
      );
      return true;
    }
    catch (Exception $e) {
      $_SESSION['return'][] =  array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $username, '*'),
        'msg' => array('u2f_verification_failed', $e->getMessage())
      );
      $_SESSION['regReq'] = null;
      return false;
    }
    $_SESSION['return'][] =  array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $username, '*'),
      'msg' => array('u2f_verification_failed', 'unknown')
    );
    return false;
  break;
  case "hotp":
      return false;
  break;
  case "totp":
    try {
      $stmt = $pdo->prepare("SELECT `id`, `secret` FROM `tfa`
          WHERE `username` = :username
          AND `authmech` = 'totp'
          AND `active`='1'");
      $stmt->execute(array(':username' => $username));
      $row = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($tfa->verifyCode($row['secret'], $_POST['token']) === true) {
        $_SESSION['tfa_id'] = $row['id'];
        $_SESSION['return'][] =  array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $username, '*'),
          'msg' => 'verified_totp_login'
        );
        return true;
      }
      $_SESSION['return'][] =  array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $username, '*'),
        'msg' => 'totp_verification_failed'
      );
      return false;
    }
    catch (PDOException $e) {
      $_SESSION['return'][] =  array(
        'type' => 'danger',
        'log' => array(__FUNCTION__, $username, '*'),
        'msg' => array('mysql_error', $e)
      );
      return false;
    }
  break;
  default:
    $_SESSION['return'][] =  array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $username, '*'),
      'msg' => 'unknown_tfa_method'
    );
    return false;
  break;
	}
  return false;
}
function admin_api($action, $data = null) {
	global $pdo;
	global $lang;
	if ($_SESSION['mailcow_cc_role'] != "admin") {
		$_SESSION['return'][] =  array(
			'type' => 'danger',
      'log' => array(__FUNCTION__),
			'msg' => 'access_denied'
		);
		return false;
	}
	switch ($action) {
		case "edit":
      $regen_key = $data['admin_api_regen_key'];
      $active = (isset($data['active'])) ? 1 : 0;
      $skip_ip_check = (isset($data['skip_ip_check'])) ? 1 : 0;
      $allow_from = array_map('trim', preg_split( "/( |,|;|\n)/", $data['allow_from']));
      foreach ($allow_from as $key => $val) {
        if (empty($val)) {
          continue;
        }
        if (!filter_var($val, FILTER_VALIDATE_IP)) {
          $_SESSION['return'][] =  array(
            'type' => 'warning',
            'log' => array(__FUNCTION__, $data),
            'msg' => array('ip_invalid', htmlspecialchars($allow_from[$key]))
          );
          unset($allow_from[$key]);
          continue;
        }
      }
      $allow_from = implode(',', array_unique(array_filter($allow_from)));
      if (empty($allow_from) && $skip_ip_check == 0) {
        $_SESSION['return'][] =  array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $data),
          'msg' => 'ip_list_empty'
        );
        return false;
      }
      $api_key = implode('-', array(
        strtoupper(bin2hex(random_bytes(3))),
        strtoupper(bin2hex(random_bytes(3))),
        strtoupper(bin2hex(random_bytes(3))),
        strtoupper(bin2hex(random_bytes(3))),
        strtoupper(bin2hex(random_bytes(3)))
      ));
      $stmt = $pdo->query("SELECT `api_key` FROM `api`");
      $num_results = count($stmt->fetchAll(PDO::FETCH_ASSOC));
      if (empty($num_results)) {
        $stmt = $pdo->prepare("INSERT INTO `api` (`api_key`, `skip_ip_check`, `active`, `allow_from`)
          VALUES (:api_key, :skip_ip_check, :active, :allow_from);");
        $stmt->execute(array(
          ':api_key' => $api_key,
          ':skip_ip_check' => $skip_ip_check,
          ':active' => $active,
          ':allow_from' => $allow_from
        ));
      }
      else {
        if ($skip_ip_check == 0) {
          $stmt = $pdo->prepare("UPDATE `api` SET `skip_ip_check` = :skip_ip_check, `active` = :active, `allow_from` = :allow_from ;");
          $stmt->execute(array(
            ':active' => $active,
            ':skip_ip_check' => $skip_ip_check,
            ':allow_from' => $allow_from
          ));
        }
        else {
          $stmt = $pdo->prepare("UPDATE `api` SET `skip_ip_check` = :skip_ip_check, `active` = :active ;");
          $stmt->execute(array(
            ':active' => $active,
            ':skip_ip_check' => $skip_ip_check
          ));
        }
      }
    break;
    case "regen_key":
      $api_key = implode('-', array(
        strtoupper(bin2hex(random_bytes(3))),
        strtoupper(bin2hex(random_bytes(3))),
        strtoupper(bin2hex(random_bytes(3))),
        strtoupper(bin2hex(random_bytes(3))),
        strtoupper(bin2hex(random_bytes(3)))
      ));
      $stmt = $pdo->prepare("UPDATE `api` SET `api_key` = :api_key");
      $stmt->execute(array(
        ':api_key' => $api_key
      ));
    break;
    case "get":
      $stmt = $pdo->query("SELECT * FROM `api`");
      $apidata = $stmt->fetch(PDO::FETCH_ASSOC);
      return $apidata;
    break;
  }
	$_SESSION['return'][] =  array(
		'type' => 'success',
    'log' => array(__FUNCTION__, $data),
		'msg' => 'admin_api_modified'
	);
}
function license($action, $data = null) {
	global $pdo;
	global $redis;
	global $lang;
	if ($_SESSION['mailcow_cc_role'] != "admin") {
		$_SESSION['return'][] =  array(
			'type' => 'danger',
      'log' => array(__FUNCTION__),
			'msg' => 'access_denied'
		);
		return false;
	}
	switch ($action) {
		case "verify":
      // Keep result until revalidate button is pressed or session expired
      $stmt = $pdo->query("SELECT `version` FROM `versions` WHERE `application` = 'GUID'");
      $versions = $stmt->fetch(PDO::FETCH_ASSOC);
      $post = array('guid' => $versions['version']);
      $curl = curl_init('https://verify.mailcow.email');
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
      $response = curl_exec($curl);
      curl_close($curl);
      $json_return = json_decode($response, true);
      if ($response && $json_return) {
        if ($json_return['response'] === "ok") {
          $_SESSION['gal']['valid'] = "true";
          $_SESSION['gal']['c'] = $json_return['c'];
          $_SESSION['gal']['s'] = $json_return['s'];
          if ($json_return['m'] == 'NoMoore') {
            $_SESSION['gal']['m'] = '🐄';
          }
          else {
            $_SESSION['gal']['m'] = str_repeat('🐄', substr_count($json_return['m'], 'o'));
          }
        }
        elseif ($json_return['response'] === "invalid") {
          $_SESSION['gal']['valid'] = "false";
          $_SESSION['gal']['c'] = $lang['mailbox']['no'];
          $_SESSION['gal']['s'] = $lang['mailbox']['no'];
          $_SESSION['gal']['m'] = $lang['mailbox']['no'];
        }
      }
      else {
        $_SESSION['gal']['valid'] = "false";
        $_SESSION['gal']['c'] = $lang['danger']['temp_error'];
        $_SESSION['gal']['s'] = $lang['danger']['temp_error'];
        $_SESSION['gal']['m'] = $lang['danger']['temp_error'];
      }
      try {
        // json_encode needs "true"/"false" instead of true/false, to not encode it to 0 or 1
        $redis->Set('LICENSE_STATUS_CACHE', json_encode($_SESSION['gal']));
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
      return $_SESSION['gal']['valid'];
    break;
    case "guid":
      $stmt = $pdo->query("SELECT `version` FROM `versions` WHERE `application` = 'GUID'");
      $versions = $stmt->fetch(PDO::FETCH_ASSOC);
      return $versions['version'];
    break;
  }
}
function rspamd_ui($action, $data = null) {
	global $lang;
	if ($_SESSION['mailcow_cc_role'] != "admin") {
		$_SESSION['return'][] =  array(
			'type' => 'danger',
      'log' => array(__FUNCTION__),
			'msg' => 'access_denied'
		);
		return false;
	}
	switch ($action) {
		case "edit":
      $rspamd_ui_pass = $data['rspamd_ui_pass'];
      $rspamd_ui_pass2 = $data['rspamd_ui_pass2'];
      if (empty($rspamd_ui_pass) || empty($rspamd_ui_pass2)) {
        $_SESSION['return'][] =  array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, '*', '*'),
          'msg' => 'password_empty'
        );
        return false;
      }
      if ($rspamd_ui_pass != $rspamd_ui_pass2) {
        $_SESSION['return'][] =  array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, '*', '*'),
          'msg' => 'password_mismatch'
        );
        return false;
      }
      if (strlen($rspamd_ui_pass) < 6) {
        $_SESSION['return'][] =  array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, '*', '*'),
          'msg' => 'rspamd_ui_pw_length'
        );
        return false;
      }
      $docker_return = docker('post', 'rspamd-mailcow', 'exec', array('cmd' => 'rspamd', 'task' => 'worker_password', 'raw' => $rspamd_ui_pass), array('Content-Type: application/json'));
      if ($docker_return_array = json_decode($docker_return, true)) {
        if ($docker_return_array['type'] == 'success') {
          $_SESSION['return'][] =  array(
            'type' => 'success',
            'log' => array(__FUNCTION__, '*', '*'),
            'msg' => 'rspamd_ui_pw_set'
          );
          return true;
        }
        else {
          $_SESSION['return'][] =  array(
            'type' => $docker_return_array['type'],
            'log' => array(__FUNCTION__, '*', '*'),
            'msg' => $docker_return_array['msg']
          );
          return false;
        }
      }
      else {
        $_SESSION['return'][] =  array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, '*', '*'),
          'msg' => 'unknown'
        );
        return false;
      }
    break;
  }
}
function get_u2f_registrations($username) {
  global $pdo;
  $sel = $pdo->prepare("SELECT * FROM `tfa` WHERE `authmech` = 'u2f' AND `username` = ? AND `active` = '1'");
  $sel->execute(array($username));
  return $sel->fetchAll(PDO::FETCH_OBJ);
}
function get_logs($application, $lines = false) {
  if ($lines === false) {
    $lines = $GLOBALS['LOG_LINES'] - 1;
  }
  elseif(is_numeric($lines) && $lines >= 1) {
    $lines = abs(intval($lines) - 1);
  }
  else {
    list ($from, $to) = explode('-', $lines);
    $from = intval($from);
    $to = intval($to);
    if ($from < 1 || $to < $from) { return false; }
  }
	global $lang;
	global $redis;
	global $pdo;
	if ($_SESSION['mailcow_cc_role'] != "admin") {
		return false;
	}
  // SQL
  if ($application == "mailcow-ui") {
    if (isset($from) && isset($to)) {
      $stmt = $pdo->prepare("SELECT * FROM `logs` ORDER BY `id` DESC LIMIT :from, :to");
      $stmt->execute(array(
        ':from' => $from - 1,
        ':to' => $to
      ));
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    else {
      $stmt = $pdo->prepare("SELECT * FROM `logs` ORDER BY `id` DESC LIMIT :lines");
      $stmt->execute(array(
        ':lines' => $lines + 1,
      ));
      $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    if (is_array($data)) {
      return $data;
    }
  }
  // Redis
  if ($application == "dovecot-mailcow") {
    if (isset($from) && isset($to)) {
      $data = $redis->lRange('DOVECOT_MAILLOG', $from - 1, $to - 1);
    }
    else {
      $data = $redis->lRange('DOVECOT_MAILLOG', 0, $lines);
    }
    if ($data) {
      foreach ($data as $json_line) {
        $data_array[] = json_decode($json_line, true);
      }
      return $data_array;
    }
  }
  if ($application == "postfix-mailcow") {
    if (isset($from) && isset($to)) {
      $data = $redis->lRange('POSTFIX_MAILLOG', $from - 1, $to - 1);
    }
    else {
      $data = $redis->lRange('POSTFIX_MAILLOG', 0, $lines);
    }
    if ($data) {
      foreach ($data as $json_line) {
        $data_array[] = json_decode($json_line, true);
      }
      return $data_array;
    }
  }
  if ($application == "sogo-mailcow") {
    if (isset($from) && isset($to)) {
      $data = $redis->lRange('SOGO_LOG', $from - 1, $to - 1);
    }
    else {
      $data = $redis->lRange('SOGO_LOG', 0, $lines);
    }
    if ($data) {
      foreach ($data as $json_line) {
        $data_array[] = json_decode($json_line, true);
      }
      return $data_array;
    }
  }
  if ($application == "watchdog-mailcow") {
    if (isset($from) && isset($to)) {
      $data = $redis->lRange('WATCHDOG_LOG', $from - 1, $to - 1);
    }
    else {
      $data = $redis->lRange('WATCHDOG_LOG', 0, $lines);
    }
    if ($data) {
      foreach ($data as $json_line) {
        $data_array[] = json_decode($json_line, true);
      }
      return $data_array;
    }
  }
  if ($application == "acme-mailcow") {
    if (isset($from) && isset($to)) {
      $data = $redis->lRange('ACME_LOG', $from - 1, $to - 1);
    }
    else {
      $data = $redis->lRange('ACME_LOG', 0, $lines);
    }
    if ($data) {
      foreach ($data as $json_line) {
        $data_array[] = json_decode($json_line, true);
      }
      return $data_array;
    }
  }
  if ($application == "ratelimited") {
    if (isset($from) && isset($to)) {
      $data = $redis->lRange('RL_LOG', $from - 1, $to - 1);
    }
    else {
      $data = $redis->lRange('RL_LOG', 0, $lines);
    }
    if ($data) {
      foreach ($data as $json_line) {
        $data_array[] = json_decode($json_line, true);
      }
      return $data_array;
    }
  }
  if ($application == "api-mailcow") {
    if (isset($from) && isset($to)) {
      $data = $redis->lRange('API_LOG', $from - 1, $to - 1);
    }
    else {
      $data = $redis->lRange('API_LOG', 0, $lines);
    }
    if ($data) {
      foreach ($data as $json_line) {
        $data_array[] = json_decode($json_line, true);
      }
      return $data_array;
    }
  }
  if ($application == "netfilter-mailcow") {
    if (isset($from) && isset($to)) {
      $data = $redis->lRange('NETFILTER_LOG', $from - 1, $to - 1);
    }
    else {
      $data = $redis->lRange('NETFILTER_LOG', 0, $lines);
    }
    if ($data) {
      foreach ($data as $json_line) {
        $data_array[] = json_decode($json_line, true);
      }
      return $data_array;
    }
  }
  if ($application == "autodiscover-mailcow") {
    if (isset($from) && isset($to)) {
      $data = $redis->lRange('AUTODISCOVER_LOG', $from - 1, $to - 1);
    }
    else {
      $data = $redis->lRange('AUTODISCOVER_LOG', 0, $lines);
    }
    if ($data) {
      foreach ($data as $json_line) {
        $data_array[] = json_decode($json_line, true);
      }
      return $data_array;
    }
  }
  if ($application == "rspamd-history") {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_UNIX_SOCKET_PATH, '/var/lib/rspamd/rspamd.sock');
    if (!is_numeric($lines)) {
      list ($from, $to) = explode('-', $lines);
      curl_setopt($curl, CURLOPT_URL,"http://rspamd/history?from=" . intval($from) . "&to=" . intval($to));
    }
    else {
      curl_setopt($curl, CURLOPT_URL,"http://rspamd/history?to=" . intval($lines));
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $history = curl_exec($curl);
    if (!curl_errno($curl)) {
      $data_array = json_decode($history, true);
      curl_close($curl);
      return $data_array['rows'];
    }
    curl_close($curl);
    return false;
  }
  return false;
}
function getGUID() {
  if (function_exists('com_create_guid')) {
    return com_create_guid();
  }
  mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
  $charid = strtoupper(md5(uniqid(rand(), true)));
  $hyphen = chr(45);// "-"
  return substr($charid, 0, 8).$hyphen
        .substr($charid, 8, 4).$hyphen
        .substr($charid,12, 4).$hyphen
        .substr($charid,16, 4).$hyphen
        .substr($charid,20,12);
}
function solr_status() {
  $curl = curl_init();
  $endpoint = 'http://solr:8983/solr/admin/cores';
  $params = array(
    'action' => 'STATUS',
    'core' => 'dovecot-fts',
    'indexInfo' => 'true'
  );
  $url = $endpoint . '?' . http_build_query($params);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curl, CURLOPT_POST, 0);
  curl_setopt($curl, CURLOPT_TIMEOUT, 10);
  $response_core = curl_exec($curl);
  if ($response_core === false) {
    $err = curl_error($curl);
    curl_close($curl);
    return false;
  }
  else {
    curl_close($curl);
    $curl = curl_init();
    $status_core = json_decode($response_core, true);
    $url = 'http://solr:8983/solr/admin/info/system';
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 0);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    $response_sysinfo = curl_exec($curl);
    if ($response_sysinfo === false) {
      $err = curl_error($curl);
      curl_close($curl);
      return false;
    }
    else {
      curl_close($curl);
      $status_sysinfo = json_decode($response_sysinfo, true);
      $status = array_merge($status_core, $status_sysinfo);
      return (!empty($status['status']['dovecot-fts']) && !empty($status['jvm']['memory'])) ? $status : false;
    }
    return (!empty($status['status']['dovecot-fts'])) ? $status['status']['dovecot-fts'] : false;
  }
  return false;
}

function cleanupJS($ignore = '', $folder = '/tmp/*.js') {
    foreach (glob($folder) as $filename) {
        if(strpos($filename, $ignore) !== false) {
            continue;
        }
        unlink($filename);
    }
}

function cleanupCSS($ignore = '', $folder = '/tmp/*.css') {
    foreach (glob($folder) as $filename) {
        if(strpos($filename, $ignore) !== false) {
            continue;
        }
        unlink($filename);
    }
}
?>

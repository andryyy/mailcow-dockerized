<?php

function dkim($_action, $_data = null, $privkey = false) {
	global $redis;
	global $lang;
  switch ($_action) {
    case 'add':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data, ),
          'msg' => 'access_denied'
        );
        return false;
      }
      $key_length	= intval($_data['key_size']);
      $dkim_selector = (isset($_data['dkim_selector'])) ? $_data['dkim_selector'] : 'dkim';
      $domains = array_map('trim', preg_split( "/( |,|;|\n)/", $_data['domains']));
      $domains = array_filter($domains);
      foreach ($domains as $domain) {
        if (!is_valid_domain_name($domain) || !is_numeric($key_length)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => array('dkim_domain_or_sel_invalid', $domain)
          );
          continue;
        }
        if ($redis->hGet('DKIM_PUB_KEYS', $domain)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => array('dkim_domain_or_sel_invalid', $domain)
          );
          continue;
        }
        if (!ctype_alnum(str_replace(['-', '_'], '', $dkim_selector))) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => array('dkim_domain_or_sel_invalid', $domain)
          );
          continue;
        }
        $config = array(
          "digest_alg" => "sha256",
          "private_key_bits" => $key_length,
          "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        if ($keypair_ressource = openssl_pkey_new($config)) {
          $key_details = openssl_pkey_get_details($keypair_ressource);
          $pubKey = implode(array_slice(
            array_filter(
              explode(PHP_EOL, $key_details['key'])
            ), 1, -1)
          );
          // Save public key and selector to redis
          try {
            $redis->hSet('DKIM_PUB_KEYS', $domain, $pubKey);
            $redis->hSet('DKIM_SELECTORS', $domain, $dkim_selector);
          }
          catch (RedisException $e) {
            $_SESSION['return'][] = array(
              'type' => 'danger',
              'log' => array(__FUNCTION__, $_action, $_data),
              'msg' => array('redis_error', $e)
            );
            continue;
          }
          // Export private key and save private key to redis
          openssl_pkey_export($keypair_ressource, $privKey);
          if (isset($privKey) && !empty($privKey)) {
            try {
              $redis->hSet('DKIM_PRIV_KEYS', $dkim_selector . '.' . $domain, trim($privKey));
            }
            catch (RedisException $e) {
              $_SESSION['return'][] = array(
                'type' => 'danger',
                'log' => array(__FUNCTION__, $_action, $_data),
                'msg' => array('redis_error', $e)
              );
              continue;
            }
          }
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => array('dkim_added', $domain)
          );
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => array('dkim_domain_or_sel_invalid', $domain)
          );
          continue;
        }
      }
    break;
    case 'duplicate':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => 'access_denied'
        );
        return false;
      }
      $from_domain = $_data['from_domain'];
      $from_domain_dkim = dkim('details', $from_domain, true);
      if (empty($from_domain_dkim)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => array('dkim_domain_or_sel_invalid', $from_domain)
        );
        continue;
      }
      $to_domains = (array)$_data['to_domain'];
      $to_domains = array_filter($to_domains);
      foreach ($to_domains as $to_domain) {
        try {
          $redis->hSet('DKIM_PUB_KEYS', $to_domain, $from_domain_dkim['pubkey']);
          $redis->hSet('DKIM_SELECTORS', $to_domain, $from_domain_dkim['dkim_selector']);
          $redis->hSet('DKIM_PRIV_KEYS', $from_domain_dkim['dkim_selector'] . '.' . $to_domain, base64_decode(trim($from_domain_dkim['privkey'])));
        }
        catch (RedisException $e) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => array('redis_error', $e)
          );
          continue;
        }
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => array('dkim_duplicated', $from_domain, $to_domain)
        );
      }
    break;
    case 'import':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => 'access_denied'
        );
        return false;
      }
      $private_key_input = trim($_data['private_key_file']);
      $overwrite_existing = intval($_data['overwrite_existing']);
      $private_key_normalized = preg_replace('~\r\n?~', "\n", $private_key_input);
      $private_key = openssl_pkey_get_private($private_key_normalized);
      if ($ssl_error = openssl_error_string()) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => array('private_key_error', $ssl_error)
        );
        return false;
      }
      // Explode by nl
      $pem_public_key_array = explode(PHP_EOL, trim(openssl_pkey_get_details($private_key)['key']));
      // Remove first and last line/item
      array_shift($pem_public_key_array);
      array_pop($pem_public_key_array);
      // Implode as single string
      $pem_public_key = implode('', $pem_public_key_array);
      $dkim_selector = (isset($_data['dkim_selector'])) ? $_data['dkim_selector'] : 'dkim';
      $domain	= $_data['domain'];
      if (!is_valid_domain_name($domain)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => array('dkim_domain_or_sel_invalid', $domain)
        );
        return false;
      }
      if ($redis->hGet('DKIM_PUB_KEYS', $domain)) {
        if ($overwrite_existing == 0) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => array('dkim_domain_or_sel_exists', $domain)
          );
          return false;
        }
      }
      if (!ctype_alnum($dkim_selector)) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => array('dkim_domain_or_sel_invalid', $domain)
        );
        return false;
      }
      try {
        dkim('delete', $domain);
        $redis->hSet('DKIM_PUB_KEYS', $domain, $pem_public_key);
        $redis->hSet('DKIM_SELECTORS', $domain, $dkim_selector);
        $redis->hSet('DKIM_PRIV_KEYS', $dkim_selector . '.' . $domain, $private_key_normalized);
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
      unset($private_key_normalized);
      unset($private_key);
      unset($private_key_input);
      try {
      }
      catch (RedisException $e) {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => array('redis_error', $e)
        );
        return false;
      }
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data),
        'msg' => array('dkim_added', $domain)
      );
      return true;
    break;
    case 'details':
      if (!hasDomainAccess($_SESSION['mailcow_cc_username'], $_SESSION['mailcow_cc_role'], $_data) && $_SESSION['mailcow_cc_role'] != "admin") {
        return false;
      }
      $dkimdata = array();
      if ($redis_dkim_key_data = $redis->hGet('DKIM_PUB_KEYS', $_data)) {
        $dkimdata['pubkey'] = $redis_dkim_key_data;
        if (strlen($dkimdata['pubkey']) < 391) {
          $dkimdata['length'] = "1024";
        }
        elseif (strlen($dkimdata['pubkey']) < 736) {
          $dkimdata['length'] = "2048";
        }
        elseif (strlen($dkimdata['pubkey']) < 1416) {
          $dkimdata['length'] = "4096";
        }
        else {
          $dkimdata['length'] = ">= 8192";
        }
        if ($GLOBALS['SPLIT_DKIM_255'] === true) {
          $dkim_txt_tmp = str_split('v=DKIM1;k=rsa;t=s;s=email;p=' . $redis_dkim_key_data, 255);
          $dkimdata['dkim_txt'] = sprintf('"%s"', implode('" "', $dkim_txt_tmp ) );
        }
        else {
          $dkimdata['dkim_txt'] = 'v=DKIM1;k=rsa;t=s;s=email;p=' . $redis_dkim_key_data;
        }
        $dkimdata['dkim_selector'] = $redis->hGet('DKIM_SELECTORS', $_data);
        if ($GLOBALS['SHOW_DKIM_PRIV_KEYS'] || $privkey == true) {
          $dkimdata['privkey'] = base64_encode($redis->hGet('DKIM_PRIV_KEYS', $dkimdata['dkim_selector'] . '.' . $_data));
        }
        else {
          $dkimdata['privkey'] = '';
        }
      }
      return $dkimdata;
    break;
    case 'blind':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => 'access_denied'
        );
        return false;
      }
      $blinddkim = array();
      foreach ($redis->hKeys('DKIM_PUB_KEYS') as $redis_dkim_domain) {
        $blinddkim[] = $redis_dkim_domain;
      }
      return array_diff($blinddkim, array_merge(mailbox('get', 'domains'), mailbox('get', 'alias_domains')));
    break;
    case 'delete':
      $domains = (array)$_data['domains'];
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => 'access_denied'
        );
        return false;
      }
      foreach ($domains as $domain) {
        if (!is_valid_domain_name($domain)) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => array('dkim_domain_or_sel_invalid', $domain)
          );
          continue;
        }
        try {
          $selector = $redis->hGet('DKIM_SELECTORS', $domain);
          $redis->hDel('DKIM_PUB_KEYS', $domain);
          $redis->hDel('DKIM_PRIV_KEYS', $selector . '.' . $domain);
          $redis->hDel('DKIM_SELECTORS', $domain);
        }
        catch (RedisException $e) {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data),
            'msg' => array('redis_error', $e)
          );
          continue;
        }
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data),
          'msg' => array('dkim_removed', htmlspecialchars($domain))
        );
      }
    break;
  }
}

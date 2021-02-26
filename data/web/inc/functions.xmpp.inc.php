<?php
function xmpp_control($_action, $_data = null) {
	global $lang;
  $_data_log = $_data;
  switch ($_action) {
    case 'reload':
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
      curl_setopt($curl, CURLOPT_URL, 'http://ejabberd:5280/api/reload_config');
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      $response = curl_exec($curl);
      curl_close($curl);

      if ($response === "0") {
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'xmpp_reloaded'
        );
      }
      else {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'xmpp_reload_failed'
        );
      }
    break;
    case 'restart':
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
      curl_setopt($curl, CURLOPT_URL, 'http://ejabberd:5280/api/restart');
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      $response = curl_exec($curl);
      curl_close($curl);

      if ($response === "0") {
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'xmpp_restarted'
        );
      }
      else {
        // If no host is available, the container might be in sleeping state, we need to restart the container
        $response = json_decode(docker('post', 'ejabberd-mailcow', 'restart'), true);
        if (isset($response['type']) && $response['type'] == "success") {
          $_SESSION['return'][] = array(
            'type' => 'success',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => 'xmpp_restarted'
          );
        }
        else {
          $_SESSION['return'][] = array(
            'type' => 'danger',
            'log' => array(__FUNCTION__, $_action, $_data_log),
            'msg' => 'xmpp_restart_failed'
          );
        }
      }
    break;
    case 'status':
      if ($_SESSION['mailcow_cc_role'] != "admin") {
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'access_denied'
        );
        return false;
      }
      foreach (array(
          'onlineusers' => 'stats?name=onlineusers',
          'uptimeseconds' => 'stats?name=uptimeseconds',
          'muc_online_rooms' => 'muc_online_rooms?service=global'
        ) as $stat => $url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_URL, 'http://ejabberd:5280/api/' . $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response_json = json_decode(curl_exec($curl), true);
        if (isset($response_json['stat'])) {
          $response_data[$stat] = $response_json['stat'];
        }
        else {
          $response_data[$stat] = $response_json;
        }
        curl_close($curl);
        // Something went wrong
        if ($response_data[$stat] === false) {
          $response_data[$stat] = '?';
        }
      }
      return $response_data;
    break;
  }
}
function xmpp_rebuild_configs() {
	global $pdo;
	global $lang;
  $_data_log = $_data;

  try {
    $xmpp_domains = array();
    $stmt = $pdo->query('SELECT CONCAT(`xmpp_prefix`, ".", `domain`) AS `xmpp_host`, `domain` FROM `domain` WHERE `xmpp` = 1');
    $xmpp_domain_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($xmpp_domain_rows as $xmpp_domain_row) {
      $xmpp_domains[$xmpp_domain_row['domain']] = array('xmpp_host' => $xmpp_domain_row['xmpp_host']);
      $stmt = $pdo->query('SELECT CONCAT(`local_part`, "@", CONCAT(`domain`.`xmpp_prefix`, ".", `domain`.`domain`)) AS `xmpp_username` FROM `mailbox`
        JOIN `domain`
          WHERE `domain`.`xmpp` = 1
            AND JSON_VALUE(`attributes`, "$.xmpp_admin") = 1');
      $xmpp_admin_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($xmpp_admin_rows as $xmpp_admin_row) {
        $xmpp_domains[$xmpp_domain_row['domain']]['xmpp_admins'][] = $xmpp_admin_row['xmpp_username'];
      }
    }

    touch('/ejabberd/ejabberd_hosts.yml');
    touch('/ejabberd/ejabberd_acl.yml');
    touch('/etc/nginx/conf.d/ZZZ-ejabberd.conf');
    $ejabberd_hosts_md5 = md5_file('/ejabberd/ejabberd_hosts.yml');
    $ejabberd_acl_md5 = md5_file('/ejabberd/ejabberd_acl.yml');
    $ejabberd_site_md5 = md5_file('/etc/nginx/conf.d/ZZZ-ejabberd.conf');

    if (!empty($xmpp_domains)) {
      // Handle hosts file
      $hosts_handle = fopen('/ejabberd/ejabberd_hosts.yml', 'w');
      if (!$hosts_handle) {
        throw new Exception($lang['danger']['file_open_error']);
      }
      fwrite($hosts_handle, '# Autogenerated by mailcow' . PHP_EOL);
      fwrite($hosts_handle, 'hosts:' . PHP_EOL);
      foreach ($xmpp_domains as $domain => $domain_values) {
        fwrite($hosts_handle, '  - ' . $xmpp_domains[$domain]['xmpp_host'] . PHP_EOL);
      }
      fclose($hosts_handle);

      // Handle ACL file
      $acl_handle = fopen('/ejabberd/ejabberd_acl.yml', 'w');
      if (!$acl_handle) {
        throw new Exception($lang['danger']['file_open_error']);
      }
      fwrite($acl_handle, '# Autogenerated by mailcow' . PHP_EOL);
      fwrite($acl_handle, 'append_host_config:' . PHP_EOL);
      foreach ($xmpp_domains as $domain => $domain_values) {
        fwrite($acl_handle, '  ' . $xmpp_domains[$domain]['xmpp_host'] . ':' . PHP_EOL);
        fwrite($acl_handle, '    acl:' . PHP_EOL);
        fwrite($acl_handle, '      admin:' . PHP_EOL);
        fwrite($acl_handle, '        user:' . PHP_EOL);
        foreach ($xmpp_domains[$domain]['xmpp_admins'] as $xmpp_admin) {
          fwrite($acl_handle, '          - ' . $xmpp_admin . PHP_EOL);
        }
      }
      fclose($acl_handle);

      // Handle Nginx site
      $site_handle = @fopen('/etc/nginx/conf.d/ZZZ-ejabberd.conf', 'r+');
      if ($site_handle !== false) {
        ftruncate($site_handle, 0);
        fclose($site_handle);
      }
      $site_handle = fopen('/etc/nginx/conf.d/ZZZ-ejabberd.conf', 'w');
      if (!$site_handle) {
        throw new Exception($lang['danger']['file_open_error']);
      }
      fwrite($site_handle, '# Autogenerated by mailcow' . PHP_EOL);
      foreach ($xmpp_domains as $domain => $domain_values) {
        $site_config = <<<EOF
server {
  root /web;

  include /etc/nginx/conf.d/listen_ssl.active;
  include /etc/nginx/conf.d/listen_plain.active;

  ssl_certificate /etc/ssl/mail/cert.pem;
  ssl_certificate_key /etc/ssl/mail/key.pem;

  server_name %s conference.%s proxy.%s pubsub.%s upload.%s;

  if (\$request_uri ~* "%%0A|%%0D") {
    return 403;
  }

  set_real_ip_from 10.0.0.0/8;
  set_real_ip_from 172.16.0.0/12;
  set_real_ip_from 192.168.0.0/16;
  set_real_ip_from fc00::/7;
  real_ip_header X-Forwarded-For;
  real_ip_recursive on;

  location / {
    proxy_pass http://ejabberd:5281/;
    proxy_set_header Host \$http_host;
    proxy_set_header X-Forwarded-For \$proxy_add_x_forwarded_for;
    proxy_set_header X-Real-IP \$remote_addr;
    proxy_redirect off;
  }
}

EOF;
        fwrite($site_handle, sprintf($site_config,
          $xmpp_domains[$domain]['xmpp_host'],
          $xmpp_domains[$domain]['xmpp_host'],
          $xmpp_domains[$domain]['xmpp_host'],
          $xmpp_domains[$domain]['xmpp_host'],
          $xmpp_domains[$domain]['xmpp_host']
        ));
      }
      fclose($site_handle);
    }
    else {
      // Write empty hosts file
      $hosts_handle = fopen('/ejabberd/ejabberd_hosts.yml', 'w');
      if (!$hosts_handle) {
        throw new Exception($lang['danger']['file_open_error']);
      }
      fwrite($hosts_handle, '# Autogenerated by mailcow' . PHP_EOL);
      fclose($hosts_handle);

      // Write empty ACL file
      $acl_handle = fopen('/ejabberd/ejabberd_acl.yml', 'w');
      if (!$acl_handle) {
        throw new Exception($lang['danger']['file_open_error']);
      }
      fwrite($acl_handle, '# Autogenerated by mailcow' . PHP_EOL);
      fclose($acl_handle);

      // Write empty Nginx site
      $acl_handle = fopen('/etc/nginx/conf.d/ZZZ-ejabberd.conf', 'w');
      if (!$acl_handle) {
        throw new Exception($lang['danger']['file_open_error']);
      }
      fwrite($acl_handle, '# Autogenerated by mailcow' . PHP_EOL);
      fclose($acl_handle);
    }

    if (md5_file('/ejabberd/ejabberd_acl.yml') != $ejabberd_acl_md5) {
      xmpp_control('restart');
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => 'xmpp_maps_updated'
      );
    }
    elseif (md5_file('/ejabberd/ejabberd_hosts.yml') != $ejabberd_hosts_md5) {
      xmpp_control('reload');
      $_SESSION['return'][] = array(
        'type' => 'success',
        'log' => array(__FUNCTION__, $_action, $_data_log),
        'msg' => 'xmpp_maps_updated'
      );
    }

    if (md5_file('/etc/nginx/conf.d/ZZZ-ejabberd.conf') != $ejabberd_site_md5) {
      $response = json_decode(docker('post', 'nginx-mailcow', 'exec', array("cmd" => "reload", "task" => "nginx"), 'Content-type: application/json'), true);
      if (isset($response['type']) && $response['type'] == "success") {
        $_SESSION['return'][] = array(
          'type' => 'success',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => 'nginx_reloaded'
        );
      }
      else {
        if (!empty($response['msg'])) {
          $error = $response['msg'];
        }
        else {
          $error = '-';
        }
        $_SESSION['return'][] = array(
          'type' => 'danger',
          'log' => array(__FUNCTION__, $_action, $_data_log),
          'msg' => array('nginx_reload_failed', htmlspecialchars($error))
        );
      }
    }
  }
  catch (Exception $e) {
    $_SESSION['return'][] = array(
      'type' => 'danger',
      'log' => array(__FUNCTION__, $_action, $_data_log),
      'msg' => array('xmpp_map_write_error', htmlspecialchars($e->getMessage()))
    );
  }
}


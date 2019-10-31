<?php
/*
edit/alias => POST data:
  {
    address: {a, b, c},   (where a, b, c represent alias addresses)
    active: 1             (0 or 1)
  }

delete/alias => POST data:
  {
    address: {a, b, c},   (where a, b, c represent alias addresses)
  }

*/
header('Content-Type: application/json');
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';
error_reporting(0);

function api_log($_data) {
  global $redis;
  $data_var = array();
  foreach ($_data as $data => &$value) {
    if ($data == 'csrf_token') {
      continue;
    }
    if ($value = json_decode($value, true)) {
      unset($value["csrf_token"]);
      foreach ($value as $key => &$val) {
        if(preg_match("/pass/i", $key)) {
          $val = '*';
        }
      }
      $value = json_encode($value);
    }
    $data_var[] = $data . "='" . $value . "'";
  }
  try {
    $log_line = array(
      'time' => time(),
      'uri' => $_SERVER['REQUEST_URI'],
      'method' => $_SERVER['REQUEST_METHOD'],
      'remote' => get_remote_ip(),
      'data' => implode(', ', $data_var)
    );
    $redis->lPush('API_LOG', json_encode($log_line));
  }
  catch (RedisException $e) {
    $_SESSION['return'][] = array(
      'type' => 'danger',
      'msg' => 'Redis: '.$e
    );
    return false;
  }
}

if (isset($_SESSION['mailcow_cc_role']) || isset($_SESSION['pending_mailcow_cc_username'])) {
  if (isset($_GET['query'])) {

    $query = explode('/', $_GET['query']);
    $action =     (isset($query[0])) ? $query[0] : null;
    $category =   (isset($query[1])) ? $query[1] : null;
    $object =     (isset($query[2])) ? $query[2] : null;
    $extra =      (isset($query[3])) ? $query[3] : null;

    // accept json in request body
    if($_SERVER['HTTP_CONTENT_TYPE'] === 'application/json') {
      $request = file_get_contents('php://input');
      $requestDecoded = json_decode($request, true);

      // check for valid json
      if ($action != 'get' && $requestDecoded === null) {
        http_response_code(400);
        echo json_encode(array(
            'type' => 'error',
            'msg' => 'Request body doesn\'t contain valid json!'
        ));
        exit;
      }

      // add
      if ($action == 'add') {
        $_POST['attr'] = $request;
      }

      // edit
      if ($action == 'edit') {
        $_POST['attr']  = json_encode($requestDecoded['attr']);
        $_POST['items'] = json_encode($requestDecoded['items']);
      }

      // delete
      if ($action == 'delete') {
        $_POST['items'] = $request;
      }

    }
    api_log($_POST);

    $request_incomplete = json_encode(array(
      'type' => 'error',
      'msg' => 'Cannot find attributes in post data'
    ));

    switch ($action) {
      case "add":
        function process_add_return($return) {
          $generic_failure = json_encode(array(
            'type' => 'error',
            'msg' => 'Cannot add item'
          ));
          $generic_success = json_encode(array(
            'type' => 'success',
            'msg' => 'Task completed'
          ));
          if ($return === false) {
            echo isset($_SESSION['return']) ? json_encode($_SESSION['return']) : $generic_failure;
          }
          else {
            echo isset($_SESSION['return']) ? json_encode($_SESSION['return']) : $generic_success;
          }
        }
        if (!isset($_POST['attr'])) {
          echo $request_incomplete;
          exit;
        }
        else {
          $attr = (array)json_decode($_POST['attr'], true);
          unset($attr['csrf_token']);
        }
        // only allow POST requests to POST API endpoints
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
          http_response_code(405);
          echo json_encode(array(
              'type' => 'error',
              'msg' => 'only POST method is allowed'
          ));
          exit();
        }
        switch ($category) {
          case "time_limited_alias":
            process_add_return(mailbox('add', 'time_limited_alias', $attr));
          break;
          case "relayhost":
            process_add_return(relayhost('add', $attr));
          break;
          case "transport":
            process_add_return(transport('add', $attr));
          break;
          case "rsetting":
            process_add_return(rsettings('add', $attr));
          break;
          case "mailbox":
            process_add_return(mailbox('add', 'mailbox', $attr));
          break;
          case "oauth2-client":
            process_add_return(oauth2('add', 'client', $attr));
          break;
          case "domain":
            process_add_return(mailbox('add', 'domain', $attr));
          break;
          case "resource":
            process_add_return(mailbox('add', 'resource', $attr));
          break;
          case "alias":
            process_add_return(mailbox('add', 'alias', $attr));
          break;
          case "filter":
            process_add_return(mailbox('add', 'filter', $attr));
          break;
          case "domain-policy":
            process_add_return(policy('add', 'domain', $attr));
          break;
          case "mailbox-policy":
            process_add_return(policy('add', 'mailbox', $attr));
          break;
          case "alias-domain":
            process_add_return(mailbox('add', 'alias_domain', $attr));
          break;
          case "fwdhost":
            process_add_return(fwdhost('add', $attr));
          break;
          case "dkim":
            process_add_return(dkim('add', $attr));
          break;
          case "dkim_duplicate":
            process_add_return(dkim('duplicate', $attr));
          break;
          case "dkim_import":
            process_add_return(dkim('import', $attr));
          break;
          case "domain-admin":
            process_add_return(domain_admin('add', $attr));
          break;
          case "admin":
            process_add_return(admin('add', $attr));
          break;
          case "syncjob":
            process_add_return(mailbox('add', 'syncjob', $attr));
          break;
          case "bcc":
            process_add_return(bcc('add', $attr));
          break;
          case "recipient_map":
            process_add_return(recipient_map('add', $attr));
          break;
          case "tls-policy-map":
            process_add_return(tls_policy_maps('add', $attr));
          break;
          // return no route found if no case is matched
          default:
            http_response_code(404);
            echo json_encode(array(
              'type' => 'error',
              'msg' => 'route not found'
            ));
            exit();
        }
      break;
      case "get":
        function process_get_return($data) {
          echo (!isset($data) || empty($data)) ? '{}' : json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        // only allow GET requests to GET API endpoints
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
          http_response_code(405);
          echo json_encode(array(
              'type' => 'error',
              'msg' => 'only GET method is allowed'
          ));
          exit();
        }
        switch ($category) {
          case "rspamd":
            switch ($object) {
              case "actions":
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
                  echo json_encode($return, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                }
                elseif (!isset($data) || empty($data)) {
                  echo '{}';
                }
              break;
            }
          break;

          case "domain":
            switch ($object) {
              case "all":
                $domains = mailbox('get', 'domains');
                if (!empty($domains)) {
                  foreach ($domains as $domain) {
                    if ($details = mailbox('get', 'domain_details', $domain)) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $data = mailbox('get', 'domain_details', $object);
                process_get_return($data);
              break;
            }
          break;

          case "mailq":
            switch ($object) {
              case "all":
                $mailq = mailq('get');
                if (!empty($mailq)) {
                  echo $mailq;
                }
                else {
                  echo '{}';
                }
              break;
            }
          break;

          case "rl-domain":
            switch ($object) {
              case "all":
                $domains = array_merge(mailbox('get', 'domains'), mailbox('get', 'alias_domains'));
                if (!empty($domains)) {
                  foreach ($domains as $domain) {
                    if ($details = ratelimit('get', 'domain', $domain)) {
                      $details['domain'] = $domain;
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $data = ratelimit('get', 'domain', $object);
                process_get_return($data);
              break;
            }
          break;

          case "rl-mbox":
            switch ($object) {
              case "all":
                $domains = mailbox('get', 'domains');
                if (!empty($domains)) {
                  foreach ($domains as $domain) {
                    $mailboxes = mailbox('get', 'mailboxes', $domain);
                    if (!empty($mailboxes)) {
                      foreach ($mailboxes as $mailbox) {
                        if ($details = ratelimit('get', 'mailbox', $mailbox)) {
                          $details['mailbox'] = $mailbox;
                          $data[] = $details;
                        }
                        else {
                          continue;
                        }
                      }
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $data = ratelimit('get', 'mailbox', $object);
                process_get_return($data);
              break;
            }
          break;

          case "relayhost":
            switch ($object) {
              case "all":
                $relayhosts = relayhost('get');
                if (!empty($relayhosts)) {
                  foreach ($relayhosts as $relayhost) {
                    if ($details = relayhost('details', $relayhost['id'])) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $data = relayhost('details', $object);
                process_get_return($data);
              break;
            }
          break;

          case "transport":
            switch ($object) {
              case "all":
                $transports = transport('get');
                if (!empty($transports)) {
                  foreach ($transports as $transport) {
                    if ($details = transport('details', $transport['id'])) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $data = transport('details', $object);
                process_get_return($data);
              break;
            }
          break;

          case "rsetting":
            switch ($object) {
              case "all":
                $rsettings = rsettings('get');
                if (!empty($rsettings)) {
                  foreach ($rsettings as $rsetting) {
                    if ($details = rsettings('details', $rsetting['id'])) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $data = rsetting('details', $object);
                process_get_return($data);
              break;
            }
          break;

          case "oauth2-client":
            switch ($object) {
              case "all":
                $clients = oauth2('get', 'clients');
                if (!empty($clients)) {
                  foreach ($clients as $client) {
                    if ($details = oauth2('details', 'client', $client)) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $data = oauth2('details', 'client', $object);
                process_get_return($data);
              break;
            }
          break;

          case "logs":
            switch ($object) {
              case "dovecot":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('dovecot-mailcow', $extra);
                }
                else {
                  $logs = get_logs('dovecot-mailcow');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "ratelimited":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('ratelimited', $extra);
                }
                else {
                  $logs = get_logs('ratelimited');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "netfilter":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('netfilter-mailcow', $extra);
                }
                else {
                  $logs = get_logs('netfilter-mailcow');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "postfix":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('postfix-mailcow', $extra);
                }
                else {
                  $logs = get_logs('postfix-mailcow');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "autodiscover":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('autodiscover-mailcow', $extra);
                }
                else {
                  $logs = get_logs('autodiscover-mailcow');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "sogo":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('sogo-mailcow', $extra);
                }
                else {
                  $logs = get_logs('sogo-mailcow');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "ui":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('mailcow-ui', $extra);
                }
                else {
                  $logs = get_logs('mailcow-ui');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "watchdog":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('watchdog-mailcow', $extra);
                }
                else {
                  $logs = get_logs('watchdog-mailcow');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "acme":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('acme-mailcow', $extra);
                }
                else {
                  $logs = get_logs('acme-mailcow');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "api":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('api-mailcow', $extra);
                }
                else {
                  $logs = get_logs('api-mailcow');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              case "rspamd-history":
                // 0 is first record, so empty is fine
                if (isset($extra)) {
                  $extra = preg_replace('/[^\d\-]/i', '', $extra);
                  $logs = get_logs('rspamd-history', $extra);
                }
                else {
                  $logs = get_logs('rspamd-history');
                }
                echo (isset($logs) && !empty($logs)) ? json_encode($logs, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) : '{}';
              break;
              // return no route found if no case is matched
              default:
                http_response_code(404);
                echo json_encode(array(
                  'type' => 'error',
                  'msg' => 'route not found'
                ));
                exit();
            }
          break;
          case "mailbox":
            switch ($object) {
              case "all":
                $domains = mailbox('get', 'domains');
                if (!empty($domains)) {
                  foreach ($domains as $domain) {
                    $mailboxes = mailbox('get', 'mailboxes', $domain);
                    if (!empty($mailboxes)) {
                      foreach ($mailboxes as $mailbox) {
                        if ($details = mailbox('get', 'mailbox_details', $mailbox)) {
                          $data[] = $details;
                        }
                        else {
                          continue;
                        }
                      }
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $data = mailbox('get', 'mailbox_details', $object);
                process_get_return($data);
              break;
            }
          break;
          case "syncjobs":
            switch ($object) {
              case "all":
                $domains = mailbox('get', 'domains');
                if (!empty($domains)) {
                  foreach ($domains as $domain) {
                    $mailboxes = mailbox('get', 'mailboxes', $domain);
                    if (!empty($mailboxes)) {
                      foreach ($mailboxes as $mailbox) {
                        $syncjobs = mailbox('get', 'syncjobs', $mailbox);
                        if (!empty($syncjobs)) {
                          foreach ($syncjobs as $syncjob) {
                            if (isset($extra)) {
                              $details = mailbox('get', 'syncjob_details', $syncjob, explode(',', $extra));
                            }
                            else {
                              $details = mailbox('get', 'syncjob_details', $syncjob);
                            }
                            if ($details) {
                              $data[] = $details;
                            }
                            else {
                              continue;
                            }
                          }
                        }
                      }
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $syncjobs = mailbox('get', 'syncjobs', $object);
                if (!empty($syncjobs)) {
                  foreach ($syncjobs as $syncjob) {
                    if (isset($extra)) {
                      $details = mailbox('get', 'syncjob_details', $syncjob, explode(',', $extra));
                    }
                    else {
                      $details = mailbox('get', 'syncjob_details', $syncjob);
                    }
                    if ($details) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                }
                process_get_return($data);
              break;
            }
          break;
          case "active-user-sieve":
            if (isset($object)) {
              $sieve_filter = mailbox('get', 'active_user_sieve', $object);
              if (!empty($sieve_filter)) {
                $data[] = $sieve_filter;
              }
            }
            process_get_return($data);
          break;
          case "filters":
            switch ($object) {
              case "all":
                $domains = mailbox('get', 'domains');
                if (!empty($domains)) {
                  foreach ($domains as $domain) {
                    $mailboxes = mailbox('get', 'mailboxes', $domain);
                    if (!empty($mailboxes)) {
                      foreach ($mailboxes as $mailbox) {
                        $filters = mailbox('get', 'filters', $mailbox);
                        if (!empty($filters)) {
                          foreach ($filters as $filter) {
                            if ($details = mailbox('get', 'filter_details', $filter)) {
                              $data[] = $details;
                            }
                            else {
                              continue;
                            }
                          }
                        }
                      }
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                $filters = mailbox('get', 'filters', $object);
                if (!empty($filters)) {
                  foreach ($filters as $filter) {
                    if ($details = mailbox('get', 'filter_details', $filter)) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                }
                process_get_return($data);
              break;
            }
          break;
          case "bcc":
            switch ($object) {
              case "all":
                $bcc_items = bcc('get');
                if (!empty($bcc_items)) {
                  foreach ($bcc_items as $bcc_item) {
                    if ($details = bcc('details', $bcc_item)) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                }
                process_get_return($data);
              break;
              default:
                $data = bcc('details', $object);
                if (!empty($data)) {
                  $data[] = $details;
                }
                process_get_return($data);
              break;
            }
          break;
          case "recipient_map":
            switch ($object) {
              case "all":
                $recipient_map_items = recipient_map('get');
                if (!empty($recipient_map_items)) {
                  foreach ($recipient_map_items as $recipient_map_item) {
                    if ($details = recipient_map('details', $recipient_map_item)) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                }
                process_get_return($data);
              break;
              default:
                $data = recipient_map('details', $object);
                if (!empty($data)) {
                  $data[] = $details;
                }
                process_get_return($data);
              break;
            }
          break;
          case "tls-policy-map":
            switch ($object) {
              case "all":
                $tls_policy_maps_items = tls_policy_maps('get');
                if (!empty($tls_policy_maps_items)) {
                  foreach ($tls_policy_maps_items as $tls_policy_maps_item) {
                    if ($details = tls_policy_maps('details', $tls_policy_maps_item)) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                }
                process_get_return($data);
              break;
              default:
                $data = tls_policy_maps('details', $object);
                if (!empty($data)) {
                  $data[] = $details;
                }
                process_get_return($data);
              break;
            }
          break;
          case "policy_wl_mailbox":
            switch ($object) {
              default:
                $data = policy('get', 'mailbox', $object)['whitelist'];
                process_get_return($data);
              break;
            }
          break;
          case "policy_bl_mailbox":
            switch ($object) {
              default:
                $data = policy('get', 'mailbox', $object)['blacklist'];
                process_get_return($data);
              break;
            }
          break;
          case "policy_wl_domain":
            switch ($object) {
              default:
                $data = policy('get', 'domain', $object)['whitelist'];
                process_get_return($data);
              break;
            }
          break;
          case "policy_bl_domain":
            switch ($object) {
              default:
                $data = policy('get', 'domain', $object)['blacklist'];
                process_get_return($data);
              break;
            }
          break;
          case "time_limited_aliases":
            switch ($object) {
              default:
                $data = mailbox('get', 'time_limited_aliases', $object);
                process_get_return($data);
              break;
            }
          break;
          case "fail2ban":
            switch ($object) {
              default:
                $data = fail2ban('get');
                process_get_return($data);
              break;
            }
          break;
          case "resource":
            switch ($object) {
              case "all":
                $domains = mailbox('get', 'domains');
                if (!empty($domains)) {
                  foreach ($domains as $domain) {
                    $resources = mailbox('get', 'resources', $domain);
                    if (!empty($resources)) {
                      foreach ($resources as $resource) {
                        if ($details = mailbox('get', 'resource_details', $resource)) {
                          $data[] = $details;
                        }
                        else {
                          continue;
                        }
                      }
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;
              default:
                $data = mailbox('get', 'resource_details', $object);
                process_get_return($data);
              break;
            }
          break;
          case "fwdhost":
            switch ($object) {
              case "all":
                process_get_return(fwdhost('get'));
              break;
              default:
                process_get_return(fwdhost('details', $object));
              break;
            }
          break;
          case "quarantine":
            // "all" will not print details
            switch ($object) {
              case "all":
                process_get_return(quarantine('get'));
              break;
              default:
                process_get_return(quarantine('details', $object));
              break;
            }
          break;
          case "alias-domain":
            switch ($object) {
              case "all":
                $alias_domains = mailbox('get', 'alias_domains');
                if (!empty($alias_domains)) {
                  foreach ($alias_domains as $alias_domain) {
                    if ($details = mailbox('get', 'alias_domain_details', $alias_domain)) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                }
                process_get_return($data);
              break;
              default:
                process_get_return(mailbox('get', 'alias_domain_details', $object));
              break;
            }
          break;
          case "alias":
            switch ($object) {
              case "all":
                $domains = array_merge(mailbox('get', 'domains'), mailbox('get', 'alias_domains'));
                if (!empty($domains)) {
                  foreach ($domains as $domain) {
                    $aliases = mailbox('get', 'aliases', $domain);
                    if (!empty($aliases)) {
                      foreach ($aliases as $alias) {
                        if ($details = mailbox('get', 'alias_details', $alias)) {
                          $data[] = $details;
                        }
                        else {
                          continue;
                        }
                      }
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                process_get_return(mailbox('get', 'alias_details', $object));
              break;
            }
          break;
          case "domain-admin":
            switch ($object) {
              case "all":
                $domain_admins = domain_admin('get');
                if (!empty($domain_admins)) {
                  foreach ($domain_admins as $domain_admin) {
                    if ($details = domain_admin('details', $domain_admin)) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                process_get_return(domain_admin('details', $object));
              break;
            }
          break;
          case "admin":
            switch ($object) {
              case "all":
                $admins = admin('get');
                if (!empty($admins)) {
                  foreach ($admins as $admin) {
                    if ($details = admin('details', $admin)) {
                      $data[] = $details;
                    }
                    else {
                      continue;
                    }
                  }
                  process_get_return($data);
                }
                else {
                  echo '{}';
                }
              break;

              default:
                process_get_return(admin('details', $object));
              break;
            }
          break;
          case "u2f-registration":
            header('Content-Type: application/javascript');
            if (($_SESSION["mailcow_cc_role"] == "admin" || $_SESSION["mailcow_cc_role"] == "domainadmin") && $_SESSION["mailcow_cc_username"] == $object) {
              list($req, $sigs) = $u2f->getRegisterData(get_u2f_registrations($object));
              $_SESSION['regReq'] = json_encode($req);
              $_SESSION['regSigs'] = json_encode($sigs);
              echo 'var req = ' . json_encode($req) . ';';
              echo 'var registeredKeys = ' . json_encode($sigs) . ';';
              echo 'var appId = req.appId;';
              echo 'var registerRequests = [{version: req.version, challenge: req.challenge}];';
            }
            else {
              return;
            }
          break;
          case "u2f-authentication":
            header('Content-Type: application/javascript');
            if (isset($_SESSION['pending_mailcow_cc_username']) && $_SESSION['pending_mailcow_cc_username'] == $object) {
              $auth_data = $u2f->getAuthenticateData(get_u2f_registrations($object));
              $challenge = $auth_data[0]->challenge;
              $appId = $auth_data[0]->appId;
              foreach ($auth_data as $each) {
                $key = array(); // Empty array
                $key['version']   = $each->version;
                $key['keyHandle'] = $each->keyHandle;
                $registeredKey[]  = $key;
              }
              $_SESSION['authReq']  = json_encode($auth_data);
              echo 'var appId = "' . $appId . '";';
              echo 'var challenge = ' . json_encode($challenge) . ';';
              echo 'var registeredKeys = ' . json_encode($registeredKey) . ';';
            }
            else {
              return;
            }
          break;
          case "dkim":
            switch ($object) {
              default:
                $data = dkim('details', $object);
                  process_get_return($data);
                  break;
            }
          break;
          // return no route found if no case is matched
          default:
            http_response_code(404);
            echo json_encode(array(
              'type' => 'error',
              'msg' => 'route not found'
            ));
            exit();
        }
      break;
      case "delete":
        function process_delete_return($return) {
          $generic_failure = json_encode(array(
            'type' => 'error',
            'msg' => 'Cannot delete item'
          ));
          $generic_success = json_encode(array(
            'type' => 'success',
            'msg' => 'Task completed'
          ));
          if ($return === false) {
            echo isset($_SESSION['return']) ? json_encode($_SESSION['return']) : $generic_failure;
          }
          else {
            echo isset($_SESSION['return']) ? json_encode($_SESSION['return']) : $generic_success;
          }
        }
        if (!isset($_POST['items'])) {
          echo $request_incomplete;
          exit;
        }
        else {
          $items = (array)json_decode($_POST['items'], true);
        }
        // only allow POST requests to POST API endpoints
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
          http_response_code(405);
          echo json_encode(array(
              'type' => 'error',
              'msg' => 'only POST method is allowed'
          ));
          exit();
        }
        switch ($category) {
          case "alias":
            process_delete_return(mailbox('delete', 'alias', array('id' => $items)));
          break;
          case "oauth2-client":
            process_delete_return(oauth2('delete', 'client', array('id' => $items)));
          break;
          case "relayhost":
            process_delete_return(relayhost('delete', array('id' => $items)));
          break;
          case "transport":
            process_delete_return(transport('delete', array('id' => $items)));
          break;
          case "rsetting":
            process_delete_return(rsettings('delete', array('id' => $items)));
          break;
          case "syncjob":
            process_delete_return(mailbox('delete', 'syncjob', array('id' => $items)));
          break;
          case "filter":
            process_delete_return(mailbox('delete', 'filter', array('id' => $items)));
          break;
          case "mailq":
            process_delete_return(mailq('delete', array('qid' => $items)));
          break;
          case "qitem":
            process_delete_return(quarantine('delete', array('id' => $items)));
          break;
          case "bcc":
            process_delete_return(bcc('delete', array('id' => $items)));
          break;
          case "recipient_map":
            process_delete_return(recipient_map('delete', array('id' => $items)));
          break;
          case "tls-policy-map":
            process_delete_return(tls_policy_maps('delete', array('id' => $items)));
          break;
          case "fwdhost":
            process_delete_return(fwdhost('delete', array('forwardinghost' => $items)));
          break;
          case "dkim":
            process_delete_return(dkim('delete', array('domains' => $items)));
          break;
          case "domain":
            file_put_contents('/tmp/dssaa', $items);
            process_delete_return(mailbox('delete', 'domain', array('domain' => $items)));
          break;
          case "alias-domain":
            process_delete_return(mailbox('delete', 'alias_domain', array('alias_domain' => $items)));
          break;
          case "mailbox":
            process_delete_return(mailbox('delete', 'mailbox', array('username' => $items)));
          break;
          case "resource":
            process_delete_return(mailbox('delete', 'resource', array('name' => $items)));
          break;
          case "mailbox-policy":
            process_delete_return(policy('delete', 'mailbox', array('prefid' => $items)));
          break;
          case "domain-policy":
            process_delete_return(policy('delete', 'domain', array('prefid' => $items)));
          break;
          case "time_limited_alias":
            process_delete_return(mailbox('delete', 'time_limited_alias', array('address' => $items)));
          break;
          case "eas_cache":
            process_delete_return(mailbox('delete', 'eas_cache', array('username' => $items)));
          break;
          case "sogo_profile":
            process_delete_return(mailbox('delete', 'sogo_profile', array('username' => $items)));
          break;
          case "domain-admin":
            process_delete_return(domain_admin('delete', array('username' => $items)));
          break;
          case "admin":
            process_delete_return(admin('delete', array('username' => $items)));
          break;
          case "rlhash":
            echo ratelimit('delete', null, implode($items));
          break;
          // return no route found if no case is matched
          default:
            http_response_code(404);
            echo json_encode(array(
              'type' => 'error',
              'msg' => 'route not found'
            ));
            exit();
        }
      break;
      case "edit":
        function process_edit_return($return) {
          $generic_failure = json_encode(array(
            'type' => 'error',
            'msg' => 'Cannot edit item'
          ));
          $generic_success = json_encode(array(
            'type' => 'success',
            'msg' => 'Task completed'
          ));
          if ($return === false) {
            echo isset($_SESSION['return']) ? json_encode($_SESSION['return']) : $generic_failure;
          }
          else {
            echo isset($_SESSION['return']) ? json_encode($_SESSION['return']) : $generic_success;
          }
        }
        if (!isset($_POST['attr'])) {
          echo $request_incomplete;
          exit;
        }
        else {
          $attr = (array)json_decode($_POST['attr'], true);
          unset($attr['csrf_token']);
          $items = isset($_POST['items']) ? (array)json_decode($_POST['items'], true) : null;
        }
        // only allow POST requests to POST API endpoints
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
          http_response_code(405);
          echo json_encode(array(
              'type' => 'error',
              'msg' => 'only POST method is allowed'
          ));
          exit();
        }
        switch ($category) {
          case "bcc":
            process_edit_return(bcc('edit', array_merge(array('id' => $items), $attr)));
          break;
          case "oauth2-client":
            process_edit_return(oauth2('edit', 'client', array_merge(array('id' => $items), $attr)));
          break;
          case "recipient_map":
            process_edit_return(recipient_map('edit', array_merge(array('id' => $items), $attr)));
          break;
          case "tls-policy-map":
            process_edit_return(tls_policy_maps('edit', array_merge(array('id' => $items), $attr)));
          break;
          case "alias":
            process_edit_return(mailbox('edit', 'alias', array_merge(array('id' => $items), $attr)));
          break;
          case "app_links":
            process_edit_return(customize('edit', 'app_links', $attr));
          break;
          case "relayhost":
            process_edit_return(relayhost('edit', array_merge(array('id' => $items), $attr)));
          break;
          case "transport":
            process_edit_return(transport('edit', array_merge(array('id' => $items), $attr)));
          break;
          case "rsetting":
            process_edit_return(rsettings('edit', array_merge(array('id' => $items), $attr)));
          break;
          case "delimiter_action":
            process_edit_return(mailbox('edit', 'delimiter_action', array_merge(array('username' => $items), $attr)));
          break;
          case "tls_policy":
            process_edit_return(mailbox('edit', 'tls_policy', array_merge(array('username' => $items), $attr)));
          break;
          case "quarantine_notification":
            process_edit_return(mailbox('edit', 'quarantine_notification', array_merge(array('username' => $items), $attr)));
          break;
          case "qitem":
            process_edit_return(quarantine('edit', array_merge(array('id' => $items), $attr)));
          break;
          case "quarantine":
            process_edit_return(quarantine('edit', $attr));
          break;
          case "quota_notification":
            process_edit_return(quota_notification('edit', $attr));
          break;
          case "mailq":
            process_edit_return(mailq('edit', array_merge(array('qid' => $items), $attr)));
          break;
          case "time_limited_alias":
            process_edit_return(mailbox('edit', 'time_limited_alias', array_merge(array('address' => $items), $attr)));
          break;
          case "mailbox":
            process_edit_return(mailbox('edit', 'mailbox', array_merge(array('username' => $items), $attr)));
          break;
          case "syncjob":
            process_edit_return(mailbox('edit', 'syncjob', array_merge(array('id' => $items), $attr)));
          break;
          case "filter":
            process_edit_return(mailbox('edit', 'filter', array_merge(array('id' => $items), $attr)));
          break;          
          case "resource":
            process_edit_return(mailbox('edit', 'resource', array_merge(array('name' => $items), $attr)));
          break;
          case "domain":
            process_edit_return(mailbox('edit', 'domain', array_merge(array('domain' => $items), $attr)));
          break;
          case "rl-domain":
            process_edit_return(ratelimit('edit', 'domain', array_merge(array('object' => $items), $attr)));
          break;
          case "rl-mbox":
            process_edit_return(ratelimit('edit', 'mailbox', array_merge(array('object' => $items), $attr)));
          break;
          case "user-acl":
            process_edit_return(acl('edit', 'user', array_merge(array('username' => $items), $attr)));
          break;
          case "da-acl":
            process_edit_return(acl('edit', 'domainadmin', array_merge(array('username' => $items), $attr)));
          break;
          case "alias-domain":
            process_edit_return(mailbox('edit', 'alias_domain', array_merge(array('alias_domain' => $items), $attr)));
          break;
          case "spam-score":
            process_edit_return(mailbox('edit', 'spam_score', array_merge(array('username' => $items), $attr)));
          break;
          case "domain-admin":
            process_edit_return(domain_admin('edit', array_merge(array('username' => $items), $attr)));
          break;
          case "admin":
            process_edit_return(admin('edit', array_merge(array('username' => $items), $attr)));
          break;
          case "fwdhost":
            process_edit_return(fwdhost('edit', array_merge(array('fwdhost' => $items), $attr)));
          break;
          case "fail2ban":
            process_edit_return(fail2ban('edit', array_merge(array('network' => $items), $attr)));
          break;
          case "ui_texts":
            process_edit_return(customize('edit', 'ui_texts', $attr));
          break;
          case "self":
            if ($_SESSION['mailcow_cc_role'] == "domainadmin") {
              process_edit_return(domain_admin('edit', $attr));
            }
            elseif ($_SESSION['mailcow_cc_role'] == "user") {
              process_edit_return(edit_user_account($attr));
            }
          break;
          // return no route found if no case is matched
          default:
            http_response_code(404);
            echo json_encode(array(
              'type' => 'error',
              'msg' => 'route not found'
            ));
            exit();
        }
      break;
      // return no route found if no case is matched
      default:
        http_response_code(404);
        echo json_encode(array(
          'type' => 'error',
          'msg' => 'route not found'
        ));
        exit();
    }
  }
  if ($_SESSION['mailcow_cc_api'] === true) {
    if (isset($_SESSION['mailcow_cc_api']) && $_SESSION['mailcow_cc_api'] === true) {
      unset($_SESSION['return']);
    }
  }
}

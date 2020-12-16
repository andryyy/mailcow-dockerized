<?php
// File size is limited by Nginx site to 10M
// To speed things up, we do not include prerequisites
header('Content-Type: text/plain');
require_once "vars.inc.php";
// Do not show errors, we log to using error_log
ini_set('error_reporting', 0);
// Init database
//$dsn = $database_type . ':host=' . $database_host . ';dbname=' . $database_name;
$dsn = $database_type . ":unix_socket=" . $database_sock . ";dbname=" . $database_name;
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
  $pdo = new PDO($dsn, $database_user, $database_pass, $opt);
}
catch (PDOException $e) {
  error_log("ALIASEXP: " . $e . PHP_EOL);
  http_response_code(501);
  exit;
}

// Init Redis
$redis = new Redis();
$redis->connect('redis-mailcow', 6379);

function parse_email($email) {
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
  $a = strrpos($email, '@');
  return array('local' => substr($email, 0, $a), 'domain' => substr(substr($email, $a), 1));
}
if (!function_exists('getallheaders'))  {
  function getallheaders() {
    if (!is_array($_SERVER)) {
      return array();
    }
    $headers = array();
    foreach ($_SERVER as $name => $value) {
      if (substr($name, 0, 5) == 'HTTP_') {
        $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
      }
    }
    return $headers;
  }
}

// Read headers
$headers = getallheaders();
// Get rcpt
$rcpt = $headers['Rcpt'];
// Remove tag
$rcpt = preg_replace('/^(.*?)\+.*(@.*)$/', '$1$2', $rcpt);
// Parse email address
$parsed_rcpt = parse_email($rcpt);
// Create array of final mailboxes
$rcpt_final_mailboxes = array();

// Skip if not a mailcow handled domain
try {
  if (!$redis->hGet('DOMAIN_MAP', $parsed_rcpt['domain'])) {
    exit;
  }
}
catch (RedisException $e) {
  error_log("ALIASEXP: " . $e . PHP_EOL);
  http_response_code(504);
  exit;
}

// Always assume rcpt is not a final mailbox but an alias for a mailbox or further aliases
//
//             rcpt
//              |
// mailbox <-- goto ---> alias1, alias2, mailbox2
//                          |       |
//                      mailbox3    |
//                                  |
//                               alias3 ---> mailbox4
//
try {
  $stmt = $pdo->prepare("SELECT `goto` FROM `alias` WHERE `address` = :rcpt AND `active` = '1'");
  $stmt->execute(array(
    ':rcpt' => $rcpt
  ));
  $gotos = $stmt->fetch(PDO::FETCH_ASSOC)['goto'];
  if (empty($gotos)) {
    $stmt = $pdo->prepare("SELECT `goto` FROM `alias` WHERE `address` = :rcpt AND `active` = '1'");
    $stmt->execute(array(
      ':rcpt' => '@' . $parsed_rcpt['domain']
    ));
    $gotos = $stmt->fetch(PDO::FETCH_ASSOC)['goto'];
  }
  if (empty($gotos)) {
    $stmt = $pdo->prepare("SELECT `target_domain` FROM `alias_domain` WHERE `alias_domain` = :rcpt AND `active` = '1'");
    $stmt->execute(array(':rcpt' => $parsed_rcpt['domain']));
    $goto_branch = $stmt->fetch(PDO::FETCH_ASSOC)['target_domain'];
    if ($goto_branch) {
      $gotos = $parsed_rcpt['local'] . '@' . $goto_branch;
    }
  }
  $gotos_array = explode(',', $gotos);

  $loop_c = 0;

  while (count($gotos_array) != 0 && $loop_c <= 20) {

    // Loop through all found gotos
    foreach ($gotos_array as $index => &$goto) {
      error_log("ALIAS EXPANDER: http pipe: query " . $goto . " as username from mailbox" . PHP_EOL);
      $stmt = $pdo->prepare("SELECT `username` FROM `mailbox` WHERE `username` = :goto AND (`active`= '1' OR `active`= '2');");
      $stmt->execute(array(':goto' => $goto));
      $username = $stmt->fetch(PDO::FETCH_ASSOC)['username'];
      if (!empty($username)) {
        error_log("ALIAS EXPANDER: http pipe: mailbox found: " . $username . PHP_EOL);
        // Current goto is a mailbox, save to rcpt_final_mailboxes if not a duplicate
        if (!in_array($username, $rcpt_final_mailboxes)) {
          $rcpt_final_mailboxes[] = $username;
        }
      }
      else {
        $parsed_goto = parse_email($goto);
        if (!$redis->hGet('DOMAIN_MAP', $parsed_goto['domain'])) {
          error_log("ALIAS EXPANDER:" . $goto . " is not a mailcow handled mailbox or alias address" . PHP_EOL);
        }
        else {
          $stmt = $pdo->prepare("SELECT `goto` FROM `alias` WHERE `address` = :goto AND `active` = '1'");
          $stmt->execute(array(':goto' => $goto));
          $goto_branch = $stmt->fetch(PDO::FETCH_ASSOC)['goto'];
          if ($goto_branch) {
            error_log("ALIAS EXPANDER: http pipe: goto address " . $goto . " is an alias branch for " . $goto_branch . PHP_EOL);
            $goto_branch_array = explode(',', $goto_branch);
          } else {
            $stmt = $pdo->prepare("SELECT `target_domain` FROM `alias_domain` WHERE `alias_domain` = :domain AND `active` AND '1'");
            $stmt->execute(array(':domain' => $parsed_goto['domain']));
            $goto_branch = $stmt->fetch(PDO::FETCH_ASSOC)['target_domain'];
            if ($goto_branch) {
              error_log("ALIAS EXPANDER: http pipe: goto domain " . $parsed_goto['domain'] . " is a domain alias branch for " . $goto_branch . PHP_EOL);
              $goto_branch_array = array($parsed_goto['local'] . '@' . $goto_branch);
            }
          }
        }
      }
      // goto item was processed, unset
      unset($gotos_array[$index]);
    }

    // Merge goto branch array derived from previous loop (if any), filter duplicates and unset goto branch array
    if (!empty($goto_branch_array)) {
      $gotos_array = array_unique(array_merge($gotos_array, $goto_branch_array));
      unset($goto_branch_array);
    }

    // Reindex array
    $gotos_array = array_values($gotos_array);

    // Force exit if loop cannot be solved
    // Postfix does not allow for alias loops, so this should never happen.
    $loop_c++;
    error_log("ALIAS EXPANDER: http pipe: goto array count on loop #". $loop_c . " is " . count($gotos_array) . PHP_EOL);
  }
}
catch (PDOException $e) {
  error_log("ALIAS EXPANDER: " . $e->getMessage() . PHP_EOL);
  http_response_code(502);
  exit;
}

// Does also return the mailbox name if question == answer (query == mailbox)
if (count($rcpt_final_mailboxes) == 1) {
  error_log("ALIASEXP: direct alias " . $rcpt . " expanded to " . $rcpt_final_mailboxes[0] . PHP_EOL);
  echo trim($rcpt_final_mailboxes[0]);
}

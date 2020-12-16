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
  error_log("QUARANTINE: " . $e . PHP_EOL);
  http_response_code(501);
  exit;
}
// Init Redis
$redis = new Redis();
$redis->connect('redis-mailcow', 6379);

// Functions
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

$raw_data_content = file_get_contents('php://input');
$raw_data = mb_convert_encoding($raw_data_content, 'HTML-ENTITIES', "UTF-8");
$headers = getallheaders();

$qid      = $headers['X-Rspamd-Qid'];
$fuzzy    = $headers['X-Rspamd-Fuzzy'];
$subject  = $headers['X-Rspamd-Subject'];
$score    = $headers['X-Rspamd-Score'];
$rcpts    = $headers['X-Rspamd-Rcpt'];
$user     = $headers['X-Rspamd-User'];
$ip       = $headers['X-Rspamd-Ip'];
$action   = $headers['X-Rspamd-Action'];
$sender   = $headers['X-Rspamd-From'];
$symbols  = $headers['X-Rspamd-Symbols'];

$raw_size = (int)$_SERVER['CONTENT_LENGTH'];

if (empty($sender)) {
  error_log("QUARANTINE: Unknown sender, assuming empty-env-from@localhost" . PHP_EOL);
  $sender = 'empty-env-from@localhost';
}

if ($fuzzy == 'unknown') {
  $fuzzy = '[]';
}

try {
  $max_size = (int)$redis->Get('Q_MAX_SIZE');
  if (($max_size * 1048576) < $raw_size) {
    error_log(sprintf("QUARANTINE: Message too large: %d b exceeds %d b", $raw_size, ($max_size * 1048576)) . PHP_EOL);
    http_response_code(505);
    exit;
  }
  if ($exclude_domains = $redis->Get('Q_EXCLUDE_DOMAINS')) {
    $exclude_domains = json_decode($exclude_domains, true);
  }
  $retention_size = (int)$redis->Get('Q_RETENTION_SIZE');
}
catch (RedisException $e) {
  error_log("QUARANTINE: " . $e . PHP_EOL);
  http_response_code(504);
  exit;
}

$rcpt_final_mailboxes = array();

// Loop through all rcpts
foreach (json_decode($rcpts, true) as $rcpt) {
  // Remove tag
  $rcpt = preg_replace('/^(.*?)\+.*(@.*)$/', '$1$2', $rcpt);
  
  // Break rcpt into local part and domain part
  $parsed_rcpt = parse_email($rcpt);
  
  // Skip if not a mailcow handled domain
  try {
    if (!$redis->hGet('DOMAIN_MAP', $parsed_rcpt['domain'])) {
      continue;
    }
  }
  catch (RedisException $e) {
    error_log("QUARANTINE: " . $e . PHP_EOL);
    http_response_code(504);
    exit;
  }

  // Skip if domain is excluded
  if (in_array($parsed_rcpt['domain'], $exclude_domains)) {
    error_log(sprintf("QUARANTINE: Skipped domain %s", $parsed_rcpt['domain']) . PHP_EOL);
    continue;
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
        error_log("RCPT RESOVLER: http pipe: query " . $goto . " as username from mailbox" . PHP_EOL);
        $stmt = $pdo->prepare("SELECT `username` FROM `mailbox` WHERE `username` = :goto AND (`active`= '1' OR `active`= '2');");
        $stmt->execute(array(':goto' => $goto));
        $username = $stmt->fetch(PDO::FETCH_ASSOC)['username'];
        if (!empty($username)) {
          error_log("RCPT RESOVLER: http pipe: mailbox found: " . $username . PHP_EOL);
          // Current goto is a mailbox, save to rcpt_final_mailboxes if not a duplicate
          if (!in_array($username, $rcpt_final_mailboxes)) {
            $rcpt_final_mailboxes[] = $username;
          }
        }
        else {
          $parsed_goto = parse_email($goto);
          if (!$redis->hGet('DOMAIN_MAP', $parsed_goto['domain'])) {
            error_log("RCPT RESOVLER:" . $goto . " is not a mailcow handled mailbox or alias address" . PHP_EOL);
          }
          else {
            $stmt = $pdo->prepare("SELECT `goto` FROM `alias` WHERE `address` = :goto AND `active` = '1'");
            $stmt->execute(array(':goto' => $goto));
            $goto_branch = $stmt->fetch(PDO::FETCH_ASSOC)['goto'];
            if ($goto_branch) {
              error_log("RCPT RESOVLER: http pipe: goto address " . $goto . " is an alias branch for " . $goto_branch . PHP_EOL);
              $goto_branch_array = explode(',', $goto_branch);
            } else {
              $stmt = $pdo->prepare("SELECT `target_domain` FROM `alias_domain` WHERE `alias_domain` = :domain AND `active` AND '1'");
              $stmt->execute(array(':domain' => $parsed_goto['domain']));
              $goto_branch = $stmt->fetch(PDO::FETCH_ASSOC)['target_domain'];
              if ($goto_branch) {
                error_log("RCPT RESOVLER: http pipe: goto domain " . $parsed_goto['domain'] . " is a domain alias branch for " . $goto_branch . PHP_EOL);
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
      error_log("RCPT RESOVLER: http pipe: goto array count on loop #". $loop_c . " is " . count($gotos_array) . PHP_EOL);
    }
  }
  catch (PDOException $e) {
    error_log("RCPT RESOVLER: " . $e->getMessage() . PHP_EOL);
    http_response_code(502);
    exit;
  }
}

foreach ($rcpt_final_mailboxes as $rcpt_final) {
  error_log("QUARANTINE: quarantine pipe: processing quarantine message for rcpt " . $rcpt_final . PHP_EOL);
  try {
    $stmt = $pdo->prepare("INSERT INTO `quarantine` (`qid`, `subject`, `score`, `sender`, `rcpt`, `symbols`, `user`, `ip`, `msg`, `action`, `fuzzy_hashes`)
      VALUES (:qid, :subject, :score, :sender, :rcpt, :symbols, :user, :ip, :msg, :action, :fuzzy_hashes)");
    $stmt->execute(array(
      ':qid' => $qid,
      ':subject' => $subject,
      ':score' => $score,
      ':sender' => $sender,
      ':rcpt' => $rcpt_final,
      ':symbols' => $symbols,
      ':user' => $user,
      ':ip' => $ip,
      ':msg' => $raw_data,
      ':action' => $action,
      ':fuzzy_hashes' => $fuzzy
    ));
    $stmt = $pdo->prepare('DELETE FROM `quarantine` WHERE `rcpt` = :rcpt AND `id` NOT IN (
      SELECT `id`
      FROM (
        SELECT `id`
        FROM `quarantine`
        WHERE `rcpt` = :rcpt2
        ORDER BY id DESC
        LIMIT :retention_size
      ) x 
    );');
    $stmt->execute(array(
      ':rcpt' => $rcpt_final,
      ':rcpt2' => $rcpt_final,
      ':retention_size' => $retention_size
    ));
  }
  catch (PDOException $e) {
    error_log("QUARANTINE: " . $e->getMessage() . PHP_EOL);
    http_response_code(503);
    exit;
  }
}


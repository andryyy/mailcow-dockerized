<?php
/*
The match section performs AND operation on different matches: for example, if you have from and rcpt in the same rule,
then the rule matches only when from AND rcpt match. For similar matches, the OR rule applies: if you have multiple rcpt matches,
then any of these will trigger the rule. If a rule is triggered then no more rules are matched.
*/
header('Content-Type: text/plain');
require_once "vars.inc.php";
// Getting headers sent by the client.
ini_set('error_reporting', 0);

//$dsn = $database_type . ':host=' . $database_host . ';dbname=' . $database_name;
$dsn = $database_type . ":unix_socket=" . $database_sock . ";dbname=" . $database_name;
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
  $pdo = new PDO($dsn, $database_user, $database_pass, $opt);
  $stmt = $pdo->query("SELECT '1' FROM `filterconf`");
}
catch (PDOException $e) {
  echo 'settings { }';
  exit;
}

// Check if db changed and return header
/*
$stmt = $pdo->prepare("SELECT MAX(UNIX_TIMESTAMP(UPDATE_TIME)) AS `db_update_time` FROM information_schema.tables
  WHERE (`TABLE_NAME` = 'filterconf' OR `TABLE_NAME` = 'settingsmap')
    AND TABLE_SCHEMA = :dbname;");
$stmt->execute(array(
  ':dbname' => $database_name
));
$db_update_time = $stmt->fetch(PDO::FETCH_ASSOC)['db_update_time'];
if (empty($db_update_time)) {
  $db_update_time = 1572048000;
}
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && (strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $db_update_time)) {
  header('Last-Modified: '.gmdate('D, d M Y H:i:s', $db_update_time).' GMT', true, 304);
  exit;
} else {
  header('Last-Modified: '.gmdate('D, d M Y H:i:s', $db_update_time).' GMT', true, 200);
}
*/

function parse_email($email) {
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
  $a = strrpos($email, '@');
  return array('local' => substr($email, 0, $a), 'domain' => substr($email, $a));
}

function wl_by_sogo() {
  global $pdo;
  $rcpt = array();
  $stmt = $pdo->query("SELECT DISTINCT(`sogo_folder_info`.`c_path2`) AS `user`, GROUP_CONCAT(`sogo_quick_contact`.`c_mail`) AS `contacts` FROM `sogo_folder_info`
    INNER JOIN `sogo_quick_contact` ON `sogo_quick_contact`.`c_folder_id` = `sogo_folder_info`.`c_folder_id`
      GROUP BY `c_path2`");
  $sogo_contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
  while ($row = array_shift($sogo_contacts)) {
    foreach (explode(',', $row['contacts']) as $contact) {
      if (!filter_var($contact, FILTER_VALIDATE_EMAIL)) {
        continue;
      }
      // Explicit from, no mime_from, no regex - envelope must match
      // mailcow white and blacklists also cover mime_from
      $rcpt[$row['user']][] = str_replace('/', '\/', $contact);
    }
  }
  return $rcpt;
}

function ucl_rcpts($object, $type) {
  global $pdo;
  $rcpt = array();
  if ($type == 'mailbox') {
    // Standard aliases
    $stmt = $pdo->prepare("SELECT `address` FROM `alias`
      WHERE `goto` = :object_goto
        AND `address` NOT LIKE '@%'");
    $stmt->execute(array(
      ':object_goto' => $object
    ));
    $standard_aliases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    while ($row = array_shift($standard_aliases)) {
      $local = parse_email($row['address'])['local'];
      $domain = parse_email($row['address'])['domain'];
      if (!empty($local) && !empty($domain)) {
        $rcpt[] = '/^' . str_replace('/', '\/', $local) . '[+].*' . str_replace('/', '\/', $domain) . '$/i';
      }
      $rcpt[] = str_replace('/', '\/', $row['address']);
    }
    // Aliases by alias domains
    $stmt = $pdo->prepare("SELECT CONCAT(`local_part`, '@', `alias_domain`.`alias_domain`) AS `alias` FROM `mailbox` 
      LEFT OUTER JOIN `alias_domain` ON `mailbox`.`domain` = `alias_domain`.`target_domain`
      WHERE `mailbox`.`username` = :object");
    $stmt->execute(array(
      ':object' => $object
    ));
    $by_domain_aliases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    array_filter($by_domain_aliases);
    while ($row = array_shift($by_domain_aliases)) {
      if (!empty($row['alias'])) {
        $local = parse_email($row['alias'])['local'];
        $domain = parse_email($row['alias'])['domain'];
        if (!empty($local) && !empty($domain)) {
          $rcpt[] = '/^' . str_replace('/', '\/', $local) . '[+].*' . str_replace('/', '\/', $domain) . '$/i';
        }
        $rcpt[] = str_replace('/', '\/', $row['alias']);
      }
    }
  }
  elseif ($type == 'domain') {
    // Domain self
    $rcpt[] = '/.*@' . $object . '/i';
    $stmt = $pdo->prepare("SELECT `alias_domain` FROM `alias_domain`
      WHERE `target_domain` = :object");
    $stmt->execute(array(':object' => $object));
    $alias_domains = $stmt->fetchAll(PDO::FETCH_ASSOC);
    array_filter($alias_domains);
    while ($row = array_shift($alias_domains)) {
      $rcpt[] = '/.*@' . $row['alias_domain'] . '/i';
    }
  }
  return $rcpt;
}
?>
settings {
  watchdog {
    priority = 10;
    rcpt_mime = "/null@localhost/i";
    from_mime = "/watchdog@localhost/i";
    apply "default" {
      symbols_disabled = ["HISTORY_SAVE", "ARC", "ARC_SIGNED", "DKIM", "DKIM_SIGNED", "CLAM_VIRUS"];
      want_spam = yes;
      actions {
        reject = 9999.0;
        greylist = 9998.0;
        "add header" = 9997.0;
      }

    }
  }
<?php

/*
// Start custom scores for users
*/

$stmt = $pdo->query("SELECT DISTINCT `object` FROM `filterconf` WHERE `option` = 'highspamlevel' OR `option` = 'lowspamlevel'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

while ($row = array_shift($rows)) {
  $username_sane = preg_replace("/[^a-zA-Z0-9]+/", "", $row['object']);
?>
  score_<?=$username_sane;?> {
    priority = 4;
<?php
  foreach (ucl_rcpts($row['object'], strpos($row['object'], '@') === FALSE ? 'domain' : 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
  }
  $stmt = $pdo->prepare("SELECT `option`, `value` FROM `filterconf` 
    WHERE (`option` = 'highspamlevel' OR `option` = 'lowspamlevel')
      AND `object`= :object");
  $stmt->execute(array(':object' => $row['object']));
  $spamscore = $stmt->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
?>
    apply "default" {
      actions {
        reject = <?=$spamscore['highspamlevel'][0];?>;
        greylist = <?=$spamscore['lowspamlevel'][0] - 1;?>;
        "add header" = <?=$spamscore['lowspamlevel'][0];?>;
      }
    }
  }
<?php
}

/*
// Start SOGo contacts whitelist
// Priority 4, lower than a domain whitelist (5) and lower than a mailbox whitelist (6)
*/

foreach (wl_by_sogo() as $user => $contacts) {
  $username_sane = preg_replace("/[^a-zA-Z0-9]+/", "", $user);
?>
  whitelist_sogo_<?=$username_sane;?> {
<?php
  foreach ($contacts as $contact) {
?>
    from = <?=json_encode($contact, JSON_UNESCAPED_SLASHES);?>;
<?php
  }
?>
    priority = 4;
<?php
    foreach (ucl_rcpts($user, 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
    }
?>
    apply "default" {
      SOGO_CONTACT = -99.0;
    }
    symbols [
      "SOGO_CONTACT"
    ]
  }
<?php
}

/*
// Start whitelist
*/

$stmt = $pdo->query("SELECT DISTINCT `object` FROM `filterconf` WHERE `option` = 'whitelist_from'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
while ($row = array_shift($rows)) {
  $username_sane = preg_replace("/[^a-zA-Z0-9]+/", "", $row['object']);
?>
  whitelist_<?=$username_sane;?> {
<?php
  $list_items = array();
  $stmt = $pdo->prepare("SELECT `value` FROM `filterconf`
    WHERE `object`= :object
      AND `option` = 'whitelist_from'");
  $stmt->execute(array(':object' => $row['object']));
  $list_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($list_items as $item) {
?>
    from = "/<?='^' . str_replace('\*', '.*', preg_quote($item['value'], '/')) . '$' ;?>/i";
<?php
  }
  if (!filter_var(trim($row['object']), FILTER_VALIDATE_EMAIL)) {
?>
    priority = 5;
<?php
    foreach (ucl_rcpts($row['object'], strpos($row['object'], '@') === FALSE ? 'domain' : 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
    }
  }
  else {
?>
    priority = 6;
<?php
    foreach (ucl_rcpts($row['object'], strpos($row['object'], '@') === FALSE ? 'domain' : 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
    }
  }
?>
    apply "default" {
      MAILCOW_WHITE = -999.0;
    }
    symbols [
      "MAILCOW_WHITE"
    ]
  }
  whitelist_mime_<?=$username_sane;?> {
<?php
  foreach ($list_items as $item) {
?>
    from_mime = "/<?='^' . str_replace('\*', '.*', preg_quote($item['value'], '/')) . '$' ;?>/i";
<?php
  }
  if (!filter_var(trim($row['object']), FILTER_VALIDATE_EMAIL)) {
?>
    priority = 5;
<?php
    foreach (ucl_rcpts($row['object'], strpos($row['object'], '@') === FALSE ? 'domain' : 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
    }
  }
  else {
?>
    priority = 6;
<?php
    foreach (ucl_rcpts($row['object'], strpos($row['object'], '@') === FALSE ? 'domain' : 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
    }
  }
?>
    apply "default" {
      MAILCOW_WHITE = -999.0;
    }
    symbols [
      "MAILCOW_WHITE"
    ]
  }
<?php
}

/*
// Start blacklist
*/

$stmt = $pdo->query("SELECT DISTINCT `object` FROM `filterconf` WHERE `option` = 'blacklist_from'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
while ($row = array_shift($rows)) {
  $username_sane = preg_replace("/[^a-zA-Z0-9]+/", "", $row['object']);
?>
  blacklist_<?=$username_sane;?> {
<?php
  $list_items = array();
  $stmt = $pdo->prepare("SELECT `value` FROM `filterconf`
    WHERE `object`= :object
      AND `option` = 'blacklist_from'");
  $stmt->execute(array(':object' => $row['object']));
  $list_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
  foreach ($list_items as $item) {
?>
    from = "/<?='^' . str_replace('\*', '.*', preg_quote($item['value'], '/')) . '$' ;?>/i";
<?php
  }
  if (!filter_var(trim($row['object']), FILTER_VALIDATE_EMAIL)) {
?>
    priority = 5;
<?php
    foreach (ucl_rcpts($row['object'], strpos($row['object'], '@') === FALSE ? 'domain' : 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
    }
  }
  else {
?>
    priority = 6;
<?php
    foreach (ucl_rcpts($row['object'], strpos($row['object'], '@') === FALSE ? 'domain' : 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
    }
  }
?>
    apply "default" {
      MAILCOW_BLACK = 999.0;
    }
    symbols [
      "MAILCOW_BLACK"
    ]
  }
  blacklist_header_<?=$username_sane;?> {
<?php
  foreach ($list_items as $item) {
?>
    from_mime = "/<?='^' . str_replace('\*', '.*', preg_quote($item['value'], '/')) . '$' ;?>/i";
<?php
  }
  if (!filter_var(trim($row['object']), FILTER_VALIDATE_EMAIL)) {
?>
    priority = 5;
<?php
    foreach (ucl_rcpts($row['object'], strpos($row['object'], '@') === FALSE ? 'domain' : 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
    }
  }
  else {
?>
    priority = 6;
<?php
    foreach (ucl_rcpts($row['object'], strpos($row['object'], '@') === FALSE ? 'domain' : 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
    }
  }
?>
    apply "default" {
      MAILCOW_BLACK = 999.0;
    }
    symbols [
      "MAILCOW_BLACK"
    ]
  }
<?php
}

/*
// Start traps
*/

?>
  traps {
<?php
  foreach (ucl_rcpts('spam@localhost', 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
  }
  foreach (ucl_rcpts('ham@localhost', 'mailbox') as $rcpt) {
?>
    rcpt = <?=json_encode($rcpt, JSON_UNESCAPED_SLASHES);?>;
<?php
  }
?>
    priority = 9;
    want_spam = yes;
  }
<?php
// Start additional content

$stmt = $pdo->query("SELECT `id`, `content` FROM `settingsmap` WHERE `active` = '1'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
while ($row = array_shift($rows)) {
  $username_sane = preg_replace("/[^a-zA-Z0-9]+/", "", $row['id']);
?>
  additional_settings_<?=intval($row['id']);?> {
<?php
    $content = preg_split('/\r\n|\r|\n/', $row['content']);
    foreach ($content as $line) {
      echo '    ' . $line . PHP_EOL;
    }
?>
  }
<?php
}
?>
}

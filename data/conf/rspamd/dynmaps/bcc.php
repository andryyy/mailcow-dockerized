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
  error_log("BCC MAP SQL ERROR: " . $e . PHP_EOL);
  http_response_code(501);
  exit;
}

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
// Get from
$from = $headers['From'];
// Remove tags
$rcpt = preg_replace('/^(.*?)\+.*(@.*)$/', '$1$2', $rcpt);
$from = preg_replace('/^(.*?)\+.*(@.*)$/', '$1$2', $from);

try {
  if (!empty($rcpt)) {
    $stmt = $pdo->prepare("SELECT `bcc_dest` FROM `bcc_maps` WHERE `type` = 'rcpt' AND `local_dest` = :local_dest AND `active` = '1'");
    $stmt->execute(array(
      ':local_dest' => $rcpt
    ));
    $bcc_dest = $stmt->fetch(PDO::FETCH_ASSOC)['bcc_dest'];
    if (!empty($bcc_dest) && filter_var($bcc_dest, FILTER_VALIDATE_EMAIL)) {
      error_log("BCC MAP: returning ". $bcc_dest . " for " . $rcpt . PHP_EOL);
      http_response_code(201);
      echo trim($bcc_dest);
      exit;
    }
  }
  if (!empty($from)) {
    $stmt = $pdo->prepare("SELECT `bcc_dest` FROM `bcc_maps` WHERE `type` = 'sender' AND `local_dest` = :local_dest AND `active` = '1'");
    $stmt->execute(array(
      ':local_dest' => $from
    ));
    $bcc_dest = $stmt->fetch(PDO::FETCH_ASSOC)['bcc_dest'];
    if (!empty($bcc_dest) && filter_var($bcc_dest, FILTER_VALIDATE_EMAIL)) {
      error_log("BCC MAP: returning ". $bcc_dest . " for " . $from . PHP_EOL);
      http_response_code(201);
      echo trim($bcc_dest);
      exit;
    }
  }
}
catch (PDOException $e) {
  error_log("BCC MAP SQL ERROR: " . $e->getMessage() . PHP_EOL);
  http_response_code(502);
  exit;
}


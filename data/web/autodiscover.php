<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/vars.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.inc.php';
$default_autodiscover_config = $autodiscover_config;
if(file_exists('inc/vars.local.inc.php')) {
  include_once 'inc/vars.local.inc.php';
}
$autodiscover_config = array_merge($default_autodiscover_config, $autodiscover_config);

// Redis
$redis = new Redis();
try {
  if (!empty(getenv('REDIS_SLAVEOF_IP'))) {
    $redis->connect(getenv('REDIS_SLAVEOF_IP'), getenv('REDIS_SLAVEOF_PORT'));
  }
  else {
    $redis->connect('redis-mailcow', 6379);
  }
}
catch (Exception $e) {
  exit;
}

error_reporting(0);

$data = trim(file_get_contents("php://input"));

if (strpos($data, 'autodiscover/outlook/responseschema') !== false) {
  $autodiscover_config['autodiscoverType'] = 'imap';
  if ($autodiscover_config['useEASforOutlook'] == 'yes' &&
    // Office for macOS does not support EAS
    strpos($_SERVER['HTTP_USER_AGENT'], 'Mac') === false &&
    // Outlook 2013 (version 15) or higher
    preg_match('/(Outlook|Office).+1[5-9]\./', $_SERVER['HTTP_USER_AGENT'])
  ) {
    $autodiscover_config['autodiscoverType'] = 'activesync';
  }
}

if (getenv('SKIP_SOGO') == "y") {
  $autodiscover_config['autodiscoverType'] = 'imap';
}

//$dsn = $database_type . ":host=" . $database_host . ";dbname=" . $database_name;
$dsn = $database_type . ":unix_socket=" . $database_sock . ";dbname=" . $database_name;
$opt = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
];
$pdo = new PDO($dsn, $database_user, $database_pass, $opt);
$login_user = strtolower(trim($_SERVER['PHP_AUTH_USER']));
$login_pass = trim(htmlspecialchars_decode($_SERVER['PHP_AUTH_PW']));

if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
  $json = json_encode(
    array(
      "time" => time(),
      "ua" => $_SERVER['HTTP_USER_AGENT'],
      "user" => "none",
      "service" => "Error: must be authenticated"
    )
  );
  $redis->lPush('AUTODISCOVER_LOG', $json);
  header('WWW-Authenticate: Basic realm="' . $_SERVER['HTTP_HOST'] . '"');
  header('HTTP/1.0 401 Unauthorized');
  exit(0);
}

$login_role = check_login($login_user, $login_pass);

if ($login_role === "user") {
  header("Content-Type: application/xml");
  echo '<?xml version="1.0" encoding="utf-8" ?>' . PHP_EOL;
?>
<Autodiscover xmlns="http://schemas.microsoft.com/exchange/autodiscover/responseschema/2006">
<?php
  if(!$data) {
    try {
      $json = json_encode(
        array(
          "time" => time(),
          "ua" => $_SERVER['HTTP_USER_AGENT'],
          "user" => $_SERVER['PHP_AUTH_USER'],
          "service" => "Error: invalid or missing request data"
        )
      );
      $redis->lPush('AUTODISCOVER_LOG', $json);
      $redis->lTrim('AUTODISCOVER_LOG', 0, 100);
    }
    catch (RedisException $e) {
      $_SESSION['return'][] = array(
        'type' => 'danger',
        'msg' => 'Redis: '.$e
      );
      return false;
    }
    list($usec, $sec) = explode(' ', microtime());
?>
  <Response>
    <Error Time="<?=date('H:i:s', $sec) . substr($usec, 0, strlen($usec) - 2);?>" Id="2477272013">
      <ErrorCode>600</ErrorCode>
      <Message>Invalid Request</Message>
      <DebugData />
    </Error>
  </Response>
</Autodiscover>
<?php
    exit(0);
  }
  try {
    $discover = new SimpleXMLElement($data);
    $email = $discover->Request->EMailAddress;
  } catch (Exception $e) {
    $email = $_SERVER['PHP_AUTH_USER'];
  }

  $username = trim($email);
  try {
    $stmt = $pdo->prepare("SELECT `name` FROM `mailbox` WHERE `username`= :username");
    $stmt->execute(array(':username' => $username));
    $MailboxData = $stmt->fetch(PDO::FETCH_ASSOC);
  }
  catch(PDOException $e) {
    die("Failed to determine name from SQL");
  }
  if (!empty($MailboxData['name'])) {
    $displayname = $MailboxData['name'];
  }
  else {
    $displayname = $email;
  }
  try {
    $json = json_encode(
      array(
        "time" => time(),
        "ua" => $_SERVER['HTTP_USER_AGENT'],
        "user" => $_SERVER['PHP_AUTH_USER'],
        "service" => $autodiscover_config['autodiscoverType']
      )
    );
    $redis->lPush('AUTODISCOVER_LOG', $json);
    $redis->lTrim('AUTODISCOVER_LOG', 0, 100);
  }
  catch (RedisException $e) {
    $_SESSION['return'][] = array(
      'type' => 'danger',
      'msg' => 'Redis: '.$e
    );
    return false;
  }
  if ($autodiscover_config['autodiscoverType'] == 'imap') {
?>
  <Response xmlns="http://schemas.microsoft.com/exchange/autodiscover/outlook/responseschema/2006a">
    <User>
      <DisplayName><?=htmlspecialchars($displayname, ENT_XML1 | ENT_QUOTES, 'UTF-8');?></DisplayName>
    </User>
    <Account>
      <AccountType>email</AccountType>
      <Action>settings</Action>
      <Protocol>
        <Type>IMAP</Type>
        <Server><?=$autodiscover_config['imap']['server'];?></Server>
        <Port><?=$autodiscover_config['imap']['port'];?></Port>
        <DomainRequired>off</DomainRequired>
        <LoginName><?=$email;?></LoginName>
        <SPA>off</SPA>
        <SSL>on</SSL>
        <AuthRequired>on</AuthRequired>
      </Protocol>
      <Protocol>
        <Type>SMTP</Type>
        <Server><?=$autodiscover_config['smtp']['server'];?></Server>
        <Port><?=$autodiscover_config['smtp']['port'];?></Port>
        <DomainRequired>off</DomainRequired>
        <LoginName><?=$email;?></LoginName>
        <SPA>off</SPA>
        <SSL>on</SSL>
        <AuthRequired>on</AuthRequired>
        <UsePOPAuth>on</UsePOPAuth>
        <SMTPLast>off</SMTPLast>
      </Protocol>
    <?php
    if (getenv('SKIP_SOGO') != "y") {
    ?>
      <Protocol>
        <Type>CalDAV</Type>
        <Server>https://<?=$autodiscover_config['caldav']['server'];?><?php if ($autodiscover_config['caldav']['port'] != 443) echo ':'.$autodiscover_config['caldav']['port']; ?>/SOGo/dav/<?=$email;?>/</Server>
        <DomainRequired>off</DomainRequired>
        <LoginName><?=$email;?></LoginName>
      </Protocol>
      <Protocol>
        <Type>CardDAV</Type>
        <Server>https://<?=$autodiscover_config['carddav']['server'];?><?php if ($autodiscover_config['caldav']['port'] != 443) echo ':'.$autodiscover_config['carddav']['port']; ?>/SOGo/dav/<?=$email;?>/</Server>
        <DomainRequired>off</DomainRequired>
        <LoginName><?=$email;?></LoginName>
      </Protocol>
    <?php
    }
    ?>
    </Account>
  </Response>
<?php
  }
  else if ($autodiscover_config['autodiscoverType'] == 'activesync') {
?>
  <Response xmlns="http://schemas.microsoft.com/exchange/autodiscover/mobilesync/responseschema/2006">
    <Culture>en:en</Culture>
    <User>
      <DisplayName><?=htmlspecialchars($displayname, ENT_XML1 | ENT_QUOTES, 'UTF-8');?></DisplayName>
      <EMailAddress><?=$email;?></EMailAddress>
    </User>
    <Action>
      <Settings>
        <Server>
        <Type>MobileSync</Type>
        <Url><?=$autodiscover_config['activesync']['url'];?></Url>
        <Name><?=$autodiscover_config['activesync']['url'];?></Name>
        </Server>
      </Settings>
    </Action>
  </Response>
<?php
  }
?>
</Autodiscover>
<?php
}
?>

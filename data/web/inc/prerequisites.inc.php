<?php

// Slave does not serve UI
/* if (!preg_match('/y|yes/i', getenv('MASTER'))) {
  header('Location: /SOGo', true, 307);
  exit;
}*/

require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/vars.inc.php';
$default_autodiscover_config = $autodiscover_config;

if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/inc/vars.local.inc.php')) {
  include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/vars.local.inc.php';
}
unset($https_port);
$autodiscover_config = array_merge($default_autodiscover_config, $autodiscover_config);

header_remove("X-Powered-By");

// Yubi OTP API
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/Yubico.php';

// Autoload composer
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/vendor/autoload.php';

// Load Sieve
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/sieve/SieveParser.php';

// minifierExtended
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/JSminifierExtended.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/CSSminifierExtended.php';

require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/array_merge_real.php';

// Minify JS
use MatthiasMullie\Minify;
$js_minifier = new JSminifierExtended();
$js_dir = array_diff(scandir('/web/js/build'), array('..', '.'));
foreach ($js_dir as $js_file) {
  $js_minifier->add('/web/js/build/' . $js_file);
}

// Minify CSS
$css_minifier = new CSSminifierExtended();
$css_dir = array_diff(scandir('/web/css/build'), array('..', '.'));
foreach ($css_dir as $css_file) {
  $css_minifier->add('/web/css/build/' . $css_file);
}

// U2F API + T/HOTP API
$u2f = new u2flib_server\U2F('https://' . $_SERVER['HTTP_HOST']);
$qrprovider = new RobThree\Auth\Providers\Qr\QRServerProvider();
$tfa = new RobThree\Auth\TwoFactorAuth($OTP_LABEL, 6, 30, 'sha1', $qrprovider);

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
?>
<center style='font-family:sans-serif;'>Connection to Redis failed.<br /><br />The following error was reported:<br/><?=$e->getMessage();?></center>
<?php
exit;
}

// PDO
// Calculate offset
// $now = new DateTime();
// $mins = $now->getOffset() / 60;
// $sgn = ($mins < 0 ? -1 : 1);
// $mins = abs($mins);
// $hrs = floor($mins / 60);
// $mins -= $hrs * 60;
// $offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);

$dsn = $database_type . ":unix_socket=" . $database_sock . ";dbname=" . $database_name;
$opt = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
  //PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . $offset . "', group_concat_max_len = 3423543543;",
];
try {
  $pdo = new PDO($dsn, $database_user, $database_pass, $opt);
}
catch (PDOException $e) {
// Stop when SQL connection fails
?>
<center style='font-family:sans-serif;'>Connection to database failed.<br /><br />The following error was reported:<br/>  <?=$e->getMessage();?></center>
<?php
exit;
}
// Stop when dockerapi is not available
if (fsockopen("tcp://dockerapi", 443, $errno, $errstr) === false) {
?>
<center style='font-family:sans-serif;'>Connection to dockerapi container failed.<br /><br />The following error was reported:<br/><?=$errno;?> - <?=$errstr;?></center>
<?php
exit;
}

// OAuth2
class mailcowPdo extends OAuth2\Storage\Pdo {
  public function __construct($connection, $config = array()) {
    parent::__construct($connection, $config);
    $this->config['user_table'] = 'mailbox';
  }
  public function checkUserCredentials($username, $password) {
    if (check_login($username, $password) == 'user') {
      return true;
    }
    return false;
  }
  public function getUserDetails($username) {
    return $this->getUser($username);
  }
}
$oauth2_scope_storage = new OAuth2\Storage\Memory(array('default_scope' => 'profile', 'supported_scopes' => array('profile')));
$oauth2_storage = new mailcowPdo(array('dsn' => $dsn, 'username' => $database_user, 'password' => $database_pass));
$oauth2_server = new OAuth2\Server($oauth2_storage, array(
    'refresh_token_lifetime'         => $REFRESH_TOKEN_LIFETIME,
    'access_lifetime'                => $ACCESS_TOKEN_LIFETIME,
));
$oauth2_server->setScopeUtil(new OAuth2\Scope($oauth2_scope_storage));
$oauth2_server->addGrantType(new OAuth2\GrantType\AuthorizationCode($oauth2_storage));
$oauth2_server->addGrantType(new OAuth2\GrantType\UserCredentials($oauth2_storage));
$oauth2_server->addGrantType(new OAuth2\GrantType\RefreshToken($oauth2_storage, array(
    'always_issue_new_refresh_token' => true
)));

function exception_handler($e) {
    if ($e instanceof PDOException) {
      $_SESSION['return'][] = array(
        'type' => 'danger',
        'log' => array(__FUNCTION__),
        'msg' => array('mysql_error', $e)
      );
      return false;
    }
    else {
      $_SESSION['return'][] = array(
        'type' => 'danger',
        'log' => array(__FUNCTION__),
        'msg' => 'An unknown error occured: ' . print_r($e, true)
      );
      return false;
    }
}
set_exception_handler('exception_handler');

// TODO: Move function
function get_remote_ip($anonymize = null) {
  global $ANONYMIZE_IPS;
  if ($anonymize === null) {
    $anonymize = $ANONYMIZE_IPS;
  }
  elseif ($anonymize !== true && $anonymize !== false)  {
    $anonymize = true;
  }
  $remote = $_SERVER['REMOTE_ADDR'];
  if (filter_var($remote, FILTER_VALIDATE_IP) === false) {
    return '0.0.0.0';
  }
  if ($anonymize) {
    if (strlen(inet_pton($remote)) == 4) {
      return inet_ntop(inet_pton($remote) & inet_pton("255.255.255.0"));
    }
    elseif (strlen(inet_pton($remote)) == 16) {
      return inet_ntop(inet_pton($remote) & inet_pton('ffff:ffff:ffff:ffff:0000:0000:0000:0000'));
    }
  }
  else {
    return $remote;
  }
}

// Load core functions first
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/sessions.inc.php';

// IMAP lib
// use Ddeboer\Imap\Server;
// $imap_server = new Server('dovecot', 143, '/imap/tls/novalidate-cert');

// Set language
if (!isset($_SESSION['mailcow_locale']) && !isset($_COOKIE['mailcow_locale'])) {
  if ($DETECT_LANGUAGE && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $header_lang = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));
    if (in_array($header_lang, $AVAILABLE_LANGUAGES)) {
      $_SESSION['mailcow_locale'] = $header_lang;
    }
  }
  else {
    $_SESSION['mailcow_locale'] = strtolower(trim($DEFAULT_LANG));
  }
}
if (isset($_COOKIE['mailcow_locale'])) {
  (preg_match('/^[a-z]{2}$/', $_COOKIE['mailcow_locale'])) ? $_SESSION['mailcow_locale'] = $_COOKIE['mailcow_locale'] : setcookie("mailcow_locale", "", time() - 300);
}
if (isset($_GET['lang']) && in_array($_GET['lang'], $AVAILABLE_LANGUAGES)) {
  $_SESSION['mailcow_locale'] = $_GET['lang'];
  setcookie("mailcow_locale", $_GET['lang'], time()+30758400); // one year
}

/*
 * load language
 */
$lang = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/lang/lang.en.json'), true);

$langFile = $_SERVER['DOCUMENT_ROOT'] . '/lang/lang.'.$_SESSION['mailcow_locale'].'.json';
if(file_exists($langFile)) {
  $lang = array_merge_real($lang, json_decode(file_get_contents($langFile), true));
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.acl.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.app_passwd.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.mailbox.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.customize.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.address_rewriting.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.domain_admin.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.admin.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.quarantine.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.quota_notification.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.policy.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.dkim.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.fwdhost.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.mailq.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.oauth2.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.pushover.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.ratelimit.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.transports.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.rspamd.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.tls_policy_maps.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.fail2ban.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.docker.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.presets.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/init_db.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/triggers.inc.php';
init_db_schema();
if (isset($_SESSION['mailcow_cc_role'])) {
  // if ($_SESSION['mailcow_cc_role'] == 'user') {
    // list($master_user, $master_passwd) = explode(':', file_get_contents('/etc/sogo/sieve.creds'));
    // $imap_connection = $imap_server->authenticate($_SESSION['mailcow_cc_username'] . '*' . trim($master_user), trim($master_passwd));
    // $master_user = $master_passwd = null;
  // }
  acl('to_session');
}
$UI_TEXTS = customize('get', 'ui_texts');

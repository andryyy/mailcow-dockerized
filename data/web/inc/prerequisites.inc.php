<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/vars.inc.php';
$default_autodiscover_config = $autodiscover_config;
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/inc/vars.local.inc.php')) {
  include_once $_SERVER['DOCUMENT_ROOT'] . '/inc/vars.local.inc.php';
}
$autodiscover_config = array_merge($default_autodiscover_config, $autodiscover_config);

header_remove("X-Powered-By");

// Yubi OTP API
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/Yubico.php';

// Autoload composer
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/vendor/autoload.php';

// Load Sieve
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/lib/sieve/SieveParser.php';

// U2F API + T/HOTP API
$u2f = new u2flib_server\U2F('https://' . $_SERVER['HTTP_HOST']);
$tfa = new RobThree\Auth\TwoFactorAuth($OTP_LABEL);

// Redis
$redis = new Redis();
$redis->connect('redis-mailcow', 6379);


// PDO
// Calculate offset
$now = new DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);

$dsn_mc = $database_type . ":host=" . $database_host . ";dbname=" . $database_name;
$dsn_mm = $database_type . ":host=" . $database_host . ";dbname=" . $database_name . '_mm';
$opt = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,
  PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . $offset . "', group_concat_max_len = 3423543543;",
];
try {
  $pdo = new PDO($dsn_mc, $database_user, $database_pass, $opt);
  $pdo_mm = new PDO($dsn_mm, $database_user, $database_pass, $opt);
}
catch (PDOException $e) {
?>
<center style='font-family: "Lucida Sans Unicode", "Lucida Grande", Verdana, Arial, Helvetica, sans-serif;'>Connection to database failed.<br /><br />The following error was reported:<br/>  <?=$e->getMessage();?></center>
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
$oauth2_storage = new mailcowPdo(array('dsn' => $dsn_mc, 'username' => $database_user, 'password' => $database_pass));
$oauth2_server = new OAuth2\Server($oauth2_storage, array(
    'always_issue_new_refresh_token' => true,
    'refresh_token_lifetime'         => 2678400,
));
$oauth2_server->setScopeUtil(new OAuth2\Scope($oauth2_scope_storage));
$oauth2_server->addGrantType(new OAuth2\GrantType\AuthorizationCode($oauth2_storage));
$oauth2_server->addGrantType(new OAuth2\GrantType\UserCredentials($oauth2_storage));
$oauth2_server->addGrantType(new OAuth2\GrantType\RefreshToken($oauth2_storage));

require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/sessions.inc.php';

// Set language
if (!isset($_SESSION['mailcow_locale']) && !isset($_COOKIE['mailcow_locale'])) {
  if ($DETECT_LANGUAGE && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $header_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    foreach ($AVAILABLE_LANGUAGES as $available_lang) {
      if ($header_lang == $available_lang) {
        $_SESSION['mailcow_locale'] = strtolower(trim($header_lang));
      }
    }
  }
  else {
    $_SESSION['mailcow_locale'] = strtolower(trim($DEFAULT_LANG));
  }
}
if (isset($_COOKIE['mailcow_locale'])) {
  $_SESSION['mailcow_locale'] = $_COOKIE['mailcow_locale'];
}
if (isset($_GET['lang']) && in_array($_GET['lang'], $AVAILABLE_LANGUAGES)) {
  $_SESSION['mailcow_locale'] = $_GET['lang'];
  setcookie("mailcow_locale", $_GET['lang'], time()+30758400); // one year
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/lang/lang.en.php';
include $_SERVER['DOCUMENT_ROOT'] . '/lang/lang.'.$_SESSION['mailcow_locale'].'.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.mailbox.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.customize.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.address_rewriting.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.domain_admin.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.quarantine.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.policy.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.dkim.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.fwdhost.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.relayhost.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.fail2ban.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.oauth2.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/functions.docker.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/init_db.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/triggers.inc.php';
init_db_schema($admin_default_pass);
if (isset($_SESSION['mailcow_cc_role'])) {
  set_acl();
}
$UI_TEXTS = customize('get', 'ui_texts');

<?php
error_reporting(E_ERROR);
//error_reporting(E_ALL);

/*
PLEASE USE THE FILE "vars.local.inc.php" TO OVERWRITE SETTINGS AND MAKE THEM PERSISTENT!
This file will be reset on upgrades.
*/

// SQL database connection variables
$database_type = 'mysql';
$database_sock = '/var/run/mysqld/mysqld.sock';
$database_host = 'mysql';
$database_user = getenv('DBUSER');
$database_pass = getenv('DBPASS');
$database_name = getenv('DBNAME');

// Other variables
$mailcow_hostname = getenv('MAILCOW_HOSTNAME');

// Autodiscover settings
// ===
// Auto-detect HTTPS port =>
$https_port = strpos($_SERVER['HTTP_HOST'], ':');
if ($https_port === FALSE) {
  $https_port = 443;
} else {
  $https_port = substr($_SERVER['HTTP_HOST'], $https_port+1);
}

// Alternatively select port here =>
//$https_port = 1234;
// Other settings =>
$autodiscover_config = array(
  // General autodiscover service type: "activesync" or "imap"
  // emClient uses autodiscover, but does not support ActiveSync. mailcow excludes emClient from ActiveSync.
  // With SOGo disabled, the type will always fallback to imap. CalDAV and CardDAV will be excluded, too.
  'autodiscoverType' => 'activesync',
  // If autodiscoverType => activesync, also use ActiveSync (EAS) for Outlook desktop clients (>= Outlook 2013 on Windows)
  // Outlook for Mac does not support ActiveSync
  'useEASforOutlook' => 'no',
  // Please don't use STARTTLS-enabled service ports in the "port" variable.
  // The autodiscover service will always point to SMTPS and IMAPS (TLS-wrapped services).
  // The autoconfig service will additionally announce the STARTTLS-enabled ports, specified in the "tlsport" variable.
  'imap' => array(
    'server' => $mailcow_hostname,
    'port' => end(explode(':', getenv('IMAPS_PORT'))),
    'tlsport' => end(explode(':', getenv('IMAP_PORT'))),
  ),
  'pop3' => array(
    'server' => $mailcow_hostname,
    'port' => end(explode(':', getenv('POPS_PORT'))),
    'tlsport' => end(explode(':', getenv('POP_PORT'))),
  ),
  'smtp' => array(
    'server' => $mailcow_hostname,
    'port' => end(explode(':', getenv('SMTPS_PORT'))),
    'tlsport' => end(explode(':', getenv('SUBMISSION_PORT'))),
  ),
  'activesync' => array(
    'url' => 'https://'.$mailcow_hostname.($https_port == 443 ? '' : ':'.$https_port).'/Microsoft-Server-ActiveSync',
  ),
  'caldav' => array(
    'server' => $mailcow_hostname,
    'port' => $https_port,
  ),
  'carddav' => array(
    'server' => $mailcow_hostname,
    'port' => $https_port,
  ),
);

// If false, we will use DEFAULT_LANG
// Uses HTTP_ACCEPT_LANGUAGE header
$DETECT_LANGUAGE = true;

// Change default language
$DEFAULT_LANG = 'en';

// Available languages
$AVAILABLE_LANGUAGES = array('ca', 'cs', 'de', 'en', 'es', 'fi', 'fr', 'it', 'lv', 'nl', 'pl', 'pt', 'ru', 'sk', 'sv');

// Change theme (default: lumen)
// Needs to be one of those: cerulean, cosmo, cyborg, darkly, flatly, journal, lumen, paper, readable, sandstone,
// simplex, slate, spacelab, superhero, united, yeti
// See https://bootswatch.com/
// WARNING: Only lumen is loaded locally. Enabling any other theme, will download external sources.
$DEFAULT_THEME = 'lumen';

// Password complexity as regular expression
// Min. 6 characters
$PASSWD_REGEP = '.{6,}';
// Min. 6 characters, which must include at least one uppercase letter, one lowercase letter and one number
// $PASSWD_REGEP = '^(?=.*[A-Z])(?=.*[0-9])(?=.*[a-z]).{6,}$';
// Min. 6 characters, which must include at least one letter and one number
// $PASSWD_REGEP = '^(?=.*[0-9])(?=.*[A-Za-z]).{6,}$';

// Show DKIM private keys - false by default
$SHOW_DKIM_PRIV_KEYS = false;

// mailcow Apps - buttons on login screen
$MAILCOW_APPS = array(
  array(
    'name' => 'Webmail',
    'link' => '/SOGo/',
  )
);

// Rows until pagination begins
$PAGINATION_SIZE = 20;

// Default number of rows/lines to display (log table)
$LOG_LINES = 1000;

// Rows until pagination begins (log table)
$LOG_PAGINATION_SIZE = 50;

// Session lifetime in seconds
$SESSION_LIFETIME = 10800;

// Label for OTP devices
$OTP_LABEL = "mailcow UI";

// Default "to" address in relay test tool
$RELAY_TO = "null@hosted.mailcow.de";

// How long to wait (in s) for cURL Docker requests
$DOCKER_TIMEOUT = 60;

// Anonymize IPs logged via UI
$ANONYMIZE_IPS = true;

// Split DKIM key notation (bind format)
$SPLIT_DKIM_255 = false;

// OAuth2 settings
$REFRESH_TOKEN_LIFETIME = 2678400;
$ACCESS_TOKEN_LIFETIME = 86400;
// Logout from mailcow after first OAuth2 session profile request
$OAUTH2_FORGET_SESSION_AFTER_LOGIN = false;

// MAILBOX_DEFAULT_ATTRIBUTES define default attributes for new mailboxes
// These settings will not change existing mailboxes

// Force incoming TLS for new mailboxes by default
$MAILBOX_DEFAULT_ATTRIBUTES['tls_enforce_in'] = false;

// Force outgoing TLS for new mailboxes by default
$MAILBOX_DEFAULT_ATTRIBUTES['tls_enforce_out'] = false;

// Force password change on next login (only allows login to mailcow UI)
$MAILBOX_DEFAULT_ATTRIBUTES['force_pw_update'] = false;

// Enable SOGo access (set to false to disable access by default)
$MAILBOX_DEFAULT_ATTRIBUTES['sogo_access'] = true;

// Send notification when quarantine is not empty (never, hourly, daily, weekly)
$MAILBOX_DEFAULT_ATTRIBUTES['quarantine_notification'] = 'hourly';

// Default mailbox format, should not be changed unless you know exactly, what you do, keep the trailing ":"
// Check dovecot.conf for further changes (e.g. shared namespace)
$MAILBOX_DEFAULT_ATTRIBUTES['mailbox_format'] = 'maildir:';

// Show last IMAP and POP3 logins
$SHOW_LAST_LOGIN = true;


// Set visible Rspamd maps in mailcow UI, do not change unless you know what you are doing
$RSPAMD_MAPS = array(
  'regex' => array(
    'Header-From: Blacklist' => 'global_mime_from_blacklist.map',
    'Header-From: Whitelist' => 'global_mime_from_whitelist.map',
    'Envelope Sender Blacklist' => 'global_smtp_from_blacklist.map',
    'Envelope Sender Whitelist' => 'global_smtp_from_whitelist.map',
    'Recipient Blacklist' => 'global_rcpt_blacklist.map',
    'Recipient Whitelist' => 'global_rcpt_whitelist.map',
    'Fishy TLDS (only fired in combination with bad words)' => 'fishy_tlds.map',
    'Bad Words (only fired in combination with fishy TLDs)' => 'bad_words.map',
    'Bad Words DE (only fired in combination with fishy TLDs)' => 'bad_words_de.map',
    'Bad Languages' => 'bad_languages.map',
    'Bulk Mail Headers' => 'bulk_header.map',
    'Monitoring Hosts' => 'monitoring_nolog.map'
  )
);

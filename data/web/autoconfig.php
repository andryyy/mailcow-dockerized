<?php
require_once __DIR__ . '/inc/vars.inc.php';
$default_autodiscover_config = $autodiscover_config;
if(file_exists(__DIR__ . '/inc/vars.local.inc.php')) {
  include_once __DIR__ . '/inc/vars.local.inc.php';
}
$autodiscover_config = array_merge($default_autodiscover_config, $autodiscover_config);

error_reporting(0);

if (empty($mailcow_hostname)) {
  exit();
}

$domain_dot = strpos($_SERVER['HTTP_HOST'], '.');
$domain_port = strpos($_SERVER['HTTP_HOST'], ':');
if ($domain_port === FALSE) {
  $domain = substr($_SERVER['HTTP_HOST'], $domain_dot+1);
  $port = 443;
}
else {
  $domain = substr($_SERVER['HTTP_HOST'], $domain_dot+1, $domain_port-$domain_dot-1);
  $port = substr($_SERVER['HTTP_HOST'], $domain_port+1);
}

header('Content-Type: application/xml');
?>
<?= '<?xml version="1.0"?>'; ?>
<clientConfig version="1.1">
    <emailProvider id="<?=$mailcow_hostname; ?>">
      <domain>%EMAILDOMAIN%</domain>
      <displayName>A mailcow mail server</displayName>
      <displayShortName>mail server</displayShortName>

      <incomingServer type="imap">
         <hostname><?=$autodiscover_config['imap']['server']; ?></hostname>
         <port><?=$autodiscover_config['imap']['port']; ?></port>
         <socketType>SSL</socketType>
         <username>%EMAILADDRESS%</username>
         <authentication>password-cleartext</authentication>
      </incomingServer>
      <incomingServer type="imap">
         <hostname><?=$autodiscover_config['imap']['server']; ?></hostname>
         <port><?=$autodiscover_config['imap']['tlsport']; ?></port>
         <socketType>STARTTLS</socketType>
         <username>%EMAILADDRESS%</username>
         <authentication>password-cleartext</authentication>
      </incomingServer>

<?php
$records = dns_get_record('_pop3s._tcp.' . $domain, DNS_SRV); // check if POP3 is announced as "not provided" via SRV record
if (count($records) == 0 || $records[0]['target'] != '') { ?>
      <incomingServer type="pop3">
         <hostname><?=$autodiscover_config['pop3']['server']; ?></hostname>
         <port><?=$autodiscover_config['pop3']['port']; ?></port>
         <socketType>SSL</socketType>
         <username>%EMAILADDRESS%</username>
         <authentication>password-cleartext</authentication>
      </incomingServer>
<?php } ?>
<?php
$records = dns_get_record('_pop3._tcp.' . $domain, DNS_SRV); // check if POP3 is announced as "not provided" via SRV record
if (count($records) == 0 || $records[0]['target'] != '') { ?>
      <incomingServer type="pop3">
         <hostname><?=$autodiscover_config['pop3']['server']; ?></hostname>
         <port><?=$autodiscover_config['pop3']['tlsport']; ?></port>
         <socketType>STARTTLS</socketType>
         <username>%EMAILADDRESS%</username>
         <authentication>password-cleartext</authentication>
      </incomingServer>
<?php } ?>

      <outgoingServer type="smtp">
         <hostname><?=$autodiscover_config['smtp']['server']; ?></hostname>
         <port><?=$autodiscover_config['smtp']['port']; ?></port>
         <socketType>SSL</socketType>
         <username>%EMAILADDRESS%</username>
         <authentication>password-cleartext</authentication>
      </outgoingServer>
      <outgoingServer type="smtp">
         <hostname><?=$autodiscover_config['smtp']['server']; ?></hostname>
         <port><?=$autodiscover_config['smtp']['tlsport']; ?></port>
         <socketType>STARTTLS</socketType>
         <username>%EMAILADDRESS%</username>
         <authentication>password-cleartext</authentication>
      </outgoingServer>

      <enable visiturl="https://<?=$mailcow_hostname; ?><?php if ($port != 443) echo ':'.$port; ?>/admin.php">
         <instruction>If you didn't change the password given to you by the administrator or if you didn't change it in a long time, please consider doing that now.</instruction>
         <instruction lang="de">Sollten Sie das Ihnen durch den Administrator vergebene Passwort noch nicht geändert haben, empfehlen wir dies nun zu tun. Auch ein altes Passwort sollte aus Sicherheitsgründen geändert werden.</instruction>
      </enable>

    </emailProvider>

    <webMail>
      <loginPage url="https://<?=$mailcow_hostname; ?><?php if ($port != 443) echo ':'.$port; ?>/SOGo/" />
    </webMail>
</clientConfig>

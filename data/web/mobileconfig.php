<?php
require_once 'inc/prerequisites.inc.php';

if (empty($mailcow_hostname)) {
  exit();
}
if (!isset($_SESSION['mailcow_cc_role']) || $_SESSION['mailcow_cc_role'] != 'user') {
  session_destroy();
  // probably better than appending the whole current http query string
  $append_get = (isset($_GET['only_email'])) ? '&only_email' : '';
  header('Location: index.php?mobileconfig' . $append_get);
  die();
}

error_reporting(0);

header('Content-Type: application/x-apple-aspen-config');
header('Content-Disposition: attachment; filename="'.$UI_TEXTS['main_name'].'.mobileconfig"');

$email = $_SESSION['mailcow_cc_username'];
$domain = explode('@', $_SESSION['mailcow_cc_username'])[1];
$identifier = implode('.', array_reverse(preg_split( '/(@|\.)/', $email))) . '.appleprofile.'.preg_replace('/[^a-zA-Z0-9]+/', '', $UI_TEXTS['main_name']);

try {
  $stmt = $pdo->prepare("SELECT `name` FROM `mailbox` WHERE `username`= :username");
  $stmt->execute(array(':username' => $email));
  $MailboxData = $stmt->fetch(PDO::FETCH_ASSOC);
  $displayname = htmlspecialchars(empty($MailboxData['name']) ? $email : $MailboxData['name'], ENT_NOQUOTES);
}
catch(PDOException $e) {
  $displayname = $email;
}

if (isset($_GET['only_email'])) {
  $onlyEmailAccount = true;
  $description = 'IMAP';  
} else {
  $onlyEmailAccount = false;
  $description = 'IMAP, CalDAV, CardDAV'; 
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
  <dict>
    <key>PayloadContent</key>
    <array>
      <dict>
        <key>EmailAccountDescription</key>
        <string><?=$email?></string>
        <key>EmailAccountType</key>
        <string>EmailTypeIMAP</string>
        <key>EmailAccountName</key>
        <string><?=$displayname?></string>
        <key>EmailAddress</key>
        <string><?=$email?></string>
        <key>IncomingMailServerAuthentication</key>
        <string>EmailAuthPassword</string>
        <key>IncomingMailServerHostName</key>
        <string><?=$autodiscover_config['imap']['server']?></string>
        <key>IncomingMailServerPortNumber</key>
        <integer><?=$autodiscover_config['imap']['port']?></integer>
        <key>IncomingMailServerUseSSL</key>
        <true/>
        <key>IncomingMailServerUsername</key>
        <string><?=$email?></string>
        <key>OutgoingMailServerAuthentication</key>
        <string>EmailAuthPassword</string>
        <key>OutgoingMailServerHostName</key>
        <string><?=$autodiscover_config['smtp']['server']?></string>
        <key>OutgoingMailServerPortNumber</key>
        <integer><?=$autodiscover_config['smtp']['port']?></integer>
        <key>OutgoingMailServerUseSSL</key>
        <true/>
        <key>OutgoingMailServerUsername</key>
        <string><?=$email?></string>
        <key>OutgoingPasswordSameAsIncomingPassword</key>
        <true/>
        <key>PayloadDescription</key>
        <string>Configures email account.</string>
        <key>PayloadDisplayName</key>
        <string>IMAP Account (<?=$email?>)</string>
        <key>PayloadIdentifier</key>
        <string><?=$identifier?>.email</string>
        <key>PayloadOrganization</key>
        <string></string>
        <key>PayloadType</key>
        <string>com.apple.mail.managed</string>
        <key>PayloadUUID</key>
        <string><?=getGUID()?></string>
        <key>PayloadVersion</key>
        <integer>1</integer>
        <key>PreventAppSheet</key>
        <false/>
        <key>PreventMove</key>
        <false/>
        <key>SMIMESigningUserOverrideable</key>
        <true/>
        <key>SMIMESigningCertificateUUIDUserOverrideable</key>
        <true/>
        <key>SMIMEEncryptByDefaultUserOverrideable</key>
        <true/>
        <key>SMIMEEncryptionCertificateUUIDUserOverrideable</key>
        <true/>
        <key>SMIMEEnableEncryptionPerMessageSwitch</key>
        <true/>
      </dict>
      <?php if($onlyEmailAccount === false): ?>
      <dict>
        <key>CalDAVAccountDescription</key>
        <string><?=$email?></string>
        <key>CalDAVHostName</key>
        <string><?=$autodiscover_config['caldav']['server']?></string>
        <key>CalDAVPort</key>
        <real><?=$autodiscover_config['caldav']['port']?></real>
        <key>CalDAVPrincipalURL</key>
        <string>/SOGo/dav/<?=$email?></string>
        <key>CalDAVUseSSL</key>
        <true/>
        <key>CalDAVUsername</key>
        <string><?=$email?></string>
        <key>PayloadDescription</key>
        <string>Configures CalDAV account.</string>
        <key>PayloadDisplayName</key>
        <string>CalDAV (<?=$email?>)</string>
        <key>PayloadIdentifier</key>
        <string><?=$identifier?>.CalDAV</string>
        <key>PayloadOrganization</key>
        <string></string>
        <key>PayloadType</key>
        <string>com.apple.caldav.account</string>
        <key>PayloadUUID</key>
        <string><?=getGUID()?></string>
        <key>PayloadVersion</key>
        <integer>1</integer>
      </dict>
      <dict>
        <key>CardDAVAccountDescription</key>
        <string><?=$email?></string>
        <key>CardDAVHostName</key>
        <string><?=$autodiscover_config['carddav']['server']?></string>
        <key>CardDAVPort</key>
        <integer><?=$autodiscover_config['carddav']['port']?></integer>
        <key>CardDAVPrincipalURL</key>
        <string>/SOGo/dav/<?=$email?></string>
        <key>CardDAVUseSSL</key>
        <true/>
        <key>CardDAVUsername</key>
        <string><?=$email?></string>
        <key>PayloadDescription</key>
        <string>Configures CardDAV accounts</string>
        <key>PayloadDisplayName</key>
        <string>CardDAV (<?=$email?>)</string>
        <key>PayloadIdentifier</key>
        <string><?=$identifier?>.carddav</string>
        <key>PayloadOrganization</key>
        <string></string>
        <key>PayloadType</key>
        <string>com.apple.carddav.account</string>
        <key>PayloadUUID</key>
        <string><?=getGUID()?></string>
        <key>PayloadVersion</key>
        <integer>1</integer>
      </dict>
      <?php endif; ?>
    </array>
    <key>PayloadDescription</key>
    <string><?=$description?></string>
    <key>PayloadDisplayName</key>
    <string><?=$email?></string>
    <key>PayloadIdentifier</key>
    <string><?=$identifier?></string>
    <key>PayloadOrganization</key>
    <string><?=$UI_TEXTS['main_name']?></string>
    <key>PayloadRemovalDisallowed</key>
    <false/>
    <key>PayloadType</key>
    <string>Configuration</string>
    <key>PayloadUUID</key>
    <string><?=getGUID()?></string>
    <key>PayloadVersion</key>
    <integer>1</integer>
  </dict>
</plist>

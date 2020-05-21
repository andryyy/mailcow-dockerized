<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';

if (!$oauth2_server->verifyResourceRequest(OAuth2\Request::createFromGlobals())) {
  $oauth2_server->getResponse()->send();
  die;
}
$token = $oauth2_server->getAccessTokenData(OAuth2\Request::createFromGlobals());
$stmt = $pdo->prepare("SELECT * FROM `mailbox` WHERE `username` = :username AND `active` = '1'");
$stmt->execute(array(':username' => $token['user_id']));
$mailbox = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($mailbox)) {
  if ($token['scope'] == 'profile') {
    echo json_encode(array(
      'success' => true,
      'username' => $token['user_id'],
      'identifier' => $token['user_id'],
      'email' => (!empty($mailbox['username']) ? $mailbox['username'] : ''),
      'full_name' => (!empty($mailbox['name']) ? $mailbox['name'] : 'mailcow administrative user'),
      'displayName' => (!empty($mailbox['name']) ? $mailbox['name'] : 'mailcow administrative user'),
      'created' => (!empty($mailbox['created']) ? $mailbox['created'] : ''),
      'modified' => (!empty($mailbox['modified']) ? $mailbox['modified'] : ''),
      'active' => (!empty($mailbox['active']) ? $mailbox['active'] : ''),
    ));
    exit;
  }
}
echo json_encode(array(
  'success' => false
));

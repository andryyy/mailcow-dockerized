<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';
header('Content-Type: text/plain');
if (!isset($_SESSION['mailcow_cc_role'])) {
	exit();
}
if (isset($_GET['token']) && ctype_alnum($_GET['token'])) {
  echo $tfa->getQRCodeImageAsDataUri($_SESSION['mailcow_cc_username'], $_GET['token']);
}
?>

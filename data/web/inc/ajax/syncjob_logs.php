<?php
require_once __DIR__ . '/../prerequisites.inc.php';
header('Content-Type: text/plain');
if (!isset($_SESSION['mailcow_cc_role'])) {
	exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
  if ($details = mailbox('get', 'syncjob_details', intval($_GET['id']))) {
    echo (empty($details['log'])) ? '-' : $details['log'];
  }
}

?>

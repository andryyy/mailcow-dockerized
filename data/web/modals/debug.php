<?php
if (!isset($_SESSION['mailcow_cc_role'])) {
	header('Location: /');
	exit();
}
?>

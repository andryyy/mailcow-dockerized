<?php
if (isset($_POST["verify_tfa_login"])) {
  if (verify_tfa_login($_SESSION['pending_mailcow_cc_username'], $_POST["token"])) {
    $_SESSION['mailcow_cc_username'] = $_SESSION['pending_mailcow_cc_username'];
    $_SESSION['mailcow_cc_role'] = $_SESSION['pending_mailcow_cc_role'];
    unset($_SESSION['pending_mailcow_cc_username']);
    unset($_SESSION['pending_mailcow_cc_role']);
    unset($_SESSION['pending_tfa_method']);
		header("Location: /user");
  }
}

if (isset($_POST["quick_release"])) {
	quarantine('quick_release', $_POST["quick_release"]);
}

if (isset($_POST["quick_delete"])) {
	quarantine('quick_delete', $_POST["quick_delete"]);
}

if (isset($_POST["login_user"]) && isset($_POST["pass_user"])) {
	$login_user = strtolower(trim($_POST["login_user"]));
	$as = check_login($login_user, $_POST["pass_user"]);
	if ($as == "admin") {
		$_SESSION['mailcow_cc_username'] = $login_user;
		$_SESSION['mailcow_cc_role'] = "admin";
    $_SESSION['mailcow_cc_last_login'] = last_login($login_user);
		header("Location: /admin");
	}
	elseif ($as == "domainadmin") {
		$_SESSION['mailcow_cc_username'] = $login_user;
		$_SESSION['mailcow_cc_role'] = "domainadmin";
    $_SESSION['mailcow_cc_last_login'] = last_login($login_user);
		header("Location: /mailbox");
	}
	elseif ($as == "user") {
		$_SESSION['mailcow_cc_username'] = $login_user;
		$_SESSION['mailcow_cc_role'] = "user";
    $_SESSION['mailcow_cc_last_login'] = last_login($login_user);
    $http_parameters = explode('&', $_SESSION['index_query_string']);
    unset($_SESSION['index_query_string']);
    if (in_array('mobileconfig', $http_parameters)) {
      if (in_array('only_email', $http_parameters)) {
        header("Location: /mobileconfig.php?email_only");
        die();
      }
      header("Location: /mobileconfig.php");
      die();
    }
		header("Location: /user");
	}
	elseif ($as != "pending") {
    unset($_SESSION['pending_mailcow_cc_username']);
    unset($_SESSION['pending_mailcow_cc_role']);
    unset($_SESSION['pending_tfa_method']);
		unset($_SESSION['mailcow_cc_username']);
		unset($_SESSION['mailcow_cc_role']);
	}
}

if (isset($_SESSION['mailcow_cc_role']) && $_SESSION['acl']['login_as'] == "1") {
	if (isset($_GET["duallogin"])) {
    $duallogin = html_entity_decode(rawurldecode($_GET["duallogin"]));
    if (filter_var($duallogin, FILTER_VALIDATE_EMAIL)) {
      if (!empty(mailbox('get', 'mailbox_details', $duallogin))) {
        $_SESSION["dual-login"]["username"] = $_SESSION['mailcow_cc_username'];
        $_SESSION["dual-login"]["role"]     = $_SESSION['mailcow_cc_role'];
        $_SESSION['mailcow_cc_username']    = $duallogin;
        $_SESSION['mailcow_cc_role']        = "user";
        header("Location: /user");
      }
    }
    else {
      if (!empty(domain_admin('details', $duallogin))) {
        $_SESSION["dual-login"]["username"] = $_SESSION['mailcow_cc_username'];
        $_SESSION["dual-login"]["role"]     = $_SESSION['mailcow_cc_role'];
        $_SESSION['mailcow_cc_username']    = $duallogin;
        $_SESSION['mailcow_cc_role']        = "domainadmin";
        header("Location: /user");
      }
    }
  }
}

if (isset($_SESSION['mailcow_cc_role']) && ($_SESSION['mailcow_cc_role'] == "admin" || $_SESSION['mailcow_cc_role'] == "domainadmin")) {
	if (isset($_POST["set_tfa"])) {
		set_tfa($_POST);
	}
	if (isset($_POST["unset_tfa_key"])) {
		unset_tfa_key($_POST);
	}
}
if (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == "admin") {
  // TODO: Move file upload to API?
	if (isset($_POST["submit_main_logo"])) {
    if ($_FILES['main_logo']['error'] == 0) {
      customize('add', 'main_logo', $_FILES);
    }
	}
	if (isset($_POST["reset_main_logo"])) {
    customize('delete', 'main_logo');
	}
  // Some actions will not be available via API
	if (isset($_POST["license_validate_now"])) {
		license('verify');
	}
  if (isset($_POST["admin_api"])) {
    if (isset($_POST["admin_api"]["ro"])) {
      admin_api('ro', 'edit', $_POST);
    }
    elseif (isset($_POST["admin_api"]["rw"])) {
      admin_api('rw', 'edit', $_POST);
    }
	}
  if (isset($_POST["admin_api_regen_key"])) {
    if (isset($_POST["admin_api_regen_key"]["ro"])) {
      admin_api('ro', 'regen_key', $_POST);
    }
    elseif (isset($_POST["admin_api_regen_key"]["rw"])) {
      admin_api('rw', 'regen_key', $_POST);
    }
	}
	if (isset($_POST["rspamd_ui"])) {
		rspamd_ui('edit', $_POST);
	}
	if (isset($_POST["mass_send"])) {
		sys_mail($_POST);
	}
}
?>

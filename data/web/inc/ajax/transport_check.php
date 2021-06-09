<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/prerequisites.inc.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/inc/vars.inc.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

error_reporting(0);
if (isset($_SESSION['mailcow_cc_role']) && $_SESSION['mailcow_cc_role'] == "admin") {
  $transport_id = intval($_GET['transport_id']);
  $transport_type = $_GET['transport_type'];
  if (isset($_GET['mail_from']) && filter_var($_GET['mail_from'], FILTER_VALIDATE_EMAIL)) {
    $mail_from = $_GET['mail_from'];
  }
  else {
    $mail_from = "relay@example.org";
  }
  if (isset($_GET['mail_rcpt']) && filter_var($_GET['mail_rcpt'], FILTER_VALIDATE_EMAIL)) {
    $mail_rcpt = $_GET['mail_rcpt'];
  }
  else {
    $mail_rcpt = "null@hosted.mailcow.de";
  }
  if ($transport_type == 'transport-map') {
    $transport_details = transport('details', $transport_id);
    $nexthop = $transport_details['nexthop'];
  }
  elseif ($transport_type == 'sender-dependent') {
    $transport_details = relayhost('details', $transport_id);
    $nexthop = $transport_details['hostname'];
  }
  if (!empty($transport_details)) {
    // Remove [ and ]
    $hostname_w_port = preg_replace('/\[|\]/', '', $nexthop);
    preg_match('/\[.+\](:.+)/', $nexthop, $hostname_port_match);
    preg_match('/\[\d\.\d\.\d\.\d\](:.+)/', $nexthop, $ipv4_port_match);
    $has_bracket_and_port = (isset($hostname_port_match[1])) ? true : false;
    $is_ipv4_and_has_port = (isset($ipv4_port_match[1])) ? true : false;
    $skip_lookup_mx = strpos($nexthop, '[');
    // Explode to hostname and port
    if ($has_bracket_and_port) {
      $port = substr($hostname_w_port, strrpos($hostname_w_port, ':') + 1);
      $hostname = preg_replace('/'. preg_quote(':' . $port, '/') . '$/', '', $hostname_w_port);
      if (filter_var($hostname, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $hostname = '[' . $hostname . ']';
      }
    }
    else {
      if ($is_ipv4_and_has_port) {
        $port = substr($hostname_w_port, strrpos($hostname_w_port, ':') + 1);
        $hostname = preg_replace('/'. preg_quote(':' . $port, '/') . '$/', '', $hostname_w_port);
      }
      if (filter_var($hostname_w_port, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
        $hostname = $hostname_w_port;
        $port = null;
      }
      elseif (filter_var($hostname_w_port, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
        $hostname = '[' . $hostname_w_port . ']';
        $port = null;
      }
      else {
        $hostname = preg_replace('/'. preg_quote(':' . $port, '/') . '$/', '', $hostname_w_port);
        $port = null;
      }
    }
    // Try to get MX if host is not [host]
    if ($skip_lookup_mx === false) {
      getmxrr($hostname, $mx_records, $mx_weight);
      if (!empty($mx_records)) {
        for ($i = 0; $i < count($mx_records); $i++) {
          $mxs[$mx_records[$i]] = $mx_weight[$i];
        }
        asort ($mxs);
        $records = array_keys($mxs);
        echo 'Using first matched primary MX for "' . $hostname . '": ';
        $hostname = $records[0];
        echo $hostname . '<br>';
      }
      else {
        echo 'No MX records for ' . $hostname . ' were found in DNS, skipping and using hostname as next-hop.<br>';
      }
    }
    // Use port 25 if no port was given
    $port = (empty($port)) ? 25 : $port;
    $username = $transport_details['username'];
    $password = $transport_details['password'];

    $mail = new PHPMailer;
    $mail->Timeout = 15;
    $mail->SMTPOptions = array(
      'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      )
    );
    $mail->SMTPDebug = 3;
    // smtp: and smtp_enforced_tls: do not support wrapped tls, todo?
    // change postfix map to detect wrapped tls or add a checkbox to toggle wrapped tls
    // if ($port == 465) {
      // $mail->SMTPSecure = "ssl";
    // }
    $mail->Debugoutput = function($str, $level) {
      foreach(preg_split("/((\r?\n)|(\r\n?)|\n)/", $str) as $line){
        if (empty($line)) { continue; }
        if (preg_match("/SERVER \-\> CLIENT: 2\d\d.+/i", $line)) {
          echo '<span style="color:darkgreen;font-weight:bold">' . htmlspecialchars($line) . '</span><br>';
        }
        elseif (preg_match("/SERVER \-\> CLIENT: 3\d\d.+/i", $line)) {
          echo '<span style="color:lightgreen;font-weight:bold">' . htmlspecialchars($line) . '</span><br>';
        }
        elseif (preg_match("/SERVER \-\> CLIENT: 4\d\d.+/i", $line)) {
          echo '<span style="color:yellow;font-weight:bold">' . htmlspecialchars($line) . '</span><br>';
        }
        elseif (preg_match("/SERVER \-\> CLIENT: 5\d\d.+/i", $line)) {
          echo '<span style="color:red;font-weight:bold">' . htmlspecialchars($line) . '</span><br>';
        }
        elseif (preg_match("/CLIENT \-\> SERVER:.+/i", $line)) {
          echo '<span style="color:#999;font-weight:bold">' . htmlspecialchars($line) . '</span><br>';
        }
        elseif (preg_match("/^(?!SERVER|CLIENT|Connection:|\)).+$/i", $line)) {
          echo '<span>&nbsp;&nbsp;&nbsp;&nbsp;↪ ' . htmlspecialchars($line) . '</span><br>';
        }
        else {
          echo htmlspecialchars($line) . '<br>';
        }
      }
    };
    $mail->isSMTP();
    $mail->Host = $hostname;
    if (!empty($username)) {
      $mail->SMTPAuth = true;
      $mail->Username = $username;
      $mail->Password = $password;
    }
    $mail->Port = $port;
    $mail->setFrom($mail_from, 'Mailer');
    $mail->Subject = 'A subject for a SMTP test';
    $mail->addAddress($mail_rcpt, 'Joe Null');
    $mail->Body = 'This is our test body';
    $mail->send();
  }
  else {
    echo "Unknown transport.";
  }
}
else {
  echo "Permission denied.";
}
